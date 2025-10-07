<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$grupo_id = intval($_POST['grupo_id']);
$accion = $_POST['accion'];

$conexion->begin_transaction();

try {
    if ($accion === 'unirse') {
        $sql_insert = "INSERT INTO miembros_grupo (id_grupo, id_usuario) VALUES ($grupo_id, $usuario_id)";
        $conexion->query($sql_insert);
        
        $sql_update = "UPDATE grupos_comunidad SET contador_miembros = contador_miembros + 1 WHERE id_grupo = $grupo_id";
        $conexion->query($sql_update);
        
        $mensaje = 'Te has unido al grupo';
    } else {
        $sql_delete = "DELETE FROM miembros_grupo WHERE id_grupo = $grupo_id AND id_usuario = $usuario_id";
        $conexion->query($sql_delete);
        
        $sql_update = "UPDATE grupos_comunidad SET contador_miembros = GREATEST(1, contador_miembros - 1) WHERE id_grupo = $grupo_id";
        $conexion->query($sql_update);
        
        $mensaje = 'Has salido del grupo';
    }
    
    $conexion->commit();
    
    $consulta_contador = "SELECT contador_miembros FROM grupos_comunidad WHERE id_grupo = $grupo_id";
    $resultado = $conexion->query($consulta_contador);
    $total = $resultado->fetch_assoc()['contador_miembros'];
    
    echo json_encode(['success' => true, 'message' => $mensaje, 'total_miembros' => $total]);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>