jQuery(document).ready(function($) {
    $('#support-ticket-form').on('submit', function(e) {
        e.preventDefault();
        var subject = $('#ticket-subject').val();
        var content = $('#ticket-content').val();
        var entity = $('#ticket-entity').val();
        var unit = $('#ticket-unit').val();

        $.ajax({
            url: wpsts_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsts_create_ticket',
                subject: subject,
                content: content,
                entity: entity,
                unit: unit,
                nonce: wpsts_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#ticket-message').html('<p class="success">' + response.data + '</p>');
                    $('#support-ticket-form')[0].reset();
                } else {
                    $('#ticket-message').html('<p class="error">' + response.data + '</p>');
                }
            }
        });
    });

    // Filtrar unidades basadas en la entidad seleccionada
    $('#ticket-entity').on('change', function() {
        var selectedEntityId = $(this).val();
        $('#ticket-unit option').hide();
        $('#ticket-unit option[data-entity="' + selectedEntityId + '"]').show();
        $('#ticket-unit').val('');
    });
});