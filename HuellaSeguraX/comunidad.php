<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'];


// Obtener estadísticas REALES de la comunidad (se actualizan al refrescar)
$consulta_miembros = "SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'";
$resultado_miembros = $conexion->query($consulta_miembros);
$total_miembros = $resultado_miembros->fetch_assoc()['total'];

$consulta_mascotas_comunidad = "SELECT COUNT(*) as total FROM mascotas WHERE estado = 'activo'";
$resultado_mascotas_comunidad = $conexion->query($consulta_mascotas_comunidad);
$total_mascotas_comunidad = $resultado_mascotas_comunidad->fetch_assoc()['total'];

// Posts del día
$fecha_hoy = date('Y-m-d');
$consulta_posts_hoy = "SELECT COUNT(*) as total FROM post_comunidad WHERE DATE(fecha) = '$fecha_hoy'";
$resultado_posts_hoy = $conexion->query($consulta_posts_hoy);
$total_posts_hoy = $resultado_posts_hoy->fetch_assoc()['total'];

// Posts de tipo "ayuda" activos
$consulta_ayudas = "SELECT COUNT(*) as total FROM post_comunidad WHERE tipo_post = 'ayuda' AND DATE(fecha) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$resultado_ayudas = $conexion->query($consulta_ayudas);
$total_ayudas = $resultado_ayudas->fetch_assoc()['total'];

// Obtener posts de la comunidad con contadores optimizados 
if ($rol_usuario === 'demo') { 
    $resultado_posts = null; 
} else { 
    $consulta_posts = "SELECT p.*, 
        u.nombre_usuario, 
        u.apellido_usuario, 
        u.foto_usuario,
        (SELECT COUNT(*) FROM likes_post WHERE id_post = p.id_post) as total_likes,
        (SELECT COUNT(*) FROM comentarios_comunidad WHERE id_post = p.id_post) as total_comentarios,
        (SELECT COUNT(*) FROM likes_post WHERE id_post = p.id_post AND id_usuario = $usuario_id) as usuario_dio_like 
        FROM post_comunidad p
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        ORDER BY p.fecha DESC LIMIT 20"; 
    
    $resultado_posts = $conexion->query($consulta_posts);
}

// Obtener eventos próximos REALES
$consulta_eventos = "SELECT e.*, u.nombre_usuario, u.apellido_usuario,
                     (SELECT COUNT(*) FROM asistentes_evento WHERE id_evento = e.id_evento AND id_usuario = $usuario_id) as usuario_participa
                     FROM eventos_comunidad e
                     JOIN usuarios u ON e.id_usuario = u.id_usuario
                     WHERE e.fecha >= NOW() AND e.estado = 'activo'
                     ORDER BY e.fecha ASC LIMIT 10";
$resultado_eventos = $conexion->query($consulta_eventos);

        // Obtener grupos de la comunidad
$consulta_grupos = "SELECT g.*, u.nombre_usuario, u.apellido_usuario,
                    (SELECT COUNT(*) FROM miembros_grupo WHERE id_grupo = g.id_grupo AND id_usuario = $usuario_id) as usuario_es_miembro
                    FROM grupos_comunidad g
                    JOIN usuarios u ON g.id_creador = u.id_usuario
                    WHERE g.estado = 'activo'
                    ORDER BY g.contador_miembros DESC LIMIT 20";
