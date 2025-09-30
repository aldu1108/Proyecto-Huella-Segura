<?php
include_once('config/conexion.php');
session_start();

$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
$usuario_id = $_SESSION['usuario_id'] ?? null;
$rol_usuario = $_SESSION['rol'] ?? 'demo';

$resultados = [
    'mascotas' => [],
    'veterinarios' => [],
    'adopciones' => [],
    'perdidas' => []
];

if (!empty($termino) && strlen($termino) >= 2) {
    $termino_sql = '%' . $conexion->real_escape_string($termino) . '%';
    
    // Buscar mascotas propias (solo si está logueado)
    if ($usuario_id && $rol_usuario != 'demo') {
        $query_mascotas = "SELECT id_mascota, nombre_mascota, tipo, foto_mascota 
                           FROM mascotas 
                           WHERE id_usuario = $usuario_id 
                           AND nombre_mascota LIKE '$termino_sql' 
                           AND estado = 'activo'
                           LIMIT 5";
        $resultado_mascotas = $conexion->query($query_mascotas);
        while ($row = $resultado_mascotas->fetch_assoc()) {
            $resultados['mascotas'][] = $row;
        }
    }
    
    // Buscar veterinarios
    $query_veterinarios = "SELECT u.id_usuario, u.nombre_usuario, u.apellido_usuario, 
                           v.especialidad, v.clinica, u.foto_usuario
                           FROM usuarios u
                           JOIN veterinario v ON u.id_usuario = v.id_usuario
                           WHERE v.certificado = 1
                           AND (u.nombre_usuario LIKE '$termino_sql' 
                                OR u.apellido_usuario LIKE '$termino_sql'
                                OR v.especialidad LIKE '$termino_sql'
                                OR v.clinica LIKE '$termino_sql')
                           LIMIT 5";
    $resultado_veterinarios = $conexion->query($query_veterinarios);
    while ($row = $resultado_veterinarios->fetch_assoc()) {
        $resultados['veterinarios'][] = $row;
    }
    
    // Buscar mascotas en adopción
    $query_adopciones = "SELECT p.id_anuncio, m.nombre_mascota, m.tipo, m.foto_mascota, pa.lugar_adopcion
                         FROM publicaciones p
                         JOIN mascotas m ON p.id_mascota = m.id_mascota
                         JOIN publicacion_adopcion pa ON p.id_anuncio = pa.id_publicacion
                         WHERE p.estado = 'activo'
                         AND (m.nombre_mascota LIKE '$termino_sql' 
                              OR m.tipo LIKE '$termino_sql'
                              OR pa.lugar_adopcion LIKE '$termino_sql')
                         LIMIT 5";
    $resultado_adopciones = $conexion->query($query_adopciones);
    while ($row = $resultado_adopciones->fetch_assoc()) {
        $resultados['adopciones'][] = $row;
    }
    
    // Buscar mascotas perdidas
    $query_perdidas = "SELECT p.id_anuncio, m.nombre_mascota, m.tipo, m.foto_mascota, 
                       pp.ultima_ubicacion, pp.fecha_perdida
                       FROM publicaciones p
                       JOIN mascotas m ON p.id_mascota = m.id_mascota
                       JOIN publicacion_perdida pp ON p.id_anuncio = pp.id_publicacion
                       WHERE p.estado = 'activo'
                       AND (m.nombre_mascota LIKE '$termino_sql' 
                            OR m.tipo LIKE '$termino_sql'
                            OR pp.ultima_ubicacion LIKE '$termino_sql')
                       LIMIT 5";
    $resultado_perdidas = $conexion->query($query_perdidas);
    while ($row = $resultado_perdidas->fetch_assoc()) {
        $resultados['perdidas'][] = $row;
    }
}

