<?php
require_once 'config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $titulo = $_POST['titulo'] ?? '';
    $premio = $_POST['premio'] ?? '';
    $fecha_sorteo = $_POST['fecha_sorteo'] ?? '';
    $precio_boleto = $_POST['precio_boleto'] ?? '';
    $numeros = $_POST['numeros'] ?? 50;
    $descripcion = $_POST['descripcion'] ?? '';
    $youtube_link = $_POST['youtube_link'] ?? '';
    
    // Validaciones básicas
    if (empty($titulo) || empty($premio) || empty($fecha_sorteo) || empty($precio_boleto) || empty($numeros)) {
        die('Por favor, complete todos los campos obligatorios');
    }
    
    // Generar un ID único para la rifa
    $rifa_id = time();
    
    // Procesar imagen 1 (obligatoria)
    $imagen1_path = '';
    if (isset($_FILES['imagen1']) && $_FILES['imagen1']['error'] === UPLOAD_ERR_OK) {
        $imagen1_name = $_FILES['imagen1']['name'];
        $extension = pathinfo($imagen1_name, PATHINFO_EXTENSION);
        $imagen1_new_name = "rifa_{$rifa_id}_1.{$extension}";
        $imagen1_path = "img/rifas/{$imagen1_new_name}";
        
        // Crear directorio si no existe
        if (!is_dir('img/rifas')) {
            mkdir('img/rifas', 0777, true);
        }
        
        // Mover la imagen al directorio
        if (!move_uploaded_file($_FILES['imagen1']['tmp_name'], $imagen1_path)) {
            die('Error al subir la imagen principal');
        }
    } else {
        die('La imagen principal es obligatoria');
    }
    
    // Procesar imagen 2 (opcional)
    $imagen2_path = '';
    if (isset($_FILES['imagen2']) && $_FILES['imagen2']['error'] === UPLOAD_ERR_OK) {
        $imagen2_name = $_FILES['imagen2']['name'];
        $extension = pathinfo($imagen2_name, PATHINFO_EXTENSION);
        $imagen2_new_name = "rifa_{$rifa_id}_2.{$extension}";
        $imagen2_path = "img/rifas/{$imagen2_new_name}";
        
        // Mover la imagen al directorio
        if (!move_uploaded_file($_FILES['imagen2']['tmp_name'], $imagen2_path)) {
            die('Error al subir la imagen secundaria');
        }
    }
    
    try {
        // Insertar la rifa en la base de datos
        $stmt = $conn->prepare("
            INSERT INTO rifas (usuario_id, titulo, descripcion, premio, fecha_sorteo, precio_boleto, numeros, imagen1, imagen2, youtube_link) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $usuario_id,
            $titulo,
            $descripcion,
            $premio,
            $fecha_sorteo,
            $precio_boleto,
            $numeros,
            $imagen1_path,
            $imagen2_path,
            $youtube_link
        ]);
        
        $id_rifa = $conn->lastInsertId();
        
        // Crear todos los boletos disponibles para esta rifa
        $stmt_boletos = $conn->prepare("
            INSERT INTO boletos (rifa_id, numero, estado) 
            VALUES (?, ?, 'disponible')
        ");
        
        for ($i = 1; $i <= $numeros; $i++) {
            $stmt_boletos->execute([$id_rifa, $i]);
        }
        
        // Redirigir al panel principal
        header('Location: panel.php');
        exit;
        
    } catch (PDOException $e) {
        die('Error al crear la rifa: ' . $e->getMessage());
    }
}

// Si no es POST, redirigir al panel
header('Location: panel.php');
exit;