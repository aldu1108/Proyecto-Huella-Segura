<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$id_peso = isset($_POST['id_peso']) ? (int)$_POST['id_peso'] : 0;
$id_mascota = isset($_POST['id_mascota']) ? (int)$_POST['id_mascota'] : 0;
$peso = isset($_POST['peso']) ? floatval($_POST['peso']) : 0;
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';

// Verificar que la mascota pertenece al usuario
$consulta_verificar = "SELECT m.id_mascota 
                       FROM mascotas m 
                       JOIN seguimiento_peso sp ON m.id_mascota = sp.id_mascota
                       WHERE sp.id_peso = $id_peso 
                       AND m.id_usuario = $usuario_id";
$resultado_verificar = $conexion->query($consulta_verificar);

if (!$resultado_verificar || $resultado_verificar->num_rows == 0) {
    header("Location: perfil-mascota.php?id=$id_mascota&error=no_autorizado");
    exit();
}

// Actualizar el peso
$consulta_actualizar = "UPDATE seguimiento_peso 
                        SET peso = $peso, fecha = '$fecha' 
                        WHERE id_peso = $id_peso";

if ($conexion->query($consulta_actualizar)) {
    header("Location: perfil-mascota.php?id=$id_mascota&exito=peso_actualizado");
} else {
    header("Location: perfil-mascota.php?id=$id_mascota&error=error_peso");
}
exit();
?>