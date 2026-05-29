<?php
/**
 * Shortcode Template
 * 
 * @package HorasOracion
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="horas-oracion-container" style="--primary-color: <?php echo esc_attr($primary_color); ?>;">
    
    <?php if (!empty($intro_text)): ?>
    <div class="horas-oracion-intro">
        <h2><?php esc_html_e('40 Horas de Oración', '40-horas-oracion'); ?></h2>
        <p><?php echo nl2br(esc_html($intro_text)); ?></p>
    </div>
    <?php endif; ?>

    <?php if ($atts['show_counters'] === 'true'): ?>
    <div class="horas-oracion-counters">
        <div class="counter-card">
            <h3><?php esc_html_e('Personas anotadas este mes', '40-horas-oracion'); ?></h3>
            <p class="counter-value"><?php echo absint($current_month_count); ?></p>
        </div>
        <div class="counter-card">
            <h3><?php esc_html_e('Total histórico de participantes', '40-horas-oracion'); ?></h3>
            <p class="counter-value"><?php echo absint($historical_count); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($atts['show_form'] === 'true'): ?>
    <form id="horas-oracion-form" class="horas-oracion-form">
        <h3><?php esc_html_e('Inscríbete en la Vigilia', '40-horas-oracion'); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label for="ho_nombre"><?php esc_html_e('Nombre', '40-horas-oracion'); ?> <span class="required">*</span></label>
                <input type="text" id="ho_nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="ho_apellido"><?php esc_html_e('Apellido', '40-horas-oracion'); ?> <span class="required">*</span></label>
                <input type="text" id="ho_apellido" name="apellido" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="ho_ciudad"><?php esc_html_e('Ciudad', '40-horas-oracion'); ?> <span class="required">*</span></label>
                <input type="text" id="ho_ciudad" name="ciudad" required>
            </div>
            <div class="form-group">
                <label for="ho_pais"><?php esc_html_e('País', '40-horas-oracion'); ?> <span class="required">*</span></label>
                <input type="text" id="ho_pais" name="pais" required>
            </div>
        </div>
        <div class="form-group">
            <label for="ho_numero_hora"><?php esc_html_e('Selecciona tu hora de oración', '40-horas-oracion'); ?> <span class="required">*</span></label>
            <select id="ho_numero_hora" name="numero_hora" required>
                <option value=""><?php esc_html_e('-- Selecciona un horario --', '40-horas-oracion'); ?></option>
                <?php foreach ($hours_structure as $num => $hour): ?>
                    <option value="<?php echo esc_attr($num); ?>">
                        <?php printf(
                            esc_html__('Hora %1$d — Día %2$d — %3$s', '40-horas-oracion'),
                            $num,
                            $hour['dia'],
                            $hour['hora']
                        ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="horas-oracion-captcha">
            <?php 
            if ($captcha_type === 'recaptcha'): 
                if (!empty($recaptcha_site_key)):
            ?>
                <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($recaptcha_site_key); ?>"></div>
            <?php 
                elseif (current_user_can('manage_options')):
                    echo '<p style="color:#d63638; font-weight:bold; background:#fbeaea; padding:10px; border-left:4px solid #d63638;">⚠️ Aviso para el Administrador: Has activado reCAPTCHA pero falta configurar la Clave del Sitio en los ajustes del plugin.</p>';
                endif;
            elseif ($captcha_type === 'turnstile'): 
                if (!empty($turnstile_site_key)):
            ?>
                <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($turnstile_site_key); ?>"></div>
            <?php 
                elseif (current_user_can('manage_options')):
                    echo '<p style="color:#d63638; font-weight:bold; background:#fbeaea; padding:10px; border-left:4px solid #d63638;">⚠️ Aviso para el Administrador: Has activado Turnstile pero falta configurar la Clave del Sitio en los ajustes del plugin.</p>';
                endif;
            endif; 
            ?>
        </div>

        <div class="form-message"></div>

        <button type="submit" class="horas-oracion-btn btn-primary">
            <?php esc_html_e('Inscribirme', '40-horas-oracion'); ?>
        </button>
    </form>
    <?php endif; ?>

    <?php if ($atts['show_table'] === 'true'): ?>
    <div class="horas-oracion-table-wrapper">
        <div class="horas-oracion-table-header">
            <h3><?php esc_html_e('Horarios y Participantes', '40-horas-oracion'); ?></h3>
        </div>
        <div style="padding: 1.5rem;">
            <?php foreach ($hours_structure as $num => $hour): ?>
                <div class="horas-oracion-hours-group">
                    <div class="horas-oracion-hours-group-header">
                        <h4>
                            <?php printf(
                                esc_html__('Hora %1$d — Día %2$d — %3$s', '40-horas-oracion'),
                                $num,
                                $hour['dia'],
                                $hour['hora']
                            ); ?>
                        </h4>
                    </div>
                    <ul class="horas-oracion-participants-list">
                        <?php 
                        if (isset($registrations[$num]) && !empty($registrations[$num]['participants'])) {
                            foreach ($registrations[$num]['participants'] as $p) {
                                echo '<li>';
                                echo '<span class="horas-oracion-participant-name">' . esc_html($p->nombre . ' ' . $p->apellido) . '</span>';
                                echo '<span class="horas-oracion-participant-location">' . esc_html($p->ciudad . ', ' . $p->pais) . '</span>';
                                echo '</li>';
                            }
                        } else {
                            echo '<li class="horas-oracion-empty-state"><p>' . esc_html__('Aún no hay inscritos en este horario.', '40-horas-oracion') . '</p></li>';
                        }
                        ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
