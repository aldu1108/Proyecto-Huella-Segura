<?php
// Agendar cita para un paciente específico (veterinario)
include_once('config/conexion.php');
session_start();

// Verificar que el usuario esté logueado y sea veterinario
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: veterinaria.php?error=no_autorizado");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener id_veterinario
$consulta_vet_id = "SELECT id_veterinario FROM veterinario WHERE id_usuario = $usuario_id";
$resultado_vet_id = $conexion->query($consulta_vet_id);

if (!$resultado_vet_id || $resultado_vet_id->num_rows == 0) {
    header("Location: veterinaria.php?error=no_es_veterinario");
    exit();
}

$id_veterinario_actual = $resultado_vet_id->fetch_assoc()['id_veterinario'];

// Solo procesar si vienen datos por POST
if ($_POST) {
    $id_mascota = intval($_POST['id_mascota']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = strip_tags($_POST['motivo']);
    $clinica = strip_tags($_POST['clinica'] ?? '');
    $observaciones = strip_tags($_POST['observaciones'] ?? '');

    // Validaciones básicas
    if (empty($id_mascota) || empty($fecha) || empty($hora) || empty($motivo)) {
        header("Location: veterinaria.php?error=datos_cita_incompletos&seccion=pacientes");
        exit();
    }

    // VALIDACIÓN: No permitir fechas pasadas
    $fecha_hoy = date('Y-m-d');
    if ($fecha < $fecha_hoy) {
        header("Location: veterinaria.php?error=fecha_pasada&seccion=pacientes");
        exit();
    }

    // Verificar que la mascota exista
    $verificar_mascota = "SELECT id_mascota FROM mascotas WHERE id_mascota = $id_mascota";
    $resultado_verificacion = $conexion->query($verificar_mascota);

    if ($resultado_verificacion->num_rows == 0) {
        header("Location: veterinaria.php?error=mascota_no_valida&seccion=pacientes");
        exit();
    }

    // Combinar fecha y hora
    $fecha_completa = $fecha . ' ' . $hora . ':00';

    // Insertar la cita directamente como aceptada y con el veterinario asignado
    $consulta = "INSERT INTO citas_veterinarias (fecha, motivo, estado, id_mascota, id_veterinario) 
                VALUES ('$fecha_completa', '$motivo', 'aceptada', $id_mascota, $id_veterinario_actual)";

    if ($conexion->query($consulta)) {
        // Redirigir con mensaje de éxito a la sección de pacientes
        header("Location: veterinaria.php?exito=cita_agendada&seccion=pacientes");
    } else {
        header("Location: veterinaria.php?error=error_agendar_cita&seccion=pacientes");
    }
} else {
    header("Location: veterinaria.php?seccion=pacientes");
}

cerrarConexion();
?>