/**
 * 40 Horas de Oración - Frontend JavaScript
 * Handles form submission, AJAX, and UI interactions
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeForm();
        bindFormEvents();
        bindTableEvents();
    });

    /**
     * Initialize form
     */
    function initializeForm() {
        // Populate hours on page load
        populateHours();
    }

    /**
     * Populate hours select dropdown
     */
    function populateHours() {
        const hoursSelect = $('#ho_numero_hora');
        
        if (hoursSelect.length === 0) {
            return;
        }

        const hours = generateHours();
        
        // Clear existing options
        hoursSelect.html('<option value="">-- Seleccionar Hora --</option>');
        
        hours.forEach(function(hour) {
            const optionText = 'Hora ' + hour.numero + ' — Día ' + hour.dia + ' — ' + hour.hora;
            hoursSelect.append(
                $('<option>').attr('value', hour.numero).text(optionText)
            );
        });
    }

    /**
     * Generate 40 hours array
     */
    function generateHours() {
        const hours = [];
        let hourCounter = 1;
        
        // Days 14, 15, 16
        for (let day = 14; day <= 16; day++) {
            for (let hour = 0; hour < (day === 16 ? 8 : 16); hour++) {
                if (hourCounter > 40) break;
                
                hours.push({
                    numero: hourCounter,
                    dia: day,
                    hora: String(8 + hour).padStart(2, '0') + ':00'
                });
                hourCounter++;
            }
            
            if (hourCounter > 40) break;
        }
        
        return hours;
    }

    /**
     * Bind form events
     */
    function bindFormEvents() {
        const form = $('#horas-oracion-form');
        
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
        const submitBtn = form.find('button[type="submit"]');
        const messageContainer = $('.form-message');
        
        // Clear previous messages
        messageContainer.html('');
        
        // Validate fields
        if (!validateForm(form)) {
            showMessage('Por favor complete todos los campos requeridos.', 'error', messageContainer);
            return;
        }

        // Disable submit button
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="horas-oracion-spinner"></span> Enviando...');

        // Prepare data
        const formData = {
            action: 'horas_oracion_submit_registration',
            nonce: horasOracion.nonce,
            nombre: form.find('[name="nombre"]').val(),
            apellido: form.find('[name="apellido"]').val(),
            ciudad: form.find('[name="ciudad"]').val(),
            pais: form.find('[name="pais"]').val(),
            numero_hora: form.find('[name="numero_hora"]').val()
        };

        // Add reCAPTCHA if present
        if ($('#g-recaptcha-response').length > 0) {
            formData.recaptcha_token = $('#g-recaptcha-response').val();
        }

        // Add Turnstile if present
        if (typeof turnstile !== 'undefined') {
            formData.turnstile_token = turnstile.getResponse();
        }

        // Send AJAX request
        $.ajax({
            url: horasOracion.ajaxUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                handleFormSuccess(response, form, messageContainer);
            },
            error: function(xhr, status, error) {
                handleFormError(error, messageContainer, submitBtn);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html('Inscribirse');
            }
        });
    }

    /**
     * Validate form
     */
    function validateForm(form) {
        const nombre = form.find('[name="nombre"]').val().trim();
        const apellido = form.find('[name="apellido"]').val().trim();
        const ciudad = form.find('[name="ciudad"]').val().trim();
        const pais = form.find('[name="pais"]').val().trim();
        const numero_hora = form.find('[name="numero_hora"]').val();

        if (!nombre || !apellido || !ciudad || !pais || !numero_hora) {
            return false;
        }

        // Validate name length
        if (nombre.length > 100 || apellido.length > 100) {
            return false;
        }

        return true;
    }

    /**
     * Handle successful form submission
     */
    function handleFormSuccess(response, form, messageContainer) {
        if (response.success) {
            showMessage(
                '✓ ' + response.data.message,
                'success',
                messageContainer
            );

            // Reset form
            form[0].reset();
            populateHours();

            // Refresh table if it exists
            refreshTable();

            // Update counters
            updateCounters();
            
            // Reload page to show new data
            setTimeout(function() {
                window.location.reload();
            }, 2000);
        } else {
            showMessage(
                '✗ ' + response.data.message,
                'error',
                messageContainer
            );
        }
    }

    /**
     * Handle form error
     */
    function handleFormError(error, messageContainer, submitBtn) {
        console.error('Form submission error:', error);
        showMessage(
            '✗ Error al procesar la solicitud. Por favor intente nuevamente.',
            'error',
            messageContainer
        );
    }

    /**
     * Refresh table
     */
    function refreshTable() {
        // Data refresh will happen via page reload
    }

    /**
     * Update counters
     */
    function updateCounters() {
        // Data refresh will happen via page reload
    }

    /**
     * Bind table events
     */
    function bindTableEvents() {
        // Any interactive table events can go here
    }

    /**
     * Show message
     */
    function showMessage(message, type, container) {
        const iconMap = {
            'success': '✓',
            'error': '✗',
            'warning': '⚠',
            'info': 'ℹ'
        };

        const html = `
            <div class="horas-oracion-message message-${type}">
                <span class="message-icon">${iconMap[type] || '•'}</span>
                <p>${message}</p>
            </div>
        `;

        container.html(html);

        // Auto-remove success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                container.fadeOut(300, function() {
                    $(this).html('').show();
                });
            }, 5000);
        }
    }

    // Expose public functions for external use
    window.horasOracionFrontend = window.horasOracionFrontend || {};
    window.horasOracionFrontend.generateHours = generateHours;
    window.horasOracionFrontend.refreshTable = refreshTable;
    window.horasOracionFrontend.updateCounters = updateCounters;

})(jQuery);
