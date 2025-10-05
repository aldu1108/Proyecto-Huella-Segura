<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$titulo = $conexion->real_escape_string(trim($_POST['titulo']));
$descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string(trim($_POST['descripcion'])) : '';
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$fecha_hora = $fecha . ' ' . $hora . ':00';

// Insertar recordatorio UNA SOLA VEZ
$sql = "INSERT INTO recordatorios_personales (titulo, descripcion, fecha, id_usuario, completado) 
        VALUES ('$titulo', '$descripcion', '$fecha_hora', $usuario_id, 0)";

if ($conexion->query($sql)) {
    $id_recordatorio = $conexion->insert_id;
    
    // Asociar con mascotas si se seleccionaron
    if (isset($_POST['mascotas']) && is_array($_POST['mascotas'])) {
        foreach ($_POST['mascotas'] as $id_mascota) {
            $id_mascota = (int)$id_mascota;
            
            // Verificar que no exista ya esta relación
            $verificar = "SELECT id FROM recordatorio_mascota WHERE id_recordatorio = $id_recordatorio AND id_mascota = $id_mascota";
            $resultado_verificar = $conexion->query($verificar);
            
            if ($resultado_verificar->num_rows == 0) {
                $sql_rel = "INSERT INTO recordatorio_mascota (id_recordatorio, id_mascota) 
                           VALUES ($id_recordatorio, $id_mascota)";
                $conexion->query($sql_rel);
            }
        }
    }
    
    // Redireccionar según el origen
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'perfil-mascota.php') !== false) {
        $id_mascota = isset($_POST['mascotas'][0]) ? $_POST['mascotas'][0] : 0;
        header("Location: perfil-mascota.php?id=$id_mascota&recordatorio=agregado");
    } else {
        header("Location: index.php?recordatorio=agregado");
    }
} else {
    header("Location: index.php?error=recordatorio");
}

cerrarConexion();
exit();
?>