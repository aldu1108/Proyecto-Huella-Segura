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
</head>

<body class="admin-panel">
    <header class="header-admin">
        <div class="logo-admin">
            <h1>🛡️ Panel Administrativo</h1>
            <p>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
        </div>
        <div class="acciones-admin">
            <span class="hora-sesion">Sesión iniciada: <?php echo $_SESSION['hora_login']; ?></span>
            <a href="logout.php" class="boton-logout">🚪 Cerrar Sesión</a>
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
            <h2>📊 Estadísticas del Sistema</h2>
            <div class="tarjetas-estadisticas">
                <div class="tarjeta-stat">
                    <div class="icono-stat">👥</div>
                    <div class="numero-stat"><?php echo $total_usuarios; ?></div>
                    <div class="texto-stat">Usuarios Activos</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">🐾</div>
                    <div class="numero-stat"><?php echo $total_mascotas; ?></div>
                    <div class="texto-stat">Mascotas Registradas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">🩺</div>
                    <div class="numero-stat"><?php echo $total_veterinarios; ?></div>
                    <div class="texto-stat">Veterinarios Certificados</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">❤️</div>
                    <div class="numero-stat"><?php echo $total_adopciones; ?></div>
                    <div class="texto-stat">Adopciones Publicadas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">🔍</div>
                    <div class="numero-stat"><?php echo $total_perdidas; ?></div>
                    <div class="texto-stat">Mascotas Perdidas</div>
                </div>

                <div class="tarjeta-stat">
                    <div class="icono-stat">📅</div>
                    <div class="numero-stat"><?php echo $total_citas; ?></div>
                    <div class="texto-stat">Citas Veterinarias</div>
                </div>
            </div>
        </section>

        <!-- Acciones rápidas -->
        <section class="acciones-rapidas">
            <h2>⚡ Acciones Rápidas</h2>
            <div class="botones-accion">
                <button class="boton-accion usuarios" onclick="verUsuarios()">
                    👥 Gestionar Usuarios
                </button>
                <button class="boton-accion veterinarios" onclick="verVeterinarios()">
                    🩺 Verificar Veterinarios
                </button>
                <button class="boton-accion reportes" onclick="verReportes()">
                    📋 Ver Reportes
                </button>
                <button class="boton-accion configuracion" onclick="configurarSistema()">
                    ⚙️ Configuración Sistema
                </button>
            </div>
        </section>

        <!-- Usuarios recientes -->
        <section class="usuarios-recientes">
            <h2>👥 Usuarios Recientes</h2>
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
            <h2>⏳ Veterinarios Pendientes de Verificación</h2>
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
                                            ✅ Aprobar
                                        </a>
                                        <a href="aprobar-veterinario.php?accion=rechazar&email=<?php echo urlencode($veterinario['email_usuario']); ?>"
                                            class="boton-pequeno rechazar"
                                            onclick="return confirm('¿Rechazar y ELIMINAR a <?php echo htmlspecialchars($veterinario['nombre_usuario']); ?>? Esta acción no se puede deshacer.')">
                                            ❌ Rechazar
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
            <h2>💡 Información del Sistema</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>🔒 Estado de Acceso:</strong> Administrador autenticado
                </div>
                <div class="info-item">
                    <strong>📅 Fecha:</strong> <?php echo date('d/m/Y'); ?>
                </div>
                <div class="info-item">
                    <strong>⏰ Hora:</strong> <?php echo date('H:i:s'); ?>
                </div>
                <div class="info-item">
                    <strong>🌐 Usuario:</strong> <?php echo $_SESSION['usuario_nombre']; ?>
                </div>
            </div>
        </section>
    </div>

    <script>
        function verUsuarios() {
            mostrarMensaje('✅ Función de gestión de usuarios - Por implementar', 'info');
        }

        function verVeterinarios() {
            mostrarMensaje('✅ Función de verificación de veterinarios - Por implementar', 'info');
        }

        function verReportes() {
            mostrarMensaje('✅ Función de reportes del sistema - Por implementar', 'info');
        }

        function configurarSistema() {
            mostrarMensaje('✅ Función de configuración del sistema - Por implementar', 'info');
        }

        function verUsuario(email) {
            mostrarMensaje(`📋 Viendo usuario: ${email}`, 'info');
        }

        function editarUsuario(email) {
            mostrarMensaje(`✏️ Editando usuario: ${email}`, 'warning');
        }

        function aprobarVeterinario(email) {
            // Función removida - ahora se usa enlace directo
        }

        function rechazarVeterinario(email) {
            // Función removida - ahora se usa enlace directo
        }

        function mostrarMensaje(mensaje, tipo) {
            const div = document.createElement('div');
            div.className = `mensaje-admin mensaje-${tipo}`;
            div.innerHTML = `
                <span>${mensaje}</span>
                <button onclick="this.parentElement.remove()">✕</button>
            `;

            div.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${tipo === 'success' ? '#27ae60' : tipo === 'error' ? '#e74c3c' : tipo === 'warning' ? '#f39c12' : '#3498db'};
                color: white;
                padding: 12px 16px;
                border-radius: 8px;
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            `;

            document.body.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }
    </script>

    <style>
        .admin-panel {
            background: #f8f9fa;
            min-height: 100vh;
        }

        .header-admin {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo-admin h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .logo-admin p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .acciones-admin {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hora-sesion {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .boton-logout {
            background: #e74c3c;
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .boton-logout:hover {
            background: #c0392b;
        }

        .contenedor-admin {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .estadisticas-admin,
        .acciones-rapidas,
        .usuarios-recientes,
        .veterinarios-pendientes,
        .info-sistema {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .estadisticas-admin h2,
        .acciones-rapidas h2,
        .usuarios-recientes h2,
        .veterinarios-pendientes h2,
        .info-sistema h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }

        .tarjetas-estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .tarjeta-stat {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s;
        }

        .tarjeta-stat:hover {
            transform: translateY(-5px);
        }

        .icono-stat {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .numero-stat {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .texto-stat {
            font-size: 1rem;
            opacity: 0.9;
        }

        .botones-accion {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .boton-accion {
            padding: 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
            transition: all 0.3s;
        }

        .boton-accion:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .boton-accion.usuarios {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

        .boton-accion.veterinarios {
            background: linear-gradient(135deg, #27ae60, #229954);
        }

        .boton-accion.reportes {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .boton-accion.configuracion {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }

        .tabla-admin {
            overflow-x: auto;
        }

        .tabla-admin table {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-admin th,
        .tabla-admin td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e8e8e8;
        }

        .tabla-admin th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        .boton-pequeno {
            padding: 0.5rem 1rem;
            margin: 0.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .boton-pequeno.ver {
            background: #3498db;
            color: white;
        }

        .boton-pequeno.editar {
            background: #f39c12;
            color: white;
        }

        .boton-pequeno.aprobar {
            background: #27ae60;
            color: white;
        }

        .boton-pequeno.rechazar {
            background: #e74c3c;
            color: white;
        }

        .boton-pequeno:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .sin-datos {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .info-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .mensaje-admin {
            position: relative;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
        }

        .mensaje-admin.mensaje-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje-admin.mensaje-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .mensaje-admin.mensaje-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .boton-pequeno {
            text-decoration: none;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .header-admin {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .contenedor-admin {
                padding: 1rem;
            }

            .tarjetas-estadisticas {
                grid-template-columns: repeat(2, 1fr);
            }

            .botones-accion {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>

</html>

<?php cerrarConexion(); ?>