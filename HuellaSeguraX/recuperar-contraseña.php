<?php
include_once('config/conexion.php');
session_start();

$mensaje = "";
$tipo_mensaje = "";
$mostrar_codigo = false;
$email_verificado = "";

// Procesar envío de código
if (isset($_POST['enviar_codigo'])) {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        // Verificar si el email existe
        $consulta = "SELECT id_usuario, nombre_usuario FROM usuarios WHERE email_usuario = '$email' AND estado = 'activo'";
        $resultado = $conexion->query($consulta);
        
        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            
            // Generar código de 6 dígitos
            $codigo_verificacion = sprintf("%06d", mt_rand(100000, 999999));
            
            // Guardar código en sesión
            $_SESSION['codigo_recuperacion'] = $codigo_verificacion;
            $_SESSION['email_recuperacion'] = $email;
            $_SESSION['timestamp_codigo'] = time();
            
            // Enviar email
            require 'vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Cambiar según tu proveedor
                $mail->SMTPAuth = true;
                $mail->Username = 'tu_email@gmail.com'; // Tu email
                $mail->Password = 'tu_contraseña_app'; // Tu contraseña de aplicación
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';
                
                // Remitente y destinatario
                $mail->setFrom('tu_email@gmail.com', 'Huella Segura');
                $mail->addAddress($email, $usuario['nombre_usuario']);
                
                // Contenido del email
                $mail->isHTML(true);
                $mail->Subject = 'Código de Recuperación de Contraseña - Huella Segura';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;'>
                        <div style='background-color: #F4D03F; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                            <h1 style='color: #D35400; margin: 0;'>🐾 Huella Segura</h1>
                        </div>
                        <div style='background-color: white; padding: 30px; border-radius: 0 0 10px 10px;'>
                            <h2 style='color: #333;'>Hola {$usuario['nombre_usuario']},</h2>
                            <p style='color: #666; line-height: 1.6;'>
                                Recibimos una solicitud para restablecer la contraseña de tu cuenta en Huella Segura.
                            </p>
                            <div style='background-color: #FFF3CD; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;'>
                                <p style='color: #856404; margin: 0 0 10px 0; font-size: 14px;'>Tu código de verificación es:</p>
                                <h1 style='color: #D35400; margin: 0; font-size: 48px; letter-spacing: 8px;'>{$codigo_verificacion}</h1>
                            </div>
                            <p style='color: #666; line-height: 1.6;'>
                                Este código es válido por <strong>15 minutos</strong>.
                            </p>
                            <p style='color: #666; line-height: 1.6;'>
                                Si no solicitaste restablecer tu contraseña, puedes ignorar este correo de forma segura.
                            </p>
                            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                            <p style='color: #999; font-size: 12px; text-align: center;'>
                                Este es un correo automático, por favor no respondas a este mensaje.<br>
                                © 2025 Huella Segura - Tu compañero para el cuidado de mascotas
                            </p>
                        </div>
                    </div>
                ";
                
                $mail->send();
                
                $mostrar_codigo = true;
                $email_verificado = $email;
                $mensaje = "Se ha enviado un código de verificación a tu correo electrónico. Por favor revisa tu bandeja de entrada y spam.";
                $tipo_mensaje = "success";
                
            } catch (Exception $e) {
                $mensaje = "Error al enviar el correo: {$mail->ErrorInfo}";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "No existe una cuenta activa con ese correo electrónico";
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Por favor ingresa tu correo electrónico";
        $tipo_mensaje = "error";
    }
}

// Procesar reenvío de código
if (isset($_POST['reenviar_codigo'])) {
    // Usar el email guardado en sesión
    if (isset($_SESSION['email_recuperacion'])) {
        $email = $_SESSION['email_recuperacion'];
        
        // Verificar si el email existe
        $consulta = "SELECT id_usuario, nombre_usuario FROM usuarios WHERE email_usuario = '$email' AND estado = 'activo'";
        $resultado = $conexion->query($consulta);
        
        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            
            // Generar nuevo código
            $codigo_verificacion = sprintf("%06d", mt_rand(100000, 999999));
            
            // Actualizar sesión
            $_SESSION['codigo_recuperacion'] = $codigo_verificacion;
            $_SESSION['timestamp_codigo'] = time();
            
            // Enviar email
            require 'vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tu_email@gmail.com';
                $mail->Password = 'tu_contraseña_app';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';
                
                $mail->setFrom('tu_email@gmail.com', 'Huella Segura');
                $mail->addAddress($email, $usuario['nombre_usuario']);
                
                $mail->isHTML(true);
                $mail->Subject = 'Nuevo Código de Recuperación - Huella Segura';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;'>
                        <div style='background-color: #F4D03F; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                            <h1 style='color: #D35400; margin: 0;'>🐾 Huella Segura</h1>
                        </div>
                        <div style='background-color: white; padding: 30px; border-radius: 0 0 10px 10px;'>
                            <h2 style='color: #333;'>Hola {$usuario['nombre_usuario']},</h2>
                            <p style='color: #666; line-height: 1.6;'>
                                Has solicitado un nuevo código de verificación.
                            </p>
                            <div style='background-color: #FFF3CD; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;'>
                                <p style='color: #856404; margin: 0 0 10px 0; font-size: 14px;'>Tu nuevo código de verificación es:</p>
                                <h1 style='color: #D35400; margin: 0; font-size: 48px; letter-spacing: 8px;'>{$codigo_verificacion}</h1>
                            </div>
                            <p style='color: #666; line-height: 1.6;'>
                                Este código es válido por <strong>15 minutos</strong>.
                            </p>
                            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                            <p style='color: #999; font-size: 12px; text-align: center;'>
                                © 2025 Huella Segura - Tu compañero para el cuidado de mascotas
                            </p>
                        </div>
                    </div>
                ";
                
                $mail->send();
                
                $mostrar_codigo = true;
                $email_verificado = $email;
                $mensaje = "Se ha enviado un nuevo código a tu correo electrónico";
                $tipo_mensaje = "success";
                
            } catch (Exception $e) {
                $mensaje = "Error al reenviar el correo: {$mail->ErrorInfo}";
                $tipo_mensaje = "error";
                $mostrar_codigo = true;
                $email_verificado = $email;
            }
        }
    } else {
        $mensaje = "Sesión expirada. Por favor inicia el proceso nuevamente";
        $tipo_mensaje = "error";
    }
}

