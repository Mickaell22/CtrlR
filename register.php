<?php
require_once 'config.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password) || empty($confirmar_password)) {
        $mensaje = 'Todos los campos son obligatorios';
    } elseif ($password !== $confirmar_password) {
        $mensaje = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $mensaje = 'Este correo electrónico ya está registrado';
        } else {
            // Insertar nuevo usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            
            try {
                $stmt->execute([$nombre, $email, $hashed_password]);
                $mensaje = 'Registro exitoso. Ahora puedes iniciar sesión.';
                // Redirigir a la página de inicio de sesión después de 2 segundos
                header("Refresh: 2; URL=login.php");
            } catch (PDOException $e) {
                $mensaje = 'Error al registrar: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Rifas</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Crear una cuenta</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirmar_password">Confirmar contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" required>
            </div>
            
            <button type="submit" class="btn">Registrarse</button>
        </form>
        
        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
</body>
</html>