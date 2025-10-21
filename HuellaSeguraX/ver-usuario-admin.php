<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

// Obtener email del usuario
$email = $_GET['email'] ?? '';

if (empty($email)) {
    header("Location: panel-admin.php?mensaje=Usuario no especificado&tipo=error");
    exit();
}

// Obtener datos del usuario
$consulta_usuario = "SELECT * FROM usuarios WHERE email_usuario = ?";
$stmt = $conexion->prepare($consulta_usuario);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: panel-admin.php?mensaje=Usuario no encontrado&tipo=error");
    exit();
}

$usuario = $resultado->fetch_assoc();

// Obtener mascotas del usuario
$consulta_mascotas = "SELECT * FROM mascotas WHERE id_usuario = ?";
$stmt_mascotas = $conexion->prepare($consulta_mascotas);
$stmt_mascotas->bind_param("i", $usuario['id_usuario']);
$stmt_mascotas->execute();
$mascotas = $stmt_mascotas->get_result();

// Obtener publicaciones del usuario
$consulta_publicaciones = "SELECT * FROM publicaciones WHERE id_usuario = ?";
$stmt_pub = $conexion->prepare($consulta_publicaciones);
$stmt_pub->bind_param("i", $usuario['id_usuario']);
$stmt_pub->execute();
$publicaciones = $stmt_pub->get_result();

