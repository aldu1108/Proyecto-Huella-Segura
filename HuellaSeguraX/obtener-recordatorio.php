<?php
include_once('config/conexion.php');
session_start();

// Evitar cualquier salida antes del JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$id_recordatorio = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener recordatorio
$consulta = "SELECT * FROM recordatorios_personales 
             WHERE id_recordatorio = $id_recordatorio 
             AND id_usuario = $usuario_id";
$resultado = $conexion->query($consulta);

if (!$resultado || $resultado->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Recordatorio no encontrado']);
    exit();
}

$recordatorio = $resultado->fetch_assoc();

// Obtener mascotas asociadas
$consulta_mascotas = "SELECT id_mascota FROM recordatorio_mascota 
                      WHERE id_recordatorio = $id_recordatorio";
$resultado_mascotas = $conexion->query($consulta_mascotas);

$mascotas_asociadas = [];
if ($resultado_mascotas) {
    while($mascota = $resultado_mascotas->fetch_assoc()) {
        $mascotas_asociadas[] = (int)$mascota['id_mascota'];
    }
}

$recordatorio['mascotas'] = $mascotas_asociadas;

echo json_encode([
    'success' => true,
    'recordatorio' => $recordatorio
]);
exit();
?>