$resultado_grupos = $conexion->query($consulta_grupos);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidad - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/modal-alerta-demo.css">
    <link rel="stylesheet" href="css/comunidad.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <!-- Header -->
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Header de comunidad -->
        <section class="comunidad-header">
            <h2 class="comunidad-title">Comunidad</h2>
            <p class="comunidad-subtitle">Conecta con otros amantes de las mascotas</p>

            <!-- Estadísticas REALES -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_miembros; ?></div>
                    <div class="stat-label">Miembros</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_mascotas_comunidad; ?></div>
                    <div class="stat-label">Mascotas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_posts_hoy; ?></div>
                    <div class="stat-label">Posts hoy</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_ayudas; ?></div>
                    <div class="stat-label">Ayudas</div>
                </div>
            </div>
        </section>

        <!-- Navegación de secciones -->
        <nav class="section-nav">
            <button class="section-btn active" data-section="feed"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#CCCCCC"><path d="M160-120q-33 0-56.5-23.5T80-200v-640l67 67 66-67 67 67 67-67 66 67 67-67 67 67 66-67 67 67 67-67 66 67 67-67v640q0 33-23.5 56.5T800-120H160Zm0-80h280v-240H160v240Zm360 0h280v-80H520v80Zm0-160h280v-80H520v80ZM160-520h640v-120H160v120Z"/></svg> Feed</button>
            <button class="section-btn" data-section="eventos"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Z"/></svg> Eventos</button>
            <button class="section-btn" data-section="grupos"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm320 280q34 0 56.5-20t23.5-60q1-34-22.5-57T680-360q-34 0-57 23t-23 57q0 34 23 57t57 23ZM864-40 762-142q-18 11-38.5 16.5T680-120q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 43.5T818-198L920-96l-56 56ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q32 0 64.5 3.5T489-425q-24 32-36.5 69T440-280q0 32 8 62.5t24 57.5H40Zm720-480q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113Z"/></svg> Grupos</button>
        </nav>

        <!-- Sección Feed -->
        <section class="feed-section" id="feedSection">
            <!-- Crear post -->
            <div class="create-post">
                <form enctype="multipart/form-data" id="formCrearPost" <?php echo $rol_usuario == 'demo' ? 'onsubmit="return false;"' : 'onsubmit="return enviarPost(event);"'; ?>>
                        <input type="text" name="titulo_post" placeholder="Título del post" required maxlength="100" class="input-titulo-post" <?php echo $rol_usuario == 'demo' ? 'readonly onclick="mostrarModalAlerta(\'Inicia sesión para crear posts\n\nRegístrate para poder:\n• Compartir experiencias con tu mascota\n• Hacer preguntas a la comunidad\n• Conectar con otros dueños\')"' : ''; ?>>
                        
                        <textarea name="contenido_post" placeholder="¿Qué quieres compartir con la comunidad?" required maxlength="500" class="textarea-contenido-post" <?php echo $rol_usuario == 'demo' ? 'readonly onclick="mostrarModalAlerta(\'Inicia sesión para crear posts\n\nRegístrate para poder:\n• Compartir experiencias con tu mascota\n• Hacer preguntas a la comunidad\n• Conectar con otros dueños\')"' : ''; ?>></textarea>
                        
                        <div class="post-options">
                            <div class="tipo-post-selector">
                                <label for="tipo_post">Tipo de post:</label>
                                <select name="tipo_post" id="tipo_post" required <?php echo $rol_usuario == 'demo' ? 'disabled' : ''; ?>>
                                    <option value="general">General</option>
                                    <option value="logro"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M280-120v-80h160v-124q-49-11-87.5-41.5T296-442q-75-9-125.5-65.5T120-640v-40q0-33 23.5-56.5T200-760h80v-80h400v80h80q33 0 56.5 23.5T840-680v40q0 76-50.5 132.5T664-442q-18 46-56.5 76.5T520-324v124h160v80H280Zm0-408v-152h-80v40q0 38 22 68.5t58 43.5Zm400 0q36-13 58-43.5t22-68.5v-40h-80v152Z"/></svg> Logro</option>
                                    <option value="paseo"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#666666"><path d="M194-80v-271l197 197q54-54 96.5-116.5T530-409q0-56-22-107.5T447-607L334-720h140l160-160 82 82q40 40 62 90.5T800-600q0 57-22 107.5T716-402l-82 82v240H194Zm197-187L183-475q-11-11-17-26t-6-31q0-16 6-30.5t17-25.5l84-85 124 123q28 28 43.5 64.5T450-409q0 40-15 76.5T391-267Z"/></svg> Paseo</option>
                                    <option value="ayuda"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> Ayuda</option>
                                </select>
                            </div>
                            
                            <div class="acciones-post">
                                <?php if ($rol_usuario == 'demo'): ?>
                                    <button type="button" class="btn-adjuntar" onclick="mostrarModalAlerta('Inicia sesión para adjuntar imágenes\n\nRegístrate para compartir fotos de tus mascotas con la comunidad')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#666666"><path d="M720-330q0 104-73 177T470-80q-104 0-177-73t-73-177v-370q0-75 52.5-127.5T400-880q75 0 127.5 52.5T580-700v350q0 46-32 78t-78 32q-46 0-78-32t-32-78v-370h80v370q0 13 8.5 21.5T470-320q13 0 21.5-8.5T500-350v-350q-1-42-29.5-71T400-800q-42 0-71 29t-29 71v370q-1 71 49 120.5T470-160q70 0 119-49.5T640-330v-390h80v390Z"/></svg> Adjuntar imágenes
                                    </button>
                                    <button type="button" class="btn-publicar" onclick="mostrarModalAlerta('Inicia sesión para publicar\n\nRegístrate para poder:\n• Compartir experiencias con tu mascota\n• Hacer preguntas a la comunidad\n• Conectar con otros dueños')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F3F3F3"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280 320-200v-80L480-520 160-720v80l320 200Z"/></svg> Publicar
                                    </button>
                                <?php else: ?>
                                    <label for="imagenes_post" class="btn-adjuntar">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#cb7a17ff"><path d="M720-330q0 104-73 177T470-80q-104 0-177-73t-73-177v-370q0-75 52.5-127.5T400-880q75 0 127.5 52.5T580-700v350q0 46-32 78t-78 32q-46 0-78-32t-32-78v-370h80v370q0 13 8.5 21.5T470-320q13 0 21.5-8.5T500-350v-350q-1-42-29.5-71T400-800q-42 0-71 29t-29 71v370q-1 71 49 120.5T470-160q70 0 119-49.5T640-330v-390h80v390Z"/></svg> Adjuntar imágenes
                                        <input type="file" name="imagenes_post[]" id="imagenes_post" accept="image/*" multiple style="display: none;" onchange="previsualizarImagenes(this)">
                                    </label>
                                    
                                    <button type="submit" name="crear_post" class="btn-publicar">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F3F3F3"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280 320-200v-80L480-520 160-720v80l320 200Z"/></svg> Publicar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div id="preview-imagenes" class="preview-imagenes"></div>
                    </form>
            </div>

            <!-- Posts -->
            <div class="posts-container">
                <?php if ($rol_usuario !== 'demo' && $resultado_posts && $resultado_posts->num_rows > 0): ?>
                    <?php while ($post = $resultado_posts->fetch_assoc()): ?>
                        <div class="post-card">
                            <!-- Header del post con menú de opciones -->
                            <div class="post-header">
                                <div class="user-avatar" style="background-image: url('imagenes/<?php echo $post['foto_usuario']; ?>')"></div>
                                <div class="user-info">
                                    <h4><?php echo htmlspecialchars($post['nombre_usuario'] . ' ' . $post['apellido_usuario']); ?></h4>
                                    <p><?php echo date('d/m/Y H:i', strtotime($post['fecha'])); ?></p>
                                </div>
                                <?php 
                                $badge_class = '';
                                $badge_text = '';
                                switch($post['tipo_post']) {
                                    case 'logro':
                                        $badge_class = 'badge-logro';
                                        $badge_text = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="18px" fill="#F19E39"><path d="M280-120v-80h160v-124q-49-11-87.5-41.5T296-442q-75-9-125.5-65.5T120-640v-40q0-33 23.5-56.5T200-760h80v-80h400v80h80q33 0 56.5 23.5T840-680v40q0 76-50.5 132.5T664-442q-18 46-56.5 76.5T520-324v124h160v80H280Zm0-408v-152h-80v40q0 38 22 68.5t58 43.5Zm400 0q36-13 58-43.5t22-68.5v-40h-80v152Z"/></svg> Logro';
                                        break;
                                    case 'paseo':
                                        $badge_class = 'badge-paseo';
                                        $badge_text = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="18px" fill="#cb7a17ff"><path d="M194-80v-271l197 197q54-54 96.5-116.5T530-409q0-56-22-107.5T447-607L334-720h140l160-160 82 82q40 40 62 90.5T800-600q0 57-22 107.5T716-402l-82 82v240H194Zm197-187L183-475q-11-11-17-26t-6-31q0-16 6-30.5t17-25.5l84-85 124 123q28 28 43.5 64.5T450-409q0 40-15 76.5T391-267Z"/></svg> Paseo';
                                        break;
                                    case 'ayuda':
                                        $badge_class = 'badge-ayuda';
                                        $badge_text = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="18px" fill="#EA3323"><path d="m480-120-58-52q-101-91-167-157T150-447.5Q111-500 95.5-544T80-634q0-94 63-157t157-63q52 0 99 22t81 62q34-40 81-62t99-22q94 0 157 63t63 157q0 46-15.5 90T810-447.5Q771-395 705-329T538-172l-58 52Z"/></svg> Ayuda';
                                        break;
                                }
                                if ($badge_text): ?>
                                    <span class="post-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                <?php endif; ?>
                                
                                <?php 
                                // Verificar si el usuario actual puede eliminar este post
                                $puede_eliminar_post = ($post['id_usuario'] == $usuario_id) || ($rol_usuario == 'admin');
                                
                                if ($puede_eliminar_post): ?>
                                    <div class="post-menu-container">
                                        <button class="btn-menu-post" onclick="toggleMenuPost(<?php echo $post['id_post']; ?>)" title="Opciones">
                                            ⋮
                                        </button>
                                        <div class="post-menu-opciones" id="menu-post-<?php echo $post['id_post']; ?>" style="display: none;">
                                            <button class="menu-opcion-eliminar" onclick="eliminarPost(<?php echo $post['id_post']; ?>, this)">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z"/></svg> Eliminar post
                                            </button>
                                            <?php if ($rol_usuario == 'admin' && $post['id_usuario'] != $usuario_id): ?>
                                                <span class="menu-nota-admin">Como administrador</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="post-content">
                                <h3 class="post-titulo"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></p>
                                
                                <?php if ($post['imagen_post']): ?>
                                    <div class="post-imagenes">
                                        <?php 
                                        $imagenes = explode(',', $post['imagen_post']);
                                        foreach ($imagenes as $imagen): ?>
                                            <img src="imagenes/posts/<?php echo $imagen; ?>" 
                                                 alt="Imagen del post" 
                                                 class="post-image"
                                                 onclick="abrirModalImagen('imagenes/posts/<?php echo $imagen; ?>')">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="post-actions">
                                <button class="action-btn btn-like <?php echo $post['usuario_dio_like'] > 0 ? 'liked' : ''; ?>" 
                                        data-post-id="<?php echo $post['id_post']; ?>"
                                        onclick="toggleLike(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> <?php echo $post['total_likes']; ?>
                                </button>
                                <button class="action-btn btn-comentarios" onclick="toggleComentarios(<?php echo $post['id_post']; ?>)">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M240-400h480v-80H240v80Zm0-120h480v-80H240v80Zm0-120h480v-80H240v80Zm-80 400q-33 0-56.5-23.5T80-320v-480q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v720L720-240H160Z"/></svg> <?php echo $post['total_comentarios']; ?>
                                </button>
                                <button class="action-btn btn-compartir" onclick="compartirPost(<?php echo $post['id_post']; ?>, '<?php echo htmlspecialchars($post['titulo']); ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M648-96q-50 0-85-35t-35-85q0-9 4-29L295-390q-16 14-36.05 22-20.04 8-42.95 8-50 0-85-35t-35-85q0-50 35-85t85-35q23 0 43 8t36 22l237-145q-2-7-3-13.81-1-6.81-1-15.19 0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-23 0-43-8t-36-22L332-509q2 7 3 13.81 1 6.81 1 15.19 0 8.38-1 15.19-1 6.81-3 13.81l237 145q16-14 36.05-22 20.04-8 42.95-8 50 0 85 35t35 85q0 50-35 85t-85 35Z"/></svg> Compartir
                                </button>
                            </div>
                            
                            <!-- Sección de comentarios (colapsable) - REEMPLAZAR en comunidad.php -->
