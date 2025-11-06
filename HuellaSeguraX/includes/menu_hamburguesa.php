<?php
// Componente reutilizable del menú hamburguesa
// Archivo: includes/menu-hamburguesa.php

// Verificar si hay sesión activa para mostrar opciones apropiadas
if (!session_id()) {
    session_start();
}

$rol_usuario = $_SESSION['rol'] ?? null;
$usuario_logueado = ($rol_usuario && $rol_usuario != 'demo' && isset($_SESSION['usuario_id']));
$es_demo = ($rol_usuario == 'demo');
$nombre_usuario = $_SESSION['usuario_nombre'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;

// Obtener contador de notificaciones no leídas (solo para usuarios autenticados)
$notificaciones_no_leidas = 0;
if ($usuario_logueado && isset($conexion)) {
    $consulta_notif = "SELECT COUNT(*) as total FROM notificaciones 
                       WHERE id_usuario_destino = ? AND leida = 0";
    $stmt_notif = $conexion->prepare($consulta_notif);
    if ($stmt_notif) {
        $stmt_notif->bind_param("i", $usuario_id);
        $stmt_notif->execute();
        $resultado_notif = $stmt_notif->get_result();
        if ($resultado_notif) {
            $notificaciones_no_leidas = $resultado_notif->fetch_assoc()['total'];
        }
        $stmt_notif->close();
    }
}
?>

<header class="cabecera-principal">
    <nav class="navegacion-principal">
        <button class="boton-menu-hamburguesa" id="menuHamburguesa">☰</button>
        <div class="logo-contenedor">
            <h1 class="logo-texto">Huella Segura</h1>
        </div>
        <div class="iconos-derecha">
            <?php if ($usuario_logueado): ?>
                <button class="boton-notificaciones" onclick="toggleNotificaciones()" title="Notificaciones">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#D35400"><path d="M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160ZM480-80q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80Z"/></svg>
                    <?php if ($notificaciones_no_leidas > 0): ?>
                        <span class="badge-notificaciones"><?php echo $notificaciones_no_leidas; ?></span>
                    <?php endif; ?>
                </button>
            <?php else: ?>
                <button class="boton-notificaciones boton-notif-disabled" title="Inicia sesión para ver notificaciones">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#D35400"><path d="M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160ZM480-80q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80Z"/></svg>
                </button>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Panel de notificaciones -->
    <?php if ($usuario_logueado): ?>
        <div class="panel-notificaciones" id="panelNotificaciones">
            <div class="header-notificaciones">
                <h3>Notificaciones</h3>
                <button class="btn-marcar-leidas" onclick="marcarTodasLeidas()">Marcar todas como leídas</button>
            </div>
            <div class="body-notificaciones" id="listaNotificaciones">
                <div class="loading-notificaciones">
                    <div class="spinner"></div>
                    <p>Cargando notificaciones...</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Menú hamburguesa desplegable -->
    <div class="menu-lateral" id="menuLateral">
        <div class="encabezado-menu">
            <?php if ($usuario_logueado): ?>
                <div class="info-usuario-menu">
                    <div class="avatar-usuario"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg></div>
                    <div class="datos-usuario">
                        <span class="nombre-usuario"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                        <span class="estado-usuario">En línea</span>
                    </div>
                </div>
            <?php elseif ($es_demo): ?>
                <div class="info-usuario-menu">
                    <div class="avatar-usuario"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg></div>
                    <div class="datos-usuario">
                        <span class="nombre-usuario">Usuario Demo</span>
                        <span class="estado-usuario">Modo solo lectura</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="opciones-menu">
            <?php if ($usuario_logueado && $rol_usuario != 'veterinario'): ?>
                <!-- Opciones para usuarios autenticados -->
                <a href="index.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/></svg></span>
                    <span class="texto-menu">Inicio</span>
                </a>
                <a href="mis-mascotas.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M180-475q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm180-160q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm240 0q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm180 160q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM266-75q-45 0-75.5-34.5T160-191q0-52 35.5-91t70.5-77q29-31 50-67.5t50-68.5q22-26 51-43t63-17q34 0 63 16t51 42q28 32 49.5 69t50.5 69q35 38 70.5 77t35.5 91q0 47-30.5 81.5T694-75q-54 0-107-9t-107-9q-54 0-107 9t-107 9Z"/></svg></span>
                    <span class="texto-menu">Mis Mascotas</span>
                </a>
                <a href="mascotas-perdidas.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg></span>
                    <span class="texto-menu">Mascotas Perdidas</span>
                </a>
                <a href="adopciones.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Zm0-108q96-86 158-147.5t98-107q36-45.5 50-81t14-70.5q0-60-40-100t-100-40q-47 0-87 26.5T518-680h-76q-15-41-55-67.5T300-774q-60 0-100 40t-40 100q0 35 14 70.5t50 81q36 45.5 98 107T480-228Zm0-273Z"/></svg></span>
                    <span class="texto-menu">Adopciones</span>
                </a>
                <a href="comunidad.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z"/></svg></span>
                    <span class="texto-menu">Comunidad</span>
                </a>
                <a href="veterinaria.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M480-254 330-104q-23 23-56 23t-56-23L104-218q-23-23-23-56t23-56l150-150-150-150q-23-23-23-56t23-56l114-114q23-23 56-23t56 23l150 150 150-150q23-23 56-23t56 23l114 114q23 23 23 56t-23 56L706-480l150 150q23 23 23 56t-23 56L742-104q-23 23-56 23t-56-23L480-254Zm0-266q17 0 28.5-11.5T520-560q0-17-11.5-28.5T480-600q-17 0-28.5 11.5T440-560q0 17 11.5 28.5T480-520Zm-170-16 114-114-150-150-114 114 150 150Zm90 96q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440Zm80 80q17 0 28.5-11.5T520-400q0-17-11.5-28.5T480-440q-17 0-28.5 11.5T440-400q0 17 11.5 28.5T480-360Zm80-80q17 0 28.5-11.5T600-480q0-17-11.5-28.5T560-520q-17 0-28.5 11.5T520-480q0 17 11.5 28.5T560-440Zm-24 130 150 150 114-114-150-150-114 114ZM339-621Zm282 282Z"/></svg></span>
                    <span class="texto-menu">Veterinaria</span>
                </a>

                <div class="separador-menu"></div>

                <a href="mi-perfil.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg></span>
                    <span class="texto-menu">Mi Perfil</span>
                </a>

                <div class="separador-menu"></div>

                <a href="logout.php" class="opcion-menu opcion-logout">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M120-120v-80h80v-560q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v560h80v80H120Zm160-80h400v-560H280v560Zm120-240q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440ZM280-760v560-560Z"/></svg></span>
                    <span class="texto-menu">Cerrar Sesión</span>
                </a>
            <?php elseif ($rol_usuario == 'veterinario'): ?>
                <a href="comunidad.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z"/></svg></span>
                    <span class="texto-menu">Comunidad</span>
                </a>
                <a href="veterinaria.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M480-254 330-104q-23 23-56 23t-56-23L104-218q-23-23-23-56t23-56l150-150-150-150q-23-23-23-56t23-56l114-114q23-23 56-23t56 23l150 150 150-150q23-23 56-23t56 23l114 114q23 23 23 56t-23 56L706-480l150 150q23 23 23 56t-23 56L742-104q-23 23-56 23t-56-23L480-254Zm0-266q17 0 28.5-11.5T520-560q0-17-11.5-28.5T480-600q-17 0-28.5 11.5T440-560q0 17 11.5 28.5T480-520Zm-170-16 114-114-150-150-114 114 150 150Zm90 96q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440Zm80 80q17 0 28.5-11.5T520-400q0-17-11.5-28.5T480-440q-17 0-28.5 11.5T440-400q0 17 11.5 28.5T480-360Zm80-80q17 0 28.5-11.5T600-480q0-17-11.5-28.5T560-520q-17 0-28.5 11.5T520-480q0 17 11.5 28.5T560-440Zm-24 130 150 150 114-114-150-150-114 114ZM339-621Zm282 282Z"/></svg></span>
                    <span class="texto-menu">Veterinaria</span>
                </a>

                <div class="separador-menu"></div>

                <a href="mi-perfil.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg></span>
                    <span class="texto-menu">Mi Perfil</span>
                </a>

                <div class="separador-menu"></div>

                <a href="logout.php" class="opcion-menu opcion-logout">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M120-120v-80h80v-560q0-33 23.5-56.5T280-840h400q33 0 56.5 23.5T760-760v560h80v80H120Zm160-80h400v-560H280v560Zm120-240q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440ZM280-760v560-560Z"/></svg></span>
                    <span class="texto-menu">Cerrar Sesión</span>
                </a>
            <?php else: ?>
                <!-- Opciones para usuarios no autenticados -->
                <a href="login.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M280-400q-33 0-56.5-23.5T200-480q0-33 23.5-56.5T280-560q33 0 56.5 23.5T360-480q0 33-23.5 56.5T280-400Zm0 160q-100 0-170-70T40-480q0-100 70-170t170-70q67 0 121.5 33t86.5 87h352l120 120-180 180-80-60-80 60-85-60h-47q-32 54-86.5 87T280-240Zm0-80q56 0 98.5-34t56.5-86h125l58 41 82-61 71 55 75-75-40-40H435q-14-52-56.5-86T280-640q-66 0-113 47t-47 113q0 66 47 113t113 47Z"/></svg></span>
                    <span class="texto-menu">Iniciar Sesión</span>
                </a>
                <a href="registro.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/></svg></span>
                    <span class="texto-menu">Registrarse</span>
                </a>

                <div class="separador-menu"></div>

                               <a href="index.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/></svg></span>
                    <span class="texto-menu">Inicio</span>
                </a>
                <a href="mascotas-perdidas.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg></span>
                    <span class="texto-menu">Mascotas Perdidas</span>
                </a>
                <a href="adopciones.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Zm0-108q96-86 158-147.5t98-107q36-45.5 50-81t14-70.5q0-60-40-100t-100-40q-47 0-87 26.5T518-680h-76q-15-41-55-67.5T300-774q-60 0-100 40t-40 100q0 35 14 70.5t50 81q36 45.5 98 107T480-228Zm0-273Z"/></svg></span>
                    <span class="texto-menu">Adopciones</span>
                </a>
                <a href="comunidad.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z"/></svg></span>
                    <span class="texto-menu">Comunidad</span>
                </a>
                <a href="veterinaria.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M480-254 330-104q-23 23-56 23t-56-23L104-218q-23-23-23-56t23-56l150-150-150-150q-23-23-23-56t23-56l114-114q23-23 56-23t56 23l150 150 150-150q23-23 56-23t56 23l114 114q23 23 23 56t-23 56L706-480l150 150q23 23 23 56t-23 56L742-104q-23 23-56 23t-56-23L480-254Zm0-266q17 0 28.5-11.5T520-560q0-17-11.5-28.5T480-600q-17 0-28.5 11.5T440-560q0 17 11.5 28.5T480-520Zm-170-16 114-114-150-150-114 114 150 150Zm90 96q17 0 28.5-11.5T440-480q0-17-11.5-28.5T400-520q-17 0-28.5 11.5T360-480q0 17 11.5 28.5T400-440Zm80 80q17 0 28.5-11.5T520-400q0-17-11.5-28.5T480-440q-17 0-28.5 11.5T440-400q0 17 11.5 28.5T480-360Zm80-80q17 0 28.5-11.5T600-480q0-17-11.5-28.5T560-520q-17 0-28.5 11.5T520-480q0 17 11.5 28.5T560-440Zm-24 130 150 150 114-114-150-150-114 114ZM339-621Zm282 282Z"/></svg></span>
                    <span class="texto-menu">Veterinaria</span>
                </a>
                <a href="acerca.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M440-400v-360h80v360h-80Zm0 200v-80h80v80h-80Z"/></svg></span>
                    <span class="texto-menu">Acerca de</span>
                </a>
                <a href="contacto.php" class="opcion-menu">
                    <span class="icono-menu"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d35400"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280L160-640v400h640v-400L480-440Zm0-80 320-200H160l320 200ZM160-640v-80 480-400Z"/></svg></span>
                    <span class="texto-menu">Contacto</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Footer del menú -->
        <div class="footer-menu">
            <div class="version-app">
                <small>Huella Segura v1.0</small>
            </div>
        </div>
    </div>
    <!-- Overlay para cerrar el menú -->
    <div class="overlay-menu" id="overlayMenu"></div>
</header>

<style>
    /* Estilos para el menú hamburguesa - Ajustado */
    .cabecera-principal {
        background: linear-gradient(135deg, #f8d43c, #f8d63cff);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        height: 60px;
        /* Altura fija reducida */
    }

    .navegacion-principal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 1rem;
        /* Padding reducido */
        max-width: 1200px;
        margin: 0 auto;
        height: 100%;
    }

    .boton-menu-hamburguesa {
        background: none;
        border: none;
        color: #333;
        /* Color negro para las líneas del menú */
        font-size: 1.3rem;
        /* Tamaño ligeramente reducido */
        cursor: pointer;
        padding: 0.4rem;
        border-radius: 6px;
        transition: background-color 0.3s;
        font-weight: bold;
        /* Para hacer las líneas más visibles */
    }

    .boton-menu-hamburguesa:hover {
        background-color: rgba(0, 0, 0, 0.1);
        /* Hover más sutil */
    }

    .logo-contenedor {
        text-align: center;
        flex: 1;
    }

    .logo-texto {
        font-size: 1.6rem;
        /* Tamaño reducido proporcionalmente */
        margin: 0;
        font-weight: bold;
        color: #d35400;
        /* Color naranja para el logo */
    }

    /* Botón de notificaciones */
    .iconos-derecha {
        display: flex;
        gap: 0.5rem;
        position: relative;
    }

    .boton-notificaciones {
        background: none;
        border: none;
        color: #333;
        font-size: 1.3rem;
        cursor: pointer;
        padding: 0.4rem;
        border-radius: 6px;
        transition: all 0.3s;
        position: relative;
    }

    .boton-notificaciones:hover:not(.boton-notif-disabled) {
        background-color: rgba(0, 0, 0, 0.1);
        transform: scale(1.1);
    }

    .boton-notif-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .badge-notificaciones {
        position: absolute;
        top: -2px;
        right: -2px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    /* Panel de notificaciones */
    .panel-notificaciones {
        position: absolute;
        top: 65px;
        right: 10px;
        width: 380px;
        max-height: 500px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        z-index: 1100;
        animation: slideDown 0.3s ease;
    }

    .panel-notificaciones.activo {
        display: flex;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .header-notificaciones {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-notificaciones h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #333;
    }

    .btn-marcar-leidas {
        background: none;
        border: none;
        color: #667eea;
        font-size: 12px;
        cursor: pointer;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-marcar-leidas:hover {
        background: #f0f0f0;
    }

    .body-notificaciones {
        overflow-y: auto;
        max-height: 420px;
    }

    .notificacion-item {
        padding: 14px 20px;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .notificacion-item:hover {
        background: #f9fafb;
    }

    .notificacion-item.no-leida {
        background: #eff6ff;
        position: relative;
    }

    .notificacion-item.no-leida::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        background: #3b82f6;
        border-radius: 50%;
    }

    .notificacion-avatar {
        font-size: 28px;
        flex-shrink: 0;
    }

    .notificacion-contenido {
        flex: 1;
    }

    .notificacion-texto {
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .notificacion-texto strong {
        font-weight: 600;
    }

    .notificacion-tiempo {
        color: #9ca3af;
        font-size: 12px;
    }

    .empty-notificaciones {
        padding: 60px 20px;
        text-align: center;
        color: #9ca3af;
    }

    .empty-notificaciones-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .loading-notificaciones {
        padding: 40px 20px;
        text-align: center;
        color: #9ca3af;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Menú lateral - Ajustado al estilo original */
    .menu-lateral {
        position: fixed;
        top: 0;
        left: -300px;
        width: 300px;
        height: 100vh;
        background: white;
        z-index: 2000;
        transition: left 0.3s ease;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
    }

    .menu-lateral.activo {
        left: 0;
    }

    .overlay-menu {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1500;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }



    /* Encabezado del menú - Estilo original */
    .encabezado-menu {
        background: #d35400;
        color: white;
        padding: 1.5rem 1rem;
    }

    .info-usuario-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .avatar-usuario {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .datos-usuario {
        display: flex;
        flex-direction: column;
    }

    .nombre-usuario {
        font-weight: bold;
        font-size: 1.1rem;
    }

    .estado-usuario {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    /* Opciones del menú - Estilo más simple y limpio */
    .opciones-menu {
        flex: 1;
        padding: 0.5rem 0;
        overflow-y: auto;
    }

    .opcion-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.8rem 1.5rem;
        color: #333;
        text-decoration: none;
        transition: all 0.3s;
        border-left: 3px solid transparent;
    }

    .opcion-menu:hover {
        background-color: #fff3cd;
        border-left-color: #f1c40f;
        color: #d35400;
    }

    .opcion-menu.opcion-logout:hover {
        background-color: #f8d7da;
        border-left-color: #dc3545;
        color: #dc3545;
    }

    .icono-menu {
        font-size: 1.1rem;
        width: 22px;
        text-align: center;
    }

    .texto-menu {
        font-size: 0.95rem;
        font-weight: 500;
    }

    .separador-menu {
        height: 1px;
        background-color: #e9ecef;
        margin: 0.3rem 1rem;
    }

    /* Footer del menú - Más compacto */
    .footer-menu {
        padding: 0.8rem;
        border-top: 1px solid #e9ecef;
        background-color: #f8f9fa;
    }

    .version-app {
        text-align: center;
        color: #6c757d;
        margin-bottom: 0.4rem;
    }

    .enlaces-rapidos {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .enlaces-rapidos a {
        color: #6c757d;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .enlaces-rapidos a:hover {
        color: #d35400;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .menu-lateral {
            width: 280px;
            left: -280px;
        }

        .navegacion-principal {
            padding: 0.6rem 0.8rem;
            /* Padding aún más reducido en móvil */
        }

        .logo-texto {
            font-size: 1.4rem;
        }

        .cabecera-principal {
            height: 55px;
            /* Altura ligeramente menor en móvil */
        }
    }
</style>