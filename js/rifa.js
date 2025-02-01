document.addEventListener("DOMContentLoaded", function () {
  // Obtener el ID de la URL
  const params = new URLSearchParams(window.location.search);
  const rifaId = parseInt(params.get("id"));

  // Obtener todas las rifas
  const rifas = JSON.parse(localStorage.getItem("rifas")) || [];
  // Encontrar la rifa específica
  const rifaActual = rifas.find((r) => r.id === rifaId);

  if (!rifaActual) {
    alert("Rifa no encontrada");
    window.location.href = "../admin.html";
    return;
  }

  // Hacer la rifa actual accesible globalmente
  window.rifaData = rifaActual;

  // Referencias a elementos del DOM
  const tituloRifa = document.getElementById("tituloRifa");
  const premioRifa = document.getElementById("premioRifa");
  const fechaRifa = document.getElementById("fechaRifa");
  const precioRifa = document.getElementById("precioRifa");
  const numerosGrid = document.getElementById("numerosGrid");
  const nombreInput = document.getElementById("nombreParticipante");
  const adminSection = document.getElementById("adminSection");

  // Variable para modo administrador
  const isAdmin = window.location.search.includes("admin=true");

  if (isAdmin && adminSection) {
    adminSection.style.display = "block";
  }

  // Cargar datos iniciales
  cargarDatosRifa();

  function cargarDatosRifa() {
    tituloRifa.textContent = window.rifaData.titulo;
    premioRifa.textContent = window.rifaData.premio;
    fechaRifa.textContent = formatearFecha(window.rifaData.fecha);
    precioRifa.textContent = window.rifaData.precio;

    generarNumeros();
  }

  function generarNumeros() {
    numerosGrid.innerHTML = "";

    for (let i = 1; i <= window.rifaData.numeros; i++) {
      const numero = document.createElement("div");
      const vendidoA = window.rifaData.numerosVendidos[i];

      numero.className = "numero" + (vendidoA ? " ocupado" : "");

      numero.innerHTML = `
            <div class="numero-valor">${i.toString().padStart(2, "0")}</div>
            <div class="numero-nombre">${vendidoA || "Disponible"}</div>
            ${
              isAdmin && vendidoA
                ? `
                <button onclick="liberarNumero(${i})" class="btn-danger">Liberar</button>
            `
                : ""
            }
        `;

      if (isAdmin && !vendidoA) {
        numero.addEventListener("click", () => seleccionarNumero(i));
      }

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
