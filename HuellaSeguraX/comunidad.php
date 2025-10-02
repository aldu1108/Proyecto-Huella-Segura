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
    $consulta_posts = "SELECT p.*, u.nombre_usuario, u.apellido_usuario, u.foto_usuario, 
        p.conteo_likes as total_likes, 
        p.conteo_comentarios as total_comentarios, 
        (SELECT COUNT(*) FROM likes_post WHERE id_post = p.id_post AND id_usuario = $usuario_id) as usuario_dio_like 
        FROM post_comunidad p 
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        ORDER BY p.fecha DESC LIMIT 20"; 
    
    $resultado_posts = $conexion->query($consulta_posts);
}

// Obtener eventos próximos
$consulta_eventos = "SELECT * FROM eventos_comunidad WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 5";
$resultado_eventos = $conexion->query($consulta_eventos);
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
                            
                            <!-- Sección de comentarios (colapsable) -->
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
                                        while ($comentario = $resultado_comentarios->fetch_assoc()): ?>
                                            <div class="comentario-item">
                                                <div class="comentario-avatar" style="background-image: url('imagenes/<?php echo $comentario['foto_usuario']; ?>')"></div>
                                                <div class="comentario-contenido">
                                                    <div class="comentario-header">
                                                        <span class="comentario-autor"><?php echo htmlspecialchars($comentario['nombre_usuario'] . ' ' . $comentario['apellido_usuario']); ?></span>
                                                        <span class="comentario-fecha"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?></span>
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
            <div class="section-header">
                <h3>Próximos Eventos</h3>
                <?php if ($rol_usuario == 'demo'): ?>
                    <button class="btn-create" onclick="mostrarModalAlerta('Inicia sesión para crear eventos\n\nRegístrate para poder:\n• Organizar eventos para mascotas\n• Invitar a otros miembros\n• Gestionar asistentes')">Crear Evento</button>
                <?php else: ?>
                    <button class="btn-create">Crear Evento</button>
                <?php endif; ?>
            </div>

            <div class="eventos-list">
                <div class="evento-card">
                    <div class="evento-date">
                        <div class="date-day">15</div>
                        <div class="date-month">Feb</div>
                    </div>
                    <div class="evento-info">
                        <h4>Adopción Solidaria</h4>
                        <div class="evento-details">
                            🕐 10:00 📍 Parque del Retiro 👥 45 asistirán
                        </div>
                        <?php if ($rol_usuario == 'demo'): ?>
                            <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a eventos\n\nCrea una cuenta para participar en eventos de la comunidad')">Unirse al Evento</button>
                        <?php else: ?>
                            <button class="btn-join">Unirse al Evento</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="evento-card">
                    <div class="evento-date">
                        <div class="date-day">18</div>
                        <div class="date-month">Feb</div>
                    </div>
                    <div class="evento-info">
                        <h4>Taller de Primeros Auxilios</h4>
                        <div class="evento-details">
                            🕐 16:00 📍 Centro Veterinario 👥 12 asistirán
                        </div>
                        <button class="btn-join">Unirse al Evento</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Grupos -->
        <section class="grupos-section" id="gruposSection" style="display: none;">
            <div class="section-header">
                <h3>Grupos Populares</h3>
                <?php if ($rol_usuario == 'demo'): ?>
                    <button class="btn-create" onclick="mostrarModalAlerta('Inicia sesión para crear grupos\n\nRegístrate para poder:\n• Crear grupos temáticos\n• Moderar discusiones\n• Conectar con dueños similares')">Crear Grupo</button>
                <?php else: ?>
                    <button class="btn-create">Crear Grupo</button>
                <?php endif; ?>
            </div>

            <div class="grupos-list">
                <div class="grupo-card">
                    <div class="grupo-icon">🐕</div>
                    <div class="grupo-info">
                        <h4>Dueños de Golden Retriever</h4>
                        <p>234 miembros</p>
                    </div>
                    <?php if ($rol_usuario == 'demo'): ?>
                        <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a grupos\n\nCrea una cuenta para formar parte de grupos temáticos')">Unirse</button>
                    <?php else: ?>
                        <button class="btn-join">Unirse</button>
                    <?php endif; ?>
                </div>

                <div class="grupo-card">
                    <div class="grupo-icon">🐱</div>
                    <div class="grupo-info">
                        <h4>Gatos de Madrid</h4>
                        <p>189 miembros</p>
                    </div>
                    <?php if ($rol_usuario == 'demo'): ?>
                        <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a grupos\n\nCrea una cuenta para formar parte de grupos temáticos')">Unirse</button>
                    <?php else: ?>
                        <button class="btn-join">Unirse</button>
                    <?php endif; ?>
                </div>

                <div class="grupo-card">
                    <div class="grupo-icon">🏥</div>
                    <div class="grupo-info">
                        <h4>Primeros Auxilios Pet</h4>
                        <p>156 miembros</p>
                    </div>
                    <?php if ($rol_usuario == 'demo'): ?>
                        <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a grupos\n\nCrea una cuenta para formar parte de grupos temáticos')">Unirse</button>
                    <?php else: ?>
                        <button class="btn-join">Unirse</button>
                    <?php endif; ?>
                </div>

                <div class="grupo-card">
                    <div class="grupo-icon">❤️</div>
                    <div class="grupo-info">
                        <h4>Adopción Responsable</h4>
                        <p>203 miembros</p>
                    </div>
                    <?php if ($rol_usuario == 'demo'): ?>
                        <button class="btn-join" onclick="mostrarModalAlerta('Inicia sesión para unirte a grupos\n\nCrea una cuenta para formar parte de grupos temáticos')">Unirse</button>
                    <?php else: ?>
                        <button class="btn-join">Unirse</button>
                    <?php endif; ?>
                </div>
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

    <!-- Navegación inferior -->
    <nav>
        <?php include_once('includes/footer.php'); ?>
    </nav>

    <script src="js/scripts.js" ></script>
    <script src="js/modal-alerta-demo.js"></script>
    <script src="js/comunidad.js"></script>
</body>
</html>