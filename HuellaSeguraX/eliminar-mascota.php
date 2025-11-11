<?php
include_once('config/conexion.php');
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mascota_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($mascota_id <= 0) {
    header(header: "Location: mis-mascotas.php?error=id_invalido");
    exit();
}

// Verificar que la mascota pertenece al usuario
$consulta_verificar = "SELECT id_mascota, nombre_mascota, foto_mascota 
                       FROM mascotas 
                       WHERE id_mascota = $mascota_id AND id_usuario = $usuario_id";
$resultado_verificar = $conexion->query($consulta_verificar);

if (!$resultado_verificar || $resultado_verificar->num_rows == 0) {
    header("Location: mis-mascotas.php?error=no_autorizado");
    exit();
}

$mascota = $resultado_verificar->fetch_assoc();

// Iniciar transacción
$conexion->begin_transaction();

try {
    // 1. Eliminar recordatorios asociados
    $consulta_recordatorios = "SELECT r.id_recordatorio 
                               FROM recordatorios_personales r
                               JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                               WHERE rm.id_mascota = $mascota_id AND r.id_usuario = $usuario_id";
    $resultado_recordatorios = $conexion->query($consulta_recordatorios);

    if ($resultado_recordatorios && $resultado_recordatorios->num_rows > 0) {
        while ($rec = $resultado_recordatorios->fetch_assoc()) {
            // Eliminar relaciones recordatorio-mascota
            $conexion->query("DELETE FROM recordatorio_mascota WHERE id_recordatorio = {$rec['id_recordatorio']}");
            // Eliminar recordatorio si no tiene más mascotas asociadas
            $conexion->query("DELETE FROM recordatorios_personales WHERE id_recordatorio = {$rec['id_recordatorio']} AND id_usuario = $usuario_id");
        }
    }

    // 2. Eliminar seguimiento de peso
    $conexion->query("DELETE FROM seguimiento_peso WHERE id_mascota = $mascota_id");

    // 3. Eliminar fichas de salud
    $conexion->query("DELETE FROM fichas_de_salud WHERE id_mascota = $mascota_id");

    // 4. Eliminar citas veterinarias
    $conexion->query("DELETE FROM citas_veterinarias WHERE id_mascota = $mascota_id");

    // 5. Eliminar documentos médicos asociados a historiales
    $consulta_historiales = "SELECT id_historial FROM historiales_medicos WHERE id_mascota = $mascota_id";
    $resultado_historiales = $conexion->query($consulta_historiales);

    if ($resultado_historiales && $resultado_historiales->num_rows > 0) {
        while ($hist = $resultado_historiales->fetch_assoc()) {
            $conexion->query("DELETE FROM documento_medico WHERE id_historial = {$hist['id_historial']}");
        }
    }

    // 6. Eliminar historiales médicos
    $conexion->query("DELETE FROM historiales_medicos WHERE id_mascota = $mascota_id");

    // 7. Eliminar paseos
    $conexion->query("DELETE FROM paseos WHERE id_mascota = $mascota_id");

    // 8. Eliminar eventos de comunidad y sus asistentes
    $consulta_eventos = "SELECT id_evento FROM eventos_comunidad WHERE id_mascota = $mascota_id";
    $resultado_eventos = $conexion->query($consulta_eventos);

    if ($resultado_eventos && $resultado_eventos->num_rows > 0) {
        while ($evento = $resultado_eventos->fetch_assoc()) {
            $conexion->query("DELETE FROM asistentes_evento WHERE id_evento = {$evento['id_evento']}");
        }
    }
    $conexion->query("DELETE FROM eventos_comunidad WHERE id_mascota = $mascota_id");

    // 9. Eliminar solicitudes de adopción relacionadas
    $consulta_publicaciones = "SELECT id_anuncio FROM publicaciones WHERE id_mascota = $mascota_id";
    $resultado_publicaciones = $conexion->query($consulta_publicaciones);

    if ($resultado_publicaciones && $resultado_publicaciones->num_rows > 0) {
        while ($pub = $resultado_publicaciones->fetch_assoc()) {
            // Buscar adopciones relacionadas
            $consulta_adopcion = "SELECT id_adopcion FROM publicacion_adopcion WHERE id_publicacion = {$pub['id_anuncio']}";
            $resultado_adopcion = $conexion->query($consulta_adopcion);

            if ($resultado_adopcion && $resultado_adopcion->num_rows > 0) {
                while ($adop = $resultado_adopcion->fetch_assoc()) {
                    // Eliminar solicitudes de adopción
                    $conexion->query("DELETE FROM solicitud_adopcion WHERE id_adopcion = {$adop['id_adopcion']}");
                }
                // Eliminar publicación de adopción
                $conexion->query("DELETE FROM publicacion_adopcion WHERE id_publicacion = {$pub['id_anuncio']}");
            }

            // Eliminar publicación perdida si existe
            $conexion->query("DELETE FROM publicacion_perdida WHERE id_publicacion = {$pub['id_anuncio']}");
        }
    }

    // 10. Eliminar publicaciones
    $conexion->query("DELETE FROM publicaciones WHERE id_mascota = $mascota_id");

    // 11. Finalmente, eliminar la mascota
    $consulta_eliminar = "DELETE FROM mascotas WHERE id_mascota = $mascota_id AND id_usuario = $usuario_id";

    if (!$conexion->query($consulta_eliminar)) {
        throw new Exception("Error al eliminar la mascota");
    }

    // Si llegamos aquí, todo salió bien
    $conexion->commit();

    // Eliminar foto si no es la predeterminada
    if (
        !empty($mascota['foto_mascota']) &&
        $mascota['foto_mascota'] != 'mascota-default.jpg' &&
        file_exists("imagenes/" . $mascota['foto_mascota'])
    ) {
        unlink("imagenes/" . $mascota['foto_mascota']);
    }

    // Redirigir con mensaje de éxito
    // cerrar conexión antes de redirigir
    $conexion->close();
    header("Location: mis-mascotas.php?exito=mascota_eliminada&nombre=" . urlencode($mascota['nombre_mascota']));
    exit();

} catch (Exception $e) {
    // Si algo salió mal, revertir todos los cambios
    $conexion->rollback();

    // Redirigir con mensaje de error
    // cerrar conexión antes de redirigir
    $conexion->close();
    header("Location: perfil-mascota.php?id=$mascota_id&error=error_eliminar");
    exit();
}

?>