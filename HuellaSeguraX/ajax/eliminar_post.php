<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para eliminar posts'
    ]);
    exit();
}

// Verificar datos
if (!isset($_POST['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'];
$post_id = intval($_POST['post_id']);

// Verificar que el post existe y obtener información
$consulta_post = "SELECT p.id_post, p.id_usuario, p.imagen_post 
                  FROM post_comunidad p 
                  WHERE p.id_post = $post_id";
$resultado_post = $conexion->query($consulta_post);

if ($resultado_post->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Post no encontrado'
    ]);
    exit();
}

$post = $resultado_post->fetch_assoc();

// Verificar permisos: solo el autor del post o un administrador pueden eliminarlo
if ($post['id_usuario'] != $usuario_id && $rol_usuario !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para eliminar este post'
    ]);
    exit();
}

// Usar transacción para eliminar post, comentarios, likes e imágenes
$conexion->begin_transaction();

try {
    // 1. Eliminar todos los comentarios del post
    $sql_delete_comentarios = "DELETE FROM comentarios_comunidad WHERE id_post = $post_id";
    $conexion->query($sql_delete_comentarios);
    
    // 2. Eliminar todos los likes del post
    $sql_delete_likes = "DELETE FROM likes_post WHERE id_post = $post_id";
    $conexion->query($sql_delete_likes);
    
    // 3. Eliminar el post
    $sql_delete_post = "DELETE FROM post_comunidad WHERE id_post = $post_id";
    $conexion->query($sql_delete_post);
    
    $conexion->commit();
    
    // 4. Eliminar imágenes físicas del servidor (después del commit)
    if (!empty($post['imagen_post'])) {
        $imagenes = explode(',', $post['imagen_post']);
        foreach ($imagenes as $imagen) {
            $ruta_imagen = '../imagenes/posts/' . trim($imagen);
            if (file_exists($ruta_imagen)) {
                @unlink($ruta_imagen); // @ para suprimir errores si el archivo no existe
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Post eliminado correctamente'
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el post: ' . $e->getMessage()
    ]);
}
?>
