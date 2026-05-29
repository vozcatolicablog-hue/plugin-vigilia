<?php
/**
 * Cron Class
 * 
 * Handles monthly reset and archiving via WP-Cron
 * 
 * @package HorasOracion
 * @subpackage Cron
 * @since 1.0.0
 */

namespace HorasOracion\Cron;

use HorasOracion\Database\Database;
use HorasOracion\Export\Export;

class Cron {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Export instance
     */
    private $export;
    
    /**
     * Cron hook name
     */
    const CRON_HOOK = 'horas_oracion_monthly_reset';
    
    /**
     * Constructor
     */
    public function __construct(Database $database, Export $export) {
        $this->database = $database;
        $this->export = $export;
        
        // Schedule the monthly cron
        add_action('init', [$this, 'schedule_monthly_reset']);
        
        // Add the cron action
        add_action(self::CRON_HOOK, [$this, 'monthly_reset']);
    }
    
    /**
     * Initialize cron hooks (if any additional needed)
     */
    public function init() {
        // Hooks are already registered in constructor, but this method
        // is required by Plugin::init_components()
    }
    
    /**
     * Schedule monthly reset
     */
    public function schedule_monthly_reset() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(strtotime('tomorrow 00:00'), 'daily', self::CRON_HOOK);
        }
    }
    
    /**
     * Clear scheduled reset on deactivation
     */
    public static function clear_scheduled_reset() {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }
    
    /**
     * Monthly reset - export and clear data
     */
    public function monthly_reset() {
        $start_day = (int) get_option('horas_oracion_start_day', 14);
        $start_time = get_option('horas_oracion_start_time', '08:00');
        $duration_hours = (int) get_option('horas_oracion_duration_hours', 40);
        $reset_days = (int) get_option('horas_oracion_reset_days', 2);
        
        // Calculate the reset day
        $current_year = date('Y');
        $current_month = date('m');
        $vigil_start = strtotime("$current_year-$current_month-" . sprintf('%02d', $start_day) . " $start_time");
        $vigil_end = $vigil_start + ($duration_hours * 3600);
        $reset_timestamp = $vigil_end + ($reset_days * 86400);
        
        $reset_day = date('d', $reset_timestamp);
        
        // Check if today is the reset day
        if (date('d') !== $reset_day) {
            return;
        }
        
        global $wpdb;
        $table_name = $this->database->get_table_name();
        
        // Find the earliest record to determine the cycle month
        $oldest = $wpdb->get_var("SELECT MIN(created_at) FROM $table_name");
        if (!$oldest) {
            return; // No data to reset
        }
        
        $cycle_month = date('m/Y', strtotime($oldest));
        
        do_action('horas_oracion_before_monthly_reset', $cycle_month);
        
        // Export data for the cycle
        $export_result = $this->export->export_month_by_date($cycle_month);
        
        if (!$export_result) {
            error_log('Error exporting data for cycle: ' . $cycle_month);
        }
        
        // Vaciar la tabla activa
        $wpdb->query("TRUNCATE TABLE $table_name");
        
        do_action('horas_oracion_after_monthly_reset', $cycle_month);
        
        // Log the action
        error_log('Monthly reset completed for cycle: ' . $cycle_month);
    }
    
    /**
     * Manual trigger of monthly reset (for testing)
     */
    public function manual_monthly_reset() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        $this->monthly_reset();
        return true;
    }
}
