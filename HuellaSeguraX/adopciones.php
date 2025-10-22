<?php
include_once('config/conexion.php');
session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires'); // O tu zona horaria

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'] ?? 'demo';

// Obtener mascotas del usuario para el selector
if ($rol_usuario === 'demo') {
    $resultado_mascotas = null; // Demo no tiene mascotas
} else {
    $consulta_mascotas = "SELECT id_mascota, nombre_mascota, tipo FROM mascotas 
                          WHERE id_usuario = $usuario_id AND estado = 'activo' 
                          ORDER BY nombre_mascota ASC";
    $resultado_mascotas = $conexion->query($consulta_mascotas);
}

// Obtener publicaciones de adopción con información más completa
$consulta_adopciones = "SELECT p.*, pa.*, m.*, 
                        u.nombre_usuario, u.telefono_usuario, u.email_usuario, u.foto_usuario,
                        p.fecha as fecha_publicacion
                        FROM publicaciones p 
                        JOIN publicacion_adopcion pa ON p.id_anuncio = pa.id_publicacion
                        JOIN mascotas m ON p.id_mascota = m.id_mascota
                        JOIN usuarios u ON p.id_usuario = u.id_usuario
                        WHERE p.estado = 'activo' 
                        ORDER BY p.fecha DESC";
$resultado_adopciones = $conexion->query($consulta_adopciones);

// Calcular total de adoptados (mascotas con estado 'adoptado')
$consulta_adoptados = "SELECT COUNT(*) as total FROM mascotas 
                       WHERE estado = 'adoptado'";
$resultado_adoptados = $conexion->query($consulta_adoptados);

// Contar estadísticas
$total_disponibles = $resultado_adopciones ? $resultado_adopciones->num_rows : 0;
$total_adoptados = $resultado_adoptados ? $resultado_adoptados->fetch_assoc()['total'] : 0;

