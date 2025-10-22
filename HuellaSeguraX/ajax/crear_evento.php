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

// SOLUCIÓN: Obtener una mascota válida del usuario para asociar al evento
// Si el usuario no tiene mascotas, usar la primera mascota disponible o crear el evento sin mascota
$sql_mascota = "SELECT id_mascota FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo' LIMIT 1";
$resultado_mascota = $conexion->query($sql_mascota);

if ($resultado_mascota && $resultado_mascota->num_rows > 0) {
    $mascota = $resultado_mascota->fetch_assoc();
    $id_mascota = $mascota['id_mascota'];
} else {
    // Si no tiene mascotas, usar cualquier mascota activa del sistema
    $sql_mascota_sistema = "SELECT id_mascota FROM mascotas WHERE estado = 'activo' LIMIT 1";
    $resultado_sistema = $conexion->query($sql_mascota_sistema);
    
    if ($resultado_sistema && $resultado_sistema->num_rows > 0) {
        $mascota = $resultado_sistema->fetch_assoc();
        $id_mascota = $mascota['id_mascota'];
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No hay mascotas disponibles en el sistema. Registra una mascota primero.'
        ]);
        exit();
    }
}

// Insertar evento con mascota válida
$sql = "INSERT INTO eventos_comunidad 
        (titulo, fecha, hora, descripcion, ubicacion, estado, contador_asistentes, id_mascota, id_usuario) 
        VALUES 
        ('$titulo', '$fecha_hora', '$hora', '$descripcion', '$ubicacion', 'activo', 0, $id_mascota, $usuario_id)";

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