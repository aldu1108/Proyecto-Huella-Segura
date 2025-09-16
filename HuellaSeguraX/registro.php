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
</head>
<body>
    <div class="cabecera-principal">
        <div class="logo-contenedor">
            <h1 class="logo-texto">Huella Segura 🐾</h1>
            <p class="logo-subtitulo">Tu compañero para el cuidado de mascotas</p>
        </div>
    </div>

    <div class="contenedor-login">
        <h2 class="titulo-login">Crear Cuenta</h2>
        <p class="subtitulo-login">Únete a la comunidad de Huella Segura</p>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="mensaje-error" style="color: red; text-align: center; margin-bottom: 1rem; padding: 0.8rem; background-color: #ffebee; border-radius: 5px;">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div class="mensaje-exito" style="color: green; text-align: center; margin-bottom: 1rem; padding: 0.8rem; background-color: #e8f5e8; border-radius: 5px;">
                <?php echo $mensaje_exito; ?>
                <br><a href="login.php" style="color: #27ae60;">Ir a Iniciar Sesión</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="fila-inputs">
                <div class="grupo-input">
                    <input type="text" name="nombre_usuario" class="input-login" placeholder="Nombre" required value="<?php echo isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : ''; ?>">
                </div>
                
                <div class="grupo-input">
                    <input type="text" name="apellido_usuario" class="input-login" placeholder="Apellido" required value="<?php echo isset($_POST['apellido_usuario']) ? $_POST['apellido_usuario'] : ''; ?>">
                </div>
            </div>
            
            <div class="grupo-input">
                <input type="email" name="email_usuario" class="input-login" placeholder="Correo electrónico" required value="<?php echo isset($_POST['email_usuario']) ? $_POST['email_usuario'] : ''; ?>">
            </div>
            
            <div class="grupo-input">
                <input type="tel" name="telefono_usuario" class="input-login" placeholder="Teléfono (opcional)" value="<?php echo isset($_POST['telefono_usuario']) ? $_POST['telefono_usuario'] : ''; ?>">
            </div>
            
            <div class="grupo-input">
                <input type="password" name="contraseña_usuario" class="input-login" placeholder="Contraseña (mín. 6 caracteres)" required>
            </div>
            
            <div class="grupo-input">
                <input type="password" name="confirmar_contraseña" class="input-login" placeholder="Confirmar contraseña" required>
            </div>
            
            <div class="checkbox-grupo">
                <input type="checkbox" id="terminos" required>
                <label for="terminos">Acepto los <a href="terminos.php" style="color: #d35400;">términos y condiciones</a></label>
            </div>
            
            <button type="submit" class="boton-login">Crear Cuenta</button>
        </form>
        
        <div style="text-align: center; margin: 1rem 0; color: #666;">o</div>
        
        <button class="boton-demo" onclick="window.location.href='login.php'">
            ⬅️ Volver al Iniciar Sesión
        </button>
        
        <div class="enlace-registro">
            ¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <h4 style="color: #d35400; margin-bottom: 1rem;">¿Por qué registrarte?</h4>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                🐾 <span>Gestiona múltiples mascotas</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                📅 <span>Calendario de citas y vacunas</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                👥 <span>Conecta con otros dueños</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                🏥 <span>Historial médico completo</span>
            </div>
        </div>
    </div>

    <style>
        /* Estilos adicionales para el registro */
        .fila-inputs {
            display: flex;
            gap: 1rem;
        }
        
        .fila-inputs .grupo-input {
            flex: 1;
        }
        
        .checkbox-grupo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .checkbox-grupo input[type="checkbox"] {
            width: auto;
        }
        
        @media (max-width: 768px) {
            .fila-inputs {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</body>
</html>