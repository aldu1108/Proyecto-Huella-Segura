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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include_once("includes/logo.php"); ?>
</head>

<body class="login-body" style="background: url('imagenes/fondo-login.png') no-repeat center center fixed; background-size: cover;">

    <!-- Header centrado -->
    <div class="login-header" style="margin-bottom: 23px">
        <h1 class="login-logo">Huella Segura</h1>
        <p class="login-subtitle">Tu compañero para el cuidado de mascotas</p>
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
                        <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg></span>
                        <input type="text" name="nombre_usuario" class="login-input" placeholder="Nombre" required
                            value="<?php echo isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : ''; ?>">
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg></span>
                        <input type="text" name="apellido_usuario" class="login-input" placeholder="Apellido" required
                            value="<?php echo isset($_POST['apellido_usuario']) ? $_POST['apellido_usuario'] : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M168-192q-29 0-50.5-21.5T96-264v-432q0-29 21.5-50.5T168-768h624q30 0 51 21.5t21 50.5v432q0 29-21 50.5T792-192H168Zm312-240 312-179v-85L480-517 168-696v85l312 179Z"/></svg></span>
                    <input type="email" name="email_usuario" class="login-input" placeholder="Correo electrónico profesional" required
                        value="<?php echo isset($_POST['email_usuario']) ? $_POST['email_usuario'] : ''; ?>">
                </div>

                <div class="input-group">
                    <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M288-48q-29.7 0-50.85-21.15Q216-90.3 216-120v-720q0-33 19.5-52.5T288-912h384q29.7 0 50.85 21.15Q744-869.7 744-840v144q20 0 34 14t14 34v96q0 20-14 34t-34 14v384q0 29.7-21.15 50.85Q701.7-48 672-48H288Zm191.79-672q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg></span>
                    <input type="tel" name="telefono_usuario" class="login-input" placeholder="Teléfono de contacto"
                        value="<?php echo isset($_POST['telefono_usuario']) ? $_POST['telefono_usuario'] : ''; ?>">
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div class="input-group" style="flex: 1;">
                        <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg></span>
                        <input type="password" name="contraseña_usuario" class="login-input" placeholder="Contraseña" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg></span>
                        <input type="password" name="confirmar_contraseña" class="login-input" placeholder="Confirmar contraseña" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
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
                    <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg></span>
                    <input type="text" name="clinica" class="login-input" placeholder="Nombre de la clínica donde trabajas" required>
                </div>

                <div class="input-group">
                    <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M287-144q-79.37 0-135.18-55.82Q96-255.63 96-335q0-81 60-135.5T298-525L144-864h264l72 158 72-158h264L662-527q83 0 142.5 55T864-336q0 80.29-55.85 136.15Q752.29-144 672-144q-11 0-21-1.5t-20-3.5q43-34 66-82.76 23-48.76 23-104.24 0-100-70-170t-170-70q-100 0-170 70t-70 170q0 55 23 104t66 83q-11 2-21 3.5t-21 1.5Zm193-24q-70 0-119-49t-49-119q0-70 49-119t119-49q70 0 119 49t49 119q0 70-49 119t-119 49Zm-74-60 74-56 74 56-28-91 74-53h-91l-29-96-29 96h-91l74 53-28 91Z"/></svg></span>
                    <input type="text" name="numero_colegiado" class="login-input" placeholder="Número de registro profesional" required>
                </div>

                <div class="input-group">
                    <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg></span>
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