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
        // Obtener datos del formulario
        $id_mascota = isset($_POST['id_mascota']) ? (int) $_POST['id_mascota'] : 0;
        $fecha_perdida = trim($_POST['fecha_perdida'] ?? '');
        $hora_perdida = trim($_POST['hora_perdida'] ?? '');
        $ultima_ubicacion = trim($_POST['ultima_ubicacion'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $recompensa = isset($_POST['recompensa']) ? (float) $_POST['recompensa'] : 0;

        // Validaciones básicas
        if (empty($id_mascota) || empty($fecha_perdida) || empty($ultima_ubicacion)) {
            throw new Exception("Faltan campos requeridos: mascota, fecha o ubicación");
        }

        // Verificar que la mascota pertenezca al usuario
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
        $nombre_mascota = $mascota_info['nombre_mascota'];
        $tipo_mascota = $mascota_info['tipo'];
        $foto_mascota = $mascota_info['foto_mascota'];

        // Limpiar y validar datos
        $ultima_ubicacion = htmlspecialchars($ultima_ubicacion);
        $descripcion = htmlspecialchars($descripcion);

        // Validar fecha (no puede ser futura)
        if (strtotime($fecha_perdida) > time()) {
            throw new Exception("La fecha no puede ser futura");
        }

        // Crear título y descripción para la publicación
        $titulo_publicacion = "Se busca: " . $nombre_mascota . " (" . ucfirst($tipo_mascota) . ")";

        $descripcion_completa = "🚨 MASCOTA PERDIDA 🚨\n\n";
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

        // 2. Crear el detalle de mascota perdida
        $stmt_perdida = $conexion->prepare("INSERT INTO publicacion_perdida (ultima_ubicacion, fecha_perdida, recompensa, id_publicacion) VALUES (?, ?, ?, ?)");

        if (!$stmt_perdida) {
            throw new Exception("Error en preparación de consulta publicacion_perdida: " . $conexion->error);
        }

        $stmt_perdida->bind_param("ssdi", $ultima_ubicacion, $fecha_perdida, $recompensa, $id_publicacion);

        if (!$stmt_perdida->execute()) {
            throw new Exception("Error al insertar datos de mascota perdida: " . $stmt_perdida->error);
        }

        // 3. NO actualizamos el estado de la mascota para que siga apareciendo en "Mis Mascotas"
        // La mascota permanece con estado 'activo' en la tabla mascotas
        // El estado de "perdida" se maneja a través de la tabla publicaciones

        // Confirmar transacción
        mysqli_commit($conexion);

        // Limpiar statements
        $stmt_verificar->close();
        $stmt_publicacion->close();
        $stmt_perdida->close();

        // Redirigir con mensaje de éxito
        header("Location: mascotas-perdidas.php?exito=reporte_creado&mascota=" . urlencode($nombre_mascota));
        exit();

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
        }
        cerrarConexion();
        // Redirigir con mensaje de error
        header("Location: mascotas-perdidas.php?error=" . $error_tipo . "&detalle=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: mascotas-perdidas.php?error=metodo_no_permitido");
    exit();
}


?>