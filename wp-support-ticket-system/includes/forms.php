<?php
// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

function wpsts_manage_forms() {
    global $wpdb;
    $forms_table = $wpdb->prefix . 'wpsts_forms';

    // Procesar formulario de creación/edición
    if (isset($_POST['wpsts_form_action']) && check_admin_referer('wpsts_form_action')) {
        $name = sanitize_text_field($_POST['form_name']);
        $description = sanitize_textarea_field($_POST['form_description']);
        $form_data = wp_json_encode($_POST['form_data']);

        if ($_POST['wpsts_form_action'] === 'create') {
            $wpdb->insert($forms_table, array(
                'name' => $name,
                'description' => $description,
                'form_data' => $form_data
            ));
            echo '<div class="updated"><p>Formulario creado con éxito.</p></div>';
        } elseif ($_POST['wpsts_form_action'] === 'edit') {
            $id = intval($_POST['form_id']);
            $wpdb->update($forms_table, array(
                'name' => $name,
                'description' => $description,
                'form_data' => $form_data
            ), array('id' => $id));
            echo '<div class="updated"><p>Formulario actualizado con éxito.</p></div>';
        }
    }

    // Procesar eliminación
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($forms_table, array('id' => $id));
        echo '<div class="updated"><p>Formulario eliminado con éxito.</p></div>';
    }

    // Obtener todos los formularios
    $forms = $wpdb->get_results("SELECT * FROM $forms_table ORDER BY created_at DESC");

    // Mostrar lista de formularios y formulario de creación/edición
    ?>
    <div class="wrap">
        <h1>Gestionar Formularios</h1>
        
        <h2>Crear/Editar Formulario</h2>
        <form method="post" action="">
            <?php wp_nonce_field('wpsts_form_action'); ?>
            <input type="hidden" name="wpsts_form_action" value="create">
            <input type="hidden" name="form_id" value="">
            <table class="form-table">
                <tr>
                    <th><label for="form_name">Nombre del Formulario</label></th>
                    <td><input type="text" name="form_name" id="form_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="form_description">Descripción</label></th>
                    <td><textarea name="form_description" id="form_description" rows="5" cols="50"></textarea></td>
                </tr>
                <tr>
                    <th><label for="form_data">Datos del Formulario (JSON)</label></th>
                    <td><textarea name="form_data" id="form_data" rows="10" cols="50" required></textarea></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Formulario">
            </p>
        </form>

        <h2>Formularios Existentes</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $form): ?>
                    <tr>
                        <td><?php echo esc_html($form->id); ?></td>
                        <td><?php echo esc_html($form->name); ?></td>
                        <td><?php echo esc_html($form->description); ?></td>
                        <td><?php echo esc_html($form->created_at); ?></td>
                        <td>
                            <a href="#" class="edit-form" 
                               data-id="<?php echo esc_attr($form->id); ?>" 
                               data-name="<?php echo esc_attr($form->name); ?>" 
                               data-description="<?php echo esc_attr($form->description); ?>"
                               data-form-data="<?php echo esc_attr($form->form_data); ?>">Editar</a> |
                            <a href="<?php echo wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $form->id)), 'delete-form'); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este formulario?')">Eliminar</a> |
                            <a href="#" class="export-form" data-id="<?php echo esc_attr($form->id); ?>">Exportar JSON</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.edit-form').click(function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');
            var formData = $(this).data('form-data');

            $('input[name="wpsts_form_action"]').val('edit');
            $('input[name="form_id"]').val(id);
            $('input[name="form_name"]').val(name);
            $('textarea[name="form_description"]').val(description);
            $('textarea[name="form_data"]').val(JSON.stringify(JSON.parse(formData), null, 2));
            $('#submit').val('Actualizar Formulario');
        });

        $('.export-form').click(function(e) {
            e.preventDefault();
            var formId = $(this).data('id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpsts_export_form',
                    form_id: formId,
                    nonce: '<?php echo wp_create_nonce('wpsts_export_form'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data));
                        var downloadAnchorNode = document.createElement('a');
                        downloadAnchorNode.setAttribute("href", dataStr);
                        downloadAnchorNode.setAttribute("download", "form_" + formId + ".json");
                        document.body.appendChild(downloadAnchorNode);
                        downloadAnchorNode.click();
                        downloadAnchorNode.remove();
                    } else {
                        alert('Error al exportar el formulario: ' + response.data);
                    }
                }
            });
        });
    });
    </script>
    <?php
}

// Función para exportar formulario como JSON
function wpsts_export_form() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('No tienes permiso para realizar esta acción.');
    }

    if (!wp_verify_nonce($_POST['nonce'], 'wpsts_export_form')) {
        wp_send_json_error('Nonce inválido');
    }

    $form_id = intval($_POST['form_id']);

    global $wpdb;
    $forms_table = $wpdb->prefix . 'wpsts_forms';
    $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE id = %d", $form_id));

    if (!$form) {
        wp_send_json_error('Formulario no encontrado');
    }

    $export_data = array(
        'name' => $form->name,
        'description' => $form->description,
        'form_data' => json_decode($form->form_data)
    );

    wp_send_json_success($export_data);
}
add_action('wp_ajax_wpsts_export_form', 'wpsts_export_form');