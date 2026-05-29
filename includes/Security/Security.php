<?php
/**
 * Security Class
 * 
 * Handles security operations: nonces, sanitization, escaping, rate limiting
 * 
 * @package HorasOracion
 * @subpackage Security
 * @since 1.0.0
 */

namespace HorasOracion\Security;

class Security {
    
    /**
     * Sanitize registration data
     */
    public static function sanitize_registration_data($data) {
        $sanitized = [];
        
        // Sanitize nombre (letters, spaces, accents)
        $sanitized['nombre'] = sanitize_text_field($data['nombre']);
        $sanitized['nombre'] = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]/', '', $sanitized['nombre']);
        $sanitized['nombre'] = substr($sanitized['nombre'], 0, 100);
        
        // Sanitize apellido (letters, spaces, accents)
        $sanitized['apellido'] = sanitize_text_field($data['apellido']);
        $sanitized['apellido'] = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]/', '', $sanitized['apellido']);
        $sanitized['apellido'] = substr($sanitized['apellido'], 0, 100);
        
        // Sanitize ciudad
        $sanitized['ciudad'] = sanitize_text_field($data['ciudad']);
        $sanitized['ciudad'] = substr($sanitized['ciudad'], 0, 100);
        
        // Sanitize pais
        $sanitized['pais'] = sanitize_text_field($data['pais']);
        $sanitized['pais'] = substr($sanitized['pais'], 0, 100);
        
        $duration_hours = (int) get_option('horas_oracion_duration_hours', 40);
        
        // Sanitize numero_hora
        $sanitized['numero_hora'] = absint($data['numero_hora']);
        $sanitized['numero_hora'] = max(1, min($duration_hours, $sanitized['numero_hora']));
        
        // Sanitize dia (must be 1-31)
        $sanitized['dia'] = absint($data['dia']);
        $sanitized['dia'] = max(1, min(31, $sanitized['dia']));
        
        // Sanitize hora (format HH:MM)
        $sanitized['hora'] = sanitize_text_field($data['hora']);
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $sanitized['hora'])) {
            $sanitized['hora'] = '08:00';
        }
        
        // Get IP address
        $sanitized['ip_address'] = self::get_client_ip();
        
        return $sanitized;
    }
    
    /**
     * Validate registration data
     */
    public static function validate_registration_data($data) {
        $errors = [];
        
        // Validate nombre
        if (empty($data['nombre'])) {
            $errors[] = 'El nombre es obligatorio';
        } elseif (strlen($data['nombre']) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($data['nombre']) > 100) {
            $errors[] = 'El nombre no puede exceder 100 caracteres';
        }
        
        // Validate apellido
        if (empty($data['apellido'])) {
            $errors[] = 'El apellido es obligatorio';
        } elseif (strlen($data['apellido']) < 2) {
            $errors[] = 'El apellido debe tener al menos 2 caracteres';
        } elseif (strlen($data['apellido']) > 100) {
            $errors[] = 'El apellido no puede exceder 100 caracteres';
        }
        
        // Validate ciudad
        if (empty($data['ciudad'])) {
            $errors[] = 'La ciudad es obligatoria';
        } elseif (strlen($data['ciudad']) > 100) {
            $errors[] = 'La ciudad no puede exceder 100 caracteres';
        }
        
        // Validate pais
        if (empty($data['pais'])) {
            $errors[] = 'El país es obligatorio';
        } elseif (strlen($data['pais']) > 100) {
            $errors[] = 'El país no puede exceder 100 caracteres';
        }
        
        $duration_hours = (int) get_option('horas_oracion_duration_hours', 40);
        
        // Validate numero_hora
        if (empty($data['numero_hora'])) {
            $errors[] = 'Debe seleccionar un horario';
        } elseif ($data['numero_hora'] < 1 || $data['numero_hora'] > $duration_hours) {
            $errors[] = 'Horario inválido';
        }
        
        return $errors;
    }
    
    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip;
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }
        
        return '127.0.0.1';
    }
    
    /**
     * Check rate limit for IP
     */
    public static function check_rate_limit($ip_address, $max_attempts = 5, $time_window = 3600) {
        $transient_key = 'horas_oracion_rate_limit_' . md5($ip_address);
        $attempts = get_transient($transient_key);
        
        if ($attempts === false) {
            // First attempt
            set_transient($transient_key, 1, $time_window);
            return true;
        }
        
        if ($attempts >= $max_attempts) {
            return false;
        }
        
        // Increment attempts
        set_transient($transient_key, $attempts + 1, $time_window);
        return true;
    }
    
    /**
     * Reset rate limit for IP
     */
    public static function reset_rate_limit($ip_address) {
        $transient_key = 'horas_oracion_rate_limit_' . md5($ip_address);
        delete_transient($transient_key);
    }
    
    /**
     * Generate nonce
     */
    public static function create_nonce($action = 'horas_oracion_nonce') {
        return wp_create_nonce($action);
    }
    
    /**
     * Verify nonce
     */
    public static function verify_nonce($nonce, $action = 'horas_oracion_nonce') {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Escape output
     */
    public static function esc($string) {
        return esc_html($string);
    }
    
    /**
     * Escape attribute
     */
    public static function esc_attr($string) {
        return esc_attr($string);
    }
    
    /**
     * Escape URL
     */
    public static function esc_url($url) {
        return esc_url($url);
    }
    
    /**
     * Verify reCAPTCHA
     */
    public static function verify_recaptcha($token) {
        $secret_key = get_option('horas_oracion_recaptcha_secret_key', '');
        
        if (empty($secret_key)) {
            return true; // Allow if not configured
        }
        
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $token
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        return isset($data->success) && $data->success === true;
    }
    
    /**
     * Verify Cloudflare Turnstile
     */
    public static function verify_turnstile($token) {
        $secret_key = get_option('horas_oracion_turnstile_secret_key', '');
        
        if (empty($secret_key)) {
            return true; // Allow if not configured
        }
        
        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $token
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        return isset($data->success) && $data->success === true;
    }
    
    /**
     * Check if CAPTCHA is enabled and which type
     */
    public static function get_captcha_type() {
        $recaptcha_key = get_option('horas_oracion_recaptcha_site_key', '');
        $turnstile_key = get_option('horas_oracion_turnstile_site_key', '');
        
        if (!empty($turnstile_key)) {
            return 'turnstile';
        } elseif (!empty($recaptcha_key)) {
            return 'recaptcha';
        }
        
        return 'none';
    }
    
    /**
     * Sanitize hour selection
     */
    public static function sanitize_hour_selection($hour_data) {
        $hours_structure = \HorasOracion\Database\Database::get_hours_structure();
        
        $numero_hora = absint($hour_data['numero_hora']);
        
        if (!isset($hours_structure[$numero_hora])) {
            return null;
        }
        
        return [
            'numero_hora' => $numero_hora,
            'dia' => $hours_structure[$numero_hora]['dia'],
            'hora' => $hours_structure[$numero_hora]['hora']
        ];
    }
}
