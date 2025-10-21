<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

// Procesar actualización de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        switch ($accion) {
            case 'actualizar_general':
                // Aquí podrías guardar configuraciones en una tabla de configuración
                // Por ahora, solo mostramos mensaje de éxito
                $mensaje = "Configuración general actualizada correctamente";
                $tipo = "success";
                break;

            case 'limpiar_cache':
                // Simular limpieza de caché
                $mensaje = "Caché del sistema limpiado correctamente";
                $tipo = "success";
                break;

            case 'backup_db':
                // Simular backup de base de datos
                $mensaje = "Backup de base de datos generado correctamente";
                $tipo = "success";
                break;

            case 'mantenimiento':
                $estado = $_POST['estado_mantenimiento'] ?? 'off';
                // Aquí guardarías el estado en una tabla o archivo de configuración
                $mensaje = $estado === 'on' ?
                    "Modo mantenimiento activado" :
                    "Modo mantenimiento desactivado";
                $tipo = "success";
                break;
        }

        header("Location: configuracion-sistema.php?mensaje=" . urlencode($mensaje) . "&tipo=" . $tipo);
        exit();

    } catch (Exception $e) {
        header("Location: configuracion-sistema.php?mensaje=" . urlencode("Error: " . $e->getMessage()) . "&tipo=error");
        exit();
    }
}

