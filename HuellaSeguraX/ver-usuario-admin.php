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
            <h1><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#5985E1"><path d="M480-96q-135-33-223.5-152.84Q168-368.69 168-515v-229l312-120 312 120v229q0 146.31-88.5 266.16Q615-129 480-96Z"/></svg> Panel Administrativo</h1>
        </div>
        <div class="acciones-admin">
            <a href="panel-admin.php" class="boton-logout"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M384-288 192-480l192-192 51 51-105 105h438v72H330l105 105-51 51Z"/></svg> Volver al Panel</a>
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
                    <?php echo $datos_veterinario['certificado'] ? '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Verificado' : '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M480-516q65 0 110.5-45.5T636-672v-120H324v120q0 65 45.5 110.5T480-516ZM192-96v-72h60v-120q0-59 28-109.5t78-82.5q-49-32-77.5-82.5T252-672v-120h-60v-72h576v72h-60v120q0 59-28.5 109.5T602-480q50 32 78 82.5T708-288v120h60v72H192Z"/></svg> Pendiente'; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mascotas del usuario -->
        <div class="seccion">
            <h3><svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> Mascotas Registradas (<?php echo $mascotas->num_rows; ?>)</h3>
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
            <h3><svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#EA3323"><path d="M720-444v-72h144v72H720Zm41 276-118-82 42-59 118 82-42 59Zm-77-483-41-59 118-82 41 59-118 82ZM192-192v-192h-24q-30 0-51-21t-21-51v-48q0-30 21-51t51-21h139l221-132v456L313-384h-25v192h-96Zm384-171v-234q23 22 35.5 53t12.5 64q0 33-12.5 64T576-363Z"/></svg> Publicaciones (<?php echo $publicaciones->num_rows; ?>)</h3>
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
                <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#ffffffff"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar Usuario
            </a>
            <a href="panel-admin.php" class="boton boton-volver">
                <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#ffffffff"><path d="M384-288 192-480l192-192 51 51-105 105h438v72H330l105 105-51 51Z"/></svg> Volver al Panel
            </a>
        </div>
    </div>
</body>
</html>
<?php cerrarConexion(); ?>