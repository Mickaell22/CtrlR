<?php
require_once 'config.php';

// Obtener todas las rifas activas
$stmt = $conn->prepare("
    SELECT r.*, u.nombre as creador_nombre 
    FROM rifas r 
    JOIN usuarios u ON r.usuario_id = u.id 
    WHERE r.estado = 'activa' AND r.fecha_sorteo >= CURDATE()
    ORDER BY r.fecha_sorteo ASC
");
$stmt->execute();
$rifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Rifas</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .rifas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .rifa-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .rifa-card h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .rifa-card .rifa-info {
            margin-bottom: 15px;
        }
        
        .rifa-card .rifa-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .welcome-section {
            background-color: #ecf0f1;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-section h2 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .no-rifas {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <h1>Sistema de Rifas</h1>
            <div class="auth-buttons">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="panel.php" class="btn-primary">Mi Panel</a>
                    <a href="logout.php" class="btn-secondary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary">Iniciar Sesión</a>
                    <a href="register.php" class="btn-secondary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="welcome-section">
            <h2>¡Bienvenido al Sistema de Rifas!</h2>
            <p>Participa en rifas o crea las tuyas propias. Cada usuario puede crear rifas y gestionar sus propios sorteos.</p>
            <?php if (!isset($_SESSION['usuario_id'])): ?>
                <p>Para crear tus propias rifas, <a href="register.php">regístrate</a> o <a href="login.php">inicia sesión</a>.</p>
            <?php endif; ?>
        </div>
        
        <h2>Rifas Disponibles</h2>
        
        <?php if (empty($rifas)): ?>
            <div class="no-rifas">
                <p>No hay rifas disponibles en este momento.</p>
            </div>
        <?php else: ?>
            <div class="rifas-grid">
                <?php foreach ($rifas as $rifa): ?>
                    <div class="rifa-card">
                        <?php if (!empty($rifa['imagen1'])): ?>
                            <img src="<?php echo htmlspecialchars($rifa['imagen1']); ?>" class="rifa-thumbnail" alt="<?php echo htmlspecialchars($rifa['titulo']); ?>">
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($rifa['titulo']); ?></h3>
                        
                        <div class="rifa-info">
                            <p><strong>Premio:</strong> <?php echo htmlspecialchars($rifa['premio']); ?></p>
                            <p><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($rifa['fecha_sorteo'])); ?></p>
                            <p><strong>Precio:</strong> $<?php echo htmlspecialchars($rifa['precio_boleto']); ?></p>
                            <p><strong>Organizador:</strong> <?php echo htmlspecialchars($rifa['creador_nombre']); ?></p>
                        </div>
                        
                        <a href="ver_rifa.php?id=<?php echo $rifa['id']; ?>" class="btn-primary">Ver Rifa</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>