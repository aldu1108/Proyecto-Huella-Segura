<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$tipo = $_GET['tipo'];
$id = (int)$_GET['id'];
$mascota_id = (int)$_GET['mascota'];

if ($tipo === 'recordatorio') {
    $sql = "DELETE FROM recordatorios_personales WHERE id_recordatorio = $id AND id_usuario = {$_SESSION['usuario_id']}";
} else {
    $sql = "DELETE FROM citas_veterinarias WHERE id_cita = $id";
}

if ($conexion->query($sql)) {
    header("Location: perfil-mascota.php?id=$mascota_id&exito=eliminado");
} else {
    header("Location: perfil-mascota.php?id=$mascota_id&error=eliminar");
}
exit();
?>