<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

// Obtener estadísticas básicas con manejo de errores
try {
    $total_usuarios = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'")->fetch_assoc()['total'] ?? 0;
    $total_mascotas = $conexion->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'activo'")->fetch_assoc()['total'] ?? 0;
    $total_veterinarios = $conexion->query("SELECT COUNT(*) as total FROM veterinario WHERE certificado = 1")->fetch_assoc()['total'] ?? 0;
    $total_adopciones = $conexion->query("SELECT COUNT(*) as total FROM publicacion_adopcion")->fetch_assoc()['total'] ?? 0;
    $total_perdidas = $conexion->query("SELECT COUNT(*) as total FROM publicacion_perdida")->fetch_assoc()['total'] ?? 0;
    $total_citas = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    // Valores por defecto si hay error
    $total_usuarios = 0;
    $total_mascotas = 0;
    $total_veterinarios = 0;
    $total_adopciones = 0;
    $total_perdidas = 0;
    $total_citas = 0;
}

// Usuarios recientes
try {
    $usuarios_recientes = $conexion->query("SELECT nombre_usuario, apellido_usuario, email_usuario, 
                                        DATE_FORMAT(NOW(), '%Y-%m-%d') as fecha_registro 
                                        FROM usuarios WHERE estado = 'activo' 
                                        ORDER BY id_usuario DESC LIMIT 10");
} catch (Exception $e) {
    $usuarios_recientes = null;
}

// Veterinarios pendientes de verificación
try {
    $veterinarios_pendientes = $conexion->query("SELECT u.nombre_usuario, u.apellido_usuario, u.email_usuario, v.especialidad, v.clinica 
                                                FROM usuarios u 
                                                JOIN veterinario v ON u.id_usuario = v.id_usuario 
                                                WHERE v.certificado = 0 LIMIT 10");
} catch (Exception $e) {
    $veterinarios_pendientes = null;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/panel-admin.css">
</head>

<body class="admin-panel">
    <header class="header-admin">
        <div class="logo-admin">
            <h1>🛡️ Panel Administrativo</h1>
            <p>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
        </div>
        <div class="acciones-admin">
            <span class="hora-sesion">Sesión iniciada: <?php echo $_SESSION['hora_login']; ?></span>
            <a href="logout.php" class="boton-logout">🚪 Cerrar Sesión</a>
        </div>
    </header>

    <div class="contenedor-admin">
        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-admin mensaje-<?php echo $_GET['tipo']; ?>">
                <span><?php echo htmlspecialchars($_GET['mensaje']); ?></span>
                <button onclick="this.parentElement.remove()"
                    style="background:none;border:none;color:inherit;cursor:pointer;padding:0 5px;">✕</button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas principales -->
        <section class="estadisticas-admin">
            <h2>📊 Estadísticas del Sistema</h2>
            <div class="tarjetas-estadisticas">
                <div class="tarjeta-stat">
                    <div class="icono-stat">👥</div>
                    <div class="numero-stat"><?php echo $total_usuarios; ?></div>
                    <div class="texto-stat">Usuarios Activos</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">🐾</div>
                    <div class="numero-stat"><?php echo $total_mascotas; ?></div>
                    <div class="texto-stat">Mascotas Registradas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">🩺</div>
                    <div class="numero-stat"><?php echo $total_veterinarios; ?></div>
                    <div class="texto-stat">Veterinarios Certificados</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">❤️</div>
                    <div class="numero-stat"><?php echo $total_adopciones; ?></div>
                    <div class="texto-stat">Adopciones Publicadas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">🔍</div>
                    <div class="numero-stat"><?php echo $total_perdidas; ?></div>
                    <div class="texto-stat">Mascotas Perdidas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">📅</div>
                    <div class="numero-stat"><?php echo $total_citas; ?></div>
                    <div class="texto-stat">Citas Veterinarias</div>
                </div>
            </div>
        </section>

        <!-- Acciones rápidas -->
        <section class="acciones-rapidas">
            <h2>⚡ Acciones Rápidas</h2>
            <div class="botones-accion">
                <button class="boton-accion usuarios" onclick="verUsuarios()">
                    👥 Gestionar Usuarios
                </button>
                <button class="boton-accion veterinarios" onclick="verVeterinarios()">
                    🩺 Verificar Veterinarios
                </button>
                <button class="boton-accion reportes" onclick="verReportes()">
                    📋 Ver Reportes
                </button>
                <button class="boton-accion configuracion" onclick="configurarSistema()">
                    ⚙️ Configuración Sistema
                </button>
            </div>
        </section>

        <!-- Usuarios recientes -->
        <section class="usuarios-recientes">
            <h2>👥 Usuarios Recientes</h2>
            <div class="tabla-admin">
                <?php if ($usuarios_recientes && $usuarios_recientes->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($usuario = $usuarios_recientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['email_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['fecha_registro']); ?></td>
                                    <td>
                                        <button class="boton-pequeno ver"
                                            onclick="verUsuario('<?php echo htmlspecialchars($usuario['email_usuario']); ?>')">Ver</button>
                                        <button class="boton-pequeno editar"
                                            onclick="editarUsuario('<?php echo htmlspecialchars($usuario['email_usuario']); ?>')">Editar</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="sin-datos">No hay usuarios registrados</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Veterinarios pendientes -->
        <section class="veterinarios-pendientes">
            <h2>⏳ Veterinarios Pendientes de Verificación</h2>
            <div class="tabla-admin">
                <?php if ($veterinarios_pendientes && $veterinarios_pendientes->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Especialidad</th>
                                <th>Clínica</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($veterinario = $veterinarios_pendientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($veterinario['nombre_usuario'] . ' ' . $veterinario['apellido_usuario']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($veterinario['email_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($veterinario['especialidad']); ?></td>
                                    <td><?php echo htmlspecialchars($veterinario['clinica']); ?></td>
                                    <td>
                                        <a href="aprobar-veterinario.php?accion=aprobar&email=<?php echo urlencode($veterinario['email_usuario']); ?>"
                                            class="boton-pequeno aprobar"
                                            onclick="return confirm('¿Aprobar a <?php echo htmlspecialchars($veterinario['nombre_usuario']); ?>? Podrá acceder al sistema.')">
                                            ✅ Aprobar
                                        </a>
                                        <a href="aprobar-veterinario.php?accion=rechazar&email=<?php echo urlencode($veterinario['email_usuario']); ?>"
                                            class="boton-pequeno rechazar"
                                            onclick="return confirm('¿Rechazar y ELIMINAR a <?php echo htmlspecialchars($veterinario['nombre_usuario']); ?>? Esta acción no se puede deshacer.')">
                                            ❌ Rechazar
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="sin-datos">No hay veterinarios pendientes de verificación</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Información del sistema -->
        <section class="info-sistema">
            <h2>💡 Información del Sistema</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>🔒 Estado de Acceso:</strong> Administrador autenticado
                </div>
                <div class="info-item">
                    <strong>📅 Fecha:</strong> <?php echo date('d/m/Y'); ?>
                </div>
                <div class="info-item">
                    <strong>⏰ Hora:</strong> <?php echo date('H:i:s'); ?>
                </div>
                <div class="info-item">
                    <strong>🌐 Usuario:</strong> <?php echo $_SESSION['usuario_nombre']; ?>
                </div>
            </div>
        </section>
    </div>

    <script src="js/panel-admin.js"></script>
</body>

</html>

<?php cerrarConexion(); ?>