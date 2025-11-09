<?php
/**
 * Session Debug Script
 *
 * Helps debug session and CSRF token issues
 */

session_start();

echo "<h2>Session Debug Information</h2>";
echo "<pre>";

echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . " (1=disabled, 2=active)\n";
echo "Session Name: " . session_name() . "\n";
echo "\n";

echo "Session Data:\n";
print_r($_SESSION);

echo "\n\nPOST Data:\n";
print_r($_POST);

echo "\n\nCookie Data:\n";
print_r($_COOKIE);

echo "\n\nSession Save Path: " . session_save_path() . "\n";
echo "Session Cookie Params:\n";
print_r(session_get_cookie_params());

echo "</pre>";

// Test CSRF token generation
require_once __DIR__ . '/common.inc';

echo "<h3>CSRF Token Test</h3>";
echo "<pre>";

$token = \Top7\Security\CsrfToken::generate();
echo "Generated Token: " . $token . "\n";
echo "Token stored in session: " . (isset($_SESSION['csrf_tokens'][$token]) ? 'YES' : 'NO') . "\n";

echo "\nAll CSRF Tokens in session:\n";
if (isset($_SESSION['csrf_tokens'])) {
    foreach ($_SESSION['csrf_tokens'] as $t => $time) {
        echo "  Token: " . substr($t, 0, 16) . "... (created: " . date('Y-m-d H:i:s', $time) . ")\n";
    }
} else {
    echo "  No tokens in session\n";
}

echo "</pre>";

// Test form
echo "<h3>Test Form</h3>";
echo "<form method='POST' action='session_debug.php'>";
echo \Top7\Security\CsrfToken::field();
echo "<input type='text' name='test_field' placeholder='Type something'><br>";
echo "<input type='submit' value='Test Submit'>";
echo "</form>";

// Validate if this is a POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Validation Result</h3>";
    echo "<pre>";
    if (isset($_POST['csrf_token'])) {
        echo "CSRF Token received: " . substr($_POST['csrf_token'], 0, 16) . "...\n";
        $isValid = \Top7\Security\CsrfToken::validate($_POST['csrf_token']);
        echo "Validation result: " . ($isValid ? 'VALID ✓' : 'INVALID ✗') . "\n";
    } else {
        echo "No CSRF token in POST data ✗\n";
    }
    echo "</pre>";
}
