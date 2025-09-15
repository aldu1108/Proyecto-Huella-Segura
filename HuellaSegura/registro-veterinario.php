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
            $consulta_usuario = "INSERT INTO usuarios (email_usuario, contraseña_usuario, telefono_usuario, nombre_usuario, apellido_usuario, foto_usuario, estado) 
                                 VALUES ('$email', '$contraseña', '$telefono', '$nombre', '$apellido', 'veterinario-default.jpg', 'activo')";

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
</head>

<body class="veterinario-body">
    <div class="cabecera-principal">
        <div class="logo-contenedor">
            <h1 class="logo-texto">PetCare 🐾</h1>
            <p class="logo-subtitulo">Registro Profesional Veterinario</p>
        </div>
    </div>

    <div class="contenedor-registro-veterinario">
        <div class="encabezado-veterinario-registro">
            <h2 class="titulo-registro">Registro Veterinario</h2>
            <p class="subtitulo-registro">Únete a nuestra red de profesionales certificados</p>
        </div>

        <?php if (!empty($mensaje_error)): ?>
            <div class="mensaje-error">
                ⚠️ <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="mensaje-exito">
                ✅ <?php echo $mensaje_exito; ?>
                <br><a href="login-veterinario.php">Ir a Iniciar Sesión</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="form-veterinario">
            <div class="seccion-formulario">
                <h3>Información Personal</h3>

                <div class="fila-inputs">
                    <div class="grupo-input">
                        <label>Nombre *</label>
                        <input type="text" name="nombre_usuario" required
                            value="<?php echo isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : ''; ?>">
                    </div>

                    <div class="grupo-input">
                        <label>Apellido *</label>
                        <input type="text" name="apellido_usuario" required
                            value="<?php echo isset($_POST['apellido_usuario']) ? $_POST['apellido_usuario'] : ''; ?>">
                    </div>
                </div>

                <div class="grupo-input">
                    <label>Correo Electrónico Profesional *</label>
                    <input type="email" name="email_usuario" required
                        value="<?php echo isset($_POST['email_usuario']) ? $_POST['email_usuario'] : ''; ?>">
                </div>

                <div class="grupo-input">
                    <label>Teléfono de Contacto</label>
                    <input type="tel" name="telefono_usuario"
                        value="<?php echo isset($_POST['telefono_usuario']) ? $_POST['telefono_usuario'] : ''; ?>">
                </div>

                <div class="fila-inputs">
                    <div class="grupo-input">
                        <label>Contraseña *</label>
                        <input type="password" name="contraseña_usuario" required>
                    </div>

                    <div class="grupo-input">
                        <label>Confirmar Contraseña *</label>
                        <input type="password" name="confirmar_contraseña" required>
                    </div>
                </div>
            </div>

            <div class="seccion-formulario">
                <h3>Información Profesional</h3>

                <div class="grupo-input">
                    <label>Especialidad *</label>
                    <select name="especialidad" required>
                        <option value="">Seleccionar especialidad</option>
                        <option value="Medicina General">Medicina General</option>
                        <option value="Cirugía">Cirugía</option>
                        <option value="Cardiología">Cardiología</option>
                        <option value="Dermatología">Dermatología</option>
                        <option value="Neurología">Neurología</option>
                        <option value="Oncología">Oncología</option>
                    </select>
                </div>

                <div class="grupo-input">
                    <label>Clínica/Hospital *</label>
                    <input type="text" name="clinica" placeholder="Nombre de la clínica donde trabajas" required>
                </div>

                <div class="grupo-input">
                    <label>Número de Colegiado *</label>
                    <input type="text" name="numero_colegiado" placeholder="Número de registro profesional" required>
                </div>

                <div class="grupo-input">
                    <label>Horarios de Atención</label>
                    <textarea name="horarios_atencion" rows="3" placeholder="Ej: Lunes a Viernes 9:00-18:00"></textarea>
                </div>
            </div>

            <button type="submit" class="boton-registro-veterinario">Registrarse como Veterinario</button>
        </form>

        <div class="enlaces-adicionales">
            <a href="login-veterinario.php">← Ya tengo cuenta veterinaria</a>
            <a href="login.php">Registro normal (no veterinario)</a>
        </div>
    </div>

    <style>
        .veterinario-body {
            background: linear-gradient(135deg, #8d6e63 0%, #5d4e75 100%);
            min-height: 100vh;
        }

        .contenedor-registro-veterinario {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .encabezado-veterinario-registro {
            background: linear-gradient(135deg, #6a4c93, #8d6e63);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .form-veterinario {
            padding: 2rem;
        }

        .seccion-formulario {
            margin-bottom: 2rem;
        }

        .fila-inputs {
            display: flex;
            gap: 1rem;
        }

        .grupo-input {
            margin-bottom: 1rem;
        }

        .grupo-input label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .grupo-input input,
        .grupo-input select,
        .grupo-input textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
        }

        .boton-registro-veterinario {
            width: 100%;
            background: linear-gradient(135deg, #8d6e63, #6a4c93);
            color: white;
            border: none;
            padding: 1.2rem;
            font-size: 1.2rem;
            border-radius: 10px;
            cursor: pointer;
        }

        .mensaje-error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            margin: 1rem 2rem;
            border-radius: 8px;
            text-align: center;
        }

        .mensaje-exito {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 1rem;
            margin: 1rem 2rem;
            border-radius: 8px;
            text-align: center;
        }

        .enlaces-adicionales {
            padding: 2rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .fila-inputs {
                flex-direction: column;
            }
        }
    </style>
</body>

</html>