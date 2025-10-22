<?php
include_once('config/conexion.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
    exit();
}

$id_recordatorio = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar que el recordatorio pertenece al usuario
$verificar = "SELECT id_recordatorio FROM recordatorios_personales 
              WHERE id_recordatorio = $id_recordatorio AND id_usuario = $usuario_id";
$resultado = $conexion->query($verificar);

if (!$resultado || $resultado->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Obtener mascotas asociadas
$consulta = "SELECT id_mascota FROM recordatorio_mascota WHERE id_recordatorio = $id_recordatorio";
$resultado = $conexion->query($consulta);

$mascotas = [];
if ($resultado) {
    while($row = $resultado->fetch_assoc()) {
        $mascotas[] = (int)$row['id_mascota'];
    }
}

echo json_encode([
    'success' => true,
    'mascotas' => $mascotas
]);
?>