// Mensajes de éxito/error
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'solicitud_enviada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "¡Solicitud de adopción de $nombre enviada exitosamente! El propietario se pondrá en contacto contigo.";
            $tipo_mensaje = 'success';
            break;
        case 'publicacion_creada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'tu mascota';
            $mensaje = "¡Publicación de adopción de $nombre creada exitosamente! Esperamos que encuentre un hogar pronto.";
            $tipo_mensaje = 'success';
            break;
        case 'marcada_adoptada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "¡Felicitaciones! $nombre ha sido marcada como adoptada. Esperamos que disfrute de su nuevo hogar.";
            $tipo_mensaje = 'success';
            break;
        case 'adopcion_editada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "Publicación de adopción de $nombre actualizada correctamente.";
            $tipo_mensaje = 'success';
            break;
        case 'adopcion_eliminada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "Publicación de adopción de $nombre eliminada correctamente.";
            $tipo_mensaje = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'adopcion_no_valida':
            $mensaje = 'La publicación de adopción no está disponible.';
            $tipo_mensaje = 'error';
            break;
        case 'propia_mascota':
            $mensaje = 'No puedes solicitar adoptar tu propia mascota.';
            $tipo_mensaje = 'error';
            break;
        case 'solicitud_duplicada':
            $mensaje = 'Ya has solicitado adoptar esta mascota.';
            $tipo_mensaje = 'error';
            break;
        case 'descripcion_requerida':
            $mensaje = 'Debes explicar por qué quieres adoptar esta mascota.';
            $tipo_mensaje = 'error';
            break;
        case 'publicacion_existente':
            $mensaje = 'Esta mascota ya tiene una publicación de adopción activa.';
            $tipo_mensaje = 'error';
            break;
        case 'campos_requeridos':
            $mensaje = 'Por favor completa todos los campos requeridos.';
            $tipo_mensaje = 'error';
            break;
        case 'sin_permisos':
            $mensaje = 'No tienes permisos para realizar esta acción.';
            $tipo_mensaje = 'error';
            break;
        case 'datos_invalidos':
            $mensaje = 'Los datos enviados no son válidos.';
            $tipo_mensaje = 'error';
            break;
        case 'error_actualizar':
            $mensaje = 'Error al actualizar el estado de la adopción.';
            $tipo_mensaje = 'error';
            break;
        case 'error_editar':
            $mensaje = 'Error al editar la publicación de adopción.';
            $tipo_mensaje = 'error';
            break;
        case 'error_eliminar':
            $mensaje = 'Error al eliminar la publicación de adopción.';
            $tipo_mensaje = 'error';
            break;
        case 'accion_invalida':
            $mensaje = 'Acción no válida.';
            $tipo_mensaje = 'error';
            break;
        default:
            $mensaje = 'Ocurrió un error inesperado.';
            $tipo_mensaje = 'error';
            break;
    }
}
function tiempoTranscurrido($fecha)
{
    $ahora = new DateTime();
    $fecha_pub = new DateTime($fecha);
    $diferencia = $ahora->diff($fecha_pub);

    if ($diferencia->y > 0) {
        return $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '');
    } elseif ($diferencia->m > 0) {
        return $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
    } elseif ($diferencia->d > 0) {
        return $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
    } elseif ($diferencia->h > 0) {
        return $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
    } elseif ($diferencia->i > 0) {
        return $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
    } else {
        return 'Ahora mismo';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopciones - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/adopciones.css">
    <link rel="stylesheet" href="css/modal-alerta-demo.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <!-- Header -->
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
        <?php include_once('includes/crear_notificacion.php'); ?>
    </header>
    <!-- Contenido principal -->
    <main class="main-content">
        <?php if (!empty($mensaje)): ?>
                <div class="mensaje-adopcion mensaje-<?php echo $tipo_mensaje; ?>">
                    <span><?php echo $mensaje; ?></span>
                    <button onclick="this.parentElement.remove()" style="
                background: none;
                border: none;
                color: inherit;
                cursor: pointer;
                font-size: 18px;
                padding: 0 5px;
            ">×</button>
                </div>
        <?php endif; ?>

        <!-- Header de adopciones -->
        <section class="adopciones-header">
            <h1 class="titulo-adopciones">Adopción de Mascotas ❤️</h1>
            <p class="subtitulo-adopciones">Dale una segunda oportunidad a una mascota</p>
        </section>

        <!-- Botón crear publicación de adopción -->
        <?php if ($rol_usuario == 'demo'): ?>
            <button class="boton-crear-publicacion" onclick="mostrarModalPublicarAdopcion()">
                ❤️ ¡Publicar en Adopción!
                <small>+ Buscar hogar para tu mascota</small>
            </button>
        <?php else: ?>
            <button class="boton-crear-publicacion" onclick="mostrarFormularioPublicacion()">
                ❤️ ¡Publicar en Adopción!
                <small>+ Buscar hogar para tu mascota</small>
            </button>
        <?php endif; ?>

        <!-- Sección de adopción -->
        <section class="seccion-adopcion">
            <div class="section-header">
                <h3>❤️ Adopción</h3>
            </div>

            <!-- Filtros -->
            <div class="filtros-adopcion">
                <button class="filtro-adopcion activo">❤️ Todos</button>
                <button class="filtro-adopcion">🐕 Perros</button>
                <button class="filtro-adopcion">🐱 Gatos</button>
                <button class="filtro-adopcion">⚙️ Otros</button>
            </div>

            <!-- Estadísticas -->
            <div class="estadisticas-adopcion">
                <div class="estadistica-adopcion">
                    <div class="numero-estadistica-adopcion"><?php echo $total_disponibles; ?></div>
                    <div class="etiqueta-estadistica-adopcion">Disponibles</div>
                </div>
                <div class="estadistica-adopcion">
                    <div class="numero-estadistica-adopcion"><?php echo $total_adoptados; ?></div>
                    <div class="etiqueta-estadistica-adopcion">Adoptados</div>
                </div>
            </div>

            <!-- Mascotas en adopción -->
            <div class="lista-adopciones">
                <?php if ($resultado_adopciones && $resultado_adopciones->num_rows > 0): ?>
                    <?php while ($adopcion = $resultado_adopciones->fetch_assoc()): ?>
                            <div class="tarjeta-reporte">
                                <!-- Badge de adopción -->
                                <div class="etiqueta-estado adopcion-badge">ADOPCIÓN</div>
                                        
                                    <!-- Contenido del reporte -->
                                    <div class="contenido-reporte">
                                        <!-- Header con usuario y tiempo -->
                                        <div class="header-usuario-reporte">
                                            <div class="avatar-usuario-reporte" style="background-image: url('imagenes/<?php echo htmlspecialchars($adopcion['foto_usuario'] ?? 'usuario-default.jpg'); ?>')">
                                </div>
                                <div class="info-usuario-reporte">
                                    <h4 class="nombre-usuario-reporte">
                                        <?php echo htmlspecialchars($adopcion['nombre_usuario']); ?>
                                    </h4>
                                    <p class="tiempo-publicacion-reporte">Hace
                                        <?php echo tiempoTranscurrido($adopcion['fecha_publicacion']); ?>
                                    </p>
                                </div>
                                </div>
                        
                                <!-- Imagen de la mascota -->
                                <img src="imagenes/<?php echo htmlspecialchars($adopcion['foto_mascota']); ?>"
                                    alt="<?php echo htmlspecialchars($adopcion['nombre_mascota']); ?>" class="foto-reporte" onerror="this.src='imagenes/mascota-default.jpg'">
                                            
                                                    <!-- Información de la mascota -->
                                                    <div class=" info-reporte">
                                <h4>
                                    <?php echo htmlspecialchars($adopcion['nombre_mascota']); ?>
                                </h4>
                                <p>
                                    <?php echo ucfirst($adopcion['tipo']); ?> •
                                    <?php echo $adopcion['sexo'] ? ucfirst($adopcion['sexo']) : 'No especificado'; ?>
                                </p>
                                <p><strong>Edad:</strong>
                                    <?php echo $adopcion['edad_mascota']; ?> años</p>
                                                        <p>📅 Publicado hace <?php echo tiempoTranscurrido($adopcion['fecha_publicacion']); ?>
                                </p>
                                <p>📍
                                    <?php echo htmlspecialchars($adopcion['lugar_adopcion']); ?></p>
                                                
                                                        <?php if (!empty($adopcion['condiciones'])): ?>
                                                                <div class="descripcion-condiciones">
                                                                    <strong>Condiciones:</strong> <?php echo nl2br(htmlspecialchars(substr($adopcion['condiciones'], 0, 100))); ?><?php echo strlen($adopcion['condiciones']) > 100 ? '...' : ''; ?>
                                        </div>
                                    <?php endif; ?>
                                    </div>
                                    </div>
                        
                                    <!-- Acciones -->
                                <div class="acciones-reporte">
                                    <?php if ($adopcion['id_usuario'] == $usuario_id && $rol_usuario != 'demo'): ?>
                                        <!-- Botones para el propietario -->
                                        <button class="boton-marcar-adoptada"
                                            onclick="marcarComoAdoptada(<?php echo $adopcion['id_adopcion']; ?>, 
                                                                        '<?php echo addslashes($adopcion['nombre_mascota']); ?>',
                                                                        '<?php echo htmlspecialchars($adopcion['foto_mascota']); ?>')">
                                            ✅ Adoptada
                                        </button>

                                        <button class="boton-editar-reporte"
                                            onclick="editarAdopcion(<?php echo $adopcion['id_adopcion']; ?>, 
                                                                '<?php echo addslashes($adopcion['condiciones']); ?>', 
                                                                '<?php echo addslashes($adopcion['lugar_adopcion']); ?>', 
                                                                '<?php echo addslashes($adopcion['nombre_mascota']); ?>')">
                                            ✏️ Editar
                                        </button>

                                        <button class="boton-eliminar-reporte"
                                            onclick="eliminarAdopcion(<?php echo $adopcion['id_adopcion']; ?>, 
                                                                    '<?php echo addslashes($adopcion['nombre_mascota']); ?>',
                                                                    '<?php echo htmlspecialchars($adopcion['foto_mascota']); ?>')">
                                            🗑️ Eliminar
                                        </button>
                                    <?php elseif ($rol_usuario == 'demo'): ?>
                                        <button class="boton-contactar"
                                            onclick="mostrarModalAlerta('Inicia sesión para solicitar adopciones\n\nRegístrate para poder:\n• Solicitar adoptar mascotas\n• Contactar con los dueños\n• Completar el proceso de adopción')">
                                            ❤️ Me interesa adoptar
                                        </button>
                                    <?php else: ?>
                                        <button class="boton-contactar boton-interesa-adoptar" data-id-adopcion="<?php echo $adopcion['id_adopcion']; ?>"
                                                                data-nombre=" <?php echo htmlspecialchars($adopcion['nombre_mascota'], ENT_QUOTES); ?>">
                                            ❤️ Me interesa adoptar
                                        </button>
                                    <?php endif; ?>
                        </div>
                        
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                        <!-- Sin adopciones disponibles -->
                        <div class="mensaje-vacio" style="text-align: center; padding: 60px 20px; color: #666; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px;">
                            <p style="font-size: 64px; margin: 0;">❤️</p>
                            <h3 style="margin: 20px 0 10px; color: #2c3e50; text-align: center;">No hay mascotas en adopción</h3>
                            <p style="margin: 0; font-size: 16px; text-align: center;">En este momento no hay publicaciones activas de adopción.</p>
                            <p style="margin: 10px 0 0; font-size: 14px; color: #95a5a6; text-align: center;">¡Sé el primero en publicar una mascota para adopción!</p>
                        </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modal para solicitar adopción -->
    <div class="modal-solicitud" id="modalSolicitud">
        <div class="contenido-modal-solicitud">
            <div class="encabezado-modal-solicitud">
                <h3 class="titulo-modal">Solicitar Adopción</h3>
                <button class="boton-cerrar-modal" onclick="cerrarSolicitud()">×</button>
                <p id="nombreMascotaSolicitud" style="color: #666; margin: 8px 0 0 0;"></p>
            </div>
            
            <form class="formulario-modal" id="formularioSolicitud" action="procesar-solicitud-adopcion.php" method="POST">
                <input type="hidden" id="idAdopcionSolicitud" name="id_adopcion">
                
                <div class="grupo-input">
                    <label class="etiqueta-input requerido">¿Por qué quieres adoptar esta mascota?</label>
                    <textarea class="textarea-form" name="descripcion" placeholder="Describe tu experiencia con mascotas, tu hogar, disponibilidad de tiempo, etc." required></textarea>
                </div>

                <div class="grupo-input">
                    <label class="etiqueta-input">Información adicional</label>
                    <textarea class="textarea-form" name="informacion_adicional" placeholder="Cualquier información adicional que quieras compartir (opcional)"></textarea>
                </div>

                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarSolicitud()">Cancelar</button>
                    <button type="submit" class="boton-enviar">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para crear publicación de adopción -->
    <div class="modal-crear-publicacion" id="modalCrearPublicacion">
        <div class="contenido-modal-publicacion">
            <div class="encabezado-modal-publicacion">
                <h3 class="titulo-modal">Publicar en Adopción</h3>
                <button class="boton-cerrar-modal" onclick="cerrarPublicacion()">×</button>
                <p style="color: #666; margin: 8px 0 0 0;">Busca un hogar lleno de amor para tu mascota</p>
            </div>
            
            <form class="formulario-modal" id="formularioPublicacion" action="procesar-publicacion-adopcion.php" method="POST">
                <div class="grupo-input">
                    <label class="etiqueta-input requerido">¿Cuál mascota quieres dar en adopción?</label>
                    <select class="select-form" name="id_mascota" required>
                        <option value="">Seleccionar mascota</option>
                        <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                                <?php
                                mysqli_data_seek($resultado_mascotas, 0); // Resetear el pointer del resultado
                                while ($mascota = $resultado_mascotas->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $mascota['id_mascota']; ?>">
                                            <?php echo htmlspecialchars($mascota['nombre_mascota']); ?>
                                            (<?php echo ucfirst($mascota['tipo']); ?>)
                                        </option>
                                <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if ($rol_usuario == 'demo'): ?>
                    <div class="sin-mascotas-mensaje">
                        <p>⚠️ Necesitas una cuenta para publicar adopciones</p>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="boton-agregar-mascota" onclick="window.location.href='login.php'">
                                🔑 Iniciar Sesión
                            </button>
                            <button type="button" class="boton-agregar-mascota" onclick="window.location.href='registro.php'">
                                📝 Registrarse
                            </button>
                        </div>
                    </div>
                <?php elseif (!$resultado_mascotas || $resultado_mascotas->num_rows == 0): ?>
                    <div class="sin-mascotas-mensaje">
                        <p>⚠️ Primero debes registrar tus mascotas</p>
                        <button type="button" class="boton-agregar-mascota"
                            onclick="window.location.href='mis-mascotas.php'">
                            + Agregar Mascota
                        </button>
                    </div>
                <?php endif; ?>

                <div class="grupo-input">
                    <label class="etiqueta-input requerido">Condiciones de adopción</label>
                    <textarea class="textarea-form" name="condiciones" placeholder="Ejemplo: Hogar con jardín, experiencia previa con perros, disponibilidad de tiempo, etc." required></textarea>
                </div>

                <div class="grupo-input">
                    <label class="etiqueta-input requerido">Lugar de entrega</label>
                    <input type="text" class="input-form" name="lugar_adopcion" placeholder="Ciudad, barrio o zona específica" required>
                </div>

                <div class="grupo-input">
                    <label class="etiqueta-input">Motivo de la adopción</label>
                    <textarea class="textarea-form" name="motivo_adopcion" placeholder="¿Por qué necesitas encontrar un hogar para tu mascota? (opcional)"></textarea>
                </div>

                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarPublicacion()">Cancelar</button>
                    <button type="submit" class="boton-enviar">Publicar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Marcar como Adoptada -->
    <div class="modal-overlay" id="modalAdoptada">
        <div class="modal-container">
            <div class="modal-header">
                <button class="modal-close" onclick="cerrarModalAdoptada()">×</button>
                <div class="modal-icon success">✓</div>
                <h3 class="modal-title">Marcar como Adoptada</h3>
                <p class="modal-subtitle">Esta acción cambiará el estado de tu mascota</p>
            </div>
            
            <div class="modal-body">
                <div class="modal-mascota-info" id="mascotaInfoAdoptada">
                    <!-- Se llenará dinámicamente -->
                </div>
                
                <div class="modal-warning-box">
                    <p><strong>⚠️ Ten en cuenta:</strong><br>
                    Al marcar como adoptada, la publicación se cerrará y la mascota cambiará de estado en el sistema.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="cerrarModalAdoptada()">
                    Cancelar
                </button>
                <button class="modal-btn modal-btn-success" onclick="confirmarAdoptada()">
                    ✓ Confirmar Adopción
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Adopción -->
    <div class="modal-overlay" id="modalEditar">
        <div class="modal-container">
            <div class="modal-header">
                <button class="modal-close" onclick="cerrarModalEditar()">×</button>
                <div class="modal-icon warning">✏️</div>
                <h3 class="modal-title">Editar Publicación</h3>
                <p class="modal-subtitle" id="subtituloEditar">Modifica los detalles de la adopción</p>
            </div>
            
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="idAdopcionEditar">
                    
                    <div class="form-group">
                        <label class="form-label">Condiciones de adopción</label>
                        <textarea class="form-textarea" id="condicionesEditar" placeholder="Ejemplo: Hogar con jardín, experiencia previa, etc." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lugar de entrega</label>
                        <input type="text" class="form-input" id="lugarEditar" placeholder="Ciudad, barrio o zona específica" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="cerrarModalEditar()">
                    Cancelar
                </button>
                <button class="modal-btn modal-btn-confirm" onclick="confirmarEditar()">
                    ✏️ Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Adopción -->
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal-container">
            <div class="modal-header">
                <button class="modal-close" onclick="cerrarModalEliminar()">×</button>
                <div class="modal-icon danger">🗑️</div>
                <h3 class="modal-title">Eliminar Publicación</h3>
                <p class="modal-subtitle">Esta acción no se puede deshacer</p>
            </div>
            
            <div class="modal-body">
                <div class="modal-mascota-info" id="mascotaInfoEliminar">
                    <!-- Se llenará dinámicamente -->
                </div>
                
                <div class="modal-danger-box">
                    <p><strong>🚨 Advertencia:</strong><br>
                    Al eliminar esta publicación, se perderán todas las solicitudes de adopción asociadas y no podrás recuperarla.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="cerrarModalEliminar()">
                    Cancelar
                </button>
                <button class="modal-btn modal-btn-danger" onclick="confirmarEliminar()">
                    🗑️ Eliminar Publicación
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de alerta para usuarios demo -->
    <div class="modal-alerta-demo" id="modalAlertaDemo">
        <div class="contenido-modal-alerta">
            <div class="encabezado-modal-alerta">
                <h3 class="titulo-modal-alerta">⚠️ Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
            </div>
            
            <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo">🔐</div>
                <p id="mensajeAlertaDemo">Para acceder a esta función necesitas iniciar sesión o registrarte.</p>
                
                <div class="detalles-alerta">
                    <h4>Con una cuenta podrás:</h4>
                    <ul id="listaBeneficiosAlerta">
                        <li>• Gestionar citas veterinarias</li>
                        <li>• Registrar consultas médicas</li>
                        <li>• Llevar historial de salud</li>
                    </ul>
                </div>
            </div>

            <div class="botones-modal-alerta">
                <button type="button" class="boton-cancelar-alerta" onclick="cerrarModalAlerta()">Más tarde</button>
                <button type="button" class="boton-login-alerta" onclick="irALogin()">🔑 Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()">📝 Registrarse</button>
            </div>
        </div>
    </div>
    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    
    <script src="js/scripts.js"></script>
    <script src="js/notificaciones.js"></script>
    <script src="js/adopciones.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
</body>
</html>