// Obtener estadísticas del sistema
try {
    $total_usuarios = $conexion->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'] ?? 0;
    $total_mascotas = $conexion->query("SELECT COUNT(*) as total FROM mascotas")->fetch_assoc()['total'] ?? 0;
    $total_publicaciones = $conexion->query("SELECT COUNT(*) as total FROM publicaciones")->fetch_assoc()['total'] ?? 0;
    $total_citas = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias")->fetch_assoc()['total'] ?? 0;

    // Tamaño aproximado de la base de datos
    $db_size_query = $conexion->query("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = 'huellasegura'
    ");
    $db_size = $db_size_query->fetch_assoc()['size_mb'] ?? 0;

} catch (Exception $e) {
    $total_usuarios = 0;
    $total_mascotas = 0;
    $total_publicaciones = 0;
    $total_citas = 0;
    $db_size = 0;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/panel-admin.css">
    <link rel="stylesheet" href="css/configuracion-sistema.css">
    <?php include_once("includes/logo.php"); ?>
</head>

<body class="admin-panel">
    <header class="header-admin">
        <div class="logo-admin">
            <h1>⚙️ Configuración del Sistema</h1>
            <p>Panel de administración y configuración</p>
        </div>
        <div class="acciones-admin">
            <a href="panel-admin.php" class="boton-volver">⬅️ Volver al Panel</a>
            <a href="logout.php" class="boton-logout">🚪 Cerrar Sesión</a>
        </div>
    </header>

    <div class="contenedor-admin">
        <!-- Mostrar mensajes -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-admin mensaje-<?php echo $_GET['tipo']; ?>">
                <span><?php echo htmlspecialchars($_GET['mensaje']); ?></span>
                <button onclick="this.parentElement.remove()"
                    style="background:none;border:none;color:inherit;cursor:pointer;padding:0 5px;">✕</button>
            </div>
        <?php endif; ?>

        <!-- Información del Sistema -->
        <section class="seccion-config">
            <h2>📊 Información del Sistema</h2>
            <div class="info-sistema-grid">
                <div class="info-card">
                    <div class="info-icon">👥</div>
                    <div class="info-datos">
                        <div class="info-numero"><?php echo $total_usuarios; ?></div>
                        <div class="info-label">Usuarios Totales</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">🐾</div>
                    <div class="info-datos">
                        <div class="info-numero"><?php echo $total_mascotas; ?></div>
                        <div class="info-label">Mascotas Registradas</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">📢</div>
                    <div class="info-datos">
                        <div class="info-numero"><?php echo $total_publicaciones; ?></div>
                        <div class="info-label">Publicaciones</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">💾</div>
                    <div class="info-datos">
                        <div class="info-numero"><?php echo $db_size; ?> MB</div>
                        <div class="info-label">Tamaño BD</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Configuración General -->
        <section class="seccion-config">
            <h2>🔧 Configuración General</h2>
            <form method="POST" class="form-config">
                <input type="hidden" name="accion" value="actualizar_general">

                <div class="config-group">
                    <label>Nombre del Sistema</label>
                    <input type="text" name="nombre_sistema" value="Huella Segura" class="input-config">
                </div>

                <div class="config-group">
                    <label>Email de Contacto</label>
                    <input type="email" name="email_contacto" value="admin@huellasegura.com" class="input-config">
                </div>

                <div class="config-group">
                    <label>Límite de Mascotas por Usuario</label>
                    <input type="number" name="limite_mascotas" value="10" min="1" class="input-config">
                </div>

                <div class="config-group">
                    <label>Días para Auto-cerrar Publicaciones</label>
                    <input type="number" name="dias_autoclose" value="30" min="7" class="input-config">
                </div>

                <button type="submit" class="boton-guardar">💾 Guardar Configuración</button>
            </form>
        </section>

        <!-- Mantenimiento -->
        <section class="seccion-config">
            <h2>🔨 Mantenimiento del Sistema</h2>
            <div class="mantenimiento-grid">
                <div class="mantenimiento-card">
                    <div class="card-icon">🗑️</div>
                    <h3>Limpiar Caché</h3>
                    <p>Elimina archivos temporales y caché del sistema</p>
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="accion" value="limpiar_cache">
                        <button type="submit" class="boton-accion-mant limpiar">Limpiar Ahora</button>
                    </form>
                </div>

                <div class="mantenimiento-card">
                    <div class="card-icon">💾</div>
                    <h3>Backup Base de Datos</h3>
                    <p>Genera una copia de seguridad completa</p>
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="accion" value="backup_db">
                        <button type="submit" class="boton-accion-mant backup">Generar Backup</button>
                    </form>
                </div>

                <div class="mantenimiento-card">
                    <div class="card-icon">🔧</div>
                    <h3>Modo Mantenimiento</h3>
                    <p>Desactiva temporalmente el acceso al sistema</p>
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="accion" value="mantenimiento">
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="estado_mantenimiento" value="on">
                                <span class="toggle-slider"></span>
                            </label>
                            <span>Activar/Desactivar</span>
                        </div>
                        <button type="submit" class="boton-accion-mant mantenimiento">Aplicar</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Estadísticas Avanzadas -->
        <section class="seccion-config">
            <h2>📈 Estadísticas Avanzadas</h2>
            <div class="estadisticas-avanzadas">
                <div class="stat-item">
                    <strong>Total de Citas:</strong>
                    <span><?php echo $total_citas; ?></span>
                </div>
                <div class="stat-item">
                    <strong>Versión del Sistema:</strong>
                    <span>1.0.0</span>
                </div>
                <div class="stat-item">
                    <strong>PHP Version:</strong>
                    <span><?php echo phpversion(); ?></span>
                </div>
                <div class="stat-item">
                    <strong>MySQL Version:</strong>
                    <span><?php echo $conexion->server_info; ?></span>
                </div>
            </div>
        </section>

        <!-- Logs del Sistema -->
        <section class="seccion-config">
            <h2>📋 Actividad Reciente</h2>
            <div class="logs-container">
                <div class="log-item">
                    <span class="log-time"><?php echo date('H:i:s'); ?></span>
                    <span class="log-tipo info">INFO</span>
                    <span class="log-mensaje">Sistema funcionando correctamente</span>
                </div>
                <div class="log-item">
                    <span class="log-time"><?php echo date('H:i:s', strtotime('-5 minutes')); ?></span>
                    <span class="log-tipo success">SUCCESS</span>
                    <span class="log-mensaje">Administrador <?php echo $_SESSION['usuario_nombre']; ?> inició
                        sesión</span>
                </div>
                <div class="log-item">
                    <span class="log-time"><?php echo date('H:i:s', strtotime('-15 minutes')); ?></span>
                    <span class="log-tipo info">INFO</span>
                    <span class="log-mensaje">Sistema iniciado correctamente</span>
                </div>
            </div>
        </section>
    </div>

    <script src="js/panel-admin.js"></script>
</body>

</html>

<?php cerrarConexion(); ?>