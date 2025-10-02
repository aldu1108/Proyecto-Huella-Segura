<?php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para comentar'
    ]);
    exit();
}

// Verificar datos
if (!isset($_POST['post_id']) || !isset($_POST['contenido'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$post_id = intval($_POST['post_id']);
$contenido = trim($_POST['contenido']);

// Validar contenido
if (empty($contenido)) {
    echo json_encode([
        'success' => false,
        'message' => 'El comentario no puede estar vacío'
    ]);
    exit();
}

if (strlen($contenido) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'El comentario debe tener al menos 2 caracteres'
    ]);
    exit();
}

if (strlen($contenido) > 500) {
    echo json_encode([
        'success' => false,
        'message' => 'El comentario no puede exceder 500 caracteres'
    ]);
    exit();
}

// Verificar que el post existe
$consulta_post = "SELECT id_post FROM post_comunidad WHERE id_post = $post_id";
$resultado_post = $conexion->query($consulta_post);

if ($resultado_post->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Post no encontrado'
    ]);
    exit();
}

// Escapar contenido
$contenido = $conexion->real_escape_string($contenido);

// Usar transacción para insertar comentario y actualizar contador
$conexion->begin_transaction();

try {
    // Insertar comentario
    $sql_insert = "INSERT INTO comentarios_comunidad (contenido, fecha, id_post, id_usuario) 
                   VALUES ('$contenido', NOW(), $post_id, $usuario_id)";
    $conexion->query($sql_insert);
    
    $comentario_id = $conexion->insert_id;
    
    // Incrementar contador de comentarios
    $sql_update = "UPDATE post_comunidad SET conteo_comentarios = conteo_comentarios + 1 WHERE id_post = $post_id";
    $conexion->query($sql_update);
    
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Comentario publicado correctamente',
        'comentario_id' => $comentario_id
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar el comentario: ' . $e->getMessage()
    ]);
}
?>
