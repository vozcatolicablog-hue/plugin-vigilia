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

<div class="horas-oracion-wrapper" style="--primary-color: <?php echo esc_attr($primary_color); ?>;">
    
    <?php if (!empty($intro_text)): ?>
    <div class="horas-oracion-intro">
        <p><?php echo nl2br(esc_html($intro_text)); ?></p>
    </div>
    <?php endif; ?>

    <?php if ($atts['show_counters'] === 'true'): ?>
    <div class="horas-oracion-counters">
        <div class="counter-box">
            <h3><?php esc_html_e('Personas anotadas este mes:', '40-horas-oracion'); ?></h3>
            <span class="counter-number"><?php echo absint($current_month_count); ?></span>
        </div>
        <div class="counter-box">
            <h3><?php esc_html_e('Total histórico de participantes:', '40-horas-oracion'); ?></h3>
            <span class="counter-number"><?php echo absint($historical_count); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($atts['show_form'] === 'true'): ?>
    <div class="horas-oracion-form-container">
        <h2><?php esc_html_e('Inscríbete', '40-horas-oracion'); ?></h2>
        <form id="horas-oracion-form" class="horas-oracion-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="ho_nombre"><?php esc_html_e('Nombre *', '40-horas-oracion'); ?></label>
                    <input type="text" id="ho_nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="ho_apellido"><?php esc_html_e('Apellido *', '40-horas-oracion'); ?></label>
                    <input type="text" id="ho_apellido" name="apellido" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ho_ciudad"><?php esc_html_e('Ciudad *', '40-horas-oracion'); ?></label>
                    <input type="text" id="ho_ciudad" name="ciudad" required>
                </div>
                <div class="form-group">
                    <label for="ho_pais"><?php esc_html_e('País *', '40-horas-oracion'); ?></label>
                    <input type="text" id="ho_pais" name="pais" required>
                </div>
            </div>
            <div class="form-group">
                <label for="ho_numero_hora"><?php esc_html_e('Selecciona tu hora de oración *', '40-horas-oracion'); ?></label>
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
            
            <div class="ho-captcha-container" style="margin-bottom: 15px;">
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

            <button type="submit" class="ho-submit-btn" style="background-color: var(--primary-color);">
                <?php esc_html_e('Inscribirme', '40-horas-oracion'); ?>
            </button>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($atts['show_table'] === 'true'): ?>
    <div class="horas-oracion-table-container">
        <h2><?php esc_html_e('Horarios y Participantes', '40-horas-oracion'); ?></h2>
        <div id="horas-oracion-schedule">
            <!-- Rendered by JS or initially populated here -->
            <?php foreach ($hours_structure as $num => $hour): ?>
                <div class="ho-hour-group">
                    <h3>
                        <?php printf(
                            esc_html__('Hora %1$d — Día %2$d — %3$s', '40-horas-oracion'),
                            $num,
                            $hour['dia'],
                            $hour['hora']
                        ); ?>
                    </h3>
                    <ul class="ho-participants-list">
                        <?php 
                        if (isset($registrations[$num]) && !empty($registrations[$num]['participants'])) {
                            foreach ($registrations[$num]['participants'] as $p) {
                                echo '<li>' . esc_html($p->nombre . ' ' . $p->apellido . ' — ' . $p->ciudad . ', ' . $p->pais) . '</li>';
                            }
                        } else {
                            echo '<li class="ho-empty">' . esc_html__('Aún no hay inscritos en este horario.', '40-horas-oracion') . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
