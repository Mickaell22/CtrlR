document.addEventListener('DOMContentLoaded', function() {
    const listaRifas = document.getElementById('listaRifas');

    function cargarRifas() {
        listaRifas.innerHTML = '';

        if (!window.rifasData || window.rifasData.length === 0) {
            listaRifas.innerHTML = '<p class="no-rifas">No hay rifas disponibles.</p>';
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

        // Mostrar el modal con los números
        const modalHTML = `
            <div id="modalGestion" class="modal">
                <div class="modal-content">
                    <h2>Gestionar Números - ${rifa.titulo}</h2>
                    <div class="numeros-grid">
                        ${generarGrillaNumeros(rifa)}
                    </div>
                    <div class="instrucciones">
                        <p>Para actualizar un número vendido:</p>
                        <ol>
                            <li>Copia el código mostrado abajo</li>
                            <li>Actualiza el archivo data.js</li>
                            <li>Haz commit y push a GitHub</li>
                        </ol>
                    </div>
                    <div class="codigo-actualizado">
                        <h3>Código para data.js:</h3>
                        <pre><code>${generarCodigoActualizado()}</code></pre>
                    </div>
                    <button onclick="cerrarModalGestion()" class="btn-secondary">Cerrar</button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.getElementById('modalGestion').style.display = 'block';
    }

    window.cerrarModalGestion = function() {
        const modalGestion = document.getElementById('modalGestion');
        if (modalGestion) {
            modalGestion.remove();
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
                </div>
            `;
        }
        return html;
    }

    function generarCodigoActualizado() {
        return 'window.rifasData = ' + JSON.stringify(window.rifasData, null, 2) + ';';
    }

    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-ES', opciones);
    }

    // Cargar rifas al iniciar
    cargarRifas();
});