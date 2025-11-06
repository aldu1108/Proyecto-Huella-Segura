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
            // Aprobar veterinario (certificado = 1) Y cambiar rol a veterinario
            $update_vet = "UPDATE veterinario SET certificado = 1 WHERE id_veterinario = " . $veterinario['id_veterinario'];
            $update_rol = "UPDATE usuarios SET rol = 'veterinario' WHERE id_usuario = " . $veterinario['id_usuario'];

            if ($conexion->query($update_vet) && $conexion->query($update_rol)) {
                $mensaje = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"#75FB4C\"><path d=\"M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z\"/></svg> Veterinario $nombre_completo APROBADO exitosamente. Ya puede acceder al sistema.";
                $tipo = "success";
        } else {
            $mensaje = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"#EA3323\"><path d=\"m336-280-56-56 144-144-144-143 56-56 144 144 143-144 56 56-144 143 144 144-56 56-143-144-144 144Z\"/></svg> Error al aprobar al veterinario $nombre_completo";
            $tipo = "error";
        }

        } elseif ($accion === 'rechazar') {
            // Rechazar = eliminar cuenta completamente
            $delete_vet = "DELETE FROM veterinario WHERE id_veterinario = " . $veterinario['id_veterinario'];
            $delete_user = "DELETE FROM usuarios WHERE id_usuario = " . $veterinario['id_usuario'];

            if ($conexion->query($delete_vet) && $conexion->query($delete_user)) {
                $mensaje = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"#EA3323\"><path d=\"m336-280-56-56 144-144-144-143 56-56 144 144 143-144 56 56-144 143 144 144-56 56-143-144-144 144Z\"/></svg> Veterinario $nombre_completo RECHAZADO y eliminado del sistema.";
                $tipo = "warning";
            } else {
                $mensaje = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"#EA3323\"><path d=\"m336-280-56-56 144-144-144-143 56-56 144 144 143-144 56 56-144 143 144 144-56 56-143-144-144 144Z\"/></svg> Error al rechazar al veterinario $nombre_completo";
                $tipo = "error";
            }
        }
    } else {
        $mensaje = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"24px\" viewBox=\"0 -960 960 960\" width=\"24px\" fill=\"#EA3323\"><path d=\"m336-280-56-56 144-144-144-143 56-56 144 144 143-144 56 56-144 143 144 144-56 56-143-144-144 144Z\"/></svg> Veterinario no encontrado o ya procesado";
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