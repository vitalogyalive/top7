<?php
/**
 * Insert Test Users Script
 *
 * Creates 7 test users with Argon2ID password hashing
 *
 * Usage:
 *   php insert_test_users.php
 *
 * @package Top7\Migrations
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "Creating 7 test users...\n\n";

init_sql();
global $pdo;

// Initialize PasswordService
$passwordService = new \Top7\Auth\PasswordService();

// Test users data
$testUsers = [
    [
        'pseudo' => 'testuser1',
        'email' => 'test1@topseven.fr',
        'password' => 'password123',
        'name' => 'Team Alpha',
    ],
    [
        'pseudo' => 'testuser2',
        'email' => 'test2@topseven.fr',
        'password' => 'password123',
        'name' => 'Team Beta',
    ],
    [
        'pseudo' => 'testuser3',
        'email' => 'test3@topseven.fr',
        'password' => 'password123',
        'name' => 'Team Gamma',
    ],
    [
        'pseudo' => 'testuser4',
        'email' => 'test4@topseven.fr',
        'password' => 'password123',
        'name' => 'Team Delta',
    ],
    [
        'pseudo' => 'testuser5',
        'email' => 'test5@topseven.fr',
        'password' => 'password123',
        'name' => 'Team Epsilon',
    ],
    [
        'pseudo' => 'testuser6',
        'email' => 'test6@topseven.fr',
        'password' => 'password123',
        'name' => 'Team Zeta',
    ],
    [
        'pseudo' => 'testuser7',
        'email' => 'test7@topseven.fr',
        'password' => 'password123',
        'name' => 'Team Eta',
    ],
];

try {
    // Get current season (default to 1 if no seasons exist)
    $seasonQuery = $pdo->query("SELECT MAX(season) as max_season FROM player");
    $seasonResult = $seasonQuery->fetch();
    $currentSeason = $seasonResult['max_season'] ?? 1;

    // Get next player_idx
    $idxQuery = $pdo->query("SELECT MAX(player_idx) as max_idx FROM player");
    $idxResult = $idxQuery->fetch();
    $nextIdx = ($idxResult['max_idx'] ?? 0) + 1;

    $pdo->beginTransaction();

    foreach ($testUsers as $index => $user) {
        $playerIdx = $nextIdx + $index;
        $hashedPassword = $passwordService->hash($user['password']);

        $sql = "INSERT INTO `player` (
            `player_idx`,
            `season`,
            `status`,
            `name`,
            `pseudo`,
            `captain`,
            `rank`,
            `rankFinal`,
            `point`,
            `J`,
            `G`,
            `N`,
            `P`,
            `team`,
            `email`,
            `password`,
            `password_new`,
            `date_reg`,
            `pm`,
            `pe`,
            `ve`,
            `evo`,
            `fun`,
            `bd`,
            `bo`,
            `pc`,
            `eq`,
            `d14`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $playerIdx,                  // player_idx
            $currentSeason,              // season
            c_player_enable,             // status (enabled)
            $user['name'],               // name (team name)
            $user['pseudo'],             // pseudo
            0,                           // captain
            0,                           // rank
            0,                           // rankFinal
            0,                           // point
            0,                           // J (played)
            0,                           // G (won)
            0,                           // N (draw)
            0,                           // P (lost)
            $playerIdx,                  // team (self-reference for now)
            $user['email'],              // email
            '',                          // password (legacy MD5, empty)
            $hashedPassword,             // password_new (Argon2ID)
            0,                           // pm (points marked)
            0,                           // pe (points against)
            0,                           // ve (away wins)
            0,                           // evo (evolution)
            0,                           // fun
            0,                           // bd
            0,                           // bo
            0,                           // pc (hairstylist points)
            0,                           // eq (different teams)
            null,                        // d14
        ];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "✓ Created user: {$user['pseudo']} ({$user['email']}) - Password: {$user['password']}\n";
    }

    $pdo->commit();

    echo "\n✓ Successfully created 7 test users!\n";
    echo "\nLogin credentials:\n";
    echo "==================\n";
    foreach ($testUsers as $user) {
        echo "Email: {$user['email']} | Password: {$user['password']}\n";
    }
    echo "\nLogin URL: http://localhost/login.php\n";
    echo "or: http://localhost/ (redirects to login if not authenticated)\n";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Failed to create users: " . $e->getMessage() . "\n";
    exit(1);
}
