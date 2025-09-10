<?php
include_once('config/conexion.php');
session_start();

$mensaje_error = "";

// Credenciales de administrador (en producción esto debería estar en base de datos con hash)
$admin_email = "admin@huellasegura.com";
$admin_password = "admin123";

// Verificar si ya hay sesión activa de admin
if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == true) {
    header("Location: panel-admin.php");
    exit();
}

// Procesar formulario de login de admin
if ($_POST) {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    $codigo_seguridad = $_POST['codigo_seguridad'];
    
    // Código de seguridad adicional para admin
    $codigo_correcto = "PETCARE2025";
    
    if (!empty($email) && !empty($contraseña) && !empty($codigo_seguridad)) {
        if ($email == $admin_email && $contraseña == $admin_password && $codigo_seguridad == $codigo_correcto) {
            // Crear sesión de administrador
            $_SESSION['usuario_id'] = 1; // ID especial para admin
            $_SESSION['usuario_nombre'] = "Administrador";
            $_SESSION['usuario_apellido'] = "Sistema";
            $_SESSION['es_admin'] = true;
            $_SESSION['nivel_acceso'] = 'superadmin';
            $_SESSION['hora_login'] = date('Y-m-d H:i:s');
            
            header("Location: panel-admin.php");
            exit();
        } else {
            $mensaje_error = "Credenciales de administrador incorrectas";
        }
    } else {
        $mensaje_error = "Por favor complete todos los campos de seguridad";
    }
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
<body class="admin-body">
    <div class="fondo-admin">
        <div class="contenedor-admin">
            <div class="encabezado-admin">
                <div class="logo-admin">
                    <h1>🛡️ PANEL ADMINISTRATIVO</h1>
                    <p>Huella Segura - Sistema de Gestión</p>
                </div>
                <div class="indicador-seguridad">
                    <span class="nivel-seguridad">🔐 ACCESO RESTRINGIDO</span>
                </div>
            </div>

            <div class="formulario-admin">
                <?php if (!empty($mensaje_error)): ?>
                    <div class="alerta-seguridad">
                        <strong>⚠️ ACCESO DENEGADO</strong>
                        <p><?php echo $mensaje_error; ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="form-seguro">
                    <div class="seccion-credenciales">
                        <h3>Credenciales de Administrador</h3>
                        
                        <div class="campo-admin">
                            <label>Email Administrativo</label>
                            <div class="input-seguro">
                                <span class="icono-seguridad">👤</span>
                                <input type="email" name="email" placeholder="admin@huellasegura.com" required>
                            </div>
                        </div>

                        <div class="campo-admin">
                            <label>Contraseña Maestra</label>
                            <div class="input-seguro">
                                <span class="icono-seguridad">🔑</span>
                                <input type="password" name="contraseña" placeholder="Contraseña administrativa" required>
                            </div>
                        </div>

                        <div class="campo-admin">
                            <label>Código de Seguridad</label>
                            <div class="input-seguro">
                                <span class="icono-seguridad">🔐</span>
                                <input type="password" name="codigo_seguridad" placeholder="Código de acceso especial" required>
                            </div>
                            <small class="ayuda-texto">Código adicional de seguridad requerido</small>
                        </div>
                    </div>

                    <div class="verificaciones-seguridad">
                        <div class="verificacion-item">
                            <input type="checkbox" id="terminos-admin" required>
                            <label for="terminos-admin">Acepto la responsabilidad del acceso administrativo</label>
                        </div>
                        
                        <div class="verificacion-item">
                            <input type="checkbox" id="auditoria" required>
                            <label for="auditoria">Entiendo que todas las acciones serán auditadas</label>
                        </div>
                    </div>

                    <button type="submit" class="boton-admin-acceso">
                        🚀 ACCEDER AL PANEL ADMINISTRATIVO
                    </button>
                </form>

                <div class="info-admin">
                    <h4>Características del Panel Administrativo</h4>
                    <div class="caracteristicas-admin">
                        <div class="caracteristica-admin">
                            <span class="icono">📊</span>
                            <div>
                                <strong>Dashboard Completo</strong>
                                <p>Estadísticas en tiempo real</p>
                            </div>
                        </div>
                        
                        <div class="caracteristica-admin">
                            <span class="icono">👥</span>
                            <div>
                                <strong>Gestión de Usuarios</strong>
                                <p>Control total de cuentas</p>
                            </div>
                        </div>
                        
                        <div class="caracteristica-admin">
                            <span class="icono">🏥</span>
                            <div>
                                <strong>Veterinarios</strong>
                                <p>Certificación y verificación</p>
                            </div>
                        </div>
                        
                        <div class="caracteristica-admin">
                            <span class="icono">📋</span>
                            <div>
                                <strong>Reportes y Logs</strong>
                                <p>Auditoría completa del sistema</p>
                            </div>
                        </div>
                        
                        <div class="caracteristica-admin">
                            <span class="icono">⚙️</span>
                            <div>
                                <strong>Configuración Sistema</strong>
                                <p>Parámetros y mantenimiento</p>
                            </div>
                        </div>
                        
                        <div class="caracteristica-admin">
                            <span class="icono">🛡️</span>
                            <div>
                                <strong>Seguridad Avanzada</strong>
                                <p>Monitoreo y protección</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="credenciales-demo">
                    <h4>🧪 Credenciales de Prueba</h4>
                    <p><strong>Email:</strong> admin@huellasegura.com</p>
                    <p><strong>Contraseña:</strong> admin123</p>
                    <p><strong>Código:</strong> PETCARE2025</p>
                    <small>Solo para demostración - En producción usar credenciales seguras</small>
                </div>
            </div>

            <div class="enlaces-admin">
                <a href="login.php" class="enlace-volver">← Volver al Login Normal</a>
                <a href="login-veterinario.php" class="enlace-vet">👨‍⚕️ Acceso Veterinario</a>
            </div>

            <div class="footer-admin">
                <p>Sistema de Administración Huella Segura v1.0</p>
                <p>Acceso restringido solo para personal autorizado</p>
            </div>
        </div>
    </div>

    <script>
        // Efectos de seguridad y validación
        document.addEventListener('DOMContentLoaded', function() {
            // Efecto de typing en el título
            const titulo = document.querySelector('.logo-admin h1');
            if (titulo) {
                titulo.style.animation = 'typing 2s steps(22), blink 0.5s infinite step-end alternate';
            }

            // Validación adicional del formulario
            document.querySelector('.form-seguro').addEventListener('submit', function(e) {
                const codigo = document.querySelector('input[name="codigo_seguridad"]').value;
                if (codigo.length < 6) {
                    e.preventDefault();
                    alert('El código de seguridad debe tener al menos 6 caracteres');
                }
            });

            // Efecto de focus en inputs
            document.querySelectorAll('.input-seguro input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('input-activo');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('input-activo');
                });
            });
        });
    </script>

    <style>
        /* Estilos específicos para panel administrativo */
        .admin-body {
            background: linear-gradient(135deg, #2c3e50, #34495e, #2c3e50);
            min-height: 100vh;
            font-family: 'Consolas', 'Monaco', monospace;
        }

        .fondo-admin {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .contenedor-admin {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            border: 3px solid #e74c3c;
        }

        .encabezado-admin {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .logo-admin h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .indicador-seguridad {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .nivel-seguridad {
            background: rgba(255,255,255,0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .formulario-admin {
            padding: 2rem;
        }

        .alerta-seguridad {
            background: #ffebee;
            border: 2px solid #e74c3c;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .alerta-seguridad strong {
            color: #c62828;
            display: block;
            margin-bottom: 0.5rem;
        }

        .seccion-credenciales h3 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.3rem;
        }

        .campo-admin {
            margin-bottom: 1.5rem;
        }

        .campo-admin label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: bold;
        }

        .input-seguro {
            position: relative;
            display: flex;
            align-items: center;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .input-seguro.input-activo {
            border-color: #e74c3c;
            box-shadow: 0 0 10px rgba(231,76,60,0.3);
        }

        .icono-seguridad {
            padding: 0 1rem;
            font-size: 1.2rem;
            background: #ecf0f1;
            border-right: 1px solid #bdc3c7;
        }

        .input-seguro input {
            flex: 1;
            padding: 1rem;
            border: none;
            background: transparent;
            outline: none;
            font-family: inherit;
        }

        .ayuda-texto {
            color: #7f8c8d;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: block;
        }

        .verificaciones-seguridad {
            margin: 2rem 0;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #f39c12;
        }

        .verificacion-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
        }

        .verificacion-item:last-child {
            margin-bottom: 0;
        }

        .verificacion-item label {
            color: #34495e;
            font-size: 0.9rem;
        }

        .boton-admin-acceso {
            width: 100%;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 1.2rem;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 2rem;
        }

        .boton-admin-acceso:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(231,76,60,0.3);
        }

        .info-admin h4 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .caracteristicas-admin {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .caracteristica-admin {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid #3498db;
        }

        .caracteristica-admin .icono {
            font-size: 1.5rem;
            width: 40px;
            text-align: center;
        }

        .caracteristica-admin strong {
            color: #2c3e50;
            display: block;
        }

        .caracteristica-admin p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin: 0;
        }

        .credenciales-demo {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .credenciales-demo h4 {
            color: #856404;
            margin-bottom: 1rem;
        }

        .credenciales-demo p {
            margin-bottom: 0.5rem;
            font-family: 'Courier New', monospace;
        }

        .credenciales-demo small {
            color: #856404;
            font-style: italic;
        }

        .enlaces-admin {
            padding: 1rem 2rem;
            background: #ecf0f1;
            display: flex;
            justify-content: space-between;
        }

        .enlace-volver, .enlace-vet {
            color: #2c3e50;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .enlace-volver:hover, .enlace-vet:hover {
            text-decoration: underline;
        }

        .footer-admin {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            text-align: center;
        }

        .footer-admin p {
            margin-bottom: 0.3rem;
            font-size: 0.8rem;
        }

        @keyframes typing {
            0% { width: 0; }
            100% { width: 100%; }
        }

        @keyframes blink {
            0%, 50% { border-color: transparent; }
            51%, 100% { border-color: white; }
        }

        @media (max-width: 768px) {
            .fondo-admin {
                padding: 1rem;
            }
            
            .formulario-admin {
                padding: 1rem;
            }
            
            .caracteristicas-admin {
                grid-template-columns: 1fr;
            }
            
            .enlaces-admin {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</body>
</html>