<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_POST['id_recordatorio'])) {
    header("Location: mis-mascotas.php");
    exit();
}

$id = (int)$_POST['id_recordatorio'];
$usuario_id = $_SESSION['usuario_id'];

$titulo = $conexion->real_escape_string($_POST['titulo']);
$descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string($_POST['descripcion']) : '';
$fecha = $_POST['fecha'] . ' ' . $_POST['hora'] . ':00';

// Actualizar el recordatorio
$consulta = "UPDATE recordatorios_personales 
             SET titulo = '$titulo', descripcion = '$descripcion', fecha = '$fecha'
             WHERE id_recordatorio = $id AND id_usuario = $usuario_id";

if ($conexion->query($consulta)) {
    // Eliminar relaciones anteriores con mascotas
    $eliminar_relaciones = "DELETE FROM recordatorio_mascota WHERE id_recordatorio = $id";
    $conexion->query($eliminar_relaciones);
    
    // Insertar nuevas relaciones si vienen mascotas
    if (isset($_POST['mascotas']) && is_array($_POST['mascotas'])) {
        foreach ($_POST['mascotas'] as $id_mascota) {
            $id_mascota = (int)$id_mascota;
            
            // Verificar que la mascota pertenece al usuario
            $verificar = "SELECT id_mascota FROM mascotas 
                          WHERE id_mascota = $id_mascota 
                          AND id_usuario = $usuario_id 
                          AND estado = 'activo'";
            $resultado_verificar = $conexion->query($verificar);
            
            if ($resultado_verificar && $resultado_verificar->num_rows > 0) {
                $insertar_relacion = "INSERT INTO recordatorio_mascota (id_recordatorio, id_mascota) 
                                      VALUES ($id, $id_mascota)";
                $conexion->query($insertar_relacion);
            }
        }
        
        // Decidir redirección según cantidad de mascotas
        if (count($_POST['mascotas']) == 1) {
            // Si es una sola mascota, ir a su perfil
            $mascota_id = $_POST['mascotas'][0];
            header("Location: perfil-mascota.php?id=$mascota_id&exito=recordatorio_actualizado");
        } else {
            // Si son múltiples, ir al index
            header("Location: index.php?exito=recordatorio_actualizado#calendario");
        }
    } else {
        // Si no hay mascotas seleccionadas, ir al index
        header("Location: index.php?exito=recordatorio_actualizado#calendario");
    }
} else {
    // En caso de error
    if (isset($_POST['mascotas'][0])) {
        $mascota_id = $_POST['mascotas'][0];
        header("Location: perfil-mascota.php?id=$mascota_id&error=error_actualizar");
    } else {
        header("Location: index.php?error=error_actualizar");
    }
}
exit();
?>