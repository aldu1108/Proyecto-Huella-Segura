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
    <title>Adopciones - Huella Segura</title>
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
        <section class="seccion-adopciones">
            <div class="encabezado-adopciones">
                <h2 class="titulo-seccion">Adopciones ❤️</h2>
                <p class="subtitulo-seccion">Encuentra tu compañero perfecto</p>
                <button class="boton-publicar-adopcion" onclick="mostrarFormularioAdopcion()">
                    Publicar en Adopción
                </button>
            </div>

            <!-- Filtros -->
            <div class="contenedor-filtros">
                <input type="text" class="barra-busqueda" placeholder="Buscar por tipo de animal...">
                
                <div class="filtros-adopcion">
                    <select class="filtro-select">
                        <option value="">Todos los animales</option>
                        <option value="perro">Perros</option>
                        <option value="gato">Gatos</option>
                        <option value="ave">Aves</option>
                        <option value="otro">Otros</option>
                    </select>
                    
                    <select class="filtro-select">
                        <option value="">Estado</option>
                        <option value="calle">De la calle</option>
                        <option value="refugio">De refugio</option>
                        <option value="casa">De una casa</option>
                    </select>
                </div>
            </div>

            <!-- Mascotas disponibles -->
            <div class="contenedor-adopciones">
                <!-- Ejemplo de mascota en adopción -->
                <div class="tarjeta-adopcion">
                    <div class="etiqueta-urgente">URGENTE</div>
                    <img src="imagenes/mascota-adopcion-1.jpg" alt="Rocky" class="foto-adopcion">
                    
                    <div class="info-adopcion">
                        <h3>Rocky</h3>
                        <p class="tipo-edad">Perro • Pastor Alemán • 2 años</p>
                        
                        <div class="detalles-basicos">
                            <span class="detalle">♂️ Macho</span>
                            <span class="detalle">🏥 Vacunado</span>
                            <span class="detalle">✂️ Esterilizado</span>
                        </div>
                        
                        <p class="personalidad">
                            Rocky es un perro muy leal y protector. Es perfecto para la familia y muy inteligente en el entrenamiento.
                        </p>
                        
                        <div class="info-publicacion">
                            <p><strong>📍 Ubicación:</strong> Madrid, Centro</p>
                            <p><strong>📅 Publicado:</strong> Hace 2 días</p>
                            <p><strong>👤 Por:</strong> Refugio Animal Madrid</p>
                        </div>
                        
                        <div class="estado-salud">
                            <span class="estado-vacunado">Vacunado</span>
                            <span class="estado-esterilizado">Esterilizado</span>
                        </div>
                    </div>
                    
                    <button class="boton-me-interesa">Me interesa adoptar</button>
                </div>

                <div class="tarjeta-adopcion">
                    <img src="imagenes/mascota-adopcion-2.jpg" alt="Mila" class="foto-adopcion">
                    
                    <div class="info-adopcion">
                        <h3>Mila</h3>
                        <p class="tipo-edad">Gata • Siamés • 1 año</p>
                        
                        <div class="detalles-basicos">
                            <span class="detalle">♀️ Hembra</span>
                            <span class="detalle">🏥 Vacunada</span>
                            <span class="detalle">✂️ Esterilizada</span>
                        </div>
                        
                        <p class="personalidad">
                            Mila es una gata muy tranquila y elegante. Le gusta dormir en lugares altos y es muy independiente pero cariñosa.
                        </p>
                        
                        <div class="info-publicacion">
                            <p><strong>📍 Ubicación:</strong> Barcelona, Eixample</p>
                            <p><strong>📅 Publicado:</strong> Hace 1 semana</p>
                            <p><strong>👤 Por:</strong> Ana García</p>
                        </div>
                        
                        <div class="estado-salud">
                            <span class="estado-vacunado">Vacunada</span>
                            <span class="estado-esterilizado">Esterilizada</span>
                        </div>
                    </div>
                    
                    <button class="boton-me-interesa">Me interesa adoptar</button>
                </div>

                <div class="tarjeta-adopcion">
                    <img src="imagenes/mascota-adopcion-3.jpg" alt="Charlie" class="foto-adopcion">
                    
                    <div class="info-adopcion">
                        <h3>Charlie</h3>
                        <p class="tipo-edad">Perro • Golden Retriever • 5 años</p>
                        
                        <div class="detalles-basicos">
                            <span class="detalle">♂️ Macho</span>
                            <span class="detalle">🏥 Vacunado</span>
                            <span class="detalle">❌ Sin esterilizar</span>
                        </div>
                        
                        <p class="personalidad">
                            Charlie es muy sociable y juguetón. Perfecto para familias con niños. Le encanta el agua y jugar en el parque.
                        </p>
                        
                        <div class="info-publicacion">
                            <p><strong>📍 Ubicación:</strong> Valencia, Centro</p>
                            <p><strong>📅 Publicado:</strong> Hace 3 días</p>
                            <p><strong>👤 Por:</strong> Familia López</p>
                        </div>
                        
                        <div class="estado-salud">
                            <span class="estado-vacunado">Vacunado</span>
                        </div>
                    </div>
                    
                    <button class="boton-me-interesa">Me interesa adoptar</button>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal para publicar adopción -->
    <div class="modal-adopcion" id="modalAdopcion" style="display: none;">
        <div class="contenido-modal">
            <div class="encabezado-modal">
                <h3>Publicar Mascota en Adopción</h3>
                <button class="boton-cerrar-modal" onclick="cerrarFormularioAdopcion()">✕</button>
            </div>
            <form method="POST" action="procesar-adopcion.php" enctype="multipart/form-data">
                <div class="grupo-input">
                    <label>Selecciona tu mascota</label>
                    <select name="id_mascota" required>
                        <option value="">Seleccionar mascota</option>
                        <!-- Aquí cargarías las mascotas del usuario -->
                    </select>
                </div>
                
                <div class="grupo-input">
                    <label>Condiciones de adopción</label>
                    <textarea name="condiciones" rows="4" placeholder="Describe las condiciones o requisitos para la adopción..." required></textarea>
                </div>
                
                <div class="grupo-input">
                    <label>Lugar de adopción</label>
                    <input type="text" name="lugar_adopcion" placeholder="Ciudad, barrio..." required>
                </div>
                
                <div class="grupo-input">
                    <label>Descripción adicional</label>
                    <textarea name="descripcion" rows="4" placeholder="Cuéntanos sobre la personalidad de tu mascota..."></textarea>
                </div>
                
                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarFormularioAdopcion()">Cancelar</button>
                    <button type="submit" class="boton-guardar">Publicar Adopción</button>
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

        // Modal adopción
        function mostrarFormularioAdopcion() {
            document.getElementById('modalAdopcion').style.display = 'flex';
        }

        function cerrarFormularioAdopcion() {
            document.getElementById('modalAdopcion').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalAdopcion').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarFormularioAdopcion();
            }
        });
    </script>

    <style>
        /* Estilos específicos para adopciones */
        .encabezado-adopciones {
            text-align: center;
            margin-bottom: 2rem;
        }

        .boton-publicar-adopcion {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1rem;
            margin-top: 1rem;
        }

        .filtros-adopcion {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .contenedor-adopciones {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .tarjeta-adopcion {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            transition: transform 0.3s;
        }

        .tarjeta-adopcion:hover {
            transform: translateY(-5px);
        }

        .etiqueta-urgente {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: #e74c3c;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }

        .foto-adopcion {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .info-adopcion {
            padding: 1.5rem;
        }

        .info-adopcion h3 {
            color: #d35400;
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
        }

        .tipo-edad {
            color: #666;
            margin-bottom: 1rem;
        }

        .detalles-basicos {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .detalle {
            background-color: #f8f9fa;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            color: #333;
        }

        .personalidad {
            color: #666;
            line-height: 1.4;
            margin-bottom: 1rem;
        }

        .info-publicacion {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .info-publicacion p {
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }

        .estado-salud {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .estado-vacunado {
            background-color: #27ae60;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .estado-esterilizado {
            background-color: #3498db;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .boton-me-interesa {
            width: 100%;
            background-color: #d35400;
            color: white;
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            cursor: pointer;
            border-radius: 0 0 15px 15px;
            transition: background-color 0.3s;
        }

        .boton-me-interesa:hover {
            background-color: #b8450e;
        }

        .modal-adopcion {
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

        .modal-adopcion .contenido-modal {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-adopcion .grupo-input {
            margin-bottom: 1rem;
        }

        .modal-adopcion .grupo-input label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }

        .modal-adopcion .grupo-input input, 
        .modal-adopcion .grupo-input select, 
        .modal-adopcion .grupo-input textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .contenedor-adopciones {
                grid-template-columns: 1fr;
            }
            
            .filtros-adopcion {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>