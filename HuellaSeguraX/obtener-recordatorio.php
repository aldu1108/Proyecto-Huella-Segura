<?php
include_once('config/conexion.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
    exit();
}

$id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$consulta = "SELECT * FROM recordatorios_personales 
             WHERE id_recordatorio = $id AND id_usuario = $usuario_id";
$resultado = $conexion->query($consulta);

if ($resultado && $resultado->num_rows > 0) {
    echo json_encode([
        'success' => true,
        'recordatorio' => $resultado->fetch_assoc()
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Recordatorio no encontrado']);
}
?>