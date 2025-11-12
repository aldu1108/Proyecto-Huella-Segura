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
    <div class="avatar-container">
        <div class="avatar-perfil" id="avatarPerfil">
            <?php if (!empty($usuario['foto_usuario']) && $usuario['foto_usuario'] !== 'usuario-default.jpg'): ?>
                <img src="<?php echo htmlspecialchars($usuario['foto_usuario']); ?>" alt="Foto de perfil" id="imagenPerfil">
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" height="80px" viewBox="0 -960 960 960" width="80px" fill="#666666">
                    <path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"/>
                </svg>
            <?php endif; ?>
        </div>
        <button class="btn-cambiar-foto" onclick="document.getElementById('inputFotoPerfil').click()">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF">
                <path d="M480-260q75 0 127.5-52.5T660-440q0-75-52.5-127.5T480-620q-75 0-127.5 52.5T300-440q0 75 52.5 127.5T480-260Zm0-80q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM160-120q-33 0-56.5-23.5T80-200v-480q0-33 23.5-56.5T160-760h126l74-80h240l74 80h126q33 0 56.5 23.5T880-680v480q0 33-23.5 56.5T800-120H160Z"/>
            </svg>
            Cambiar foto
        </button>
        <input type="file" id="inputFotoPerfil" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;" onchange="subirFotoPerfil(this)">
    </div>
    
    <h1 class="nombre-perfil">
        <?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']); ?>
    </h1>
    <p class="email-perfil"><?php echo htmlspecialchars($usuario['email_usuario']); ?></p>

    <div class="badges-perfil">
        <?php if ($es_veterinario): ?>
            <span class="badge-usuario badge-veterinario">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B">
                    <path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Z"/>
                </svg>
                Veterinario Certificado
            </span>
        <?php endif; ?>
        <span class="badge-usuario badge-miembro">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39">
                <path d="M216-192v-72h528v72H216Zm0-120-50-289q-2 1-4.5 1H156q-25 0-42.5-17.5T96-660q0-25 17.5-42.5T156-720q25 0 42.5 17.5T216-660q0 7-1.5 13t-4.5 12l126 59 112-177q-13-8-20.5-21.63Q420-788.27 420-804q0-25 17.5-42.5T480-864q25 0 42.5 17.5T540-804q0 16-7.5 29.5T512-753l112 177 126-59q-3-6-4.5-12t-1.5-13q0-25 17.5-42.5T804-720q25 0 42.5 17.5T864-660q0 25-17.5 42.5T804-600h-5.5q-2.5 0-4.5-1l-50 289H216Z"/>
            </svg>
            Miembro desde 2025
        </span>
        <?php if ($total_mascotas > 0): ?>
            <span class="badge-usuario">
                Dueño de <?php echo $total_mascotas; ?> mascota<?php echo $total_mascotas > 1 ? 's' : ''; ?>
            </span>
        <?php endif; ?>
    </div>
