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
                             u.nombre_usuario, u.telefono_usuario, u.email_usuario, u.foto_usuario,
                             p.fecha as fecha_publicacion
                      FROM publicaciones p 
                      JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      JOIN usuarios u ON p.id_usuario = u.id_usuario
                      WHERE p.estado = 'activo' 
                      ORDER BY p.fecha DESC";
$resultado_reportes = $conexion->query($consulta_reportes);

// Verificar si se está editando un reporte
$modo_edicion = false;
$reporte_editar = null;

if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $id_editar = (int) $_GET['editar'];

    // Obtener datos del reporte a editar
    $consulta_editar = "SELECT p.*, pp.*, m.nombre_mascota, m.tipo, m.id_mascota
                        FROM publicaciones p 
                        JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                        JOIN mascotas m ON p.id_mascota = m.id_mascota
                        WHERE p.id_anuncio = ? AND p.id_usuario = ?";

    $stmt_editar = $conexion->prepare($consulta_editar);
    if ($stmt_editar) {
        $stmt_editar->bind_param("ii", $id_editar, $usuario_id);
        $stmt_editar->execute();
        $resultado_editar = $stmt_editar->get_result();

        if ($resultado_editar->num_rows > 0) {
            $modo_edicion = true;
            $reporte_editar = $resultado_editar->fetch_assoc();

            // Extraer descripción detallada
            $descripcion_completa = $reporte_editar['descripcion'];
            $reporte_editar['descripcion_detalle'] = '';

            if (preg_match('/Detalles:\s*(.+?)(?:\n\n|$)/s', $descripcion_completa, $matches)) {
                $reporte_editar['descripcion_detalle'] = trim($matches[1]);
            }

            // Formatear fecha y hora
            $reporte_editar['fecha_perdida_formato'] = date('Y-m-d', strtotime($reporte_editar['fecha_perdida']));
            $reporte_editar['hora_perdida_formato'] = date('H:i', strtotime($reporte_editar['fecha_perdida']));
        }
        $stmt_editar->close();
    }
}

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
        case 'reporte_actualizado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "✅ Cambios guardados correctamente. El reporte de $nombre ha sido actualizado.";
            $tipo_mensaje = 'success';
            break;
        case 'mascota_encontrada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "¡Excelente noticia! Has marcado a $nombre como encontrada. El reporte ha sido cerrado.";
            $tipo_mensaje = 'success';
            break;
        case 'reporte_eliminado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "El reporte de $nombre ha sido eliminado correctamente.";
            $tipo_mensaje = 'success';
            break;
    }
}

