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
        var form = $('#horas-oracion-form');
        
        if (form.length === 0) {
            return;
        }

        // Form submission
        form.on('submit', function(e) {
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
        if ($('#g-recaptcha-response').length > 0) {
            formData.recaptcha_token = $('#g-recaptcha-response').val();
        }

        // Add Turnstile if present
        if ($('[name="cf-turnstile-response"]').length > 0) {
            formData.turnstile_token = $('[name="cf-turnstile-response"]').val();
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
                submitBtn.html('Inscribirme');
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
                    if ($('.horas-oracion-table-wrapper').length > 0) {
                        var scheduleHtml = '';
                        
                        $.each(data.registrations, function(index, hourGroup) {
                            scheduleHtml += '<div class="horas-oracion-hours-group">';
                            scheduleHtml += '<div class="horas-oracion-hours-group-header">';
                            scheduleHtml += '<h4>Hora ' + hourGroup.numero_hora + ' — Día ' + hourGroup.dia + ' — ' + hourGroup.hora + '</h4>';
                            scheduleHtml += '</div>';
                            
                            scheduleHtml += '<ul class="horas-oracion-participants-list">';
                            
                            if (hourGroup.participants && hourGroup.participants.length > 0) {
                                $.each(hourGroup.participants, function(i, participant) {
                                    scheduleHtml += '<li>';
                                    scheduleHtml += '<span class="horas-oracion-participant-name">' + participant.nombre + ' ' + participant.apellido + '</span>';
                                    scheduleHtml += '<span class="horas-oracion-participant-location">' + participant.ciudad + ', ' + participant.pais + '</span>';
                                    scheduleHtml += '</li>';
                                });
                            } else {
                                scheduleHtml += '<li class="horas-oracion-empty-state"><p>Aún no hay inscritos en este horario.</p></li>';
                            }
                            
                            scheduleHtml += '</ul></div>';
                        });
                        
                        // Reemplazar todo el contenido del contenedor de la tabla
                        // Seleccionamos el div que contiene todos los groups
                        $('.horas-oracion-table-wrapper > div:last-child').html(scheduleHtml);
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
