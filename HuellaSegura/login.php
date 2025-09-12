<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";

// Verificar si ya hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Procesar formulario de login
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    
    if (!empty($email) && !empty($contraseña)) {
        // Consulta muy básica para verificar usuario
        $consulta = "SELECT id_usuario, nombre_usuario, apellido_usuario, contraseña_usuario FROM usuarios WHERE email_usuario = '$email' AND estado = 'activo'";
        $resultado = $conexion->query($consulta);
        
        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            
            // Verificar contraseña (en un caso real usarías password_verify)
            if ($contraseña == $usuario['contraseña_usuario']) {
                // Crear sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];
                $_SESSION['usuario_apellido'] = $usuario['apellido_usuario'];
                
                header("Location: index.php");
                exit();
            } else {
                $mensaje_error = "Contraseña incorrecta";
            }
        } else {
            $mensaje_error = "Usuario no encontrado";
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
    <title>Iniciar Sesión - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="cabecera-principal">
        <div class="logo-contenedor">
            <h1 class="logo-texto">PetCare 🐾</h1>
            <p class="logo-subtitulo">Tu compañero para el cuidado de mascotas</p>
        </div>
    </div>

    <div class="contenedor-login">
        <h2 class="titulo-login">Iniciar Sesión</h2>
        <p class="subtitulo-login">Bienvenido de vuelta a PetCare</p>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="mensaje-error" style="color: red; text-align: center; margin-bottom: 1rem;">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="grupo-input">
                <input type="email" name="email" class="input-login" placeholder="Correo electrónico" required>
            </div>
            
            <div class="grupo-input">
                <input type="password" name="contraseña" class="input-login" placeholder="Contraseña" required>
            </div>
            
            <button type="submit" class="boton-login">Iniciar Sesión</button>
        </form>
        
        <div style="text-align: center; margin: 1rem 0; color: #666;">o</div>
        
        <button class="boton-demo" onclick="loginDemo()">
            ❤️ Probar con Cuenta Demo
        </button>
        
        <button class="boton-veterinario" onclick="window.location.href='login-veterinario.php'">
            🩺 Iniciar Sesión como Veterinario
        </button>
        
        <button class="boton-admin" onclick="window.location.href='login-admin.php'">
            ⚙️ Acceso de Administrador
        </button>
        
        <div class="enlace-registro">
            ¿No tienes cuenta? <a href="registro.php">Registrarse</a>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                📋 <span>Historial médico completo</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                🎯 <span>Recordatorios personalizados</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                🔍 <span>Búsqueda de mascotas perdidas</span>
            </div>
        </div>
    </div>

    <script>
        function loginDemo() {
            // Crear usuario demo temporalmente
            fetch('crear-demo.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Enviar formulario con datos demo
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="email" value="${data.email}">
                        <input type="hidden" name="contraseña" value="${data.password}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>