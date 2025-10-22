<?php
include_once('config/conexion.php');
session_start();

// Verificar que es un veterinario
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: veterinaria.php?error=no_autorizado");
    exit();
}

$veterinario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos de la mascota
    $nombre_mascota = $conexion->real_escape_string(trim($_POST['nombre_mascota']));
    $tipo = $conexion->real_escape_string($_POST['tipo']);
    $sexo = $conexion->real_escape_string($_POST['sexo']);
    $edad_mascota = intval($_POST['edad_mascota']);
    $cumpleanos_mascota = !empty($_POST['cumpleanos_mascota']) ? $_POST['cumpleanos_mascota'] : date('Y-m-d');
    
    // Determinar el dueño
    $id_dueno = null;
    $id_dueno_existente = !empty($_POST['id_dueno_existente']) ? intval($_POST['id_dueno_existente']) : null;
    
    if ($id_dueno_existente) {
        // Usar dueño existente
        $id_dueno = $id_dueno_existente;
    } else {
        // Crear nuevo dueño
        $nombre_dueno = $conexion->real_escape_string(trim($_POST['nombre_dueno']));
        $apellido_dueno = $conexion->real_escape_string(trim($_POST['apellido_dueno']));
        $email_dueno = $conexion->real_escape_string(trim($_POST['email_dueno']));
        $password_dueno = $conexion->real_escape_string($_POST['password_dueno']);
        $telefono_dueno = !empty($_POST['telefono_dueno']) ? $conexion->real_escape_string(trim($_POST['telefono_dueno'])) : '';
        
        // Validar campos obligatorios
        if (empty($nombre_dueno) || empty($apellido_dueno) || empty($email_dueno) || empty($password_dueno)) {
            header("Location: veterinaria.php?error=datos_dueno_incompletos");
            exit();
        }
        
        // Verificar que el email no exista
        $consulta_email = "SELECT id_usuario FROM usuarios WHERE email_usuario = '$email_dueno'";
        $resultado_email = $conexion->query($consulta_email);
        
        if ($resultado_email->num_rows > 0) {
            header("Location: veterinaria.php?error=email_existe");
            exit();
        }
        
        // Crear el usuario dueño
        $consulta_crear_dueno = "INSERT INTO usuarios (email_usuario, `contraseña_usuario`, telefono_usuario, nombre_usuario, apellido_usuario, foto_usuario, estado, rol) 
                                VALUES ('$email_dueno', '$password_dueno', '$telefono_dueno', '$nombre_dueno', '$apellido_dueno', 'usuario-default.jpg', 'activo', 'usuario')";
        
        if ($conexion->query($consulta_crear_dueno)) {
            $id_dueno = $conexion->insert_id;
        } else {
            header("Location: veterinaria.php?error=error_crear_dueno");
            exit();
        }
    }
    
    // Procesar imagen
    $nombre_foto = 'mascota-default.jpg';
    
    if (isset($_FILES['foto_mascota']) && $_FILES['foto_mascota']['error'] === UPLOAD_ERR_OK) {
        $archivo_tmp = $_FILES['foto_mascota']['tmp_name'];
        $nombre_original = $_FILES['foto_mascota']['name'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extension, $extensiones_permitidas)) {
            $nombre_foto = 'mascota_' . $id_dueno . '_' . time() . '.' . $extension;
            $ruta_destino = 'imagenes/' . $nombre_foto;
            
            if (!move_uploaded_file($archivo_tmp, $ruta_destino)) {
                $nombre_foto = 'mascota-default.jpg';
            }
        }
    }
    
    // Insertar la mascota
    $consulta_mascota = "INSERT INTO mascotas (id_usuario, tipo, sexo, nombre_mascota, edad_mascota, `cumpleaños_mascota`, foto_mascota, estado) 
                        VALUES ($id_dueno, '$tipo', '$sexo', '$nombre_mascota', $edad_mascota, '$cumpleanos_mascota', '$nombre_foto', 'activo')";
    
    if ($conexion->query($consulta_mascota)) {
        $mensaje_exito = $id_dueno_existente ? "paciente_agregado" : "paciente_y_dueno_creados";
        header("Location: veterinaria.php?exito=$mensaje_exito&nombre=" . urlencode($nombre_mascota));
        exit();
    } else {
        header("Location: veterinaria.php?error=error_agregar_mascota");
        exit();
    }
} else {
    $conexion->close();
    header("Location: veterinaria.php");
    exit();
}
?>