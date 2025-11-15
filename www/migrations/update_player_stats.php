<?php
/**
 * Update Player Statistics
 *
 * Calculates and updates player points, rankings, and other statistics
 *
 * Usage:
 *   php update_player_stats.php
 *
 * @package Top7\Migrations
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "Updating player statistics...\n\n";

init_sql();
global $pdo;

try {
    $seasonId = 1;
    $maxDay = 5; // Days with scores

    // Calculate player points for each day
    echo "Calculating player points...\n";
    for ($day = 1; $day <= $maxDay; $day++) {
        echo "  Day $day: ";

        // Calculate points for each player
        // Points = sum of (V*4 + N*2 + bo + bd) from their predicted teams
        $query = "
            SELECT
                p.player_idx,
                p.pseudo,
                COALESCE(SUM(s.pc), 0) as total_points,
                COALESCE(SUM(s.pm), 0) as points_marked,
                COALESCE(SUM(s.pe), 0) as points_against
            FROM player p
            LEFT JOIN prono pr ON p.player_idx = pr.player
            LEFT JOIN score s ON pr.team = s.team AND pr.day = s.day AND pr.season = s.season
            WHERE p.season = ? AND pr.day <= ? AND s.day <= ?
            GROUP BY p.player_idx, p.pseudo
            ORDER BY total_points DESC, points_marked DESC
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$seasonId, $day, $day]);
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update player points and rank
        $rank = 1;
        foreach ($players as $player) {
            $updateQuery = "UPDATE player
                           SET point = ?,
                               pm = ?,
                               pe = ?,
                               `rank` = ?
                           WHERE player_idx = ? AND season = ?";

            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([
                $player['total_points'],
                $player['points_marked'],
                $player['points_against'],
                $rank,
                $player['player_idx'],
                $seasonId
            ]);

            $rank++;
        }

        echo "Updated " . count($players) . " players\n";
    }

    // Calculate number of different teams selected (eq field)
    echo "\nCalculating team diversity (eq)...\n";
    $query = "
        SELECT
            pr.player,
            COUNT(DISTINCT pr.team) as num_teams
        FROM prono pr
        JOIN score s ON pr.team = s.team AND pr.day = s.day AND pr.season = s.season
        WHERE pr.season = ? AND pr.day <= ? AND s.J = 1
        GROUP BY pr.player
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$seasonId, $maxDay]);
    $teamCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($teamCounts as $tc) {
        $updateQuery = "UPDATE player SET eq = ? WHERE player_idx = ? AND season = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$tc['num_teams'], $tc['player'], $seasonId]);
    }

    echo "Updated team diversity for " . count($teamCounts) . " players\n";

    // Calculate match statistics (J, G, N, P)
    echo "\nCalculating match statistics...\n";
    $query = "
        SELECT
            pr.player,
            COUNT(*) as matches_played,
            SUM(s.V) as wins,
            SUM(s.N) as draws,
            SUM(s.D) as losses
        FROM prono pr
        JOIN score s ON pr.team = s.team AND pr.day = s.day AND pr.season = s.season
        WHERE pr.season = ? AND pr.day <= ?
        GROUP BY pr.player
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$seasonId, $maxDay]);
    $matchStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($matchStats as $ms) {
        $updateQuery = "UPDATE player SET J = ?, G = ?, N = ?, P = ? WHERE player_idx = ? AND season = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([
            $ms['matches_played'],
            $ms['wins'],
            $ms['draws'],
            $ms['losses'],
            $ms['player'],
            $seasonId
        ]);
    }

    echo "Updated match statistics for " . count($matchStats) . " players\n";

    // Display final rankings
    echo "\n===========================================\n";
    echo "  Final Rankings (after Day $maxDay)\n";
    echo "===========================================\n";

    $query = "SELECT `rank`, pseudo, point, pm, pe, eq, J, G, N, P
              FROM player
              WHERE season = ?
              ORDER BY `rank` ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$seasonId]);
    $finalRankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo sprintf("%-4s %-15s %6s %6s %6s %4s %4s %4s %4s %4s\n",
                 "Rank", "Player", "Points", "PM", "PE", "Eq", "J", "G", "N", "P");
    echo str_repeat("-", 80) . "\n";

    foreach ($finalRankings as $player) {
        echo sprintf("%-4s %-15s %6s %6s %6s %4s %4s %4s %4s %4s\n",
                     $player['rank'],
                     $player['pseudo'],
                     $player['point'],
                     $player['pm'],
                     $player['pe'],
                     $player['eq'],
                     $player['J'],
                     $player['G'],
                     $player['N'],
                     $player['P']);
    }

    echo "\n✓ Player statistics updated successfully!\n";
    echo "\nYou can now view:\n";
    echo "- Classement Top7: http://localhost/\n";
    echo "- All 7 players should be visible with their points and rankings\n";

} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
