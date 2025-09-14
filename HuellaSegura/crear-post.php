<?php
// Crear post en la comunidad
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
    $contenido = $_POST['contenido'];

    // Validación básica
    if (empty($contenido)) {
        header("Location: comunidad.php?error=contenido_vacio");
        exit();
    }

    // Limpiar el contenido (básico)
    $contenido = strip_tags($contenido);
    $contenido = substr($contenido, 0, 500); // Máximo 500 caracteres

    // Generar un título automático basado en las primeras palabras
    $titulo = substr($contenido, 0, 50);
    if (strlen($contenido) > 50) {
        $titulo .= "...";
    }

    // Insertar el post
    $consulta = "INSERT INTO post_comunidad (titulo, contenido, fecha, id_usuario) 
                VALUES ('$titulo', '$contenido', NOW(), $usuario_id)";

    if ($conexion->query($consulta)) {
        header("Location: comunidad.php?exito=post_creado");
    } else {
        header("Location: comunidad.php?error=error_crear_post");
    }
} else {
    header("Location: comunidad.php");
}

cerrarConexion();
?>