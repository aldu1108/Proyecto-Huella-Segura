<?php
// Componente reutilizable del menú hamburguesa
// Archivo: includes/menu-hamburguesa.php

// Verificar si hay sesión activa para mostrar opciones apropiadas
if (!session_id()) {
    session_start();
}

$rol_usuario = $_SESSION['rol'] ?? null;
$usuario_logueado = ($rol_usuario && $rol_usuario != 'demo' && isset($_SESSION['usuario_id']));$es_demo = ($rol_usuario == 'demo');
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
            <h1 class="logo-texto">Huella Segura 🐾</h1>
        </div>
        <div class="iconos-derecha">
            <?php if ($usuario_logueado): ?>
                <button class="boton-notificaciones" onclick="toggleNotificaciones()" title="Notificaciones">
                    🔔
                    <?php if ($notificaciones_no_leidas > 0): ?>
                        <span class="badge-notificaciones"><?php echo $notificaciones_no_leidas; ?></span>
                    <?php endif; ?>
                </button>
            <?php else: ?>
                <button class="boton-notificaciones boton-notif-disabled" title="Inicia sesión para ver notificaciones">
                    🔔
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
                    <div class="avatar-usuario">👤</div>
                    <div class="datos-usuario">
                        <span class="nombre-usuario"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                        <span class="estado-usuario">En línea</span>
                    </div>
                </div>
            <?php elseif ($es_demo): ?>
                <div class="info-usuario-menu">
                    <div class="avatar-usuario">👤</div>
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
                    <span class="icono-menu">🏠</span>
                    <span class="texto-menu">Inicio</span>
                </a>
                <a href="mis-mascotas.php" class="opcion-menu">
                    <span class="icono-menu">🐕</span>
                    <span class="texto-menu">Mis Mascotas</span>
                </a>
                <a href="mascotas-perdidas.php" class="opcion-menu">
                    <span class="icono-menu">🔍</span>
                    <span class="texto-menu">Mascotas Perdidas</span>
                </a>
                <a href="adopciones.php" class="opcion-menu">
                    <span class="icono-menu">❤️</span>
                    <span class="texto-menu">Adopciones</span>
                </a>
                <a href="comunidad.php" class="opcion-menu">
                    <span class="icono-menu">👥</span>
                    <span class="texto-menu">Comunidad</span>
                </a>
                <a href="veterinaria.php" class="opcion-menu">
                    <span class="icono-menu">🏥</span>
                    <span class="texto-menu">Veterinaria</span>
                </a>
                
                <div class="separador-menu"></div>
                
                <a href="mi-perfil.php" class="opcion-menu">
                    <span class="icono-menu">👤</span>
                    <span class="texto-menu">Mi Perfil</span>
                </a>
                <a href="configuracion.php" class="opcion-menu">
                    <span class="icono-menu">⚙️</span>
                    <span class="texto-menu">Configuración</span>
                </a>
                
                <div class="separador-menu"></div>
                
                <a href="logout.php" class="opcion-menu opcion-logout">
                    <span class="icono-menu">🚪</span>
                    <span class="texto-menu">Cerrar Sesión</span>
                </a>
            <?php elseif ($rol_usuario == 'veterinario'): ?>
                <a href="comunidad.php" class="opcion-menu">
                    <span class="icono-menu">👥</span>
                    <span class="texto-menu">Comunidad</span>
                </a>
                <a href="veterinaria.php" class="opcion-menu">
                    <span class="icono-menu">🏥</span>
                    <span class="texto-menu">Veterinaria</span>
                </a>
                
                <div class="separador-menu"></div>
                
                <a href="mi-perfil.php" class="opcion-menu">
                    <span class="icono-menu">👤</span>
                    <span class="texto-menu">Mi Perfil</span>
                </a>
                <a href="configuracion.php" class="opcion-menu">
                    <span class="icono-menu">⚙️</span>
                    <span class="texto-menu">Configuración</span>
                </a>
                
                <div class="separador-menu"></div>
                
                <a href="logout.php" class="opcion-menu opcion-logout">
                    <span class="icono-menu">🚪</span>
                    <span class="texto-menu">Cerrar Sesión</span>
                </a>
            <?php else: ?>
                <!-- Opciones para usuarios no autenticados -->
                <a href="login.php" class="opcion-menu">
                    <span class="icono-menu">🔑</span>
                    <span class="texto-menu">Iniciar Sesión</span>
                </a>
                <a href="registro.php" class="opcion-menu">
                    <span class="icono-menu">📝</span>
                    <span class="texto-menu">Registrarse</span>
                </a>
                
                <div class="separador-menu"></div>
                
                <a href="index.php" class="opcion-menu">
                    <span class="icono-menu">🏠</span>
                    <span class="texto-menu">Inicio</span>
                </a>
                <a href="mascotas-perdidas.php" class="opcion-menu">
                    <span class="icono-menu">🔍</span>
                    <span class="texto-menu">Mascotas Perdidas</span>
                </a>
                <a href="adopciones.php" class="opcion-menu">
                    <span class="icono-menu">❤️</span>
                    <span class="texto-menu">Adopciones</span>
                </a>
                <a href="comunidad.php" class="opcion-menu">
                    <span class="icono-menu">👥</span>
                    <span class="texto-menu">Comunidad</span>
                </a>
                <a href="veterinaria.php" class="opcion-menu">
                    <span class="icono-menu">🏥</span>
                    <span class="texto-menu">Veterinaria</span>
                </a>
                <a href="acerca.php" class="opcion-menu">
                    <span class="icono-menu">ℹ️</span>
                    <span class="texto-menu">Acerca de</span>
                </a>
                <a href="contacto.php" class="opcion-menu">
                    <span class="icono-menu">📧</span>
                    <span class="texto-menu">Contacto</span>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Footer del menú -->
        <div class="footer-menu">
            <div class="version-app">
                <small>Huella Segura v1.0</small>
            </div>
            <div class="enlaces-rapidos">
                <a href="ayuda.php">Ayuda</a>
                <a href="privacidad.php">Privacidad</a>
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
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: 60px; /* Altura fija reducida */
}

.navegacion-principal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem 1rem; /* Padding reducido */
    max-width: 1200px;
    margin: 0 auto;
    height: 100%;
}

.boton-menu-hamburguesa {
    background: none;
    border: none;
    color: #333; /* Color negro para las líneas del menú */
    font-size: 1.3rem; /* Tamaño ligeramente reducido */
    cursor: pointer;
    padding: 0.4rem;
    border-radius: 6px;
    transition: background-color 0.3s;
    font-weight: bold; /* Para hacer las líneas más visibles */
}

.boton-menu-hamburguesa:hover {
    background-color: rgba(0,0,0,0.1); /* Hover más sutil */
}

.logo-contenedor {
    text-align: center;
    flex: 1;
}

.logo-texto {
    font-size: 1.6rem; /* Tamaño reducido proporcionalmente */
    margin: 0;
    font-weight: bold;
    color: #d35400; /* Color naranja para el logo */
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
    background-color: rgba(0,0,0,0.1);
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
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
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
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
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
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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
    box-shadow: 2px 0 10px rgba(0,0,0,0.15);
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
    background: rgba(0,0,0,0.5);
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
    background: rgba(255,255,255,0.2);
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
        padding: 0.6rem 0.8rem; /* Padding aún más reducido en móvil */
    }
    
    .logo-texto {
        font-size: 1.4rem;
    }
    
    .cabecera-principal {
        height: 55px; /* Altura ligeramente menor en móvil */
    }
}
</style>