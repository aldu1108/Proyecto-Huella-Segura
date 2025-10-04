<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mascota_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Obtener información de la mascota
$consulta_mascota = "SELECT * FROM mascotas WHERE id_mascota = $mascota_id AND id_usuario = $usuario_id AND estado = 'activo'";
$resultado_mascota = $conexion->query($consulta_mascota);

if (!$resultado_mascota || $resultado_mascota->num_rows == 0) {
    header("Location: mis-mascotas.php");
    exit();
}

$mascota = $resultado_mascota->fetch_assoc();

// Obtener datos de seguimiento de peso
$consulta_peso = "SELECT * FROM seguimiento_peso WHERE id_mascota = $mascota_id ORDER BY fecha DESC";
$resultado_peso = $conexion->query($consulta_peso);

// Obtener descripción de la ficha de salud
$consulta_ficha = "SELECT * FROM fichas_de_salud WHERE id_mascota = $mascota_id LIMIT 1";
$resultado_ficha = $conexion->query($consulta_ficha);
$ficha = $resultado_ficha && $resultado_ficha->num_rows > 0 ? $resultado_ficha->fetch_assoc() : null;

// Obtener recordatorios personales de esta mascota
$consulta_recordatorios_mascota = "SELECT r.* FROM recordatorios_personales r
                                   JOIN recordatorio_mascota rm ON r.id_recordatorio = rm.id_recordatorio
                                   WHERE rm.id_mascota = $mascota_id 
                                   AND r.id_usuario = $usuario_id
                                   AND DATE(r.fecha) >= '$fecha_hoy'
                                   AND r.completado = 0
                                   ORDER BY r.fecha ASC";
$resultado_recordatorios_mascota = $conexion->query($consulta_recordatorios_mascota);

// Obtener días con eventos para el calendario de esta mascota
$consulta_dias_mascota = "SELECT DISTINCT DAY(c.fecha) as dia
                         FROM citas_veterinarias c
                         WHERE c.id_mascota = $mascota_id 
                         AND MONTH(c.fecha) = MONTH(CURDATE())
                         AND YEAR(c.fecha) = YEAR(CURDATE())
                         AND c.estado = 'programada'
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
while($dia = $resultado_dias_mascota->fetch_assoc()) {
    $dias_con_eventos_mascota[] = (int)$dia['dia'];
}

// Obtener historial médico
$consulta_historial = "SELECT * FROM historiales_medicos WHERE id_mascota = $mascota_id ORDER BY fecha DESC LIMIT 10";
$resultado_historial = $conexion->query($consulta_historial);

