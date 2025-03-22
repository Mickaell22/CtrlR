<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password) || empty($confirmar_password)) {
        $mensaje = 'Todos los campos son obligatorios';
        $tipo_mensaje = 'error';
    } elseif ($password !== $confirmar_password) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'error';
    } elseif (strlen($password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipo_mensaje = 'error';
    } else {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $mensaje = 'Este correo electrónico ya está registrado';
            $tipo_mensaje = 'error';
        } else {
            // Insertar nuevo usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            
            try {
                $stmt->execute([$nombre, $email, $hashed_password]);
                $mensaje = 'Registro exitoso. Ahora puedes iniciar sesión.';
                $tipo_mensaje = 'exito';
                // Redirigir a la página de inicio de sesión después de 2 segundos
                header("Refresh: 2; URL=login.php");
            } catch (PDOException $e) {
                $mensaje = 'Error al registrar: ' . $e->getMessage();
                $tipo_mensaje = 'error';
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
            <h1>Crear una cuenta</h1>
            <p>Regístrate para crear y gestionar tus propias rifas</p>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                <?php if ($tipo_mensaje === 'error'): ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php else: ?>
                    <i class="fas fa-check-circle"></i>
                <?php endif; ?>
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registro-form">
            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required>
                <i class="fas fa-user"></i>
            </div>
            
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                <i class="fas fa-envelope"></i>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                <i class="fas fa-lock"></i>
            </div>
            
            <div class="form-group">
                <label for="confirmar_password">Confirmar contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" placeholder="Repite tu contraseña" required minlength="6">
                <i class="fas fa-lock"></i>
            </div>
            
            <button type="submit" class="btn-auth">Registrarse</button>
        </form>
        
        <div class="alt-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </div>
        
        <div class="auth-separator">
            <span>O</span>
        </div>
        
        <div class="alt-link">
            <a href="index.php">Volver a la página principal</a>
        </div>
    </div>

    <script>
        // Validación de cliente simple
        document.getElementById('registro-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmarPassword = document.getElementById('confirmar_password').value;
            
            if (password !== confirmarPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
            }
        });
    </script>
</body>
</html>