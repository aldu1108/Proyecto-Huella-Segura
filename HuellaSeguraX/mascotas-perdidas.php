<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'] ?? 'demo';

// Obtener estadísticas
$consulta_perdidas = "SELECT COUNT(*) as total FROM publicacion_perdida pp 
                      JOIN publicaciones p ON pp.id_publicacion = p.id_anuncio 
                      WHERE p.estado = 'activo'";
$resultado_perdidas = $conexion->query($consulta_perdidas);
$total_perdidas = $resultado_perdidas ? $resultado_perdidas->fetch_assoc()['total'] : 0;

// Contar encontradas (mascotas con estado 'encontrado')
$consulta_encontradas = "SELECT COUNT(*) as total FROM publicaciones p 
                         WHERE p.estado = 'encontrado'";
$resultado_encontradas = $conexion->query($consulta_encontradas);
$total_encontradas = $resultado_encontradas ? $resultado_encontradas->fetch_assoc()['total'] : 0;

// Contar con recompensa
$consulta_recompensa = "SELECT COUNT(*) as total FROM publicacion_perdida pp 
                        JOIN publicaciones p ON pp.id_publicacion = p.id_anuncio 
                        WHERE p.estado = 'activo' AND pp.recompensa > 0";
$resultado_recompensa = $conexion->query($consulta_recompensa);
$total_recompensa = $resultado_recompensa ? $resultado_recompensa->fetch_assoc()['total'] : 0;

// Obtener mascotas del usuario para el selector
if ($rol_usuario === 'demo') {
    $resultado_mascotas = null; // Demo no tiene mascotas
} else {
    $consulta_mascotas = "SELECT id_mascota, nombre_mascota, tipo FROM mascotas 
                          WHERE id_usuario = $usuario_id AND estado = 'activo' 
                          ORDER BY nombre_mascota ASC";
    $resultado_mascotas = $conexion->query($consulta_mascotas);
}

// Obtener reportes activos con información más completa
$consulta_reportes = "SELECT p.*, pp.*, m.nombre_mascota, m.tipo, m.foto_mascota, m.sexo, m.edad_mascota,
                             u.nombre_usuario, u.telefono_usuario, u.email_usuario, u.foto_usuario,
                             p.fecha as fecha_publicacion
                      FROM publicaciones p 
                      JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      JOIN usuarios u ON p.id_usuario = u.id_usuario
                      WHERE p.estado = 'activo' 
                      ORDER BY p.fecha DESC";
$resultado_reportes = $conexion->query($consulta_reportes);

// Verificar si se está editando un reporte
$modo_edicion = false;
$reporte_editar = null;

if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $id_editar = (int) $_GET['editar'];

    // Obtener datos del reporte a editar
    $consulta_editar = "SELECT p.*, pp.*, m.nombre_mascota, m.tipo, m.id_mascota
                        FROM publicaciones p 
                        JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                        JOIN mascotas m ON p.id_mascota = m.id_mascota
                        WHERE p.id_anuncio = ? AND p.id_usuario = ?";

    $stmt_editar = $conexion->prepare($consulta_editar);
    if ($stmt_editar) {
        $stmt_editar->bind_param("ii", $id_editar, $usuario_id);
        $stmt_editar->execute();
        $resultado_editar = $stmt_editar->get_result();

        if ($resultado_editar->num_rows > 0) {
            $modo_edicion = true;
            $reporte_editar = $resultado_editar->fetch_assoc();

            // Extraer descripción detallada
            $descripcion_completa = $reporte_editar['descripcion'];
            $reporte_editar['descripcion_detalle'] = '';

            if (preg_match('/Detalles:\s*(.+?)(?:\n\n|$)/s', $descripcion_completa, $matches)) {
                $reporte_editar['descripcion_detalle'] = trim($matches[1]);
            }

            // Formatear fecha y hora
            $reporte_editar['fecha_perdida_formato'] = date('Y-m-d', strtotime($reporte_editar['fecha_perdida']));
            $reporte_editar['hora_perdida_formato'] = date('H:i', strtotime($reporte_editar['fecha_perdida']));
        }
        $stmt_editar->close();
    }
}

// Mensajes de éxito/error
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'reporte_creado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "¡Reporte de $nombre creado exitosamente! La comunidad será notificada.";
            $tipo_mensaje = 'success';
            break;
        case 'reporte_actualizado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"#75FB4C\"><path d=\"m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z\"/></svg> Cambios guardados correctamente. El reporte de $nombre ha sido actualizado.";
            $tipo_mensaje = 'success';
            break;
        case 'mascota_encontrada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "¡Excelente noticia! Has marcado a $nombre como encontrada. El reporte ha sido cerrado.";
            $tipo_mensaje = 'success';
            break;
        case 'reporte_eliminado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "El reporte de $nombre ha sido eliminado correctamente.";
            $tipo_mensaje = 'success';
            break;
    }
}

