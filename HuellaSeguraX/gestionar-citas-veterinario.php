<?php
include_once('config/conexion.php');
session_start();

// Verificar que el usuario sea veterinario
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: veterinaria.php?error=acceso_denegado");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener el id_veterinario del usuario
$consulta_vet = "SELECT id_veterinario FROM veterinario WHERE id_usuario = $usuario_id";
$resultado_vet = $conexion->query($consulta_vet);

if (!$resultado_vet || $resultado_vet->num_rows == 0) {
    header("Location: veterinaria.php?error=no_es_veterinario");
    exit();
}

$id_veterinario = $resultado_vet->fetch_assoc()['id_veterinario'];

// Procesar acciones
if ($_POST && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    
    switch ($accion) {
        case 'aceptar_cita':
            $id_cita = intval($_POST['id_cita']);
            
            // Verificar que la cita esté pendiente Y (sin veterinario asignado O asignada a este veterinario)
            $verificar = "SELECT id_cita, fecha FROM citas_veterinarias 
                         WHERE id_cita = $id_cita 
                         AND (id_veterinario IS NULL OR id_veterinario = $id_veterinario) 
                         AND estado = 'pendiente'";
            $resultado = $conexion->query($verificar);
            
            if ($resultado && $resultado->num_rows > 0) {
                // Obtener la fecha original antes de actualizar
                $cita_data = $resultado->fetch_assoc();
                $fecha_original = $cita_data['fecha'];
                
                // Actualizar preservando la fecha original
                $actualizar = "UPDATE citas_veterinarias 
                              SET estado = 'aceptada', 
                                  id_veterinario = $id_veterinario,
                                  fecha = '$fecha_original'
                              WHERE id_cita = $id_cita";
                
                if ($conexion->query($actualizar)) {
                    header("Location: veterinaria.php?exito=cita_aceptada");
                } else {
                    header("Location: veterinaria.php?error=error_aceptar");
                }
            } else {
                header("Location: veterinaria.php?error=cita_no_valida");
            }
            exit();
            
        case 'rechazar_cita':
            $id_cita = intval($_POST['id_cita']);
            
            // Verificar que la cita esté pendiente y obtener fecha
            $verificar = "SELECT id_cita, fecha FROM citas_veterinarias 
                         WHERE id_cita = $id_cita 
                         AND (id_veterinario IS NULL OR id_veterinario = $id_veterinario) 
                         AND estado = 'pendiente'";
            $resultado = $conexion->query($verificar);
            
            if ($resultado && $resultado->num_rows > 0) {
                // Obtener la fecha original antes de actualizar
                $cita_data = $resultado->fetch_assoc();
                $fecha_original = $cita_data['fecha'];
                
                // Actualizar preservando la fecha original
                $actualizar = "UPDATE citas_veterinarias 
                              SET estado = 'rechazada',
                                  fecha = '$fecha_original'
                              WHERE id_cita = $id_cita";
                
                if ($conexion->query($actualizar)) {
                    header("Location: veterinaria.php?exito=cita_rechazada");
                } else {
                    header("Location: veterinaria.php?error=error_rechazar");
                }
            } else {
                header("Location: veterinaria.php?error=cita_no_valida");
            }
            exit();
    }
}

// Si no hay acción POST válida, redirigir
header("Location: veterinaria.php");
exit();
?>