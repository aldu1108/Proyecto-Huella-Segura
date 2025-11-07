<?php
include_once('config/conexion.php');
include_once('includes/funciones.php');
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'] ?? 'demo';

// Manejar errores de URL
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'fecha_pasada':
            $mensaje_error = "No puedes agendar citas para fechas pasadas. Por favor selecciona hoy o una fecha futura.";
            break;
        case 'datos_cita_incompletos':
            $mensaje_error = "Por favor completa todos los campos requeridos.";
            break;
        case 'mascota_no_valida':
            $mensaje_error = "La mascota seleccionada no es válida.";
            break;
        case 'error_agendar_cita':
            $mensaje_error = "Error al agendar la cita. Inténtalo nuevamente.";
            break;
        case 'acceso_denegado':
            $mensaje_error = "No tienes permisos para realizar esta acción.";
            break;
        case 'no_es_veterinario':
            $mensaje_error = "No estás registrado como veterinario.";
            break;
        case 'cita_no_valida':
            $mensaje_error = "La cita no es válida o ya fue procesada.";
            break;
        case 'error_aceptar':
            $mensaje_error = "Error al aceptar la cita.";
            break;
        case 'error_rechazar':
            $mensaje_error = "Error al rechazar la cita.";
            break;
                    case 'no_autorizado':
            $mensaje_error = "No tienes permisos para realizar esta acción.";
            break;
        case 'datos_dueno_incompletos':
            $mensaje_error = "Debes completar todos los campos del dueño (nombre, apellido, email y contraseña).";
            break;
        case 'email_existe':
            $mensaje_error = "El email ingresado ya está registrado. Selecciona el dueño existente o usa otro email.";
            break;
        case 'error_crear_dueno':
            $mensaje_error = "Error al crear el dueño. Intenta nuevamente.";
            break;
        case 'error_agregar_mascota':
            $mensaje_error = "Error al agregar la mascota. Intenta nuevamente.";
            break;
    }
}


if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'cita_agendada':
            $mensaje_exito = "¡Cita agendada exitosamente! El veterinario debe aprobarla.";
            break;
        case 'cita_aceptada':
            $mensaje_exito = "¡Cita aceptada exitosamente!";
            break;
        case 'cita_rechazada':
            $mensaje_exito = "Cita rechazada correctamente.";
            break;
             case 'paciente_agregado':
            $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : 'el paciente';
            $mensaje_exito = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"#75FB4C\"><path d=\"m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z\"/></svg> ¡Paciente agregado exitosamente! $nombre ha sido registrado.";
            break;
        case 'paciente_y_dueno_creados':
            $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : 'el paciente';
            $mensaje_exito = "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20px\" viewBox=\"0 -960 960 960\" width=\"20px\" fill=\"#75FB4C\"><path d=\"m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z\"/></svg> ¡Paciente y dueño creados exitosamente! $nombre y su dueño pueden ahora iniciar sesión.";
            break;
    }
}

// Procesar acciones POST
if ($_POST) {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'agendar_cita':
            $id_mascota = intval($_POST['id_mascota']);
            $motivo = strip_tags($_POST['motivo']);
            $fecha = $_POST['fecha'];
            $hora = $_POST['hora'];
            $clinica = strip_tags($_POST['clinica'] ?? '');
            $observaciones = strip_tags($_POST['observaciones'] ?? '');
            
            // VALIDACIÓN: No permitir fechas pasadas
            $fecha_hoy = date('Y-m-d');
            if ($fecha < $fecha_hoy) {
                $mensaje_error = "No puedes agendar citas para fechas pasadas. Por favor selecciona hoy o una fecha futura.";
                break;
            }
            
            // Verificar que la mascota pertenezca al usuario
            $verificar_mascota = "SELECT id_mascota FROM mascotas WHERE id_mascota = $id_mascota AND id_usuario = $usuario_id";
            $resultado_verificacion = $conexion->query($verificar_mascota);
            
            if ($resultado_verificacion->num_rows > 0) {
                $fecha_completa = $fecha . ' ' . $hora . ':00';
                
                // CAMBIO: Insertar con id_veterinario NULL para que cualquier veterinario pueda aceptarla
                $consulta_insertar = "INSERT INTO citas_veterinarias (fecha, motivo, estado, id_mascota, id_veterinario) 
                                     VALUES ('$fecha_completa', '$motivo', 'pendiente', $id_mascota, NULL)";
                
                if ($conexion->query($consulta_insertar)) {
                    $mensaje_exito = "¡Cita agendada exitosamente! El veterinario debe aprobarla.";
                    header("Location: veterinaria.php?exito=cita_agendada");
                    exit();
                } else {
                    $mensaje_error = "Error al agendar la cita. Inténtalo nuevamente.";
                }
            } else {
                $mensaje_error = "Mascota no válida.";
            }
            break;

        case 'eliminar_consulta':
            $id_historial = intval($_POST['id_historial']);
            
            // Verificar que la consulta pertenezca al usuario
            $verificar_consulta = "SELECT h.id_historial FROM historiales_medicos h 
                                  JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                  WHERE h.id_historial = $id_historial AND m.id_usuario = $usuario_id";
            $resultado_verificacion = $conexion->query($verificar_consulta);
            
            if ($resultado_verificacion->num_rows > 0) {
                $consulta_eliminar = "DELETE FROM historiales_medicos WHERE id_historial = $id_historial";
                
                if ($conexion->query($consulta_eliminar)) {
                    $mensaje_exito = "Consulta eliminada exitosamente.";
                } else {
                    $mensaje_error = "Error al eliminar la consulta.";
                }
            } else {
                $mensaje_error = "Consulta no válida.";
            }
            break;

        case 'eliminar_cita':
            $id_cita = intval($_POST['id_cita']);
            
            // Verificar que la cita pertenezca al usuario
            $verificar_cita = "SELECT c.id_cita FROM citas_veterinarias c 
                              JOIN mascotas m ON c.id_mascota = m.id_mascota 
                              WHERE c.id_cita = $id_cita AND m.id_usuario = $usuario_id";
            $resultado_verificacion = $conexion->query($verificar_cita);
            
            if ($resultado_verificacion->num_rows > 0) {
                $consulta_eliminar = "DELETE FROM citas_veterinarias WHERE id_cita = $id_cita";
                
                if ($conexion->query($consulta_eliminar)) {
                    $mensaje_exito = "Cita eliminada exitosamente.";
                } else {
                    $mensaje_error = "Error al eliminar la cita.";
                }
            } else {
                $mensaje_error = "Cita no válida.";
            }
            break;
            
        case 'registrar_consulta':
            $id_mascota = intval($_POST['id_mascota']);
            $fecha_consulta = $_POST['fecha_consulta'];
            $diagnostico = strip_tags($_POST['diagnostico']);
            $tratamiento = strip_tags($_POST['tratamiento']);
            $veterinario = strip_tags($_POST['veterinario'] ?? '');
            $observaciones = strip_tags($_POST['observaciones_consulta'] ?? '');
            
            // Verificar que la mascota pertenezca al usuario
            $verificar_mascota = "SELECT id_mascota FROM mascotas WHERE id_mascota = $id_mascota AND id_usuario = $usuario_id";
            $resultado_verificacion = $conexion->query($verificar_mascota);
            
            if ($resultado_verificacion->num_rows > 0) {
                $consulta_insertar = "INSERT INTO historiales_medicos (fecha, diagnostico, tratamiento, id_mascota, id_veterinario) 
                                     VALUES ('$fecha_consulta', '$diagnostico', '$tratamiento', $id_mascota, 1)";
                
                if ($conexion->query($consulta_insertar)) {
                    $mensaje_exito = "¡Consulta registrada exitosamente!";
                } else {
                    $mensaje_error = "Error al registrar la consulta. Inténtalo nuevamente.";
                }
            } else {
                $mensaje_error = "Mascota no válida.";
            }
            break;
    }
}

// Si es veterinario, obtener id_veterinario
$id_veterinario_actual = null;
if ($rol_usuario === 'veterinario') {
    $consulta_vet_id = "SELECT id_veterinario FROM veterinario WHERE id_usuario = $usuario_id";
    $resultado_vet_id = $conexion->query($consulta_vet_id);
    
    if ($resultado_vet_id && $resultado_vet_id->num_rows > 0) {
        $id_veterinario_actual = $resultado_vet_id->fetch_assoc()['id_veterinario'];
    }
}

// Obtener próximas citas (solo futuras y no rechazadas) con todos los datos necesarios
$fecha_hoy = date('Y-m-d');

// CAMBIO: Si es veterinario, mostrar citas donde él es el veterinario asignado
// Si es usuario normal, mostrar citas de sus mascotas
if ($rol_usuario === 'veterinario' && $id_veterinario_actual) {
    $consulta_proximas = "SELECT c.*, m.nombre_mascota, m.tipo, m.id_usuario, 
                          v.clinica as vet_clinica, v.especialidad,
                          u.nombre_usuario as nombre_veterinario, 
                          u.apellido_usuario as apellido_veterinario,
                          owner.nombre_usuario as nombre_dueno, 
                          owner.apellido_usuario as apellido_dueno,
                          owner.telefono_usuario, owner.email_usuario,
                          DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                          FROM citas_veterinarias c 
                          JOIN mascotas m ON c.id_mascota = m.id_mascota 
                          JOIN usuarios owner ON m.id_usuario = owner.id_usuario
                          LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                          LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                          WHERE c.id_veterinario = $id_veterinario_actual 
                          AND DATE(c.fecha) >= '$fecha_hoy' 
                          AND c.estado IN ('pendiente', 'aceptada', 'programada')
                          ORDER BY c.fecha ASC LIMIT 10";
} else {
    $consulta_proximas = "SELECT c.*, m.nombre_mascota, m.tipo, m.id_usuario, 
                          v.clinica as vet_clinica, v.especialidad,
                          u.nombre_usuario as nombre_veterinario, 
                          u.apellido_usuario as apellido_veterinario,
                          DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                          FROM citas_veterinarias c 
                          JOIN mascotas m ON c.id_mascota = m.id_mascota 
                          LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                          LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                          WHERE m.id_usuario = $usuario_id 
                          AND DATE(c.fecha) >= '$fecha_hoy' 
                          AND c.estado IN ('pendiente', 'aceptada', 'programada')
                          ORDER BY c.fecha ASC LIMIT 5";
}
$resultado_proximas = $conexion->query($consulta_proximas);

// Obtener citas de hoy
if ($rol_usuario === 'veterinario' && $id_veterinario_actual) {
    $consulta_citas_hoy = "SELECT COUNT(*) as total FROM citas_veterinarias c 
                          WHERE c.id_veterinario = $id_veterinario_actual 
                          AND DATE(c.fecha) = '$fecha_hoy' 
                          AND c.estado IN ('pendiente', 'aceptada', 'programada')";
} else {
    $consulta_citas_hoy = "SELECT COUNT(*) as total FROM citas_veterinarias c 
                          JOIN mascotas m ON c.id_mascota = m.id_mascota 
                          WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) = '$fecha_hoy' 
                          AND c.estado IN ('pendiente', 'aceptada', 'programada')";
}
$citas_hoy_count = $conexion->query($consulta_citas_hoy)->fetch_assoc()['total'];

