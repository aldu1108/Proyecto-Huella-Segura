<?php
include_once('config/conexion.php');
session_start();

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
</head>
<body>
    <!-- Header -->
    <header class="header-petcare">
        <nav class="nav-principal">
            <button class="btn-menu" id="menuHamburguesa">☰</button>
            <div class="logo-container">
                <h1 class="logo">Huella Segura 🐾</h1>
            </div>
            <div class="nav-icons">
                <button class="btn-icon">🔍</button>
                <button class="btn-icon">⚡</button>
            </div>
        </nav>
        
        <!-- Menú lateral -->
        <div class="menu-lateral" id="menuLateral">
            <div class="menu-options">
                <a href="index.php" class="menu-item">🏠 Inicio</a>
                <a href="mis-mascotas.php" class="menu-item">🐕 Mis Mascotas</a>
                <a href="mascotas-perdidas.php" class="menu-item">🔍 Mascotas Perdidas</a>
                <a href="adopciones.php" class="menu-item">❤️ Adopciones</a>
                <a href="comunidad.php" class="menu-item">👥 Comunidad</a>
                <a href="veterinaria.php" class="menu-item">🏥 Veterinaria</a>
                <a href="logout.php" class="menu-item">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Alertas superiores -->
        <div class="alertas-superiores">
            <div class="alerta-emergencia">
                <h3>⚠️ Emergencia</h3>
                <p><strong><?php echo $total_perdidas; ?> mascotas perdidas</strong></p>
                <p>Tu ayuda es crucial</p>
            </div>
            

        </div>

        <!-- Sección mascotas perdidas -->
        <section class="seccion-mascotas-perdidas">
            <h2 class="titulo-seccion">Mascotas Perdidas 🔍</h2>
            <p class="subtitulo-seccion">Ayuda a reunir familias con sus mascotas</p>

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
                <br><small>+ Crear reporte de búsqueda</small>
            </button>

            <!-- Consejos rápidos -->
            <div class="consejos-rapidos">
                <h4>⚡ Actúa rápido:</h4>
                <ul>
                    <li>Las primeras 24 horas son cruciales</li>
                    <li>Comparte en redes sociales automáticamente</li>
                    <li>Alerta a la comunidad local</li>
                </ul>
            </div>

            <!-- Filtros de búsqueda -->
            <div class="contenedor-filtros">
                <input type="text" class="barra-busqueda" placeholder="Buscar por nombre, raza o ubicación...">
                
                <div class="filtros-linea">
                    <select class="filtro-select">
                        <option value="perdidas">Perdidas</option>
                        <option value="mas-recientes">Más recientes</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Reportes activos -->
        <section class="seccion-reportes-activos">
            <h3>Reportes Activos (2) <a href="#" class="enlace-ver-mapa">📍 Ver en mapa</a></h3>

            <div class="contenedor-reportes">
                <!-- Ejemplo de reporte - Buddy -->
                <div class="tarjeta-reporte">
                    <div class="etiqueta-estado">PERDIDO</div>
                    <div class="etiqueta-recompensa">Recompensa: €300</div>
                    
                    <div class="contenido-reporte">
                        <img src="imagenes/buddy.jpg" alt="Buddy" class="foto-reporte">
                        
                        <div class="info-reporte">
                            <h4>Buddy</h4>
                            <p>Perro • Labrador</p>
                            <p><strong>Color:</strong> Dorado</p>
                            <p><strong>Tamaño:</strong> Grande</p>
                            <p><strong>📅 Perdido hace:</strong> 599 días (2024-01-20)</p>
                            <p><strong>📍</strong> Parque del Retiro, Madrid</p>
                        </div>
                    </div>
                    
                    <div class="acciones-reporte">
                        <button class="boton-contactar">
                            📞 Contactar
                        </button>
                        <button class="boton-compartir-reporte">📤</button>
                        <button class="boton-ver-detalles">👁</button>
                    </div>
                    
                    <div class="mensaje-ayuda">
                        ¿Has visto a Buddy? Tu ayuda puede ser crucial para reunir a esta familia.
                    </div>
                </div>

                <!-- Ejemplo de reporte - Mimi -->
                <div class="tarjeta-reporte">
                    <div class="etiqueta-estado">PERDIDO</div>
                    
                    <div class="contenido-reporte">
                        <div class="foto-placeholder"></div>
                        
                        <div class="info-reporte">
                            <h4>Mimi</h4>
                            <p>Gato • Siamés</p>
                            <p><strong>Color:</strong> Crema y marrón</p>
                            <p><strong>Tamaño:</strong> Pequeño</p>
                            <p><strong>📅 Perdido hace:</strong> 601 días (2024-01-18)</p>
                            <p><strong>📍</strong> Calle Gran Vía, 45</p>
                        </div>
                    </div>
                    
                    <div class="acciones-reporte">
                        <button class="boton-contactar">
                            📞 Contactar
                        </button>
                        <button class="boton-compartir-reporte">📤</button>
                        <button class="boton-ver-detalles">👁</button>
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
                </ul>
            </div>
        </section>
    </main>

    <!-- Modal para reportar mascota perdida -->
    <div class="modal-reporte" id="modalReporte" style="display: none;">
        <div class="contenido-modal">
            <div class="encabezado-modal">
                <h3>Reportar Mascota Perdida</h3>
                <button class="boton-cerrar-modal" onclick="cerrarFormularioReporte()">✕</button>
            </div>
            <form method="POST" action="procesar-reporte.php" enctype="multipart/form-data">
                <div class="grupo-input">
                    <label>Selecciona tu mascota</label>
                    <select name="id_mascota" required>
                        <option value="">Seleccionar mascota</option>
                        <!-- Aquí cargarías las mascotas del usuario -->
                    </select>
                </div>
                
                <div class="grupo-input">
                    <label>Última ubicación vista</label>
                    <input type="text" name="ultima_ubicacion" placeholder="Ej: Parque del Retiro, Madrid" required>
                </div>
                
                <div class="grupo-input">
                    <label>Fecha cuando se perdió</label>
                    <input type="date" name="fecha_perdida" required>
                </div>
                
                <div class="grupo-input">
                    <label>Recompensa (opcional)</label>
                    <input type="number" name="recompensa" placeholder="0" min="0">
                </div>
                
                <div class="grupo-input">
                    <label>Descripción adicional</label>
                    <textarea name="descripcion" rows="4" placeholder="Cualquier información adicional que pueda ayudar..."></textarea>
                </div>
                
                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarFormularioReporte()">Cancelar</button>
                    <button type="submit" class="boton-guardar">Crear Reporte</button>
                </div>
            </form>
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
        // Modal reporte
        function mostrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'flex';
        }

        function cerrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalReporte').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarFormularioReporte();
            }
        });
    </script>
</body>
</html>