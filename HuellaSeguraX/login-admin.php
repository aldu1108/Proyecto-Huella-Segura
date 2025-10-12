<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";

// Verificar si ya hay sesión de admin activa
if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true) {
    header("Location: panel-admin.php");
    exit();
}

// Credenciales de administrador hardcodeadas (simple y seguro para demo)
const ADMIN_EMAIL = "admin@huellasegura.com";
const ADMIN_PASSWORD = "admin123";

// Procesar login de administrador
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    
    if (!empty($email) && !empty($contraseña)) {
        // Verificar credenciales de admin
        if ($email === ADMIN_EMAIL && $contraseña === ADMIN_PASSWORD) {
            // Crear sesión de administrador
            $_SESSION['usuario_id'] = 999; // ID especial para admin
            $_SESSION['usuario_nombre'] = 'Administrador';
            $_SESSION['usuario_apellido'] = 'Sistema';
            $_SESSION['es_admin'] = true;
            $_SESSION['hora_login'] = date('H:i:s');
            
            header("Location: panel-admin.php");
            exit();
        } else {
            $mensaje_error = "Credenciales de administrador incorrectas";
        }
    } else {
        $mensaje_error = "Por favor complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/login-admin.css">
</head>
<body class="admin-login-body" style="background: url('imagenes/fondo-login.png') no-repeat center center fixed; background-size: cover;">
    <!-- Header simple -->
    <div class="login-header">
        <h1 class="login-logo">🛡️ Panel Administrativo</h1>
        <p class="login-subtitle">Acceso restringido para administradores</p>
    </div>

    <!-- Contenedor de login admin -->
    <div class="login-container admin-container">
        <h2 class="login-title">Acceso Administrativo</h2>
        <p class="login-welcome">Ingrese sus credenciales de administrador</p>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message">
                🔒 <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <span class="input-icon">👤</span>
                <input type="email" name="email" class="login-input" placeholder="Email de administrador" required>
            </div>
            
            <div class="input-group">
                <span class="input-icon">🔐</span>
                <input type="password" name="contraseña" class="login-input" placeholder="Contraseña de administrador" required>
            </div>
            
            <button type="submit" class="btn-login admin-btn">🛡️ Acceder al Panel</button>
        </form>
        
        <div class="texto-credencial">
            <span>Credenciales para prueba</span>
        </div>
        
        <!-- Credenciales de prueba (solo para demo) -->
        <div class="demo-credentials">
            <p><strong>📧 Email:</strong> admin@huellasegura.com</p>
            <p><strong>🔑 Contraseña:</strong> admin123</p>
        </div>
        
        <div class="admin-links">
            <a href="login.php">← Volver al login de usuarios</a>
        </div>
        
        <div class="admin-warning">
            <p>⚠️ <strong>Acceso Restringido</strong></p>
            <p>Este panel es solo para administradores autorizados del sistema.</p>
        </div>
    </div>

    <script src="js/login-admin.js"></script>
</body>
</html>