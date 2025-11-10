<?php
/**
 * Generate Full Top 14 Dataset
 *
 * Creates complete dataset with:
 * - 2025-2026 season
 * - All 14 Top 14 teams
 * - 26 match days (full regular season)
 * - All matches with realistic scheduling
 * - Results for matches played before Nov 10, 2025
 * - 7 test players on one team
 * - Random predictions for each player for played matches
 *
 * Usage: php generate_full_dataset.php
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "==============================================\n";
echo "  Generating Full Top 14 Dataset\n";
echo "==============================================\n\n";

init_sql();
global $pdo;

try {
    $pdo->beginTransaction();

    // Current date: November 10, 2025
    $currentDate = new DateTime('2025-11-10');

    // Season configuration
    $seasonId = 1;
    $seasonTitle = "Saison 2025-2026";
    $seasonStart = "2025-09-06"; // First weekend of September
    $startRegister = "2025-08-01";
    $stopRegister = "2026-05-31";
    $closeForum = "2026-06-30";

    echo "Step 1: Creating season...\n";

    // Clear existing data
    $pdo->exec("DELETE FROM player WHERE season = $seasonId");
    $pdo->exec("DELETE FROM team WHERE season = $seasonId");
    $pdo->exec("DELETE FROM `match` WHERE season = $seasonId");
    $pdo->exec("DELETE FROM calendar WHERE season = $seasonId");
    $pdo->exec("DELETE FROM season WHERE Id = $seasonId");
    $pdo->exec("DELETE FROM prono");
    $pdo->exec("DELETE FROM score");

    // Create season
    $stmt = $pdo->prepare("INSERT INTO season (Id, title, start, start_register, stop_register, close_forum) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$seasonId, $seasonTitle, $seasonStart, $startRegister, $stopRegister, $closeForum]);
    echo "✓ Created season: $seasonTitle\n\n";

    // Top 14 teams 2025-2026
    echo "Step 2: Creating 14 Top 14 teams...\n";
    $teams = [
        1 => ['short' => 'TOU', 'long' => 'Stade Toulousain'],
        2 => ['short' => 'LRO', 'long' => 'Stade Rochelais'],
        3 => ['short' => 'UBB', 'long' => 'Union Bordeaux-Bègles'],
        4 => ['short' => 'ASM', 'long' => 'ASM Clermont Auvergne'],
        5 => ['short' => 'R92', 'long' => 'Racing 92'],
        6 => ['short' => 'RCT', 'long' => 'RC Toulon'],
        7 => ['short' => 'CO', 'long' => 'Castres Olympique'],
        8 => ['short' => 'MHR', 'long' => 'Montpellier Hérault Rugby'],
        9 => ['short' => 'LOU', 'long' => 'LOU Rugby'],
        10 => ['short' => 'SF', 'long' => 'Stade Français Paris'],
        11 => ['short' => 'PAU', 'long' => 'Section Paloise'],
        12 => ['short' => 'BAY', 'long' => 'Aviron Bayonnais'],
        13 => ['short' => 'PER', 'long' => 'USA Perpignan'],
        14 => ['short' => 'VAN', 'long' => 'RC Vannes'],
    ];

    $teamStmt = $pdo->prepare("INSERT INTO team (team_short, team_long, team_idx, season, previous_season) VALUES (?, ?, ?, ?, ?)");
    foreach ($teams as $idx => $team) {
        $teamStmt->execute([$team['short'], $team['long'], $idx, $seasonId, 0]);
    }
    echo "✓ Created 14 teams\n\n";

    // Create calendar (26 match days for regular season)
    echo "Step 3: Creating calendar (26 match days)...\n";
    $matchDays = [];
    $calendarStmt = $pdo->prepare("INSERT INTO calendar (season, day, date) VALUES (?, ?, ?)");

    $matchDate = new DateTime($seasonStart);
    for ($day = 1; $day <= 26; $day++) {
        $dateStr = $matchDate->format('Y-m-d');
        $matchDays[$day] = $dateStr;
        $calendarStmt->execute([$seasonId, $day, $dateStr]);
        $matchDate->modify('+1 week'); // Matches every week
    }
    echo "✓ Created 26 calendar entries\n\n";

    // Generate match schedule (round-robin)
    echo "Step 4: Creating match schedule...\n";
    $matchStmt = $pdo->prepare("INSERT INTO `match` (id, season, day, team1, team2, date, time) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Simple round-robin scheduling for 14 teams (each day has 7 matches)
    $matchId = 1;
    $matchSchedule = [];

    for ($day = 1; $day <= 26; $day++) {
        // Rotate teams for variety
        $offset = ($day - 1) % 13;
        for ($match = 0; $match < 7; $match++) {
            $team1 = (($match * 2 + $offset) % 14) + 1;
            $team2 = (($match * 2 + 1 + $offset) % 14) + 1;

            // Swap home/away based on day
            if ($day % 2 == 0) {
                list($team1, $team2) = [$team2, $team1];
            }

            $matchTime = ($match % 2 == 0) ? '15:00:00' : '17:00:00'; // Vary match times

            $matchStmt->execute([
                $matchId,
                $seasonId,
                $day,
                $team1,
                $team2,
                $matchDays[$day],
                $matchTime
            ]);

            $matchSchedule[$matchId] = [
                'day' => $day,
                'date' => $matchDays[$day],
                'team1' => $team1,
                'team2' => $team2,
                'played' => (new DateTime($matchDays[$day])) < $currentDate
            ];

            $matchId++;
        }
    }
    echo "✓ Created " . ($matchId - 1) . " matches\n\n";

    // Create 7 test players
    echo "Step 5: Creating 7 test players...\n";
    $passwordService = new \Top7\Auth\PasswordService();

    $players = [];
    $playerStmt = $pdo->prepare("
        INSERT INTO player (
            player_idx, season, status, name, pseudo, captain, rank, rankFinal,
            point, J, G, N, P, team, email, password, password_new, date_reg,
            pm, pe, ve, evo, fun, bd, bo, pc, eq, d14
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    for ($i = 1; $i <= 7; $i++) {
        $playerIdx = $i;
        $pseudo = "player" . $i;
        $email = "player{$i}@top7.test";
        $teamName = "Les Guerriers " . $i;
        $hashedPassword = $passwordService->hash('password123');

        $playerStmt->execute([
            $playerIdx,         // player_idx
            $seasonId,          // season
            c_player_enable,    // status
            $teamName,          // name
            $pseudo,            // pseudo
            0,                  // captain
            0,                  // rank
            0,                  // rankFinal
            0,                  // point
            0,                  // J (played)
            0,                  // G (won)
            0,                  // N (draw)
            0,                  // P (lost)
            $playerIdx,         // team (self-reference)
            $email,             // email
            '',                 // password (legacy)
            $hashedPassword,    // password_new
            0,                  // pm
            0,                  // pe
            0,                  // ve
            0,                  // evo
            0,                  // fun
            0,                  // bd
            0,                  // bo
            0,                  // pc
            0,                  // eq
            null                // d14
        ]);

        $players[$playerIdx] = [
            'idx' => $playerIdx,
            'pseudo' => $pseudo,
            'email' => $email,
            'team' => $playerIdx
        ];
    }
    echo "✓ Created 7 players\n\n";

    // Generate match results and player predictions for played matches
    echo "Step 6: Generating results for played matches...\n";
    $scoreStmt = $pdo->prepare("INSERT INTO score (id, season, day, team1, team2, score1, score2, bonus1, bonus2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $pronoStmt = $pdo->prepare("INSERT INTO prono (player, season, day, team) VALUES (?, ?, ?, ?)");

    $playedCount = 0;
    $pronoCount = 0;

    foreach ($matchSchedule as $matchId => $match) {
        if ($match['played']) {
            // Generate random but realistic score
            $score1 = rand(10, 45);
            $score2 = rand(10, 45);

            // Determine bonus points
            $diff = abs($score1 - $score2);
            $bonus1 = 0;
            $bonus2 = 0;

            // Offensive bonus (4+ tries = 28+ points)
            if ($score1 >= 28) $bonus1 = 1;
            if ($score2 >= 28) $bonus2 = 1;

            // Defensive bonus (lose by 7 or less)
            if ($score1 < $score2 && $diff <= 7) $bonus1 = 1;
            if ($score2 < $score1 && $diff <= 7) $bonus2 = 1;

            $scoreStmt->execute([
                $matchId,
                $seasonId,
                $match['day'],
                $match['team1'],
                $match['team2'],
                $score1,
                $score2,
                $bonus1,
                $bonus2
            ]);

            $playedCount++;

            // Generate predictions for each player for this match day
            $teams = range(1, 14);
            shuffle($teams);

            foreach ($players as $player) {
                // Each player picks 7 random teams for this day
                $pickedTeams = array_slice($teams, 0, 7);

                foreach ($pickedTeams as $teamIdx) {
                    $pronoStmt->execute([
                        $player['idx'],
                        $seasonId,
                        $match['day'],
                        $teamIdx
                    ]);
                    $pronoCount++;
                }
            }
        }
    }

    echo "✓ Generated results for $playedCount matches\n";
    echo "✓ Generated $pronoCount player predictions\n\n";

    $pdo->commit();

    echo "\n==============================================\n";
    echo "  ✓ Dataset Generation Complete!\n";
    echo "==============================================\n\n";

    echo "Summary:\n";
    echo "--------\n";
    echo "Season: $seasonTitle\n";
    echo "Teams: 14 Top 14 teams\n";
    echo "Players: 7 test players\n";
    echo "Match Days: 26 (regular season)\n";
    echo "Total Matches: " . ($matchId - 1) . "\n";
    echo "Matches Played (before Nov 10, 2025): $playedCount\n";
    echo "Player Predictions: $pronoCount\n\n";

    echo "Login Credentials:\n";
    echo "------------------\n";
    for ($i = 1; $i <= 7; $i++) {
        echo "Player $i: player{$i}@top7.test / password123\n";
    }

    echo "\nLogin URL: http://localhost/\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
