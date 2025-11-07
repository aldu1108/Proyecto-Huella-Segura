<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

include_once('../config/conexion.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_clean();

function responderJSON($success, $message, $extra = []) {
    ob_clean();
    $response = array_merge(['success' => $success, 'message' => $message], $extra);
    echo json_encode($response);
    exit();
}

try {
    // Verificar autenticación
    if (!isset($_SESSION['rol']) || !isset($_SESSION['usuario_id'])) {
        responderJSON(false, 'No autenticado');
    }
    
    $usuario_id = intval($_SESSION['usuario_id']);
    $rol_usuario = $_SESSION['rol'];
    
    // Verificar que sea veterinario
    if ($rol_usuario !== 'veterinario') {
        responderJSON(false, 'Solo los veterinarios pueden eliminar documentos');
    }
    
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderJSON(false, 'Método no permitido');
    }
    
    // Validar datos recibidos
    if (!isset($_POST['id_documento']) || !isset($_POST['id_historial']) || !isset($_POST['archivo'])) {
        responderJSON(false, 'Datos incompletos');
    }
    
    $id_documento = intval($_POST['id_documento']);
    $id_historial = intval($_POST['id_historial']);
    $nombre_archivo = $conexion->real_escape_string($_POST['archivo']);
    
    // Obtener id_veterinario del usuario actual
    $stmt = $conexion->prepare("SELECT id_veterinario FROM veterinario WHERE id_usuario = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 0) {
        responderJSON(false, 'No estás registrado como veterinario');
    }
    
    $id_veterinario_actual = $resultado->fetch_assoc()['id_veterinario'];
    $stmt->close();
    
    // SEGURIDAD: Verificar que el documento pertenezca al veterinario actual
    $stmt = $conexion->prepare("SELECT dm.archivo, h.id_veterinario 
                                FROM documento_medico dm 
                                JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                WHERE dm.id_documento = ? AND h.id_historial = ?");
    $stmt->bind_param("ii", $id_documento, $id_historial);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 0) {
        responderJSON(false, 'Documento no encontrado');
    }
    
    $documento = $resultado->fetch_assoc();
    $stmt->close();
    
    // Verificar que el veterinario sea el propietario
    if ($documento['id_veterinario'] != $id_veterinario_actual) {
        responderJSON(false, 'No tienes permisos para eliminar este documento');
    }
    
    // Verificar que el nombre del archivo coincida (seguridad adicional)
    if ($documento['archivo'] !== $nombre_archivo) {
        responderJSON(false, 'Error de validación del archivo');
    }
    
    // Ruta del archivo
    $ruta_archivo = '../documentos/medicos/' . $nombre_archivo;
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    try {
        // 1. Eliminar registro del documento
        $stmt = $conexion->prepare("DELETE FROM documento_medico WHERE id_documento = ?");
        $stmt->bind_param("i", $id_documento);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al eliminar el registro del documento');
        }
        $stmt->close();
        
        // 2. Eliminar historial médico asociado
        $stmt = $conexion->prepare("DELETE FROM historiales_medicos WHERE id_historial = ?");
        $stmt->bind_param("i", $id_historial);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al eliminar el historial médico');
        }
        $stmt->close();
        
        // 3. Eliminar archivo físico
        if (file_exists($ruta_archivo)) {
            if (!unlink($ruta_archivo)) {
                throw new Exception('Error al eliminar el archivo del servidor');
            }
        }
        
        // Confirmar transacción
        $conexion->commit();
        
        responderJSON(true, 'Documento eliminado exitosamente', [
            'id_documento' => $id_documento
        ]);
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conexion->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error en eliminar_documento.php: " . $e->getMessage());
    responderJSON(false, 'Error al eliminar el documento: ' . $e->getMessage());
}

$conexion->close();
