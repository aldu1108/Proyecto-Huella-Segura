<?php
include_once('config/conexion.php');
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/privacidad.css">
    <?php include_once("includes/logo.php"); ?>
</head>
<body>
    <div class="cabecera-principal">
        <div class="logo-contenedor">
            <h1 class="logo-texto">PetCare</h1>
            <p class="logo-subtitulo">Tu compañero para el cuidado de mascotas</p>
        </div>
    </div>

    <div class="contenedor-privacidad">
        <header class="encabezado-legal">
            <h1>Política de Privacidad</h1>
            <p class="fecha-actualizacion">Última actualización: 10 de Septiembre de 2025</p>
            <p class="resumen-privacidad">En Huella Segura valoramos tu privacidad y nos comprometemos a proteger tu información personal.</p>
        </header>

        <div class="contenido-legal">
            <section class="seccion-privacidad">
                <h2>1. Información que Recopilamos</h2>
                
                <h3>Información Personal</h3>
                <ul>
                    <li><strong>Datos de registro:</strong> nombre, apellido, correo electrónico, teléfono</li>
                    <li><strong>Información de mascotas:</strong> nombre, edad, tipo, fotos, historial médico</li>
                    <li><strong>Datos veterinarios:</strong> citas, tratamientos, documentos médicos</li>
                    <li><strong>Ubicación:</strong> para servicios de mascotas perdidas y adopciones</li>
                </ul>

                <h3>Información Técnica</h3>
                <ul>
                    <li><strong>Datos de navegación:</strong> dirección IP, tipo de navegador, páginas visitadas</li>
                    <li><strong>Cookies:</strong> preferencias, sesión, análisis de uso</li>
                    <li><strong>Logs del sistema:</strong> actividad de la cuenta, errores técnicos</li>
                </ul>
            </section>

            <section class="seccion-privacidad">
                <h2>2. Cómo Utilizamos tu Información</h2>
                
                <div class="propositos-uso">
                    <div class="proposito-item">
                        <h4>Prestación del Servicio</h4>
                        <ul>
                            <li>Crear y gestionar tu cuenta</li>
                            <li>Almacenar información de tus mascotas</li>
                            <li>Facilitar citas veterinarias</li>
                            <li>Sistema de mascotas perdidas</li>
                        </ul>
                    </div>

                    <div class="proposito-item">
                        <h4>Comunicación</h4>
                        <ul>
                            <li>Recordatorios de citas y vacunas</li>
                            <li>Notificaciones de la comunidad</li>
                            <li>Soporte técnico</li>
                            <li>Actualizaciones del servicio</li>
                        </ul>
                    </div>

                    <div class="proposito-item">
                        <h4>Mejora del Servicio</h4>
                        <ul>
                            <li>Análisis de uso y comportamiento</li>
                            <li>Desarrollo de nuevas funcionalidades</li>
                            <li>Personalización de experiencias</li>
                            <li>Prevención de fraude</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="seccion-privacidad">
                <h2>3. Base Legal para el Procesamiento</h2>
                <p>Procesamos tu información personal basándonos en:</p>
                <ul>
                    <li><strong>Consentimiento:</strong> cuando aceptas nuestros términos y esta política</li>
                    <li><strong>Ejecución contractual:</strong> para proporcionar los servicios solicitados</li>
                    <li><strong>Interés legítimo:</strong> para mejorar nuestros servicios y seguridad</li>
                    <li><strong>Obligación legal:</strong> cuando la ley requiere retención de datos</li>
                </ul>
            </section>

            <section class="seccion-privacidad">
                <h2>4. Compartir Información</h2>
                
                <div class="compartir-info">
                    <h3>NO compartimos tu información con terceros para marketing</h3>
                    
                    <h3>Compartimos información únicamente en estos casos:</h3>
                    <ul>
                        <li><strong>Veterinarios:</strong> datos necesarios para consultas médicas</li>
                        <li><strong>Servicios técnicos:</strong> proveedores de hosting y análisis</li>
                        <li><strong>Emergencias:</strong> para reunir mascotas perdidas con sus familias</li>
                        <li><strong>Requisitos legales:</strong> cuando la ley lo exija</li>
                    </ul>
                </div>
            </section>

            <section class="seccion-privacidad">
                <h2>5. Seguridad de los Datos</h2>
                
                <div class="medidas-seguridad">
                    <div class="medida-seguridad">
                        <h4>Cifrado</h4>
                        <p>Conexiones SSL/TLS y cifrado de datos sensibles</p>
                    </div>
                    
                    <div class="medida-seguridad">
                        <h4>Acceso Restringido</h4>
                        <p>Solo personal autorizado puede acceder a los datos</p>
                    </div>
                    
                    <div class="medida-seguridad">
                        <h4>Auditorías</h4>
                        <p>Revisiones regulares de seguridad y vulnerabilidades</p>
                    </div>
                    
                    <div class="medida-seguridad">
                        <h4>Backups Seguros</h4>
                        <p>Copias de seguridad cifradas y almacenamiento seguro</p>
                    </div>
                </div>
            </section>

            <section class="seccion-privacidad">
                <h2>6. Tus Derechos</h2>
                
                <div class="derechos-usuario">
                    <div class="derecho-item">
                        <h4>Acceso</h4>
                        <p>Solicitar copia de tu información personal</p>
                    </div>
                    
                    <div class="derecho-item">
                        <h4>Rectificación</h4>
                        <p>Corregir información inexacta o incompleta</p>
                    </div>
                    
                    <div class="derecho-item">
                        <h4>Eliminación</h4>
                        <p>Solicitar la eliminación de tu información</p>
                    </div>
                    
                    <div class="derecho-item">
                        <h4>Portabilidad</h4>
                        <p>Exportar tus datos en formato legible</p>
                    </div>
                    
                    <div class="derecho-item">
                        <h4>Limitación</h4>
                        <p>Restringir el procesamiento de tus datos</p>
                    </div>
                    
                    <div class="derecho-item">
                        <h4>Oposición</h4>
                        <p>Oponerte al procesamiento por intereses legítimos</p>
                    </div>
                </div>

                <div class="ejercer-derechos">
                    <h4>Para ejercer estos derechos:</h4>
                    <p>Contáctanos en <strong>privacidad@huellasegura.com</strong> o desde tu configuración de cuenta.</p>
                    <p>Responderemos en un plazo máximo de 30 días.</p>
                </div>
            </section>

            <section class="seccion-privacidad">
                <h2>7. Retención de Datos</h2>
                <ul>
                    <li><strong>Cuenta activa:</strong> mientras uses el servicio</li>
                    <li><strong>Después de eliminación:</strong> 90 días para recuperación</li>
                    <li><strong>Datos médicos:</strong> 7 años (requisito legal)</li>
                    <li><strong>Logs técnicos:</strong> 12 meses máximo</li>
                    <li><strong>Datos financieros:</strong> según regulaciones fiscales</li>
                </ul>
            </section>

            <section class="seccion-privacidad">
                <h2>8. Cookies y Tecnologías Similares</h2>
                
                <div class="tipos-cookies">
                    <div class="tipo-cookie">
                        <h4>Cookies Esenciales</h4>
                        <p>Necesarias para el funcionamiento básico del sitio</p>
                        <span class="estado-cookie necesarias">Siempre activas</span>
                    </div>
                    
                    <div class="tipo-cookie">
                        <h4>Cookies de Funcionalidad</h4>
                        <p>Recordar preferencias y configuración personal</p>
                        <span class="estado-cookie opcional">Opcional</span>
                    </div>
                    
                    <div class="tipo-cookie">
                        <h4>Cookies de Análisis</h4>
                        <p>Entender cómo interactúas con el sitio</p>
                        <span class="estado-cookie opcional">Opcional</span>
                    </div>
                </div>

                <p>Puedes gestionar las cookies desde la configuración de tu navegador o desde nuestro <a href="configuracion-cookies.php">panel de cookies</a>.</p>
            </section>

            <section class="seccion-privacidad">
                <h2>9. Transferencias Internacionales</h2>
                <p>Tus datos se almacenan principalmente en servidores ubicados en Argentina. En caso de transferencias internacionales, garantizamos:</p>
                <ul>
                    <li>Países con nivel de protección adecuado</li>
                    <li>Cláusulas contractuales tipo de la UE</li>
                    <li>Certificaciones de privacidad reconocidas</li>
                    <li>Consentimiento explícito cuando sea necesario</li>
                </ul>
            </section>

            <section class="seccion-privacidad">
                <h2>10. Menores de Edad</h2>
                <div class="proteccion-menores">
                    <p>Huella Segura está diseñado para usuarios mayores de 16 años.</p>
                    <ul>
                        <li>No recopilamos intencionalmente datos de menores de 16 años</li>
                        <li>Si detectamos datos de menores, los eliminaremos inmediatamente</li>
                        <li>Los padres pueden solicitar eliminación de datos de sus hijos</li>
                        <li>Verificación adicional para cuentas sospechosas</li>
                    </ul>
                </div>
            </section>

            <section class="seccion-privacidad">
                <h2>11. Cambios en la Política</h2>
                <p>Podemos actualizar esta política ocasionalmente. Te notificaremos:</p>
                <ul>
                    <li>Por email a tu cuenta registrada</li>
                    <li>Mediante aviso en la plataforma</li>
                    <li>Con 30 días de anticipación para cambios importantes</li>
                    <li>Solicitud de nuevo consentimiento si es necesario</li>
                </ul>
            </section>

            <section class="seccion-privacidad">
                <h2>12. Contacto</h2>
                <div class="contacto-privacidad">
                    <h4>Delegado de Protección de Datos</h4>
                    <p><strong>Email:</strong> dpo@huellasegura.com</p>
                    <p><strong>Teléfono:</strong> +54 11 1234-5678</p>
                    <p><strong>Dirección:</strong> Av. Corrientes 1234, Buenos Aires, Argentina</p>
                    
                    <h4>Autoridad de Control</h4>
                    <p>Agencia de Acceso a la Información Pública (AAIP)</p>
                    <p><strong>Web:</strong> www.argentina.gob.ar/aaip</p>
                </div>
            </section>
        </div>

        <div class="resumen-puntos-clave">
            <h3>Puntos Clave de Nuestra Política</h3>
            <div class="puntos-clave">
                <div class="punto-clave">
                    <span class="icono-punto">🔒</span>
                    <p>Protegemos tu información con medidas de seguridad avanzadas</p>
                </div>
                <div class="punto-clave">
                    <span class="icono-punto">🚫</span>
                    <p>NO vendemos ni compartimos tus datos con terceros para marketing</p>
                </div>
                <div class="punto-clave">
                    <span class="icono-punto">✋</span>
                    <p>Tienes control total sobre tus datos personales</p>
                </div>
                <div class="punto-clave">
                    <span class="icono-punto">🏥</span>
                    <p>Los datos médicos se manejan con máxima confidencialidad</p>
                </div>
            </div>
        </div>

        <div class="acciones-legales">
            <button onclick="window.print()" class="boton-imprimir">Imprimir Política</button>
            <button onclick="window.history.back()" class="boton-volver">Volver</button>
            <button onclick="window.location.href='terminos.php'" class="boton-enlace">Ver Términos y Condiciones</button>
            <button onclick="window.location.href='configuracion-privacidad.php'" class="boton-configurar">Configurar Privacidad</button>
        </div>
    </div>
</body>
</html>