<?php
/**
 * Plugin Name: WP Support Ticket System
 * Plugin URI: http://example.com/wp-support-ticket-system
 * Description: Un sistema simple de tickets de soporte para WordPress
 * Version: 1.5
 * Author: Tu Nombre
 * Author URI: http://example.com
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Incluir archivos necesarios
require_once plugin_dir_path(__FILE__) . 'includes/entities.php';
require_once plugin_dir_path(__FILE__) . 'includes/units.php';
require_once plugin_dir_path(__FILE__) . 'includes/users.php';
require_once plugin_dir_path(__FILE__) . 'includes/ticket-statuses.php';
require_once plugin_dir_path(__FILE__) . 'includes/services.php';
require_once plugin_dir_path(__FILE__) . 'includes/forms.php';

// Función para registrar el menú del plugin
function wpsts_register_menu() {
    add_menu_page(
        'Sistema de Tickets',
        'Tickets de Soporte',
        'manage_options',
        'wpsts-tickets',
        'wpsts_admin_page',
        'dashicons-tickets',
        30
    );
    
    add_submenu_page(
        'wpsts-tickets',
        'Gestionar Entidades',
        'Entidades',
        'manage_options',
        'wpsts-entities',
        'wpsts_manage_entities'
    );
    
    add_submenu_page(
        'wpsts-tickets',
        'Gestionar Unidades',
        'Unidades',
        'manage_options',
        'wpsts-units',
        'wpsts_manage_units'
    );
    
    add_submenu_page(
        'wpsts-tickets',
        'Gestionar Usuarios',
        'Usuarios',
        'manage_options',
        'wpsts-users',
        'wpsts_manage_users'
    );

    add_submenu_page(
        'wpsts-tickets',
        'Gestionar Estados de Tickets',
        'Estados de Tickets',
        'manage_options',
        'wpsts-ticket-statuses',
        'wpsts_manage_ticket_statuses'
    );

    add_submenu_page(
        'wpsts-tickets',
        'Servicios Creados',
        'Servicios',
        'manage_options',
        'wpsts-services',
        'wpsts_manage_services'
    );

    add_submenu_page(
        'wpsts-tickets',
        'Formularios',
        'Formularios',
        'manage_options',
        'wpsts-forms',
        'wpsts_manage_forms'
    );
}
add_action('admin_menu', 'wpsts_register_menu');

// Función para la página principal de administración de tickets
function wpsts_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';
    $tickets = $wpdb->get_results("SELECT t.*, e.name as entity_name, u.name as unit_name 
                                   FROM $table_name t 
                                   LEFT JOIN {$wpdb->prefix}wpsts_entities e ON t.entity_id = e.id
                                   LEFT JOIN {$wpdb->prefix}wpsts_units u ON t.unit_id = u.id
                                   ORDER BY t.time DESC");
    
    $statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpsts_ticket_statuses");
    
    echo '<div class="wrap">';
    echo '<h1>Tickets de Soporte</h1>';
    echo '<form method="post" action="">';
    wp_nonce_field('bulk_ticket_actions', 'wpsts_bulk_nonce');
    echo '<select name="bulk_action">
            <option value="">Acciones en lote</option>
            <option value="delete">Eliminar</option>';
    foreach ($statuses as $status) {
        echo '<option value="change_status_' . esc_attr($status->id) . '">Cambiar estado a ' . esc_html($status->name) . '</option>';
    }
    echo '</select>';
    echo '<input type="submit" class="button" value="Aplicar">';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th class="manage-column column-cb check-column"><input type="checkbox" /></th><th>ID</th><th>Fecha</th><th>Usuario</th><th>Asunto</th><th>Estado</th><th>Entidad</th><th>Unidad</th><th>Nuevo Servicio</th><th>Acciones</th></tr></thead>';
    echo '<tbody>';
    foreach ($tickets as $ticket) {
        $user = get_userdata($ticket->user_id);
        $new_service = $ticket->new_service ? 'Sí' : 'No';
        echo '<tr>';
        echo '<td><input type="checkbox" name="ticket_ids[]" value="' . esc_attr($ticket->id) . '" /></td>';
        echo '<td>' . esc_html($ticket->id) . '</td>';
        echo '<td>' . esc_html($ticket->time) . '</td>';
        echo '<td>' . esc_html($user->display_name) . '</td>';
        echo '<td>' . esc_html($ticket->subject) . '</td>';
        echo '<td>' . esc_html($ticket->status) . '</td>';
        echo '<td>' . esc_html($ticket->entity_name) . '</td>';
        echo '<td>' . esc_html($ticket->unit_name) . '</td>';
        echo '<td>' . esc_html($new_service) . '</td>';
        echo '<td>
                <a href="' . admin_url('admin.php?page=wpsts-view-ticket&id=' . $ticket->id) . '">Ver</a> | 
                <a href="' . admin_url('admin.php?page=wpsts-edit-ticket&id=' . $ticket->id) . '">Editar</a> | 
                <a href="' . wp_nonce_url(admin_url('admin-post.php?action=delete_ticket&id=' . $ticket->id), 'delete_ticket_' . $ticket->id) . '" onclick="return confirm(\'¿Estás seguro de que quieres eliminar este ticket?\')">Eliminar</a>
              </td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</form>';
    echo '</div>';
}

// Función para ver un ticket específico
function wpsts_view_ticket() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para acceder a esta página.'));
    }

    $ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$ticket_id) {
        wp_die(__('ID de ticket no válido.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';
    $ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $ticket_id));

    if (!$ticket) {
        wp_die(__('Ticket no encontrado.'));
    }

    $user = get_userdata($ticket->user_id);
    $statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpsts_ticket_statuses");

    echo '<div class="wrap">';
    echo '<h1>Ver Ticket #' . esc_html($ticket->id) . '</h1>';
    echo '<table class="form-table">';
    echo '<tr><th>Fecha</th><td>' . esc_html($ticket->time) . '</td></tr>';
    echo '<tr><th>Usuario</th><td>' . esc_html($user->display_name) . '</td></tr>';
    echo '<tr><th>Asunto</th><td>' . esc_html($ticket->subject) . '</td></tr>';
    echo '<tr><th>Contenido</th><td>' . wp_kses_post($ticket->content) . '</td></tr>';
    echo '<tr><th>Estado</th><td>';
    echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
    wp_nonce_field('update_ticket_status', 'wpsts_nonce');
    echo '<input type="hidden" name="action" value="update_ticket_status">';
    echo '<select name="ticket_status">';
    foreach ($statuses as $status) {
        echo '<option value="' . esc_attr($status->name) . '" ' . selected($ticket->status, $status->name, false) . '>' . esc_html($status->name) . '</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="ticket_id" value="' . esc_attr($ticket->id) . '">';
    echo '<input type="submit" class="button button-primary" value="Actualizar Estado">';
    echo '</form>';
    echo '</td></tr>';
    echo '<tr><th>Nuevo Servicio</th><td>';
    echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
    wp_nonce_field('update_new_service', 'wpsts_new_service_nonce');
    echo '<input type="hidden" name="action" value="update_new_service">';
    echo '<input type="checkbox" name="new_service" value="1" ' . checked($ticket->new_service, 1, false) . '>';
    echo '<input type="hidden" name="ticket_id" value="' . esc_attr($ticket->id) . '">';
    echo '<input type="submit" class="button button-secondary" value="Actualizar Nuevo Servicio">';
    echo '</form>';
    echo '</td></tr>';
    echo '</table>';
    echo '<p><a href="' . admin_url('admin.php?page=wpsts-edit-ticket&id=' . $ticket->id) . '" class="button">Editar Ticket</a></p>';
    echo '</div>';
}

// Función para editar un ticket
function wpsts_edit_ticket() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para acceder a esta página.'));
    }

    $ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$ticket_id) {
        wp_die(__('ID de ticket no válido.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';
    $ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $ticket_id));

    if (!$ticket) {
        wp_die(__('Ticket no encontrado.'));
    }

    $statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpsts_ticket_statuses");

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket'])) {
        check_admin_referer('update_ticket_' . $ticket_id);

        $updated_ticket = array(
            'subject' => sanitize_text_field($_POST['subject']),
            'content' => wp_kses_post($_POST['content']),
            'status' => sanitize_text_field($_POST['status']),
            'new_service' => isset($_POST['new_service']) ? 1 : 0,
        );

        $wpdb->update($table_name, $updated_ticket, array('id' => $ticket_id));
        $ticket = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $ticket_id));
        echo '<div class="updated"><p>Ticket actualizado con éxito.</p></div>';
    }

    echo '<div class="wrap">';
    echo '<h1>Editar Ticket #' . esc_html($ticket->id) . '</h1>';
    echo '<form method="post" action="">';
    wp_nonce_field('update_ticket_' . $ticket_id);
    echo '<table class="form-table">';
    echo '<tr><th><label for="subject">Asunto</label></th><td><input type="text" id="subject" name="subject" value="' . esc_attr($ticket->subject) . '" required></td></tr>';
    echo '<tr><th><label for="content">Contenido</label></th><td><textarea id="content" name="content" rows="5" required>' . esc_textarea($ticket->content) . '</textarea></td></tr>';
    echo '<tr><th><label for="status">Estado</label></th><td>';
    echo '<select id="status" name="status">';
    foreach ($statuses as $status) {
        echo '<option value="' . esc_attr($status->name) . '" ' . selected($ticket->status, $status->name, false) . '>' . esc_html($status->name) . '</option>';
    }
    echo '</select>';
    echo '</td></tr>';
    echo '<tr><th><label for="new_service">Nuevo Servicio</label></th><td><input type="checkbox" id="new_service" name="new_service" value="1" ' . checked($ticket->new_service, 1, false) . '></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="update_ticket" class="button button-primary" value="Actualizar Ticket">';
    echo '</form>';
    echo '</div>';
}

// Función para activar el plugin
function wpsts_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Crear tabla de tickets
    $table_name = $wpdb->prefix . 'support_tickets';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        user_id mediumint(9) NOT NULL,
        name varchar(100) NOT NULL,
        surname varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(20) NOT NULL,
        subject text NOT NULL,
        content text NOT NULL,
        status varchar(20) NOT NULL,
        entity_type_id mediumint(9),
        entity_name varchar(255),
        new_service tinyint(1) DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Crear tabla de tipos de entidades
    $entity_types_table = $wpdb->prefix . 'wpsts_entity_types';
    $sql .= "CREATE TABLE $entity_types_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Crear tabla de unidades
    $units_table = $wpdb->prefix . 'wpsts_units';
    $sql .= "CREATE TABLE $units_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Crear tabla de estados de tickets
    $ticket_statuses_table = $wpdb->prefix . 'wpsts_ticket_statuses';
    $sql .= "CREATE TABLE $ticket_statuses_table (
        id mediumint(9) NOT NULL AUTO_AUTO_INCREMENT,
        name varchar(50) NOT NULL,
        description text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Crear tabla de servicios
    $services_table = $wpdb->prefix . 'wpsts_services';
    $sql .= "CREATE TABLE $services_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ticket_id mediumint(9) NOT NULL,
        name varchar(255) NOT NULL,
        description text,
        unit_id mediumint(9) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        FOREIGN KEY (ticket_id) REFERENCES $table_name(id),
        FOREIGN KEY (unit_id) REFERENCES $units_table(id)
    ) $charset_collate;";

    // Crear tabla de ticket_units (relación muchos a muchos entre tickets y unidades)
    $ticket_units_table = $wpdb->prefix . 'wpsts_ticket_units';
    $sql .= "CREATE TABLE $ticket_units_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ticket_id mediumint(9) NOT NULL,
        unit_id mediumint(9) NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (ticket_id) REFERENCES $table_name(id),
        FOREIGN KEY (unit_id) REFERENCES $units_table(id)
    ) $charset_collate;";

    // Crear tabla de formularios
    $forms_table = $wpdb->prefix . 'wpsts_forms';
    $sql .= "CREATE TABLE $forms_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        form_data longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Insertar estados de tickets por defecto
    $default_statuses = array('Nuevo', 'Procesando', 'Cerrado');
    foreach ($default_statuses as $status) {
        $wpdb->insert($ticket_statuses_table, array('name' => $status));
    }
}
register_activation_hook(__FILE__, 'wpsts_activate');

// Función para desactivar el plugin
function wpsts_deactivate() {
    // Aquí puedes agregar cualquier limpieza necesaria al desactivar el plugin
}
register_deactivation_hook(__FILE__, 'wpsts_deactivate');

// Agregar scripts y estilos
function wpsts_enqueue_scripts() {
    wp_enqueue_style('wpsts-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('wpsts-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0', true);
    wp_localize_script('wpsts-script', 'wpsts_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpsts_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'wpsts_enqueue_scripts');
add_action('wp_enqueue_scripts', 'wpsts_enqueue_scripts');

// Shortcode para mostrar el formulario de tickets
function wpsts_ticket_form_shortcode() {
    ob_start();
    include(plugin_dir_path(__FILE__) . 'templates/ticket-form.php');
    return ob_get_clean();
}
add_shortcode('wpsts_ticket_form', 'wpsts_ticket_form_shortcode');

// Función para crear un ticket
function wpsts_create_ticket() {
    if (!wp_verify_nonce($_POST['nonce'], 'wpsts_nonce')) {
        wp_send_json_error('Nonce inválido');
    }

    $name = sanitize_text_field($_POST['name']);
    $surname = sanitize_text_field($_POST['surname']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone_prefix'] . $_POST['phone']);
    $entity_type_id = intval($_POST['entity_type']);
    $entity_name = sanitize_text_field($_POST['entity_name']);
    $units = array_map('intval', $_POST['units']);
    $subject = sanitize_text_field($_POST['subject']);
    $description = sanitize_textarea_field($_POST['description']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';

    $result = $wpdb->insert(
        $table_name,
        array(
            'time' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'content' => $description,
            'status' => 'Nuevo',
            'entity_type_id' => $entity_type_id,
            'entity_name' => $entity_name
        )
    );

    if ($result) {
        $ticket_id = $wpdb->insert_id;
        
        // Asociar unidades al ticket
        $ticket_units_table = $wpdb->prefix . 'wpsts_ticket_units';
        foreach ($units as $unit_id) {
            $wpdb->insert(
                $ticket_units_table,
                array(
                    'ticket_id' => $ticket_id,
                    'unit_id' => $unit_id
                )
            );
        }

        $success_url = home_url('/ticket-creado-con-exito/');
        wp_send_json_success(array('redirect_url' => $success_url));
    } else {
        $error_url = home_url('/error-al-crear-ticket/');
        wp_send_json_error(array('redirect_url' => $error_url));
    }
}
add_action('wp_ajax_wpsts_create_ticket', 'wpsts_create_ticket');
add_action('wp_ajax_nopriv_wpsts_create_ticket', 'wpsts_create_ticket');

// Función para actualizar el estado de un ticket
function wpsts_update_ticket_status() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para realizar esta acción.'));
    }

    if (!wp_verify_nonce($_POST['wpsts_nonce'], 'update_ticket_status')) {
        wp_die(__('Nonce de seguridad no válido.'));
    }

    $ticket_id = intval($_POST['ticket_id']);
    $new_status = sanitize_text_field($_POST['ticket_status']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';

    $result = $wpdb->update(
        $table_name,
        array('status' => $new_status),
        array('id' => $ticket_id)
    );

    if ($result !== false) {
        wp_redirect(add_query_arg('updated', '1', wp_get_referer()));
        exit;
    } else {
        wp_die(__('Error al actualizar el estado del ticket.'));
    }
}
add_action('admin_post_update_ticket_status', 'wpsts_update_ticket_status');

// Función para actualizar el campo "nuevo servicio" de un ticket
function wpsts_update_new_service() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para realizar esta acción.'));
    }

    if (!wp_verify_nonce($_POST['wpsts_new_service_nonce'], 'update_new_service')) {
        wp_die(__('Nonce de seguridad no válido.'));
    }

    $ticket_id = intval($_POST['ticket_id']);
    $new_service = isset($_POST['new_service']) ? 1 : 0;

    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';

    $result = $wpdb->update(
        $table_name,
        array('new_service' => $new_service),
        array('id' => $ticket_id)
    );

    if ($result !== false) {
        wp_redirect(add_query_arg('updated', '1', wp_get_referer()));
        exit;
    } else {
        wp_die(__('Error al actualizar el campo "nuevo servicio" del ticket.'));
    }
}
add_action('admin_post_update_new_service', 'wpsts_update_new_service');

// Añadir página para ver un ticket específico
function wpsts_add_view_ticket_page() {
    add_submenu_page(
        null,
        'Ver Ticket',
        'Ver Ticket',
        'manage_options',
        'wpsts-view-ticket',
        'wpsts_view_ticket'
    );
}
add_action('admin_menu', 'wpsts_add_view_ticket_page');

// Añadir página para editar un ticket específico
function wpsts_add_edit_ticket_page() {
    add_submenu_page(
        null,
        'Editar Ticket',
        'Editar Ticket',
        'manage_options',
        'wpsts-edit-ticket',
        'wpsts_edit_ticket'
    );
}
add_action('admin_menu', 'wpsts_add_edit_ticket_page');

// Función para eliminar un ticket
function wpsts_delete_ticket() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para realizar esta acción.'));
    }

    $ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$ticket_id) {
        wp_die(__('ID de ticket no válido.'));
    }

    check_admin_referer('delete_ticket_' . $ticket_id);

    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';

    $result = $wpdb->delete($table_name, array('id' => $ticket_id));

    if ($result !== false) {
        wp_redirect(add_query_arg('deleted', '1', admin_url('admin.php?page=wpsts-tickets')));
        exit;
    } else {
        wp_die(__('Error al eliminar el ticket.'));
    }
}
add_action('admin_post_delete_ticket', 'wpsts_delete_ticket');

// Función para manejar acciones en lote
function wpsts_handle_bulk_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!isset($_POST['wpsts_bulk_nonce']) || !wp_verify_nonce($_POST['wpsts_bulk_nonce'], 'bulk_ticket_actions')) {
        return;
    }

    if (!isset($_POST['ticket_ids']) || !is_array($_POST['ticket_ids'])) {
        return;
    }

    $action = isset($_POST['bulk_action']) ? $_POST['bulk_action'] : '';
    $ticket_ids = array_map('intval', $_POST['ticket_ids']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';

    switch ($action) {
        case 'delete':
            foreach ($ticket_ids as $ticket_id) {
                $wpdb->delete($table_name, array('id' => $ticket_id));
            }
            $message = 'Tickets eliminados con éxito.';
            break;
        default:
            if (strpos($action, 'change_status_') === 0) {
                $new_status_id = intval(substr($action, 14));
                $new_status = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}wpsts_ticket_statuses WHERE id = %d", $new_status_id));
                if ($new_status) {
                    foreach ($ticket_ids as $ticket_id) {
                        $wpdb->update($table_name, array('status' => $new_status), array('id' => $ticket_id));
                    }
                    $message = 'Estado de tickets actualizado con éxito.';
                }
            }
            break;
    }

    if (isset($message)) {
        wp_redirect(add_query_arg('message', urlencode($message), admin_url('admin.php?page=wpsts-tickets')));
        exit;
    }
}
add_action('admin_init', 'wpsts_handle_bulk_actions');