$total_resultados = count($resultados['mascotas']) + count($resultados['veterinarios']) + 
                    count($resultados['adopciones']) + count($resultados['perdidas']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda: <?php echo htmlspecialchars($termino); ?> - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/buscar.css">
    <link rel="icon" type="image/png" href="imagenes/logo-hs.png">
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <main class="main-content">
        <div class="contenedor-busqueda">
            <h1>🔍 Resultados de búsqueda</h1>
            
            <!-- Barra de búsqueda en página de resultados -->
            <form action="buscar.php" method="GET" class="form-busqueda-pagina">
                <input type="text" 
                       name="q" 
                       class="input-busqueda-pagina" 
                       placeholder="Buscar..." 
                       value="<?php echo htmlspecialchars($termino); ?>"
                       required>
                <button type="submit" class="boton-buscar-pagina">🔍 Buscar</button>
            </form>

            <?php if (empty($termino)): ?>
                <div class="sin-resultados">
                    <div style="font-size: 64px;">🔍</div>
                    <h3>Ingresa un término para buscar</h3>
                    <p>Puedes buscar mascotas, veterinarios, adopciones y más</p>
                </div>
            <?php elseif ($total_resultados == 0): ?>
                <div class="sin-resultados">
                    <div style="font-size: 64px;">😕</div>
                    <h3>No se encontraron resultados para "<?php echo htmlspecialchars($termino); ?>"</h3>
                    <p>Intenta con otros términos de búsqueda</p>
                </div>
            <?php else: ?>
                <p class="total-resultados">Se encontraron <?php echo $total_resultados; ?> resultados</p>

                <!-- Mis Mascotas -->
                <?php if (!empty($resultados['mascotas'])): ?>
                    <section class="seccion-resultados">
                        <h2>🐾 Mis Mascotas (<?php echo count($resultados['mascotas']); ?>)</h2>
                        <div class="grid-resultados">
                            <?php foreach ($resultados['mascotas'] as $mascota): ?>
                                <a href="perfil-mascota.php?id=<?php echo $mascota['id_mascota']; ?>" class="card-resultado">
                                    <img src="imagenes/<?php echo $mascota['foto_mascota']; ?>" 
                                         alt="<?php echo htmlspecialchars($mascota['nombre_mascota']); ?>" 
                                         class="foto-resultado">
                                    <div class="info-resultado">
                                        <h4><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h4>
                                        <p><?php echo ucfirst($mascota['tipo']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Veterinarios -->
                <?php if (!empty($resultados['veterinarios'])): ?>
                    <section class="seccion-resultados">
                        <h2>🩺 Veterinarios (<?php echo count($resultados['veterinarios']); ?>)</h2>
                        <div class="grid-resultados">
                            <?php foreach ($resultados['veterinarios'] as $vet): ?>
                                <a href="veterinaria.php?vet=<?php echo $vet['id_usuario']; ?>" class="card-resultado">
                                    <img src="imagenes/<?php echo $vet['foto_usuario']; ?>" 
                                         alt="<?php echo htmlspecialchars($vet['nombre_usuario']); ?>" 
                                         class="foto-resultado">
                                    <div class="info-resultado">
                                        <h4>Dr. <?php echo htmlspecialchars($vet['nombre_usuario'] . ' ' . $vet['apellido_usuario']); ?></h4>
                                        <p><?php echo htmlspecialchars($vet['especialidad']); ?></p>
                                        <p style="font-size: 0.85rem; color: #666;">🏥 <?php echo htmlspecialchars($vet['clinica']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Adopciones -->
                <?php if (!empty($resultados['adopciones'])): ?>
                    <section class="seccion-resultados">
                        <h2>❤️ En Adopción (<?php echo count($resultados['adopciones']); ?>)</h2>
                        <div class="grid-resultados">
                            <?php foreach ($resultados['adopciones'] as $adopcion): ?>
                                <a href="adopciones.php?id=<?php echo $adopcion['id_anuncio']; ?>" class="card-resultado">
                                    <img src="imagenes/<?php echo $adopcion['foto_mascota']; ?>" 
                                         alt="<?php echo htmlspecialchars($adopcion['nombre_mascota']); ?>" 
                                         class="foto-resultado">
                                    <div class="info-resultado">
                                        <h4><?php echo htmlspecialchars($adopcion['nombre_mascota']); ?></h4>
                                        <p><?php echo ucfirst($adopcion['tipo']); ?></p>
                                        <p style="font-size: 0.85rem; color: #666;">📍 <?php echo htmlspecialchars($adopcion['lugar_adopcion']); ?></p>
                                    </div>
                                    <span class="badge-adopcion">En adopción</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Perdidas -->
                <?php if (!empty($resultados['perdidas'])): ?>
                    <section class="seccion-resultados">
                        <h2>🔍 Mascotas Perdidas (<?php echo count($resultados['perdidas']); ?>)</h2>
                        <div class="grid-resultados">
                            <?php foreach ($resultados['perdidas'] as $perdida): ?>
                                <a href="mascotas-perdidas.php?id=<?php echo $perdida['id_anuncio']; ?>" class="card-resultado">
                                    <img src="imagenes/<?php echo $perdida['foto_mascota']; ?>" 
                                         alt="<?php echo htmlspecialchars($perdida['nombre_mascota']); ?>" 
                                         class="foto-resultado">
                                    <div class="info-resultado">
                                        <h4><?php echo htmlspecialchars($perdida['nombre_mascota']); ?></h4>
                                        <p><?php echo ucfirst($perdida['tipo']); ?></p>
                                        <p style="font-size: 0.85rem; color: #666;">
                                            📍 <?php echo htmlspecialchars($perdida['ultima_ubicacion']); ?> • 
                                            <?php 
                                            $dias = (time() - strtotime($perdida['fecha_perdida'])) / (60 * 60 * 24);
                                            echo 'Hace ' . floor($dias) . ' días';
                                            ?>
                                        </p>
                                    </div>
                                    <span class="badge-perdido">PERDIDO</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js"></script>
</body>
</html>