// Obtener historial médico
if ($rol_usuario === 'veterinario' && $id_veterinario_actual) {
    // Veterinario: ver TODOS los historiales médicos del sistema
    $consulta_historial_simple = "SELECT h.*, m.nombre_mascota, m.tipo, m.id_usuario as id_dueno,
                                   u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario,
                                   owner.nombre_usuario as nombre_dueno, owner.apellido_usuario as apellido_dueno
                                   FROM historiales_medicos h 
                                   JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                   JOIN usuarios owner ON m.id_usuario = owner.id_usuario
                                   LEFT JOIN veterinario v ON h.id_veterinario = v.id_veterinario
                                   LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                                   ORDER BY h.fecha DESC LIMIT 50";
} else {
    // Usuario normal: solo sus registros
    $consulta_historial_simple = "SELECT h.*, m.nombre_mascota, m.tipo,
                                   u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario
                                   FROM historiales_medicos h 
                                   JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                   LEFT JOIN veterinario v ON h.id_veterinario = v.id_veterinario
                                   LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                                   WHERE m.id_usuario = $usuario_id 
                                   ORDER BY h.fecha DESC LIMIT 20";
}
$resultado_historial = $conexion->query($consulta_historial_simple);


// Obtener citas pasadas para mostrar en el historial
if ($rol_usuario === 'veterinario' && $id_veterinario_actual) {
    $consulta_citas_pasadas = "SELECT c.*, m.nombre_mascota, m.tipo, m.id_usuario as id_dueno, 
                               v.clinica as vet_clinica, v.especialidad,
                               u.nombre_usuario as nombre_veterinario, 
                               u.apellido_usuario as apellido_veterinario,
                               owner.nombre_usuario as nombre_dueno, 
                               owner.apellido_usuario as apellido_dueno,
                               DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                               FROM citas_veterinarias c 
                               JOIN mascotas m ON c.id_mascota = m.id_mascota 
                               JOIN usuarios owner ON m.id_usuario = owner.id_usuario
                               LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                               LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                               WHERE c.id_veterinario = $id_veterinario_actual 
                               AND DATE(c.fecha) < '$fecha_hoy' 
                               AND c.estado IN ('aceptada', 'completada')
                               ORDER BY c.fecha DESC LIMIT 20";
} else {
    $consulta_citas_pasadas = "SELECT c.*, m.nombre_mascota, m.tipo, 
                               v.clinica as vet_clinica, v.especialidad,
                               u.nombre_usuario as nombre_veterinario, 
                               u.apellido_usuario as apellido_veterinario,
                               DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                               FROM citas_veterinarias c 
                               JOIN mascotas m ON c.id_mascota = m.id_mascota 
                               LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                               LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                               WHERE m.id_usuario = $usuario_id 
                               AND DATE(c.fecha) < '$fecha_hoy' 
                               AND c.estado IN ('aceptada', 'completada')
                               ORDER BY c.fecha DESC LIMIT 10";
}
$resultado_citas_pasadas = $conexion->query($consulta_citas_pasadas);

// Obtener mascotas para el selector
$consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
$resultado_mascotas = $conexion->query($consulta_mascotas);

