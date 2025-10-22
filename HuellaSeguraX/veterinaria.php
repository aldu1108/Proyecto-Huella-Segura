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
    $consulta_proximas = "SELECT c.*, m.nombre_mascota, m.tipo, m.id_usuario, v.clinica as vet_clinica, v.especialidad,
                          u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario,
                          owner.nombre_usuario as nombre_dueno, owner.apellido_usuario as apellido_dueno,
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
    $consulta_proximas = "SELECT c.*, m.nombre_mascota, m.tipo, m.id_usuario, v.clinica as vet_clinica, v.especialidad,
                          u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario,
                          DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                          FROM citas_veterinarias c 
                          JOIN mascotas m ON c.id_mascota = m.id_mascota 
                          LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                          LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                          WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) >= '$fecha_hoy' 
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

// Obtener historial médico simple
$consulta_historial_simple = "SELECT h.*, m.nombre_mascota, m.tipo,
                               u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario
                               FROM historiales_medicos h 
                               JOIN mascotas m ON h.id_mascota = m.id_mascota 
                               LEFT JOIN veterinario v ON h.id_veterinario = v.id_veterinario
                               LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                               WHERE m.id_usuario = $usuario_id 
                               ORDER BY h.fecha DESC LIMIT 20";
$resultado_historial = $conexion->query($consulta_historial_simple);

