<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

// Procesar eliminación de usuario
if (isset($_GET['eliminar'])) {
    $email_eliminar = $_GET['eliminar'];

    // Obtener ID del usuario
    $consulta_id = "SELECT id_usuario FROM usuarios WHERE email_usuario = ?";
    $stmt = $conexion->prepare($consulta_id);
    $stmt->bind_param("s", $email_eliminar);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $id_usuario = $usuario['id_usuario'];

        // Eliminar usuario (las relaciones se manejan con CASCADE en la BD)
        $consulta_eliminar = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt_delete = $conexion->prepare($consulta_eliminar);
        $stmt_delete->bind_param("i", $id_usuario);

        if ($stmt_delete->execute()) {
            header("Location: gestionar-usuarios-admin.php?mensaje=Usuario eliminado exitosamente&tipo=exito");
            exit();
        } else {
            header("Location: gestionar-usuarios-admin.php?mensaje=Error al eliminar usuario&tipo=error");
            exit();
        }
    }
}

// Procesar cambio de estado
if (isset($_GET['cambiar_estado'])) {
    $email = $_GET['cambiar_estado'];
    $nuevo_estado = $_GET['estado'];

    $consulta_estado = "UPDATE usuarios SET estado = ? WHERE email_usuario = ?";
    $stmt = $conexion->prepare($consulta_estado);
    $stmt->bind_param("ss", $nuevo_estado, $email);

    if ($stmt->execute()) {
        $mensaje_estado = $nuevo_estado === 'activo' ? 'Usuario activado exitosamente' : 'Usuario desactivado exitosamente';
        header("Location: gestionar-usuarios-admin.php?mensaje=" . urlencode($mensaje_estado) . "&tipo=exito");
        exit();
    } else {
        header("Location: gestionar-usuarios-admin.php?mensaje=Error al cambiar el estado&tipo=error");
        exit();
    }
}