<div class="comentarios-seccion" id="comentarios-<?php echo $post['id_post']; ?>" style="display: none;">
    <div class="comentarios-lista">
        <?php
        // Obtener comentarios de este post
        $post_id = $post['id_post'];
        $consulta_comentarios = "SELECT c.*, u.nombre_usuario, u.apellido_usuario, u.foto_usuario 
                                FROM comentarios_comunidad c 
                                JOIN usuarios u ON c.id_usuario = u.id_usuario 
                                WHERE c.id_post = $post_id 
                                ORDER BY c.fecha ASC";
        $resultado_comentarios = $conexion->query($consulta_comentarios);
        
        if ($resultado_comentarios && $resultado_comentarios->num_rows > 0):
            while ($comentario = $resultado_comentarios->fetch_assoc()): 
                // Verificar si el usuario actual puede eliminar este comentario
                $puede_eliminar = ($comentario['id_usuario'] == $usuario_id) || ($rol_usuario == 'admin');
            ?>
                <div class="comentario-item">
                    <div class="comentario-avatar" style="background-image: url('imagenes/<?php echo $comentario['foto_usuario']; ?>')"></div>
                    <div class="comentario-contenido">
                        <div class="comentario-header">
                            <span class="comentario-autor"><?php echo htmlspecialchars($comentario['nombre_usuario'] . ' ' . $comentario['apellido_usuario']); ?></span>
                            <span class="comentario-fecha"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?></span>
                            
                            <?php if ($puede_eliminar): ?>
                                <button class="btn-eliminar-comentario" 
                                        onclick="eliminarComentario(<?php echo $comentario['id_comentario']; ?>, this)"
                                        data-post-id="<?php echo $post_id; ?>"
                                        title="Eliminar comentario">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm80-160h80v-360h-80v360Zm160 0h80v-360h-80v360Z"/></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                        <p class="comentario-texto"><?php echo nl2br(htmlspecialchars($comentario['contenido'])); ?></p>
                    </div>
                </div>
            <?php endwhile;
        else: ?>
            <p class="sin-comentarios">No hay comentarios aún. ¡Sé el primero en comentar!</p>
        <?php endif; ?>
    </div>
    
    <!-- Formulario para nuevo comentario -->
    <div class="comentario-form">
        <div class="comentario-input-wrapper">
            <textarea 
                class="comentario-input" 
                placeholder="Escribe un comentario..." 
                maxlength="500"
                data-post-id="<?php echo $post['id_post']; ?>"
                onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault(); enviarComentario(this);}"
            ></textarea>
            <button class="btn-enviar-comentario" onclick="enviarComentario(this.previousElementSibling)">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#999999"><path d="M120-160v-240l320-80-320-80v-240l760 320-760 320Z"/></svg>
            </button>
        </div>
        <small class="comentario-ayuda">Presiona Enter para enviar, Shift+Enter para nueva línea</small>
    </div>
