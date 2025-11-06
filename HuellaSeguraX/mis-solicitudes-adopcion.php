<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] === 'demo') {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener solicitudes recibidas (mascotas del usuario)
$consulta_solicitudes = "SELECT sa.*, 
                         u.nombre_usuario, u.email_usuario, u.telefono_usuario,
                         m.nombre_mascota, m.foto_mascota, m.tipo,
                         p.id_anuncio
                         FROM solicitud_adopcion sa
                         JOIN usuarios u ON sa.id_usuario = u.id_usuario
                         JOIN publicacion_adopcion pa ON sa.id_adopcion = pa.id_adopcion
                         JOIN publicaciones p ON pa.id_publicacion = p.id_anuncio
                         JOIN mascotas m ON p.id_mascota = m.id_mascota
                         WHERE p.id_usuario = ?
                         ORDER BY sa.fecha DESC";

$stmt = $conexion->prepare($consulta_solicitudes);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Adopción - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/adopciones.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <main class="main-content">
        <section class="adopciones-header">
            <h1 class="titulo-adopciones">Solicitudes de Adopción <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#A7C4E5"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg></h1>
            <p class="subtitulo-adopciones">Gestiona las solicitudes para tus mascotas</p>
        </section>

        <div class="lista-adopciones">
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($solicitud = $resultado->fetch_assoc()): ?>
                            <div class="tarjeta-reporte" style="margin-bottom: 20px;">
                                <div class="contenido-reporte">
                                    <div class="header-usuario-reporte">
                                        <div class="avatar-usuario-reporte" style="background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M480-480q-60 0-102-42t-42-102q0-60 42-102t102-42q60 0 102 42t42 102q0 60-42 102t-102 42ZM192-192v-96q0-23 12.5-43.5T239-366q55-32 116.5-49T480-432q63 0 124.5 17T721-366q22 13 34.5 34t12.5 44v96H192Z"/></svg>
                                        </div>
                                        <div class="info-usuario-reporte">
                                            <h4 class="nombre-usuario-reporte">
                                                <?php echo htmlspecialchars($solicitud['nombre_usuario']); ?>
                                            </h4>
                                            <p class="tiempo-publicacion-reporte">
                                                <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha'])); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div style="padding: 15px; background: #f9fafb; border-radius: 8px; margin: 15px 0;">
                                        <h4 style="margin: 0 0 10px 0; color: #333;">
                                            Quiere adoptar a: <strong><?php echo htmlspecialchars($solicitud['nombre_mascota']); ?></strong>
                                        </h4>
                                        <p style="margin: 10px 0; color: #666; line-height: 1.6;">
                                            <?php echo nl2br(htmlspecialchars($solicitud['descripcion'])); ?>
                                        </p>
                                
                                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                                            <p style="margin: 5px 0;"><strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#D9D9D9"><path d="M168-192q-29 0-50.5-21.5T96-264v-432q0-29 21.5-50.5T168-768h624q30 0 51 21.5t21 50.5v432q0 29-21 50.5T792-192H168Zm312-240 312-179v-85L480-517 168-696v85l312 179Z"/></svg> Email:</strong> <?php echo htmlspecialchars($solicitud['email_usuario']); ?></p>
                                            <?php if ($solicitud['telefono_usuario']): ?>
                                                    <p style="margin: 5px 0;"><strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M744-481q0-109-77.5-186.5T480-745v-72q70 0 131 26.5t106.5 72Q763-673 789.5-612T816-481h-72Zm-144 0q0-50-35-85t-85-35v-72q80 0 136 56t56 136h-72Zm163 336q-121-9-229.5-59.5T339-341q-86-86-136-194.5T144-765q-2-21 12.5-36.5T192-817h136q17 0 29.5 10.5T374-780l24 107q2 13-1.5 25T385-628l-97 98q20 38 46 73t58 66q30 30 64 55.5t72 45.5l99-96q8-8 20-11.5t25-1.5l107 23q17 5 27 17.5t10 29.5v136q0 21-16 35.5T763-145Z"/></svg> Teléfono:</strong> <?php echo htmlspecialchars($solicitud['telefono_usuario']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="acciones-reporte">
                                        <a href="mailto:<?php echo htmlspecialchars($solicitud['email_usuario']); ?>" 
                                           class="boton-contactar" style="text-decoration: none; display: inline-block;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#D9D9D9"><path d="M168-192q-29 0-50.5-21.5T96-264v-432q0-29 21.5-50.5T168-768h624q30 0 51 21.5t21 50.5v432q0 29-21 50.5T792-192H168Zm312-240 312-179v-85L480-517 168-696v85l312 179Z"/></svg> Contactar por Email
                                        </a>
                                        <?php if ($solicitud['telefono_usuario']): ?>
                                                <a href="tel:<?php echo htmlspecialchars($solicitud['telefono_usuario']); ?>" 
                                                   class="boton-contactar" style="text-decoration: none; display: inline-block;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M744-481q0-109-77.5-186.5T480-745v-72q70 0 131 26.5t106.5 72Q763-673 789.5-612T816-481h-72Zm-144 0q0-50-35-85t-85-35v-72q80 0 136 56t56 136h-72Zm163 336q-121-9-229.5-59.5T339-341q-86-86-136-194.5T144-765q-2-21 12.5-36.5T192-817h136q17 0 29.5 10.5T374-780l24 107q2 13-1.5 25T385-628l-97 98q20 38 46 73t58 66q30 30 64 55.5t72 45.5l99-96q8-8 20-11.5t25-1.5l107 23q17 5 27 17.5t10 29.5v136q0 21-16 35.5T763-145Z"/></svg> Llamar
                                                </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                    <?php endwhile; ?>
            <?php else: ?>
                    <div class="mensaje-vacio" style="text-align: center; padding: 60px 20px; color: #666;">
                        <p style="font-size: 64px; margin: 0;"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm72-144h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8Z"/></svg></p>
                        <h3 style="margin: 20px 0 10px; color: #2c3e50;">No tienes solicitudes</h3>
                        <p style="margin: 0; font-size: 16px;">Cuando alguien solicite adoptar tus mascotas, aparecerán aquí.</p>
                    </div>
            <?php endif; ?>
        </div>
    </main>

    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/notificaciones.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>