// Agregar en la sección de errores:

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
        case 'reporte_duplicado':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'esta mascota';
            $mensaje = "Ya existe un reporte activo de {$nombre}. No puedes crear múltiples reportes de la misma mascota.";
            $tipo_mensaje = 'error';
            break;
        case 'datos_invalidos':
            $mensaje = 'Datos inválidos. Por favor intenta de nuevo.';
            $tipo_mensaje = 'error';
            break;
        case 'error_procesar':
            $mensaje = 'Error al procesar la acción. Intenta de nuevo.';
            $tipo_mensaje = 'error';
            break;
        default:
            $mensaje = 'Ocurrió un error inesperado.';
            $tipo_mensaje = 'error';
            break;
    }
}
function tiempoTranscurrido($fecha)
{
    $ahora = new DateTime();
    $fecha_pub = new DateTime($fecha);
    $diferencia = $ahora->diff($fecha_pub);

    if ($diferencia->y > 0) {
        return $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '');
    } elseif ($diferencia->m > 0) {
        return $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
    } elseif ($diferencia->d > 0) {
        return $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
    } elseif ($diferencia->h > 0) {
        return $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
    } elseif ($diferencia->i > 0) {
        return $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
    } else {
        return 'Ahora mismo';
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
    <?php include_once("includes/logo.php"); ?>
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
                                        <!-- Nuevo header con info del usuario -->
                                        <div class="header-usuario-reporte">
                                            <div class="avatar-usuario-reporte" style="background-image: url('imagenes/<?php echo htmlspecialchars($reporte['foto_usuario'] ?? 'usuario-default.jpg'); ?>')"></div>
                                            <div class="info-usuario-reporte">
                                                <h4 class="nombre-usuario-reporte"><?php echo htmlspecialchars($reporte['nombre_usuario']); ?></h4>
                                                <p class="tiempo-publicacion-reporte">Hace <?php echo tiempoTranscurrido($reporte['fecha_publicacion']); ?></p>
                                            </div>
                                        </div>

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
                                        <?php if ($reporte['id_usuario'] == $usuario_id): ?>
                                            <!-- Botones para el propietario -->
                                            <button class="boton-encontrado"
                                                onclick="marcarComoEncontrada(<?php echo $reporte['id_anuncio']; ?>, '<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>')"
                                                title="Marcar como encontrada">
                                                ✅ Encontrada
                                            </button>
                                            <button class="boton-editar-reporte" onclick="editarReporte(<?php echo $reporte['id_anuncio']; ?>)"
                                                title="Editar reporte">
                                                ✏️ Editar
                                            </button>
                                            <button class="boton-eliminar-reporte"
                                                onclick="eliminarReporte(<?php echo $reporte['id_anuncio']; ?>, '<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>')"
                                                title="Eliminar reporte">
                                                🗑️ Eliminar
                                            </button>
                                        <?php else: ?>
                                            <!-- Botones para otros usuarios -->
                                            <?php if ($rol_usuario == 'demo'): ?>
                                                <button class="boton-contactar"
                                                    onclick="mostrarModalAlerta('Inicia sesión para contactar propietarios\n\nRegístrate para poder:\n• Contactar a dueños de mascotas perdidas\n• Reportar avistamientos\n• Ayudar a reunir familias')">
                                                    📞 Contactar
                                                </button>
                                            <?php else: ?>
                                                <button class="boton-contactar"
                                                    onclick="contactarPropietario('<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>', '<?php echo htmlspecialchars($reporte['telefono_usuario']); ?>')">
                                                    📞 Contactar
                                                </button>
                                            <?php endif; ?>
                                            <button class="boton-compartir-reporte"
                                                onclick="compartirReporte('<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>')"
                                                title="Compartir">📤</button>
                                            <button class="boton-ver-detalles" onclick="verDetallesReporte(<?php echo $reporte['id_anuncio']; ?>)"
                                                title="Ver detalles">👁</button>
                                        <?php endif; ?>
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

                <!-- Modal para reportar/editar mascota perdida -->
                <div class="modal-reporte" id="modalReporte" <?php echo $modo_edicion ? 'style="display: flex;"' : ''; ?>>
                    <div class="contenido-modal-reporte">
                        <form class="formulario-reporte" id="formularioReporte" 
                            action="procesar-mascota-perdida.php" 
                            method="POST">
                            
                            <?php if ($modo_edicion): ?>
                                <input type="hidden" name="id_publicacion" value="<?php echo $reporte_editar['id_anuncio']; ?>">
                                <input type="hidden" name="id_mascota" value="<?php echo $reporte_editar['id_mascota']; ?>">
                            <?php endif; ?>
                            
                            <!-- Paso 1: Seleccionar Mascota -->
                            <div class="paso-formulario" id="paso1" style="display: <?php echo $modo_edicion ? 'none' : 'block'; ?>;">
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
                                            <select class="select-form" name="id_mascota" <?php echo !$modo_edicion ? 'required' : ''; ?>>
                                                <option value="">Seleccionar mascota</option>
                                                <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                                                    <?php 
                                                    mysqli_data_seek($resultado_mascotas, 0); // Reset pointer
                                                    while ($mascota = $resultado_mascotas->fetch_assoc()): 
                                                    ?>
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
                                                        🔒 Iniciar Sesión
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
                            <div class="paso-formulario" id="paso2" style="display: <?php echo $modo_edicion ? 'block' : 'none'; ?>;">
                                <div class="encabezado-modal-reporte">
                                    <h3 class="titulo-modal-reporte"><?php echo $modo_edicion ? 'Editar Reporte' : 'Reportar Mascota Perdida'; ?></h3>
                                    <button type="button" class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                                    <?php if (!$modo_edicion): ?>
                                    <div class="progreso-pasos">
                                        <div class="paso-progreso activo"></div>
                                        <div class="paso-progreso activo"></div>
                                        <div class="paso-progreso"></div>
                                    </div>
                                    <p class="subtitulo-paso">Paso 2 de 3</p>
                                    <?php endif; ?>
                                </div>

                                <div class="formulario-reporte">
                                    <?php if ($modo_edicion): ?>
                                    <div class="seccion-formulario">
                                        <div class="info-mascota-editar">
                                            <p><strong>Mascota:</strong> <?php echo htmlspecialchars($reporte_editar['nombre_mascota']); ?> (<?php echo ucfirst($reporte_editar['tipo']); ?>)</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="seccion-formulario">
                                        <h4 class="titulo-seccion-form">¿Cuándo y dónde se perdió?</h4>

                                        <div class="grupo-campos">
                                            <div class="grupo-input">
                                                <label class="etiqueta-input requerido">Fecha en que se perdió</label>
                                                <input type="date" class="input-form" name="fecha_perdida" required
                                                    max="<?php echo date('Y-m-d'); ?>"
                                                    value="<?php echo $modo_edicion ? $reporte_editar['fecha_perdida_formato'] : ''; ?>">
                                            </div>
                                            <div class="grupo-input">
                                                <label class="etiqueta-input">Hora aproximada</label>
                                                <input type="time" class="input-form" name="hora_perdida"
                                                    value="<?php echo $modo_edicion ? $reporte_editar['hora_perdida_formato'] : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="grupo-input campo-completo">
                                            <label class="etiqueta-input requerido">Última ubicación conocida</label>
                                            <div class="campo-ubicacion">
                                                <input type="text" class="input-form" placeholder="📍 Dirección o punto de referencia"
                                                    name="ultima_ubicacion" required
                                                    value="<?php echo $modo_edicion ? htmlspecialchars($reporte_editar['ultima_ubicacion']) : ''; ?>">
                                                <button type="button" class="boton-gps" onclick="obtenerUbicacion()">GPS</button>
                                            </div>
                                        </div>

                                        <div class="grupo-input campo-completo">
                                            <label class="etiqueta-input">¿Cómo se perdió?</label>
                                            <textarea class="textarea-form"
                                                placeholder="Describe las circunstancias: se escapó del jardín, se asustó con fuegos artificiales, etc."
                                                name="descripcion"><?php echo $modo_edicion ? htmlspecialchars($reporte_editar['descripcion_detalle']) : ''; ?></textarea>
                                        </div>

                                        <div class="checkbox-recompensa">
                                            <input type="checkbox" id="checkboxRecompensa" class="input-checkbox"
                                                name="ofrecer_recompensa" onchange="toggleRecompensa()"
                                                <?php echo ($modo_edicion && $reporte_editar['recompensa'] > 0) ? 'checked' : ''; ?>>
                                            <label for="checkboxRecompensa" class="etiqueta-checkbox">💰 Ofrecer recompensa</label>
                                        </div>

                                        <div class="grupo-input campo-completo" id="campoRecompensa" 
                                            style="display: <?php echo ($modo_edicion && $reporte_editar['recompensa'] > 0) ? 'block' : 'none'; ?>;">
                                            <label class="etiqueta-input">Monto de la recompensa (€)</label>
                                            <input type="number" class="input-form" name="recompensa" min="0"
                                                placeholder="Ejemplo: 100"
                                                value="<?php echo ($modo_edicion && $reporte_editar['recompensa'] > 0) ? $reporte_editar['recompensa'] : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="botones-formulario">
                                        <?php if ($modo_edicion): ?>
                                            <button type="button" class="boton-anterior" onclick="window.location.href='mascotas-perdidas.php'">Cancelar</button>
                                            <button type="submit" class="boton-crear-reporte">💾 Guardar Cambios</button>
                                        <?php else: ?>
                                            <button type="button" class="boton-anterior" onclick="anteriorPaso(1)">Anterior</button>
                                            <button type="button" class="boton-siguiente" onclick="siguientePaso(3)">Siguiente</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 3: Confirmar y Enviar (solo para crear nuevo) -->
                            <?php if (!$modo_edicion): ?>
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
                            <?php endif; ?>
                        </form>
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
    
    <!-- Modal Marcar como Encontrada -->
    <div class="modal-overlay-perdidas" id="modalEncontrada">
        <div class="modal-container-perdidas">
            <div class="modal-header-perdidas">
                <button class="modal-close-perdidas" onclick="cerrarModalEncontrada()">×</button>
                <div class="modal-icon-perdidas success">🎉</div>
                <h3 class="modal-title-perdidas">¡Excelente Noticia!</h3>
                <p class="modal-subtitle-perdidas">Marcar mascota como encontrada</p>
            </div>
            
            <div class="modal-body-perdidas">
                <div class="modal-mascota-info-perdidas" id="mascotaInfoEncontrada">
                    <!-- Se llenará dinámicamente -->
                </div>
                
                <div class="modal-success-box">
                    <p><strong>✨ Al confirmar:</strong></p>
                    <ul>
                        <li>El reporte se cerrará automáticamente</li>
                        <li>La comunidad será notificada de la buena noticia</li>
                        <li>El estado de la mascota se actualizará</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer-perdidas">
                <button class="modal-btn-perdidas modal-btn-cancel-perdidas" onclick="cerrarModalEncontrada()">
                    Cancelar
                </button>
                <button class="modal-btn-perdidas modal-btn-success-perdidas" onclick="confirmarEncontrada()">
                    🎉 Confirmar - ¡Está en Casa!
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Reporte -->
    <div class="modal-overlay-perdidas" id="modalEditarPerdida">
        <div class="modal-container-perdidas">
            <div class="modal-header-perdidas">
                <button class="modal-close-perdidas" onclick="cerrarModalEditarPerdida()">×</button>
                <div class="modal-icon-perdidas warning">✏️</div>
                <h3 class="modal-title-perdidas">Editar Reporte</h3>
                <p class="modal-subtitle-perdidas" id="subtituloEditarPerdida">Actualiza la información del reporte</p>
            </div>
            
            <div class="modal-body-perdidas">
                <form id="formEditarPerdida">
                    <input type="hidden" id="idPublicacionEditar">
                    
                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">Fecha en que se perdió</label>
                        <input type="date" class="form-input-perdidas" id="fechaPerdidaEditar" required>
                    </div>

                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">Hora aproximada</label>
                        <input type="time" class="form-input-perdidas" id="horaPerdidaEditar">
                    </div>

                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">Última ubicación conocida</label>
                        <div class="campo-ubicacion-perdidas">
                            <input type="text" class="form-input-perdidas" id="ubicacionEditar" placeholder="📍 Dirección o punto de referencia" required>
                            <button type="button" class="boton-gps-modal" onclick="obtenerUbicacionModal()">📍</button>
                        </div>
                    </div>

                    <div class="form-group-perdidas">
                        <label class="form-label-perdidas">¿Cómo se perdió?</label>
                        <textarea class="form-textarea-perdidas" id="descripcionEditar" placeholder="Describe las circunstancias"></textarea>
                    </div>

                    <div class="checkbox-recompensa-modal">
                        <input type="checkbox" id="checkboxRecompensaEditar" onchange="toggleRecompensaModal()">
                        <label for="checkboxRecompensaEditar">💰 Ofrecer recompensa</label>
                    </div>

                    <div class="form-group-perdidas" id="campoRecompensaEditar" style="display: none;">
                        <label class="form-label-perdidas">Monto de la recompensa (€)</label>
                        <input type="number" class="form-input-perdidas" id="recompensaEditar" min="0" placeholder="Ejemplo: 100">
                    </div>
                </form>
            </div>

            <div class="modal-footer-perdidas">
                <button class="modal-btn-perdidas modal-btn-cancel-perdidas" onclick="cerrarModalEditarPerdida()">
                    Cancelar
                </button>
                <button class="modal-btn-perdidas modal-btn-confirm-perdidas" onclick="confirmarEditarPerdida()">
                    ✏️ Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Reporte -->
    <div class="modal-overlay-perdidas" id="modalEliminarPerdida">
        <div class="modal-container-perdidas">
            <div class="modal-header-perdidas">
                <button class="modal-close-perdidas" onclick="cerrarModalEliminarPerdida()">×</button>
                <div class="modal-icon-perdidas danger">🗑️</div>
                <h3 class="modal-title-perdidas">Eliminar Reporte</h3>
                <p class="modal-subtitle-perdidas">Esta acción no se puede deshacer</p>
            </div>
            
            <div class="modal-body-perdidas">
                <div class="modal-mascota-info-perdidas" id="mascotaInfoEliminarPerdida">
                    <!-- Se llenará dinámicamente -->
                </div>
                
                <div class="modal-danger-box-perdidas">
                    <p><strong>⚠️ Advertencia:</strong></p>
                    <ul>
                        <li>El reporte será eliminado permanentemente</li>
                        <li>La comunidad dejará de recibir alertas</li>
                        <li>No podrás recuperar esta información</li>
                        <li>Si encontraste a tu mascota, usa "Encontrada" en su lugar</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer-perdidas">
                <button class="modal-btn-perdidas modal-btn-cancel-perdidas" onclick="cerrarModalEliminarPerdida()">
                    Cancelar
                </button>
                <button class="modal-btn-perdidas modal-btn-danger-perdidas" onclick="confirmarEliminarPerdida()">
                    🗑️ Sí, Eliminar Reporte
                </button>
            </div>
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

    <script src="js/notificaciones.js"></script>
    <script src="js/mascotas-perdidas.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
    <script>
        // Inicializar variable global para modo edición ANTES de cargar otros scripts
        <?php if ($modo_edicion): ?>
                window.modoEdicion = true;
            <?php else: ?>
                window.modoEdicion = false;
            <?php endif; ?>

            // Abrir automáticamente el modal si estamos en modo edición
            document.addEventListener('DOMContentLoaded', function () {
                <?php if ($modo_edicion): ?>
                    document.body.style.overflow = 'hidden';
                    pasoActual = 2; // Ir directamente al paso 2
                <?php endif; ?>
            });
    </script>
</body>
</html>