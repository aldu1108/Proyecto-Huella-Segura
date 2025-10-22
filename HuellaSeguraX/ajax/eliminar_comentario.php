<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para eliminar comentarios'
    ]);
    exit();
}

// Verificar datos
if (!isset($_POST['comentario_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'];
$comentario_id = intval($_POST['comentario_id']);

// Verificar que el comentario existe y obtener información
$consulta_comentario = "SELECT c.id_comentario, c.id_usuario, c.id_post 
                        FROM comentarios_comunidad c 
                        WHERE c.id_comentario = $comentario_id";
$resultado_comentario = $conexion->query($consulta_comentario);

if ($resultado_comentario->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Comentario no encontrado'
    ]);
    exit();
}

$comentario = $resultado_comentario->fetch_assoc();

// Verificar permisos: solo el autor del comentario o un administrador pueden eliminarlo
if ($comentario['id_usuario'] != $usuario_id && $rol_usuario !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para eliminar este comentario'
    ]);
    exit();
}

// Usar transacción para eliminar comentario y actualizar contador
$conexion->begin_transaction();

try {
    // Eliminar el comentario
    $sql_delete = "DELETE FROM comentarios_comunidad WHERE id_comentario = $comentario_id";
    $conexion->query($sql_delete);
    
    // Decrementar el contador de comentarios del post (sin bajar de 0)
    $post_id = $comentario['id_post'];
    $sql_update = "UPDATE post_comunidad 
                   SET conteo_comentarios = GREATEST(0, conteo_comentarios - 1) 
                   WHERE id_post = $post_id";
    $conexion->query($sql_update);
    
    $conexion->commit();
    
    // Obtener el nuevo conteo
    $consulta_conteo = "SELECT conteo_comentarios FROM post_comunidad WHERE id_post = $post_id";
    $resultado_conteo = $conexion->query($consulta_conteo);
    $nuevo_conteo = $resultado_conteo->fetch_assoc()['conteo_comentarios'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Comentario eliminado correctamente',
        'nuevo_conteo' => $nuevo_conteo
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el comentario: ' . $e->getMessage()
    ]);
}
?>
