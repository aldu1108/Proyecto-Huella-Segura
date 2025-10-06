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

// Calcular total de adoptados (solicitudes con estado 'aprobada' o 'adoptado')
$consulta_adoptados = "SELECT COUNT(*) as total FROM solicitud_adopcion 
                       WHERE estado = 'aprobada' OR estado = 'adoptado'";
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
                        <div class="tarjeta-adopcion-nueva">
                            <!-- Badge de adopción -->
                            <div class="badge-adopcion-tag">ADOPCIÓN</div>
                            
                            <!-- Imagen de la mascota -->
                            <div class="imagen-mascota-container">
                                <img src="imagenes/<?php echo htmlspecialchars($adopcion['foto_mascota']); ?>" 
                                    alt="<?php echo htmlspecialchars($adopcion['nombre_mascota']); ?>" 
                                    class="imagen-mascota-adopcion"
                                    onerror="this.src='imagenes/mascota-default.jpg'">
                            </div>
                            
                            <!-- Contenido principal -->
                            <div class="contenido-tarjeta-adopcion">
                                <!-- Header con usuario y tiempo -->
                                <div class="post-header">
                                    <div class="user-avatar" style="background-image: url('imagenes/<?php echo htmlspecialchars($adopcion['foto_usuario'] ?? 'usuario-default.jpg'); ?>')"></div>
                                    <div class="user-info">
                                        <h4><?php echo htmlspecialchars($adopcion['nombre_usuario']); ?></h4>
                                        <p>Hace <?php echo tiempoTranscurrido($adopcion['fecha_publicacion']); ?></p>
                                    </div>
                                </div>

                                <!-- Nombre y detalles básicos -->
                                <div class="header-mascota">
                                    <h3 class="nombre-mascota"><?php echo htmlspecialchars($adopcion['nombre_mascota']); ?></h3>
                                    <div class="detalles-mascota">
                                        <span class="detalle-item"><?php echo ucfirst($adopcion['tipo']); ?></span>
                                        <span class="separador">•</span>
                                        <span class="detalle-item"><?php echo $adopcion['edad_mascota']; ?> años</span>
                                        <span class="separador">•</span>
                                        <span class="detalle-item"><?php echo $adopcion['sexo'] ? ($adopcion['sexo'] == 'macho' ? '♂' : '♀') : ''; ?></span>
                                    </div>
                                </div>
                                
                                <!-- Descripción -->
                                <p class="descripcion-mascota">
                                    <?php echo nl2br(htmlspecialchars(substr($adopcion['descripcion'], 0, 120))); ?>...
                                </p>
                                
                                <!-- Información de ubicación y fecha -->
                                <div class="info-adicional">
                                    <div class="info-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span><?php echo htmlspecialchars($adopcion['lugar_adopcion']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <span>Hace <?php echo tiempoTranscurrido($adopcion['fecha_publicacion']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span><?php echo htmlspecialchars($adopcion['nombre_usuario']); ?></span>
                                    </div>
                                </div>
                                
                                <!-- Estados (Vacunado, Esterilizado) -->
                                <div class="estados-mascota">
                                    <span class="estado-tag vacunado">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        Vacunado
                                    </span>
                                    <span class="estado-tag esterilizado">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                        Disponible
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Botón de acción -->
                            <div class="accion-container">
                                <?php if ($adopcion['id_usuario'] == $usuario_id && $rol_usuario != 'demo'): ?>
                                    <!-- Botones para el propietario -->
                                    <div class="botones-propietario-grid">
                                        <button class="boton-accion-secundario"
                                            onclick="editarAdopcion(<?php echo $adopcion['id_adopcion']; ?>, '<?php echo addslashes($adopcion['condiciones']); ?>', '<?php echo addslashes($adopcion['lugar_adopcion']); ?>', '<?php echo addslashes($adopcion['nombre_mascota']); ?>')">
                                            ✏️ Editar
                                        </button>
                                        <button class="boton-accion-eliminar"
                                            onclick="eliminarAdopcion(<?php echo $adopcion['id_adopcion']; ?>, '<?php echo addslashes($adopcion['nombre_mascota']); ?>')">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                <?php elseif ($rol_usuario == 'demo'): ?>
                                    <button class="boton-adoptar-principal"
                                        onclick="mostrarModalAlerta('Inicia sesión para solicitar adopciones\n\nRegístrate para poder:\n• Solicitar adoptar mascotas\n• Contactar con los dueños\n• Completar el proceso de adopción')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        Me interesa adoptar
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <button class="boton-adoptar-principal boton-interesa-adoptar" 
                                        data-id-adopcion="<?php echo $adopcion['id_adopcion']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($adopcion['nombre_mascota'], ENT_QUOTES); ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        Me interesa adoptar
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                        <!-- Mascotas de ejemplo si no hay datos -->
                        <div class="tarjeta-adopcion">
                            <div class="badge-adopcion">ADOPCIÓN</div>
                        
                            <div class="contenido-adopcion">
                                <img src="imagenes/perro.jpg" alt="Carlos" class="foto-mascota-adopcion">
                            
                                <div class="info-adopcion">
                                    <!-- Header con usuario -->
                                    <div class="post-header">
                                        <div class="user-avatar" style="background-image: url('imagenes/<?php echo htmlspecialchars($adopcion['foto_usuario'] ?? 'usuario-default.jpg'); ?>')"></div>
                                        <div class="user-info">
                                            <h4><?php echo htmlspecialchars($adopcion['nombre_usuario']); ?></h4>
                                            <p>Hace <?php echo tiempoTranscurrido($adopcion['fecha_publicacion']); ?></p>
                                        </div>
                                    </div>

                                <div class="encabezado-adopcion">
                                    <div class="info-basica-adopcion">
                                        <h3><?php echo htmlspecialchars($adopcion['nombre_mascota']); ?></h3>
                                        <p class="detalles-basicos">
                                            <?php echo ucfirst($adopcion['tipo']); ?> • 
                                            <?php echo $adopcion['edad_mascota']; ?> años • 
                                            <?php echo $adopcion['sexo'] ? ($adopcion['sexo'] == 'macho' ? '♂' : '♀') : ''; ?>
                                        </p>
                                    </div>
                                </div>

                                <p class="descripcion-adopcion"><?php echo nl2br(htmlspecialchars(substr($adopcion['descripcion'], 0, 150))); ?>...</p>

                                <div class="meta-adopcion">
                                    <span>📍 <?php echo htmlspecialchars($adopcion['lugar_adopcion']); ?></span>
                                    <span>📅 <?php echo date('d/m/Y', strtotime($adopcion['fecha_publicacion'])); ?></span>
                                </div>

                                <div class="estados-adopcion">
                                    <span class="estado-badge estado-vacunado">✅ Disponible</span>
                                </div>
                            </div>
                        
                            <?php if ($rol_usuario == 'demo'): ?>
                                <button class="boton-interesa-adoptar" onclick="mostrarModalAlerta('Inicia sesión para solicitar adopciones\n\nEste es un ejemplo de mascota en adopción.')">
                                    ❤️ Me interesa adoptar →
                                </button>
                            <?php else: ?>
                                <button class="boton-interesa-adoptar" data-id-adopcion="0" data-alert="Este es un ejemplo. Registra mascotas para ver funcionalidad completa.">
                                    ❤️ Me interesa adoptar →
                                </button>
                            <?php endif; ?>
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
    <script src="js/adopciones.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
</body>
</html>