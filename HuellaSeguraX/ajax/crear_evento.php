<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para crear eventos'
    ]);
    exit();
}

// Verificar datos
if (!isset($_POST['titulo_evento']) || !isset($_POST['descripcion_evento']) || 
    !isset($_POST['fecha_evento']) || !isset($_POST['hora_evento']) || 
    !isset($_POST['ubicacion_evento'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$titulo = $conexion->real_escape_string(trim($_POST['titulo_evento']));
$descripcion = $conexion->real_escape_string(trim($_POST['descripcion_evento']));
$fecha = $_POST['fecha_evento'];
$hora = $_POST['hora_evento'];
$ubicacion = $conexion->real_escape_string(trim($_POST['ubicacion_evento']));

// Validar campos
if (empty($titulo) || empty($descripcion) || empty($fecha) || empty($hora) || empty($ubicacion)) {
    echo json_encode([
        'success' => false,
        'message' => 'Todos los campos son obligatorios'
    ]);
    exit();
}

// Validar longitud
if (strlen($titulo) < 5 || strlen($titulo) > 100) {
    echo json_encode([
        'success' => false,
        'message' => 'El título debe tener entre 5 y 100 caracteres'
    ]);
    exit();
}

if (strlen($descripcion) < 10 || strlen($descripcion) > 255) {
    echo json_encode([
        'success' => false,
        'message' => 'La descripción debe tener entre 10 y 255 caracteres'
    ]);
    exit();
}

// Combinar fecha y hora
$fecha_hora = $fecha . ' ' . $hora . ':00';

// Validar que la fecha no sea pasada
$fecha_evento_dt = new DateTime($fecha_hora);
$ahora = new DateTime();

if ($fecha_evento_dt < $ahora) {
    echo json_encode([
        'success' => false,
        'message' => 'La fecha y hora del evento no puede ser en el pasado'
    ]);
    exit();
}

// Insertar evento
$sql = "INSERT INTO eventos_comunidad 
        (titulo, fecha, hora, descripcion, ubicacion, estado, contador_participantes, id_mascota, id_creador) 
        VALUES 
        ('$titulo', '$fecha_hora', '$hora', '$descripcion', '$ubicacion', 'activo', 0, 0, $usuario_id)";

if ($conexion->query($sql)) {
    echo json_encode([
        'success' => true,
        'message' => 'Evento creado exitosamente',
        'evento_id' => $conexion->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el evento: ' . $conexion->error
    ]);
}
?>