<?php
include_once('config/conexion.php');
session_start();

include_once('includes/menu_hamburguesa.php');


if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener estadísticas
$consulta_perdidas = "SELECT COUNT(*) as total FROM publicacion_perdida pp 
                      JOIN publicaciones p ON pp.id_publicacion = p.id_anuncio 
                      WHERE p.estado = 'activo'";
$resultado_perdidas = $conexion->query($consulta_perdidas);
$total_perdidas = $resultado_perdidas ? $resultado_perdidas->fetch_assoc()['total'] : 2;

$total_encontradas = 1;
$total_recompensa = 2;

// Obtener reportes activos
$consulta_reportes = "SELECT p.*, pp.*, m.nombre_mascota, m.tipo, u.nombre_usuario 
                      FROM publicaciones p 
                      JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                      JOIN mascotas m ON p.id_mascota = m.id_mascota
                      JOIN usuarios u ON p.id_usuario = u.id_usuario
                      WHERE p.estado = 'activo' 
                      ORDER BY p.fecha DESC";
$resultado_reportes = $conexion->query($consulta_reportes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascotas Perdidas - PetCare</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Incluir los estilos actualizados aquí */
        <?php include 'css/estilos-actualizados.css'; ?>
    </style>
</head>
<body>
    <!-- Header -->
    <header class="">
        
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
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

        <!-- Barra de búsqueda -->
        <input type="text" class="barra-busqueda" placeholder="🔍 Buscar por nombre, raza o ubicación...">

        <!-- Filtros -->
        <div class="filtros-busqueda">
            <select class="filtro-select">
                <option value="perdidas">Perdidas</option>
                <option value="encontradas">Encontradas</option>
                <option value="todos">Todos</option>
            </select>
            <select class="filtro-select">
                <option value="mas-recientes">Más recientes</option>
                <option value="mas-antiguos">Más antiguos</option>
                <option value="con-recompensa">Con recompensa</option>
            </select>
        </div>

        <!-- Reportes Activos -->
        <section class="seccion-reportes-activos">
            <h3>Reportes Activos (2) </h3>

            <div class="contenedor-reportes">
                <!-- Buddy - Labrador -->
                <div class="tarjeta-reporte">
                    <div class="etiqueta-estado">PERDIDO</div>
                    <div class="etiqueta-recompensa">Recompensa: €300</div>
                    
                    <div class="contenido-reporte">
                        <img src="imagenes/perro.jpg" alt="Buddy" class="foto-reporte">
                        
                        <div class="info-reporte">
                            <h4>Buddy</h4>
                            <p>Perro • Labrador</p>
                            <p><strong>Color:</strong> Dorado</p>
                            <p><strong>Tamaño:</strong> Grande</p>
                            <p>📅 Perdido hace 604 días (2024-01-20)</p>
                            <p>📍 Parque del Retiro, Madrid</p>
                        </div>
                    </div>
                    
                    <div class="acciones-reporte">
                        <button class="boton-contactar" onclick="contactarPropietario('Buddy', '+34 123 456 789')">
                            📞 Contactar
                        </button>
                        <button class="boton-compartir-reporte" onclick="compartirReporte('Buddy')" title="Compartir">📤</button>
                        <button class="boton-ver-detalles" onclick="verDetallesReporte('Buddy')" title="Ver detalles">👁</button>
                    </div>
                    
                    <div class="mensaje-ayuda">
                        ¿Has visto a Buddy? Tu ayuda puede ser crucial para reunir a esta familia.
                    </div>
                </div>

                <!-- Mimi - Siamés -->
                <div class="tarjeta-reporte">
                    <div class="etiqueta-estado">PERDIDO</div>
                    
                    <div class="contenido-reporte">
                        <div class="foto-placeholder">
                            <span style="font-size: 32px; color: #ccc;">📷</span>
                        </div>
                        
                        <div class="info-reporte">
                            <h4>Mimi</h4>
                            <p>Gato • Siamés</p>
                            <p><strong>Color:</strong> Crema y marrón</p>
                            <p><strong>Tamaño:</strong> Pequeño</p>
                            <p>📅 Perdido hace 606 días (2024-01-18)</p>
                            <p>📍 Calle Gran Vía, 45</p>
                        </div>
                    </div>
                    
                    <div class="acciones-reporte">
                        <button class="boton-contactar" onclick="contactarPropietario('Mimi', '+34 987 654 321')">
                            📞 Contactar
                        </button>
                        <button class="boton-compartir-reporte" onclick="compartirReporte('Mimi')" title="Compartir">📤</button>
                        <button class="boton-ver-detalles" onclick="verDetallesReporte('Mimi')" title="Ver detalles">👁</button>
                    </div>
                    
                    <div class="mensaje-ayuda">
                        ¿Has visto a Mimi? Tu ayuda puede ser crucial para reunir a esta familia.
                    </div>
                </div>
            </div>

            <!-- Consejos para encontrar mascotas perdidas -->
            <div class="consejos-encontrar">
                <h4>💡 Consejos para encontrar mascotas perdidas:</h4>
                <ul>
                    <li>Busca en un radio de 1-2 km del último lugar visto</li>
                    <li>Publica en grupos locales de Facebook y redes sociales</li>
                    <li>Contacta refugios y clínicas veterinarias cercanas</li>
                    <li>Busca temprano en la mañana y al atardecer</li>
                    <li>Coloca carteles con foto y número de contacto</li>
                </ul>
            </div>
        </section>
    </main>

    <!-- Modal para reportar mascota perdida -->
    <div class="modal-reporte" id="modalReporte" style="display: none;">
        <div class="contenido-modal-reporte">
            <!-- Paso 1: Información de la Mascota -->
            <div class="paso-formulario" id="paso1" style="display: block;">
                <div class="encabezado-modal-reporte">
                    <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                    <button class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                    <div class="progreso-pasos">
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso"></div>
                        <div class="paso-progreso"></div>
                        <div class="paso-progreso"></div>
                    </div>
                    <p class="subtitulo-paso">Paso 1 de 4</p>
                </div>

                <form class="formulario-reporte" id="formularioReporte">
                    <div class="seccion-formulario">
                        <h4 class="titulo-seccion-form">Información de la Mascota</h4>
                        
                        <div class="campo-foto">
                            <div class="contenedor-foto">
                                <img src="imagenes/perro-ejemplo.jpg" alt="Foto mascota" class="preview-foto" id="previewFoto">
                                <button type="button" class="boton-cambiar-foto" onclick="document.getElementById('inputFoto').click()">📷</button>
                                <input type="file" id="inputFoto" class="input-file" accept="image/*">
                            </div>
                            <p class="etiqueta-foto">Foto reciente de tu mascota *</p>
                        </div>

                        <div class="grupo-campos">
                            <div class="grupo-input">
                                <label class="etiqueta-input requerido">Nombre de la mascota</label>
                                <input type="text" class="input-form" placeholder="Ej: Luna" name="nombre_mascota" required>
                            </div>
                            <div class="grupo-input">
                                <label class="etiqueta-input requerido">Tipo de animal</label>
                                <select class="select-form" name="tipo_animal" required>
                                    <option value="">Seleccionar</option>
                                    <option value="perro">Perro</option>
                                    <option value="gato">Gato</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <div class="grupo-campos">
                            <div class="grupo-input">
                                <label class="etiqueta-input">Raza</label>
                                <input type="text" class="input-form" placeholder="Ej: Golden Retriever" name="raza">
                            </div>
                            <div class="grupo-input">
                                <label class="etiqueta-input requerido">Color principal</label>
                                <input type="text" class="input-form" placeholder="Ej: Dorado" name="color" required>
                            </div>
                        </div>

                        <div class="grupo-campos">
                            <div class="grupo-input">
                                <label class="etiqueta-input requerido">Tamaño</label>
                                <select class="select-form" name="tamaño" required>
                                    <option value="">Seleccionar</option>
                                    <option value="pequeño">Pequeño</option>
                                    <option value="mediano">Mediano</option>
                                    <option value="grande">Grande</option>
                                </select>
                            </div>
                            <div class="grupo-input">
                                <label class="etiqueta-input">Edad aproximada</label>
                                <input type="text" class="input-form" placeholder="Ej: 3 años" name="edad">
                            </div>
                            <div class="grupo-input">
                                <label class="etiqueta-input">Sexo</label>
                                <select class="select-form" name="sexo">
                                    <option value="">Seleccionar</option>
                                    <option value="macho">Macho</option>
                                    <option value="hembra">Hembra</option>
                                </select>
                            </div>
                        </div>

                        <div class="grupo-input campo-completo">
                            <label class="etiqueta-input">Número de microchip</label>
                            <input type="text" class="input-form" placeholder="Si lo conoces" name="microchip">
                        </div>

                        <div class="grupo-input campo-completo">
                            <label class="etiqueta-input">Marcas distintivas</label>
                            <textarea class="textarea-form" placeholder="Cicatrices, manchas especiales, collar, etc." name="marcas_distintivas"></textarea>
                        </div>
                    </div>

                    <div class="botones-formulario">
                        <button type="button" class="boton-siguiente" onclick="siguientePaso(2)">Siguiente</button>
                    </div>
                </form>
            </div>

            <!-- Paso 2: Información del Incidente -->
            <div class="paso-formulario" id="paso2" style="display: none;">
                <div class="encabezado-modal-reporte">
                    <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                    <button class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                    <div class="progreso-pasos">
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso"></div>
                        <div class="paso-progreso"></div>
                    </div>
                    <p class="subtitulo-paso">Paso 2 de 4</p>
                </div>

                <div class="formulario-reporte">
                    <div class="seccion-formulario">
                        <h4 class="titulo-seccion-form">Información del Incidente</h4>
                        
                        <div class="grupo-campos">
                            <div class="grupo-input">
                                <label class="etiqueta-input requerido">Fecha en que se perdió</label>
                                <input type="date" class="input-form" name="fecha_perdida" required>
                            </div>
                            <div class="grupo-input">
                                <label class="etiqueta-input">Hora aproximada</label>
                                <input type="time" class="input-form" name="hora_perdida">
                            </div>
                        </div>

                        <div class="grupo-input campo-completo">
                            <label class="etiqueta-input requerido">Última ubicación conocida</label>
                            <div class="campo-ubicacion">
                                <input type="text" class="input-form" placeholder="📍 Dirección o punto de referencia" name="ubicacion" required>
                                <button type="button" class="boton-gps" onclick="obtenerUbicacion()">GPS</button>
                            </div>
                            <small style="color: #666; margin-top: 8px; display: block;">Obteniendo ubicación...</small>
                        </div>

                        <div class="grupo-input campo-completo">
                            <label class="etiqueta-input">Detalles del lugar</label>
                            <textarea class="textarea-form" placeholder="Describe el lugar: parque, cerca de una escuela, zona comercial, etc." name="detalles_lugar"></textarea>
                        </div>

                        <div class="grupo-input campo-completo">
                            <label class="etiqueta-input requerido">¿Cómo se perdió?</label>
                            <textarea class="textarea-form" placeholder="Describe las circunstancias: se escapó del jardín, se asustó con fuegos artificiales, se perdió durante un paseo, etc." name="como_se_perdio" required></textarea>
                        </div>
                    </div>

                    <div class="botones-formulario">
                        <button type="button" class="boton-anterior" onclick="anteriorPaso(1)">Anterior</button>
                        <button type="button" class="boton-siguiente" onclick="siguientePaso(3)">Siguiente</button>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Información de Contacto -->
            <div class="paso-formulario" id="paso3" style="display: none;">
                <div class="encabezado-modal-reporte">
                    <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                    <button class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                    <div class="progreso-pasos">
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso"></div>
                    </div>
                    <p class="subtitulo-paso">Paso 3 de 4</p>
                </div>

                <div class="formulario-reporte">
                    <div class="seccion-formulario">
                        <h4 class="titulo-seccion-form">Información de Contacto</h4>
                        
                        <div class="grupo-input campo-completo">
                            <label class="etiqueta-input requerido">Tu nombre completo</label>
                            <input type="text" class="input-form" placeholder="Nombre del propietario" name="nombre_propietario" required>
                        </div>

                        <div class="grupo-campos">
                            <div class="grupo-input">
                                <label class="etiqueta-input requerido">Teléfono principal</label>
                                <input type="tel" class="input-form" placeholder="📞 +34 123 456 789" name="telefono" required>
                            </div>
                            <div class="grupo-input">
                                <label class="etiqueta-input">Email</label>
                                <input type="email" class="input-form" placeholder="📧 tu@email.com" name="email">
                            </div>
                        </div>

                        <div class="grupo-input campo-completo">
                            <label class="etiqueta-input">Contacto de emergencia</label>
                            <input type="tel" class="input-form" placeholder="Teléfono alternativo" name="telefono_emergencia">
                        </div>

                        <div class="checkbox-recompensa">
                            <input type="checkbox" id="checkboxRecompensa" class="input-checkbox" name="ofrecer_recompensa">
                            <label for="checkboxRecompensa" class="etiqueta-checkbox">Ofrecer recompensa</label>
                        </div>
                    </div>

                    <div class="botones-formulario">
                        <button type="button" class="boton-anterior" onclick="anteriorPaso(2)">Anterior</button>
                        <button type="button" class="boton-siguiente" onclick="siguientePaso(4)">Siguiente</button>
                    </div>
                </div>
            </div>

            <!-- Paso 4: Confirmación y Envío -->
            <div class="paso-formulario" id="paso4" style="display: none;">
                <div class="encabezado-modal-reporte">
                    <h3 class="titulo-modal-reporte">Reportar Mascota Perdida</h3>
                    <button class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">×</button>
                    <div class="progreso-pasos">
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso activo"></div>
                        <div class="paso-progreso activo"></div>
                    </div>
                    <p class="subtitulo-paso">Paso 4 de 4</p>
                </div>

                <div class="formulario-reporte">
                    <div class="seccion-formulario">
                        <h4 class="titulo-seccion-form">Resumen del Reporte</h4>
                        <div id="resumenReporte">
                            <!-- El resumen se completará dinámicamente -->
                        </div>
                    </div>

                    <div class="botones-formulario">
                        <button type="button" class="boton-anterior" onclick="anteriorPaso(3)">Anterior</button>
                        <button type="submit" class="boton-siguiente" onclick="enviarReporte()">Crear Reporte</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn active" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
    <script>
        let pasoActual = 1;
        const totalPasos = 4;

        // Modal reporte
        function mostrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'flex';
            pasoActual = 1;
            mostrarPaso(1);
        }

        function cerrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'none';
            pasoActual = 1;
            mostrarPaso(1);
        }

        function mostrarPaso(numeroPaso) {
            // Ocultar todos los pasos
            for (let i = 1; i <= totalPasos; i++) {
                document.getElementById('paso' + i).style.display = 'none';
            }
            
            // Mostrar paso actual
            document.getElementById('paso' + numeroPaso).style.display = 'block';
            pasoActual = numeroPaso;
        }

        function siguientePaso(siguientePasoNum) {
            if (validarPasoActual()) {
                mostrarPaso(siguientePasoNum);
                if (siguientePasoNum === 4) {
                    generarResumen();
                }
            }
        }

        function anteriorPaso(anteriorPasoNum) {
            mostrarPaso(anteriorPasoNum);
        }

        function validarPasoActual() {
            const pasoDiv = document.getElementById('paso' + pasoActual);
            const camposRequeridos = pasoDiv.querySelectorAll('input[required], select[required], textarea[required]');
            
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
                alert('Por favor completa todos los campos requeridos.');
            }

            return esValido;
        }

        function generarResumen() {
            const form = document.getElementById('formularioReporte');
            const formData = new FormData(form);
            
            let resumen = '<div style="background: #f8f9fa; padding: 16px; border-radius: 12px;">';
            resumen += '<h5 style="margin-bottom: 12px; color: #333;">Información de la mascota:</h5>';
            resumen += '<p><strong>Nombre:</strong> ' + (formData.get('nombre_mascota') || 'No especificado') + '</p>';
            resumen += '<p><strong>Tipo:</strong> ' + (formData.get('tipo_animal') || 'No especificado') + '</p>';
            resumen += '<p><strong>Color:</strong> ' + (formData.get('color') || 'No especificado') + '</p>';
            resumen += '<p><strong>Tamaño:</strong> ' + (formData.get('tamaño') || 'No especificado') + '</p>';
            resumen += '<h5 style="margin: 16px 0 8px; color: #333;">Información del incidente:</h5>';
            resumen += '<p><strong>Fecha:</strong> ' + (formData.get('fecha_perdida') || 'No especificada') + '</p>';
            resumen += '<p><strong>Ubicación:</strong> ' + (formData.get('ubicacion') || 'No especificada') + '</p>';
            resumen += '<h5 style="margin: 16px 0 8px; color: #333;">Contacto:</h5>';
            resumen += '<p><strong>Nombre:</strong> ' + (formData.get('nombre_propietario') || 'No especificado') + '</p>';
            resumen += '<p><strong>Teléfono:</strong> ' + (formData.get('telefono') || 'No especificado') + '</p>';
            resumen += '</div>';
            
            document.getElementById('resumenReporte').innerHTML = resumen;
        }

        function enviarReporte() {
            const form = document.getElementById('formularioReporte');
            const formData = new FormData(form);
            
            // Aquí normalmente enviarías los datos al servidor
            alert('¡Reporte creado exitosamente! Te notificaremos si alguien encuentra a tu mascota.');
            cerrarFormularioReporte();
        }

        function obtenerUbicacion() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // Aquí normalmente convertirías las coordenadas a una dirección
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    document.querySelector('input[name="ubicacion"]').value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                    document.querySelector('small').textContent = 'Ubicación obtenida correctamente';
                }, function() {
                    document.querySelector('small').textContent = 'No se pudo obtener la ubicación';
                });
            }
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalReporte').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarFormularioReporte();
            }
        });

        // Preview de imagen
        document.getElementById('inputFoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>