<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'] ?? 'demo';

// Obtener estadísticas
$consulta_perdidas = "SELECT COUNT(*) as total FROM publicacion_perdida pp 
                      JOIN publicaciones p ON pp.id_publicacion = p.id_anuncio 
                      WHERE p.estado = 'activo'";
$resultado_perdidas = $conexion->query($consulta_perdidas);
$total_perdidas = $resultado_perdidas ? $resultado_perdidas->fetch_assoc()['total'] : 0;

// Contar encontradas (mascotas con estado 'encontrado')
$consulta_encontradas = "SELECT COUNT(*) as total FROM publicaciones p 
                         WHERE p.estado = 'encontrado'";
$resultado_encontradas = $conexion->query($consulta_encontradas);
$total_encontradas = $resultado_encontradas ? $resultado_encontradas->fetch_assoc()['total'] : 0;

// Contar con recompensa
$consulta_recompensa = "SELECT COUNT(*) as total FROM publicacion_perdida pp 
                        JOIN publicaciones p ON pp.id_publicacion = p.id_anuncio 
                        WHERE p.estado = 'activo' AND pp.recompensa > 0";
$resultado_recompensa = $conexion->query($consulta_recompensa);
$total_recompensa = $resultado_recompensa ? $resultado_recompensa->fetch_assoc()['total'] : 0;

// Obtener mascotas del usuario para el selector
if ($rol_usuario === 'demo') {
    $resultado_mascotas = null; // Demo no tiene mascotas
} else {
    $consulta_mascotas = "SELECT id_mascota, nombre_mascota, tipo FROM mascotas 
                          WHERE id_usuario = $usuario_id AND estado = 'activo' 
                          ORDER BY nombre_mascota ASC";
    $resultado_mascotas = $conexion->query($consulta_mascotas);
}

// Obtener reportes activos con información más completa
$consulta_reportes = "SELECT p.*, pp.*, m.nombre_mascota, m.tipo, m.foto_mascota, m.sexo, m.edad_mascota,
                             u.nombre_usuario, u.telefono_usuario, u.email_usuario
                      FROM publicaciones p 
                      JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      JOIN usuarios u ON p.id_usuario = u.id_usuario
                      WHERE p.estado = 'activo' 
                      ORDER BY p.fecha DESC";
$resultado_reportes = $conexion->query($consulta_reportes);

// Mensajes de éxito/error
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'reporte_creado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "¡Reporte de $nombre creado exitosamente! La comunidad será notificada.";
            $tipo_mensaje = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'datos_incompletos':
            $mensaje = 'Por favor completa todos los campos requeridos.';
            $tipo_mensaje = 'error';
            break;
        case 'mascota_no_valida':
            $mensaje = 'La mascota seleccionada no es válida.';
            $tipo_mensaje = 'error';
            break;
        case 'fecha_invalida':
            $mensaje = 'La fecha no puede ser futura.';
            $tipo_mensaje = 'error';
            break;
        case 'error_crear_reporte':
            $mensaje = 'Error al crear el reporte. Intenta de nuevo.';
            $tipo_mensaje = 'error';
            break;
        default:
            $mensaje = 'Ocurrió un error inesperado.';
            $tipo_mensaje = 'error';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascotas Perdidas - PetCare</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/mascotas-perdidas.css">
    <link rel="stylesheet" href="css/modal-alerta-demo.css">
</head>

