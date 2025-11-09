<?php

namespace Top7\Security;

/**
 * CsrfToken - Cross-Site Request Forgery Protection
 *
 * Provides CSRF token generation and validation
 * Supports multiple tokens for multi-tab browsing
 *
 * @package Top7\Security
 */
class CsrfToken
{
    /** @var int Token expiration time in seconds (2 hours) */
    private const TOKEN_EXPIRATION = 7200;

    /** @var int Maximum number of tokens to keep in session */
    private const MAX_TOKENS = 5;

    /**
     * Generate a new CSRF token
     *
     * @return string The generated token
     */
    public static function generate(): string
    {
        self::startSession();

        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$token] = time();

        // Keep only the last MAX_TOKENS tokens (for multi-tab support)
        if (count($_SESSION['csrf_tokens']) > self::MAX_TOKENS) {
            array_shift($_SESSION['csrf_tokens']);
        }

        return $token;
    }

    /**
     * Validate a CSRF token
     *
     * @param string|null $token The token to validate
     * @return bool True if token is valid
     */
    public static function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        self::startSession();

        if (!isset($_SESSION['csrf_tokens'][$token])) {
            return false;
        }

        // Check if token has expired
        if (time() - $_SESSION['csrf_tokens'][$token] > self::TOKEN_EXPIRATION) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }

        // One-time use - remove token after validation
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }

    /**
     * Generate a hidden form field with CSRF token
     *
     * @return string HTML input field
     */
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' .
               htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Get the current CSRF token without generating a new one
     *
     * @return string|null Current token or null if none exists
     */
    public static function get(): ?string
    {
        self::startSession();

        if (!isset($_SESSION['csrf_tokens']) || empty($_SESSION['csrf_tokens'])) {
            return null;
        }

        // Return the most recent token
        $tokens = array_keys($_SESSION['csrf_tokens']);
        return end($tokens);
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanup(): void
    {
        self::startSession();

        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }

        $now = time();
        foreach ($_SESSION['csrf_tokens'] as $token => $timestamp) {
            if ($now - $timestamp > self::TOKEN_EXPIRATION) {
                unset($_SESSION['csrf_tokens'][$token]);
            }
        }
    }

    /**
     * Start session if not already started
     */
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
