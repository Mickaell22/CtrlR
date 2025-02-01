document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const btnCrearRifa = document.getElementById('crearRifa');
    const modal = document.getElementById('modalCrearRifa');
    const formRifa = document.getElementById('formRifa');
    const listaRifas = document.getElementById('listaRifas');

    // Event Listeners
    btnCrearRifa.addEventListener('click', abrirModal);
    formRifa.addEventListener('submit', crearRifa);

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        if (event.target === modal) {
            cerrarModal();
        }
    }

    // Cargar rifas existentes al iniciar
    cargarRifas();

    // Funciones principales
    function abrirModal() {
        modal.style.display = 'block';
    }

    function cerrarModal() {
        modal.style.display = 'none';
        formRifa.reset();
    }

    window.cerrarModal = cerrarModal; // Para acceso global

    function crearRifa(e) {
        e.preventDefault();

        const rifa = {
            id: Date.now(), // Identificador único
            titulo: document.getElementById('titulo').value,
            premio: document.getElementById('premio').value,
            fecha: document.getElementById('fecha').value,
            precio: document.getElementById('precio').value,
            numeros: parseInt(document.getElementById('numeros').value),
            numerosVendidos: {} // Objeto para almacenar números vendidos: {numero: "nombre"}
        };

        // Obtener rifas existentes
        let rifas = JSON.parse(localStorage.getItem('rifas')) || [];
        rifas.push(rifa);
        
        // Guardar en localStorage
        localStorage.setItem('rifas', JSON.stringify(rifas));

        // Actualizar la vista
        cargarRifas();
        cerrarModal();
    }

    function cargarRifas() {
        const rifas = JSON.parse(localStorage.getItem('rifas')) || [];
        listaRifas.innerHTML = ''; // Limpiar lista actual

        if (rifas.length === 0) {
            listaRifas.innerHTML = '<p class="no-rifas">No hay rifas creadas. ¡Crea una nueva!</p>';
            return;
        }

        rifas.forEach((rifa) => {
            const numerosVendidos = Object.keys(rifa.numerosVendidos).length;
            const porcentajeVendido = ((numerosVendidos / rifa.numeros) * 100).toFixed(1);

            const rifaCard = document.createElement('div');
            rifaCard.className = 'rifa-card';
            rifaCard.innerHTML = `
                <h3>${rifa.titulo}</h3>
                <div class="rifa-info">
                    <p><strong>Premio:</strong> ${rifa.premio}</p>
                    <p><strong>Fecha:</strong> ${formatearFecha(rifa.fecha)}</p>
                    <p><strong>Precio:</strong> $${rifa.precio}</p>
                    <p><strong>Números vendidos:</strong> ${numerosVendidos} de ${rifa.numeros} (${porcentajeVendido}%)</p>
                </div>
                <div class="rifa-acciones">
                    <button onclick="verRifa(${rifa.id})" class="btn-primary">Ver Rifa</button>
                    <button onclick="gestionarNumeros(${rifa.id})" class="btn-secondary">Gestionar Números</button>
                    <button onclick="eliminarRifa(${rifa.id})" class="btn-danger">Eliminar</button>
                </div>
            `;
            listaRifas.appendChild(rifaCard);
        });
    }

    // Funciones de gestión
    window.verRifa = function(rifaId) {
        window.location.href = `html/rifa.html?id=${rifaId}`;
    }

    window.gestionarNumeros = function(rifaId) {
        // Obtener la rifa específica
        const rifas = JSON.parse(localStorage.getItem('rifas')) || [];
        const rifa = rifas.find(r => r.id === rifaId);
        
        if (!rifa) return;
    
        // Remover modal existente si hay uno
        const modalExistente = document.getElementById('modalGestion');
        if (modalExistente) {
            modalExistente.remove();
        }
    
        // Crear modal de gestión
        const modalHTML = `
            <div id="modalGestion" class="modal">
                <div class="modal-content">
                    <h2 style="margin-bottom: 20px;">Gestionar Números - ${rifa.titulo}</h2>
                    <div class="numeros-grid">
                        ${generarGrillaNumeros(rifa)}
                    </div>
                    <div class="buttons" style="text-align: center;">
                        <button onclick="cerrarModalGestion()" class="btn-secondary">Cerrar</button>
                    </div>
                </div>
            </div>
        `;
    
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Mostrar modal
        const modalGestion = document.getElementById('modalGestion');
        modalGestion.style.display = 'block';
    
        // Cerrar modal al hacer clic fuera
        modalGestion.onclick = function(event) {
            if (event.target === modalGestion) {
                cerrarModalGestion();
            }
        }
    }

    function generarGrillaNumeros(rifa) {
        let html = '';
        for (let i = 1; i <= rifa.numeros; i++) {
            const numero = i.toString().padStart(2, '0');
            const vendidoA = rifa.numerosVendidos[i] || '';
            const clase = vendidoA ? 'numero ocupado' : 'numero';
            
            html += `
                <div class="${clase}">
                    <div class="numero-valor">#${numero}</div>
                    <div class="numero-nombre">${vendidoA || 'Disponible'}</div>
                    ${vendidoA ? `
                        <button onclick="liberarNumero(${rifa.id}, ${i})" 
                                class="btn-danger" 
                                style="width: 100%; margin-top: 5px;">
                            Liberar
                        </button>
                    ` : ''}
                </div>
            `;
        }
        return html;
    }

    window.cerrarModalGestion = function() {
        const modalGestion = document.getElementById('modalGestion');
        modalGestion.remove();
    }

    window.liberarNumero = function(rifaId, numero) {
        if (!confirm('¿Estás seguro de que quieres liberar este número?')) return;

        const rifas = JSON.parse(localStorage.getItem('rifas')) || [];
        const rifaIndex = rifas.findIndex(r => r.id === rifaId);
        
        if (rifaIndex === -1) return;

        // Eliminar el número vendido
        delete rifas[rifaIndex].numerosVendidos[numero];
        
        // Guardar cambios
        localStorage.setItem('rifas', JSON.stringify(rifas));
        
        // Actualizar vista
        cerrarModalGestion();
        gestionarNumeros(rifaId);
    }

    window.eliminarRifa = function(rifaId) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta rifa?')) return;

        let rifas = JSON.parse(localStorage.getItem('rifas')) || [];
        rifas = rifas.filter(rifa => rifa.id !== rifaId);
        
        localStorage.setItem('rifas', JSON.stringify(rifas));
        cargarRifas();
    }

    // Función auxiliar para formatear fechas
    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-ES', opciones);
    }
});