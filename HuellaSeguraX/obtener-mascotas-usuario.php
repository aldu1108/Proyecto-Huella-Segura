<?php
// API simple para obtener mascotas del usuario (para los selects de formularios)
include_once('config/conexion.php');
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener mascotas del usuario
$consulta = "SELECT id_mascota, nombre_mascota, tipo FROM mascotas 
            WHERE id_usuario = $usuario_id AND estado = 'activo' 
            ORDER BY nombre_mascota";

$resultado = $conexion->query($consulta);

$mascotas = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($mascota = $resultado->fetch_assoc()) {
        $mascotas[] = [
            'id' => $mascota['id_mascota'],
            'nombre' => $mascota['nombre_mascota'],
            'tipo' => $mascota['tipo']
        ];
    }
}

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($mascotas);

cerrarConexion();
?>