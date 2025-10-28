<?php
include_once('config/conexion.php');
include_once('includes/funciones.php');
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Procesar formulario de actualización de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['actualizar_perfil'])) {
        $nombre = limpiarDatos($_POST['nombre_usuario']);
        $apellido = limpiarDatos($_POST['apellido_usuario']);
        $email = limpiarDatos($_POST['email_usuario']);
        $telefono = limpiarDatos($_POST['telefono_usuario']);

        $errores = array();

        // Validaciones
        if (empty($nombre)) {
            $errores[] = "El nombre es obligatorio";
        }

        if (empty($apellido)) {
            $errores[] = "El apellido es obligatorio";
        }

        if (empty($email) || !validarEmail($email)) {
            $errores[] = "Email válido es obligatorio";
        }

        // Verificar si el email ya existe (excluyendo el usuario actual)
        $consulta_email = "SELECT id_usuario FROM usuarios WHERE email_usuario = '$email' AND id_usuario != $usuario_id";
        $resultado_email = $conexion->query($consulta_email);

        if ($resultado_email->num_rows > 0) {
            $errores[] = "Este email ya está registrado por otro usuario";
        }

        if (empty($errores)) {
            $consulta_actualizar = "UPDATE usuarios SET 
                                   nombre_usuario = '$nombre',
                                   apellido_usuario = '$apellido',
                                   email_usuario = '$email',
                                   telefono_usuario = '$telefono'
                                   WHERE id_usuario = $usuario_id";

            if ($conexion->query($consulta_actualizar)) {
                // Actualizar variables de sesión
                $_SESSION['usuario_nombre'] = $nombre;
                $mensaje_exito = "Perfil actualizado correctamente";
                registrarActividad($usuario_id, 'PERFIL_ACTUALIZADO', "Datos personales actualizados");
            } else {
                $mensaje_error = "Error al actualizar el perfil";
            }
        }
    }

    // Cambiar contraseña
    if (isset($_POST['cambiar_password'])) {
        $password_actual = $_POST['password_actual'];
        $password_nueva = $_POST['password_nueva'];
        $password_confirmar = $_POST['password_confirmar'];

        $errores_password = array();

        // Obtener contraseña actual del usuario
        $consulta_password = "SELECT contraseña_usuario FROM usuarios WHERE id_usuario = $usuario_id";
        $resultado_password = $conexion->query($consulta_password);
        $usuario_data = $resultado_password->fetch_assoc();

        // Verificar contraseña actual
        if (!password_verify($password_actual, $usuario_data['contraseña_usuario'])) {
            $errores_password[] = "La contraseña actual no es correcta";
        }

        if (strlen($password_nueva) < 6) {
            $errores_password[] = "La nueva contraseña debe tener al menos 6 caracteres";
        }

        if ($password_nueva !== $password_confirmar) {
            $errores_password[] = "Las contraseñas nuevas no coinciden";
        }

        if (empty($errores_password)) {
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $consulta_password_update = "UPDATE usuarios SET contraseña_usuario = '$password_hash' WHERE id_usuario = $usuario_id";

            if ($conexion->query($consulta_password_update)) {
                $mensaje_exito_password = "Contraseña actualizada correctamente";
                registrarActividad($usuario_id, 'PASSWORD_CAMBIADO', "Contraseña modificada");
            } else {
                $mensaje_error_password = "Error al cambiar la contraseña";
            }
        }
    }
}

// Obtener datos actuales del usuario
$consulta_usuario = "SELECT * FROM usuarios WHERE id_usuario = $usuario_id";
$resultado_usuario = $conexion->query($consulta_usuario);
$usuario = $resultado_usuario->fetch_assoc();

// Obtener estadísticas del usuario
$total_mascotas = contarMascotasUsuario($usuario_id, $conexion);

$consulta_citas = "SELECT COUNT(*) as total FROM citas_veterinarias c 
                   JOIN mascotas m ON c.id_mascota = m.id_mascota 
                   WHERE m.id_usuario = $usuario_id";
$resultado_citas = $conexion->query($consulta_citas);
$total_citas = $resultado_citas->fetch_assoc()['total'];

$consulta_eventos = "SELECT COUNT(*) as total FROM eventos_comunidad WHERE id_usuario = $usuario_id";
$resultado_eventos = $conexion->query($consulta_eventos);
$total_eventos = $resultado_eventos->fetch_assoc()['total'];

// Verificar si es veterinario
$es_veterinario = esVeterinario($usuario_id, $conexion);
$info_veterinario = null;
if ($es_veterinario) {
    $info_veterinario = obtenerVeterinario($usuario_id, $conexion);
}

