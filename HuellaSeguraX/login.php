<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";

// Verificar si ya hay sesión activa
if (isset($_SESSION['usuario_id']) && $_SESSION['rol'] != 'demo' && $_SESSION['rol'] != 'veterinario') {
    header("Location: index.php");
    exit();
} elseif (isset($_SESSION['usuario_id']) && $_SESSION['rol'] == 'veterinario') {
    header("Location: veterinaria.php");
    exit();
}

if (isset($_POST['demo_login'])) {
    $_SESSION['usuario_id'] = -1; // ID especial para demo
    $_SESSION['usuario_nombre'] = 'Demo';
    $_SESSION['usuario_apellido'] = 'User'; 
    $_SESSION['rol'] = 'demo';
    header("Location: index.php");
    exit();
}
// Procesar formulario de login
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];

    if (!empty($email) && !empty($contraseña)) {
        // Consulta muy básica para verificar usuario
        $consulta = "
            SELECT id_usuario, nombre_usuario, apellido_usuario, contraseña_usuario, rol
            FROM usuarios
            WHERE email_usuario = '$email'
            AND estado = 'activo'
        ";

        $resultado = $conexion->query($consulta);

        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();

            // Verificar contraseña (en un caso real usarías password_verify)
            if ($contraseña == $usuario['contraseña_usuario']) {
                
                // Verificar si es veterinario pendiente (tiene registro en tabla veterinario con certificado = 0)
                $consulta_vet_pendiente = "SELECT certificado FROM veterinario WHERE id_usuario = " . $usuario['id_usuario'];
                $resultado_vet_pendiente = $conexion->query($consulta_vet_pendiente);
                
                if ($resultado_vet_pendiente && $resultado_vet_pendiente->num_rows > 0) {
                    // Es veterinario
                    $vet_data = $resultado_vet_pendiente->fetch_assoc();
                    if ($vet_data['certificado'] != 1) {
                        // Veterinario NO aprobado
                        $mensaje_error = "Tu cuenta de veterinario está pendiente de aprobación por el administrador. Te notificaremos por email cuando sea aprobada.";
                    } else {
                        // Veterinario aprobado, crear sesión
                        $_SESSION['usuario_id'] = $usuario['id_usuario'];
                        $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];
                        $_SESSION['usuario_apellido'] = $usuario['apellido_usuario'];
                        $_SESSION['rol'] = $usuario['rol'];

                        header("Location: veterinaria.php");
                        exit();
                    }
                } else {
                    // Usuario normal, crear sesión directamente
                    $_SESSION['usuario_id'] = $usuario['id_usuario'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];
                    $_SESSION['usuario_apellido'] = $usuario['apellido_usuario'];
                    $_SESSION['rol'] = $usuario['rol'];

                    header("Location: index.php");
                    exit();
                }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body class="login-body"
      style="background: url('imagenes/fondo-login.png') no-repeat center center fixed;
             background-size: cover;">

    <!-- Header centrado -->
    <div class="login-header">
        <h1 class="login-logo">Huella Segura</h1>
        <p class="login-subtitle">Tu compañero para el cuidado de mascotas 🐕</p>
    </div>

    <!-- Contenedor de login -->
    <div class="login-container">
        <h2 class="login-title">Iniciar Sesión</h2>
        <p class="login-welcome">¡Bienvenido de vuelta a Huella Segura!</p>

        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <input type="email"
                       name="email"
                       class="login-input"
                       placeholder="Ingrese su correo electrónico"
                       required>
            </div>

            <div class="input-group">
                <input type="password"
                       name="contraseña"
                       class="login-input"
                       placeholder="Ingrese su contraseña"
                       required>
                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="forgot-password">
                <a href="recuperar-contraseña.php">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="divider">
            <span>o</span>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="demo_login" value="1">
            <button type="submit" class="btn-demo">
                ❤️ Probar con Cuenta Demo
            </button>
        </form>

        <button class="btn-veterinario" onclick="window.location.href='registro-veterinario.php'">
            🩺 Registrarse como Veterinario
        </button>

        <button class="btn-admin" onclick="window.location.href='login-admin.php'">
            🛡️ Acceso Administrativo
        </button>

        <div class="register-link">
            ¿No tienes cuenta?
            <a href="registro.php">Registrarse</a>
        </div>
    </div>

    <script src="js/login.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
