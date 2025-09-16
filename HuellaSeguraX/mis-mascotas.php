<?php
include_once('config/conexion.php');
session_start();

// Verificar si hay sesión activa

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Obtener mascotas del usuario
$consulta_base = "SELECT * FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
if (!empty($busqueda)) {
    $consulta_base .= " AND nombre_mascota LIKE '%$busqueda%'";
}
$consulta_mascotas = $consulta_base . " ORDER BY nombre_mascota ASC";
$resultado_mascotas = $conexion->query($consulta_mascotas);

// Contar total de mascotas
$consulta_total = "SELECT COUNT(*) as total FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
$resultado_total = $conexion->query($consulta_total);
$total_mascotas = $resultado_total->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Mascotas - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php');?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Barra de búsqueda -->
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Buscar mascotas, veterinarios, recordatorios...">
            <button class="filter-btn">🔽</button>
        </div>

        <!-- Sección Mis Mascotas -->
        <section class="mis-mascotas-section">
            <div class="section-header">
                <h2>Mis Mascotas</h2>
                <button class="btn-add">+ Agregar</button>
            </div>

            <div class="mascotas-grid">
                <div class="mascota-card">
                    <img src="imagenes/perro.jpg" alt="Max" class="mascota-photo">
                    <div class="mascota-info">
                        <h3>Max</h3>
                        <p>Golden Retriever</p>
                        <p>3 años</p>
                    </div>
                </div>

                <div class="mascota-card">
                    <img src="imagenes/perro.jpg" alt="Luna" class="mascota-photo">
                    <div class="mascota-info">
                        <h3>Luna</h3>
                        <p>Gato Persa</p>
                        <p>2 años</p>
                    </div>
                </div>
            </div>

            <!-- Sugerencia adopción -->
            <div class="adopcion-banner">
                <h4>🐾 ¿Buscas una nueva mascota?</h4>
                <p>Hay mascotas esperando un hogar. La adopción es amor puro.</p>
                <button class="btn-adopcion">❤️ Ver Mascotas en Adopción</button>
            </div>
        </section>

        <!-- Calendario de Cuidados -->
        <section class="calendario-section">
            <div class="calendario-header">
                <div class="calendario-title">
                    <h3>📅 Calendario de Cuidados</h3>
                    <p>5 eventos programados</p>
                </div>
                <div class="calendario-nav">
                    <button class="nav-arrow">◀</button>
                    <span class="mes-actual">septiembre de 2025</span>
                    <button class="nav-arrow">▶</button>
                </div>
            </div>

            <!-- Mini calendario -->
            <div class="mini-calendario">
                <div class="calendario-month">
                    <span class="month-label">Septiembre 2025</span>
                </div>
                <div class="calendar-grid">
                    <div class="day-header">Su</div>
                    <div class="day-header">Mo</div>
                    <div class="day-header">Tu</div>
                    <div class="day-header">We</div>
                    <div class="day-header">Th</div>
                    <div class="day-header">Fr</div>
                    <div class="day-header">Sa</div>
                    
                    <div class="day">31</div>
                    <div class="day">1</div>
                    <div class="day">2</div>
                    <div class="day">3</div>
                    <div class="day">4</div>
                    <div class="day">5</div>
                    <div class="day">6</div>
                    <div class="day">7</div>
                    <div class="day today">8</div>
                    <div class="day event">9</div>
                    <div class="day">10</div>
                    <div class="day">11</div>
                    <div class="day">12</div>
                    <div class="day">13</div>
                    <div class="day">14</div>
                    <div class="day event">15</div>
                    <div class="day">16</div>
                    <div class="day">17</div>
                    <div class="day">18</div>
                    <div class="day">19</div>
                    <div class="day">20</div>
                    <div class="day">21</div>
                    <div class="day">22</div>
                    <div class="day">23</div>
                    <div class="day">24</div>
                    <div class="day">25</div>
                    <div class="day">26</div>
                    <div class="day">27</div>
                </div>
            </div>

            <!-- Eventos de hoy -->
            <div class="eventos-hoy">
                <div class="eventos-header">
                    <h4>📍 Hoy</h4>
                    <span class="evento-count">2</span>
                </div>

                <div class="evento-item urgente">
                    <div class="evento-icon">💉</div>
                    <div class="evento-details">
                        <span class="evento-title">Vacuna anual</span>
                        <div class="evento-meta">
                            <span class="evento-pet">Max • 14:00</span>
                            <span class="evento-desc">Vacuna anual completa</span>
                        </div>
                    </div>
                    <span class="evento-status urgente">Urgente</span>
                </div>

                <div class="evento-item medio">
                    <div class="evento-icon">💊</div>
                    <div class="evento-details">
                        <span class="evento-title">Medicina para alergias</span>
                        <div class="evento-meta">
                            <span class="evento-pet">Luna • 18:30</span>
                            <span class="evento-desc">Administrar antihistamínico</span>
                        </div>
                    </div>
                    <span class="evento-status medio">Medio</span>
                </div>
            </div>
        </section>
    </main>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn">❤️</button>
        <button class="nav-btn">🔍</button>
        <button class="nav-btn">🏠</button>
        <button class="nav-btn">👥</button>
        <button class="nav-btn">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
</body>
</html>