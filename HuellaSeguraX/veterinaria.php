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
    }
}

if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'cita_agendada':
            $mensaje_exito = "¡Cita agendada exitosamente!";
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
                
                // Insertar la cita usando el mismo formato que el archivo agendar-cita.php
                $consulta_insertar = "INSERT INTO citas_veterinarias (fecha, motivo, estado, id_mascota, id_veterinario) 
                                     VALUES ('$fecha_completa', '$motivo', 'programada', $id_mascota, 1)";
                
                if ($conexion->query($consulta_insertar)) {
                    $mensaje_exito = "¡Cita agendada exitosamente!";
                    header("Location: veterinaria.php");
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

// Obtener próximas citas (solo futuras y programadas) con todos los datos necesarios
$fecha_hoy = date('Y-m-d');
$consulta_proximas = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica as vet_clinica, v.especialidad,
                      u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario,
                      DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                      FROM citas_veterinarias c 
                      JOIN mascotas m ON c.id_mascota = m.id_mascota 
                      LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                      LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                      WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) >= '$fecha_hoy' AND c.estado != 'cancelada'
                      ORDER BY c.fecha ASC LIMIT 5";
$resultado_proximas = $conexion->query($consulta_proximas);

// Obtener citas de hoy con todos los datos
$consulta_citas_hoy = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica as vet_clinica, v.especialidad,
                       DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                       FROM citas_veterinarias c 
                       JOIN mascotas m ON c.id_mascota = m.id_mascota 
                       LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                       WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) = '$fecha_hoy' AND c.estado != 'cancelada'
                       ORDER BY c.fecha ASC";
$resultado_citas_hoy = $conexion->query($consulta_citas_hoy);

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

// Obtener citas pasadas para mostrar en el historial con todos los datos
$consulta_citas_pasadas = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica as vet_clinica, v.especialidad,
                           u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario,
                           DATE(c.fecha) as fecha_solo, TIME(c.fecha) as hora_solo
                           FROM citas_veterinarias c 
                           JOIN mascotas m ON c.id_mascota = m.id_mascota 
                           LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                           LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                           WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) < '$fecha_hoy' AND c.estado != 'cancelada'
                           ORDER BY c.fecha DESC LIMIT 10";
$resultado_citas_pasadas = $conexion->query($consulta_citas_pasadas);

// Obtener mascotas para el selector
$consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
$resultado_mascotas = $conexion->query($consulta_mascotas);

// Contar estadísticas
$citas_pendientes = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias c 
                                     JOIN mascotas m ON c.id_mascota = m.id_mascota 
                                     WHERE m.id_usuario = $usuario_id AND c.estado = 'programada'")->fetch_assoc()['total'];

$citas_hoy_count = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias c 
                                    JOIN mascotas m ON c.id_mascota = m.id_mascota 
                                    WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) = '$fecha_hoy' AND c.estado != 'cancelada'")->fetch_assoc()['total'];

