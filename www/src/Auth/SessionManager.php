<?php
/**
 * SessionManager - Session Management
 *
 * Provides session validation and initialization functionality.
 * Extracted from common.inc as part of code modernization.
 *
 * @package Top7\Auth
 * @since Phase 1, Task 1.2.1
 */

namespace Top7\Auth;

use Top7\Utils\Logger;

class SessionManager {

    /**
     * Check if session is valid and active
     *
     * Validates:
     * - Session timeout (based on last activity)
     * - Login status
     * - Token presence
     *
     * Redirects to index if session is invalid.
     *
     * @return void
     */
    public static function checkSession(): void {
        session_start();
        Logger::logVar(__METHOD__, "SESSION", $_SESSION);

        // Check session timeout (requires now() function)
        if (isset($_SESSION['last_activity']) &&
            function_exists('now') &&
            (now() - $_SESSION['last_activity'] > c_session_activity)) {
            self::destroySession();
            self::redirectToIndex();
        }

        // Update last activity
        if (function_exists('now')) {
            $_SESSION['last_activity'] = now();
        }

        // Check if user is logged in
        if (!isset($_SESSION['login'])) {
            self::redirectToIndex();
        }

        // Check if token exists (legacy support)
        if (!isset($_SESSION['token'])) {
            self::redirectToIndex();
        }
    }

    /**
     * Initialize time-based session variables
     *
     * Sets up session variables for:
     * - Current season
     * - Current day
     * - Game timing and deadlines
     *
     * Note: This function has many dependencies and remains largely as a wrapper
     * for the legacy implementation. Future refactoring should break this into
     * smaller, more focused methods.
     *
     * @return void
     */
    public static function initTimeSession(): void {
        // This function is complex with many dependencies
        // For now, we keep it as a wrapper to the legacy function
        // Future refactoring should extract the logic into smaller methods

        if (function_exists('init_time_session')) {
            // Call legacy function - will be refactored in future iteration
            $legacyFunction = 'init_time_session';
            $legacyFunction();
        }
    }

    /**
     * Destroy current session
     *
     * @return void
     */
    public static function destroySession(): void {
        session_unset();
        session_destroy();
    }

    /**
     * Redirect to index page
     *
     * @return never
     */
    public static function redirectToIndex(): never {
        echo '<meta http-equiv="refresh" content="0;URL=index">';
        exit;
    }

    /**
     * Start a new session
     *
     * @return void
     */
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if session is active
     *
     * @return bool True if session is active
     */
    public static function isSessionActive(): bool {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Get session variable
     *
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session value or default
     */
    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session variable
     *
     * @param string $key Session key
     * @param mixed $value Value to set
     * @return void
     */
    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session key exists
     *
     * @param string $key Session key
     * @return bool True if key exists
     */
    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session variable
     *
     * @param string $key Session key
     * @return void
     */
    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     *
     * @return array Session data
     */
    public static function all(): array {
        return $_SESSION ?? [];
    }

    /**
     * Initialize player session after successful login
     *
     * @param array $player Player data from database
     * @return void
     */
    public static function initPlayerSession(array $player): void {
        self::startSession();

        $_SESSION['login'] = $player['email'];
        $_SESSION['pseudo'] = $player['pseudo'];
        $_SESSION['player'] = $player['player'];
        $_SESSION['captain'] = $player['captain'];
        $_SESSION['top7team'] = $player['team'];
        $_SESSION['mode'] = c_guest;
        $_SESSION['game'] = c_disable;
        $_SESSION['display'] = c_top7;

        if (function_exists('now')) {
            $_SESSION['last_activity'] = now();
        }
    }

    /**
     * Initialize admin session
     *
     * @param string $login Admin login
     * @return void
     */
    public static function initAdminSession(string $login): void {
        self::startSession();

        $_SESSION['login'] = $login;
        $_SESSION['pseudo'] = "";
        $_SESSION['mode'] = c_admin;
        $_SESSION['display'] = c_top14;
        $_SESSION['captain'] = "";

        if (function_exists('now')) {
            $_SESSION['last_activity'] = now();
        }
    }
}
