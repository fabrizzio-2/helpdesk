<?php
// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

function wpsts_manage_units() {
    global $wpdb;
    $units_table = $wpdb->prefix . 'wpsts_units';

    // Procesar formulario de creación/edición
    if (isset($_POST['wpsts_unit_action']) && check_admin_referer('wpsts_unit_action')) {
        $name = sanitize_text_field($_POST['unit_name']);
        $description = sanitize_textarea_field($_POST['unit_description']);

        if ($_POST['wpsts_unit_action'] === 'create') {
            $wpdb->insert($units_table, array('name' => $name, 'description' => $description));
            echo '<div class="updated"><p>Unidad creada con éxito.</p></div>';
        } elseif ($_POST['wpsts_unit_action'] === 'edit') {
            $id = intval($_POST['unit_id']);
            $wpdb->update($units_table, array('name' => $name, 'description' => $description), array('id' => $id));
            echo '<div class="updated"><p>Unidad actualizada con éxito.</p></div>';
        }
    }

    // Procesar eliminación
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($units_table, array('id' => $id));
        echo '<div class="updated"><p>Unidad eliminada con éxito.</p></div>';
    }

    // Obtener todas las unidades
    $units = $wpdb->get_results("SELECT * FROM $units_table");

    // Mostrar formulario y lista de unidades
    ?>
    <div class="wrap">
        <h1>Gestionar Unidades</h1>
        
        <h2>Agregar/Editar Unidad</h2>
        <form method="post" action="">
            <?php wp_nonce_field('wpsts_unit_action'); ?>
            <input type="hidden" name="wpsts_unit_action" value="create">
            <input type="hidden" name="unit_id" value="">
            <table class="form-table">
                <tr>
                    <th><label for="unit_name">Nombre</label></th>
                    <td><input type="text" name="unit_name" id="unit_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="unit_description">Descripción</label></th>
                    <td><textarea name="unit_description" id="unit_description" rows="5" cols="50"></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Unidad">
            </p>
        </form>

        <h2>Unidades Existentes</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($units as $unit): ?>
                    <tr>
                        <td><?php echo esc_html($unit->id); ?></td>
                        <td><?php echo esc_html($unit->name); ?></td>
                        <td><?php echo esc_html($unit->description); ?></td>
                        <td>
                            <a href="#" class="edit-unit" 
                               data-id="<?php echo esc_attr($unit->id); ?>" 
                               data-name="<?php echo esc_attr($unit->name); ?>" 
                               data-description="<?php echo esc_attr($unit->description); ?>">Editar</a> |
                            <a href="<?php echo wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $unit->id)), 'delete-unit'); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar esta unidad?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.edit-unit').click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');

            $('input[name="wpsts_unit_action"]').val('edit');
            $('input[name="unit_id"]').val(id);
            $('input[name="unit_name"]').val(name);
            $('textarea[name="unit_description"]').val(description);
            $('#submit').val('Actualizar Unidad');
        });
    });
    </script>
    <?php
}