$total_consultas = $conexion->query("SELECT COUNT(*) as total FROM historiales_medicos h 
                                    JOIN mascotas m ON h.id_mascota = m.id_mascota 
                                    WHERE m.id_usuario = $usuario_id")->fetch_assoc()['total'];
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
                <div class="texto-stat-vet"> Consultas Realizadas</div>
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

        <!-- Seccion Mi Agenda (SIN historial de citas) -->
        <section class="seccion-veterinaria seccion-agenda activa" id="seccionAgenda">
            <div class="encabezado-agenda">
                <h3>Mi Agenda Veterinaria</h3>
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

            <!-- Acciones rapidas -->
            <div class="acciones-rapidas">
                <button class="boton-accion-rapida" onclick="verAgendaCompleta()">
                    <span class="icono-accion">📅</span>
                    <div class="titulo-accion">Ver Agenda Completa</div>
                    <div class="descripcion-accion">Todas las citas programadas</div>
                </button>
                
                <button class="boton-accion-rapida" onclick="verAgendaDelDia()">
                    <span class="icono-accion">📋</span>
                    <div class="titulo-accion">Ver Agenda del Día</div>
                    <div class="descripcion-accion">Citas de hoy</div>
                </button>
                
            </div>

            <!-- Proximas citas -->
            <div class="proximas-citas">
                <h4>Proximas Citas</h4>
                <?php if ($resultado_proximas && $resultado_proximas->num_rows > 0): ?>
                    <?php while($cita = $resultado_proximas->fetch_assoc()): ?>
                        <div class="tarjeta-cita <?php echo (date('Y-m-d', strtotime($cita['fecha'])) == $fecha_hoy) ? 'hoy' : 'proxima'; ?>">
                            <div class="info-cita">
                                <div class="fecha-cita">
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
                                <div class="estado-cita <?php echo $cita['estado']; ?>">
                                    <?php echo ucfirst($cita['estado']); ?>
                                </div>
                                <div class="botones-cita">
                                    <button class="boton-eliminar-cita" onclick="confirmarEliminarCita(<?php echo $cita['id_cita']; ?>, '<?php echo htmlspecialchars($cita['nombre_mascota']); ?>', '<?php echo htmlspecialchars($cita['motivo']); ?>')">
                                        🗑️ Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sin-citas">
                        <p>No tienes citas programadas próximamente</p>
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

        <!-- Seccion Historial Medico (INCLUYE citas pasadas) -->
        <section class="seccion-veterinaria seccion-historial" id="seccionHistorial">
            <div class="encabezado-historial">
                <h3>Historial Medico Completo</h3>
                <div class="filtros-historial">
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
                    <?php if ($rol_usuario == 'demo'): ?>
                        <button class="boton-nueva-consulta" onclick="mostrarModalAlerta('Inicia sesión para registrar consultas\n\nRegístrate para llevar el historial médico de tus mascotas')">
                            + Nueva Consulta
                        </button>
                    <?php else: ?>
                        <button class="boton-nueva-consulta" onclick="registrarNuevaConsulta()">
                            + Nueva Consulta
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="registros-medicos">
                <!-- Consultas médicas registradas -->
                <?php if ($resultado_historial && $resultado_historial->num_rows > 0): ?>
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

                <!-- Citas pasadas -->
                <?php if ($resultado_citas_pasadas && $resultado_citas_pasadas->num_rows > 0): ?>
                    <h4 class="subtitulo-historial">📅 Citas Realizadas</h4>
                    <?php while($cita_pasada = $resultado_citas_pasadas->fetch_assoc()): ?>
                        <div class="registro-medico" data-mascota="<?php echo $cita_pasada['id_mascota']; ?>">
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
                                
                                <div class="clinica-cita">
                                    <h5>🏥 Clínica</h5>
                                    <p><?php echo htmlspecialchars($cita_pasada['vet_clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                </div>
                                
                                <div class="veterinario-registro">
                                    <h5>👩‍⚕️ Veterinario</h5>
                                    <p><?php echo htmlspecialchars(($cita_pasada['nombre_veterinario'] && $cita_pasada['apellido_veterinario']) ? $cita_pasada['nombre_veterinario'] . ' ' . $cita_pasada['apellido_veterinario'] : 'Dr. Veterinario'); ?></p>
                                </div>

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

                <?php if ((!$resultado_historial || $resultado_historial->num_rows == 0) && (!$resultado_citas_pasadas || $resultado_citas_pasadas->num_rows == 0)): ?>
                    <div class="sin-registros">
                        <h4>📋 Sin Registros Médicos</h4>
                        <p>Aún no hay registros médicos o citas realizadas para tus mascotas</p>
                        <?php if ($rol_usuario == 'demo'): ?>
                            <button class="boton-agendar-primera" onclick="mostrarModalAlerta('Inicia sesión para registrar consultas\n\nCrea una cuenta para gestionar el historial médico')">
                                Registrar Primera Consulta
                            </button>
                        <?php else: ?>
                            <button class="boton-agendar-primera" onclick="registrarNuevaConsulta()">
                                Registrar Primera Consulta
                            </button>
                        <?php endif; ?>
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
                        // Consulta para obtener documentos de vacunación
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
                <button type="button" class="boton-login-alerta" onclick="irALogin()">🔐 Iniciar Sesión</button>
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