</div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Posts de ejemplo para usuario demo -->
                    <div class="post-card">
                        <div class="post-header">
                            <div class="user-avatar"></div>
                            <div class="user-info">
                                <h4>María García</h4>
                                <p>Hace 2 horas</p>
                            </div>
                            <span class="post-badge badge-logro"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M288-144v-72h156v-124q-42-8-77.5-33.5T313-434q-73-9-121-62.5T144-622v-26q0-30 21-51t51-21h72v-96h384v96h72q30 0 51 21t21 51v24q0 72-48 126.5T647-434q-18 35-53.5 60.5T516-340v124h156v72H288Zm0-372v-132h-72v24q0 37 19 65.5t53 42.5Zm384 0q34-14 53-42.5t19-65.5v-24h-72v132Z"/></svg> Logro</span>
                        </div>
                        <div class="post-content">
                            <h3 class="post-titulo">¡Primer examen veterinario aprobado!</h3>
                            <p>¡Luna acaba de pasar su primer examen veterinario con excelentes resultados! 🎉 Gracias al Dr. Martínez por el cuidado excepcional.</p>
                            <div class="post-tag">🐾 Luna</div>
                        </div>
                        <div class="post-actions">
                            <?php if ($rol_usuario == 'demo'): ?>
                                <button class="action-btn" onclick="mostrarModalAlerta('Inicia sesión para dar me gusta')"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> 24</button>
                                <button class="action-btn" onclick="mostrarModalAlerta('Inicia sesión para comentar')"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EFEFEF"><path d="M168-192q-29.7 0-50.85-21.16Q96-234.32 96-264.04v-432.24Q96-726 117.15-747T168-768h624q29.7 0 50.85 21.16Q864-725.68 864-695.96v432.24Q864-234 842.85-213T792-192H168Zm48-120h528v-72H216v72Zm0-132h528v-72H216v72Zm0-132h384v-72H216v72Z"/></svg> 5</button>
                                <button class="action-btn" onclick="mostrarModalAlerta('Inicia sesión para compartir')"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M648-96q-50 0-85-35t-35-85q0-9 4-29L295-390q-16 14-36.05 22-20.04 8-42.95 8-50 0-85-35t-35-85q0-50 35-85t85-35q23 0 43 8t36 22l237-145q-2-7-3-13.81-1-6.81-1-15.19 0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-23 0-43-8t-36-22L332-509q2 7 3 13.81 1 6.81 1 15.19 0 8.38-1 15.19-1 6.81-3 13.81l237 145q16-14 36.05-22 20.04-8 42.95-8 50 0 85 35t35 85q0 50-35 85t-85 35Z"/></svg> Compartir</button>
                            <?php else: ?>
                                <button class="action-btn"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> <?php echo $post['total_likes']; ?> 24</button>
                                <button class="action-btn"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EFEFEF"><path d="M168-192q-29.7 0-50.85-21.16Q96-234.32 96-264.04v-432.24Q96-726 117.15-747T168-768h624q29.7 0 50.85 21.16Q864-725.68 864-695.96v432.24Q864-234 842.85-213T792-192H168Zm48-120h528v-72H216v72Zm0-132h528v-72H216v72Zm0-132h384v-72H216v72Z"/></svg> <?php echo $post['total_comentarios']; ?>5</button>
                                <button class="action-btn"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M648-96q-50 0-85-35t-35-85q0-9 4-29L295-390q-16 14-36.05 22-20.04 8-42.95 8-50 0-85-35t-35-85q0-50 35-85t85-35q23 0 43 8t36 22l237-145q-2-7-3-13.81-1-6.81-1-15.19 0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-23 0-43-8t-36-22L332-509q2 7 3 13.81 1 6.81 1 15.19 0 8.38-1 15.19-1 6.81-3 13.81l237 145q16-14 36.05-22 20.04-8 42.95-8 50 0 85 35t35 85q0 50-35 85t-85 35Z"/></svg>  Compartir</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="post-card">
                        <div class="post-header">
                            <div class="user-avatar"></div>
                            <div class="user-info">
                                <h4>Carlos Ruiz</h4>
                                <p>Hace 4 horas</p>
                            </div>
                           <span class="post-badge badge-ayuda"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> Ayuda</span>
                        </div>
                        <div class="post-content">
                            <h3 class="post-titulo">Busco veterinario especialista en gatos</h3>
                            <p>¿Alguien sabe de un buen veterinario especialista en gatos en la zona de Salamanca? Mi gatito necesita una revisión especializada.</p>
                            <div class="location-tag"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M648-96q-50 0-85-35t-35-85q0-9 4-29L295-390q-16 14-36.05 22-20.04 8-42.95 8-50 0-85-35t-35-85q0-50 35-85t85-35q23 0 43 8t36 22l237-145q-2-7-3-13.81-1-6.81-1-15.19 0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-23 0-43-8t-36-22L332-509q2 7 3 13.81 1 6.81 1 15.19 0 8.38-1 15.19-1 6.81-3 13.81l237 145q16-14 36.05-22 20.04-8 42.95-8 50 0 85 35t35 85q0 50-35 85t-85 35Z"/></svg>  Madrid, Salamanca</div>
                        </div>
                        <div class="post-actions">
                            <button class="action-btn"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> 14</button>
                            <button class="action-btn"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EFEFEF"><path d="M168-192q-29.7 0-50.85-21.16Q96-234.32 96-264.04v-432.24Q96-726 117.15-747T168-768h624q29.7 0 50.85 21.16Q864-725.68 864-695.96v432.24Q864-234 842.85-213T792-192H168Zm48-120h528v-72H216v72Zm0-132h528v-72H216v72Zm0-132h384v-72H216v72Z"/></svg> 8</button>
                            <button class="action-btn"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0000F5"><path d="M648-96q-50 0-85-35t-35-85q0-9 4-29L295-390q-16 14-36.05 22-20.04 8-42.95 8-50 0-85-35t-35-85q0-50 35-85t85-35q23 0 43 8t36 22l237-145q-2-7-3-13.81-1-6.81-1-15.19 0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-23 0-43-8t-36-22L332-509q2 7 3 13.81 1 6.81 1 15.19 0 8.38-1 15.19-1 6.81-3 13.81l237 145q16-14 36.05-22 20.04-8 42.95-8 50 0 85 35t35 85q0 50-35 85t-85 35Z"/></svg>  Compartir</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sección Eventos -->
        <section class="eventos-section" id="eventosSection" style="display: none;">
    <div>
        
    <div class="section-header">
            <h3>Próximos Eventos</h3>
            <button class="btn-create" onclick="mostrarModalCrearEvento()">Crear Evento</button>
    </div>