// Obtener citas pasadas para mostrar en el historial
// CAMBIO: Si es veterinario, mostrar sus citas pasadas
if ($rol_usuario === 'veterinario' && $id_veterinario_actual) {
    $consulta_citas_pasadas = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica as vet_clinica,
                               u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario,
                               owner.nombre_usuario as nombre_dueno, owner.apellido_usuario as apellido_dueno,
                               DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                               FROM citas_veterinarias c 
                               JOIN mascotas m ON c.id_mascota = m.id_mascota 
                               JOIN usuarios owner ON m.id_usuario = owner.id_usuario
                               LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                               LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                               WHERE c.id_veterinario = $id_veterinario_actual 
                               AND DATE(c.fecha) < '$fecha_hoy' 
                               AND c.estado IN ('aceptada', 'completada')
                               ORDER BY c.fecha DESC LIMIT 10";
} else {
    $consulta_citas_pasadas = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica as vet_clinica, 
                               u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario,
                               DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                               FROM citas_veterinarias c 
                               JOIN mascotas m ON c.id_mascota = m.id_mascota 
                               LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                               LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                               WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) < '$fecha_hoy' 
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
                        u.nombre_usuario as nombre_dueno,
                            u.telefono_usuario, u.email_usuario,
                            DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                            FROM citas_veterinarias c 
                            JOIN mascotas m ON c.id_mascota = m.id_mascota 
                            JOIN usuarios u ON m.id_usuario = u.id_usuario
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
                ✅ <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
            <div id="mensajeError" class="mensaje-error" style="background: #E74C3C; color: white; padding: 16px; border-radius: 12px; margin-bottom: 20px; text-align: center;">
                ✖ <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <!-- Header del área veterinaria -->
        <section class="header-veterinaria">
            <h2 class="titulo-veterinaria">🏥 Área Veterinaria</h2>
            <p class="subtitulo-veterinaria">Gestión completa de la salud de tus mascotas</p>
        </section>

        <!-- Estadísticas veterinaria -->
        <section class="estadisticas-vet">
            <div class="tarjeta-stat-vet hoy">
                <span class="icono-stat-vet">📅</span>
                <div class="numero-stat-vet"><?php echo $citas_hoy_count; ?></div>
                <div class="texto-stat-vet">Citas Hoy</div>
            </div>

            <div class="tarjeta-stat-vet pendiente">
                <span class="icono-stat-vet">⏰</span>
                <div class="numero-stat-vet"><?php echo $citas_pendientes; ?></div>
                <div class="texto-stat-vet">Citas Pendientes</div>
            </div>

            <div class="tarjeta-stat-vet completadas">
                <span class="icono-stat-vet">📋</span>
                <div class="numero-stat-vet"><?php echo $total_consultas; ?></div>
                <div class="texto-stat-vet"><?php echo ($rol_usuario === 'veterinario') ? 'Citas Completadas' : 'Consultas Realizadas'; ?></div>
            </div>

            <div class="tarjeta-stat-vet">
                <span class="icono-stat-vet">🐾</span>
                <div class="numero-stat-vet"><?php echo $resultado_mascotas->num_rows; ?></div>
                <div class="texto-stat-vet">Mis Mascotas</div>
            </div>
        </section>

        <!-- Navegacion de secciones -->
        <nav class="navegacion-veterinaria">
            <button class="boton-seccion-vet activo" data-seccion="agenda">📅 Mi Agenda</button>
            <?php if ($rol_usuario === 'veterinario'): ?>
                <button class="boton-seccion-vet" data-seccion="pacientes">🐕 Pacientes</button>
            <?php endif; ?>
            <button class="boton-seccion-vet" data-seccion="historial">📋 Historial</button>
            <button class="boton-seccion-vet" data-seccion="documentos">📄 Documentos</button>
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
                    <h4>✅ Citas Confirmadas</h4>
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
                                <p>🐕 <strong>Mascota:</strong> <?php echo htmlspecialchars($cita['nombre_mascota']); ?> (<?php echo ucfirst($cita['tipo']); ?>)</p>
                                <?php if ($rol_usuario === 'veterinario'): ?>
                                    <p>👤 <strong>Dueño:</strong> <?php echo htmlspecialchars($cita['nombre_dueno'] . ' ' . $cita['apellido_dueno']); ?></p>
                                    <p>📱 <strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono_usuario'] ?: 'No disponible'); ?></p>
                                <?php endif; ?>
                                <p>🏥 <strong>Clínica:</strong> <?php echo htmlspecialchars($cita['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                <p>⏰ <strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                                <p>📅 <strong>Fecha completa:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha'])); ?></p>
                                <?php if ($cita['especialidad']): ?>
                                    <p>👨‍⚕️ <strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="acciones-cita">
                            <div class="estado-cita <?php echo $cita['estado']; ?>">
                                <?php 
                                    $estados_texto = [
                                        'pendiente' => '⏳ Pendiente',
                                        'aceptada' => '✅ Aceptada',
                                        'rechazada' => '❌ Rechazada',
                                        'programada' => '📅 Programada',
                                        'completada' => '✔️ Completada',
                                        'cancelada' => '🚫 Cancelada'
                                    ];
                                    echo $estados_texto[$cita['estado']] ?? ucfirst($cita['estado']);
                                ?>
                            </div>
                            <div class="botones-cita">
                                <button class="boton-eliminar-cita" onclick="confirmarEliminarCita(<?php echo $cita['id_cita']; ?>, '<?php echo htmlspecialchars($cita['nombre_mascota']); ?>', '<?php echo htmlspecialchars($cita['motivo']); ?>')">
                                    🗑️ Eliminar
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
                    <h4>🔔 Citas Pendientes de Aprobación</h4>
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
                                        <h5>📋 <?php echo htmlspecialchars($cita['motivo']); ?></h5>
                                        <p>🐕 <strong>Mascota:</strong> <?php echo htmlspecialchars($cita['nombre_mascota']); ?> (<?php echo ucfirst($cita['tipo']); ?>)</p>
                                        <p>👤 <strong>Dueño:</strong> <?php echo htmlspecialchars($cita['nombre_dueno'] . ' ' . $cita['apellido_dueno']); ?></p>
                                        <p>📱 <strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono_usuario'] ?: 'No disponible'); ?></p>
                                        <p>📧 <strong>Email:</strong> <?php echo htmlspecialchars($cita['email_usuario']); ?></p>
                                        <p>⏰ <strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                                        <p>📅 <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></p>
                                    </div>
                                </div>
                                <div class="acciones-cita-pendiente">
                                    <form method="POST" action="gestionar-citas-veterinario.php" style="display: inline;">
                                        <input type="hidden" name="accion" value="aceptar_cita">
                                        <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                        <button type="submit" class="boton-aceptar-cita" onclick="return confirm('¿Confirmas que deseas ACEPTAR esta cita?')">
                                            ✅ Aceptar
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="gestionar-citas-veterinario.php" style="display: inline;">
                                        <input type="hidden" name="accion" value="rechazar_cita">
                                        <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                        <button type="submit" class="boton-rechazar-cita" onclick="return confirm('¿Estás seguro de RECHAZAR esta cita?')">
                                            ❌ Rechazar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="sin-citas">
                        <p>✅ No tienes citas pendientes de aprobar</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <!-- TARJETA 2: Citas Pendientes de Aprobación USUARIOS NORMALES -->
            <div class="proximas-citas citas-pendientes-usuario">
                <div class="encabezado-citas-seccion">
                    <h4>⏳ Citas Pendientes de Aprobación</h4>
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
                    <p>💡 Estas citas están esperando la confirmación del veterinario</p>
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
                                <p>🐕 <strong>Mascota:</strong> <?php echo htmlspecialchars($cita['nombre_mascota']); ?> (<?php echo ucfirst($cita['tipo']); ?>)</p>
                                <p>🏥 <strong>Clínica:</strong> <?php echo htmlspecialchars($cita['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                <p>⏰ <strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                                <p>📅 <strong>Fecha completa:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha'])); ?></p>
                                <?php if ($cita['especialidad']): ?>
                                    <p>👨‍⚕️ <strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="acciones-cita">
                            <div class="estado-cita pendiente">
                                ⏳ Pendiente
                            </div>
                            <div class="botones-cita">
                                <button class="boton-eliminar-cita" onclick="confirmarEliminarCita(<?php echo $cita['id_cita']; ?>, '<?php echo htmlspecialchars($cita['nombre_mascota']); ?>', '<?php echo htmlspecialchars($cita['motivo']); ?>')">
                                    🗑️ Eliminar
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
                    <button class="boton-nueva-cita" onclick="window.location.href='mis-mascotas.php'">
                        + Agregar Mascota
                    </button>
                </div>

                <div class="lista-pacientes">
                    <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                        <?php 
                        $resultado_mascotas->data_seek(0);
                        while($mascota = $resultado_mascotas->fetch_assoc()): 
                        ?>
                            <div class="tarjeta-paciente">
                                <div class="info-cita">
                                    <div class="foto-paciente">
                                        <?php if (!empty($mascota['foto_mascota'])): ?>
                                            <img src="imagenes/perro.jpg" class="foto-paciente">
                                        <?php else: ?>
                                            <div class="placeholder-paciente"><?php echo ($mascota['tipo'] == 'perro') ? '🐕' : '🐱'; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="detalles-cita">
                                        <h5><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h5>
                                        <p><?php echo ucfirst($mascota['tipo']); ?> • <?php echo $mascota['edad_mascota']; ?> años</p>
                                        <p>♂ <?php echo ucfirst($mascota['sexo']); ?></p>
                                        <p>📅 Nació el <?php echo date('d M Y', strtotime($mascota['cumpleaños_mascota'])); ?></p>
                                    </div>
                                </div>
                                <div class="acciones-paciente">
                                    <button class="boton-ver-historial" onclick="verHistorialPaciente(<?php echo $mascota['id_mascota']; ?>)">
                                        Ver Historial
                                    </button>
                                    <button class="boton-nueva-cita-paciente" onclick="agendarCitaPaciente(<?php echo $mascota['id_mascota']; ?>)">
                                        Nueva Cita
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="sin-citas">
                            <p>No tienes mascotas registradas</p>
                            <button class="boton-agendar-primera" onclick="window.location.href='mis-mascotas.php'">
                                Agregar Primera Mascota
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Seccion Historial Medico  -->
                    <div class="encabezado-agenda">
                        <h4 class="subtitulo-historial">📅 Citas Realizadas</h4>
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
                    <h4 class="subtitulo-historial">📋 Consultas Médicas Registradas</h4>
                    <?php while($historial = $resultado_historial->fetch_assoc()): ?>
                        <div class="registro-medico" data-mascota="<?php echo $historial['id_mascota']; ?>">
                            <div class="encabezado-registro">
                                <div class="fecha-registro">
                                    📅 <?php echo date('d M Y', strtotime($historial['fecha'])); ?>
                                </div>
                                <div class="mascota-registro">
                                    🐕 <?php echo htmlspecialchars($historial['nombre_mascota']); ?>
                                </div>
                                <div class="tipo-registro">
                                    <span class="badge-consulta">📋 Consulta Médica</span>
                                </div>
                            </div>
                            
                            <div class="contenido-registro">
                                <div class="diagnostico">
                                    <h5>📋 Diagnóstico</h5>
                                    <p><?php echo htmlspecialchars($historial['diagnostico']); ?></p>
                                </div>
                                
                                <div class="tratamiento">
                                    <h5>💊 Tratamiento</h5>
                                    <p><?php echo htmlspecialchars($historial['tratamiento']); ?></p>
                                </div>
                                
                                <div class="veterinario-registro">
                                    <h5>👩‍⚕️ Veterinario</h5>
                                    <p><?php echo htmlspecialchars(($historial['nombre_veterinario'] && $historial['apellido_veterinario']) ? $historial['nombre_veterinario'] . ' ' . $historial['apellido_veterinario'] : 'Dr. Veterinario'); ?></p>
                                </div>

                                <div class="acciones-consulta">
                                    <button class="boton-eliminar-consulta" onclick="confirmarEliminarConsulta(<?php echo $historial['id_historial']; ?>, '<?php echo htmlspecialchars($historial['nombre_mascota']); ?>')">
                                        🗑️ Eliminar Consulta
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <!-- Citas pasadas (PARA TODOS) -->
                <?php if ($resultado_citas_pasadas && $resultado_citas_pasadas->num_rows > 0): ?>
                    <?php while($cita_pasada = $resultado_citas_pasadas->fetch_assoc()): ?>
                        <div class="registro-medico" data-mascota="<?php echo $cita_pasada['id_mascota']; ?>" data-dueno="<?php echo $cita_pasada['id_usuario'] ?? ''; ?>">
                            <div class="encabezado-registro">
                                <div class="fecha-registro">
                                    📅 <?php echo date('d M Y', strtotime($cita_pasada['fecha'])); ?>
                                </div>
                                <div class="mascota-registro">
                                    🐕 <?php echo htmlspecialchars($cita_pasada['nombre_mascota']); ?>
                                </div>
                                <div class="tipo-registro">
                                    <span class="badge-cita">📅 Cita Realizada</span>
                                </div>
                            </div>
                            
                            <div class="contenido-registro">
                                <div class="motivo-cita">
                                    <h5>📋 Motivo de la Cita</h5>
                                    <p><?php echo htmlspecialchars($cita_pasada['motivo']); ?></p>
                                </div>
                                
                                <?php if ($rol_usuario === 'veterinario'): ?>
                                    <div class="paciente-cita">
                                        <h5>👤 Paciente (Dueño)</h5>
                                        <p><?php echo htmlspecialchars($cita_pasada['nombre_dueno'] . ' ' . $cita_pasada['apellido_dueno']); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="clinica-cita">
                                    <h5>🏥 Clínica</h5>
                                    <p><?php echo htmlspecialchars($cita_pasada['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                </div>
                                
                                <?php if ($rol_usuario !== 'veterinario'): ?>
                                <div class="veterinario-registro">
                                    <h5>👩‍⚕️ Veterinario</h5>
                                    <p><?php echo htmlspecialchars(($cita_pasada['nombre_veterinario'] && $cita_pasada['apellido_veterinario']) ? $cita_pasada['nombre_veterinario'] . ' ' . $cita_pasada['apellido_veterinario'] : 'Dr. Veterinario'); ?></p>
                                </div>
                                <?php endif; ?>

                                <div class="fecha-completa-cita">
                                    <h5>📅 Fecha Completa</h5>
                                    <p><?php echo date('d/m/Y H:i', strtotime($cita_pasada['fecha'])); ?></p>
                                </div>

                                <div class="mascota-detalle-cita">
                                    <h5>🐕 Mascota</h5>
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
                        <h4>📋 Sin Registros Médicos</h4>
                        <p>Aún no hay registros médicos o citas realizadas para tus mascotas</p>
                    </div>
                <?php elseif ($rol_usuario === 'veterinario' && (!$resultado_citas_pasadas || $resultado_citas_pasadas->num_rows == 0)): ?>
                    <div class="sin-registros">
                        <h4>📋 Sin Citas Realizadas</h4>
                        <p>Aún no tienes citas completadas en tu historial</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sección Documentos -->
        <section class="seccion-veterinaria seccion-documentos" id="seccionDocumentos">
            <div class="encabezado-documentos">
                <h3>Documentos Médicos</h3>
                <?php if ($rol_usuario == 'demo'): ?>
                    <button class="boton-subir-documento" onclick="mostrarModalAlerta('Inicia sesión para subir documentos\n\nRegístrate para poder:\n• Subir documentos médicos\n• Organizar certificados\n• Mantener registros actualizados')">
                        📎 Subir Documento
                    </button>
                <?php else: ?>
                    <button class="boton-subir-documento" onclick="mostrarSubirDocumento()">
                        📎 Subir Documento
                    </button>
                <?php endif; ?>
            </div>

            <div class="categorias-documentos">
                <div class="categoria-doc">
                    <h4>🧾 Certificados de Vacunación</h4>
                    <div class="lista-documentos">
                        <?php
                        $consulta_docs_vacunas = "SELECT dm.*, h.fecha, m.nombre_mascota 
                                                 FROM documento_medico dm 
                                                 JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                                 JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                                 WHERE m.id_usuario = $usuario_id AND dm.tipo = 'vacuna'
                                                 ORDER BY h.fecha DESC";
                        $resultado_vacunas = $conexion->query($consulta_docs_vacunas);
                        
                        if ($resultado_vacunas && $resultado_vacunas->num_rows > 0):
                            while($doc = $resultado_vacunas->fetch_assoc()):
                        ?>
                            <div class="documento-item">
                                <span class="icono-doc">📄</span>
                                <div class="info-doc">
                                    <strong>Certificado de Vacunación - <?php echo htmlspecialchars($doc['nombre_mascota']); ?></strong>
                                    <p>Subido el <?php echo date('d M Y', strtotime($doc['fecha'])); ?></p>
                                </div>
                                <button class="boton-ver-doc" onclick="verDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">Ver</button>
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

                <div class="categoria-doc">
                    <h4>🧾 Análisis y Estudios</h4>
                    <div class="lista-documentos">
                        <?php
                        $consulta_docs_analisis = "SELECT dm.*, h.fecha, m.nombre_mascota 
                                                  FROM documento_medico dm 
                                                  JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                                  JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                                  WHERE m.id_usuario = $usuario_id AND dm.tipo = 'analisis'
                                                  ORDER BY h.fecha DESC";
                        $resultado_analisis = $conexion->query($consulta_docs_analisis);
                        
                        if ($resultado_analisis && $resultado_analisis->num_rows > 0):
                            while($doc = $resultado_analisis->fetch_assoc()):
                        ?>
                            <div class="documento-item">
                                <span class="icono-doc">📊</span>
                                <div class="info-doc">
                                    <strong>Análisis - <?php echo htmlspecialchars($doc['nombre_mascota']); ?></strong>
                                    <p>Subido el <?php echo date('d M Y', strtotime($doc['fecha'])); ?></p>
                                </div>
                                <button class="boton-ver-doc" onclick="verDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">Ver</button>
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

                <div class="categoria-doc">
                    <h4>📋 Recetas Médicas</h4>
                    <div class="lista-documentos">
                        <?php
                        $consulta_docs_recetas = "SELECT dm.*, h.fecha, m.nombre_mascota 
                                                 FROM documento_medico dm 
                                                 JOIN historiales_medicos h ON dm.id_historial = h.id_historial 
                                                 JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                                 WHERE m.id_usuario = $usuario_id AND dm.tipo = 'receta'
                                                 ORDER BY h.fecha DESC";
                        $resultado_recetas = $conexion->query($consulta_docs_recetas);
                        
                        if ($resultado_recetas && $resultado_recetas->num_rows > 0):
                            while($doc = $resultado_recetas->fetch_assoc()):
                        ?>
                            <div class="documento-item">
                                <span class="icono-doc">📋</span>
                                <div class="info-doc">
                                    <strong>Receta Médica - <?php echo htmlspecialchars($doc['nombre_mascota']); ?></strong>
                                    <p>Subido el <?php echo date('d M Y', strtotime($doc['fecha'])); ?></p>
                                </div>
                                <button class="boton-ver-doc" onclick="verDocumento('<?php echo htmlspecialchars($doc['archivo']); ?>')">Ver</button>
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
                <div class="icono-advertencia">⚠️</div>
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
                <div class="icono-advertencia">⚠️</div>
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
                <h3 class="titulo-modal-alerta">⚠️ Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
            </div>
            
            <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo">🔒</div>
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
                <button type="button" class="boton-login-alerta" onclick="irALogin()">🔓 Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()">📝 Registrarse</button>
            </div>
        </div>
    </div>

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
    <script src="js/veterinaria.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
</body>
</html>
<?php cerrarConexion(); ?>