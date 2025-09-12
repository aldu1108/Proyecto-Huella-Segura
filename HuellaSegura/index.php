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

// Obtener mascotas del usuario
$consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo' LIMIT 2";
$resultado_mascotas = $conexion->query($consulta_mascotas);

// Obtener eventos/citas próximas
$fecha_hoy = date('Y-m-d');
$consulta_eventos = "SELECT * FROM eventos WHERE id_usuario = $usuario_id AND fecha >= '$fecha_hoy' ORDER BY fecha ASC LIMIT 5";
$resultado_eventos = $conexion->query($consulta_eventos);

// Obtener citas veterinarias próximas
$consulta_citas = "SELECT c.*, m.nombre_mascota FROM citas_veterinarias c 
                   JOIN mascotas m ON c.id_mascota = m.id_mascota 
                   WHERE m.id_usuario = $usuario_id AND c.fecha >= '$fecha_hoy' 
                   ORDER BY c.fecha ASC LIMIT 3";
$resultado_citas = $conexion->query($consulta_citas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - PetCare</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <!-- Header -->
    <header class="header-petcare">
        <nav class="nav-principal">
            <button class="btn-menu" id="menuHamburguesa">☰</button>
            <div class="logo-container">
                <h1 class="logo">PetCare 🐾</h1>
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
        <!-- Próximos eventos esta semana -->
        <section class="eventos-semana">
            <h2>Próximos eventos esta semana</h2>
            <div class="eventos-cards">
                <div class="evento-card">
                    <div class="evento-icon">🐕</div>
                    <div class="evento-info">
                        <h4>Max</h4>
                        <p>Mañana</p>
                    </div>
                </div>
                <div class="evento-card">
                    <div class="evento-icon">🌙</div>
                    <div class="evento-info">
                        <h4>Luna</h4>
                        <p>mié, 10 sept</p>
                    </div>
                </div>
                <div class="evento-card">
                    <div class="evento-icon">🐕</div>
                    <div class="evento-info">
                        <h4>Max</h4>
                        <p>lun, 15 sept</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recordatorios Urgentes -->
        <section class="recordatorios-urgentes">
            <div class="section-header">
                <h3>🔔 Recordatorios Urgentes</h3>
                <span class="count">2 para hoy</span>
            </div>
            
            <div class="urgente-list">
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
            </div>
            
            <div class="proximamente">
                <h4>📅 Próximamente</h4>
                <div class="proximo-item">
                    <span class="proximo-info">🌅 Mañana • Max • Cita veterinario</span>
                    <span class="proximo-time">10:00</span>
                </div>
            </div>
            
            <a href="#" class="ver-todos">Ver todos los recordatorios →</a>
        </section>

        <!-- Mascotas Perdidas -->
        <section class="mascotas-perdidas">
            <div class="section-header">
                <h3>🔍 Mascotas Perdidas</h3>
                <a href="mascotas-perdidas.php" class="ver-todas">Ver todas</a>
            </div>
            
            <button class="btn-reporte-perdida">
                ⚠️ ¡Reportar Mascota Perdida!
            </button>
            
            <div class="perdidas-list">
                <div class="perdida-item">
                    <img src="imagenes/buddy.jpg" alt="Buddy" class="perdida-photo">
                    <div class="perdida-info">
                        <h4>Buddy</h4>
                        <p>Perro Labrador</p>
                        <p>📍 Parque del Retiro • Hace 3 días</p>
                    </div>
                    <span class="perdida-status">PERDIDO</span>
                </div>
                
                <div class="perdida-item">
                    <div class="perdida-placeholder"></div>
                    <div class="perdida-info">
                        <h4>Mimi</h4>
                        <p>Gato Siamés</p>
                        <p>📍 Gran Vía • Hace 5 días</p>
                    </div>
                    <span class="perdida-status">PERDIDO</span>
                </div>
            </div>
            
            <div class="perdidas-help">
                <h4>❓ ¿Has visto alguna mascota perdida?</h4>
                <p>Tu ayuda puede ser crucial para reunir a una familia con su mascota.</p>
                <a href="#" class="btn-help">Ver Todas las Mascotas Perdidas</a>
            </div>
        </section>
    </main>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn">❤️</button>
        <button class="nav-btn">🔍</button>
        <button class="nav-btn active">🏠</button>
        <button class="nav-btn">👥</button>
        <button class="nav-btn">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
</body>
</html>