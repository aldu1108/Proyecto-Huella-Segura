<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit();
}

if (!isset($_POST['nombre_grupo']) || !isset($_POST['descripcion_grupo']) || !isset($_POST['icono_grupo'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = $conexion->real_escape_string(trim($_POST['nombre_grupo']));
$descripcion = $conexion->real_escape_string(trim($_POST['descripcion_grupo']));
$icono = $conexion->real_escape_string($_POST['icono_grupo']);

if (strlen($nombre) < 3 || strlen($nombre) > 100) {
    echo json_encode(['success' => false, 'message' => 'El nombre debe tener entre 3 y 100 caracteres']);
    exit();
}

$conexion->begin_transaction();

try {
    $sql_grupo = "INSERT INTO grupos_comunidad (nombre_grupo, descripcion, icono, id_creador) 
                  VALUES ('$nombre', '$descripcion', '$icono', $usuario_id)";
    $conexion->query($sql_grupo);
    $grupo_id = $conexion->insert_id;
    
    $sql_miembro = "INSERT INTO miembros_grupo (id_grupo, id_usuario, rol) 
                    VALUES ($grupo_id, $usuario_id, 'admin')";
    $conexion->query($sql_miembro);
    
    $conexion->commit();
    
    echo json_encode(['success' => true, 'grupo_id' => $grupo_id]);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>