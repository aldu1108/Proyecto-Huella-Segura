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
    header("Location: index.php?recordatorio=agregado");
} else {
    header("Location: index.php?error=recordatorio");
}
exit();
?>