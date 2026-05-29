<?php
/**
 * Admin Settings Template
 * 
 * @package HorasOracion
 * @subpackage Templates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap horas-oracion-admin-page">
    <h1><?php esc_html_e('Configuración', '40-horas-oracion'); ?></h1>
    <p class="page-description"><?php esc_html_e('Personaliza el plugin según tus necesidades', '40-horas-oracion'); ?></p>

    <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Configuración guardada correctamente.', '40-horas-oracion'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="options.php">
        <?php settings_fields('horas_oracion_settings_group'); ?>

        <!-- Shortcode Info -->
        <div class="horas-oracion-admin-box" style="background: #f0f6fc; border-left: 4px solid #3b82f6;">
            <h2><?php esc_html_e('Shortcode del Plugin', '40-horas-oracion'); ?></h2>
            <p><?php esc_html_e('Copia y pega el siguiente shortcode en la página donde desees mostrar el sistema de inscripción:', '40-horas-oracion'); ?></p>
            <p><code style="font-size: 1.2em; padding: 5px 10px; display: inline-block; background: #fff; border: 1px solid #ccd0d4;">[horas_oracion]</code></p>
        </div>

        <!-- Información General -->
        <div class="horas-oracion-admin-box">
            <h2><?php esc_html_e('Información de la página', '40-horas-oracion'); ?></h2>

            <div class="form-field">
                <label for="horas_oracion_intro_text">
                    <?php esc_html_e('Texto introductorio', '40-horas-oracion'); ?>
                </label>
                <textarea id="horas_oracion_intro_text" name="horas_oracion_intro_text" rows="5"><?php 
                    echo esc_textarea($intro_text); 
                ?></textarea>
                <p class="description">
                    <?php esc_html_e('Este texto aparecerá al principio de la página pública.', '40-horas-oracion'); ?>
                </p>
            </div>

            <div class="form-field">
                <label for="horas_oracion_primary_color">
                    <?php esc_html_e('Color principal', '40-horas-oracion'); ?>
                </label>
                <input type="color" id="horas_oracion_primary_color" name="horas_oracion_primary_color" 
                       value="<?php echo esc_attr($primary_color); ?>" />
                <p class="description">
                    <?php esc_html_e('Elige el color para los elementos principales del sitio.', '40-horas-oracion'); ?>
                </p>
            </div>
        </div>

        <!-- CAPTCHA -->
        <div class="horas-oracion-admin-box">
            <h2><?php esc_html_e('CAPTCHA Anti-spam', '40-horas-oracion'); ?></h2>

            <p><?php esc_html_e('Elige uno de los siguientes servicios de CAPTCHA. Ambos son gratuitos.', '40-horas-oracion'); ?></p>

            <h3><?php esc_html_e('Google reCAPTCHA v2', '40-horas-oracion'); ?></h3>
            <div class="form-field">
                <label for="horas_oracion_recaptcha_site_key">
                    <?php esc_html_e('Clave del sitio', '40-horas-oracion'); ?>
                </label>
                <input type="text" id="horas_oracion_recaptcha_site_key" name="horas_oracion_recaptcha_site_key" 
                       value="<?php echo esc_attr($recaptcha_site_key); ?>" />
                <p class="description">
                    <?php echo wp_kses_post(__('Obtén tus claves en <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a>', '40-horas-oracion')); ?>
                </p>
            </div>

            <div class="form-field">
                <label for="horas_oracion_recaptcha_secret_key">
                    <?php esc_html_e('Clave secreta', '40-horas-oracion'); ?>
                </label>
                <input type="password" id="horas_oracion_recaptcha_secret_key" name="horas_oracion_recaptcha_secret_key" 
                       value="<?php echo esc_attr($recaptcha_secret_key); ?>" />
            </div>

            <h3><?php esc_html_e('Cloudflare Turnstile', '40-horas-oracion'); ?></h3>
            <div class="form-field">
                <label for="horas_oracion_turnstile_site_key">
                    <?php esc_html_e('Clave del sitio', '40-horas-oracion'); ?>
                </label>
                <input type="text" id="horas_oracion_turnstile_site_key" name="horas_oracion_turnstile_site_key" 
                       value="<?php echo esc_attr($turnstile_site_key); ?>" />
                <p class="description">
                    <?php echo wp_kses_post(__('Obtén tus claves en <a href="https://dash.cloudflare.com/" target="_blank">Cloudflare Turnstile</a>', '40-horas-oracion')); ?>
                </p>
            </div>

            <div class="form-field">
                <label for="horas_oracion_turnstile_secret_key">
                    <?php esc_html_e('Clave secreta', '40-horas-oracion'); ?>
                </label>
                <input type="password" id="horas_oracion_turnstile_secret_key" name="horas_oracion_turnstile_secret_key" 
                       value="<?php echo esc_attr($turnstile_secret_key); ?>" />
            </div>
        </div>

        <!-- Estructura de la Vigilia -->
        <div class="horas-oracion-admin-box">
            <h2><?php esc_html_e('Estructura de la Vigilia', '40-horas-oracion'); ?></h2>

            <div class="form-field">
                <label for="horas_oracion_start_day">
                    <?php esc_html_e('Día de inicio del mes', '40-horas-oracion'); ?>
                </label>
                <input type="number" id="horas_oracion_start_day" name="start_day" 
                       value="<?php echo absint($start_day); ?>" min="1" max="31" />
                <p class="description">
                    <?php esc_html_e('El día del mes en que comienza la vigilia (ej: 14).', '40-horas-oracion'); ?>
                </p>
            </div>

            <div class="form-field">
                <label for="horas_oracion_start_time">
                    <?php esc_html_e('Hora de inicio', '40-horas-oracion'); ?>
                </label>
                <input type="time" id="horas_oracion_start_time" name="start_time" 
                       value="<?php echo esc_attr($start_time); ?>" />
                <p class="description">
                    <?php esc_html_e('La hora en que comienza el primer turno (ej: 08:00).', '40-horas-oracion'); ?>
                </p>
            </div>

            <div class="form-field">
                <label for="horas_oracion_duration_hours">
                    <?php esc_html_e('Duración de la vigilia (horas)', '40-horas-oracion'); ?>
                </label>
                <input type="number" id="horas_oracion_duration_hours" name="duration_hours" 
                       value="<?php echo absint($duration_hours); ?>" min="1" />
                <p class="description">
                    <?php esc_html_e('Cantidad total de horas que durará la vigilia.', '40-horas-oracion'); ?>
                </p>
            </div>

            <div class="form-field">
                <label for="horas_oracion_reset_days">
                    <?php esc_html_e('Días para reinicio automático', '40-horas-oracion'); ?>
                </label>
                <input type="number" id="horas_oracion_reset_days" name="reset_days" 
                       value="<?php echo absint($reset_days); ?>" min="0" />
                <p class="description">
                    <?php esc_html_e('Cantidad de días después de finalizar la vigilia para reiniciar automáticamente el mes.', '40-horas-oracion'); ?>
                </p>
            </div>
        </div>

        <!-- Opciones de Inscripción -->
        <div class="horas-oracion-admin-box">
            <h2><?php esc_html_e('Opciones de inscripción', '40-horas-oracion'); ?></h2>

            <div class="form-field">
                <label class="checkbox-label">
                    <input type="checkbox" name="horas_oracion_allow_multiple_per_hour" value="1" 
                           <?php checked($allow_multiple_per_hour, 1); ?> />
                    <?php esc_html_e('Permitir múltiples personas por hora', '40-horas-oracion'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Si está marcado, varias personas pueden inscribirse en la misma hora.', '40-horas-oracion'); ?>
                </p>
            </div>

            <div class="form-field">
                <label for="horas_oracion_max_per_hour">
                    <?php esc_html_e('Máximo de personas por hora', '40-horas-oracion'); ?>
                </label>
                <input type="number" id="horas_oracion_max_per_hour" name="horas_oracion_max_per_hour" 
                       value="<?php echo absint($max_per_hour); ?>" min="0" />
                <p class="description">
                    <?php esc_html_e('Deja en 0 para ilimitado. Escribe 1 para solo una persona por hora.', '40-horas-oracion'); ?>
                </p>
            </div>
        </div>

        <!-- Contador Histórico -->
        <div class="horas-oracion-admin-box">
            <h2><?php esc_html_e('Contador histórico', '40-horas-oracion'); ?></h2>

            <div class="form-field">
                <label for="horas_oracion_historical_count">
                    <?php esc_html_e('Valor inicial del contador histórico', '40-horas-oracion'); ?>
                </label>
                <input type="number" id="horas_oracion_historical_count" name="horas_oracion_historical_count" 
                       value="<?php echo absint($historical_count); ?>" min="0" />
                <p class="description">
                    <?php esc_html_e('Este valor se incrementa en 1 cada vez que alguien se inscribe. Por defecto es 13965.', '40-horas-oracion'); ?>
                </p>
            </div>
        </div>

        <?php submit_button(__('Guardar configuración', '40-horas-oracion')); ?>
    </form>
</div>
