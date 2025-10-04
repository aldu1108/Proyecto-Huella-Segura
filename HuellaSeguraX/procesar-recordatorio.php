<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$titulo = $conexion->real_escape_string($_POST['titulo']);
$descripcion = $conexion->real_escape_string($_POST['descripcion']);
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$fecha_hora = $fecha . ' ' . $hora . ':00';

$sql = "INSERT INTO recordatorios_personales (titulo, descripcion, fecha, id_usuario) 
        VALUES ('$titulo', '$descripcion', '$fecha_hora', $usuario_id)";

if ($conexion->query($sql)) {
    $id_recordatorio = $conexion->insert_id;
    
    // Asociar con mascotas si se seleccionaron
    if (isset($_POST['mascotas']) && is_array($_POST['mascotas'])) {
        foreach ($_POST['mascotas'] as $id_mascota) {
            $id_mascota = (int)$id_mascota;
            $sql_rel = "INSERT INTO recordatorio_mascota (id_recordatorio, id_mascota) 
                       VALUES ($id_recordatorio, $id_mascota)";
            $conexion->query($sql_rel);
        }
    }
    
    header("Location: index.php?recordatorio=agregado");
}

if ($conexion->query($sql)) {
    header("Location: index.php?recordatorio=agregado");
} else {
    header("Location: index.php?error=recordatorio");
}
exit();
?>