<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";

// Verificar si ya hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Procesar formulario de login de veterinario
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    
    if (!empty($email) && !empty($contraseña)) {
        // Consulta para verificar veterinario
        $consulta = "SELECT u.id_usuario, u.nombre_usuario, u.apellido_usuario, u.contraseña_usuario, v.id_veterinario, v.especialidad, v.clinica, v.certificado
                     FROM usuarios u 
                     JOIN veterinario v ON u.id_usuario = v.id_usuario 
                     WHERE u.email_usuario = '$email' AND u.estado = 'activo' AND v.certificado = 1";
        $resultado = $conexion->query($consulta);
        
        if ($resultado && $resultado->num_rows > 0) {
            $veterinario = $resultado->fetch_assoc();
            
            // Verificar contraseña
            if ($contraseña == $veterinario['contraseña_usuario']) {
                // Crear sesión de veterinario
                $_SESSION['usuario_id'] = $veterinario['id_usuario'];
                $_SESSION['usuario_nombre'] = $veterinario['nombre_usuario'];
                $_SESSION['usuario_apellido'] = $veterinario['apellido_usuario'];
                $_SESSION['veterinario_id'] = $veterinario['id_veterinario'];
                $_SESSION['es_veterinario'] = true;
                $_SESSION['especialidad'] = $veterinario['especialidad'];
                $_SESSION['clinica'] = $veterinario['clinica'];
                
                header("Location: panel-veterinario.php");
                exit();
            } else {
                $mensaje_error = "Contraseña incorrecta";
            }
        } else {
            $mensaje_error = "Veterinario no encontrado o no certificado";
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
    <title>Login Veterinario - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="login-body-vet">
    <!-- Header centrado -->
    <div class="login-header">
        <p class="portal-text">Portal profesional para veterinarios</p>
        <a href="login.php" class="back-link">← Volver al login de usuarios</a>
    </div>

    <!-- Contenedor de login -->
    <div class="login-container-vet">
        <h2 class="login-title">Login Veterinario</h2>
        <p class="login-welcome">Accede a tu cuenta profesional</p>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <span class="input-icon">📧</span>
                <input type="email" name="email" class="login-input" placeholder="Correo profesional" required>
            </div>
            
            <div class="input-group">
                <span class="input-icon">🔒</span>
                <input type="password" name="contraseña" class="login-input" placeholder="Contraseña" required>
                <button type="button" class="password-toggle">👁</button>
            </div>
            
            <div class="forgot-password">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit" class="btn-login-vet">Iniciar Sesión</button>
        </form>
        
        <div class="divider">
            <span>o</span>
        </div>
        
        <button class="btn-demo" onclick="loginDemo()">
            ❤️ Probar con Cuenta Demo
        </button>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Registrarse</a>
        </div>
        
        <!-- Features veterinario -->
        <div class="features-list">
            <div class="feature-item">
                <span class="feature-icon">📋</span>
                <span>Gestión de historiales médicos</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">📅</span>
                <span>Agenda de citas y turnos</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">👥</span>
                <span>Portal de pacientes integrado</span>
            </div>
        </div>
    </div>

    <script>
        function loginDemo() {
            // Simulación de login demo para veterinario
            window.location.href = 'panel-veterinario.php';
        }
    </script>
    <script src="js/scripts.js"></script>
</body>
</html>