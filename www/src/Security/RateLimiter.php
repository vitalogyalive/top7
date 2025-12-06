<?php
/**
 * Rate Limiter Service - Protection against brute force attacks
 *
 * This service implements rate limiting to protect against:
 * - Brute force login attempts
 * - Password reset abuse
 * - Registration spam
 *
 * Uses session-based and IP-based rate limiting with configurable
 * thresholds and lockout periods.
 *
 * @package Top7\Security
 * @since Phase 1, Security Enhancement
 */

namespace Top7\Security;

class RateLimiter {

    /**
     * Default configuration for rate limiting
     */
    private const DEFAULT_MAX_ATTEMPTS = 5;
    private const DEFAULT_LOCKOUT_TIME = 900; // 15 minutes in seconds
    private const DEFAULT_DECAY_TIME = 3600; // 1 hour - time to forget old attempts

    /**
     * Check if an action is rate limited
     *
     * @param string $action The action being rate limited (e.g., 'login', 'password_reset')
     * @param string $identifier Unique identifier (e.g., IP address, email)
     * @param int $maxAttempts Maximum allowed attempts before lockout
     * @param int $lockoutTime Lockout duration in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int|null]
     */
    public static function check(
        string $action,
        string $identifier,
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        int $lockoutTime = self::DEFAULT_LOCKOUT_TIME
    ): array {
        self::ensureSession();

        $key = self::getKey($action, $identifier);
        $data = self::getData($key);

        // Clean up old attempts
        $data = self::cleanOldAttempts($data);

        // Check if currently locked out
        if (isset($data['locked_until']) && $data['locked_until'] > time()) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => $data['locked_until'] - time(),
                'attempts' => count($data['attempts'] ?? [])
            ];
        }

        // Clear lockout if expired
        if (isset($data['locked_until'])) {
            unset($data['locked_until']);
            $data['attempts'] = [];
            self::setData($key, $data);
        }

        $attemptCount = count($data['attempts'] ?? []);
        $remaining = max(0, $maxAttempts - $attemptCount);

        return [
            'allowed' => $attemptCount < $maxAttempts,
            'remaining' => $remaining,
            'retry_after' => null,
            'attempts' => $attemptCount
        ];
    }

    /**
     * Record a failed attempt
     *
     * @param string $action The action being rate limited
     * @param string $identifier Unique identifier
     * @param int $maxAttempts Maximum allowed attempts before lockout
     * @param int $lockoutTime Lockout duration in seconds
     * @return array Status after recording the attempt
     */
    public static function recordFailure(
        string $action,
        string $identifier,
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        int $lockoutTime = self::DEFAULT_LOCKOUT_TIME
    ): array {
        self::ensureSession();

        $key = self::getKey($action, $identifier);
        $data = self::getData($key);

        // Clean up old attempts first
        $data = self::cleanOldAttempts($data);

        // Add new attempt
        if (!isset($data['attempts'])) {
            $data['attempts'] = [];
        }
        $data['attempts'][] = time();

        // Check if we need to lock out
        if (count($data['attempts']) >= $maxAttempts) {
            $data['locked_until'] = time() + $lockoutTime;
            error_log("Rate limit lockout triggered for $action: $identifier");
        }

        self::setData($key, $data);

        $remaining = max(0, $maxAttempts - count($data['attempts']));

        return [
            'allowed' => !isset($data['locked_until']),
            'remaining' => $remaining,
            'retry_after' => isset($data['locked_until']) ? $lockoutTime : null,
            'attempts' => count($data['attempts'])
        ];
    }

    /**
     * Record a successful attempt (clears rate limiting)
     *
     * @param string $action The action
     * @param string $identifier Unique identifier
     * @return void
     */
    public static function recordSuccess(string $action, string $identifier): void {
        self::ensureSession();

        $key = self::getKey($action, $identifier);
        self::clearData($key);
    }

    /**
     * Clear rate limiting data for an action/identifier
     *
     * @param string $action The action
     * @param string $identifier Unique identifier
     * @return void
     */
    public static function clear(string $action, string $identifier): void {
        self::ensureSession();

        $key = self::getKey($action, $identifier);
        self::clearData($key);
    }

    /**
     * Get the client's IP address
     *
     * @return string The client IP
     */
    public static function getClientIp(): string {
        // Check for forwarded IP (when behind proxy/load balancer)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get time remaining until lockout expires
     *
     * @param string $action The action
     * @param string $identifier Unique identifier
     * @return int|null Seconds remaining, or null if not locked
     */
    public static function getLockoutRemaining(string $action, string $identifier): ?int {
        self::ensureSession();

        $key = self::getKey($action, $identifier);
        $data = self::getData($key);

        if (isset($data['locked_until']) && $data['locked_until'] > time()) {
            return $data['locked_until'] - time();
        }

        return null;
    }

    /**
     * Check if currently locked out
     *
     * @param string $action The action
     * @param string $identifier Unique identifier
     * @return bool
     */
    public static function isLockedOut(string $action, string $identifier): bool {
        return self::getLockoutRemaining($action, $identifier) !== null;
    }

    /**
     * Ensure session is started
     */
    private static function ensureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Generate a storage key
     */
    private static function getKey(string $action, string $identifier): string {
        return 'rate_limit_' . $action . '_' . md5($identifier);
    }

    /**
     * Get rate limiting data from session
     */
    private static function getData(string $key): array {
        return $_SESSION[$key] ?? [];
    }

    /**
     * Set rate limiting data in session
     */
    private static function setData(string $key, array $data): void {
        $_SESSION[$key] = $data;
    }

    /**
     * Clear rate limiting data
     */
    private static function clearData(string $key): void {
        unset($_SESSION[$key]);
    }

    /**
     * Remove attempts older than decay time
     */
    private static function cleanOldAttempts(array $data, int $decayTime = self::DEFAULT_DECAY_TIME): array {
        if (!isset($data['attempts'])) {
            return $data;
        }

        $cutoff = time() - $decayTime;
        $data['attempts'] = array_filter($data['attempts'], function($time) use ($cutoff) {
            return $time > $cutoff;
        });
        $data['attempts'] = array_values($data['attempts']); // Re-index

        return $data;
    }

    /**
     * Format remaining time for display
     *
     * @param int $seconds Seconds remaining
     * @return string Human-readable time
     */
    public static function formatRemainingTime(int $seconds): string {
        if ($seconds < 60) {
            return "$seconds seconde" . ($seconds > 1 ? 's' : '');
        }

        $minutes = ceil($seconds / 60);
        return "$minutes minute" . ($minutes > 1 ? 's' : '');
    }
}