<!-- Sección de eventos con menú de opciones -->
<div class="eventos-list">
    
    <?php if ($rol_usuario !== 'demo' && $resultado_eventos && $resultado_eventos->num_rows > 0): ?>
        <?php while ($evento = $resultado_eventos->fetch_assoc()): 
            $fecha_evento = new DateTime($evento['fecha']);
            $dia = $fecha_evento->format('d');
            $mes = $fecha_evento->format('M');
            
            // Verificar si el usuario actual puede eliminar este evento
            $puede_eliminar_evento = ($evento['id_usuario'] == $usuario_id) || ($rol_usuario == 'admin');
            $es_creador = ($evento['id_usuario'] == $usuario_id);
        ?>
            <div class="evento-card">
                <?php if ($puede_eliminar_evento): ?>
                    <div class="evento-menu-container">
                        <button class="btn-menu-evento" onclick="toggleMenuEvento(<?php echo $evento['id_evento']; ?>)" title="Opciones">
                            ⋮
                        </button>
                        <div class="evento-menu-opciones" id="menu-evento-<?php echo $evento['id_evento']; ?>" style="display: none;">
                            <button class="menu-opcion-eliminar-evento" onclick="eliminarEvento(<?php echo $evento['id_evento']; ?>, this)">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar evento
                            </button>
                            <?php if ($rol_usuario == 'admin' && !$es_creador): ?>
                                <span class="menu-nota-admin-evento">Como administrador</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="evento-date">
                    <div class="date-day"><?php echo $dia; ?></div>
                    <div class="date-month"><?php echo ucfirst($mes); ?></div>
                </div>
                <div class="evento-info">
                    <h4><?php echo htmlspecialchars($evento['titulo']); ?></h4>
                    <p class="evento-descripcion"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                    <div class="evento-details">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> <?php echo $fecha_evento->format('H:i'); ?> 
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg> <?php echo htmlspecialchars($evento['ubicacion']); ?> 
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M48-264v-57q0-39 39-63t105-24q14 0 26 1t23 3q-12 18-18.5 39.11Q216-343.77 216-322v58H48Zm216 0v-58q0-28 14.5-50t43.5-39q29-17 69-25t89.5-8q49.5 0 89 8t68.5 25q29 16 43.5 38.69Q696-349.62 696-322v58H264Zm480 0v-58q0-22-6.5-42.5T719-404q9-2 20.5-3t28.5-1q66 0 105 24t39 63v57H744ZM192-456q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm576 0q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm-288-36q-45 0-76.5-31.52T372-600.07q0-44.93 31.52-76.43 31.52-31.5 76.55-31.5 44.93 0 76.43 31.55Q588-644.9 588-600q0 45-31.55 76.5T480-492Z"/></svg> <?php echo $evento['contador_asistentes']; ?> asistirán
                    </div>
                    <?php if ($rol_usuario == 'demo'): ?>
                        <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a eventos')">Unirse al Evento</button>
                    <?php else: ?>
                        <button class="btn-join <?php echo $evento['usuario_participa'] > 0 ? 'btn-joined' : ''; ?>" 
                                data-evento-id="<?php echo $evento['id_evento']; ?>"
                                onclick="toggleParticipacion(this)">
                            <?php echo $evento['usuario_participa'] > 0 ? 'Participando' : 'Unirse al Evento'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <!-- Eventos de ejemplo para demo -->
        <div class="evento-card">
            <div class="evento-date">
                <div class="date-day">15</div>
                <div class="date-month">Nov</div>
            </div>
            <div class="evento-info">
                <h4>Adopción Solidaria</h4>
                <p class="evento-descripcion">Jornada de adopción de mascotas rescatadas</p>
                <div class="evento-details">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#CCCCCC"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> 10:00 <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg> Parque del Retiro <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M48-264v-57q0-39 39-63t105-24q14 0 26 1t23 3q-12 18-18.5 39.11Q216-343.77 216-322v58H48Zm216 0v-58q0-28 14.5-50t43.5-39q29-17 69-25t89.5-8q49.5 0 89 8t68.5 25q29 16 43.5 38.69Q696-349.62 696-322v58H264Zm480 0v-58q0-22-6.5-42.5T719-404q9-2 20.5-3t28.5-1q66 0 105 24t39 63v57H744ZM192-456q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm576 0q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm-288-36q-45 0-76.5-31.52T372-600.07q0-44.93 31.52-76.43 31.52-31.5 76.55-31.5 44.93 0 76.43 31.55Q588-644.9 588-600q0 45-31.55 76.5T480-492Z"/></svg> 45 asistirán
                </div>
                <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a eventos')">Unirse al Evento</button>
            </div>
        </div>
    <?php endif; ?>