</section>

            <!-- Estadísticas del usuario -->
            <section class="estadisticas-perfil">
                <div class="stat-card">
                    <span class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg></span>
                    <span class="stat-number"><?php echo $total_mascotas; ?></span>
                    <span class="stat-label">Mascotas</span>
                </div>

                <div class="stat-card">
                    <span class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg></span>
                    <span class="stat-number"><?php echo $total_citas; ?></span>
                    <span class="stat-label">Citas Veterinarias</span>
                </div>

                <div class="stat-card">
                    <span class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg></span>
                    <span class="stat-number"><?php echo $total_eventos; ?></span>
                    <span class="stat-label">Eventos Programados</span>
                </div>
            </section>

            <div class="secciones-perfil">
                <!-- Información Personal -->
                <section class="seccion-card">
                    <h2 class="titulo-seccion"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg> Información Personal</h2>

                    <?php if (isset($mensaje_exito)): ?>
                        <div class="mensaje-perfil mensaje-exito">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> <?php echo $mensaje_exito; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($mensaje_error)): ?>
                        <div class="mensaje-perfil mensaje-error">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m291-240-51-51 189-189-189-189 51-51 189 189 189-189 51 51-189 189 189 189-51 51-189-189-189 189Z"/></svg> <?php echo $mensaje_error; ?>
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
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M816-672v456q0 29.7-21.15 50.85Q773.7-144 744-144H216q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h456l144 144ZM480-252q45 0 76.5-31.5T588-360q0-45-31.5-76.5T480-468q-45 0-76.5 31.5T372-360q0 45 31.5 76.5T480-252ZM264-552h336v-144H264v144Z"/></svg> Actualizar Perfil
                            </button>
                            <button type="button" onclick="mostrarModalPassword()" class="btn-perfil btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Información de Veterinario (si aplica) -->
                <?php if ($es_veterinario && $info_veterinario): ?>
                    <section class="seccion-card">
                        <h2 class="titulo-seccion"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg> Información Profesional</h2>

                        <div class="info-veterinario">
                            <h4>Datos como Veterinario</h4>
                            <div class="datos-veterinario">
                                <div class="dato-vet">
                                    <span><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M432-288h96v-96h96v-96h-96v-96h-96v96h-96v96h96v96ZM192-144v-456l288-216 288 216v456H192Z"/></svg></span>
                                    <strong>Clínica:</strong> <?php echo htmlspecialchars($info_veterinario['clinica']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span>🎓</span>
                                    <strong>Especialidad:</strong>
                                    <?php echo htmlspecialchars($info_veterinario['especialidad']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg></span>
                                    <strong>Horarios:</strong>
                                    <?php echo htmlspecialchars($info_veterinario['horarios_de_atencion']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg></span>
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
            <h3 class="modal-title"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Cambiar Contraseña</h3>

            <?php if (isset($mensaje_exito_password)): ?>
                <div class="mensaje-perfil mensaje-exito">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> <?php echo $mensaje_exito_password; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($mensaje_error_password)): ?>
                <div class="mensaje-perfil mensaje-error">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M432-288h96v-96h96v-96h-96v-96h-96v96h-96v96h96v96ZM192-144v-456l288-216 288 216v456H192Z"/></svg> <?php echo $mensaje_error_password; ?>
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
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Cambiar Contraseña
                    </button>
                    <button type="button" onclick="cerrarModalPassword()" class="btn-perfil btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M432-288h96v-96h96v-96h-96v-96h-96v96h-96v96h96v96ZM192-144v-456l288-216 288 216v456H192Z"/></svg> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Navegación inferior -->
   <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'"  title="Adopciones"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Zm0-108q96-86 158-147.5t98-107q36-45.5 50-81t14-70.5q0-60-40-100t-100-40q-47 0-87 26.5T518-680h-76q-15-41-55-67.5T300-774q-60 0-100 40t-40 100q0 35 14 70.5t50 81q36 45.5 98 107T480-228Zm0-273Z"/></svg></button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'"  title="Mascotas perdidas"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q146 0 255.5 91.5T872-559h-82q-19-73-68.5-130.5T600-776v16q0 33-23.5 56.5T520-680h-80v80q0 17-11.5 28.5T400-560h-80v80h80v120h-40L168-552q-3 18-5.5 36t-2.5 36q0 131 92 225t228 95v80Zm364-20L716-228q-21 12-45 20t-51 8q-75 0-127.5-52.5T440-380q0-75 52.5-127.5T620-560q75 0 127.5 52.5T800-380q0 27-8 51t-20 45l128 128-56 56ZM620-280q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Z"/></svg></button>
        <button class="nav-btn" onclick="window.location.href='index.php'"  title="Inicio"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/></svg></button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'"  title="Comunidad"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z"/></svg></button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'"  title="Veterinaria"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-254 330-104q-23 23-56 23t-56-23L104-218q-23-23-23-56t23-56l150-150-150-150q-23-23-23-56t23-56l114-114q23-23 56-23t56 23l150 150 150-150q23-23 56-23t56 23l114 114q23 23 23 56t-23 56L706-480l150 150q23 23 23 56t-23 56L742-104q-23 23-56 23t-56-23L480-254Zm0-266q17 0 28.5-11.5T520-560q0-17-11.5-28.5T480-600q-17 0-28.5 11.5T440-560q0 17 11.5 28.5T480-520Zm-170-16 114-114-150-150-114 114 150 150Zm90 96q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440Zm80 80q17 0 28.5-11.5T520-400q0-17-11.5-28.5T480-440q-17 0-28.5 11.5T440-400q0 17 11.5 28.5T480-360Zm80-80q17 0 28.5-11.5T600-480q0-17-11.5-28.5T560-520q-17 0-28.5 11.5T520-480q0 17 11.5 28.5T560-440Zm-24 130 150 150 114-114-150-150-114 114ZM339-621Zm282 282Z"/></svg></button>
    </nav>

    <script src="js/scripts.js"></script>
    <script src="js/notificaciones.js"></script>
    <script src="js/mi-perfil.js"></script>
</body>

</html>

<?php
// Cerrar conexión a la base de datos
if (isset($conexion)) {
    $conexion->close();
}