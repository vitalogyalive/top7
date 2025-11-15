#!/usr/bin/env php
<?php
/**
 * Verify password hash
 */

$hash = '$argon2id$v=19$m=65536,t=4,p=1$NmxiUjRYTnFwLm5IdmtDMw$ftWnyf1xAPzecZO190d0g9W7KQYUhoCw04Vfu7PWZZU';

$passwords_to_test = [
    'test123',
    'password123',
    'test',
    'test2',
    ''
];

echo "Testing Argon2ID hash against common passwords:\n";
echo "================================================\n\n";

foreach ($passwords_to_test as $password) {
    $result = password_verify($password, $hash);
    $status = $result ? '✓ MATCH' : '✗ no match';
    echo sprintf("%-20s : %s\n", "'$password'", $status);
}

echo "\n";