</div>
</section>

        <!-- Sección Grupos -->
<section class="grupos-section" id="gruposSection" style="display: none;">
    <div class="section-header">
        <h3>Grupos Populares</h3>
        <?php if ($rol_usuario == 'demo'): ?>
            <button class="btn-create" onclick="mostrarModalAlerta('Inicia sesión para crear grupos')">Crear Grupo</button>
        <?php else: ?>
            <button class="btn-create" onclick="mostrarModalCrearGrupo()">Crear Grupo</button>
        <?php endif; ?>
    </div>

    <!-- Sección de grupos con menú de opciones - REEMPLAZAR en comunidad.php -->
<div class="grupos-list">
    <?php if ($rol_usuario !== 'demo' && $resultado_grupos && $resultado_grupos->num_rows > 0): ?>
        <?php while ($grupo = $resultado_grupos->fetch_assoc()): 
            // Verificar si el usuario actual puede eliminar este grupo
            $puede_eliminar_grupo = ($grupo['id_creador'] == $usuario_id) || ($rol_usuario == 'admin');
            $es_creador = ($grupo['id_creador'] == $usuario_id);
        ?>
            <div class="grupo-card">
                
                <?php if ($puede_eliminar_grupo): ?>
                    <div class="grupo-menu-container">
                        <button class="btn-menu-grupo" onclick="toggleMenuGrupo(<?php echo $grupo['id_grupo']; ?>)" title="Opciones">
                            ⋮
                        </button>
                        <div class="grupo-menu-opciones" id="menu-grupo-<?php echo $grupo['id_grupo']; ?>" style="display: none;">
                            <button class="menu-opcion-eliminar-grupo" onclick="eliminarGrupo(<?php echo $grupo['id_grupo']; ?>, this)">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M312-144q-29.7 0-50.85-21.15Q240-186.3 240-216v-480h-48v-72h192v-48h192v48h192v72h-48v479.57Q720-186 698.85-165T648-144H312Zm72-144h72v-336h-72v336Zm120 0h72v-336h-72v336Z"/></svg> Eliminar grupo
                            </button>
                            <?php if ($rol_usuario == 'admin' && !$es_creador): ?>
                                <span class="menu-nota-admin-grupo">Como administrador</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="grupo-icon"><?php echo $grupo['icono']; ?></div>
                <div class="grupo-info">
                    <h4><?php echo htmlspecialchars($grupo['nombre_grupo']); ?></h4>
                    <p><?php echo $grupo['contador_miembros']; ?> miembros</p>
                    <p class="grupo-descripcion-mini"><?php echo htmlspecialchars(substr($grupo['descripcion'], 0, 60)); ?>...</p>
                </div>
                <button class="btn-join <?php echo $grupo['usuario_es_miembro'] > 0 ? 'btn-joined' : ''; ?>" 
                        data-grupo-id="<?php echo $grupo['id_grupo']; ?>"
                        onclick="toggleMiembroGrupo(this)">
                    <?php echo $grupo['usuario_es_miembro'] > 0 ? 'Miembro' : 'Unirse'; ?>
                </button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <!-- Grupos de ejemplo para demo -->
        <div class="grupo-card">
            <div class="grupo-icon"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg></div>
            <div class="grupo-info">
                <h4>Dueños de Golden Retriever</h4>
                <p>234 miembros</p>
            </div>
            <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a grupos')">Unirse</button>
        </div>
    <?php endif; ?>
