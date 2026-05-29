<?php
/**
 * Admin Inscriptions Template
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
    <p class="page-description"><?php esc_html_e('Gestiona todas las inscripciones mensuales', '40-horas-oracion'); ?></p>

    <!-- Shortcode Info -->
    <div class="horas-oracion-admin-box" style="background: #f0f6fc; border-left: 4px solid #3b82f6; margin-top: 15px;">
        <p style="margin: 0;"><strong><?php esc_html_e('Shortcode:', '40-horas-oracion'); ?></strong> <code style="font-size: 1.1em; padding: 3px 8px; background: #fff; border: 1px solid #ccd0d4;">[horas_oracion]</code> - <?php esc_html_e('Usa este código para mostrar el formulario en cualquier página.', '40-horas-oracion'); ?></p>
    </div>

    <!-- Notices -->
    <div id="horas-oracion-notices"></div>

    <!-- Statistics -->
    <div class="horas-oracion-admin-stats">
        <div class="horas-oracion-stat-box">
            <strong><?php echo absint($current_month_count); ?></strong>
            <p><?php esc_html_e('Inscritas este mes', '40-horas-oracion'); ?></p>
        </div>
        <div class="horas-oracion-stat-box">
            <strong><?php echo absint($historical_count); ?></strong>
            <p><?php esc_html_e('Total histórico', '40-horas-oracion'); ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="horas-oracion-admin-box">
        <form method="get" action="">
            <input type="hidden" name="page" value="horas-oracion">
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <input type="text" name="s" placeholder="<?php esc_attr_e('Buscar por nombre...', '40-horas-oracion'); ?>" 
                       value="<?php echo esc_attr($search); ?>" style="flex: 1; padding: 0.5rem;">
                <input type="text" name="month" placeholder="MM/YYYY" 
                       value="<?php echo esc_attr($month); ?>" style="width: 150px; padding: 0.5rem;">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Filtrar', '40-horas-oracion'); ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=horas-oracion')); ?>" class="button">
                    <?php esc_html_e('Limpiar', '40-horas-oracion'); ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="horas-oracion-admin-box">
        <?php if (!empty($registrations)): ?>
            <table class="horas-oracion-admin-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Nombre', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Apellido', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Ciudad', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('País', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Número de Hora', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Día', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Hora', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Fecha de Inscripción', '40-horas-oracion'); ?></th>
                        <th><?php esc_html_e('Acciones', '40-horas-oracion'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><?php echo absint($reg->id); ?></td>
                            <td><?php echo esc_html($reg->nombre); ?></td>
                            <td><?php echo esc_html($reg->apellido); ?></td>
                            <td><?php echo esc_html($reg->ciudad); ?></td>
                            <td><?php echo esc_html($reg->pais); ?></td>
                            <td class="hora-number"><?php echo absint($reg->numero_hora); ?></td>
                            <td><?php echo absint($reg->dia); ?></td>
                            <td><?php echo esc_html($reg->hora); ?></td>
                            <td><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($reg->created_at))); ?></td>
                            <td class="actions">
                                <button class="horas-oracion-delete-btn button button-small button-link-delete" 
                                        data-registration-id="<?php echo absint($reg->id); ?>">
                                    <?php esc_html_e('Eliminar', '40-horas-oracion'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="horas-oracion-pagination">
                    <?php
                    $base_url = admin_url('admin.php?page=horas-oracion&paged=%d');
                    if ($search) {
                        $base_url .= '&s=' . urlencode($search);
                    }
                    if ($month) {
                        $base_url .= '&month=' . urlencode($month);
                    }
                    
                    // Previous page
                    if ($page > 1) {
                        echo '<a href="' . esc_url(sprintf($base_url, $page - 1)) . '">&laquo;</a>';
                    }
                    
                    // Page numbers
                    for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
                        if ($i == $page) {
                            echo '<span class="current">' . absint($i) . '</span>';
                        } else {
                            echo '<a href="' . esc_url(sprintf($base_url, $i)) . '">' . absint($i) . '</a>';
                        }
                    }
                    
                    // Next page
                    if ($page < $total_pages) {
                        echo '<a href="' . esc_url(sprintf($base_url, $page + 1)) . '">&raquo;</a>';
                    }
                    ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="horas-oracion-empty-state">
                <p><?php esc_html_e('No hay inscripciones', '40-horas-oracion'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
