<?php
// eliminar_rifa.php
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

try {
    // Comenzar transacción
    $conn->beginTransaction();
    
    // Eliminar primero los boletos asociados a la rifa
    $stmt = $conn->prepare("DELETE FROM boletos WHERE rifa_id = ?");
    $stmt->execute([$rifa_id]);
    
    // Luego eliminar la rifa
    $stmt = $conn->prepare("DELETE FROM rifas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$rifa_id, $usuario_id]);
    
    // Confirmar transacción
    $conn->commit();
    
    // Intentar eliminar las imágenes asociadas si existen
    if (!empty($rifa['imagen1']) && file_exists($rifa['imagen1'])) {
        unlink($rifa['imagen1']);
    }
    
    if (!empty($rifa['imagen2']) && file_exists($rifa['imagen2'])) {
        unlink($rifa['imagen2']);
    }
    
    // Redirigir al panel con mensaje de éxito
    header('Location: panel.php?mensaje=rifa_eliminada');
    exit;
    
} catch (PDOException $e) {
    // Revertir cambios en caso de error
    $conn->rollBack();
    
    // Redirigir al panel con mensaje de error
    header('Location: panel.php?mensaje=error&error=' . urlencode($e->getMessage()));
    exit;
}