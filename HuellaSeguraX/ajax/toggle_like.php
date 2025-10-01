<?php
// ajax/toggle_like.php
session_start();
include_once('../config/conexion.php');

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para dar like'
    ]);
    exit();
}

// Verificar que se reciban los datos necesarios
if (!isset($_POST['post_id']) || !isset($_POST['accion'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$post_id = intval($_POST['post_id']);
$accion = $_POST['accion'];

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

if ($accion === 'dar') {
    // Dar like (verificar que no exista primero)
    $consulta_existe = "SELECT id_like FROM likes_post WHERE id_post = $post_id AND id_usuario = $usuario_id";
    $resultado_existe = $conexion->query($consulta_existe);
    
    if ($resultado_existe->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya diste like a este post'
        ]);
        exit();
    }
    
    // Insertar like y actualizar contador en una transacción
    $conexion->begin_transaction();
    
    try {
        // Insertar el like
        $sql_insert = "INSERT INTO likes_post (id_post, id_usuario, fecha) VALUES ($post_id, $usuario_id, NOW())";
        $conexion->query($sql_insert);
        
        // Incrementar el contador
        $sql_update = "UPDATE post_comunidad SET conteo_likes = conteo_likes + 1 WHERE id_post = $post_id";
        $conexion->query($sql_update);
        
        $conexion->commit();
        $exito = true;
    } catch (Exception $e) {
        $conexion->rollback();
        $exito = false;
    }
    
} else if ($accion === 'quitar') {
    // Quitar like y actualizar contador en una transacción
    $conexion->begin_transaction();
    
    try {
        // Eliminar el like
        $sql_delete = "DELETE FROM likes_post WHERE id_post = $post_id AND id_usuario = $usuario_id";
        $conexion->query($sql_delete);
        
        // Decrementar el contador (sin bajar de 0)
        $sql_update = "UPDATE post_comunidad SET conteo_likes = GREATEST(0, conteo_likes - 1) WHERE id_post = $post_id";
        $conexion->query($sql_update);
        
        $conexion->commit();
        $exito = true;
    } catch (Exception $e) {
        $conexion->rollback();
        $exito = false;
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Acción no válida'
    ]);
    exit();
}

// Ejecutar la consulta
if ($exito) {
    // Obtener el nuevo conteo directamente del campo conteo_likes
    $consulta_conteo = "SELECT conteo_likes FROM post_comunidad WHERE id_post = $post_id";
    $resultado_conteo = $conexion->query($consulta_conteo);
    $total_likes = $resultado_conteo->fetch_assoc()['conteo_likes'];
    
    echo json_encode([
        'success' => true,
        'message' => $accion === 'dar' ? 'Like agregado' : 'Like quitado',
        'total_likes' => $total_likes
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el like'
    ]);
}
?>