<?php
include_once('config/conexion.php');
session_start();

$mensaje = "";
$tipo_mensaje = "";

// Verificar que el usuario llegó desde el proceso de recuperación
if (!isset($_SESSION['email_validado'])) {
    header("Location: recuperar-contraseña.php");
    exit();
}

$email_validado = $_SESSION['email_validado'];

// Procesar cambio de contraseña
if (isset($_POST['cambiar_contrasena'])) {
    $nueva_contrasena = trim($_POST['nueva_contrasena']);
    $confirmar_contrasena = trim($_POST['confirmar_contrasena']);
    
    if (empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        $mensaje = "Por favor completa todos los campos";
        $tipo_mensaje = "error";
    } elseif (strlen($nueva_contrasena) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres";
        $tipo_mensaje = "error";
    } elseif ($nueva_contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden";
        $tipo_mensaje = "error";
    } else {
        // Actualizar contraseña (en producción deberías usar password_hash)
        $consulta = "UPDATE usuarios SET contraseña_usuario = '$nueva_contrasena' WHERE email_usuario = '$email_validado'";
        
        if ($conexion->query($consulta)) {
            // Limpiar sesión de recuperación
            unset($_SESSION['codigo_recuperacion']);
            unset($_SESSION['email_recuperacion']);
            unset($_SESSION['timestamp_codigo']);
            unset($_SESSION['email_validado']);
            
            $mensaje = "Contraseña actualizada exitosamente. Redirigiendo al inicio de sesión...";
            $tipo_mensaje = "success";
            
            // Redirigir después de 2 segundos
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            </script>";
        } else {
            $mensaje = "Error al actualizar la contraseña. Intenta nuevamente";
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Huella Segura</title>
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
        <p class="login-subtitle">Crea tu nueva contraseña 🔐</p>
    </div>

    <!-- Contenedor de cambio de contraseña -->
    <div class="login-container">
        <h2 class="login-title">Nueva Contraseña</h2>
        <p class="login-welcome">
            Ingresa tu nueva contraseña para la cuenta:<br>
            <strong><?php echo htmlspecialchars($email_validado); ?></strong>
        </p>

        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo $tipo_mensaje === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <input type="password"
                       name="nueva_contrasena"
                       id="nueva_contrasena"
                       class="login-input"
                       placeholder="Nueva contraseña (mínimo 6 caracteres)"
                       minlength="6"
                       required>
                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="input-group">
                <input type="password"
                       name="confirmar_contrasena"
                       id="confirmar_contrasena"
                       class="login-input"
                       placeholder="Confirmar nueva contraseña"
                       minlength="6"
                       required>
                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="requisitos-contrasena">
                <p>La contraseña debe tener:</p>
                <ul>
                    <li id="req-length">✗ Mínimo 6 caracteres</li>
                </ul>
            </div>

            <button type="submit" name="cambiar_contrasena" class="btn-login">
                Cambiar Contraseña
            </button>
        </form>

        <div class="register-link">
            <a href="login.php">← Volver al inicio de sesión</a>
        </div>
    </div>

    <script src="js/scripts.js"></script>
    <script>
        // Validación en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const nuevaContrasena = document.getElementById('nueva_contrasena');
            const confirmarContrasena = document.getElementById('confirmar_contrasena');
            const reqLength = document.getElementById('req-length');
            
            nuevaContrasena.addEventListener('input', function() {
                // Validar longitud
                if (this.value.length >= 6) {
                    reqLength.style.color = '#27AE60';
                    reqLength.textContent = '✓ Mínimo 6 caracteres';
                } else {
                    reqLength.style.color = '#E74C3C';
                    reqLength.textContent = '✗ Mínimo 6 caracteres';
                }
            });
            
            // Validar que las contraseñas coincidan
            confirmarContrasena.addEventListener('input', function() {
                if (this.value === nuevaContrasena.value && this.value.length > 0) {
                    this.style.borderColor = '#27AE60';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#E74C3C';
                } else {
                    this.style.borderColor = '#e8e8e8';
                }
            });
        });
    </script>
</body>
</html>