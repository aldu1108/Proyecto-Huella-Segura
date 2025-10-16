<?php
include_once('config/conexion.php');
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = $_SESSION['rol'];

// Procesar formulario de nuevo post
$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo_post']) && $rol_usuario !== 'demo') {
    
    // Validar que vengan los datos
    if (empty($_POST['titulo_post']) || empty($_POST['contenido_post']) || empty($_POST['tipo_post'])) {
        $mensaje_error = "Por favor completa todos los campos obligatorios";
    } else {
        $titulo = $conexion->real_escape_string(trim($_POST['titulo_post']));
        $contenido = $conexion->real_escape_string(trim($_POST['contenido_post']));
        $tipo_post = $conexion->real_escape_string($_POST['tipo_post']);
        $imagenes = [];
        
        // Crear directorio si no existe
        if (!file_exists('imagenes/posts')) {
            if (!mkdir('imagenes/posts', 0777, true)) {
                $mensaje_error = "Error: No se pudo crear la carpeta de imágenes";
            }
        }
        
        // Procesar múltiples imágenes solo si no hay errores previos
        if (empty($mensaje_error) && isset($_FILES['imagenes_post']) && !empty($_FILES['imagenes_post']['name'][0])) {
            $total_imagenes = count($_FILES['imagenes_post']['name']);
            
            for ($i = 0; $i < $total_imagenes && $i < 5; $i++) { // Máximo 5 imágenes
                if ($_FILES['imagenes_post']['error'][$i] === 0) {
                    $extension = strtolower(pathinfo($_FILES['imagenes_post']['name'][$i], PATHINFO_EXTENSION));
                    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($extension, $extensiones_permitidas) && $_FILES['imagenes_post']['size'][$i] <= 5000000) {
                        $nombre_archivo = 'post_' . $usuario_id . '_' . time() . '_' . $i . '.' . $extension;
                        $ruta_destino = 'imagenes/posts/' . $nombre_archivo;
                        
                        if (move_uploaded_file($_FILES['imagenes_post']['tmp_name'][$i], $ruta_destino)) {
                            $imagenes[] = $nombre_archivo;
                        } else {
                            $mensaje_error = "Error al subir la imagen " . ($i + 1);
                        }
                    }
                }
            }
        }
        
        // Solo insertar si no hay errores
        if (empty($mensaje_error)) {
            $imagenes_str = !empty($imagenes) ? implode(',', $imagenes) : NULL;
            
            // Insertar post en la base de datos
            $sql_insert = "INSERT INTO post_comunidad (titulo, contenido, fecha, id_usuario, tipo_post, imagen_post) 
                           VALUES ('$titulo', '$contenido', NOW(), $usuario_id, '$tipo_post', " . 
                           ($imagenes_str ? "'$imagenes_str'" : "NULL") . ")";
            
            // Debug: descomentar para ver la consulta SQL
            // echo "SQL: " . $sql_insert . "<br>";
            
            if ($conexion->query($sql_insert)) {
                $mensaje_exito = "¡Post publicado exitosamente!";
                header("Location: comunidad.php?success=1");
                exit();
            } else {
                $mensaje_error = "Error al publicar el post en la base de datos: " . $conexion->error . "<br>SQL: " . $sql_insert;
            }
        }
    }
}

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
    $consulta_posts = "SELECT a.*, u.nombre_usuario, u.apellido_usuario, u.foto_usuario, 
        a.conteo_likes as total_likes, 
        a.conteo_comentarios as total_comentarios, 
        (SELECT COUNT(*) FROM likes_post WHERE id_post = a.id_post AND id_usuario = $usuario_id) as usuario_dio_like 
        FROM post_comunidad a 
        JOIN usuarios u ON a.id_usuario = u.id_usuario 
        ORDER BY a.fecha DESC LIMIT 20"; 
    
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
</head>
<body>
    <!-- Header -->
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Mensajes de éxito/error -->
        <?php if ($mensaje_exito): ?>
            <div class="mensaje-exito"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        <?php if ($mensaje_error): ?>
            <div class="mensaje-error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="mensaje-exito">¡Post publicado exitosamente!</div>
        <?php endif; ?>

        <!-- Header de comunidad -->
        <section class="comunidad-header">
            <h2 class="comunidad-title">Comunidad PetCare</h2>
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
            <button class="section-btn active" data-section="feed">📰 Feed</button>
            <button class="section-btn" data-section="eventos">📅 Eventos</button>
            <button class="section-btn" data-section="grupos">👥 Grupos</button>
        </nav>

        <!-- Sección Feed -->
        <section class="feed-section" id="feedSection">
            <!-- Crear post -->
            <div class="create-post">
                <form method="POST" enctype="multipart/form-data" id="formCrearPost" <?php echo $rol_usuario == 'demo' ? 'onsubmit="return false;"' : ''; ?>>
                        <input type="text" name="titulo_post" placeholder="Título del post" required maxlength="100" class="input-titulo-post" <?php echo $rol_usuario == 'demo' ? 'readonly onclick="mostrarModalAlerta(\'Inicia sesión para crear posts\n\nRegístrate para poder:\n• Compartir experiencias con tu mascota\n• Hacer preguntas a la comunidad\n• Conectar con otros dueños\')"' : ''; ?>>
                        
                        <textarea name="contenido_post" placeholder="¿Qué quieres compartir con la comunidad?" required maxlength="500" class="textarea-contenido-post" <?php echo $rol_usuario == 'demo' ? 'readonly onclick="mostrarModalAlerta(\'Inicia sesión para crear posts\n\nRegístrate para poder:\n• Compartir experiencias con tu mascota\n• Hacer preguntas a la comunidad\n• Conectar con otros dueños\')"' : ''; ?>></textarea>
                        
                        <div class="post-options">
                            <div class="tipo-post-selector">
                                <label for="tipo_post">Tipo de post:</label>
                                <select name="tipo_post" id="tipo_post" required <?php echo $rol_usuario == 'demo' ? 'disabled' : ''; ?>>
                                    <option value="general">General</option>
                                    <option value="logro">🏆 Logro</option>
                                    <option value="paseo">🐾 Paseo</option>
                                    <option value="ayuda">❤️ Ayuda</option>
                                </select>
                            </div>
                            
                            <div class="acciones-post">
                                <?php if ($rol_usuario == 'demo'): ?>
                                    <button type="button" class="btn-adjuntar" onclick="mostrarModalAlerta('Inicia sesión para adjuntar imágenes\n\nRegístrate para compartir fotos de tus mascotas con la comunidad')">
                                        📎 Adjuntar imágenes
                                    </button>
                                    <button type="button" class="btn-publicar" onclick="mostrarModalAlerta('Inicia sesión para publicar\n\nRegístrate para poder:\n• Compartir experiencias con tu mascota\n• Hacer preguntas a la comunidad\n• Conectar con otros dueños')">
                                        ✉️ Publicar
                                    </button>
                                <?php else: ?>
                                    <label for="imagenes_post" class="btn-adjuntar">
                                        📎 Adjuntar imágenes
                                        <input type="file" name="imagenes_post[]" id="imagenes_post" accept="image/*" multiple style="display: none;" onchange="previsualizarImagenes(this)">
                                    </label>
                                    
                                    <button type="submit" name="crear_post" class="btn-publicar">
                                        ✉️ Publicar
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
                                        $badge_text = '🏆 Logro';
                                        break;
                                    case 'paseo':
                                        $badge_class = 'badge-paseo';
                                        $badge_text = '🐾 Paseo';
                                        break;
                                    case 'ayuda':
                                        $badge_class = 'badge-ayuda';
                                        $badge_text = '❤️ Ayuda';
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
                                                🗑️ Eliminar post
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
                                    ❤️ <?php echo $post['total_likes']; ?>
                                </button>
                                <button class="action-btn btn-comentarios" onclick="toggleComentarios(<?php echo $post['id_post']; ?>)">
                                    💬 <?php echo $post['total_comentarios']; ?>
                                </button>
                                <button class="action-btn btn-compartir" onclick="compartirPost(<?php echo $post['id_post']; ?>, '<?php echo htmlspecialchars($post['titulo']); ?>')">
                                    🔤 Compartir
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
                                    🗑️
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
                ➤
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
                            <span class="post-badge badge-logro">🏆 Logro</span>
                        </div>
                        <div class="post-content">
                            <h3 class="post-titulo">¡Primer examen veterinario aprobado!</h3>
                            <p>¡Luna acaba de pasar su primer examen veterinario con excelentes resultados! 🎉 Gracias al Dr. Martínez por el cuidado excepcional.</p>
                            <div class="post-tag">🐾 Luna</div>
                        </div>
                        <div class="post-actions">
                            <?php if ($rol_usuario == 'demo'): ?>
                                <button class="action-btn" onclick="mostrarModalAlerta('Inicia sesión para dar me gusta')">❤️ 24</button>
                                <button class="action-btn" onclick="mostrarModalAlerta('Inicia sesión para comentar')">💬 5</button>
                                <button class="action-btn" onclick="mostrarModalAlerta('Inicia sesión para compartir')">🔤 Compartir</button>
                            <?php else: ?>
                                <button class="action-btn">❤️ 24</button>
                                <button class="action-btn">💬 5</button>
                                <button class="action-btn">🔤 Compartir</button>
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
                            <span class="post-badge badge-ayuda">❤️ Ayuda</span>
                        </div>
                        <div class="post-content">
                            <h3 class="post-titulo">Busco veterinario especialista en gatos</h3>
                            <p>¿Alguien sabe de un buen veterinario especialista en gatos en la zona de Salamanca? Mi gatito necesita una revisión especializada.</p>
                            <div class="location-tag">📍 Madrid, Salamanca</div>
                        </div>
                        <div class="post-actions">
                            <button class="action-btn">❤️ 12</button>
                            <button class="action-btn">💬 8</button>
                            <button class="action-btn">🔤 Compartir</button>
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
                                🗑️ Eliminar evento
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
                        🕐 <?php echo $fecha_evento->format('H:i'); ?> 
                        📍 <?php echo htmlspecialchars($evento['ubicacion']); ?> 
                        👥 <?php echo $evento['contador_asistentes']; ?> asistirán
                    </div>
                    <?php if ($rol_usuario == 'demo'): ?>
                        <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a eventos')">Unirse al Evento</button>
                    <?php else: ?>
                        <button class="btn-join <?php echo $evento['usuario_participa'] > 0 ? 'btn-joined' : ''; ?>" 
                                data-evento-id="<?php echo $evento['id_evento']; ?>"
                                onclick="toggleParticipacion(this)">
                            <?php echo $evento['usuario_participa'] > 0 ? '✓ Participando' : 'Unirse al Evento'; ?>
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
                    🕐 10:00 📍 Parque del Retiro 👥 45 asistirán
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
                                🗑️ Eliminar grupo
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
                    <?php echo $grupo['usuario_es_miembro'] > 0 ? '✓ Miembro' : 'Unirse'; ?>
                </button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <!-- Grupos de ejemplo para demo -->
        <div class="grupo-card">
            <div class="grupo-icon">🐕</div>
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
                <h3 class="titulo-modal-alerta">⚠️ Funcionalidad no disponible</h3>
                <button class="boton-cerrar-modal-alerta" onclick="cerrarModalAlerta()">×</button>
            </div>
            
            <div class="cuerpo-modal-alerta">
                <div class="icono-alerta-demo">🔒</div>
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
                <button type="button" class="boton-login-alerta" onclick="irALogin()">🔓 Iniciar Sesión</button>
                <button type="button" class="boton-registro-alerta" onclick="irARegistro()">📝 Registrarse</button>
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
                    <button class="btn-copiar-link" onclick="copiarEnlace()">📋 Copiar enlace</button>
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
                        <option value="🐕">🐕 Perro</option>
                        <option value="🐱">🐱 Gato</option>
                        <option value="🐾">🐾 Huellas</option>
                        <option value="❤️">❤️ Corazón</option>
                        <option value="🏥">🏥 Veterinaria</option>
                        <option value="🎓">🎓 Educación</option>
                        <option value="🏃">🏃 Actividad</option>
                        <option value="👥">👥 Comunidad</option>
                        <option value="🌟">🌟 Especial</option>
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
    <script src="js/modal-alerta-demo.js"></script>
    <script src="js/comunidad.js"></script>
</body>
</html>