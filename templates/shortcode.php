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
// Calculate end day: if 40 hours starting at 08:00, it usually ends the next day at 24:00 (day 15), meaning 14 to 16 inclusive for a 3-day vigilia.
$current_month_str = wp_date('F \d\e Y');
$dates_str = sprintf('%d al %d de %s', $start_day, $start_day + 2, $current_month_str);

?>

<div class="horas-oracion-container">
    
    <!-- Header / Intro -->
    <div class="horas-oracion-intro">
        <!-- SVG Custodia Placeholder -->
        <div class="ho-icon-custodia">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="4"></line>
                <line x1="12" y1="20" x2="12" y2="23"></line>
                <line x1="1" y1="12" x2="4" y2="12"></line>
                <line x1="20" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="4.22" x2="6.34" y2="6.34"></line>
                <line x1="17.66" y1="17.66" x2="19.78" y2="19.78"></line>
                <line x1="4.22" y1="19.78" x2="6.34" y2="17.66"></line>
                <line x1="17.66" y1="4.22" x2="19.78" y2="6.34"></line>
            </svg>
        </div>
        
        <h2 class="ho-main-title"><?php esc_html_e('40 Horas de Oración', '40-horas-oracion'); ?></h2>
        <h3 class="ho-subtitle"><?php printf(esc_html__('%d, %d y %d de cada mes', '40-horas-oracion'), $start_day, $start_day+1, $start_day+2); ?></h3>
        
        <?php if (!empty($intro_text)): ?>
            <div class="ho-intro-text"><?php echo wpautop(esc_html($intro_text)); ?></div>
        <?php endif; ?>
        
        <div class="ho-intro-note">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#5b3b8c" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            <span><?php esc_html_e('Una hora de tu oración puede cambiar muchas vidas.', '40-horas-oracion'); ?></span>
        </div>
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
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
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
                <p><?php esc_html_e('Estos son los horarios ya cubiertos por otras mujeres que oran.', '40-horas-oracion'); ?></p>
            </div>
        </div>

        <div class="table-body">
            <?php foreach ($hours_structure as $num => $hour): ?>
                <div class="ho-hour-row">
                    <div class="ho-hour-header">
                        <h4>
                            <?php printf(
                                esc_html__('Hora %1$d — Día %2$d — %3$s', '40-horas-oracion'),
                                $num,
                                $hour['dia'],
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
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="table-footer-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#5b3b8c" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            <span><?php esc_html_e('Gracias por ser parte de esta cadena de oración.', '40-horas-oracion'); ?></span>
        </div>
    </div>
    <?php endif; ?>
</div>
