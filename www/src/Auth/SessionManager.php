<?php

namespace Top7\Auth;

/**
 * SessionManager - User session management
 *
 * Handles session lifecycle, authentication checks, and security
 *
 * @package Top7\Auth
 */
class SessionManager
{
    /** @var int Session timeout in seconds (30 minutes) */
    private const SESSION_TIMEOUT = 1800;

    /**
     * Start session if not already started
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user has a valid session
     *
     * @param bool $redirect Whether to redirect to login on failure
     * @return bool True if session is valid
     */
    public static function check(bool $redirect = true): bool
    {
        self::start();

        // Check if session variables exist
        if (!isset($_SESSION['player_id']) || !isset($_SESSION['last_activity'])) {
            if ($redirect) {
                self::redirectToLogin();
            }
            return false;
        }

        // Check session timeout
        if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            self::destroy();
            if ($redirect) {
                self::redirectToLogin();
            }
            return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Create a new user session
     *
     * @param int $playerId Player ID
     * @param array $playerData Player data
     */
    public static function login(int $playerId, array $playerData): void
    {
        self::start();

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store session data
        $_SESSION['player_id'] = $playerId;
        $_SESSION['login'] = $playerData['login'] ?? '';
        $_SESSION['mode'] = $playerData['mode'] ?? c_player;
        $_SESSION['season'] = $playerData['season'] ?? TOP7_SEASON;
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Destroy the current session
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Get the current player ID
     *
     * @return int|null Player ID or null if not logged in
     */
    public static function getPlayerId(): ?int
    {
        self::start();
        return $_SESSION['player_id'] ?? null;
    }

    /**
     * Get the current player login
     *
     * @return string|null Player login or null if not logged in
     */
    public static function getLogin(): ?string
    {
        self::start();
        return $_SESSION['login'] ?? null;
    }

    /**
     * Get the current player mode
     *
     * @return int|null Player mode or null if not logged in
     */
    public static function getMode(): ?int
    {
        self::start();
        return $_SESSION['mode'] ?? null;
    }

    /**
     * Check if user is admin
     *
     * @return bool True if user is admin
     */
    public static function isAdmin(): bool
    {
        return self::getMode() === c_admin;
    }

    /**
     * Check if user is logged in
     *
     * @return bool True if logged in
     */
    public static function isLoggedIn(): bool
    {
        return self::check(false);
    }

    /**
     * Set a session variable
     *
     * @param string $key Variable key
     * @param mixed $value Variable value
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable
     *
     * @param string $key Variable key
     * @param mixed $default Default value if not set
     * @return mixed Variable value or default
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Redirect to login page
     */
    private static function redirectToLogin(): void
    {
        header('Location: /');
        exit;
    }
}
