<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mis-mascotas.php");
    exit();
}

$id_mascota = (int)$_POST['id_mascota'];
$peso = (float)$_POST['peso'];
$fecha = $_POST['fecha'];

// Verificar que la mascota pertenece al usuario
$usuario_id = $_SESSION['usuario_id'];
$verificar = "SELECT id_mascota FROM mascotas WHERE id_mascota = $id_mascota AND id_usuario = $usuario_id";
$resultado = $conexion->query($verificar);

if ($resultado && $resultado->num_rows > 0) {
    $sql = "INSERT INTO seguimiento_peso (peso, fecha, id_mascota) VALUES ($peso, '$fecha', $id_mascota)";
    
    if ($conexion->query($sql)) {
        header("Location: perfil-mascota.php?id=$id_mascota&peso=agregado");
    } else {
        header("Location: perfil-mascota.php?id=$id_mascota&error=peso");
    }
} else {
    header("Location: mis-mascotas.php");
}
exit();
?>