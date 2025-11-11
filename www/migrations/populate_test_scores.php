<?php
/**
 * Populate Test Scores
 *
 * Creates sample score data for the first 5 match days
 *
 * Usage:
 *   php populate_test_scores.php
 *
 * @package Top7\Migrations
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "Populating test scores for Top 14...\n\n";

init_sql();
global $pdo;

try {
    $pdo->beginTransaction();

    $seasonId = 1;

    // Clear existing scores
    $pdo->exec("DELETE FROM score WHERE season = $seasonId");
    echo "✓ Cleared existing scores\n";

    // Get all matches
    $matches = $pdo->query("SELECT * FROM `match` WHERE season = $seasonId ORDER BY day, id")->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($matches) . " matches\n\n";

    // Generate scores for first 5 days
    $maxDay = 5;

    foreach ($matches as $match) {
        $day = $match['day'];
        if ($day > $maxDay) {
            continue;
        }

        $team1 = $match['team1'];
        $team2 = $match['team2'];

        // Generate random scores
        $score1 = rand(10, 40);
        $score2 = rand(10, 40);

        // Calculate tries (roughly 1 try per 7 points)
        $tries1 = intval($score1 / 7);
        $tries2 = intval($score2 / 7);

        // Determine winner
        $winner1 = $score1 > $score2 ? 1 : 0;
        $winner2 = $score2 > $score1 ? 1 : 0;
        $draw = $score1 == $score2 ? 1 : 0;

        // Calculate bonus points
        $diff = abs($score1 - $score2);
        $bonus1_off = $tries1 >= 4 ? 1 : 0;  // Offensive bonus (4+ tries)
        $bonus2_off = $tries2 >= 4 ? 1 : 0;
        $bonus1_def = ($winner1 == 0 && $diff <= 7) ? 1 : 0;  // Defensive bonus (lose by ≤7)
        $bonus2_def = ($winner2 == 0 && $diff <= 7) ? 1 : 0;

        // Calculate standings points
        $points1 = $winner1 ? 4 : ($draw ? 2 : 0);
        $points1 += $bonus1_off + $bonus1_def;

        $points2 = $winner2 ? 4 : ($draw ? 2 : 0);
        $points2 += $bonus2_off + $bonus2_def;

        // Insert score for team1
        $sql = "INSERT INTO score (season, day, team, `rank`, pm, pe, pc, bd, bo, em, ee, ve, J, V, N, D)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $seasonId,
            $day,
            $team1,
            0,  // rank (will calculate later)
            $score1,  // pm - points marked
            $score2,  // pe - points conceded
            $points1,  // pc - standings points
            $bonus1_def,  // bd - bonus défensif
            $bonus1_off,  // bo - bonus offensif
            $tries1,  // em - essais marqués
            $tries2,  // ee - essais encaissés
            0,  // ve - victoire extérieur
            1,  // J - matches played
            $winner1,  // V - victories
            $draw,  // N - draws
            1 - $winner1 - $draw  // D - defeats
        ]);

        // Insert score for team2
        $stmt->execute([
            $seasonId,
            $day,
            $team2,
            0,  // rank
            $score2,  // pm
            $score1,  // pe
            $points2,  // pc
            $bonus2_def,  // bd
            $bonus2_off,  // bo
            $tries2,  // em
            $tries1,  // ee
            0,  // ve
            1,  // J
            $winner2,  // V
            $draw,  // N
            1 - $winner2 - $draw  // D
        ]);

        echo "Day $day: Team $team1 ($score1) vs Team $team2 ($score2)\n";
    }

    // Calculate rankings for each day
    echo "\nCalculating rankings...\n";
    for ($day = 1; $day <= $maxDay; $day++) {
        $teams = $pdo->query("SELECT team, pc FROM score WHERE season = $seasonId AND day = $day ORDER BY pc DESC, pm - pe DESC")->fetchAll(PDO::FETCH_ASSOC);

        $rank = 1;
        foreach ($teams as $team) {
            $pdo->prepare("UPDATE score SET `rank` = ? WHERE season = ? AND day = ? AND team = ?")
                ->execute([$rank, $seasonId, $day, $team['team']]);
            $rank++;
        }
    }

    $pdo->commit();

    echo "\n✓ Successfully populated test scores for days 1-$maxDay!\n";
    echo "\nYou can now view the Top 14 standings at: http://localhost/\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
