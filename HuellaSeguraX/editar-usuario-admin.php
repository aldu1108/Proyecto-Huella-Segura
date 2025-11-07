<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Obtener email del usuario
$email = $_GET['email'] ?? $_POST['email_original'] ?? '';

if (empty($email)) {
    header("Location: panel-admin.php?mensaje=Usuario no especificado&tipo=error");
    exit();
}

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cambios'])) {
    $nombre = $_POST['nombre_usuario'];
    $apellido = $_POST['apellido_usuario'];
    $email_nuevo = $_POST['email_usuario'];
    $telefono = $_POST['telefono_usuario'];
    $estado = $_POST['estado'];
    $rol = $_POST['rol'];

    // Validar datos
    if (empty($nombre) || empty($apellido) || empty($email_nuevo)) {
        $mensaje = "Por favor complete todos los campos obligatorios";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el nuevo email ya existe (si cambió)
        if ($email !== $email_nuevo) {
            $consulta_email = "SELECT id_usuario FROM usuarios WHERE email_usuario = ? AND email_usuario != ?";
            $stmt_check = $conexion->prepare($consulta_email);
            $stmt_check->bind_param("ss", $email_nuevo, $email);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $mensaje = "Ya existe otro usuario con ese correo electrónico";
                $tipo_mensaje = "error";
            }
        }

        if (empty($mensaje)) {
            // Actualizar usuario
            $consulta_update = "UPDATE usuarios SET 
                nombre_usuario = ?, 
                apellido_usuario = ?, 
                email_usuario = ?, 
                telefono_usuario = ?, 
                estado = ?, 
                rol = ? 
                WHERE email_usuario = ?";

            $stmt_update = $conexion->prepare($consulta_update);
            $stmt_update->bind_param("sssssss", $nombre, $apellido, $email_nuevo, $telefono, $estado, $rol, $email);

            if ($stmt_update->execute()) {
                $mensaje = "Usuario actualizado exitosamente";
                $tipo_mensaje = "exito";
                $email = $email_nuevo; // Actualizar para consultas siguientes
            } else {
                $mensaje = "Error al actualizar el usuario";
                $tipo_mensaje = "error";
            }
        }
    }
}

// Actualizar contraseña si se proporcionó
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_contrasena'])) {
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if (empty($nueva_contrasena)) {
        $mensaje = "La contraseña no puede estar vacía";
        $tipo_mensaje = "error";
    } elseif ($nueva_contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden";
        $tipo_mensaje = "error";
    } elseif (strlen($nueva_contrasena) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres";
        $tipo_mensaje = "error";
    } else {
        $consulta_pass = "UPDATE usuarios SET contraseña_usuario = ? WHERE email_usuario = ?";
        $stmt_pass = $conexion->prepare($consulta_pass);
        $stmt_pass->bind_param("ss", $nueva_contrasena, $email);

        if ($stmt_pass->execute()) {
            $mensaje = "Contraseña actualizada exitosamente";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al actualizar la contraseña";
            $tipo_mensaje = "error";
        }
    }
}

// Obtener datos actuales del usuario
$consulta_usuario = "SELECT * FROM usuarios WHERE email_usuario = ?";
$stmt = $conexion->prepare($consulta_usuario);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: panel-admin.php?mensaje=Usuario no encontrado&tipo=error");
    exit();
}

$usuario = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Panel Admin</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/panel-admin.css">
    <link rel="stylesheet" href="css/registro.css">
    <link rel="stylesheet" href="css/editar-usuario-admin.css">
    <?php include_once("includes/logo.php"); ?>
</head>

