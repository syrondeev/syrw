<?php
/**
 * Autoloader for SYRW classes
 */

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    // Project namespace prefix
    $prefix = 'SYRW\\';

    // Base directory
    $base_dir = plugin_dir_path(__FILE__);

    // Check if class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get relative class name
    $relative_class = substr($class, $len);

    // Convert namespace to path
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
