/**
 * 40 Horas de Oración - Admin JavaScript
 * Handles admin panel interactions
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        bindAdminEvents();
    });

    /**
     * Bind admin events
     */
    function bindAdminEvents() {
        // Delete registration
        $(document).on('click', '.horas-oracion-delete-btn', function(e) {
            e.preventDefault();
            deleteRegistration($(this));
        });

        // Export month CSV
        $(document).on('click', '.horas-oracion-export-btn', function(e) {
            e.preventDefault();
            exportMonthCsv($(this));
        });

        // Show success/error messages
        showNotices();
    }

    /**
     * Delete registration
     */
    function deleteRegistration(btn) {
        const registrationId = btn.data('registration-id');
        
        if (!confirm(horasOracionAdmin.confirmDelete)) {
            return;
        }

        btn.prop('disabled', true);
        const originalText = btn.text();
        btn.text('Eliminando...');

        $.ajax({
            url: horasOracionAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'horas_oracion_delete_registration',
                nonce: horasOracionAdmin.nonce,
                registration_id: registrationId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Remove row from table
                    btn.closest('tr').fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    showAdminNotice(response.data.message, 'success');
                } else {
                    showAdminNotice(response.data.message, 'error');
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                showAdminNotice('Error al eliminar el registro.', 'error');
                btn.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Export month CSV
     */
    function exportMonthCsv(btn) {
        const month = btn.data('month');
        
        btn.prop('disabled', true);
        const originalText = btn.text();
        btn.text('Exportando...');

        $.ajax({
            url: horasOracionAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'horas_oracion_export_month_csv',
                nonce: horasOracionAdmin.nonce,
                month: month
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAdminNotice('CSV exportado correctamente.', 'success');
                    btn.prop('disabled', false).text(originalText);
                } else {
                    showAdminNotice(response.data.message, 'error');
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                showAdminNotice('Error al exportar CSV.', 'error');
                btn.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Show admin notice
     */
    function showAdminNotice(message, type) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = $('<div>').addClass('notice').addClass(noticeClass).addClass('is-dismissible')
            .html('<p>' + message + '</p>');
        
        // Find the correct container for notices
        let container = $('.horas-oracion-notice-container');
        if (container.length === 0) {
            container = $('h1').first().after('<div class="horas-oracion-notice-container"></div>')
                .next('.horas-oracion-notice-container');
        }
        
        container.prepend(notice);
        
        // Add dismissible functionality
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Show existing notices (success, error messages)
     */
    function showNotices() {
        if (window.location.search.indexOf('deleted=1') !== -1) {
            showAdminNotice('Registro eliminado correctamente.', 'success');
        }

        if (window.location.search.indexOf('saved=1') !== -1) {
            showAdminNotice('Configuración guardada correctamente.', 'success');
        }

        if (window.location.search.indexOf('exported=1') !== -1) {
            showAdminNotice('CSV exportado correctamente.', 'success');
        }

        if (window.location.search.indexOf('error=1') !== -1) {
            showAdminNotice('Error al procesar la solicitud.', 'error');
        }
    }

})(jQuery);
