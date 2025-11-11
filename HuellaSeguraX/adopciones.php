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
            <h1 class="titulo-adopciones">Adopción de Mascotas</h1>
            <p class="subtitulo-adopciones">Dale una segunda oportunidad a una mascota</p>
        </section>

        <!-- Botón crear publicación de adopción -->
        <?php if ($rol_usuario == 'demo'): ?>
            <button class="boton-crear-publicacion" onclick="mostrarModalPublicarAdopcion()">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> ¡Publicar en Adopción!
                <small>+ Buscar hogar para tu mascota</small>
            </button>
        <?php else: ?>
            <button class="boton-crear-publicacion" onclick="mostrarFormularioPublicacion()">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> ¡Publicar en Adopción!
                <small>+ Buscar hogar para tu mascota</small>
            </button>
        <?php endif; ?>

        <!-- Sección de adopción -->
        <section class="seccion-adopcion">
            <div class="section-header">
                <h3><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> Adopción</h3>
            </div>

            <!-- Filtros -->
            <div class="filtros-adopcion">
                <button class="filtro-adopcion activo"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> Todos</button>
                <button class="filtro-adopcion"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F9DB78"><path d="M384-96q-55 0-93.5-38.5T252-228q0-14 2.5-19.5t.5-7.5q-2-2-8 .5t-19 2.5q-55 0-93.5-38.5T96-384q0-55 38.5-93.5T228-516q23 0 45 8t40 24l163-163q-16-17-24-39.5t-8-45.5q0-55 38.5-93.5T576-864q55 0 93.5 38.5T708-732q0 14-2.5 19.5t-.5 7.5q2 2 7.5-.5T732-708q55 0 93.5 38.5T864-576q0 55-38.5 93.5T732-444q-23 0-45-8.5T646-477L483-314q16 19 24.5 41t8.5 45q0 55-38.5 93.5T384-96Z"/></svg> Perros</button>
                <button class="filtro-adopcion"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M120-384q-30 0-51-21t-21-51v-336q0-30 21-51t51-21h720q30 0 51 21t21 51v336q0 30-21 51t-51 21H120Zm25 144-4-72 671-35 4 72-671 35Zm-1 120v-72h672v72H144Zm276-384q75 0 143-28.5T672-624q2 44 38 70t82 26v-192q-46 0-82 26t-38 70q-42-61-109.5-90.5T420-744q-75 0-144 28.5T168-624q38 64 107.5 92T420-504Z"/></svg> Gatos</button>
                <button class="filtro-adopcion"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M180-475q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm180-160q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm240 0q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm180 160q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM266-75q-45 0-75.5-34.5T160-191q0-52 35.5-91t70.5-77q29-31 50-67.5t50-68.5q22-26 51-43t63-17q34 0 63 16t51 42q28 32 49.5 69t50.5 69q35 38 70.5 77t35.5 91q0 47-30.5 81.5T694-75q-54 0-107-9t-107-9q-54 0-107 9t-107 9Z"/></svg> Otros</button>
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
                                                        <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Publicado hace <?php echo tiempoTranscurrido($adopcion['fecha_publicacion']); ?>
                                </p>
                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#75FB4C"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg> Adoptada
                                        </button>

                                        <button class="boton-editar-reporte"
                                            onclick="editarAdopcion(<?php echo $adopcion['id_adopcion']; ?>, 
                                                                '<?php echo addslashes($adopcion['condiciones']); ?>', 
                                                                '<?php echo addslashes($adopcion['lugar_adopcion']); ?>', 
                                                                '<?php echo addslashes($adopcion['nombre_mascota']); ?>')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M120-120v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm584-528 56-56-56-56-56 56 56 56Z"/></svg> Editar
                                        </button>

                                        <button class="boton-eliminar-reporte"
                                            onclick="eliminarAdopcion(<?php echo $adopcion['id_adopcion']; ?>, 
                                                                    '<?php echo addslashes($adopcion['nombre_mascota']); ?>',
                                                                    '<?php echo htmlspecialchars($adopcion['foto_mascota']); ?>')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z"/></svg> Eliminar
                                        </button>
                                    <?php elseif ($rol_usuario == 'demo'): ?>
                                        <button class="boton-contactar"
                                            onclick="mostrarModalAlerta('Inicia sesión para solicitar adopciones\n\nRegístrate para poder:\n• Solicitar adoptar mascotas\n• Contactar con los dueños\n• Completar el proceso de adopción')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> Me interesa adoptar
                                        </button>
                                    <?php else: ?>
                                        <button class="boton-contactar boton-interesa-adoptar" data-id-adopcion="<?php echo $adopcion['id_adopcion']; ?>"
                                                                data-nombre=" <?php echo htmlspecialchars($adopcion['nombre_mascota'], ENT_QUOTES); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> Me interesa adoptar
                                        </button>
                                    <?php endif; ?>
                        </div>
                        
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                        <!-- Sin adopciones disponibles -->
                        <div class="mensaje-vacio" style="text-align: center; padding: 60px 20px; color: #666; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px;">
                            <p style="font-size: 64px; margin: 0;"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg></p>
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
                        <p><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Necesitas una cuenta para publicar adopciones</p>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="boton-agregar-mascota" onclick="window.location.href='login.php'">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M280-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 120q-100 0-170-70T40-480q0-100 70-170t170-70q81 0 141.5 46T506-560h335l79 79-140 160-100-79-80 80-80-80h-14q-25 72-87 116t-139 44Z"/></svg> Iniciar Sesión
                            </button>
                            <button type="button" class="boton-agregar-mascota" onclick="window.location.href='registro.php'">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520h200L520-800v200Z"/></svg> Registrarse
                            </button>
                        </div>
                    </div>
                <?php elseif (!$resultado_mascotas || $resultado_mascotas->num_rows == 0): ?>
                    <div class="sin-mascotas-mensaje">
                        <p><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Primero debes registrar tus mascotas</p>
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
                    <p><strong><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Ten en cuenta:</strong><br>
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
                <div class="modal-icon warning"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFF55"><path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/></svg></div>
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
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFF55"><path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/></svg> Guardar Cambios
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
                    <p><strong><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M160-200h640v-80H160v80Zm160-240h80v-120q0-33 23.5-56.5T480-640v-80q-66 0-113 47t-47 113v120Zm160 160Zm-200-80h400v-200q0-83-58.5-141.5T480-760q-83 0-141.5 58.5T280-560v200ZM160-120q-33 0-56.5-23.5T80-200v-80q0-33 23.5-56.5T160-360h40v-200q0-117 81.5-198.5T480-840q117 0 198.5 81.5T760-560v200h40q33 0 56.5 23.5T880-280v80q0 33-23.5 56.5T800-120H160Zm320-240Z"/></svg> Advertencia:</strong><br>
                    Al eliminar esta publicación, se perderán todas las solicitudes de adopción asociadas y no podrás recuperarla.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="cerrarModalEliminar()">
                    Cancelar
                </button>
                <button class="modal-btn modal-btn-danger" onclick="confirmarEliminar()">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg> Eliminar Publicación
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de alerta para usuarios demo -->
    <div class="modal-alerta-demo" id="modalAlertaDemo">
        <div class="contenido-modal-alerta">
            <div class="encabezado-modal-alerta">
                <h3 class="titulo-modal-alerta"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
            </div>
            
            <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm0-80h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80ZM240-160v-400 400Z"/></svg></div>
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
                <button type="button" class="boton-login-alerta" onclick="irALogin()"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M280-400q-33 0-56.5-23.5T200-480q0-33 23.5-56.5T280-560q33 0 56.5 23.5T360-480q0 33-23.5 56.5T280-400Zm0 160q-100 0-170-70T40-480q0-100 70-170t170-70q67 0 121.5 33t86.5 87h352l120 120-180 180-80-60-80 60-85-60h-47q-32 54-86.5 87T280-240Zm0-80q56 0 98.5-34t56.5-86h125l58 41 82-61 71 55 75-75-40-40H435q-14-52-56.5-86T280-640q-66 0-113 47t-47 113q0 66 47 113t113 47Z"/></svg> Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/></svg> Registrarse</button>
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