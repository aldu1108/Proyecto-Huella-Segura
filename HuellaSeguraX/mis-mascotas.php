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
<!-- Estilos para el modal -->
    <style>
        /* ESTILOS DEL MODAL */
        .modal-agregar-mascota {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }

        .contenido-modal-mascota {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .encabezado-modal-mascota {
            padding: 24px 24px 0;
            text-align: center;
            position: relative;
        }

        .titulo-modal-mascota {
            font-size: 20px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .boton-cerrar-modal-mascota {
            position: absolute;
            top: 20px;
            right: 24px;
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
        }

        .formulario-mascota {
            padding: 24px;
        }

        .grupo-input-mascota {
            margin-bottom: 20px;
        }

        .fila-inputs-mascota {
            display: flex;
            gap: 16px;
        }

        .fila-inputs-mascota .grupo-input-mascota {
            flex: 1;
        }

        .etiqueta-input-mascota {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .etiqueta-input-mascota.requerido::after {
            content: " *";
            color: #E74C3C;
        }

        .input-mascota, .select-mascota {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .input-mascota:focus, .select-mascota:focus {
            border-color: #D35400;
        }

        .input-file-mascota {
            width: 100%;
            padding: 12px;
            border: 2px dashed #e8e8e8;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            background: #f9f9f9;
        }

        .preview-foto-mascota {
            margin-top: 12px;
            text-align: center;
        }

        .botones-modal-mascota {
            display: flex;
            gap: 12px;
            padding: 24px;
            border-top: 1px solid #f0f0f0;
        }

        .boton-cancelar-mascota {
            flex: 1;
            padding: 14px;
            border: 2px solid #e8e8e8;
            background: white;
            color: #666;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
        }

        .boton-guardar-mascota {
            flex: 1;
            padding: 14px;
            background: #D35400;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-guardar-mascota:hover {
            background: #B8450E;
        }

        @media (max-width: 768px) {
            .fila-inputs-mascota {
                flex-direction: column;
                gap: 0;
            }
            
            .contenido-modal-mascota {
                margin: 0 10px;
            }
        }
    </style>
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
            <button class="filter-btn">🔽</button>
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
                                <div class="mascota-card">
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
                    <h4>📋 Hoy</h4>
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
    <nav class="bottom-nav">
        <button class="nav-btn">❤️</button>
        <button class="nav-btn">🔍</button>
        <button class="nav-btn">🏠</button>
        <button class="nav-btn">💥</button>
        <button class="nav-btn">🏥</button>
    </nav>

    <!-- JavaScript -->
    <script>
        // Abrir modal de agregar mascota
        document.addEventListener('DOMContentLoaded', function() {
            const btnAgregar = document.getElementById('btnAgregarMascota');
            const modal = document.getElementById('modalAgregarMascota');
            
            if (btnAgregar) {
                btnAgregar.addEventListener('click', function() {
                    modal.style.display = 'flex';
                });
            }
            
            // Cerrar modal al hacer clic fuera
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    cerrarModalMascota();
                }
            });
        });

        // Cerrar modal
        function cerrarModalMascota() {
            const modal = document.getElementById('modalAgregarMascota');
            const formulario = document.getElementById('formularioMascota');
            const preview = document.getElementById('previewFotoMascota');
            
            modal.style.display = 'none';
            formulario.reset();
            preview.style.display = 'none';
        }

        // Preview de imagen
        function previewImagen(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('previewFotoMascota');
                    const img = preview.querySelector('img');
                    img.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalMascota();
            }
        });
    </script>
</body>
</html>