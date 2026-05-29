<?php
/**
 * Uninstall plugin
 * 
 * @package HorasOracion
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Get table name
$table_name = $wpdb->prefix . '40_horas_oracion';

// Drop custom table
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete plugin options
delete_option('horas_oracion_recaptcha_site_key');
delete_option('horas_oracion_recaptcha_secret_key');
delete_option('horas_oracion_turnstile_site_key');
delete_option('horas_oracion_turnstile_secret_key');
delete_option('horas_oracion_intro_text');
delete_option('horas_oracion_primary_color');
delete_option('horas_oracion_historical_count');
delete_option('horas_oracion_allow_multiple_per_hour');
delete_option('horas_oracion_max_per_hour');

// Delete uploads directory
$upload_dir = wp_upload_dir();
$export_dir = $upload_dir['basedir'] . '/40-horas-oracion/';

if (is_dir($export_dir)) {
    $files = glob($export_dir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    rmdir($export_dir);
}

// Clear scheduled cron
wp_clear_scheduled_hook('horas_oracion_monthly_reset');
