<?php
require_once 'config.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $mensaje = 'Por favor, ingresa tu correo y contraseña';
    } else {
        $stmt = $conn->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $usuario['password'])) {
                // Inicio de sesión exitoso
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                // Redirigir al panel de admin
                header('Location: panel.php');
                exit;
            } else {
                $mensaje = 'Contraseña incorrecta';
            }
        } else {
            $mensaje = 'Usuario no encontrado';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Rifas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth-styles.css">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">
                <!-- Puedes reemplazar esto con tu propio logo -->
                <img src="img/logo.png" alt="Logo Sistema de Rifas" onerror="this.src='img/logo-placeholder.png'; this.onerror=null;">
            </div>
            <h1>Bienvenido de nuevo</h1>
            <p>Ingresa tus credenciales para acceder a tu cuenta</p>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje mensaje-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                <i class="fas fa-envelope"></i>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                <i class="fas fa-lock"></i>
            </div>
            
            <button type="submit" class="btn-auth">Iniciar Sesión</button>
        </form>
        
        <div class="alt-link">
            ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
        </div>
        
        <div class="auth-separator">
            <span>O</span>
        </div>
        
        <div class="alt-link">
            <a href="index.php">Volver a la página principal</a>
        </div>
    </div>
</body>
</html>