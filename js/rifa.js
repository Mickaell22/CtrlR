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
    numerosGrid.innerHTML = "";

    for (let i = 1; i <= rifaActual.numeros; i++) {
      const numero = document.createElement("div");
      const vendidoA = rifaActual.numerosVendidos[i];

      numero.className = "numero" + (vendidoA ? " ocupado" : "");

      numero.innerHTML = `
            <div class="numero-valor">${i.toString().padStart(2, "0")}</div>
            <div class="numero-nombre">${vendidoA || "Disponible"}</div>
        `;

      numerosGrid.appendChild(numero);
    }
  }

  function seleccionarNumero(numero) {
    if (!isAdmin) return;

    const nombre = nombreInput.value.trim();

    if (!nombre) {
      alert("Por favor, ingrese su nombre primero");
      nombreInput.focus();
      return;
    }

    // Actualizar datos locales
    window.rifaData.numerosVendidos[numero] = nombre;

    // Actualizar en localStorage
    actualizarRifaEnStorage();

    nombreInput.value = "";
    generarNumeros();
    mostrarCodigo();
  }

  window.liberarNumero = function (numero) {
    if (!isAdmin) return;
    if (!confirm("¿Estás seguro de que quieres liberar este número?")) return;

    delete window.rifaData.numerosVendidos[numero];
    actualizarRifaEnStorage();
    generarNumeros();
    mostrarCodigo();
  };

  function actualizarRifaEnStorage() {
    const rifas = JSON.parse(localStorage.getItem("rifas")) || [];
    const index = rifas.findIndex((r) => r.id === rifaId);
    if (index !== -1) {
      rifas[index] = window.rifaData;
      localStorage.setItem("rifas", JSON.stringify(rifas));
    }
  }

  function mostrarCodigo() {
    const codigo = `window.rifaData = ${JSON.stringify(
      window.rifaData,
      null,
      2
    )};`;

    let codigoElement = document.getElementById("codigoActualizado");
    if (!codigoElement) {
      codigoElement = document.createElement("div");
      codigoElement.id = "codigoActualizado";
      codigoElement.className = "codigo-container";
      document.body.appendChild(codigoElement);
    }

    codigoElement.innerHTML = `
            <h3>Código Actualizado:</h3>
            <p>Copia este código y reemplázalo en data.js:</p>
            <pre><code>${codigo}</code></pre>
            <button onclick="copiarCodigo()" class="btn-primary">Copiar Código</button>
        `;
  }

  window.copiarCodigo = function () {
    const codigo = `window.rifaData = ${JSON.stringify(
      window.rifaData,
      null,
      2
    )};`;
    navigator.clipboard
      .writeText(codigo)
      .then(() => alert("Código copiado al portapapeles"))
      .catch((err) => {
        console.error("Error al copiar:", err);
        alert("Error al copiar el código");
      });
  };

  function formatearFecha(fecha) {
    const opciones = { year: "numeric", month: "long", day: "numeric" };
    return new Date(fecha).toLocaleDateString("es-ES", opciones);
  }

  // Si es admin, agregar estilos para el contenedor de código
  if (isAdmin) {
    const style = document.createElement("style");
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
            }
        `;
    document.head.appendChild(style);
  }
});
