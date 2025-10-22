<?php
include_once('config/conexion.php');
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['rol'])) {
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

// Obtener eventos del día de hoy
$fecha_hoy = date('Y-m-d');
$consulta_eventos_hoy = "SELECT c.*, m.nombre_mascota, m.tipo
                         FROM citas_veterinarias c
                         JOIN mascotas m ON c.id_mascota = m.id_mascota
                         WHERE m.id_usuario = $usuario_id 
                         AND DATE(c.fecha) = '$fecha_hoy'
                         AND c.estado != 'cancelada'
                         ORDER BY c.fecha ASC";
$resultado_eventos_hoy = $conexion->query($consulta_eventos_hoy);

// Contar total de mascotas
$consulta_total = "SELECT COUNT(*) as total FROM mascotas WHERE id_usuario = $usuario_id AND estado = 'activo'";
$resultado_total = $conexion->query($consulta_total);
$total_mascotas = $resultado_total->fetch_assoc()['total'];

// Mensajes de éxito/error
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['mensaje'])) {
    switch ($_GET['mensaje']) {
        case 'mascota_agregada':
            $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : 'la mascota';
            $mensaje = "¡$nombre ha sido agregada exitosamente! 🐾";
            $tipo_mensaje = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'error_agregar':
            $mensaje = 'Error al agregar la mascota. Intenta de nuevo.';
            $tipo_mensaje = 'error';
            break;
        case 'error_base_datos':
            $mensaje = 'Error en la base de datos. Contacta al administrador.';
            $tipo_mensaje = 'error';
            break;
        default:
            $mensaje = 'Ocurrió un error inesperado.';
            $tipo_mensaje = 'error';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Mascotas - PetCare</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/mis-mascotas.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <?php if (!empty($mensaje)): ?>
                <div class="mensaje-mascota mensaje-<?php echo $tipo_mensaje; ?>" style="
            background: <?php echo $tipo_mensaje === 'success' ? '#d4edda' : '#f8d7da'; ?>;
            color: <?php echo $tipo_mensaje === 'success' ? '#155724' : '#721c24'; ?>;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid <?php echo $tipo_mensaje === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;
            display: flex;
            justify-content: space-between;
            align-items: center;
            ">
                    <span><?php echo $mensaje; ?></span>
                    <button onclick="this.parentElement.remove()" style="
                background: none;
                border: none;
                color: inherit;
                cursor: pointer;
                font-size: 18px;
                padding: 0 5px;
            ">×</button>
                </div>
        <?php endif; ?>
        
        <!-- Barra de búsqueda -->
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Buscar mascotas, veterinarios, recordatorios...">
            <button class="filter-btn">🔍</button>
        </div>

        <!-- Sección Mis Mascotas -->
        <section class="mis-mascotas-section">
            <div class="section-header">
                <h2>Mis Mascotas</h2>
                <button class="btn-add" id="btnAgregarMascota">+ Agregar</button>
            </div>

            <div class="mascotas-grid">
                <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                        <?php while ($mascota = $resultado_mascotas->fetch_assoc()): ?>
                                <div class="mascota-card" onclick="window.location.href='perfil-mascota.php?id=<?php echo $mascota['id_mascota']; ?>'">
                                    <img src="imagenes/<?php echo htmlspecialchars($mascota['foto_mascota']); ?>" 
                                         alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" 
                                         class="mascota-photo"
                                         onerror="this.src=''">
                                    <div class="mascota-info">
                                        <h3><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h3>
                                        <p><?php echo ucfirst($mascota['tipo']); ?></p>
                                        <p><?php echo $mascota['edad_mascota']; ?> años</p>
                                    </div>
                                </div>
                        <?php endwhile; ?>
                <?php else: ?>
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
                <?php endif; ?>
            </div>

            <!-- Sugerencia adopción -->
            <div class="adopcion-banner">
                <h4>🐾 ¿Buscas una nueva mascota?</h4>
                <p>Hay mascotas esperando un hogar. La adopción es amor puro.</p>
                <button class="btn-adopcion">❤️ Ver Mascotas en Adopción</button>
            </div>
        </section>
    </main>

    <!-- Modal para agregar mascota -->
    <div class="modal-agregar-mascota" id="modalAgregarMascota">
        <div class="contenido-modal-mascota">
            <div class="encabezado-modal-mascota">
                <h3 class="titulo-modal-mascota">Agregar Nueva Mascota</h3>
                <button class="boton-cerrar-modal-mascota" onclick="cerrarModalMascota()">×</button>
            </div>
            
            <form class="formulario-mascota" id="formularioMascota" action="procesar-mascota.php" method="POST" enctype="multipart/form-data">
                <div class="grupo-input-mascota">
                    <label class="etiqueta-input-mascota requerido">Nombre de la mascota</label>
                    <input type="text" class="input-mascota" name="nombre_mascota" placeholder="Ej: Max, Luna, Bella..." required>
                </div>

                <div class="fila-inputs-mascota">
                    <div class="grupo-input-mascota">
                        <label class="etiqueta-input-mascota requerido">Tipo</label>
                        <select class="select-mascota" name="tipo" required>
                            <option value="">Seleccionar</option>
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="grupo-input-mascota">
                        <label class="etiqueta-input-mascota requerido">Sexo</label>
                        <select class="select-mascota" name="sexo" required>
                            <option value="">Seleccionar</option>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                        </select>
                    </div>
                </div>

                <div class="fila-inputs-mascota">
                    <div class="grupo-input-mascota">
                        <label class="etiqueta-input-mascota requerido">Edad (años)</label>
                        <input type="number" class="input-mascota" name="edad_mascota" min="0" max="30" required>
                    </div>
                    
                    <div class="grupo-input-mascota">
                        <label class="etiqueta-input-mascota">Fecha de nacimiento</label>
                        <input type="date" class="input-mascota" name="cumpleanos_mascota">
                    </div>
                </div>

                <div class="grupo-input-mascota">
                    <label class="etiqueta-input-mascota">Foto de la mascota</label>
                    <input type="file" class="input-file-mascota" name="foto_mascota" accept="image/*" onchange="previewImagen(this)">
                    <div class="preview-foto-mascota" id="previewFotoMascota" style="display: none;">
                        <img src="" alt="Preview" style="max-width: 150px; border-radius: 8px;">
                    </div>
                </div>

                <div class="botones-modal-mascota">
                    <button type="button" class="boton-cancelar-mascota" onclick="cerrarModalMascota()">Cancelar</button>
                    <button type="submit" class="boton-guardar-mascota">Guardar Mascota</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <!-- JavaScript -->
     <script src="js/scripts.js"></script>
    <script src="js/mis-mascotas.js"></script>
</body>
</html>