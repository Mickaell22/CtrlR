document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const rifaId = parseInt(params.get('id'));

    // Buscar la rifa en rifasData
    const rifaActual = window.rifasData.find(r => r.id === rifaId);

    if (!rifaActual) {
        alert('Rifa no encontrada');
        window.location.href = '../admin.html';
        return;
    }

    // Referencias a elementos del DOM
    const tituloRifa = document.getElementById('tituloRifa');
    const premioRifa = document.getElementById('premioRifa');
    const fechaRifa = document.getElementById('fechaRifa');
    const precioRifa = document.getElementById('precioRifa');
    const numerosGrid = document.getElementById('numerosGrid');

    // Cargar datos de la rifa
    tituloRifa.textContent = rifaActual.titulo;
    premioRifa.textContent = rifaActual.premio;
    fechaRifa.textContent = formatearFecha(rifaActual.fecha);
    precioRifa.textContent = rifaActual.precio;

    // Generar grid de números
    generarNumeros();

    function generarNumeros() {
        numerosGrid.innerHTML = '';
        
        for (let i = 1; i <= rifaActual.numeros; i++) {
            const numero = document.createElement('div');
            const vendidoA = rifaActual.numerosVendidos[i];
            
            numero.className = 'numero' + (vendidoA ? ' ocupado' : '');
            
            numero.innerHTML = `
                <div class="numero-valor">${i.toString().padStart(2, '0')}</div>
                <div class="numero-nombre">${vendidoA || 'Disponible'}</div>
            `;

            numerosGrid.appendChild(numero);
        }
    }

    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-ES', opciones);
    }
});