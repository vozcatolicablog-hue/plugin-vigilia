<?php
/**
 * Export Class
 * 
 * Handles CSV export functionality
 * 
 * @package HorasOracion
 * @subpackage Export
 * @since 1.0.0
 */

namespace HorasOracion\Export;

use HorasOracion\Database\Database;

class Export {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct(Database $database) {
        $this->database = $database;
    }
    
    /**
     * Export current month to CSV
     */
    public function export_current_month() {
        $registrations = $this->database->get_current_month_registrations();
        $filename = $this->get_filename_for_current_month();
        
        return $this->generate_csv($registrations, $filename);
    }
    
    /**
     * Export month by date string (MM/YYYY)
     */
    public function export_month_by_date($date_string) {
        list($month, $year) = explode('/', $date_string);
        $registrations = $this->database->get_registrations_by_month($month, $year);
        
        $filename = sprintf('40-horas-oracion-%s-%s.csv', $year, str_pad($month, 2, '0', STR_PAD_LEFT));
        
        return $this->generate_csv($registrations, $filename);
    }
    
    /**
     * Generate CSV file
     */
    private function generate_csv($registrations, $filename) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/40-horas-oracion/';
        
        // Create directory if it doesn't exist
        if (!is_dir($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        $filepath = $export_dir . $filename;
        
        // Open file for writing
        $file = fopen($filepath, 'w');
        
        if (!$file) {
            return false;
        }
        
        // Set UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Write headers
        $headers = [
            __('Nombre', '40-horas-oracion'),
            __('Apellido', '40-horas-oracion'),
            __('Ciudad', '40-horas-oracion'),
            __('País', '40-horas-oracion'),
            __('Número de Hora', '40-horas-oracion'),
            __('Día', '40-horas-oracion'),
            __('Hora', '40-horas-oracion'),
            __('Fecha de Inscripción', '40-horas-oracion'),
        ];
        fputcsv($file, $headers);
        
        // Write data rows
        foreach ($registrations as $registration) {
            $row = [
                $registration->nombre,
                $registration->apellido,
                $registration->ciudad,
                $registration->pais,
                $registration->numero_hora,
                $registration->dia,
                $registration->hora,
                $registration->created_at,
            ];
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return [
            'filename' => $filename,
            'url' => $upload_dir['baseurl'] . '/40-horas-oracion/' . $filename,
            'path' => $filepath,
        ];
    }
    
    /**
     * Get filename for current month
     */
    private function get_filename_for_current_month() {
        $current_date = new \DateTime();
        $year = $current_date->format('Y');
        $month = $current_date->format('m');
        
        return sprintf('40-horas-oracion-%s-%s.csv', $year, $month);
    }
    
    /**
     * Get all exported files
     */
    public function get_exported_files() {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/40-horas-oracion/';
        
        if (!is_dir($export_dir)) {
            return [];
        }
        
        $files = array_diff(scandir($export_dir), ['.', '..']);
        $files = array_filter($files, function ($file) {
            return preg_match('/^40-horas-oracion-\d{4}-\d{2}\.csv$/', $file);
        });
        
        $result = [];
        foreach ($files as $file) {
            $filepath = $export_dir . $file;
            $result[] = [
                'filename' => $file,
                'url' => $upload_dir['baseurl'] . '/40-horas-oracion/' . $file,
                'path' => $filepath,
                'size' => filesize($filepath),
                'date' => filemtime($filepath),
            ];
        }
        
        return array_reverse($result);
    }
    
    /**
     * Delete exported file
     */
    public function delete_exported_file($filename) {
        // Validate filename format
        if (!preg_match('/^40-horas-oracion-\d{4}-\d{2}\.csv$/', $filename)) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'] . '/40-horas-oracion/' . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}
