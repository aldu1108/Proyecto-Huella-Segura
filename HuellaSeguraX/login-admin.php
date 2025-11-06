<?php
include_once('config/conexion.php');
session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires'); // O tu zona horaria

$mensaje_error = "";

// Verificar si ya hay sesión de admin activa
if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true) {
    header("Location: panel-admin.php");
    exit();
}

// Credenciales de administrador hardcodeadas (simple y seguro para demo)
const ADMIN_EMAIL = "admin@huellasegura.com";
const ADMIN_PASSWORD = "admin123";

// Procesar login de administrador
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    
    if (!empty($email) && !empty($contraseña)) {
        // Verificar credenciales de admin
        if ($email === ADMIN_EMAIL && $contraseña === ADMIN_PASSWORD) {
            // Crear sesión de administrador
            $_SESSION['usuario_id'] = 999; // ID especial para admin
            $_SESSION['usuario_nombre'] = 'Administrador';
            $_SESSION['usuario_apellido'] = 'Sistema';
            $_SESSION['es_admin'] = true;
            $_SESSION['hora_login'] = date('H:i:s');
            
            header("Location: panel-admin.php");
            exit();
        } else {
            $mensaje_error = "Credenciales de administrador incorrectas";
        }
    } else {
        $mensaje_error = "Por favor complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/login-admin.css">
</head>
<body class="admin-login-body" style="background: url('imagenes/fondo-login.png') no-repeat center center fixed; background-size: cover;">
    <!-- Header simple -->
    <div class="login-header">
        <h1><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M480-96q-135-33-223.5-152.84Q168-368.69 168-515v-229l312-120 312 120v229q0 146.31-88.5 266.16Q615-129 480-96Z"/></svg> Panel Administrativo</h1>
        <p class="login-subtitle">Acceso restringido para administradores</p>
    </div>

    <!-- Contenedor de login admin -->
    <div class="login-container admin-container">
        <h2 class="login-title">Acceso Administrativo</h2>
        <p class="login-welcome">Ingrese sus credenciales de administrador</p>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="input-group">
                <span class="input-icon">👤</span>
                <input type="email" name="email" class="login-input" placeholder="Email de administrador" required>
            </div>
            
            <div class="input-group">
                <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg></span>
                <input type="password" name="contraseña" class="login-input" placeholder="Contraseña de administrador" required>
            </div>
            
            <button type="submit" class="btn-login admin-btn"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M480-96q-135-33-223.5-152.84Q168-368.69 168-515v-229l312-120 312 120v229q0 146.31-88.5 266.16Q615-129 480-96Z"/></svg> Acceder al Panel</button>
        </form>
        
        <div class="texto-credencial">
            <span>Credenciales para prueba</span>
        </div>
        
        <!-- Credenciales de prueba (solo para demo) -->
        <div class="demo-credentials">
            <p><strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M168-192q-29 0-50.5-21.5T96-264v-432q0-29 21.5-50.5T168-768h624q30 0 51 21.5t21 50.5v432q0 29-21 50.5T792-192H168Zm312-240 312-179v-85L480-517 168-696v85l312 179Z"/></svg> Email:</strong> admin@huellasegura.com</p>
            <p><strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M288-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 120q-100 0-170-70T48-480q0-100 70-170t170-70q78 0 140.5 46.5T516-552h324l72 72-132 159-84-87-72 72-72-72h-36q-24 75-86.93 121.5Q366.15-240 288-240Z"/></svg> Contraseña:</strong> admin123</p>
        </div>
        
        <div class="admin-links">
            <a href="login.php">← Volver al login de usuarios</a>
        </div>
        
        <div class="admin-warning">
            <p><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> <strong>Acceso Restringido</strong></p>
            <p>Este panel es solo para administradores autorizados del sistema.</p>
        </div>
    </div>

    <script src="js/login-admin.js"></script>
</body>
</html>