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
$usuario_id = $_SESSION['usuario_id'];

// Verificar que la mascota pertenece al usuario usando prepared statement
$stmt = $conexion->prepare("SELECT id_mascota FROM mascotas WHERE id_mascota = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_mascota, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    // Insertar el peso usando prepared statement
    $stmt2 = $conexion->prepare("INSERT INTO seguimiento_peso (peso, fecha, id_mascota) VALUES (?, ?, ?)");
    $stmt2->bind_param("dsi", $peso, $fecha, $id_mascota);
    
    if ($stmt2->execute()) {
        header("Location: perfil-mascota.php?id=$id_mascota&peso=agregado");
    } else {
        header("Location: perfil-mascota.php?id=$id_mascota&error=peso");
    }
    $stmt2->close();
} else {
    header("Location: mis-mascotas.php");
}

$stmt->close();
cerrarConexion();
exit();
?>