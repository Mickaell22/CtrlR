document.addEventListener("DOMContentLoaded", function () {
  // Referencias a elementos del DOM
  const btnCrearRifa = document.getElementById("crearRifa");
  const modal = document.getElementById("modalCrearRifa");
  const formRifa = document.getElementById("formRifa");
  const listaRifas = document.getElementById("listaRifas");

  // Event Listeners
  btnCrearRifa.addEventListener("click", abrirModal);
  formRifa.addEventListener("submit", crearRifa);

  // Cerrar modal al hacer clic fuera
  window.onclick = function (event) {
    if (event.target === modal) {
      cerrarModal();
    }
  };

  // Inicializar y cargar rifas
  window.rifasData = window.rifasData || [];
  cargarRifas();
  console.log("Rifas cargadas:", window.rifasData);

  // Funciones del Modal Principal
  function abrirModal() {
    modal.style.display = "block";
  }

  function cerrarModal() {
    modal.style.display = "none";
    formRifa.reset();
  }

  // Funciones de Gestión de Rifas
  function crearRifa(e) {
    e.preventDefault();

    const imagen1 = document.getElementById("imagen1").files[0];
    const imagen2 = document.getElementById("imagen2").files[0];

    // Generar nombres únicos para las imágenes
    const timestamp = Date.now();
    const imagen1Name = `rifa_${timestamp}_1.png`;
    const imagen2Name = `rifa_${timestamp}_2.png`;

    const nuevaRifa = {
      id: timestamp,
      titulo: document.getElementById("titulo").value,
      premio: document.getElementById("premio").value,
      fecha: document.getElementById("fecha").value,
      precio: document.getElementById("precio").value,
      imagen1: imagen1Name,
      imagen2: imagen2Name,
      numeros: parseInt(document.getElementById("numeros").value),
      numerosVendidos: {},
    };

    // Guardar imágenes
    guardarImagen(imagen1, imagen1Name);
    guardarImagen(imagen2, imagen2Name);

    window.rifasData.push(nuevaRifa);
    mostrarCodigoActualizado();
    cargarRifas();
    cerrarModal();
  }

  function guardarImagen(file, filename) {
    const reader = new FileReader();
    reader.onload = function (e) {
      // Guarda la imagen en la carpeta del proyecto
      const img = new Image();
      img.src = e.target.result;

      // Crear un enlace para descargar la imagen
      const link = document.createElement("a");
      link.download = filename;
      link.href = e.target.result;

      // Guardar en la carpeta img/rifas/
      const path = `img/rifas/${filename}`;
      link.click();

      // También guardar en localStorage como respaldo
      localStorage.setItem(filename, e.target.result);
    };
    reader.readAsDataURL(file);
  }

  document.getElementById("imagen1").addEventListener("change", function (e) {
    previewImage(e.target, "preview1");
  });

  document.getElementById("imagen2").addEventListener("change", function (e) {
    previewImage(e.target, "preview2");
  });

  function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const preview = document.getElementById(previewId);
        preview.src = e.target.result;
        preview.style.display = "block";
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
  function cargarRifas() {
    listaRifas.innerHTML = "";

    if (window.rifasData.length === 0) {
      listaRifas.innerHTML =
        '<p class="no-rifas">No hay rifas creadas. ¡Crea una nueva!</p>';
      return;
    }

    window.rifasData.forEach((rifa) => {
      const numerosVendidos = Object.keys(rifa.numerosVendidos || {}).length;
      const porcentajeVendido = (
        (numerosVendidos / rifa.numeros) *
        100
      ).toFixed(1);

      const rifaCard = document.createElement("div");
      rifaCard.className = "rifa-card";
      rifaCard.innerHTML = `
            <div class="rifa-images">
                <img src="${
                  localStorage.getItem(rifa.imagen1) || "img/default.png"
                }" class="rifa-image" alt="Imagen 1">
                <img src="${
                  localStorage.getItem(rifa.imagen2) || "img/default.png"
                }" class="rifa-image" alt="Imagen 2">
            </div>
            <div class="rifa-content">
                <h3>${rifa.titulo}</h3>
                <div class="rifa-info">
                    <p><strong>Premio:</strong> ${rifa.premio}</p>
                    <p><strong>Fecha:</strong> ${formatearFecha(rifa.fecha)}</p>
                    <p><strong>Precio:</strong> $${rifa.precio}</p>
                    <p><strong>Números vendidos:</strong> ${numerosVendidos} de ${
        rifa.numeros
      } (${porcentajeVendido}%)</p>
                </div>
                <div class="rifa-acciones">
                    <button onclick="verRifa(${
                      rifa.id
                    })" class="btn-primary">Ver Rifa</button>
                    <button onclick="gestionarNumeros(${
                      rifa.id
                    })" class="btn-secondary">Gestionar Números</button>
                    <button onclick="eliminarRifa(${
                      rifa.id
                    })" class="btn-danger">Eliminar</button>
                </div>
            </div>
        `;
      listaRifas.appendChild(rifaCard);
    });
  }

  // Funciones para la Gestión de Números
  window.gestionarNumeros = function (rifaId) {
    const rifa = window.rifasData.find((r) => r.id === rifaId);
    if (!rifa) return;

    const modalExistente = document.getElementById("modalGestion");
    if (modalExistente) modalExistente.remove();

    const numerosVendidos = Object.keys(rifa.numerosVendidos || {}).length;
    const porcentajeVendido = ((numerosVendidos / rifa.numeros) * 100).toFixed(
      1
    );

    const modalHTML = `
            <div id="modalGestion" class="modal">
                <div class="modal-content">
                    <h2 style="margin-bottom: 20px;">Gestionar Números - ${
                      rifa.titulo
                    }</h2>
                    
                    <div class="info-section">
                        <div>
                            <strong>Números vendidos:</strong> ${numerosVendidos} de ${
      rifa.numeros
    } (${porcentajeVendido}%)
                        </div>
                        <div>
                            <strong>Precio por número:</strong> $${rifa.precio}
                        </div>
                    </div>

                    <div class="input-section">
                        <input type="text" 
                               id="nombreComprador" 
                               placeholder="Nombre del comprador" 
                               class="input-nombre"
                               autocomplete="off">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Ingresa un nombre y haz clic en un número disponible para asignarlo
                        </small>
                    </div>

                    <div class="numeros-grid">
                        ${generarGrillaNumeros(rifa)}
                    </div>

                    <div class="buttons" style="text-align: right; margin-top: 20px;">
                        <button onclick="cerrarModalGestion()" class="btn-secondary">Cerrar</button>
                    </div>
                </div>
            </div>
        `;

    document.body.insertAdjacentHTML("beforeend", modalHTML);

    const modal = document.getElementById("modalGestion");
    const numerosElements = modal.querySelectorAll(".numero:not(.ocupado)");

    numerosElements.forEach((numeroElement) => {
      numeroElement.addEventListener("click", function () {
        const numero = parseInt(this.dataset.numero);
        const nombre = document.getElementById("nombreComprador").value.trim();

        if (!nombre) {
          alert("Por favor, ingresa el nombre del comprador");
          document.getElementById("nombreComprador").focus();
          return;
        }

        asignarNumero(rifaId, numero, nombre);
      });
    });

    modal.style.display = "block";
    document.getElementById("nombreComprador").focus();

    modal.onclick = function (event) {
      if (event.target === modal) cerrarModalGestion();
    };
  };

  function generarGrillaNumeros(rifa) {
    let html = "";
    for (let i = 1; i <= rifa.numeros; i++) {
      const numero = i.toString().padStart(2, "0");
      const vendidoA = rifa.numerosVendidos[i] || "";
      const clase = vendidoA ? "numero ocupado" : "numero";

      html += `
                <div class="${clase}" data-numero="${i}">
                    <div class="numero-valor">#${numero}</div>
                    <div class="numero-nombre">${vendidoA || "Disponible"}</div>
                    ${
                      vendidoA
                        ? `
                        <button onclick="liberarNumero(${rifa.id}, ${i})" class="btn-liberar">
                            Liberar
                        </button>
                    `
                        : ""
                    }
                </div>
            `;
    }
    return html;
  }

  // Funciones de Navegación y Utilidades
  window.verRifa = function (rifaId) {
    window.location.href = `html/rifa.html?id=${rifaId}`;
  };

  window.eliminarRifa = function (rifaId) {
    if (!confirm("¿Estás seguro de que quieres eliminar esta rifa?")) return;
    window.rifasData = window.rifasData.filter((rifa) => rifa.id !== rifaId);
    mostrarCodigoActualizado();
    cargarRifas();
  };

  window.cerrarModalGestion = function () {
    const modalGestion = document.getElementById("modalGestion");
    if (modalGestion) modalGestion.remove();
  };

  window.liberarNumero = function (rifaId, numero) {
    if (!confirm("¿Estás seguro de que quieres liberar este número?")) return;

    const rifaIndex = window.rifasData.findIndex((r) => r.id === rifaId);
    if (rifaIndex === -1) return;

    delete window.rifasData[rifaIndex].numerosVendidos[numero];
    mostrarCodigoActualizado();
    gestionarNumeros(rifaId);
  };

  function asignarNumero(rifaId, numero, nombre) {
    const rifaIndex = window.rifasData.findIndex((r) => r.id === rifaId);
    if (rifaIndex === -1) return;

    window.rifasData[rifaIndex].numerosVendidos[numero] = nombre;
    mostrarCodigoActualizado();
    gestionarNumeros(rifaId);
  }

  // Funciones para el Código Actualizado
  function mostrarCodigoActualizado() {
    const codigo = `window.rifasData = ${JSON.stringify(
      window.rifasData,
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
    const codigo = `window.rifasData = ${JSON.stringify(
      window.rifasData,
      null,
      2
    )};`;
    navigator.clipboard
      .writeText(codigo)
      .then(() => alert("Código copiado al portapapeles"))
      .catch((err) => alert("Error al copiar el código"));
  };

  function formatearFecha(fecha) {
    const opciones = { year: "numeric", month: "long", day: "numeric" };
    return new Date(fecha).toLocaleDateString("es-ES", opciones);
  }

  // Hacer globales las funciones necesarias
  window.cerrarModal = cerrarModal;
});
