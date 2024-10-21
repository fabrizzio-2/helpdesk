<?php
// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

function wpsts_manage_users() {
    global $wpdb;
    $units_table = $wpdb->prefix . 'wpsts_units';
    $entities_table = $wpdb->prefix . 'wpsts_entities';

    // Procesar asignación de unidad a usuario
    if (isset($_POST['wpsts_assign_unit']) && check_admin_referer('wpsts_assign_unit')) {
        $user_id = intval($_POST['user_id']);
        $unit_id = intval($_POST['unit_id']);
        update_user_meta($user_id, 'wpsts_assigned_unit', $unit_id);
        echo '<div class="updated"><p>Unidad asignada con éxito al usuario.</p></div>';
    }

    // Obtener todos los usuarios
    $users = get_users();

    // Obtener todas las unidades y entidades
    $units = $wpdb->get_results("SELECT u.*, e.name as entity_name FROM $units_table u LEFT JOIN $entities_table e ON u.entity_id = e.id");

    // Mostrar lista de usuarios y formulario de asignación
    ?>
    <div class="wrap">
        <h1>Gestionar Usuarios</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Unidad Asignada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): 
                    $assigned_unit_id = get_user_meta($user->ID, 'wpsts_assigned_unit', true);
                    $assigned_unit = $assigned_unit_id ? $wpdb->get_row($wpdb->prepare("SELECT u.*, e.name as entity_name FROM $units_table u LEFT JOIN $entities_table e ON u.entity_id = e.id WHERE u.id = %d", $assigned_unit_id)) : null;
                ?>
                    <tr>
                        <td><?php echo esc_html($user->ID); ?></td>
                        <td><?php echo esc_html($user->user_login); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                        <td>
                            <?php 
                            if ($assigned_unit) {
                                echo esc_html($assigned_unit->name . ' (' . $assigned_unit->entity_name . ')');
                            } else {
                                echo 'No asignada';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="#" class="assign-unit" data-user-id="<?php echo esc_attr($user->ID); ?>">Asignar Unidad</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="assign-unit-modal" style="display:none;">
            <h2>Asignar Unidad a Usuario</h2>
            <form method="post" action="">
                <?php wp_nonce_field('wpsts_assign_unit'); ?>
                <input type="hidden" name="wpsts_assign_unit" value="1">
                <input type="hidden" name="user_id" id="modal-user-id" value="">
                <p>
                    <label for="unit_id">Seleccionar Unidad:</label>
                    <select name="unit_id" id="unit_id" required>
                        <option value="">Selecciona una unidad</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?php echo esc_attr($unit->id); ?>"><?php echo esc_html($unit->name . ' (' . $unit->entity_name . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <input type="submit" class="button button-primary" value="Asignar Unidad">
                </p>
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
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
    </script>
    <?php
}