// Agregar en la sección de errores:

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'datos_incompletos':
            $mensaje = 'Por favor completa todos los campos requeridos.';
            $tipo_mensaje = 'error';
            break;
        case 'mascota_no_valida':
            $mensaje = 'La mascota seleccionada no es válida.';
            $tipo_mensaje = 'error';
            break;
        case 'fecha_invalida':
            $mensaje = 'La fecha no puede ser futura.';
            $tipo_mensaje = 'error';
            break;
        case 'error_crear_reporte':
            $mensaje = 'Error al crear el reporte. Intenta de nuevo.';
            $tipo_mensaje = 'error';
            break;
        case 'reporte_duplicado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'esta mascota';
            $mensaje = "Ya existe un reporte activo de {$nombre}. No puedes crear múltiples reportes de la misma mascota.";
            $tipo_mensaje = 'error';
            break;
        case 'datos_invalidos':
            $mensaje = 'Datos inválidos. Por favor intenta de nuevo.';
            $tipo_mensaje = 'error';
            break;
        case 'error_procesar':
            $mensaje = 'Error al procesar la acción. Intenta de nuevo.';
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
    <title>Mascotas Perdidas - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/mascotas-perdidas.css">
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
                <div class="mensaje-perdida mensaje-<?php echo $tipo_mensaje; ?>">
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

        <!-- Header de mascotas perdidas -->
        <section class="mascotas-perdidas-header">
            <h1 class="titulo-mascotas-perdidas">Mascotas Perdidas</h1>
            <p class="subtitulo-mascotas-perdidas">Ayuda a reunir familias con sus mascotas</p>
        </section>

        <!-- Estadísticas -->
        <div class="estadisticas-perdidas">
            <div class="estadistica-item">
                <span class="numero-estadistica perdidas"><?php echo $total_perdidas; ?></span>
                <span>Perdidas</span>
            </div>
            <div class="estadistica-item">
                <span class="numero-estadistica encontradas"><?php echo $total_encontradas; ?></span>
                <span>Encontradas</span>
            </div>
            <div class="estadistica-item">
                <span class="numero-estadistica recompensa"><?php echo $total_recompensa; ?></span>
                <span>Con recompensa</span>
            </div>
        </div>

        <!-- Botón reportar mascota perdida -->
        <?php if ($rol_usuario == 'demo'): ?>
            <button class="boton-reporte-perdida" onclick="mostrarModalAlerta('Inicia sesión para reportar mascotas perdidas\n\nPara usar esta función necesitas:\n• Tener una cuenta registrada\n• Registrar tus mascotas')">
                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> ¡Reportar Mascota Perdida!
                <small>+ Crear reporte de búsqueda</small>
            </button>
        <?php else: ?>
            <button class="boton-reporte-perdida" onclick="mostrarFormularioReporte()">
                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> ¡Reportar Mascota Perdida!
                <small>+ Crear reporte de búsqueda</small>
            </button>
        <?php endif; ?>

        <!-- Alerta actúa rápido -->
        <div class="alerta-actua-rapido">
            <h4><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Actúa rápido:</h4>
            <ul>
                <li>Las primeras 24 horas son cruciales</li>
                <li>Comparte en redes sociales automáticamente</li>
                <li>Alerta a la comunidad local</li>
            </ul>
        </div>

        <!-- Reportes Activos -->
        <section class="seccion-reportes-activos">
            <h3>Reportes Activos (<?php echo $total_perdidas; ?>)</h3>

            <div class="contenedor-reportes">
                <?php if ($resultado_reportes && $resultado_reportes->num_rows > 0): ?>
                        <?php while ($reporte = $resultado_reportes->fetch_assoc()): ?>
                                <?php
                                // Calcular días perdido
                                $fecha_perdida = new DateTime($reporte['fecha_perdida']);
                                $fecha_actual = new DateTime();
                                $dias_perdido = $fecha_actual->diff($fecha_perdida)->days;
                                ?>
                                <div class="tarjeta-reporte">
                                    <div class="etiqueta-estado">PERDIDO</div>
                                    <?php if ($reporte['recompensa'] > 0): ?>
                                            <div class="etiqueta-recompensa">Recompensa: €<?php echo number_format($reporte['recompensa'], 0); ?></div>
                                    <?php endif; ?>
                            
                                    <div class="contenido-reporte">
                                        <!-- Nuevo header con info del usuario -->
                                        <div class="header-usuario-reporte">
                                            <div class="avatar-usuario-reporte" style="background-image: url('imagenes/<?php echo htmlspecialchars($reporte['foto_usuario'] ?? 'usuario-default.jpg'); ?>')"></div>
                                            <div class="info-usuario-reporte">
                                                <h4 class="nombre-usuario-reporte"><?php echo htmlspecialchars($reporte['nombre_usuario']); ?></h4>
                                                <p class="tiempo-publicacion-reporte">Hace <?php echo tiempoTranscurrido($reporte['fecha_publicacion']); ?></p>
                                            </div>
                                        </div>

                                        <img src="<?php 
                                        if ($reporte['foto_mascota'] === 'mascota-default.jpg') {
                                            echo 'imagenes/mascota-default.jpg';
                                        } else {
                                            echo file_exists('uploads/mascotas/' . $reporte['foto_mascota']) ? 'uploads/mascotas/' . $reporte['foto_mascota'] : 'imagenes/mascota-default.jpg';
                                        }
                                        ?>" 
                                        alt="<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>" 
                                        class="foto-reporte">
                                        
                                        <div class="info-reporte">
                                            <h4><?php echo htmlspecialchars($reporte['nombre_mascota']); ?></h4>
                                            <p><?php echo ucfirst($reporte['tipo']); ?> • <?php echo $reporte['sexo'] ? ucfirst($reporte['sexo']) : 'No especificado'; ?></p>
                                            <p><strong>Edad:</strong> <?php echo $reporte['edad_mascota']; ?> años</p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Perdido hace <?php echo $dias_perdido; ?> día<?php echo $dias_perdido != 1 ? 's' : ''; ?> (<?php echo date('d/m/Y', strtotime($reporte['fecha_perdida'])); ?>)</p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg> <?php echo htmlspecialchars($reporte['ultima_ubicacion']); ?></p>
                                        </div>
                                    </div>
                            
                                    <div class="acciones-reporte">
                                        <?php if ($reporte['id_usuario'] == $usuario_id): ?>
                                            <!-- Botones para el propietario -->
                                            <button class="boton-encontrado"
                                                onclick="marcarComoEncontrada(<?php echo $reporte['id_anuncio']; ?>, '<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>')"
                                                title="Marcar como encontrada">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Encontrada
                                            </button>
                                            <button class="boton-editar-reporte" onclick="editarReporte(<?php echo $reporte['id_anuncio']; ?>)"
                                                title="Editar reporte">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="M144-144v-153l498-498q11-11 24-16t27-5q14 0 27 5t24 16l51 51q11 11 16 24t5 27q0 14-5 27t-16 24L297-144H144Zm549-498 51-51-51-51-51 51 51 51Z"/></svg> Editar
                                            </button>
                                            <button class="boton-eliminar-reporte"
                                                onclick="eliminarReporte(<?php echo $reporte['id_anuncio']; ?>, '<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>')"
                                                title="Eliminar reporte">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar
                                            </button>
                                        <?php else: ?>
                                            <!-- Botones para otros usuarios -->
                                            <?php if ($rol_usuario == 'demo'): ?>
                                                <button class="boton-contactar"
                                                    onclick="mostrarModalAlerta('Inicia sesión para contactar propietarios\n\nRegístrate para poder:\n• Contactar a dueños de mascotas perdidas\n• Reportar avistamientos\n• Ayudar a reunir familias')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M744-481q0-109-77.5-186.5T480-745v-72q70 0 131 26.5t106.5 72Q763-673 789.5-612T816-481h-72Zm-144 0q0-50-35-85t-85-35v-72q80 0 136 56t56 136h-72Zm163 336q-121-9-229.5-59.5T339-341q-86-86-136-194.5T144-765q-2-21 12.5-36.5T192-817h136q17 0 29.5 10.5T374-780l24 107q2 13-1.5 25T385-628l-97 98q20 38 46 73t58 66q30 30 64 55.5t72 45.5l99-96q8-8 20-11.5t25-1.5l107 23q17 5 27 17.5t10 29.5v136q0 21-16 35.5T763-145Z"/></svg> Contactar
                                                </button>
                                            <?php else: ?>
                                                <button class="boton-contactar"
                                                    onclick="contactarPropietario('<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>', '<?php echo htmlspecialchars($reporte['telefono_usuario']); ?>')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M744-481q0-109-77.5-186.5T480-745v-72q70 0 131 26.5t106.5 72Q763-673 789.5-612T816-481h-72Zm-144 0q0-50-35-85t-85-35v-72q80 0 136 56t56 136h-72Zm163 336q-121-9-229.5-59.5T339-341q-86-86-136-194.5T144-765q-2-21 12.5-36.5T192-817h136q17 0 29.5 10.5T374-780l24 107q2 13-1.5 25T385-628l-97 98q20 38 46 73t58 66q30 30 64 55.5t72 45.5l99-96q8-8 20-11.5t25-1.5l107 23q17 5 27 17.5t10 29.5v136q0 21-16 35.5T763-145Z"/></svg> Contactar
                                                </button>
                                            <?php endif; ?>
                                            <button class="boton-compartir-reporte"
                                                onclick="compartirReporte('<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>')"
                                                title="Compartir"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M648-96q-50 0-85-35t-35-85q0-9 4-29L295-390q-16 14-36.05 22-20.04 8-42.95 8-50 0-85-35t-35-85q0-50 35-85t85-35q23 0 43 8t36 22l237-145q-2-7-3-13.81-1-6.81-1-15.19 0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-23 0-43-8t-36-22L332-509q2 7 3 13.81 1 6.81 1 15.19 0 8.38-1 15.19-1 6.81-3 13.81l237 145q16-14 36.05-22 20.04-8 42.95-8 50 0 85 35t35 85q0 50-35 85t-85 35Z"/></svg></button>
                                        <?php endif; ?>
                                    </div>
                            
                                    <div class="mensaje-ayuda">
                                        ¿Has visto a <?php echo htmlspecialchars($reporte['nombre_mascota']); ?>? Tu ayuda puede ser crucial para reunir a esta familia.
                                    </div>
                                </div>
                        <?php endwhile; ?>
                <?php else: ?>
                        <div class="sin-reportes">
                            <p>No hay reportes activos en este momento.</p>
                            <p>¡Esperemos que todas las mascotas estén seguras en casa!</p>
                        </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

                <!-- Modal para reportar/editar mascota perdida -->
                <div class="modal-reporte" id="modalReporte" <?php echo $modo_edicion ? 'style="display: flex;"' : ''; ?>>
                    <div class="contenido-modal-reporte">
                        <form class="formulario-reporte" id="formularioReporte" 
                            action="procesar-mascota-perdida.php" 
                            method="POST">
                            
                            <?php if ($modo_edicion): ?>
                                <input type="hidden" name="id_publicacion" value="<?php echo $reporte_editar['id_anuncio']; ?>">
                                <input type="hidden" name="id_mascota" value="<?php echo $reporte_editar['id_mascota']; ?>">
                            <?php endif; ?>
                            
                            <!-- Paso 1: Seleccionar Mascota -->
                            <div class="paso-formulario" id="paso1" style="display: <?php echo $modo_edicion ? 'none' : 'block'; ?>;">
                                <div class="encabezado-modal-reporte">
                                    <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                                    <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                                    <div class="progreso-pasos">
                                        <div class="paso-progreso activo"></div>
                                        <div class="paso-progreso"></div>
                                        <div class="paso-progreso"></div>
                                    </div>
                                    <p class="subtitulo-paso">Paso 1 de 3</p>
                                </div>

                                <div class="formulario-reporte">
                                    <div class="seccion-formulario">
                                        <h4 class="titulo-seccion-form">Seleccionar Mascota</h4>

                                        <div class="grupo-input campo-completo">
                                            <label class="etiqueta-input requerido">¿Cuál mascota se perdió?</label>
                                            <select class="select-form" name="id_mascota" <?php echo !$modo_edicion ? 'required' : ''; ?>>
                                                <option value="">Seleccionar mascota</option>
                                                <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                                                    <?php 
                                                    mysqli_data_seek($resultado_mascotas, 0); // Reset pointer
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
                                                <p><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Necesitas una cuenta para reportar mascotas</p>
                                                <div style="display: flex; gap: 10px;">
                                                    <button type="button" class="boton-agregar-mascota" onclick="window.location.href='login.php'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Iniciar Sesión
                                                    </button>
                                                    <button type="button" class="boton-agregar-mascota" onclick="window.location.href='registro.php'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Registrarse
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
                                    </div>

                                    <div class="botones-formulario">
                                        <button type="button" class="boton-siguiente" onclick="siguientePaso(2)">Siguiente</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 2: Información del Incidente -->
                            <div class="paso-formulario" id="paso2" style="display: <?php echo $modo_edicion ? 'block' : 'none'; ?>;">
                                <div class="encabezado-modal-reporte">
                                    <h3 class="titulo-modal-reporte"><?php echo $modo_edicion ? 'Editar Reporte' : 'Reportar Mascota Perdida'; ?></h3>
                                    <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                                    <?php if (!$modo_edicion): ?>
                                    <div class="progreso-pasos">
                                        <div class="paso-progreso activo"></div>
                                        <div class="paso-progreso activo"></div>
                                        <div class="paso-progreso"></div>
                                    </div>
                                    <p class="subtitulo-paso">Paso 2 de 3</p>
                                    <?php endif; ?>
                                </div>

                                <div class="formulario-reporte">
                                    <?php if ($modo_edicion): ?>
                                    <div class="seccion-formulario">
                                        <div class="info-mascota-editar">
                                            <p><strong>Mascota:</strong> <?php echo htmlspecialchars($reporte_editar['nombre_mascota']); ?> (<?php echo ucfirst($reporte_editar['tipo']); ?>)</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="seccion-formulario">
                                        <h4 class="titulo-seccion-form">¿Cuándo y dónde se perdió?</h4>

                                        <div class="grupo-campos">
                                            <div class="grupo-input">
                                                <label class="etiqueta-input requerido">Fecha en que se perdió</label>
                                                <input type="date" class="input-form" name="fecha_perdida" required
                                                    max="<?php echo date('Y-m-d'); ?>"
                                                    value="<?php echo $modo_edicion ? $reporte_editar['fecha_perdida_formato'] : ''; ?>">
                                            </div>
                                            <div class="grupo-input">
                                                <label class="etiqueta-input">Hora aproximada</label>
                                                <input type="time" class="input-form" name="hora_perdida"
                                                    value="<?php echo $modo_edicion ? $reporte_editar['hora_perdida_formato'] : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="grupo-input campo-completo">
                                            <label class="etiqueta-input requerido">Última ubicación conocida</label>
                                            <div class="campo-ubicacion">
                                                <input type="text" class="input-form" placeholder="Dirección o punto de referencia"
                                                    name="ultima_ubicacion" required
                                                    value="<?php echo $modo_edicion ? htmlspecialchars($reporte_editar['ultima_ubicacion']) : ''; ?>">
                                                <button type="button" class="boton-gps" onclick="obtenerUbicacion()">GPS</button>
                                            </div>
                                        </div>

                                        <div class="grupo-input campo-completo">
                                            <label class="etiqueta-input">¿Cómo se perdió?</label>
                                            <textarea class="textarea-form"
                                                placeholder="Describe las circunstancias: se escapó del jardín, se asustó con fuegos artificiales, etc."
                                                name="descripcion"><?php echo $modo_edicion ? htmlspecialchars($reporte_editar['descripcion_detalle']) : ''; ?></textarea>
                                        </div>

                                        <div class="checkbox-recompensa">
                                            <input type="checkbox" id="checkboxRecompensa" class="input-checkbox"
                                                name="ofrecer_recompensa" onchange="toggleRecompensa()"
                                                <?php echo ($modo_edicion && $reporte_editar['recompensa'] > 0) ? 'checked' : ''; ?>>
                                            <label for="checkboxRecompensa" class="etiqueta-checkbox"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="M444-144v-80q-51-11-87.5-46T305-357l74-30q8 36 40.5 64.5T487-294q39 0 64-20t25-52q0-30-22.5-50T474-456q-78-28-114-61.5T324-604q0-50 32.5-86t87.5-47v-79h72v79q72 12 96.5 55t25.5 45l-70 29q-8-26-32-43t-53-17q-35 0-58 18t-23 44q0 26 25 44.5t93 41.5q70 23 102 60t32 94q0 57-37 96t-101 49v77h-72Z"/></svg> Ofrecer recompensa</label>
                                        </div>

                                        <div class="grupo-input campo-completo" id="campoRecompensa" 
                                            style="display: <?php echo ($modo_edicion && $reporte_editar['recompensa'] > 0) ? 'block' : 'none'; ?>;">
                                            <label class="etiqueta-input">Monto de la recompensa (€)</label>
                                            <input type="number" class="input-form" name="recompensa" min="0"
                                                placeholder="Ejemplo: 100"
                                                value="<?php echo ($modo_edicion && $reporte_editar['recompensa'] > 0) ? $reporte_editar['recompensa'] : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="botones-formulario">
                                        <?php if ($modo_edicion): ?>
                                            <button type="button" class="boton-anterior" onclick="window.location.href='mascotas-perdidas.php'">Cancelar</button>
                                            <button type="submit" class="boton-crear-reporte"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M816-672v456q0 29.7-21.15 50.85Q773.7-144 744-144H216q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h456l144 144ZM480-252q45 0 76.5-31.5T588-360q0-45-31.5-76.5T480-468q-45 0-76.5 31.5T372-360q0 45 31.5 76.5T480-252ZM264-552h336v-144H264v144Z"/></svg> Guardar Cambios</button>
                                        <?php else: ?>
                                            <button type="button" class="boton-anterior" onclick="anteriorPaso(1)">Anterior</button>
                                            <button type="button" class="boton-siguiente" onclick="siguientePaso(3)">Siguiente</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 3: Confirmar y Enviar (solo para crear nuevo) -->
                            <?php if (!$modo_edicion): ?>
                            <div class="paso-formulario" id="paso3" style="display: none;">
                                <div class="encabezado-modal-reporte">
                                    <h3 class="titulo-modal-reporte">Confirmar Reporte</h3>
                                    <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                                    <div class="progreso-pasos">
                                        <div class="paso-progreso activo"></div>
                                        <div class="paso-progreso activo"></div>
                                        <div class="paso-progreso activo"></div>
                                    </div>
                                    <p class="subtitulo-paso">Paso 3 de 3</p>
                                </div>

                                <div class="formulario-reporte">
                                    <div class="seccion-formulario">
                                        <h4 class="titulo-seccion-form">Resumen del Reporte</h4>
                                        <div id="resumenReporte">
                                            <!-- Se llenará dinámicamente -->
                                        </div>

                                        <div class="aviso-importante">
                                            <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M720-444v-72h144v72H720Zm41 276-118-82 42-59 118 82-42 59Zm-77-483-41-59 118-82 41 59-118 82ZM192-192v-192h-24q-30 0-51-21t-21-51v-48q0-30 21-51t51-21h139l221-132v456L313-384h-25v192h-96Zm384-171v-234q23 22 35.5 53t12.5 64q0 33-12.5 64T576-363Z"/></svg> Qué haremos después:</h5>
                                            <ul>
                                                <li><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Publicaremos tu reporte en la comunidad</li>
                                                <li><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M765-144 526-383q-30 22-65.79 34.5-35.79 12.5-76.18 12.5Q284-336 214-406t-70-170q0-100 70-170t170-70q100 0 170 70t70 170.03q0 40.39-12.5 76.18Q599-464 577-434l239 239-51 51ZM384-408q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Z"/></svg> Activaremos búsqueda en la zona</li>
                                                <li><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M744-481q0-109-77.5-186.5T480-745v-72q70 0 131 26.5t106.5 72Q763-673 789.5-612T816-481h-72Zm-144 0q0-50-35-85t-85-35v-72q80 0 136 56t56 136h-72Zm163 336q-121-9-229.5-59.5T339-341q-86-86-136-194.5T144-765q-2-21 12.5-36.5T192-817h136q17 0 29.5 10.5T374-780l24 107q2 13-1.5 25T385-628l-97 98q20 38 46 73t58 66q30 30 64 55.5t72 45.5l99-96q8-8 20-11.5t25-1.5l107 23q17 5 27 17.5t10 29.5v136q0 21-16 35.5T763-145Z"/></svg> Te contactaremos si hay pistas</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="botones-formulario">
                                        <button type="button" class="boton-anterior" onclick="anteriorPaso(2)">Anterior</button>
                                        <button type="submit" class="boton-crear-reporte"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M336-456h72v-96q0-29.7 21.15-50.85Q450.3-624 480-624v-72q-60 0-102 42t-42 102v96ZM168-144q-29.7 0-50.85-21.19Q96-186.37 96-216.12v-72.13Q96-318 117.15-339T168-360h48v-192q0-110.31 76.78-187.16 76.78-76.84 187-76.84T667-739.16q77 76.85 77 187.16v192h48q29.7 0 50.85 21.19Q864-317.63 864-287.88v72.13Q864-186 842.85-165T792-144H168Z"/></svg> Crear Reporte</button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Paso 2: Información del Incidente -->
                <div class="paso-formulario" id="paso2" style="display: none;">
                    <div class="encabezado-modal-reporte">
                        <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                        <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                        <div class="progreso-pasos">
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso"></div>
                        </div>
                        <p class="subtitulo-paso">Paso 2 de 3</p>
                    </div>

                    <div class="formulario-reporte">
                        <div class="seccion-formulario">
                            <h4 class="titulo-seccion-form">¿Cuándo y dónde se perdió?</h4>

                            <div class="grupo-campos">
                                <div class="grupo-input">
                                    <label class="etiqueta-input requerido">Fecha en que se perdió</label>
                                    <input type="date" class="input-form" name="fecha_perdida" required
                                        max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="grupo-input">
                                    <label class="etiqueta-input">Hora aproximada</label>
                                    <input type="time" class="input-form" name="hora_perdida">
                                </div>
                            </div>

                            <div class="grupo-input campo-completo">
                                <label class="etiqueta-input requerido">Última ubicación conocida</label>
                                <div class="campo-ubicacion">
                                    <input type="text" class="input-form" placeholder="<svg xmlns='http://www.w3.org/2000/svg' height='20px' viewBox='0 -960 960 960' width='20px' fill='#EA3323'><path d='M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z'/></svg> Dirección o punto de referencia"
                                        name="ultima_ubicacion" required>
                                    <button type="button" class="boton-gps" onclick="obtenerUbicacion()">GPS</button>
                                </div>
                            </div>

                            <div class="grupo-input campo-completo">
                                <label class="etiqueta-input">¿Cómo se perdió?</label>
                                <textarea class="textarea-form"
                                    placeholder="Describe las circunstancias: se escapó del jardín, se asustó con fuegos artificiales, etc."
                                    name="descripcion"></textarea>
                            </div>

                            <div class="checkbox-recompensa">
                                <input type="checkbox" id="checkboxRecompensa" class="input-checkbox"
                                    name="ofrecer_recompensa" onchange="toggleRecompensa()">
                                <label for="checkboxRecompensa" class="etiqueta-checkbox"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="M444-144v-80q-51-11-87.5-46T305-357l74-30q8 36 40.5 64.5T487-294q39 0 64-20t25-52q0-30-22.5-50T474-456q-78-28-114-61.5T324-604q0-50 32.5-86t87.5-47v-79h72v79q72 12 96.5 55t25.5 45l-70 29q-8-26-32-43t-53-17q-35 0-58 18t-23 44q0 26 25 44.5t93 41.5q70 23 102 60t32 94q0 57-37 96t-101 49v77h-72Z"/></svg> Ofrecer recompensa</label>
                            </div>

                            <div class="grupo-input campo-completo" id="campoRecompensa" style="display: none;">
                                <label class="etiqueta-input">Monto de la recompensa (€)</label>
                                <input type="number" class="input-form" name="recompensa" min="0"
                                    placeholder="Ejemplo: 100">
                            </div>
                        </div>

                        <div class="botones-formulario">
                            <button type="button" class="boton-anterior" onclick="anteriorPaso(1)">Anterior</button>
                            <button type="button" class="boton-siguiente" onclick="siguientePaso(3)">Siguiente</button>
                        </div>
                    </div>
                </div>

                <!-- Paso 3: Confirmar y Enviar -->
                <div class="paso-formulario" id="paso3" style="display: none;">
                    <div class="encabezado-modal-reporte">
                        <h3 class="titulo-modal-reporte">Confirmar Reporte</h3>
                        <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                        <div class="progreso-pasos">
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso activo"></div>
                        </div>
                        <p class="subtitulo-paso">Paso 3 de 3</p>
                    </div>

                    <div class="formulario-reporte">
                        <div class="seccion-formulario">
                            <h4 class="titulo-seccion-form">Resumen del Reporte</h4>
                            <div id="resumenReporte">
                                <!-- Se llenará dinámicamente -->
                            </div>

                          <div class="aviso-importante">
                                            <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M720-444v-72h144v72H720Zm41 276-118-82 42-59 118 82-42 59Zm-77-483-41-59 118-82 41 59-118 82ZM192-192v-192h-24q-30 0-51-21t-21-51v-48q0-30 21-51t51-21h139l221-132v456L313-384h-25v192h-96Zm384-171v-234q23 22 35.5 53t12.5 64q0 33-12.5 64T576-363Z"/></svg> Qué haremos después:</h5>
                                            <ul>
                                                <li><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Publicaremos tu reporte en la comunidad</li>
                                                <li><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M765-144 526-383q-30 22-65.79 34.5-35.79 12.5-76.18 12.5Q284-336 214-406t-70-170q0-100 70-170t170-70q100 0 170 70t70 170.03q0 40.39-12.5 76.18Q599-464 577-434l239 239-51 51ZM384-408q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Z"/></svg> Activaremos búsqueda en la zona</li>
                                                <li><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M744-481q0-109-77.5-186.5T480-745v-72q70 0 131 26.5t106.5 72Q763-673 789.5-612T816-481h-72Zm-144 0q0-50-35-85t-85-35v-72q80 0 136 56t56 136h-72Zm163 336q-121-9-229.5-59.5T339-341q-86-86-136-194.5T144-765q-2-21 12.5-36.5T192-817h136q17 0 29.5 10.5T374-780l24 107q2 13-1.5 25T385-628l-97 98q20 38 46 73t58 66q30 30 64 55.5t72 45.5l99-96q8-8 20-11.5t25-1.5l107 23q17 5 27 17.5t10 29.5v136q0 21-16 35.5T763-145Z"/></svg> Te contactaremos si hay pistas</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="botones-formulario">
                                        <button type="button" class="boton-anterior" onclick="anteriorPaso(2)">Anterior</button>
                                        <button type="submit" class="boton-crear-reporte"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M336-456h72v-96q0-29.7 21.15-50.85Q450.3-624 480-624v-72q-60 0-102 42t-42 102v96ZM168-144q-29.7 0-50.85-21.19Q96-186.37 96-216.12v-72.13Q96-318 117.15-339T168-360h48v-192q0-110.31 76.78-187.16 76.78-76.84 187-76.84T667-739.16q77 76.85 77 187.16v192h48q29.7 0 50.85 21.19Q864-317.63 864-287.88v72.13Q864-186 842.85-165T792-144H168Z"/></svg> Crear Reporte</button>
                                    </div>
                                </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Marcar como Encontrada -->
    <div class="modal-overlay-perdidas" id="modalEncontrada">
        <div class="modal-container-perdidas">
            <div class="modal-header-perdidas">
                <button class="modal-close-perdidas" onclick="cerrarModalEncontrada()">×</button>
                <div class="modal-icon-perdidas success"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#8B7DBE"><path d="m243-145 63-266L96-590l276-24 108-251 108 252 276 23-210 179 63 266-237-141-237 141Zm430-527 22-89-71-59 94-8 36-84 37 84 93 8-71 59 21 89-80-47-81 47Z"/></svg></div>
                <h3 class="modal-title-perdidas">¡Excelente Noticia!</h3>
                <p class="modal-subtitle-perdidas">Marcar mascota como encontrada</p>
            </div>
            
            <div class="modal-body-perdidas">
                <div class="modal-mascota-info-perdidas" id="mascotaInfoEncontrada">
                    <!-- Se llenará dinámicamente -->
                </div>
                
                <div class="modal-success-box">
                    <p><strong> Al confirmar:</strong></p>
                    <ul>
                        <li>El reporte se cerrará automáticamente</li>
                        <li>La comunidad será notificada de la buena noticia</li>
                        <li>El estado de la mascota se actualizará</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer-perdidas">
                <button class="modal-btn-perdidas modal-btn-cancel-perdidas" onclick="cerrarModalEncontrada()">
                    Cancelar
                </button>
                <button class="modal-btn-perdidas modal-btn-success-perdidas" onclick="confirmarEncontrada()">
                     Confirmar - ¡Está en Casa!
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Reporte -->
    <div class="modal-overlay-perdidas" id="modalEditarPerdida">
        <div class="modal-container-perdidas">
            <div class="modal-header-perdidas">
                <button class="modal-close-perdidas" onclick="cerrarModalEditarPerdida()">×</button>
                <div class="modal-icon-perdidas warning"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="M144-144v-153l498-498q11-11 24-16t27-5q14 0 27 5t24 16l51 51q11 11 16 24t5 27q0 14-5 27t-16 24L297-144H144Zm549-498 51-51-51-51-51 51 51 51Z"/></svg></div>
                <h3 class="modal-title-perdidas">Editar Reporte</h3>
                <p class="modal-subtitle-perdidas" id="subtituloEditarPerdida">Actualiza la información del reporte</p>
            </div>
            
            <div class="modal-body-perdidas">
                <form id="formEditarPerdida">
                    <input type="hidden" id="idPublicacionEditar">
                    
                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">Fecha en que se perdió</label>
                        <input type="date" class="form-input-perdidas" id="fechaPerdidaEditar" required>
                    </div>

                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">Hora aproximada</label>
                        <input type="time" class="form-input-perdidas" id="horaPerdidaEditar">
                    </div>

                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">Última ubicación conocida</label>
                        <div class="campo-ubicacion-perdidas">
                            <input type="text" class="form-input-perdidas" id="ubicacionEditar" placeholder="<svg xmlns='http://www.w3.org/2000/svg' height='20px' viewBox='0 -960 960 960' width='20px' fill='#EA3323'><path d='M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z'/></svg> Dirección o punto de referencia" required>
                            <button type="button" class="boton-gps-modal" onclick="obtenerUbicacionModal()"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg></button>
                        </div>
                    </div>

                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">¿Cómo se perdió?</label>
                        <textarea class="form-textarea-perdidas" id="descripcionEditar" placeholder="Describe las circunstancias"></textarea>
                    </div>

                    <div class="checkbox-recompensa-modal">
                        <input type="checkbox" id="checkboxRecompensaEditar" onchange="toggleRecompensaModal()">
                        <label for="checkboxRecompensaEditar"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="M444-144v-80q-51-11-87.5-46T305-357l74-30q8 36 40.5 64.5T487-294q39 0 64-20t25-52q0-30-22.5-50T474-456q-78-28-114-61.5T324-604q0-50 32.5-86t87.5-47v-79h72v79q72 12 96.5 55t25.5 45l-70 29q-8-26-32-43t-53-17q-35 0-58 18t-23 44q0 26 25 44.5t93 41.5q70 23 102 60t32 94q0 57-37 96t-101 49v77h-72Z"/></svg> Ofrecer recompensa</label>
                    </div>

                    <div class="form-group-perdidas" id="campoRecompensaEditar" style="display: none;">
                        <label class="form-label-perdidas">Monto de la recompensa (€)</label>
                        <input type="number" class="form-input-perdidas" id="recompensaEditar" min="0" placeholder="Ejemplo: 100">
                    </div>
                </form>
            </div>

            <div class="modal-footer-perdidas">
                <button class="modal-btn-perdidas modal-btn-cancel-perdidas" onclick="cerrarModalEditarPerdida()">
                    Cancelar
                </button>
                <button class="modal-btn-perdidas modal-btn-confirm-perdidas" onclick="confirmarEditarPerdida()">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="M144-144v-153l498-498q11-11 24-16t27-5q14 0 27 5t24 16l51 51q11 11 16 24t5 27q0 14-5 27t-16 24L297-144H144Zm549-498 51-51-51-51-51 51 51 51Z"/></svg> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Reporte -->
    <div class="modal-overlay-perdidas" id="modalEliminarPerdida">
        <div class="modal-container-perdidas">
            <div class="modal-header-perdidas">
                <button class="modal-close-perdidas" onclick="cerrarModalEliminarPerdida()">×</button>
                <div class="modal-icon-perdidas danger"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg></div>
                <h3 class="modal-title-perdidas">Eliminar Reporte</h3>
                <p class="modal-subtitle-perdidas">Esta acción no se puede deshacer</p>
            </div>
            
            <div class="modal-body-perdidas">
                <div class="modal-mascota-info-perdidas" id="mascotaInfoEliminarPerdida">
                    <!-- Se llenará dinámicamente -->
                </div>
                
                <div class="modal-danger-box-perdidas">
                    <p><strong><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Advertencia:</strong></p>
                    <ul>
                        <li>El reporte será eliminado permanentemente</li>
                        <li>La comunidad dejará de recibir alertas</li>
                        <li>No podrás recuperar esta información</li>
                        <li>Si encontraste a tu mascota, usa "Encontrada" en su lugar</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer-perdidas">
                <button class="modal-btn-perdidas modal-btn-cancel-perdidas" onclick="cerrarModalEliminarPerdida()">
                    Cancelar
                </button>
                <button class="modal-btn-perdidas modal-btn-danger-perdidas" onclick="confirmarEliminarPerdida()">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Sí, Eliminar Reporte
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
                <div class="icono-alerta-demo"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg></div>
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

    <script src="js/notificaciones.js"></script>
    <script src="js/mascotas-perdidas.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
    <script>
        // Inicializar variable global para modo edición ANTES de cargar otros scripts
        <?php if ($modo_edicion): ?>
                window.modoEdicion = true;
            <?php else: ?>
                window.modoEdicion = false;
            <?php endif; ?>

            // Abrir automáticamente el modal si estamos en modo edición
            document.addEventListener('DOMContentLoaded', function () {
                <?php if ($modo_edicion): ?>
                    document.body.style.overflow = 'hidden';
                    pasoActual = 2; // Ir directamente al paso 2
                <?php endif; ?>
            });
    </script>
</body>
</html>