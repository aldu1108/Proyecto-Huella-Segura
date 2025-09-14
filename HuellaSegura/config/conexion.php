<?php
// Configuración de conexión a la base de datos
$servidor = "localhost";
$usuario_bd = "root";
$clave_bd = "";
$nombre_bd = "huellasegura";

// Crear conexión
$conexion = new mysqli($servidor, $usuario_bd, $clave_bd, $nombre_bd);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer el conjunto de caracteres
$conexion->set_charset("utf8");

// Función para cerrar conexión
function cerrarConexion() {
    global $conexion;
    $conexion->close();
}
?>