// Contar estadísticas
if ($rol_usuario === 'veterinario' && $id_veterinario_actual) {
    $citas_pendientes = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias c 
                                         WHERE c.id_veterinario = $id_veterinario_actual 
                                         AND c.estado IN ('pendiente', 'aceptada')")->fetch_assoc()['total'];
    
    $total_consultas = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias c 
                                        WHERE c.id_veterinario = $id_veterinario_actual 
                                        AND c.estado = 'completada'")->fetch_assoc()['total'];
} else {
    $citas_pendientes = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias c 
                                         JOIN mascotas m ON c.id_mascota = m.id_mascota 
                                         WHERE m.id_usuario = $usuario_id AND c.estado IN ('pendiente', 'aceptada')")->fetch_assoc()['total'];
    
    $total_consultas = $conexion->query("SELECT COUNT(*) as total FROM historiales_medicos h 
                                        JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                        WHERE m.id_usuario = $usuario_id")->fetch_assoc()['total'];
}

// Si es veterinario, obtener citas pendientes de aprobación
$citas_pendientes_vet = 0;
$resultado_citas_pendientes = null;

if ($rol_usuario === 'veterinario' && $id_veterinario_actual) {
    // CAMBIO: Obtener citas pendientes SIN veterinario asignado O asignadas a este veterinario
    $consulta_pendientes = "SELECT c.*, m.nombre_mascota, m.tipo, m.id_usuario,
                            owner.nombre_usuario as nombre_dueno,
                            owner.apellido_usuario as apellido_dueno,
                            owner.telefono_usuario, 
                            owner.email_usuario,
                            v.clinica as vet_clinica,
                            v.especialidad,
                            DATE(c.fecha) as fecha_solo, 
                            TIME(c.fecha) as hora_solo
                            FROM citas_veterinarias c 
                            JOIN mascotas m ON c.id_mascota = m.id_mascota 
                            JOIN usuarios owner ON m.id_usuario = owner.id_usuario
                            LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                            WHERE (c.id_veterinario IS NULL OR c.id_veterinario = $id_veterinario_actual) 
                            AND c.estado = 'pendiente'
                            ORDER BY c.fecha ASC";
    $resultado_citas_pendientes = $conexion->query($consulta_pendientes);
    $citas_pendientes_vet = $resultado_citas_pendientes->num_rows;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Veterinaria - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/veterinaria.css">
    <link rel="stylesheet" href="css/modal-alerta-demo.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>
        
    <div class="contenedor-veterinaria">
        <!-- Mostrar mensajes -->
        <?php if (isset($mensaje_exito)): ?>
            <div id="mensajeExito" class="mensaje-exito" style="background: #27AE60; color: white; padding: 16px; border-radius: 12px; margin-bottom: 20px; text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
            <div id="mensajeError" class="mensaje-error" style="background: #E74C3C; color: white; padding: 16px; border-radius: 12px; margin-bottom: 20px; text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m291-240-51-51 189-189-189-189 51-51 189 189 189-189 51 51-189 189 189 189-51 51-189-189-189 189Z"/></svg> <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <!-- Header del área veterinaria -->
        <section class="header-veterinaria">
            <h2 class="titulo-veterinaria"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg> Área Veterinaria</h2>
            <p class="subtitulo-veterinaria">Gestión completa de la salud de tus mascotas</p>
        </section>

        <!-- Estadísticas veterinaria -->
        <section class="estadisticas-vet">
            <div class="tarjeta-stat-vet hoy">
                <span class="icono-stat-vet"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg></span>
                <div class="numero-stat-vet"><?php echo $citas_hoy_count; ?></div>
                <div class="texto-stat-vet">Citas Hoy</div>
            </div>

            <div class="tarjeta-stat-vet pendiente">
                <span class="icono-stat-vet"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg></span>
                <div class="numero-stat-vet"><?php echo $citas_pendientes; ?></div>
                <div class="texto-stat-vet">Citas Pendientes</div>
            </div>

            <div class="tarjeta-stat-vet completadas">
                <span class="icono-stat-vet"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#789DE5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg></span>
                <div class="numero-stat-vet"><?php echo $total_consultas; ?></div>
                <div class="texto-stat-vet"><?php echo ($rol_usuario === 'veterinario') ? 'Citas Completadas' : 'Consultas Realizadas'; ?></div>
            </div>

            <div class="tarjeta-stat-vet">
                <span class="icono-stat-vet"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg></span>
                <div class="numero-stat-vet"><?php echo $resultado_mascotas->num_rows; ?></div>
                <div class="texto-stat-vet">Mis Mascotas</div>
            </div>
        </section>

        <!-- Navegacion de secciones -->
        <nav class="navegacion-veterinaria">
            <button class="boton-seccion-vet activo" data-seccion="agenda"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Mi Agenda</button>
            <?php if ($rol_usuario === 'veterinario'): ?>
                <button class="boton-seccion-vet" data-seccion="pacientes"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> Pacientes</button>
            <?php endif; ?>
            <button class="boton-seccion-vet" data-seccion="historial"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Historial</button>
            <button class="boton-seccion-vet" data-seccion="documentos"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZZm263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624h168L528-792v168Z"/></svg> Documentos</button>
        </nav>

            <!-- Seccion Mi Agenda -->
                    <section class="seccion-veterinaria seccion-agenda activa" id="seccionAgenda">
                        <div class="encabezado-agenda">
                            <h3>Mi Agenda Veterinaria</h3>
                            <div class="filtros-historial">
                                <?php if ($rol_usuario === 'veterinario'): ?>
                                    <select class="filtro-mascota" onchange="filtrarAgendaPorDueno(this.value)">
                                        <option value="">Todos los dueños</option>
                                        <?php 
                                        // Obtener lista de dueños únicos de las citas del veterinario
                                        if ($id_veterinario_actual) {
                                            $consulta_duenos = "SELECT DISTINCT u.id_usuario, u.nombre_usuario, u.apellido_usuario
                                                            FROM citas_veterinarias c 
                                                            JOIN mascotas m ON c.id_mascota = m.id_mascota 
                                                            JOIN usuarios u ON m.id_usuario = u.id_usuario
                                                            WHERE (c.id_veterinario = $id_veterinario_actual OR c.id_veterinario IS NULL)
                                                            ORDER BY u.nombre_usuario, u.apellido_usuario";
                                            $resultado_duenos = $conexion->query($consulta_duenos);
                                            
                                            if ($resultado_duenos && $resultado_duenos->num_rows > 0):
                                                while($dueno = $resultado_duenos->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $dueno['id_usuario']; ?>">
                                                <?php echo htmlspecialchars($dueno['nombre_usuario'] . ' ' . $dueno['apellido_usuario']); ?>
                                            </option>
                                        <?php 
                                                endwhile;
                                            endif;
                                        }
                                        ?>
                                    </select>
                                <?php else: ?>
                                    <select class="filtro-mascota" onchange="filtrarAgenda(this.value)">
                                        <option value="">Todas las mascotas</option>
                                        <?php 
                                        $resultado_mascotas->data_seek(0);
                                        while($mascota = $resultado_mascotas->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $mascota['id_mascota']; ?>">
                                                <?php echo htmlspecialchars($mascota['nombre_mascota']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                <?php endif; ?>
                        </div>
                        </div>
                        <!-- TARJETA 1: Citas Aceptadas/Confirmadas -->
                        <div class="proximas-citas citas-aceptadas">
                            <div class="encabezado-citas-seccion">
                                <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Citas Confirmadas</h4>
                            </div>
                            
                            <?php 
                            // Filtrar solo citas aceptadas
                            $resultado_proximas->data_seek(0);
                            $hay_aceptadas = false;
                            while($cita = $resultado_proximas->fetch_assoc()): 
                                if ($cita['estado'] === 'aceptada' || $cita['estado'] === 'programada'):
                                    $hay_aceptadas = true;
                            ?>
            <div class="tarjeta-cita <?php echo (date('Y-m-d', strtotime($cita['fecha'])) == $fecha_hoy) ? 'hoy' : 'proxima'; ?>" data-mascota="<?php echo $cita['id_mascota']; ?>" data-dueno="<?php echo $cita['id_usuario'] ?? ''; ?>">
                                    <div class="info-cita">
                                        <div class="fecha-cita">
                                            <span class="dia"><?php echo date('d', strtotime($cita['fecha'])); ?></span>
                                            <span class="mes"><?php echo date('M', strtotime($cita['fecha'])); ?></span>
                                        </div>
                                        <div class="detalles-cita">
                                            <h5><?php echo htmlspecialchars($cita['motivo']); ?></h5>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> <strong>Mascota:</strong> <?php echo htmlspecialchars($cita['nombre_mascota']); ?> (<?php echo ucfirst($cita['tipo']); ?>)</p>
                                            <?php if ($rol_usuario === 'veterinario'): ?>
                                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg> <strong>Dueño:</strong> <?php echo htmlspecialchars($cita['nombre_dueno'] . ' ' . $cita['apellido_dueno']); ?></p>
                                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M288-48q-29.7 0-50.85-21.15Q216-90.3 216-120v-720q0-33 19.5-52.5T288-912h384q29.7 0 50.85 21.15Q744-869.7 744-840v144q20 0 34 14t14 34v96q0 20-14 34t-34 14v384q0 29.7-21.15 50.85Q701.7-48 672-48H288Zm191.79-672q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg> <strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono_usuario'] ?: 'No disponible'); ?></p>
                                            <?php endif; ?>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M432-288h96v-96h96v-96h-96v-96h-96v96h-96v96h96v96ZM192-144v-456l288-216 288 216v456H192Z"/></svg> <strong>Clínica:</strong> <?php echo htmlspecialchars($cita['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> <strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> <strong>Fecha completa:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha'])); ?></p>
                                            <?php if ($cita['especialidad']): ?>
                                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg> <strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="acciones-cita">
                                        <div class="estado-cita <?php echo $cita['estado']; ?>">
                                            <?php 
                                                $estados_texto = [
                                                    'pendiente' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M480-516q65 0 110.5-45.5T636-672v-120H324v120q0 65 45.5 110.5T480-516ZM192-96v-72h60v-120q0-59 28-109.5t78-82.5q-49-32-77.5-82.5T252-672v-120h-60v-72h576v72h-60v120q0 59-28.5 109.5T602-480q50 32 78 82.5T708-288v120h60v72H192Z"/></svg> Pendiente',
                                                    'aceptada' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Aceptada',
                                                    'rechazada' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Rechazada',
                                                    'programada' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Programada',
                                                    'completada' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="M389-267 195-460l51-52 143 143 325-324 51 51-376 375Z"/></svg> Completada',
                                                    'cancelada' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Cancelada'
                                                ];
                                                echo $estados_texto[$cita['estado']] ?? ucfirst($cita['estado']);
                                            ?>
                                        </div>
                                        <div class="botones-cita">
                                            <button class="boton-eliminar-cita" onclick="confirmarEliminarCita(<?php echo $cita['id_cita']; ?>, '<?php echo htmlspecialchars($cita['nombre_mascota']); ?>', '<?php echo htmlspecialchars($cita['motivo']); ?>')">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endwhile; 
                            
                            if (!$hay_aceptadas): 
                            ?>
                                <div class="sin-citas">
                                    <p>No tienes citas confirmadas próximamente</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- TARJETA 2: Citas Pendientes de Aprobación (SOLO MOSTRAR AQUÍ SI ES VETERINARIO) -->
                        <?php if ($rol_usuario === 'veterinario' && isset($resultado_citas_pendientes)): ?>
                        <div class="proximas-citas citas-pendientes-veterinario">
                            <div class="encabezado-citas-seccion">
                                <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#d35400"><path d="M192-216v-72h48v-240q0-87 53.5-153T432-763v-53q0-20 14-34t34-14q20 0 34 14t14 34v53q85 16 138.5 82T720-528v240h48v72H192ZM479.79-96Q450-96 429-117.15T408-168h144q0 30-21.21 51t-51 21Z"/></svg> Citas Pendientes de Aprobación</h4>
                                <span class="badge-pendientes"><?php echo $citas_pendientes_vet; ?> pendientes</span>
                            </div>

                            <?php if ($resultado_citas_pendientes && $resultado_citas_pendientes->num_rows > 0): ?>
                                <div class="lista-citas-pendientes">
                                    <?php while($cita = $resultado_citas_pendientes->fetch_assoc()): ?>
                                        <div class="tarjeta-cita-pendiente" data-mascota="<?php echo $cita['id_mascota']; ?>" data-dueno="<?php echo $cita['id_usuario'] ?? ''; ?>">
                                            <div class="info-cita">
                                                <div class="fecha-cita">
                                                    <span class="dia"><?php echo date('d', strtotime($cita['fecha'])); ?></span>
                                                    <span class="mes"><?php echo date('M', strtotime($cita['fecha'])); ?></span>
                                                </div>
                                                 <div class="detalles-cita">
                                            <h5><?php echo htmlspecialchars($cita['motivo']); ?></h5>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> <strong>Mascota:</strong> <?php echo htmlspecialchars($cita['nombre_mascota']); ?> (<?php echo ucfirst($cita['tipo']); ?>)</p>
                                            <?php if ($rol_usuario === 'veterinario'): ?>
                                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg> <strong>Dueño:</strong> <?php echo htmlspecialchars($cita['nombre_dueno'] . ' ' . $cita['apellido_dueno']); ?></p>
                                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M288-48q-29.7 0-50.85-21.15Q216-90.3 216-120v-720q0-33 19.5-52.5T288-912h384q29.7 0 50.85 21.15Q744-869.7 744-840v144q20 0 34 14t14 34v96q0 20-14 34t-34 14v384q0 29.7-21.15 50.85Q701.7-48 672-48H288Zm191.79-672q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg> <strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono_usuario'] ?: 'No disponible'); ?></p>
                                            <?php endif; ?>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M432-288h96v-96h96v-96h-96v-96h-96v96h-96v96h96v96ZM192-144v-456l288-216 288 216v456H192Z"/></svg> <strong>Clínica:</strong> <?php echo htmlspecialchars($cita['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> <strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> <strong>Fecha completa:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha'])); ?></p>
                                            <?php if ($cita['especialidad']): ?>
                                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg> <strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                            </div>
                                            <div class="acciones-cita-pendiente">
                                                <form method="POST" action="gestionar-citas-veterinario.php" style="display: inline;">
                                                    <input type="hidden" name="accion" value="aceptar_cita">
                                                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                                    <button type="submit" class="boton-aceptar-cita" onclick="return confirm('¿Confirmas que deseas ACEPTAR esta cita?')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Aceptar
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" action="gestionar-citas-veterinario.php" style="display: inline;">
                                                    <input type="hidden" name="accion" value="rechazar_cita">
                                                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                                    <button type="submit" class="boton-rechazar-cita" onclick="return confirm('¿Estás seguro de RECHAZAR esta cita?')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m291-240-51-51 189-189-189-189 51-51 189 189 189-189 51 51-189 189 189 189-51 51-189-189-189 189Z"/></svg> Rechazar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="sin-citas">
                                    <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> No tienes citas pendientes de aprobar</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <!-- TARJETA 2: Citas Pendientes de Aprobación USUARIOS NORMALES -->
                        <div class="proximas-citas citas-pendientes-usuario">
                            <div class="encabezado-citas-seccion">
                                <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M480-516q65 0 110.5-45.5T636-672v-120H324v120q0 65 45.5 110.5T480-516ZM192-96v-72h60v-120q0-59 28-109.5t78-82.5q-49-32-77.5-82.5T252-672v-120h-60v-72h576v72h-60v120q0 59-28.5 109.5T602-480q50 32 78 82.5T708-288v120h60v72H192Z"/></svg> Citas Pendientes de Aprobación</h4>
                                <?php if ($rol_usuario == 'demo'): ?>
                                    <button class="boton-nueva-cita" onclick="mostrarModalAlerta('Para agendar citas veterinarias necesitas una cuenta registrada.', ['Agendar citas con veterinarios', 'Recibir recordatorios automáticos', 'Gestionar horarios de tus mascotas', 'Llevar control de consultas'])">
                                        + Agendar Nueva Cita
                                    </button>
                                <?php else: ?>
                                    <button class="boton-nueva-cita" onclick="mostrarFormularioCita()">
                                        + Agendar Nueva Cita
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="info-pendientes">
                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="M479.79-96Q450-96 429-117.15T408-168h144q0 30-21.21 51t-51 21ZM336-216v-72h288v72H336Zm-15-120q-62-38-95.5-102.5T192-576q0-120 84-204t204-84q120 0 204 84t84 204q0 73-33.5 137.5T639-336H321Z"/></svg> Estas citas están esperando la confirmación del veterinario</p>
                            </div>

                            <?php 
                            // Filtrar solo citas pendientes
                            $resultado_proximas->data_seek(0);
                            $hay_pendientes = false;
                            while($cita = $resultado_proximas->fetch_assoc()): 
                                if ($cita['estado'] === 'pendiente'):
                                    $hay_pendientes = true;
                            ?>
                                <div class="tarjeta-cita pendiente-aprobacion" data-mascota="<?php echo $cita['id_mascota']; ?>" data-dueno="<?php echo $cita['id_usuario'] ?? ''; ?>">
                                    <div class="info-cita">
                                        <div class="fecha-cita fecha-pendiente">
                                            <span class="dia"><?php echo date('d', strtotime($cita['fecha'])); ?></span>
                                            <span class="mes"><?php echo date('M', strtotime($cita['fecha'])); ?></span>
                                        </div>
                                        <div class="detalles-cita">
                                            <h5><?php echo htmlspecialchars($cita['motivo']); ?></h5>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg><strong>Mascota:</strong> <?php echo htmlspecialchars($cita['nombre_mascota']); ?> (<?php echo ucfirst($cita['tipo']); ?>)</p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M432-288h96v-96h96v-96h-96v-96h-96v96h-96v96h96v96ZM192-144v-456l288-216 288 216v456H192Z"/></svg> <strong>Clínica:</strong> <?php echo htmlspecialchars($cita['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> <strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                                            <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> <strong>Fecha completa:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha'])); ?></p>
                                            <?php if ($cita['especialidad']): ?>
                                                <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg> <strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="acciones-cita">
                                        <div class="estado-cita pendiente">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M480-516q65 0 110.5-45.5T636-672v-120H324v120q0 65 45.5 110.5T480-516ZM192-96v-72h60v-120q0-59 28-109.5t78-82.5q-49-32-77.5-82.5T252-672v-120h-60v-72h576v72h-60v120q0 59-28.5 109.5T602-480q50 32 78 82.5T708-288v120h60v72H192Z"/></svg> Pendiente
                                        </div>
                                        <div class="botones-cita">
                                            <button class="boton-eliminar-cita" onclick="confirmarEliminarCita(<?php echo $cita['id_cita']; ?>, '<?php echo htmlspecialchars($cita['nombre_mascota']); ?>', '<?php echo htmlspecialchars($cita['motivo']); ?>')">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endwhile; 
                            
                            if (!$hay_pendientes): 
                            ?>
                                <div class="sin-citas">
                                    <p>No tienes citas pendientes de aprobación</p>
                                    <?php if ($rol_usuario == 'demo'): ?>
                                        <button class="boton-agendar-primera" onclick="mostrarModalAlerta('Inicia sesión para agendar citas\n\nRegístrate para gestionar la salud de tus mascotas')">
                                            Agendar Primera Cita
                                        </button>
                                    <?php else: ?>
                                        <button class="boton-agendar-primera" onclick="mostrarFormularioCita()">
                                            Agendar Primera Cita
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </section>

       <!-- Sección Pacientes -->
<?php if ($rol_usuario === 'veterinario'): ?>
    <section class="seccion-veterinaria seccion-pacientes" id="seccionPacientes">
        <div class="encabezado-agenda">
            <h3>Mis Pacientes</h3>
            <div style="display: flex; gap: 12px; align-items: center;">
                <!-- Filtro por dueño -->
                <select class="filtro-mascota" onchange="filtrarPacientesPorDueno(this.value)">
                    <option value="">Todos los dueños</option>
                    <?php 
                    // Obtener lista de dueños únicos que tienen mascotas
                    $consulta_duenos_pacientes = "SELECT DISTINCT u.id_usuario, u.nombre_usuario, u.apellido_usuario
                                                  FROM usuarios u
                                                  JOIN mascotas m ON u.id_usuario = m.id_usuario
                                                  WHERE u.rol = 'usuario' AND m.estado = 'activo'
                                                  ORDER BY u.nombre_usuario, u.apellido_usuario";
                    $resultado_duenos_pacientes = $conexion->query($consulta_duenos_pacientes);
                    
                    if ($resultado_duenos_pacientes && $resultado_duenos_pacientes->num_rows > 0):
                        while($dueno = $resultado_duenos_pacientes->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $dueno['id_usuario']; ?>">
                            <?php echo htmlspecialchars($dueno['nombre_usuario'] . ' ' . $dueno['apellido_usuario']); ?>
                        </option>
                    <?php 
                        endwhile;
                    endif;
                    ?>
                </select>
                
                <button class="boton-nueva-cita" onclick="mostrarModalAgregarPaciente()">
                    + Agregar Paciente
                </button>
            </div>
        </div>

        <div class="lista-pacientes">
            <?php 
            // Obtener TODAS las mascotas del sistema (no solo las del usuario actual)
            $consulta_todas_mascotas = "SELECT m.*, 
                                        u.nombre_usuario as nombre_dueno, 
                                        u.apellido_usuario as apellido_dueno,
                                        u.telefono_usuario,
                                        u.email_usuario,
                                        u.id_usuario as id_dueno
                                        FROM mascotas m 
                                        JOIN usuarios u ON m.id_usuario = u.id_usuario
                                        WHERE m.estado = 'activo'
                                        ORDER BY m.nombre_mascota ASC";
            $resultado_todas_mascotas = $conexion->query($consulta_todas_mascotas);
            
            if ($resultado_todas_mascotas && $resultado_todas_mascotas->num_rows > 0): 
            ?>
                <?php while($mascota = $resultado_todas_mascotas->fetch_assoc()): ?>
                    <div class="tarjeta-paciente" data-dueno="<?php echo $mascota['id_dueno']; ?>">
                        <div class="info-cita">
                            <div class="foto-paciente">
                                <?php if (!empty($mascota['foto_mascota']) && file_exists('imagenes/' . $mascota['foto_mascota'])): ?>
                                    <img src="imagenes/<?php echo htmlspecialchars($mascota['foto_mascota']); ?>" alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-paciente">
                                        <?php echo ($mascota['tipo'] == 'perro') ? '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg>' : (($mascota['tipo'] == 'gato') ? '🐱' : '🐾'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="detalles-cita">
                                <h5><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h5>
                                <p><strong>Tipo:</strong> <?php echo ucfirst($mascota['tipo']); ?> • <?php echo $mascota['edad_mascota']; ?> años</p>
                                <p><strong>Sexo:</strong> <?php echo ucfirst($mascota['sexo']); ?></p>
                                <p><strong>Nacimiento:</strong> <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> <?php echo date('d M Y', strtotime($mascota['cumpleaños_mascota'])); ?></p>
                                <p style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #f0f0f0;">
                                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg> Dueño:</strong> <?php echo htmlspecialchars($mascota['nombre_dueno'] . ' ' . $mascota['apellido_dueno']); ?>
                                </p>
                                <?php if (!empty($mascota['telefono_usuario'])): ?>
                                    <p><strong>📱 Teléfono:</strong> <?php echo htmlspecialchars($mascota['telefono_usuario']); ?></p>
                                <?php endif; ?>
                                <p><strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M168-192q-29 0-50.5-21.5T96-264v-432q0-29 21.5-50.5T168-768h624q30 0 51 21.5t21 50.5v432q0 29-21 50.5T792-192H168Zm312-240 312-179v-85L480-517 168-696v85l312 179Z"/></svg> Email:</strong> <?php echo htmlspecialchars($mascota['email_usuario']); ?></p>
                            </div>
                        </div>
                        <div class="acciones-paciente">
                            <button class="boton-ver-historial" onclick="verHistorialPaciente(<?php echo $mascota['id_mascota']; ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Ver Historial
                            </button>
                            <button class="boton-nueva-cita-paciente" onclick="agendarCitaPaciente(<?php echo $mascota['id_mascota']; ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Nueva Cita
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="sin-citas">
                    <p><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> No hay pacientes registrados en el sistema</p>
                    <button class="boton-agendar-primera" onclick="mostrarModalAgregarPaciente()">
                        + Agregar Primer Paciente
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

        <!-- Seccion Historial Medico  -->
         <section class="seccion-veterinaria seccion-historial" id="seccionHistorial">
                    <div class="encabezado-agenda">
                        <h4 class="subtitulo-historial"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Citas Realizadas</h4>
                        <div class="filtros-historial">
                            <?php if ($rol_usuario === 'veterinario'): ?>
                                <select class="filtro-mascota" onchange="filtrarHistorialPorDueno(this.value)">
                                    <option value="">Todos los dueños</option>
                                    <?php 
                                    if ($id_veterinario_actual) {
                                        $consulta_duenos_historial = "SELECT DISTINCT u.id_usuario, u.nombre_usuario, u.apellido_usuario
                                                                    FROM citas_veterinarias c 
                                                                    JOIN mascotas m ON c.id_mascota = m.id_mascota 
                                                                    JOIN usuarios u ON m.id_usuario = u.id_usuario
                                                                    WHERE c.id_veterinario = $id_veterinario_actual 
                                                                    ORDER BY u.nombre_usuario, u.apellido_usuario";
                                        $resultado_duenos_historial = $conexion->query($consulta_duenos_historial);
                                        
                                        if ($resultado_duenos_historial && $resultado_duenos_historial->num_rows > 0):
                                            while($dueno = $resultado_duenos_historial->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $dueno['id_usuario']; ?>">
                                            <?php echo htmlspecialchars($dueno['nombre_usuario'] . ' ' . $dueno['apellido_usuario']); ?>
                                        </option>
                                    <?php 
                                            endwhile;
                                        endif;
                                    }
                                    ?>
                                </select>
                                <button class="boton-nueva-consulta" onclick="registrarNuevaConsulta()">
                                    + Nueva Consulta
                                </button>
                            <?php else: ?>
                                <select class="filtro-mascota" onchange="filtrarHistorial(this.value)">
                                    <option value="">Todas las mascotas</option>
                                    <?php 
                                    $resultado_mascotas->data_seek(0);
                                    while($mascota = $resultado_mascotas->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $mascota['id_mascota']; ?>">
                                            <?php echo htmlspecialchars($mascota['nombre_mascota']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

            <div class="registros-medicos">
                <!-- Consultas médicas registradas (SOLO PARA USUARIOS NORMALES) -->
                    <?php if ($rol_usuario !== 'veterinario' && $resultado_historial && $resultado_historial->num_rows > 0): ?>
                        <h4 class="subtitulo-historial"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Consultas Médicas Registradas</h4>
                        <?php while($historial = $resultado_historial->fetch_assoc()): ?>
                          <div class="registro-medico" data-mascota="<?php echo $historial['id_mascota']; ?>">
                        <div class="registro-medico" data-mascota="<?php echo $historial['id_mascota']; ?>" data-dueno="<?php echo $historial['id_dueno'] ?? ''; ?>">
                            <div class="encabezado-registro">
                                <div class="fecha-registro">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> <?php echo date('d M Y', strtotime($historial['fecha'])); ?>
                                </div>
                                <div class="mascota-registro">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> <?php echo htmlspecialchars($historial['nombre_mascota']); ?>
                                </div>
                                <div class="tipo-registro">
                                    <span class="badge-consulta"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Consulta Médica</span>
                                </div>
                            </div>
                            
                            <div class="contenido-registro">
                                <div class="diagnostico">
                                    <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Diagnóstico</h5>
                                    <p><?php echo htmlspecialchars($historial['diagnostico']); ?></p>
                                </div>
                                
                                <div class="tratamiento">
                                    <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M354-144q-87.73 0-148.87-61.13Q144-266.27 144-354q0-42 16-81t45-68l252-252q29-29 68-45t81-16q87.73 0 148.87 61.13Q816-693.73 816-606q0 42-16 81t-45 68L503-205q-29 29-68 45t-81 16Zm249-264 101-100q20-20 30-45t10-52.67q0-57.24-40.55-97.78Q662.91-744 605.67-744 578-744 553-734t-45 30L408-603l195 195ZM354.33-216Q382-216 407-226t45-30l100-101-195-195-100 100q-20 20-30.5 45T216-354.33q0 57.24 40.55 97.78Q297.09-216 354.33-216Z"/></svg> Tratamiento</h5>
                                    <p><?php echo htmlspecialchars($historial['tratamiento']); ?></p>
                                </div>
                                
                                <div class="veterinario-registro">
                                    <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 68.33 47.56 116.17Q465.12-167 533.06-167t115.44-47.83Q696-262.67 696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 98.03-68.69 167.02Q630.62-96 533-96Zm199-360q20.4 0 34.2-13.8Q780-483.6 780-504q0-20.4-13.8-34.2Q752.4-552 732-552q-20.4 0-34.2 13.8Q684-524.4 684-504q0 20.4 13.8 34.2Q711.6-456 732-456Zm0-48Z"/></svg> Veterinario</h5>
                                    <p><?php echo htmlspecialchars(($historial['nombre_veterinario'] && $historial['apellido_veterinario']) ? $historial['nombre_veterinario'] . ' ' . $historial['apellido_veterinario'] : 'Dr. Veterinario'); ?></p>
                                </div>

                                <div class="acciones-consulta">
                                    <button class="boton-eliminar-consulta" onclick="confirmarEliminarConsulta(<?php echo $historial['id_historial']; ?>, '<?php echo htmlspecialchars($historial['nombre_mascota']); ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar Consulta
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                    <!-- Citas pasadas (PARA VETERINARIOS) -->

                    <?php if ($rol_usuario === 'veterinario' && $resultado_historial && $resultado_historial->num_rows > 0): ?>
                        <h4 class="subtitulo-historial"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Consultas Médicas Registradas</h4>
                        <?php while($historial = $resultado_historial->fetch_assoc()): ?>
                            <div class="registro-medico" data-mascota="<?php echo $historial['id_mascota']; ?>" data-dueno="<?php echo $historial['id_dueno']; ?>">
                                <div class="encabezado-registro">
                                    <div class="fecha-registro">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> <?php echo date('d M Y', strtotime($historial['fecha'])); ?>
                                    </div>
                                    <div class="mascota-registro">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> <?php echo htmlspecialchars($historial['nombre_mascota']); ?>
                                    </div>
                                    <div class="tipo-registro">
                                        <span class="badge-consulta"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Consulta Médica</span>
                                    </div>
                                </div>
                                
                                <div class="contenido-registro">
                                    <div class="paciente-cita">
                                        <h5>👤 Dueño</h5>
                                        <p><?php echo htmlspecialchars($historial['nombre_dueno'] . ' ' . $historial['apellido_dueno']); ?></p>
                                    </div>
                                    
                                    <div class="diagnostico">
                                        <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Diagnóstico</h5>
                                        <p><?php echo htmlspecialchars($historial['diagnostico']); ?></p>
                                    </div>
                                    
                                    <div class="tratamiento">
                                        <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M354-144q-87.73 0-148.87-61.13Q144-266.27 144-354q0-42 16-81t45-68l252-252q29-29 68-45t81-16q87.73 0 148.87 61.13Q816-693.73 816-606q0 42-16 81t-45 68L503-205q-29 29-68 45t-81 16Zm249-264 101-100q20-20 30-45t10-52.67q0-57.24-40.55-97.78Q662.91-744 605.67-744 578-744 553-734t-45 30L408-603l195 195ZM354.33-216Q382-216 407-226t45-30l100-101-195-195-100 100q-20 20-30.5 45T216-354.33q0 57.24 40.55 97.78Q297.09-216 354.33-216Z"/></svg> Tratamiento</h5>
                                        <p><?php echo htmlspecialchars($historial['tratamiento']); ?></p>
                                    </div>
                                    
                                    <div class="veterinario-registro">
                                        <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 68.33 47.56 116.17Q465.12-167 533.06-167t115.44-47.83Q696-262.67 696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 98.03-68.69 167.02Q630.62-96 533-96Zm199-360q20.4 0 34.2-13.8Q780-483.6 780-504q0-20.4-13.8-34.2Q752.4-552 732-552q-20.4 0-34.2 13.8Q684-524.4 684-504q0 20.4 13.8 34.2Q711.6-456 732-456Zm0-48Z"/></svg><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg> Veterinario</h5>
                                        <p><?php echo htmlspecialchars(($historial['nombre_veterinario'] && $historial['apellido_veterinario']) ? $historial['nombre_veterinario'] . ' ' . $historial['apellido_veterinario'] : 'Dr. Veterinario'); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                <!-- Citas pasadas (PARA TODOS) -->
               <?php if ($resultado_citas_pasadas && $resultado_citas_pasadas->num_rows > 0): ?>
                 <?php while($cita_pasada = $resultado_citas_pasadas->fetch_assoc()): ?>
                    <div class="registro-medico" data-mascota="<?php echo $cita_pasada['id_mascota']; ?>" data-dueno="<?php echo $cita_pasada['id_dueno'] ?? ''; ?>">
                            <div class="encabezado-registro">
                                <div class="fecha-registro">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> <?php echo date('d M Y', strtotime($cita_pasada['fecha'])); ?>
                                </div>
                                <div class="mascota-registro">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> <?php echo htmlspecialchars($cita_pasada['nombre_mascota']); ?>
                                </div>
                                <div class="tipo-registro">
                                    <span class="badge-cita"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Cita Realizada</span>
                                </div>
                            </div>
                            
                            <div class="contenido-registro">
                                <div class="motivo-cita">
                                    <h5>📋 Motivo de la Cita</h5>
                                    <p><?php echo htmlspecialchars($cita_pasada['motivo']); ?></p>
                                </div>
                                
                                <?php if ($rol_usuario === 'veterinario'): ?>
                                    <div class="paciente-cita">
                                        <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg> Paciente (Dueño)</h5>
                                        <p><?php echo htmlspecialchars($cita_pasada['nombre_dueno'] . ' ' . $cita_pasada['apellido_dueno']); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="clinica-cita">
                                    <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M432-288h96v-96h96v-96h-96v-96h-96v96h-96v96h96v96ZM192-144v-456l288-216 288 216v456H192Z"/></svg> Clínica</h5>
                                    <p><?php echo htmlspecialchars($cita_pasada['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                </div>
                                
                                <?php if ($rol_usuario !== 'veterinario'): ?>
                                <div class="veterinario-registro">
                                    <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg> Veterinario</h5>
                                    <p><?php echo htmlspecialchars(($cita_pasada['nombre_veterinario'] && $cita_pasada['apellido_veterinario']) ? $cita_pasada['nombre_veterinario'] . ' ' . $cita_pasada['apellido_veterinario'] : 'Dr. Veterinario'); ?></p>
                                </div>
                                <?php endif; ?>

                                <div class="fecha-completa-cita">
                                    <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Fecha Completa</h5>
                                    <p><?php echo date('d/m/Y H:i', strtotime($cita_pasada['fecha'])); ?></p>
                                </div>

                                <div class="mascota-detalle-cita">
                                    <h5><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> Mascota</h5>
                                    <p><?php echo htmlspecialchars($cita_pasada['nombre_mascota']); ?> (<?php echo ucfirst($cita_pasada['tipo']); ?>)</p>
                                </div>            

                                <div class="acciones-cita-historial">
                                    <button class="boton-eliminar-cita" onclick="confirmarEliminarCita(<?php echo $cita_pasada['id_cita']; ?>, '<?php echo htmlspecialchars($cita_pasada['nombre_mascota']); ?>', '<?php echo htmlspecialchars($cita_pasada['motivo']); ?>')">
                                        🗑️ Eliminar Cita
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <?php if (($rol_usuario !== 'veterinario' && (!$resultado_historial || $resultado_historial->num_rows == 0)) && (!$resultado_citas_pasadas || $resultado_citas_pasadas->num_rows == 0)): ?>
                    <div class="sin-registros">
                        <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#789DE5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Sin Registros Médicos</h4>
                        <p>Aún no hay registros médicos o citas realizadas para tus mascotas</p>
                    </div>
                <?php elseif ($rol_usuario === 'veterinario' && (!$resultado_citas_pasadas || $resultado_citas_pasadas->num_rows == 0)): ?>
                    <div class="sin-registros">
                        <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#789DE5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Sin Citas Realizadas</h4>
                        <p>Aún no tienes citas completadas en tu historial</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sección Documentos -->
<section class="seccion-veterinaria seccion-documentos" id="seccionDocumentos">
    <div class="encabezado-documentos">
    <h3>Documentos Médicos</h3>
    <?php if ($rol_usuario === 'veterinario'): ?>
        <!-- Botón para veterinarios -->
        <button class="boton-subir-documento" onclick="mostrarSubirDocumento('')">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M696-312q0 89.86-63.07 152.93Q569.86-96 480-96q-91 0-153.5-65.5T264-319v-389q0-65 45.5-110.5T420-864q66 0 111 48t45 115v365q0 40.15-27.93 68.07Q520.15-240 480-240q-41 0-68.5-29.09T384-340v-380h72v384q0 10.4 6.8 17.2 6.8 6.8 17.2 6.8 10.4 0 17.2-6.8 6.8-6.8 6.8-17.2v-372q0-35-24.5-59.5T419.8-792q-35.19 0-59.5 25.5Q336-741 336-706v394q0 60 42 101.5T480-168q60 1 102-43t42-106v-403h72v408Z"/></svg> 
            Subir Documento
        </button>
    <?php elseif ($rol_usuario == 'demo'): ?>
        <!-- Botón para usuarios demo -->
        <button class="boton-subir-documento" onclick="mostrarModalAlerta('Inicia sesión para subir documentos', ['Subir documentos médicos', 'Organizar certificados', 'Mantener registros actualizados'])">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M696-312q0 89.86-63.07 152.93Q569.86-96 480-96q-91 0-153.5-65.5T264-319v-389q0-65 45.5-110.5T420-864q66 0 111 48t45 115v365q0 40.15-27.93 68.07Q520.15-240 480-240q-41 0-68.5-29.09T384-340v-380h72v384q0 10.4 6.8 17.2 6.8 6.8 17.2 6.8 10.4 0 17.2-6.8 6.8-6.8 6.8-17.2v-372q0-35-24.5-59.5T419.8-792q-35.19 0-59.5 25.5Q336-741 336-706v394q0 60 42 101.5T480-168q60 1 102-43t42-106v-403h72v408Z"/></svg> 
            Subir Documento
        </button>
    <?php else: ?>
        <!-- Botón para usuarios normales (no pueden subir) -->
        <button class="boton-subir-documento" style="opacity: 0.5; cursor: not-allowed;" disabled title="Solo veterinarios pueden subir documentos">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M696-312q0 89.86-63.07 152.93Q569.86-96 480-96q-91 0-153.5-65.5T264-319v-389q0-65 45.5-110.5T420-864q66 0 111 48t45 115v365q0 40.15-27.93 68.07Q520.15-240 480-240q-41 0-68.5-29.09T384-340v-380h72v384q0 10.4 6.8 17.2 6.8 6.8 17.2 6.8 10.4 0 17.2-6.8 6.8-6.8 6.8-17.2v-372q0-35-24.5-59.5T419.8-792q-35.19 0-59.5 25.5Q336-741 336-706v394q0 60 42 101.5T480-168q60 1 102-43t42-106v-403h72v408Z"/></svg> 
            Subir Documento
        </button>
    <?php endif; ?>
</div>

    <div class="categorias-documentos">
        <!-- CERTIFICADOS DE VACUNACIÓN -->
        <div class="categoria-doc">
            <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M240-96q-40 0-68-28t-28-68v-144h96v-528h576v660q0 45-31.5 76.5T708-96H240Zm467.79-72q15.21 0 25.71-10.35T744-204v-588H312v456h360v132q0 15.3 10.29 25.65Q692.58-168 707.79-168ZM360-600v-72h336v72H360Zm0 120v-72h336v72H360Z"/></svg> Certificados de Vacunación</h4>
            <div class="lista-documentos">
                <?php
                // Consulta para veterinario: ver TODOS los documentos de vacunación
                if ($rol_usuario === 'veterinario') {
                    $consulta_docs_vacunas = "SELECT dm.*, h.fecha, h.id_historial, m.nombre_mascota, m.id_mascota,
                                               u.nombre_usuario as nombre_dueno, u.apellido_usuario as apellido_dueno
                                               FROM documento_medico dm 
                                               JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                               JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                               JOIN usuarios u ON m.id_usuario = u.id_usuario
                                               WHERE dm.tipo = 'vacuna'
                                               ORDER BY h.fecha DESC";
                } else {
                    $consulta_docs_vacunas = "SELECT dm.*, h.fecha, h.id_historial, m.nombre_mascota, m.id_mascota
                                             FROM documento_medico dm 
                                             JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                             JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                             WHERE m.id_usuario = $usuario_id AND dm.tipo = 'vacuna'
                                             ORDER BY h.fecha DESC";
                }
                $resultado_vacunas = $conexion->query($consulta_docs_vacunas);
                
                if ($resultado_vacunas && $resultado_vacunas->num_rows > 0):
                    while($doc = $resultado_vacunas->fetch_assoc()):
                ?>
                    <div class="documento-item" data-documento-id="<?php echo $doc['id_documento']; ?>">
                        <span class="icono-doc"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZM263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624h168L528-792v168Z"/></svg></span>
                        <div class="info-doc">
                            <strong>Certificado de Vacunación - <?php echo htmlspecialchars($doc['nombre_mascota']); ?></strong>
                            <?php if ($rol_usuario === 'veterinario'): ?>
                                <p>Dueño: <?php echo htmlspecialchars($doc['nombre_dueno'] . ' ' . $doc['apellido_dueno']); ?></p>
                            <?php endif; ?>
                            <p>Subido el <?php echo date('d M Y', strtotime($doc['fecha'])); ?></p>
                        </div>
                        <div class="acciones-doc">
                            <button class="boton-ver-doc" onclick="verDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#3498db"><path d="M480-336q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Zm0-72q-40 0-68-28t-28-68q0-40 28-68t68-28q40 0 68 28t28 68q0 40-28 68t-68 28Zm0 192q-134 0-244.5-72T61-462q-5-9-7.5-18.5T51-500q0-10 2.5-19.5T61-538q64-118 174.5-190T480-800q134 0 244.5 72T899-538q5 9 7.5 18.5T909-500q0 10-2.5 19.5T899-462q-64 118-174.5 190T480-200Z"/></svg>
                                Ver
                            </button>
                            <button class="boton-descargar-doc" onclick="descargarDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#27ae60"><path d="M480-336 288-528l51-51 105 105v-342h72v342l105-105 51 51-192 192ZM263.72-192Q234-192 213-213.15T192-264v-72h72v72h432v-72h72v72q0 29.7-21.16 50.85Q725.68-192 695.96-192H263.72Z"/></svg>
                                Descargar
                            </button>
                            <?php if ($rol_usuario === 'veterinario'): ?>
                                <button class="boton-eliminar-doc" onclick="confirmarEliminarDocumento(<?php echo $doc['id_documento']; ?>, <?php echo $doc['id_historial']; ?>, '<?php echo htmlspecialchars($doc['archivo']); ?>', '<?php echo htmlspecialchars($doc['nombre_mascota']); ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#e74c3c"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg>
                                    Eliminar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else: 
                ?>
                    <div class="sin-citas" style="padding: 20px;">
                        <p>No hay certificados de vacunación registrados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ANÁLISIS Y ESTUDIOS -->
        <div class="categoria-doc">
            <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M240-96q-40 0-68-28t-28-68v-144h96v-528h576v660q0 45-31.5 76.5T708-96H240Zm467.79-72q15.21 0 25.71-10.35T744-204v-588H312v456h360v132q0 15.3 10.29 25.65Q692.58-168 707.79-168ZM360-600v-72h336v72H360Zm0 120v-72h336v72H360Z"/></svg> Análisis y Estudios</h4>
            <div class="lista-documentos">
                <?php
                if ($rol_usuario === 'veterinario') {
                    $consulta_docs_analisis = "SELECT dm.*, h.fecha, h.id_historial, m.nombre_mascota, m.id_mascota,
                                               u.nombre_usuario as nombre_dueno, u.apellido_usuario as apellido_dueno
                                               FROM documento_medico dm 
                                               JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                               JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                               JOIN usuarios u ON m.id_usuario = u.id_usuario
                                               WHERE dm.tipo = 'analisis'
                                               ORDER BY h.fecha DESC";
                } else {
                    $consulta_docs_analisis = "SELECT dm.*, h.fecha, h.id_historial, m.nombre_mascota, m.id_mascota
                                              FROM documento_medico dm 
                                              JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                              JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                              WHERE m.id_usuario = $usuario_id AND dm.tipo = 'analisis'
                                              ORDER BY h.fecha DESC";
                }
                $resultado_analisis = $conexion->query($consulta_docs_analisis);
                
                if ($resultado_analisis && $resultado_analisis->num_rows > 0):
                    while($doc = $resultado_analisis->fetch_assoc()):
                ?>
                    <div class="documento-item" data-documento-id="<?php echo $doc['id_documento']; ?>">
                        <span class="icono-doc"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m107-384-59-42 192-312 120 144 168-264 120 168 146-222 58 42-202 307-119-166-163 257-119-143-142 231Zm468.77 144Q616-240 644-267.77q28-27.78 28-68Q672-376 644.23-404q-27.78-28-68-28Q536-432 508-404.23q-28 27.78-28 68Q480-296 507.77-268q27.78 28 68 28ZM765-96l-98-98q-19.91 13-43.13 19.5Q600.65-168 576-168q-70 0-119-49t-49-119q0-70 49-119t119-49q70 0 119 49t49 119q0 24.65-6.5 47.87T718-245l98 98-51 51Z"/></svg></span>
                        <div class="info-doc">
                            <strong>Análisis - <?php echo htmlspecialchars($doc['nombre_mascota']); ?></strong>
                            <?php if ($rol_usuario === 'veterinario'): ?>
                                <p>Dueño: <?php echo htmlspecialchars($doc['nombre_dueno'] . ' ' . $doc['apellido_dueno']); ?></p>
                            <?php endif; ?>
                            <p>Subido el <?php echo date('d M Y', strtotime($doc['fecha'])); ?></p>
                        </div>
                        <div class="acciones-doc">
                            <button class="boton-ver-doc" onclick="verDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#3498db"><path d="M480-336q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Zm0-72q-40 0-68-28t-28-68q0-40 28-68t68-28q40 0 68 28t28 68q0 40-28 68t-68 28Zm0 192q-134 0-244.5-72T61-462q-5-9-7.5-18.5T51-500q0-10 2.5-19.5T61-538q64-118 174.5-190T480-800q134 0 244.5 72T899-538q5 9 7.5 18.5T909-500q0 10-2.5 19.5T899-462q-64 118-174.5 190T480-200Z"/></svg>
                                Ver
                            </button>
                            <button class="boton-descargar-doc" onclick="descargarDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#27ae60"><path d="M480-336 288-528l51-51 105 105v-342h72v342l105-105 51 51-192 192ZM263.72-192Q234-192 213-213.15T192-264v-72h72v72h432v-72h72v72q0 29.7-21.16 50.85Q725.68-192 695.96-192H263.72Z"/></svg>
                                Descargar
                            </button>
                            <?php if ($rol_usuario === 'veterinario'): ?>
                                <button class="boton-eliminar-doc" onclick="confirmarEliminarDocumento(<?php echo $doc['id_documento']; ?>, <?php echo $doc['id_historial']; ?>, '<?php echo htmlspecialchars($doc['archivo']); ?>', '<?php echo htmlspecialchars($doc['nombre_mascota']); ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#e74c3c"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg>
                                    Eliminar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else: 
                ?>
                    <div class="sin-citas" style="padding: 20px;">
                        <p>No hay análisis o estudios registrados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RECETAS MÉDICAS -->
        <div class="categoria-doc">
            <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#789DE5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg> Recetas Médicas</h4>
            <div class="lista-documentos">
                <?php
                if ($rol_usuario === 'veterinario') {
                    $consulta_docs_recetas = "SELECT dm.*, h.fecha, h.id_historial, m.nombre_mascota, m.id_mascota,
                                               u.nombre_usuario as nombre_dueno, u.apellido_usuario as apellido_dueno
                                               FROM documento_medico dm 
                                               JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                               JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                               JOIN usuarios u ON m.id_usuario = u.id_usuario
                                               WHERE dm.tipo = 'receta'
                                               ORDER BY h.fecha DESC";
                } else {
                    $consulta_docs_recetas = "SELECT dm.*, h.fecha, h.id_historial, m.nombre_mascota, m.id_mascota
                                             FROM documento_medico dm 
                                             JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                             JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                             WHERE m.id_usuario = $usuario_id AND dm.tipo = 'receta'
                                             ORDER BY h.fecha DESC";
                }
                $resultado_recetas = $conexion->query($consulta_docs_recetas);
                
                if ($resultado_recetas && $resultado_recetas->num_rows > 0):
                    while($doc = $resultado_recetas->fetch_assoc()):
                ?>
                    <div class="documento-item" data-documento-id="<?php echo $doc['id_documento']; ?>">
                        <span class="icono-doc"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#789DE5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg></span>
                        <div class="info-doc">
                            <strong>Receta Médica - <?php echo htmlspecialchars($doc['nombre_mascota']); ?></strong>
                            <?php if ($rol_usuario === 'veterinario'): ?>
                                <p>Dueño: <?php echo htmlspecialchars($doc['nombre_dueno'] . ' ' . $doc['apellido_dueno']); ?></p>
                            <?php endif; ?>
                            <p>Subido el <?php echo date('d M Y', strtotime($doc['fecha'])); ?></p>
                        </div>
                        <div class="acciones-doc">
                            <button class="boton-ver-doc" onclick="verDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#3498db"><path d="M480-336q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Zm0-72q-40 0-68-28t-28-68q0-40 28-68t68-28q40 0 68 28t28 68q0 40-28 68t-68 28Zm0 192q-134 0-244.5-72T61-462q-5-9-7.5-18.5T51-500q0-10 2.5-19.5T61-538q64-118 174.5-190T480-800q134 0 244.5 72T899-538q5 9 7.5 18.5T909-500q0 10-2.5 19.5T899-462q-64 118-174.5 190T480-200Z"/></svg>
                                Ver
                            </button>
                            <button class="boton-descargar-doc" onclick="descargarDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#27ae60"><path d="M480-336 288-528l51-51 105 105v-342h72v342l105-105 51 51-192 192ZM263.72-192Q234-192 213-213.15T192-264v-72h72v72h432v-72h72v72q0 29.7-21.16 50.85Q725.68-192 695.96-192H263.72Z"/></svg>
                                Descargar
                            </button>
                            <?php if ($rol_usuario === 'veterinario'): ?>
                                <button class="boton-eliminar-doc" onclick="confirmarEliminarDocumento(<?php echo $doc['id_documento']; ?>, <?php echo $doc['id_historial']; ?>, '<?php echo htmlspecialchars($doc['archivo']); ?>', '<?php echo htmlspecialchars($doc['nombre_mascota']); ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#e74c3c"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg>
                                    Eliminar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else: 
                ?>
                    <div class="sin-citas" style="padding: 20px;">
                        <p>No hay recetas médicas registradas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
    </div>
   <!-- Modal para agregar paciente (mascota) como veterinario -->
<div class="modal-agregar-paciente" id="modalAgregarPaciente">
    <div class="contenido-modal-paciente-moderno">
        <div class="encabezado-modal-paciente-moderno">
            <h3 class="titulo-modal-paciente-moderno"> Agregar Nuevo Paciente</h3>
            <button class="boton-cerrar-modal-paciente-moderno" onclick="cerrarModalAgregarPaciente()">×</button>
        </div>
        
        <form class="formulario-paciente-moderno" id="formularioPaciente" action="procesar-mascota-veterinario.php" method="POST" enctype="multipart/form-data">
            
            <!-- Información de la mascota -->
            <div class="grupo-input-moderno">
                <label class="etiqueta-input-moderno">Nombre de la mascota <span class="requerido-asterisco">*</span></label>
                <input type="text" class="input-moderno" name="nombre_mascota" placeholder="Ej: Max, Luna, Bella..." required>
            </div>

            <div class="fila-inputs-moderno">
                <div class="grupo-input-moderno">
                    <label class="etiqueta-input-moderno">Tipo <span class="requerido-asterisco">*</span></label>
                    <select class="select-moderno" name="tipo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="perro"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FBE6A3"><path d="M384-96q-55 0-93.5-38.5T252-228q0-14 2.5-19.5t.5-7.5q-2-2-8 .5t-19 2.5q-55 0-93.5-38.5T96-384q0-55 38.5-93.5T228-516q23 0 45 8t40 24l163-163q-16-17-24-39.5t-8-45.5q0-55 38.5-93.5T576-864q55 0 93.5 38.5T708-732q0 14-2.5 19.5t-.5 7.5q2 2 7.5-.5T732-708q55 0 93.5 38.5T864-576q0 55-38.5 93.5T732-444q-23 0-45-8.5T646-477L483-314q16 19 24.5 41t8.5 45q0 55-38.5 93.5T384-96Z"/></svg> Perro</option>
                        <option value="gato"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M120-384q-30 0-51-21t-21-51v-336q0-30 21-51t51-21h720q30 0 51 21t21 51v336q0 30-21 51t-51 21H120Zm25 144-4-72 671-35 4 72-671 35Zm-1 120v-72h672v72H144Zm276-384q75 0 143-28.5T672-624q2 44 38 70t82 26v-192q-46 0-82 26t-38 70q-42-61-109.5-90.5T420-744q-75 0-144 28.5T168-624q38 64 107.5 92T420-504Z"/></svg> Gato</option>
                        <option value="otro"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> Otro</option>
                    </select>
                </div>
                
                <div class="grupo-input-moderno">
                    <label class="etiqueta-input-moderno">Sexo <span class="requerido-asterisco">*</span></label>
                    <select class="select-moderno" name="sexo" required>
                        <option value="">Seleccionar sexo</option>
                        <option value="macho">♂ Macho</option>
                        <option value="hembra">♀ Hembra</option>
                    </select>
                </div>
            </div>

            <div class="fila-inputs-moderno">
                <div class="grupo-input-moderno">
                    <label class="etiqueta-input-moderno">Edad (años) <span class="requerido-asterisco">*</span></label>
                    <input type="number" class="input-moderno" name="edad_mascota" min="0" max="30" placeholder="0" required>
                </div>
                
                <div class="grupo-input-moderno">
                    <label class="etiqueta-input-moderno">Fecha de nacimiento</label>
                    <input type="date" class="input-moderno" name="cumpleanos_mascota">
                </div>
            </div>

            <div class="grupo-input-moderno">
                <label class="etiqueta-input-moderno">Foto de la mascota</label>
                <div class="contenedor-file-moderno">
                    <input type="file" class="input-file-moderno" name="foto_mascota" id="inputFotoPaciente" accept="image/*" onchange="previewImagenPaciente(this)">
                    <label for="inputFotoPaciente" class="label-file-moderno">
                        <span class="icono-file"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-264q72 0 120-49t48-119q0-69-48-118.5T480-600q-72 0-120 49.5T312-432q0 70 48 119t120 49Zm0-72q-42 0-69-27t-27-68q0-40 27-68.5t69-28.5q42 0 69 28.5t27 68.5q0 41-27 68t-69 27ZM168-144q-29 0-50.5-21.5T96-216v-432q0-29 21.5-50.5T168-720h120l72-96h240l72 96h120q30 0 51 21.5t21 50.5v432q0 29-21 50.5T792-144H168Z"/></svg></span>
                        <span class="texto-file">Seleccionar imagen</span>
                    </label>
                </div>
                <div class="preview-foto-paciente-moderno" id="previewFotoPaciente" style="display: none;">
                    <img src="" alt="Preview">
                    <button type="button" class="boton-eliminar-preview" onclick="eliminarPreview()">×</button>
                </div>
            </div>

            <!-- Separador visual -->
            <div class="separador-modal"></div>

            <!-- Información del dueño -->
            <div class="seccion-dueno-moderno">
                <h4 class="subtitulo-seccion-moderno"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg> Información del Dueño</h4>
                
                <div class="grupo-input-moderno">
                    <label class="etiqueta-input-moderno">Seleccionar dueño existente</label>
                    <select class="select-moderno" name="id_dueno_existente" id="selectDuenoExistente" onchange="toggleNuevoDueno()">
                        <option value="">-- Crear nuevo dueño --</option>
                        <?php 
                        // Obtener todos los usuarios (no veterinarios, no admins)
                        $consulta_usuarios = "SELECT id_usuario, nombre_usuario, apellido_usuario, email_usuario 
                                             FROM usuarios 
                                             WHERE rol = 'usuario' 
                                             ORDER BY nombre_usuario, apellido_usuario";
                        $resultado_usuarios = $conexion->query($consulta_usuarios);
                        
                        if ($resultado_usuarios && $resultado_usuarios->num_rows > 0):
                            while($usuario = $resultado_usuarios->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $usuario['id_usuario']; ?>">
                                <?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario'] . ' (' . $usuario['email_usuario'] . ')'); ?>
                            </option>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </select>
                    </div>
                <!-- Campos para nuevo dueño -->
                <div id="camposNuevoDueno" style="display: block;">
                    <div class="alerta-info-moderno">
                        <span class="icono-info"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="M407.74-240Q378-240 357-261.15 336-282.3 336-312v-67q-57-37.3-88.5-95.65Q216-533 216-600q0-110.31 76.78-187.16 76.78-76.84 187-76.84T667-787.16q77 76.85 77 187.16 0 66.82-31.5 125.41T624-379v67q0 29.7-21.18 50.85Q581.65-240 551.91-240H407.74ZM408-96q-20.4 0-34.2-13.8Q360-123.6 360-144v-24h240v24q0 20.4-13.8 34.2Q572.4-96 552-96H408Z"/></svg></span>
                        <p>Se creará un nuevo usuario que podrá iniciar sesión con estos datos</p>
                    </div>

                    <div class="grupo-input-moderno">
                        <label class="etiqueta-input-moderno">Nombre del dueño <span class="requerido-asterisco">*</span></label>
                        <input type="text" class="input-moderno" name="nombre_dueno" id="inputNombreDueno" placeholder="Nombre">
                    </div>

                    <div class="grupo-input-moderno">
                        <label class="etiqueta-input-moderno">Apellido del dueño <span class="requerido-asterisco">*</span></label>
                        <input type="text" class="input-moderno" name="apellido_dueno" id="inputApellidoDueno" placeholder="Apellido">
                    </div>

                    <div class="grupo-input-moderno">
                        <label class="etiqueta-input-moderno">Email del dueño <span class="requerido-asterisco">*</span></label>
                        <input type="email" class="input-moderno" name="email_dueno" id="inputEmailDueno" placeholder="ejemplo@correo.com">
                    </div>

                    <div class="grupo-input-moderno">
                        <label class="etiqueta-input-moderno">Contraseña inicial <span class="requerido-asterisco">*</span></label>
                        <input type="password" class="input-moderno" name="password_dueno" id="inputPasswordDueno" placeholder="Mínimo 6 caracteres" minlength="6">
                        <small class="texto-ayuda-moderno">
                            El dueño podrá cambiar esta contraseña después de iniciar sesión
                        </small>
                    </div>

                    <div class="grupo-input-moderno">
                        <label class="etiqueta-input-moderno">Teléfono (opcional)</label>
                        <input type="tel" class="input-moderno" name="telefono_dueno" id="inputTelefonoDueno" placeholder="Ej: 2494634099">
                    </div>
                </div>
            </div>

            <div class="botones-modal-moderno">
                <button type="button" class="boton-cancelar-moderno" onclick="cerrarModalAgregarPaciente()">Cancelar</button>
                <button type="submit" class="boton-guardar-moderno">Guardar Paciente</button>
            </div>
        </form>
    </div>
</div>
    <!-- Modal para nueva cita -->
    <div class="modal-nueva-cita" id="modalNuevaCita">
        <div class="contenido-modal-cita">
            <div class="encabezado-modal-cita">
                <h3 class="titulo-modal-cita">Agendar Nueva Cita</h3>
                <button class="boton-cerrar-modal-cita" onclick="cerrarModalCita()">×</button>
            </div>
            
            <form class="formulario-cita" id="formularioCita" method="POST">
                <input type="hidden" name="accion" value="agendar_cita">
                
                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita requerido">Seleccionar Mascota</label>
                    <select class="select-cita" name="id_mascota" required>
                        <option value="">Seleccionar mascota</option>
                        <?php 
                        $resultado_mascotas->data_seek(0);
                        while($mascota = $resultado_mascotas->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $mascota['id_mascota']; ?>">
                                <?php echo htmlspecialchars($mascota['nombre_mascota']); ?> (<?php echo ucfirst($mascota['tipo']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita requerido">Motivo de la Cita</label>
                    <select class="select-cita" name="motivo" required>
                        <option value="">Seleccionar motivo</option>
                        <option value="Consulta General">Consulta General</option>
                        <option value="Vacunación">Vacunación</option>
                        <option value="Revisión">Revisión</option>
                        <option value="Urgencia">Urgencia</option>
                        <option value="Control">Control</option>
                        <option value="Cirugía">Cirugía</option>
                        <option value="Análisis">Análisis</option>
                        <option value="Desparasitación">Desparasitación</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita requerido">Fecha</label>
                    <input type="date" class="input-cita" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita requerido">Hora</label>
                    <select class="select-cita" name="hora" required>
                        <option value="">Seleccionar hora</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="17:00">05:00 PM</option>
                        <option value="18:00">06:00 PM</option>
                    </select>
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita">Veterinaria/Clínica</label>
                    <input type="text" class="input-cita" name="clinica" placeholder="Nombre de la clínica veterinaria">
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita">Observaciones</label>
                    <textarea class="textarea-cita" name="observaciones" placeholder="Observaciones adicionales sobre la cita..."></textarea>
                </div>
            </form>

            <div class="botones-modal-cita">
                <button type="button" class="boton-cancelar-cita" onclick="cerrarModalCita()">Cancelar</button>
                <button type="button" class="boton-agendar-cita" onclick="guardarCita()">Agendar Cita</button>
            </div>
        </div>
    </div>

    <!-- Modal para nueva consulta -->
    <div class="modal-nueva-consulta" id="modalNuevaConsulta">
        <div class="contenido-modal-consulta">
            <div class="encabezado-modal-consulta">
                <h3 class="titulo-modal-consulta">Registrar Nueva Consulta</h3>
                <button class="boton-cerrar-modal-consulta" onclick="cerrarModalConsulta()">×</button>
            </div>
            
            <form class="formulario-consulta" id="formularioConsulta" method="POST">
                <input type="hidden" name="accion" value="registrar_consulta">
                
                <div class="grupo-input-consulta">
                    <label class="etiqueta-input-consulta requerido">Seleccionar Mascota</label>
                    <select class="select-consulta" name="id_mascota" required>
                        <option value="">Seleccionar mascota</option>
                        <?php 
                        $resultado_mascotas->data_seek(0);
                        while($mascota = $resultado_mascotas->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $mascota['id_mascota']; ?>">
                                <?php echo htmlspecialchars($mascota['nombre_mascota']); ?> (<?php echo ucfirst($mascota['tipo']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grupo-input-consulta">
                    <label class="etiqueta-input-consulta requerido">Fecha de la Consulta</label>
                    <input type="date" class="input-consulta" name="fecha_consulta" required>
                </div>

                <div class="grupo-input-consulta">
                    <label class="etiqueta-input-consulta requerido">Diagnóstico</label>
                    <textarea class="textarea-consulta" name="diagnostico" required placeholder="Diagnóstico del veterinario..."></textarea>
                </div>

                <div class="grupo-input-consulta">
                    <label class="etiqueta-input-consulta requerido">Tratamiento</label>
                    <textarea class="textarea-consulta" name="tratamiento" required placeholder="Tratamiento prescrito..."></textarea>
                </div>

                <div class="grupo-input-consulta">
                    <label class="etiqueta-input-consulta">Veterinario</label>
                    <input type="text" class="input-consulta" name="veterinario" placeholder="Nombre del veterinario">
                </div>

                <div class="grupo-input-consulta">
                    <label class="etiqueta-input-consulta">Observaciones</label>
                    <textarea class="textarea-consulta" name="observaciones_consulta" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </form>

            <div class="botones-modal-consulta">
                <button type="button" class="boton-cancelar-consulta" onclick="cerrarModalConsulta()">Cancelar</button>
                <button type="button" class="boton-guardar-consulta" onclick="guardarConsulta()">Guardar Consulta</button>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación de cita -->
    <div class="modal-confirmar-eliminar-cita" id="modalConfirmarEliminarCita">
        <div class="contenido-modal-eliminar">
            <div class="encabezado-modal-eliminar">
                <h3 class="titulo-modal-eliminar">Confirmar Eliminación de Cita</h3>
                <button class="boton-cerrar-modal-eliminar" onclick="cerrarModalEliminarCita()">×</button>
            </div>
            
            <div class="cuerpo-modal-eliminar">
                <div class="icono-advertencia"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg></div>
                <p>¿Estás seguro de que deseas eliminar esta cita veterinaria?</p>
                <p><strong>Mascota:</strong> <span id="mascotaEliminarCita"></span></p>
                <p><strong>Motivo:</strong> <span id="motivoEliminarCita"></span></p>
                <p class="texto-advertencia">Esta acción no se puede deshacer.</p>
            </div>

            <form id="formularioEliminarCita" method="POST">
                <input type="hidden" name="accion" value="eliminar_cita">
                <input type="hidden" name="id_cita" id="idCitaEliminar">
            </form>

            <div class="botones-modal-eliminar">
                <button type="button" class="boton-cancelar-eliminar" onclick="cerrarModalEliminarCita()">Cancelar</button>
                <button type="button" class="boton-confirmar-eliminar" onclick="eliminarCita()">Sí, Eliminar Cita</button>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación de consulta -->
    <div class="modal-confirmar-eliminar" id="modalConfirmarEliminar">
        <div class="contenido-modal-eliminar">
            <div class="encabezado-modal-eliminar">
                <h3 class="titulo-modal-eliminar">Confirmar Eliminación</h3>
                <button class="boton-cerrar-modal-eliminar" onclick="cerrarModalEliminar()">×</button>
            </div>
            
            <div class="cuerpo-modal-eliminar">
                <div class="icono-advertencia"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg></div>
                <p>¿Estás seguro de que deseas eliminar esta consulta médica?</p>
                <p><strong>Mascota:</strong> <span id="mascotaEliminar"></span></p>
                <p class="texto-advertencia">Esta acción no se puede deshacer.</p>
            </div>

            <form id="formularioEliminarConsulta" method="POST">
                <input type="hidden" name="accion" value="eliminar_consulta">
                <input type="hidden" name="id_historial" id="idHistorialEliminar">
            </form>

            <div class="botones-modal-eliminar">
                <button type="button" class="boton-cancelar-eliminar" onclick="cerrarModalEliminar()">Cancelar</button>
                <button type="button" class="boton-confirmar-eliminar" onclick="eliminarConsulta()">Sí, Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Modal de alerta para usuarios demo -->
    <div class="modal-alerta-demo" id="modalAlertaDemo">
        <div class="contenido-modal-alerta">
            <div class="encabezado-modal-alerta">
                <h3 class="titulo-modal-alerta"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
            </div>
            
            <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg></div>
                <p id="mensajeAlertaDemo">Para acceder a esta función necesitas iniciar sesión o registrarte.</p>
                
                <div class="detalles-alerta">
                    <h4>Con una cuenta podrás:</h4>
                    <ul id="listaBeneficiosAlerta">
                        <li>• Gestionar citas veterinarias</li>
                        <li>• Registrar consultas médicas</li>
                        <li>• Llevar historial de salud</li>
                    </ul>
                </div>
            </div>

            <div class="botones-modal-alerta">
                <button type="button" class="boton-cancelar-alerta" onclick="cerrarModalAlerta()">Más tarde</button>
                <button type="button" class="boton-login-alerta" onclick="irALogin()"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#AAC1F0"><path d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZM263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624h168L528-792v168Z"/></svg> Registrarse</button>
            </div>
        </div>
    </div>
    <!-- Modal para subir documento -->
<div class="modal-subir-documento" id="modalSubirDocumento">
    <div class="contenido-modal-documento">
        <div class="encabezado-modal-documento">
            <h3 class="titulo-modal-documento">📄 Subir Documento Médico</h3>
            <button class="boton-cerrar-modal-documento" onclick="cerrarModalSubirDocumento()">×</button>
        </div>
        
        <form class="formulario-documento" id="formularioSubirDocumento" enctype="multipart/form-data">
            
            <!-- Tipo de documento -->
            <div class="grupo-input-documento">
                <label class="etiqueta-input-documento requerido">Tipo de Documento</label>
                <select class="select-documento" name="tipo_documento" id="tipoDocumento" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="vacuna">📋 Certificado de Vacunación</option>
                    <option value="analisis">🔬 Análisis / Estudio</option>
                    <option value="receta">💊 Receta Médica</option>
                </select>
            </div>

            <!-- Seleccionar mascota -->
            <div class="grupo-input-documento">
                <label class="etiqueta-input-documento requerido">Paciente (Mascota)</label>
                <select class="select-documento" name="id_mascota" id="mascotaDocumento" required>
                    <option value="">Seleccionar mascota</option>
                    <?php 
                    // Obtener TODAS las mascotas para veterinarios
                    if ($rol_usuario === 'veterinario') {
                        $consulta_mascotas_docs = "SELECT m.id_mascota, m.nombre_mascota, m.tipo, 
                                                   u.nombre_usuario, u.apellido_usuario
                                                   FROM mascotas m 
                                                   JOIN usuarios u ON m.id_usuario = u.id_usuario
                                                   WHERE m.estado = 'activo'
                                                   ORDER BY m.nombre_mascota ASC";
                        $resultado_mascotas_docs = $conexion->query($consulta_mascotas_docs);
                        
                        if ($resultado_mascotas_docs && $resultado_mascotas_docs->num_rows > 0):
                            while($mascota = $resultado_mascotas_docs->fetch_assoc()): 
                    ?>
                            <option value="<?php echo $mascota['id_mascota']; ?>">
                                <?php echo htmlspecialchars($mascota['nombre_mascota']); ?> 
                                (<?php echo ucfirst($mascota['tipo']); ?>) - 
                                Dueño: <?php echo htmlspecialchars($mascota['nombre_usuario'] . ' ' . $mascota['apellido_usuario']); ?>
                            </option>
                    <?php 
                            endwhile;
                        endif;
                    }
                    ?>
                </select>
            </div>

            <!-- Título del documento -->
            <div class="grupo-input-documento">
                <label class="etiqueta-input-documento requerido">Título del Documento</label>
                <input type="text" 
                       class="input-documento" 
                       name="titulo_documento" 
                       id="tituloDocumento"
                       placeholder="Ej: Vacuna Antirrábica 2025"
                       maxlength="100"
                       required>
                <small class="texto-ayuda-documento">Máximo 100 caracteres</small>
            </div>

            <!-- Fecha del documento -->
            <div class="grupo-input-documento">
                <label class="etiqueta-input-documento requerido">Fecha del Documento</label>
                <input type="date" 
                       class="input-documento" 
                       name="fecha_documento" 
                       id="fechaDocumento"
                       required>
            </div>

            <!-- Descripción -->
            <div class="grupo-input-documento">
                <label class="etiqueta-input-documento">Descripción / Observaciones</label>
                <textarea class="textarea-documento" 
                          name="descripcion" 
                          id="descripcionDocumento"
                          placeholder="Observaciones adicionales sobre el documento..."
                          maxlength="500"></textarea>
                <small class="texto-ayuda-documento">Opcional - Máximo 500 caracteres</small>
            </div>

            <!-- Archivo -->
            <div class="grupo-input-documento">
                <label class="etiqueta-input-documento requerido">Archivo</label>
                <div class="contenedor-file-documento">
                    <input type="file" 
                           class="input-file-documento" 
                           name="archivo_documento" 
                           id="inputArchivoDocumento"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           onchange="previewArchivoDocumento(this)"
                           required>
                    <label for="inputArchivoDocumento" class="label-file-documento">
                        <span class="icono-file">📎</span>
                        <span class="texto-file">Seleccionar archivo</span>
                    </label>
                </div>
                <small class="texto-ayuda-documento">
                    Formatos: PDF, DOC, DOCX, JPG, PNG - Máximo 10MB
                </small>
                
                <!-- Preview del archivo -->
                <div class="preview-archivo-documento" id="previewArchivoDocumento" style="display: none;">
                    <div class="icono-archivo">📄</div>
                    <div class="info-archivo">
                        <span class="nombre-archivo"></span>
                    </div>
                    <button type="button" class="boton-eliminar-preview-doc" onclick="eliminarPreviewDocumento()">×</button>
                </div>
            </div>

            <!-- Alerta informativa -->
            <div class="alerta-info-documento">
                <span class="icono-info">ℹ️</span>
                <p>El documento quedará asociado al historial médico del paciente y será visible para su dueño.</p>
            </div>

        </form>

        <div class="botones-modal-documento">
            <button type="button" class="boton-cancelar-documento" onclick="cerrarModalSubirDocumento()">Cancelar</button>
            <button type="submit" form="formularioSubirDocumento" class="boton-subir-documento-modal" id="btnSubirDocumento">
                <span class="texto-boton">📤 Subir Documento</span>
                <span class="spinner-boton" style="display: none;">⏳</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación de documento -->
<div class="modal-confirmar-eliminar-documento" id="modalConfirmarEliminarDocumento">
    <div class="contenido-modal-eliminar">
        <div class="encabezado-modal-eliminar">
            <h3 class="titulo-modal-eliminar">⚠️ Confirmar Eliminación de Documento</h3>
            <button class="boton-cerrar-modal-eliminar" onclick="cerrarModalEliminarDocumento()">×</button>
        </div>
        
        <div class="cuerpo-modal-eliminar">
            <div class="icono-advertencia">⚠️</div>
            <p>¿Estás seguro de que deseas eliminar este documento médico?</p>
            <p><strong>Mascota:</strong> <span id="mascotaEliminarDoc"></span></p>
            <p><strong>Archivo:</strong> <span id="archivoEliminarDoc"></span></p>
            <p class="texto-advertencia">Esta acción eliminará tanto el archivo como el registro médico asociado y no se puede deshacer.</p>
        </div>

        <form id="formularioEliminarDocumento" style="display: none;">
            <input type="hidden" name="id_documento" id="idDocumentoEliminar">
            <input type="hidden" name="id_historial" id="idHistorialEliminar">
            <input type="hidden" name="archivo" id="archivoEliminar">
        </form>

        <div class="botones-modal-eliminar">
            <button type="button" class="boton-cancelar-eliminar" onclick="cerrarModalEliminarDocumento()">Cancelar</button>
            <button type="button" class="boton-confirmar-eliminar" onclick="eliminarDocumento()">Sí, Eliminar Documento</button>
        </div>
    </div>
</div>

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
    <script src="js/notificaciones.js"></script>
    <script src="js/veterinaria.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
</body>
</html>
<?php cerrarConexion(); ?>