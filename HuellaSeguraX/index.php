<?php
include_once('config/conexion.php');
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];

$rol_usuario = $_SESSION['rol'] ?? 'demo';

// Obtener mascotas del usuario
if ($rol_usuario === 'demo') {
    $resultado_mascotas = null; // Demo no tiene mascotas
} else {
    $consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo' ORDER BY nombre_mascota ASC";
    $resultado_mascotas = $conexion->query($consulta_mascotas);
}

// Variables para fechas
$fecha_hoy = date('Y-m-d');
$fecha_inicio_dia = date('Y-m-d 00:00:00');
$fecha_fin_dia = date('Y-m-d 23:59:59');
$fecha_fin_semana = date('Y-m-d 23:59:59', strtotime('+7 days'));
$hora_actual = date('Y-m-d H:i:s');

// Inicializar variables de consulta
$resultado_eventos_hoy = null;
$resultado_eventos_proximos = null;
$total_eventos_hoy = 0;
$dias_con_eventos = [];
$eventos_mes = [];

// Verificar rol y ejecutar consultas según corresponda
if ($rol_usuario === 'demo') {
    // Demo no tiene datos personales
    $resultado_eventos = null;
    $resultado_citas_hoy = null;
    $resultado_citas_proximas = null;
} else {
    // Obtener días con recordatorios para el calendario
    $consulta_dias_recordatorios = "SELECT DISTINCT DAY(c.fecha) as dia
                                    FROM citas_veterinarias c
                                    JOIN mascotas m ON c.id_mascota = m.id_mascota
                                    WHERE m.id_usuario = $usuario_id 
                                    AND MONTH(c.fecha) = MONTH(CURDATE())
                                    AND YEAR(c.fecha) = YEAR(CURDATE())
                                    AND c.estado = 'programada'
                                    UNION
                                    SELECT DISTINCT DAY(fecha) as dia
                                    FROM recordatorios_personales
                                    WHERE id_usuario = $usuario_id
                                    AND MONTH(fecha) = MONTH(CURDATE())
                                    AND YEAR(fecha) = YEAR(CURDATE())
                                    AND completado = 0";
    $resultado_dias = $conexion->query($consulta_dias_recordatorios);
    $dias_con_eventos = [];
    while($dia = $resultado_dias->fetch_assoc()) {
        $dias_con_eventos[] = (int)$dia['dia'];
    }

    // Obtener TODOS los eventos del mes para el calendario interactivo
    $consulta_eventos_mes_index = "(SELECT DISTINCT 'cita' as tipo, c.motivo as titulo, c.fecha, c.id_cita as id_evento, m.nombre_mascota, m.id_mascota
                                    FROM citas_veterinarias c
                                    JOIN mascotas m ON c.id_mascota = m.id_mascota
                                    WHERE m.id_usuario = $usuario_id 
                                    AND c.estado != 'completada'
                                    AND c.estado != 'cancelada')
                                UNION
                                (SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.fecha, r.id_recordatorio as id_evento, m.nombre_mascota, rm.id_mascota
                                    FROM recordatorios_personales r
                                    JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                                    JOIN mascotas m ON rm.id_mascota = m.id_mascota
                                    WHERE r.id_usuario = $usuario_id
                                    AND m.id_usuario = $usuario_id
                                    AND r.completado = 0)
                                UNION
                                (SELECT DISTINCT 'evento' as tipo, e.titulo, e.fecha, e.id_evento, '' as nombre_mascota, 0 as id_mascota
                                    FROM eventos_comunidad e
                                    JOIN asistentes_evento ae ON e.id_evento = ae.id_evento
                                    WHERE ae.id_usuario = $usuario_id
                                    AND e.estado = 'activo')
                                ORDER BY fecha ASC";
    $resultado_eventos_mes_index = $conexion->query($consulta_eventos_mes_index);

    // Convertir a JSON para JavaScript
    $eventos_mes_index = [];
    if ($resultado_eventos_mes_index) {
        while($evento = $resultado_eventos_mes_index->fetch_assoc()) {
            $eventos_mes_index[] = $evento;
        }
    }

    // Obtener TODOS los eventos del mes para el calendario interactivo
    $consulta_eventos_mes = "(SELECT DISTINCT 'cita' as tipo, c.motivo as titulo, c.fecha, c.id_cita as id_evento, 
                            m.nombre_mascota, m.id_mascota
                            FROM citas_veterinarias c
                            JOIN mascotas m ON c.id_mascota = m.id_mascota
                            WHERE m.id_usuario = $usuario_id 
                            AND c.estado != 'completada')
                            UNION
                            (SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.fecha, r.id_recordatorio as id_evento,
                            NULL as nombre_mascota, NULL as id_mascota
                            FROM recordatorios_personales r
                            WHERE r.id_usuario = $usuario_id
                            AND r.completado = 0)
                            UNION
                            (SELECT DISTINCT 'evento' as tipo, e.titulo, e.fecha, e.id_evento,
                            NULL as nombre_mascota, NULL as id_mascota
                            FROM eventos_comunidad e
                            JOIN asistentes_evento ae ON e.id_evento = ae.id_evento
                            WHERE ae.id_usuario = $usuario_id
                            AND e.estado = 'activo')
                            ORDER BY fecha ASC";
    $resultado_eventos_mes = $conexion->query($consulta_eventos_mes);

    // Convertir a JSON para JavaScript
    $eventos_mes = [];
    if ($resultado_eventos_mes) {
        while($evento = $resultado_eventos_mes->fetch_assoc()) {
            $eventos_mes[] = $evento;
        }
    }

    $consulta_eventos_hoy = "(SELECT DISTINCT 'cita' as tipo, c.motivo as titulo, c.fecha, c.id_cita as id_evento,
                            m.nombre_mascota
                            FROM citas_veterinarias c
                            JOIN mascotas m ON c.id_mascota = m.id_mascota
                            WHERE m.id_usuario = $usuario_id 
                            AND c.fecha >= '$fecha_inicio_dia'
                            AND c.fecha <= '$fecha_fin_dia'
                            AND c.estado != 'completada')
                            UNION
                            (SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.fecha, r.id_recordatorio as id_evento,
                            NULL as nombre_mascota
                            FROM recordatorios_personales r
                            WHERE r.id_usuario = $usuario_id
                            AND r.fecha >= '$fecha_inicio_dia'
                            AND r.fecha <= '$fecha_fin_dia'
                            AND r.completado = 0)
                            ORDER BY fecha ASC";
    $resultado_eventos_hoy = $conexion->query($consulta_eventos_hoy);
    $total_eventos_hoy = $resultado_eventos_hoy ? $resultado_eventos_hoy->num_rows : 0;

    $consulta_eventos_proximos = "(SELECT DISTINCT 'cita' as tipo, c.motivo as titulo, c.fecha, c.id_cita as id_evento,
                                m.nombre_mascota
                                FROM citas_veterinarias c
                                JOIN mascotas m ON c.id_mascota = m.id_mascota
                                WHERE m.id_usuario = $usuario_id 
                                AND c.fecha > '$fecha_fin_dia'
                                AND c.fecha <= '$fecha_fin_semana'
                                AND c.estado != 'completada')
                                UNION
                                (SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.fecha, r.id_recordatorio as id_evento,
                                NULL as nombre_mascota
                                FROM recordatorios_personales r
                                WHERE r.id_usuario = $usuario_id
                                AND r.fecha > '$fecha_fin_dia'
                                AND r.fecha <= '$fecha_fin_semana'
                                AND r.completado = 0)
                                UNION
                                (SELECT DISTINCT 'evento' as tipo, e.titulo, e.fecha, e.id_evento,
                                NULL as nombre_mascota
                                FROM eventos_comunidad e
                                JOIN asistentes_evento ae ON e.id_evento = ae.id_evento
                                WHERE ae.id_usuario = $usuario_id
                                AND e.fecha > '$fecha_fin_dia'
                                AND e.fecha <= '$fecha_fin_semana'
                                AND e.estado = 'activo')
                                ORDER BY fecha ASC LIMIT 5";
    $resultado_eventos_proximos = $conexion->query($consulta_eventos_proximos);
}

