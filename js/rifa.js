document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const tituloRifa = document.getElementById('tituloRifa');
    const premioRifa = document.getElementById('premioRifa');
    const fechaRifa = document.getElementById('fechaRifa');
    const precioRifa = document.getElementById('precioRifa');
    const numerosGrid = document.getElementById('numerosGrid');
    const nombreInput = document.getElementById('nombreParticipante');

    // Variable para modo administrador
    const isAdmin = window.location.search.includes('admin=true');

    // Cargar datos iniciales
    cargarDatosRifa();

    function cargarDatosRifa() {
        tituloRifa.textContent = rifaData.titulo;
        premioRifa.textContent = rifaData.premio;
        fechaRifa.textContent = formatearFecha(rifaData.fecha);
        precioRifa.textContent = rifaData.precio;

        generarNumeros();
    }

    function generarNumeros() {
        numerosGrid.innerHTML = '';
        
        for (let i = 1; i <= rifaData.numeros; i++) {
            const numero = document.createElement('div');
            const vendidoA = rifaData.numerosVendidos[i];
            
            numero.className = 'numero' + (vendidoA ? ' ocupado' : '');
            
            numero.innerHTML = `
                <div class="numero-valor">${i.toString().padStart(2, '0')}</div>
                <div class="numero-nombre">${vendidoA || 'Disponible'}</div>
                ${isAdmin && vendidoA ? `
                    <button onclick="liberarNumero(${i})" class="btn-danger">Liberar</button>
                ` : ''}
            `;

            if (isAdmin && !vendidoA) {
                numero.addEventListener('click', () => seleccionarNumero(i));
            }

            numerosGrid.appendChild(numero);
        }
    }

    function seleccionarNumero(numero) {
        const nombre = nombreInput.value.trim();
        
        if (!nombre) {
            alert('Por favor, ingrese su nombre primero');
            nombreInput.focus();
            return;
        }

        rifaData.numerosVendidos[numero] = nombre;
        nombreInput.value = '';
        
        generarNumeros();
        mostrarCodigo();
    }

    window.liberarNumero = function(numero) {
        if (!confirm('¿Estás seguro de que quieres liberar este número?')) return;
        
        delete rifaData.numerosVendidos[numero];
        generarNumeros();
        mostrarCodigo();
    }

    function mostrarCodigo() {
        const codigo = `const rifaData = ${JSON.stringify(rifaData, null, 2)};`;
        
        // Crear o actualizar el elemento de código
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
        const codigo = `const rifaData = ${JSON.stringify(rifaData, null, 2)};`;
        navigator.clipboard.writeText(codigo)
            .then(() => alert('Código copiado al portapapeles'))
            .catch(err => {
                console.error('Error al copiar:', err);
                alert('Error al copiar el código');
            });
    }

    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-ES', opciones);
    }

    // Si es admin, agregar estilos para el contenedor de código
    if (isAdmin) {
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
            }
            .codigo-container pre {
                background: #f5f5f5;
                padding: 10px;
                border-radius: 4px;
                overflow-x: auto;
            }
        `;
        document.head.appendChild(style);
    }
});