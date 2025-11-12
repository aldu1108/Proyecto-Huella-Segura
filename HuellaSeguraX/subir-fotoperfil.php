<?php
session_start();
include_once("../config/conexion.php");

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$response = ['success' => false, 'message' => '', 'foto_url' => ''];

// Verificar que se subió un archivo
if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
    $error_msg = 'Error al subir el archivo';

    if (isset($_FILES['foto_perfil']['error'])) {
        switch ($_FILES['foto_perfil']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_msg = 'El archivo es demasiado grande';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_msg = 'El archivo se subió parcialmente';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_msg = 'No se seleccionó ningún archivo';
                break;
            default:
                $error_msg = 'Error desconocido al subir el archivo';
        }
    }

    $response['message'] = $error_msg;
    echo json_encode($response);
    exit();
}

$archivo = $_FILES['foto_perfil'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

// Validar extensión
if (!in_array($extension, $extensiones_permitidas)) {
    $response['message'] = 'Solo se permiten imágenes JPG, JPEG, PNG o WEBP';
    echo json_encode($response);
    exit();
}

// Validar tamaño (máximo 5MB)
$tamaño_maximo = 5 * 1024 * 1024; // 5MB
if ($archivo['size'] > $tamaño_maximo) {
    $response['message'] = 'La imagen no debe superar los 5MB';
    echo json_encode($response);
    exit();
}

// Validar que sea imagen real
$check_imagen = @getimagesize($archivo['tmp_name']);
if ($check_imagen === false) {
    $response['message'] = 'El archivo no es una imagen válida';
    echo json_encode($response);
    exit();
}

// Rutas
$directorio_servidor = '../imagenes/pfp/';
$ruta_web_base = 'imagenes/pfp/';

// Crear directorio si no existe
if (!file_exists($directorio_servidor)) {
    if (!mkdir($directorio_servidor, 0755, true)) {
        $response['message'] = 'Error al crear el directorio de imágenes';
        echo json_encode($response);
        exit();
    }
}

// Obtener foto anterior
$consulta_foto_anterior = "SELECT foto_usuario FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($consulta_foto_anterior);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$foto_anterior = '';

if ($resultado && $resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();
    $foto_anterior = $datos['foto_usuario'];
}
$stmt->close();

// Generar nombre único
$nombre_archivo = 'pfp_' . $usuario_id . '_' . time() . '.' . $extension;
$ruta_fisica = $directorio_servidor . $nombre_archivo;
$ruta_web = $ruta_web_base . $nombre_archivo;

// Mover archivo
if (move_uploaded_file($archivo['tmp_name'], $ruta_fisica)) {
    // Actualizar BD
    $consulta_actualizar = "UPDATE usuarios SET foto_usuario = ? WHERE id_usuario = ?";
    $stmt = $conexion->prepare($consulta_actualizar);
    $stmt->bind_param("si", $ruta_web, $usuario_id);

    if ($stmt->execute()) {
        // Eliminar foto anterior (si existe y no es default)
        $fotos_default = ['usuario-default.jpg', 'imagenes/pfp/default.jpg', 'veterinario-default.jpg'];

        if (
            !empty($foto_anterior) &&
            !in_array($foto_anterior, $fotos_default) &&
            file_exists('../' . $foto_anterior)
        ) {
            @unlink('../' . $foto_anterior);
        }

        $response['success'] = true;
        $response['message'] = 'Foto de perfil actualizada correctamente';
        $response['foto_url'] = $ruta_web;

        // Actualizar variable de sesión
        $_SESSION['usuario_foto'] = $ruta_web;
    } else {
        if (file_exists($ruta_fisica)) {
            @unlink($ruta_fisica);
        }
        $response['message'] = 'Error al actualizar la base de datos: ' . $conexion->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Error al guardar el archivo en el servidor';
}

echo json_encode($response);
$conexion->close();
?>