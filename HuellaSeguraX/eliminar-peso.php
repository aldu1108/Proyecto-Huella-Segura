<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_peso = (int)$_GET['id'];
$mascota_id = (int)$_GET['mascota'];

$sql = "DELETE FROM seguimiento_peso WHERE id_peso = $id_peso";

if ($conexion->query($sql)) {
    header("Location: perfil-mascota.php?id=$mascota_id&exito=peso_eliminado");
} else {
    header("Location: perfil-mascota.php?id=$mascota_id&error=eliminar_peso");
}
exit();
?>