<?php
include_once('config/conexion.php');
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];

    try {
        // Obtener datos del formulario
        $id_mascota = isset($_POST['id_mascota']) ? (int) $_POST['id_mascota'] : 0;
        $condiciones = trim($_POST['condiciones'] ?? '');
        $lugar_adopcion = trim($_POST['lugar_adopcion'] ?? '');
        $motivo_adopcion = trim($_POST['motivo_adopcion'] ?? '');

        // Validaciones básicas
        if (empty($id_mascota)) {
            throw new Exception("Debes seleccionar una mascota");
        }

        if (empty($condiciones)) {
            throw new Exception("Las condiciones de adopción son requeridas");
        }

        if (empty($lugar_adopcion)) {
            throw new Exception("El lugar de adopción es requerido");
        }

        // Verificar que la mascota pertenezca al usuario
        $consulta_verificar = "SELECT nombre_mascota, tipo, foto_mascota, sexo, edad_mascota FROM mascotas WHERE id_mascota = ? AND id_usuario = ? AND estado = 'activo'";
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
        $nombre_mascota = $mascota_info['nombre_mascota'];
        $tipo_mascota = $mascota_info['tipo'];
        $foto_mascota = $mascota_info['foto_mascota'];

        // Verificar que la mascota no esté ya en adopción
        $consulta_adopcion_existente = "SELECT pa.id_adopcion FROM publicacion_adopcion pa 
                                       JOIN publicaciones p ON pa.id_publicacion = p.id_anuncio 
                                       WHERE p.id_mascota = ? AND p.estado = 'activo'";
        $stmt_adopcion_existente = $conexion->prepare($consulta_adopcion_existente);
        if ($stmt_adopcion_existente) {
            $stmt_adopcion_existente->bind_param("i", $id_mascota);
            $stmt_adopcion_existente->execute();
            $resultado_adopcion_existente = $stmt_adopcion_existente->get_result();

            if ($resultado_adopcion_existente->num_rows > 0) {
                throw new Exception("Esta mascota ya tiene una publicación de adopción activa");
            }
            $stmt_adopcion_existente->close();
        }

        // Limpiar y validar datos
        $condiciones = htmlspecialchars($condiciones);
        $lugar_adopcion = htmlspecialchars($lugar_adopcion);
        $motivo_adopcion = htmlspecialchars($motivo_adopcion);

        // Crear título y descripción para la publicación
        $titulo_publicacion = "En adopción: " . $nombre_mascota . " (" . ucfirst($tipo_mascota) . ")";

        $descripcion_completa = "💝 BUSCA HOGAR 💝\n\n";
        $descripcion_completa .= "Nombre: " . $nombre_mascota . "\n";
        $descripcion_completa .= "Tipo: " . ucfirst($tipo_mascota) . "\n";

        if (!empty($mascota_info['sexo'])) {
            $descripcion_completa .= "Sexo: " . ucfirst($mascota_info['sexo']) . "\n";
        }

        if (!empty($mascota_info['edad_mascota'])) {
            $descripcion_completa .= "Edad: " . $mascota_info['edad_mascota'] . " años\n";
        }

        if (!empty($motivo_adopcion)) {
            $descripcion_completa .= "\nMotivo: " . $motivo_adopcion . "\n";
        }

        $descripcion_completa .= "\nCondiciones de adopción:\n" . $condiciones;
        $descripcion_completa .= "\n\nLugar de entrega: " . $lugar_adopcion;
        $descripcion_completa .= "\n\n¿Le darías un hogar lleno de amor a " . $nombre_mascota . "? ¡Contáctanos! ❤️";

        // Iniciar transacción
        mysqli_autocommit($conexion, FALSE);

        // 1. Crear la publicación principal
        $stmt_publicacion = $conexion->prepare("INSERT INTO publicaciones (fecha, estado, titulo, descripcion, foto, id_mascota, id_usuario) VALUES (NOW(), 'activo', ?, ?, ?, ?, ?)");

        if (!$stmt_publicacion) {
            throw new Exception("Error en preparación de consulta publicaciones: " . $conexion->error);
        }

        $stmt_publicacion->bind_param("sssii", $titulo_publicacion, $descripcion_completa, $foto_mascota, $id_mascota, $usuario_id);

        if (!$stmt_publicacion->execute()) {
            throw new Exception("Error al insertar publicación: " . $stmt_publicacion->error);
        }

        $id_publicacion = $conexion->insert_id;

        // 2. Crear el detalle de publicación de adopción
        $stmt_adopcion = $conexion->prepare("INSERT INTO publicacion_adopcion (condiciones, lugar_adopcion, id_publicacion) VALUES (?, ?, ?)");

        if (!$stmt_adopcion) {
            throw new Exception("Error en preparación de consulta publicacion_adopcion: " . $conexion->error);
        }

        $stmt_adopcion->bind_param("ssi", $condiciones, $lugar_adopcion, $id_publicacion);

        if (!$stmt_adopcion->execute()) {
            throw new Exception("Error al insertar datos de adopción: " . $stmt_adopcion->error);
        }

        // Confirmar transacción
        mysqli_commit($conexion);

        // Limpiar statements
        $stmt_verificar->close();
        $stmt_publicacion->close();
        $stmt_adopcion->close();

        // Redirigir con mensaje de éxito
        header("Location: adopciones.php?exito=publicacion_creada&mascota=" . urlencode($nombre_mascota));
        exit();

    } catch (Exception $e) {
        // Revertir cambios
        mysqli_rollback($conexion);

        // Log del error para debug
        error_log("Error en publicación adopción: " . $e->getMessage());

        // Determinar tipo de error para mensaje más específico
        $error_tipo = "error_crear_publicacion";
        if (strpos($e->getMessage(), "seleccionar una mascota") !== false) {
            $error_tipo = "mascota_requerida";
        } elseif (strpos($e->getMessage(), "no es válida") !== false) {
            $error_tipo = "mascota_no_valida";
        } elseif (strpos($e->getMessage(), "ya tiene una publicación") !== false) {
            $error_tipo = "publicacion_existente";
        } elseif (strpos($e->getMessage(), "son requeridas") !== false || strpos($e->getMessage(), "es requerido") !== false) {
            $error_tipo = "campos_requeridos";
        }
        cerrarConexion();
        // Redirigir con mensaje de error
        header("Location: adopciones.php?error=" . $error_tipo . "&detalle=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: adopciones.php?error=metodo_no_permitido");
    exit();
}


?>