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
    $nombre_mascota = strip_tags($_POST['nombre_mascota']);
    $tipo = $_POST['tipo'];
    $sexo = $_POST['sexo'];
    $edad_mascota = (int) $_POST['edad_mascota'];
    $cumpleanos_mascota = !empty($_POST['cumpleanos_mascota']) ? $_POST['cumpleanos_mascota'] : null;

    // Si no hay fecha de cumpleaños, calcular una aproximada
    if (!$cumpleanos_mascota) {
        $año_actual = date('Y');
        $año_nacimiento = $año_actual - $edad_mascota;
        $cumpleanos_mascota = $año_nacimiento . '-01-01';
    }

    // Manejo de la foto
    $foto_mascota = 'mascota-default.jpg'; // Valor por defecto

    if (isset($_FILES['foto_mascota']) && $_FILES['foto_mascota']['error'] == 0) {
        $directorio_destino = 'imagenes/';

        // Crear directorio si no existe
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }

        $extension = strtolower(pathinfo($_FILES['foto_mascota']['name'], PATHINFO_EXTENSION));
        $nombre_archivo = 'mascota_' . $usuario_id . '_' . time() . '.' . $extension;
        $ruta_completa = $directorio_destino . $nombre_archivo;

        // Verificar que sea una imagen válida
        $tipos_permitidos = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $tamaño_maximo = 5000000; // 5MB

        if (in_array($extension, $tipos_permitidos) && $_FILES['foto_mascota']['size'] <= $tamaño_maximo) {
            if (move_uploaded_file($_FILES['foto_mascota']['tmp_name'], $ruta_completa)) {
                $foto_mascota = $nombre_archivo;
            }
        }
    }

    // Preparar la consulta con valores seguros
    $stmt = $conexion->prepare("INSERT INTO mascotas (id_usuario, tipo, sexo, nombre_mascota, edad_mascota, cumpleaños_mascota, foto_mascota, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'activo')");

    if ($stmt) {
        $stmt->bind_param("isssiss", $usuario_id, $tipo, $sexo, $nombre_mascota, $edad_mascota, $cumpleanos_mascota, $foto_mascota);

        if ($stmt->execute()) {
            header("Location: mis-mascotas.php?mensaje=mascota_agregada&nombre=" . urlencode($nombre_mascota));
        } else {
            header("Location: mis-mascotas.php?error=error_agregar");
        }
        $stmt->close();
    } else {
        header("Location: mis-mascotas.php?error=error_base_datos");
    }
} else {
    header("Location: mis-mascotas.php");
}

cerrarConexion();
?>