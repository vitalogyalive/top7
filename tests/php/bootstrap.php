<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the testing environment including autoloading and
 * any necessary configuration for the test suite.
 */

// Start output buffering to prevent headers already sent errors
ob_start();

// Load Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Define constants that are normally defined in conf.php
// These are safe test values for unit testing
if (!defined('c_min_pseudo')) {
    define('c_min_pseudo', 3);
}
if (!defined('c_max_pseudo')) {
    define('c_max_pseudo', 20);
}
if (!defined('c_min_team')) {
    define('c_min_team', 3);
}
if (!defined('c_max_team')) {
    define('c_max_team', 30);
}

// Mock session if not started
if (session_status() === PHP_SESSION_NONE) {
    // Use a custom session handler for testing that doesn't require headers
    ini_set('session.use_cookies', '0');
    ini_set('session.use_only_cookies', '0');
    ini_set('session.cache_limiter', '');
}

/**
 * Helper function to start a mock session for testing
 */
function startTestSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
}

/**
 * Helper function to reset session for testing
 */
function resetTestSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        @session_destroy();
    }
    $_SESSION = [];
}

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
