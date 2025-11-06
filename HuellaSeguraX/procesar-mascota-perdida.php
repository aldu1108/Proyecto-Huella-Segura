<?php
include_once('config/conexion.php');
session_start();

// Habilitar el reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];

    try {
        // Detectar si es edición o creación
        $modo_edicion = isset($_POST['id_publicacion']) && !empty($_POST['id_publicacion']);
        $id_publicacion = $modo_edicion ? (int) $_POST['id_publicacion'] : 0;

        // Obtener datos del formulario
        // Obtener datos del formulario
        if ($modo_edicion) {
            // En modo edición, id_mascota viene del input hidden
            $id_mascota = isset($_POST['id_mascota']) ? (int) $_POST['id_mascota'] : 0;
        } else {
            // En modo creación, viene del select
            $id_mascota = isset($_POST['id_mascota']) ? (int) $_POST['id_mascota'] : 0;
        }
        $fecha_perdida = trim($_POST['fecha_perdida'] ?? '');
        $hora_perdida = trim($_POST['hora_perdida'] ?? '');
        $ultima_ubicacion = trim($_POST['ultima_ubicacion'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $recompensa = isset($_POST['recompensa']) ? (float) $_POST['recompensa'] : 0;

        // Validaciones básicas
        if ($modo_edicion) {
            // En edición no validamos id_mascota porque ya existe
            if (empty($fecha_perdida) || empty($ultima_ubicacion)) {
                throw new Exception("Faltan campos requeridos: fecha o ubicación");
            }
        } else {
            // En creación sí validamos todo
            if (empty($id_mascota) || empty($fecha_perdida) || empty($ultima_ubicacion)) {
                throw new Exception("Faltan campos requeridos: mascota, fecha o ubicación");
            }
        }

        // Verificar que la mascota pertenezca al usuario
        if ($modo_edicion) {
            // En edición, obtener la mascota desde la publicación existente
            $consulta_verificar = "SELECT m.nombre_mascota, m.tipo, m.foto_mascota, m.id_mascota 
                           FROM mascotas m 
                           JOIN publicaciones p ON m.id_mascota = p.id_mascota 
                           WHERE p.id_anuncio = ? AND p.id_usuario = ?";
            $stmt_verificar = $conexion->prepare($consulta_verificar);

            if (!$stmt_verificar) {
                throw new Exception("Error en preparación de consulta verificación: " . $conexion->error);
            }

            $stmt_verificar->bind_param("ii", $id_publicacion, $usuario_id);
            $stmt_verificar->execute();
            $resultado_verificacion = $stmt_verificar->get_result();

            if ($resultado_verificacion->num_rows == 0) {
                throw new Exception("No tienes permiso para editar este reporte");
            }

            $mascota_info = $resultado_verificacion->fetch_assoc();
            $id_mascota = $mascota_info['id_mascota']; // Actualizar id_mascota
        } else {
            // En creación nueva, verificar normalmente
            $consulta_verificar = "SELECT nombre_mascota, tipo, foto_mascota FROM mascotas WHERE id_mascota = ? AND id_usuario = ? AND estado = 'activo'";
            $stmt_verificar = $conexion->prepare($consulta_verificar);

            if (!$stmt_verificar) {
                throw new Exception("Error en preparación de consulta verificación: " . $conexion->error);
            }

            $stmt_verificar->bind_param("ii", $id_mascota, $usuario_id);
            $stmt_verificar->execute();
            $resultado_verificacion = $stmt_verificar->get_result();

            if ($resultado_verificacion->num_rows == 0) {
                throw new Exception("La mascota seleccionada no es válida o no te pertenece");
            }

            $mascota_info = $resultado_verificacion->fetch_assoc();
        }
        $nombre_mascota = $mascota_info['nombre_mascota'];
        $tipo_mascota = $mascota_info['tipo'];
        $foto_mascota = $mascota_info['foto_mascota'];

        // Si es creación, verificar que no exista un reporte activo
        if (!$modo_edicion) {
            $consulta_reporte_existente = "SELECT COUNT(*) as total FROM publicaciones p 
                                           WHERE p.id_mascota = ? AND p.estado = 'activo' 
                                           AND EXISTS (
                                               SELECT 1 FROM publicacion_perdida pp 
                                               WHERE pp.id_publicacion = p.id_anuncio
                                           )";

            $stmt_verificar_reporte = $conexion->prepare($consulta_reporte_existente);
            if (!$stmt_verificar_reporte) {
                throw new Exception("Error al verificar reportes existentes: " . $conexion->error);
            }

            $stmt_verificar_reporte->bind_param("i", $id_mascota);
            $stmt_verificar_reporte->execute();
            $resultado_reporte = $stmt_verificar_reporte->get_result();
            $reporte_existente = $resultado_reporte->fetch_assoc();

            if ($reporte_existente['total'] > 0) {
                $stmt_verificar_reporte->close();
                $stmt_verificar->close();
                header("Location: mascotas-perdidas.php?error=reporte_duplicado&mascota=" . urlencode($nombre_mascota));
                exit();
            }

            $stmt_verificar_reporte->close();
        }

        // Limpiar y validar datos
        $ultima_ubicacion = htmlspecialchars($ultima_ubicacion);
        $descripcion = htmlspecialchars($descripcion);

        // Validar fecha (no puede ser futura)
        if (strtotime($fecha_perdida) > time()) {
            throw new Exception("La fecha no puede ser futura");
        }

        // Combinar fecha y hora
        $fecha_hora_perdida = $fecha_perdida;
        if (!empty($hora_perdida)) {
            $fecha_hora_perdida = $fecha_perdida . ' ' . $hora_perdida . ':00';
        } else {
            $fecha_hora_perdida = $fecha_perdida . ' 00:00:00';
        }

        // Crear título y descripción para la publicación
        $titulo_publicacion = "Se busca: " . $nombre_mascota . " (" . ucfirst($tipo_mascota) . ")";

        $descripcion_completa = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"#EA3323\"><path d=\"M200-160v-80h64l79-263q8-26 29.5-41.5T420-560h120q26 0 47.5 15.5T617-503l79 263h64v80H200Zm240-480v-200h80v200h-80Zm238 99-57-57 142-141 56 56-141 142Zm42 181v-80h200v80H720ZM282-541 141-683l56-56 142 141-57 57ZM40-360v-80h200v80H40Z/></svg> MASCOTA PERDIDA <svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"#EA3323\"><path d=\"M200-160v-80h64l79-263q8-26 29.5-41.5T420-560h120q26 0 47.5 15.5T617-503l79 263h64v80H200Zm240-480v-200h80v200h-80Zm238 99-57-57 142-141 56 56-141 142Zm42 181v-80h200v80H720ZM282-541 141-683l56-56 142 141-57 57ZM40-360v-80h200v80H40Z/></svg>\n\n";
        $descripcion_completa .= "Nombre: " . $nombre_mascota . "\n";
        $descripcion_completa .= "Tipo: " . ucfirst($tipo_mascota) . "\n";
        $descripcion_completa .= "Fecha perdida: " . date('d/m/Y', strtotime($fecha_perdida));

        if (!empty($hora_perdida)) {
            $descripcion_completa .= " a las " . $hora_perdida;
        }

        $descripcion_completa .= "\nÚltima ubicación: " . $ultima_ubicacion;

        if (!empty($descripcion)) {
            $descripcion_completa .= "\n\nDetalles: " . $descripcion;
        }

        if ($recompensa > 0) {
            $descripcion_completa .= "\n\n💰 RECOMPENSA: €" . number_format($recompensa, 2);
        }

        $descripcion_completa .= "\n\n¿Has visto a " . $nombre_mascota . "? ¡Contacta inmediatamente! 📞";

        // Iniciar transacción
        mysqli_autocommit($conexion, FALSE);

        if ($modo_edicion) {
            // EDITAR REPORTE EXISTENTE

            // Verificar que el reporte pertenezca al usuario
            $consulta_permiso = "SELECT id_anuncio FROM publicaciones WHERE id_anuncio = ? AND id_usuario = ?";
            $stmt_permiso = $conexion->prepare($consulta_permiso);
            $stmt_permiso->bind_param("ii", $id_publicacion, $usuario_id);
            $stmt_permiso->execute();
            $resultado_permiso = $stmt_permiso->get_result();

            if ($resultado_permiso->num_rows == 0) {
                throw new Exception("No tienes permiso para editar este reporte");
            }
            $stmt_permiso->close();

            // Actualizar publicación
            $stmt_actualizar_pub = $conexion->prepare("UPDATE publicaciones SET titulo = ?, descripcion = ? WHERE id_anuncio = ?");
            if (!$stmt_actualizar_pub) {
                throw new Exception("Error al preparar actualización de publicación: " . $conexion->error);
            }

            $stmt_actualizar_pub->bind_param("ssi", $titulo_publicacion, $descripcion_completa, $id_publicacion);
            if (!$stmt_actualizar_pub->execute()) {
                throw new Exception("Error al actualizar publicación: " . $stmt_actualizar_pub->error);
            }

            // Actualizar publicacion_perdida
            $stmt_actualizar_perdida = $conexion->prepare("UPDATE publicacion_perdida SET ultima_ubicacion = ?, fecha_perdida = ?, recompensa = ? WHERE id_publicacion = ?");
            if (!$stmt_actualizar_perdida) {
                throw new Exception("Error al preparar actualización de perdida: " . $conexion->error);
            }

            $stmt_actualizar_perdida->bind_param("ssdi", $ultima_ubicacion, $fecha_hora_perdida, $recompensa, $id_publicacion);
            if (!$stmt_actualizar_perdida->execute()) {
                throw new Exception("Error al actualizar datos de perdida: " . $stmt_actualizar_perdida->error);
            }

            // Confirmar transacción
            mysqli_commit($conexion);

            $stmt_actualizar_pub->close();
            $stmt_actualizar_perdida->close();
            $stmt_verificar->close();

            // Redirigir con éxito
            header("Location: mascotas-perdidas.php?exito=reporte_actualizado&mascota=" . urlencode($nombre_mascota));
            exit();

        } else {
            // CREAR NUEVO REPORTE

            // Crear la publicación principal
            $stmt_publicacion = $conexion->prepare("INSERT INTO publicaciones (fecha, estado, titulo, descripcion, foto, id_mascota, id_usuario) VALUES (NOW(), 'activo', ?, ?, ?, ?, ?)");

            if (!$stmt_publicacion) {
                throw new Exception("Error en preparación de consulta publicaciones: " . $conexion->error);
            }

            $stmt_publicacion->bind_param("sssii", $titulo_publicacion, $descripcion_completa, $foto_mascota, $id_mascota, $usuario_id);

            if (!$stmt_publicacion->execute()) {
                throw new Exception("Error al insertar publicación: " . $stmt_publicacion->error);
            }

            $id_publicacion = $conexion->insert_id;

            // Crear el detalle de mascota perdida
            $stmt_perdida = $conexion->prepare("INSERT INTO publicacion_perdida (ultima_ubicacion, fecha_perdida, recompensa, id_publicacion) VALUES (?, ?, ?, ?)");

            if (!$stmt_perdida) {
                throw new Exception("Error en preparación de consulta publicacion_perdida: " . $conexion->error);
            }

            $stmt_perdida->bind_param("ssdi", $ultima_ubicacion, $fecha_hora_perdida, $recompensa, $id_publicacion);

            if (!$stmt_perdida->execute()) {
                throw new Exception("Error al insertar datos de mascota perdida: " . $stmt_perdida->error);
            }

            // Confirmar transacción
            mysqli_commit($conexion);

            // Limpiar statements
            $stmt_verificar->close();
            $stmt_publicacion->close();
            $stmt_perdida->close();

            // Redirigir con mensaje de éxito
            header("Location: mascotas-perdidas.php?exito=reporte_creado&mascota=" . urlencode($nombre_mascota));
            exit();
        }

    } catch (Exception $e) {
        // Revertir cambios
        mysqli_rollback($conexion);

        // Log del error para debug
        error_log("Error en reporte mascota perdida: " . $e->getMessage());
        error_log("POST data: " . print_r($_POST, true));

        // Determinar tipo de error para mensaje más específico
        $error_tipo = "error_crear_reporte";
        if (strpos($e->getMessage(), "Faltan campos") !== false) {
            $error_tipo = "datos_incompletos";
        } elseif (strpos($e->getMessage(), "no es válida") !== false) {
            $error_tipo = "mascota_no_valida";
        } elseif (strpos($e->getMessage(), "fecha no puede") !== false) {
            $error_tipo = "fecha_invalida";
        } elseif (strpos($e->getMessage(), "No tienes permiso") !== false) {
            $error_tipo = "sin_permiso";
        }

        // Redirigir con mensaje de error
        header("Location: mascotas-perdidas.php?error=" . $error_tipo . "&detalle=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: mascotas-perdidas.php?error=metodo_no_permitido");
    exit();
}
?>