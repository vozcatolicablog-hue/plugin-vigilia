/**
 * 40 Horas de Oración - Frontend JavaScript
 * Handles form submission, AJAX, and UI interactions
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        bindFormEvents();
        
        // Si hay una tabla en la página, refrescarla asíncronamente al cargar
        // Esto soluciona problemas con plugins de caché (WP Rocket, LiteSpeed, etc.)
        if ($('.horas-oracion-table-wrapper').length > 0) {
            refreshTable();
        }
    });

    /**
     * Bind form events
     */
    function bindFormEvents() {
        var forms = $('.horas-oracion-form');
        
        if (forms.length === 0) {
            return;
        }

        // Form submission
        forms.on('submit', function(e) {
            e.preventDefault();
            submitForm($(this));
        });
    }

    /**
     * Submit form via AJAX
     */
    function submitForm(form) {
        var submitBtn = form.find('button[type="submit"]');
        var messageContainer = form.find('.form-message');
        
        // Clear previous messages
        messageContainer.html('');
        
        // Validate fields
        var nombre = form.find('[name="nombre"]').val();
        var apellido = form.find('[name="apellido"]').val();
        var ciudad = form.find('[name="ciudad"]').val();
        var pais = form.find('[name="pais"]').val();
        var numero_hora = form.find('[name="numero_hora"]').val();

        if (!nombre || !nombre.trim() || !apellido || !apellido.trim() || 
            !ciudad || !ciudad.trim() || !pais || !pais.trim() || !numero_hora) {
            showMessage('Por favor complete todos los campos requeridos.', 'error', messageContainer);
            return;
        }

        // Disable submit button
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="horas-oracion-spinner"></span> Enviando...');

        // Prepare data
        var formData = {
            action: 'horas_oracion_submit_registration',
            nonce: horasOracion.nonce,
            nombre: nombre.trim(),
            apellido: apellido.trim(),
            ciudad: ciudad.trim(),
            pais: pais.trim(),
            numero_hora: numero_hora
        };

        // Add reCAPTCHA if present
        var recaptchaResponse = form.find('[name="g-recaptcha-response"]');
        if (recaptchaResponse.length > 0) {
            formData.recaptcha_token = recaptchaResponse.val();
        }

        // Add Turnstile if present
        var turnstileResponse = form.find('[name="cf-turnstile-response"]');
        if (turnstileResponse.length > 0) {
            formData.turnstile_token = turnstileResponse.val();
        }

        // Send AJAX request
        $.ajax({
            url: horasOracion.ajaxUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage('✓ ' + response.data.message, 'success', messageContainer);
                    
                    // Reset form
                    form[0].reset();
                    
                    // Actualizar tabla y contadores por AJAX sin recargar la página
                    refreshTable();
                    
                    // Si hay captcha, resetearlo
                    if (typeof grecaptcha !== 'undefined' && $('.g-recaptcha').length > 0) {
                        grecaptcha.reset();
                    }
                    if (typeof turnstile !== 'undefined' && $('.cf-turnstile').length > 0) {
                        turnstile.reset();
                    }
                } else {
                    showMessage('✗ ' + (response.data && response.data.message ? response.data.message : 'Error desconocido'), 'error', messageContainer);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr.responseText);
                showMessage('✗ Error al procesar la solicitud. Por favor intente nuevamente.', 'error', messageContainer);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html('<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg> Inscribirme');
            }
        });
    }

    /**
     * Refresca la tabla y los contadores vía AJAX para saltarse la caché de la página
     */
    function refreshTable() {
        $.ajax({
            url: horasOracion.ajaxUrl,
            type: 'POST',
            data: {
                action: 'horas_oracion_get_registrations',
                nonce: horasOracion.nonce
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // 1. Actualizar contadores si existen
                    if ($('.horas-oracion-counters').length > 0) {
                        var counters = $('.counter-value');
                        if (counters.length >= 2) {
                            $(counters[0]).text(data.current_month_count);
                            $(counters[1]).text(data.historical_count);
                        }
                    }
                    
                    // 2. Reconstruir la tabla de participantes si existe
                    if ($('.table-body').length > 0) {
                        var scheduleHtml = '';
                        
                        $.each(data.registrations, function(index, hourGroup) {
                            scheduleHtml += '<div class="ho-hour-row">';
                            scheduleHtml += '<div class="ho-hour-header">';
                            scheduleHtml += '<h4>Hora ' + hourGroup.numero_hora + ' — Día ' + hourGroup.dia + ' — ' + hourGroup.hora + '</h4>';
                            scheduleHtml += '</div>';
                            
                            if (hourGroup.participants && hourGroup.participants.length > 0) {
                                scheduleHtml += '<ul class="ho-participants-list">';
                                $.each(hourGroup.participants, function(i, participant) {
                                    scheduleHtml += '<li>';
                                    scheduleHtml += '<div class="ho-p-name">';
                                    scheduleHtml += '<div class="p-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>';
                                    scheduleHtml += '<span>' + participant.nombre + ' ' + participant.apellido + '</span>';
                                    scheduleHtml += '</div>';
                                    scheduleHtml += '<div class="ho-p-location">' + participant.ciudad + ', ' + participant.pais + '</div>';
                                    var dateStr = participant.created_at ? participant.created_at : '';
                                    scheduleHtml += '<div class="ho-p-date">' + dateStr + '</div>';
                                    scheduleHtml += '</li>';
                                });
                                scheduleHtml += '</ul>';
                            }
                            scheduleHtml += '</div>';
                        });
                        
                        // Reemplazar todo el contenido del contenedor de la tabla
                        $('.table-body').html(scheduleHtml);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching updated table:', error);
            }
        });
    }

    /**
     * Show message
     */
    function showMessage(message, type, container) {
        var html = '<div class="horas-oracion-message message-' + type + '">' +
                   '<p>' + message + '</p>' +
                   '</div>';

        container.html(html);

        // Scroll to message
        $('html, body').animate({
            scrollTop: container.offset().top - 100
        }, 300);

        // Auto-remove success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                container.fadeOut(300, function() {
                    $(this).html('').show();
                });
            }, 5000);
        }
    }

})(jQuery);
