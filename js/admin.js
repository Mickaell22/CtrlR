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
    console.log('Cargando rifas existentes...');

    // Funciones principales
    function abrirModal() {
        modal.style.display = 'block';
    }

    function cerrarModal() {
        modal.style.display = 'none';
        formRifa.reset();
    }

    window.cerrarModal = cerrarModal;

    function crearRifa(e) {
        e.preventDefault();

        // Crear nueva rifa
        const nuevaRifa = {
            id: Date.now(),
            titulo: document.getElementById('titulo').value,
            premio: document.getElementById('premio').value,
            fecha: document.getElementById('fecha').value,
            precio: document.getElementById('precio').value,
            numeros: parseInt(document.getElementById('numeros').value),
            numerosVendidos: {}
        };

        // Añadir la nueva rifa al array de rifas
        window.rifasData.push(nuevaRifa);
        
        // Mostrar el código actualizado
        mostrarCodigoActualizado();
        
        // Actualizar la vista
        cargarRifas();
        cerrarModal();
    }

    function cargarRifas() {
        listaRifas.innerHTML = '';

        if (!window.rifasData || window.rifasData.length === 0) {
            listaRifas.innerHTML = '<p class="no-rifas">No hay rifas creadas. ¡Crea una nueva!</p>';
            return;
        }

        window.rifasData.forEach((rifa) => {
            const numerosVendidos = Object.keys(rifa.numerosVendidos || {}).length;
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

    window.verRifa = function(rifaId) {
        window.location.href = `html/rifa.html?id=${rifaId}`;
    }

    window.gestionarNumeros = function(rifaId) {
        const rifa = window.rifasData.find(r => r.id === rifaId);
        if (!rifa) return;

        // Remover modal existente si hay
        const modalExistente = document.getElementById('modalGestion');
        if (modalExistente) {
            modalExistente.remove();
        }

        // Crear nuevo modal
        const modalHTML = `
            <div id="modalGestion" class="modal">
                <div class="modal-content">
                    <h2>Gestionar Números - ${rifa.titulo}</h2>
                    <div class="numeros-grid">
                        ${generarGrillaNumeros(rifa)}
                    </div>
                    <div class="instrucciones">
                        <p class="texto-ayuda">Para actualizar los números:</p>
                        <ol>
                            <li>Ingresa el nombre en el campo de texto</li>
                            <li>Haz clic en el número que deseas asignar</li>
                            <li>Copia el código generado abajo</li>
                            <li>Actualiza el archivo data.js con el nuevo código</li>
                        </ol>
                    </div>
                    <div class="input-section">
                        <input type="text" id="nombreComprador" placeholder="Nombre del comprador" class="input-nombre">
                    </div>
                    <div class="codigo-actualizado">
                        <h3>Código para data.js:</h3>
                        <pre><code>${generarCodigoActualizado()}</code></pre>
                        <button onclick="copiarCodigo()" class="btn-primary">Copiar Código</button>
                    </div>
                    <div class="buttons">
                        <button onclick="cerrarModalGestion()" class="btn-secondary">Cerrar</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Añadir event listeners para números
        const numerosElements = document.querySelectorAll('.numero:not(.ocupado)');
        numerosElements.forEach(numeroElement => {
            numeroElement.addEventListener('click', function() {
                const numero = parseInt(this.dataset.numero);
                const nombre = document.getElementById('nombreComprador').value.trim();
                
                if (!nombre) {
                    alert('Por favor, ingresa el nombre del comprador');
                    return;
                }

                asignarNumero(rifaId, numero, nombre);
            });
        });

        document.getElementById('modalGestion').style.display = 'block';
    }

    function asignarNumero(rifaId, numero, nombre) {
        const rifaIndex = window.rifasData.findIndex(r => r.id === rifaId);
        if (rifaIndex === -1) return;

        window.rifasData[rifaIndex].numerosVendidos[numero] = nombre;
        mostrarCodigoActualizado();
        gestionarNumeros(rifaId);
    }

    window.eliminarRifa = function(rifaId) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta rifa?')) return;

        window.rifasData = window.rifasData.filter(rifa => rifa.id !== rifaId);
        mostrarCodigoActualizado();
        cargarRifas();
    }

    window.cerrarModalGestion = function() {
        const modalGestion = document.getElementById('modalGestion');
        if (modalGestion) {
            modalGestion.remove();
        }
    }

    window.liberarNumero = function(rifaId, numero) {
        if (!confirm('¿Estás seguro de que quieres liberar este número?')) return;
        
        const rifaIndex = window.rifasData.findIndex(r => r.id === rifaId);
        if (rifaIndex === -1) return;

        delete window.rifasData[rifaIndex].numerosVendidos[numero];
        mostrarCodigoActualizado();
        gestionarNumeros(rifaId);
    }

    function generarGrillaNumeros(rifa) {
        let html = '';
        for (let i = 1; i <= rifa.numeros; i++) {
            const numero = i.toString().padStart(2, '0');
            const vendidoA = rifa.numerosVendidos[i] || '';
            const clase = vendidoA ? 'numero ocupado' : 'numero';
            
            html += `
                <div class="${clase}" data-numero="${i}">
                    <div class="numero-valor">#${numero}</div>
                    <div class="numero-nombre">${vendidoA || 'Disponible'}</div>
                    ${vendidoA ? `
                        <button onclick="liberarNumero(${rifa.id}, ${i})" class="btn-danger">Liberar</button>
                    ` : ''}
                </div>
            `;
        }
        return html;
    }

    function mostrarCodigoActualizado() {
        const codigo = `window.rifasData = ${JSON.stringify(window.rifasData, null, 2)};`;
        
        let codigoElement = document.getElementById('codigoActualizado');
        if (!codigoElement) {
            codigoElement = document.createElement('div');
            codigoElement.id = 'codigoActualizado';
            codigoElement.className = 'codigo-container';
            document.body.appendChild(codigoElement);
        }

        codigoElement.innerHTML = `
            <h3>Código Actualizado:</h3>
            <p>Copia este código y reemplázalo en data.js:</p>
            <pre><code>${codigo}</code></pre>
            <button onclick="copiarCodigo()" class="btn-primary">Copiar Código</button>
        `;
    }

    window.copiarCodigo = function() {
        const codigo = `window.rifasData = ${JSON.stringify(window.rifasData, null, 2)};`;
        navigator.clipboard.writeText(codigo)
            .then(() => alert('Código copiado al portapapeles'))
            .catch(err => alert('Error al copiar el código'));
    }

    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-ES', opciones);
    }

    // Estilos para el contenedor de código
    const style = document.createElement('style');
    style.textContent = `
        .codigo-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            max-height: 400px;
            overflow: auto;
            z-index: 1000;
        }
        .codigo-container pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .text-ayuda {
            color: #666;
            margin-bottom: 10px;
        }
        .input-nombre {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    `;
    document.head.appendChild(style);
});