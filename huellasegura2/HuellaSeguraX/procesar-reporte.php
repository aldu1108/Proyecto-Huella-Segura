<?php
// Procesar reporte de mascota perdida
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
    $ultima_ubicacion = $_POST['ultima_ubicacion'];
    $fecha_perdida = $_POST['fecha_perdida'];
    $recompensa = $_POST['recompensa'];
    $descripcion = $_POST['descripcion'];

    // Validaciones básicas
    if (empty($id_mascota) || empty($ultima_ubicacion) || empty($fecha_perdida)) {
        header("Location: mascotas-perdidas.php?error=datos_incompletos");
        exit();
    }

    // Si no hay recompensa, poner 0
    if (empty($recompensa)) {
        $recompensa = 0;
    }

    // Crear la publicación principal
    $consulta_publicacion = "INSERT INTO publicaciones (fecha, estado, titulo, descripcion, foto, id_mascota, id_usuario) 
                            VALUES (NOW(), 'activo', 'Mascota Perdida', '$descripcion', '', $id_mascota, $usuario_id)";

    if ($conexion->query($consulta_publicacion)) {
        $id_publicacion = $conexion->insert_id;

        // Crear el detalle de mascota perdida
        $consulta_perdida = "INSERT INTO publicacion_perdida (ultima_ubicacion, fecha_perdida, recompensa, id_publicacion) 
                            VALUES ('$ultima_ubicacion', '$fecha_perdida', $recompensa, $id_publicacion)";

        if ($conexion->query($consulta_perdida)) {
            header("Location: mascotas-perdidas.php?exito=reporte_creado");
        } else {
            header("Location: mascotas-perdidas.php?error=error_crear_reporte");
        }
    } else {
        header("Location: mascotas-perdidas.php?error=error_publicacion");
    }
} else {
    header("Location: mascotas-perdidas.php");
}

cerrarConexion();
?>