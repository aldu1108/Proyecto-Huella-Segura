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
                <h3>❤️ ¿Buscas una nueva mascota?</h3>
                <p>Hay mascotas esperando un hogar. La adopción es amor puro.</p>
                <button class="boton-ver-adopciones" onclick="window.location.href='adopciones.php'">
                    ❤️ Ver Mascotas en Adopción
                </button>
            </div>
        </section>

        <hr style="margin: 2rem 0; color: white">

        <!-- Calendario de Cuidados -->
        <section class="calendario-cuidados">
            <div class="encabezado-calendario">
                <div>
                    <h3 class="titulo-calendario">📅 Calendario de Cuidados</h3>
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
                    <h4 class="titulo-eventos-hoy" id="tituloEventosDia">📅 Hoy</h4>
                    <span class="contador-eventos" id="contadorEventosDia"><?php echo $total_eventos_hoy; ?></span>
                </div>

                <div class="lista-eventos-hoy" id="listaEventosDia">
                    <?php if ($rol_usuario == 'demo'): ?>
                        <div class="sin-eventos">
                            <div class="icono-grande">📅</div>
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
                                            echo '📝';
                                        } else if ($evento['tipo'] == 'cita') {
                                            echo match($evento['titulo']) {
                                                'Vacunación' => '💉',
                                                'Análisis' => '🧪',
                                                'Cirugía' => '🏥',
                                                'Control' => '📋',
                                                'Urgencia' => '⚠️',
                                                default => '💊'
                                            };
                                        } else {
                                            echo '🎉';
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
                                <div class="icono-grande">📅</div>
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
                                            echo '🎉 ' . htmlspecialchars($evento['titulo']);
                                        } elseif ($evento['tipo'] === 'cita') {
                                            echo '💊 ' . htmlspecialchars($evento['titulo']);
                                            if (!empty($evento['nombre_mascota'])) {
                                                echo ' - ' . htmlspecialchars($evento['nombre_mascota']);
                                            }
                                        } else {
                                            echo '📝 ' . htmlspecialchars($evento['titulo']);
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
                <h3>🔔 Recordatorios Urgentes</h3>
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
                        <div style="font-size: 48px; margin-bottom: 16px;">🔔</div>
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
                                    <?php echo $item['tipo'] == 'recordatorio' ? '📝 ' : '💊 '; ?>
                                    <?php echo htmlspecialchars($item['titulo']); ?>
                                    <?php if (!empty($item['nombre_mascota'])): ?>
                                        <span style="color: #999; font-weight: 400; font-size: 13px;">
                                            • <?php echo htmlspecialchars($item['nombre_mascota']); ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                                <span class="urgente-time">🕐 <?php echo date('H:i', strtotime($item['fecha'])); ?></span>
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
                            <div class="icono-grande">✨</div>
                            <p>No hay recordatorios para hoy</p>
                            <small>¡Todo en orden!</small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="proximamente">
                <h4>📅 Próximamente</h4>
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
                                <?php echo $rec['tipo'] == 'recordatorio' ? '📝' : '💊'; ?>
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
                    <h3>📝 Nuevo Recordatorio</h3>
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
                <h3 class="titulo-perdidas-index">🔍 Mascotas Perdidas</h3>
                <a href="mascotas-perdidas.php" class="enlace-ver-todas">Ver todas</a>
            </div>
            
            <?php if ($rol_usuario == 'demo'): ?>
                <button class="boton-reporte-index" onclick="mostrarModalAlerta('Inicia sesión para reportar mascotas perdidas')">
                    ⚠️ ¡Reportar Mascota Perdida!
                </button>
            <?php else: ?>
                <button class="boton-reporte-index" onclick="window.location.href='mascotas-perdidas.php'">
                    ⚠️ ¡Reportar Mascota Perdida!
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
                                <p class="detalles-perdida-index">📍 <?php echo htmlspecialchars($perdida['ultima_ubicacion']); ?> • 
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
                            <p class="detalles-perdida-index">📍 Parque del Retiro • Hace 3 días</p>
                        </div>
                        <span class="estado-perdida-index">PERDIDO</span>
                    </div>
                    
                    <div class="perdida-item-index">
                        <img src="imagenes/perro.jpg" alt="Buddy" class="foto-perdida-index">
                        <div class="info-perdida-index">
                            <h4 class="nombre-perdida-index">Mimi</h4>
                            <p class="detalles-perdida-index">Gato Siamés</p>
                            <p class="detalles-perdida-index">📍 Gran Vía • Hace 5 días</p>
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
                <h3 class="titulo-modal-alerta">⚠️ Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">x</button>
            </div>
            
            <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo">🔐</div>
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
                <button type="button" class="boton-login-alerta" onclick="irALogin()">🔑 Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()">📝 Registrarse</button>
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