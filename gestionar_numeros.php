<?php
require_once 'config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$rifa_id = $_GET['id'] ?? 0;

// Verificar que la rifa existe y pertenece al usuario
$stmt = $conn->prepare("SELECT * FROM rifas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$rifa_id, $usuario_id]);
$rifa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rifa) {
    // La rifa no existe o no pertenece al usuario
    header('Location: panel.php');
    exit;
}

// Obtener todos los boletos de la rifa
$stmt = $conn->prepare("SELECT * FROM boletos WHERE rifa_id = ? ORDER BY numero");
$stmt->execute([$rifa_id]);
$boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar boletos vendidos
$stmt = $conn->prepare("SELECT COUNT(*) FROM boletos WHERE rifa_id = ? AND estado != 'disponible'");
$stmt->execute([$rifa_id]);
$boletos_vendidos = $stmt->fetchColumn();

// Calcular porcentaje vendido
$porcentaje_vendido = ($boletos_vendidos / count($boletos)) * 100;

// Procesar asignación de números
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar'])) {
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
            
            // Actualizar contadores
            $stmt = $conn->prepare("SELECT COUNT(*) FROM boletos WHERE rifa_id = ? AND estado != 'disponible'");
            $stmt->execute([$rifa_id]);
            $boletos_vendidos = $stmt->fetchColumn();
            $porcentaje_vendido = ($boletos_vendidos / count($boletos)) * 100;
        } catch (PDOException $e) {
            $mensaje_error = "Error al asignar el número: " . $e->getMessage();
        }
    }
}

// Procesar liberación de números
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['liberar'])) {
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
        
        // Actualizar contadores
        $stmt = $conn->prepare("SELECT COUNT(*) FROM boletos WHERE rifa_id = ? AND estado != 'disponible'");
        $stmt->execute([$rifa_id]);
        $boletos_vendidos = $stmt->fetchColumn();
        $porcentaje_vendido = ($boletos_vendidos / count($boletos)) * 100;
    } catch (PDOException $e) {
        $mensaje_error = "Error al liberar el número: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Números - <?php echo htmlspecialchars($rifa['titulo']); ?></title>
    <link rel="stylesheet" href="css/admin-styles.css">
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
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestionar Números - <?php echo htmlspecialchars($rifa['titulo']); ?></h1>
            <a href="panel.php" class="btn-secondary">Volver al Panel</a>
        </header>
        
        <?php if (isset($mensaje_exito)): ?>
            <div class="mensaje mensaje-exito"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        
        <?php if (isset($mensaje_error)): ?>
            <div class="mensaje mensaje-error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>
        
        <div class="info-section">
            <div>
                <strong>Números vendidos:</strong> <?php echo $boletos_vendidos; ?> de <?php echo count($boletos); ?> (<?php echo number_format($porcentaje_vendido, 1); ?>%)
            </div>
            <div>
                <strong>Precio por número:</strong> $<?php echo htmlspecialchars($rifa['precio_boleto']); ?>
            </div>
        </div>
        
        <div class="input-section">
            <form method="POST" action="">
                <input type="text" id="nombreComprador" name="nombre" placeholder="Nombre del comprador" class="input-nombre" required>
                <input type="email" id="emailComprador" name="email" placeholder="Email del comprador (opcional)" class="input-nombre">
                <input type="tel" id="telefonoComprador" name="telefono" placeholder="Teléfono del comprador (opcional)" class="input-nombre">
                <input type="hidden" id="numeroSeleccionado" name="numero" value="">
                <button type="submit" name="asignar" class="btn-primary" disabled id="btnAsignar">Asignar Número</button>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Ingresa un nombre y haz clic en un número disponible para asignarlo
                </small>
            </form>
        </div>
        
        <div class="numeros-grid">
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
                    <?php if ($boleto['estado'] !== 'disponible'): ?>
                        <form method="POST" action="" style="margin-top: 5px;">
                            <input type="hidden" name="numero" value="<?php echo $boleto['numero']; ?>">
                            <button type="submit" name="liberar" class="btn-liberar">Liberar</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        // JavaScript para manejar la selección de números
        document.addEventListener('DOMContentLoaded', function() {
            const numerosDisponibles = document.querySelectorAll('.numero:not(.ocupado)');
            const numeroSeleccionado = document.getElementById('numeroSeleccionado');
            const btnAsignar = document.getElementById('btnAsignar');
            
            numerosDisponibles.forEach(numero => {
                numero.addEventListener('click', function() {
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
</body>
</html>