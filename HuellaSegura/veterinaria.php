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

// Obtener mascotas para el selector
$consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
$resultado_mascotas = $conexion->query($consulta_mascotas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Veterinaria - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header class="cabecera-principal">
        <nav class="navegacion-principal">
            <button class="boton-menu-hamburguesa" id="menuHamburguesa">☰</button>
            <div class="logo-contenedor">
                <h1 class="logo-texto">PetCare</h1>
            </div>
            <div class="iconos-derecha">
                <button class="boton-buscar">🔍</button>
                <button class="boton-compartir">⚡</button>
            </div>
        </nav>
        
        <div class="menu-lateral" id="menuLateral">
            <div class="opciones-menu">
                <a href="index.php" class="opcion-menu">🏠 Inicio</a>
                <a href="mis-mascotas.php" class="opcion-menu">🐕 Mis Mascotas</a>
                <a href="mascotas-perdidas.php" class="opcion-menu">🔍 Mascotas Perdidas</a>
                <a href="adopciones.php" class="opcion-menu">❤️ Adopciones</a>
                <a href="comunidad.php" class="opcion-menu">👥 Comunidad</a>
                <a href="veterinaria.php" class="opcion-menu">🏥 Veterinaria</a>
                <a href="logout.php" class="opcion-menu">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="contenedor-principal">
        <!-- Header del área veterinaria -->
        <section class="header-veterinaria">
            <h2 class="titulo-veterinaria">🏥 Área Veterinaria</h2>
            <p class="subtitulo-veterinaria">Gestión completa de la salud de tus mascotas</p>
        </section>

        <!-- Navegación de secciones -->
        <nav class="navegacion-veterinaria">
            <button class="boton-seccion-vet activo" data-seccion="agenda">📅 Mi Agenda</button>
            <button class="boton-seccion-vet" data-seccion="historial">📋 Historial Médico</button>
            <button class="boton-seccion-vet" data-seccion="documentos">📄 Documentos</button>
            <button class="boton-seccion-vet" data-seccion="recordatorios">🔔 Recordatorios</button>
        </nav>

        <!-- Sección Mi Agenda -->
        <section class="seccion-agenda" id="seccionAgenda">
            <div class="encabezado-agenda">
                <h3>Mi Agenda Veterinaria</h3>
                <button class="boton-nueva-cita" onclick="mostrarFormularioCita()">
                    + Agendar Nueva Cita
                </button>
            </div>

            <!-- Próximas citas -->
            <div class="proximas-citas">
                <h4>Próximas Citas</h4>
                <?php if ($resultado_proximas && $resultado_proximas->num_rows > 0): ?>
                    <?php while($cita = $resultado_proximas->fetch_assoc()): ?>
                        <div class="tarjeta-cita proxima">
                            <div class="info-cita">
                                <div class="fecha-cita">
                                    <span class="dia"><?php echo date('d', strtotime($cita['fecha'])); ?></span>
                                    <span class="mes"><?php echo date('M', strtotime($cita['fecha'])); ?></span>
                                </div>
                                <div class="detalles-cita">
                                    <h5><?php echo $cita['motivo']; ?></h5>
                                    <p>🐕 <?php echo $cita['nombre_mascota']; ?></p>
                                    <p>🏥 <?php echo $cita['clinica'] ?: 'Clínica no especificada'; ?></p>
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

            <!-- Historial de citas -->
            <div class="historial-citas">
                <h4>Historial de Citas</h4>
                <div class="lista-citas">
                    <?php if ($resultado_citas && $resultado_citas->num_rows > 0): ?>
                        <?php while($cita = $resultado_citas->fetch_assoc()): ?>
                            <div class="item-cita">
                                <div class="fecha-item"><?php echo formatearFecha($cita['fecha']); ?></div>
                                <div class="info-item">
                                    <strong><?php echo $cita['motivo']; ?></strong>
                                    <p><?php echo $cita['nombre_mascota']; ?> - <?php echo $cita['nombre_veterinario'] . ' ' . $cita['apellido_veterinario']; ?></p>
                                </div>
                                <div class="estado-item <?php echo $cita['estado']; ?>">
                                    <?php echo ucfirst($cita['estado']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="sin-historial">No hay citas registradas</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Sección Historial Médico -->
        <section class="seccion-historial" id="seccionHistorial" style="display: none;">
            <div class="encabezado-historial">
                <h3>Historial Médico</h3>
                <div class="filtros-historial">
                    <select class="filtro-mascota">
                        <option value="">Todas las mascotas</option>
                        <?php 
                        $resultado_mascotas->data_seek(0); // Resetear el puntero
                        while($mascota = $resultado_mascotas->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $mascota['id_mascota']; ?>">
                                <?php echo $mascota['nombre_mascota']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="registros-medicos">
                <?php if ($resultado_historial && $resultado_historial->num_rows > 0): ?>
                    <?php while($historial = $resultado_historial->fetch_assoc()): ?>
                        <div class="registro-medico">
                            <div class="encabezado-registro">
                                <div class="fecha-registro">
                                    <?php echo formatearFecha($historial['fecha']); ?>
                                </div>
                                <div class="mascota-registro">
                                    🐕 <?php echo $historial['nombre_mascota']; ?>
                                </div>
                            </div>
                            
                            <div class="contenido-registro">
                                <div class="diagnostico">
                                    <h5>📋 Diagnóstico</h5>
                                    <p><?php echo $historial['diagnostico']; ?></p>
                                </div>
                                
                                <div class="tratamiento">
                                    <h5>💊 Tratamiento</h5>
                                    <p><?php echo $historial['tratamiento']; ?></p>
                                </div>
                                
                                <div class="veterinario-registro">
                                    <h5>👨‍⚕️ Veterinario</h5>
                                    <p><?php echo $historial['nombre_veterinario'] . ' ' . $historial['apellido_veterinario']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sin-registros">
                        <h4>📋 Sin Registros Médicos</h4>
                        <p>Aún no hay registros médicos para tus mascotas</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sección Documentos -->
        <section class="seccion-documentos" id="seccionDocumentos" style="display: none;">
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
                                <strong>Vacuna Antirrábica - Luna</strong>
                                <p>Subido el 15 de Agosto, 2025</p>
                            </div>
                            <button class="boton-ver-doc">Ver</button>
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
                                <p