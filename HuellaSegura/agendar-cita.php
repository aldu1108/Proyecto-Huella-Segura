<?php
// Agendar cita veterinaria
include_once('config/conexion.php');
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Solo procesar si vienen datos por POST
if ($_POST) {
    $usuario_id = $_SESSION['usuario_id'];
    $id_mascota = $_POST['id_mascota'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo'];
    $id_veterinario = isset($_POST['id_veterinario']) ? $_POST['id_veterinario'] : 1; // Por defecto

    // Validaciones básicas
    if (empty($id_mascota) || empty($fecha) || empty($hora) || empty($motivo)) {
        header("Location: veterinaria.php?error=datos_cita_incompletos");
        exit();
    }

    // Verificar que la mascota pertenezca al usuario
    $verificar_mascota = "SELECT id_mascota FROM mascotas WHERE id_mascota = $id_mascota AND id_usuario = $usuario_id";
    $resultado_verificacion = $conexion->query($verificar_mascota);

    if ($resultado_verificacion->num_rows == 0) {
        header("Location: veterinaria.php?error=mascota_no_valida");
        exit();
    }

    // Combinar fecha y hora
    $fecha_completa = $fecha . ' ' . $hora . ':00';

    // Limpiar datos
    $motivo = strip_tags($motivo);

    // Insertar la cita
    $consulta = "INSERT INTO citas_veterinarias (fecha, motivo, estado, id_mascota, id_veterinario) 
                VALUES ('$fecha_completa', '$motivo', 'programada', $id_mascota, $id_veterinario)";

    if ($conexion->query($consulta)) {
        header("Location: veterinaria.php?exito=cita_agendada");
    } else {
        header("Location: veterinaria.php?error=error_agendar_cita");
    }
} else {
    header("Location: veterinaria.php");
}

cerrarConexion();
?>