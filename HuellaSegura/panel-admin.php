<?php
include_once('config/conexion.php');
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: login-admin.php");
    exit();
}

// Obtener estadísticas básicas
$total_usuarios = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'")->fetch_assoc()['total'];
$total_mascotas = $conexion->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'activo'")->fetch_assoc()['total'];
$total_veterinarios = $conexion->query("SELECT COUNT(*) as total FROM veterinario WHERE certificado = 1")->fetch_assoc()['total'];
$total_adopciones = $conexion->query("SELECT COUNT(*) as total FROM publicacion_adopcion")->fetch_assoc()['total'];
$total_perdidas = $conexion->query("SELECT COUNT(*) as total FROM publicacion_perdida")->fetch_assoc()['total'];
$total_citas = $conexion->query("SELECT COUNT(*) as total FROM citas_veterinarias")->fetch_assoc()['total'];

// Usuarios recientes
$usuarios_recientes = $conexion->query("SELECT nombre_usuario, apellido_usuario, email_usuario, 
                                    DATE_FORMAT(NOW(), '%Y-%m-%d') as fecha_registro 
                                    FROM usuarios WHERE estado = 'activo' 
                                    ORDER BY id_usuario DESC LIMIT 10");

// Veterinarios pendientes de verificación
$veterinarios_pendientes = $conexion->query("SELECT u.nombre_usuario, u.apellido_usuario, u.email_usuario, v.especialidad, v.clinica 
                                            FROM usuarios u 
                                            JOIN veterinario v ON u.id_usuario = v.id_usuario 
                                            WHERE v.certificado = 0 LIMIT 10");
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
            <a href="logout.php" class="boton-logout">Cerrar Sesión</a>
        </div>
    </header>

    <div class="contenedor-admin">
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
                                <td><?php echo $usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario']; ?></td>
                                <td><?php echo $usuario['email_usuario']; ?></td>
                                <td><?php echo $usuario['fecha_registro']; ?></td>
                                <td>
                                    <button class="boton-pequeno ver">Ver</button>
                                    <button class="boton-pequeno editar">Editar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Veterinarios pendientes -->
        <section class="veterinarios-pendientes">
            <h2>⏳ Veterinarios Pendientes de Verificación</h2>
            <div class="tabla-admin">
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
                                <td><?php echo $veterinario['nombre_usuario'] . ' ' . $veterinario['apellido_usuario']; ?>
                                </td>
                                <td><?php echo $veterinario['email_usuario']; ?></td>
                                <td><?php echo $veterinario['especialidad']; ?></td>
                                <td><?php echo $veterinario['clinica']; ?></td>
                                <td>
                                    <button class="boton-pequeno aprobar">✅ Aprobar</button>
                                    <button class="boton-pequeno rechazar">❌ Rechazar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function verUsuarios() {
            alert('Funcionalidad de gestión de usuarios - Por implementar');
        }

        function verVeterinarios() {
            alert('Funcionalidad de verificación de veterinarios - Por implementar');
        }

        function verReportes() {
            alert('Funcionalidad de reportes del sistema - Por implementar');
        }

        function configurarSistema() {
            alert('Funcionalidad de configuración del sistema - Por implementar');
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
        }

        .logo-admin h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .boton-logout {
            background: #e74c3c;
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
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
        .veterinarios-pendientes {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .tarjetas-estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .tarjeta-stat {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
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

        .botones-accion {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .boton-accion {
            padding: 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
            transition: transform 0.3s;
        }

        .boton-accion:hover {
            transform: translateY(-2px);
        }

        .boton-accion.usuarios {
            background: #3498db;
        }

        .boton-accion.veterinarios {
            background: #27ae60;
        }

        .boton-accion.reportes {
            background: #f39c12;
        }

        .boton-accion.configuracion {
            background: #9b59b6;
        }

        .tabla-admin {
            margin-top: 1.5rem;
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
        }

        .boton-pequeno {
            padding: 0.5rem 1rem;
            margin: 0.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
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

        @media (max-width: 768px) {
            .header-admin {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .contenedor-admin {
                padding: 1rem;
            }
        }
    </style>
</body>

</html>

<?php cerrarConexion(); ?>