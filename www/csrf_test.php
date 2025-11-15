<?php
/**
 * CSRF Token Test - Minimal test to isolate the issue
 */

require_once __DIR__ . '/common.inc';

session_start();

echo "<h2>CSRF Token Validation Test</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "\n";

// Show current session data
echo "Current Session Data:\n";
print_r($_SESSION);
echo "\n";

// If this is a POST request, validate the token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "\n=== POST REQUEST - VALIDATING ===\n";
    echo "POST data received:\n";
    print_r($_POST);
    echo "\n";

    if (isset($_POST['csrf_token'])) {
        echo "CSRF Token from POST: " . $_POST['csrf_token'] . "\n";
        echo "Session tokens BEFORE validation:\n";
        if (isset($_SESSION['csrf_tokens'])) {
            foreach ($_SESSION['csrf_tokens'] as $token => $time) {
                echo "  - " . $token . " (age: " . (time() - $time) . "s)\n";
            }
        } else {
            echo "  NO TOKENS IN SESSION!\n";
        }
        echo "\n";

        $isValid = \Top7\Security\CsrfToken::validate($_POST['csrf_token']);
        echo "Validation Result: " . ($isValid ? "✓ VALID" : "✗ INVALID") . "\n";

        echo "\nSession tokens AFTER validation:\n";
        if (isset($_SESSION['csrf_tokens'])) {
            foreach ($_SESSION['csrf_tokens'] as $token => $time) {
                echo "  - " . $token . " (age: " . (time() - $time) . "s)\n";
            }
        } else {
            echo "  (empty - token was consumed)\n";
        }
    } else {
        echo "✗ NO CSRF TOKEN IN POST DATA\n";
    }
}

echo "</pre>";

// Generate a new form
echo "<h3>Test Form</h3>";
echo "<form method='POST' action='csrf_test.php'>";
echo \Top7\Security\CsrfToken::field();
echo "<input type='text' name='test_input' placeholder='Type something' value='test'><br><br>";
echo "<input type='submit' value='Submit Test'>";
echo "</form>";

echo "<hr>";
echo "<p><a href='csrf_test.php'>Refresh (GET request)</a> | <a href='index.php'>Back to Login</a></p>";