$consulta_perdidas = "SELECT p.*, m.nombre_mascota, m.tipo, m.foto_mascota, m.id_mascota,
                      pp.ultima_ubicacion, pp.fecha_perdida, pp.recompensa
                      FROM publicaciones p
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                      WHERE p.estado = 'activo'
                      ORDER BY pp.fecha_perdida DESC
                      LIMIT 3";
$resultado_perdidas = $conexion->query($consulta_perdidas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/modal-alerta-demo.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">

        <!-- Mensajes de éxito/error -->
        <?php if (isset($_GET['exito'])): ?>
            <div class="mensaje-exito" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #28a745; font-weight: 500; animation: slideInDown 0.3s ease-out;">
                <?php 
                echo match($_GET['exito']) {
                    'recordatorio_agregado' => '✓ Recordatorio creado correctamente',
                    'recordatorio_actualizado' => '✓ Recordatorio actualizado correctamente',
                    'eliminado' => '✓ Evento eliminado correctamente',
                    default => '✓ Operación exitosa'
                };
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mensaje-error" style="background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #dc3545; font-weight: 500; animation: slideInDown 0.3s ease-out;">
                <?php 
                echo match($_GET['error']) {
                    'datos_incompletos' => '✗ Faltan datos obligatorios',
                    'error_recordatorio' => '✗ Error al crear el recordatorio',
                    'error_relacion' => '✗ Error al asociar mascotas',
                    'error_actualizar' => '✗ Error al actualizar',
                    'eliminar' => '✗ Error al eliminar',
                    default => '✗ Ha ocurrido un error'
                };
                ?>
            </div>
        <?php endif; ?>

        <!-- Barra de búsqueda principal -->
        <div class="barra-busqueda-principal">
            <form action="buscar.php" method="GET" style="display: flex; width: 100%; gap: 0.5rem;">
                <input type="text" 
                    name="q" 
                    id="inputBusqueda"
                    class="input-busqueda-principal" 
                    placeholder="Buscar mascotas, veterinarios, recordatorios..."
                    autocomplete="off"
                    required>
                <button type="submit" class="boton-filtro-principal" title="Buscar"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#D35400"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg></button>
            </form>
            
            <!-- Resultados de búsqueda en tiempo real -->
            <div class="resultados-busqueda" id="resultadosBusqueda" style="display: none;"></div>
        </div>

        <!-- Mis Mascotas -->
        <section class="seccion-mis-mascotas">
            <h2 class="titulo-mis-mascotas">Mis Mascotas</h2>
            
            <div class="mascotas-grid">
                <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                    <?php while($mascota = $resultado_mascotas->fetch_assoc()): ?>
                        <div class="mascota-card-principal" onclick="window.location.href='perfil-mascota.php?id=<?php echo $mascota['id_mascota']; ?>'">
                            <img src="imagenes/<?php 
                                $foto = !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'perro.jpg';
                                echo file_exists('imagenes/' . $foto) ? $foto : 'mascota-default.jpg';
                            ?>" 
                            alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" 
                            class="mascota-foto-principal">
                            <div class="mascota-info-principal">
                                <h3 class="nombre-mascota-principal"><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h3>
                                <p class="detalles-mascota-principal"><?php echo ucfirst($mascota['tipo']); ?></p>
                                <p class="detalles-mascota-principal"><?php echo $mascota['edad_mascota']; ?> años</p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Botón agregar más mascotas -->
                    <div style="display: flex; align-items: center; justify-content: center; padding: 40px;">
                        <?php if ($rol_usuario == 'demo'): ?>
                            <a href="#" class="boton-agregar-mascota" onclick="mostrarModalAgregarMascota(); return false;">
                                + Agregar Primera Mascota
                            </a>
                        <?php else: ?>
                            <a href="mis-mascotas.php" class="boton-agregar-mascota">
                                Ver mis mascotas
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div style="font-size: 48px; margin-bottom: 16px;">🐕</div>
                        <p style="color: #666; font-size: 16px; margin-bottom: 20px;">¡Todavía no tienes mascotas registradas!</p>
                        <?php if ($rol_usuario == 'demo'): ?>
                            <a href=# class="boton-agregar-mascota" onclick="mostrarModalAlerta('Inicia sesión para agregar mascotas')">
                                + Agregar Primera Mascota
                            </a>
                        <?php else: ?>
                            <a href="mis-mascotas.php" class="boton-agregar-mascota" style="display: inline-flex;">
                                <span style="font-size: 20px;">+</span> + Agregar Primera Mascota
                            </a>
                        <?php endif; ?>                      
                    </div>
                <?php endif; ?>
            </div>

            <hr style="margin: 2rem 0; color: white">

            <!-- Banner de adopción -->
            <div class="banner-adopcion">
                <h3><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> ¿Buscas una nueva mascota?</h3>
                <p>Hay mascotas esperando un hogar. La adopción es amor puro.</p>
                <button class="boton-ver-adopciones" onclick="window.location.href='adopciones.php'">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> Ver Mascotas en Adopción
                </button>
            </div>
        </section>

        <hr style="margin: 2rem 0; color: white">

        <!-- Calendario de Cuidados -->
        <section class="calendario-cuidados">
            <div class="encabezado-calendario">
                <div>
                    <h3 class="titulo-calendario"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Calendario de Cuidados</h3>
                </div>
                <div class="navegacion-mes">
                    <button class="boton-nav-mes" onclick="cambiarMes(-1)">‹</button>
                    <span class="mes-actual" id="mesActual">septiembre de 2025</span>
                    <button class="boton-nav-mes" onclick="cambiarMes(1)">›</button>
                </div>
            </div>

            <div class="mini-calendario">
                <div class="encabezado-dias">
                    <div class="dia-semana">D</div>
                    <div class="dia-semana">L</div>
                    <div class="dia-semana">M</div>
                    <div class="dia-semana">X</div>
                    <div class="dia-semana">J</div>
                    <div class="dia-semana">V</div>
                    <div class="dia-semana">S</div>
                </div>
                
                <div class="dias-calendario" id="diasCalendario">
                    <!-- Los días se generarán dinámicamente con JavaScript -->
                </div>
            </div>

            <div class="eventos-hoy">
                <div class="encabezado-eventos-hoy">
                    <h4 class="titulo-eventos-hoy" id="tituloEventosDia"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Hoy</h4>
                    <span class="contador-eventos" id="contadorEventosDia"><?php echo $total_eventos_hoy; ?></span>
                </div>

                <div class="lista-eventos-hoy" id="listaEventosDia">
                    <?php if ($rol_usuario == 'demo'): ?>
                        <div class="sin-eventos">
                            <div class="icono-grande"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#EA3323"><path d="M180-80q-24 0-42-18t-18-42v-620q0-24 18-42t42-18h65v-60h65v60h340v-60h65v60h65q24 0 42 18t18 42v620q0 24-18 42t-42 18H180Zm0-60h600v-430H180v430Z"/></svg></div>
                            <p>Tu calendario está vacío</p>
                            <small>Inicia sesión o regístrate para ver tus recordatorios y eventos</small>
                        </div>
                    <?php else: ?>
                        <?php if ($resultado_eventos_hoy && $resultado_eventos_hoy->num_rows > 0): ?>
                            <?php while($evento = $resultado_eventos_hoy->fetch_assoc()): 
                                $es_urgente = ($evento['tipo'] == 'cita' && in_array($evento['titulo'], ['Urgencia', 'Vacunación']));
                            ?>
                                <div class="evento-hoy <?php echo $es_urgente ? 'urgente' : ''; ?>" 
                                    <?php if($evento['tipo'] == 'cita'): ?>
                                        onclick="window.location.href='veterinaria.php'" style="cursor: pointer;"
                                    <?php endif; ?>>
                                    <div class="icono-evento">
                                        <?php 
                                        if ($evento['tipo'] == 'recordatorio') {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZM263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624h168L528-792v168Z"/></svg>';
                                        } else if ($evento['tipo'] == 'cita') {
                                            echo match($evento['titulo']) {
                                                'Vacunación' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M178-513q-11-11-10.5-25t11.5-25l106-106-39-39-17 17q-11 11-25.5 11T178-691q-11-11-11-25.5t11-25.5l85-85q11-11 25.5-11t25.5 11q11 11 11 25.5T314-776l-17 17 39 39 106-107q11-11 25.5-11t25.5 10q11 11 11 25.5T493-777l-24 24 59 59-106 105q-11 11-11 26t11 25q11 11 25.5 11t25.5-11l106-105 59 59-106 105q-11 11-11 25.5t11 25.5q11 11 25 11t25-11l107-105 58 58q21 21 21 51t-21 51l-32 32 175 174H787L664-290l-31 31q-21 21-51 21t-51-21L253-537l-24 24q-11 11-25.5 11T178-513Z"/></svg>',
                                                'Análisis' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="M479.77-96Q400-96 344-152.16 288-208.32 288-288v-336q-30 0-51-21.15T216-696v-96q0-29.7 21.15-50.85Q258.3-864 288-864h384q29.7 0 50.85 21.15Q744-821.7 744-792v96q0 29.7-21.15 50.85Q701.7-624 672-624v336q0 79.68-56.23 135.84Q559.55-96 479.77-96Zm.23-72q50 0 85-35t35-85H480v-72h120v-72H480v-72h120v-120H360v336q0 50 35 85t85 35Z"/></svg>',
                                                'Cirugía' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg>',
                                                'Control' => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg>',
                                                'Urgencia' => '<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg>',
                                                default => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M354-144q-87.73 0-148.87-61.13Q144-266.27 144-354q0-42 16-81t45-68l252-252q29-29 68-45t81-16q87.73 0 148.87 61.13Q816-693.73 816-606q0 42-16 81t-45 68L503-205q-29 29-68 45t-81 16Zm249-264 101-100q20-20 30-45t10-52.67q0-57.24-40.55-97.78Q662.91-744 605.67-744 578-744 553-734t-45 30L408-603l195 195ZM354.33-216Q382-216 407-226t45-30l100-101-195-195-100 100q-20 20-30.5 45T216-354.33q0 57.24 40.55 97.78Q297.09-216 354.33-216Z"/></svg>'
                                            };
                                        } else {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#8C1AF6"><path d="m96-101 195-551 357 356L96-101Zm118-118 302-107-195-195-107 302Zm358-255-34-34 220-221q28-28 67.5-28t68.5 28l35 34-34 35-34-35q-14-14-34.5-14T792-695L572-474ZM435-610l-34-34 34-34q14-14 14-33.5T435-745l-34-35 34-34 34 34q27 29 27 68t-27 68l-34 34Zm69 68-35-34 119-119q14-14 14-34t-14-34l-68-67 34-34 68 68q29 29 29 68.5T622-660L504-542Zm135 136-34-34 85-85q29-27 68.5-27.5T826-525l68 68-34 34-68-68q-14-14-34-14t-34 14l-85 85ZM214-219Z"/></svg>';
                                        }
                                        ?>
                                    </div>
                                    <div class="info-evento">
                                        <div class="titulo-evento"><?php echo htmlspecialchars($evento['titulo']); ?></div>
                                        <div class="detalles-evento">
                                            <?php 
                                            if (!empty($evento['nombre_mascota'])) {
                                                echo htmlspecialchars($evento['nombre_mascota']) . ' • ';
                                            }
                                            echo date('H:i', strtotime($evento['fecha'])); 
                                            ?>
                                        </div>
                                    </div>
                                    <?php if ($evento['tipo'] == 'recordatorio'): ?>
                                        <div class="acciones-evento" onclick="event.stopPropagation()">
                                            <button class="btn-accion-evento btn-editar" onclick="event.stopPropagation(); editarEvento('<?php echo $evento['tipo']; ?>', <?php echo $evento['id_evento']; ?>)" title="Editar">✏️</button>
                                            <button class="btn-accion-evento btn-eliminar" onclick="event.stopPropagation(); eliminarEvento('<?php echo $evento['tipo']; ?>', <?php echo $evento['id_evento']; ?>)" title="Eliminar">🗑️</button>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($es_urgente): ?>
                                        <div class="estado-urgente">Urgente</div>
                                    <?php else: ?>
                                        <div class="estado-medio">Programado</div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="sin-eventos">
                                <div class="icono-grande"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#EA3323"><path d="M180-80q-24 0-42-18t-18-42v-620q0-24 18-42t42-18h65v-60h65v60h340v-60h65v60h65q24 0 42 18t18 42v620q0 24-18 42t-42 18H180Zm0-60h600v-430H180v430Z"/></svg></div>
                                <p>No hay eventos programados para hoy</p>
                                <small>Agenda una cita o crea un recordatorio</small>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="proximos-eventos">
                    <h4 class="titulo-proximos">Próximos eventos</h4>
                    <?php if ($rol_usuario == 'demo'): ?>
                        <div class="sin-eventos-proximos">Inicia sesión para ver eventos próximos</div>
                    <?php else: ?>
                        <?php if ($resultado_eventos_proximos && $resultado_eventos_proximos->num_rows > 0): ?>
                            <?php while($evento = $resultado_eventos_proximos->fetch_assoc()): 
                                $fecha_evento = new DateTime($evento['fecha']);
                                $hoy = new DateTime();
                                $diff = $hoy->diff($fecha_evento);
                            ?>
                                <div class="evento-proximo" 
                                    <?php if($evento['tipo'] == 'cita'): ?>
                                        onclick="window.location.href='veterinaria.php'" style="cursor: pointer;"
                                    <?php endif; ?>>
                                    <div class="info-evento-proximo">
                                        <?php 
                                        if ($evento['tipo'] === 'evento') {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#8C1AF6"><path d="m96-101 195-551 357 356L96-101Zm118-118 302-107-195-195-107 302Zm358-255-34-34 220-221q28-28 67.5-28t68.5 28l35 34-34 35-34-35q-14-14-34.5-14T792-695L572-474ZM435-610l-34-34 34-34q14-14 14-33.5T435-745l-34-35 34-34 34 34q27 29 27 68t-27 68l-34 34Zm69 68-35-34 119-119q14-14 14-34t-14-34l-68-67 34-34 68 68q29 29 29 68.5T622-660L504-542Zm135 136-34-34 85-85q29-27 68.5-27.5T826-525l68 68-34 34-68-68q-14-14-34-14t-34 14l-85 85ZM214-219Z"/></svg>' . htmlspecialchars($evento['titulo']);
                                        } elseif ($evento['tipo'] === 'cita') {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M354-144q-87.73 0-148.87-61.13Q144-266.27 144-354q0-42 16-81t45-68l252-252q29-29 68-45t81-16q87.73 0 148.87 61.13Q816-693.73 816-606q0 42-16 81t-45 68L503-205q-29 29-68 45t-81 16Zm249-264 101-100q20-20 30-45t10-52.67q0-57.24-40.55-97.78Q662.91-744 605.67-744 578-744 553-734t-45 30L408-603l195 195ZM354.33-216Q382-216 407-226t45-30l100-101-195-195-100 100q-20 20-30.5 45T216-354.33q0 57.24 40.55 97.78Q297.09-216 354.33-216Z"/></svg>' . htmlspecialchars($evento['titulo']);
                                            if (!empty($evento['nombre_mascota'])) {
                                                echo ' - ' . htmlspecialchars($evento['nombre_mascota']);
                                            }
                                        } else {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZM263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624v-168H264v624h432v-456H528ZM264-792v189-189 624-624Z"/></svg>' . htmlspecialchars($evento['titulo']);
                                        }
                                        ?>
                                    </div>
                                    <div class="fecha-evento-proximo">
                                        <?php 
                                        if ($diff->days == 0) echo "Hoy • " . $fecha_evento->format('H:i');
                                        elseif ($diff->days == 1) echo "Mañana • " . $fecha_evento->format('H:i');
                                        else echo $fecha_evento->format('D, j M • H:i');
                                        ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="sin-eventos-proximos">No hay eventos próximos esta semana</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Recordatorios Urgentes -->
        <section class="recordatorios-urgentes">
            <div class="section-header">
                <h3><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#d35400"><path d="M192-216v-72h48v-240q0-87 53.5-153T432-763v-53q0-20 14-34t34-14q20 0 34 14t14 34v53q85 16 138.5 82T720-528v240h48v72H192ZM479.79-96Q450-96 429-117.15T408-168h144q0 30-21.21 51t-51 21Z"/></svg> Recordatorios Urgentes</h3>
                <span class="count"><?php echo $total_eventos_hoy; ?> para hoy</span>
            </div>

            <?php if ($rol_usuario == 'demo'): ?>
                <button class="btn-agregar-recordatorio" onclick="mostrarModalAlerta('Inicia sesión para agregar recordatorios')" style="
                    width: 100%;
                    background: linear-gradient(135deg, #D35400 0%, #B8450E 100%);
                    color: white;
                    border: none;
                    padding: 14px 20px;
                    border-radius: 12px;
                    cursor: pointer;
                    margin-bottom: 20px;
                    font-weight: 600;
                    font-size: 15px;
                    transition: all 0.3s;
                    box-shadow: 0 4px 12px rgba(211, 84, 0, 0.3);
                ">
                    + Agregar Recordatorio
                </button>
            <?php else: ?>
                <button class="btn-agregar-recordatorio" onclick="mostrarModalRecordatorio()" style="
                    width: 100%;
                    background: linear-gradient(135deg, #D35400 0%, #B8450E 100%);
                    color: white;
                    border: none;
                    padding: 14px 20px;
                    border-radius: 12px;
                    cursor: pointer;
                    margin-bottom: 20px;
                    font-weight: 600;
                    font-size: 15px;
                    transition: all 0.3s;
                    box-shadow: 0 4px 12px rgba(211, 84, 0, 0.3);
                ">
                    + Agregar Recordatorio
                </button>
            <?php endif; ?>

            <div class="urgente-list">
                <?php if ($rol_usuario == 'demo'): ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#d35400"><path d="M192-216v-72h48v-240q0-87 53.5-153T432-763v-53q0-20 14-34t34-14q20 0 34 14t14 34v53q85 16 138.5 82T720-528v240h48v72H192ZM479.79-96Q450-96 429-117.15T408-168h144q0 30-21.21 51t-51 21Z"/></svg></div>
                        <h4>No tienes recordatorios</h4>
                        <p>Registra tus mascotas para recibir recordatorios de citas y cuidados</p>
                        <div style="margin-top: 20px;">
                            <a href="login.php" style="color: #D35400; text-decoration: none; margin-right: 10px;">Iniciar Sesión</a>
                            <span>|</span>
                            <a href="registro.php" style="color: #D35400; text-decoration: none; margin-left: 10px;">Registrarse</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    // Obtener recordatorios y citas de HOY para TODAS las mascotas
                    $consulta_recordatorios_hoy = "(SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.descripcion, r.fecha, 
                                                    r.id_recordatorio as id_evento, m.nombre_mascota, m.id_mascota
                                                    FROM recordatorios_personales r
                                                    LEFT JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                                                    LEFT JOIN mascotas m ON rm.id_mascota = m.id_mascota
                                                    WHERE r.id_usuario = $usuario_id
                                                    AND r.fecha >= '$fecha_inicio_dia'
                                                    AND r.fecha <= '$fecha_fin_dia'
                                                    AND r.completado = 0)
                                                UNION
                                                (SELECT DISTINCT 'cita' as tipo, c.motivo as titulo, NULL as descripcion, c.fecha, 
                                                c.id_cita as id_evento, m.nombre_mascota, m.id_mascota
                                                FROM citas_veterinarias c
                                                JOIN mascotas m ON c.id_mascota = m.id_mascota
                                                WHERE m.id_usuario = $usuario_id 
                                                AND c.fecha >= '$fecha_inicio_dia'
                                                AND c.fecha <= '$fecha_fin_dia'
                                                AND c.estado != 'completada')
                                                ORDER BY fecha ASC";
                    $resultado_recordatorios_hoy = $conexion->query($consulta_recordatorios_hoy);
                    
                    if ($resultado_recordatorios_hoy && $resultado_recordatorios_hoy->num_rows > 0): 
                        while($item = $resultado_recordatorios_hoy->fetch_assoc()): 
                            $es_urgente = ($item['tipo'] == 'cita' && in_array($item['titulo'], ['Urgencia', 'Vacunación']));
                            $texto_descripcion = '';
                            
                            if ($item['tipo'] == 'recordatorio' && !empty($item['descripcion'])) {
                                $texto_descripcion = htmlspecialchars($item['descripcion']);
                            } else if ($item['tipo'] == 'recordatorio') {
                                $texto_descripcion = 'Recordatorio';
                            } else {
                                $texto_descripcion = 'Cita veterinaria';
                            }
                    ?>
                        <div class="urgente-item <?php echo $es_urgente ? 'urgente' : ''; ?>" 
                            <?php if($item['tipo'] == 'cita'): ?>
                                onclick="window.location.href='veterinaria.php'" style="cursor: pointer;"
                            <?php endif; ?>>
                            <div class="urgente-info">
                                <span class="mascota-name">
                                    <?php echo $item['tipo'] == 'recordatorio' ? '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#A7C4E5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm0-72h528v-528H216v528Zm72-72h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8ZM216-216v-528 528Z"/></svg> ' : '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M354-144q-87.73 0-148.87-61.13Q144-266.27 144-354q0-42 16-81t45-68l252-252q29-29 68-45t81-16q87.73 0 148.87 61.13Q816-693.73 816-606q0 42-16 81t-45 68L503-205q-29 29-68 45t-81 16Zm249-264 101-100q20-20 30-45t10-52.67q0-57.24-40.55-97.78Q662.91-744 605.67-744 578-744 553-734t-45 30L408-603l195 195ZM354.33-216Q382-216 407-226t45-30l100-101-195-195-100 100q-20 20-30.5 45T216-354.33q0 57.24 40.55 97.78Q297.09-216 354.33-216Z"/></svg> '; ?>
                                    <?php echo htmlspecialchars($item['titulo']); ?>
                                    <?php if (!empty($item['nombre_mascota'])): ?>
                                        <span style="color: #999; font-weight: 400; font-size: 13px;">
                                            • <?php echo htmlspecialchars($item['nombre_mascota']); ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                                <span class="urgente-time"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm0-336Zm100 136 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51ZM479.78-168Q590-168 667-244.78t77-187Q744-542 667.22-619t-187-77Q370-696 293-619.22t-77 187Q216-322 292.78-245t187 77Z"/></svg> <?php echo date('H:i', strtotime($item['fecha'])); ?></span>
                                <?php if (!empty($texto_descripcion)): ?>
                                    <span class="urgente-label" style="background: <?php echo $item['tipo'] == 'recordatorio' ? '#3498db' : '#e74c3c'; ?>;">
                                        <?php echo $texto_descripcion; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($item['tipo'] == 'recordatorio'): ?>
                                <div class="acciones-urgente" onclick="event.stopPropagation()">
                                    <button class="btn-accion-pequeno btn-editar" onclick="event.stopPropagation(); editarEvento('<?php echo $item['tipo']; ?>', <?php echo $item['id_evento']; ?>)" title="Editar">✏️</button>
                                    <button class="btn-accion-pequeno btn-eliminar" onclick="event.stopPropagation(); eliminarEvento('<?php echo $item['tipo']; ?>', <?php echo $item['id_evento']; ?>)" title="Eliminar">🗑️</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="sin-eventos">
                            <div class="icono-grande"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#EA3323"><path d="M180-80q-24 0-42-18t-18-42v-620q0-24 18-42t42-18h65v-60h65v60h340v-60h65v60h65q24 0 42 18t18 42v620q0 24-18 42t-42 18H180Zm0-60h600v-430H180v430Z"/></svg></div>
                            <p>No hay recordatorios para hoy</p>
                            <small>¡Todo en orden!</small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="proximamente">
                <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Próximamente</h4>
                <?php if ($rol_usuario == 'demo'): ?>
                    <div style="text-align: center; padding: 15px; color: #95A5A6; font-size: 14px;">
                        <p>Inicia sesión para ver eventos próximos</p>
                    </div>
                <?php else: ?>
                    <?php 
                    // Obtener recordatorios FUTUROS (DESPUÉS de hoy) de TODAS las mascotas
                    $hora_actual = date('Y-m-d H:i:s');
                    $fecha_fin_semana = date('Y-m-d 23:59:59', strtotime('+7 days'));
                    
                    $consulta_recordatorios_futuros = "(SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.fecha, 
                                                        r.id_recordatorio as id_evento, m.nombre_mascota, m.id_mascota
                                                        FROM recordatorios_personales r
                                                        LEFT JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                                                        LEFT JOIN mascotas m ON rm.id_mascota = m.id_mascota
                                                        WHERE r.id_usuario = $usuario_id
                                                        AND r.fecha > '$hora_actual'
                                                        AND r.fecha <= '$fecha_fin_semana'
                                                        AND r.completado = 0)
                                                    UNION
                                                    (SELECT DISTINCT 'cita' as tipo, c.motivo as titulo, c.fecha, 
                                                    c.id_cita as id_evento, m.nombre_mascota, m.id_mascota
                                                    FROM citas_veterinarias c
                                                    JOIN mascotas m ON c.id_mascota = m.id_mascota
                                                    WHERE m.id_usuario = $usuario_id
                                                    AND c.fecha > '$hora_actual'
                                                    AND c.fecha <= '$fecha_fin_semana'
                                                    AND c.estado != 'completada' AND c.estado != 'cancelada')
                                                    ORDER BY fecha ASC
                                                    LIMIT 5";
                    
                    $resultado_recordatorios_futuros = $conexion->query($consulta_recordatorios_futuros);
                    
                    if ($resultado_recordatorios_futuros && $resultado_recordatorios_futuros->num_rows > 0): 
                        while($rec = $resultado_recordatorios_futuros->fetch_assoc()): 
                    ?>
                        <div class="proximo-item" 
                            <?php if($rec['tipo'] == 'cita'): ?>
                                onclick="window.location.href='veterinaria.php'" style="cursor: pointer;"
                            <?php endif; ?>>
                            <span class="proximo-info">
                                <?php echo $rec['tipo'] == 'recordatorio' ? '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm0-72h528v-528H216v528Zm72-72h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8ZM216-216v-528 528Z"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M354-144q-87.73 0-148.87-61.13Q144-266.27 144-354q0-42 16-81t45-68l252-252q29-29 68-45t81-16q87.73 0 148.87 61.13Q816-693.73 816-606q0 42-16 81t-45 68L503-205q-29 29-68 45t-81 16Zm249-264 101-100q20-20 30-45t10-52.67q0-57.24-40.55-97.78Q662.91-744 605.67-744 578-744 553-734t-45 30L408-603l195 195ZM354.33-216Q382-216 407-226t45-30l100-101-195-195-100 100q-20 20-30.5 45T216-354.33q0 57.24 40.55 97.78Q297.09-216 354.33-216Z"/></svg>'; ?>
                                <?php echo date('D j', strtotime($rec['fecha'])) . ' • ' . htmlspecialchars($rec['titulo']); ?>
                                <?php if (!empty($rec['nombre_mascota'])): ?>
                                    <span style="color: #999; font-size: 12px;">
                                        (<?php echo htmlspecialchars($rec['nombre_mascota']); ?>)
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="proximo-time"><?php echo date('H:i', strtotime($rec['fecha'])); ?></span>
                            <?php if ($rec['tipo'] == 'recordatorio'): ?>
                                <div class="acciones-proximo" onclick="event.stopPropagation()">
                                    <button class="btn-accion-mini" onclick="event.stopPropagation(); editarEvento('recordatorio', <?php echo $rec['id_evento']; ?>)" title="Editar">✏️</button>
                                    <button class="btn-accion-mini" onclick="event.stopPropagation(); eliminarEvento('recordatorio', <?php echo $rec['id_evento']; ?>)" title="Eliminar">🗑️</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; else: ?>
                        <div style="text-align: center; padding: 15px; color: #95A5A6; font-size: 14px;">
                            <p>Sin recordatorios próximos esta semana</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <a href="veterinaria.php" class="ver-todos">Ver todos los recordatorios →</a>
        </section>

        <hr style="margin: 2rem 0; color: white">
        
        <!-- Modal Recordatorio -->
        <div id="modalRecordatorio" class="modal">
            <div class="modal-contenido modal-pequeno">
                <div class="modal-header">
                    <h3><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#7CA7D8"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm0-72h528v-528H216v528Zm72-72h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8ZM216-216v-528 528Z"/></svg> Nuevo Recordatorio</h3>
                    <button class="btn-cerrar" onclick="cerrarModalRecordatorio()">×</button>
                </div>
                <form method="POST" action="procesar-recordatorio.php" class="modal-form">
                    <div class="form-grupo">
                        <label>Título *</label>
                        <input type="text" name="titulo" required maxlength="100">
                    </div>

                    <div class="form-grupo">
                        <label>Descripción</label>
                        <textarea name="descripcion" maxlength="255" rows="3"></textarea>
                    </div>

                    <div class="form-fila">
                        <div class="form-grupo">
                            <label>Fecha *</label>
                            <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-grupo">
                            <label>Hora *</label>
                            <input type="time" name="hora" required>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label>Selecciona mascota(s) *</label>
                        <div class="selector-mascotas">
                            <?php 
                            if ($resultado_mascotas) {
                                mysqli_data_seek($resultado_mascotas, 0);
                                while($m = $resultado_mascotas->fetch_assoc()): 
                            ?>
                                <label class="mascota-checkbox">
                                    <input type="checkbox" name="mascotas[]" value="<?php echo $m['id_mascota']; ?>">
                                    <span class="mascota-check-contenido">
                                        <span class="mascota-icono">
                                            <?php echo $m['tipo'] == 'perro' ? '🐕' : ($m['tipo'] == 'gato' ? '🐱' : '🐾'); ?>
                                        </span>
                                        <span class="mascota-nombre"><?php echo htmlspecialchars($m['nombre_mascota']); ?></span>
                                        <span class="mascota-check-mark">✓</span>
                                    </span>
                                </label>
                            <?php endwhile; } ?>
                        </div>
                        <small>Puedes seleccionar una o varias mascotas</small>
                    </div>

                    <div class="form-botones">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalRecordatorio()">Cancelar</button>
                        <button type="submit" class="btn-guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mascotas Perdidas -->
        <section class="mascotas-perdidas-index">
            <div class="encabezado-perdidas-index">
                <h3 class="titulo-perdidas-index"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M765-144 526-383q-30 22-65.79 34.5-35.79 12.5-76.18 12.5Q284-336 214-406t-70-170q0-100 70-170t170-70q100 0 170 70t70 170.03q0 40.39-12.5 76.18Q599-464 577-434l239 239-51 51ZM384-408q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Z"/></svg> Mascotas Perdidas</h3>
                <a href="mascotas-perdidas.php" class="enlace-ver-todas">Ver todas</a>
            </div>
            
            <?php if ($rol_usuario == 'demo'): ?>
                <button class="boton-reporte-index" onclick="mostrarModalAlerta('Inicia sesión para reportar mascotas perdidas')">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> ¡Reportar Mascota Perdida!
                </button>
            <?php else: ?>
                <button class="boton-reporte-index" onclick="window.location.href='mascotas-perdidas.php'">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> ¡Reportar Mascota Perdida!
                </button>
            <?php endif; ?>
            
            <div class="lista-perdidas-index">
                <?php if ($resultado_perdidas && $resultado_perdidas->num_rows > 0): ?>
                    <?php while($perdida = $resultado_perdidas->fetch_assoc()): ?>
                        <div class="perdida-item-index">
                            <?php if (!empty($perdida['foto'])): ?>
                                <img src="imagenes/<?php echo $perdida['foto']; ?>" alt="<?php echo htmlspecialchars($perdida['nombre_mascota']); ?>" class="foto-perdida-index">
                            <?php else: ?>
                                <div class="placeholder-perdida-index">📷</div>
                            <?php endif; ?>
                            
                            <div class="info-perdida-index">
                                <h4 class="nombre-perdida-index"><?php echo htmlspecialchars($perdida['nombre_mascota']); ?></h4>
                                <p class="detalles-perdida-index"><?php echo ucfirst($perdida['tipo']); ?> • <?php echo ucfirst($perdida['raza'] ?? 'Mestizo'); ?></p>
                                <p class="detalles-perdida-index"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg> <?php echo htmlspecialchars($perdida['ultima_ubicacion']); ?> • 
                                    <?php 
                                    $fecha_perdida = new DateTime($perdida['fecha_perdida']);
                                    $hoy = new DateTime();
                                    $dias = $hoy->diff($fecha_perdida)->days;
                                    echo "Hace $dias días";
                                    ?>
                                </p>
                            </div>
                            <span class="estado-perdida-index">PERDIDO</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Datos de ejemplo si no hay reportes reales -->
                    <div class="perdida-item-index">
                        <img src="imagenes/perro.jpg" alt="Buddy" class="foto-perdida-index">
                        <div class="info-perdida-index">
                            <h4 class="nombre-perdida-index">Buddy</h4>
                            <p class="detalles-perdida-index">Perro Labrador</p>
                            <p class="detalles-perdida-index"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg> Parque del Retiro • Hace 3 días</p>
                        </div>
                        <span class="estado-perdida-index">PERDIDO</span>
                    </div>
                    
                    <div class="perdida-item-index">
                        <img src="imagenes/perro.jpg" alt="Buddy" class="foto-perdida-index">
                        <div class="info-perdida-index">
                            <h4 class="nombre-perdida-index">Mimi</h4>
                            <p class="detalles-perdida-index">Gato Siamés</p>
                            <p class="detalles-perdida-index"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg> Gran Vía • Hace 5 días</p>
                        </div>
                        <span class="estado-perdida-index">PERDIDO</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ayuda-perdidas-index">
                <h4>👁 ¿Has visto alguna mascota perdida?</h4>
                <p>Tu ayuda puede ser crucial para reunir a una familia con su mascota.</p>
                <button class="boton-ver-perdidas" onclick="window.location.href='mascotas-perdidas.php'">
                    Ver Todas las Mascotas Perdidas
                </button>
            </div>
        </section>
    </main>

    <!-- Modal de alerta para usuarios demo -->
    <div class="modal-alerta-demo" id="modalAlertaDemo">
        <div class="contenido-modal-alerta">
            <div class="encabezado-modal-alerta">
                <h3 class="titulo-modal-alerta"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
            </div>
            
                <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm0-80h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80ZM240-160v-400 400Z"/></svg></div>
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
                <button type="button" class="boton-login-alerta" onclick="irALogin()"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M280-400q-33 0-56.5-23.5T200-480q0-33 23.5-56.5T280-560q33 0 56.5 23.5T360-480q0 33-23.5 56.5T280-400Zm0 160q-100 0-170-70T40-480q0-100 70-170t170-70q67 0 121.5 33t86.5 87h352l120 120-180 180-80-60-80 60-85-60h-47q-32 54-86.5 87T280-240Zm0-80q56 0 98.5-34t56.5-86h125l58 41 82-61 71 55 75-75-40-40H435q-14-52-56.5-86T280-640q-66 0-113 47t-47 113q0 66 47 113t113 47Z"/></svg> Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/></svg> Registrarse</button>
            </div>
        </div>
    </div>
    
    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
    <script src="js/notificaciones.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
    <script src=></script>
    <script>
    window.indexData = {
        eventosMes: <?php echo json_encode($eventos_mes); ?>,
        diasConEventos: <?php echo json_encode($dias_con_eventos); ?>
    };

    // Datos para el calendario del index
    window.indexCalendarioData = {
        diasConEventos: <?php echo json_encode($dias_con_eventos); ?>,
        eventosMes: <?php echo json_encode($eventos_mes_index); ?>,
        usuarioId: <?php echo $usuario_id; ?>
    };
    </script>
</body>
</html>