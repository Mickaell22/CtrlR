document.addEventListener('DOMContentLoaded', function() {
    // Obtener el ID de la rifa de la URL
    const params = new URLSearchParams(window.location.search);
    const rifaId = parseInt(params.get('id'));

    // Referencias a elementos del DOM
    const tituloRifa = document.getElementById('tituloRifa');
    const premioRifa = document.getElementById('premioRifa');
    const fechaRifa = document.getElementById('fechaRifa');
    const precioRifa = document.getElementById('precioRifa');
    const numerosGrid = document.getElementById('numerosGrid');
    const nombreInput = document.getElementById('nombreParticipante');

    // Cargar datos de la rifa
    cargarDatosRifa();

    function cargarDatosRifa() {
        // Obtener rifas del localStorage
        const rifas = JSON.parse(localStorage.getItem('rifas')) || [];
        const rifa = rifas.find(r => r.id === rifaId);

        if (!rifa) {
            alert('Rifa no encontrada');
            window.location.href = '../admin.html';
            return;
        }

        // Actualizar la información en la página
        tituloRifa.textContent = rifa.titulo;
        premioRifa.textContent = rifa.premio;
        fechaRifa.textContent = formatearFecha(rifa.fecha);
        precioRifa.textContent = rifa.precio;

        // Generar grid de números
        generarNumeros(rifa);
    }

    function generarNumeros(rifa) {
        numerosGrid.innerHTML = '';
        
        for (let i = 1; i <= rifa.numeros; i++) {
            const numero = document.createElement('div');
            numero.className = 'numero' + (rifa.numerosVendidos[i] ? ' ocupado' : '');
            
            numero.innerHTML = `
                <div class="numero-valor">${i.toString().padStart(2, '0')}</div>
                <div class="numero-nombre">${rifa.numerosVendidos[i] || ''}</div>
            `;

            if (!rifa.numerosVendidos[i]) {
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

        // Obtener y actualizar datos
        const rifas = JSON.parse(localStorage.getItem('rifas')) || [];
        const rifaIndex = rifas.findIndex(r => r.id === rifaId);

        if (rifaIndex === -1) return;

        // Verificar si el número ya está vendido
        if (rifas[rifaIndex].numerosVendidos[numero]) {
            alert('Este número ya está ocupado');
            return;
        }

        // Guardar la selección
        rifas[rifaIndex].numerosVendidos[numero] = nombre;
        localStorage.setItem('rifas', JSON.stringify(rifas));

        // Actualizar la vista
        cargarDatosRifa();
        
        // Limpiar el input
        nombreInput.value = '';
    }

    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-ES', opciones);
    }
});