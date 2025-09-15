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
</head>
<body class="admin-login-body">
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
        
        <div class="divider">
            <span>Credenciales para prueba</span>
        </div>
        
        <!-- Credenciales de prueba (solo para demo) -->
        <div class="demo-credentials">
            <p><strong>📧 Email:</strong> admin@huellasegura.com</p>
            <p><strong>🔑 Contraseña:</strong> admin123</p>
        </div>
        
        <div class="admin-links">
            <a href="login.php">← Volver al login de usuarios</a>
            <a href="login-veterinario.php">Acceso veterinarios →</a>
        </div>
        
        <div class="admin-warning">
            <p>⚠️ <strong>Acceso Restringido</strong></p>
            <p>Este panel es solo para administradores autorizados del sistema.</p>
        </div>
    </div>

    <style>
        .admin-login-body {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .admin-container {
            background: white;
            border: 3px solid #e74c3c;
            box-shadow: 0 8px 32px rgba(231, 76, 60, 0.3);
        }

        .admin-container .login-title {
            color: #c0392b;
        }

        .admin-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .admin-btn:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        .demo-credentials {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
        }

        .demo-credentials p {
            margin: 0.5rem 0;
            font-size: 14px;
        }

        .admin-links {
            text-align: center;
            margin: 1.5rem 0;
        }

        .admin-links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 1rem;
            font-size: 14px;
        }

        .admin-links a:hover {
            text-decoration: underline;
        }

        .admin-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-top: 1rem;
        }

        .admin-warning p {
            margin: 0.25rem 0;
            font-size: 13px;
            color: #856404;
        }
    </style>

    <script>
        // Auto-llenar campos para facilitar pruebas (solo en desarrollo)
        document.addEventListener('DOMContentLoaded', function() {
            // Solo para desarrollo - remove en producción
            const isDevelopment = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            
            if (isDevelopment) {
                document.querySelector('input[name="email"]').value = 'admin@huellasegura.com';
                document.querySelector('input[name="contraseña"]').value = 'admin123';
            }
        });
    </script>
</body>
</html>