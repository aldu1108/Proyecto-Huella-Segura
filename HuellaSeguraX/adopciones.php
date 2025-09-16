<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    header("Location: adopciones.php?id=" . $id_publicacion);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener mascotas del usuario para el selector
$consulta_mascotas = "SELECT id_mascota, nombre_mascota, tipo FROM mascotas 
                      WHERE id_usuario = $usuario_id AND estado = 'activo' 
                      ORDER BY nombre_mascota ASC";
$resultado_mascotas = $conexion->query($consulta_mascotas);

// Obtener publicaciones de adopción con información más completa
$consulta_adopciones = "SELECT p.*, pa.*, m.*, u.nombre_usuario, u.telefono_usuario, u.email_usuario
                        FROM publicaciones p 
                        JOIN publicacion_adopcion pa ON p.id_anuncio = pa.id_publicacion
                        JOIN mascotas m ON p.id_mascota = m.id_mascota
                        JOIN usuarios u ON p.id_usuario = u.id_usuario
                        WHERE p.estado = 'activo' 
                        ORDER BY p.fecha DESC";
$resultado_adopciones = $conexion->query($consulta_adopciones);

// Contar estadísticas
$total_disponibles = $resultado_adopciones ? $resultado_adopciones->num_rows : 0;
$total_adoptados = 15; // Esto se podría calcular con una consulta adicional
$total_urgentes = 1;   // Esto se podría calcular basado en algún criterio

