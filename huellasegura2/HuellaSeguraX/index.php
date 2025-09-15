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

// Obtener mascotas del usuario
$consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo' ORDER BY nombre_mascota ASC";
$resultado_mascotas = $conexion->query($consulta_mascotas);

// Obtener eventos próximos de esta semana
$fecha_hoy = date('Y-m-d');
$fecha_fin_semana = date('Y-m-d', strtotime('+7 days'));
$consulta_eventos = "SELECT e.*, m.nombre_mascota, m.foto_mascota 
                     FROM eventos e 
                     JOIN mascotas m ON e.id_mascota = m.id_mascota 
                     WHERE e.id_usuario = $usuario_id 
                     AND e.fecha BETWEEN '$fecha_hoy' AND '$fecha_fin_semana' 
                     AND e.estado = 'activo'
                     ORDER BY e.fecha ASC LIMIT 5";
$resultado_eventos = $conexion->query($consulta_eventos);

// Obtener citas veterinarias para hoy y próximas
$consulta_citas_hoy = "SELECT c.*, m.nombre_mascota, m.foto_mascota 
                       FROM citas_veterinarias c 
                       JOIN mascotas m ON c.id_mascota = m.id_mascota 
                       WHERE m.id_usuario = $usuario_id 
                       AND c.fecha = '$fecha_hoy' 
                       AND c.estado != 'completada'
                       ORDER BY c.fecha ASC";
$resultado_citas_hoy = $conexion->query($consulta_citas_hoy);

$consulta_citas_proximas = "SELECT c.*, m.nombre_mascota 
                           FROM citas_veterinarias c 
                           JOIN mascotas m ON c.id_mascota = m.id_mascota 
                           WHERE m.id_usuario = $usuario_id 
                           AND c.fecha > '$fecha_hoy' 
                           AND c.fecha <= '$fecha_fin_semana'
                           AND c.estado != 'completada'
                           ORDER BY c.fecha ASC LIMIT 3";
$resultado_citas_proximas = $conexion->query($consulta_citas_proximas);

// Obtener mascotas perdidas (datos de ejemplo o reales)
$consulta_perdidas = "SELECT p.*, pp.*, m.nombre_mascota, m.tipo, u.nombre_usuario 
                      FROM publicaciones p 
                      JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      JOIN usuarios u ON p.id_usuario = u.id_usuario
                      WHERE p.estado = 'activo' 
                      ORDER BY p.fecha DESC LIMIT 2";
$resultado_perdidas = $conexion->query($consulta_perdidas);

