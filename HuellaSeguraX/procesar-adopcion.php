<?php
// Procesar publicación de adopción
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
    $condiciones = $_POST['condiciones'];
    $lugar_adopcion = $_POST['lugar_adopcion'];
    $descripcion = $_POST['descripcion'];

    // Validaciones básicas
    if (empty($id_mascota) || empty($condiciones) || empty($lugar_adopcion)) {
        header("Location: adopciones.php?error=datos_incompletos");
        exit();
    }

    // Verificar que la mascota pertenezca al usuario
    $verificar_mascota = "SELECT id_mascota FROM mascotas WHERE id_mascota = $id_mascota AND id_usuario = $usuario_id";
    $resultado_verificacion = $conexion->query($verificar_mascota);

    if ($resultado_verificacion->num_rows == 0) {
        header("Location: adopciones.php?error=mascota_no_valida");
        exit();
    }

    // Crear la publicación principal
    $consulta_publicacion = "INSERT INTO publicaciones (fecha, estado, titulo, descripcion, foto, id_mascota, id_usuario) 
                            VALUES (NOW(), 'activo', 'Mascota en Adopción', '$descripcion', '', $id_mascota, $usuario_id)";

    if ($conexion->query($consulta_publicacion)) {
        $id_publicacion = $conexion->insert_id;

        // Crear el detalle de adopción
        $consulta_adopcion = "INSERT INTO publicacion_adopcion (condiciones, lugar_adopcion, id_publicacion) 
                            VALUES ('$condiciones', '$lugar_adopcion', $id_publicacion)";

        if ($conexion->query($consulta_adopcion)) {
            header("Location: adopciones.php?exito=publicacion_creada");
        } else {
            header("Location: adopciones.php?error=error_crear_adopcion");
        }
    } else {
        header("Location: adopciones.php?error=error_publicacion");
    }
} else {
    header("Location: adopciones.php");
}

cerrarConexion();
?>