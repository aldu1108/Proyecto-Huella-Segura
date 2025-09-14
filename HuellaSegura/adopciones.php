<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener publicaciones de adopción
$consulta_adopciones = "SELECT p.*, pa.*, m.*, u.nombre_usuario 
                        FROM publicaciones p 
                        JOIN publicacion_adopcion pa ON p.id_anuncio = pa.id_publicacion
                        JOIN mascotas m ON p.id_mascota = m.id_mascota
                        JOIN usuarios u ON p.id_usuario = u.id_usuario
                        WHERE p.estado = 'activo' 
                        ORDER BY p.fecha DESC";
$resultado_adopciones = $conexion->query($consulta_adopciones);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopciones - PetCare</title>
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
        <!-- Header de adopciones -->
        <section class="adopciones-header">
            <h2 class="adopciones-title">Adopción de Mascotas ❤️</h2>
            <p class="adopciones-subtitle">Dale una segunda oportunidad a una mascota</p>
        </section>

        <!-- Sección de adopción -->
        <section class="adopcion-section">
            <div class="section-header">
                <h3>❤️ Adopción</h3>
                <a href="#" class="ver-todas">Ver todas</a>
            </div>

            <!-- Filtros -->
            <div class="filtros-adopcion">
                <button class="filter-btn active">❤️ Todos</button>
                <button class="filter-btn">🐕 Perros</button>
                <button class="filter-btn">🐱 Gatos</button>
                <button class="filter-btn">⚙️ Otros</button>
            </div>

            <!-- Estadísticas -->
            <div class="stats-adopcion">
                <div class="stat-item">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Disponibles</div>
                </div>
                <div class="stat-item urgente">
                    <div class="stat-number">1</div>
                    <div class="stat-label">Urgentes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">15</div>
                    <div class="stat-label">Adoptados</div>
                </div>
            </div>

            <!-- Mascotas en adopción -->
            <div class="adopcion-list">
                <!-- Carlos - Mestizo -->
                <div class="adopcion-card">
                    <img src="imagenes/carlos.jpg" alt="Carlos" class="adopcion-photo">
                    <div class="adopcion-info">
                        <div class="adopcion-header">
                            <h4>Carlos</h4>
                            <span class="adopcion-badge">ADOPCIÓN</span>
                        </div>
                        <p>Mestizo • 2 años • ♂</p>
                        <p>Carlos es un perro muy cariñoso y juguetón. Le encanta pasear y es perfecto para familias activas.</p>
                        <div class="adopcion-meta">
                            <p>📍 Madrid Centro • 📅 Hace 5 días • 🏠 Refugio Esperanza</p>
                        </div>
                        <div class="adopcion-status">
                            <span class="status-badge vacunado">✅ Vacunado</span>
                        </div>
                        <button class="btn-interesa">❤️ Me interesa adoptar</button>
                    </div>
                </div>
            </div>

            <!-- Llamada a la acción -->
            <div class="cta-adopcion">
                <h4>🧡 ¡Cada acción cuenta!</h4>
                <p>Dale una segunda oportunidad a una mascota. La adopción responsable cambia vidas.</p>
                <div class="cta-buttons">
                    <button class="btn-cta">Ver Perdidas</button>
                    <button class="btn-cta primary">Adoptar Ahora</button>
                </div>
            </div>

            <!-- Sección ¿Quieres ayudar más? -->
            <div class="ayudar-mas">
                <h4>💙 ¿Quieres ayudar más?</h4>
                <p>También puedes ser casa de acogida temporal, hacer donaciones o ser voluntario.</p>
                <div class="ayudar-options">
                    <button class="option-btn">Casa de Acogida</button>
                    <button class="option-btn">Donar/Voluntario</button>
                </div>
            </div>
        </section>
    </main>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn active" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
</body>
</html>