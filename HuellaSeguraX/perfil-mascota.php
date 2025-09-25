<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mascota_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Obtener información de la mascota
$consulta_mascota = "SELECT * FROM mascotas WHERE id_mascota = $mascota_id AND id_usuario = $usuario_id AND estado = 'activo'";
$resultado_mascota = $conexion->query($consulta_mascota);

if (!$resultado_mascota || $resultado_mascota->num_rows == 0) {
    header("Location: mis-mascotas.php");
    exit();
}

$mascota = $resultado_mascota->fetch_assoc();

// Obtener historial médico
$consulta_historial = "SELECT * FROM historiales_medicos WHERE id_mascota = $mascota_id ORDER BY fecha DESC LIMIT 10";
$resultado_historial = $conexion->query($consulta_historial);

// Obtener próximas citas
$fecha_hoy = date('Y-m-d');
$consulta_citas = "SELECT * FROM citas_veterinarias WHERE id_mascota = $mascota_id AND fecha >= '$fecha_hoy' ORDER BY fecha ASC LIMIT 5";
$resultado_citas = $conexion->query($consulta_citas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $mascota['nombre_mascota']; ?> - Perfil - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/perfil-mascota.css">
</head>
<body>
    <!-- Header -->
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Header de la mascota -->
        <section class="mascota-header">
            <div class="mascota-info-principal">
                <img src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'imagenes/mascota-default.jpg'; ?>" 
                     alt="<?php echo $mascota['nombre_mascota']; ?>" class="mascota-foto-grande">
                <div class="mascota-datos">
                    <h1><?php echo $mascota['nombre_mascota']; ?> 💡</h1>
                    <p class="mascota-tipo">Perro • Golden Retriever</p>
                    <div class="mascota-stats">
                        <span class="stat">🎂 <?php echo $mascota['edad_mascota']; ?> años</span>
                        <span class="stat">⚖️ 28 kg</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Información detallada -->
        <section class="informacion-detallada">
            <h3>Información Detallada</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <label>Fecha de nacimiento:</label>
                    <span><?php echo date('d/m/Y', strtotime($mascota['cumpleanos_mascota'] ?? '2021-03-15')); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Color:</label>
                    <span>Dorado</span>
                </div>
                
                <div class="info-item">
                    <label>Microchip:</label>
                    <span>982123456789012</span>
                </div>
                
                <div class="info-item">
                    <label>Propietario:</label>
                    <span>Juan Pérez</span>
                </div>
            </div>

            <div class="descripcion">
                <label>Descripción:</label>
                <p><?php echo $mascota['nombre_mascota']; ?> es una perra muy cariñosa y juguetona. Le encanta nadar y jugar en el parque. Es muy obediente y sociable con otros perros.</p>
            </div>
        </section>

        <!-- Seguimiento de peso -->
        <section class="seguimiento-peso">
            <h3>Seguimiento de Peso</h3>
            
            <!-- Gráfico simulado -->
            <div class="grafico-peso">
                <div class="grafico-container">
                    <canvas id="pesoChart" width="400" height="200"></canvas>
                    <!-- Simulación de gráfico con CSS -->
                    <div class="peso-timeline">
                        <div class="peso-point" style="left: 10%; bottom: 20%;">
                            <span class="peso-value">22kg</span>
                            <span class="peso-date">2023-01</span>
                        </div>
                        <div class="peso-point" style="left: 30%; bottom: 40%;">
                            <span class="peso-value">24kg</span>
                            <span class="peso-date">2023-03</span>
                        </div>
                        <div class="peso-point" style="left: 50%; bottom: 60%;">
                            <span class="peso-value">26kg</span>
                            <span class="peso-date">2023-06</span>
                        </div>
                        <div class="peso-point" style="left: 70%; bottom: 70%;">
                            <span class="peso-value">27kg</span>
                            <span class="peso-date">2023-09</span>
                        </div>
                        <div class="peso-point active" style="left: 90%; bottom: 80%;">
                            <span class="peso-value">28kg</span>
                            <span class="peso-date">2024-01</span>
                        </div>
                        <!-- Línea de conexión -->
                        <svg class="peso-line">
                            <polyline points="10,160 120,120 200,80 280,60 360,40" stroke="#D35400" stroke-width="2" fill="none"/>
                        </svg>
                    </div>
                    
                    <!-- Eje Y (peso) -->
                    <div class="eje-y">
                        <span class="eje-label" style="bottom: 90%;">30</span>
                        <span class="eje-label" style="bottom: 70%;">26</span>
                        <span class="eje-label" style="bottom: 50%;">23</span>
                        <span class="eje-label" style="bottom: 30%;">20</span>
                        <span class="eje-label" style="bottom: 10%;">kg</span>
                    </div>
                    
                    <!-- Eje X (tiempo) -->
                    <div class="eje-x">
                        <span class="eje-label" style="left: 10%;">2023-01</span>
                        <span class="eje-label" style="left: 30%;">2023-03</span>
                        <span class="eje-label" style="left: 50%;">2023-06</span>
                        <span class="eje-label" style="left: 70%;">2023-09</span>
                        <span class="eje-label" style="left: 90%;">2024-01</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Historial Médico -->
        <section class="historial-medico">
            <div class="section-header">
                <h3>Historial Médico</h3>
                <button class="btn-veterinarios">🩺 Veterinarios</button>
            </div>

            <div class="historial-list">
                <!-- Consulta General -->
                <div class="historial-item">
                    <div class="historial-date">2024-01-15</div>
                    <div class="historial-content">
                        <h4>Consulta General</h4>
                        <p><strong>Dr. María González</strong></p>
                        <p>Clínica Veterinaria San Martín</p>
                        <p><strong>Diagnóstico:</strong> Estado de salud excelente</p>
                        <p><strong>Tratamiento:</strong> Vacunación anual completa</p>
                        <p><strong>Notas:</strong> Peso ideal, muy activo y saludable</p>
                    </div>
                </div>

                <!-- Cirugía -->
                <div class="historial-item">
                    <div class="historial-date">2023-09-20</div>
                    <div class="historial-content">
                        <h4>Cirugía</h4>
                        <p><strong>Dr. Carlos Rodríguez</strong></p>
                        <p>Hospital Veterinario Central</p>
                        <p><strong>Diagnóstico:</strong> Esterilización</p>
                        <p><strong>Tratamiento:</strong> Cirugía de esterilización exitosa</p>
                        <p><strong>Notas:</strong> Recuperación rápida, sin complicaciones</p>
                    </div>
                </div>

                <!-- Emergencia -->
                <div class="historial-item">
                    <div class="historial-date">2023-06-10</div>
                    <div class="historial-content">
                        <h4>Emergencia</h4>
                        <p><strong>Dra. Ana López</strong></p>
                        <p>Urgencias Veterinarias 24h</p>
                        <p><strong>Diagnóstico:</strong> Gastroenteritis leve</p>
                        <p><strong>Tratamiento:</strong> Tratamiento con probióticos y dieta blanda</p>
                        <p><strong>Notas:</strong> Mejoría completa en 3 días</p>
                    </div>
                </div>
            </div>

            <button class="btn-agregar-visita">+ Agregar Visita Médica</button>
        </section>

        <!-- Próximas Citas -->
        <section class="proximas-citas">
            <h3>Próximas Citas</h3>
            
            <div class="cita-proxima">
                <div class="cita-fecha">
                    <div class="fecha-dia">15</div>
                    <div class="fecha-mes">Feb 2024</div>
                    <div class="fecha-hora">10:30 AM</div>
                </div>
                <div class="cita-info">
                    <h4>Vacunación Anual</h4>
                    <p>Dr. María González</p>
                    <p>📍 Clínica Veterinaria San Martín</p>
                </div>
            </div>

            <button class="btn-nueva-cita">📅 Agendar Nueva Cita</button>
        </section>

        <!-- Contacto de Emergencia -->
        <section class="contacto-emergencia">
            <h3>❤️ Contacto de Emergencia</h3>
            <div class="emergencia-info">
                <p><strong>Teléfono:</strong> +34 123 456 789</p>
                <p><strong>Hospital 24h:</strong> Urgencias Veterinarias Central</p>
                <button class="btn-llamar">📞 Llamar</button>
            </div>
        </section>
    </main>

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
                