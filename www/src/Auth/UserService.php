<?php
/**
 * UserService - User/Player Management
 *
 * Provides user and player data retrieval functionality.
 * Extracted from common.inc as part of code modernization.
 *
 * @package Top7\Auth
 * @since Phase 1, Task 1.2.1
 */

namespace Top7\Auth;

use Top7\Database\QueryExecutor;

class UserService {

    /**
     * Get player information by player ID
     *
     * @param int $playerId Player ID
     * @return array|null Player data or null if not found
     */
    public static function getPlayer(int $playerId): ?array {
        $query = "SELECT * FROM player WHERE player_idx = ?";
        return QueryExecutor::fetch(__METHOD__, QueryExecutor::MODE_ONE, $query, [$playerId]);
    }

    /**
     * Get player information by player ID (alias for getPlayer)
     *
     * @param int $playerId Player ID
     * @return array|null Player data or null if not found
     */
    public static function getPlayerInfo(int $playerId): ?array {
        $query = "SELECT * FROM player WHERE player_idx = ?";
        return QueryExecutor::fetch(__METHOD__, QueryExecutor::MODE_ONE, $query, [$playerId]);
    }

    /**
     * Get player by email address
     *
     * @param string $email Player email
     * @return array|null Player data or null if not found
     */
    public static function getPlayerByEmail(string $email): ?array {
        $query = "SELECT player_idx as player, season, pseudo, team, email, password, password_new, captain
                  FROM player
                  WHERE email = ?
                  ORDER BY player_idx DESC
                  LIMIT 1";
        return QueryExecutor::fetch(__METHOD__, QueryExecutor::MODE_ONE, $query, [$email]);
    }

    /**
     * Get player status information
     *
     * @param array $sessionData Session data containing player info
     * @return array Status array with 'player' and 'team' keys
     */
    public static function getPlayerStatus(array $sessionData): array {
        $playerId = $sessionData['player'] ?? 0;

        if (!$playerId) {
            return ['player' => 0, 'team' => 0];
        }

        $playerInfo = self::getPlayerInfo($playerId);
        if (!$playerInfo) {
            return ['player' => 0, 'team' => 0];
        }

        // Get team info - calling legacy function for now
        $teamInfo = function_exists('get_info_team')
            ? get_info_team($playerInfo['team'])
            : ['status' => 0];

        return [
            'player' => $playerInfo['status'] ?? 0,
            'team' => $teamInfo['status'] ?? 0
        ];
    }

    /**
     * Get player's pseudo/nickname
     *
     * @param int $playerId Player ID
     * @return string Player pseudo or empty string
     */
    public static function getPlayerPseudo(int $playerId): string {
        $player = self::getPlayer($playerId);
        return $player['pseudo'] ?? '';
    }

    /**
     * Check if player is a captain
     *
     * @param int $playerId Player ID
     * @return bool True if player is captain
     */
    public static function isCaptain(int $playerId): bool {
        $player = self::getPlayer($playerId);
        return isset($player['captain']) && $player['captain'] == 1;
    }

    /**
     * Get player's team ID
     *
     * @param int $playerId Player ID
     * @return int Team ID or 0 if not found
     */
    public static function getPlayerTeam(int $playerId): int {
        $player = self::getPlayer($playerId);
        return (int)($player['team'] ?? 0);
    }

    /**
     * Get player's current season
     *
     * @param int $playerId Player ID
     * @return int Season ID or 0 if not found
     */
    public static function getPlayerSeason(int $playerId): int {
        $player = self::getPlayer($playerId);
        return (int)($player['season'] ?? 0);
    }

    /**
     * Update player email
     *
     * @param string $email New email address
     * @param int $playerId Player ID
     * @return void
     */
    public static function updatePlayerEmail(string $email, int $playerId): void {
        $query = "UPDATE player SET email = ? WHERE player_idx = ?";
        QueryExecutor::execute(__METHOD__, $query, [$email, $playerId]);
    }

    /**
     * Check if email exists in database
     *
     * @param string $email Email to check
     * @return bool True if email exists
     */
    public static function emailExists(string $email): bool {
        $query = "SELECT COUNT(*) as count FROM player WHERE email = ?";
        $result = QueryExecutor::fetch(__METHOD__, QueryExecutor::MODE_ONE, $query, [$email]);
        return isset($result['count']) && $result['count'] > 0;
    }
}
