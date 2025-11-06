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
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <main class="main-content">
        <div class="contenedor-busqueda">
            <h1><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg> Resultados de búsqueda</h1>
            
            <!-- Barra de búsqueda en página de resultados -->
            <form action="buscar.php" method="GET" class="form-busqueda-pagina">
                <input type="text" 
                       name="q" 
                       class="input-busqueda-pagina" 
                       placeholder="Buscar..." 
                       value="<?php echo htmlspecialchars($termino); ?>"
                       required>
                <button type="submit" class="boton-buscar-pagina"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg> Buscar</button>
            </form>

            <?php if (empty($termino)): ?>
                <div class="sin-resultados">
                    <div style="font-size: 64px;"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg></div>
                    <h3>Ingresa un término para buscar</h3>
                    <p>Puedes buscar mascotas, veterinarios, adopciones y más</p>
                </div>
            <?php elseif ($total_resultados == 0): ?>
                <div class="sin-resultados">
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
                        <h2><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5985E1"><path d="M540-80q-108 0-184-76t-76-184v-23q-86-14-143-80.5T80-600v-240h120v-40h80v160h-80v-40h-40v160q0 66 47 113t113 47q66 0 113-47t47-113v-160h-40v40h-80v-160h80v40h120v240q0 90-57 156.5T360-363v23q0 75 52.5 127.5T540-160q75 0 127.5-52.5T720-340v-67q-35-12-57.5-43T640-520q0-50 35-85t85-35q50 0 85 35t35 85q0 39-22.5 70T800-407v67q0 108-76 184T540-80Zm220-400q17 0 28.5-11.5T800-520q0-17-11.5-28.5T760-560q-17 0-28.5 11.5T720-520q0 17 11.5 28.5T760-480Zm0-40Z"/></svg> Veterinarios (<?php echo count($resultados['veterinarios']); ?>)</h2>
                        <div class="grid-resultados">
                            <?php foreach ($resultados['veterinarios'] as $vet): ?>
                                <a href="veterinaria.php?vet=<?php echo $vet['id_usuario']; ?>" class="card-resultado">
                                    <img src="imagenes/<?php echo $vet['foto_usuario']; ?>" 
                                         alt="<?php echo htmlspecialchars($vet['nombre_usuario']); ?>" 
                                         class="foto-resultado">
                                    <div class="info-resultado">
                                        <h4>Dr. <?php echo htmlspecialchars($vet['nombre_usuario'] . ' ' . $vet['apellido_usuario']); ?></h4>
                                        <p><?php echo htmlspecialchars($vet['especialidad']); ?></p>
                                        <p style="font-size: 0.85rem; color: #666;"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#DF9D9B"><path d="M480-254 330-104q-23 23-56 23t-56-23L104-218q-23-23-23-56t23-56l150-150-150-150q-23-23-23-56t23-56l114-114q23-23 56-23t56 23l150 150 150-150q23-23 56-23t56 23l114 114q23 23 23 56t-23 56L706-480l150 150q23 23 23 56t-23 56L742-104q-23 23-56 23t-56-23L480-254Zm0-266q17 0 28.5-11.5T520-560q0-17-11.5-28.5T480-600q-17 0-28.5 11.5T440-560q0 17 11.5 28.5T480-520Zm-170-16 114-114-150-150-114 114 150 150Zm90 96q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440Zm80 80q17 0 28.5-11.5T520-400q0-17-11.5-28.5T480-440q-17 0-28.5 11.5T440-400q0 17 11.5 28.5T480-360Zm80-80q17 0 28.5-11.5T600-480q0-17-11.5-28.5T560-520q-17 0-28.5 11.5T520-480q0 17 11.5 28.5T560-440Zm-24 130 150 150 114-114-150-150-114 114ZM339-621Zm282 282Z"/></svg> <?php echo htmlspecialchars($vet['clinica']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Adopciones -->
                <?php if (!empty($resultados['adopciones'])): ?>
                    <section class="seccion-resultados">
                        <h2><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> En Adopción (<?php echo count($resultados['adopciones']); ?>)</h2>
                        <div class="grid-resultados">
                            <?php foreach ($resultados['adopciones'] as $adopcion): ?>
                                <a href="adopciones.php?id=<?php echo $adopcion['id_anuncio']; ?>" class="card-resultado">
                                    <img src="imagenes/<?php echo $adopcion['foto_mascota']; ?>" 
                                         alt="<?php echo htmlspecialchars($adopcion['nombre_mascota']); ?>" 
                                         class="foto-resultado">
                                    <div class="info-resultado">
                                        <h4><?php echo htmlspecialchars($adopcion['nombre_mascota']); ?></h4>
                                        <p><?php echo ucfirst($adopcion['tipo']); ?></p>
                                        <p style="font-size: 0.85rem; color: #666;"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M480-480q33 0 56.5-23.5T560-560q0-33-23.5-56.5T480-640q-33 0-56.5 23.5T400-560q0 33 23.5 56.5T480-480Zm0 400Q319-217 239.5-334.5T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 100-79.5 217.5T480-80Z"/></svg> <?php echo htmlspecialchars($adopcion['lugar_adopcion']); ?></p>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M480-480q33 0 56.5-23.5T560-560q0-33-23.5-56.5T480-640q-33 0-56.5 23.5T400-560q0 33 23.5 56.5T480-480Zm0 400Q319-217 239.5-334.5T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 100-79.5 217.5T480-80Z"/></svg> <?php echo htmlspecialchars($perdida['ultima_ubicacion']); ?> • 
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