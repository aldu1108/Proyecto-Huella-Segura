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
    <!-- Header -->
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Header de comunidad -->
        <section class="comunidad-header">
            <h2 class="comunidad-title">Comunidad PetCare ☀️</h2>
            <p class="comunidad-subtitle">Conecta con otros amantes de las mascotas</p>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">1247</div>
                    <div class="stat-label">Miembros</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">856</div>
                    <div class="stat-label">Mascotas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">23</div>
                    <div class="stat-label">Posts hoy</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">7</div>
                    <div class="stat-label">Ayudas</div>

                </div>
            </div>
        </section>


        <!-- Navegación de secciones -->
        <nav class="section-nav">
            <button class="section-btn active" data-section="feed">📰 Feed</button>
            <button class="section-btn" data-section="eventos">📅 Eventos</button>
            <button class="section-btn" data-section="grupos">👥 Grupos</button>
        </nav>

        <!-- Sección Feed -->
        <section class="feed-section" id="feedSection">
            <!-- Crear post -->
            <div class="create-post">
                <textarea placeholder="¿Qué quieres compartir con la comunidad?"></textarea>
            </div>

            <!-- Posts -->
            <div class="posts-container">
                <div class="post-card">
                    <div class="post-header">
                        <div class="user-avatar"></div>
                        <div class="user-info">
                            <h4>María García 👩‍⚕️ ❤️</h4>
                            <p>Hace 2 horas</p>
                        </div>
                        <span class="achievement-badge">Logro</span>
                    </div>
                    <div class="post-content">
                        <p>¡Luna acaba de pasar su primer examen veterinario con excelentes resultados! 🎉 Gracias al Dr. Martínez por el cuidado excepcional.</p>
                        <img src="imagenes/luna-examen.jpg" alt="Luna" class="post-image">
                        <div class="post-tag">🐾 Luna</div>
                    </div>
                    <div class="post-actions">
                        <button class="action-btn">❤️ 24</button>
                        <button class="action-btn">💬 5</button>
                        <button class="action-btn">📤 Compartir</button>
                    </div>
                </div>

                <div class="post-card">
                    <div class="post-header">
                        <div class="user-avatar"></div>
                        <div class="user-info">
                            <h4>Carlos Ruiz 👨‍⚕️</h4>
                            <p>Hace 4 horas</p>
                        </div>
                        <span class="help-badge">Ayuda</span>
                    </div>
                    <div class="post-content">
                        <p>¿Alguien sabe de un buen veterinario especialista en gatos en la zona de Salamanca? Mi gatito necesita una revisión especializada.</p>
                        <div class="location-tag">📍 Madrid, Salamanca</div>
                    </div>
                    <div class="post-actions">
                        <button class="action-btn">❤️ 12</button>
                        <button class="action-btn">💬 8</button>
                        <button class="action-btn">📤 Compartir</button>

                    </div>
                </div>
            </div>
        </section>


        <!-- Sección Eventos (activa por defecto) -->
        <section class="eventos-section" id="eventosSection" style="display: none;">
            <div class="section-header">
                <h3>Próximos Eventos</h3>
                <button class="btn-create">Crear Evento</button>
            </div>

            <div class="eventos-list">
                <div class="evento-card">
                    <div class="evento-date">
                        <div class="date-day">15</div>
                        <div class="date-month">Feb</div>
                    </div>
                    <div class="evento-info">
                        <h4>Adopción Solidaria</h4>
                        <div class="evento-details">
                            🕐 10:00 📍 Parque del Retiro 👥 45 asistirán
                        </div>
                        <button class="btn-join">Unirse al Evento</button>
                    </div>
                </div>

                <div class="evento-card">
                    <div class="evento-date">
                        <div class="date-day">18</div>
                        <div class="date-month">Feb</div>
                    </div>
                    <div class="evento-info">
                        <h4>Taller de Primeros Auxilios</h4>
                        <div class="evento-details">
                            🕐 16:00 📍 Centro Veterinario 👥 12 asistirán
                        </div>
                        <button class="btn-join">Unirse al Evento</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Grupos -->
        <section class="grupos-section" id="gruposSection" style="display: none;">
            <div class="section-header">
                <h3>Grupos Populares</h3>
                <button class="btn-create">Crear Grupo</button>
            </div>

            <div class="grupos-list">
                <div class="grupo-card">
                    <div class="grupo-icon">🐕</div>
                    <div class="grupo-info">
                        <h4>Dueños de Golden Retriever</h4>
                        <p>234 miembros</p>
                    </div>
                    <button class="btn-join">Unirse</button>
                </div>

                <div class="grupo-card">
                    <div class="grupo-icon">🐱</div>
                    <div class="grupo-info">
                        <h4>Gatos de Madrid</h4>
                        <p>189 miembros</p>
                    </div>
                    <button class="btn-join">Unirse</button>
                </div>

                <div class="grupo-card">
                    <div class="grupo-icon">🏥</div>
                    <div class="grupo-info">
                        <h4>Primeros Auxilios Pet</h4>
                        <p>156 miembros</p>
                    </div>
                    <button class="btn-join">Unirse</button>
                </div>

                <div class="grupo-card">
                    <div class="grupo-icon">❤️</div>
                    <div class="grupo-info">
                        <h4>Adopción Responsable</h4>
                        <p>203 miembros</p>
                    </div>
                    <button class="btn-join">Unirse</button>
                </div>
            </div>
        </section>
    </main>

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
    <script>
        // Navegación entre secciones
        document.querySelectorAll('.section-btn').forEach(button => {
            button.addEventListener('click', function() {
                const section = this.dataset.section;
                
                // Remover clase activa
                document.querySelectorAll('.section-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Ocultar todas las secciones
                document.querySelectorAll('.feed-section, .eventos-section, .grupos-section').forEach(sec => sec.style.display = 'none');
                
                // Mostrar sección seleccionada
                document.getElementById(section + 'Section').style.display = 'block';
            });
        });
    </script>
</body>
</html>