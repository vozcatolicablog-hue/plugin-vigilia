<?php
/**
 * Database Class
 * 
 * Handles database operations for the plugin
 * 
 * @package HorasOracion
 * @subpackage Database
 * @since 1.0.0
 */

namespace HorasOracion\Database;

use HorasOracion\Security\Security;

class Database {
    
    /**
     * Table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . '40_horas_oracion';
    }
    
    /**
     * Get table name
     */
    public function get_table_name() {
        return $this->table_name;
    }
    
    /**
     * Create the custom table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . '40_horas_oracion';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            apellido varchar(100) NOT NULL,
            ciudad varchar(100) NOT NULL,
            pais varchar(100) NOT NULL,
            numero_hora int(11) NOT NULL,
            dia int(11) NOT NULL,
            hora varchar(5) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ip_address varchar(45) NOT NULL,
            PRIMARY KEY (id),
            KEY idx_numero_hora (numero_hora),
            KEY idx_dia (dia),
            KEY idx_created_at (created_at),
            KEY idx_ip_address (ip_address)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set initial historical counter if not set
        if (get_option('horas_oracion_historical_count') === false) {
            update_option('horas_oracion_historical_count', 13965);
        }
    }
    
    /**
     * Insert a new registration
     */
    public function insert_registration($data) {
        global $wpdb;
        
        $sanitized = Security::sanitize_registration_data($data);
        
        $result = $wpdb->insert(
            $this->table_name,
            [
                'nombre' => $sanitized['nombre'],
                'apellido' => $sanitized['apellido'],
                'ciudad' => $sanitized['ciudad'],
                'pais' => $sanitized['pais'],
                'numero_hora' => $sanitized['numero_hora'],
                'dia' => $sanitized['dia'],
                'hora' => $sanitized['hora'],
                'ip_address' => $sanitized['ip_address']
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s']
        );
        
        if ($result !== false) {
            // Update historical counter
            $current_count = get_option('horas_oracion_historical_count', 13965);
            update_option('horas_oracion_historical_count', $current_count + 1);
            
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get all registrations for current month
     */
    public function get_current_month_registrations() {
        global $wpdb;
        
        $current_month = date('m');
        $current_year = date('Y');
        
        $query = $wpdb->prepare(
            "SELECT * FROM $this->table_name 
            WHERE MONTH(created_at) = %d 
            AND YEAR(created_at) = %d 
            ORDER BY numero_hora ASC, created_at ASC",
            $current_month,
            $current_year
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get registrations grouped by hour
     */
    public function get_registrations_grouped_by_hour() {
        global $wpdb;
        
        $current_month = date('m');
        $current_year = date('Y');
        
        $query = $wpdb->prepare(
            "SELECT * FROM $this->table_name 
            WHERE MONTH(created_at) = %d 
            AND YEAR(created_at) = %d 
            ORDER BY numero_hora ASC, created_at ASC",
            $current_month,
            $current_year
        );
        
        $results = $wpdb->get_results($query);
        
        // Group by hour
        $grouped = [];
        foreach ($results as $row) {
            $hour_key = $row->numero_hora;
            if (!isset($grouped[$hour_key])) {
                $grouped[$hour_key] = [
                    'numero_hora' => $row->numero_hora,
                    'dia' => $row->dia,
                    'hora' => $row->hora,
                    'participants' => []
                ];
            }
            $grouped[$hour_key]['participants'][] = $row;
        }
        
        return $grouped;
    }
    
    /**
     * Get count for current month
     */
    public function get_current_month_count() {
        global $wpdb;
        
        $current_month = date('m');
        $current_year = date('Y');
        
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name 
            WHERE MONTH(created_at) = %d 
            AND YEAR(created_at) = %d",
            $current_month,
            $current_year
        );
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Get historical count
     */
    public function get_historical_count() {
        return (int) get_option('horas_oracion_historical_count', 13965);
    }
    
    /**
     * Get count for specific hour
     */
    public function get_hour_count($numero_hora) {
        global $wpdb;
        
        $current_month = date('m');
        $current_year = date('Y');
        
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name 
            WHERE numero_hora = %d 
            AND MONTH(created_at) = %d 
            AND YEAR(created_at) = %d",
            $numero_hora,
            $current_month,
            $current_year
        );
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Get all registrations (for admin)
     */
    public function get_all_registrations($limit = 50, $offset = 0, $search = '', $month = '') {
        global $wpdb;
        
        $where = ["1=1"];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(nombre LIKE %s OR apellido LIKE %s OR ciudad LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if (!empty($month)) {
            $where[] = "MONTH(created_at) = %d";
            $params[] = $month;
        }
        
        $where_clause = implode(' AND ', $where);
        
        if (!empty($params)) {
            $query = $wpdb->prepare(
                "SELECT * FROM $this->table_name 
                WHERE $where_clause 
                ORDER BY created_at DESC 
                LIMIT %d OFFSET %d",
                ...array_merge($params, [$limit, $offset])
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM $this->table_name 
                WHERE $where_clause 
                ORDER BY created_at DESC 
                LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get total count for pagination
     */
    public function get_total_count($search = '', $month = '') {
        global $wpdb;
        
        $where = ["1=1"];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(nombre LIKE %s OR apellido LIKE %s OR ciudad LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if (!empty($month)) {
            $where[] = "MONTH(created_at) = %d";
            $params[] = $month;
        }
        
        $where_clause = implode(' AND ', $where);
        
        if (!empty($params)) {
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM $this->table_name WHERE $where_clause",
                ...$params
            );
        } else {
            $query = "SELECT COUNT(*) FROM $this->table_name WHERE $where_clause";
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Delete registration
     */
    public function delete_registration($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );
        
        if ($result !== false) {
            // Update historical counter
            $current_count = get_option('horas_oracion_historical_count', 13965);
            update_option('horas_oracion_historical_count', max(0, $current_count - 1));
        }
        
        return $result !== false;
    }
    
    /**
     * Get all data for export
     */
    public function get_all_for_export($month = null, $year = null) {
        global $wpdb;
        
        $where = ["1=1"];
        $params = [];
        
        if ($month !== null) {
            $where[] = "MONTH(created_at) = %d";
            $params[] = $month;
        }
        
        if ($year !== null) {
            $where[] = "YEAR(created_at) = %d";
            $params[] = $year;
        }
        
        $where_clause = implode(' AND ', $where);
        
        if (!empty($params)) {
            $query = $wpdb->prepare(
                "SELECT * FROM $this->table_name 
                WHERE $where_clause 
                ORDER BY created_at ASC",
                ...$params
            );
        } else {
            $query = "SELECT * FROM $this->table_name WHERE $where_clause ORDER BY created_at ASC";
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Clear all registrations for current month
     */
    public function clear_current_month() {
        global $wpdb;
        
        $current_month = date('m');
        $current_year = date('Y');
        
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $this->table_name 
                WHERE MONTH(created_at) = %d 
                AND YEAR(created_at) = %d",
                $current_month,
                $current_year
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get available hours (40 hours structure)
     */
    public static function get_hours_structure() {
        $start_day = (int) get_option('horas_oracion_start_day', 14);
        $start_time = get_option('horas_oracion_start_time', '08:00');
        $duration_hours = (int) get_option('horas_oracion_duration_hours', 40);
        
        $structure = [];
        
        // We use a base date in a month with 31 days to avoid edge cases while rolling over days
        $base_date_string = "2026-05-" . sprintf('%02d', $start_day) . " " . $start_time;
        $base_time = strtotime($base_date_string);
        
        for ($i = 1; $i <= $duration_hours; $i++) {
            $current_time = $base_time + (($i - 1) * 3600);
            $structure[$i] = [
                'dia' => (int) date('d', $current_time),
                'hora' => date('H:i', $current_time)
            ];
        }
        
        return $structure;
    }
    
    /**
     * Get registrations by month
     */
    public function get_registrations_by_month($month, $year) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM $this->table_name 
            WHERE MONTH(created_at) = %d 
            AND YEAR(created_at) = %d 
            ORDER BY numero_hora ASC, created_at ASC",
            $month,
            $year
        );
        
        return $wpdb->get_results($query);
    }
}

