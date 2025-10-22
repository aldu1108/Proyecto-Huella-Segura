<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para eliminar grupos'
    ]);
    exit();
}

// Verificar datos
if (!isset($_POST['grupo_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'];
$grupo_id = intval($_POST['grupo_id']);

// Verificar que el grupo existe y obtener información
$consulta_grupo = "SELECT g.id_grupo, g.id_creador, g.nombre_grupo, g.contador_miembros
                   FROM grupos_comunidad g 
                   WHERE g.id_grupo = $grupo_id";
$resultado_grupo = $conexion->query($consulta_grupo);

if ($resultado_grupo->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Grupo no encontrado'
    ]);
    exit();
}

$grupo = $resultado_grupo->fetch_assoc();

// Verificar permisos: solo el creador del grupo o un administrador pueden eliminarlo
if ($grupo['id_creador'] != $usuario_id && $rol_usuario !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para eliminar este grupo'
    ]);
    exit();
}

// Usar transacción para eliminar grupo y sus miembros
$conexion->begin_transaction();

try {
    // 1. Eliminar todos los miembros del grupo
    $sql_delete_miembros = "DELETE FROM miembros_grupo WHERE id_grupo = $grupo_id";
    $conexion->query($sql_delete_miembros);
    
    // 2. Eliminar el grupo
    $sql_delete_grupo = "DELETE FROM grupos_comunidad WHERE id_grupo = $grupo_id";
    $conexion->query($sql_delete_grupo);
    
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Grupo eliminado correctamente',
        'nombre_grupo' => $grupo['nombre_grupo'],
        'miembros_afectados' => $grupo['contador_miembros']
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el grupo: ' . $e->getMessage()
    ]);
}
?>
