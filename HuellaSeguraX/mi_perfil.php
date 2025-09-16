<?php
include_once('config/conexion.php');
include_once('includes/funciones.php');
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
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

$consulta_eventos = "SELECT COUNT(*) as total FROM eventos WHERE id_usuario = $usuario_id";
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
    <style>
        /* Estilos específicos para el perfil */
        .perfil-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .perfil-header {
            background: linear-gradient(135deg, #D35400, #E67E22);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .perfil-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .avatar-perfil {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 20px;
            border: 4px solid rgba(255,255,255,0.3);
        }
        
        .nombre-perfil {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .email-perfil {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .badges-perfil {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .badge-usuario {
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-veterinario {
            background: #27AE60;
            color: white;
        }
        
        .badge-miembro {
            background: #3498DB;
            color: white;
        }
        
        .estadisticas-perfil {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 36px;
            margin-bottom: 15px;
            display: block;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #D35400;
            display: block;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .secciones-perfil {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .seccion-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .titulo-seccion {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .formulario-perfil {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .campo-perfil {
            display: flex;
            flex-direction: column;
        }
        
        .label-perfil {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .input-perfil {
            padding: 14px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .input-perfil:focus {
            border-color: #D35400;
        }
        
        .input-perfil:disabled {
            background: #f8f9fa;
            color: #666;
            cursor: not-allowed;
        }
        
        .botones-perfil {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .btn-perfil {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #D35400;
            color: white;
        }
        
        .btn-primary:hover {
            background: #B8450E;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6C757D;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5A6268;
        }
        
        .btn-danger {
            background: #E74C3C;
            color: white;
        }
        
        .btn-danger:hover {
            background: #C0392B;
        }
        
        .mensaje-perfil {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .mensaje-exito {
            background: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }
        
        .mensaje-error {
            background: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }
        
        .lista-errores {
            margin: 0;
            padding-left: 20px;
        }
        
        .info-veterinario {
            background: #E8F5E8;
            border: 1px solid #C3E6CB;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }
        
        .info-veterinario h4 {
            color: #27AE60;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .datos-veterinario {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .dato-vet {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #2C5F41;
        }
        
        .configuraciones-adicionales {
            border-top: 1px solid #e8e8e8;
            padding-top: 25px;
            margin-top: 25px;
        }
        
        .opcion-config {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .opcion-config:last-child {
            border-bottom: none;
        }
        
        .info-opcion {
            flex: 1;
        }
        
        .titulo-opcion {
            font-size: 16px;
            color: #333;
            margin-bottom: 4px;
            font-weight: 500;
        }
        
        .desc-opcion {
            font-size: 14px;
            color: #666;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #D35400;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        /* Modal para cambiar contraseña */
        .modal-password {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }
        
        .modal-password.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
        }
        
        .modal-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .perfil-container {
                padding: 0 15px;
            }
            
            .perfil-header {
                padding: 30px 20px;
            }
            
            .avatar-perfil {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
            
            .nombre-perfil {
                font-size: 22px;
            }
            
            .estadisticas-perfil {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .formulario-perfil {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .botones-perfil {
                flex-direction: column;
            }
            
            .btn-perfil {
                justify-content: center;
            }
            
            .seccion-card {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .estadisticas-perfil {
                grid-template-columns: 1fr;
            }
            
            .badges-perfil {
                flex-direction: column;
                align-items: center;
            }
            
            .datos-veterinario {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                <h1 class="nombre-perfil"><?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']); ?></h1>
                <p class="email-perfil"><?php echo htmlspecialchars($usuario['email_usuario']); ?></p>
                
                <div class="badges-perfil">
                    <?php if ($es_veterinario): ?>
                        <span class="badge-usuario badge-veterinario">🏥 Veterinario Certificado</span>
                    <?php endif; ?>
                    <span class="badge-usuario badge-miembro">👑 Miembro desde 2025</span>
                    <?php if ($total_mascotas > 0): ?>
                        <span class="badge-usuario">🐾 Dueño de <?php echo $total_mascotas; ?> mascota<?php echo $total_mascotas > 1 ? 's' : ''; ?></span>
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
                
                <div class="stat-card">
                    <span class="stat-icon">⭐</span>
                    <span class="stat-number">4.8</span>
                    <span class="stat-label">Puntuación</span>
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
                            <input type="text" 
                                   name="nombre_usuario" 
                                   value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" 
                                   class="input-perfil" 
                                   required>
                        </div>
                        
                        <div class="campo-perfil">
                            <label class="label-perfil">Apellido</label>
                            <input type="text" 
                                   name="apellido_usuario" 
                                   value="<?php echo htmlspecialchars($usuario['apellido_usuario']); ?>" 
                                   class="input-perfil" 
                                   required>
                        </div>
                        
                        <div class="campo-perfil">
                            <label class="label-perfil">Email</label>
                            <input type="email" 
                                   name="email_usuario" 
                                   value="<?php echo htmlspecialchars($usuario['email_usuario']); ?>" 
                                   class="input-perfil" 
                                   required>
                        </div>
                        
                        <div class="campo-perfil">
                            <label class="label-perfil">Teléfono</label>
                            <input type="tel" 
                                   name="telefono_usuario" 
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
                                    <strong>Especialidad:</strong> <?php echo htmlspecialchars($info_veterinario['especialidad']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span>🕒</span>
                                    <strong>Horarios:</strong> <?php echo htmlspecialchars($info_veterinario['horarios_de_atencion']); ?>
                                </div>
                                <div class="dato-vet">
                                    <span>✅</span>
                                    <strong>Estado:</strong> <?php echo $info_veterinario['certificado'] ? 'Certificado' : 'Pendiente'; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Configuraciones -->
                <section class="seccion-card">
                    <h2 class="titulo-seccion">⚙️ Configuraciones</h2>
                    
                    <div class="configuraciones-adicionales">
                        <div class="opcion-config">
                            <div class="info-opcion">
                                <div class="titulo-opcion">Notificaciones por Email</div>
                                <div class="desc-opcion">Recibir recordatorios y alertas por correo</div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="opcion-config">
                            <div class="info-opcion">
                                <div class="titulo-opcion">Perfil Público</div>
                                <div class="desc-opcion">Permitir que otros usuarios vean tu perfil</div>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="opcion-config">
                            <div class="info-opcion">
                                <div class="titulo-opcion">Recordatorios de Citas</div>
                                <div class="desc-opcion">Notificaciones push para citas veterinarias</div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="opcion-config">
                            <div class="info-opcion">
                                <div class="titulo-opcion">Compartir Ubicación</div>
                                <div class="desc-opcion">Para encontrar veterinarios cercanos</div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="botones-perfil">
                        <button type="button" class="btn-perfil btn-secondary" onclick="window.location.href='mis-mascotas.php'">
                            🐕 Gestionar Mascotas
                        </button>
                        <button type="button" class="btn-perfil btn-secondary" onclick="window.location.href='veterinaria.php'">
                            📅 Mis Citas
                        </button>
                        <button type="button" class="btn-perfil btn-danger" onclick="confirmarCerrarSesion()">
                            🚪 Cerrar Sesión
                        </button>
                    </div>
                </section>
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
    <script>
        // Funcionalidad del modal de contraseña
        function mostrarModalPassword() {
            document.getElementById('modalPassword').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function cerrarModalPassword() {
            document.getElementById('modalPassword').classList.remove('show');
            document.body.style.overflow = '';
            
            // Limpiar formulario
            const form = document.querySelector('#modalPassword form');
            if (form) {
                form.reset();
            }
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('modalPassword').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalPassword();
            }
        });
        
        // Confirmar cerrar sesión
        function confirmarCerrarSesion() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                window.location.href = 'logout.php';
            }
        }
        
        // Validación en tiempo real para contraseñas
        document.addEventListener('DOMContentLoaded', function() {
            const passwordNueva = document.querySelector('input[name="password_nueva"]');
            const passwordConfirmar = document.querySelector('input[name="password_confirmar"]');
            
            if (passwordNueva && passwordConfirmar) {
                function validarPasswords() {
                    if (passwordNueva.value && passwordConfirmar.value) {
                        if (passwordNueva.value === passwordConfirmar.value) {
                            passwordConfirmar.style.borderColor = '#27AE60';
                        } else {
                            passwordConfirmar.style.borderColor = '#E74C3C';
                        }
                    } else {
                        passwordConfirmar.style.borderColor = '#e8e8e8';
                    }
                }
                
                passwordNueva.addEventListener('input', validarPasswords);
                passwordConfirmar.addEventListener('input', validarPasswords);
            }
        });
        
        // Animaciones de entrada
        function animarElementos() {
            const elementos = document.querySelectorAll('.stat-card, .seccion-card');
            elementos.forEach((elemento, index) => {
                elemento.style.opacity = '0';
                elemento.style.transform = 'translateY(20px)';
                elemento.style.transition = `all 0.3s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    elemento.style.opacity = '1';
                    elemento.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }
        
        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }
        
        // Validación del formulario de perfil
        function validarFormularioPerfil(form) {
            const inputs = form.querySelectorAll('input[required]');
            let esValido = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#E74C3C';
                    esValido = false;
                } else {
                    input.style.borderColor = '#e8e8e8';
                }
                
                // Validación específica de email
                if (input.type === 'email' && input.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value)) {
                        input.style.borderColor = '#E74C3C';
                        esValido = false;
                    }
                }
            });
            
            return esValido;
        }
        
        // Interceptar envío de formularios para validación
        document.addEventListener('DOMContentLoaded', function() {
            const formularios = document.querySelectorAll('form');
            
            formularios.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!validarFormularioPerfil(this)) {
                        e.preventDefault();
                        mostrarMensaje('Por favor, completa todos los campos correctamente', 'error');
                    }
                });
            });
            
            // Ejecutar animaciones
            setTimeout(animarElementos, 300);
            
            // Si hay errores de contraseña, mostrar modal automáticamente
            <?php if (!empty($errores_password) || isset($mensaje_error_password) || isset($mensaje_exito_password)): ?>
                mostrarModalPassword();
            <?php endif; ?>
        });
        
        // Función para mostrar mensajes temporales
        function mostrarMensaje(texto, tipo = 'info', duracion = 4000) {
            const mensaje = document.createElement('div');
            mensaje.className = `mensaje-perfil mensaje-${tipo === 'error' ? 'error' : 'exito'}`;
            mensaje.innerHTML = (tipo === 'error' ? '❌ ' : '✅ ') + texto;
            mensaje.style.position = 'fixed';
            mensaje.style.top = '80px';
            mensaje.style.right = '20px';
            mensaje.style.zIndex = '10000';
            mensaje.style.minWidth = '300px';
            mensaje.style.animation = 'slideIn 0.3s ease';
            
            document.body.appendChild(mensaje);
            
            setTimeout(() => {
                if (mensaje.parentElement) {
                    mensaje.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => mensaje.remove(), 300);
                }
            }, duracion);
        }
        
        // Funcionalidad para los switches
        document.addEventListener('DOMContentLoaded', function() {
            const switches = document.querySelectorAll('.switch input');
            
            switches.forEach(switchElement => {
                switchElement.addEventListener('change', function() {
                    const opcion = this.closest('.opcion-config');
                    const titulo = opcion.querySelector('.titulo-opcion').textContent;
                    
                    // Aquí podrías enviar la configuración al servidor via AJAX
                    console.log(`${titulo}: ${this.checked ? 'Activado' : 'Desactivado'}`);
                    
                    // Mostrar mensaje de confirmación
                    mostrarMensaje(`${titulo} ${this.checked ? 'activado' : 'desactivado'}`, 'exito', 2000);
                });
            });
        });
        
        // Función para copiar ID de usuario al portapapeles
        function copiarIDUsuario() {
            const userId = '<?php echo $usuario_id; ?>';
            navigator.clipboard.writeText(userId).then(() => {
                mostrarMensaje('ID de usuario copiado al portapapeles', 'exito', 2000);
            });
        }
        
        console.log('Perfil de usuario cargado correctamente - Huella Segura v1.0');
    </script>
</body>
</html>

<?php
// Cerrar conexión a la base de datos
if (isset($conexion)) {
    $conexion->close();
}
?>