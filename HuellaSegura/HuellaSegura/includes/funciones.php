<?php
// Funciones auxiliares para Huella Segura

// Función para verificar si el usuario está logueado
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Función para limpiar datos de entrada (seguridad básica)
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    return $datos;
}

// Función para validar email básico
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para formatear fecha en español
function formatearFecha($fecha) {
    $meses = array(
        'January' => 'Enero',
        'February' => 'Febrero', 
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre'
    );
    
    $fecha_formateada = date('j \d\e F \d\e Y', strtotime($fecha));
    return str_replace(array_keys($meses), array_values($meses), $fecha_formateada);
}

// Función para calcular edad en años
function calcularEdad($fecha_nacimiento) {
    $hoy = new DateTime();
    $nacimiento = new DateTime($fecha_nacimiento);
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}

// Función para calcular días transcurridos
function diasTranscurridos($fecha) {
    $hoy = new DateTime();
    $fecha_dada = new DateTime($fecha);
    $diferencia = $hoy->diff($fecha_dada);
    return $diferencia->days;
}

// Función para obtener información básica del usuario
function obtenerUsuario($usuario_id, $conexion) {
    $consulta = "SELECT * FROM usuarios WHERE id_usuario = $usuario_id";
    $resultado = $conexion->query($consulta);
    return $resultado->fetch_assoc();
}

// Función para obtener mascotas de un usuario
function obtenerMascotasUsuario($usuario_id, $conexion) {
    $consulta = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo' ORDER BY nombre_mascota ASC";
    $resultado = $conexion->query($consulta);
    return $resultado;
}

// Función para contar mascotas de un usuario
function contarMascotasUsuario($usuario_id, $conexion) {
    $consulta = "SELECT COUNT(*) as total FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
    $resultado = $conexion->query($consulta);
    $fila = $resultado->fetch_assoc();
    return $fila['total'];
}

// Función para obtener próximas citas veterinarias
function obtenerProximasCitas($usuario_id, $conexion, $limite = 5) {
    $fecha_hoy = date('Y-m-d');
    $consulta = "SELECT c.*, m.nombre_mascota, v.clinica 
                 FROM citas_veterinarias c 
                 JOIN mascotas m ON c.id_mascota = m.id_mascota 
                 LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                 WHERE m.id_usuario = $usuario_id AND c.fecha >= '$fecha_hoy' 
                 ORDER BY c.fecha ASC LIMIT $limite";
    $resultado = $conexion->query($consulta);
    return $resultado;
}

// Función para obtener eventos próximos del usuario
function obtenerProximosEventos($usuario_id, $conexion, $limite = 5) {
    $fecha_hoy = date('Y-m-d');
    $consulta = "SELECT * FROM eventos 
                 WHERE id_usuario = $usuario_id AND fecha >= '$fecha_hoy' 
                 ORDER BY fecha ASC LIMIT $limite";
    $resultado = $conexion->query($consulta);
    return $resultado;
}

// Función para generar un mensaje de tiempo transcurrido
function tiempoTranscurrido($fecha) {
    $tiempo = time() - strtotime($fecha);
    
    if ($tiempo < 60) {
        return 'Hace unos segundos';
    } elseif ($tiempo < 3600) {
        $minutos = floor($tiempo / 60);
        return "Hace $minutos minuto" . ($minutos > 1 ? 's' : '');
    } elseif ($tiempo < 86400) {
        $horas = floor($tiempo / 3600);
        return "Hace $horas hora" . ($horas > 1 ? 's' : '');
    } elseif ($tiempo < 2592000) {
        $dias = floor($tiempo / 86400);
        return "Hace $dias día" . ($dias > 1 ? 's' : '');
    } else {
        return formatearFecha($fecha);
    }
}

// Función para subir imagen con validación básica
function subirImagen($archivo, $directorio = 'imagenes/') {
    $extensiones_permitidas = array('jpg', 'jpeg', 'png', 'gif');
    $tamaño_maximo = 5000000; // 5MB
    
    $nombre_archivo = $archivo['name'];
    $tamaño_archivo = $archivo['size'];
    $archivo_temporal = $archivo['tmp_name'];
    $error_archivo = $archivo['error'];
    
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    
    if ($error_archivo !== 0) {
        return array('success' => false, 'message' => 'Error al subir el archivo');
    }
    
    if (!in_array($extension, $extensiones_permitidas)) {
        return array('success' => false, 'message' => 'Tipo de archivo no permitido');
    }
    
    if ($tamaño_archivo > $tamaño_maximo) {
        return array('success' => false, 'message' => 'El archivo es demasiado grande');
    }
    
    $nuevo_nombre = 'imagen_' . time() . '.' . $extension;
    $ruta_destino = $directorio . $nuevo_nombre;
    
    if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
        return array('success' => true, 'filename' => $nuevo_nombre);
    } else {
        return array('success' => false, 'message' => 'Error al mover el archivo');
    }
}

