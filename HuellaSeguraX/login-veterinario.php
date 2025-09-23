<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";

// Verificar si ya hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Procesar formulario de login de veterinario
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    
    if (!empty($email) && !empty($contraseña)) {
        // Consulta para verificar veterinario CERTIFICADO
        $consulta = "SELECT u.id_usuario, u.nombre_usuario, u.apellido_usuario, u.contraseña_usuario, 
                            v.id_veterinario, v.especialidad, v.clinica, v.certificado
                     FROM usuarios u 
                     JOIN veterinario v ON u.id_usuario = v.id_usuario 
                     WHERE u.email_usuario = '$email' AND u.estado = 'activo'";
        $resultado = $conexion->query($consulta);
        
        if ($resultado && $resultado->num_rows > 0) {
            $veterinario = $resultado->fetch_assoc();
            
            // Verificar contraseña
            if ($contraseña == $veterinario['contraseña_usuario']) {
                // Verificar si está certificado
                if ($veterinario['certificado'] == 1) {
                    // Veterinario APROBADO - Crear sesión
                    $_SESSION['usuario_id'] = $veterinario['id_usuario'];
                    $_SESSION['usuario_nombre'] = $veterinario['nombre_usuario'];
                    $_SESSION['usuario_apellido'] = $veterinario['apellido_usuario'];
                    $_SESSION['veterinario_id'] = $veterinario['id_veterinario'];
                    $_SESSION['es_veterinario'] = true;
                    $_SESSION['especialidad'] = $veterinario['especialidad'];
                    $_SESSION['clinica'] = $veterinario['clinica'];
                    
                    header("Location: panel-veterinario.php");
                    exit();
                } else {
                    // Veterinario NO CERTIFICADO
                    $mensaje_error = "⏳ Tu cuenta está pendiente de verificación por el administrador. Te contactaremos pronto.";
                }
            } else {
                $mensaje_error = "❌ Contraseña incorrecta";
            }
        } else {
            $mensaje_error = "❌ Veterinario no encontrado o cuenta inactiva";
        }
    } else {
        $mensaje_error = "⚠️ Por favor complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Veterinario - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="veterinario-body">
    <div class="cabecera-principal">
        <div class="logo-contenedor">
            <h1 class="logo-texto">Huella Segura 🐾</h1>
            <p class="logo-subtitulo">Portal Veterinario Profesional</p>
        </div>
    </div>

    <div class="contenedor-login veterinario">
        <div class="encabezado-veterinario">
            <h2 class="titulo-login">🩺 Acceso Veterinario</h2>
            <p class="subtitulo-login">Panel profesional para veterinarios certificados</p>
        </div>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="mensaje-error">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="grupo-input">
                <div class="icono-input">👨‍⚕️</div>
                <input type="email" name="email" class="input-login veterinario-input" placeholder="Correo electrónico profesional" required>
            </div>
            
            <div class="grupo-input">
                <div class="icono-input">🔒</div>
                <input type="password" name="contraseña" class="input-login veterinario-input" placeholder="Contraseña" required>
            </div>
            
            <button type="submit" class="boton-login veterinario">🩺 Iniciar Sesión Profesional</button>
        </form>
        
        <div class="separador">
            <span>o</span>
        </div>
        
        <div class="opciones-veterinario">
            <button class="boton-registro-veterinario" onclick="window.location.href='registro-veterinario.php'">
                📋 Registrarse como Veterinario
            </button>
            
            <div class="info-estados">
                <div class="estado-item">
                    <span class="icono-estado">✅</span>
                    <div>
                        <strong>Cuenta Aprobada</strong>
                        <p>Acceso completo al panel veterinario</p>
                    </div>
                </div>
                <div class="estado-item">
                    <span class="icono-estado">⏳</span>
                    <div>
                        <strong>Pendiente de Verificación</strong>
                        <p>Tu solicitud está siendo revisada</p>
                    </div>
                </div>
                <div class="estado-item">
                    <span class="icono-estado">❌</span>
                    <div>
                        <strong>Rechazada</strong>
                        <p>Debes registrarte nuevamente</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="enlaces-adicionales">
            <a href="login.php" class="enlace-volver">← Volver al login normal</a>
        </div>
    </div>

    <style>
        .veterinario-body {
            background: linear-gradient(135deg, #8d6e63 0%, #5d4e75 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .contenedor-login.veterinario {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }

        .encabezado-veterinario {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: linear-gradient(135deg, #8d6e63, #5d4e75);
            color: white;
            border-radius: 10px;
            margin: -1rem -1rem 2rem -1rem;
        }

        .mensaje-error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
        }

        .grupo-input {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .icono-input {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #666;
            z-index: 1;
        }

        .veterinario-input {
            padding-left: 3.5rem;
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 1rem;
        }

        .veterinario-input:focus {
            outline: none;
            border-color: #8d6e63;
        }

        .boton-login.veterinario {
            width: 100%;
            background: linear-gradient(135deg, #8d6e63, #5d4e75);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 1.5rem;
        }

        .separador {
            text-align: center;
            margin: 1.5rem 0;
            color: #666;
        }

        .separador span {
            background: white;
            padding: 0 1rem;
        }

        .boton-registro-veterinario {
            width: 100%;
            background: rgba(141, 110, 99, 0.1);
            border: 2px solid #8d6e63;
            color: #8d6e63;
            padding: 1rem;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 2rem;
        }

        .info-estados {
            margin: 2rem 0;
        }

        .estado-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .icono-estado {
            font-size: 1.5rem;
        }

        .estado-item strong {
            color: #333;
            font-size: 0.9rem;
        }

        .estado-item p {
            color: #666;
            font-size: 0.8rem;
            margin: 0;
        }

        .enlaces-adicionales {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .enlace-volver {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</body>
</html>