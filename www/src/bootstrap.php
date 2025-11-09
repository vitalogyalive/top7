<?php
/**
 * Bootstrap - Application Initialization and Autoloader
 *
 * This file provides:
 * - PSR-4 autoloader for Top7 namespace
 * - Configuration loading
 * - Legacy constants support
 *
 * @package Top7
 * @since Phase 1, Task 1.2.1
 */

// PSR-4 Autoloader for Top7 namespace
spl_autoload_register(function ($class) {
    $prefix = 'Top7\\';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration
$ovh_path    = dirname(__DIR__) . "/conf/conf.php";
$docker_path = "/var/www/html/conf/conf.php";

if (file_exists($ovh_path)) {
    require $ovh_path;
    $log_path = dirname(__DIR__) . "/../tmp";
} elseif (file_exists($docker_path)) {
    require $docker_path;
    $log_path = "/tmp";
} else {
    die("Configuration file not found");
}

// Set global log path
if (!defined('LOG_PATH')) {
    define('LOG_PATH', $log_path);
}
