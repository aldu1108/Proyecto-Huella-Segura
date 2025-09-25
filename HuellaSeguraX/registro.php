<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";
$mensaje_exito = "";

// Verificar si ya hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Procesar formulario de registro
if ($_POST) {
    $nombre = $_POST['nombre_usuario'];
    $apellido = $_POST['apellido_usuario'];
    $email = $_POST['email_usuario'];
    $telefono = $_POST['telefono_usuario'];
    $contraseña = $_POST['contraseña_usuario'];
    $confirmar_contraseña = $_POST['confirmar_contraseña'];
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($contraseña)) {
        $mensaje_error = "Por favor complete todos los campos obligatorios";
    } elseif ($contraseña != $confirmar_contraseña) {
        $mensaje_error = "Las contraseñas no coinciden";
    } elseif (strlen($contraseña) < 6) {
        $mensaje_error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar si el email ya existe
        $consulta_email = "SELECT id_usuario FROM usuarios WHERE email_usuario = '$email'";
        $resultado_email = $conexion->query($consulta_email);
        
        if ($resultado_email && $resultado_email->num_rows > 0) {
            $mensaje_error = "Ya existe una cuenta con este correo electrónico";
        } else {
            // Insertar nuevo usuario
            $consulta = "INSERT INTO usuarios (email_usuario, contraseña_usuario, telefono_usuario, nombre_usuario, apellido_usuario, foto_usuario, estado) 
                         VALUES ('$email', '$contraseña', '$telefono', '$nombre', '$apellido', 'usuario-default.jpg', 'activo')";
            
            if ($conexion->query($consulta)) {
                $mensaje_exito = "Cuenta creada exitosamente. Ya puedes iniciar sesión.";
            } else {
                $mensaje_error = "Error al crear la cuenta. Intenta de nuevo.";
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
    <title>Registrarse - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/registro.css">
</head>
<body class="login-body" style="background: url('imagenes/fondo-login.png') no-repeat center center fixed; background-size: cover;">

    <!-- Header centrado -->
    <div class="login-header">
        <h1 class="login-logo">Huella Segura</h1>
        <p class="login-subtitle">Tu compañero para el cuidado de mascotas 🐕</p>
    </div>

    <!-- Contenedor de registro -->
    <div class="login-container">
        <h2 class="login-title">Crear Cuenta</h2>
        <p class="login-welcome">¡Únete a la comunidad de Huella Segura!</p>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div class="error-message" style="background: #E8F5E8; color: #2E7D32;">
                <?php echo $mensaje_exito; ?>
                <br><a href="login.php" style="color: #27AE60; font-weight: 600;">Ir a Iniciar Sesión</a>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <input type="text" name="nombre_usuario" class="login-input" placeholder="Ingrese su nombre" required 
                       value="<?php echo isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : ''; ?>">
            </div>
            
            <div class="input-group">
                <input type="text" name="apellido_usuario" class="login-input" placeholder="Ingrese su apellido" required 
                       value="<?php echo isset($_POST['apellido_usuario']) ? $_POST['apellido_usuario'] : ''; ?>">
            </div>
            
            <div class="input-group">
                <input type="email" name="email_usuario" class="login-input" placeholder="Ingrese su correo electrónico" required 
                       value="<?php echo isset($_POST['email_usuario']) ? $_POST['email_usuario'] : ''; ?>">
            </div>
            
            <div class="input-group">
                <input type="tel" name="telefono_usuario" class="login-input" placeholder="Teléfono (opcional)" 
                       value="<?php echo isset($_POST['telefono_usuario']) ? $_POST['telefono_usuario'] : ''; ?>">
            </div>
            
            <div class="input-group">
                <input type="password" name="contraseña_usuario" class="login-input" placeholder="Contraseña (mín. 6 caracteres)" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="confirmar_contraseña" class="login-input" placeholder="Confirmar contraseña" required>
            </div>
            
            <div class="terms-checkbox">
                <label>
                    <input type="checkbox" id="terminos" required>
                    <span class="checkmark"></span>
                    Acepto los <a href="terminos.php">términos y condiciones</a>
                </label>
            </div>
            
            <button type="submit" class="btn-login">Crear Cuenta</button>
        </form>
        
        <div class="divider">
            <span>o</span>
        </div>
        
        <button class="btn-demo" onclick="window.location.href='login.php'">
            ⬅️ Volver al Iniciar Sesión
        </button>
        
        <div class="register-link">
            ¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a>
        </div>
    </div>
    <script src="js/scripts.js"></script>
</body>
</html>