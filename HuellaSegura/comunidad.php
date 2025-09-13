<?php
include_once('config/conexion.php');
session_start();
include_once('includes/menu_hamburguesa.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener estadísticas de la comunidad
$consulta_miembros = "SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'";
$resultado_miembros = $conexion->query($consulta_miembros);
$total_miembros = $resultado_miembros->fetch_assoc()['total'];

$consulta_mascotas_comunidad = "SELECT COUNT(*) as total FROM mascotas WHERE estado = 'activo'";
$resultado_mascotas_comunidad = $conexion->query($consulta_mascotas_comunidad);
$total_mascotas_comunidad = $resultado_mascotas_comunidad->fetch_assoc()['total'];

// Obtener posts de la comunidad
$fecha_hoy = date('Y-m-d');
$consulta_posts = "SELECT p.*, u.nombre_usuario, u.foto_usuario 
                   FROM post_comunidad p 
                   JOIN usuarios u ON p.id_usuario = u.id_usuario 
                   ORDER BY p.fecha DESC LIMIT 10";
$resultado_posts = $conexion->query($consulta_posts);

// Obtener eventos próximos
$consulta_eventos = "SELECT * FROM eventos WHERE fecha >= '$fecha_hoy' ORDER BY fecha ASC LIMIT 5";
$resultado_eventos = $conexion->query($consulta_eventos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidad - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>    
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <div class="contenedor-principal">
        <!-- Header de comunidad -->
        <section class="header-comunidad">
            <h2 class="titulo-comunidad">Comunidad PetCare ☀️</h2>
            <p class="subtitulo-comunidad">Conecta con otros amantes de las mascotas</p>

            <!-- Estadísticas de la comunidad -->
            <div class="estadisticas-comunidad">
                <div class="estadistica-item">
                    <span class="numero-estadistica"><?php echo $total_miembros; ?></span>
                    <span>Miembros</span>
                </div>
                <div class="estadistica-item">
                    <span class="numero-estadistica"><?php echo $total_mascotas_comunidad; ?></span>
                    <span>Mascotas</span>
                </div>
                <div class="estadistica-item">
                    <span class="numero-estadistica">23</span>
                    <span>Posts hoy</span>
                </div>
                <div class="estadistica-item">
                    <span class="numero-estadistica">7</span>
                    <span>Ayudas</span>
                </div>
            </div>
        </section>

        <!-- Navegación por secciones -->
        <nav class="navegacion-comunidad">
            <button class="boton-seccion" data-seccion="feed">📰 Feed</button>
            <button class="boton-seccion activo" data-seccion="eventos">📅 Eventos</button>
            <button class="boton-seccion" data-seccion="grupos">👥 Grupos</button>
        </nav>

        <!-- Sección Feed -->
        <section class="seccion-feed" id="seccionFeed" style="display: none;">
            <!-- Formulario para nueva publicación -->
            <div class="formulario-publicacion">
                <form method="POST" action="crear-post.php">
                    <textarea name="contenido" placeholder="¿Qué quieres compartir con la comunidad?" rows="3"></textarea>
                    <div class="opciones-publicacion">
                        <button type="button" class="boton-opcion">📸 Foto</button>
                        <button type="button" class="boton-opcion">🏆 Logro</button>
                        <button type="button" class="boton-opcion">❓ Ayuda</button>
                        <button type="button" class="boton-opcion">📅 Evento</button>
                        <button type="submit" class="boton-publicar">Publicar</button>
                    </div>
                </form>
            </div>

            <!-- Posts de la comunidad -->
            <div class="contenedor-posts">
                <div class="post-comunidad">
                    <div class="encabezado-post">
                        <img src="imagenes/usuario-default.jpg" alt="María García" class="foto-usuario-post">
                        <div class="info-usuario-post">
                            <h4>María García</h4>
                            <p>Hace 2 horas</p>
                        </div>
                        <span class="etiqueta-logro">Logro</span>
                    </div>
                    <div class="contenido-post">
                        <p>¡Luna acaba de pasar su primer examen veterinario con excelentes resultados! 🎉 Gracias al Dr. Martínez por el cuidado excepcional.</p>
                        <img src="imagenes/luna-examen.jpg" alt="Luna en el veterinario" class="imagen-post">
                        <div class="etiqueta-mascota">🐾 Luna</div>
                    </div>
                    <div class="acciones-post">
                        <button class="boton-accion">❤️ 24</button>
                        <button class="boton-accion">💬 5</button>
                        <button class="boton-accion">📤 Compartir</button>
                    </div>
                </div>

                <div class="post-comunidad">
                    <div class="encabezado-post">
                        <img src="imagenes/usuario-default.jpg" alt="Carlos Ruiz" class="foto-usuario-post">
                        <div class="info-usuario-post">
                            <h4>Carlos Ruiz 👨‍⚕️</h4>
                            <p>Hace 4 horas</p>
                        </div>
                        <span class="etiqueta-ayuda">Ayuda</span>
                    </div>
                    <div class="contenido-post">
                        <p>¿Alguien sabe de un buen veterinario especialista en gatos en la zona de Salamanca? Mi gatito necesita una revisión especializada.</p>
                        <div class="ubicacion-post">📍 Madrid, Salamanca</div>
                    </div>
                    <div class="acciones-post">
                        <button class="boton-accion">❤️ 12</button>
                        <button class="boton-accion">💬 8</button>
                        <button class="boton-accion">📤 Compartir</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Eventos -->
        <section class="seccion-eventos" id="seccionEventos">
            <div class="encabezado-eventos">
                <h3>Próximos Eventos</h3>
                <button class="boton-crear-evento" onclick="mostrarFormularioEvento()">Crear Evento</button>
            </div>

            <div class="contenedor-eventos">
                <div class="tarjeta-evento">
                    <div class="fecha-evento">
                        <span class="dia">15</span>
                        <span class="mes">Feb</span>
                    </div>
                    <div class="info-evento">
                        <h4>Adopción Solidaria</h4>
                        <p class="detalles-evento">
                            🕐 10:00 📍 Parque del Retiro 👥 45 asistirán
                        </p>
                        <button class="boton-unirse-evento">Unirse al Evento</button>
                    </div>
                </div>

                <div class="tarjeta-evento">
                    <div class="fecha-evento">
                        <span class="dia">18</span>
                        <span class="mes">Feb</span>
                    </div>
                    <div class="info-evento">
                        <h4>Taller de Primeros Auxilios</h4>
                        <p class="detalles-evento">
                            🕐 16:00 📍 Centro Veterinario 👥 12 asistirán
                        </p>
                        <button class="boton-unirse-evento">Unirse al Evento</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Grupos -->
        <section class="seccion-grupos" id="seccionGrupos" style="display: none;">
            <div class="encabezado-grupos">
                <h3>Grupos de la Comunidad</h3>
                <button class="boton-crear-grupo">Crear Grupo</button>
            </div>

            <div class="contenedor-grupos">
                <div class="tarjeta-grupo">
                    <div class="portada-grupo"></div>
                    <h4>Dueños de Golden Retrievers</h4>
                    <p>245 miembros • 15 posts esta semana</p>
                    <button class="boton-unirse-grupo">Unirse</button>
                </div>

                <div class="tarjeta-grupo">
                    <div class="portada-grupo"></div>
                    <h4>Veterinarios de Madrid</h4>
                    <p>89 miembros • 8 posts esta semana</p>
                    <button class="boton-unirse-grupo">Unirse</button>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal para crear evento -->
    <div class="modal-evento" id="modalEvento" style="display: none;">
        <div class="contenido-modal">
            <div class="encabezado-modal">
                <h3>Crear Nuevo Evento</h3>
                <button class="boton-cerrar-modal" onclick="cerrarFormularioEvento()">✕</button>
            </div>
            <form method="POST" action="crear-evento.php">
                <div class="grupo-input">
                    <label>Título del evento</label>
                    <input type="text" name="titulo" required>
                </div>
                
                <div class="grupo-input">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="4" required></textarea>
                </div>
                
                <div class="fila-inputs">
                    <div class="grupo-input">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required>
                    </div>
                    
                    <div class="grupo-input">
                        <label>Hora</label>
                        <input type="time" name="hora" required>
                    </div>
                </div>
                
                <div class="grupo-input">
                    <label>Ubicación</label>
                    <input type="text" name="ubicacion" required>
                </div>
                
                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarFormularioEvento()">Cancelar</button>
                    <button type="submit" class="boton-guardar">Crear Evento</button>
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

    <style>
        /* Estilos específicos para comunidad */
        .header-comunidad {
            text-align: center;
            margin-bottom: 2rem;
        }

        .titulo-comunidad {
            color: #d35400;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .subtitulo-comunidad {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .estadisticas-comunidad {
            display: flex;
            justify-content: space-around;
            background-color: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .navegacion-comunidad {
            display: flex;
            background-color: white;
            border-radius: 10px;
            padding: 0.5rem;
            margin: 2rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .boton-seccion {
            flex: 1;
            padding: 1rem;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .boton-seccion.activo {
            background-color: #d35400;
            color: white;
        }

        /* Estilos para Feed */
        .formulario-publicacion {
            background-color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .formulario-publicacion textarea {
            width: 100%;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            padding: 1rem;
            resize: vertical;
            font-family: inherit;
        }

        .opciones-publicacion {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }

        .boton-opcion {
            background: none;
            border: 2px solid #e8e8e8;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .boton-publicar {
            background-color: #d35400;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 20px;
            cursor: pointer;
        }

        .post-comunidad {
            background-color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .encabezado-post {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .foto-usuario-post {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
        }

        .info-usuario-post h4 {
            margin: 0;
            color: #333;
        }

        .info-usuario-post p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .etiqueta-logro {
            background-color: #f1c40f;
            color: #333;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-left: auto;
        }

        .etiqueta-ayuda {
            background-color: #e74c3c;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-left: auto;
        }

        .imagen-post {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin: 1rem 0;
        }

        .acciones-post {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .boton-accion {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 20px;
            transition: background-color 0.3s;
        }

        .boton-accion:hover {
            background-color: #f8f9fa;
        }

        /* Estilos para Eventos */
        .encabezado-eventos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .boton-crear-evento {
            background-color: #d35400;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .tarjeta-evento {
            background-color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .fecha-evento {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            min-width: 80px;
        }

        .fecha-evento .dia {
            display: block;
            font-size: 1.5rem;
            font-weight: bold;
            color: #d35400;
        }

        .fecha-evento .mes {
            display: block;
            font-size: 0.9rem;
            color: #666;
        }

        .info-evento {
            flex: 1;
        }

        .info-evento h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .detalles-evento {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .boton-unirse-evento {
            background-color: #d35400;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        /* Estilos para Grupos */
        .encabezado-grupos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .boton-crear-grupo {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .contenedor-grupos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .tarjeta-grupo {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .portada-grupo {
            height: 120px;
            background: linear-gradient(135deg, #d35400, #f39c12);
        }

        .tarjeta-grupo h4 {
            padding: 1rem 1rem 0.5rem;
            color: #333;
            margin: 0;
        }

        .tarjeta-grupo p {
            padding: 0 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .boton-unirse-grupo {
            width: 100%;
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 1rem;
            cursor: pointer;
            font-size: 1rem;
        }

        .etiqueta-mascota {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            display: inline-block;
            margin-top: 0.5rem;
        }

        .ubicacion-post {
            color: #666;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        /* Modal */
        .modal-evento {
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

        .modal-evento .contenido-modal {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-evento .grupo-input {
            margin-bottom: 1rem;
        }

        .modal-evento .grupo-input label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }

        .modal-evento .grupo-input input, 
        .modal-evento .grupo-input textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 1rem;
        }

        .fila-inputs {
            display: flex;
            gap: 1rem;
        }

        .fila-inputs .grupo-input {
            flex: 1;
        }

        @media (max-width: 768px) {
            .estadisticas-comunidad {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .navegacion-comunidad {
                flex-direction: column;
            }
            
            .tarjeta-evento {
                flex-direction: column;
                text-align: center;
            }
            
            .opciones-publicacion {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }
    </style>
    <script src="js/scripts.js"></script>
</body>
</html>