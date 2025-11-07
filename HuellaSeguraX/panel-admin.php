<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

// Obtener estadísticas básicas con manejo de errores
try {
    $total_usuarios = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'")->fetch_assoc()['total'] ?? 0;
    $total_mascotas = $conexion->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'activo'")->fetch_assoc()['total'] ?? 0;
    $total_veterinarios = $conexion->query("SELECT COUNT(*) as total FROM veterinario WHERE certificado = 1")->fetch_assoc()['total'] ?? 0;
    $total_adopciones = $conexion->query("SELECT COUNT(*) as total FROM publicacion_adopcion")->fetch_assoc()['total'] ?? 0;
    $total_perdidas = $conexion->query("SELECT COUNT(*) as total FROM publicacion_perdida")->fetch_assoc()['total'] ?? 0;
    $total_citas = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    // Valores por defecto si hay error
    $total_usuarios = 0;
    $total_mascotas = 0;
    $total_veterinarios = 0;
    $total_adopciones = 0;
    $total_perdidas = 0;
    $total_citas = 0;
}

// Usuarios recientes
try {
    $usuarios_recientes = $conexion->query("SELECT nombre_usuario, apellido_usuario, email_usuario, 
                                        DATE_FORMAT(NOW(), '%Y-%m-%d') as fecha_registro 
                                        FROM usuarios WHERE estado = 'activo' 
                                        ORDER BY id_usuario DESC LIMIT 10");
} catch (Exception $e) {
    $usuarios_recientes = null;
}

// Veterinarios pendientes de verificación
try {
    $veterinarios_pendientes = $conexion->query("SELECT u.nombre_usuario, u.apellido_usuario, u.email_usuario, v.especialidad, v.clinica 
                                                FROM usuarios u 
                                                JOIN veterinario v ON u.id_usuario = v.id_usuario 
                                                WHERE v.certificado = 0 LIMIT 10");
} catch (Exception $e) {
    $veterinarios_pendientes = null;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/panel-admin.css">
    <?php include_once("includes/logo.php"); ?>
</head>

<body class="admin-panel">
<header class="header-admin">
        <div class="logo-admin">
            <h1><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#5985E1"><path d="M480-96q-135-33-223.5-152.84Q168-368.69 168-515v-229l312-120 312 120v229q0 146.31-88.5 266.16Q615-129 480-96Z"/></svg> Panel Administrativo</h1>
        </div>
        <div class="acciones-admin">
            <a href="logout.php" class="boton-logout">🚪 Cerrar Sesión</a>
            <a href="panel-admin.php" class="boton-logout"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M384-288 192-480l192-192 51 51-105 105h438v72H330l105 105-51 51Z"/></svg> Volver al Panel</a>
        </div>
    </header>

    <div class="contenedor-admin">
        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="mensaje-admin mensaje-<?php echo $_GET['tipo']; ?>">
                <span><?php echo htmlspecialchars($_GET['mensaje']); ?></span>
                <button onclick="this.parentElement.remove()"
                    style="background:none;border:none;color:inherit;cursor:pointer;padding:0 5px;">✕</button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas principales -->
        <section class="estadisticas-admin">
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#EA3323"><path d="m107-384-59-42 192-312 120 144 168-264 120 168 146-222 58 42-202 307-119-166-163 257-119-143-142 231Zm468.77 144Q616-240 644-267.77q28-27.78 28-68Q672-376 644.23-404q-27.78-28-68-28Q536-432 508-404.23q-28 27.78-28 68Q480-296 507.77-268q27.78 28 68 28ZM765-96l-98-98q-19.91 13-43.13 19.5Q600.65-168 576-168q-70 0-119-49t-49-119q0-70 49-119t119-49q70 0 119 49t49 119q0 24.65-6.5 47.87T718-245l98 98-51 51Z"/></svg> Estadísticas del Sistema</h2>
            <div class="tarjetas-estadisticas">
                <div class="tarjeta-stat">
                    <div class="icono-stat"><svg xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="#434343"><path d="M48-264v-57q0-39 39-63t105-24q14 0 26 1t23 3q-12 18-18.5 39.11Q216-343.77 216-322v58H48Zm216 0v-58q0-28 14.5-50t43.5-39q29-17 69-25t89.5-8q49.5 0 89 8t68.5 25q29 16 43.5 38.69Q696-349.62 696-322v58H264Zm480 0v-58q0-22-6.5-42.5T719-404q9-2 20.5-3t28.5-1q66 0 105 24t39 63v57H744ZM192-456q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm576 0q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm-288-36q-45 0-76.5-31.52T372-600.07q0-44.93 31.52-76.43 31.52-31.5 76.55-31.5 44.93 0 76.43 31.55Q588-644.9 588-600q0 45-31.55 76.5T480-492Z"/></svg></div>
                    <div class="numero-stat"><?php echo $total_usuarios; ?></div>
                    <div class="texto-stat">Usuarios Activos</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat"><svg xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg></div>
                    <div class="numero-stat"><?php echo $total_mascotas; ?></div>
                    <div class="texto-stat">Mascotas Registradas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat"><svg xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="#5985E1"><path d="M533-96q-97.62 0-166.31-68.98Q298-233.97 298-332v-30q-85-11-143.5-74.5T96-588v-228h120v-48h72v168h-72v-48h-48v156.46q0 64.54 45.5 110.04T324-432q65 0 110.5-45.5T480-587.54V-744h-48v48h-72v-168h72v48h120v228q0 84.35-51.5 146.67Q449-379 370-364v33q0 67.92 47.5 115.46Q465-168 533-167q68-1 115.5-48.54T696-331v-59.37Q659-401 635.5-432T612-504q0-50 35-85t85-35q50 0 85 35t35 85q0 41-23.5 72T768-390v58q0 97.62-69 166.31T533-96Z"/></svg></div>
                    <div class="numero-stat"><?php echo $total_veterinarios; ?></div>
                    <div class="texto-stat">Veterinarios Certificados</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat"><svg xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg></div>
                    <div class="numero-stat"><?php echo $total_adopciones; ?></div>
                    <div class="texto-stat">Adopciones Publicadas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat"><svg xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="#666666"><path d="M765-144 526-383q-30 22-65.79 34.5-35.79 12.5-76.18 12.5Q284-336 214-406t-70-170q0-100 70-170t170-70q100 0 170 70t70 170.03q0 40.39-12.5 76.18Q599-464 577-434l239 239-51 51ZM384-408q70 0 119-49t49-119q0-70-49-119t-119-49q-70 0-119 49t-49 119q0 70 49 119t119 49Z"/></svg></div>
                    <div class="numero-stat"><?php echo $total_perdidas; ?></div>
                    <div class="texto-stat">Mascotas Perdidas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat"><svg xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg></div>
                    <div class="numero-stat"><?php echo $total_citas; ?></div>
                    <div class="texto-stat">Citas Veterinarias</div>
                </div>
            </div>
        </section>

        <!-- Acciones rápidas -->
        <section class="acciones-rapidas">
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#FFFF55"><path d="m288-96 144-288-288-48 456-432h72L528-576l288 48L360-96h-72Z"/></svg> Acciones Rápidas</h2>
            <div class="botones-accion">
                <button class="boton-accion usuarios" onclick="verUsuarios()">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M96-192v-92q0-26 12.5-47.5T143-366q54-32 114.5-49T384-432q66 0 126.5 17T625-366q22 13 34.5 34.5T672-284v92H96Zm648 0v-92q0-42-19.5-78T672-421q39 8 75.5 21.5T817-366q22 13 34.5 34.5T864-284v92H744ZM384-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42Zm336-144q0 60-42 102t-102 42q-8 0-15-.5t-15-2.5q25-29 39.5-64.5T600-624q0-41-14.5-76.5T546-765q8-2 15-2.5t15-.5q60 0 102 42t42 102Z"/></svg> Gestionar Usuarios
                </button>
                <button class="boton-accion configuracion" onclick="configurarSistema()">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="m403-96-22-114q-23-9-44.5-21T296-259l-110 37-77-133 87-76q-2-12-3-24t-1-25q0-13 1-25t3-24l-87-76 77-133 110 37q19-16 40.5-28t44.5-21l22-114h154l22 114q23 9 44.5 21t40.5 28l110-37 77 133-87 76q2 12 3 24t1 25q0 13-1 25t-3 24l87 76-77 133-110-37q-19 16-40.5 28T579-210L557-96H403Zm77-240q60 0 102-42t42-102q0-60-42-102t-102-42q-60 0-102 42t-42 102q0 60 42 102t102 42Z"/></svg> Configuración Sistema
                </button>
            </div>
        </section>

        <!-- Usuarios recientes -->
        <section class="usuarios-recientes">
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#434343"><path d="M96-192v-92q0-26 12.5-47.5T143-366q54-32 114.5-49T384-432q66 0 126.5 17T625-366q22 13 34.5 34.5T672-284v92H96Zm648 0v-92q0-42-19.5-78T672-421q39 8 75.5 21.5T817-366q22 13 34.5 34.5T864-284v92H744ZM384-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42Zm336-144q0 60-42 102t-102 42q-8 0-15-.5t-15-2.5q25-29 39.5-64.5T600-624q0-41-14.5-76.5T546-765q8-2 15-2.5t15-.5q60 0 102 42t42 102Z"/></svg> Gestionar Usuarios Usuarios Recientes</h2>
            <div class="tabla-admin">
                <?php if ($usuarios_recientes && $usuarios_recientes->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($usuario = $usuarios_recientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['email_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['fecha_registro']); ?></td>
                                    <td>
                                        <button class="boton-pequeno ver"
                                            onclick="verUsuario('<?php echo htmlspecialchars($usuario['email_usuario']); ?>')">Ver</button>
                                        <button class="boton-pequeno editar"
                                            onclick="editarUsuario('<?php echo htmlspecialchars($usuario['email_usuario']); ?>')">Editar</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="sin-datos">No hay usuarios registrados</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Veterinarios pendientes -->
        <section class="veterinarios-pendientes">
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#F19E39"><path d="M480-516q65 0 110.5-45.5T636-672v-120H324v120q0 65 45.5 110.5T480-516ZM192-96v-72h60v-120q0-59 28-109.5t78-82.5q-49-32-77.5-82.5T252-672v-120h-60v-72h576v72h-60v120q0 59-28.5 109.5T602-480q50 32 78 82.5T708-288v120h60v72H192Z"/></svg> Veterinarios Pendientes de Verificación</h2>
            <div class="tabla-admin">
                <?php if ($veterinarios_pendientes && $veterinarios_pendientes->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Especialidad</th>
                                <th>Clínica</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($veterinario = $veterinarios_pendientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($veterinario['nombre_usuario'] . ' ' . $veterinario['apellido_usuario']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($veterinario['email_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($veterinario['especialidad']); ?></td>
                                    <td><?php echo htmlspecialchars($veterinario['clinica']); ?></td>
                                    <td>
                                        <a href="aprobar-veterinario.php?accion=aprobar&email=<?php echo urlencode($veterinario['email_usuario']); ?>"
                                            class="boton-pequeno aprobar"
                                            onclick="return confirm('¿Aprobar a <?php echo htmlspecialchars($veterinario['nombre_usuario']); ?>? Podrá acceder al sistema.')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#75FB4C"><path d="m429-336 238-237-51-51-187 186-85-84-51 51 136 135ZM216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h528q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Z"/></svg> Aprobar
                                        </a>
                                        <a href="aprobar-veterinario.php?accion=rechazar&email=<?php echo urlencode($veterinario['email_usuario']); ?>"
                                            class="boton-pequeno rechazar"
                                            onclick="return confirm('¿Rechazar y ELIMINAR a <?php echo htmlspecialchars($veterinario['nombre_usuario']); ?>? Esta acción no se puede deshacer.')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#EA3323"><path d="m291-240-51-51 189-189-189-189 51-51 189 189 189-189 51 51-189 189 189 189-51 51-189-189-189 189Z"/></svg> Rechazar
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="sin-datos">No hay veterinarios pendientes de verificación</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Información del sistema -->
        <section class="info-sistema">
            <h2><svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#FFFF55"><path d="M407.74-240Q378-240 357-261.15 336-282.3 336-312v-67q-57-37.3-88.5-95.65Q216-533 216-600q0-110.31 76.78-187.16 76.78-76.84 187-76.84T667-787.16q77 76.85 77 187.16 0 66.82-31.5 125.41T624-379v67q0 29.7-21.18 50.85Q581.65-240 551.91-240H407.74ZM408-96q-20.4 0-34.2-13.8Q360-123.6 360-144v-24h240v24q0 20.4-13.8 34.2Q572.4-96 552-96H408Z"/></svg> Información del Sistema</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#F19E39"><path d="M263.72-96Q234-96 213-117.15T192-168v-384q0-29.7 21.15-50.85Q234.3-624 264-624h24v-96q0-79.68 56.23-135.84 56.22-56.16 136-56.16Q560-912 616-855.84q56 56.16 56 135.84v96h24q29.7 0 50.85 21.15Q768-581.7 768-552v384q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72Zm216.49-192Q510-288 531-309.21t21-51Q552-390 530.79-411t-51-21Q450-432 429-410.79t-21 51Q408-330 429.21-309t51 21ZM360-624h240v-96q0-50-35-85t-85-35q-50 0-85 35t-35 85v96Z"/></svg> Estado de Acceso:</strong> Administrador autenticado
                </div>
                <div class="info-item">
                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Fecha:</strong> <?php echo date('d/m/Y'); ?>
                </div>
                <div class="info-item">
                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#666666"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> Hora:</strong> <?php echo date('H:i:s'); ?>
                </div>
                <div class="info-item">
                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#75FBFD"><path d="M237-285q54-38 115.5-56.5T480-360q66 0 127.5 18.5T723-285q35-41 52-91t17-104q0-129.67-91.23-220.84-91.23-91.16-221-91.16Q350-792 259-700.84 168-609.67 168-480q0 54 17 104t52 91Zm243-123q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42Zm.28 312Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Z"/></svg> Usuario:</strong> <?php echo $_SESSION['usuario_nombre']; ?>
                </div>
            </div>
        </section>
    </div>

    <script src="js/panel-admin.js"></script>
</body>

</html>

<?php cerrarConexion(); ?>