<?php

/**
 * Top7 Bootstrap File
 *
 * Provides PSR-4 autoloading and basic initialization
 * This file is the entry point for the new modular architecture
 *
 * @package Top7
 */

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Top7\\';
    $base_dir = __DIR__ . '/';

    // Check if the class uses the Top7 namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    // Add .php extension
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration if not already loaded
$ovh_path    = dirname(__DIR__) . "/conf/conf.php";
$docker_path = "/var/www/html/conf/conf.php";

if (file_exists($ovh_path)) {
    require_once $ovh_path;
} elseif (file_exists($docker_path)) {
    require_once $docker_path;
}

// Start session management
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    // Use strict session ID handling
    ini_set('session.use_strict_mode', '1');

    // Set session lifetime (30 minutes)
    ini_set('session.gc_maxlifetime', '1800');

    session_start();
}

// Set default timezone if not already set
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Europe/Paris');
}

// Error reporting for development (will be controlled by environment)
if (defined('APP_ENV') && APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}
