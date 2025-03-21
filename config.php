<?php
// config.php - Configuración de la base de datos
$host = 'localhost';
$dbname = 'sistema_rifas';
$username = 'root';
$password = ''; // Por defecto en XAMPP no tiene contraseña

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    die();
}

// Iniciar sesión
session_start();
?>