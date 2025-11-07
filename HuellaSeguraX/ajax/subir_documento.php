<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

include_once('../config/conexion.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_clean();

function responderJSON($success, $message, $extra = []) {
    ob_clean();
    $response = array_merge(['success' => $success, 'message' => $message], $extra);
    echo json_encode($response);
    exit();
}

// ============================================
// FUNCIONES DE SEGURIDAD
// ============================================

/**
 * Valida que el archivo sea realmente del tipo declarado
 * Verifica el MIME type real del archivo, no solo la extensión
 */
function validarTipoArchivoSeguro($tmp_name, $extension) {
    // Obtener el MIME type real del archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    
    // MIME types permitidos por extensión
    $mimes_permitidos = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png']
    ];
    
    // Verificar que el MIME type coincida con la extensión
    if (!isset($mimes_permitidos[$extension])) {
        return false;
    }
    
    if (!in_array($mime_type, $mimes_permitidos[$extension])) {
        return false;
    }
    
    return true;
}

/**
 * Escanea el archivo en busca de contenido malicioso
 */
function escanearArchivoMalicioso($filepath) {
    // Leer el contenido del archivo
    $contenido = file_get_contents($filepath);
    
    // Patrones sospechosos comunes en scripts maliciosos
    $patrones_maliciosos = [
        '/<\?php/i',           // Código PHP
        '/eval\s*\(/i',        // eval()
        '/base64_decode/i',    // base64_decode()
        '/system\s*\(/i',      // system()
        '/exec\s*\(/i',        // exec()
        '/passthru\s*\(/i',    // passthru()
        '/shell_exec/i',       // shell_exec()
        '/proc_open/i',        // proc_open()
        '/popen\s*\(/i',       // popen()
        '/curl_exec/i',        // curl_exec()
        '/curl_multi_exec/i',  // curl_multi_exec()
        '/parse_ini_file/i',   // parse_ini_file()
        '/show_source/i',      // show_source()
        '/<script/i',          // Scripts en imágenes (XSS)
        '/javascript:/i',      // JavaScript en imágenes
        '/on\w+\s*=/i'        // Eventos JavaScript (onclick, onerror, etc)
    ];
    
    foreach ($patrones_maliciosos as $patron) {
        if (preg_match($patron, $contenido)) {
            return true; // Archivo sospechoso
        }
    }
    
    return false; // Archivo limpio
}

/**
 * Valida imágenes específicamente
 */
function validarImagenSegura($filepath, $extension) {
    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
        return true; // No es imagen, validar con otros métodos
    }
    
    // Verificar que sea una imagen válida
    $image_info = @getimagesize($filepath);
    
    if ($image_info === false) {
        return false; // No es una imagen válida
    }
    
    // Verificar dimensiones razonables (prevenir DoS)
    $max_width = 10000;
    $max_height = 10000;
    
    if ($image_info[0] > $max_width || $image_info[1] > $max_height) {
        return false; // Imagen demasiado grande
    }
    
    return true;
}

/**
 * Sanitiza el nombre del archivo
 */
function sanitizarNombreArchivo($nombre) {
    // Eliminar caracteres peligrosos
    $nombre = preg_replace('/[^a-zA-Z0-9._-]/', '', $nombre);
    // Limitar longitud
    $nombre = substr($nombre, 0, 200);
    return $nombre;
}

// ============================================
// CÓDIGO PRINCIPAL
// ============================================