// Función para obtener estadísticas básicas de la comunidad
function obtenerEstadisticasComunidad($conexion) {
    $estadisticas = array();
    
    // Total usuarios
    $consulta = "SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'";
    $resultado = $conexion->query($consulta);
    $estadisticas['usuarios'] = $resultado->fetch_assoc()['total'];
    
    // Total mascotas
    $consulta = "SELECT COUNT(*) as total FROM mascotas WHERE estado = 'activo'";
    $resultado = $conexion->query($consulta);
    $estadisticas['mascotas'] = $resultado->fetch_assoc()['total'];
    
    // Posts de hoy
    $fecha_hoy = date('Y-m-d');
    $consulta = "SELECT COUNT(*) as total FROM post_comunidad WHERE fecha = '$fecha_hoy'";
    $resultado = $conexion->query($consulta);
    $estadisticas['posts_hoy'] = $resultado->fetch_assoc()['total'];
    
    return $estadisticas;
}

// Función para generar contraseña temporal
function generarContraseñaTemporal($longitud = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $contraseña = '';
    
    for ($i = 0; $i < $longitud; $i++) {
        $contraseña .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $contraseña;
}

// Función para enviar notificación simple (simulada)
function enviarNotificacion($usuario_id, $mensaje, $tipo = 'info') {
    // En una implementación real, aquí enviarías email, push notification, etc.
    // Por ahora solo guardamos en sesión para mostrar como mensaje
    if (!isset($_SESSION['notificaciones'])) {
        $_SESSION['notificaciones'] = array();
    }
    
    $_SESSION['notificaciones'][] = array(
        'mensaje' => $mensaje,
        'tipo' => $tipo,
        'fecha' => date('Y-m-d H:i:s')
    );
}

// Función para obtener notificaciones del usuario
function obtenerNotificaciones() {
    if (isset($_SESSION['notificaciones'])) {
        $notificaciones = $_SESSION['notificaciones'];
        // Limpiar notificaciones después de obtenerlas
        $_SESSION['notificaciones'] = array();
        return $notificaciones;
    }
    return array();
}

// Función para verificar si es un veterinario
function esVeterinario($usuario_id, $conexion) {
    $consulta = "SELECT id_veterinario FROM veterinario WHERE id_usuario = $usuario_id";
    $resultado = $conexion->query($consulta);
    return $resultado && $resultado->num_rows > 0;
}

// Función para obtener información del veterinario
function obtenerVeterinario($usuario_id, $conexion) {
    $consulta = "SELECT v.*, u.nombre_usuario, u.apellido_usuario 
                 FROM veterinario v 
                 JOIN usuarios u ON v.id_usuario = u.id_usuario 
                 WHERE v.id_usuario = $usuario_id";
    $resultado = $conexion->query($consulta);
    return $resultado->fetch_assoc();
}

// Función para crear slug de URL amigable
function crearSlug($texto) {
    // Convertir a minúsculas
    $texto = strtolower($texto);
    
    // Reemplazar caracteres especiales
    $caracteres_especiales = array(
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ñ' => 'n', 'ü' => 'u'
    );
    
    $texto = str_replace(array_keys($caracteres_especiales), array_values($caracteres_especiales), $texto);
    
    // Reemplazar espacios y caracteres no alfanuméricos con guiones
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    
    // Eliminar guiones al inicio y final
    $texto = trim($texto, '-');
    
    return $texto;
}

// Función para paginar resultados
function paginar($consulta_base, $pagina_actual = 1, $resultados_por_pagina = 10, $conexion) {
    $offset = ($pagina_actual - 1) * $resultados_por_pagina;
    
    // Obtener total de resultados
    $consulta_total = "SELECT COUNT(*) as total FROM ($consulta_base) as subconsulta";
    $resultado_total = $conexion->query($consulta_total);
    $total_resultados = $resultado_total->fetch_assoc()['total'];
    
    // Calcular total de páginas
    $total_paginas = ceil($total_resultados / $resultados_por_pagina);
    
    // Obtener resultados de la página actual
    $consulta_paginada = "$consulta_base LIMIT $offset, $resultados_por_pagina";
    $resultados = $conexion->query($consulta_paginada);
    
    return array(
        'resultados' => $resultados,
        'pagina_actual' => $pagina_actual,
        'total_paginas' => $total_paginas,
        'total_resultados' => $total_resultados,
        'resultados_por_pagina' => $resultados_por_pagina
    );
}

// Función para generar HTML de paginación
function generarPaginacion($pagina_actual, $total_paginas, $url_base) {
    if ($total_paginas <= 1) return '';
    
    $html = '<div class="paginacion">';
    
    // Botón anterior
    if ($pagina_actual > 1) {
        $pagina_anterior = $pagina_actual - 1;
        $html .= "<a href='$url_base?pagina=$pagina_anterior' class='boton-paginacion'>← Anterior</a>";
    }
    
    // Números de página
    for ($i = 1; $i <= $total_paginas; $i++) {
        if ($i == $pagina_actual) {
            $html .= "<span class='pagina-actual'>$i</span>";
        } else {
            $html .= "<a href='$url_base?pagina=$i' class='numero-pagina'>$i</a>";
        }
    }
    
    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        $pagina_siguiente = $pagina_actual + 1;
        $html .= "<a href='$url_base?pagina=$pagina_siguiente' class='boton-paginacion'>Siguiente →</a>";
    }
    
    $html .= '</div>';
    return $html;
}

