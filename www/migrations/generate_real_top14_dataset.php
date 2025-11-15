<?php
/**
 * Generate Real Top 14 2025-2026 Dataset
 *
 * Uses actual Top 14 match schedule and results
 * Results accurate as of November 10, 2025
 *
 * Usage: php generate_real_top14_dataset.php
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "==============================================\n";
echo "  Top 14 2025-2026 Real Dataset Generator\n";
echo "==============================================\n\n";

init_sql();
global $pdo;

// Team name mapping (real names to database IDs)
$teamMap = [
    'Stade Français' => 10,
    'Montauban' => 15, // Note: Montauban is not in Top 14, might be exhibition
    'Perpignan' => 13,
    'Bayonne' => 12,
    'Castres' => 7,
    'Pau' => 11,
    'Lyon' => 9,
    'Racing 92' => 5,
    'Montpellier' => 8,
    'Toulon' => 6,
    'Bordeaux-Bègles' => 3,
    'La Rochelle' => 2,
    'Clermont' => 4,
    'Stade Toulousain' => 1,
];

// Real match data from rugbyrama.fr (Phase Régulière 2025-2026)
// Format: [date, time, team1, score1, score2, team2]
$realMatches = [
    // Journée 1 - Samedi 6 septembre 2025
    ['2025-09-06', '13:00', 'Stade Français', 47, 24, 'Montauban'],
    ['2025-09-06', '15:00', 'Perpignan', 19, 26, 'Bayonne'],
    ['2025-09-06', '17:00', 'Castres', 15, 17, 'Pau'],
    ['2025-09-06', '17:00', 'Lyon', 32, 7, 'Racing 92'],
    ['2025-09-06', '19:00', 'Montpellier', 17, 27, 'Toulon'],
    ['2025-09-06', '21:05', 'Bordeaux-Bègles', 23, 18, 'La Rochelle'],
    // Dimanche 7 septembre
    ['2025-09-07', '21:05', 'Clermont', 24, 34, 'Stade Toulousain'],

    // Journée 2 - Samedi 13 septembre 2025
    ['2025-09-13', '14:30', 'Montauban', 18, 25, 'Lyon'],
    ['2025-09-13', '16:35', 'Bayonne', 26, 23, 'Montpellier'],
    ['2025-09-13', '16:35', 'Pau', 34, 10, 'Stade Français'],
    ['2025-09-13', '16:35', 'La Rochelle', 34, 16, 'Clermont'],
    ['2025-09-13', '16:35', 'Stade Toulousain', 31, 13, 'Perpignan'],
    ['2025-09-13', '21:00', 'Toulon', 16, 12, 'Castres'],
    // Dimanche 14 septembre
    ['2025-09-14', '21:05', 'Racing 92', 44, 32, 'Bordeaux-Bègles'],

    // Journée 3 - Samedi 20 septembre 2025
    ['2025-09-20', '14:30', 'Clermont', 50, 27, 'Pau'],
    ['2025-09-20', '18:30', 'Castres', 48, 17, 'Bayonne'],
    ['2025-09-20', '18:30', 'Lyon', 42, 37, 'Stade Français'],
    ['2025-09-20', '18:30', 'Bordeaux-Bègles', 71, 24, 'Montauban'],
    ['2025-09-20', '18:30', 'Perpignan', 15, 28, 'Racing 92'],
    ['2025-09-20', '21:00', 'Montpellier', 44, 14, 'Stade Toulousain'],

    // Journée 4 - Samedi 27 septembre 2025
    ['2025-09-27', '14:30', 'Stade Français', 28, 7, 'Bordeaux-Bègles'],
    ['2025-09-27', '16:35', 'Racing 92', 43, 31, 'Clermont'],
    ['2025-09-27', '16:35', 'Pau', 40, 15, 'Lyon'],
    ['2025-09-27', '16:35', 'La Rochelle', 31, 8, 'Perpignan'],
    ['2025-09-27', '16:35', 'Montauban', 22, 22, 'Montpellier'],
    ['2025-09-27', '21:00', 'Stade Toulousain', 59, 12, 'Castres'],
    // Dimanche 28 septembre
    ['2025-09-28', '21:05', 'Bayonne', 35, 32, 'Toulon'],

    // Journée 5 - Samedi 4 octobre 2025
    ['2025-10-04', '14:30', 'Montpellier', 37, 13, 'La Rochelle'],
    ['2025-10-04', '16:35', 'Clermont', 84, 31, 'Montauban'],
    ['2025-10-04', '16:35', 'Castres', 20, 16, 'Racing 92'],
    ['2025-10-04', '16:35', 'Toulon', 33, 17, 'Pau'],
    ['2025-10-04', '16:35', 'Perpignan', 11, 28, 'Stade Français'],
    ['2025-10-04', '21:00', 'Bordeaux-Bègles', 32, 20, 'Lyon'],
    // Dimanche 5 octobre
    ['2025-10-05', '21:05', 'Bayonne', 40, 26, 'Stade Toulousain'],

    // Journée 6 - Samedi 11 octobre 2025
    ['2025-10-11', '14:30', 'Pau', 47, 24, 'Bayonne'],
    ['2025-10-11', '16:35', 'Lyon', 44, 19, 'Perpignan'],
    ['2025-10-11', '16:35', 'Racing 92', 32, 25, 'Montpellier'],
    ['2025-10-11', '16:35', 'Stade Français', 26, 24, 'La Rochelle'],
    ['2025-10-11', '16:35', 'Montauban', 28, 32, 'Castres'],
    ['2025-10-11', '21:00', 'Clermont', 27, 10, 'Toulon'],
    // Dimanche 12 octobre
    ['2025-10-12', '21:05', 'Stade Toulousain', 56, 13, 'Bordeaux-Bègles'],

    // Journée 7 - Samedi 18 octobre 2025
    ['2025-10-18', '14:30', 'Bayonne', 44, 17, 'Clermont'],
    ['2025-10-18', '16:35', 'Castres', 29, 24, 'Stade Français'],
    ['2025-10-18', '16:35', 'Montpellier', 35, 13, 'Lyon'],
    ['2025-10-18', '16:35', 'La Rochelle', 54, 19, 'Montauban'],
    ['2025-10-18', '16:35', 'Perpignan', 12, 27, 'Bordeaux-Bègles'],
    ['2025-10-18', '21:00', 'Pau', 30, 26, 'Stade Toulousain'],
    // Dimanche 19 octobre
    ['2025-10-19', '21:05', 'Toulon', 45, 21, 'Racing 92'],

    // Journée 8 - Samedi 25 octobre 2025
    ['2025-10-25', '14:30', 'Lyon', 19, 36, 'La Rochelle'],
    ['2025-10-25', '16:35', 'Clermont', 63, 14, 'Castres'],
    ['2025-10-25', '16:35', 'Racing 92', 15, 10, 'Pau'],
    ['2025-10-25', '16:35', 'Stade Français', 35, 12, 'Montpellier'],
    ['2025-10-25', '16:35', 'Montauban', 29, 22, 'Perpignan'],
    ['2025-10-25', '21:00', 'Bordeaux-Bègles', 41, 12, 'Bayonne'],
    // Dimanche 26 octobre
    ['2025-10-26', '21:05', 'Stade Toulousain', 59, 24, 'Toulon'],

    // Journée 9 - Samedi 1 novembre 2025
    ['2025-11-01', '14:30', 'Toulon', 54, 21, 'Lyon'],
    ['2025-11-01', '16:35', 'Bayonne', 49, 7, 'Montauban'],
    ['2025-11-01', '16:35', 'Castres', 26, 28, 'Bordeaux-Bègles'],
    ['2025-11-01', '16:35', 'Montpellier', 7, 9, 'Clermont'],
    ['2025-11-01', '16:35', 'Pau', 27, 23, 'Perpignan'],
    ['2025-11-01', '21:00', 'Stade Toulousain', 29, 17, 'Stade Français'],
    // Dimanche 2 novembre
    ['2025-11-02', '21:05', 'La Rochelle', 33, 6, 'Racing 92'],

    // Journée 10 - Samedi 8 novembre 2025
    ['2025-11-08', '16:30', 'Toulon', 39, 14, 'La Rochelle'],

    // Matches not yet played (after Nov 10, 2025)
    // Will be added without scores
];

try {
    $pdo->beginTransaction();

    $seasonId = 1;
    $seasonTitle = "Saison 2025-2026";
    $seasonStart = "2025-09-06";

    echo "Step 1: Clearing existing data...\n";
    $pdo->exec("DELETE FROM player WHERE season = $seasonId");
    $pdo->exec("DELETE FROM team WHERE season = $seasonId");
    $pdo->exec("DELETE FROM `match` WHERE season = $seasonId");
    $pdo->exec("DELETE FROM calendar WHERE season = $seasonId");
    $pdo->exec("DELETE FROM season WHERE Id = $seasonId");
    $pdo->exec("DELETE FROM prono");
    $pdo->exec("DELETE FROM score");
    echo "✓ Cleared\n\n";

    echo "Step 2: Creating season...\n";
    $stmt = $pdo->prepare("INSERT INTO season (Id, title, start, start_register, stop_register, close_forum) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$seasonId, $seasonTitle, $seasonStart, '2025-08-01', '2026-05-31', '2026-06-30']);
    echo "✓ Season created\n\n";

    echo "Step 3: Creating 14 Top 14 teams...\n";
    $teams = [
        1 => ['TOU', 'Stade Toulousain'],
        2 => ['LRO', 'La Rochelle'],
        3 => ['UBB', 'Bordeaux-Bègles'],
        4 => ['ASM', 'Clermont'],
        5 => ['R92', 'Racing 92'],
        6 => ['RCT', 'Toulon'],
        7 => ['CO', 'Castres'],
        8 => ['MHR', 'Montpellier'],
        9 => ['LOU', 'Lyon'],
        10 => ['SF', 'Stade Français'],
        11 => ['PAU', 'Pau'],
        12 => ['BAY', 'Bayonne'],
        13 => ['PER', 'Perpignan'],
        14 => ['VAN', 'Vannes'],
        15 => ['MON', 'Montauban'], // Pro D2 team (exhibition matches)
    ];

    $teamStmt = $pdo->prepare("INSERT INTO team (team_short, team_long, team_idx, season, previous_season) VALUES (?, ?, ?, ?, ?)");
    foreach ($teams as $idx => $team) {
        $teamStmt->execute([$team[0], $team[1], $idx, $seasonId, 0]);
    }
    echo "✓ Created " . count($teams) . " teams\n\n";

    echo "Step 4: Processing real match data...\n";
    $matchStmt = $pdo->prepare("INSERT INTO `match` (id, season, day, team1, team2, date, time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $scoreStmt = $pdo->prepare("INSERT INTO score (id, season, day, team1, team2, score1, score2, bonus1, bonus2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $calendarStmt = $pdo->prepare("INSERT INTO calendar (season, day, date) VALUES (?, ?, ?)");

    $matchId = 1;
    $currentDay = 0;
    $lastDate = '';
    $dayDates = [];
    $playedMatches = 0;

    foreach ($realMatches as $match) {
        list($date, $time, $team1Name, $score1, $score2, $team2Name) = $match;

        // Determine match day
        if ($date != $lastDate) {
            // New match day
            if (!in_array($date, $dayDates)) {
                $currentDay++;
                $dayDates[$currentDay] = $date;
                $calendarStmt->execute([$seasonId, $currentDay, $date]);
            }
            $lastDate = $date;
        }

        // Get team IDs
        $team1Id = $teamMap[$team1Name] ?? null;
        $team2Id = $teamMap[$team2Name] ?? null;

        if (!$team1Id || !$team2Id) {
            echo "Warning: Unknown team - $team1Name vs $team2Name\n";
            continue;
        }

        // Insert match
        $matchStmt->execute([
            $matchId,
            $seasonId,
            $currentDay,
            $team1Id,
            $team2Id,
            $date,
            $time
        ]);

        // Insert score if match was played
        if ($score1 !== null && $score2 !== null) {
            // Calculate bonus points
            $diff = abs($score1 - $score2);
            $bonus1 = 0;
            $bonus2 = 0;

            // Offensive bonus (4+ tries ≈ 28+ points)
            if ($score1 >= 28) $bonus1 = 1;
            if ($score2 >= 28) $bonus2 = 1;

            // Defensive bonus (lose by ≤7 points)
            if ($score1 < $score2 && $diff <= 7) $bonus1 = 1;
            if ($score2 < $score1 && $diff <= 7) $bonus2 = 1;

            $scoreStmt->execute([
                $matchId,
                $seasonId,
                $currentDay,
                $team1Id,
                $team2Id,
                $score1,
                $score2,
                $bonus1,
                $bonus2
            ]);

            $playedMatches++;
        }

        $matchId++;
    }

    echo "✓ Created " . ($matchId - 1) . " matches\n";
    echo "✓ $playedMatches matches with results\n";
    echo "✓ $currentDay match days\n\n";

    echo "Step 5: Creating 7 test players...\n";
    $passwordService = new \Top7\Auth\PasswordService();
    $playerStmt = $pdo->prepare("
        INSERT INTO player (
            player_idx, season, status, name, pseudo, captain, rank, rankFinal,
            point, J, G, N, P, team, email, password, password_new, date_reg,
            pm, pe, ve, evo, fun, bd, bo, pc, eq, d14
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $players = [];
    for ($i = 1; $i <= 7; $i++) {
        $pseudo = "player" . $i;
        $email = "player{$i}@top7.test";
        $hashedPassword = $passwordService->hash('password123');

        $playerStmt->execute([
            $i, $seasonId, c_player_enable, "Team $i", $pseudo, 0, 0, 0,
            0, 0, 0, 0, 0, $i, $email, '', $hashedPassword,
            0, 0, 0, 0, 0, 0, 0, 0, 0, null
        ]);

        $players[] = ['idx' => $i, 'pseudo' => $pseudo, 'email' => $email];
    }
    echo "✓ Created 7 players\n\n";

    echo "Step 6: Generating player predictions for played matches...\n";
    $pronoStmt = $pdo->prepare("INSERT INTO prono (player, season, day, team) VALUES (?, ?, ?, ?)");
    $pronoCount = 0;

    // For each played day, generate predictions for each player
    for ($day = 1; $day <= $currentDay; $day++) {
        // Check if this day has played matches
        $hasResults = $pdo->query("SELECT COUNT(*) as cnt FROM score WHERE season = $seasonId AND day = $day")->fetch();

        if ($hasResults['cnt'] > 0) {
            // Generate random predictions for each player
            foreach ($players as $player) {
                // Each player picks 7 teams
                $allTeams = range(1, 14);
                shuffle($allTeams);
                $pickedTeams = array_slice($allTeams, 0, 7);

                foreach ($pickedTeams as $teamId) {
                    $pronoStmt->execute([$player['idx'], $seasonId, $day, $teamId]);
                    $pronoCount++;
                }
            }
        }
    }

    echo "✓ Generated $pronoCount predictions\n\n";

    $pdo->commit();

    echo "\n==============================================\n";
    echo "  ✓ Real Dataset Complete!\n";
    echo "==============================================\n\n";

    echo "Summary:\n";
    echo "- Season: $seasonTitle\n";
    echo "- Teams: 15 (14 Top 14 + Montauban)\n";
    echo "- Match days: $currentDay\n";
    echo "- Total matches: " . ($matchId - 1) . "\n";
    echo "- Played matches: $playedMatches\n";
    echo "- Predictions: $pronoCount\n\n";

    echo "Test Players:\n";
    foreach ($players as $p) {
        echo "  {$p['email']} / password123\n";
    }

    echo "\nLogin: http://localhost/\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
