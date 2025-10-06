<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: mis-mascotas.php");
    exit();
}

$id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$mascota_id = isset($_POST['mascotas'][0]) ? (int)$_POST['mascotas'][0] : 0;

$titulo = $conexion->real_escape_string($_POST['titulo']);
$descripcion = $conexion->real_escape_string($_POST['descripcion']);
$fecha = $_POST['fecha'] . ' ' . $_POST['hora'];

$consulta = "UPDATE recordatorios_personales 
             SET titulo = '$titulo', descripcion = '$descripcion', fecha = '$fecha'
             WHERE id_recordatorio = $id AND id_usuario = $usuario_id";

if ($conexion->query($consulta)) {
    header("Location: perfil-mascota.php?id=$mascota_id&exito=recordatorio_actualizado");
} else {
    header("Location: perfil-mascota.php?id=$mascota_id&error=error_actualizar");
}
?>