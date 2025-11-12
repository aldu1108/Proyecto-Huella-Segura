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
$mascota_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

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
    while ($row = $resultado_peso->fetch_assoc()) {
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
    while ($dia = $resultado_dias_mascota->fetch_assoc()) {
        $dias_con_eventos_mascota[] = (int) $dia['dia'];
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
    while ($evento = $resultado_eventos_mes->fetch_assoc()) {
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
    while ($cita = $resultado_citas_mes->fetch_assoc()) {
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
    while ($evento = $resultado_eventos_comunidad->fetch_assoc()) {
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
                echo match ($_GET['exito']) {
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
                echo match ($_GET['error']) {
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
                    alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" class="mascota-foto-grande">
                <div class="mascota-datos">
                    <h1><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h1>
                    <p class="mascota-tipo"><?php echo ucfirst($mascota['tipo']) . ' • ' . ucfirst($mascota['sexo']); ?>
                    </p>
                    <div class="mascota-stats">
                        <span class="stat">🎂 <?php echo $mascota['edad_mascota']; ?> años</span>
                        <span class="stat">⚖️
                            <?php echo $ficha && !empty($ficha['peso']) ? $ficha['peso'] . ' kg' : 'Sin registrar'; ?></span>
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
                    <span><?php echo date('d/m/Y', strtotime($mascota['cumpleaños_mascota'])); ?></span>
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
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="M144-144v-153l498-498q11-11 24-16t27-5q14 0 27 5t24 16l51 51q11 11 16 24t5 27q0 14-5 27t-16 24L297-144H144Zm549-498 51-51-51-51-51 51 51 51Z"/></svg> Editar Perfil
                </button>
                <button onclick="mostrarModalEliminar()" class="btn-eliminar-perfil">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z"/></svg> Eliminar Mascota
                </button>
            </div>

            <!-- Modal Eliminar Mascota -->
            <div id="modalEliminar" class="modal">
                <div class="modal-contenido modal-pequeno">
                    <div class="modal-header modal-header-peligro">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z"/></svg> 
                            Eliminar Mascota
                        </h3>
                        <button class="btn-cerrar" onclick="cerrarModalEliminar()">×</button>
                    </div>
                    <div class="modal-form">
                        <div class="advertencia-eliminacion">
                            <div class="icono-advertencia">⚠️</div>
                            <p class="titulo-advertencia">¿Estás seguro de que deseas eliminar a <strong><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></strong>?</p>
                                        <p class="texto-advertencia">Esta acción no se puede deshacer. Se eliminarán todos los datos asociados:
                                        </p>
                                        <ul class="lista-advertencias">
                                            <li>📊 Registros de peso</li>
                                            <li>💉 Citas veterinarias</li>
                                            <li>📝 Recordatorios</li>
                                            <li>🏥 Fichas de salud</li>
                                            <li>📋 Historial médico</li>
                                        </ul>
                                    </div>
                        
                                    <div class="form-botones">
                                        <button type="button" class="btn-cancelar" onclick="cerrarModalEliminar()">Cancelar</button>
                                        <button type="button" class="btn-eliminar-confirmar" onclick="confirmarEliminacion()">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                                                fill="#FFFFFF">
                                                <path
                                                    d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z" />
                                            </svg>
                                            Sí, eliminar permanentemente
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
        </section>

        <!-- Calendario -->
        <section class="calendario-cuidados">
            <div class="encabezado-calendario">
                <h3 class="titulo-calendario"><svg xmlns="http://www.w3.org/2000/svg" height="20px"
                        viewBox="0 -960 960 960" width="20px" fill="#EA3323">
                        <path
                            d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z" />
                    </svg> Calendario de <?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h3>
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
                    <h4 class="titulo-eventos-hoy" id="tituloEventosDia"><svg xmlns="http://www.w3.org/2000/svg"
                            height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323">
                            <path
                                d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z" />
                        </svg> Hoy</h4>
                    <span class="contador-eventos"
                        id="contadorEventosDia"><?php echo $resultado_eventos_hoy ? $resultado_eventos_hoy->num_rows : 0; ?></span>
                </div>

                <div class="lista-eventos-hoy" id="listaEventosDia">
                    <?php if ($resultado_eventos_hoy && $resultado_eventos_hoy->num_rows > 0):
                        while ($evento = $resultado_eventos_hoy->fetch_assoc()):
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
                                        echo match ($motivo) {
                                            'Vacunación' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M156-513q-11-12-11-28.5t11-28.5l112-112-43-43-12 12q-12 12-28.5 12T156-713q-11-11-11-28t11-28l80-80q12-12 28.5-12t28.5 12q11 11 11 28t-11 28l-12 12 43 43 112-112q12-12 28.5-12t28.5 12q12 12 12 28.5T493-793l-27 26 295 295q23 23 23 56.5T761-359l-28 29 189 188H808L676-274l-28 29q-23 23-56.5 23T535-245L240-540l-27 27q-12 11-28.5 11T156-513Zm140-83 295 295 113-114-60-61-56 56q-12 11-28.5 11.5T532-419q-12-12-12-28.5t12-28.5l56-56-60-60-56 56q-12 11-28.5 11T415-536q-11-12-11-28.5t11-28.5l56-56-61-61-114 114Zm0 0 114-114-114 114Z"/></svg>',
                                            'Análisis' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#75FB4C"><path d="M480-80q-83 0-141.5-58.5T280-280v-360q-33 0-56.5-23.5T200-720v-80q0-33 23.5-56.5T280-880h400q33 0 56.5 23.5T760-800v80q0 33-23.5 56.5T680-640v360q0 83-58.5 141.5T480-80ZM280-720h400v-80H280v80Zm200 560q50 0 85-35t35-85H480v-80h120v-80H480v-80h120v-120H360v360q0 50 35 85t85 35ZM280-720v-80 80Z"/></svg>',
                                            'Cirugía' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#DF9D9B"><path d="M218-104 104-218q-23-23-23-56t23-56l526-526q23-23 56-23t56 23l114 114q23 23 23 56t-23 56L330-104q-23 23-56 23t-56-23Zm290-122 226-226 122 122q23 23 23 56t-23 56L742-104q-23 23-56 23t-56-23L508-226Zm-28-134q17 0 28.5-11.5T520-400q0-17-11.5-28.5T480-440q-17 0-28.5 11.5T440-400q0 17 11.5 28.5T480-360Zm-80-80q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440Zm160 0q17 0 28.5-11.5T600-480q0-17-11.5-28.5T560-520q-17 0-28.5 11.5T520-480q0 17 11.5 28.5T560-440Zm-335-69L104-630q-23-23-23-56t23-56l114-114q23-23 56-23t56 23l122 122-227 225Zm255-11q17 0 28.5-11.5T520-560q0-17-11.5-28.5T480-600q-17 0-28.5 11.5T440-560q0 17 11.5 28.5T480-520Z"/></svg>',
                                            'Control' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5985E1"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm80-160h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm200-190q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790Z"/></svg>',
                                            'Urgencia' => '<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg>',
                                            default => '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M354-144q-87.73 0-148.87-61.13Q144-266.27 144-354q0-42 16-81t45-68l252-252q29-29 68-45t81-16q87.73 0 148.87 61.13Q816-693.73 816-606q0 42-16 81t-45 68L503-205q-29 29-68 45t-81 16Zm249-264 101-100q20-20 30-45t10-52.67q0-57.24-40.55-97.78Q662.91-744 605.67-744 578-744 553-734t-45 30L408-603l195 195ZM354.33-216Q382-216 407-226t45-30l100-101-195-195-100 100q-20 20-30.5 45T216-354.33q0 57.24 40.55 97.78Q297.09-216 354.33-216Z"/></svg>'
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
                                        <button class="btn-accion-evento btn-editar"
                                            onclick="editarEvento('<?php echo $evento['tipo']; ?>', <?php echo $evento['id_evento']; ?>)"
                                            title="Editar">✏️</button>
                                        <button class="btn-accion-evento btn-eliminar"
                                            onclick="eliminarEvento('<?php echo $evento['tipo']; ?>', <?php echo $evento['id_evento']; ?>)"
                                            title="Eliminar">🗑️</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; else: ?>
                        <div class="sin-eventos" id="sinEventos">
                            <div class="icono-grande"><svg xmlns="http://www.w3.org/2000/svg" height="20px"
                                    viewBox="0 -960 960 960" width="20px" fill="#EA3323">
                                    <path
                                        d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z" />
                                </svg></div>
                            <p>No hay eventos para hoy</p>
                            <small>Agenda una cita o crea un recordatorio</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="proximos-eventos">
                    <h4 class="titulo-proximos">Próximos eventos</h4>
                    <?php if ($resultado_eventos && $resultado_eventos->num_rows > 0):
                        while ($evento = $resultado_eventos->fetch_assoc()):
                            $fecha_evento = new DateTime($evento['fecha']);
                            $diff = (new DateTime())->diff($fecha_evento);
                            ?>
                            <div class="evento-proximo" onclick="window.location.href='veterinaria.php'"
                                style="cursor: pointer;">
                                <div class="info-evento-proximo">
                                    <?php
                                    if ($evento['tipo'] === 'evento') {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#8C1AF6"><path d="m80-80 200-560 360 360L80-80Zm502-378-42-42 224-224q32-32 77-32t77 32l24 24-42 42-24-24q-14-14-35-14t-35 14L582-458ZM422-618l-42-42 24-24q14-14 14-34t-14-34l-26-26 42-42 26 26q32 32 32 76t-32 76l-24 24Zm80 80-42-42 144-144q14-14 14-35t-14-35l-64-64 42-42 64 64q32 32 32 77t-32 77L502-538Zm160 160-42-42 64-64q32-32 77-32t77 32l64 64-42 42-64-64q-14-14-35-14t-35 14l-64 64Z"/></svg> ' . htmlspecialchars($evento['titulo']);
                                    } elseif ($evento['tipo'] === 'cita') {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M345-120q-94 0-159.5-65.5T120-345q0-45 17-86t49-73l270-270q32-32 73-49t86-17q94 0 159.5 65.5T840-615q0 45-17 86t-49 73L504-186q-32 32-73 49t-86 17Zm266-286 107-106q20-20 31-47t11-56q0-60-42.5-102.5T615-760q-29 0-56 11t-47 31L406-611l205 205ZM345-200q29 0 56-11t47-31l106-107-205-205-107 106q-20 20-31 47t-11 56q0 60 42.5 102.5T345-200Z"/></svg> Cita: ' . htmlspecialchars($evento['titulo']);
                                    } else {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5985E1"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm80-80h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm200-190q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z"/></svg> ' . htmlspecialchars($evento['titulo']);
                                    }
                                    ?>
                                </div>
                                <div class="fecha-evento-proximo">
                                    <?php
                                    if ($diff->days == 0)
                                        echo "Hoy • " . $fecha_evento->format('H:i');
                                    elseif ($diff->days == 1)
                                        echo "Mañana • " . $fecha_evento->format('H:i');
                                    else
                                        echo $fecha_evento->format('D, j M • H:i');
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
                <h3><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#d35400">
                        <path
                            d="M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160ZM480-80q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80Z" />
                    </svg> Recordatorios</h3>
                <span class="count">
                    <?php
                    echo $resultado_eventos_hoy ? $resultado_eventos_hoy->num_rows : 0;
                    ?> hoy
                </span>
            </div>

            <button class="btn-agregar-recordatorio" onclick="mostrarModalRecordatorio()">+ Agregar
                Recordatorio</button>

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
                    while ($item = $resultado_recordatorios_hoy->fetch_assoc()):
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
                                    <?php echo $item['tipo'] == 'recordatorio' ? '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#789DE5"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520h200L520-800v200Z"/></svg> ' : '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M345-120q-94 0-159.5-65.5T120-345q0-45 17-86t49-73l270-270q32-32 73-49t86-17q94 0 159.5 65.5T840-615q0 45-17 86t-49 73L504-186q-32 32-73 49t-86 17Zm266-286 107-106q20-20 31-47t11-56q0-60-42.5-102.5T615-760q-29 0-56 11t-47 31L406-611l205 205ZM345-200q29 0 56-11t47-31l106-107-205-205-107 106q-20 20-31 47t-11 56q0 60 42.5 102.5T345-200Z"/></svg> '; ?>
                                    <?php echo htmlspecialchars($item['titulo']); ?>
                                </span>
                                <span class="urgente-time"><svg xmlns="http://www.w3.org/2000/svg" height="24px"
                                        viewBox="0 -960 960 960" width="24px" fill="#666666">
                                        <path
                                            d="M480-80q-75 0-140.5-28.5t-114-77q-48.5-48.5-77-114T120-440q0-75 28.5-140.5t77-114q48.5-48.5 114-77T480-800q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-440q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-80Zm0-360Zm112 168 56-56-128-128v-184h-80v216l152 152ZM224-866l56 56-170 170-56-56 170-170Zm512 0 170 170-56 56-170-170 56-56ZM480-160q117 0 198.5-81.5T760-440q0-117-81.5-198.5T480-720q-117 0-198.5 81.5T200-440q0 117 81.5 198.5T480-160Z" />
                                    </svg> <?php echo date('H:i', strtotime($item['fecha'])); ?></span>
                                <span class="urgente-label"
                                    style="background: <?php echo $item['tipo'] == 'recordatorio' ? '#3498db' : '#e74c3c'; ?>;">
                                    <?php echo $texto_descripcion; ?>
                                </span>
                            </div>
                            <?php if ($item['tipo'] == 'recordatorio'): ?>
                                <div class="acciones-urgente">
                                    <button class="btn-accion-pequeno btn-editar"
                                        onclick="editarEvento('<?php echo $item['tipo']; ?>', <?php echo $item['id_evento']; ?>)"
                                        title="Editar"><svg xmlns="http://www.w3.org/2000/svg" height="24px"
                                            viewBox="0 -960 960 960" width="24px" fill="#FFFF55">
                                            <path
                                                d="M120-120v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm584-528 56-56-56-56-56 56 56 56Z" />
                                        </svg></button>
                                    <button class="btn-accion-pequeno btn-eliminar"
                                        onclick="eliminarEvento('<?php echo $item['tipo']; ?>', <?php echo $item['id_evento']; ?>)"
                                        title="Eliminar"><svg xmlns="http://www.w3.org/2000/svg" height="24px"
                                            viewBox="0 -960 960 960" width="24px" fill="#EA3323">
                                            <path
                                                d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z" />
                                        </svg></button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; else: ?>
                    <div class="sin-eventos">
                        <div class="icono-grande"><svg xmlns="http://www.w3.org/2000/svg" height="48px"
                                viewBox="0 -960 960 960" width="48px" fill="#ff75e1">
                                <path
                                    d="M852-226 746-332l42-42 106 106-42 42ZM708-706l-42-42 106-106 42 42-106 106Zm-456 0L146-812l42-42 106 106-42 42ZM108-226l-42-42 106-106 42 42-106 106Zm125 106 65-281L80-590l288-25 112-265 112 265 288 25-218 189 65 281-247-149-247 149Z" />
                            </svg></div>
                        <p>No hay recordatorios para hoy</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="proximamente">
                <h4><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                        fill="#EA3323">
                        <path
                            d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z" />
                    </svg> Próximamente</h4>
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
                    while ($rec = $resultado_recordatorios_futuros->fetch_assoc()):
                        ?>
                        <div class="proximo-item">
                            <span class="proximo-info">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                                    fill="#789DE5">
                                    <path
                                        d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZM263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624h168L528-792v168Z" />
                                </svg>
                                <?php echo date('D j', strtotime($rec['fecha'])) . ' • ' . htmlspecialchars($rec['titulo']); ?>
                            </span>
                            <span class="proximo-time"><?php echo date('H:i', strtotime($rec['fecha'])); ?></span>
                            <div class="acciones-proximo">
                                <button class="btn-accion-mini"
                                    onclick="editarEvento('recordatorio', <?php echo $rec['id_evento']; ?>)"
                                    title="Editar">✏️</button>
                                <button class="btn-accion-mini"
                                    onclick="eliminarEvento('recordatorio', <?php echo $rec['id_evento']; ?>)"
                                    title="Eliminar">🗑️</button>
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
                            <line x1="8" y1="15" x2="92" y2="15" stroke="#e8e8e8" stroke-width="0.3" />
                            <line x1="8" y1="35" x2="92" y2="35" stroke="#e8e8e8" stroke-width="0.3" />
                            <line x1="8" y1="55" x2="92" y2="55" stroke="#e8e8e8" stroke-width="0.3" />
                            <line x1="8" y1="75" x2="92" y2="75" stroke="#e8e8e8" stroke-width="0.3" />

                            <!-- Eje Y principal -->
                            <line x1="8" y1="15" x2="8" y2="75" stroke="#999" stroke-width="0.5" />
                            <!-- Eje X principal -->
                            <line x1="8" y1="75" x2="92" y2="75" stroke="#999" stroke-width="0.5" />
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
                            foreach ($pesos as $index => $peso):
                                if ($index % $mostrar_cada == 0 || $index == count($pesos) - 1):
                                    $left = ($index / $divisor) * 84 + 8;
                                    ?>
                                    <span
                                        style="left: <?php echo $left; ?>%"><?php echo date('d/m', strtotime($peso['fecha'])); ?></span>
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
                                foreach ($pesos as $index => $peso) {
                                    $x = ($index / $divisor) * 84 + 8;
                                    $y = 75 - ((($peso['peso'] - $min_peso) / $rango) * 60);
                                    $puntos[] = "$x,$y";
                                }
                                echo implode(' ', $puntos);
                                // Cerrar el área
                                $ultimo_x = ($index / $divisor) * 84 + 8;
                                echo " $ultimo_x,75 8,75";
                                ?>
                            " />
                        </svg>

                        <!-- Línea de datos -->
                        <svg class="grafico-linea" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <polyline fill="none" stroke="#D35400" stroke-width="0.8" stroke-linecap="round"
                                stroke-linejoin="round" points="<?php echo implode(' ', $puntos); ?>" />
                        </svg>

                        <!-- Puntos de datos -->
                        <?php foreach ($pesos as $index => $peso):
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
                                        <button class="btn-peso-accion"
                                            onclick="editarPeso(<?php echo $peso['id_peso']; ?>, <?php echo $peso['peso']; ?>, '<?php echo $peso['fecha']; ?>')"
                                            title="Editar">✏️</button>
                                        <button class="btn-peso-accion" onclick="eliminarPeso(<?php echo $peso['id_peso']; ?>)"
                                            title="Eliminar">🗑️</button>
                                    </div>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="sin-datos-peso">
                        <div class="icono-grande"><svg xmlns="http://www.w3.org/2000/svg" height="48px"
                                viewBox="0 -960 960 960" width="48px" fill="#EA3323">
                                <path
                                    d="m89-412-49-36 193-310 125 146 165-268 125 186 143-226 48 35-190 301-124-184-159 258-125-146L89-412Zm494 192q48 0 82.5-33.5T700-335q0-49-34-83t-83-34q-48 0-81.5 34.5T468-335q0 48 33.5 81.5T583-220ZM797-80 685-192q-22 15-47.5 23.5T583-160q-73 0-124-51t-51-124q0-73 51-125t124-52q74 0 125.5 51.5T760-335q0 29-8.5 54.5T727-233l112 111-42 42Z" />
                            </svg></div>
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
                    <h3><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                            fill="#FFFF55">
                            <path
                                d="M144-144v-153l498-498q11-11 24-16t27-5q14 0 27 5t24 16l51 51q11 11 16 24t5 27q0 14-5 27t-16 24L297-144H144Zm549-498 51-51-51-51-51 51 51 51Z" />
                        </svg> Editar Perfil de <?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h3>
                    <button class="btn-cerrar" onclick="cerrarModalEditar()">×</button>
                </div>
                <form method="POST" action="editar-mascota.php" enctype="multipart/form-data" class="modal-form">
                    <input type="hidden" name="id_mascota" value="<?php echo $mascota_id; ?>">

                    <div class="form-foto">
                        <img id="previewFoto"
                            src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'mascota-default.jpg'; ?>"
                            alt="Foto">
                        <label for="fotoMascota" class="btn-cambiar-foto"><svg xmlns="http://www.w3.org/2000/svg"
                                height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666">
                                <path
                                    d="M480-264q72 0 120-49t48-119q0-69-48-118.5T480-600q-72 0-120 49.5T312-432q0 70 48 119t120 49Zm0-72q-42 0-69-27t-27-68q0-40 27-68.5t69-28.5q42 0 69 28.5t27 68.5q0 41-27 68t-69 27ZM168-144q-29 0-50.5-21.5T96-216v-432q0-29 21.5-50.5T168-720h120l72-96h240l72 96h120q30 0 51 21.5t21 50.5v432q0 29-21 50.5T792-144H168Z" />
                            </svg>
                            <input type="file" id="fotoMascota" name="foto_mascota" accept="image/*">
                        </label>
                        <p class="form-ayuda">Haz clic para cambiar la foto</p>
                    </div>

                    <div class="form-grupo">
                        <label>Nombre *</label>
                        <input type="text" name="nombre_mascota"
                            value="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" required maxlength="20">
                    </div>

                    <div class="form-fila">
                        <div class="form-grupo">
                            <label>Tipo *</label>
                            <select name="tipo" required>
                                <option value="perro" <?php echo $mascota['tipo'] == 'perro' ? 'selected' : ''; ?>>Perro
                                </option>
                                <option value="gato" <?php echo $mascota['tipo'] == 'gato' ? 'selected' : ''; ?>>Gato
                                </option>
                                <option value="otro" <?php echo $mascota['tipo'] == 'otro' ? 'selected' : ''; ?>>Otro
                                </option>
                            </select>
                        </div>
                        <div class="form-grupo">
                            <label>Sexo *</label>
                            <select name="sexo" required>
                                <option value="macho" <?php echo $mascota['sexo'] == 'macho' ? 'selected' : ''; ?>>Macho
                                </option>
                                <option value="hembra" <?php echo $mascota['sexo'] == 'hembra' ? 'selected' : ''; ?>>
                                    Hembra</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label>Fecha de nacimiento *</label>
                        <input type="date" name="cumpleanos_mascota"
                            value="<?php echo $mascota['cumpleaños_mascota']; ?>" required
                            max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-grupo">
                        <label>Peso actual (kg)</label>
                        <input type="number" name="peso_actual" value="<?php echo $ficha ? $ficha['peso'] : ''; ?>"
                            step="0.1" min="0.1" max="200" placeholder="Ej: 28.5">
                        <small>Opcional</small>
                    </div>

                    <div class="form-botones">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                        <button type="submit" class="btn-guardar"><svg xmlns="http://www.w3.org/2000/svg" height="20px"
                                viewBox="0 -960 960 960" width="20px" fill="#666666">
                                <path
                                    d="M816-672v456q0 29.7-21.15 50.85Q773.7-144 744-144H216q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h456l144 144ZM480-252q45 0 76.5-31.5T588-360q0-45-31.5-76.5T480-468q-45 0-76.5 31.5T372-360q0 45 31.5 76.5T480-252ZM264-552h336v-144H264v144Z" />
                            </svg> Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Agregar Peso -->
        <div id="modalPeso" class="modal">
            <div class="modal-contenido modal-pequeno">
                <div class="modal-header">
                    <h3><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                            fill="#EA3323">
                            <path
                                d="m107-384-59-42 192-312 120 144 168-264 120 168 146-222 58 42-202 307-119-166-163 257-119-143-142 231Zm468.77 144Q616-240 644-267.77q28-27.78 28-68Q672-376 644.23-404q-27.78-28-68-28Q536-432 508-404.23q-28 27.78-28 68Q480-296 507.77-268q27.78 28 68 28ZM765-96l-98-98q-19.91 13-43.13 19.5Q600.65-168 576-168q-70 0-119-49t-49-119q0-70 49-119t119-49q70 0 119 49t49 119q0 24.65-6.5 47.87T718-245l98 98-51 51Z" />
                        </svg> Registrar Peso</h3>
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
                        <input type="date" name="fecha" required max="<?php echo date('Y-m-d'); ?>"
                            value="<?php echo date('Y-m-d'); ?>">
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
                    <h3><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                            fill="#789DE5">
                            <path
                                d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZM263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624h168L528-792v168Z" />
                        </svg> Nuevo Recordatorio</h3>
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