<body>
    <!-- Header -->
    <header>
         <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <?php if (!empty($mensaje)): ?>
                <div class="mensaje-perdida mensaje-<?php echo $tipo_mensaje; ?>">
                    <span><?php echo $mensaje; ?></span>
                    <button onclick="this.parentElement.remove()" style="
                background: none;
                border: none;
                color: inherit;
                cursor: pointer;
                font-size: 18px;
                padding: 0 5px;
            ">×</button>
                </div>
        <?php endif; ?>

        <!-- Header de mascotas perdidas -->
        <section class="mascotas-perdidas-header">
            <h1 class="titulo-mascotas-perdidas">Mascotas Perdidas 🔍</h1>
            <p class="subtitulo-mascotas-perdidas">Ayuda a reunir familias con sus mascotas</p>
        </section>

        <!-- Estadísticas -->
        <div class="estadisticas-perdidas">
            <div class="estadistica-item">
                <span class="numero-estadistica perdidas"><?php echo $total_perdidas; ?></span>
                <span>Perdidas</span>
            </div>
            <div class="estadistica-item">
                <span class="numero-estadistica encontradas"><?php echo $total_encontradas; ?></span>
                <span>Encontradas</span>
            </div>
            <div class="estadistica-item">
                <span class="numero-estadistica recompensa"><?php echo $total_recompensa; ?></span>
                <span>Con recompensa</span>
            </div>
        </div>

        <!-- Botón reportar mascota perdida -->
        <?php if ($rol_usuario == 'demo'): ?>
            <button class="boton-reporte-perdida" onclick="mostrarModalAlerta('Inicia sesión para reportar mascotas perdidas\n\nPara usar esta función necesitas:\n• Tener una cuenta registrada\n• Registrar tus mascotas')">
                ⚠️ ¡Reportar Mascota Perdida!
                <small>+ Crear reporte de búsqueda</small>
            </button>
        <?php else: ?>
            <button class="boton-reporte-perdida" onclick="mostrarFormularioReporte()">
                ⚠️ ¡Reportar Mascota Perdida!
                <small>+ Crear reporte de búsqueda</small>
            </button>
        <?php endif; ?>

        <!-- Alerta actúa rápido -->
        <div class="alerta-actua-rapido">
            <h4>⚠️ Actúa rápido:</h4>
            <ul>
                <li>Las primeras 24 horas son cruciales</li>
                <li>Comparte en redes sociales automáticamente</li>
                <li>Alerta a la comunidad local</li>
            </ul>
        </div>

        <!-- Reportes Activos -->
        <section class="seccion-reportes-activos">
            <h3>Reportes Activos (<?php echo $total_perdidas; ?>)</h3>

            <div class="contenedor-reportes">
                <?php if ($resultado_reportes && $resultado_reportes->num_rows > 0): ?>
                        <?php while ($reporte = $resultado_reportes->fetch_assoc()): ?>
                                <?php
                                // Calcular días perdido
                                $fecha_perdida = new DateTime($reporte['fecha_perdida']);
                                $fecha_actual = new DateTime();
                                $dias_perdido = $fecha_actual->diff($fecha_perdida)->days;
                                ?>
                                <div class="tarjeta-reporte">
                                    <div class="etiqueta-estado">PERDIDO</div>
                                    <?php if ($reporte['recompensa'] > 0): ?>
                                            <div class="etiqueta-recompensa">Recompensa: €<?php echo number_format($reporte['recompensa'], 0); ?></div>
                                    <?php endif; ?>
                            
                                    <div class="contenido-reporte">
                                        <img src="<?php 
                                        if ($reporte['foto_mascota'] === 'mascota-default.jpg') {
                                            echo 'imagenes/mascota-default.jpg';
                                        } else {
                                            echo file_exists('uploads/mascotas/' . $reporte['foto_mascota']) ? 'uploads/mascotas/' . $reporte['foto_mascota'] : 'imagenes/mascota-default.jpg';
                                        }
                                        ?>" 
                                        alt="<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>" 
                                        class="foto-reporte">
                                
                                        <div class="info-reporte">
                                            <h4><?php echo htmlspecialchars($reporte['nombre_mascota']); ?></h4>
                                            <p><?php echo ucfirst($reporte['tipo']); ?> • <?php echo $reporte['sexo'] ? ucfirst($reporte['sexo']) : 'No especificado'; ?></p>
                                            <p><strong>Edad:</strong> <?php echo $reporte['edad_mascota']; ?> años</p>
                                            <p>📅 Perdido hace <?php echo $dias_perdido; ?> día<?php echo $dias_perdido != 1 ? 's' : ''; ?> (<?php echo date('d/m/Y', strtotime($reporte['fecha_perdida'])); ?>)</p>
                                            <p>📍 <?php echo htmlspecialchars($reporte['ultima_ubicacion']); ?></p>
                                        </div>
                                    </div>
                            
                                    <div class="acciones-reporte">
                                        <?php if ($rol_usuario == 'demo'): ?>
                                            <button class="boton-contactar" onclick="mostrarModalAlerta('Inicia sesión para contactar propietarios\n\nRegístrate para poder:\n• Contactar a dueños de mascotas perdidas\n• Reportar avistamientos\n• Ayudar a reunir familias')">
                                                📞 Contactar
                                            </button>
                                        <?php else: ?>
                                            <button class="boton-contactar" onclick="contactarPropietario('<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>', '<?php echo htmlspecialchars($reporte['telefono_usuario']); ?>')">
                                                📞 Contactar
                                            </button>
                                        <?php endif; ?>
                                        <button class="boton-compartir-reporte" onclick="compartirReporte('<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>')" title="Compartir">📤</button>
                                        <button class="boton-ver-detalles" onclick="verDetallesReporte(<?php echo $reporte['id_anuncio']; ?>)" title="Ver detalles">👁</button>
                                    </div>
                            
                                    <div class="mensaje-ayuda">
                                        ¿Has visto a <?php echo htmlspecialchars($reporte['nombre_mascota']); ?>? Tu ayuda puede ser crucial para reunir a esta familia.
                                    </div>
                                </div>
                        <?php endwhile; ?>
                <?php else: ?>
                        <div class="sin-reportes">
                            <p>No hay reportes activos en este momento.</p>
                            <p>¡Esperemos que todas las mascotas estén seguras en casa!</p>
                        </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modal para reportar mascota perdida -->
    <div class="modal-reporte" id="modalReporte">
        <div class="contenido-modal-reporte">
            <form class="formulario-reporte" id="formularioReporte" action="procesar-mascota-perdida.php" method="POST">
                <!-- Paso 1: Seleccionar Mascota -->
                <div class="paso-formulario" id="paso1" style="display: block;">
                    <div class="encabezado-modal-reporte">
                        <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                        <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                        <div class="progreso-pasos">
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso"></div>
                            <div class="paso-progreso"></div>
                        </div>
                        <p class="subtitulo-paso">Paso 1 de 3</p>
                    </div>

                    <div class="formulario-reporte">
                        <div class="seccion-formulario">
                            <h4 class="titulo-seccion-form">Seleccionar Mascota</h4>

                            <div class="grupo-input campo-completo">
                                <label class="etiqueta-input requerido">¿Cuál mascota se perdió?</label>
                                <select class="select-form" name="id_mascota" required>
                                    <option value="">Seleccionar mascota</option>
                                    <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                                            <?php while ($mascota = $resultado_mascotas->fetch_assoc()): ?>
                                                    <option value="<?php echo $mascota['id_mascota']; ?>">
                                                        <?php echo htmlspecialchars($mascota['nombre_mascota']); ?>
                                                        (<?php echo ucfirst($mascota['tipo']); ?>)
                                                    </option>
                                            <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <?php if ($rol_usuario == 'demo'): ?>
                                <div class="sin-mascotas-mensaje">
                                    <p>⚠️ Necesitas una cuenta para reportar mascotas</p>
                                    <div style="display: flex; gap: 10px;">
                                        <button type="button" class="boton-agregar-mascota" onclick="window.location.href='login.php'">
                                            🔑 Iniciar Sesión
                                        </button>
                                        <button type="button" class="boton-agregar-mascota" onclick="window.location.href='registro.php'">
                                            📝 Registrarse
                                        </button>
                                    </div>
                                </div>
                            <?php elseif (!$resultado_mascotas || $resultado_mascotas->num_rows == 0): ?>
                                <div class="sin-mascotas-mensaje">
                                    <p>⚠️ Primero debes registrar tus mascotas</p>
                                    <button type="button" class="boton-agregar-mascota"
                                        onclick="window.location.href='mis-mascotas.php'">
                                        + Agregar Mascota
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="botones-formulario">
                            <button type="button" class="boton-siguiente" onclick="siguientePaso(2)">Siguiente</button>
                        </div>
                    </div>
                </div>

                <!-- Paso 2: Información del Incidente -->
                <div class="paso-formulario" id="paso2" style="display: none;">
                    <div class="encabezado-modal-reporte">
                        <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                        <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                        <div class="progreso-pasos">
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso"></div>
                        </div>
                        <p class="subtitulo-paso">Paso 2 de 3</p>
                    </div>

                    <div class="formulario-reporte">
                        <div class="seccion-formulario">
                            <h4 class="titulo-seccion-form">¿Cuándo y dónde se perdió?</h4>

                            <div class="grupo-campos">
                                <div class="grupo-input">
                                    <label class="etiqueta-input requerido">Fecha en que se perdió</label>
                                    <input type="date" class="input-form" name="fecha_perdida" required
                                        max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="grupo-input">
                                    <label class="etiqueta-input">Hora aproximada</label>
                                    <input type="time" class="input-form" name="hora_perdida">
                                </div>
                            </div>

                            <div class="grupo-input campo-completo">
                                <label class="etiqueta-input requerido">Última ubicación conocida</label>
                                <div class="campo-ubicacion">
                                    <input type="text" class="input-form" placeholder="📍 Dirección o punto de referencia"
                                        name="ultima_ubicacion" required>
                                    <button type="button" class="boton-gps" onclick="obtenerUbicacion()">GPS</button>
                                </div>
                            </div>

                            <div class="grupo-input campo-completo">
                                <label class="etiqueta-input">¿Cómo se perdió?</label>
                                <textarea class="textarea-form"
                                    placeholder="Describe las circunstancias: se escapó del jardín, se asustó con fuegos artificiales, etc."
                                    name="descripcion"></textarea>
                            </div>

                            <div class="checkbox-recompensa">
                                <input type="checkbox" id="checkboxRecompensa" class="input-checkbox"
                                    name="ofrecer_recompensa" onchange="toggleRecompensa()">
                                <label for="checkboxRecompensa" class="etiqueta-checkbox">💰 Ofrecer recompensa</label>
                            </div>

                            <div class="grupo-input campo-completo" id="campoRecompensa" style="display: none;">
                                <label class="etiqueta-input">Monto de la recompensa (€)</label>
                                <input type="number" class="input-form" name="recompensa" min="0"
                                    placeholder="Ejemplo: 100">
                            </div>
                        </div>

                        <div class="botones-formulario">
                            <button type="button" class="boton-anterior" onclick="anteriorPaso(1)">Anterior</button>
                            <button type="button" class="boton-siguiente" onclick="siguientePaso(3)">Siguiente</button>
                        </div>
                    </div>
                </div>

                <!-- Paso 3: Confirmar y Enviar -->
                <div class="paso-formulario" id="paso3" style="display: none;">
                    <div class="encabezado-modal-reporte">
                        <h3 class="titulo-modal-reporte">Confirmar Reporte</h3>
                        <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                        <div class="progreso-pasos">
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso activo"></div>
                            <div class="paso-progreso activo"></div>
                        </div>
                        <p class="subtitulo-paso">Paso 3 de 3</p>
                    </div>

                    <div class="formulario-reporte">
                        <div class="seccion-formulario">
                            <h4 class="titulo-seccion-form">Resumen del Reporte</h4>
                            <div id="resumenReporte">
                                <!-- Se llenará dinámicamente -->
                            </div>

                            <div class="aviso-importante">
                                <h5>📢 Qué haremos después:</h5>
                                <ul>
                                    <li>✅ Publicaremos tu reporte en la comunidad</li>
                                    <li>🔍 Activaremos búsqueda en la zona</li>
                                    <li>📞 Te contactaremos si hay pistas</li>
                                </ul>
                            </div>
                        </div>

                        <div class="botones-formulario">
                            <button type="button" class="boton-anterior" onclick="anteriorPaso(2)">Anterior</button>
                            <button type="submit" class="boton-crear-reporte">🚨 Crear Reporte</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
    <script src="js/mascotas-perdidas.js"></script>
    <script src="js/modal-alerta-demo.js"></script>

</body>
</html>