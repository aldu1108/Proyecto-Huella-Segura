<?php
session_start();
include_once('config/conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header class="cabecera-principal">
        <nav class="navegacion-principal">
            <button class="boton-menu-hamburguesa" id="menuHamburguesa">☰</button>
            <div class="logo-contenedor">
                <h1 class="logo-texto">PetCare 🐾</h1>
                <p class="logo-subtitulo">Tu compañero para el cuidado de mascotas</p>
            </div>
            <div class="iconos-derecha">
                <button class="boton-buscar">🔍</button>
                <button class="boton-compartir">⚡</button>
            </div>
        </nav>
        
        <!-- Menú hamburguesa desplegable -->
        <div class="menu-lateral" id="menuLateral" style="display: none;">
            <div class="opciones-menu">
                <a href="index.php" class="opcion-menu">🏠 Inicio</a>
                <a href="mis-mascotas.php" class="opcion-menu">🐕 Mis Mascotas</a>
                <a href="mascotas-perdidas.php" class="opcion-menu">🔍 Mascotas Perdidas</a>
                <a href="adopciones.php" class="opcion-menu">❤️ Adopciones</a>
                <a href="comunidad.php" class="opcion-menu">👥 Comunidad</a>
                <a href="veterinaria.php" class="opcion-menu">🏥 Veterinaria</a>
                <a href="mi-perfil.php" class="opcion-menu">👤 Mi Perfil</a>
                <a href="configuracion.php" class="opcion-menu">⚙️ Configuración</a>
                <a href="logout.php" class="opcion-menu">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>