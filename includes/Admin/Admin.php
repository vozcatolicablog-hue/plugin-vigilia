<?php
/**
 * Admin Class
 * 
 * Handles admin panel, menus, and screens
 * 
 * @package HorasOracion
 * @subpackage Admin
 * @since 1.0.0
 */

namespace HorasOracion\Admin;

use HorasOracion\Database\Database;
use HorasOracion\Security\Security;
use HorasOracion\Export\Export;

class Admin {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Security instance
     */
    private $security;
    
    /**
     * Constructor
     */
    public function __construct(Database $database, Security $security) {
        $this->database = $database;
        $this->security = $security;
    }
    
    /**
     * Initialize admin
     */
    public function init() {
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Settings registration
        add_action('admin_init', [$this, 'register_settings']);
        
        // Handle admin actions (e.g. settings save, delete, export)
        add_action('admin_init', [$this, 'handle_admin_actions']);
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_horas_oracion_delete_registration', [$this, 'delete_registration_ajax']);
        add_action('wp_ajax_horas_oracion_export_month_csv', [$this, 'export_month_csv_ajax']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            '40 Horas de Oración',
            '40 Horas de Oración',
            'manage_options',
            'horas-oracion',
            [$this, 'render_inscriptions_page'],
            'dashicons-clock',
            30
        );
        
        // Submenu: Inscripciones
        add_submenu_page(
            'horas-oracion',
            'Inscripciones',
            'Inscripciones',
            'manage_options',
            'horas-oracion',
            [$this, 'render_inscriptions_page']
        );
        
        // Submenu: Configuración
        add_submenu_page(
            'horas-oracion',
            'Configuración',
            'Configuración',
            'manage_options',
            'horas-oracion-settings',
            [$this, 'render_settings_page']
        );
        
        // Submenu: Exportaciones
        add_submenu_page(
            'horas-oracion',
            'Exportaciones',
            'Exportaciones',
            'manage_options',
            'horas-oracion-exports',
            [$this, 'render_exports_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // This method is called on admin_init hook
        // Settings registration can be done here if needed with register_setting()
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only enqueue on our admin pages
        if (strpos($hook, 'horas-oracion') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'horas-oracion-admin',
            HORAS_ORACION_PLUGIN_URL . 'assets/css/admin.css',
            [],
            HORAS_ORACION_VERSION
        );
        
        // JS
        wp_enqueue_script(
            'horas-oracion-admin',
            HORAS_ORACION_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            HORAS_ORACION_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('horas-oracion-admin', 'horasOracionAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => $this->security->create_nonce('horas_oracion_admin_nonce'),
            'confirmDelete' => __('¿Está seguro de que desea eliminar esta inscripción?', '40-horas-oracion'),
        ]);
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        // Delete registration
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['registration_id'])) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_registration_' . $_GET['registration_id'])) {
                wp_die('Error de seguridad');
            }
            
            $id = absint($_GET['registration_id']);
            $this->database->delete_registration($id);
            
