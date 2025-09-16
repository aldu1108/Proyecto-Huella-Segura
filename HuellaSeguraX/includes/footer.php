<!-- Pie de página común -->
    <footer class="pie-pagina">
        <div class="contenedor-footer">
            <div class="seccion-footer">
                <h4>Huella Segura</h4>
                <p>Tu compañero digital para el cuidado integral de mascotas</p>
                <div class="redes-sociales">
                    <a href="#" class="enlace-red">📘 Facebook</a>
                    <a href="#" class="enlace-red">📷 Instagram</a>
                    <a href="#" class="enlace-red">🐦 Twitter</a>
                </div>
            </div>
            
            <div class="seccion-footer">
                <h4>Servicios</h4>
                <ul class="lista-footer">
                    <li><a href="mis-mascotas.php">Gestión de Mascotas</a></li>
                    <li><a href="veterinaria.php">Citas Veterinarias</a></li>
                    <li><a href="adopciones.php">Adopciones</a></li>
                    <li><a href="mascotas-perdidas.php">Mascotas Perdidas</a></li>
                    <li><a href="comunidad.php">Comunidad</a></li>
                </ul>
            </div>
            
            <div class="seccion-footer">
                <h4>Soporte</h4>
                <ul class="lista-footer">
                    <li><a href="ayuda.php">Centro de Ayuda</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                    <li><a href="veterinarios.php">Para Veterinarios</a></li>
                    <li><a href="refugios.php">Para Refugios</a></li>
                    <li><a href="emergencia.php">Emergencias 24h</a></li>
                </ul>
            </div>
            
            <div class="seccion-footer">
                <h4>Legal</h4>
                <ul class="lista-footer">
                    <li><a href="terminos.php">Términos y Condiciones</a></li>
                    <li><a href="privacidad.php">Política de Privacidad</a></li>
                    <li><a href="cookies.php">Política de Cookies</a></li>
                    <li><a href="responsabilidad.php">Aviso Legal</a></li>
                </ul>
            </div>
        </div>
        
        <div class="linea-separacion"></div>
        
        <div class="footer-inferior">
            <div class="info-empresa">
                <p>&copy; 2025 Huella Segura - Todos los derechos reservados.</p>
                <p>Desarrollado con ❤️ para la comunidad de amantes de las mascotas</p>
            </div>
            
            <div class="contacto-emergencia">
                <p><strong>📞 Emergencias Veterinarias:</strong> 911-PET-HELP</p>
                <p><strong>📧 Soporte:</strong> ayuda@huellasegura.com</p>
            </div>
        </div>
        
        <div class="estadisticas-footer">
            <div class="estadistica-footer">
                <span class="numero">+10,000</span>
                <span class="texto">Mascotas Registradas</span>
            </div>
            <div class="estadistica-footer">
                <span class="numero">+5,000</span>
                <span class="texto">Familias Felices</span>
            </div>
            <div class="estadistica-footer">
                <span class="numero">+500</span>
                <span class="texto">Veterinarios</span>
            </div>
            <div class="estadistica-footer">
                <span class="numero">24/7</span>
                <span class="texto">Soporte</span>
            </div>
        </div>
    </footer>

    <!-- Scripts al final del documento -->
    <script src="js/scripts.js"></script>
    
    <!-- Script adicional para funcionalidades del footer -->
    <script>
        // Funcionalidad del footer
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de números en estadísticas (si están visibles)
            const observador = new IntersectionObserver(function(entradas) {
                entradas.forEach(function(entrada) {
                    if (entrada.isIntersecting) {
                        animarNumero(entrada.target);
                    }
                });
            });
            
            document.querySelectorAll('.estadistica-footer .numero').forEach(function(elemento) {
                observador.observe(elemento);
            });
        });
        
        function animarNumero(elemento) {
            const texto = elemento.textContent;
            const numero = parseInt(texto.replace(/[^\d]/g, ''));
            
            if (!isNaN(numero)) {
                let contador = 0;
                const incremento = numero / 50;
                const timer = setInterval(function() {
                    contador += incremento;
                    if (contador >= numero) {
                        elemento.textContent = texto;
                        clearInterval(timer);
                    } else {
                        elemento.textContent = '+' + Math.floor(contador).toLocaleString('es-ES');
                    }
                }, 30);
            }
        }
    </script>

    <style>
        /* Estilos para el pie de página */
        .pie-pagina {
            background-color: #2c3e50;
            color: white;
            margin-top: 4rem;
            padding: 3rem 0 1rem;
        }
        
        .contenedor-footer {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .seccion-footer h4 {
            color: #f39c12;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .seccion-footer p {
            line-height: 1.6;
            margin-bottom: 1rem;
            color: #bdc3c7;
        }
        
        .redes-sociales {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .enlace-red {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .enlace-red:hover {
            color: #2980b9;
        }
        
        .lista-footer {
            list-style: none;
            padding: 0;
        }
        
        .lista-footer li {
            margin-bottom: 0.8rem;
        }
        
        .lista-footer a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .lista-footer a:hover {
            color: #f39c12;
        }
        
        .linea-separacion {
            height: 1px;
            background-color: #34495e;
            margin: 2rem auto;
            max-width: 1200px;
        }
        
        .footer-inferior {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .info-empresa p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #95a5a6;
        }
        
        .contacto-emergencia p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #e74c3c;
        }
        
        .estadisticas-footer {
            max-width: 1200px;
            margin: 2rem auto 0;
            padding: 2rem 1rem 0;
            display: flex;
            justify-content: space-around;
            border-top: 1px solid #34495e;
        }
        
        .estadistica-footer {
            text-align: center;
        }
        
        .estadistica-footer .numero {
            display: block;
            font-size: 2rem;
            font-weight: bold;
            color: #f39c12;
            margin-bottom: 0.5rem;
        }
        
        .estadistica-footer .texto {
            font-size: 0.9rem;
            color: #bdc3c7;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .pie-pagina {
                text-align: center;
            }
            
            .footer-inferior {
                flex-direction: column;
                text-align: center;
                gap: 2rem;
            }
            
            .estadisticas-footer {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .contenedor-footer {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
        
        /* Agregar espacio para la navegación inferior fija en móviles */
        @media (max-width: 768px) {
            .pie-pagina {
                margin-bottom: 80px; /* Altura de la navegación inferior */
            }
        }
    </style>
</body>
</html>

<?php
// Cerrar conexión a la base de datos si existe
if (isset($conexion)) {
    cerrarConexion();
}
?>