// Mensajes de éxito/error
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['exito'])) {
    switch ($_GET['exito']) {
        case 'solicitud_enviada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'la mascota';
            $mensaje = "¡Solicitud de adopción de $nombre enviada exitosamente! El propietario se pondrá en contacto contigo.";
            $tipo_mensaje = 'success';
            break;
        case 'publicacion_creada':
            $nombre = isset($_GET['mascota']) ? $_GET['mascota'] : 'tu mascota';
            $mensaje = "¡Publicación de adopción de $nombre creada exitosamente! Esperamos que encuentre un hogar pronto.";
            $tipo_mensaje = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'adopcion_no_valida':
            $mensaje = 'La publicación de adopción no está disponible.';
            $tipo_mensaje = 'error';
            break;
        case 'propia_mascota':
            $mensaje = 'No puedes solicitar adoptar tu propia mascota.';
            $tipo_mensaje = 'error';
            break;
        case 'solicitud_duplicada':
            $mensaje = 'Ya has solicitado adoptar esta mascota.';
            $tipo_mensaje = 'error';
            break;
        case 'descripcion_requerida':
            $mensaje = 'Debes explicar por qué quieres adoptar esta mascota.';
            $tipo_mensaje = 'error';
            break;
        case 'publicacion_existente':
            $mensaje = 'Esta mascota ya tiene una publicación de adopción activa.';
            $tipo_mensaje = 'error';
            break;
        case 'campos_requeridos':
            $mensaje = 'Por favor completa todos los campos requeridos.';
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
    <title>Adopciones - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    
    <style>
        .mensaje-adopcion {
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

        /* Estilos del modal solicitud */
        .modal-solicitud {
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

        .contenido-modal-solicitud {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .encabezado-modal-solicitud {
            padding: 24px 24px 16px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid #f0f0f0;
        }

        /* Estilos del modal crear publicación */
        .modal-crear-publicacion {
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

        .contenido-modal-publicacion {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .encabezado-modal-publicacion {
            padding: 24px 24px 16px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid #f0f0f0;
        }

        .titulo-modal {
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

        .formulario-modal {
            padding: 24px;
        }

        .grupo-input {
            margin-bottom: 20px;
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
            border-color: #E91E63;
        }

        .textarea-form {
            resize: vertical;
            min-height: 100px;
        }

        .botones-modal {
            display: flex;
            gap: 12px;
            padding: 24px;
            border-top: 1px solid #f0f0f0;
        }

        .boton-cancelar, .boton-enviar {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-cancelar {
            background: white;
            color: #666;
            border: 2px solid #e8e8e8;
        }

        .boton-enviar {
            background: #E91E63;
            color: white;
        }

        .boton-enviar:hover {
            background: #C2185B;
        }

        .boton-crear-publicacion {
            background: #E91E63;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .boton-crear-publicacion:hover {
            background: #C2185B;
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

        @media (max-width: 768px) {
            .contenido-modal-solicitud, .contenido-modal-publicacion {
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <?php if (!empty($mensaje)): ?>
                <div class="mensaje-adopcion mensaje-<?php echo $tipo_mensaje; ?>">
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

        <!-- Header de adopciones -->
        <section class="adopciones-header">
            <h1 class="titulo-adopciones">Adopción de Mascotas ❤️</h1>
            <p class="subtitulo-adopciones">Dale una segunda oportunidad a una mascota</p>
        </section>

        <!-- Botón crear publicación de adopción -->
        <button class="boton-crear-publicacion" onclick="mostrarFormularioPublicacion()">
            ❤️ ¡Publicar en Adopción!
            <small>+ Buscar hogar para tu mascota</small>
        </button>

        <!-- Sección de adopción -->
        <section class="seccion-adopcion">
            <div class="section-header">
                <h3>❤️ Adopción</h3>
            </div>

            <!-- Filtros -->
            <div class="filtros-adopcion">
                <button class="filtro-adopcion activo">❤️ Todos</button>
                <button class="filtro-adopcion">🐕 Perros</button>
                <button class="filtro-adopcion">🐱 Gatos</button>
                <button class="filtro-adopcion">⚙️ Otros</button>
            </div>

            <!-- Estadísticas -->
            <div class="estadisticas-adopcion">
                <div class="estadistica-adopcion">
                    <div class="numero-estadistica-adopcion"><?php echo $total_disponibles; ?></div>
                    <div class="etiqueta-estadistica-adopcion">Disponibles</div>
                </div>
                <div class="estadistica-adopcion urgente">
                    <div class="numero-estadistica-adopcion"><?php echo $total_urgentes; ?></div>
                    <div class="etiqueta-estadistica-adopcion">Urgentes</div>
                </div>
                <div class="estadistica-adopcion">
                    <div class="numero-estadistica-adopcion"><?php echo $total_adoptados; ?></div>
                    <div class="etiqueta-estadistica-adopcion">Adoptados</div>
                </div>
            </div>

            <!-- Mascotas en adopción -->
            <div class="lista-adopciones">
                <?php if ($resultado_adopciones && $resultado_adopciones->num_rows > 0): ?>
                        <?php while ($adopcion = $resultado_adopciones->fetch_assoc()): ?>
                                <div class="tarjeta-adopcion">
                                    <div class="badge-adopcion">ADOPCIÓN</div>
                            
                                    <div class="contenido-adopcion">
                                        <img src="imagenes/<?php echo htmlspecialchars($adopcion['foto_mascota']); ?>" 
                                             alt="<?php echo htmlspecialchars($adopcion['nombre_mascota']); ?>" 
                                             class="foto-mascota-adopcion"
                                             onerror="this.src='imagenes/mascota-default.jpg'">
                                
                                        <div class="info-adopcion">
                                            <div class="encabezado-adopcion">
                                                <div class="info-basica-adopcion">
                                                    <h3><?php echo htmlspecialchars($adopcion['nombre_mascota']); ?></h3>
                                                    <p class="detalles-basicos">
                                                        <?php echo ucfirst($adopcion['tipo']); ?> • 
                                                        <?php echo $adopcion['edad_mascota']; ?> años • 
                                                        <?php echo $adopcion['sexo'] ? ($adopcion['sexo'] == 'macho' ? '♂' : '♀') : ''; ?>
                                                    </p>
                                                </div>
                                            </div>
                                    
                                            <p class="descripcion-adopcion"><?php echo nl2br(htmlspecialchars(substr($adopcion['descripcion'], 0, 150))); ?>...</p>
                                    
                                            <div class="meta-adopcion">
                                                <span>📍 <?php echo htmlspecialchars($adopcion['lugar_adopcion']); ?></span>
                                                <span>📅 <?php echo date('d/m/Y', strtotime($adopcion['fecha'])); ?></span>
                                                <span>🏠 <?php echo htmlspecialchars($adopcion['nombre_usuario']); ?></span>
                                            </div>
                                    
                                            <div class="estados-adopcion">
                                                <span class="estado-badge estado-vacunado">✅ Disponible</span>
                                            </div>
                                        </div>
                                    </div>
                            
                                    <button class="boton-interesa-adoptar" onclick="mostrarSolicitudAdopcion(<?php echo $adopcion['id_adopcion']; ?>, '<?php echo htmlspecialchars($adopcion['nombre_mascota']); ?>')">
                                        ❤️ Me interesa adoptar →
                                    </button>
                                </div>
                        <?php endwhile; ?>
                <?php else: ?>
                        <!-- Mascotas de ejemplo si no hay datos -->
                        <div class="tarjeta-adopcion">
                            <div class="badge-adopcion">ADOPCIÓN</div>
                        
                            <div class="contenido-adopcion">
                                <img src="imagenes/perro.jpg" alt="Carlos" class="foto-mascota-adopcion">
                            
                                <div class="info-adopcion">
                                    <div class="encabezado-adopcion">
                                        <div class="info-basica-adopcion">
                                            <h3>Carlos</h3>
                                            <p class="detalles-basicos">Mestizo • 2 años • ♂</p>
                                        </div>
                                    </div>
                                
                                    <p class="descripcion-adopcion">Carlos es un perro muy cariñoso y juguetón. Le encanta pasear y es perfecto para familias activas.</p>
                                
                                    <div class="meta-adopcion">
                                        <span>📍 Madrid Centro</span>
                                        <span>📅 Hace 5 días</span>
                                        <span>🏠 Refugio Esperanza</span>
                                    </div>
                                
                                    <div class="estados-adopcion">
                                        <span class="estado-badge estado-vacunado">✅ Vacunado</span>
                                        <span class="estado-badge estado-esterilizado">💉 Esterilizado</span>
                                    </div>
                                </div>
                            </div>
                        
                            <button class="boton-interesa-adoptar" onclick="alert('Este es un ejemplo. Registra mascotas para ver funcionalidad completa.')">
                                ❤️ Me interesa adoptar →
                            </button>
                        </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modal para solicitar adopción -->
    <div class="modal-solicitud" id="modalSolicitud">
        <div class="contenido-modal-solicitud">
            <div class="encabezado-modal-solicitud">
                <h3 class="titulo-modal">Solicitar Adopción</h3>
                <button class="boton-cerrar-modal" onclick="cerrarSolicitud()">×</button>
                <p id="nombreMascotaSolicitud" style="color: #666; margin: 8px 0 0 0;"></p>
            </div>
            
            <form class="formulario-modal" id="formularioSolicitud" action="procesar-solicitud-adopcion.php" method="POST">
                <input type="hidden" id="idAdopcionSolicitud" name="id_adopcion">
                
                <div class="grupo-input">
                    <label class="etiqueta-input requerido">¿Por qué quieres adoptar esta mascota?</label>
                    <textarea class="textarea-form" name="descripcion" placeholder="Describe tu experiencia con mascotas, tu hogar, disponibilidad de tiempo, etc." required></textarea>
                </div>

                <div class="grupo-input">
                    <label class="etiqueta-input">Información adicional</label>
                    <textarea class="textarea-form" name="informacion_adicional" placeholder="Cualquier información adicional que quieras compartir (opcional)"></textarea>
                </div>

                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarSolicitud()">Cancelar</button>
                    <button type="submit" class="boton-enviar">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para crear publicación de adopción -->
    <div class="modal-crear-publicacion" id="modalCrearPublicacion">
        <div class="contenido-modal-publicacion">
            <div class="encabezado-modal-publicacion">
                <h3 class="titulo-modal">Publicar en Adopción</h3>
                <button class="boton-cerrar-modal" onclick="cerrarPublicacion()">×</button>
                <p style="color: #666; margin: 8px 0 0 0;">Busca un hogar lleno de amor para tu mascota</p>
            </div>
            
            <form class="formulario-modal" id="formularioPublicacion" action="procesar-publicacion-adopcion.php" method="POST">
                <div class="grupo-input">
                    <label class="etiqueta-input requerido">¿Cuál mascota quieres dar en adopción?</label>
                    <select class="select-form" name="id_mascota" required>
                        <option value="">Seleccionar mascota</option>
                        <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                                <?php
                                mysqli_data_seek($resultado_mascotas, 0); // Resetear el pointer del resultado
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

                <?php if (!$resultado_mascotas || $resultado_mascotas->num_rows == 0): ?>
                        <div class="sin-mascotas-mensaje">
                            <p>⚠️ Primero debes registrar tus mascotas</p>
                            <button type="button" class="boton-agregar-mascota"
                                onclick="window.location.href='mis-mascotas.php'">
                                + Agregar Mascota
                            </button>
                        </div>
                <?php endif; ?>

                <div class="grupo-input">
                    <label class="etiqueta-input requerido">Condiciones de adopción</label>
                    <textarea class="textarea-form" name="condiciones" placeholder="Ejemplo: Hogar con jardín, experiencia previa con perros, disponibilidad de tiempo, etc." required></textarea>
                </div>

                <div class="grupo-input">
                    <label class="etiqueta-input requerido">Lugar de entrega</label>
                    <input type="text" class="input-form" name="lugar_adopcion" placeholder="Ciudad, barrio o zona específica" required>
                </div>

                <div class="grupo-input">
                    <label class="etiqueta-input">Motivo de la adopción</label>
                    <textarea class="textarea-form" name="motivo_adopcion" placeholder="¿Por qué necesitas encontrar un hogar para tu mascota? (opcional)"></textarea>
                </div>

                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarPublicacion()">Cancelar</button>
                    <button type="submit" class="boton-enviar">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn active" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">💥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script>
        // Variables globales
        let adopcionSeleccionada = null;
        let nombreMascotaSeleccionada = '';

        // Modal solicitud de adopción
        function mostrarSolicitudAdopcion(idAdopcion, nombreMascota) {
            adopcionSeleccionada = idAdopcion;
            nombreMascotaSeleccionada = nombreMascota;
            
            document.getElementById('idAdopcionSolicitud').value = idAdopcion;
            document.getElementById('nombreMascotaSolicitud').textContent = `Solicitar adopción de: ${nombreMascota}`;
            document.getElementById('modalSolicitud').style.display = 'flex';
        }

        function cerrarSolicitud() {
            document.getElementById('modalSolicitud').style.display = 'none';
            document.getElementById('formularioSolicitud').reset();
        }

        // Modal crear publicación
        function mostrarFormularioPublicacion() {
            document.getElementById('modalCrearPublicacion').style.display = 'flex';
        }

        function cerrarPublicacion() {
            document.getElementById('modalCrearPublicacion').style.display = 'none';
            document.getElementById('formularioPublicacion').reset();
        }

        // Cerrar modales al hacer clic fuera
        document.getElementById('modalSolicitud').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarSolicitud();
            }
        });

        document.getElementById('modalCrearPublicacion').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarPublicacion();
            }
        });

        // Funcionalidad de filtros
        document.querySelectorAll('.filtro-adopcion').forEach(filtro => {
            filtro.addEventListener('click', function() {
                document.querySelectorAll('.filtro-adopcion').forEach(f => f.classList.remove('activo'));
                this.classList.add('activo');
                
                const tipoFiltro = this.textContent.trim();
                filtrarMascotas(tipoFiltro);
            });
        });

        function filtrarMascotas(tipo) {
            const tarjetas = document.querySelectorAll('.tarjeta-adopcion');
            
            tarjetas.forEach(tarjeta => {
                if (tipo === '❤️ Todos') {
                    tarjeta.style.display = 'block';
                } else {
                    const tipoMascota = tarjeta.querySelector('.detalles-basicos').textContent;
                    let mostrar = false;
                    
                    if (tipo === '🐕 Perros' && tipoMascota.toLowerCase().includes('perro')) {
                        mostrar = true;
                    } else if (tipo === '🐱 Gatos' && tipoMascota.toLowerCase().includes('gato')) {
                        mostrar = true;
                    } else if (tipo === '⚙️ Otros' && !tipoMascota.toLowerCase().includes('perro') && !tipoMascota.toLowerCase().includes('gato')) {
                        mostrar = true;
                    }
                    
                    tarjeta.style.display = mostrar ? 'block' : 'none';
                }
            });
        }

        // Animaciones de entrada para las tarjetas
        function animarTarjetas() {
            const tarjetas = document.querySelectorAll('.tarjeta-adopcion');
            tarjetas.forEach((tarjeta, index) => {
                tarjeta.style.animationDelay = `${index * 0.1}s`;
            });
        }

        // Cerrar modales con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarSolicitud();
                cerrarPublicacion();
            }
        });

        // Ejecutar animaciones al cargar
        document.addEventListener('DOMContentLoaded', function() {
            animarTarjetas();
            
            // Manejar mensajes de la URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('exito') || urlParams.get('error')) {
                // Limpiar URL después de mostrar el mensaje
                setTimeout(() => {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 5000);
            }
        });

        console.log('Sistema de adopciones cargado correctamente');
    </script>
</body>
</html>