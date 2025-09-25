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
            
            // Verificar que la mascota pertenezca al usuario
            $verificar_mascota = "SELECT id_mascota FROM mascotas WHERE id_mascota = $id_mascota AND id_usuario = $usuario_id";
            $resultado_verificacion = $conexion->query($verificar_mascota);
            
            if ($resultado_verificacion->num_rows > 0) {
                $fecha_completa = $fecha . ' ' . $hora . ':00';
                
                // Insertar la cita
                $consulta_insertar = "INSERT INTO citas_veterinarias (fecha, motivo, estado, id_mascota, id_veterinario) 
                                     VALUES ('$fecha_completa', '$motivo', 'programada', $id_mascota, 1)";
                
                if ($conexion->query($consulta_insertar)) {
                    $mensaje_exito = "¡Cita agendada exitosamente!";
                } else {
                    $mensaje_error = "Error al agendar la cita. Inténtalo nuevamente.";
                }
            } else {
                $mensaje_error = "Mascota no válida.";
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

// Obtener citas veterinarias del usuario
$fecha_hoy = date('Y-m-d');
$consulta_citas = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica, v.especialidad,
                   u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario
                   FROM citas_veterinarias c 
                   JOIN mascotas m ON c.id_mascota = m.id_mascota 
                   LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                   LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                   WHERE m.id_usuario = $usuario_id 
                   ORDER BY c.fecha DESC LIMIT 10";
$resultado_citas = $conexion->query($consulta_citas);

// Obtener historial médico
$consulta_historial = "SELECT h.*, m.nombre_mascota, m.tipo,
                       u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario
                       FROM historiales_medicos h 
                       JOIN mascotas m ON h.id_mascota = m.id_mascota 
                       LEFT JOIN veterinario v ON h.id_veterinario = v.id_veterinario
                       LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                       WHERE m.id_usuario = $usuario_id 
                       ORDER BY h.fecha DESC LIMIT 20";
$resultado_historial = $conexion->query($consulta_historial);

// Obtener próximas citas
$consulta_proximas = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica, v.especialidad,
                      u.nombre_usuario as nombre_veterinario, u.apellido_usuario as apellido_veterinario
                      FROM citas_veterinarias c 
                      JOIN mascotas m ON c.id_mascota = m.id_mascota 
                      LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                      LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                      WHERE m.id_usuario = $usuario_id AND c.fecha >= '$fecha_hoy' AND c.estado != 'cancelada'
                      ORDER BY c.fecha ASC LIMIT 5";
$resultado_proximas = $conexion->query($consulta_proximas);

// Obtener citas de hoy
$consulta_citas_hoy = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica, v.especialidad
                       FROM citas_veterinarias c 
                       JOIN mascotas m ON c.id_mascota = m.id_mascota 
                       LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                       WHERE m.id_usuario = $usuario_id AND DATE(c.fecha) = '$fecha_hoy' AND c.estado != 'cancelada'
                       ORDER BY c.fecha ASC";
$resultado_citas_hoy = $conexion->query($consulta_citas_hoy);

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
                ❌ <?php echo $mensaje_error; ?>
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
                <div class="texto-stat-vet">Total Consultas</div>
            </div>

            <div class="tarjeta-stat-vet">
                <span class="icono-stat-vet">🐾</span>
                <div class="numero-stat-vet"><?php echo $resultado_mascotas->num_rows; ?></div>
                <div class="texto-stat-vet">Mis Mascotas</div>
            </div>
        </section>

        <!-- Navegación de secciones -->
        <nav class="navegacion-veterinaria">
            <button class="boton-seccion-vet activo" data-seccion="agenda">📅 Mi Agenda</button>
            <button class="boton-seccion-vet" data-seccion="pacientes">🐕 Pacientes</button>
            <button class="boton-seccion-vet" data-seccion="historial">📋 Historial</button>
            <button class="boton-seccion-vet" data-seccion="documentos">📄 Documentos</button>
        </nav>

        <!-- Sección Mi Agenda -->
        <section class="seccion-veterinaria seccion-agenda activa" id="seccionAgenda">
            <div class="encabezado-agenda">
                <h3>Mi Agenda Veterinaria</h3>
                <button class="boton-nueva-cita" onclick="mostrarFormularioCita()">
                    + Agendar Nueva Cita
                </button>
            </div>

            <!-- Acciones rápidas -->
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
                
                <button class="boton-accion-rapida" onclick="registrarNuevaConsulta()">
                    <span class="icono-accion">📝</span>
                    <div class="titulo-accion">Registrar Nueva Consulta</div>
                    <div class="descripcion-accion">Agregar consulta médica</div>
                </button>
            </div>

            <!-- Próximas citas -->
            <div class="proximas-citas">
                <h4>Próximas Citas</h4>
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
                                    <p>🐕 <?php echo htmlspecialchars($cita['nombre_mascota']); ?></p>
                                    <p>🏥 <?php echo htmlspecialchars($cita['clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                    <p>⏰ <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                                </div>
                            </div>
                            <div class="estado-cita <?php echo $cita['estado']; ?>">
                                <?php echo ucfirst($cita['estado']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sin-citas">
                        <p>No tienes citas programadas próximamente</p>
                        <button class="boton-agendar-primera" onclick="mostrarFormularioCita()">
                            Agendar Primera Cita
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Historial de citas recientes -->
            <div class="historial-citas">
                <h4>Historial Reciente de Citas</h4>
                <div class="lista-citas">
                    <?php if ($resultado_citas && $resultado_citas->num_rows > 0): ?>
                        <?php while($cita_hist = $resultado_citas->fetch_assoc()): ?>
                            <div class="tarjeta-cita">
                                <div class="info-cita">
                                    <div class="fecha-cita">
                                        <span class="dia"><?php echo date('d', strtotime($cita_hist['fecha'])); ?></span>
                                        <span class="mes"><?php echo date('M', strtotime($cita_hist['fecha'])); ?></span>
                                    </div>
                                    <div class="detalles-cita">
                                        <h5><?php echo htmlspecialchars($cita_hist['motivo']); ?></h5>
                                        <p>🐕 <?php echo htmlspecialchars($cita_hist['nombre_mascota']); ?></p>
                                        <p>🏥 <?php echo htmlspecialchars($cita_hist['clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                        <p>⏰ <?php echo date('H:i', strtotime($cita_hist['fecha'])); ?></p>
                                    </div>
                                </div>
                                <div class="estado-cita <?php echo $cita_hist['estado']; ?>">
                                    <?php echo ucfirst($cita_hist['estado']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="sin-citas">
                            <p>No hay historial de citas disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Sección Pacientes -->
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

        <!-- Sección Historial Médico -->
        <section class="seccion-veterinaria seccion-historial" id="seccionHistorial">
            <div class="encabezado-historial">
                <h3>Historial Médico</h3>
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
                    <button class="boton-nueva-consulta" onclick="registrarNuevaConsulta()">
                        + Nueva Consulta
                    </button>
                </div>
            </div>

            <div class="registros-medicos">
                <?php if ($resultado_historial && $resultado_historial->num_rows > 0): ?>
                    <?php while($historial = $resultado_historial->fetch_assoc()): ?>
                        <div class="registro-medico" data-mascota="<?php echo $historial['id_mascota']; ?>">
                            <div class="encabezado-registro">
                                <div class="fecha-registro">
                                    📅 <?php echo date('d M Y', strtotime($historial['fecha'])); ?>
                                </div>
                                <div class="mascota-registro">
                                    🐕 <?php echo htmlspecialchars($historial['nombre_mascota']); ?>
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
                                    <h5>👨‍⚕️ Veterinario</h5>
                                    <p><?php echo htmlspecialchars(($historial['nombre_veterinario'] && $historial['apellido_veterinario']) ? $historial['nombre_veterinario'] . ' ' . $historial['apellido_veterinario'] : 'Dr. Veterinario'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sin-registros">
                        <h4>📋 Sin Registros Médicos</h4>
                        <p>Aún no hay registros médicos para tus mascotas</p>
                        <button class="boton-agendar-primera" onclick="registrarNuevaConsulta()">
                            Registrar Primera Consulta
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sección Documentos -->
        <section class="seccion-veterinaria seccion-documentos" id="seccionDocumentos">
            <div class="encabezado-documentos">
                <h3>Documentos Médicos</h3>
                <button class="boton-subir-documento" onclick="mostrarSubirDocumento()">
                    📎 Subir Documento
                </button>
            </div>

            <div class="categorias-documentos">
                <div class="categoria-doc">
                    <h4>🩺 Certificados de Vacunación</h4>
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
                    <input type="date" class="input-consulta" name="fecha_consulta" required max="<?php echo date('Y-m-d'); ?>">
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

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
    <script src="js/veterinaria.js"></script>
</body>
</html>
<?php cerrarConexion(); ?>