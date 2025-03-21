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
</head>
<body>
    <div class="container">
        <h1>Iniciar Sesión</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        
        <p>¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
    </div>
</body>
</html>