</div>
</section>

    </main>

    <!-- Modal de alerta para usuarios demo -->
    <div class="modal-alerta-demo" id="modalAlertaDemo">
        <div class="contenido-modal-alerta">
            <div class="encabezado-modal-alerta">
                <h3 class="titulo-modal-alerta"><svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#ffea00" stroke-width="0.5"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.61617 3.6419C10.6736 1.80296 13.3268 1.80296 14.3841 3.6419L22.4271 17.6296C23.4813 19.463 22.1579 21.7504 20.0431 21.7504H3.95721C1.84242 21.7504 0.519055 19.463 1.57322 17.6296L9.61617 3.6419ZM12 8.25C12.4142 8.25 12.75 8.58579 12.75 9V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V9C11.25 8.58579 11.5858 8.25 12 8.25ZM12.5675 17.5008C12.8446 17.1929 12.8196 16.7187 12.5117 16.4416C12.2038 16.1645 11.7296 16.1894 11.4525 16.4973L11.4425 16.5084C11.1654 16.8163 11.1904 17.2905 11.4983 17.5676C11.8062 17.8447 12.2804 17.8197 12.5575 17.5119L12.5675 17.5008Z" fill="#ffea00"></path></svg> Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
            </div>
            
                <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm0-80h480v-400H240v400Zm240-120q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80ZM240-160v-400 400Z"/></svg></div>
                <p id="mensajeAlertaDemo">Para acceder a esta función necesitas iniciar sesión o registrarte.</p>
                
                <div class="detalles-alerta">
                    <h4>Con una cuenta podrás:</h4>
                    <ul id="listaBeneficiosAlerta">
                        <li>• Gestionar citas veterinarias</li>
                        <li>• Registrar consultas médicas</li>
                        <li>• Llevar historial de salud</li>
                    </ul>
                </div>
            </div>

             <div class="botones-modal-alerta">
                <button type="button" class="boton-cancelar-alerta" onclick="cerrarModalAlerta()">Más tarde</button>
                <button type="button" class="boton-login-alerta" onclick="irALogin()"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#F19E39"><path d="M280-400q-33 0-56.5-23.5T200-480q0-33 23.5-56.5T280-560q33 0 56.5 23.5T360-480q0 33-23.5 56.5T280-400Zm0 160q-100 0-170-70T40-480q0-100 70-170t170-70q67 0 121.5 33t86.5 87h352l120 120-180 180-80-60-80 60-85-60h-47q-32 54-86.5 87T280-240Zm0-80q56 0 98.5-34t56.5-86h125l58 41 82-61 71 55 75-75-40-40H435q-14-52-56.5-86T280-640q-66 0-113 47t-47 113q0 66 47 113t113 47Z"/></svg> Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/></svg> Registrarse</button>
            </div>
        </div>
    </div>

    <!-- Modal para ver imagen en tamaño completo -->
    <div id="modalImagen" class="modal-imagen" style="display: none;" onclick="cerrarModalImagen()">
        <span class="cerrar-modal-imagen">&times;</span>
        <img class="imagen-modal-contenido" id="imagenExpandida">
        <div class="caption-imagen" id="captionImagen"></div>
    </div>

    <!-- Modal de compartir -->
    <div id="modalCompartir" class="modal-compartir" style="display: none;">
        <div class="modal-compartir-contenido">
            <div class="modal-compartir-header">
                <h3>Compartir Post</h3>
                <button class="cerrar-modal-compartir" onclick="cerrarModalCompartir()">&times;</button>
            </div>
            <div class="modal-compartir-body">
                <div class="compartir-opciones">
                    <button class="btn-compartir-red" onclick="compartirEnFacebook()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        Facebook
                    </button>
                    <button class="btn-compartir-red btn-twitter" onclick="compartirEnTwitter()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                        Twitter
                    </button>
                    <button class="btn-compartir-red btn-instagram" onclick="compartirEnInstagram()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        Instagram
                    </button>
                </div>
                <div class="compartir-link">
                    <input type="text" id="linkCompartir" readonly>
                    <button class="btn-copiar-link" onclick="copiarEnlace()"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M216-144q-29.7 0-50.85-21.15Q144-186.3 144-216v-528q0-29.7 21.15-50.85Q186.3-816 216-816h171q8-31 33.5-51.5T480-888q34 0 59.5 20.5T573-816h171q29.7 0 50.85 21.15Q816-773.7 816-744v528q0 29.7-21.15 50.85Q773.7-144 744-144H216Zm0-72h528v-528H216v528Zm72-72h288v-72H288v72Zm0-156h384v-72H288v72Zm0-156h384v-72H288v72Zm192-168q10.4 0 17.2-6.8 6.8-6.8 6.8-17.2 0-10.4-6.8-17.2-6.8-6.8-17.2-6.8-10.4 0-17.2 6.8-6.8 6.8-6.8 17.2 0 10.4 6.8 17.2 6.8 6.8 17.2 6.8ZM216-216v-528 528Z"/></svg> Copiar enlace</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear evento -->
    <div id="modalCrearEvento" class="modal-crear-evento"  style="display: none;">
        <div class="modal-crear-evento-contenido">
            <div class="modal-crear-evento-header">
                <h3>Crear Nuevo Evento</h3>
                <button class="cerrar-modal-evento" onclick="cerrarModalCrearEvento()">&times;</button>
            </div>
            <div class="modal-crear-evento-body">
                <form id="formCrearEvento" onsubmit="return enviarEvento(event)">
                    <div class="form-group">
                        <label for="titulo_evento">Título del Evento *</label>
                        <input type="text" id="titulo_evento" name="titulo_evento" required maxlength="100" 
                               placeholder="Ej: Jornada de Adopción">
                    </div>

                    <div class="form-group">
                        <label for="descripcion_evento">Descripción *</label>
                        <textarea id="descripcion_evento" name="descripcion_evento" required maxlength="255" 
                                  rows="4" placeholder="Describe el evento y lo que se hará"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_evento">Fecha *</label>
                            <input type="date" id="fecha_evento" name="fecha_evento" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="hora_evento">Hora *</label>
                            <input type="time" id="hora_evento" name="hora_evento" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ubicacion_evento">Ubicación *</label>
                        <input type="text" id="ubicacion_evento" name="ubicacion_evento" required maxlength="100"
                               placeholder="Ej: Parque Central">
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalCrearEvento()">Cancelar</button>
                        <button type="submit" class="btn-crear-evento">Crear Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para crear grupo -->
