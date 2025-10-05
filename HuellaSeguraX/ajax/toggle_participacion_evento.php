<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para participar en eventos'
    ]);
    exit();
}

// Verificar datos
if (!isset($_POST['evento_id']) || !isset($_POST['accion'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$evento_id = intval($_POST['evento_id']);
$accion = $_POST['accion'];

// Verificar que el evento existe
$consulta_evento = "SELECT id_evento FROM eventos_comunidad WHERE id_evento = $evento_id AND estado = 'activo'";
$resultado_evento = $conexion->query($consulta_evento);

if ($resultado_evento->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Evento no encontrado'
    ]);
    exit();
}

// Usar transacción para consistencia
$conexion->begin_transaction();

try {
    if ($accion === 'unirse') {
        // Verificar que no esté ya participando
        $consulta_existe = "SELECT id_participante FROM participantes_evento 
                           WHERE id_evento = $evento_id AND id_usuario = $usuario_id";
        $resultado_existe = $conexion->query($consulta_existe);
        
        if ($resultado_existe->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya estás participando en este evento'
            ]);
            $conexion->rollback();
            exit();
        }
        
        // Insertar participación
        $sql_insert = "INSERT INTO participantes_evento (id_evento, id_usuario, fecha_union) 
                      VALUES ($evento_id, $usuario_id, NOW())";
        $conexion->query($sql_insert);
        
        // Incrementar contador
        $sql_update = "UPDATE eventos_comunidad 
                      SET contador_participantes = contador_participantes + 1 
                      WHERE id_evento = $evento_id";
        $conexion->query($sql_update);
        
        $mensaje = '¡Te has unido al evento exitosamente!';
        
    } else if ($accion === 'salir') {
        // Eliminar participación
        $sql_delete = "DELETE FROM participantes_evento 
                      WHERE id_evento = $evento_id AND id_usuario = $usuario_id";
        $conexion->query($sql_delete);
        
        // Decrementar contador (sin bajar de 0)
        $sql_update = "UPDATE eventos_comunidad 
                      SET contador_participantes = GREATEST(0, contador_participantes - 1) 
                      WHERE id_evento = $evento_id";
        $conexion->query($sql_update);
        
        $mensaje = 'Has salido del evento';
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        $conexion->rollback();
        exit();
    }
    
    $conexion->commit();
    
    // Obtener el nuevo contador
    $consulta_contador = "SELECT contador_participantes FROM eventos_comunidad WHERE id_evento = $evento_id";
    $resultado_contador = $conexion->query($consulta_contador);
    $total_participantes = $resultado_contador->fetch_assoc()['contador_participantes'];
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'total_participantes' => $total_participantes
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la participación: ' . $e->getMessage()
    ]);
}
?>