<?php
/**
 * Insert Test Season Script
 *
 * Creates a test season for the current year to allow the application to function
 *
 * Usage:
 *   php insert_test_season.php
 *
 * @package Top7\Migrations
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "Creating test season...\n\n";

init_sql();
global $pdo;

try {
    // Check if season already exists
    $existingSeasonQuery = $pdo->query("SELECT COUNT(*) as count FROM season");
    $existingSeasonResult = $existingSeasonQuery->fetch();

    if ($existingSeasonResult['count'] > 0) {
        echo "⚠ Season data already exists. Skipping...\n";
        echo "Current seasons:\n";
        $seasons = $pdo->query("SELECT Id, title, start FROM season ORDER BY Id DESC")->fetchAll();
        foreach ($seasons as $season) {
            echo "  - Season {$season['Id']}: {$season['title']} (starts: {$season['start']})\n";
        }
        exit(0);
    }

    // Create a test season for current year
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;

    $seasonId = 1;
    $seasonTitle = "Season $currentYear-$nextYear (Test)";
    $seasonStart = "$currentYear-09-01";
    $startRegister = "$currentYear-08-01";
    $stopRegister = "$currentYear-12-31";
    $closeForum = "$nextYear-06-30";

    $sql = "INSERT INTO `season` (`Id`, `title`, `start`, `start_register`, `stop_register`, `close_forum`)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$seasonId, $seasonTitle, $seasonStart, $startRegister, $stopRegister, $closeForum]);

    echo "✓ Created test season:\n";
    echo "  ID: {$seasonId}\n";
    echo "  Title: {$seasonTitle}\n";
    echo "  Season Start: {$seasonStart}\n";
    echo "  Registration Period: {$startRegister} to {$stopRegister}\n";
    echo "  Forum Closes: {$closeForum}\n";

    // Create basic calendar entries (26 match days for a typical rugby season)
    echo "\nCreating calendar entries...\n";

    $calendarSql = "INSERT INTO `calendar` (`season`, `day`, `date`) VALUES (?, ?, ?)";
    $calendarStmt = $pdo->prepare($calendarSql);

    $startDate = new DateTime($seasonStart);

    for ($day = 1; $day <= 26; $day++) {
        // Rugby matches are typically weekly, on Saturdays
        $matchDate = clone $startDate;
        $matchDate->modify('+' . ($day - 1) . ' weeks');

        $calendarStmt->execute([
            $seasonId,
            $day,
            $matchDate->format('Y-m-d')
        ]);
    }

    echo "✓ Created 26 calendar entries (weekly matches)\n";

    // Create Top 14 teams
    echo "\nCreating Top 14 teams...\n";

    $teams = [
        ['Toulouse', 'Stade Toulousain', 1],
        ['La Rochelle', 'Stade Rochelais', 2],
        ['Bordeaux', 'Union Bordeaux-Bègles', 3],
        ['Clermont', 'ASM Clermont Auvergne', 4],
        ['Racing', 'Racing 92', 5],
        ['Toulon', 'RC Toulon', 6],
        ['Castres', 'Castres Olympique', 7],
        ['Montpellier', 'Montpellier HR', 8],
        ['Lyon', 'LOU Rugby', 9],
        ['Stade Français', 'Stade Français Paris', 10],
        ['Pau', 'Section Paloise', 11],
        ['Bayonne', 'Aviron Bayonnais', 12],
        ['Perpignan', 'USA Perpignan', 13],
        ['Vannes', 'RC Vannes', 14],
    ];

    $teamSql = "INSERT INTO `team` (`team_short`, `team_long`, `team_idx`, `season`, `previous_season`)
                VALUES (?, ?, ?, ?, ?)";
    $teamStmt = $pdo->prepare($teamSql);

    foreach ($teams as $team) {
        $teamStmt->execute([
            $team[0],      // team_short
            $team[1],      // team_long
            $team[2],      // team_idx
            $seasonId,     // season
            0              // previous_season
        ]);
    }

    echo "✓ Created 14 teams\n";

    // Create matches for the first few days
    echo "\nCreating match schedule...\n";

    $matchSql = "INSERT INTO `match` (`id`, `season`, `day`, `team1`, `team2`, `date`, `time`)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
    $matchStmt = $pdo->prepare($matchSql);

    $matchId = 1;

    // Create matches for first 5 days (each day has 7 matches - all 14 teams play)
    for ($day = 1; $day <= 5; $day++) {
        $matchDate = clone $startDate;
        $matchDate->modify('+' . ($day - 1) . ' weeks');

        // Create 7 matches per day (14 teams = 7 matches)
        // Simple pairing: 1v2, 3v4, 5v6, etc.
        for ($match = 0; $match < 7; $match++) {
            $team1 = ($match * 2) + 1;
            $team2 = ($match * 2) + 2;

            $matchStmt->execute([
                $matchId++,
                $seasonId,
                $day,
                $team1,
                $team2,
                $matchDate->format('Y-m-d'),
                '15:00:00'  // 3 PM match time
            ]);
        }
    }

    echo "✓ Created " . ($matchId - 1) . " matches (5 days)\n";
    echo "\n✓ Season setup complete!\n";

} catch (PDOException $e) {
    echo "\n✗ Failed to create season: " . $e->getMessage() . "\n";
    exit(1);
}
