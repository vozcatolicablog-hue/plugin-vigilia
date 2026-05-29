<?php
/**
 * Admin Exports Template
 * 
 * @package HorasOracion
 * @subpackage Templates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap horas-oracion-admin-page">
    <h1><?php esc_html_e('40 Horas de Oración', '40-horas-oracion'); ?></h1>
    <h2><?php esc_html_e('Exportaciones', '40-horas-oracion'); ?></h2>
    <p class="page-description"><?php esc_html_e('Descarga los archivos CSV de los meses anteriores', '40-horas-oracion'); ?></p>

    <div class="horas-oracion-admin-box">
        <?php if (!empty($files)): ?>
            <table class="horas-oracion-admin-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Archivo', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Tamaño', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Fecha', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Descargar', '40-horas-oracion'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                        <?php 
                        $filesize = filesize($file);
                        $filedate = filemtime($file);
                        $filename = basename($file);
                        $fileurl = $export_url . '/' . $filename;
                        ?>
                        <tr>
                            <td><?php echo esc_html($filename); ?></td>
                            <td><?php echo esc_html(size_format($filesize)); ?></td>
                            <td><?php echo esc_html(date_i18n('d/m/Y H:i', $filedate)); ?></td>
                            <td>
                                <a href="<?php echo esc_url($fileurl); ?>" class="horas-oracion-admin-btn" download>
                                    <?php esc_html_e('Descargar', '40-horas-oracion'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="horas-oracion-empty-state">
                <p><?php esc_html_e('No hay exportaciones disponibles', '40-horas-oracion'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Manual Export -->
    <div class="horas-oracion-admin-box">
        <h2><?php esc_html_e('Exportar mes específico', '40-horas-oracion'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('export_csv'); ?>
            
            <div class="form-field">
                <label for="export_month"><?php esc_html_e('Seleccionar mes', '40-horas-oracion'); ?></label>
                <input type="month" id="export_month" name="export_month" required />
            </div>

            <button type="submit" name="horas_oracion_export_month" class="button button-primary">
                <?php esc_html_e('Exportar CSV', '40-horas-oracion'); ?>
            </button>
        </form>
    </div>
</div>
