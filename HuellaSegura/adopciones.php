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
    <style>
        /* Incluir los estilos actualizados aquí */
        <?php include 'css/estilos-actualizados.css'; ?>
    </style>
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
            <h1 class="titulo-adopciones">Adopción de Mascotas ❤️</h1>
            <p class="subtitulo-adopciones">Dale una segunda oportunidad a una mascota</p>
        </section>

        <!-- Sección de adopción -->
        <section class="seccion-adopcion">
            <div class="section-header">
                <h3>❤️ Adopción</h3>
                <a href="#" class="ver-todas">Ver todas</a>
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
                    <div class="numero-estadistica-adopcion">3</div>
                    <div class="etiqueta-estadistica-adopcion">Disponibles</div>
                </div>
                <div class="estadistica-adopcion urgente">
                    <div class="numero-estadistica-adopcion">1</div>
                    <div class="etiqueta-estadistica-adopcion">Urgentes</div>
                </div>
                <div class="estadistica-adopcion">
                    <div class="numero-estadistica-adopcion">15</div>
                    <div class="etiqueta-estadistica-adopcion">Adoptados</div>
                </div>
            </div>

            <!-- Mascotas en adopción -->
            <div class="lista-adopciones">
                <!-- Carlos - Mestizo -->
                <div class="tarjeta-adopcion">
                    <div class="badge-adopcion">ADOPCIÓN</div>
                    
                    <div class="contenido-adopcion">
                        <img src="imagenes/carlos.jpg" alt="Carlos" class="foto-mascota-adopcion">
                        
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
                    
                    <button class="boton-interesa-adoptar">
                        ❤️ Me interesa adoptar →
                    </button>
                </div>

                <!-- Bella - Siamés -->
                <div class="tarjeta-adopcion">
                    <div class="badge-adopcion badge-urgente">URGENTE</div>
                    
                    <div class="contenido-adopcion">
                        <img src="imagenes/bella.jpg" alt="Bella" class="foto-mascota-adopcion">
                        
                        <div class="info-adopcion">
                            <div class="encabezado-adopcion">
                                <div class="info-basica-adopcion">
                                    <h3>Bella</h3>
                                    <p class="detalles-basicos">Siamés • 6 meses • ♀</p>
                                </div>
                            </div>
                            
                            <p class="descripcion-adopcion">Bella es una gatita muy dulce y tranquila. Ideal para apartamentos y personas mayores.</p>
                            
                            <div class="meta-adopcion">
                                <span>📍 Madrid Sur</span>
                                <span>📅 Hace 12 días</span>
                                <span>🏠 Asociación Felina</span>
                            </div>
                            
                            <div class="estados-adopcion">
                                <span class="estado-badge estado-vacunado">✅ Vacunado</span>
                            </div>
                        </div>
                    </div>
                    
                    <button class="boton-interesa-adoptar">
                        ❤️ Me interesa adoptar →
                    </button>
                </div>

                <!-- Rocky - Pastor Alemán -->
                <div class="tarjeta-adopcion">
                    <div class="badge-adopcion">ADOPCIÓN</div>
                    
                    <div class="contenido-adopcion">
                        <img src="imagenes/rocky.jpg" alt="Rocky" class="foto-mascota-adopcion">
                        
                        <div class="info-adopcion">
                            <div class="encabezado-adopcion">
                                <div class="info-basica-adopcion">
                                    <h3>Rocky</h3>
                                    <p class="detalles-basicos">Pastor Alemán • 4 años • ♂</p>
                                </div>
                            </div>
                            
                            <p class="descripcion-adopcion">Rocky es un perro leal y protector. Necesita una familia con experiencia en perros grandes.</p>
                            
                            <div class="meta-adopcion">
                                <span>📍 Madrid Norte</span>
                                <span>📅 Hace 8 días</span>
                                <span>🏠 Protectora Animal</span>
                            </div>
                            
                            <div class="estados-adopcion">
                                <span class="estado-badge estado-vacunado">✅ Vacunado</span>
                                <span class="estado-badge estado-esterilizado">💉 Esterilizado</span>
                            </div>
                        </div>
                    </div>
                    
                    <button class="boton-interesa-adoptar">
                        ❤️ Me interesa adoptar →
                    </button>
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
    <script>
        // Funcionalidad de filtros
        document.querySelectorAll('.filtro-adopcion').forEach(filtro => {
            filtro.addEventListener('click', function() {
                // Remover clase activo de todos
                document.querySelectorAll('.filtro-adopcion').forEach(f => f.classList.remove('activo'));
                // Agregar clase activo al seleccionado
                this.classList.add('activo');
                
                // Aquí implementarías la lógica de filtrado
                const tipoFiltro = this.textContent.trim();
                console.log('Filtro seleccionado:', tipoFiltro);
            });
        });

        // Funcionalidad de botones "Me interesa adoptar"
        document.querySelectorAll('.boton-interesa-adoptar').forEach(boton => {
            boton.addEventListener('click', function() {
                const nombreMascota = this.closest('.tarjeta-adopcion').querySelector('h3').textContent;
                alert(`¡Te has interesado en adoptar a ${nombreMascota}! Te contactaremos pronto.`);
            });
        });

        // Animaciones de entrada para las tarjetas
        function animarTarjetas() {
            const tarjetas = document.querySelectorAll('.tarjeta-adopcion');
            tarjetas.forEach((tarjeta, index) => {
                tarjeta.style.animationDelay = `${index * 0.1}s`;
            });
        }

        // Ejecutar animaciones al cargar
        document.addEventListener('DOMContentLoaded', animarTarjetas);
    </script>
</body>
</html>