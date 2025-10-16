<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para eliminar eventos'
    ]);
    exit();
}

// Verificar datos
if (!isset($_POST['evento_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'];
$evento_id = intval($_POST['evento_id']);

// Verificar que el evento existe y obtener información
$consulta_evento = "SELECT e.id_evento, e.id_usuario, e.titulo, e.contador_asistentes
                    FROM eventos_comunidad e 
                    WHERE e.id_evento = $evento_id";
$resultado_evento = $conexion->query($consulta_evento);

if ($resultado_evento->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Evento no encontrado'
    ]);
    exit();
}

$evento = $resultado_evento->fetch_assoc();

// Verificar permisos: solo el creador del evento o un administrador pueden eliminarlo
if ($evento['id_usuario'] != $usuario_id && $rol_usuario !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para eliminar este evento'
    ]);
    exit();
}

// Usar transacción para eliminar evento y sus asistentes
$conexion->begin_transaction();

try {
    // 1. Eliminar todos los asistentes del evento
    $sql_delete_asistentes = "DELETE FROM asistentes_evento WHERE id_evento = $evento_id";
    $conexion->query($sql_delete_asistentes);
    
    // 2. Eliminar el evento
    $sql_delete_evento = "DELETE FROM eventos_comunidad WHERE id_evento = $evento_id";
    $conexion->query($sql_delete_evento);
    
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Evento eliminado correctamente',
        'titulo_evento' => $evento['titulo'],
        'asistentes_afectados' => $evento['contador_asistentes']
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el evento: ' . $e->getMessage()
    ]);
}
?>