// Función para log de actividades básico
function registrarActividad($usuario_id, $accion, $detalles = '', $conexion = null) {
    // Crear tabla de logs si no existe (implementación muy básica)
    $fecha_hora = date('Y-m-d H:i:s');
    
    // En una implementación real, aquí insertarías en una tabla de logs
    // Por ahora solo guardamos en archivo de log simple
    $log_mensaje = "[$fecha_hora] Usuario $usuario_id: $accion - $detalles\n";
    file_put_contents('logs/actividad.log', $log_mensaje, FILE_APPEND | LOCK_EX);
}

// Función para validar datos de mascota
function validarDatosMascota($datos) {
    $errores = array();
    
    if (empty($datos['nombre_mascota'])) {
        $errores[] = 'El nombre de la mascota es obligatorio';
    }
    
    if (empty($datos['tipo'])) {
        $errores[] = 'El tipo de mascota es obligatorio';
    }
    
    if (empty($datos['sexo'])) {
        $errores[] = 'El sexo de la mascota es obligatorio';
    }
    
    if (!is_numeric($datos['edad_mascota']) || $datos['edad_mascota'] < 0 || $datos['edad_mascota'] > 30) {
        $errores[] = 'La edad debe ser un número entre 0 y 30';
    }
    
    return $errores;
}

// Función para obtener tipos de mascotas disponibles
function obtenerTiposMascotas() {
    return array(
        'perro' => 'Perro',
        'gato' => 'Gato',
        'ave' => 'Ave',
        'pez' => 'Pez',
        'hamster' => 'Hámster',
        'conejo' => 'Conejo',
        'reptil' => 'Reptil',
        'otro' => 'Otro'
    );
}

// Función para obtener razas por tipo de mascota (básico)
function obtenerRazasPorTipo($tipo) {
    $razas = array(
        'perro' => array('Golden Retriever', 'Pastor Alemán', 'Labrador', 'Bulldog', 'Chihuahua', 'Mestizo'),
        'gato' => array('Siamés', 'Persa', 'Maine Coon', 'Angora', 'Mestizo'),
        'ave' => array('Canario', 'Periquito', 'Loro', 'Ninfa'),
        'otro' => array('No especificado')
    );
    
    return isset($razas[$tipo]) ? $razas[$tipo] : array('No especificado');
}

// Función para crear miniatura de imagen
function crearMiniatura($imagen_origen, $ancho_max = 300, $alto_max = 300) {
    $info_imagen = getimagesize($imagen_origen);
    
    if (!$info_imagen) return false;
    
    $ancho_original = $info_imagen[0];
    $alto_original = $info_imagen[1];
    $tipo_imagen = $info_imagen[2];
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($ancho_max / $ancho_original, $alto_max / $alto_original);
    $nuevo_ancho = round($ancho_original * $ratio);
    $nuevo_alto = round($alto_original * $ratio);
    
    // Crear imagen desde archivo según el tipo
    switch ($tipo_imagen) {
        case IMAGETYPE_JPEG:
            $imagen_src = imagecreatefromjpeg($imagen_origen);
            break;
        case IMAGETYPE_PNG:
            $imagen_src = imagecreatefrompng($imagen_origen);
            break;
        case IMAGETYPE_GIF:
            $imagen_src = imagecreatefromgif($imagen_origen);
            break;
        default:
            return false;
    }
    
    // Crear imagen de destino
    $imagen_destino = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
    
    // Mantener transparencia para PNG
    if ($tipo_imagen == IMAGETYPE_PNG) {
        imagealphablending($imagen_destino, false);
        imagesavealpha($imagen_destino, true);
    }
    
    // Redimensionar imagen
    imagecopyresampled(
        $imagen_destino, $imagen_src, 
        0, 0, 0, 0, 
        $nuevo_ancho, $nuevo_alto, 
        $ancho_original, $alto_original
    );
    
    return $imagen_destino;
}