try {
    // Verificar autenticación
    if (!isset($_SESSION['rol']) || !isset($_SESSION['usuario_id'])) {
        responderJSON(false, 'No autenticado');
    }
    
    $usuario_id = intval($_SESSION['usuario_id']);
    $rol_usuario = $_SESSION['rol'];
    
    // Verificar rol
    if ($rol_usuario !== 'veterinario') {
        responderJSON(false, 'Solo los veterinarios pueden subir documentos');
    }
    
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderJSON(false, 'Método no permitido');
    }
    
    // Validar campos obligatorios
    $campos_requeridos = ['tipo_documento', 'id_mascota', 'titulo_documento', 'fecha_documento'];
    foreach ($campos_requeridos as $campo) {
        if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
            responderJSON(false, "Campo requerido: $campo");
        }
    }
    
    // Validar tipo de documento
    $tipos_permitidos = ['vacuna', 'analisis', 'receta'];
    $tipo_documento = trim($_POST['tipo_documento']);
    if (!in_array($tipo_documento, $tipos_permitidos)) {
        responderJSON(false, 'Tipo de documento no válido');
    }
    
    $tipo_documento = $conexion->real_escape_string($tipo_documento);
    $id_mascota = intval($_POST['id_mascota']);
    $titulo_documento = $conexion->real_escape_string(trim($_POST['titulo_documento']));
    $descripcion = $conexion->real_escape_string(trim($_POST['descripcion'] ?? ''));
    $fecha_documento = $conexion->real_escape_string($_POST['fecha_documento']);
    
    // Validar fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_documento)) {
        responderJSON(false, 'Formato de fecha inválido');
    }
    
    // Validar mascota existe
    $stmt = $conexion->prepare("SELECT id_mascota FROM mascotas WHERE id_mascota = ? AND estado = 'activo'");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 0) {
        responderJSON(false, 'Mascota no válida o inactiva');
    }
    $stmt->close();
    
    // ============================================
    // VALIDACIÓN EXHAUSTIVA DEL ARCHIVO
    // ============================================
    
    // Validar que existe el archivo
    if (!isset($_FILES['archivo_documento']) || $_FILES['archivo_documento']['error'] !== UPLOAD_ERR_OK) {
        responderJSON(false, 'Error al recibir el archivo');
    }
    
    $archivo = $_FILES['archivo_documento'];
    
    // Validar tamaño ANTES de procesarlo
    if ($archivo['size'] > 10485760) { // 10MB
        responderJSON(false, 'El archivo es demasiado grande. Máximo 10MB');
    }
    
    if ($archivo['size'] == 0) {
        responderJSON(false, 'El archivo está vacío');
    }
    
    // Obtener extensión
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    
    if (!in_array($extension, $extensiones_permitidas)) {
        responderJSON(false, 'Formato no permitido. Solo: PDF, DOC, DOCX, JPG, PNG');
    }
    
    // SEGURIDAD 1: Validar MIME type real del archivo
    if (!validarTipoArchivoSeguro($archivo['tmp_name'], $extension)) {
        responderJSON(false, 'El archivo no es del tipo declarado. Posible intento de falsificación.');
    }
    
    // SEGURIDAD 2: Escanear contenido malicioso
    if (escanearArchivoMalicioso($archivo['tmp_name'])) {
        responderJSON(false, 'El archivo contiene contenido sospechoso y fue rechazado por seguridad.');
    }
    
    // SEGURIDAD 3: Validación específica para imágenes
    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
        if (!validarImagenSegura($archivo['tmp_name'], $extension)) {
            responderJSON(false, 'La imagen no es válida o es demasiado grande.');
        }
    }
    
    // Crear directorio con permisos seguros
    $directorio_documentos = '../documentos/medicos';
    if (!file_exists($directorio_documentos)) {
        if (!mkdir($directorio_documentos, 0755, true)) {
            responderJSON(false, 'No se pudo crear la carpeta de documentos');
        }
    }
    
    // Crear .htaccess para prevenir ejecución de scripts
    $htaccess_path = $directorio_documentos . '/.htaccess';
    if (!file_exists($htaccess_path)) {
        $htaccess_content = "# Bloquear ejecución de scripts\n";
        $htaccess_content .= "php_flag engine off\n";
        $htaccess_content .= "AddType application/octet-stream .php .php3 .php4 .php5 .php6 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi\n";
        $htaccess_content .= "# Solo permitir descarga\n";
        $htaccess_content .= "<FilesMatch \"\\.(pdf|doc|docx|jpg|jpeg|png)$\">\n";
        $htaccess_content .= "    Header set Content-Disposition attachment\n";
        $htaccess_content .= "</FilesMatch>\n";
        file_put_contents($htaccess_path, $htaccess_content);
    }
    
    // SEGURIDAD 4: Generar nombre único y seguro
    $nombre_original_seguro = sanitizarNombreArchivo(pathinfo($archivo['name'], PATHINFO_FILENAME));
    $nombre_archivo = 'doc_' . $id_mascota . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $ruta_destino = $directorio_documentos . '/' . $nombre_archivo;
    
    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        responderJSON(false, 'Error al guardar el archivo en el servidor');
    }
    
    // SEGURIDAD 5: Cambiar permisos del archivo para que no sea ejecutable
    chmod($ruta_destino, 0644);
    
    // Obtener id_veterinario
    $stmt = $conexion->prepare("SELECT id_veterinario FROM veterinario WHERE id_usuario = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 0) {
        if (file_exists($ruta_destino)) unlink($ruta_destino);
        responderJSON(false, 'No estás registrado como veterinario');
    }
    
    $id_veterinario = $resultado->fetch_assoc()['id_veterinario'];
    $stmt->close();
    
    // Insertar historial médico
    $diagnostico = $titulo_documento;
    $tratamiento = $descripcion ?: 'Ver documento adjunto';
    
    $stmt = $conexion->prepare("INSERT INTO historiales_medicos (fecha, diagnostico, tratamiento, id_mascota, id_veterinario) 
                                VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $fecha_documento, $diagnostico, $tratamiento, $id_mascota, $id_veterinario);
    
    if (!$stmt->execute()) {
        if (file_exists($ruta_destino)) unlink($ruta_destino);
        responderJSON(false, 'Error al crear el registro médico');
    }
    
    $id_historial = $conexion->insert_id;
    $stmt->close();
    
    // Verificar ID válido
    if ($id_historial <= 0) {
        if (file_exists($ruta_destino)) unlink($ruta_destino);
        responderJSON(false, 'Error: No se generó ID de historial válido');
    }
    
    // Insertar documento
    $stmt = $conexion->prepare("INSERT INTO documento_medico (tipo, archivo, id_historial) 
                                VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $tipo_documento, $nombre_archivo, $id_historial);
    
    if (!$stmt->execute()) {
        // Rollback
        $conexion->query("DELETE FROM historiales_medicos WHERE id_historial = $id_historial");
        if (file_exists($ruta_destino)) unlink($ruta_destino);
        responderJSON(false, 'Error al guardar el documento en la base de datos');
    }
    
    $documento_id = $conexion->insert_id;
    $stmt->close();
    $conexion->close();
    
    // Respuesta exitosa
    responderJSON(true, '¡Documento subido exitosamente!', [
        'documento_id' => $documento_id,
        'nombre_archivo' => $nombre_archivo
    ]);
    
} catch (Exception $e) {
    // No revelar detalles técnicos al usuario
    error_log("Error en subir_documento.php: " . $e->getMessage());
    responderJSON(false, 'Error del servidor. Por favor intenta nuevamente.');
}
