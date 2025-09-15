<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";

// Verificar si ya hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Procesar formulario de login
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    
    if (!empty($email) && !empty($contraseña)) {
        // Consulta muy básica para verificar usuario
        $consulta = "SELECT id_usuario, nombre_usuario, apellido_usuario, contraseña_usuario FROM usuarios WHERE email_usuario = '$email' AND estado = 'activo'";
        $resultado = $conexion->query($consulta);
        
        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            
            // Verificar contraseña (en un caso real usarías password_verify)
            if ($contraseña == $usuario['contraseña_usuario']) {
                // Crear sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];
                $_SESSION['usuario_apellido'] = $usuario['apellido_usuario'];
                
                header("Location: index.php");
                exit();
            } else {
                $mensaje_error = "Contraseña incorrecta";
            }
        } else {
            $mensaje_error = "Usuario no encontrado";
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
    <title>Iniciar Sesión - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="login-body" style="background: url('fondo-login.png') no-repeat center center fixed;">

    <!-- Header centrado -->
    <div class="login-header">
        <h1 class="login-logo">Huella Segura</h1>
        <p class="login-subtitle">Tu compañero para el cuidado de mascotas</p>
    </div>

    <!-- Contenedor de login -->
    <div class="login-container">
        <h2 class="login-title">Iniciar Sesión</h2>
        <p class="login-welcome">Bienvenido de vuelta a PetCare</p>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <span class="input-icon">📧</span>
                <input type="email" name="email" class="login-input" placeholder="Correo electrónico" required>
            </div>
            
            <div class="input-group">
                <span class="input-icon">🔒</span>
                <input type="password" name="contraseña" class="login-input" placeholder="Contraseña" required>
                <button type="button" class="password-toggle">👁</button>
            </div>
            
            <div class="forgot-password">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
        
        <div class="divider">
            <span>o</span>
        </div>
        
        <button class="btn-demo" onclick="loginDemo()">
            ❤️ Probar con Cuenta Demo
        </button>
        
        <button class="btn-veterinario" onclick="window.location.href='login-veterinario.php'">
            🩺 Iniciar Sesión como Veterinario
        </button>
        
        <button class="btn-admin" onclick="window.location.href='login-admin.php'">
            🛡️ Acceso Administrativo
        </button>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Registrarse</a>
        </div>
        
        
    </div>

    <script>
        function loginDemo() {
            // Crear usuario demo temporalmente
            fetch('crear-demo.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Enviar formulario con datos demo
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="email" value="${data.email}">
                        <input type="hidden" name="contraseña" value="${data.password}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
    <script src="js/scripts.js"></script>
</body>
</html>