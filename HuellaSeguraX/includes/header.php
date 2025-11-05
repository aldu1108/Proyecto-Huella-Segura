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
                <h1 class="logo-texto">Huella Segura</h1>
                <p class="logo-subtitulo">Tu compañero para el cuidado de mascotas</p>
            </div>
            <div class="iconos-derecha">
                <button class="boton-buscar"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24"
                        width="24px" fill="#D35400">
                        <path d="M0 0h24v24H0V0z" fill="none" />
                        <path
                            d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                    </svg></button>
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