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
                       ORDER BY h.fecha DESC LIMIT 5";
$resultado_historial = $conexion->query($consulta_historial);

// Obtener próximas citas
$consulta_proximas = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica, v.especialidad

                      FROM citas_veterinarias c 
                      JOIN mascotas m ON c.id_mascota = m.id_mascota 
                      LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                      WHERE m.id_usuario = $usuario_id AND c.fecha >= '$fecha_hoy' 
                      ORDER BY c.fecha ASC LIMIT 3";
$resultado_proximas = $conexion->query($consulta_proximas);

// Obtener citas de hoy
$consulta_citas_hoy = "SELECT c.*, m.nombre_mascota, m.tipo, v.clinica, v.especialidad
                       FROM citas_veterinarias c 
                       JOIN mascotas m ON c.id_mascota = m.id_mascota 
                       LEFT JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                       WHERE m.id_usuario = $usuario_id AND c.fecha = '$fecha_hoy'
                       ORDER BY c.fecha ASC";
$resultado_citas_hoy = $conexion->query($consulta_citas_hoy);

// Obtener mascotas para el selector
$consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
$resultado_mascotas = $conexion->query($consulta_mascotas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Veterinaria - PetCare</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Estilos específicos para veterinaria.php */
        .contenedor-veterinaria {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 16px 100px;
        }

        .header-veterinaria {
            text-align: left;
            margin-bottom: 30px;
        }

        .titulo-veterinaria {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .subtitulo-veterinaria {
            color: #999;
            font-size: 16px;
        }

        /* Navegación de secciones */
        .navegacion-veterinaria {
            display: flex;
            background: white;
            border-radius: 15px;
            padding: 6px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .boton-seccion-vet {
            flex: 1;
            padding: 12px 16px;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            transition: all 0.3s;
            white-space: nowrap;
            min-width: 120px;
        }

        .boton-seccion-vet.activo,
        .boton-seccion-vet:hover {
            background: #D35400;
            color: white;
        }

        /* Secciones principales */
        .seccion-veterinaria {
            display: none;
        }

        .seccion-veterinaria.activa {
            display: block;
        }

        /* Mi Agenda */
        .encabezado-agenda {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .encabezado-agenda h3 {
            font-size: 22px;
            color: #333;
            font-weight: 600;
        }

        .boton-nueva-cita {
            background: #27AE60;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .boton-nueva-cita:hover {
            background: #219A52;
            transform: translateY(-2px);
        }

        /* Botones de acción rápida */
        .acciones-rapidas {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .boton-accion-rapida {
            flex: 1;
            min-width: 180px;
            background: white;
            border: 2px solid #e8e8e8;
            padding: 16px;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }

        .boton-accion-rapida:hover {
            border-color: #D35400;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .boton-accion-rapida .icono-accion {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }

        .boton-accion-rapida .titulo-accion {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .boton-accion-rapida .descripcion-accion {
            font-size: 12px;
            color: #666;
        }

        /* Próximas citas */
        .proximas-citas {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .proximas-citas h4 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .tarjeta-cita {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            margin-bottom: 12px;
            transition: all 0.3s;
            background: white;
        }

        .tarjeta-cita:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .tarjeta-cita.proxima {
            border-left: 4px solid #27AE60;
        }

        .tarjeta-cita.hoy {
            border-left: 4px solid #E74C3C;
            background: #FFF8F8;
        }

        .info-cita {
            display: flex;
            gap: 16px;
            align-items: center;
            flex: 1;
        }

        .fecha-cita {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            min-width: 60px;
        }

        .fecha-cita .dia {
            display: block;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .fecha-cita .mes {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }

        .detalles-cita h5 {
            font-size: 16px;
            color: #333;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .detalles-cita p {
            font-size: 13px;
            color: #666;
            margin-bottom: 2px;
        }

        .estado-cita {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .estado-cita.programada {
            background: #E3F2FD;
            color: #1976D2;
        }

        .estado-cita.confirmada {
            background: #E8F5E8;
            color: #27AE60;
        }

        .estado-cita.completada {
            background: #F3E5F5;
            color: #8E24AA;
        }

        .estado-cita.cancelada {
            background: #FFEBEE;
            color: #E74C3C;
        }

        .sin-citas {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .sin-citas p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .boton-agendar-primera {
            background: #27AE60;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        /* Historial de citas */
        .historial-citas {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .historial-citas h4 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .item-cita {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #f8f8f8;
            transition: background 0.3s;
        }

        .item-cita:hover {
            background: #f8f9fa;
        }

        .item-cita:last-child {
            border-bottom: none;
        }

        .fecha-item {
            font-size: 12px;
            color: #666;
            width: 100px;
            flex-shrink: 0;
        }

        .info-item {
            flex: 1;
            margin-left: 16px;
        }

        .info-item strong {
            color: #333;
            font-size: 14px;
        }

        .info-item p {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }

        .estado-item {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Historial Médico */
        .encabezado-historial {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .encabezado-historial h3 {
            font-size: 22px;
            color: #333;
            font-weight: 600;
        }

        .filtros-historial {
            display: flex;
            gap: 12px;
        }

        .filtro-mascota {
            padding: 10px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            background: white;
            font-size: 14px;
            outline: none;
        }

        .filtro-mascota:focus {
            border-color: #D35400;
        }

        .registro-medico {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }

        .encabezado-registro {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .fecha-registro {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .mascota-registro {
            color: #3498db;
            font-weight: 600;
        }

        .contenido-registro {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .diagnostico, .tratamiento, .veterinario-registro {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .contenido-registro h5 {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .contenido-registro p {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }

        .sin-registros {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        /* Documentos */
        .encabezado-documentos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .encabezado-documentos h3 {
            font-size: 22px;
            color: #333;
            font-weight: 600;
        }

        .boton-subir-documento {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .boton-subir-documento:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .categoria-doc {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .categoria-doc h4 {
            font-size: 16px;
            color: #333;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .documento-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #f0f0f0;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .documento-item:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .icono-doc {
            font-size: 24px;
            margin-right: 12px;
            color: #3498db;
        }

        .info-doc {
            flex: 1;
        }

        .info-doc strong {
            color: #333;
            font-size: 14px;
        }

        .info-doc p {
            color: #666;
            font-size: 12px;
            margin-top: 2px;
        }

        .boton-ver-doc {
            background: #e8f4f8;
            color: #3498db;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        /* Modal para nueva cita */
        .modal-nueva-cita {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }

        .contenido-modal-cita {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .encabezado-modal-cita {
            padding: 24px 24px 0;
            text-align: center;
            position: relative;
        }

        .titulo-modal-cita {
            font-size: 20px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .boton-cerrar-modal-cita {
            position: absolute;
            top: 20px;
            right: 24px;
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
        }

        .formulario-cita {
            padding: 24px;
        }

        .grupo-input-cita {
            margin-bottom: 20px;
        }

        .etiqueta-input-cita {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .etiqueta-input-cita.requerido::after {
            content: " *";
            color: #E74C3C;
        }

        .input-cita, .select-cita, .textarea-cita {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .input-cita:focus, .select-cita:focus, .textarea-cita:focus {
            border-color: #27AE60;
        }

        .textarea-cita {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        .botones-modal-cita {
            display: flex;
            gap: 12px;
            padding: 24px;
            border-top: 1px solid #f0f0f0;
        }

        .boton-cancelar-cita {
            flex: 1;
            padding: 14px;
            border: 2px solid #e8e8e8;
            background: white;
            color: #666;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
        }

        .boton-agendar-cita {
            flex: 1;
            padding: 14px;
            background: #27AE60;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-agendar-cita:hover {
            background: #219A52;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navegacion-veterinaria {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .boton-seccion-vet {
                min-width: 100px;
                padding: 10px 12px;
                font-size: 12px;
            }

            .encabezado-agenda {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .acciones-rapidas {
                flex-direction: column;
            }

            .boton-accion-rapida {
                min-width: auto;
            }

            .info-cita {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .contenido-registro {
                grid-template-columns: 1fr;
            }

            .encabezado-registro {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?></header>
        
       
    <div class="contenedor-veterinaria">
        <!-- Header del área veterinaria -->
        <section class="header-veterinaria">
            <h2 class="titulo-veterinaria">🏥 Área Veterinaria</h2>
            <p class="subtitulo-veterinaria">Gestión completa de la salud de tus mascotas</p>
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
                        <div class="tarjeta-cita <?php echo ($cita['fecha'] == $fecha_hoy) ? 'hoy' : 'proxima'; ?>">
                            <div class="info-cita">
                                <div class="fecha-cita">
                                    <span class="dia"><?php echo date('d', strtotime($cita['fecha'])); ?></span>
                                    <span class="mes"><?php echo date('M', strtotime($cita['fecha'])); ?></span>
                                </div>
                                <div class="detalles-cita">
                                    <h5><?php echo htmlspecialchars($cita['motivo']); ?></h5>
                                    <p>🐕 <?php echo htmlspecialchars($cita['nombre_mascota']); ?></p>
                                    <p>🏥 <?php echo htmlspecialchars($cita['clinica'] ?: 'Clínica Veterinaria'); ?></p>
                                    <p>⏰ 10:00 AM</p>
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
                <h4>Historial Reciente</h4>
                <div class="lista-citas">
                    <?php if ($resultado_proximas && $resultado_proximas->num_rows > 0): ?>
                        <?php while (($cita = $resultado_proximas->fetch_assoc()) !== null && $cita !== false): ?>
                            <div class="tarjeta-cita <?php echo ($cita['fecha'] == $fecha_hoy) ? 'hoy' : 'proxima'; ?>">
                                <div class="info-cita">
                                    <div class="fecha-cita">
                                        <span class="dia"><?php echo date('d', strtotime($cita['fecha'])); ?></span>
                                        <span class="mes"><?php echo date('M', strtotime($cita['fecha'])); ?></span>
                                    </div>
                                    <div class="detalles-cita">
                                        <h5><?php echo htmlspecialchars($cita['motivo']); ?></h5>
                                        <p>🐕 <?php echo htmlspecialchars($cita['nombre_mascota']); ?></p>
                                        <p>🏥 <?php echo htmlspecialchars(!empty($cita['clinica']) ? $cita['clinica'] : 'Clínica Veterinaria'); ?>
                                        </p>
                                        <p>⏰ 10:00 AM</p>
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
                                        <img src="imagenes/<?php echo $mascota['foto_mascota']; ?>" alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>">
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
                    <button class="boton-nueva-cita" onclick="registrarNuevaConsulta()">
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
                                    <p><?php echo htmlspecialchars($historial['nombre_veterinario'] . ' ' . $historial['apellido_veterinario']); ?></p>
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
                        <div class="documento-item">
                            <span class="icono-doc">📄</span>
                            <div class="info-doc">
                                <strong>Vacuna Antirrábica - Max</strong>
                                <p>Subido el 15 de Septiembre, 2025</p>
                            </div>
                            <button class="boton-ver-doc" onclick="verDocumento('vacuna_max.pdf')">Ver</button>
                        </div>
                        
                        <div class="documento-item">
                            <span class="icono-doc">📄</span>
                            <div class="info-doc">
                                <strong>Certificado de Vacunación - Luna</strong>
                                <p>Subido el 10 de Septiembre, 2025</p>
                            </div>
                            <button class="boton-ver-doc" onclick="verDocumento('vacuna_luna.pdf')">Ver</button>
                        </div>
                    </div>
                </div>

                <div class="categoria-doc">
                    <h4>🧾 Análisis y Estudios</h4>
                    <div class="lista-documentos">
                        <div class="documento-item">
                            <span class="icono-doc">📊</span>
                            <div class="info-doc">
                                <strong>Análisis de Sangre - Max</strong>
                                <p>Subido el 5 de Septiembre, 2025</p>
                            </div>
                            <button class="boton-ver-doc" onclick="verDocumento('analisis_max.pdf')">Ver</button>
                        </div>
                    </div>
                </div>

                <div class="categoria-doc">
                    <h4>📋 Recetas Médicas</h4>
                    <div class="lista-documentos">
                        <div class="documento-item">
                            <span class="icono-doc">📋</span>
                            <div class="info-doc">
                                <strong>Receta Antibióticos - Luna</strong>
                                <p>Subido el 1 de Septiembre, 2025</p>
                            </div>
                            <button class="boton-ver-doc" onclick="verDocumento('receta_luna.pdf')">Ver</button>
                        </div>
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
            
            <form class="formulario-cita" id="formularioCita">
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

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="nav-btn active" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
    <script>
        // Navegación entre secciones
        document.addEventListener('DOMContentLoaded', function() {
            const botonesSeccion = document.querySelectorAll('.boton-seccion-vet');
            const secciones = document.querySelectorAll('.seccion-veterinaria');

            botonesSeccion.forEach(boton => {
                boton.addEventListener('click', function() {
                    const seccionTarget = this.dataset.seccion;
                    
                    // Remover clase activa de todos los botones
                    botonesSeccion.forEach(btn => btn.classList.remove('activo'));
                    this.classList.add('activo');
                    
                    // Ocultar todas las secciones
                    secciones.forEach(seccion => seccion.classList.remove('activa'));
                    
                    // Mostrar sección seleccionada
                    const seccionActiva = document.getElementById('seccion' + seccionTarget.charAt(0).toUpperCase() + seccionTarget.slice(1));
                    if (seccionActiva) {
                        seccionActiva.classList.add('activa');
                    }
                });
            });
        });

        // Funciones para los botones de acción
        function verAgendaCompleta() {
            showMessage('Mostrando agenda completa...', 'info');
            console.log('Ver agenda completa');
        }

        function verAgendaDelDia() {
            showMessage('Cargando citas de hoy...', 'info');
            console.log('Ver agenda del día');
            
            const citasHoy = document.querySelectorAll('.tarjeta-cita.hoy');
            if (citasHoy.length > 0) {
                showMessage(`Tienes ${citasHoy.length} cita(s) programada(s) para hoy`, 'success');
            } else {
                showMessage('No tienes citas programadas para hoy', 'info');
            }
        }

        function registrarNuevaConsulta() {
            showMessage('Abriendo formulario de nueva consulta...', 'info');
            console.log('Registrar nueva consulta');
            
            const btnHistorial = document.querySelector('[data-seccion="historial"]');
            if (btnHistorial) {
                btnHistorial.click();
            }
        }

        // Modal para nueva cita
        function mostrarFormularioCita() {
            document.getElementById('modalNuevaCita').style.display = 'flex';
            
            const inputFecha = document.querySelector('input[name="fecha"]');
            if (inputFecha) {
                const hoy = new Date();
                const fechaMin = hoy.toISOString().split('T')[0];
                inputFecha.min = fechaMin;
                inputFecha.value = fechaMin;
            }
        }

        function cerrarModalCita() {
            document.getElementById('modalNuevaCita').style.display = 'none';
            document.getElementById('formularioCita').reset();
        }

        function guardarCita() {
            const form = document.getElementById('formularioCita');
            const formData = new FormData(form);
            
            const camposRequeridos = form.querySelectorAll('[required]');
            let formValido = true;
            
            camposRequeridos.forEach(campo => {
                if (!campo.value.trim()) {
                    campo.style.borderColor = '#E74C3C';
                    formValido = false;
                } else {
                    campo.style.borderColor = '#e8e8e8';
                }
            });
            
            if (!formValido) {
                showMessage('Por favor completa todos los campos requeridos', 'error');
                return;
            }
            
            showMessage('¡Cita agendada exitosamente!', 'success');
            cerrarModalCita();
            
            console.log('Datos de la cita:', Object.fromEntries(formData));
        }

        document.getElementById('modalNuevaCita').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalCita();
            }
        });

        // Funciones para pacientes
        function verHistorialPaciente(idMascota) {
            showMessage('Cargando historial médico...', 'info');
            
            const btnHistorial = document.querySelector('[data-seccion="historial"]');
            if (btnHistorial) {
                btnHistorial.click();
            }
            
            setTimeout(() => {
                filtrarHistorial(idMascota);
            }, 300);
        }

        function agendarCitaPaciente(idMascota) {
            mostrarFormularioCita();
            
            setTimeout(() => {
                const selectMascota = document.querySelector('select[name="id_mascota"]');
                if (selectMascota) {
                    selectMascota.value = idMascota;
                }
            }, 100);
        }

        function filtrarHistorial(idMascota) {
            const registros = document.querySelectorAll('.registro-medico');
            
            registros.forEach(registro => {
                if (!idMascota || registro.dataset.mascota === idMascota) {
                    registro.style.display = 'block';
                } else {
                    registro.style.display = 'none';
                }
            });
            
            const mascotaSelect = document.querySelector('.filtro-mascota');
            if (mascotaSelect) {
                mascotaSelect.value = idMascota;
            }
            
            if (idMascota) {
                const nombreMascota = mascotaSelect.options[mascotaSelect.selectedIndex]?.text || 'esta mascota';
                showMessage(`Mostrando historial de ${nombreMascota}`, 'info');
            }
        }

        function mostrarSubirDocumento() {
            showMessage('Función de subir documento en desarrollo...', 'info');
            console.log('Mostrar formulario subir documento');
        }

        function verDocumento(archivo) {
            showMessage(`Abriendo documento: ${archivo}`, 'info');
            console.log('Ver documento:', archivo);
        }

        function showMessage(mensaje, tipo = 'info', duracion = 3000) {
            const mensajesExistentes = document.querySelectorAll('.mensaje-veterinaria');
            mensajesExistentes.forEach(m => m.remove());
            
            const mensajeDiv = document.createElement('div');
            mensajeDiv.className = `mensaje-veterinaria mensaje-${tipo}`;
            
            const iconos = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            
            mensajeDiv.innerHTML = `
                <span class="icono-mensaje">${iconos[tipo] || iconos.info}</span>
                <span class="texto-mensaje">${mensaje}</span>
                <button class="boton-cerrar-mensaje" onclick="this.parentElement.remove()">×</button>
            `;
            
            Object.assign(mensajeDiv.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                background: getColorMensaje(tipo),
                color: 'white',
                padding: '16px 20px',
                borderRadius: '12px',
                boxShadow: '0 4px 16px rgba(0,0,0,0.2)',
                zIndex: '10000',
                maxWidth: '400px',
                display: 'flex',
                alignItems: 'center',
                gap: '12px',
                animation: 'slideInRight 0.3s ease',
                fontSize: '14px',
                fontWeight: '500'
            });
            
            document.body.appendChild(mensajeDiv);
            
            setTimeout(() => {
                if (mensajeDiv.parentElement) {
                    mensajeDiv.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => mensajeDiv.remove(), 300);
                }
            }, duracion);
        }

        function getColorMensaje(tipo) {
            const colores = {
                success: '#27ae60',
                error: '#e74c3c',
                warning: '#f39c12',
                info: '#3498db'
            };
            return colores[tipo] || colores.info;
        }

        const estilosAnimaciones = document.createElement('style');
        estilosAnimaciones.textContent = `
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
            
            .mensaje-veterinaria .boton-cerrar-mensaje {
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                padding: 4px 8px;
                border-radius: 50%;
                cursor: pointer;
                font-size: 12px;
            }
            
            .mensaje-veterinaria .boton-cerrar-mensaje:hover {
                background: rgba(255,255,255,0.3);
            }
            
            .tarjeta-paciente {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px;
                border: 1px solid #f0f0f0;
                border-radius: 15px;
                margin-bottom: 12px;
                transition: all 0.3s;
                background: white;
            }
            
            .tarjeta-paciente:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                transform: translateY(-2px);
            }
            
            .foto-paciente {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                overflow: hidden;
                margin-right: 16px;
                flex-shrink: 0;
            }
            
            .foto-paciente img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .placeholder-paciente {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: #f0f0f0;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin-right: 16px;
            }
            
            .acciones-paciente {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .boton-ver-historial, .boton-nueva-cita-paciente {
                background: #e8f4f8;
                color: #3498db;
                border: none;
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .boton-nueva-cita-paciente {
                background: #e8f5e8;
                color: #27ae60;
            }
            
            .boton-ver-historial:hover, .boton-nueva-cita-paciente:hover {
                transform: translateY(-1px);
            }
            
            @media (max-width: 768px) {
                .acciones-paciente {
                    flex-direction: row;
                    gap: 6px;
                }
                
                .boton-ver-historial, .boton-nueva-cita-paciente {
                    font-size: 10px;
                    padding: 6px 8px;
                }
                
                .foto-paciente, .placeholder-paciente {
                    width: 50px;
                    height: 50px;
                    margin-right: 12px;
                }
                
                .tarjeta-paciente {
                    padding: 12px;
                }
                
                .info-cita {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 8px;
                }
            }
        `;
        document.head.appendChild(estilosAnimaciones);

        console.log('Veterinaria.php cargado correctamente');
    </script>
</body>
</html>