// Función para formatear bytes a tamaño legible
function formatearBytes($bytes, $precision = 2) {
    $unidades = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $bytes > 1024 && $i < count($unidades) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $unidades[$i];
}

// Función para generar código QR simple (placeholder)
function generarCodigoQR($texto, $tamaño = 200) {
    // En una implementación real usarías una librería como phpqrcode
    // Por ahora retornamos un placeholder
    return "https://api.qrserver.com/v1/create-qr-code/?size={$tamaño}x{$tamaño}&data=" . urlencode($texto);
}

// Función para enviar email básico (simulado)
function enviarEmail($destinatario, $asunto, $mensaje, $remitente = 'noreply@huellasegura.com') {
    // En una implementación real usarías PHPMailer o mail()
    // Por ahora solo simulamos el envío
    
    registrarActividad(0, 'EMAIL_ENVIADO', "Para: $destinatario, Asunto: $asunto");
    
    return array(
        'success' => true,
        'message' => 'Email enviado correctamente'
    );
}

// Función para backup básico de datos
function crearBackup($conexion, $directorio = 'backups/') {
    $fecha = date('Y-m-d_H-i-s');
    $archivo_backup = $directorio . "backup_$fecha.sql";
    
    // Crear directorio si no existe
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }
    
    // Obtener lista de tablas
    $tablas = array();
    $resultado = $conexion->query("SHOW TABLES");
    while ($fila = $resultado->fetch_row()) {
        $tablas[] = $fila[0];
    }
    
    $contenido_sql = '';
    
    foreach ($tablas as $tabla) {
        // Estructura de la tabla
        $resultado = $conexion->query("SHOW CREATE TABLE $tabla");
        $fila = $resultado->fetch_row();
        $contenido_sql .= "\n\n" . $fila[1] . ";\n\n";
        
        // Datos de la tabla
        $resultado = $conexion->query("SELECT * FROM $tabla");
        while ($fila = $resultado->fetch_assoc()) {
            $contenido_sql .= "INSERT INTO $tabla VALUES (";
            $valores = array();
            foreach ($fila as $valor) {
                $valores[] = "'" . $conexion->real_escape_string($valor) . "'";
            }
            $contenido_sql .= implode(',', $valores) . ");\n";
        }
    }
    
    file_put_contents($archivo_backup, $contenido_sql);
    
    return array(
        'success' => true,
        'archivo' => $archivo_backup,
        'tamaño' => formatearBytes(filesize($archivo_backup))
    );
}

// Función para obtener configuración de la aplicación
function obtenerConfiguracion($clave = null) {
    $configuracion = array(
        'nombre_app' => 'Huella Segura',
        'version' => '1.0.0',
        'mantenimiento' => false,
        'registro_abierto' => true,
        'max_mascotas_usuario' => 10,
        'max_tamaño_foto' => 5000000, // 5MB
        'email_soporte' => 'ayuda@huellasegura.com',
        'telefono_emergencia' => '911-PET-HELP'
    );
    
    return $clave ? (isset($configuracion[$clave]) ? $configuracion[$clave] : null) : $configuracion;
}

// Función para debug (solo en desarrollo)
function debug($variable, $etiqueta = 'DEBUG') {
    if (obtenerConfiguracion('modo_debug')) {
        echo "<div style='background: #f0f0f0; border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
        echo "<strong>$etiqueta:</strong><br>";
        echo "<pre>" . print_r($variable, true) . "</pre>";
        echo "</div>";
    }
}

// Función para obtener IP del usuario
function obtenerIPUsuario() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'desconocida';
}

// Función para mostrar mensajes de éxito/error
function mostrarMensaje($tipo, $mensaje) {
    $clases = array(
        'success' => 'mensaje-exito',
        'error' => 'mensaje-error',
        'warning' => 'mensaje-advertencia',
        'info' => 'mensaje-info'
    );
    
    $clase = isset($clases[$tipo]) ? $clases[$tipo] : 'mensaje-info';
    
    return "<div class='$clase'>$mensaje</div>";
}

?>