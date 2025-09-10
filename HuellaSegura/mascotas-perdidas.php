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
$total_perdidas = $resultado_perdidas->fetch_assoc()['total'];

// Para el ejemplo, definiremos algunas estadísticas estáticas
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
    <title>Mascotas Perdidas - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header class="cabecera-principal">
        <nav class="navegacion-principal">
            <button class="boton-menu-hamburguesa" id="menuHamburguesa">☰</button>
            <div class="logo-contenedor">
                <h1 class="logo-texto">PetCare</h1>
            </div>
            <div class="iconos-derecha">
                <button class="boton-buscar">🔍</button>
                <button class="boton-compartir">⚡</button>
            </div>
        </nav>
        
        <div class="menu-lateral" id="menuLateral">
            <div class="opciones-menu">
                <a href="index.php" class="opcion-menu">🏠 Inicio</a>
                <a href="mis-mascotas.php" class="opcion-menu">🐕 Mis Mascotas</a>
                <a href="mascotas-perdidas.php" class="opcion-menu">🔍 Mascotas Perdidas</a>
                <a href="adopciones.php" class="opcion-menu">❤️ Adopciones</a>
                <a href="comunidad.php" class="opcion-menu">👥 Comunidad</a>
                <a href="veterinaria.php" class="opcion-menu">🏥 Veterinaria</a>
                <a href="logout.php" class="opcion-menu">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="contenedor-principal">
        <!-- Alertas de emergencia -->
        <div class="alertas-contenedor">
            <div class="alerta-emergencia">
                <h3>⚠️ Emergencia</h3>
                <p><strong><?php echo $total_perdidas; ?> mascotas perdidas</strong></p>
                <p>Tu ayuda es crucial</p>
            </div>
            
            <div class="alerta-adopcion">
                <h3>❤️ Adopción</h3>
                <p><strong>3 mascotas disponibles</strong></p>
                <p>1 adopción urgente</p>
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
                <ul style="margin-left: 2rem; color: #666;">
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
                        <option value="todas">Todas</option>
                        <option value="perdidas">Perdidas</option>
                        <option value="encontradas">Encontradas</option>
                    </select>
                    
                    <select class="filtro-select">
                        <option value="recientes">Más recientes</option>
                        <option value="ubicacion">Por ubicación</option>
                        <option value="recompensa">Con recompensa</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Reportes activos -->
        <section class="seccion-reportes-activos">
            <h3>Reportes Activos (<?php echo $resultado_reportes ? $resultado_reportes->num_rows : 0; ?>)</h3>
            <a href="#" class="enlace-ver-mapa">📍 Ver en mapa</a>

            <div class="contenedor-reportes">
                <?php if ($resultado_reportes && $resultado_reportes->num_rows > 0): ?>
                    <?php while($reporte = $resultado_reportes->fetch_assoc()): ?>
                        <div class="tarjeta-reporte">
                            <div class="etiqueta-estado">PERDIDO</div>
                            <?php if ($reporte['recompensa'] > 0): ?>
                                <div class="etiqueta-recompensa">Recompensa: €<?php echo number_format($reporte['recompensa'], 0); ?></div>
                            <?php endif; ?>
                            
                            <div class="contenido-reporte">
                                <img src="imagenes/<?php echo $reporte['foto']; ?>" alt="Mascota perdida" class="foto-reporte">
                                
                                <div class="info-reporte">
                                    <h4><?php echo $reporte['nombre_mascota']; ?></h4>
                                    <p><?php echo ucfirst($reporte['tipo']); ?> • Labrador</p>
                                    <p><strong>Color:</strong> Dorado</p>
                                    <p><strong>Tamaño:</strong> Grande</p>
                                    <p><strong>📅 Perdido hace:</strong> <?php echo (time() - strtotime($reporte['fecha_perdida'])) / (60*60*24); ?> días (<?php echo date('d-m-Y', strtotime($reporte['fecha_perdida'])); ?>)</p>
                                    <p><strong>📍</strong> <?php echo $reporte['ultima_ubicacion']; ?></p>
                                </div>
                            </div>
                            
                            <div class="acciones-reporte">
                                <button class="boton-contactar" onclick="contactarDueño(<?php echo $reporte['id_usuario']; ?>)">
                                    📞 Contactar
                                </button>
                                <button class="boton-compartir-reporte">📤</button>
                                <button class="boton-ver-detalles">👁</button>
                            </div>
                            
                            <div class="mensaje-ayuda">
                                ¿Has visto a <?php echo $reporte['nombre_mascota']; ?>? Tu ayuda puede ser crucial para reunir a esta familia.
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="mensaje-sin-reportes">
                        <h4>¡Buenas noticias! 🎉</h4>
                        <p>No hay reportes de mascotas perdidas en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

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
    <nav class="navegacion-inferior">
        <button class="boton-nav-inferior" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="boton-nav-inferior" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="boton-nav-inferior" onclick="window.location.href='index.php'">🏠</button>
        <button class="boton-nav-inferior" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="boton-nav-inferior" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script>
        // Menú hamburguesa
        document.getElementById('menuHamburguesa').addEventListener('click', function() {
            const menu = document.getElementById('menuLateral');
            menu.style.display = menu.style.display === 'none' || menu.style.display === '' ? 'block' : 'none';
        });

        // Modal reporte
        function mostrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'flex';
        }

        function cerrarFormularioReporte() {
            document.getElementById('modalReporte').style.display = 'none';
        }

        function contactarDueño(usuarioId) {
            alert('Funcionalidad de contacto - Usuario ID: ' + usuarioId);
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalReporte').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarFormularioReporte();
            }
        });
    </script>

    <style>
        /* Estilos específicos para mascotas perdidas */
        .contenedor-filtros {
            margin-bottom: 2rem;
        }

        .filtros-linea {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .filtro-select {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            background-color: white;
        }

        .tarjeta-reporte {
            background-color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
        }

        .etiqueta-estado {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background-color: #e74c3c;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .etiqueta-recompensa {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: #f39c12;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .contenido-reporte {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .foto-reporte {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }

        .info-reporte {
            flex: 1;
        }

        .info-reporte h4 {
            color: #d35400;
            margin-bottom: 0.5rem;
        }

        .info-reporte p {
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }

        .acciones-reporte {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .boton-contactar {
            flex: 1;
            background-color: #d35400;
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .boton-compartir-reporte, .boton-ver-detalles {
            background-color: #f8f9fa;
            border: 2px solid #e8e8e8;
            padding: 0.8rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .mensaje-ayuda {
            background-color: #fff3cd;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .consejos-rapidos {
            background-color: #ffeaa7;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .enlace-ver-mapa {
            color: #d35400;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .seccion-reportes-activos h3 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #333;
        }

        .modal-reporte {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .modal-reporte .contenido-modal {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-reporte .grupo-input {
            margin-bottom: 1rem;
        }

        .modal-reporte .grupo-input label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }

        .modal-reporte .grupo-input input, 
        .modal-reporte .grupo-input select, 
        .modal-reporte .grupo-input textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .contenido-reporte {
                flex-direction: column;
            }
            
            .foto-reporte {
                width: 100%;
                height: 200px;
            }
            
            .acciones-reporte {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>