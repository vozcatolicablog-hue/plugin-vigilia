<?php
/**
 * Shortcode Template
 * 
 * @package HorasOracion
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get dynamic dates for the counters
$start_day = get_option('horas_oracion_start_day', 14);
$duration = get_option('horas_oracion_duration_hours', 40);

// Calculate target timestamp to determine the month
$end_day_approx = $start_day + ceil($duration / 24);
$current_day = (int) wp_date('j');
$target_timestamp = time();

if ($current_day > $end_day_approx) {
    $target_timestamp = strtotime('first day of next month');
}

$current_month_str = wp_date('F \d\e Y', $target_timestamp);
$dates_str = sprintf('%d al %d de %s', $start_day, $start_day + 2, $current_month_str);

$target_month = wp_date('F', $target_timestamp);
$next_month = wp_date('F', strtotime('+1 month', $target_timestamp));

// Calculate color variants based on primary color
$primary_color = isset($primary_color) ? $primary_color : '#5b3b8c';
$hex = ltrim($primary_color, '#');
if (strlen($hex) == 3) {
    $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
    $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
    $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
} else {
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
}
$primary_light = sprintf('rgba(%d, %d, %d, 0.15)', $r, $g, $b);
$hover_r = max(0, round($r * 0.8));
$hover_g = max(0, round($g * 0.8));
$hover_b = max(0, round($b * 0.8));
$primary_hover = sprintf('rgb(%d, %d, %d)', $hover_r, $hover_g, $hover_b);
?>

<style>
.horas-oracion-container {
    --ho-primary: <?php echo esc_attr($primary_color); ?>;
    --ho-primary-light: <?php echo esc_attr($primary_light); ?>;
    --ho-primary-hover: <?php echo esc_attr($primary_hover); ?>;
}
</style>

<div class="horas-oracion-container">
    
    <!-- Header / Intro -->
    <div class="horas-oracion-intro">   
        <h2 class="ho-main-title"><?php esc_html_e('40 Horas de Oración', '40-horas-oracion'); ?></h2>
        <h3 class="ho-subtitle"><?php printf(esc_html__('%d, %d y %d de cada mes', '40-horas-oracion'), $start_day, $start_day+1, $start_day+2); ?></h3>
        
        <?php if (!empty($intro_text)): ?>
            <div class="ho-intro-text"><?php echo wpautop(esc_html($intro_text)); ?></div>
        <?php endif; ?>
    </div>

    <!-- Counters -->
    <?php if ($atts['show_counters'] === 'true'): ?>
    <div class="horas-oracion-counters">
        <div class="counter-card">
            <div class="counter-icon bg-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="counter-content">
                <h3><?php esc_html_e('Personas anotadas este mes', '40-horas-oracion'); ?></h3>
                <p class="counter-value"><?php echo absint($current_month_count); ?></p>
                <p class="counter-date">(<?php echo esc_html($dates_str); ?>)</p>
            </div>
        </div>
        <div class="counter-card">
            <div class="counter-icon bg-yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="counter-content">
                <h3><?php esc_html_e('Total histórico de participantes', '40-horas-oracion'); ?></h3>
                <p class="counter-value"><?php echo number_format_i18n(absint($historical_count)); ?></p>
                <p class="counter-date"><?php esc_html_e('Desde el inicio', '40-horas-oracion'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <?php if ($atts['show_form'] === 'true'): ?>
    <form class="horas-oracion-form">
        <div class="form-header">
            <div class="icon-calendar bg-purple-light">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <h3><?php esc_html_e('Inscribite para orar una hora', '40-horas-oracion'); ?></h3>
        </div>

        <div class="form-row">
            <div class="form-group">
                <input type="text" name="nombre" placeholder="<?php esc_attr_e('Nombre *', '40-horas-oracion'); ?>" required>
            </div>
            <div class="form-group">
                <input type="text" name="apellido" placeholder="<?php esc_attr_e('Apellido *', '40-horas-oracion'); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <input type="text" name="ciudad" placeholder="<?php esc_attr_e('Ciudad *', '40-horas-oracion'); ?>" required>
            </div>
            <div class="form-group">
                <input type="text" name="pais" placeholder="<?php esc_attr_e('País *', '40-horas-oracion'); ?>" required>
            </div>
        </div>
        <div class="form-group select-wrapper">
            <span class="select-label"><?php esc_html_e('Hora de oración *', '40-horas-oracion'); ?></span>
            <select name="numero_hora" required>
                <!-- Keep a blank hidden option so it's required to change -->
                <option value="" disabled selected hidden></option>
                <?php foreach ($hours_structure as $num => $hour): ?>
                    <option value="<?php echo esc_attr($num); ?>">
                        <?php printf(
                            esc_html__('Hora %1$d — %2$d de %3$s — %4$s', '40-horas-oracion'),
                            $num,
                            $hour['dia'],
                            ($hour['dia'] < $start_day) ? $next_month : $target_month,
                            $hour['hora']
                        ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="horas-oracion-captcha">
            <?php 
            if ($captcha_type === 'recaptcha' && !empty($recaptcha_site_key)): 
            ?>
                <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($recaptcha_site_key); ?>"></div>
            <?php 
            elseif ($captcha_type === 'turnstile' && !empty($turnstile_site_key)): 
            ?>
                <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($turnstile_site_key); ?>"></div>
            <?php 
            endif; 
            ?>
        </div>

        <div class="form-message"></div>

        <button type="submit" class="horas-oracion-btn btn-primary btn-full">
            <?php esc_html_e('Inscribirme', '40-horas-oracion'); ?>
        </button>
        <p class="form-footer-note"><?php esc_html_e('Al inscribirte, estás ofreciendo una hora de oración por las vocaciones.', '40-horas-oracion'); ?></p>
    </form>
    <?php endif; ?>

    <!-- Table -->
    <?php if ($atts['show_table'] === 'true'): ?>
    <div class="horas-oracion-table-wrapper">
        <div class="table-header-main">
            <div class="icon-users bg-purple-light">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <h3><?php esc_html_e('Horarios y personas anotadas', '40-horas-oracion'); ?></h3>
            </div>
        </div>

        <div class="table-body">
            <?php foreach ($hours_structure as $num => $hour): ?>
                <div class="ho-hour-row">
                    <div class="ho-hour-header">
                        <h4>
                            <?php printf(
                                esc_html__('Hora %1$d — %2$d de %3$s — %4$s', '40-horas-oracion'),
                                $num,
                                $hour['dia'],
                                ($hour['dia'] < $start_day) ? $next_month : $target_month,
                                $hour['hora']
                            ); ?>
                        </h4>
                    </div>
                    <?php if (isset($registrations[$num]) && !empty($registrations[$num]['participants'])): ?>
                        <ul class="ho-participants-list">
                            <?php foreach ($registrations[$num]['participants'] as $p): ?>
                            <li>
                                <div class="ho-p-name">
                                    <div class="p-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <span><?php echo esc_html($p->nombre . ' ' . $p->apellido); ?></span>
                                </div>
                                <div class="ho-p-location">
                                    <?php echo esc_html($p->ciudad . ', ' . $p->pais); ?>
                                </div>
                                <div class="ho-p-date">
                                    <?php 
                                    if (isset($p->created_at)) {
                                        echo esc_html(wp_date('d \d\e F \d\e Y - H:i', strtotime($p->created_at)));
                                    }
                                    ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="ho-empty-hour" style="padding: 1.5rem; text-align: center; color: var(--ho-text-light); font-size: 0.9rem; font-style: italic;">
                            <?php esc_html_e('Aún no hay personas anotadas en esta hora. ¡Animate a ser la primera!', '40-horas-oracion'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="table-footer-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--ho-primary)" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            <span><?php esc_html_e('Gracias por ser parte de esta cadena de oración.', '40-horas-oracion'); ?></span>
        </div>
    </div>
    <?php endif; ?>
</div>
