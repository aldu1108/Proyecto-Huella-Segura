<?php
include_once('config/conexion.php');
include_once('config/timezone.php');
session_start();

// Configurar zona horaria del servidor para evitar problemas de fecha
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mascota_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener información de la mascota
$consulta_mascota = "SELECT * FROM mascotas WHERE id_mascota = $mascota_id AND id_usuario = $usuario_id AND estado = 'activo'";
$resultado_mascota = $conexion->query($consulta_mascota);

if (!$resultado_mascota || $resultado_mascota->num_rows == 0) {
    header("Location: mis-mascotas.php");
    exit();
}

$mascota = $resultado_mascota->fetch_assoc();
$fecha_hoy = date('Y-m-d');
$hora_actual = date('Y-m-d H:i:s');
$fecha_fin_semana = date('Y-m-d', strtotime('+7 days'));
$fecha_inicio_dia = date('Y-m-d 00:00:00');
$fecha_fin_dia = date('Y-m-d 23:59:59');

// Obtener datos de seguimiento de peso
$consulta_peso = "SELECT * FROM seguimiento_peso WHERE id_mascota = $mascota_id ORDER BY fecha ASC";
$resultado_peso = $conexion->query($consulta_peso);

// Convertir resultado en array
$pesos = [];
if ($resultado_peso && $resultado_peso->num_rows > 0) {
    while($row = $resultado_peso->fetch_assoc()) {
        $pesos[] = $row;
    }
}

// Calcular variables para el gráfico
$max_peso = !empty($pesos) ? max(array_column($pesos, 'peso')) : 0;
$min_peso = !empty($pesos) ? min(array_column($pesos, 'peso')) : 0;
$rango = ($max_peso - $min_peso) > 0 ? ($max_peso - $min_peso) : 1;
$divisor = (count($pesos) - 1) > 0 ? (count($pesos) - 1) : 1;

// Obtener ficha de salud
$consulta_ficha = "SELECT * FROM fichas_de_salud WHERE id_mascota = $mascota_id LIMIT 1";
$resultado_ficha = $conexion->query($consulta_ficha);
$ficha = $resultado_ficha && $resultado_ficha->num_rows > 0 ? $resultado_ficha->fetch_assoc() : null;

// Obtener días con eventos para el calendario
$consulta_dias_mascota = "SELECT DISTINCT DAY(c.fecha) as dia
                         FROM citas_veterinarias c
                         WHERE c.id_mascota = $mascota_id 
                         AND MONTH(c.fecha) = MONTH(CURDATE())
                         AND YEAR(c.fecha) = YEAR(CURDATE())
                         AND c.estado != 'completada'
                         UNION
                         SELECT DISTINCT DAY(r.fecha) as dia
                         FROM recordatorios_personales r
                         JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                         WHERE rm.id_mascota = $mascota_id
                         AND r.id_usuario = $usuario_id
                         AND MONTH(r.fecha) = MONTH(CURDATE())
                         AND YEAR(r.fecha) = YEAR(CURDATE())
                         AND r.completado = 0";
$resultado_dias_mascota = $conexion->query($consulta_dias_mascota);
$dias_con_eventos_mascota = [];
if ($resultado_dias_mascota) {
    while($dia = $resultado_dias_mascota->fetch_assoc()) {
        $dias_con_eventos_mascota[] = (int)$dia['dia'];
    }
}

// Obtener eventos de hoy (citas + recordatorios)
$consulta_eventos_hoy = "(SELECT DISTINCT 'cita' as tipo, motivo as titulo, fecha, id_cita as id_evento
                         FROM citas_veterinarias 
                         WHERE id_mascota = $mascota_id 
                         AND fecha >= '$fecha_inicio_dia'
                         AND fecha <= '$fecha_fin_dia'
                         AND estado != 'completada')
                        UNION
                        (SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.fecha, r.id_recordatorio as id_evento
                         FROM recordatorios_personales r
                         JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                         WHERE rm.id_mascota = $mascota_id
                         AND r.id_usuario = $usuario_id
                         AND r.fecha >= '$fecha_inicio_dia'
                         AND r.fecha <= '$fecha_fin_dia'
                         AND r.completado = 0)
                        ORDER BY fecha ASC";
$resultado_eventos_hoy = $conexion->query($consulta_eventos_hoy);

