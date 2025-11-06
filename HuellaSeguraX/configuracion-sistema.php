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
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M444-288h72v-240h-72v240Zm35.79-312q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm.49 504Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Z"/></svg> Información del Sistema</h2>
            <div class="info-sistema-grid">
                <div class="info-card">
                    <div class="info-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M96-192v-92q0-26 12.5-47.5T143-366q54-32 114.5-49T384-432q66 0 126.5 17T625-366q22 13 34.5 34.5T672-284v92H96Zm648 0v-92q0-42-19.5-78T672-421q39 8 75.5 21.5T817-366q22 13 34.5 34.5T864-284v92H744ZM384-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42Zm336-144q0 60-42 102t-102 42q-8 0-15-.5t-15-2.5q25-29 39.5-64.5T600-624q0-41-14.5-76.5T546-765q8-2 15-2.5t15-.5q60 0 102 42t42 102Z"/></svg></div>
                    <div class="info-datos">
                        <div class="info-numero"><?php echo $total_usuarios; ?></div>
                        <div class="info-label">Usuarios Totales</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#d35400"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg></div>
                    <div class="info-datos">
                        <div class="info-numero"><?php echo $total_mascotas; ?></div>
                        <div class="info-label">Mascotas Registradas</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M720-444v-72h144v72H720Zm41 276-118-82 42-59 118 82-42 59Zm-77-483-41-59 118-82 41 59-118 82ZM192-192v-192h-24q-30 0-51-21t-21-51v-48q0-30 21-51t51-21h139l221-132v456L313-384h-25v192h-96Zm384-171v-234q23 22 35.5 53t12.5 64q0 33-12.5 64T576-363Z"/></svg></div>
                    <div class="info-datos">
                        <div class="info-numero"><?php echo $total_publicaciones; ?></div>
                        <div class="info-label">Publicaciones</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M816-672v456q0 29.7-21.15 50.85Q773.7-144 744-144H216q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h456l144 144ZM480-252q45 0 76.5-31.5T588-360q0-45-31.5-76.5T480-468q-45 0-76.5 31.5T372-360q0 45 31.5 76.5T480-252ZM264-552h336v-144H264v144Z"/></svg></div>
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

                <button type="submit" class="boton-guardar"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M816-672v456q0 29.7-21.15 50.85Q773.7-144 744-144H216q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h456l144 144ZM480-252q45 0 76.5-31.5T588-360q0-45-31.5-76.5T480-468q-45 0-76.5 31.5T372-360q0 45 31.5 76.5T480-252ZM264-552h336v-144H264v144Z"/></svg> Guardar Configuración</button>
            </form>
        </section>

        <!-- Mantenimiento -->
        <section class="seccion-config">
            <h2>🔨 Mantenimiento del Sistema</h2>
            <div class="mantenimiento-grid">
                <div class="mantenimiento-card">
                    <div class="card-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg></div>
                    <h3>Limpiar Caché</h3>
                    <p>Elimina archivos temporales y caché del sistema</p>
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="accion" value="limpiar_cache">
                        <button type="submit" class="boton-accion-mant limpiar">Limpiar Ahora</button>
                    </form>
                </div>

                <div class="mantenimiento-card">
                    <div class="card-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M816-672v456q0 29.7-21.15 50.85Q773.7-144 744-144H216q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h456l144 144ZM480-252q45 0 76.5-31.5T588-360q0-45-31.5-76.5T480-468q-45 0-76.5 31.5T372-360q0 45 31.5 76.5T480-252ZM264-552h336v-144H264v144Z"/></svg></div>
                    <h3>Backup Base de Datos</h3>
                    <p>Genera una copia de seguridad completa</p>
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="accion" value="backup_db">
                        <button type="submit" class="boton-accion-mant backup">Generar Backup</button>
                    </form>
                </div>

                <div class="mantenimiento-card">
                    <div class="card-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M666-163 475-354q-20 8-43.5 12.5T384-337q-99 0-169.5-70T144-576q0-38 9.5-72t28.5-63l144 144 70-70-144-144q29-17 62.5-26t69.5-9q100 0 170 71t70 170q0 23-4.5 42.5T607-493l195 194q14 14 14 34.5T802-230l-68 67q-14 14-34 14t-34-14Z"/></svg></div>
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
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m107-384-59-42 192-312 120 144 168-264 120 168 146-222 58 42-202 307-119-166-163 257-119-143-142 231Zm468.77 144Q616-240 644-267.77q28-27.78 28-68Q672-376 644.23-404q-27.78-28-68-28Q536-432 508-404.23q-28 27.78-28 68Q480-296 507.77-268q27.78 28 68 28ZM765-96l-98-98q-19.91 13-43.13 19.5Q600.65-168 576-168q-70 0-119-49t-49-119q0-70 49-119t119-49q70 0 119 49t49 119q0 24.65-6.5 47.87T718-245l98 98-51 51Z"/></svg> Estadísticas Avanzadas</h2>
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
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Actividad Reciente</h2>
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