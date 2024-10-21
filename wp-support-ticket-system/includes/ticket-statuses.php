<?php
// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

function wpsts_manage_ticket_statuses() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpsts_ticket_statuses';

    // Procesar formulario de creación/edición
    if (isset($_POST['wpsts_status_action']) && check_admin_referer('wpsts_status_action')) {
        $name = sanitize_text_field($_POST['status_name']);
        $description = sanitize_textarea_field($_POST['status_description']);

        if ($_POST['wpsts_status_action'] === 'create') {
            $wpdb->insert($table_name, array('name' => $name, 'description' => $description));
            echo '<div class="updated"><p>Estado de ticket creado con éxito.</p></div>';
        } elseif ($_POST['wpsts_status_action'] === 'edit') {
            $id = intval($_POST['status_id']);
            $wpdb->update($table_name, array('name' => $name, 'description' => $description), array('id' => $id));
            echo '<div class="updated"><p>Estado de ticket actualizado con éxito.</p></div>';
        }
    }

    // Procesar eliminación
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($table_name, array('id' => $id));
        echo '<div class="updated"><p>Estado de ticket eliminado con éxito.</p></div>';
    }

    // Obtener todos los estados de tickets
    $statuses = $wpdb->get_results("SELECT * FROM $table_name");

    // Mostrar formulario y lista de estados de tickets
    ?>
    <div class="wrap">
        <h1>Gestionar Estados de Tickets</h1>
        
        <h2>Agregar/Editar Estado de Ticket</h2>
        <form method="post" action="">
            <?php wp_nonce_field('wpsts_status_action'); ?>
            <input type="hidden" name="wpsts_status_action" value="create">
            <input type="hidden" name="status_id" value="">
            <table class="form-table">
                <tr>
                    <th><label for="status_name">Nombre</label></th>
                    <td><input type="text" name="status_name" id="status_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="status_description">Descripción</label></th>
                    <td><textarea name="status_description" id="status_description" rows="5" cols="50"></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Estado">
            </p>
        </form>

        <h2>Estados de Tickets Existentes</h2>
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
                <?php foreach ($statuses as $status): ?>
                    <tr>
                        <td><?php echo esc_html($status->id); ?></td>
                        <td><?php echo esc_html($status->name); ?></td>
                        <td><?php echo esc_html($status->description); ?></td>
                        <td>
                            <a href="#" class="edit-status" data-id="<?php echo esc_attr($status->id); ?>" data-name="<?php echo esc_attr($status->name); ?>" data-description="<?php echo esc_attr($status->description); ?>">Editar</a> |
                            <a href="<?php echo wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $status->id)), 'delete-status'); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este estado de ticket?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.edit-status').click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');

            $('input[name="wpsts_status_action"]').val('edit');
            $('input[name="status_id"]').val(id);
            $('input[name="status_name"]').val(name);
            $('textarea[name="status_description"]').val(description);
            $('#submit').val('Actualizar Estado');
        });
    });
    </script>
    <?php
}