// Filtros
$filtro_rol = $_GET['rol'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$busqueda = $_GET['buscar'] ?? '';

// Construir consulta con filtros
$consulta = "SELECT u.*, 
             (SELECT COUNT(*) FROM mascotas WHERE id_usuario = u.id_usuario) as total_mascotas,
             (SELECT COUNT(*) FROM publicaciones WHERE id_usuario = u.id_usuario) as total_publicaciones
             FROM usuarios u WHERE 1=1";

$params = [];
$types = "";

if (!empty($filtro_rol)) {
    $consulta .= " AND u.rol = ?";
    $params[] = $filtro_rol;
    $types .= "s";
}

if (!empty($filtro_estado)) {
    $consulta .= " AND u.estado = ?";
    $params[] = $filtro_estado;
    $types .= "s";
}

if (!empty($busqueda)) {
    $consulta .= " AND (u.nombre_usuario LIKE ? OR u.apellido_usuario LIKE ? OR u.email_usuario LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "sss";
}

$consulta .= " ORDER BY u.id_usuario DESC";

$stmt = $conexion->prepare($consulta);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$usuarios = $stmt->get_result();

// Estadísticas
$total_usuarios = $conexion->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$usuarios_activos = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'")->fetch_assoc()['total'];
$usuarios_inactivos = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 'inactivo'")->fetch_assoc()['total'];
$total_veterinarios = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'veterinario'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Panel Admin</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/panel-admin.css">
    <link rel="stylesheet" href="css/gestionar-usuarios-admin.css">
    <?php include_once("includes/logo.php"); ?>
</head>

<body class="admin-panel">
    <header class="header-admin">
        <div class="logo-admin">
            <h1>🛡️ Panel Administrativo</h1>
            <p>Gestión de Usuarios</p>
        </div>
        <div class="acciones-admin">
            <a href="panel-admin.php" class="boton-logout">⬅️ Volver al Panel</a>
        </div>
    </header>

    <div class="contenedor-gestion">
        <!-- Mensajes -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje mensaje-<?php echo $_GET['tipo']; ?>">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas Rápidas -->
        <div class="estadisticas-rapidas">
            <div class="stat-card">
                <h3><?php echo $total_usuarios; ?></h3>
                <p>👥 Total Usuarios</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $usuarios_activos; ?></h3>
                <p>✅ Usuarios Activos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $usuarios_inactivos; ?></h3>
                <p>⛔ Usuarios Inactivos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_veterinarios; ?></h3>
                <p>🩺 Veterinarios</p>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="filtros-busqueda">
            <h2 style="margin-top: 0;">🔍 Buscar y Filtrar Usuarios</h2>
            <form method="GET" class="form-filtros">
                <div class="campo-filtro">
                    <label>Buscar por nombre o email</label>
                    <input type="text" name="buscar" placeholder="Escribe nombre, apellido o email..."
                        value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>

                <div class="campo-filtro">
                    <label>Rol</label>
                    <select name="rol">
                        <option value="">Todos</option>
                        <option value="usuario" <?php echo $filtro_rol === 'usuario' ? 'selected' : ''; ?>>Usuario
                        </option>
                        <option value="veterinario" <?php echo $filtro_rol === 'veterinario' ? 'selected' : ''; ?>>
                            Veterinario</option>
                        <option value="admin" <?php echo $filtro_rol === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="demo" <?php echo $filtro_rol === 'demo' ? 'selected' : ''; ?>>Demo</option>
                    </select>
                </div>

                <div class="campo-filtro">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?php echo $filtro_estado === 'activo' ? 'selected' : ''; ?>>Activo
                        </option>
                        <option value="inactivo" <?php echo $filtro_estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo
                        </option>
                        <option value="suspendido" <?php echo $filtro_estado === 'suspendido' ? 'selected' : ''; ?>>
                            Suspendido</option>
                    </select>
                </div>

                <div class="campo-filtro">
                    <button type="submit" class="boton-filtrar">🔍 Filtrar</button>
                </div>
            </form>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="tabla-usuarios">
            <?php if ($usuarios->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Mascotas</th>
                            <th>Publicaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                            <tr>
                                <td data-label="ID">#<?php echo $usuario['id_usuario']; ?></td>
                                <td data-label="Usuario">
                                    <div class="usuario-info">
                                        <div class="usuario-avatar">
                                            <?php echo strtoupper(substr($usuario['nombre_usuario'], 0, 1) . substr($usuario['apellido_usuario'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Email"><?php echo htmlspecialchars($usuario['email_usuario']); ?></td>
                                <td data-label="Teléfono">
                                    <?php echo !empty($usuario['telefono_usuario']) ? htmlspecialchars($usuario['telefono_usuario']) : '-'; ?>
                                </td>
                                <td data-label="Rol">
                                    <span class="badge badge-<?php echo $usuario['rol']; ?>">
                                        <?php echo strtoupper($usuario['rol']); ?>
                                    </span>
                                </td>
                                <td data-label="Estado">
                                    <span class="badge badge-<?php echo $usuario['estado']; ?>">
                                        <?php echo strtoupper($usuario['estado']); ?>
                                    </span>
                                </td>
                                <td data-label="Mascotas"><?php echo $usuario['total_mascotas']; ?></td>
                                <td data-label="Publicaciones"><?php echo $usuario['total_publicaciones']; ?></td>
                                <td data-label="Acciones">
                                    <div class="acciones-tabla">
                                        <a href="ver-usuario-admin.php?email=<?php echo urlencode($usuario['email_usuario']); ?>"
                                            class="btn-accion btn-ver" title="Ver detalles">
                                            👁️ Ver
                                        </a>
                                        <a href="editar-usuario-admin.php?email=<?php echo urlencode($usuario['email_usuario']); ?>"
                                            class="btn-accion btn-editar" title="Editar">
                                            ✏️ Editar
                                        </a>
                                        <?php if ($usuario['estado'] === 'activo'): ?>
                                            <a href="gestionar-usuarios-admin.php?cambiar_estado=<?php echo urlencode($usuario['email_usuario']); ?>&estado=inactivo<?php
                                               // Mantener filtros actuales
                                               if (!empty($filtro_rol))
                                                   echo '&rol=' . urlencode($filtro_rol);
                                               if (!empty($filtro_estado))
                                                   echo '&estado_filtro=' . urlencode($filtro_estado);
                                               if (!empty($busqueda))
                                                   echo '&buscar=' . urlencode($busqueda);
                                               ?>" class="btn-accion btn-estado" title="Desactivar"
                                                onclick="return confirm('¿Desactivar a <?php echo htmlspecialchars($usuario['nombre_usuario']); ?>?')">
                                                ⏸️ Desactivar
                                            </a>
                                        <?php else: ?>
                                            <a href="gestionar-usuarios-admin.php?cambiar_estado=<?php echo urlencode($usuario['email_usuario']); ?>&estado=activo<?php
                                               // Mantener filtros actuales
                                               if (!empty($filtro_rol))
                                                   echo '&rol=' . urlencode($filtro_rol);
                                               if (!empty($filtro_estado))
                                                   echo '&estado_filtro=' . urlencode($filtro_estado);
                                               if (!empty($busqueda))
                                                   echo '&buscar=' . urlencode($busqueda);
                                               ?>" class="btn-accion btn-estado" title="Activar"
                                                onclick="return confirm('¿Activar a <?php echo htmlspecialchars($usuario['nombre_usuario']); ?>?')">
                                                ▶️ Activar
                                            </a>
                                        <?php endif; ?>
                                        <a href="gestionar-usuarios-admin.php?eliminar=<?php echo urlencode($usuario['email_usuario']); ?><?php
                                           // Mantener filtros actuales
                                           if (!empty($filtro_rol))
                                               echo '&rol=' . urlencode($filtro_rol);
                                           if (!empty($filtro_estado))
                                               echo '&estado_filtro=' . urlencode($filtro_estado);
                                           if (!empty($busqueda))
                                               echo '&buscar=' . urlencode($busqueda);
                                           ?>" class="btn-accion btn-eliminar" title="Eliminar"
                                            onclick="return confirm('⚠️ ¿Estás seguro de eliminar a <?php echo htmlspecialchars($usuario['nombre_usuario']); ?>?\n\nEsta acción eliminará:\n- El usuario\n- Sus mascotas\n- Sus publicaciones\n- Todos sus datos\n\nEsta acción NO se puede deshacer.')">
                                            🗑️ Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sin-resultados">
                    <h3>😕 No se encontraron usuarios</h3>
                    <p>Intenta ajustar los filtros de búsqueda</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-cerrar mensajes
        setTimeout(() => {
            const mensajes = document.querySelectorAll('.mensaje');
            mensajes.forEach(m => m.style.display = 'none');
        }, 5000);
    </script>
</body>

</html>
<?php cerrarConexion(); ?>