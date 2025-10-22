<?php
// includes/crear_notificacion.php
// Función helper para crear notificaciones en todo el sistema

function crearNotificacion($conexion, $datos)
{
    /*
    Parámetros esperados en $datos:
    - id_usuario_destino (int): ID del usuario que recibirá la notificación
    - id_usuario_origen (int, opcional): ID del usuario que generó la acción
    - tipo (string): Tipo de notificación
    - titulo (string): Título corto
    - mensaje (string): Mensaje descriptivo
    - url_relacionada (string, opcional): URL a la que redirigir al hacer click
    */

    // Validaciones básicas
    if (!isset($datos['id_usuario_destino']) || !isset($datos['tipo']) || !isset($datos['mensaje'])) {
        return false;
    }

    // No notificar a usuarios demo
    $stmt_check = $conexion->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
    $stmt_check->bind_param("i", $datos['id_usuario_destino']);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result();
    if ($resultado) {
        $usuario = $resultado->fetch_assoc();
        if ($usuario && $usuario['rol'] === 'demo') {
            return false;
        }
    }

    $id_usuario_origen = $datos['id_usuario_origen'] ?? null;
    $titulo = $datos['titulo'] ?? '';
    $url_relacionada = $datos['url_relacionada'] ?? null;

    try {
        $stmt = $conexion->prepare("INSERT INTO notificaciones 
                                   (id_usuario_destino, id_usuario_origen, tipo, titulo, mensaje, url_relacionada) 
                                   VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "iissss",
            $datos['id_usuario_destino'],
            $id_usuario_origen,
            $datos['tipo'],
            $titulo,
            $datos['mensaje'],
            $url_relacionada
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;

    } catch (Exception $e) {
        error_log("Error al crear notificación: " . $e->getMessage());
        return false;
    }
}

// Función para notificar sobre solicitud de adopción
function notificarSolicitudAdopcion($conexion, $id_propietario, $id_solicitante, $nombre_mascota, $nombre_solicitante)
{
    return crearNotificacion($conexion, [
        'id_usuario_destino' => $id_propietario,
        'id_usuario_origen' => $id_solicitante,
        'tipo' => 'solicitud_adopcion',
        'titulo' => 'Nueva solicitud de adopción',
        'mensaje' => "<strong>$nombre_solicitante</strong> quiere adoptar a <strong>$nombre_mascota</strong>",
        'url_relacionada' => 'mis-solicitudes-adopcion.php'
    ]);
}

// Función para notificar cuando alguien comenta en un post
function notificarComentarioPost($conexion, $id_autor_post, $id_comentador, $nombre_comentador)
{
    return crearNotificacion($conexion, [
        'id_usuario_destino' => $id_autor_post,
        'id_usuario_origen' => $id_comentador,
        'tipo' => 'comentario_post',
        'titulo' => 'Nuevo comentario',
        'mensaje' => "<strong>$nombre_comentador</strong> comentó en tu publicación",
        'url_relacionada' => 'comunidad.php'
    ]);
}

// Función para notificar cuando alguien da like a un post
function notificarLikePost($conexion, $id_autor_post, $id_quien_da_like, $nombre_quien_da_like)
{
    return crearNotificacion($conexion, [
        'id_usuario_destino' => $id_autor_post,
        'id_usuario_origen' => $id_quien_da_like,
        'tipo' => 'like_post',
        'titulo' => 'Le gustó tu publicación',
        'mensaje' => "A <strong>$nombre_quien_da_like</strong> le gustó tu publicación",
        'url_relacionada' => 'comunidad.php'
    ]);
}

// Función para notificar cita veterinaria aceptada
function notificarCitaAceptada($conexion, $id_usuario, $nombre_veterinario, $fecha_cita)
{
    return crearNotificacion($conexion, [
        'id_usuario_destino' => $id_usuario,
        'tipo' => 'cita_aceptada',
        'titulo' => 'Cita aceptada',
        'mensaje' => "<strong>Dr. $nombre_veterinario</strong> aceptó tu cita para el $fecha_cita",
        'url_relacionada' => 'veterinaria.php'
    ]);
}

// Función para notificar cita veterinaria rechazada
function notificarCitaRechazada($conexion, $id_usuario, $nombre_veterinario)
{
    return crearNotificacion($conexion, [
        'id_usuario_destino' => $id_usuario,
        'tipo' => 'cita_rechazada',
        'titulo' => 'Cita rechazada',
        'mensaje' => "<strong>Dr. $nombre_veterinario</strong> no pudo aceptar tu cita. Por favor agenda otra",
        'url_relacionada' => 'veterinaria.php'
    ]);
}

// Función para notificar mascota encontrada
function notificarMascotaEncontrada($conexion, $id_propietario, $nombre_mascota)
{
    return crearNotificacion($conexion, [
        'id_usuario_destino' => $id_propietario,
        'tipo' => 'mascota_encontrada',
        'titulo' => '¡Excelente noticia!',
        'mensaje' => "¡<strong>$nombre_mascota</strong> ha sido encontrada! 🎉",
        'url_relacionada' => 'mascotas-perdidas.php'
    ]);
}
?>