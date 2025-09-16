<?php
include_once('config/conexion.php');
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_POST) {
    $usuario_id = $_SESSION['usuario_id'];
    $nombre_mascota = $_POST['nombre_mascota'];
    $tipo = $_POST['tipo'];
    $sexo = $_POST['sexo'];
    $edad_mascota = $_POST['edad_mascota'];
    $cumpleanos_mascota = $_POST['cumpleanos_mascota'];
    
    // Manejo de la foto
    $foto_mascota = '';
    if (isset($_FILES['foto_mascota']) && $_FILES['foto_mascota']['error'] == 0) {
        $directorio_destino = 'imagenes/';
        $extension = pathinfo($_FILES['foto_mascota']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'mascota_' . time() . '.' . $extension;
        $ruta_completa = $directorio_destino . $nombre_archivo;
        
        // Verificar que sea una imagen
        $tipos_permitidos = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($extension), $tipos_permitidos)) {
            if (move_uploaded_file($_FILES['foto_mascota']['tmp_name'], $ruta_completa)) {
                $foto_mascota = $nombre_archivo;
            }
        }
    }
    
    // Si no hay foto, usar imagen por defecto
    if (empty($foto_mascota)) {
        $foto_mascota = 'mascota-default.jpg';
    }
    
    // Insertar en la base de datos - consulta muy básica
    $consulta = "INSERT INTO mascotas (id_usuario, tipo, sexo, nombre_mascota, edad_mascota, cumpleaños_mascota, foto_mascota, estado) 
                 VALUES ($usuario_id, '$tipo', '$sexo', '$nombre_mascota', $edad_mascota, '$cumpleanos_mascota', '$foto_mascota', 'activo')";
    
    if ($conexion->query($consulta)) {
        header("Location: mis-mascotas.php?mensaje=mascota_agregada");
    } else {
        header("Location: mis-mascotas.php?error=error_agregar");
    }
} else {
    header("Location: mis-mascotas.php");
}

cerrarConexion();
?>