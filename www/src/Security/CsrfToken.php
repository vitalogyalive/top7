<?php
/**
 * CSRF Token Service - Cross-Site Request Forgery Protection
 *
 * This service generates and validates CSRF tokens to protect against
 * cross-site request forgery attacks.
 *
 * Features:
 * - Multi-token support (for multi-tab browsing)
 * - Token expiration (2 hours)
 * - One-time use tokens
 *
 * @package Top7\Security
 * @since Phase 1, Task 1.1.2
 */

namespace Top7\Security;

class CsrfToken {

    /**
     * Generate a new CSRF token
     *
     * @return string The generated token
     */
    public static function generate(): string {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$token] = time();

        // Keep only last 5 tokens (for multi-tab support)
        if (count($_SESSION['csrf_tokens']) > 5) {
            array_shift($_SESSION['csrf_tokens']);
        }

        return $token;
    }

    /**
     * Validate a CSRF token
     *
     * @param string $token The token to validate
     * @return bool True if token is valid, false otherwise
     */
    public static function validate(string $token): bool {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_tokens'][$token])) {
            return false;
        }

        // Token expires after 2 hours
        if (time() - $_SESSION['csrf_tokens'][$token] > 7200) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }

        // One-time use (remove after validation)
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }

    /**
     * Generate a hidden input field with CSRF token
     *
     * @return string HTML input field
     */
    public static function field(): string {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' .
               htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate CSRF token from request or die with error
     *
     * @param string|null $token The token from POST/GET
     * @param string $errorMessage Custom error message
     * @return void
     */
    public static function validateOrDie(?string $token, string $errorMessage = 'Invalid CSRF token'): void {
        if (!self::validate($token ?? '')) {
            http_response_code(403);
            die($errorMessage);
        }
    }
}
