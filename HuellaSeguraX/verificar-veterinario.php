<?php
// Verificar que el usuario sea veterinario aprobado
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: login.php");
    exit();
}

// Verificar que el veterinario esté certificado
$consulta_cert = "SELECT certificado FROM veterinario WHERE id_usuario = " . $_SESSION['usuario_id'];
$resultado_cert = $conexion->query($consulta_cert);

if ($resultado_cert && $resultado_cert->num_rows > 0) {
    $vet_data = $resultado_cert->fetch_assoc();
    if ($vet_data['certificado'] != 1) {
        // Veterinario no aprobado
        session_destroy();
        header("Location: login.php?mensaje=cuenta_pendiente");
        exit();
    }
} else {
    // No es veterinario
    header("Location: login.php");
    exit();
}
?>