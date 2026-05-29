<?php
/**
 * AJAX Handler Class
 * 
 * Handles AJAX requests for form submission
 * 
 * @package HorasOracion
 * @subpackage AJAX
 * @since 1.0.0
 */

namespace HorasOracion\AJAX;

use HorasOracion\Database\Database;
use HorasOracion\Security\Security;

class AJAX {
    
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
     * Initialize AJAX handlers
     */
    public function init() {
        // Logged in users
        add_action('wp_ajax_horas_oracion_submit_registration', [$this, 'handle_registration']);
        add_action('wp_ajax_horas_oracion_get_registrations', [$this, 'handle_get_registrations']);
        
        // Non-logged in users
        add_action('wp_ajax_nopriv_horas_oracion_submit_registration', [$this, 'handle_registration']);
        add_action('wp_ajax_nopriv_horas_oracion_get_registrations', [$this, 'handle_get_registrations']);
    }
    
    /**
     * Handle registration submission
     */
    public function handle_registration() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !$this->security->verify_nonce($_POST['nonce'], 'horas_oracion_nonce')) {
            wp_send_json_error([
                'message' => 'Error de seguridad. Por favor recargue la página.'
            ]);
        }
        
        // Check rate limit
        $ip_address = $this->security->get_client_ip();
        if (!$this->security->check_rate_limit($ip_address, 5, 3600)) {
            wp_send_json_error([
                'message' => 'Ha excedido el límite de intentos. Por favor espere una hora.'
            ]);
        }
        
        // Validate CAPTCHA
        $captcha_type = $this->security->get_captcha_type();
        if ($captcha_type === 'recaptcha') {
            if (!isset($_POST['recaptcha_token']) || !$this->security->verify_recaptcha($_POST['recaptcha_token'])) {
                wp_send_json_error([
                    'message' => 'Error en la verificación CAPTCHA.'
                ]);
            }
        } elseif ($captcha_type === 'turnstile') {
            if (!isset($_POST['turnstile_token']) || !$this->security->verify_turnstile($_POST['turnstile_token'])) {
                wp_send_json_error([
                    'message' => 'Error en la verificación CAPTCHA.'
                ]);
            }
        }
        
        // Get and validate data
        $data = [
            'nombre' => isset($_POST['nombre']) ? $_POST['nombre'] : '',
            'apellido' => isset($_POST['apellido']) ? $_POST['apellido'] : '',
            'ciudad' => isset($_POST['ciudad']) ? $_POST['ciudad'] : '',
            'pais' => isset($_POST['pais']) ? $_POST['pais'] : '',
            'numero_hora' => isset($_POST['numero_hora']) ? $_POST['numero_hora'] : '',
        ];
        
        // Validate fields
        $errors = $this->security->validate_registration_data($data);
        
        if (!empty($errors)) {
            wp_send_json_error([
                'message' => implode('<br>', $errors)
            ]);
        }
        
        // Sanitize hour selection
        $hour_data = $this->security->sanitize_hour_selection([
            'numero_hora' => $data['numero_hora']
        ]);
        
        if ($hour_data === null) {
            wp_send_json_error([
                'message' => 'Horario inválido.'
            ]);
        }
        
        // Merge hour data
        $data['numero_hora'] = $hour_data['numero_hora'];
        $data['dia'] = $hour_data['dia'];
        $data['hora'] = $hour_data['hora'];
        
        // Check if hour is full (if limit is set)
        $allow_multiple = get_option('horas_oracion_allow_multiple_per_hour', '1') === '1';
        $max_per_hour = (int) get_option('horas_oracion_max_per_hour', '0');
        
        if (!$allow_multiple || $max_per_hour > 0) {
            $current_count = $this->database->get_hour_count($data['numero_hora']);
            $limit = $allow_multiple ? $max_per_hour : 1;
            
            if ($limit > 0 && $current_count >= $limit) {
                wp_send_json_error([
                    'message' => 'Ese horario ya está completo.'
                ]);
            }
        }
        
        // Insert registration
        $result = $this->database->insert_registration($data);
        
        if ($result === false) {
            wp_send_json_error([
                'message' => 'Error al guardar la inscripción. Por favor intente nuevamente.'
            ]);
        }
        
        // Reset rate limit on success
        $this->security->reset_rate_limit($ip_address);
        
        wp_send_json_success([
            'message' => 'Inscripción realizada correctamente.'
        ]);
    }
    
    /**
     * Handle get registrations request
     */
    public function handle_get_registrations() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !$this->security->verify_nonce($_POST['nonce'], 'horas_oracion_nonce')) {
            wp_send_json_error([
                'message' => 'Error de seguridad.'
            ]);
        }
        
        // Get registrations grouped by hour
        $registrations = $this->database->get_registrations_grouped_by_hour();
        
        // Get counts
        $current_month_count = $this->database->get_current_month_count();
        $historical_count = $this->database->get_historical_count();
        
        // Format registrations for output
        $formatted = [];
        foreach ($registrations as $hour_group) {
            $participants = [];
            foreach ($hour_group['participants'] as $participant) {
                $created_at_formatted = '';
                if (isset($participant->created_at)) {
                    $created_at_formatted = wp_date('d \d\e F \d\e Y - H:i', strtotime($participant->created_at));
                }
                
                $participants[] = [
                    'nombre' => $this->security->esc($participant->nombre),
                    'apellido' => $this->security->esc($participant->apellido),
                    'ciudad' => $this->security->esc($participant->ciudad),
                    'pais' => $this->security->esc($participant->pais),
                    'created_at' => $created_at_formatted
                ];
            }
            
            $formatted[] = [
                'numero_hora' => $hour_group['numero_hora'],
                'dia' => $hour_group['dia'],
                'hora' => $hour_group['hora'],
                'participants' => $participants
            ];
        }
        
        wp_send_json_success([
            'registrations' => $formatted,
            'current_month_count' => $current_month_count,
            'historical_count' => $historical_count
        ]);
    }
}
