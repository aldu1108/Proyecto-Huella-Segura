<!-- ESTO VA A SERVIR CUANDO SE IMPLEMENTE LA FUNCION DE QUE EL ADMINISTRADOR PUEDA ACEPTAR LA SOLICITUD DEL VETERINARIO -->

<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea veterinario
if (!isset($_SESSION['es_veterinario']) || $_SESSION['es_veterinario'] !== true) {
    header("Location: login-veterinario.php");
    exit();
}

$veterinario_id = $_SESSION['veterinario_id'];
$nombre_veterinario = $_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido'];

// Obtener estadísticas del veterinario
$fecha_hoy = date('Y-m-d');

// Citas de hoy
$citas_hoy = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias 
                            WHERE id_veterinario = $veterinario_id AND DATE(fecha) = '$fecha_hoy'")->fetch_assoc()['total'];

// Total pacientes
$total_pacientes = $conexion->query("SELECT COUNT(DISTINCT id_mascota) as total FROM citas_veterinarias 
                                    WHERE id_veterinario = $veterinario_id")->fetch_assoc()['total'];

// Citas pendientes
$citas_pendientes = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias 
                                    WHERE id_veterinario = $veterinario_id AND estado = 'programada'")->fetch_assoc()['total'];

// Próximas citas
$proximas_citas = $conexion->query("SELECT c.*, m.nombre_mascota, m.tipo, u.nombre_usuario, u.apellido_usuario, u.telefono_usuario
                                    FROM citas_veterinarias c
                                    JOIN mascotas m ON c.id_mascota = m.id_mascota
                                    JOIN usuarios u ON m.id_usuario = u.id_usuario
                                    WHERE c.id_veterinario = $veterinario_id AND c.fecha >= NOW()
                                    ORDER BY c.fecha ASC LIMIT 10");

// Historial reciente
$historial_reciente = $conexion->query("SELECT h.*, m.nombre_mascota, m.tipo
                                        FROM historiales_medicos h
                                        JOIN mascotas m ON h.id_mascota = m.id_mascota
                                        WHERE h.id_veterinario = $veterinario_id
                                        ORDER BY h.fecha DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Veterinario - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/panel-veterinario.css">
</head>

<body class="panel-veterinario">
    <header class="header-veterinario">
        <div class="logo-veterinario">
            <h1>🩺 Panel Veterinario</h1>
            <p>Dr. <?php echo $nombre_veterinario; ?></p>
            <span class="especialidad"><?php echo $_SESSION['especialidad']; ?></span>
        </div>
        <div class="info-clinica">
            <p><strong>🏥 <?php echo $_SESSION['clinica']; ?></strong></p>
            <a href="logout.php" class="boton-logout-vet">Cerrar Sesión</a>
        </div>
    </header>

    <div class="contenedor-veterinario">
        <!-- Estadísticas rápidas -->
        <section class="estadisticas-vet">
            <div class="tarjeta-stat-vet urgente">
                <div class="icono-stat-vet">📅</div>
                <div class="numero-stat-vet"><?php echo $citas_hoy; ?></div>
                <div class="texto-stat-vet">Citas Hoy</div>
            </div>

            <div class="tarjeta-stat-vet">
                <div class="icono-stat-vet">🐾</div>
                <div class="numero-stat-vet"><?php echo $total_pacientes; ?></div>
                <div class="texto-stat-vet">Pacientes Total</div>
            </div>

            <div class="tarjeta-stat-vet pendiente">
                <div class="icono-stat-vet">⏰</div>
                <div class="numero-stat-vet"><?php echo $citas_pendientes; ?></div>
                <div class="texto-stat-vet">Citas Pendientes</div>
            </div>
        </section>

        <!-- Acciones rápidas veterinario -->
        <section class="acciones-vet">
            <button class="boton-accion-vet nueva-cita" onclick="nuevaCita()">
                ➕ Nueva Cita
            </button>
            <button class="boton-accion-vet ver-agenda" onclick="verAgenda()">
                📅 Mi Agenda
            </button>
            <button class="boton-accion-vet historial" onclick="verHistoriales()">
                📋 Historiales
            </button>
            <button class="boton-accion-vet configurar" onclick="configurarPerfil()">
                ⚙️ Mi Perfil
            </button>
        </section>

        <!-- Próximas citas -->
        <section class="proximas-citas-vet">
            <h2>📅 Próximas Citas</h2>
            <div class="lista-citas-vet">
                <?php if ($proximas_citas->num_rows > 0): ?>
                    <?php while ($cita = $proximas_citas->fetch_assoc()): ?>
                        <div class="cita-item-vet">
                            <div class="fecha-cita-vet">
                                <span class="dia-cita"><?php echo date('d', strtotime($cita['fecha'])); ?></span>
                                <span class="mes-cita"><?php echo date('M', strtotime($cita['fecha'])); ?></span>
                                <span class="hora-cita"><?php echo date('H:i', strtotime($cita['fecha'])); ?></span>
                            </div>
                            <div class="info-cita-vet">
                                <h4><?php echo htmlspecialchars($cita['nombre_mascota']); ?></h4>
                                <p><strong>Dueño:</strong>
                                    <?php echo htmlspecialchars($cita['nombre_usuario'] . ' ' . $cita['apellido_usuario']); ?>
                                </p>
                                <p><strong>Tipo:</strong> <?php echo ucfirst($cita['tipo']); ?></p>
                                <p><strong>Motivo:</strong> <?php echo htmlspecialchars($cita['motivo']); ?></p>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono_usuario']); ?></p>
                            </div>
                            <div class="acciones-cita-vet">
                                <button class="boton-pequeno-vet confirmar">✅</button>
                                <button class="boton-pequeno-vet ver-historial">📋</button>
                                <button class="boton-pequeno-vet contactar">📞</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sin-citas-vet">
                        <p>No tienes citas programadas próximamente</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Historial reciente -->
        <section class="historial-reciente-vet">
            <h2>📋 Consultas Recientes</h2>
            <div class="lista-historial-vet">
                <?php if ($historial_reciente->num_rows > 0): ?>
                    <?php while ($historial = $historial_reciente->fetch_assoc()): ?>
                        <div class="historial-item-vet">
                            <div class="fecha-historial">
                                <?php echo date('d/m/Y', strtotime($historial['fecha'])); ?>
                            </div>
                            <div class="info-historial">
                                <h4><?php echo htmlspecialchars($historial['nombre_mascota']); ?></h4>
                                <p><strong>Diagnóstico:</strong> <?php echo htmlspecialchars($historial['diagnostico']); ?></p>
                                <p><strong>Tratamiento:</strong> <?php echo htmlspecialchars($historial['tratamiento']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sin-historial-vet">
                        <p>No hay consultas recientes registradas</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script src="js/panel-veterinario.js"></script>
</body>

</html>

<?php cerrarConexion(); ?>