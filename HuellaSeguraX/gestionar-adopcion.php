<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

if ($accion === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_adopcion = (int) $_POST['id_adopcion'];
        $condiciones = trim($_POST['condiciones']);
        $lugar_adopcion = trim($_POST['lugar_adopcion']);

        if (empty($condiciones) || empty($lugar_adopcion)) {
            throw new Exception("Todos los campos son requeridos");
        }

        mysqli_autocommit($conexion, FALSE);

        // Obtener info de la mascota para actualizar descripción completa
        $stmt_info = $conexion->prepare("SELECT m.nombre_mascota, m.tipo, m.sexo, m.edad_mascota, p.id_anuncio
                                         FROM publicacion_adopcion pa
                                         JOIN publicaciones p ON pa.id_publicacion = p.id_anuncio
                                         JOIN mascotas m ON p.id_mascota = m.id_mascota
                                         WHERE pa.id_adopcion = ? AND p.id_usuario = ?");
        $stmt_info->bind_param("ii", $id_adopcion, $usuario_id);
        $stmt_info->execute();
        $resultado = $stmt_info->get_result();

        if ($resultado->num_rows == 0) {
            throw new Exception("No tienes permisos o no se encontró la publicación");
        }

        $mascota = $resultado->fetch_assoc();

        // Reconstruir descripción completa
        $descripcion_completa = "💜 BUSCA HOGAR 💜\n\n";
        $descripcion_completa .= "Nombre: " . $mascota['nombre_mascota'] . "\n";
        $descripcion_completa .= "Tipo: " . ucfirst($mascota['tipo']) . "\n";
        if (!empty($mascota['sexo'])) {
            $descripcion_completa .= "Sexo: " . ucfirst($mascota['sexo']) . "\n";
        }
        if (!empty($mascota['edad_mascota'])) {
            $descripcion_completa .= "Edad: " . $mascota['edad_mascota'] . " años\n";
        }
        $descripcion_completa .= "\nCondiciones de adopción:\n" . $condiciones;
        $descripcion_completa .= "\n\nLugar de entrega: " . $lugar_adopcion;
        $descripcion_completa .= "\n\n¿Le darías un hogar lleno de amor a " . $mascota['nombre_mascota'] . "? ¡Contáctanos! ❤️";

        // Actualizar publicacion_adopcion
        $stmt1 = $conexion->prepare("UPDATE publicacion_adopcion SET condiciones = ?, lugar_adopcion = ? WHERE id_adopcion = ?");
        $stmt1->bind_param("ssi", $condiciones, $lugar_adopcion, $id_adopcion);
        $stmt1->execute();

        // Actualizar descripción en publicaciones
        $stmt2 = $conexion->prepare("UPDATE publicaciones SET descripcion = ? WHERE id_anuncio = ? AND id_usuario = ?");
        $stmt2->bind_param("sii", $descripcion_completa, $mascota['id_anuncio'], $usuario_id);
        $stmt2->execute();

        if ($stmt1->affected_rows > 0 || $stmt2->affected_rows > 0) {
            mysqli_commit($conexion);
            header("Location: adopciones.php?exito=publicacion_editada");
        } else {
            throw new Exception("No se realizaron cambios");
        }
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        header("Location: adopciones.php?error=error_editar&detalle=" . urlencode($e->getMessage()));
    }
} elseif ($accion === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_adopcion = (int) $_POST['id_adopcion'];

        // Verificar solicitudes pendientes
        $stmt_check = $conexion->prepare("SELECT COUNT(*) as total FROM solicitud_adopcion WHERE id_adopcion = ? AND estado = 'pendiente'");
        $stmt_check->bind_param("i", $id_adopcion);
        $stmt_check->execute();
        $solicitudes_pendientes = $stmt_check->get_result()->fetch_assoc()['total'];

        if ($solicitudes_pendientes > 0) {
            throw new Exception("No puedes eliminar: tienes solicitudes pendientes");
        }

        mysqli_autocommit($conexion, FALSE);

        // Eliminar solicitudes
        $stmt1 = $conexion->prepare("DELETE FROM solicitud_adopcion WHERE id_adopcion = ?");
        $stmt1->bind_param("i", $id_adopcion);
        $stmt1->execute();

        // Eliminar publicación completa
        $stmt2 = $conexion->prepare("DELETE p, pa FROM publicaciones p 
                                    JOIN publicacion_adopcion pa ON p.id_anuncio = pa.id_publicacion 
                                    WHERE pa.id_adopcion = ? AND p.id_usuario = ?");
        $stmt2->bind_param("ii", $id_adopcion, $usuario_id);

        if ($stmt2->execute() && $stmt2->affected_rows > 0) {
            mysqli_commit($conexion);
            header("Location: adopciones.php?exito=publicacion_eliminada");
        } else {
            throw new Exception("No tienes permisos o no se encontró la publicación");
        }
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        header("Location: adopciones.php?error=error_eliminar&detalle=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: adopciones.php");
}
?>