<body class="admin-panel">
    <header class="header-admin">
        <div class="logo-admin">
            <h1><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#5985E1"><path d="M480-96q-135-33-223.5-152.84Q168-368.69 168-515v-229l312-120 312 120v229q0 146.31-88.5 266.16Q615-129 480-96Z"/></svg> Panel Administrativo</h1>
        </div>
        <div class="acciones-admin">
            <a href="logout.php" class="boton-logout">🚪 Cerrar Sesión</a>
            <a href="panel-admin.php" class="boton-logout"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M384-288 192-480l192-192 51 51-105 105h438v72H330l105 105-51 51Z"/></svg> Volver al Panel</a>
        </div>
    </header>

    <div class="contenedor-edicion">
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de datos básicos -->
        <form method="POST" class="formulario-edicion">
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#FFFF55"><path d="M144-144v-153l498-498q11-11 24-16t27-5q14 0 27 5t24 16l51 51q11 11 16 24t5 27q0 14-5 27t-16 24L297-144H144Zm549-498 51-51-51-51-51 51 51 51Z"/></svg> Editar Información del Usuario</h2>

            <input type="hidden" name="email_original" value="<?php echo htmlspecialchars($email); ?>">

            <div class="grupo-campo">
                <label>Nombre *</label>
                <input type="text" name="nombre_usuario"
                    value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
            </div>

            <div class="grupo-campo">
                <label>Apellido *</label>
                <input type="text" name="apellido_usuario"
                    value="<?php echo htmlspecialchars($usuario['apellido_usuario']); ?>" required>
            </div>

            <div class="grupo-campo">
                <label>Email *</label>
                <input type="email" name="email_usuario"
                    value="<?php echo htmlspecialchars($usuario['email_usuario']); ?>" required>
            </div>

            <div class="grupo-campo">
                <label>Teléfono</label>
                <input type="tel" name="telefono_usuario"
                    value="<?php echo htmlspecialchars($usuario['telefono_usuario']); ?>">
            </div>

            <div class="grupo-campo">
                <label>Estado *</label>
                <select name="estado" required>
                    <option value="activo" <?php echo $usuario['estado'] === 'activo' ? 'selected' : ''; ?>>Activo
                    </option>
                    <option value="inactivo" <?php echo $usuario['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo
                    </option>
                    <option value="suspendido" <?php echo $usuario['estado'] === 'suspendido' ? 'selected' : ''; ?>>
                        Suspendido</option>
                </select>
            </div>

            <div class="grupo-campo">
                <label>Rol *</label>
                <select name="rol" required>
                    <option value="usuario" <?php echo $usuario['rol'] === 'usuario' ? 'selected' : ''; ?>>Usuario
                    </option>
                    <option value="veterinario" <?php echo $usuario['rol'] === 'veterinario' ? 'selected' : ''; ?>>
                        Veterinario</option>
                    <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador
                    </option>
                    <option value="demo" <?php echo $usuario['rol'] === 'demo' ? 'selected' : ''; ?>>Demo</option>
                </select>
            </div>

            <div class="grupo-botones">
                <button type="submit" name="guardar_cambios" class="boton boton-guardar">
                    <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#666666"><path d="M816-672v456q0 29.7-21.15 50.85Q773.7-144 744-144H216q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h456l144 144ZM480-252q45 0 76.5-31.5T588-360q0-45-31.5-76.5T480-468q-45 0-76.5 31.5T372-360q0 45 31.5 76.5T480-252ZM264-552h336v-144H264v144Z"/></svg> Guardar Cambios
                </button>
                <a href="ver-usuario.php?email=<?php echo urlencode($email); ?>" class="boton boton-cancelar">
                    <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#EA3323"><path d="m291-240-51-51 189-189-189-189 51-51 189 189 189-189 51 51-189 189 189 189-51 51-189-189-189 189Z"/></svg> Cancelar
                </a>
            </div>
        </form>

        <!-- Formulario de cambio de contraseña -->
        <form method="POST" class="formulario-edicion seccion-peligro">
            <h3><svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Cambiar Contraseña</h3>
            <p style="color: #666; margin-bottom: 15px;">
                Esta acción cambiará la contraseña del usuario. Úsala con precaución.
            </p>

            <input type="hidden" name="email_original" value="<?php echo htmlspecialchars($email); ?>">

            <div class="grupo-campo">
                <label>Nueva Contraseña (mín. 6 caracteres) *</label>
                <input type="password" name="nueva_contrasena" minlength="6">
            </div>

            <div class="grupo-campo">
                <label>Confirmar Contraseña *</label>
                <input type="password" name="confirmar_contrasena" minlength="6">
            </div>

            <button type="submit" name="cambiar_contrasena" class="boton boton-guardar">
                <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Actualizar Contraseña
            </button>
        </form>
    </div>
</body>

</html>
<?php cerrarConexion(); ?>