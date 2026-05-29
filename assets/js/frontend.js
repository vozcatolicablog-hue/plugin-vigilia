/**
 * 40 Horas de Oración - Frontend JavaScript
 * Handles form submission, AJAX, and UI interactions
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        bindFormEvents();
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
                    
                    // Reload page after 2 seconds to show updated data
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
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
