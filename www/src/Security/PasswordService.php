<?php

namespace Top7\Security;

/**
 * PasswordService - Secure password hashing and verification
 *
 * Replaces insecure MD5 hashing with modern Argon2ID algorithm
 * Includes migration support for existing MD5 passwords
 *
 * @package Top7\Security
 */
class PasswordService
{
    /**
     * Hash a password using Argon2ID
     *
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a password hash needs rehashing
     *
     * @param string $hash Current password hash
     * @return bool True if rehashing is recommended
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }

    /**
     * Legacy MD5 check (for migration only)
     *
     * @param string $password Plain text password
     * @param string $md5Hash MD5 hash
     * @return bool True if password matches MD5 hash
     */
    public static function verifyMD5(string $password, string $md5Hash): bool
    {
        return md5($password) === $md5Hash;
    }

    /**
     * Generate a secure random password
     *
     * @param int $length Password length (default: 12)
     * @return string Generated password
     */
    public static function generateRandom(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Generate a secure random token (for password reset, etc.)
     *
     * @param int $length Token length in bytes (default: 32)
     * @return string Hex-encoded token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
