<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mis-mascotas.php");
    exit();
}

$id_mascota = (int)$_POST['id_mascota'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar que la mascota pertenece al usuario
$stmt = $conexion->prepare("SELECT id_mascota FROM mascotas WHERE id_mascota = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_mascota, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: mis-mascotas.php?error=no_autorizado");
    exit();
}

// Obtener datos del formulario
$nombre = $conexion->real_escape_string(trim($_POST['nombre_mascota']));
$tipo = $_POST['tipo'];
$sexo = $_POST['sexo'];
$cumpleanos = $_POST['cumpleanos_mascota'];
$peso_actual = !empty($_POST['peso_actual']) ? (float)$_POST['peso_actual'] : null;

// Calcular edad
$fecha_nac = new DateTime($cumpleanos);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_nac)->y;

// Procesar foto si se subió una nueva
$foto_mascota = '';
if (isset($_FILES['foto_mascota']) && $_FILES['foto_mascota']['error'] === 0) {
    $extension = strtolower(pathinfo($_FILES['foto_mascota']['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($extension, $extensiones_permitidas) && $_FILES['foto_mascota']['size'] <= 5000000) {
        $nombre_archivo = 'mascota_' . $usuario_id . '_' . time() . '.' . $extension;
        $ruta_destino = 'imagenes/' . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['foto_mascota']['tmp_name'], $ruta_destino)) {
            $foto_mascota = $nombre_archivo;
        }
    }
}

// Actualizar mascota
if (!empty($foto_mascota)) {
    $stmt2 = $conexion->prepare("UPDATE mascotas SET nombre_mascota = ?, tipo = ?, sexo = ?, cumpleaños_mascota = ?, edad_mascota = ?, foto_mascota = ? WHERE id_mascota = ?");
    $stmt2->bind_param("ssssssi", $nombre, $tipo, $sexo, $cumpleanos, $edad, $foto_mascota, $id_mascota);
} else {
    $stmt2 = $conexion->prepare("UPDATE mascotas SET nombre_mascota = ?, tipo = ?, sexo = ?, cumpleaños_mascota = ?, edad_mascota = ? WHERE id_mascota = ?");
    $stmt2->bind_param("sssssi", $nombre, $tipo, $sexo, $cumpleanos, $edad, $id_mascota);
}

if ($stmt2->execute()) {
    // Actualizar peso en ficha de salud si se proporcionó
    if ($peso_actual !== null) {
        // Verificar si existe ficha
        $stmt3 = $conexion->prepare("SELECT id_ficha FROM fichas_de_salud WHERE id_mascota = ?");
        $stmt3->bind_param("i", $id_mascota);
        $stmt3->execute();
        $resultado_ficha = $stmt3->get_result();
        
        if ($resultado_ficha->num_rows > 0) {
            // Actualizar ficha existente
            $ficha = $resultado_ficha->fetch_assoc();
            $stmt4 = $conexion->prepare("UPDATE fichas_de_salud SET peso = ? WHERE id_ficha = ?");
            $stmt4->bind_param("di", $peso_actual, $ficha['id_ficha']);
            $stmt4->execute();
        } else {
            // Crear nueva ficha
            $stmt4 = $conexion->prepare("INSERT INTO fichas_de_salud (vacunas, esterilizado, peso, altura, documento, id_mascota) VALUES ('', 0, ?, 0, '', ?)");
            $stmt4->bind_param("di", $peso_actual, $id_mascota);
            $stmt4->execute();
        }
    }
    
    header("Location: perfil-mascota.php?id=$id_mascota&exito=perfil_actualizado");
} else {
    header("Location: perfil-mascota.php?id=$id_mascota&error=error_actualizar");
}

$stmt->close();
$stmt2->close();
cerrarConexion();
exit();
?>