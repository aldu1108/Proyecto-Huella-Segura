<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";
$mensaje_exito = "";

// Verificar si ya hay sesión activa
if (isset($_SESSION['usuario_id']) && $_SESSION['rol'] != 'demo') {
    header("Location: index.php");
    exit();
}

// Procesar formulario de registro de veterinario
if ($_POST) {
    $nombre = $_POST['nombre_usuario'];
    $apellido = $_POST['apellido_usuario'];
    $email = $_POST['email_usuario'];
    $telefono = $_POST['telefono_usuario'];
    $contraseña = $_POST['contraseña_usuario'];
    $confirmar_contraseña = $_POST['confirmar_contraseña'];

    // Datos específicos de veterinario
    $especialidad = $_POST['especialidad'];
    $clinica = $_POST['clinica'];
    $numero_colegiado = $_POST['numero_colegiado'];
    $horarios_atencion = $_POST['horarios_atencion'];

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($contraseña) || empty($especialidad) || empty($numero_colegiado)) {
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
            $consulta_usuario = "INSERT INTO usuarios (email_usuario, contraseña_usuario, telefono_usuario, nombre_usuario, apellido_usuario, foto_usuario, estado, rol) 
                     VALUES ('$email', '$contraseña', '$telefono', '$nombre', '$apellido', 'veterinario-default.jpg', 'activo', 'usuario')";
            if ($conexion->query($consulta_usuario)) {
                $usuario_id = $conexion->insert_id;

                // Insertar datos de veterinario (certificado = 0 hasta verificación)
                $consulta_veterinario = "INSERT INTO veterinario (certificado, especialidad, clinica, horarios_de_atencion, id_usuario) 
                                        VALUES (0, '$especialidad', '$clinica', '$horarios_atencion', $usuario_id)";

                if ($conexion->query($consulta_veterinario)) {
                    $mensaje_exito = "Registro exitoso. Tu cuenta está pendiente de verificación profesional.";
                } else {
                    $mensaje_error = "Error al registrar datos veterinarios.";
                }
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
    <title>Registro Veterinario - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/registro-veterinario.css">
</head>

<body class="login-body" style="background: url('imagenes/fondo-login.png') no-repeat center center fixed; background-size: cover;">

    <!-- Header centrado -->
    <div class="login-header" style="margin-bottom: 23px">
        <h1 class="login-logo">Huella Segura</h1>
        <p class="login-subtitle">Tu compañero para el cuidado de mascotas 🐾</p>
    </div>

    <!-- Contenedor de registro -->
    <div class="login-container" style="max-width: 500px; max-height: 78vh; overflow-y: auto;">
        <h2 class="login-title">Registro Veterinario</h2>
        <p class="login-welcome">Únete a nuestra red de profesionales</p>

        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="success-message">
                <?php echo $mensaje_exito; ?>
                <br><a href="login.php" style="color: #fff; text-decoration: underline;">Ir a Iniciar Sesión</a>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <!-- Información Personal -->
            <div class="form-section">
                <h3 style="color: #d17c41; margin-bottom: 1rem; font-size: 1.1rem;">Información Personal</h3>
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div class="input-group" style="flex: 1;">
                        <span class="input-icon">👤</span>
                        <input type="text" name="nombre_usuario" class="login-input" placeholder="Nombre" required
                            value="<?php echo isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : ''; ?>">
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <span class="input-icon">👤</span>
                        <input type="text" name="apellido_usuario" class="login-input" placeholder="Apellido" required
                            value="<?php echo isset($_POST['apellido_usuario']) ? $_POST['apellido_usuario'] : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <span class="input-icon">📧</span>
                    <input type="email" name="email_usuario" class="login-input" placeholder="Correo electrónico profesional" required
                        value="<?php echo isset($_POST['email_usuario']) ? $_POST['email_usuario'] : ''; ?>">
                </div>

                <div class="input-group">
                    <span class="input-icon">📱</span>
                    <input type="tel" name="telefono_usuario" class="login-input" placeholder="Teléfono de contacto"
                        value="<?php echo isset($_POST['telefono_usuario']) ? $_POST['telefono_usuario'] : ''; ?>">
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div class="input-group" style="flex: 1;">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="contraseña_usuario" class="login-input" placeholder="Contraseña" required>
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="confirmar_contraseña" class="login-input" placeholder="Confirmar contraseña" required>
                    </div>
                </div>
            </div>

            <!-- Información Profesional -->
            <div class="form-section">
                <h3 style="color: #d17c41; margin-bottom: 1rem; font-size: 1.1rem;">Información Profesional</h3>
                
                <div class="input-group">
                    <span class="input-icon">🩺</span>
                    <select name="especialidad" class="login-input" required>
                        <option value="">Seleccionar especialidad</option>
                        <option value="Medicina General">Medicina General</option>
                        <option value="Cirugía">Cirugía</option>
                        <option value="Cardiología">Cardiología</option>
                        <option value="Dermatología">Dermatología</option>
                        <option value="Neurología">Neurología</option>
                        <option value="Oncología">Oncología</option>
                    </select>
                </div>

                <div class="input-group">
                    <span class="input-icon">🏥</span>
                    <input type="text" name="clinica" class="login-input" placeholder="Nombre de la clínica donde trabajas" required>
                </div>

                <div class="input-group">
                    <span class="input-icon">🏅</span>
                    <input type="text" name="numero_colegiado" class="login-input" placeholder="Número de registro profesional" required>
                </div>

                <div class="input-group">
                    <span class="input-icon">🕐</span>
                    <textarea name="horarios_atencion" class="login-input" placeholder="Ej: Lunes a Viernes 9:00-18:00" rows="3"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-login">Registrarse como Veterinario</button>
        </form>

        <div class="divider">
            <span>o</span>
        </div>

        <div class="register-link">
            ¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a>
        </div>
    </div>
    <script src="js/scripts.js"></script>
</body>

</html>