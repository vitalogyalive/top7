#!/usr/bin/env php
<?php
/**
 * Fix test2 user password - set to correct password123
 */

require_once dirname(__FILE__) . '/common.inc';

echo "Fixing test2 user password\n";
echo "==========================\n\n";

init_sql();
global $pdo;

$passwordService = new \Top7\Auth\PasswordService();

// Generate correct hash for password123
$correctPassword = 'password123';
$newHash = $passwordService->hash($correctPassword);

echo "Generated Argon2ID hash for 'password123'\n";
echo "Hash length: " . strlen($newHash) . " characters\n\n";

// Update the database
$query = "UPDATE player SET password_new = ?, password = '' WHERE email = ?";
$stmt = $pdo->prepare($query);
$result = $stmt->execute([$newHash, 'test2@topseven.fr']);

if ($result) {
    echo "✓ Successfully updated test2 password\n";
    echo "  - password_new set to Argon2ID hash for 'password123'\n";
    echo "  - password (MD5) cleared to prevent fallback\n\n";

    // Verify the update
    $verifyQuery = "SELECT pseudo, email, password, password_new FROM player WHERE email = ?";
    $stmt = $pdo->prepare($verifyQuery);
    $stmt->execute(['test2@topseven.fr']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Verification:\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Pseudo: " . $user['pseudo'] . "\n";
    echo "  MD5 password: " . ($user['password'] === '' ? 'EMPTY (good)' : 'HAS VALUE (unexpected)') . "\n";
    echo "  Argon2ID hash: " . (strlen($user['password_new']) > 0 ? 'SET (' . strlen($user['password_new']) . ' chars)' : 'NOT SET') . "\n\n";

    // Test verification
    echo "Password verification test:\n";
    echo "  'test123':     " . ($passwordService->verify('test123', $user['password_new']) ? "✓ MATCH (WRONG!)" : "✗ no match (correct)") . "\n";
    echo "  'password123': " . ($passwordService->verify('password123', $user['password_new']) ? "✓ MATCH (correct!)" : "✗ no match (WRONG!)") . "\n";

} else {
    echo "✗ Failed to update password\n";
    exit(1);
}

echo "\n✓ Done! test2 user should now login with 'password123'\n";