// Procesar verificación de código
if (isset($_POST['verificar_codigo'])) {
    $codigo_ingresado = trim($_POST['codigo']);
    $email = $_POST['email_hidden'];
    
    if (isset($_SESSION['codigo_recuperacion']) && isset($_SESSION['email_recuperacion'])) {
        // Verificar que el código no haya expirado (15 minutos)
        $tiempo_transcurrido = time() - $_SESSION['timestamp_codigo'];
        
        if ($tiempo_transcurrido > 900) { // 15 minutos
            $mensaje = "El código ha expirado. Por favor solicita uno nuevo";
            $tipo_mensaje = "error";
            $mostrar_codigo = true;
            $email_verificado = $email;
        } elseif ($codigo_ingresado == $_SESSION['codigo_recuperacion'] && $email == $_SESSION['email_recuperacion']) {
            // Código correcto, redirigir a cambiar contraseña
            $_SESSION['email_validado'] = $email;
            header("Location: cambiar-contraseña.php");
            exit();
        } else {
            $mensaje = "El código ingresado es incorrecto";
            $tipo_mensaje = "error";
            $mostrar_codigo = true;
            $email_verificado = $email;
        }
    } else {
        $mensaje = "Sesión expirada. Por favor inicia el proceso nuevamente";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Huella Segura</title>
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
        <p class="login-subtitle">Recuperación de contraseña 🔑</p>
    </div>

    <!-- Contenedor de recuperación -->
    <div class="login-container">
        <h2 class="login-title">¿Olvidaste tu contraseña?</h2>
        <p class="login-welcome">
            <?php if (!$mostrar_codigo): ?>
                Ingresa tu correo electrónico y te enviaremos un código de verificación
            <?php else: ?>
                Ingresa el código de 6 dígitos que enviamos a<br>
                <strong><?php echo htmlspecialchars($email_verificado); ?></strong>
            <?php endif; ?>
        </p>

        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo $tipo_mensaje === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php if (!$mostrar_codigo): ?>
            <!-- Formulario para solicitar código -->
            <form class="login-form" method="POST" action="">
                <div class="input-group">
                    <input type="email"
                           name="email"
                           class="login-input"
                           placeholder="Ingrese su correo electrónico"
                           required>
                </div>

                <button type="submit" name="enviar_codigo" class="btn-login">
                    Enviar Código de Verificación
                </button>
            </form>
        <?php else: ?>
            <!-- Formulario para verificar código -->
            <form class="login-form" method="POST" action="" id="formVerificar">
                <input type="hidden" name="email_hidden" value="<?php echo htmlspecialchars($email_verificado); ?>">
                
                <div class="input-group">
                    <input type="text"
                           name="codigo"
                           class="login-input codigo-input"
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           required>
                </div>

                <button type="submit" name="verificar_codigo" class="btn-login">
                    Verificar Código
                </button>
            </form>

            <!-- Formulario separado para reenviar código -->
            <form method="POST" action="" style="margin-top: 12px;">
                <button type="submit" name="reenviar_codigo" class="btn-reenviar">
                    ✉️ Reenviar Código
                </button>
            </form>

            <div class="timer-expiracion" id="timer">
                ⏱️ El código expira en: <span id="tiempo-restante">15:00</span>
            </div>
        <?php endif; ?>

        <div class="register-link">
            <a href="login.php">← Volver al inicio de sesión</a>
        </div>
    </div>

    <script src="js/scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codigoInput = document.querySelector('.codigo-input');
            
            if (codigoInput) {
                codigoInput.focus();
                
                // Solo permitir números
                codigoInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });

                // Timer de expiración
                <?php if ($mostrar_codigo && isset($_SESSION['timestamp_codigo'])): ?>
                const timestampInicio = <?php echo $_SESSION['timestamp_codigo']; ?>;
                const tiempoExpiracion = 900; // 15 minutos en segundos
                
                function actualizarTimer() {
                    const tiempoActual = Math.floor(Date.now() / 1000);
                    const tiempoTranscurrido = tiempoActual - timestampInicio;
                    const tiempoRestante = tiempoExpiracion - tiempoTranscurrido;
                    
                    if (tiempoRestante <= 0) {
                        document.getElementById('tiempo-restante').textContent = '00:00';
                        document.getElementById('timer').classList.add('urgente');
                        document.getElementById('timer').innerHTML = '⚠️ El código ha expirado. Por favor solicita uno nuevo';
                        return;
                    }
                    
                    const minutos = Math.floor(tiempoRestante / 60);
                    const segundos = tiempoRestante % 60;
                    document.getElementById('tiempo-restante').textContent = 
                        `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
                    
                    if (tiempoRestante <= 60) {
                        document.getElementById('timer').classList.add('urgente');
                    }
                    
                    setTimeout(actualizarTimer, 1000);
                }
                
                actualizarTimer();
                <?php endif; ?>
            }
        });
    </script>
</body>
</html>