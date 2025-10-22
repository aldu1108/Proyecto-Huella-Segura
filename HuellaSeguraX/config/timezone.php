<?php
// Detectar zona horaria del usuario basado en su configuración o geolocalización
function obtenerZonaHoraria($usuario_id, $conexion) {
    // Opción 1: Desde base de datos (requiere campo en tabla usuarios)
    $consulta = "SELECT zona_horaria FROM usuarios WHERE id_usuario = $usuario_id";
    $resultado = $conexion->query($consulta);
    
    if ($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        if (!empty($usuario['zona_horaria'])) {
            return $usuario['zona_horaria'];
        }
    }
    
    // Opción 2: Desde sesión (si se detectó con JavaScript)
    if (isset($_SESSION['zona_horaria'])) {
        return $_SESSION['zona_horaria'];
    }
    
    // Por defecto: Argentina
    return 'America/Argentina/Buenos_Aires';
}

// Establecer zona horaria
if (isset($_SESSION['usuario_id'])) {
    $zona = obtenerZonaHoraria($_SESSION['usuario_id'], $conexion);
    date_default_timezone_set($zona);
}
?>