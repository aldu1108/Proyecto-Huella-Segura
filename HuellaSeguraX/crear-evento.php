<?php
// Crear evento en la comunidad
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
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $ubicacion = $_POST['ubicacion'];

    // Validaciones básicas
    if (empty($titulo) || empty($fecha) || empty($hora)) {
        header("Location: comunidad.php?error=datos_evento_incompletos");
        exit();
    }

    // Combinar fecha y hora
    $fecha_completa = $fecha . ' ' . $hora . ':00';

    // Limpiar datos
    $titulo = strip_tags($titulo);
    $descripcion = strip_tags($descripcion);
    $ubicacion = strip_tags($ubicacion);

    // Como no tenemos mascota específica, usaremos 1 por defecto o crear evento sin mascota
    // Para simplificar, asumiremos que el usuario tiene al menos una mascota
    $consulta_mascota = "SELECT id_mascota FROM mascotas WHERE id_usuario = $usuario_id LIMIT 1";
    $resultado_mascota = $conexion->query($consulta_mascota);

    if ($resultado_mascota->num_rows > 0) {
        $mascota = $resultado_mascota->fetch_assoc();
        $id_mascota = $mascota['id_mascota'];
    } else {
        // Si no tiene mascotas, usar 0 (habría que modificar la tabla para permitir NULL)
        $id_mascota = 0;
    }

    // Insertar el evento
    $consulta = "INSERT INTO eventos (fecha, titulo, descripcion, estado, id_mascota, id_usuario) 
                VALUES ('$fecha_completa', '$titulo', '$descripcion - Lugar: $ubicacion', 'activo', $id_mascota, $usuario_id)";

    if ($conexion->query($consulta)) {
        header("Location: comunidad.php?exito=evento_creado");
    } else {
        header("Location: comunidad.php?error=error_crear_evento");
    }
} else {
    header("Location: comunidad.php");
}

cerrarConexion();
?>