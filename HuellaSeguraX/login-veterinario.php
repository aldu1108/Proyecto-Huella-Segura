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
        // Consulta para verificar veterinario
        $consulta = "SELECT u.id_usuario, u.nombre_usuario, u.apellido_usuario, u.contraseña_usuario, v.id_veterinario, v.especialidad, v.clinica, v.certificado
                     FROM usuarios u 
                     JOIN veterinario v ON u.id_usuario = v.id_usuario 
                     WHERE u.email_usuario = '$email' AND u.estado = 'activo' AND v.certificado = 1";
        $resultado = $conexion->query($consulta);
        
        if ($resultado && $resultado->num_rows > 0) {
            $veterinario = $resultado->fetch_assoc();
            
            // Verificar contraseña
            if ($contraseña == $veterinario['contraseña_usuario']) {
                // Crear sesión de veterinario
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
                $mensaje_error = "Contraseña incorrecta";
            }
        } else {
            $mensaje_error = "Veterinario no encontrado o no certificado";
        }
    } else {
        $mensaje_error = "Por favor complete todos los campos";
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
<body>
    <div class="cabecera-principal">
        <div class="logo-contenedor">
            <h1 class="logo-texto">PetCare 🐾</h1>
            <p class="logo-subtitulo">Portal Veterinario Profesional</p>
        </div>
    </div>

    <div class="contenedor-login veterinario">
        <div class="encabezado-veterinario">
            <h2 class="titulo-login">Acceso Veterinario</h2>
            <p class="subtitulo-login">Panel profesional para veterinarios certificados</p>
        </div>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="mensaje-error" style="color: red; text-align: center; margin-bottom: 1rem; padding: 0.8rem; background-color: #ffebee; border-radius: 5px;">
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
            
            <div class="recordar-datos">
                <input type="checkbox" id="recordar">
                <label for="recordar">Mantener sesión activa</label>
            </div>
            
            <button type="submit" class="boton-login veterinario">Iniciar Sesión Profesional</button>
        </form>
        
        <div class="separador">
            <span>o</span>
        </div>
        
        <div class="opciones-veterinario">
            <button class="boton-registro-veterinario" onclick="window.location.href='registro-veterinario.php'">
                📋 Registrarse como Veterinario
            </button>
            
            <button class="boton-recuperar" onclick="mostrarRecuperacion()">
                🔑 ¿Olvidaste tu contraseña?
            </button>
        </div>
        
        <div class="info-veterinario">
            <h4>Acceso al Panel Veterinario</h4>
            <div class="caracteristicas-veterinario">
                <div class="caracteristica">
                    <span class="icono">📊</span>
                    <div>
                        <strong>Panel de Control</strong>
                        <p>Gestiona citas, historiales y pacientes</p>
                    </div>
                </div>
                
                <div class="caracteristica">
                    <span class="icono">🏥</span>
                    <div>
                        <strong>Agenda Profesional</strong>
                        <p>Calendario de citas y disponibilidad</p>
                    </div>
                </div>
                
                <div class="caracteristica">
                    <span class="icono">📋</span>
                    <div>
                        <strong>Historiales Médicos</strong>
                        <p>Acceso completo a expedientes</p>
                    </div>
                </div>
                
                <div class="caracteristica">
                    <span class="icono">💊</span>
                    <div>
                        <strong>Prescripciones</strong>
                        <p>Gestión de tratamientos y medicamentos</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="enlaces-adicionales">
            <a href="login.php" class="enlace-volver">← Volver al login normal</a>
            <a href="ayuda-veterinarios.php" class="enlace-ayuda">¿Necesitas ayuda?</a>
        </div>
        
        <div class="certificacion-info">
            <p><strong>Nota:</strong> Solo veterinarios con certificación verificada pueden acceder a este panel.</p>
            <p>Si eres veterinario y no tienes acceso, <a href="contacto-verificacion.php">solicita verificación</a>.</p>
        </div>
    </div>

    <!-- Modal de recuperación -->
    <div class="modal-recuperacion" id="modalRecuperacion" style="display: none;">
        <div class="contenido-modal">
            <div class="encabezado-modal">
                <h3>Recuperar Contraseña</h3>
                <button class="boton-cerrar-modal" onclick="cerrarRecuperacion()">✕</button>
            </div>
            <form method="POST" action="recuperar-veterinario.php">
                <div class="grupo-input">
                    <label>Correo electrónico profesional</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="grupo-input">
                    <label>Número de colegiado (opcional)</label>
                    <input type="text" name="numero_colegiado" placeholder="Para verificación adicional">
                </div>
                
                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarRecuperacion()">Cancelar</button>
                    <button type="submit" class="boton-guardar">Enviar Enlace de Recuperación</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function mostrarRecuperacion() {
            document.getElementById('modalRecuperacion').style.display = 'flex';
        }

        function cerrarRecuperacion() {
            document.getElementById('modalRecuperacion').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalRecuperacion').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarRecuperacion();
            }
        });
    </script>

    <style>
        /* Estilos específicos para login veterinario */
        .contenedor-login.veterinario {
            background: linear-gradient(135deg, #8d6e63 0%, #5d4e75 100%);
            color: white;
            max-width: 500px;
        }

        .encabezado-veterinario {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }

        .titulo-login, .subtitulo-login {
            color: white;
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
            z-index: 1;
        }

        .veterinario-input {
            padding-left: 3.5rem;
            background: rgba(255,255,255,0.95);
            border: 2px solid rgba(255,255,255,0.3);
        }

        .veterinario-input:focus {
            background: white;
            border-color: #8d6e63;
        }

        .recordar-datos {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.9);
        }

        .boton-login.veterinario {
            background: linear-gradient(135deg, #6a4c93, #8d6e63);
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .separador {
            text-align: center;
            margin: 1.5rem 0;
            color: rgba(255,255,255,0.7);
        }

        .separador span {
            background: linear-gradient(135deg, #8d6e63 0%, #5d4e75 100%);
            padding: 0 1rem;
        }

        .opciones-veterinario {
            margin-bottom: 2rem;
        }

        .boton-registro-veterinario, .boton-recuperar {
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .boton-registro-veterinario:hover, .boton-recuperar:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
        }

        .info-veterinario {
            background: rgba(255,255,255,0.1);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .info-veterinario h4 {
            color: #f1c40f;
            margin-bottom: 1rem;
            text-align: center;
        }

        .caracteristicas-veterinario {
            display: grid;
            gap: 1rem;
        }

        .caracteristica {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .caracteristica .icono {
            font-size: 1.5rem;
            width: 40px;
            text-align: center;
        }

        .caracteristica strong {
            color: #f39c12;
            display: block;
        }

        .caracteristica p {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
            margin: 0;
        }

        .enlaces-adicionales {
            text-align: center;
            margin-bottom: 1rem;
        }

        .enlace-volver, .enlace-ayuda {
            color: #3498db;
            text-decoration: none;
            margin: 0 1rem;
            font-size: 0.9rem;
        }

        .enlace-volver:hover, .enlace-ayuda:hover {
            text-decoration: underline;
        }

        .certificacion-info {
            background: rgba(231,76,60,0.2);
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid #e74c3c;
            font-size: 0.9rem;
        }

        .certificacion-info a {
            color: #f39c12;
            text-decoration: none;
        }

        .certificacion-info a:hover {
            text-decoration: underline;
        }

        /* Modal */
        .modal-recuperacion {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .modal-recuperacion .contenido-modal {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
        }

        .modal-recuperacion .grupo-input label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }

        .modal-recuperacion .grupo-input input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .contenedor-login.veterinario {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .caracteristicas-veterinario {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>