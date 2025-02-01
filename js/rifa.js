document.addEventListener('DOMContentLoaded', function() {
    const ticketsContainer = document.getElementById('ticketsContainer');
    const nameInput = document.getElementById('nameInput');
    const tickets = new Array(50).fill('');

    // Crear los 50 números de la rifa
    function createTickets() {
        for (let i = 0; i < 50; i++) {
            const ticket = document.createElement('div');
            ticket.className = 'ticket';
            ticket.innerHTML = `
                <div class="ticket-number">${(i + 1).toString().padStart(2, '0')}</div>
                <div class="ticket-name"></div>
            `;
            
            ticket.addEventListener('click', () => selectTicket(ticket, i));
            ticketsContainer.appendChild(ticket);
        }
    }

    // Manejar la selección de un número
    function selectTicket(ticketElement, index) {
        const name = nameInput.value.trim();
        
        // Verificar si el número ya está tomado
        if (tickets[index] !== '') {
            alert('Este número ya está ocupado');
            return;
        }

        // Verificar si se ingresó un nombre
        if (name === '') {
            alert('Por favor, ingrese su nombre antes de seleccionar un número');
            nameInput.focus();
            return;
        }

        // Actualizar el ticket
        tickets[index] = name;
        ticketElement.classList.add('taken');
        ticketElement.querySelector('.ticket-name').textContent = name;
        
        // Limpiar el input del nombre
        nameInput.value = '';

        // Guardar en localStorage
        saveTickets();
    }

    // Guardar tickets en localStorage
    function saveTickets() {
        localStorage.setItem('raffleTickets', JSON.stringify(tickets));
    }

    // Cargar tickets desde localStorage
    function loadTickets() {
        const savedTickets = localStorage.getItem('raffleTickets');
        if (savedTickets) {
            const parsedTickets = JSON.parse(savedTickets);
            tickets.splice(0, tickets.length, ...parsedTickets);
            
            // Actualizar la interfaz
            const ticketElements = document.querySelectorAll('.ticket');
            tickets.forEach((name, index) => {
                if (name) {
                    ticketElements[index].classList.add('taken');
                    ticketElements[index].querySelector('.ticket-name').textContent = name;
                }
            });
        }
    }

    // Inicializar la rifa
    createTickets();
    loadTickets();
});