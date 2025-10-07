<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_POST['id_recordatorio'])) {
    header("Location: mis-mascotas.php");
    exit();
}

$id = (int)$_POST['id_recordatorio'];
$usuario_id = $_SESSION['usuario_id'];

$titulo = $conexion->real_escape_string($_POST['titulo']);
$descripcion = $conexion->real_escape_string($_POST['descripcion']);
$fecha = $_POST['fecha'] . ' ' . $_POST['hora'] . ':00';

// Obtener la mascota asociada al recordatorio
$consulta_mascota = "SELECT rm.id_mascota FROM recordatorio_mascota rm 
                     JOIN recordatorios_personales r ON rm.id_recordatorio = r.id_recordatorio
                     WHERE r.id_recordatorio = $id AND r.id_usuario = $usuario_id LIMIT 1";
$resultado_mascota = $conexion->query($consulta_mascota);
$mascota_id = $resultado_mascota->num_rows > 0 ? $resultado_mascota->fetch_assoc()['id_mascota'] : 0;

$consulta = "UPDATE recordatorios_personales 
             SET titulo = '$titulo', descripcion = '$descripcion', fecha = '$fecha'
             WHERE id_recordatorio = $id AND id_usuario = $usuario_id";

if ($conexion->query($consulta)) {
    header("Location: perfil-mascota.php?id=$mascota_id&exito=recordatorio_actualizado");
} else {
    header("Location: perfil-mascota.php?id=$mascota_id&error=error_actualizar");
}
exit();
?>