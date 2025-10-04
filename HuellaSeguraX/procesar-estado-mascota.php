<?php
include_once('config/conexion.php');
session_start();

// Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $id_publicacion = isset($_POST['id_publicacion']) ? (int) $_POST['id_publicacion'] : 0;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    if (empty($id_publicacion) || empty($accion)) {
        header("Location: mascotas-perdidas.php?error=datos_invalidos");
        exit();
    }

    try {
        // Verificar que la publicación pertenezca al usuario
        $consulta_verificar = "SELECT p.id_anuncio, p.id_mascota, m.nombre_mascota 
                               FROM publicaciones p
                               JOIN mascotas m ON p.id_mascota = m.id_mascota
                               WHERE p.id_anuncio = ? AND p.id_usuario = ?";

        $stmt_verificar = $conexion->prepare($consulta_verificar);
        if (!$stmt_verificar) {
            throw new Exception("Error en verificación: " . $conexion->error);
        }

        $stmt_verificar->bind_param("ii", $id_publicacion, $usuario_id);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->get_result();

        if ($resultado->num_rows == 0) {
            throw new Exception("No tienes permiso para modificar esta publicación");
        }

        $publicacion = $resultado->fetch_assoc();
        $nombre_mascota = $publicacion['nombre_mascota'];

        // Procesar según la acción
        switch ($accion) {
            case 'encontrada':
                // Cambiar estado de la publicación a 'encontrado'
                $stmt_actualizar = $conexion->prepare("UPDATE publicaciones SET estado = 'encontrado' WHERE id_anuncio = ?");
                if (!$stmt_actualizar) {
                    throw new Exception("Error al actualizar: " . $conexion->error);
                }

                $stmt_actualizar->bind_param("i", $id_publicacion);
                if (!$stmt_actualizar->execute()) {
                    throw new Exception("Error al marcar como encontrada: " . $stmt_actualizar->error);
                }

                $stmt_actualizar->close();
                header("Location: mascotas-perdidas.php?exito=mascota_encontrada&mascota=" . urlencode($nombre_mascota));
                break;

            case 'eliminar':
                // Iniciar transacción
                mysqli_autocommit($conexion, FALSE);

                // Eliminar primero de publicacion_perdida (por foreign key)
                $stmt_eliminar_perdida = $conexion->prepare("DELETE FROM publicacion_perdida WHERE id_publicacion = ?");
                if (!$stmt_eliminar_perdida) {
                    throw new Exception("Error al eliminar detalle: " . $conexion->error);
                }

                $stmt_eliminar_perdida->bind_param("i", $id_publicacion);
                if (!$stmt_eliminar_perdida->execute()) {
                    throw new Exception("Error al eliminar detalle perdida: " . $stmt_eliminar_perdida->error);
                }

                // Luego eliminar la publicación
                $stmt_eliminar_pub = $conexion->prepare("DELETE FROM publicaciones WHERE id_anuncio = ?");
                if (!$stmt_eliminar_pub) {
                    throw new Exception("Error al eliminar publicación: " . $conexion->error);
                }

                $stmt_eliminar_pub->bind_param("i", $id_publicacion);
                if (!$stmt_eliminar_pub->execute()) {
                    throw new Exception("Error al eliminar publicación: " . $stmt_eliminar_pub->error);
                }

                // Confirmar transacción
                mysqli_commit($conexion);

                $stmt_eliminar_perdida->close();
                $stmt_eliminar_pub->close();

                header("Location: mascotas-perdidas.php?exito=reporte_eliminado&mascota=" . urlencode($nombre_mascota));
                break;

            default:
                throw new Exception("Acción no válida");
        }

        $stmt_verificar->close();
        exit();

    } catch (Exception $e) {
        // Revertir si hubo transacción
        mysqli_rollback($conexion);

        error_log("Error en procesar-estado-mascota: " . $e->getMessage());
        header("Location: mascotas-perdidas.php?error=error_procesar&detalle=" . urlencode($e->getMessage()));
        exit();
    }

} else {
    header("Location: mascotas-perdidas.php");
    exit();
}
?>