// Obtener próximas citas
$fecha_hoy = date('Y-m-d');
$consulta_citas = "SELECT * FROM citas_veterinarias WHERE id_mascota = $mascota_id AND fecha >= '$fecha_hoy' ORDER BY fecha ASC LIMIT 5";
$resultado_citas = $conexion->query($consulta_citas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $mascota['nombre_mascota']; ?> - Perfil - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/perfil-mascota.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <!-- Header -->
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Header de la mascota -->
        <section class="mascota-header">
            <div class="mascota-info-principal">
                <img src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'imagenes/mascota-default.jpg'; ?>" 
                     alt="<?php echo $mascota['nombre_mascota']; ?>" class="mascota-foto-grande">
                <div class="mascota-datos">
                    <h1><?php echo $mascota['nombre_mascota']; ?> 💡</h1>
                    <p class="mascota-tipo">Perro • Golden Retriever</p>
                    <div class="mascota-stats">
                        <span class="stat">🎂 <?php echo $mascota['edad_mascota']; ?> años</span>
                        <span class="stat">⚖️ 28 kg</span>
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
                    <label>Sexo:</label>
                    <span><?php echo ucfirst($mascota['sexo']); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Tipo:</label>
                    <span><?php echo ucfirst($mascota['tipo']); ?></span>
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
                    
                    <div class="info-item">
                        <label>Esterilizado:</label>
                        <span><?php echo $ficha['esterilizado'] ? 'Sí' : 'No'; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Vacunas:</label>
                        <span><?php echo !empty($ficha['vacunas']) ? $ficha['vacunas'] : 'Sin registrar'; ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($ficha && !empty($ficha['documento'])): ?>
                <div class="descripcion">
                    <label>Descripción:</label>
                    <p><?php echo nl2br(htmlspecialchars($ficha['documento'])); ?></p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Calendario de eventos de esta mascota -->
        <section class="calendario-cuidados" style="background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px;">
            <div class="encabezado-calendario">
                <h3 class="titulo-calendario">📅 Calendario de <?php echo $mascota['nombre_mascota']; ?></h3>
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
                
                <div class="dias-calendario" id="diasCalendario"></div>
            </div>
        </section>

        <!-- Seguimiento de peso -->
        <section class="seguimiento-peso">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>Seguimiento de Peso</h3>
                <button class="btn-veterinarios" onclick="mostrarModalPeso()">+ Agregar Peso</button>
            </div>
            
            <div class="grafico-container" style="position: relative; height: 250px; padding: 20px;">
                <?php if ($resultado_peso && $resultado_peso->num_rows > 0): ?>
                    <div class="peso-timeline" style="position: relative; width: 100%; height: 100%;">
                        <?php 
                        $pesos = [];
                        while($p = $resultado_peso->fetch_assoc()) {
                            $pesos[] = $p;
                        }
                        $pesos = array_reverse($pesos);
                        $max_peso = max(array_column($pesos, 'peso'));
                        $min_peso = min(array_column($pesos, 'peso'));
                        $rango = $max_peso - $min_peso > 0 ? $max_peso - $min_peso : 1;
                        
                        foreach($pesos as $index => $peso):
                            $left = ($index / (count($pesos) - 1)) * 80 + 10;
                            $bottom = (($peso['peso'] - $min_peso) / $rango) * 60 + 10;
                        ?>
                            <div class="peso-point" style="left: <?php echo $left; ?>%; bottom: <?php echo $bottom; ?>%;">
                                <span class="peso-value"><?php echo $peso['peso']; ?>kg</span>
                                <span class="peso-date"><?php echo date('d/m', strtotime($peso['fecha'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px; color: #95A5A6;">
                        <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
                        <p>No hay registros de peso</p>
                        <p style="font-size: 14px; margin-top: 8px;">Agrega el primer registro para ver el gráfico</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Modal agregar peso -->
        <div id="modalPeso" class="modal-alerta-demo" style="display: none;">
            <div class="contenido-modal-alerta" style="max-width: 400px;">
                <div class="encabezado-modal-alerta">
                    <h3 class="titulo-modal-alerta">📊 Registrar Peso</h3>
                    <button class="boton-cerrar-modal-alerta" onclick="cerrarModalPeso()">×</button>
                </div>
                
                <form method="POST" action="procesar-peso.php" class="cuerpo-modal-alerta">
                    <input type="hidden" name="id_mascota" value="<?php echo $mascota_id; ?>">
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Peso (kg)</label>
                        <input type="number" name="peso" step="0.1" min="0" max="200" required 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Fecha</label>
                        <input type="date" name="fecha" required max="<?php echo date('Y-m-d'); ?>"
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px;">
                    </div>
                    
                    <div class="botones-modal-alerta">
                        <button type="button" class="boton-cancelar-alerta" onclick="cerrarModalPeso()">Cancelar</button>
                        <button type="submit" class="boton-login-alerta">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script>
    window.diasConEventosCalendario = <?php echo json_encode($dias_con_eventos_mascota); ?>;

    function mostrarModalPeso() {
        document.getElementById('modalPeso').style.display = 'flex';
    }

    function cerrarModalPeso() {
        document.getElementById('modalPeso').style.display = 'none';
    }
    </script>
    <script src="js/scripts.js"></script>
</body>
</html>
                