<?php
require_once 'config.php';

$rifa_id = $_GET['id'] ?? 0;
$modo_admin = isset($_SESSION['usuario_id']) ? true : false;

// Verificar que la rifa existe
$stmt = $conn->prepare("SELECT r.*, u.nombre as creador_nombre FROM rifas r JOIN usuarios u ON r.usuario_id = u.id WHERE r.id = ?");
$stmt->execute([$rifa_id]);
$rifa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rifa) {
    // La rifa no existe
    header('Location: index.php');
    exit;
}

// Si está en modo admin, verificar que la rifa pertenece al usuario
if ($modo_admin && $rifa['usuario_id'] != $_SESSION['usuario_id']) {
    // La rifa no pertenece al usuario
    header('Location: panel.php');
    exit;
}

// Obtener todos los boletos de la rifa
$stmt = $conn->prepare("SELECT * FROM boletos WHERE rifa_id = ? ORDER BY numero");
$stmt->execute([$rifa_id]);
$boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar asignación de números (solo en modo admin)
if ($modo_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar'])) {
    $numero = $_POST['numero'] ?? 0;
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    if (empty($nombre) || empty($numero)) {
        $mensaje_error = "Por favor, ingresa el nombre del comprador y selecciona un número";
    } else {
        try {
            $stmt = $conn->prepare("
                UPDATE boletos 
                SET nombre_comprador = ?, email_comprador = ?, telefono_comprador = ?, estado = 'vendido', fecha_compra = NOW()
                WHERE rifa_id = ? AND numero = ? AND estado = 'disponible'
            ");

            $stmt->execute([$nombre, $email, $telefono, $rifa_id, $numero]);

            if ($stmt->rowCount() > 0) {
                $mensaje_exito = "Número asignado correctamente";
            } else {
                $mensaje_error = "El número ya está ocupado o no existe";
            }

            // Refrescar la lista de boletos
            $stmt = $conn->prepare("SELECT * FROM boletos WHERE rifa_id = ? ORDER BY numero");
            $stmt->execute([$rifa_id]);
            $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $mensaje_error = "Error al asignar el número: " . $e->getMessage();
        }
    }
}

// Procesar liberación de números (solo en modo admin)
if ($modo_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['liberar'])) {
    $numero = $_POST['numero'] ?? 0;

    try {
        $stmt = $conn->prepare("
            UPDATE boletos 
            SET nombre_comprador = NULL, email_comprador = NULL, telefono_comprador = NULL, estado = 'disponible', fecha_compra = NULL
            WHERE rifa_id = ? AND numero = ?
        ");

        $stmt->execute([$rifa_id, $numero]);

        if ($stmt->rowCount() > 0) {
            $mensaje_exito = "Número liberado correctamente";
        } else {
            $mensaje_error = "No se pudo liberar el número";
        }

        // Refrescar la lista de boletos
        $stmt = $conn->prepare("SELECT * FROM boletos WHERE rifa_id = ? ORDER BY numero");
        $stmt->execute([$rifa_id]);
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $mensaje_error = "Error al liberar el número: " . $e->getMessage();
    }
}

// Formatear fecha
$fecha_formateada = date("d/m/Y", strtotime($rifa['fecha_sorteo']));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($rifa['titulo']); ?> - Rifa</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .mensaje {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .numero.seleccionado {
            border-color: #3498db;
            background-color: #e1f0fa;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="rifa-header">
            <h1><?php echo htmlspecialchars($rifa['titulo']); ?></h1>

            <?php if (!empty($rifa['imagen1']) || !empty($rifa['imagen2'])): ?>
                <div class="rifa-images">
                    <?php if (!empty($rifa['imagen1'])): ?>
                        <img src="<?php echo htmlspecialchars($rifa['imagen1']); ?>" class="rifa-image" alt="Imagen 1">
                    <?php endif; ?>

                    <?php if (!empty($rifa['imagen2'])): ?>
                        <img src="<?php echo htmlspecialchars($rifa['imagen2']); ?>" class="rifa-image" alt="Imagen 2">
                    <?php endif; ?>

                    <?php if (!empty($rifa['imagen3'])): ?>
                        <img src="<?php echo htmlspecialchars($rifa['imagen3']); ?>" class="rifa-image" alt="Imagen 3">
                    <?php endif; ?>

                    <?php if (!empty($rifa['imagen4'])): ?>
                        <img src="<?php echo htmlspecialchars($rifa['imagen4']); ?>" class="rifa-image" alt="Imagen 4">
                    <?php endif; ?>

                    <?php if (!empty($rifa['youtube_link'])): ?>
                        <a href="<?php echo htmlspecialchars($rifa['youtube_link']); ?>" target="_blank"
                            class="youtube-link">Ver Video del Premio</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="rifa-info">
                <p>Premio: <span><?php echo htmlspecialchars($rifa['premio']); ?></span></p>
                <p>Fecha: <span><?php echo $fecha_formateada; ?></span></p>
                <p class="precio">Precio: $<span><?php echo htmlspecialchars($rifa['precio_boleto']); ?></span></p>

                <?php if (!empty($rifa['descripcion'])): ?>
                    <p>Descripción: <?php echo nl2br(htmlspecialchars($rifa['descripcion'])); ?></p>
                <?php endif; ?>

                <p>Organizado por: <?php echo htmlspecialchars($rifa['creador_nombre']); ?></p>
            </div>
        </div>

        <?php if (isset($mensaje_exito)): ?>
            <div class="mensaje mensaje-exito"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
            <div class="mensaje mensaje-error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <?php if ($modo_admin): ?>
            <div id="adminControls" class="admin-controls">
                <form method="POST" action="">
                    <input type="text" id="nombreParticipante" name="nombre"
                        placeholder="Ingrese el nombre del participante" class="input-nombre" required>
                    <input type="email" id="emailParticipante" name="email" placeholder="Email del participante (opcional)"
                        class="input-nombre">
                    <input type="tel" id="telefonoParticipante" name="telefono"
                        placeholder="Teléfono del participante (opcional)" class="input-nombre">
                    <input type="hidden" id="numeroSeleccionado" name="numero" value="">
                    <button type="submit" name="asignar" class="btn-primary" disabled id="btnAsignar">Asignar
                        Número</button>
                    <p class="instruccion-admin">Ingresa un nombre y haz clic en un número disponible para asignarlo</p>
                </form>
            </div>
        <?php endif; ?>

        <div id="numerosGrid" class="numeros-grid">
            <?php foreach ($boletos as $boleto): ?>
                <?php
                $clase = $boleto['estado'] !== 'disponible' ? 'numero ocupado' : 'numero';
                $numero_formateado = str_pad($boleto['numero'], 2, '0', STR_PAD_LEFT);
                ?>
                <div class="<?php echo $clase; ?>" data-numero="<?php echo $boleto['numero']; ?>">
                    <div class="numero-valor">#<?php echo $numero_formateado; ?></div>
                    <div class="numero-nombre">
                        <?php echo $boleto['estado'] !== 'disponible' ? htmlspecialchars($boleto['nombre_comprador']) : 'Disponible'; ?>
                    </div>
                    <?php if ($modo_admin && $boleto['estado'] !== 'disponible'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="numero" value="<?php echo $boleto['numero']; ?>">
                            <button type="submit" name="liberar" class="btn-danger">Liberar</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="instrucciones">
            <h3>Instrucciones:</h3>
            <ol>
                <li>Revisa los números disponibles (los que no tienen nombre)</li>
                <li>Anota el número que te interesa</li>
                <li>Contacta al organizador para apartar tu número</li>
            </ol>
        </div>

        <div class="acciones">
            <?php if ($modo_admin): ?>
                <a href="panel.php" class="btn-secondary">Volver al Panel</a>
            <?php else: ?>
                <a href="index.php" class="btn-secondary">Ver otras rifas</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($modo_admin): ?>
        <script>
            // JavaScript para manejar la selección de números
            document.addEventListener('DOMContentLoaded', function () {
                const numerosDisponibles = document.querySelectorAll('.numero:not(.ocupado)');
                const numeroSeleccionado = document.getElementById('numeroSeleccionado');
                const btnAsignar = document.getElementById('btnAsignar');

                numerosDisponibles.forEach(numero => {
                    numero.addEventListener('click', function () {
                        // Quitar selección previa
                        document.querySelectorAll('.numero.seleccionado').forEach(n => {
                            n.classList.remove('seleccionado');
                        });

                        // Agregar selección
                        this.classList.add('seleccionado');

                        // Establecer valor en el input oculto
                        numeroSeleccionado.value = this.dataset.numero;

                        // Habilitar botón
                        btnAsignar.disabled = false;
                    });
                });
            });
        </script>
    <?php endif; ?>
</body>

</html>