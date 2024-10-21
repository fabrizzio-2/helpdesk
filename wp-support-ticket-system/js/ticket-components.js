// ticket-components.js

(function() {
    // Función auxiliar para crear elementos
    function createElement(tag, attributes = {}, ...children) {
        const element = document.createElement(tag);
        if (attributes && typeof attributes === 'object') {
            Object.entries(attributes).forEach(([key, value]) => {
                if (key === 'className') {
                    element.className = value;
                } else if (key.startsWith('on') && typeof value === 'function') {
                    element.addEventListener(key.toLowerCase().substr(2), value);
                } else {
                    element.setAttribute(key, value);
                }
            });
        }
        children.forEach(child => {
            if (typeof child === 'string') {
                element.appendChild(document.createTextNode(child));
            } else if (child instanceof Node) {
                element.appendChild(child);
            }
        });
        return element;
    }

    // LoginForm Component
    function LoginForm() {
        const form = createElement('form', { className: 'login-form' });
        const usernameInput = createElement('input', { type: 'text', id: 'username', required: true });
        const passwordInput = createElement('input', { type: 'password', id: 'password', required: true });

        form.appendChild(createElement('div', { className: 'form-group' },
            createElement('label', { htmlFor: 'username' }, 'Usuario:'),
            usernameInput
        ));
        form.appendChild(createElement('div', { className: 'form-group' },
            createElement('label', { htmlFor: 'password' }, 'Contraseña:'),
            passwordInput
        ));
        form.appendChild(createElement('button', { type: 'submit', className: 'submit-button' }, 'Iniciar sesión'));

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const response = await fetch(ticket_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'custom_login',
                        username: usernameInput.value,
                        password: passwordInput.value,
                        nonce: ticket_ajax.nonce,
                    }),
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error de inicio de sesión: ' + data.data);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al iniciar sesión');
            }
        });

        return createElement('div', { className: 'login-form-container' },
            createElement('h2', null, 'Iniciar sesión'),
            form
        );
    }

    // TicketList Component
    function TicketList() {
        const container = createElement('div', { className: 'ticket-list-container' });
        const table = createElement('table', { className: 'ticket-table' });
        const tbody = createElement('tbody');
        let tickets = [];

        function renderTickets() {
            tbody.innerHTML = '';
            tickets.forEach((ticket) => {
                const tr = createElement('tr', null,
                    createElement('td', null, ticket.ID),
                    createElement('td', null, ticket.post_title),
                    createElement('td', null,
                        createElement('select', {
                            value: ticket.status,
                            onchange: (e) => handleStatusChange(ticket.ID, e.target.value)
                        },
                            createElement('option', { value: 'nuevo' }, 'Nuevo'),
                            createElement('option', { value: 'abierto' }, 'Abierto'),
                            createElement('option', { value: 'en_proceso' }, 'En Proceso'),
                            createElement('option', { value: 'respondido' }, 'Respondido'),
                            createElement('option', { value: 'cerrado' }, 'Cerrado')
                        )
                    ),
                    createElement('td', null, ticket.date),
                    createElement('td', null,
                        createElement('button', { onclick: () => updateTicketStatus(ticket.ID) }, 'Guardar')
                    )
                );
                tbody.appendChild(tr);
            });
        }

        function handleStatusChange(ticketId, newStatus) {
            tickets = tickets.map(ticket => 
                ticket.ID === ticketId ? { ...ticket, status: newStatus } : ticket
            );
            renderTickets();
        }

        async function updateTicketStatus(ticketId) {
            const ticket = tickets.find(t => t.ID === ticketId);
            if (!ticket) return;

            try {
                const response = await fetch(ticket_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'update_ticket_status',
                        ticket_id: ticketId.toString(),
                        new_status: ticket.status,
                        nonce: ticket_ajax.nonce,
                    }),
                });
                const data = await response.json();
                if (data.success) {
                    alert('Estado del ticket actualizado con éxito');
                } else {
                    alert('Error al actualizar el estado del ticket: ' + data.data);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar el estado del ticket');
            }
        }

        async function updateAllTickets() {
            try {
                const response = await fetch(ticket_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'update_all_tickets',
                        tickets: JSON.stringify(tickets),
                        nonce: ticket_ajax.nonce,
                    }),
                });
                const data = await response.json();
                if (data.success) {
                    alert('Todos los tickets han sido actualizados con éxito');
                } else {
                    alert('Error al actualizar los tickets: ' + data.data);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar los tickets');
            }
        }

        async function fetchTickets() {
            try {
                const response = await fetch(ticket_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'get_user_tickets',
                        nonce: ticket_ajax.nonce,
                    }),
                });
                const data = await response.json();
                if (data.success) {
                    tickets = data.data;
                    renderTickets();
                } else {
                    console.error('Error fetching tickets:', data.data);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        table.appendChild(createElement('thead', null,
            createElement('tr', null,
                createElement('th', null, 'ID'),
                createElement('th', null, 'Asunto'),
                createElement('th', null, 'Estado'),
                createElement('th', null, 'Fecha'),
                createElement('th', null, 'Acciones')
            )
        ));
        table.appendChild(tbody);

        container.appendChild(createElement('h2', null, 'Lista de Tickets'));
        container.appendChild(table);
        container.appendChild(createElement('div', { className: 'ticket-actions' },
            createElement('button', { onclick: () => {
                tickets = tickets.map(t => ({ ...t, status: 'en_proceso' }));
                renderTickets();
            } }, 'Cambiar todos a En Proceso'),
            createElement('button', { onclick: updateAllTickets }, 'Guardar todos los cambios')
        ));

        fetchTickets();

        return container;
    }

    // Exponer los componentes globalmente
    window.ticketComponents = {
        LoginForm: LoginForm,
        TicketList: TicketList
    };

    // Inicializar los componentes cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        const loginFormContainer = document.getElementById('login-form-container');
        const ticketListContainer = document.getElementById('ticket-list-container');
        
        if (loginFormContainer) {
            loginFormContainer.appendChild(LoginForm());
        }
        
        if (ticketListContainer) {
            ticketListContainer.appendChild(TicketList());
        }
    });
})();