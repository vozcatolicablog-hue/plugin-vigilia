<?php
/**
 * Frontend Class
 * 
 * Handles frontend shortcode rendering and assets
 * 
 * @package HorasOracion
 * @subpackage Frontend
 * @since 1.0.0
 */

namespace HorasOracion\Frontend;

use HorasOracion\Database\Database;
use HorasOracion\Security\Security;

class Frontend {
    
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
    public function __construct($database, $security) {
        $this->database = $database;
        $this->security = $security;
    }
    
    /**
     * Initialize frontend
     */
    public function init() {
        // Register shortcode
        add_shortcode('horas_oracion', [$this, 'render_shortcode']);
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        // Only enqueue on pages with the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'horas_oracion')) {
            // CSS
            wp_enqueue_style(
                'horas-oracion-frontend',
                HORAS_ORACION_PLUGIN_URL . 'assets/css/style.css',
                [],
                HORAS_ORACION_VERSION
            );
            
            // JS
            wp_enqueue_script(
                'horas-oracion-frontend',
                HORAS_ORACION_PLUGIN_URL . 'assets/js/frontend.js',
                ['jquery'],
                HORAS_ORACION_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('horas-oracion-frontend', 'horasOracion', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => $this->security->create_nonce('horas_oracion_nonce'),
                'captchaType' => $this->security->get_captcha_type(),
                'recaptchaSiteKey' => get_option('horas_oracion_recaptcha_site_key', ''),
                'turnstileSiteKey' => get_option('horas_oracion_turnstile_site_key', ''),
                'currentMonth' => date_i18n('F'),
                'i18n' => [
                    'fillRequired' => __('Por favor complete todos los campos requeridos.', '40-horas-oracion'),
                    'sending' => __('Enviando...', '40-horas-oracion'),
                    'unknownError' => __('Error desconocido', '40-horas-oracion'),
                    'requestError' => __('Error al procesar la solicitud. Por favor intente nuevamente.', '40-horas-oracion'),
                    'submitBtn' => __('Inscribirme', '40-horas-oracion'),
                    'emptyHour' => __('Aún no hay personas anotadas en esta hora. ¡Animate a ser la primera!', '40-horas-oracion'),
                    'hourPrefix' => __('Hora', '40-horas-oracion'),
                    'dayPrefix' => __('Día', '40-horas-oracion'),
                ]
            ]);
            
            // Load reCAPTCHA script if needed
            if ($this->security->get_captcha_type() === 'recaptcha') {
                $site_key = get_option('horas_oracion_recaptcha_site_key', '');
                if (!empty($site_key)) {
                    wp_enqueue_script(
                        'google-recaptcha',
                        'https://www.google.com/recaptcha/api.js',
                        [],
                        null,
                        true
                    );
                }
            }
            
            // Load Turnstile script if needed
            if ($this->security->get_captcha_type() === 'turnstile') {
                $site_key = get_option('horas_oracion_turnstile_site_key', '');
                if (!empty($site_key)) {
                    wp_enqueue_script(
                        'cloudflare-turnstile',
                        'https://challenges.cloudflare.com/turnstile/v0/api.js',
                        [],
                        null,
                        true
                    );
                }
            }
        }
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'show_counters' => 'true',
            'show_form' => 'true',
            'show_table' => 'true',
        ], $atts);
        
        // Get initial data
        $current_month_count = $this->database->get_current_month_count();
        $historical_count = $this->database->get_historical_count();
        $registrations = $this->database->get_registrations_grouped_by_hour();
        $hours_structure = Database::get_hours_structure();
        
        // Get intro text
        $intro_text = get_option('horas_oracion_intro_text', '');
        
        // Get primary color
        $primary_color = get_option('horas_oracion_primary_color', '#3b82f6');
        
        // Get CAPTCHA info
        $captcha_type = $this->security->get_captcha_type();
        $recaptcha_site_key = get_option('horas_oracion_recaptcha_site_key', '');
        $turnstile_site_key = get_option('horas_oracion_turnstile_site_key', '');
        
        ob_start();
        include HORAS_ORACION_PLUGIN_DIR . 'templates/shortcode.php';
        return ob_get_clean();
    }
}
