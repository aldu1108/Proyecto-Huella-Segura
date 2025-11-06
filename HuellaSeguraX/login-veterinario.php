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
            <h1 class="logo-texto">Huella Segura</h1>
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
                <div class="icono-input"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg></div>
                <input type="email" name="email" class="input-login veterinario-input" placeholder="Correo electrónico profesional" required>
            </div>
            
            <div class="grupo-input">
                <div class="icono-input"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg></div>
                <input type="password" name="contraseña" class="input-login veterinario-input" placeholder="Contraseña" required>
            </div>
            
            <button type="submit" class="boton-login veterinario">🩺 Iniciar Sesión Profesional</button>
        </form>
        
        <div class="separador">
            <span>o</span>
        </div>
        
        <div class="opciones-veterinario">
            <button class="boton-registro-veterinario" onclick="window.location.href='registro-veterinario.php'">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#A7C4E5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Registrarse como Veterinario
            </button>
            
            <div class="info-estados">
                <div class="estado-item">
                    <span class="icono-estado"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg></span>
                    <div>
                        <strong>Cuenta Aprobada</strong>
                        <p>Acceso completo al panel veterinario</p>
                    </div>
                </div>
                <div class="estado-item">
                    <span class="icono-estado"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M480-516q65 0 110.5-45.5T636-672v-120H324v120q0 65 45.5 110.5T480-516ZM192-96v-72h60v-120q0-59 28-109.5t78-82.5q-49-32-77.5-82.5T252-672v-120h-60v-72h576v72h-60v120q0 59-28.5 109.5T602-480q50 32 78 82.5T708-288v120h60v72H192Z"/></svg></span>
                    <div>
                        <strong>Pendiente de Verificación</strong>
                        <p>Tu solicitud está siendo revisada</p>
                    </div>
                </div>
                <div class="estado-item">
                    <span class="icono-estado"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m291-240-51-51 189-189-189-189 51-51 189 189 189-189 51 51-189 189 189 189-51 51-189-189-189 189Z"/></svg></span>
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