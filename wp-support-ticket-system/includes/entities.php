<?php
// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

function wpsts_manage_entities() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpsts_entities';

    // Procesar formulario de creación/edición
    if (isset($_POST['wpsts_entity_action']) && check_admin_referer('wpsts_entity_action')) {
        $name = sanitize_text_field($_POST['entity_name']);
        $description = sanitize_textarea_field($_POST['entity_description']);

        if ($_POST['wpsts_entity_action'] === 'create') {
            $wpdb->insert($table_name, array('name' => $name, 'description' => $description));
            echo '<div class="updated"><p>Entidad creada con éxito.</p></div>';
        } elseif ($_POST['wpsts_entity_action'] === 'edit') {
            $id = intval($_POST['entity_id']);
            $wpdb->update($table_name, array('name' => $name, 'description' => $description), array('id' => $id));
            echo '<div class="updated"><p>Entidad actualizada con éxito.</p></div>';
        }
    }

    // Procesar eliminación
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($table_name, array('id' => $id));
        echo '<div class="updated"><p>Entidad eliminada con éxito.</p></div>';
    }

    // Obtener todas las entidades
    $entities = $wpdb->get_results("SELECT * FROM $table_name");

    // Mostrar formulario y lista de entidades
    ?>
    <div class="wrap">
        <h1>Gestionar Entidades</h1>
        
        <h2>Agregar/Editar Entidad</h2>
        <form method="post" action="">
            <?php wp_nonce_field('wpsts_entity_action'); ?>
            <input type="hidden" name="wpsts_entity_action" value="create">
            <input type="hidden" name="entity_id" value="">
            <table class="form-table">
                <tr>
                    <th><label for="entity_name">Nombre</label></th>
                    <td><input type="text" name="entity_name" id="entity_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="entity_description">Descripción</label></th>
                    <td><textarea name="entity_description" id="entity_description" rows="5" cols="50"></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Entidad">
            </p>
        </form>

        <h2>Entidades Existentes</h2>
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
                <?php foreach ($entities as $entity): ?>
                    <tr>
                        <td><?php echo esc_html($entity->id); ?></td>
                        <td><?php echo esc_html($entity->name); ?></td>
                        <td><?php echo esc_html($entity->description); ?></td>
                        <td>
                            <a href="#" class="edit-entity" data-id="<?php echo esc_attr($entity->id); ?>" data-name="<?php echo esc_attr($entity->name); ?>" data-description="<?php echo esc_attr($entity->description); ?>">Editar</a> |
                            <a href="<?php echo wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $entity->id)), 'delete-entity'); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar esta entidad?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
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
    });
    </script>
    <?php
}