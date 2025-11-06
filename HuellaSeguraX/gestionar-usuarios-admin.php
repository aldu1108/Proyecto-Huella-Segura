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
            <h1><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M480-96q-135-33-223.5-152.84Q168-368.69 168-515v-229l312-120 312 120v229q0 146.31-88.5 266.16Q615-129 480-96Z"/></svg> Panel Administrativo</h1>
        </div>
        <div class="acciones-admin">
            <a href="panel-admin.php" class="boton-logout"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M384-288 192-480l192-192 51 51-105 105h438v72H330l105 105-51 51Z"/></svg> Volver al Panel</a>
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
                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M48-264v-57q0-39 39-63t105-24q14 0 26 1t23 3q-12 18-18.5 39.11Q216-343.77 216-322v58H48Zm216 0v-58q0-28 14.5-50t43.5-39q29-17 69-25t89.5-8q49.5 0 89 8t68.5 25q29 16 43.5 38.69Q696-349.62 696-322v58H264Zm480 0v-58q0-22-6.5-42.5T719-404q9-2 20.5-3t28.5-1q66 0 105 24t39 63v57H744ZM192-456q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm576 0q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm-288-36q-45 0-76.5-31.52T372-600.07q0-44.93 31.52-76.43 31.52-31.5 76.55-31.5 44.93 0 76.43 31.55Q588-644.9 588-600q0 45-31.55 76.5T480-492Z"/></svg> Total Usuarios</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $usuarios_activos; ?></h3>
                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Usuarios Activos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $usuarios_inactivos; ?></h3>
                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M288-444h384v-72H288v72ZM480.28-96Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Z"/></svg> Usuarios Inactivos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_veterinarios; ?></h3>
                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg> Veterinarios</p>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="filtros-busqueda">
            <h2 style="margin-top: 0;"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#999999"><path d="M765-144 526-383q-30 22-65.79 34.5-35.79 12.5-76.18 12.5Q284-336 214-406t-70-170q0-100 70-170t170-70q100 0 170 70t70 170.03q0 40.39-12.5 76.18Q599-464 577-434l239 239-51 51ZM384-408q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Z"/></svg> Buscar y Filtrar Usuarios</h2>
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
                    <button type="submit" class="boton-filtrar"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#999999"><path d="M765-144 526-383q-30 22-65.79 34.5-35.79 12.5-76.18 12.5Q284-336 214-406t-70-170q0-100 70-170t170-70q100 0 170 70t70 170.03q0 40.39-12.5 76.18Q599-464 577-434l239 239-51 51ZM384-408q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Z"/></svg> Filtrar</button>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M480-312q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Zm0-72q-40 0-68-28t-28-68q0-40 28-68t68-28q40 0 68 28t28 68q0 40-28 68t-68 28Zm0 192q-142.6 0-259.8-78.5Q103-349 48-480q55-131 172.2-209.5Q337.4-768 480-768q142.6 0 259.8 78.5Q857-611 912-480q-55 131-172.2 209.5Q622.6-192 480-192Z"/></svg> Ver
                                        </a>
                                        <a href="editar-usuario-admin.php?email=<?php echo urlencode($usuario['email_usuario']); ?>"
                                            class="btn-accion btn-editar" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="M144-144v-153l498-498q11-11 24-16t27-5q14 0 27 5t24 16l51 51q11 11 16 24t5 27q0 14-5 27t-16 24L297-144H144Zm549-498 51-51-51-51-51 51 51 51Z"/></svg> Editar
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
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M564-228v-504h168v504H564Zm-336 0v-504h168v504H228Z"/></svg> Desactivar
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
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M384-288v-384l192 192-192 192Z"/></svg> Activar
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
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sin-resultados">
                    <h3>No se encontraron usuarios</h3>
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