// Contar citas urgentes para hoy
$total_citas_hoy = $resultado_citas_hoy ? $resultado_citas_hoy->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - PetCare</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Estilos específicos para index.php */
        .barra-busqueda-principal {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            padding: 0 4px;
        }

        .input-busqueda-principal {
            flex: 1;
            padding: 16px 20px;
            border: 2px solid #e8e8e8;
            border-radius: 25px;
            font-size: 16px;
            background: white;
            outline: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .input-busqueda-principal:focus {
            border-color: #D35400;
        }

        .input-busqueda-principal::placeholder {
            color: #999;
        }

        .boton-filtro-principal {
            background: white;
            border: 2px solid #e8e8e8;
            padding: 16px;
            border-radius: 20px;
            cursor: pointer;
            color: #666;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .boton-filtro-principal:hover {
            border-color: #D35400;
            color: #D35400;
        }

        /* Estilos para Mis Mascotas */
        .seccion-mis-mascotas {
            margin-bottom: 30px;
        }

        .titulo-mis-mascotas {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .mascotas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .mascota-card-principal {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }

        .mascota-card-principal:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .mascota-foto-principal {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .mascota-info-principal {
            padding: 20px;
        }

        .nombre-mascota-principal {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .detalles-mascota-principal {
            color: #999;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .boton-agregar-mascota {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #D35400;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: color 0.3s;
        }

        .boton-agregar-mascota:hover {
            color: #B8450E;
        }

        .banner-adopcion {
            background: linear-gradient(135deg, #FF9800, #F57C00);
            border-radius: 20px;
            padding: 24px;
            color: white;
            text-align: left;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .banner-adopcion::before {
            content: '';
            position: absolute;
            right: -20px;
            top: -20px;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .banner-adopcion h3 {
            font-size: 20px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .banner-adopcion p {
            font-size: 14px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .boton-ver-adopciones {
            background: #E74C3C;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .boton-ver-adopciones:hover {
            background: #C0392B;
            transform: translateY(-2px);
        }

        /* Calendario de Cuidados */
        .calendario-cuidados {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .encabezado-calendario {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .titulo-calendario {
            font-size: 20px;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .eventos-programados {
            color: #999;
            font-size: 14px;
        }

        .navegacion-mes {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .boton-nav-mes {
            background: none;
            border: none;
            font-size: 20px;
            color: #666;
            cursor: pointer;
            padding: 4px;
            transition: color 0.3s;
        }

        .boton-nav-mes:hover {
            color: #D35400;
        }

        .mes-actual {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .mini-calendario {
            margin-bottom: 24px;
        }

        .encabezado-dias {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-bottom: 8px;
        }

        .dia-semana {
            text-align: center;
            font-size: 12px;
            color: #999;
            padding: 8px 4px;
            font-weight: 500;
        }

        .dias-calendario {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .dia-calendario {
            text-align: center;
            padding: 8px 4px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            position: relative;
        }

        .dia-calendario:hover {
            background: #f5f5f5;
        }

        .dia-calendario.hoy {
            background: #333;
            color: white;
            font-weight: 600;
        }

        .dia-calendario.evento {
            background: #FFF3CD;
            color: #856404;
            font-weight: 600;
        }

        .dia-calendario.evento::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: #F39C12;
            border-radius: 50%;
        }

        .eventos-hoy {
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }

        .encabezado-eventos-hoy {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .titulo-eventos-hoy {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .contador-eventos {
            background: #E74C3C;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .lista-eventos-hoy {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .evento-hoy {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #FFF8E1;
            border-left: 4px solid #F39C12;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .evento-hoy.urgente {
            background: #FFEBEE;
            border-left-color: #E74C3C;
        }

        .evento-hoy:hover {
            transform: translateX(4px);
        }

        .icono-evento {
            font-size: 20px;
        }

        .info-evento {
            flex: 1;
        }

        .titulo-evento {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .detalles-evento {
            font-size: 12px;
            color: #666;
        }

        .estado-urgente {
            background: #E74C3C;
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .estado-medio {
            background: #FF9800;
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .proximos-eventos {
            border-top: 1px solid #f0f0f0;
            padding-top: 16px;
        }

        .titulo-proximos {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
            font-weight: 500;
        }

        .evento-proximo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f8f8f8;
        }

        .evento-proximo:last-child {
            border-bottom: none;
        }

        .info-evento-proximo {
            font-size: 14px;
            color: #333;
        }

        .fecha-evento-proximo {
            font-size: 12px;
            color: #999;
        }

        /* Mascotas Perdidas en Index */
        .mascotas-perdidas-index {
            margin-bottom: 30px;
        }

        .encabezado-perdidas-index {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .titulo-perdidas-index {
            font-size: 20px;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .enlace-ver-todas {
            color: #E74C3C;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .enlace-ver-todas:hover {
            text-decoration: underline;
        }

        .boton-reporte-index {
            width: 100%;
            background: #E74C3C;
            color: white;
            border: none;
            padding: 18px 24px;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .boton-reporte-index:hover {
            background: #C0392B;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(231, 76, 60, 0.4);
        }

        .lista-perdidas-index {
            margin-bottom: 20px;
        }

        .perdida-item-index {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: white;
            border-radius: 15px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #E74C3C;
            transition: all 0.3s;
        }

        .perdida-item-index:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .foto-perdida-index {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .placeholder-perdida-index {
            width: 60px;
            height: 60px;
            background: #f5f5f5;
            border: 2px dashed #ddd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 20px;
            flex-shrink: 0;
        }

        .info-perdida-index {
            flex: 1;
        }

        .nombre-perdida-index {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .detalles-perdida-index {
            font-size: 13px;
            color: #666;
            margin-bottom: 4px;
        }

        .estado-perdida-index {
            background: #E74C3C;
            color: white;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .ayuda-perdidas-index {
            background: #FFF8E1;
            border: 1px solid #FFECB5;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }

        .ayuda-perdidas-index h4 {
            color: #856404;
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .ayuda-perdidas-index p {
            color: #856404;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .boton-ver-perdidas {
            background: #FFC107;
            color: #333;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .boton-ver-perdidas:hover {
            background: #FFB300;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mascotas-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .barra-busqueda-principal {
                flex-direction: column;
                gap: 12px;
            }
            
            .encabezado-calendario {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .eventos-programados {
                order: -1;
            }
            
            .dias-calendario {
                gap: 2px;
            }
            
            .dia-calendario {
                padding: 6px 2px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Barra de búsqueda principal -->
        <div class="barra-busqueda-principal">
            <input type="text" class="input-busqueda-principal" placeholder="🔍 Buscar mascotas, veterinarios, recordatorios...">
            <button class="boton-filtro-principal">🎚️</button>
        </div>

        <!-- Mis Mascotas -->
        <section class="seccion-mis-mascotas">
            <h2 class="titulo-mis-mascotas">Mis Mascotas</h2>
            
            <div class="mascotas-grid">
                <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                    <?php while($mascota = $resultado_mascotas->fetch_assoc()): ?>
                        <div class="mascota-card-principal" onclick="window.location.href='perfil-mascota.php?id=<?php echo $mascota['id_mascota']; ?>'">
                            <!--<img src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'mascota-default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" 
                                 class="mascota-foto-principal"> -->
                            <div class="mascota-info-principal">
                                <h3 class="nombre-mascota-principal"><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h3>
                                <p class="detalles-mascota-principal"><?php echo ucfirst($mascota['tipo']); ?></p>
                                <p class="detalles-mascota-principal"><?php echo $mascota['edad_mascota']; ?> años</p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Botón agregar más mascotas -->
                    <div style="display: flex; align-items: center; justify-content: center; padding: 40px;">
                        <a href="mis-mascotas.php" class="boton-agregar-mascota">
                            <span style="font-size: 24px;">+</span> Agregar
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div style="font-size: 48px; margin-bottom: 16px;">🐕</div>
                        <p style="color: #666; font-size: 16px; margin-bottom: 20px;">¡Todavía no tienes mascotas registradas!</p>
                        <a href="mis-mascotas.php" class="boton-agregar-mascota" style="display: inline-flex;">
                            <span style="font-size: 20px;">+</span> Agregar Primera Mascota
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Banner de adopción -->
            <div class="banner-adopcion">
                <h3>❤️ ¿Buscas una nueva mascota?</h3>
                <p>Hay mascotas esperando un hogar. La adopción es amor puro.</p>
                <button class="boton-ver-adopciones" onclick="window.location.href='adopciones.php'">
                    ❤️ Ver Mascotas en Adopción
                </button>
            </div>
        </section>

        <!-- Calendario de Cuidados -->
        <section class="calendario-cuidados">
            <div class="encabezado-calendario">
                <div>
                    <h3 class="titulo-calendario">📅 Calendario de Cuidados</h3>
                    <p class="eventos-programados">5 eventos programados</p>
                </div>
                <div class="navegacion-mes">
                    <button class="boton-nav-mes" onclick="cambiarMes(-1)">‹</button>
                    <span class="mes-actual" id="mesActual">septiembre de 2025</span>
                    <button class="boton-nav-mes" onclick="cambiarMes(1)">›</button>
                </div>
            </div>

            <div class="mini-calendario">
                <div class="encabezado-dias">
                    <div class="dia-semana">Su</div>
                    <div class="dia-semana">Mo</div>
                    <div class="dia-semana">Tu</div>
                    <div class="dia-semana">We</div>
                    <div class="dia-semana">Th</div>
                    <div class="dia-semana">Fr</div>
                    <div class="dia-semana">Sa</div>
                </div>
                
                <div class="dias-calendario" id="diasCalendario">
                    <!-- Los días se generarán dinámicamente con JavaScript -->
                </div>
            </div>

            <div class="eventos-hoy">
                <div class="encabezado-eventos-hoy">
                    <h4 class="titulo-eventos-hoy">📅 Hoy</h4>
                    <span class="contador-eventos"><?php echo $total_citas_hoy; ?></span>
                </div>

                <div class="lista-eventos-hoy">
                    <?php if ($resultado_citas_hoy && $resultado_citas_hoy->num_rows > 0): ?>
                        <?php while($cita = $resultado_citas_hoy->fetch_assoc()): ?>
                            <div class="evento-hoy <?php echo ($cita['motivo'] == 'Vacuna anual' || $cita['motivo'] == 'urgente') ? 'urgente' : ''; ?>">
                                <div class="icono-evento"><?php echo $cita['motivo'] == 'Vacuna anual' ? '💉' : '💊'; ?></div>
                                <div class="info-evento">
                                    <div class="titulo-evento"><?php echo htmlspecialchars($cita['motivo']); ?></div>
                                    <div class="detalles-evento">
                                        <?php echo htmlspecialchars($cita['nombre_mascota']); ?> • 
                                        <?php echo date('H:i', strtotime($cita['fecha'] . ' 14:00')); ?>
                                    </div>
                                </div>
                                <?php if ($cita['motivo'] == 'Vacuna anual'): ?>
                                    <div class="estado-urgente">Urgente</div>
                                <?php else: ?>
                                    <div class="estado-medio">Medio</div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Eventos de ejemplo -->
                        <div class="evento-hoy urgente">
                            <div class="icono-evento">💉</div>
                            <div class="info-evento">
                                <div class="titulo-evento">Vacuna anual</div>
                                <div class="detalles-evento">Max • 14:00</div>
                                <div style="font-size: 11px; color: #666; margin-top: 2px;">Vacuna anual completa</div>
                            </div>
                            <div class="estado-urgente">Urgente</div>
                        </div>
                        
                        <div class="evento-hoy">
                            <div class="icono-evento">💊</div>
                            <div class="info-evento">
                                <div class="titulo-evento">Medicina para alergias</div>
                                <div class="detalles-evento">Luna • 18:30</div>
                                <div style="font-size: 11px; color: #666; margin-top: 2px;">Administrar antihistamínico</div>
                            </div>
                            <div class="estado-medio">Medio</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="proximos-eventos">
                    <h4 class="titulo-proximos">Próximos eventos esta semana</h4>
                    <?php if ($resultado_eventos && $resultado_eventos->num_rows > 0): ?>
                        <?php while($evento = $resultado_eventos->fetch_assoc()): ?>
                            <div class="evento-proximo">
                                <div class="info-evento-proximo">
                                    🐕 <?php echo htmlspecialchars($evento['nombre_mascota']); ?>
                                </div>
                                <div class="fecha-evento-proximo">
                                    <?php 
                                    $fecha_evento = new DateTime($evento['fecha']);
                                    $hoy = new DateTime();
                                    $diff = $hoy->diff($fecha_evento);
                                    
                                    if ($diff->days == 0) {
                                        echo "Hoy";
                                    } elseif ($diff->days == 1) {
                                        echo "Mañana";
                                    } else {
                                        echo $fecha_evento->format('D, j M');
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Eventos de ejemplo -->
                        <div class="evento-proximo">
                            <div class="info-evento-proximo">🐕 Max</div>
                            <div class="fecha-evento-proximo">Mañana</div>
                        </div>
                        <div class="evento-proximo">
                            <div class="info-evento-proximo">🌙 Luna</div>
                            <div class="fecha-evento-proximo">mar, 16 sept</div>
                        </div>
                        <div class="evento-proximo">
                            <div class="info-evento-proximo">🐕 Max</div>
                            <div class="fecha-evento-proximo">dom, 21 sept</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Recordatorios Urgentes (mantener la sección original) -->
        <section class="recordatorios-urgentes">
            <div class="section-header">
                <h3>🔔 Recordatorios Urgentes</h3>
                <span class="count"><?php echo $total_citas_hoy; ?> para hoy</span>
            </div>
            
            <div class="urgente-list">
                <?php if ($resultado_citas_hoy && $resultado_citas_hoy->num_rows > 0): ?>
                    <?php 
                    // Reset pointer para volver a iterar
                    $resultado_citas_hoy->data_seek(0);
                    while($cita = $resultado_citas_hoy->fetch_assoc()): 
                    ?>
                        <div class="urgente-item <?php echo ($cita['motivo'] == 'Vacuna anual') ? 'urgente' : ''; ?>">
                            <div class="urgente-info">
                                <span class="mascota-name"><?php echo htmlspecialchars($cita['nombre_mascota']); ?> • <?php echo htmlspecialchars($cita['motivo']); ?></span>
                                <span class="urgente-time">🕐 14:00</span>
                                <?php if ($cita['motivo'] == 'Vacuna anual'): ?>
                                    <span class="urgente-label">Urgente</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="urgente-item urgente">
                        <div class="urgente-info">
                            <span class="mascota-name">Max • Vacuna</span>
                            <span class="urgente-time">🕐 14:00</span>
                            <span class="urgente-label">Urgente</span>
                        </div>
                    </div>
                    
                    <div class="urgente-item">
                        <div class="urgente-info">
                            <span class="mascota-name">Luna • Medicina</span>
                            <span class="urgente-time">🕐 18:30</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="proximamente">
                <h4>📅 Próximamente</h4>
                <?php if ($resultado_citas_proximas && $resultado_citas_proximas->num_rows > 0): ?>
                    <?php while($cita_proxima = $resultado_citas_proximas->fetch_assoc()): ?>
                        <div class="proximo-item">
                            <span class="proximo-info">🌅 <?php echo date('D', strtotime($cita_proxima['fecha'])); ?> • <?php echo htmlspecialchars($cita_proxima['nombre_mascota']); ?> • <?php echo htmlspecialchars($cita_proxima['motivo']); ?></span>
                            <span class="proximo-time">10:00</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="proximo-item">
                        <span class="proximo-info">🌅 Mañana • Max • Cita veterinario</span>
                        <span class="proximo-time">10:00</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <a href="veterinaria.php" class="ver-todos">Ver todos los recordatorios →</a>
        </section>

        <!-- Mascotas Perdidas -->
        <section class="mascotas-perdidas-index">
            <div class="encabezado-perdidas-index">
                <h3 class="titulo-perdidas-index">🔍 Mascotas Perdidas</h3>
                <a href="mascotas-perdidas.php" class="enlace-ver-todas">Ver todas</a>
            </div>
            
            <button class="boton-reporte-index" onclick="window.location.href='mascotas-perdidas.php'">
                ⚠️ ¡Reportar Mascota Perdida!
            </button>
            
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
                        <img src="imagenes/buddy.jpg" alt="Buddy" class="foto-perdida-index">
                        <div class="info-perdida-index">
                            <h4 class="nombre-perdida-index">Buddy</h4>
                            <p class="detalles-perdida-index">Perro Labrador</p>
                            <p class="detalles-perdida-index">📍 Parque del Retiro • Hace 3 días</p>
                        </div>
                        <span class="estado-perdida-index">PERDIDO</span>
                    </div>
                    
                    <div class="perdida-item-index">
                        <div class="placeholder-perdida-index">📷</div>
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

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn active">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
    <script>
        // Funcionalidad del calendario
        let mesActual = new Date().getMonth();
        let añoActual = new Date().getFullYear();
        
        const meses = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];

        function generarCalendario(mes, año) {
            const primerDia = new Date(año, mes, 1).getDay();
            const diasEnMes = new Date(año, mes + 1, 0).getDate();
            const hoy = new Date();
            const esHoy = (dia) => hoy.getDate() === dia && hoy.getMonth() === mes && hoy.getFullYear() === año;
            
            // Días con eventos (ejemplo - podrías conectar esto con la base de datos)
            const diasConEventos = [15, 16, 21];
            
            let html = '';
            
            // Días vacíos del mes anterior
            for (let i = 0; i < primerDia; i++) {
                const diasMesAnterior = new Date(año, mes, 0).getDate();
                const dia = diasMesAnterior - primerDia + i + 1;
                html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
            }
            
            // Días del mes actual
            for (let dia = 1; dia <= diasEnMes; dia++) {
                let clases = 'dia-calendario';
                if (esHoy(dia)) clases += ' hoy';
                if (diasConEventos.includes(dia)) clases += ' evento';
                
                html += `<div class="${clases}" onclick="seleccionarDia(${dia})">${dia}</div>`;
            }
            
            // Días del próximo mes para completar la grilla
            const celdasTotales = 42; // 6 semanas × 7 días
            const celdasUsadas = primerDia + diasEnMes;
            const diasProximoMes = celdasTotales - celdasUsadas;
            
            for (let dia = 1; dia <= diasProximoMes; dia++) {
                html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
            }
            
            document.getElementById('diasCalendario').innerHTML = html;
            document.getElementById('mesActual').textContent = `${meses[mes]} de ${año}`;
        }

        function cambiarMes(direccion) {
            mesActual += direccion;
            if (mesActual > 11) {
                mesActual = 0;
                añoActual++;
            } else if (mesActual < 0) {
                mesActual = 11;
                añoActual--;
            }
            generarCalendario(mesActual, añoActual);
        }

        function seleccionarDia(dia) {
            // Remover selección previa
            document.querySelectorAll('.dia-calendario.seleccionado').forEach(d => 
                d.classList.remove('seleccionado')
            );
            
            // Agregar selección al día clickeado
            event.target.classList.add('seleccionado');
            
            // Aquí podrías cargar eventos específicos del día
            console.log(`Día seleccionado: ${dia}/${mesActual + 1}/${añoActual}`);
        }

        // Inicializar calendario
        document.addEventListener('DOMContentLoaded', function() {
            generarCalendario(mesActual, añoActual);
            
            // Funcionalidad de búsqueda
            const inputBusqueda = document.querySelector('.input-busqueda-principal');
            let timeoutBusqueda;
            
            inputBusqueda.addEventListener('input', function() {
                clearTimeout(timeoutBusqueda);
                const termino = this.value.trim();
                
                timeoutBusqueda = setTimeout(() => {
                    if (termino.length > 2) {
                        realizarBusqueda(termino);
                    }
                }, 300);
            });
        });

        function realizarBusqueda(termino) {
            console.log('Buscando:', termino);
            // Aquí implementarías la búsqueda real
            // Por ahora solo mostramos un mensaje
            if (termino.toLowerCase().includes('veterinario')) {
                window.location.href = 'veterinaria.php';
            } else if (termino.toLowerCase().includes('perdida')) {
                window.location.href = 'mascotas-perdidas.php';
            } else if (termino.toLowerCase().includes('adopcion')) {
                window.location.href = 'adopciones.php';
            }
        }

        // Animaciones de entrada para las tarjetas
        function animarTarjetas() {
            const tarjetas = document.querySelectorAll('.mascota-card-principal, .perdida-item-index');
            tarjetas.forEach((tarjeta, index) => {
                tarjeta.style.opacity = '0';
                tarjeta.style.transform = 'translateY(20px)';
                tarjeta.style.transition = `all 0.3s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    tarjeta.style.opacity = '1';
                    tarjeta.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        // Ejecutar animaciones cuando se carga la página
        document.addEventListener('DOMContentLoaded', animarTarjetas);
    </script>
</body>
</html>