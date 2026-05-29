<?php
/**
 * Main Plugin Class
 * 
 * @package HorasOracion
 * @since 1.0.0
 */

namespace HorasOracion;

use HorasOracion\Database\Database;
use HorasOracion\Security\Security;
use HorasOracion\Frontend\Frontend;
use HorasOracion\Admin\Admin;
use HorasOracion\AJAX\AJAX;
use HorasOracion\Export\Export;
use HorasOracion\Cron\Cron;

class Plugin {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Security instance
     */
    private $security;
    
    /**
     * Frontend instance
     */
    private $frontend;
    
    /**
     * Admin instance
     */
    private $admin;
    
    /**
     * AJAX instance
     */
    private $ajax;
    
    /**
     * Export instance
     */
    private $export;
    
    /**
     * Cron instance
     */
    private $cron;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load dependencies
     */
    private function load_dependencies() {
        $this->database = new Database();
        $this->security = new Security();
        $this->frontend = new Frontend($this->database, $this->security);
        $this->admin = new Admin($this->database, $this->security);
        $this->ajax = new AJAX($this->database, $this->security);
        $this->export = new Export($this->database);
        $this->cron = new Cron($this->database, $this->export);
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load text domain
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        
        // Initialize components
        add_action('init', [$this, 'init_components']);
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            '40-horas-oracion',
            false,
            dirname(HORAS_ORACION_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Initialize components
     */
    public function init_components() {
        $this->frontend->init();
        $this->admin->init();
        $this->ajax->init();
        $this->export->init();
        $this->cron->init();
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Create database table
        Database::create_table();
        
        // Set default options
        self::set_default_options();
        
        // Schedule cron job
        wp_schedule_event(
            strtotime('next month 18th 00:00:00'),
            'monthly',
            'horas_oracion_monthly_reset'
        );
        
        // Create upload directory
        self::create_upload_directory();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clear scheduled cron
        wp_clear_scheduled_hook('horas_oracion_monthly_reset');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default options
     */
    private static function set_default_options() {
        $defaults = [
            'horas_oracion_intro_text' => 'Nos unimos durante 40 horas continuas los días 14, 15 y 16 de cada mes, ofreciendo una hora de oración por la santidad y perseverancia de los religiosos y el aumento de las vocaciones.',
            'horas_oracion_primary_color' => '#3b82f6',
            'horas_oracion_allow_multiple_per_hour' => '1',
            'horas_oracion_max_per_hour' => '0',
            'horas_oracion_historical_count' => '13965',
            'horas_oracion_recaptcha_site_key' => '',
            'horas_oracion_recaptcha_secret_key' => '',
            'horas_oracion_turnstile_site_key' => '',
            'horas_oracion_turnstile_secret_key' => '',
        ];
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * Create upload directory for CSV exports
     */
    private static function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/40-horas-oracion';
        
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
            
            // Create index.php to prevent directory browsing
            file_put_contents($export_dir . '/index.php', '<?php // Silence is golden.');
        }
    }
    
    /**
     * Get database instance
     */
    public function get_database() {
        return $this->database;
    }
    
    /**
     * Get security instance
     */
    public function get_security() {
        return $this->security;
    }
}
