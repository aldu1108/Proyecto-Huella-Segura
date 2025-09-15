<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

$mensaje = "";
$tipo = "";

// Procesar acción
if ($_GET && isset($_GET['accion']) && isset($_GET['email'])) {
    $accion = $_GET['accion'];
    $email = $_GET['email'];

    // Buscar el veterinario por email
    $consulta_vet = "SELECT u.id_usuario, v.id_veterinario, u.nombre_usuario, u.apellido_usuario 
                     FROM usuarios u 
                     JOIN veterinario v ON u.id_usuario = v.id_usuario 
                     WHERE u.email_usuario = '$email' AND v.certificado = 0";

    $resultado = $conexion->query($consulta_vet);

    if ($resultado && $resultado->num_rows > 0) {
        $veterinario = $resultado->fetch_assoc();
        $nombre_completo = $veterinario['nombre_usuario'] . ' ' . $veterinario['apellido_usuario'];

        if ($accion === 'aprobar') {
            // Aprobar veterinario (certificado = 1)
            $update = "UPDATE veterinario SET certificado = 1 WHERE id_veterinario = " . $veterinario['id_veterinario'];

            if ($conexion->query($update)) {
                $mensaje = "✅ Veterinario $nombre_completo APROBADO exitosamente. Ya puede acceder al sistema.";
                $tipo = "success";
            } else {
                $mensaje = "❌ Error al aprobar al veterinario $nombre_completo";
                $tipo = "error";
            }

        } elseif ($accion === 'rechazar') {
            // Rechazar = eliminar cuenta completamente
            $delete_vet = "DELETE FROM veterinario WHERE id_veterinario = " . $veterinario['id_veterinario'];
            $delete_user = "DELETE FROM usuarios WHERE id_usuario = " . $veterinario['id_usuario'];

            if ($conexion->query($delete_vet) && $conexion->query($delete_user)) {
                $mensaje = "❌ Veterinario $nombre_completo RECHAZADO y eliminado del sistema.";
                $tipo = "warning";
            } else {
                $mensaje = "❌ Error al rechazar al veterinario $nombre_completo";
                $tipo = "error";
            }
        }
    } else {
        $mensaje = "❌ Veterinario no encontrado o ya procesado";
        $tipo = "error";
    }
}

// Redirigir de vuelta al panel con mensaje
$redirect_url = "panel-admin.php";
if (!empty($mensaje)) {
    $redirect_url .= "?mensaje=" . urlencode($mensaje) . "&tipo=" . $tipo;
}

header("Location: $redirect_url");
exit();
?>