<div id="modalCrearGrupo" class="modal-crear-evento" style="display: none;">
    <div class="modal-crear-evento-contenido">
        <div class="modal-crear-evento-header">
            <h3>Crear Nuevo Grupo</h3>
            <button class="cerrar-modal-evento" onclick="cerrarModalCrearGrupo()">&times;</button>
        </div>
        <div class="modal-crear-evento-body">
            <form id="formCrearGrupo" onsubmit="return enviarGrupo(event)">
                <div class="form-group">
                    <label for="nombre_grupo">Nombre del Grupo *</label>
                    <input type="text" id="nombre_grupo" name="nombre_grupo" required maxlength="100" 
                           placeholder="Ej: Amantes de los Beagles">
                </div>

                <div class="form-group">
                    <label for="descripcion_grupo">Descripción *</label>
                    <textarea id="descripcion_grupo" name="descripcion_grupo" required maxlength="500" 
                              rows="4" placeholder="Describe el propósito del grupo"></textarea>
                </div>

                <div class="form-group">
                    <label for="icono_grupo">Icono del Grupo *</label>
                    <select id="icono_grupo" name="icono_grupo" required class="select-icono">
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192-96v-283l202 203q53-49 96-106t43-129q0-55-20-106t-59-90L327-720h153l144-144 60 59q42 41 64.5 94T771-599q0 60-23 114.5T681-389l-57 53v240H192Zm204-180L192-481q-8-11-12.5-23t-4.5-25q0-14 4.5-27.5T195-579l86-86 124 110q29 29 42.5 66t13.5 78q0 39-18 74t-47 61Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192-96v-283l202 203q53-49 96-106t43-129q0-55-20-106t-59-90L327-720h153l144-144 60 59q42 41 64.5 94T771-599q0 60-23 114.5T681-389l-57 53v240H192Zm204-180L192-481q-8-11-12.5-23t-4.5-25q0-14 4.5-27.5T195-579l86-86 124 110q29 29 42.5 66t13.5 78q0 39-18 74t-47 61Z"/></svg> Perro</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M120-384q-30 0-51-21t-21-51v-336q0-30 21-51t51-21h720q30 0 51 21t21 51v336q0 30-21 51t-51 21H120Zm25 144-4-72 671-35 4 72-671 35Zm-1 120v-72h672v72H144Zm276-384q75 0 143-28.5T672-624q2 44 38 70t82 26v-192q-46 0-82 26t-38 70q-42-61-109.5-90.5T420-744q-75 0-144 28.5T168-624q38 64 107.5 92T420-504Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#666666"><path d="M120-384q-30 0-51-21t-21-51v-336q0-30 21-51t51-21h720q30 0 51 21t21 51v336q0 30-21 51t-51 21H120Zm25 144-4-72 671-35 4 72-671 35Zm-1 120v-72h672v72H144Zm276-384q75 0 143-28.5T672-624q2 44 38 70t82 26v-192q-46 0-82 26t-38 70q-42-61-109.5-90.5T420-744q-75 0-144 28.5T168-624q38 64 107.5 92T420-504Z"/></svg> Gato</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> Huellas</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="m480-144-50-45q-100-89-165-152.5t-102.5-113Q125-504 110.5-545T96-629q0-89 61-150t150-61q49 0 95 21t78 59q32-38 78-59t95-21q89 0 150 61t61 150q0 43-14 83t-51.5 89q-37.5 49-103 113.5T528-187l-48 43Z"/></svg> Corazón</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#DF9D9B"><path d="M236-118 117-236q-22-20.93-22-50.97Q95-317 117-338l506-504q20.67-21 50.34-21Q703-863 724-842l119 118q21 20.93 21 50.97Q864-643 843-622L337-118q-20.67 21-50.34 21Q257-97 236-118Zm278-107 221-221 109 109q21 20 20.5 50T842-236L724-117q-20.93 21-50.97 21Q643-96 622-117L514-225Zm-34.21-142q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm-77-77q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Zm154 0q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5ZM225-514 114.92-624.08Q94-645 95-675t22-51l119-117q20.93-21 50.97-21Q317-864 338-843l108 108-221 221Zm254.79-7q15.21 0 25.71-10.29t10.5-25.5q0-15.21-10.29-25.71t-25.5-10.5q-15.21 0-25.71 10.29t-10.5 25.5q0 15.21 10.29 25.71t25.5 10.5Z"/></svg> Veterinaria</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M840-288v-276L480-384 48-600l432-216 432 216v312h-72ZM480-144 216-276v-159l264 132 264-132v159L480-144Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M840-288v-276L480-384 48-600l432-216 432 216v312h-72ZM480-144 216-276v-159l264 132 264-132v159L480-144Z"/></svg> Educación</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M552-96v-213l-76-75-32 160-252-51 14-70 181 36 52-263-79 34v106h-72v-154l185-78q9-4 18.5-6t19.5-2q27 0 48.5 12.5T595-623l14 23q30 51 68 73.5t91 22.5v72q-57 0-106.5-25T577-528l-23 120 70 70v242h-72Zm24-600q-35 0-59.5-24.5T492-780q0-35 24.5-59.5T576-864q35 0 59.5 24.5T660-780q0 35-24.5 59.5T576-696Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M552-96v-213l-76-75-32 160-252-51 14-70 181 36 52-263-79 34v106h-72v-154l185-78q9-4 18.5-6t19.5-2q27 0 48.5 12.5T595-623l14 23q30 51 68 73.5t91 22.5v72q-57 0-106.5-25T577-528l-23 120 70 70v242h-72Zm24-600q-35 0-59.5-24.5T492-780q0-35 24.5-59.5T576-864q35 0 59.5 24.5T660-780q0 35-24.5 59.5T576-696Z"/></svg> Actividad</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M48-264v-57q0-39 39-63t105-24q14 0 26 1t23 3q-12 18-18.5 39.11Q216-343.77 216-322v58H48Zm216 0v-58q0-28 14.5-50t43.5-39q29-17 69-25t89.5-8q49.5 0 89 8t68.5 25q29 16 43.5 38.69Q696-349.62 696-322v58H264Zm480 0v-58q0-22-6.5-42.5T719-404q9-2 20.5-3t28.5-1q66 0 105 24t39 63v57H744ZM192-456q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm576 0q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm-288-36q-45 0-76.5-31.52T372-600.07q0-44.93 31.52-76.43 31.52-31.5 76.55-31.5 44.93 0 76.43 31.55Q588-644.9 588-600q0 45-31.55 76.5T480-492Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M48-264v-57q0-39 39-63t105-24q14 0 26 1t23 3q-12 18-18.5 39.11Q216-343.77 216-322v58H48Zm216 0v-58q0-28 14.5-50t43.5-39q29-17 69-25t89.5-8q49.5 0 89 8t68.5 25q29 16 43.5 38.69Q696-349.62 696-322v58H264Zm480 0v-58q0-22-6.5-42.5T719-404q9-2 20.5-3t28.5-1q66 0 105 24t39 63v57H744ZM192-456q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm576 0q-30 0-51-21t-21-51q0-30 21-51t51-21q30 0 51 21t21 51q0 30-21 51t-51 21Zm-288-36q-45 0-76.5-31.52T372-600.07q0-44.93 31.52-76.43 31.52-31.5 76.55-31.5 44.93 0 76.43 31.55Q588-644.9 588-600q0 45-31.55 76.5T480-492Z"/></svg> Comunidad</option>
                        <option value="<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="m243-144 63-266L96-589l276-24 108-251 108 252 276 23-210 179 63 266-237-141-237 141Z"/></svg>"><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFF55"><path d="m243-144 63-266L96-589l276-24 108-251 108 252 276 23-210 179 63 266-237-141-237 141Z"/></svg> Especial</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalCrearGrupo(this)">Cancelar</button>
                    <button type="submit" class="btn-crear-grupo" id="btnSubmit">Crear Grupo</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js" ></script>
    <script src="js/notificaciones.js"></script>
    <script src="js/modal-alerta-demo.js"></script>
    <script src="js/comunidad.js"></script>
</body>
</html>