// Obtener TODOS los eventos del mes para el calendario interactivo
$consulta_eventos_mes = "(SELECT DISTINCT 'cita' as tipo, motivo as titulo, fecha, id_cita as id_evento, id_mascota
                          FROM citas_veterinarias 
                          WHERE id_mascota = $mascota_id 
                          AND estado != 'completada')
                         UNION
                         (SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.fecha, r.id_recordatorio as id_evento, $mascota_id as id_mascota
                          FROM recordatorios_personales r
                          JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                          WHERE rm.id_mascota = $mascota_id
                          AND r.id_usuario = $usuario_id
                          AND r.completado = 0)
                         ORDER BY fecha ASC";
$resultado_eventos_mes = $conexion->query($consulta_eventos_mes);

// Convertir a JSON para JavaScript
$eventos_mes = [];
if ($resultado_eventos_mes) {
    while($evento = $resultado_eventos_mes->fetch_assoc()) {
        $eventos_mes[] = $evento;
    }
}

// Obtener citas de esta mascota
$consulta_citas_mes = "SELECT 'cita' as tipo, c.motivo as titulo, c.fecha, c.id_cita as id_evento, c.id_mascota
                       FROM citas_veterinarias c
                       WHERE c.id_mascota = $mascota_id
                       AND c.estado != 'completada'
                       AND c.estado != 'cancelada'
                       AND c.estado != 'rechazada'";
$resultado_citas_mes = $conexion->query($consulta_citas_mes);
if ($resultado_citas_mes) {
    while($cita = $resultado_citas_mes->fetch_assoc()) {
        $eventos_mes[] = $cita;
    }
}

// Obtener eventos de comunidad del usuario
$consulta_eventos_comunidad = "SELECT 'evento' as tipo, e.titulo, e.fecha, e.id_evento, $mascota_id as id_mascota
                               FROM eventos_comunidad e
                               JOIN asistentes_evento ae ON e.id_evento = ae.id_evento
                               WHERE ae.id_usuario = $usuario_id
                               AND e.estado = 'activo'";
$resultado_eventos_comunidad = $conexion->query($consulta_eventos_comunidad);
if ($resultado_eventos_comunidad) {
    while($evento = $resultado_eventos_comunidad->fetch_assoc()) {
        $eventos_mes[] = $evento;
    }
}

// Obtener eventos de comunidad
$consulta_eventos = "(SELECT 'evento' as tipo, e.titulo, e.fecha, e.id_evento
                      FROM eventos_comunidad e
                      JOIN asistentes_evento ae ON e.id_evento = ae.id_evento
                      WHERE ae.id_usuario = $usuario_id 
                      AND e.fecha > '$hora_actual'
                      AND e.fecha <= DATE_ADD('$hora_actual', INTERVAL 7 DAY)
                      AND e.estado = 'activo')
                     UNION
                     (SELECT 'cita' as tipo, c.motivo as titulo, c.fecha, c.id_cita as id_evento
                      FROM citas_veterinarias c
                      WHERE c.id_mascota = $mascota_id
                      AND c.fecha > '$hora_actual'
                      AND c.fecha <= DATE_ADD('$hora_actual', INTERVAL 7 DAY)
                      AND c.estado != 'completada' AND c.estado != 'cancelada')
                     ORDER BY fecha ASC LIMIT 5";
$resultado_eventos = $conexion->query($consulta_eventos);

// Obtener próximas citas
$consulta_citas_proximas = "SELECT * FROM citas_veterinarias 
                            WHERE id_mascota = $mascota_id 
                            AND fecha > '$hora_actual'
                            AND fecha <= DATE_ADD('$hora_actual', INTERVAL 7 DAY)
                            AND estado != 'completada'
                            ORDER BY fecha ASC LIMIT 3";
