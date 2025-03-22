<?php
require_once 'config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// Obtener todas las rifas del usuario actual
$stmt = $conn->prepare("SELECT * FROM rifas WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
$stmt->execute([$usuario_id]);
$rifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Rifas</title>
    <link rel="stylesheet" href="css/admin-styles.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Panel de Administración de Rifas</h1>
            <div>
                <span>Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?></span>
                <a href="logout.php" class="btn-secondary">Cerrar Sesión</a>
                <button id="crearRifa" class="btn-primary">Crear Nueva Rifa</button>
            </div>
        </header>

        <main>
            <div id="listaRifas" class="rifas-grid">
                <?php if (empty($rifas)): ?>
                    <p class="no-rifas">No hay rifas creadas. ¡Crea una nueva!</p>
                <?php else: ?>
                    <?php foreach ($rifas as $rifa): ?>
                        <?php
                        // Obtener el número de boletos vendidos
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM boletos WHERE rifa_id = ? AND estado != 'disponible'");
                        $stmt->execute([$rifa['id']]);
                        $boletos_vendidos = $stmt->fetchColumn();

                        // Calcular el porcentaje vendido
                        $porcentaje_vendido = ($boletos_vendidos / $rifa['numeros']) * 100;
                        ?>
                        <div class="rifa-card">
                            <div class="rifa-images">
                                <?php if (!empty($rifa['imagen1'])): ?>
                                    <img src="<?php echo htmlspecialchars($rifa['imagen1']); ?>" class="rifa-image" alt="Imagen 1">
                                <?php endif; ?>
                                <?php if (!empty($rifa['imagen2'])): ?>
                                    <img src="<?php echo htmlspecialchars($rifa['imagen2']); ?>" class="rifa-image" alt="Imagen 2">
                                <?php endif; ?>
                            </div>
                            <div class="rifa-content">
                                <h3><?php echo htmlspecialchars($rifa['titulo']); ?></h3>
                                <div class="rifa-info">
                                    <p><strong>Premio:</strong> <?php echo htmlspecialchars($rifa['premio']); ?></p>
                                    <p><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($rifa['fecha_sorteo'])); ?>
                                    </p>
                                    <p><strong>Precio:</strong> $<?php echo htmlspecialchars($rifa['precio_boleto']); ?></p>
                                    <p><strong>Números vendidos:</strong> <?php echo $boletos_vendidos; ?> de
                                        <?php echo $rifa['numeros']; ?> (<?php echo number_format($porcentaje_vendido, 1); ?>%)
                                    </p>
                                </div>
                                <div class="rifa-acciones">
                                    <a href="ver_rifa.php?id=<?php echo $rifa['id']; ?>" class="btn-primary">Ver Rifa</a>
                                    <a href="gestionar_numeros.php?id=<?php echo $rifa['id']; ?>"
                                        class="btn-secondary">Gestionar Números</a>
                                    <a href="eliminar_rifa.php?id=<?php echo $rifa['id']; ?>" class="btn-danger"
                                        onclick="return confirm('¿Estás seguro de que quieres eliminar esta rifa?');">Eliminar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <!-- Modal para crear rifa -->
        <div id="modalCrearRifa" class="modal" style="display: none;">
            <div class="modal-content">
                <h2>Nueva Rifa</h2>
                <form id="formRifa" method="POST" action="crear_rifa.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="premio">Premio:</label>
                        <input type="text" id="premio" name="premio" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_sorteo">Fecha del Sorteo:</label>
                        <input type="date" id="fecha_sorteo" name="fecha_sorteo" required>
                    </div>
                    <div class="form-group">
                        <label for="precio_boleto">Precio del Boleto:</label>
                        <input type="number" id="precio_boleto" name="precio_boleto" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="numeros">Cantidad de números:</label>
                        <input type="number" id="numeros" name="numeros" value="50" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción (opcional):</label>
                        <textarea id="descripcion" name="descripcion" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="imagen1"><strong>Imagen Principal:</strong> (Obligatoria)</label>
                        <input type="file" id="imagen1" name="imagen1" accept="image/*" required>
                        <img id="preview1" class="image-preview">
                    </div>
                    <div class="form-group">
                        <label for="imagen2"><strong>Segunda Imagen:</strong> (Obligatoria)</label>
                        <input type="file" id="imagen2" name="imagen2" accept="image/*" required>
                        <img id="preview2" class="image-preview">
                    </div>
                    <div class="form-group">
                        <label for="imagen3">Tercera Imagen: (Opcional)</label>
                        <input type="file" id="imagen3" name="imagen3" accept="image/*">
                        <img id="preview3" class="image-preview">
                    </div>
                    <div class="form-group">
                        <label for="imagen4">Cuarta Imagen: (Opcional)</label>
                        <input type="file" id="imagen4" name="imagen4" accept="image/*">
                        <img id="preview4" class="image-preview">
                    </div>
                    <div class="form-group">
                        <label for="youtube_link">Enlace de YouTube (opcional):</label>
                        <input type="url" id="youtube_link" name="youtube_link">
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn-primary">Crear</button>
                        <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // JavaScript para manejar el modal
            document.addEventListener('DOMContentLoaded', function () {
                // Referencias a elementos
                const btnCrearRifa = document.getElementById('crearRifa');
                const modal = document.getElementById('modalCrearRifa');

                // Event Listeners
                btnCrearRifa.addEventListener('click', function () {
                    modal.style.display = 'block';
                });

                // Función para cerrar modal
                window.cerrarModal = function () {
                    modal.style.display = 'none';
                    document.getElementById('formRifa').reset();

                    // Limpiar previsualizaciones
                    document.querySelectorAll('.image-preview').forEach(img => {
                        img.style.display = 'none';
                        img.src = '';
                    });
                };

                // Cerrar modal al hacer clic fuera
                window.addEventListener('click', function (event) {
                    if (event.target == modal) {
                        cerrarModal();
                    }
                });

                // Previsualización de imágenes
                document.getElementById('imagen1').addEventListener('change', function (e) {
                    previewImage(e.target, 'preview1');
                });

                document.getElementById('imagen2').addEventListener('change', function (e) {
                    previewImage(e.target, 'preview2');
                });

                document.getElementById('imagen3').addEventListener('change', function (e) {
                    previewImage(e.target, 'preview3');
                });

                document.getElementById('imagen4').addEventListener('change', function (e) {
                    previewImage(e.target, 'preview4');
                });

                function previewImage(input, previewId) {
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const preview = document.getElementById(previewId);
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            });
        </script>
</body>

</html>