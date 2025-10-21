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
            <h1>🛡️ Panel Administrativo</h1>
        </div>
        <div class="acciones-admin">
            <a href="panel-admin.php" class="boton-logout">⬅️ Volver al Panel</a>
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
            <h2>✏️ Editar Información del Usuario</h2>

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
                    💾 Guardar Cambios
                </button>
                <a href="ver-usuario.php?email=<?php echo urlencode($email); ?>" class="boton boton-cancelar">
                    ❌ Cancelar
                </a>
            </div>
        </form>

        <!-- Formulario de cambio de contraseña -->
        <form method="POST" class="formulario-edicion seccion-peligro">
            <h3>🔒 Cambiar Contraseña</h3>
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
                🔐 Actualizar Contraseña
            </button>
        </form>
    </div>
</body>

</html>
<?php cerrarConexion(); ?>