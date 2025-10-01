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

// Obtener eventos próximos de esta semana
// Variables para fechas
$fecha_hoy = date('Y-m-d');
$fecha_fin_semana = date('Y-m-d', strtotime('+7 days'));

// Verificar rol y ejecutar consultas según corresponda
if ($rol_usuario === 'demo') {
    // Demo no tiene datos personales
    $resultado_eventos = null;
    $resultado_citas_hoy = null;
    $resultado_citas_proximas = null;
    $total_citas_hoy = 0;
} else {
    // Usuario normal - ejecutar consultas
    $consulta_eventos = "SELECT e.*, m.nombre_mascota, m.foto_mascota 
                         FROM eventos_comunidad e 
                         JOIN mascotas m ON e.id_mascota = m.id_mascota 
                         WHERE e.id_usuario = $usuario_id 
                         AND e.fecha BETWEEN '$fecha_hoy' AND '$fecha_fin_semana' 
                         AND e.estado = 'activo'
                         ORDER BY e.fecha ASC LIMIT 5";
    $resultado_eventos = $conexion->query($consulta_eventos);

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
    
    $total_citas_hoy = $resultado_citas_hoy ? $resultado_citas_hoy->num_rows : 0;
}

// Obtener mascotas perdidas (esta consulta sí funciona para demo)
$consulta_perdidas = "SELECT p.*, pp.*, m.nombre_mascota, m.tipo, u.nombre_usuario 
                      FROM publicaciones p 
                      JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      JOIN usuarios u ON p.id_usuario = u.id_usuario
                      WHERE p.estado = 'activo' 
                      ORDER BY p.fecha DESC LIMIT 2";
$resultado_perdidas = $conexion->query($consulta_perdidas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - PetCare</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/modal-alerta-demo.css">
    <!-- Icono  -->
    <link rel="icon" type="image/png" href="imagenes/logo-hs.png">

    <!-- Opcional: para distintas resoluciones -->
    <link rel="icon" type="image/png" sizes="32x32" href="imagenes/logo-hs.png">
    <link rel="icon" type="image/png" sizes="16x16" href="imagenes/logo-hs.png">

    <!-- Para cuando alguien guarda la webapp en la pantalla de inicio en iPhone/iPad -->
    <link rel="apple-touch-icon" href="imagenes/logo-hs.png">
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Barra de búsqueda principal -->
        <div class="barra-busqueda-principal">
            <input type="text" class="input-busqueda-principal" placeholder="Buscar mascotas, veterinarios, recordatorios...">
            <button class="boton-filtro-principal">🔍</button>
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
                                + Agregar Primera Mascota
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
                                <span style="font-size: 20px;">+</span> Agregar Primera Mascota
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
                    <?php if ($rol_usuario == 'demo'): ?>
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📅</div>
                            <h4>Tu calendario está vacío</h4>
                            <p>Inicia sesión o regístrate para ver tus recordatorios y eventos</p>
                            <div style="margin-top: 20px;">
                                <a href="login.php" style="color: #D35400; text-decoration: none; margin-right: 10px;">Iniciar Sesión</a>
                                <span>|</span>
                                <a href="registro.php" style="color: #D35400; text-decoration: none; margin-left: 10px;">Registrarse</a>
                            </div>
                        </div>
                    <?php else: ?>
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

        <hr style="margin: 2rem 0; color: white">
        
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
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
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
    <script src="js/modal-alerta-demo.js"></script>
</body>
</html>