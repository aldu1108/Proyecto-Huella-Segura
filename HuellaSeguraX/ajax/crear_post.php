<?php
// ajax/crear_post.php
include_once('../config/conexion.php');
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['rol']) || !isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'];

// Verificar que no sea usuario demo
if ($rol_usuario === 'demo') {
    echo json_encode(['success' => false, 'message' => 'Los usuarios demo no pueden crear posts']);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Validar que vengan los datos obligatorios
if (empty($_POST['titulo_post']) || empty($_POST['contenido_post']) || empty($_POST['tipo_post'])) {
    echo json_encode(['success' => false, 'message' => 'Por favor completa todos los campos obligatorios']);
    exit();
}

$titulo = $conexion->real_escape_string(trim($_POST['titulo_post']));
$contenido = $conexion->real_escape_string(trim($_POST['contenido_post']));
$tipo_post = $conexion->real_escape_string($_POST['tipo_post']);
$imagenes = [];

// Crear directorio si no existe
if (!file_exists('../imagenes/posts')) {
    if (!mkdir('../imagenes/posts', 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Error: No se pudo crear la carpeta de imágenes']);
        exit();
    }
}

// Procesar múltiples imágenes
if (isset($_FILES['imagenes_post']) && !empty($_FILES['imagenes_post']['name'][0])) {
    $total_imagenes = count($_FILES['imagenes_post']['name']);
    
    for ($i = 0; $i < $total_imagenes && $i < 5; $i++) { // Máximo 5 imágenes
        if ($_FILES['imagenes_post']['error'][$i] === 0) {
            $extension = strtolower(pathinfo($_FILES['imagenes_post']['name'][$i], PATHINFO_EXTENSION));
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extension, $extensiones_permitidas) && $_FILES['imagenes_post']['size'][$i] <= 5000000) {
                $nombre_archivo = 'post_' . $usuario_id . '_' . time() . '_' . $i . '.' . $extension;
                $ruta_destino = '../imagenes/posts/' . $nombre_archivo;
                
                if (move_uploaded_file($_FILES['imagenes_post']['tmp_name'][$i], $ruta_destino)) {
                    $imagenes[] = $nombre_archivo;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al subir la imagen ' . ($i + 1)]);
                    exit();
                }
            }
        }
    }
}

// Preparar cadena de imágenes
$imagenes_str = !empty($imagenes) ? implode(',', $imagenes) : NULL;

// Insertar post en la base de datos
$sql_insert = "INSERT INTO post_comunidad (titulo, contenido, fecha, id_usuario, tipo_post, imagen_post) 
               VALUES ('$titulo', '$contenido', NOW(), $usuario_id, '$tipo_post', " . 
               ($imagenes_str ? "'$imagenes_str'" : "NULL") . ")";

if ($conexion->query($sql_insert)) {
    echo json_encode([
        'success' => true, 
        'message' => '¡Post publicado exitosamente!',
        'post_id' => $conexion->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Error al publicar el post en la base de datos: ' . $conexion->error
    ]);
}
?>