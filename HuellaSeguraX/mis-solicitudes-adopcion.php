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
            <h1 class="titulo-adopciones">Solicitudes de Adopción 📋</h1>
            <p class="subtitulo-adopciones">Gestiona las solicitudes para tus mascotas</p>
        </section>

        <div class="lista-adopciones">
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($solicitud = $resultado->fetch_assoc()): ?>
                            <div class="tarjeta-reporte" style="margin-bottom: 20px;">
                                <div class="contenido-reporte">
                                    <div class="header-usuario-reporte">
                                        <div class="avatar-usuario-reporte" style="background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                            👤
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
                                            <p style="margin: 5px 0;"><strong>📧 Email:</strong> <?php echo htmlspecialchars($solicitud['email_usuario']); ?></p>
                                            <?php if ($solicitud['telefono_usuario']): ?>
                                                    <p style="margin: 5px 0;"><strong>📞 Teléfono:</strong> <?php echo htmlspecialchars($solicitud['telefono_usuario']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="acciones-reporte">
                                        <a href="mailto:<?php echo htmlspecialchars($solicitud['email_usuario']); ?>" 
                                           class="boton-contactar" style="text-decoration: none; display: inline-block;">
                                            📧 Contactar por Email
                                        </a>
                                        <?php if ($solicitud['telefono_usuario']): ?>
                                                <a href="tel:<?php echo htmlspecialchars($solicitud['telefono_usuario']); ?>" 
                                                   class="boton-contactar" style="text-decoration: none; display: inline-block;">
                                                    📞 Llamar
                                                </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                    <?php endwhile; ?>
            <?php else: ?>
                    <div class="mensaje-vacio" style="text-align: center; padding: 60px 20px; color: #666;">
                        <p style="font-size: 64px; margin: 0;">📋</p>
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