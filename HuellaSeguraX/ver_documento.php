<?php
session_start();
include_once('config/conexion.php');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);
$rol_usuario = $_SESSION['rol'];

// Verificar que se envió el archivo
if (!isset($_GET['archivo'])) {
    die('Archivo no especificado');
}

$nombre_archivo = basename($_GET['archivo']); // Prevenir directory traversal
$ruta_archivo = 'documentos/medicos/' . $nombre_archivo;

// Verificar que el archivo existe
if (!file_exists($ruta_archivo)) {
    die('Archivo no encontrado');
}

// SEGURIDAD: Verificar que el usuario tiene permiso para ver este documento
$permitido = false;

if ($rol_usuario === 'veterinario') {
    // Veterinarios pueden ver todos los documentos
    $permitido = true;
} else {
    // Usuarios normales solo pueden ver documentos de sus mascotas
    $consulta_verificar = "SELECT dm.id_documento 
                          FROM documento_medico dm
                          JOIN historiales_medicos h ON dm.id_historial = h.id_historial
                          JOIN mascotas m ON h.id_mascota = m.id_mascota
                          WHERE dm.archivo = ? AND m.id_usuario = ?";
    
    $stmt = $conexion->prepare($consulta_verificar);
    $stmt->bind_param("si", $nombre_archivo, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $permitido = true;
    }
    $stmt->close();
}

if (!$permitido) {
    die('No tienes permisos para acceder a este documento');
}

// Obtener extensión del archivo
$extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

// Establecer el tipo MIME correcto
$mime_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png'
];

$content_type = $mime_types[$extension] ?? 'application/octet-stream';

// Verificar si es descarga o visualización
$descargar = isset($_GET['descargar']) && $_GET['descargar'] == '1';

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Content-Type: ' . $content_type);
header('Content-Length: ' . filesize($ruta_archivo));

if ($descargar) {
    // Forzar descarga
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
} else {
    // Mostrar en el navegador (si es posible)
    if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
        header('Content-Disposition: inline; filename="' . $nombre_archivo . '"');
    } else {
        // Para DOC/DOCX forzar descarga
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    }
}

// Limpiar buffer de salida
ob_clean();
flush();

// Enviar archivo
readfile($ruta_archivo);
exit();
