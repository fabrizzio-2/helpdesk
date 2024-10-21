<?php
// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

function wpsts_manage_services() {
    global $wpdb;
    $services_table = $wpdb->prefix . 'wpsts_services';
    $tickets_table = $wpdb->prefix . 'support_tickets';
    $units_table = $wpdb->prefix . 'wpsts_units';

    // Obtener todos los servicios
    $services = $wpdb->get_results("
        SELECT s.*, t.subject as ticket_subject, u.name as unit_name
        FROM $services_table s
        LEFT JOIN $tickets_table t ON s.ticket_id = t.id
        LEFT JOIN $units_table u ON s.unit_id = u.id
        ORDER BY s.created_at DESC
    ");

    // Mostrar lista de servicios
    ?>
    <div class="wrap">
        <h1>Servicios Creados por Tickets</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Servicio</th>
                    <th>Descripción</th>
                    <th>Ticket Relacionado</th>
                    <th>Unidad</th>
                    <th>Fecha de Creación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo esc_html($service->id); ?></td>
                        <td><?php echo esc_html($service->name); ?></td>
                        <td><?php echo esc_html($service->description); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wpsts-view-ticket&id=' . $service->ticket_id); ?>">
                                <?php echo esc_html($service->ticket_subject); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($service->unit_name); ?></td>
                        <td><?php echo esc_html($service->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}