// Obtener fecha de registro (aproximada)
$fecha_registro = new DateTime($usuario['id_usuario'] . ' days ago'); // Simplificado
$fecha_registro_formateada = formatearFecha(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/mi-perfil.css">
    <?php include_once("includes/logo.php"); ?>
</head>

<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <main class="main-content">
        <div class="perfil-container">
            <!-- Header del perfil -->
            <section class="perfil-header">
                <div class="avatar-perfil">👤</div>
                <h1 class="nombre-perfil">
                    <?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']); ?>
                </h1>
                <p class="email-perfil"><?php echo htmlspecialchars($usuario['email_usuario']); ?></p>

                <div class="badges-perfil">
                    <?php if ($es_veterinario): ?>
                        <span class="badge-usuario badge-veterinario">🏥 Veterinario Certificado</span>
                    <?php endif; ?>
                    <span class="badge-usuario badge-miembro">👑 Miembro desde 2025</span>
                    <?php if ($total_mascotas > 0): ?>
                        <span class="badge-usuario">🐾 Dueño de <?php echo $total_mascotas; ?>
                            mascota<?php echo $total_mascotas > 1 ? 's' : ''; ?></span>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Estadísticas del usuario -->
            <section class="estadisticas-perfil">
                <div class="stat-card">
                    <span class="stat-icon">🐕</span>
                    <span class="stat-number"><?php echo $total_mascotas; ?></span>
                    <span class="stat-label">Mascotas</span>
                </div>

                <div class="stat-card">
                    <span class="stat-icon">🏥</span>
                    <span class="stat-number"><?php echo $total_citas; ?></span>
                    <span class="stat-label">Citas Veterinarias</span>
                </div>

                <div class="stat-card">
                    <span class="stat-icon">📅</span>
                    <span class="stat-number"><?php echo $total_eventos; ?></span>
                    <span class="stat-label">Eventos Programados</span>
                </div>
            </section>

            <div class="secciones-perfil">
                <!-- Información Personal -->
                <section class="seccion-card">
                    <h2 class="titulo-seccion">👤 Información Personal</h2>

                    <?php if (isset($mensaje_exito)): ?>
                        <div class="mensaje-perfil mensaje-exito">
                            ✅ <?php echo $mensaje_exito; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($mensaje_error)): ?>
                        <div class="mensaje-perfil mensaje-error">
                            ❌ <?php echo $mensaje_error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errores)): ?>
                        <div class="mensaje-perfil mensaje-error">
                            <strong>Errores encontrados:</strong>
                            <ul class="lista-errores">
                                <?php foreach ($errores as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="formulario-perfil">
                        <div class="campo-perfil">
                            <label class="label-perfil">Nombre</label>
                            <input type="text" name="nombre_usuario"
                                value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" class="input-perfil"
                                required>
                        </div>

                        <div class="campo-perfil">
                            <label class="label-perfil">Apellido</label>
                            <input type="text" name="apellido_usuario"
                                value="<?php echo htmlspecialchars($usuario['apellido_usuario']); ?>"
                                class="input-perfil" required>
                        </div>

                        <div class="campo-perfil">
                            <label class="label-perfil">Email</label>
                            <input type="email" name="email_usuario"
                                value="<?php echo htmlspecialchars($usuario['email_usuario']); ?>" class="input-perfil"
                                required>
                        </div>

                        <div class="campo-perfil">
                            <label class="label-perfil">Teléfono</label>
                            <input type="tel" name="telefono_usuario"
                                value="<?php echo htmlspecialchars($usuario['telefono_usuario']); ?>"
                                class="input-perfil">
                        </div>

                        <div class="botones-perfil">
                            <button type="submit" name="actualizar_perfil" class="btn-perfil btn-primary">
                                💾 Actualizar Perfil
                            </button>
                            <button type="button" onclick="mostrarModalPassword()" class="btn-perfil btn-secondary">
                                🔒 Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Información de Veterinario (si aplica) -->
                <?php if ($es_veterinario && $info_veterinario): ?>
                    <section class="seccion-card">
                        <h2 class="titulo-seccion">🏥 Información Profesional</h2>

                        <div class="info-veterinario">
                            <h4>Datos como Veterinario</h4>
                            <div class="datos-veterinario">
                                <div class="dato-vet">
                                    <span>🏢</span>
                                    <strong>Clínica:</strong> <?php echo htmlspecialchars($info_veterinario['clinica']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span>🎓</span>
                                    <strong>Especialidad:</strong>
                                    <?php echo htmlspecialchars($info_veterinario['especialidad']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span>🕒</span>
                                    <strong>Horarios:</strong>
                                    <?php echo htmlspecialchars($info_veterinario['horarios_de_atencion']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span>✅</span>
                                    <strong>Estado:</strong>
                                    <?php echo $info_veterinario['certificado'] ? 'Certificado' : 'Pendiente'; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal para cambiar contraseña -->
    <div class="modal-password" id="modalPassword">
        <div class="modal-content">
            <button class="modal-close" onclick="cerrarModalPassword()">×</button>
            <h3 class="modal-title">🔒 Cambiar Contraseña</h3>

            <?php if (isset($mensaje_exito_password)): ?>
                <div class="mensaje-perfil mensaje-exito">
                    ✅ <?php echo $mensaje_exito_password; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($mensaje_error_password)): ?>
                <div class="mensaje-perfil mensaje-error">
                    ❌ <?php echo $mensaje_error_password; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores_password)): ?>
                <div class="mensaje-perfil mensaje-error">
                    <strong>Errores:</strong>
                    <ul class="lista-errores">
                        <?php foreach ($errores_password as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="campo-perfil">
                    <label class="label-perfil">Contraseña Actual</label>
                    <input type="password" name="password_actual" class="input-perfil" required>
                </div>

                <div class="campo-perfil">
                    <label class="label-perfil">Nueva Contraseña</label>
                    <input type="password" name="password_nueva" class="input-perfil" required minlength="6">
                </div>

                <div class="campo-perfil">
                    <label class="label-perfil">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmar" class="input-perfil" required minlength="6">
                </div>

                <div class="botones-perfil">
                    <button type="submit" name="cambiar_password" class="btn-perfil btn-primary">
                        🔒 Cambiar Contraseña
                    </button>
                    <button type="button" onclick="cerrarModalPassword()" class="btn-perfil btn-secondary">
                        ❌ Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <script src="js/scripts.js"></script>
    <script src="js/mi-perfil.js"></script>
</body>

</html>

<?php
// Cerrar conexión a la base de datos
if (isset($conexion)) {
    $conexion->close();
}