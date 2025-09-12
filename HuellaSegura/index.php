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
    <title>Inicio - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

  
    <div class="contenedor-principal">
        <!-- Saludo personalizado -->
        <div class="saludo-usuario">
            <h2>¡Hola, <?php echo $nombre_usuario; ?>! 👋</h2>
        </div>

        <!-- Sección Mis Mascotas -->
        <section class="seccion-mis-mascotas">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="titulo-seccion">Mis Mascotas</h3>
                <button class="boton-agregar-mascota" onclick="window.location.href='mis-mascotas.php'">+ Agregar</button>
            </div>
            
            <div class="contenedor-mascotas">
                <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
                    <?php while($mascota = $resultado_mascotas->fetch_assoc()): ?>
                        <div class="tarjeta-mascota">
                            <img src="imagenes/<?php echo !empty($mascota['foto_mascota']) ? $mascota['foto_mascota'] : 'mascota-default.jpg'; ?>" 
                                 alt="<?php echo $mascota['nombre_mascota']; ?>" class="foto-mascota">
                            <div class="info-mascota">
                                <h3><?php echo $mascota['nombre_mascota']; ?></h3>
                                <p><?php echo ucfirst($mascota['tipo']); ?> • <?php echo $mascota['edad_mascota']; ?> años</p>
                                <p>Sexo: <?php echo ucfirst($mascota['sexo']); ?></p>
                            </div>
                            <button class="boton-ver-perfil" onclick="window.location.href='perfil-mascota.php?id=<?php echo $mascota['id_mascota']; ?>'">
                                Ver Perfil Completo
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="mensaje-sin-mascotas">
                        <p>¡Todavía no tienes mascotas registradas!</p>
                        <button class="boton-agregar-mascota" onclick="window.location.href='mis-mascotas.php'">
                            Agregar Primera Mascota
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sugerencia de adopción -->
            <div class="sugerencia-adopcion">
                <h4>¿Buscas una nueva mascota?</h4>
                <p>Hay mascotas esperando un hogar. La adopción es amor puro.</p>
                <button class="boton-ver-adopciones" onclick="window.location.href='adopciones.php'">
                    ❤️ Ver Mascotas en Adopción
                </button>
            </div>
        </section>

        <!-- Calendario de Cuidados -->
        <section class="seccion-calendario">
            <div class="encabezado-calendario">
                <h3 class="titulo-calendario">📅 Calendario de Cuidados</h3>
                <div class="navegacion-calendario">
                    <button class="boton-calendario">◀</button>
                    <span>septiembre de 2025</span>
                    <button class="boton-calendario">▶</button>
                </div>
                <span class="eventos-programados">5 eventos programados</span>
            </div>
            
            <!-- Mini calendario visual -->
            <div class="mini-calendario">
                <div class="dias-semana">
                    <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sa</span><span>Do</span>
                </div>
                <div class="dias-mes">
                    <span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span>
                    <span class="dia-actual">8</span><span class="dia-evento">9</span><span>10</span><span>11</span><span>12</span><span>13</span><span>14</span>
                    <span class="dia-evento">15</span><span>16</span><span>17</span><span>18</span><span>19</span><span>20</span><span>21</span>
                </div>
            </div>
            
            <div class="eventos-hoy">
                <h4>📍 Hoy - <span style="color: #e74c3c;">3</span></h4>
                
                <?php if ($resultado_citas && $resultado_citas->num_rows > 0): ?>
                    <?php while($cita = $resultado_citas->fetch_assoc()): ?>
                        <div class="evento-item <?php echo $cita['estado'] == 'urgente' ? 'evento-urgente' : 'evento-medio'; ?>">
                            <span class="icono-evento">💉</span>
                            <div class="info-evento">
                                <strong><?php echo $cita['motivo']; ?></strong>
                                <p><?php echo $cita['nombre_mascota']; ?> • <?php echo date('H:i', strtotime($cita['fecha'])); ?></p>
                            </div>
                            <span class="etiqueta-urgencia"><?php echo ucfirst($cita['estado']); ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <?php if ($resultado_eventos && $resultado_eventos->num_rows > 0): ?>
                    <?php while($evento = $resultado_eventos->fetch_assoc()): ?>
                        <div class="evento-item">
                            <span class="icono-evento">📋</span>
                            <div class="info-evento">
                                <strong><?php echo $evento['titulo']; ?></strong>
                                <p><?php echo substr($evento['descripcion'], 0, 50); ?>...</p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Navegación inferior -->
    <nav class="navegacion-inferior">
        <button class="boton-nav-inferior" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="boton-nav-inferior" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="boton-nav-inferior" onclick="window.location.href='index.php'">🏠</button>
        <button class="boton-nav-inferior" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="boton-nav-inferior" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
</body>
</html>