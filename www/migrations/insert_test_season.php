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

    $seasonData = [
        'Id' => 1,
        'title' => "Season $currentYear-$nextYear (Test)",
        'start' => "$currentYear-09-01",           // Season starts September 1st
        'start_register' => "$currentYear-08-01",  // Registration opens August 1st
        'stop_register' => "$currentYear-12-31",   // Registration closes December 31st
        'close_forum' => "$nextYear-06-30",        // Forum closes June 30th next year
    ];

    $sql = "INSERT INTO `season` (`Id`, `title`, `start`, `start_register`, `stop_register`, `close_forum`)
            VALUES (:id, :title, :start, :start_register, :stop_register, :close_forum)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($seasonData);

    echo "✓ Created test season:\n";
    echo "  ID: {$seasonData['Id']}\n";
    echo "  Title: {$seasonData['title']}\n";
    echo "  Season Start: {$seasonData['start']}\n";
    echo "  Registration Period: {$seasonData['start_register']} to {$seasonData['stop_register']}\n";
    echo "  Forum Closes: {$seasonData['close_forum']}\n";

    // Create basic calendar entries (26 match days for a typical rugby season)
    echo "\nCreating calendar entries...\n";

    $calendarSql = "INSERT INTO `calendar` (`season`, `day`, `date`) VALUES (?, ?, ?)";
    $calendarStmt = $pdo->prepare($calendarSql);

    $startDate = new DateTime($seasonData['start']);

    for ($day = 1; $day <= 26; $day++) {
        // Rugby matches are typically weekly, on Saturdays
        $matchDate = clone $startDate;
        $matchDate->modify('+' . ($day - 1) . ' weeks');

        $calendarStmt->execute([
            $seasonData['Id'],
            $day,
            $matchDate->format('Y-m-d')
        ]);
    }

    echo "✓ Created 26 calendar entries (weekly matches)\n";
    echo "\n✓ Season setup complete!\n";

} catch (PDOException $e) {
    echo "\n✗ Failed to create season: " . $e->getMessage() . "\n";
    exit(1);
}
