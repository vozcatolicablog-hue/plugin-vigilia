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
        
        // Calculate the reset date (full date, not just day)
        $current_year = date('Y');
        $current_month = date('m');
        $vigil_start = strtotime("$current_year-$current_month-" . sprintf('%02d', $start_day) . " $start_time");
        $vigil_end = $vigil_start + ($duration_hours * 3600);
        $reset_timestamp = $vigil_end + ($reset_days * 86400);
        
        // Compare full date (Y-m-d) not just day number to avoid cross-month issues
        $reset_date = date('Y-m-d', $reset_timestamp);
        $today = date('Y-m-d');
        
        if ($today !== $reset_date) {
            return;
        }
        
        global $wpdb;
        $table_name = $this->database->get_table_name();
        
        // Check if there is any data to reset
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if (!$count || $count == 0) {
            return; // No data to reset
        }
        
        // Find the earliest record to determine the cycle month
        $oldest = $wpdb->get_var("SELECT MIN(created_at) FROM $table_name");
        if (!$oldest) {
            return;
        }
        
        $cycle_month = date('m/Y', strtotime($oldest));
        
        do_action('horas_oracion_before_monthly_reset', $cycle_month);
        
        // Export ALL data (not filtered by month) to ensure nothing is lost
        $export_result = $this->export->export_all_current_data($cycle_month);
        
        // CRITICAL: Only truncate if export was successful
        if (!$export_result) {
            error_log('[40 Horas Oración] CRITICAL: Export FAILED for cycle ' . $cycle_month . '. Data was NOT deleted to prevent data loss.');
            
            // Notify admin via email
            $admin_email = get_option('admin_email');
            wp_mail(
                $admin_email,
                '[40 Horas Oración] Error en exportación automática',
                'La exportación automática del ciclo ' . $cycle_month . ' ha fallado. Los datos NO fueron eliminados para prevenir pérdida de datos. Por favor revise los archivos de exportación y realice una exportación manual desde el panel de administración.'
            );
            
            return; // DO NOT truncate
        }
        
        // Verify the exported file exists and has data
        if (isset($export_result['path']) && file_exists($export_result['path']) && filesize($export_result['path']) > 50) {
            // Safe to truncate
            $wpdb->query("TRUNCATE TABLE $table_name");
            
            do_action('horas_oracion_after_monthly_reset', $cycle_month);
            
            error_log('[40 Horas Oración] Monthly reset completed successfully for cycle: ' . $cycle_month . '. Export: ' . $export_result['filename']);
        } else {
            error_log('[40 Horas Oración] CRITICAL: Export file verification failed for cycle ' . $cycle_month . '. Data was NOT deleted.');
        }
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