// Si es veterinario, obtener datos adicionales
$es_veterinario = ($usuario['rol'] === 'veterinario');
$datos_veterinario = null;
if ($es_veterinario) {
    $consulta_vet = "SELECT * FROM veterinario WHERE id_usuario = ?";
    $stmt_vet = $conexion->prepare($consulta_vet);
    $stmt_vet->bind_param("i", $usuario['id_usuario']);
    $stmt_vet->execute();
    $resultado_vet = $stmt_vet->get_result();
    if ($resultado_vet->num_rows > 0) {
        $datos_veterinario = $resultado_vet->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuario - Panel Admin</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/panel-admin.css">
    <link rel="stylesheet" href="css/ver-usuario-admin.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body class="admin-panel">
    <header class="header-admin">
        <div class="logo-admin">
            <h1>🛡️ Panel Administrativo</h1>
        </div>
        <div class="acciones-admin">
            <a href="panel-admin.php" class="boton-logout">⬅️ Panel Principal</a>
        </div>
    </header>

    <div class="detalle-usuario">
        <!-- Header del usuario -->
        <div class="usuario-header">
            <?php 
            $foto_usuario = $usuario['foto_usuario'];
            $iniciales = strtoupper(substr($usuario['nombre_usuario'], 0, 1) . substr($usuario['apellido_usuario'], 0, 1));
            $mostrar_placeholder = false;
            $ruta_imagen = '';
            
            // Verificar si es una foto por defecto
            if (strpos($foto_usuario, 'default') !== false) {
                $mostrar_placeholder = true;
            } else {
                // Verificar primero en imagenes/usuarios/
                $ruta_completa = 'imagenes/usuarios/' . $foto_usuario;
                if (file_exists($ruta_completa)) {
                    $ruta_imagen = $ruta_completa;
                } else {
                    // Verificar en imagenes/ directamente
                    $ruta_alterna = 'imagenes/' . $foto_usuario;
                    if (file_exists($ruta_alterna)) {
                        $ruta_imagen = $ruta_alterna;
                    } else {
                        $mostrar_placeholder = true;
                    }
                }
            }
            ?>
            
            <?php if ($mostrar_placeholder): ?>
                <div class="usuario-foto-placeholder">
                    <?php echo $iniciales; ?>
                </div>
            <?php else: ?>
                <img src="<?php echo htmlspecialchars($ruta_imagen); ?>" 
                     alt="Foto de perfil" class="usuario-foto"
                     onerror="this.outerHTML='<div class=\'usuario-foto-placeholder\'><?php echo $iniciales; ?></div>';">
            <?php endif; ?>
            <div class="usuario-info-principal">
                <h2><?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']); ?></h2>
                <div>
                    <span class="badge-rol badge-<?php echo $usuario['rol']; ?>">
                        <?php echo strtoupper($usuario['rol']); ?>
                    </span>
                    <span class="badge-rol badge-<?php echo $usuario['estado']; ?>">
                        <?php echo strtoupper($usuario['estado']); ?>
                    </span>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>📧 Email:</strong>
                        <?php echo htmlspecialchars($usuario['email_usuario']); ?>
                    </div>
                    <div class="info-item">
                        <strong>📱 Teléfono:</strong>
                        <?php echo !empty($usuario['telefono_usuario']) ? htmlspecialchars($usuario['telefono_usuario']) : 'No especificado'; ?>
                    </div>
                    <div class="info-item">
                        <strong>🆔 ID Usuario:</strong>
                        #<?php echo $usuario['id_usuario']; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de veterinario (si aplica) -->
        <?php if ($es_veterinario && $datos_veterinario): ?>
        <div class="seccion">
            <h3>🩺 Información de Veterinario</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Especialidad:</strong>
                    <?php echo htmlspecialchars($datos_veterinario['especialidad']); ?>
                </div>
                <div class="info-item">
                    <strong>Clínica:</strong>
                    <?php echo htmlspecialchars($datos_veterinario['clinica']); ?>
                </div>
                <div class="info-item">
                    <strong>Horarios:</strong>
                    <?php echo htmlspecialchars($datos_veterinario['horarios_de_atencion']); ?>
                </div>
                <div class="info-item">
                    <strong>Certificado:</strong>
                    <?php echo $datos_veterinario['certificado'] ? '✅ Verificado' : '⏳ Pendiente'; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mascotas del usuario -->
        <div class="seccion">
            <h3>🐾 Mascotas Registradas (<?php echo $mascotas->num_rows; ?>)</h3>
            <?php if ($mascotas->num_rows > 0): ?>
                <div class="lista-mascotas">
                    <?php while ($mascota = $mascotas->fetch_assoc()): ?>
                        <div class="tarjeta-mascota">
                            <h4><?php echo htmlspecialchars($mascota['nombre_mascota']); ?></h4>
                            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($mascota['tipo']); ?></p>
                            <p><strong>Sexo:</strong> <?php echo htmlspecialchars($mascota['sexo']); ?></p>
                            <p><strong>Edad:</strong> <?php echo $mascota['edad_mascota']; ?> años</p>
                            <p><strong>Estado:</strong> <?php echo htmlspecialchars($mascota['estado']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="sin-datos">El usuario no tiene mascotas registradas</p>
            <?php endif; ?>
        </div>

        <!-- Publicaciones del usuario -->
        <div class="seccion">
            <h3>📢 Publicaciones (<?php echo $publicaciones->num_rows; ?>)</h3>
            <?php if ($publicaciones->num_rows > 0): ?>
                <div class="lista-publicaciones">
                    <?php while ($pub = $publicaciones->fetch_assoc()): ?>
                        <div class="tarjeta-publicacion">
                            <h4><?php echo htmlspecialchars($pub['titulo']); ?></h4>
                            <p><strong>Estado:</strong> <?php echo htmlspecialchars($pub['estado']); ?></p>
                            <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($pub['fecha'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="sin-datos">El usuario no tiene publicaciones</p>
            <?php endif; ?>
        </div>

        <!-- Botones de acción -->
        <div class="botones-accion">
            <a href="eliminar-usuario.php?email=<?php echo urlencode($email); ?>" 
               class="boton boton-eliminar"
               onclick="return confirm('¿Estás seguro de eliminar a <?php echo htmlspecialchars($usuario['nombre_usuario']); ?>? Esta acción no se puede deshacer.')">
                🗑️ Eliminar Usuario
            </a>
            <a href="panel-admin.php" class="boton boton-volver">
                ⬅️ Volver al Panel
            </a>
        </div>
    </div>
</body>
</html>
<?php cerrarConexion(); ?>