<?php
/**
 * Password Service - Modern password hashing with Argon2ID
 *
 * This service handles secure password hashing and verification using
 * Argon2ID algorithm, replacing the legacy MD5 implementation.
 *
 * @package Top7\Auth
 * @since Phase 1, Task 1.1.1
 */

namespace Top7\Auth;

class PasswordService {

    /**
     * Hash a password using Argon2ID algorithm
     *
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hash(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password Plain text password to verify
     * @param string $hash     Stored password hash
     * @return bool True if password matches, false otherwise
     */
    public function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Check if a hash needs to be rehashed (algorithm updated)
     *
     * @param string $hash Stored password hash
     * @return bool True if hash needs rehashing
     */
    public function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }

    /**
     * Verify MD5 password (legacy support during migration)
     *
     * @param string $password Plain text password
     * @param string $md5Hash  MD5 hash from database
     * @return bool True if password matches MD5 hash
     */
    public function verifyLegacyMd5(string $password, string $md5Hash): bool {
        return md5($password) === $md5Hash;
    }
}
