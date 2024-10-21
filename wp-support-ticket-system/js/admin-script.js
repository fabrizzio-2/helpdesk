jQuery(document).ready(function($) {
    // Funcionalidad para editar entidades
    $('.edit-entity').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');

        $('input[name="wpsts_entity_action"]').val('edit');
        $('input[name="entity_id"]').val(id);
        $('input[name="entity_name"]').val(name);
        $('textarea[name="entity_description"]').val(description);
        $('#submit').val('Actualizar Entidad');
    });

    // Funcionalidad para editar unidades
    $('.edit-unit').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var entity = $(this).data('entity');

        $('input[name="wpsts_unit_action"]').val('edit');
        $('input[name="unit_id"]').val(id);
        $('input[name="unit_name"]').val(name);
        $('textarea[name="unit_description"]').val(description);
        $('select[name="unit_entity"]').val(entity);
        $('#submit').val('Actualizar Unidad');
    });

    // Funcionalidad para asignar unidades a usuarios
    $('.assign-unit').click(function(e) {
        e.preventDefault();
        var userId = $(this).data('user-id');
        $('#modal-user-id').val(userId);
        $('#assign-unit-modal').dialog({
            title: 'Asignar Unidad a Usuario',
            modal: true,
            width: 400
        });
    });
});