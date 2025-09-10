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
    <header class="cabecera-principal">
        <nav class="navegacion-principal">
            <button class="boton-menu-hamburguesa" id="menuHamburguesa">☰</button>
            <div class="logo-contenedor">
                <h1 class="logo-texto">PetCare 🐾</h1>
            </div>
            <div class="iconos-derecha">
                <button class="boton-buscar">🔍</button>
                <button class="boton-filtrar" id="botonFiltrar">🔧</button>
            </div>
        </nav>
        
        <!-- Menú hamburguesa desplegable -->
        <div class="menu-lateral" id="menuLateral">
            <div class="opciones-menu">
                <a href="index.php" class="opcion-menu">🏠 Inicio</a>
                <a href="mis-mascotas.php" class="opcion-menu">🐕 Mis Mascotas</a>
                <a href="mascotas-perdidas.php" class="opcion-menu">🔍 Mascotas Perdidas</a>
                <a href="adopciones.php" class="opcion-menu">❤️ Adopciones</a>
                <a href="comunidad.php" class="opcion-menu">👥 Comunidad</a>
                <a href="veterinaria.php" class="opcion-menu">🏥 Veterinaria</a>
                <a href="mi-perfil.php" class="opcion-menu">👤 Mi Perfil</a>
                <a href="configuracion.php" class="opcion-menu">⚙️ Configuración</a>
                <a href="logout.php" class="opcion-menu">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="contenedor-principal">
        <!-- Barra de búsqueda -->
        <form method="GET" action="" class="formulario-busqueda">
            <input type="text" name="buscar" class="barra-busqueda" 
                   placeholder="Buscar entre mis mascotas..." 
                   value="<?php echo htmlspecialchars($busqueda); ?>">
        </form>

        <!-- Botón agregar mascota -->
        <button class="boton-agregar-mascota" onclick="mostrarFormulario()">
            🐾 Agregar + Mascota
        </button>

        <!-- Sección Mis Mascotas -->
        <section class="seccion-mis-mascotas">
            <h2 class="titulo-seccion">Mis Mascotas (<?php echo $total_mascotas; ?>)</h2>
            
            <div class="contenedor-mascotas">
                <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                    <?php while($mascota = $resultado_mascotas->fetch_assoc()): ?>
                        <div class="tarjeta-mascota">
                            <img src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'mascota-default.jpg'; ?>" 
                                 alt="<?php echo $mascota['nombre_mascota']; ?>" class="foto-mascota">
                            <div class="info-mascota">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <h3><?php echo $mascota['nombre_mascota']; ?></h3>
                                    <span class="icono-genero"><?php echo $mascota['sexo'] == 'hembra' ? '♀️' : '♂️'; ?></span>
                                    <span class="etiqueta-mi-mascota">MI MASCOTA</span>
                                </div>
                                <p><?php echo ucfirst($mascota['tipo']); ?> • Golden Retriever</p>
                                <p>🎂 <?php echo $mascota['edad_mascota']; ?> años</p>
                                <p>⚖️ 28 kg</p>
                                
                                <p class="descripcion-mascota">
                                    <?php echo $mascota['nombre_mascota']; ?> es una <?php echo $mascota['tipo']; ?> muy cariñosa y juguetona. 
                                    Le encanta nadar y jugar en el parque. Es muy obediente y sociable con otros perros.
                                </p>
                            </div>
                            <button class="boton-ver-perfil" onclick="window.location.href='perfil-mascota.php?id=<?php echo $mascota['id_mascota']; ?>'">
                                👁 Ver Perfil Completo
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="mensaje-sin-mascotas" style="text-align: center; padding: 3rem; color: #666;">
                        <h3>¡Todavía no tienes mascotas registradas!</h3>
                        <p>Agrega tu primera mascota para comenzar a llevar un control completo de su salud y cuidados.</p>
                        <button class="boton-agregar-mascota" onclick="mostrarFormulario()">
                            Agregar Primera Mascota
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Modal para agregar mascota -->
    <div class="modal-agregar-mascota" id="modalAgregarMascota" style="display: none;">
        <div class="contenido-modal">
            <div class="encabezado-modal">
                <h3>Agregar Nueva Mascota</h3>
                <button class="boton-cerrar-modal" onclick="cerrarFormulario()">✕</button>
            </div>
            <form method="POST" action="procesar-mascota.php" enctype="multipart/form-data">
                <div class="grupo-input">
                    <label>Nombre de la mascota</label>
                    <input type="text" name="nombre_mascota" required>
                </div>
                
                <div class="fila-inputs">
                    <div class="grupo-input">
                        <label>Tipo</label>
                        <select name="tipo" required>
                            <option value="">Seleccionar</option>
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="ave">Ave</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="grupo-input">
                        <label>Sexo</label>
                        <select name="sexo" required>
                            <option value="">Seleccionar</option>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                        </select>
                    </div>
                </div>
                
                <div class="grupo-input">
                    <label>Edad (años)</label>
                    <input type="number" name="edad_mascota" min="0" max="30" required>
                </div>
                
                <div class="grupo-input">
                    <label>Fecha de cumpleaños</label>
                    <input type="date" name="cumpleanos_mascota" required>
                </div>
                
                <div class="grupo-input">
                    <label>Foto de la mascota</label>
                    <input type="file" name="foto_mascota" accept="image/*">
                </div>
                
                <div class="botones-modal">
                    <button type="button" class="boton-cancelar" onclick="cerrarFormulario()">Cancelar</button>
                    <button type="submit" class="boton-guardar">Guardar Mascota</button>
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

        // Modal agregar mascota
        function mostrarFormulario() {
            document.getElementById('modalAgregarMascota').style.display = 'flex';
        }

        function cerrarFormulario() {
            document.getElementById('modalAgregarMascota').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalAgregarMascota').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarFormulario();
            }
        });
    </script>

    <style>
        /* Estilos adicionales para el modal */
        .modal-agregar-mascota {
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

        .contenido-modal {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .encabezado-modal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .boton-cerrar-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .fila-inputs {
            display: flex;
            gap: 1rem;
        }

        .fila-inputs .grupo-input {
            flex: 1;
        }

        .grupo-input {
            margin-bottom: 1rem;
        }

        .grupo-input label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }

        .grupo-input input, .grupo-input select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 1rem;
        }

        .botones-modal {
            display: flex;
            gap: 1rem;
            justify-content: end;
            margin-top: 1.5rem;
        }

        .boton-cancelar, .boton-guardar {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
        }

        .boton-cancelar {
            background-color: #ccc;
            color: #333;
        }

        .boton-guardar {
            background-color: #d35400;
            color: white;
        }

        .descripcion-mascota {
            margin-top: 1rem;
            font-size: 0.9rem;
            line-height: 1.4;
            color: #666;
        }

        .icono-genero {
            color: #e74c3c;
        }

        .formulario-busqueda {
            margin-bottom: 1rem;
        }
    </style>
</body>
</html>