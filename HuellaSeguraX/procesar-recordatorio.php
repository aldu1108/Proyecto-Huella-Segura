<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Validar datos requeridos
if (!isset($_POST['titulo']) || !isset($_POST['fecha']) || !isset($_POST['hora']) || !isset($_POST['mascotas'])) {
    header("Location: index.php?error=datos_incompletos");
    exit();
}

$titulo = $conexion->real_escape_string($_POST['titulo']);
$descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string($_POST['descripcion']) : '';
$fecha = $_POST['fecha'] . ' ' . $_POST['hora'] . ':00';
$mascotas = $_POST['mascotas'];

// Insertar recordatorio
$consulta_recordatorio = "INSERT INTO recordatorios_personales (id_usuario, titulo, descripcion, fecha, completado) 
                          VALUES ($usuario_id, '$titulo', '$descripcion', '$fecha', 0)";

if ($conexion->query($consulta_recordatorio)) {
    $id_recordatorio = $conexion->insert_id;
    
    // Asociar con mascotas
    $exito = true;
    foreach ($mascotas as $id_mascota) {
        $id_mascota = (int)$id_mascota;
        $consulta_relacion = "INSERT INTO recordatorio_mascota (id_recordatorio, id_mascota) 
                             VALUES ($id_recordatorio, $id_mascota)";
        if (!$conexion->query($consulta_relacion)) {
            $exito = false;
        }
    }
    
    // Redirigir según contexto
    if (count($mascotas) == 1) {
        // Si es para una sola mascota, volver a su perfil
        $mascota_id = $mascotas[0];
        if ($exito) {
            header("Location: perfil-mascota.php?id=$mascota_id&exito=recordatorio_agregado");
        } else {
            header("Location: perfil-mascota.php?id=$mascota_id&error=error_relacion");
        }
    } else {
        // Si es para múltiples mascotas, volver al index con ancla al calendario
        if ($exito) {
            header("Location: index.php?exito=recordatorio_agregado#calendario");
        } else {
            header("Location: index.php?error=error_relacion#calendario");
        }
    }
} else {
    if (isset($_POST['mascotas'][0])) {
        $mascota_id = $_POST['mascotas'][0];
        header("Location: perfil-mascota.php?id=$mascota_id&error=error_recordatorio");
    } else {
        header("Location: index.php?error=error_recordatorio");
    }
}
?>