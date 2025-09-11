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
        
        <form method="POST" action="" enctype="multipart/form-data" class="form-veterinario">
            <div class="seccion-formulario">
                <h3>Información Personal</h3>
                
                <div class="fila-inputs">
                    <div class="grupo-input">
                        <label>Nombre *</label>
                        <input type="text" name="nombre_usuario" required value="<?php echo isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : ''; ?>">
                    </div>
                    
                    <div class="grupo-input">
                        <label>Apellido *</label>
                        <input type="text" name="apellido_usuario" required value="<?php echo isset($_POST['apellido_usuario']) ? $_POST['apellido_usuario'] : ''; ?>">
                    </div>
                </div>
                
                <div class="grupo-input">
                    <label>Correo Electrónico Profesional *</label>
                    <input type="email" name="email_usuario" required value="<?php echo isset($_POST['email_usuario']) ? $_POST['email_usuario'] : ''; ?>">
                </div>
                
                <div class="grupo-input">
                    <label>Teléfono de Contacto</label>
                    <input type="tel" name="telefono_usuario" value="<?php echo isset($_POST['telefono_usuario']) ? $_POST['telefono_usuario'] : ''; ?>">
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
                        <option value="Oftalmología">Oftalmología</option>
                        <option value="Traumatología">Traumatología</option>
                        <option value="Medicina Exótica">Medicina Exótica</option>
                        <option value="Urgencias">Urgencias</option>
                    </select>
                </div>
                
                <div class="grupo-input">
                    <label>Clínica/Hospital *</label>
                    <input type="text" name="clinica" placeholder="Nombre de la clínica donde trabajas" required value="<?php echo isset($_POST['clinica']) ? $_POST['clinica'] : ''; ?>">
                </div>
                
                <div class="grupo-input">
                    <label>Número de Colegiado *</label>
                    <input type="text" name="numero_colegiado" placeholder="Número de registro profesional" required value="<?php echo isset($_POST['numero_colegiado']) ? $_POST['numero_colegiado'] : ''; ?>">
                </div>
                
                <div class="grupo-input">
                    <label>Horarios de Atención</label>
                    <textarea name="horarios_atencion" rows="3" placeholder="Ej: Lunes a Viernes 9:00-18:00, Sábados 9:00-14:00"><?php echo isset($_POST['horarios_atencion']) ? $_POST['horarios_atencion'] : ''; ?></textarea>
                </div>
            </div>

            <div class="seccion-formulario">
                <h3>Documentación (Próximamente)</h3>
                <div class="documentos-pendientes">
                    <p>📋 Título universitario (PDF)</p>
                    <p>📋 Certificado de colegiado (PDF)</p>
                    <p>📋 Foto de perfil profesional</p>
                    <small>Podrás subir estos documentos después del registro para completar la verificación</small>
                </div>
            </div>

            <div class="verificaciones-veterinario">
                <div class="verificacion-item">
                    <input type="checkbox" id="terminos-vet" required>
                    <label for="terminos-vet">Acepto los <a href="terminos.php">términos y condiciones</a> y la <a href="privacidad.php">política de privacidad</a></label>
                </div>
                
                <div class="verificacion-item">
                    <input type="checkbox" id="codigo-etica" required>
                    <label for="codigo-etica">Me comprometo a seguir el código de ética veterinario</label>
                </div>
                
                <div class="verificacion-item">
                    <input type="checkbox" id="informacion-veraz" required>
                    <label for="informacion-veraz">Confirmo que toda la información proporcionada es veraz</label>
                </div>
            </div>
            
            <button type="submit" class="boton-registro-veterinario">Registrarse como Veterinario</button>
        </form>
        
        <div class="proceso-verificacion">
            <h4>Proceso de Verificación</h4>
            <div class="pasos-verificacion">
                <div class="paso-verificacion">
                    <span class="numero-paso">1</span>
                    <div>
                        <strong>Registro</strong>
                        <p>Completa el formulario con tu información</p>
                    </div>
                </div>
                
                <div class="paso-verificacion">
                    <span class="numero-paso">2</span>
                    <div>
                        <strong>Documentación</strong>
                        <p>Sube tu título y certificados</p>
                    </div>
                </div>
                
                <div class="paso-verificacion">
                    <span class="numero-paso">3</span>
                    <div>
                        <strong>Verificación</strong>
                        <p>Nuestro equipo revisa tu información (24-48h)</p>
                    </div>
                </div>
                
                <div class="paso-verificacion">
                    <span class="numero-paso">4</span>
                    <div>
                        <strong>Activación</strong>
                        <p>Accede a tu panel profesional</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="beneficios-veterinario">
            <h4>Beneficios de Unirte a PetCare</h4>
            <div class="lista-beneficios">
                <div class="beneficio-item">
                    <span class="icono-beneficio">📊</span>
                    <div>
                        <strong>Panel de Control Profesional</strong>
                        <p>Gestiona citas, historiales y pacientes desde una plataforma integral</p>
                    </div>
                </div>
                
                <div class="beneficio-item">
                    <span class="icono-beneficio">👥</span>
                    <div>
                        <strong>Red de Clientes</strong>
                        <p>Conecta con dueños de mascotas en tu área</p>
                    </div>
                </div>
                
                <div class="beneficio-item">
                    <span class="icono-beneficio">📱</span>
                    <div>
                        <strong>Herramientas Digitales</strong>
                        <p>Recordatorios automáticos, historial digital y comunicación directa</p>
                    </div>
                </div>
                
                <div class="beneficio-item">
                    <span class="icono-beneficio">🏆</span>
                    <div>
                        <strong>Perfil Profesional</strong>
                        <p>Construye tu reputación con reseñas y certificaciones verificadas</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="enlaces-adicionales">
            <a href="login-veterinario.php" class="enlace-volver">← Ya tengo cuenta veterinaria</a>
            <a href="login.php" class="enlace-normal">Registro normal (no veterinario)</a>
        </div>
    </div>

    <style>
        .veterinario-body {
            background: linear-gradient(135deg, #8d6e63 0%, #5d4e75 100%);
            min-height: 100vh;
        }

        .contenedor-registro-veterinario {
            max-width: 800px;
            margin: 2rem auto;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .encabezado-veterinario-registro {
            background: linear-gradient(135deg, #6a4c93, #8d6e63);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .titulo-registro {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-veterinario {
            padding: 2rem;
        }

        .seccion-formulario {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .seccion-formulario:last-child {
            border-bottom: none;
        }

        .seccion-formulario h3 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #3498db;
        }

        .fila-inputs {
            display: flex;
            gap: 1rem;
        }

        .fila-inputs .grupo-input {
            flex: 1;
        }

        .grupo-input {
            margin-bottom: 1.5rem;
        }

        .grupo-input label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: bold;
        }

        .grupo-input input, .grupo-input select, .grupo-input textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .grupo-input input:focus, .grupo-input select:focus, .grupo-input textarea:focus {
            outline: none;
            border-color: #8d6e63;
        }

        .documentos-pendientes {
            background-color: #fff3cd;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }

        .documentos-pendientes p {
            margin-bottom: 0.5rem;
            color: #856404;
        }

        .verificaciones-veterinario {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .verificacion-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .verificacion-item:last-child {
            margin-bottom: 0;
        }

        .verificacion-item input[type="checkbox"] {
            width: auto;
            margin-top: 0.2rem;
        }

        .verificacion-item label {
            color: #2c3e50;
            line-height: 1.4;
        }

        .verificacion-item a {
            color: #8d6e63;
            text-decoration: none;
        }

        .boton-registro-veterinario {
            width: 100%;
            background: linear-gradient(135deg, #8d6e63, #6a4c93);
            color: white;
            border: none;
            padding: 1.2rem;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 2rem;
            transition: transform 0.3s;
        }

        .boton-registro-veterinario:hover {
            transform: translateY(-2px);
        }

        .proceso-verificacion {
            background-color: #f8f9fa;
            padding: 2rem;
            margin: 2rem 0;
        }

        .proceso-verificacion h4 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .pasos-verificacion {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .paso-verificacion {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-align: center;
        }

        .numero-paso {
            background-color: #3498db;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .beneficios-veterinario {
            background-color: #e8f5e8;
            padding: 2rem;
        }

        .beneficios-veterinario h4 {
            color: #27ae60;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .lista-beneficios {
            display: grid;
            gap: 1rem;
        }

        .beneficio-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background-color: white;
            border-radius: 8px;
        }

        .icono-beneficio {
            font-size: 2rem;
            width: 50px;
            text-align: center;
            flex-shrink: 0;
        }

        .enlaces-adicionales {
            padding: 2rem;
            text-align: center;
            border-top: 2px solid #f0f0f0;
        }

        .enlace-volver, .enlace-normal {
            color: #8d6e63;
            text-decoration: none;
            margin: 0 1rem;
            font-size: 1rem;
        }

        .enlace-volver:hover, .enlace-normal:hover {
            text-decoration: underline;
        }

        .mensaje-error, .mensaje-exito {
            padding: 1rem;
            margin: 1rem 2rem;
            border-radius: 8px;
            text-align: center;
        }

        .mensaje-error {
            background-color: #ffebee;
            color: #c62828;
            border: 2px solid #e74c3c;
        }

        .mensaje-exito {
            background-color: #e8f5e8;
            color: #2e7d32;
            border: 2px solid #27ae60;
        }

        .mensaje-exito a {
            color: #27ae60;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .contenedor-registro-veterinario {
                margin: 1rem;
                border-radius: 0;
            }
            
            .form-veterinario {
                padding: 1rem;
            }
            
            .fila-inputs {
                flex-direction: column;
            }
            
            .pasos-verificacion {
                grid-template-columns: 1fr;
            }
            
            .paso-verificacion {
                flex-direction: column;
                text-align: center;
            }
            
            .enlaces-adicionales {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</body>
</html>