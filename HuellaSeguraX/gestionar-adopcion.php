<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    header("Location: adopciones.php?error=sin_permisos");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_adopcion = intval($_POST['id_adopcion'] ?? 0);

    if ($id_adopcion <= 0) {
        header("Location: adopciones.php?error=datos_invalidos");
        exit();
    }

    // Verificar que la adopción pertenece al usuario
    $sql_verificar = "SELECT pa.*, p.id_usuario, p.id_mascota, m.nombre_mascota 
                      FROM publicacion_adopcion pa 
                      JOIN publicaciones p ON pa.id_publicacion = p.id_anuncio 
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      WHERE pa.id_adopcion = ? AND p.id_usuario = ?";
    $stmt = $conexion->prepare($sql_verificar);
    $stmt->bind_param("ii", $id_adopcion, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: adopciones.php?error=sin_permisos");
        exit();
    }

    $adopcion = $resultado->fetch_assoc();
    $id_publicacion = $adopcion['id_publicacion'];
    $id_mascota = $adopcion['id_mascota'];
    $nombre_mascota = $adopcion['nombre_mascota'];

    switch ($accion) {
        case 'adoptada':
            // Cambiar estado de la publicación a 'inactivo'
            $sql_publicacion = "UPDATE publicaciones SET estado = 'inactivo' WHERE id_anuncio = ?";
            $stmt = $conexion->prepare($sql_publicacion);
            $stmt->bind_param("i", $id_publicacion);

            // Cambiar estado de la mascota a 'adoptado'
            $sql_mascota = "UPDATE mascotas SET estado = 'adoptado' WHERE id_mascota = ?";
            $stmt2 = $conexion->prepare($sql_mascota);
            $stmt2->bind_param("i", $id_mascota);

            if ($stmt->execute() && $stmt2->execute()) {
                // Marcar la solicitud de adopción como 'aprobada' (esto incrementa el contador)
                $sql_solicitudes = "UPDATE solicitud_adopcion SET estado = 'aprobada' 
                                   WHERE id_adopcion = ? AND estado = 'pendiente' LIMIT 1";
                $stmt3 = $conexion->prepare($sql_solicitudes);
                $stmt3->bind_param("i", $id_adopcion);
                $stmt3->execute();

                // Rechazar las demás solicitudes pendientes
                $sql_rechazar = "UPDATE solicitud_adopcion SET estado = 'rechazada' 
                                WHERE id_adopcion = ? AND estado = 'pendiente'";
                $stmt4 = $conexion->prepare($sql_rechazar);
                $stmt4->bind_param("i", $id_adopcion);
                $stmt4->execute();

                header("Location: adopciones.php?exito=marcada_adoptada&mascota=" . urlencode($nombre_mascota));
            } else {
                header("Location: adopciones.php?error=error_actualizar");
            }
            break;

        case 'editar':
            $condiciones = $_POST['condiciones'] ?? '';
            $lugar_adopcion = $_POST['lugar_adopcion'] ?? '';

            if (empty($condiciones) || empty($lugar_adopcion)) {
                header("Location: adopciones.php?error=campos_requeridos");
                exit();
            }

            $sql_editar = "UPDATE publicacion_adopcion 
                          SET condiciones = ?, lugar_adopcion = ? 
                          WHERE id_adopcion = ?";
            $stmt = $conexion->prepare($sql_editar);
            $stmt->bind_param("ssi", $condiciones, $lugar_adopcion, $id_adopcion);

            if ($stmt->execute()) {
                header("Location: adopciones.php?exito=adopcion_editada&mascota=" . urlencode($nombre_mascota));
            } else {
                header("Location: adopciones.php?error=error_editar");
            }
            break;

        case 'eliminar':
            // Eliminar solicitudes de adopción asociadas
            $sql_solicitudes = "DELETE FROM solicitud_adopcion WHERE id_adopcion = ?";
            $stmt = $conexion->prepare($sql_solicitudes);
            $stmt->bind_param("i", $id_adopcion);
            $stmt->execute();

            // Eliminar la publicación de adopción
            $sql_adopcion = "DELETE FROM publicacion_adopcion WHERE id_adopcion = ?";
            $stmt = $conexion->prepare($sql_adopcion);
            $stmt->bind_param("i", $id_adopcion);

            // Eliminar la publicación
            $sql_publicacion = "DELETE FROM publicaciones WHERE id_anuncio = ?";
            $stmt2 = $conexion->prepare($sql_publicacion);
            $stmt2->bind_param("i", $id_publicacion);

            if ($stmt->execute() && $stmt2->execute()) {
                header("Location: adopciones.php?exito=adopcion_eliminada&mascota=" . urlencode($nombre_mascota));
            } else {
                header("Location: adopciones.php?error=error_eliminar");
            }
            break;

        default:
            header("Location: adopciones.php?error=accion_invalida");
            break;
    }
} else {
    header("Location: adopciones.php");
}

$conexion->close();
?>