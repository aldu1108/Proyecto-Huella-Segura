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
        $id_adopcion = isset($_POST['id_adopcion']) ? (int) $_POST['id_adopcion'] : 0;
        $descripcion = trim($_POST['descripcion'] ?? '');
        $informacion_adicional = trim($_POST['informacion_adicional'] ?? '');

        // Validaciones básicas
        if (empty($id_adopcion)) {
            throw new Exception("ID de adopción no válido");
        }

        if (empty($descripcion)) {
            throw new Exception("La descripción es requerida");
        }

        // CORRECCIÓN: Usar id_publicacion en lugar de id_anuncio
        $consulta_verificar = "SELECT pa.id_adopcion, p.titulo, m.nombre_mascota, u.id_usuario as propietario_id
                              FROM publicacion_adopcion pa 
                              JOIN publicaciones p ON pa.id_publicacion = p.id_anuncio
                              JOIN mascotas m ON p.id_mascota = m.id_mascota
                              JOIN usuarios u ON p.id_usuario = u.id_usuario
                              WHERE pa.id_adopcion = ? AND p.estado = 'activo'";

        $stmt_verificar = $conexion->prepare($consulta_verificar);
        if (!$stmt_verificar) {
            throw new Exception("Error en preparación de consulta verificación: " . $conexion->error);
        }

        $stmt_verificar->bind_param("i", $id_adopcion);
        $stmt_verificar->execute();
        $resultado_verificacion = $stmt_verificar->get_result();

        if ($resultado_verificacion->num_rows == 0) {
            throw new Exception("La publicación de adopción no existe o no está disponible");
        }

        $adopcion_info = $resultado_verificacion->fetch_assoc();

        // Verificar que el usuario no esté solicitando adoptar su propia mascota
        if ($adopcion_info['propietario_id'] == $usuario_id) {
            throw new Exception("No puedes solicitar adoptar tu propia mascota");
        }

        // Verificar que el usuario no haya solicitado ya esta adopción
        $consulta_duplicada = "SELECT id_solicitud FROM solicitud_adopcion 
                              WHERE id_usuario = ? AND id_adopcion = ? AND estado IN ('pendiente', 'aprobada')";
        $stmt_duplicada = $conexion->prepare($consulta_duplicada);
        if ($stmt_duplicada) {
            $stmt_duplicada->bind_param("ii", $usuario_id, $id_adopcion);
            $stmt_duplicada->execute();
            $resultado_duplicada = $stmt_duplicada->get_result();

            if ($resultado_duplicada->num_rows > 0) {
                throw new Exception("Ya has solicitado adoptar esta mascota");
            }
            $stmt_duplicada->close();
        }

        // Sanitizar datos
        $descripcion = htmlspecialchars($descripcion);
        $informacion_adicional = htmlspecialchars($informacion_adicional);

        // Iniciar transacción
        mysqli_autocommit($conexion, FALSE);

        // CORRECCIÓN: Agregar campos adicionales a la inserción
        if (!empty($informacion_adicional)) {
            $descripcion_completa = $descripcion . "\n\nInformación adicional: " . $informacion_adicional;
        } else {
            $descripcion_completa = $descripcion;
        }

        // Insertar solicitud de adopción
        $stmt_solicitud = $conexion->prepare("INSERT INTO solicitud_adopcion (fecha, estado, descripcion, id_usuario, id_adopcion) VALUES (NOW(), 'pendiente', ?, ?, ?)");

        if (!$stmt_solicitud) {
            throw new Exception("Error en preparación de consulta solicitud: " . $conexion->error);
        }

        $stmt_solicitud->bind_param("sii", $descripcion_completa, $usuario_id, $id_adopcion);

        if (!$stmt_solicitud->execute()) {
            throw new Exception("Error al insertar solicitud de adopción: " . $stmt_solicitud->error);
        }

        // Confirmar transacción
        mysqli_commit($conexion);

        // Limpiar statements
        $stmt_verificar->close();
        $stmt_solicitud->close();

        // Redirigir con mensaje de éxito
        header("Location: adopciones.php?exito=solicitud_enviada&mascota=" . urlencode($adopcion_info['nombre_mascota']));
        exit();

    } catch (Exception $e) {
        // Revertir cambios
        mysqli_rollback($conexion);

        // Log del error para debug
        error_log("Error en solicitud adopción: " . $e->getMessage());

        // Determinar tipo de error para mensaje más específico
        $error_tipo = "error_solicitud";
        if (strpos($e->getMessage(), "no válido") !== false) {
            $error_tipo = "adopcion_no_valida";
        } elseif (strpos($e->getMessage(), "propia mascota") !== false) {
            $error_tipo = "propia_mascota";
        } elseif (strpos($e->getMessage(), "Ya has solicitado") !== false) {
            $error_tipo = "solicitud_duplicada";
        } elseif (strpos($e->getMessage(), "requerida") !== false) {
            $error_tipo = "descripcion_requerida";
        }

        // Redirigir con mensaje de error
        header("Location: adopciones.php?error=" . $error_tipo . "&detalle=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: adopciones.php?error=metodo_no_permitido");
    exit();
}
?>