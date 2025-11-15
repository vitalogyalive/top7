<?php
/**
 * Populate Test Predictions (Pronos)
 *
 * Creates predictions for all test users for played match days
 *
 * Usage:
 *   php populate_test_pronos.php
 *
 * @package Top7\Migrations
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "Populating test predictions (pronos) for Top7...\n\n";

init_sql();
global $pdo;

try {
    $pdo->beginTransaction();

    $seasonId = 1;
    $maxDay = 5; // Days with scores

    // Clear existing pronos
    $pdo->exec("DELETE FROM prono WHERE season = $seasonId");
    echo "✓ Cleared existing pronos\n";

    // Get all players
    $players = $pdo->query("SELECT player_idx FROM player WHERE season = $seasonId ORDER BY player_idx")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($players) . " players\n\n";

    $pronoStmt = $pdo->prepare("INSERT INTO prono (player, season, day, `match`, team) VALUES (?, ?, ?, ?, ?)");
    $pronoCount = 0;

    // For each day with scores
    for ($day = 1; $day <= $maxDay; $day++) {
        echo "Day $day:\n";

        // Get all matches for this day
        $matches = $pdo->query("
            SELECT id, team1, team2
            FROM `match`
            WHERE season = $seasonId AND day = $day
            ORDER BY id
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Get the winning teams for this day (to create realistic predictions)
        $winningTeams = $pdo->query("
            SELECT team
            FROM score
            WHERE season = $seasonId AND day = $day AND V = 1
            ORDER BY team
        ")->fetchAll(PDO::FETCH_COLUMN);

        // If we have a draw, include both teams
        $drawMatches = $pdo->query("
            SELECT team
            FROM score
            WHERE season = $seasonId AND day = $day AND N = 1
            ORDER BY team
        ")->fetchAll(PDO::FETCH_COLUMN);

        $allPossibleTeams = array_unique(array_merge($winningTeams, $drawMatches));

        echo "  Winning/draw teams: " . implode(", ", $allPossibleTeams) . "\n";

        // Each player picks 7 teams
        foreach ($players as $player) {
            $playerIdx = $player['player_idx'];

            // Create varied predictions:
            // - Some players pick mostly winners (good players)
            // - Some pick randomly (bad players)

            // Player 1-3: Pick mostly winners (80% accuracy)
            // Player 4-5: Pick some winners (50% accuracy)
            // Player 6-7: Pick randomly (30% accuracy)

            $accuracy = 0.3; // default
            if ($playerIdx <= 3) {
                $accuracy = 0.8; // good players
            } elseif ($playerIdx <= 5) {
                $accuracy = 0.5; // average players
            }

            $pickedTeams = [];

            // First, add some winning teams based on accuracy
            $numWinners = intval(7 * $accuracy);
            $shuffledWinners = $allPossibleTeams;
            shuffle($shuffledWinners);

            for ($i = 0; $i < $numWinners && $i < count($shuffledWinners); $i++) {
                $pickedTeams[] = $shuffledWinners[$i];
            }

            // Fill the rest with random teams (including some losers)
            $allTeams = range(1, 14);
            shuffle($allTeams);

            foreach ($allTeams as $team) {
                if (count($pickedTeams) >= 7) break;
                if (!in_array($team, $pickedTeams)) {
                    $pickedTeams[] = $team;
                }
            }

            // Ensure exactly 7 teams
            $pickedTeams = array_slice($pickedTeams, 0, 7);

            // Insert predictions - each prediction needs to be linked to a match
            foreach ($pickedTeams as $teamIdx) {
                // Find which match this team is playing in
                $matchId = null;
                foreach ($matches as $match) {
                    if ($match['team1'] == $teamIdx || $match['team2'] == $teamIdx) {
                        $matchId = $match['id'];
                        break;
                    }
                }

                if ($matchId) {
                    $pronoStmt->execute([$playerIdx, $seasonId, $day, $matchId, $teamIdx]);
                    $pronoCount++;
                }
            }

            echo "  Player $playerIdx: " . implode(", ", $pickedTeams) . "\n";
        }
        echo "\n";
    }

    $pdo->commit();

    echo "✓ Successfully created $pronoCount predictions for $maxDay days!\n";
    echo "\nYou can now view:\n";
    echo "- Classement Top7: http://localhost/\n";
    echo "- Résultat Top7: Check individual player results\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
