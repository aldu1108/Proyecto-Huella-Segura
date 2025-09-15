<?php
include_once('config/conexion.php');
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - Huella Segura</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="cabecera-principal">
        <div class="logo-contenedor">
            <h1 class="logo-texto">PetCare 🐾</h1>
            <p class="logo-subtitulo">Tu compañero para el cuidado de mascotas</p>
        </div>
    </div>

    <div class="contenedor-terminos">
        <header class="encabezado-legal">
            <h1>Términos y Condiciones de Uso</h1>
            <p class="fecha-actualizacion">Última actualización: 10 de Septiembre de 2025</p>
        </header>

        <div class="contenido-legal">
            <section class="seccion-terminos">
                <h2>1. Aceptación de los Términos</h2>
                <p>Al acceder y utilizar Huella Segura - PetCare ("el Servicio"), usted acepta estar sujeto a estos términos y condiciones de uso. Si no está de acuerdo con alguna parte de estos términos, no debe utilizar nuestro servicio.</p>
            </section>

            <section class="seccion-terminos">
                <h2>2. Descripción del Servicio</h2>
                <p>Huella Segura es una plataforma web que permite a los usuarios:</p>
                <ul>
                    <li>Registrar y gestionar información de sus mascotas</li>
                    <li>Programar y llevar registro de citas veterinarias</li>
                    <li>Participar en la comunidad de amantes de mascotas</li>
                    <li>Reportar mascotas perdidas y encontradas</li>
                    <li>Facilitar procesos de adopción</li>
                    <li>Conectar con veterinarios y servicios relacionados</li>
                </ul>
            </section>

            <section class="seccion-terminos">
                <h2>3. Registro de Cuenta</h2>
                <p>Para utilizar ciertas funciones del servicio, debe crear una cuenta proporcionando:</p>
                <ul>
                    <li>Información personal veraz y actualizada</li>
                    <li>Una dirección de correo electrónico válida</li>
                    <li>Una contraseña segura</li>
                </ul>
                <p>Usted es responsable de mantener la confidencialidad de su cuenta y contraseña.</p>
            </section>

            <section class="seccion-terminos">
                <h2>4. Uso Aceptable</h2>
                <h3>Está permitido:</h3>
                <ul>
                    <li>Registrar información veraz sobre sus mascotas</li>
                    <li>Compartir experiencias y consejos relacionados con mascotas</li>
                    <li>Reportar mascotas perdidas o encontradas de buena fe</li>
                    <li>Participar constructivamente en la comunidad</li>
                </ul>
                
                <h3>Está prohibido:</h3>
                <ul>
                    <li>Proporcionar información falsa o engañosa</li>
                    <li>Usar el servicio para actividades ilegales</li>
                    <li>Acosar, amenazar o intimidar a otros usuarios</li>
                    <li>Publicar contenido ofensivo, difamatorio o inapropiado</li>
                    <li>Intentar acceder no autorizado a cuentas de otros usuarios</li>
                    <li>Usar el servicio para spam o publicidad no solicitada</li>
                </ul>
            </section>

            <section class="seccion-terminos">
                <h2>5. Contenido del Usuario</h2>
                <p>Usted conserva los derechos sobre el contenido que publique, pero otorga a Huella Segura una licencia no exclusiva para usar, modificar y mostrar dicho contenido en el servicio.</p>
                <p>Nos reservamos el derecho de eliminar contenido que viole estos términos.</p>
            </section>

            <section class="seccion-terminos">
                <h2>6. Servicios Veterinarios</h2>
                <p><strong>Importante:</strong> Huella Segura NO proporciona servicios veterinarios directos. La plataforma solo facilita:</p>
                <ul>
                    <li>Conexión con profesionales veterinarios</li>
                    <li>Gestión de citas y recordatorios</li>
                    <li>Almacenamiento de historial médico</li>
                </ul>
                <p>Siempre consulte con un veterinario calificado para decisiones médicas sobre su mascota.</p>
            </section>

            <section class="seccion-terminos">
                <h2>7. Mascotas Perdidas y Adopciones</h2>
                <p>Para reportes de mascotas perdidas y procesos de adopción:</p>
                <ul>
                    <li>Verificamos la información en la medida de lo posible</li>
                    <li>No garantizamos la veracidad de todos los reportes</li>
                    <li>Recomendamos verificar identidad y documentación</li>
                    <li>No somos responsables por transacciones entre usuarios</li>
                </ul>
            </section>

            <section class="seccion-terminos">
                <h2>8. Privacidad y Datos Personales</h2>
                <p>Su privacidad es importante. Consulte nuestra <a href="privacidad.php">Política de Privacidad</a> para conocer cómo recopilamos, utilizamos y protegemos su información.</p>
            </section>

            <section class="seccion-terminos">
                <h2>9. Limitación de Responsabilidad</h2>
                <p>Huella Segura se proporciona "tal como está". No garantizamos:</p>
                <ul>
                    <li>Disponibilidad ininterrumpida del servicio</li>
                    <li>Exactitud de toda la información</li>
                    <li>Resultados específicos del uso del servicio</li>
                </ul>
                <p>No somos responsables por daños directos, indirectos o consecuenciales derivados del uso del servicio.</p>
            </section>

            <section class="seccion-terminos">
                <h2>10. Modificaciones del Servicio</h2>
                <p>Nos reservamos el derecho de:</p>
                <ul>
                    <li>Modificar o discontinuar el servicio</li>
                    <li>Actualizar estos términos</li>
                    <li>Suspender o terminar cuentas por violaciones</li>
                </ul>
                <p>Los cambios importantes se notificarán con al menos 30 días de anticipación.</p>
            </section>

            <section class="seccion-terminos">
                <h2>11. Propiedad Intelectual</h2>
                <p>Huella Segura y todo su contenido (logos, diseño, texto, código) están protegidos por derechos de autor y otras leyes de propiedad intelectual.</p>
            </section>

            <section class="seccion-terminos">
                <h2>12. Terminación</h2>
                <p>Puede eliminar su cuenta en cualquier momento desde la configuración. Podemos suspender o terminar cuentas por:</p>
                <ul>
                    <li>Violación de estos términos</li>
                    <li>Actividad fraudulenta o ilegal</li>
                    <li>Inactividad prolongada</li>
                </ul>
            </section>

            <section class="seccion-terminos">
                <h2>13. Ley Aplicable</h2>
                <p>Estos términos se rigen por las leyes de Argentina. Cualquier disputa se resolverá en los tribunales competentes de Buenos Aires.</p>
            </section>

            <section class="seccion-terminos">
                <h2>14. Contacto</h2>
                <p>Para preguntas sobre estos términos, contáctenos:</p>
                <ul>
                    <li><strong>Email:</strong> legal@huellasegura.com</li>
                    <li><strong>Teléfono:</strong> +54 11 1234-5678</li>
                    <li><strong>Dirección:</strong> Av. Corrientes 1234, Buenos Aires, Argentina</li>
                </ul>
            </section>
        </div>

        <div class="acciones-legales">
            <button onclick="window.print()" class="boton-imprimir">Imprimir Términos</button>
            <button onclick="window.history.back()" class="boton-volver">Volver</button>
            <button onclick="window.location.href='privacidad.php'" class="boton-enlace">Ver Política de Privacidad</button>
        </div>
    </div>

    <style>
        .contenedor-terminos {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .encabezado-legal {
            text-align: center;
            padding: 2rem 0;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 2rem;
        }

        .encabezado-legal h1 {
            color: #d35400;
            margin-bottom: 1rem;
        }

        .fecha-actualizacion {
            color: #666;
            font-style: italic;
        }

        .contenido-legal {
            padding: 0 2rem;
        }

        .seccion-terminos {
            margin-bottom: 2rem;
        }

        .seccion-terminos h2 {
            color: #2c3e50;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .seccion-terminos h3 {
            color: #34495e;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .seccion-terminos ul {
            margin-left: 2rem;
            margin-bottom: 1rem;
        }

        .seccion-terminos li {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .seccion-terminos p {
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .seccion-terminos a {
            color: #d35400;
            text-decoration: none;
        }

        .seccion-terminos a:hover {
            text-decoration: underline;
        }

        .acciones-legales {
            padding: 2rem;
            text-align: center;
            border-top: 2px solid #f0f0f0;
            margin-top: 2rem;
        }

        .boton-imprimir, .boton-volver, .boton-enlace {
            background-color: #d35400;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            margin: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .boton-volver {
            background-color: #95a5a6;
        }

        .boton-enlace {
            background-color: #3498db;
        }

        .boton-imprimir:hover, .boton-volver:hover, .boton-enlace:hover {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .contenedor-terminos {
                margin: 1rem;
                border-radius: 0;
            }
            
            .contenido-legal {
                padding: 0 1rem;
            }
            
            .acciones-legales {
                padding: 1rem;
            }
            
            .boton-imprimir, .boton-volver, .boton-enlace {
                display: block;
                width: 100%;
                margin: 0.5rem 0;
            }
        }

        @media print {
            .acciones-legales {
                display: none;
            }
        }
    </style>
</body>
</html>