            wp_redirect(admin_url('admin.php?page=horas-oracion&deleted=1'));
            exit;
        }
        
        // Export CSV
        if (isset($_GET['action']) && $_GET['action'] === 'export') {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'export_csv')) {
                wp_die('Error de seguridad');
            }
            
            $this->export_csv();
        }
        
        // Save settings
        if (isset($_POST['horas_oracion_save_settings'])) {
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'horas_oracion_settings')) {
                wp_die('Error de seguridad');
            }
            
            $this->save_settings();
        }
        
        // Export month CSV (Manual Form POST)
        if (isset($_POST['horas_oracion_export_month'])) {
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'export_csv')) {
                wp_die('Error de seguridad');
            }
            
            $export_month = isset($_POST['export_month']) ? sanitize_text_field($_POST['export_month']) : '';
            if (!empty($export_month)) {
                $parts = explode('-', $export_month);
                if (count($parts) === 2) {
                    $year = $parts[0];
                    $month = $parts[1];
                    
                    $export = new Export($this->database);
                    $result = $export->export_month_by_date("$month/$year");
                    
                    if ($result !== false) {
                        wp_redirect(admin_url('admin.php?page=horas-oracion-exports&exported=1'));
                        exit;
                    }
                }
            }
            
            wp_redirect(admin_url('admin.php?page=horas-oracion-exports&error=1'));
            exit;
        }
    }
    
    /**
     * Render inscriptions page
     */
    public function render_inscriptions_page() {
        $page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : '';
        
        $registrations = $this->database->get_all_registrations($per_page, $offset, $search, $month);
        $total = $this->database->get_total_count($search, $month);
        $total_pages = ceil($total / $per_page);
        
        // Get statistics
        $current_month_count = $this->database->get_current_month_count();
        $historical_count = $this->database->get_historical_count();
        
        include HORAS_ORACION_PLUGIN_DIR . 'templates/admin-inscriptions.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $settings = [
            'intro_text' => get_option('horas_oracion_intro_text', ''),
            'primary_color' => get_option('horas_oracion_primary_color', '#3b82f6'),
            'allow_multiple_per_hour' => get_option('horas_oracion_allow_multiple_per_hour', '1'),
            'max_per_hour' => get_option('horas_oracion_max_per_hour', '0'),
            'historical_count' => get_option('horas_oracion_historical_count', '13965'),
            'recaptcha_site_key' => get_option('horas_oracion_recaptcha_site_key', ''),
            'recaptcha_secret_key' => get_option('horas_oracion_recaptcha_secret_key', ''),
            'turnstile_site_key' => get_option('horas_oracion_turnstile_site_key', ''),
            'turnstile_secret_key' => get_option('horas_oracion_turnstile_secret_key', ''),
            'start_day' => get_option('horas_oracion_start_day', '14'),
            'start_time' => get_option('horas_oracion_start_time', '08:00'),
            'duration_hours' => get_option('horas_oracion_duration_hours', '40'),
            'reset_days' => get_option('horas_oracion_reset_days', '2'),
        ];
        
        extract($settings);
        
        include HORAS_ORACION_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Render exports page
     */
    public function render_exports_page() {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/40-horas-oracion';
        $export_url = $upload_dir['baseurl'] . '/40-horas-oracion';
        
        $files = [];
        if (is_dir($export_dir)) {
            $files = glob($export_dir . '/*.csv');
            rsort($files); // Newest first
        }
        
        include HORAS_ORACION_PLUGIN_DIR . 'templates/admin-exports.php';
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Intro text
        $intro_text = isset($_POST['horas_oracion_intro_text']) ? sanitize_textarea_field($_POST['horas_oracion_intro_text']) : '';
        update_option('horas_oracion_intro_text', $intro_text);
        
        // Primary color
        $primary_color = isset($_POST['horas_oracion_primary_color']) ? sanitize_hex_color($_POST['horas_oracion_primary_color']) : '#3b82f6';
        update_option('horas_oracion_primary_color', $primary_color);
        
        // Allow multiple per hour
        $allow_multiple = isset($_POST['horas_oracion_allow_multiple_per_hour']) ? '1' : '0';
        update_option('horas_oracion_allow_multiple_per_hour', $allow_multiple);
        
        // Max per hour
        $max_per_hour = isset($_POST['horas_oracion_max_per_hour']) ? absint($_POST['horas_oracion_max_per_hour']) : 0;
        update_option('horas_oracion_max_per_hour', $max_per_hour);
        
        // Historical count
        $historical_count = isset($_POST['horas_oracion_historical_count']) ? absint($_POST['horas_oracion_historical_count']) : 13965;
        update_option('horas_oracion_historical_count', $historical_count);
        
        // reCAPTCHA
        $recaptcha_site_key = isset($_POST['horas_oracion_recaptcha_site_key']) ? sanitize_text_field($_POST['horas_oracion_recaptcha_site_key']) : '';
        update_option('horas_oracion_recaptcha_site_key', $recaptcha_site_key);
        
        $recaptcha_secret_key = isset($_POST['horas_oracion_recaptcha_secret_key']) ? sanitize_text_field($_POST['horas_oracion_recaptcha_secret_key']) : '';
        update_option('horas_oracion_recaptcha_secret_key', $recaptcha_secret_key);
        
        // Turnstile
        $turnstile_site_key = isset($_POST['horas_oracion_turnstile_site_key']) ? sanitize_text_field($_POST['horas_oracion_turnstile_site_key']) : '';
        update_option('horas_oracion_turnstile_site_key', $turnstile_site_key);
        
        $turnstile_secret_key = isset($_POST['horas_oracion_turnstile_secret_key']) ? sanitize_text_field($_POST['horas_oracion_turnstile_secret_key']) : '';
        update_option('horas_oracion_turnstile_secret_key', $turnstile_secret_key);
        
        // Dynamic schedule
        $start_day = isset($_POST['start_day']) ? absint($_POST['start_day']) : 14;
        update_option('horas_oracion_start_day', $start_day);
        
        $start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '08:00';
        update_option('horas_oracion_start_time', $start_time);
        
        $duration_hours = isset($_POST['duration_hours']) ? absint($_POST['duration_hours']) : 40;
        update_option('horas_oracion_duration_hours', $duration_hours);
        
        $reset_days = isset($_POST['reset_days']) ? absint($_POST['reset_days']) : 2;
        update_option('horas_oracion_reset_days', $reset_days);
        
        // Redirect with success message
        wp_redirect(admin_url('admin.php?page=horas-oracion-settings&saved=1'));
        exit;
    }
    
    /**
     * Export CSV
     */
    private function export_csv() {
        $month = isset($_GET['month']) ? absint($_GET['month']) : null;
        $year = isset($_GET['year']) ? absint($_GET['year']) : null;
        
        $registrations = $this->database->get_all_for_export($month, $year);
        
        // Generate filename
        if ($month && $year) {
            $filename = sprintf('40-horas-oracion-%04d-%02d.csv', $year, $month);
        } else {
            $filename = '40-horas-oracion-export.csv';
        }
        
        // Set headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Write header
        fputcsv($output, ['Nombre', 'Apellido', 'Ciudad', 'País', 'Número de Hora', 'Día', 'Hora', 'Fecha de Inscripción']);
        
        // Write data
        foreach ($registrations as $row) {
            fputcsv($output, [
                $row->nombre,
                $row->apellido,
                $row->ciudad,
                $row->pais,
                $row->numero_hora,
                $row->dia,
                $row->hora,
                $row->created_at
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Delete registration via AJAX
     */
    public function delete_registration_ajax() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !$this->security->verify_nonce($_POST['nonce'], 'horas_oracion_admin_nonce')) {
            wp_send_json_error([
                'message' => 'Error de seguridad. Por favor recargue la página.'
            ]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'No tiene permisos suficientes para realizar esta acción.'
            ]);
        }
        
        // Get registration ID
        $id = isset($_POST['registration_id']) ? absint($_POST['registration_id']) : 0;
        if (!$id) {
            wp_send_json_error([
                'message' => 'ID de registro inválido.'
            ]);
        }
        
        // Delete registration
        $result = $this->database->delete_registration($id);
        if ($result === false) {
            wp_send_json_error([
                'message' => 'No se pudo eliminar el registro.'
            ]);
        }
        
        wp_send_json_success([
            'message' => 'Registro eliminado correctamente.'
        ]);
    }
    
    /**
     * Export month CSV via AJAX
     */
    public function export_month_csv_ajax() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !$this->security->verify_nonce($_POST['nonce'], 'horas_oracion_admin_nonce')) {
            wp_send_json_error([
                'message' => 'Error de seguridad. Por favor recargue la página.'
            ]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'No tiene permisos suficientes para realizar esta acción.'
            ]);
        }
        
        $month_str = isset($_POST['month']) ? sanitize_text_field($_POST['month']) : '';
        if (empty($month_str)) {
            wp_send_json_error([
                'message' => 'Mes no especificado.'
            ]);
        }
        
        // Month expected in MM/YYYY format
        $export = new Export($this->database);
        $result = $export->export_month_by_date($month_str);
        
        if ($result === false) {
            wp_send_json_error([
                'message' => 'No se pudo exportar el archivo CSV.'
            ]);
        }
        
        wp_send_json_success([
            'message' => 'Archivo exportado correctamente.',
            'file' => $result
        ]);
    }
}
