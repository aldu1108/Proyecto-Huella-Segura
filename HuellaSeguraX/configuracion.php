<?php
session_start();
include_once('config/conexion.php');

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Estilos específicos para configuración */
        .contenedor-configuracion {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 40px;
        }

        .titulo-configuracion {
            text-align: center;
            font-size: 24px;
            color: #D35400;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .seccion-configuracion {
            background: white;
            padding: 25px 30px;
            margin-bottom: 25px;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .seccion-configuracion h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }

        .descripcion-seccion {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .opcion-configuracion {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .opcion-configuracion:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-opcion {
            flex: 1;
        }

        .info-opcion span {
            font-weight: 600;
            color: #333;
            font-size: 15px;
            display: block;
            margin-bottom: 4px;
        }

        .info-opcion p {
            color: #666;
            font-size: 13px;
            margin: 0;
            line-height: 1.4;
        }

        /* Toggle Switch */
        .toggle-switch {
            width: 50px;
            height: 28px;
            border-radius: 14px;
            background: #ddd;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-left: 15px;
        }

        .toggle-switch::after {
            content: "";
            position: absolute;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            top: 2px;
            left: 2px;
            transition: left 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .toggle-switch.activo {
            background: #D35400;
        }

        .toggle-switch.activo::after {
            left: 24px;
        }

        /* Opciones sin toggle */
        .opcion-simple {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .opcion-simple:last-child {
            border-bottom: none;
        }

        .opcion-simple:hover {
            background: #f8f9fa;
            margin: 0 -20px;
            padding-left: 32px;
            padding-right: 32px;
        }

        .icono-opcion {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .texto-opcion {
            font-weight: 500;
            color: #333;
            font-size: 15px;
        }

        .flecha-derecha {
            margin-left: auto;
            color: #ccc;
            font-size: 16px;
        }

        /* Zona de peligro */
        .zona-peligro .opcion-simple:hover {
            background: #ffebee;
        }

        .zona-peligro .texto-opcion {
            color: #e74c3c;
        }

        .zona-peligro .icono-opcion {
            color: #e74c3c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .contenedor-configuracion {
                padding: 20px 15px;
                max-width: none;
            }

            .seccion-configuracion {
                padding: 20px 18px;
                margin-bottom: 20px;
                border-radius: 15px;
            }

            .titulo-configuracion {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .opcion-configuracion {
                padding: 12px 0;
            }

            .info-opcion span {
                font-size: 14px;
            }

            .info-opcion p {
                font-size: 12px;
            }

            .toggle-switch {
                width: 45px;
                height: 25px;
                margin-left: 10px;
            }

            .toggle-switch::after {
                width: 21px;
                height: 21px;
            }

            .toggle-switch.activo::after {
                left: 22px;
            }

            .opcion-simple {
                padding: 12px 0;
            }

            .texto-opcion {
                font-size: 14px;
            }

            .icono-opcion {
                font-size: 16px;
                width: 20px;
            }
        }

        @media (max-width: 480px) {
            .contenedor-configuracion {
                padding: 15px 10px;
            }

            .seccion-configuracion {
                padding: 18px 15px;
                margin-bottom: 18px;
            }

            .titulo-configuracion {
                font-size: 18px;
                margin-bottom: 18px;
            }

            .contenido-modal {
                padding: 25px 20px;
                width: 95%;
            }

            .botones-modal {
                flex-direction: column;
                gap: 10px;
            }

            .boton-modal {
                width: 100%;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .contenedor-configuracion {
                max-width: 700px;
                padding: 30px 35px;
            }
        }

        @media (min-width: 1025px) {
            .contenedor-configuracion {
                padding: 40px 60px;
            }

            .seccion-configuracion {
                padding: 30px 35px;
                margin-bottom: 30px;
            }
        }

        /* Estilos para los selectores */
        .selector-opcion {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            min-width: 100px;
            text-align: center;
        }

        .selector-opcion:hover {
            border-color: #D35400;
        }

        /* Modal para confirmaciones */
        .modal-confirmacion {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .contenido-modal {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .contenido-modal h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .contenido-modal p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .botones-modal {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .boton-modal {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .boton-cancelar {
            background: #f8f9fa;
            color: #666;
        }

        .boton-cancelar:hover {
            background: #e9ecef;
        }

        .boton-confirmar {
            background: #e74c3c;
            color: white;
        }

        .boton-confirmar:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <header>
        <?php include_once('includes/menu_hamburguesa.php'); ?>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="contenedor-configuracion">
            <h1 class="titulo-configuracion">
                ⚙️ Configuración
            </h1>

            <!-- NOTIFICACIONES -->
            <section class="seccion-configuracion">
                <h2>🔔 Notificaciones</h2>
                <p class="descripcion-seccion">Personaliza qué notificaciones quieres recibir para mantenerte informado sobre tus mascotas.</p>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Notificaciones generales</span>
                        <p>Recibe notificaciones importantes de la app</p>
                    </div>
                    <div class="toggle-switch activo" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Recordatorios médicos</span>
                        <p>Citas veterinarias, medicinas y vacunas</p>
                    </div>
                    <div class="toggle-switch activo" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Recordatorios de cuidado</span>
                        <p>Comida, ejercicio y actividades diarias</p>
                    </div>
                    <div class="toggle-switch activo" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Actividad de comunidad</span>
                        <p>Nuevos posts, comentarios y eventos</p>
                    </div>
                    <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Ofertas y promociones</span>
                        <p>Descuentos en tienda y productos recomendados</p>
                    </div>
                    <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
                </div>
            </section>

            <!-- PRIVACIDAD Y SEGURIDAD -->
            <section class="seccion-configuracion">
                <h2>🛡️ Privacidad y Seguridad</h2>
                <p class="descripcion-seccion">Controla quién puede ver tu información y la de tus mascotas.</p>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Perfil público</span>
                        <p>Permitir que otros usuarios vean tu perfil</p>
                    </div>
                    <div class="toggle-switch activo" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Mascotas visibles</span>
                        <p>Mostrar tus mascotas en la comunidad</p>
                    </div>
                    <div class="toggle-switch activo" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Información médica</span>
                        <p>Compartir historial médico con veterinarios</p>
                    </div>
                    <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Compartir ubicación</span>
                        <p>Para mascotas perdidas y servicios cerca de ti</p>
                    </div>
                    <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
                </div>
            </section>

            <!-- PREFERENCIAS -->
            <section class="seccion-configuracion">
                <h2>⚙️ Preferencias</h2>
                <p class="descripcion-seccion">Personaliza la apariencia y comportamiento de la aplicación.</p>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Tema de la aplicación</span>
                        <p>Claro u oscuro</p>
                    </div>
                    <select class="selector-opcion" onchange="cambiarTema(this.value)">
                        <option value="claro">Claro</option>
                        <option value="oscuro">Oscuro</option>
                        <option value="auto">Automático</option>
                    </select>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Idioma</span>
                        <p>Español (ES)</p>
                    </div>
                    <select class="selector-opcion" onchange="cambiarIdioma(this.value)">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                        <option value="pt">Português</option>
                    </select>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Sonidos de la app</span>
                        <p>Reproducir sonidos para notificaciones</p>
                    </div>
                    <div class="toggle-switch activo" onclick="toggleSwitch(this)"></div>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Copia de seguridad automática</span>
                        <p>Guardar datos en la nube automáticamente</p>
                    </div>
                    <div class="toggle-switch activo" onclick="toggleSwitch(this)"></div>
                </div>
            </section>

            <!-- DATOS Y ALMACENAMIENTO -->
            <section class="seccion-configuracion">
                <h2>💾 Datos y Almacenamiento</h2>
                <p class="descripcion-seccion">Gestiona tus datos personales y configuraciones.</p>
                
                <div class="opcion-simple" onclick="exportarDatos()">
                    <span class="icono-opcion">📤</span>
                    <span class="texto-opcion">Exportar mis datos</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-simple" onclick="importarConfiguracion()">
                    <span class="icono-opcion">📥</span>
                    <span class="texto-opcion">Importar configuración</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-simple" onclick="limpiarCache()">
                    <span class="icono-opcion">🧹</span>
                    <span class="texto-opcion">Limpiar caché</span>
                    <span class="flecha-derecha">›</span>
                </div>
            </section>

            <!-- SOPORTE Y AYUDA -->
            <section class="seccion-configuracion">
                <h2>❓ Soporte y Ayuda</h2>
                <p class="descripcion-seccion">Obtén ayuda y proporciona retroalimentación.</p>
                
                <div class="opcion-simple" onclick="window.location.href='ayuda.php'">
                    <span class="icono-opcion">📖</span>
                    <span class="texto-opcion">Centro de ayuda</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-simple" onclick="window.location.href='contacto.php'">
                    <span class="icono-opcion">💬</span>
                    <span class="texto-opcion">Contactar soporte</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-simple" onclick="calificarApp()">
                    <span class="icono-opcion">⭐</span>
                    <span class="texto-opcion">Calificar la app</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-simple" onclick="compartirApp()">
                    <span class="icono-opcion">📱</span>
                    <span class="texto-opcion">Compartir la app</span>
                    <span class="flecha-derecha">›</span>
                </div>
            </section>

            <!-- ACERCA DE -->
            <section class="seccion-configuracion">
                <h2>ℹ️ Acerca de</h2>
                <p class="descripcion-seccion">Información sobre la aplicación y términos legales.</p>
                
                <div class="opcion-simple" onclick="window.location.href='acerca.php'">
                    <span class="icono-opcion">📱</span>
                    <span class="texto-opcion">Acerca de Huella Segura</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-simple" onclick="window.location.href='terminos.php'">
                    <span class="icono-opcion">📋</span>
                    <span class="texto-opcion">Términos y Condiciones</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-simple" onclick="window.location.href='privacidad.php'">
                    <span class="icono-opcion">🔒</span>
                    <span class="texto-opcion">Política de Privacidad</span>
                    <span class="flecha-derecha">›</span>
                </div>
                
                <div class="opcion-configuracion">
                    <div class="info-opcion">
                        <span>Versión</span>
                        <p>Huella Segura v1.0.0</p>
                    </div>
                    <div style="color: #28a745; font-size: 14px; font-weight: 600;">Actualizada</div>
                </div>
            </section>

            <!-- ZONA DE PELIGRO -->
            <section class="seccion-configuracion zona-peligro">
                <h2>⚠️ Zona de Peligro</h2>
                <p class="descripcion-seccion">Acciones que no se pueden deshacer. Procede con precaución.</p>
                
                <div class="opcion-simple" onclick="confirmarEliminacion()">
                    <span class="icono-opcion">🗑️</span>
                    <span class="texto-opcion">Eliminar cuenta</span>
                    <span class="flecha-derecha">›</span>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal de confirmación -->
    <div class="modal-confirmacion" id="modalConfirmacion">
        <div class="contenido-modal">
            <h3 id="tituloModal">¿Estás seguro?</h3>
            <p id="mensajeModal">Esta acción no se puede deshacer.</p>
            <div class="botones-modal">
                <button class="boton-modal boton-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button class="boton-modal boton-confirmar" id="botonConfirmar">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'">❤️</button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'">👥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'">🏥</button>
    </nav>

    <!-- Incluir el footer -->
    <?php include_once('footer.php'); ?>

    <script src="js/scripts.js"></script>
    <script>
        // Funciones de configuración
        function toggleSwitch(elemento) {
            elemento.classList.toggle('activo');
            
            // Aquí podrías enviar la configuración al servidor
            const activo = elemento.classList.contains('activo');
            console.log('Toggle cambiado:', activo);
            
            // Mostrar mensaje de confirmación
            showMessage(activo ? 'Configuración activada' : 'Configuración desactivada', 'success');
        }

        function cambiarTema(tema) {
            console.log('Tema cambiado a:', tema);
            document.body.className = tema === 'oscuro' ? 'tema-oscuro' : '';
            showMessage('Tema cambiado correctamente', 'success');
        }

        function cambiarIdioma(idioma) {
            console.log('Idioma cambiado a:', idioma);
            showMessage('Idioma actualizado (requiere reiniciar)', 'info');
        }

        function exportarDatos() {
            showMessage('Preparando exportación de datos...', 'info');
            setTimeout(() => {
                showMessage('Datos exportados correctamente', 'success');
            }, 2000);
        }

        function importarConfiguracion() {
            // Crear input file dinámico
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json,.txt';
            input.onchange = function() {
                if (this.files.length > 0) {
                    showMessage('Configuración importada correctamente', 'success');
                }
            };
            input.click();
        }

        function limpiarCache() {
            mostrarModal(
                '🧹 Limpiar caché',
                'Se eliminarán todos los datos temporales almacenados. ¿Continuar?',
                function() {
                    showMessage('Caché limpiado correctamente', 'success');
                    cerrarModal();
                }
            );
        }

        function calificarApp() {
            mostrarModal(
                '⭐ Calificar aplicación',
                '¿Te gusta Huella Segura? Tu calificación nos ayuda a mejorar.',
                function() {
                    window.open('https://play.google.com/store', '_blank');
                    cerrarModal();
                }
            );
        }

        function compartirApp() {
            if (navigator.share) {
                navigator.share({
                    title: 'Huella Segura - PetCare',
                    text: '¡Descubre la mejor app para el cuidado de mascotas!',
                    url: window.location.origin
                });
            } else {
                // Fallback para navegadores sin soporte
                const texto = 'Huella Segura - La mejor app para el cuidado de mascotas: ' + window.location.origin;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(texto).then(() => {
                        showMessage('Enlace copiado al portapapeles', 'success');
                    });
                }
            }
        }

        function confirmarEliminacion() {
            mostrarModal(
                '⚠️ Eliminar cuenta',
                'Esta acción eliminará permanentemente tu cuenta y todos tus datos. No se puede deshacer. ¿Estás completamente seguro?',
                function() {
                    showMessage('Procesando eliminación de cuenta...', 'warning');
                    setTimeout(() => {
                        window.location.href = 'logout.php';
                    }, 2000);
                }
            );
        }

        function mostrarModal(titulo, mensaje, callback) {
            document.getElementById('tituloModal').textContent = titulo;
            document.getElementById('mensajeModal').textContent = mensaje;
            document.getElementById('botonConfirmar').onclick = callback;
            document.getElementById('modalConfirmacion').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalConfirmacion').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalConfirmacion').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Función para mostrar mensajes (reutilizar del script principal)
        function showMessage(texto, tipo = 'info') {
            // Esta función ya está definida en scripts.js
            if (window.showMessage) {
                window.showMessage(texto, tipo);
            } else {
                console.log(`[${tipo.toUpperCase()}] ${texto}`);
            }
        }

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Configuración cargada correctamente');
            
            // Aplicar configuraciones guardadas (si existen)
            cargarConfiguracionGuardada();
        });

        function cargarConfiguracionGuardada() {
            // Aquí podrías cargar las configuraciones desde el servidor o localStorage
            // Por ahora solo simulamos la carga
            console.log('Cargando configuración del usuario...');
        }

        // Guardar configuraciones automáticamente
        function guardarConfiguracion(clave, valor) {
            // Aquí enviarías la configuración al servidor
            console.log(`Guardando configuración: ${clave} = ${valor}`);
        }
    </script>
</body>
</html>