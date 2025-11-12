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

<body class="login-body" style="background: url('imagenes/fondo-login.png') no-repeat center center fixed;
             background-size: cover;">

    <!-- Header centrado -->
    <div class="login-header">
        <h1 class="login-logo">Huella Segura</h1>
        <p class="login-subtitle">Tu compañero para el cuidado de mascotas</p>
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
                <input type="email" name="email" class="login-input" placeholder="Ingrese su correo electrónico"
                    required>
            </div>

            <div class="input-group">
                <input type="password" name="contraseña" class="login-input" placeholder="Ingrese su contraseña"
                    required>
                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="divider">
            <span>o</span>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="demo_login" value="1">
            <button type="submit" class="btn-demo">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="#8B1A10">
                    <path d="M0 0h24v24H0V0z" fill="none" />
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>Probar con Cuenta Demo
            </button>
        </form>

        <button class="btn-veterinario" onclick="window.location.href='registro-veterinario.php'">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#e3e3e3">
                <path
                    d="M540-80q-108 0-184-76t-76-184v-23q-86-14-143-80.5T80-600v-240h120v-40h80v160h-80v-40h-40v160q0 66 47 113t113 47q66 0 113-47t47-113v-160h-40v40h-80v-160h80v40h120v240q0 90-57 156.5T360-363v23q0 75 52.5 127.5T540-160q75 0 127.5-52.5T720-340v-67q-35-12-57.5-43T640-520q0-50 35-85t85-35q50 0 85 35t35 85q0 39-22.5 70T800-407v67q0 108-76 184T540-80Zm220-400q17 0 28.5-11.5T800-520q0-17-11.5-28.5T760-560q-17 0-28.5 11.5T720-520q0 17 11.5 28.5T760-480Zm0-40Z" />
            </svg> Registrarse como Veterinario
        </button>

        <button class="btn-admin" onclick="window.location.href='login-admin.php'">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="#EFEFEF">
                <path d="M0 0h24v24H0V0z" fill="none" />
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z" />
            </svg> Acceso Administrativo
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