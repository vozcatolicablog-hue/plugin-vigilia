<?php
/**
 * Plugin Name: 40 Horas de Oración
 * Plugin URI: https://example.com
 * Description: Sistema de inscripción para 40 horas de oración continua por la santidad y perseverancia de los religiosos y el aumento de las vocaciones.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: 40-horas-oracion
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * License: GPL v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HORAS_ORACION_VERSION', '1.0.0');
define('HORAS_ORACION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HORAS_ORACION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HORAS_ORACION_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'HorasOracion\\';
    $base_dir = HORAS_ORACION_PLUGIN_DIR . 'includes/';
    
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

// Main plugin class
use HorasOracion\Plugin;

/**
 * Initialize the plugin
 */
function horas_oracion_init() {
    return Plugin::get_instance();
}

// Start the plugin
horas_oracion_init();

// Activation hook
register_activation_hook(__FILE__, ['HorasOracion\\Plugin', 'activate']);

// Deactivation hook
register_deactivation_hook(__FILE__, ['HorasOracion\\Plugin', 'deactivate']);
