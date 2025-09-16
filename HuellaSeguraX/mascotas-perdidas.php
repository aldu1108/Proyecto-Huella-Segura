<?php
include_once('config/conexion.php');
session_start();

include_once('includes/menu_hamburguesa.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

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
$consulta_mascotas = "SELECT id_mascota, nombre_mascota, tipo FROM mascotas 
                      WHERE id_usuario = $usuario_id AND estado = 'activo' 
                      ORDER BY nombre_mascota ASC";
$resultado_mascotas = $conexion->query($consulta_mascotas);

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
    <style>
        .mensaje-perdida {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mensaje-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Estilos del modal */
        .modal-reporte {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }

        .contenido-modal-reporte {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .encabezado-modal-reporte {
            padding: 24px 24px 16px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid #f0f0f0;
        }

        .titulo-modal-reporte {
            font-size: 20px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .boton-cerrar-modal {
            position: absolute;
            top: 20px;
            right: 24px;
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
        }

        .progreso-pasos {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 16px 0 8px;
        }

        .paso-progreso {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e8e8e8;
        }

        .paso-progreso.activo {
            background: #D35400;
        }

        .subtitulo-paso {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .formulario-reporte {
            padding: 24px;
        }

        .seccion-formulario {
            margin-bottom: 24px;
        }

        .titulo-seccion-form {
            color: #333;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: 600;
        }

        .grupo-campos {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }

        .grupo-input {
            flex: 1;
            margin-bottom: 20px;
        }

        .campo-completo {
            width: 100%;
        }

        .etiqueta-input {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .etiqueta-input.requerido::after {
            content: " *";
            color: #E74C3C;
        }

        .input-form, .select-form, .textarea-form {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .input-form:focus, .select-form:focus, .textarea-form:focus {
            border-color: #D35400;
        }

        .textarea-form {
            resize: vertical;
            min-height: 80px;
        }

        .campo-ubicacion {
            display: flex;
            gap: 8px;
        }

        .boton-gps {
            background: #D35400;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            cursor: pointer;
            white-space: nowrap;
        }

        .checkbox-recompensa {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }

        .input-checkbox {
            width: 18px;
            height: 18px;
        }

        .etiqueta-checkbox {
            color: #333;
            cursor: pointer;
        }

        .botones-formulario {
            display: flex;
            gap: 12px;
            padding: 24px;
            border-top: 1px solid #f0f0f0;
        }

        .boton-anterior, .boton-siguiente {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-anterior {
            background: white;
            color: #666;
            border: 2px solid #e8e8e8;
        }

        .boton-siguiente {
            background: #D35400;
            color: white;
        }

        .boton-siguiente:hover {
            background: #B8450E;
        }

        .sin-mascotas-mensaje {
            text-align: center;
            background: #fff3cd;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #ffeaa7;
            margin: 16px 0;
        }

        .boton-agregar-mascota {
            background: #f39c12;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 12px;
        }

        .boton-crear-reporte {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }

        .boton-crear-reporte:hover {
            background: #c0392b;
        }

        .boton-crear-reporte:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .grupo-campos {
                flex-direction: column;
                gap: 0;
            }
            
            .contenido-modal-reporte {
                margin: 0 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header></header>

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
        <button class="boton-reporte-perdida" onclick="mostrarFormularioReporte()">
            ⚠️ ¡Reportar Mascota Perdida!
            <small>+ Crear reporte de búsqueda</small>
        </button>

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
                                        <img src="imagenes/<?php echo htmlspecialchars($reporte['foto_mascota']); ?>" 
                                             alt="<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>" 
                                             class="foto-reporte"
                                             onerror="this.src=''">
                                
                                        <div class="info-reporte">
                                            <h4><?php echo htmlspecialchars($reporte['nombre_mascota']); ?></h4>
                                            <p><?php echo ucfirst($reporte['tipo']); ?> • <?php echo $reporte['sexo'] ? ucfirst($reporte['sexo']) : 'No especificado'; ?></p>
                                            <p><strong>Edad:</strong> <?php echo $reporte['edad_mascota']; ?> años</p>
                                            <p>📅 Perdido hace <?php echo $dias_perdido; ?> día<?php echo $dias_perdido != 1 ? 's' : ''; ?> (<?php echo date('d/m/Y', strtotime($reporte['fecha_perdida'])); ?>)</p>
                                            <p>📍 <?php echo htmlspecialchars($reporte['ultima_ubicacion']); ?></p>
                                        </div>
                                    </div>
                            
                                    <div class="acciones-reporte">
                                        <button class="boton-contactar" onclick="contactarPropietario('<?php echo htmlspecialchars($reporte['nombre_mascota']); ?>', '<?php echo htmlspecialchars($reporte['telefono_usuario']); ?>')">
                                            📞 Contactar
                                        </button>
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

                            <?php if (!$resultado_mascotas || $resultado_mascotas->num_rows == 0): ?>
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
                                    <li>📱 Notificaremos a usuarios cercanos</li>
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

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn active" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">💥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script>
        let pasoActual = 1;
        const totalPasos = 3;
        let datosReporte = {};

        function mostrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            pasoActual = 1;
            mostrarPaso(1);
        }

        function cerrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('formularioReporte').reset();
            document.getElementById('campoRecompensa').style.display = 'none';
            pasoActual = 1;
            mostrarPaso(1);
            datosReporte = {};
        }

        function mostrarPaso(numeroPaso) {
            // Ocultar todos los pasos
            for (let i = 1; i <= totalPasos; i++) {
                const paso = document.getElementById('paso' + i);
                if (paso) {
                    paso.style.display = 'none';
                }
            }

            // Mostrar paso actual
            const pasoActual = document.getElementById('paso' + numeroPaso);
            if (pasoActual) {
                pasoActual.style.display = 'block';
            }

            // Actualizar progreso
            const pasos = document.querySelectorAll('.paso-progreso');
            pasos.forEach((paso, index) => {
                if (index < numeroPaso) {
                    paso.classList.add('activo');
                } else {
                    paso.classList.remove('activo');
                }
            });
        }

        function siguientePaso(siguientePasoNum) {
            if (validarPasoActual()) {
                guardarDatosPaso();
                pasoActual = siguientePasoNum;
                mostrarPaso(siguientePasoNum);

                if (siguientePasoNum === 3) {
                    generarResumen();
                }
            }
        }

        function anteriorPaso(anteriorPasoNum) {
            pasoActual = anteriorPasoNum;
            mostrarPaso(anteriorPasoNum);
        }

        function validarPasoActual() {
            const pasoDiv = document.getElementById('paso' + pasoActual);
            const camposRequeridos = pasoDiv.querySelectorAll('input[required], select[required]');

            let esValido = true;
            camposRequeridos.forEach(campo => {
                if (!campo.value.trim()) {
                    campo.style.borderColor = '#E74C3C';
                    esValido = false;
                } else {
                    campo.style.borderColor = '#e8e8e8';
                }
            });

            if (!esValido) {
                alert('Por favor completa todos los campos requeridos');
            }

            return esValido;
        }

        function guardarDatosPaso() {
            const pasoDiv = document.getElementById('paso' + pasoActual);
            const inputs = pasoDiv.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                if (input.name) {
                    if (input.type === 'checkbox') {
                        datosReporte[input.name] = input.checked;
                    } else {
                        datosReporte[input.name] = input.value;
                    }
                }
            });
        }

        function generarResumen() {
            const selectMascota = document.querySelector('select[name="id_mascota"]');
            const nombreMascota = selectMascota.options[selectMascota.selectedIndex]?.text || 'Mascota seleccionada';

            let resumen = `
                <div class="resumen-reporte">
                    <div class="item-resumen">
                        <strong>🐕 Mascota:</strong> ${nombreMascota}
                    </div>
                    <div class="item-resumen">
                        <strong>📅 Fecha perdida:</strong> ${datosReporte.fecha_perdida ? new Date(datosReporte.fecha_perdida).toLocaleDateString('es-ES') : 'No especificada'}
                    </div>
                    ${datosReporte.hora_perdida ? `
                        <div class="item-resumen">
                            <strong>⏰ Hora:</strong> ${datosReporte.hora_perdida}
                        </div>
                    ` : ''}
                    <div class="item-resumen">
                        <strong>📍 Ubicación:</strong> ${datosReporte.ultima_ubicacion || 'No especificada'}
                    </div>
                    ${datosReporte.descripcion ? `
                        <div class="item-resumen">
                            <strong>📝 Descripción:</strong> ${datosReporte.descripcion}
                        </div>
                    ` : ''}
                    ${datosReporte.ofrecer_recompensa ? `
                        <div class="item-resumen destacado">
                            <strong>💰 Recompensa:</strong> €${datosReporte.recompensa || '0'}
                        </div>
                    ` : ''}
                </div>
            `;

            document.getElementById('resumenReporte').innerHTML = resumen;
        }

        function toggleRecompensa() {
            const checkbox = document.getElementById('checkboxRecompensa');
            const campo = document.getElementById('campoRecompensa');
            campo.style.display = checkbox.checked ? 'block' : 'none';
        }

        function obtenerUbicacion() {
            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización');
                return;
            }

            const botonGps = document.querySelector('.boton-gps');
            const inputUbicacion = document.querySelector('input[name="ultima_ubicacion"]');

            botonGps.innerHTML = '⏳';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    inputUbicacion.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    botonGps.innerHTML = '✅';

                    setTimeout(() => {
                        botonGps.innerHTML = 'GPS';
                    }, 2000);
                },
                function (error) {
                    let mensaje = 'No se pudo obtener la ubicación';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            mensaje = 'Permisos de ubicación denegados';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            mensaje = 'Ubicación no disponible';
                            break;
                    }
                    alert(mensaje);
                    botonGps.innerHTML = 'GPS';
                }
            );
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalReporte').addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarFormularioReporte();
            }
        });

        // Funciones para los botones de acción
        function contactarPropietario(nombre, telefono) {
            if (telefono && telefono !== 'null' && telefono !== '') {
                if (confirm(`¿Deseas contactar al propietario de ${nombre}?\nTeléfono: ${telefono}`)) {
                    window.location.href = `tel:${telefono}`;
                }
            } else {
                alert('Información de contacto no disponible');
            }
        }

        function compartirReporte(nombre) {
            if (navigator.share) {
                navigator.share({
                    title: `Mascota perdida: ${nombre}`,
                    text: `Ayuda a encontrar a ${nombre}. Mascota perdida en la zona.`,
                    url: window.location.href
                });
            } else {
                const url = window.location.href;
                const texto = `Ayuda a encontrar a ${nombre}. Mascota perdida: ${url}`;
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(texto).then(() => {
                        alert('Enlace copiado al portapapeles. Compártelo en redes sociales.');
                    });
                } else {
                    prompt('Copia este enlace para compartir:', texto);
                }
            }
        }

        function verDetallesReporte(idReporte) {
            alert(`Ver detalles del reporte ID: ${idReporte}`);
        }

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarFormularioReporte();
            }
        });
    </script>

    <style>
        .resumen-reporte {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin: 16px 0;
        }
        
        .item-resumen {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-resumen:last-child {
            border-bottom: none;
        }
        
        .item-resumen.destacado {
            background: #fff3cd;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ffeaa7;
            margin: 8px 0;
        }
        
        .aviso-importante {
            background: #e8f5e8;
            padding: 16px;
            border-radius: 12px;
            margin-top: 20px;
        }
        
        .aviso-importante h5 {
            color: #27ae60;
            margin-bottom: 12px;
        }
        
        .aviso-importante ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .aviso-importante li {
            margin-bottom: 8px;
            color: #2c5f41;
        }

        .sin-reportes {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            background: #f8f9fa;
            border-radius: 12px;
        }
    </style>
</body>
</html>