<?php
/**
 * Database Migration Runner
 *
 * Run this script ONCE to apply the password_new column migration
 *
 * Usage:
 *   php run_migration.php 001
 *
 * @package Top7\Migrations
 * @since Phase 1, Task 1.1.1
 */

require_once dirname(__DIR__) . '/common.inc';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

$migration_number = $argv[1] ?? null;

if (!$migration_number) {
    echo "Usage: php run_migration.php <migration_number>\n";
    echo "Example: php run_migration.php 001\n";
    exit(1);
}

$migration_file = __DIR__ . "/{$migration_number}_add_password_new_column.sql";

if (!file_exists($migration_file)) {
    echo "Error: Migration file not found: {$migration_file}\n";
    exit(1);
}

echo "Running migration: {$migration_file}\n";

$sql = file_get_contents($migration_file);

// Remove comments and split by semicolon
$statements = array_filter(
    array_map('trim',
        explode(';',
            preg_replace('/--.*$/m', '', $sql)
        )
    )
);

init_sql();
global $pdo;

try {
    // Note: DDL statements (CREATE, ALTER, DROP) cause implicit commits in MySQL
    // So transactions don't really help here, but we'll keep the structure for consistency
    $pdo->beginTransaction();

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 80) . "...\n";
            $pdo->exec($statement);
        }
    }

    // Only commit if transaction is still active
    // (DDL statements cause implicit commits, so transaction may not be active)
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

    echo "\n✓ Migration completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Verify the migration: SELECT * FROM player LIMIT 1;\n";
    echo "2. Test login with an existing account (should auto-migrate to Argon2ID)\n";
    echo "3. Create a new account (should use Argon2ID from the start)\n";

} catch (PDOException $e) {
    // Only rollback if transaction is still active
    // (DDL statements cause implicit commits, so transaction may not be active)
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    exit(1);
}