$resultado_citas_proximas = $conexion->query($consulta_citas_proximas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($mascota['nombre_mascota']); ?> - Perfil - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/perfil-mascota.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <main class="main-content">
        <!-- Mensajes -->
        <?php if (isset($_GET['exito'])): ?>
            <div class="mensaje-exito">
                <?php 
                echo match($_GET['exito']) {
                    'perfil_actualizado' => '✓ Perfil actualizado correctamente',
                    'peso_agregado' => '✓ Peso registrado correctamente',
                    'peso_actualizado' => '✓ Peso actualizado correctamente',
                    default => '✓ Operación exitosa'
                };
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mensaje-error">
                <?php 
                echo match($_GET['error']) {
                    'error_actualizar' => '✗ Error al actualizar el perfil',
                    'no_autorizado' => '✗ No autorizado',
                    'error_peso' => '✗ Error al registrar el peso',
                    default => '✗ Ha ocurrido un error'
                };
                ?>
            </div>
        <?php endif; ?>

        <!-- Header de la mascota -->
        <section class="mascota-header">
            <div class="mascota-info-principal">
                <img src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'mascota-default.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" 
                     class="mascota-foto-grande">
                <div class="mascota-datos">
                    <h1><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h1>
                    <p class="mascota-tipo"><?php echo ucfirst($mascota['tipo']) . ' • ' . ucfirst($mascota['sexo']); ?></p>
                    <div class="mascota-stats">
                        <span class="stat">🎂 <?php echo $mascota['edad_mascota']; ?> años</span>
                        <span class="stat">⚖️ <?php echo $ficha && !empty($ficha['peso']) ? $ficha['peso'] . ' kg' : 'Sin registrar'; ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Información detallada -->
        <section class="informacion-detallada">
            <h3>Información Detallada</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <label>Fecha de nacimiento:</label>
                    <span><?php echo date('d/m/Y', strtotime($mascota['cumpleanos_mascota'] ?? '2021-03-15')); ?></span>
                </div>
                <div class="info-item">
                    <label>Edad:</label>
                    <span><?php echo $mascota['edad_mascota']; ?> años</span>
                </div>
                <?php if ($ficha): ?>
                    <div class="info-item">
                        <label>Peso actual:</label>
                        <span><?php echo $ficha['peso']; ?> kg</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="boton-editar-container">
                <button onclick="mostrarModalEditar()" class="btn-editar-perfil">
                    ✏️ Editar Perfil
                </button>
            </div>
        </section>

        <!-- Calendario -->
        <section class="calendario-cuidados">
            <div class="encabezado-calendario">
                <h3 class="titulo-calendario">📅 Calendario de <?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h3>
                <div class="navegacion-mes">
                    <button class="boton-nav-mes" onclick="cambiarMes(-1)">‹</button>
                    <span class="mes-actual" id="mesActual"></span>
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
                <div class="dias-calendario" id="diasCalendario"></div>
            </div>

            <div class="eventos-hoy">
                <div class="encabezado-eventos-hoy">
                    <h4 class="titulo-eventos-hoy" id="tituloEventosDia">📅 Hoy</h4>
                    <span class="contador-eventos" id="contadorEventosDia"><?php echo $resultado_eventos_hoy ? $resultado_eventos_hoy->num_rows : 0; ?></span>
                </div>

                <div class="lista-eventos-hoy" id="listaEventosDia">
                    <?php if ($resultado_eventos_hoy && $resultado_eventos_hoy->num_rows > 0): 
                        while($evento = $resultado_eventos_hoy->fetch_assoc()): 
                            $es_urgente = ($evento['tipo'] == 'cita' && in_array($evento['titulo'], ['Urgencia', 'Vacunación']));
                    ?>
                        <div class="evento-hoy <?php echo $es_urgente ? 'urgente' : ''; ?>" 
                        onclick="<?php echo $evento['tipo'] == 'cita' ? 'window.location.href=\'veterinaria.php\'' : ''; ?>" 
                        style="<?php echo $evento['tipo'] == 'cita' ? 'cursor: pointer;' : ''; ?>">
                            <div class="icono-evento">
                                <?php 
                                if ($evento['tipo'] == 'recordatorio') {
                                    echo '📝';
                                } else {
                                    // Para citas, usar el campo 'motivo' en lugar de 'titulo'
                                    $motivo = $evento['titulo'];
                                    echo match($motivo) {
                                        'Vacunación' => '💉',
                                        'Análisis' => '🧪',
                                        'Cirugía' => '🏥',
                                        'Control' => '📋',
                                        'Urgencia' => '⚠️',
                                        default => '💊'
                                    };
                                }
                                ?>
                            </div>
                            <div class="info-evento">
                                <div class="titulo-evento"><?php echo htmlspecialchars($evento['titulo']); ?></div>
                                <div class="detalles-evento">
                                    <?php echo $evento['tipo'] == 'recordatorio' ? 'Recordatorio' : 'Cita'; ?> • 
                                    <?php echo date('H:i', strtotime($evento['fecha'])); ?>
                                </div>
                            </div>
                            <?php if ($evento['tipo'] == 'recordatorio'): ?>
                                <div class="acciones-evento">
                                    <button class="btn-accion-evento btn-editar" onclick="editarEvento('<?php echo $evento['tipo']; ?>', <?php echo $evento['id_evento']; ?>)" title="Editar">✏️</button>
                                    <button class="btn-accion-evento btn-eliminar" onclick="eliminarEvento('<?php echo $evento['tipo']; ?>', <?php echo $evento['id_evento']; ?>)" title="Eliminar">🗑️</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="sin-eventos" id="sinEventos">
                            <div class="icono-grande">📅</div>
                            <p>No hay eventos para hoy</p>
                            <small>Agenda una cita o crea un recordatorio</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="proximos-eventos">
                    <h4 class="titulo-proximos">Próximos eventos</h4>
                    <?php if ($resultado_eventos && $resultado_eventos->num_rows > 0): 
                        while($evento = $resultado_eventos->fetch_assoc()): 
                        $fecha_evento = new DateTime($evento['fecha']);
                        $diff = (new DateTime())->diff($fecha_evento);
                    ?>
                        <div class="evento-proximo" onclick="window.location.href='veterinaria.php'" style="cursor: pointer;">
                            <div class="info-evento-proximo">
                                <?php 
                                if ($evento['tipo'] === 'evento') {
                                    echo '🎉 ' . htmlspecialchars($evento['titulo']);
                                } elseif ($evento['tipo'] === 'cita') {
                                    echo '💊 Cita: ' . htmlspecialchars($evento['titulo']);
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
                    <?php endwhile; else: ?>
                        <div class="sin-eventos-proximos">No hay eventos próximos</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Recordatorios -->
        <section class="recordatorios-urgentes">
            <div class="section-header">
                <h3>🔔 Recordatorios</h3>
                <span class="count">
                    <?php 
                    echo $resultado_eventos_hoy ? $resultado_eventos_hoy->num_rows : 0;
                    ?> hoy
                </span>            
            </div>

            <button class="btn-agregar-recordatorio" onclick="mostrarModalRecordatorio()">+ Agregar Recordatorio</button>

            <div class="urgente-list">
                <?php 
                // Solo recordatorios de HOY
                $consulta_recordatorios_hoy = "(SELECT DISTINCT 'recordatorio' as tipo, r.titulo, r.descripcion, r.fecha, r.id_recordatorio as id_evento
                                                FROM recordatorios_personales r
                                                JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                                                WHERE rm.id_mascota = $mascota_id
                                                AND r.id_usuario = $usuario_id
                                                AND r.fecha >= '$fecha_inicio_dia'
                                                AND r.fecha <= '$fecha_fin_dia'
                                                AND r.completado = 0)
                                            UNION
                                            (SELECT DISTINCT 'cita' as tipo, motivo as titulo, NULL as descripcion, fecha, id_cita as id_evento
                                                FROM citas_veterinarias 
                                                WHERE id_mascota = $mascota_id 
                                                AND fecha >= '$fecha_inicio_dia'
                                                AND fecha <= '$fecha_fin_dia'
                                                AND estado != 'completada')
                                            ORDER BY fecha ASC";
                $resultado_recordatorios_hoy = $conexion->query($consulta_recordatorios_hoy);
                
                if ($resultado_recordatorios_hoy && $resultado_recordatorios_hoy->num_rows > 0): 
                    while($item = $resultado_recordatorios_hoy->fetch_assoc()): 
                        $es_urgente = ($item['tipo'] == 'cita' && in_array($item['titulo'], ['Urgencia', 'Vacunación']));
                        $texto_descripcion = '';
                        
                        // Si es recordatorio y tiene descripción, mostrarla
                        if ($item['tipo'] == 'recordatorio' && !empty($item['descripcion'])) {
                            $texto_descripcion = htmlspecialchars($item['descripcion']);
                        } else if ($item['tipo'] == 'recordatorio') {
                            $texto_descripcion = 'Recordatorio';
                        } else {
                            $texto_descripcion = 'Cita veterinaria';
                        }
                ?>
                    <div class="urgente-item <?php echo $es_urgente ? 'urgente' : ''; ?>" 
                    onclick="<?php echo $item['tipo'] == 'cita' ? 'window.location.href=\'veterinaria.php\'' : ''; ?>" 
                    style="<?php echo $item['tipo'] == 'cita' ? 'cursor: pointer;' : ''; ?>">
                        <div class="urgente-info">
                            <span class="mascota-name">
                                <?php echo $item['tipo'] == 'recordatorio' ? '📝 ' : '💊 '; ?>
                                <?php echo htmlspecialchars($item['titulo']); ?>
                            </span>
                            <span class="urgente-time">🕐 <?php echo date('H:i', strtotime($item['fecha'])); ?></span>
                            <span class="urgente-label" style="background: <?php echo $item['tipo'] == 'recordatorio' ? '#3498db' : '#e74c3c'; ?>;">
                                <?php echo $texto_descripcion; ?>
                            </span>
                        </div>
                        <?php if ($item['tipo'] == 'recordatorio'): ?>
                            <div class="acciones-urgente">
                                <button class="btn-accion-pequeno btn-editar" onclick="editarEvento('<?php echo $item['tipo']; ?>', <?php echo $item['id_evento']; ?>)" title="Editar">✏️</button>
                                <button class="btn-accion-pequeno btn-eliminar" onclick="eliminarEvento('<?php echo $item['tipo']; ?>', <?php echo $item['id_evento']; ?>)" title="Eliminar">🗑️</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; else: ?>
                    <div class="sin-eventos">
                        <div class="icono-grande">✨</div>
                        <p>No hay recordatorios para hoy</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="proximamente">
                <h4>📅 Próximamente</h4>
                <?php 
                // RECORDATORIOS futuros (DESPUÉS de hoy)
                $consulta_recordatorios_futuros = "SELECT 'recordatorio' as tipo, r.titulo, r.fecha, r.id_recordatorio as id_evento
                                FROM recordatorios_personales r
                                JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                                WHERE rm.id_mascota = $mascota_id
                                AND r.id_usuario = $usuario_id
                                AND r.fecha > '$hora_actual'
                                AND r.fecha <= DATE_ADD('$hora_actual', INTERVAL 7 DAY)
                                AND r.completado = 0
                                ORDER BY r.fecha ASC
                                LIMIT 5";
                
                $resultado_recordatorios_futuros = $conexion->query($consulta_recordatorios_futuros);
                
                if ($resultado_recordatorios_futuros && $resultado_recordatorios_futuros->num_rows > 0): 
                    while($rec = $resultado_recordatorios_futuros->fetch_assoc()): 
                ?>
                    <div class="proximo-item">
                        <span class="proximo-info">
                            📝 <?php echo date('D j', strtotime($rec['fecha'])) . ' • ' . htmlspecialchars($rec['titulo']); ?>
                        </span>
                        <span class="proximo-time"><?php echo date('H:i', strtotime($rec['fecha'])); ?></span>
                        <div class="acciones-proximo">
                            <button class="btn-accion-mini" onclick="editarEvento('recordatorio', <?php echo $rec['id_evento']; ?>)" title="Editar">✏️</button>
                            <button class="btn-accion-mini" onclick="eliminarEvento('recordatorio', <?php echo $rec['id_evento']; ?>)" title="Eliminar">🗑️</button>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <div class="sin-eventos-proximos">Sin recordatorios próximos esta semana</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Seguimiento de peso -->
        <section class="seguimiento-peso">
            <div class="peso-header">
                <h3>Seguimiento de Peso</h3>
                <button class="btn-agregar-peso" onclick="mostrarModalPeso()">+ Agregar Peso</button>
            </div>

            <div class="grafico-container">
                <?php if (!empty($pesos)): ?>
                    <div class="peso-grafico">
                        <!-- Ejes y grid -->
                        <svg class="grafico-fondo" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <!-- Líneas horizontales del grid -->
                            <line x1="8" y1="15" x2="92" y2="15" stroke="#e8e8e8" stroke-width="0.3"/>
                            <line x1="8" y1="35" x2="92" y2="35" stroke="#e8e8e8" stroke-width="0.3"/>
                            <line x1="8" y1="55" x2="92" y2="55" stroke="#e8e8e8" stroke-width="0.3"/>
                            <line x1="8" y1="75" x2="92" y2="75" stroke="#e8e8e8" stroke-width="0.3"/>
                            
                            <!-- Eje Y principal -->
                            <line x1="8" y1="15" x2="8" y2="75" stroke="#999" stroke-width="0.5"/>
                            <!-- Eje X principal -->
                            <line x1="8" y1="75" x2="92" y2="75" stroke="#999" stroke-width="0.5"/>
                        </svg>

                        <!-- Etiquetas del eje Y (peso) -->
                        <div class="etiquetas-y">
                            <span style="top: 15%"><?php echo round($max_peso, 1); ?> kg</span>
                            <span style="top: 35%"><?php echo round($max_peso - ($rango * 0.33), 1); ?> kg</span>
                            <span style="top: 55%"><?php echo round($max_peso - ($rango * 0.66), 1); ?> kg</span>
                            <span style="top: 75%"><?php echo round($min_peso, 1); ?> kg</span>
                        </div>

                        <!-- Etiquetas del eje X (fechas) -->
                        <div class="etiquetas-x">
                            <?php 
                            $mostrar_cada = max(1, floor(count($pesos) / 5));
                            foreach($pesos as $index => $peso): 
                                if ($index % $mostrar_cada == 0 || $index == count($pesos) - 1):
                                    $left = ($index / $divisor) * 84 + 8;
                            ?>
                                <span style="left: <?php echo $left; ?>%"><?php echo date('d/m', strtotime($peso['fecha'])); ?></span>
                            <?php endif; endforeach; ?>
                        </div>

                        <!-- Área bajo la curva -->
                        <svg class="grafico-area" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="gradientArea" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#D35400;stop-opacity:0.3" />
                                    <stop offset="100%" style="stop-color:#D35400;stop-opacity:0.05" />
                                </linearGradient>
                            </defs>
                            <polygon fill="url(#gradientArea)" points="
                                <?php 
                                $puntos = [];
                                foreach($pesos as $index => $peso) {
                                    $x = ($index / $divisor) * 84 + 8;
                                    $y = 75 - ((($peso['peso'] - $min_peso) / $rango) * 60);
                                    $puntos[] = "$x,$y";
                                }
                                echo implode(' ', $puntos);
                                // Cerrar el área
                                $ultimo_x = ($index / $divisor) * 84 + 8;
                                echo " $ultimo_x,75 8,75";
                                ?>
                            "/>
                        </svg>

                        <!-- Línea de datos -->
                        <svg class="grafico-linea" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <polyline 
                                fill="none" 
                                stroke="#D35400" 
                                stroke-width="0.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="<?php echo implode(' ', $puntos); ?>"
                            />
                        </svg>

                        <!-- Puntos de datos -->
                        <?php foreach($pesos as $index => $peso):
                            $left = ($index / $divisor) * 84 + 8;
                            $bottom = 25 + ((($peso['peso'] - $min_peso) / $rango) * 60);
                        ?>
                            <div class="punto-dato" style="left: <?php echo $left; ?>%; bottom: <?php echo $bottom; ?>%;" 
                                data-peso="<?php echo $peso['peso']; ?>" 
                                data-fecha="<?php echo date('d/m/Y', strtotime($peso['fecha'])); ?>"
                                data-id="<?php echo $peso['id_peso']; ?>">
                                <span class="tooltip-peso">
                                    <?php echo $peso['peso']; ?> kg<br>
                                    <small><?php echo date('d/m/Y', strtotime($peso['fecha'])); ?></small>
                                    <div class="acciones-peso">
                                        <button class="btn-peso-accion" onclick="editarPeso(<?php echo $peso['id_peso']; ?>, <?php echo $peso['peso']; ?>, '<?php echo $peso['fecha']; ?>')" title="Editar">✏️</button>
                                        <button class="btn-peso-accion" onclick="eliminarPeso(<?php echo $peso['id_peso']; ?>)" title="Eliminar">🗑️</button>
                                    </div>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="sin-datos-peso">
                        <div class="icono-grande">📊</div>
                        <p>No hay registros de peso</p>
                        <small>Agrega el primer registro</small>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Modal Editar Perfil -->
        <div id="modalEditar" class="modal">
            <div class="modal-contenido">
                <div class="modal-header">
                    <h3>✏️ Editar Perfil de <?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h3>
                    <button class="btn-cerrar" onclick="cerrarModalEditar()">×</button>
                </div>
                <form method="POST" action="editar-mascota.php" enctype="multipart/form-data" class="modal-form">
                    <input type="hidden" name="id_mascota" value="<?php echo $mascota_id; ?>">
                    
                    <div class="form-foto">
                        <img id="previewFoto" src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'mascota-default.jpg'; ?>" alt="Foto">
                        <label for="fotoMascota" class="btn-cambiar-foto">📷
                            <input type="file" id="fotoMascota" name="foto_mascota" accept="image/*">
                        </label>
                        <p class="form-ayuda">Haz clic para cambiar la foto</p>
                    </div>

                    <div class="form-grupo">
                        <label>Nombre *</label>
                        <input type="text" name="nombre_mascota" value="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" required maxlength="20">
                    </div>

                    <div class="form-fila">
                        <div class="form-grupo">
                            <label>Tipo *</label>
                            <select name="tipo" required>
                                <option value="perro" <?php echo $mascota['tipo'] == 'perro' ? 'selected' : ''; ?>>Perro</option>
                                <option value="gato" <?php echo $mascota['tipo'] == 'gato' ? 'selected' : ''; ?>>Gato</option>
                                <option value="otro" <?php echo $mascota['tipo'] == 'otro' ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                        <div class="form-grupo">
                            <label>Sexo *</label>
                            <select name="sexo" required>
                                <option value="macho" <?php echo $mascota['sexo'] == 'macho' ? 'selected' : ''; ?>>Macho</option>
                                <option value="hembra" <?php echo $mascota['sexo'] == 'hembra' ? 'selected' : ''; ?>>Hembra</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label>Fecha de nacimiento *</label>
                        <input type="date" name="cumpleanos_mascota" value="<?php echo $mascota['cumpleaños_mascota']; ?>" required max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-grupo">
                        <label>Peso actual (kg)</label>
                        <input type="number" name="peso_actual" value="<?php echo $ficha ? $ficha['peso'] : ''; ?>" step="0.1" min="0.1" max="200" placeholder="Ej: 28.5">
                        <small>Opcional</small>
                    </div>

                    <div class="form-botones">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                        <button type="submit" class="btn-guardar">💾 Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Agregar Peso -->
        <div id="modalPeso" class="modal">
            <div class="modal-contenido modal-pequeno">
                <div class="modal-header">
                    <h3>📊 Registrar Peso</h3>
                    <button class="btn-cerrar" onclick="cerrarModalPeso()">×</button>
                </div>
                <form method="POST" action="procesar-peso.php" class="modal-form">
                    <input type="hidden" name="id_mascota" value="<?php echo $mascota_id; ?>">
                    
                    <div class="form-grupo">
                        <label>Peso (kg) *</label>
                        <input type="number" name="peso" step="0.1" min="0.1" max="200" required placeholder="Ej: 28.5">
                        <small>Peso en kilogramos</small>
                    </div>

                    <div class="form-grupo">
                        <label>Fecha *</label>
                        <input type="date" name="fecha" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                        <small>Solo fechas pasadas o presente</small>
                    </div>

                    <div class="form-botones">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalPeso()">Cancelar</button>
                        <button type="submit" class="btn-guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Recordatorio -->
        <div id="modalRecordatorio" class="modal">
            <div class="modal-contenido modal-pequeno">
                <div class="modal-header">
                    <h3>📝 Nuevo Recordatorio</h3>
                    <button class="btn-cerrar" onclick="cerrarModalRecordatorio()">×</button>
                </div>
                <form method="POST" action="procesar-recordatorio.php" class="modal-form">
                    <input type="hidden" name="mascotas[]" value="<?php echo $mascota_id; ?>">
                    
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

                    <div class="form-botones">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalRecordatorio()">Cancelar</button>
                        <button type="submit" class="btn-guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
    <script src="js/notificaciones.js"></script>
    <script src="js/perfil-mascota.js"></script>
    <script>
    window.mascotaData = {
        diasConEventos: <?php echo json_encode($dias_con_eventos_mascota); ?>,
        mascotaId: <?php echo $mascota_id; ?>,
        eventosMes: <?php echo json_encode($eventos_mes); ?>
    };
    </script>

   
</body>
</html>