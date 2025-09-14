<?php
// Componente reutilizable del menú hamburguesa
// Archivo: includes/menu-hamburguesa.php

// Verificar si hay sesión activa para mostrar opciones apropiadas
$usuario_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario = $usuario_logueado ? $_SESSION['usuario_nombre'] : '';
?>

<header class="cabecera-principal">
    <nav class="navegacion-principal">
        <button class="boton-menu-hamburguesa" id="menuHamburguesa">☰</button>
        <div class="logo-contenedor">
            <h1 class="logo-texto">PetCare 🐾</h1>
        </div>
        <div class="iconos-derecha">
            <button class="boton-buscar" onclick="toggleBusqueda()">🔍</button>
            <button class="boton-notificaciones" onclick="toggleNotificaciones()">🔔</button>
        </div>
    </nav>
    
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
            <?php else: ?>
                <div class="info-usuario-menu">
                    <div class="avatar-usuario">👤</div>
                    <div class="datos-usuario">
                        <span class="nombre-usuario">Invitado</span>
                        <span class="estado-usuario">No autenticado</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="opciones-menu">
            <?php if ($usuario_logueado): ?>
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
                
                <a href="mascotas-perdidas.php" class="opcion-menu">
                    <span class="icono-menu">🔍</span>
                    <span class="texto-menu">Mascotas Perdidas</span>
                </a>
                <a href="adopciones.php" class="opcion-menu">
                    <span class="icono-menu">❤️</span>
                    <span class="texto-menu">Adopciones</span>
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

.iconos-derecha {
    display: flex;
    gap: 0.5rem;
}

.iconos-derecha button {
    background: none;
    border: none;
    color: #333; /* Color negro para los iconos también */
    font-size: 1.1rem; /* Tamaño ligeramente reducido */
    cursor: pointer;
    padding: 0.4rem;
    border-radius: 6px;
    transition: background-color 0.3s;
}

.iconos-derecha button:hover {
    background-color: rgba(0,0,0,0.1); /* Hover consistente */
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

.overlay-menu.activo {
    opacity: 1;
    visibility: visible;
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