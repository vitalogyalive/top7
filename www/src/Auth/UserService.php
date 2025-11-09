<?php

namespace Top7\Auth;

use Top7\Database\Connection;
use Top7\Security\PasswordService;

/**
 * UserService - User authentication and management
 *
 * Handles user login, registration, and password management
 *
 * @package Top7\Auth
 */
class UserService
{
    /**
     * Authenticate a user with login and password
     *
     * @param string $login User login
     * @param string $password Plain text password
     * @return array|null Player data if authentication successful, null otherwise
     */
    public static function authenticate(string $login, string $password): ?array
    {
        $query = "SELECT * FROM player WHERE login = ? AND season = ?";
        $season = defined('TOP7_SEASON') ? TOP7_SEASON : date('Y');
        $player = Connection::fetch($query, [$login, $season], c_one);

        if (!$player) {
            return null;
        }

        // Try new password hash first (Argon2ID)
        if (!empty($player['password_new'])) {
            if (PasswordService::verify($password, $player['password_new'])) {
                return $player;
            }
        }

        // Fall back to legacy MD5 password for migration
        if (!empty($player['password'])) {
            if (PasswordService::verifyMD5($password, $player['password'])) {
                // Auto-migrate to new password hash
                self::updatePassword($player['id'], $password);
                return $player;
            }
        }

        return null;
    }

    /**
     * Update user password (creates new hash, keeps old for migration)
     *
     * @param int $playerId Player ID
     * @param string $newPassword New plain text password
     * @return bool True on success
     */
    public static function updatePassword(int $playerId, string $newPassword): bool
    {
        $newHash = PasswordService::hash($newPassword);
        $query = "UPDATE player SET password_new = ? WHERE id = ?";
        $affected = Connection::exec($query, [$newHash, $playerId]);

        return $affected > 0;
    }

    /**
     * Create a new user account
     *
     * @param array $userData User data
     * @return int|null New player ID or null on failure
     */
    public static function register(array $userData): ?int
    {
        // Hash the password with new method
        $passwordHash = PasswordService::hash($userData['password']);

        $query = "INSERT INTO player (login, password_new, email, season, date_reg, status, mode)
                  VALUES (?, ?, ?, ?, NOW(), ?, ?)";

        $params = [
            $userData['login'],
            $passwordHash,
            $userData['email'],
            $userData['season'] ?? TOP7_SEASON,
            $userData['status'] ?? c_player_waiting,
            $userData['mode'] ?? c_player
        ];

        $affected = Connection::exec($query, $params);

        if ($affected > 0) {
            return (int) Connection::lastInsertId();
        }

        return null;
    }

    /**
     * Get player by ID
     *
     * @param int $playerId Player ID
     * @return array|null Player data or null if not found
     */
    public static function getById(int $playerId): ?array
    {
        $query = "SELECT * FROM player WHERE id = ?";
        return Connection::fetch($query, [$playerId], c_one);
    }

    /**
     * Get player by login
     *
     * @param string $login Player login
     * @param int|null $season Season year (defaults to current season)
     * @return array|null Player data or null if not found
     */
    public static function getByLogin(string $login, ?int $season = null): ?array
    {
        $season = $season ?? (defined('TOP7_SEASON') ? TOP7_SEASON : date('Y'));
        $query = "SELECT * FROM player WHERE login = ? AND season = ?";
        return Connection::fetch($query, [$login, $season], c_one);
    }

    /**
     * Check if login already exists for the season
     *
     * @param string $login Player login
     * @param int|null $season Season year
     * @return bool True if login exists
     */
    public static function loginExists(string $login, ?int $season = null): bool
    {
        return self::getByLogin($login, $season) !== null;
    }

    /**
     * Generate a password reset token
     *
     * @param string $login Player login
     * @param string $email Player email
     * @return string|null Reset token or null on failure
     */
    public static function generatePasswordResetToken(string $login, string $email): ?string
    {
        $player = self::getByLogin($login);

        if (!$player || $player['email'] !== $email) {
            return null;
        }

        $token = PasswordService::generateToken();
        $query = "UPDATE player SET password_reset_token = ?, password_reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                  WHERE id = ?";

        Connection::exec($query, [$token, $player['id']]);

        return $token;
    }

    /**
     * Validate a password reset token
     *
     * @param string $token Reset token
     * @return array|null Player data if token is valid, null otherwise
     */
    public static function validatePasswordResetToken(string $token): ?array
    {
        $query = "SELECT * FROM player
                  WHERE password_reset_token = ?
                  AND password_reset_expires > NOW()";

        return Connection::fetch($query, [$token], c_one);
    }

    /**
     * Reset password using a token
     *
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return bool True on success
     */
    public static function resetPassword(string $token, string $newPassword): bool
    {
        $player = self::validatePasswordResetToken($token);

        if (!$player) {
            return false;
        }

        $newHash = PasswordService::hash($newPassword);
        $query = "UPDATE player SET password_new = ?, password_reset_token = NULL, password_reset_expires = NULL
                  WHERE id = ?";

        $affected = Connection::exec($query, [$newHash, $player['id']]);

        return $affected > 0;
    }
}
