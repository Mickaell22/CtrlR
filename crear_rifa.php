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
    
    // Crear directorio si no existe
    if (!is_dir('img/rifas')) {
        mkdir('img/rifas', 0777, true);
    }
    
    // Procesar imagen 1 (obligatoria)
    $imagen1_path = '';
    if (isset($_FILES['imagen1']) && $_FILES['imagen1']['error'] === UPLOAD_ERR_OK) {
        $imagen1_name = $_FILES['imagen1']['name'];
        $extension = pathinfo($imagen1_name, PATHINFO_EXTENSION);
        $imagen1_new_name = "rifa_{$rifa_id}_1.{$extension}";
        $imagen1_path = "img/rifas/{$imagen1_new_name}";
        
        // Mover la imagen al directorio
        if (!move_uploaded_file($_FILES['imagen1']['tmp_name'], $imagen1_path)) {
            die('Error al subir la imagen principal');
        }
    } else {
        die('La imagen principal es obligatoria');
    }
    
    // Procesar imagen 2 (obligatoria)
    $imagen2_path = '';
    if (isset($_FILES['imagen2']) && $_FILES['imagen2']['error'] === UPLOAD_ERR_OK) {
        $imagen2_name = $_FILES['imagen2']['name'];
        $extension = pathinfo($imagen2_name, PATHINFO_EXTENSION);
        $imagen2_new_name = "rifa_{$rifa_id}_2.{$extension}";
        $imagen2_path = "img/rifas/{$imagen2_new_name}";
        
        // Mover la imagen al directorio
        if (!move_uploaded_file($_FILES['imagen2']['tmp_name'], $imagen2_path)) {
            die('Error al subir la segunda imagen');
        }
    } else {
        die('La segunda imagen es obligatoria');
    }
    
    // Procesar imagen 3 (opcional)
    $imagen3_path = '';
    if (isset($_FILES['imagen3']) && $_FILES['imagen3']['error'] === UPLOAD_ERR_OK) {
        $imagen3_name = $_FILES['imagen3']['name'];
        $extension = pathinfo($imagen3_name, PATHINFO_EXTENSION);
        $imagen3_new_name = "rifa_{$rifa_id}_3.{$extension}";
        $imagen3_path = "img/rifas/{$imagen3_new_name}";
        
        // Mover la imagen al directorio
        if (!move_uploaded_file($_FILES['imagen3']['tmp_name'], $imagen3_path)) {
            die('Error al subir la tercera imagen');
        }
    }
    
    // Procesar imagen 4 (opcional)
    $imagen4_path = '';
    if (isset($_FILES['imagen4']) && $_FILES['imagen4']['error'] === UPLOAD_ERR_OK) {
        $imagen4_name = $_FILES['imagen4']['name'];
        $extension = pathinfo($imagen4_name, PATHINFO_EXTENSION);
        $imagen4_new_name = "rifa_{$rifa_id}_4.{$extension}";
        $imagen4_path = "img/rifas/{$imagen4_new_name}";
        
        // Mover la imagen al directorio
        if (!move_uploaded_file($_FILES['imagen4']['tmp_name'], $imagen4_path)) {
            die('Error al subir la cuarta imagen');
        }
    }
    
    try {
        // Insertar la rifa en la base de datos
        $stmt = $conn->prepare("
            INSERT INTO rifas (usuario_id, titulo, descripcion, premio, fecha_sorteo, precio_boleto, numeros, imagen1, imagen2, imagen3, imagen4, youtube_link) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $imagen3_path,
            $imagen4_path,
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