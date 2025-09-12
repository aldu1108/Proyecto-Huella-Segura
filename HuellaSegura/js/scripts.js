// Scripts básicos para Huella Segura

// Funcionalidad del menú hamburguesa - VERSIÓN CORREGIDA
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando menú hamburguesa...');
    
    const menuHamburguesa = document.getElementById('menuHamburguesa');
    const menuLateral = document.getElementById('menuLateral');
    
    if (menuHamburguesa && menuLateral) {
        console.log('Elementos del menú encontrados');
        
        // Asegurar que el menú esté oculto inicialmente
        menuLateral.style.display = 'none';
        
        menuHamburguesa.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Click en menú hamburguesa');
            
            if (menuLateral.style.display === 'none' || menuLateral.style.display === '') {
                menuLateral.style.display = 'block';
                menuLateral.classList.add('activo');
                console.log('Menú mostrado');
            } else {
                menuLateral.style.display = 'none';
                menuLateral.classList.remove('activo');
                console.log('Menú ocultado');
            }
        });

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            if (!menuHamburguesa.contains(event.target) && !menuLateral.contains(event.target)) {
                menuLateral.style.display = 'none';
                menuLateral.classList.remove('activo');
            }
        });
        
        // Cerrar menú al hacer clic en una opción
        const opcionesMenu = menuLateral.querySelectorAll('.opcion-menu');
        opcionesMenu.forEach(function(opcion) {
            opcion.addEventListener('click', function() {
                menuLateral.style.display = 'none';
                menuLateral.classList.remove('activo');
            });
        });
        
    } else {
        console.error('No se encontraron los elementos del menú hamburguesa');
        console.log('menuHamburguesa:', menuHamburguesa);
        console.log('menuLateral:', menuLateral);
    }
});

// Funciones adicionales para otros componentes
function toggleBusqueda() {
    console.log('Toggle búsqueda');
    
    // Buscar barra de búsqueda existente
    const barraBusqueda = document.querySelector('.barra-busqueda');
    if (barraBusqueda) {
        barraBusqueda.focus();
        barraBusqueda.select();
    } else {
        // Crear barra de búsqueda dinámica
        mostrarBarraBusquedaDinamica();
    }
}

function toggleNotificaciones() {
    console.log('Toggle notificaciones');
    mostrarMensaje('No tienes notificaciones nuevas', 'info');
}

function mostrarBarraBusquedaDinamica() {
    // Verificar si ya existe
    const existente = document.querySelector('.barra-busqueda-flotante');
    if (existente) {
        existente.remove();
        return;
    }
    
    // Crear barra de búsqueda flotante
    const barraBusqueda = document.createElement('div');
    barraBusqueda.className = 'barra-busqueda-flotante';
    barraBusqueda.innerHTML = `
        <input type="text" placeholder="Buscar..." class="input-busqueda-flotante">
        <button class="cerrar-busqueda">✕</button>
    `;
    
    // Estilos inline para la barra flotante
    barraBusqueda.style.cssText = `
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 1rem;
        border-radius: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        z-index: 3000;
        display: flex;
        gap: 0.5rem;
        align-items: center;
        min-width: 300px;
        animation: slideDown 0.3s ease;
    `;
    
    document.body.appendChild(barraBusqueda);
    
    // Estilos para el input y botón
    const input = barraBusqueda.querySelector('.input-busqueda-flotante');
    input.style.cssText = `
        border: none;
        outline: none;
        flex: 1;
        font-size: 1rem;
        padding: 0.5rem;
        border-radius: 15px;
        background: #f8f9fa;
    `;
    
    const botonCerrar = barraBusqueda.querySelector('.cerrar-busqueda');
    botonCerrar.style.cssText = `
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        transition: background-color 0.3s;
    `;
    
    // Focus automático
    input.focus();
    
    // Event listeners
    botonCerrar.addEventListener('click', () => {
        barraBusqueda.remove();
    });
    
    botonCerrar.addEventListener('mouseenter', () => {
        botonCerrar.style.backgroundColor = '#f0f0f0';
    });
    
    botonCerrar.addEventListener('mouseleave', () => {
        botonCerrar.style.backgroundColor = 'transparent';
    });
    
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            barraBusqueda.remove();
        } else if (e.key === 'Enter') {
            if (input.value.trim()) {
                console.log('Buscar:', input.value);
                mostrarMensaje(`Buscando: ${input.value}`, 'info');
                // Aquí podrías redirigir a una página de búsqueda
                // window.location.href = `buscar.php?q=${encodeURIComponent(input.value)}`;
            }
            barraBusqueda.remove();
        }
    });
    
    // Cerrar al hacer clic fuera
    setTimeout(() => {
        document.addEventListener('click', function cerrarBusqueda(e) {
            if (!barraBusqueda.contains(e.target)) {
                if (document.body.contains(barraBusqueda)) {
                    barraBusqueda.remove();
                }
                document.removeEventListener('click', cerrarBusqueda);
            }
        });
    }, 100);
}

// Variables globales para el menú
let menuHamburguesaInstance;

// Inicializar el menú hamburguesa cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    menuHamburguesaInstance = new MenuHamburguesa();
    
    // Hacer disponible globalmente para debugging
    window.menuHamburguesa = menuHamburguesaInstance;
    
    // Agregar animación CSS para la barra de búsqueda
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
});

// Función para obtener la instancia del menú (útil para otros scripts)
function getMenuHamburguesa() {
    return menuHamburguesaInstance;
}
function mostrarMensaje(texto, tipo = 'info') {
    const mensaje = document.createElement('div');
    mensaje.className = 'mensaje-flotante mensaje-' + tipo;
    mensaje.textContent = texto;
    
    mensaje.style.position = 'fixed';
    mensaje.style.top = '20px';
    mensaje.style.right = '20px';
    mensaje.style.padding = '1rem 1.5rem';
    mensaje.style.borderRadius = '8px';
    mensaje.style.color = 'white';
    mensaje.style.zIndex = '9999';
    mensaje.style.maxWidth = '300px';
    mensaje.style.boxShadow = '0 4px 12px rgba(0,0,0,0.3)';
    
    // Colores según tipo
    switch(tipo) {
        case 'success':
            mensaje.style.backgroundColor = '#27ae60';
            break;
        case 'error':
            mensaje.style.backgroundColor = '#e74c3c';
            break;
        case 'warning':
            mensaje.style.backgroundColor = '#f39c12';
            break;
        default:
            mensaje.style.backgroundColor = '#3498db';
    }
    
    document.body.appendChild(mensaje);
    
    // Animación de entrada
    mensaje.style.transform = 'translateX(100%)';
    mensaje.style.transition = 'transform 0.3s ease';
    
    setTimeout(() => {
        mensaje.style.transform = 'translateX(0)';
    }, 10);
    
    // Remover después de 3 segundos
    setTimeout(function() {
        if (mensaje.parentNode) {
            mensaje.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (mensaje.parentNode) {
                    mensaje.parentNode.removeChild(mensaje);
                }
            }, 300);
        }
    }, 3000);
}

// Validación básica de formularios
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validarFormulario(formulario) {
    const inputs = formulario.querySelectorAll('input[required], select[required], textarea[required]');
    let valido = true;
    
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.style.borderColor = '#e74c3c';
            valido = false;
        } else {
            input.style.borderColor = '#27ae60';
        }
        
        // Validar email específicamente
        if (input.type === 'email' && input.value && !validarEmail(input.value)) {
            input.style.borderColor = '#e74c3c';
            valido = false;
        }
    });
    
    return valido;
}

// Función para confirmar acciones importantes
function confirmarAccion(mensaje, callback) {
    if (confirm(mensaje)) {
        callback();
    }
}

// Función para formatear fechas
function formatearFecha(fecha) {
    const opciones = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
}

// Función para calcular edad de mascota
function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

// Función para búsqueda en tiempo real
function busquedaEnTiempoReal(inputId, contenedorId, filtrarFuncion) {
    const input = document.getElementById(inputId);
    const contenedor = document.getElementById(contenedorId);
    
    if (input && contenedor) {
        input.addEventListener('input', function() {
            const termino = this.value.toLowerCase();
            const elementos = contenedor.children;
            
            Array.from(elementos).forEach(function(elemento) {
                const texto = elemento.textContent.toLowerCase();
                if (texto.includes(termino)) {
                    elemento.style.display = 'block';
                } else {
                    elemento.style.display = 'none';
                }
            });
        });
    }
}

// Función para manejo de archivos
function manejarSubidaArchivo(inputId, previsualizacionId) {
    const input = document.getElementById(inputId);
    const previsualizacion = document.getElementById(previsualizacionId);
    
    if (input && previsualizacion) {
        input.addEventListener('change', function(e) {
            const archivo = e.target.files[0];
            if (archivo) {
                if (archivo.type.startsWith('image/')) {
                    const lector = new FileReader();
                    lector.onload = function(e) {
                        previsualizacion.src = e.target.result;
                        previsualizacion.style.display = 'block';
                    };
                    lector.readAsDataURL(archivo);
                } else {
                    mostrarMensaje('Por favor selecciona una imagen válida', 'error');
                }
            }
        });
    }
}

// Función para geolocalización básica
function obtenerUbicacion(callback) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const ubicacion = {
                    latitud: position.coords.latitude,
                    longitud: position.coords.longitude
                };
                callback(ubicacion);
            },
            function() {
                mostrarMensaje('No se pudo obtener la ubicación', 'warning');
            }
        );
    } else {
        mostrarMensaje('Geolocalización no soportada', 'error');
    }
}

// Función para lazy loading de imágenes
function configurarLazyLoading() {
    const imagenes = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const observador = new IntersectionObserver(function(entradas) {
            entradas.forEach(function(entrada) {
                if (entrada.isIntersecting) {
                    const img = entrada.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observador.unobserve(img);
                }
            });
        });
        
        imagenes.forEach(function(img) {
            observador.observe(img);
        });
    }
}

// Inicializar lazy loading cuando carga la página
document.addEventListener('DOMContentLoaded', configurarLazyLoading);

// Función para animación suave de scroll
function scrollSuave(elemento) {
    if (elemento) {
        elemento.scrollIntoView({
            behavior: 'smooth'
        });
    }
}

// Función para detectar si el usuario está en móvil
function esMobile() {
    return window.innerWidth <= 768;
}

// Función para ajustar layout responsivo
function ajustarLayout() {
    if (esMobile()) {
        document.body.classList.add('mobile');
    } else {
        document.body.classList.remove('mobile');
    }
}

// Ajustar layout al cargar y redimensionar
window.addEventListener('load', ajustarLayout);
window.addEventListener('resize', ajustarLayout);

// Función para almacenamiento local simulado (sin usar localStorage)
let almacenamientoLocal = {};

function guardarDato(clave, valor) {
    almacenamientoLocal[clave] = valor;
}

function obtenerDato(clave) {
    return almacenamientoLocal[clave] || null;
}

function eliminarDato(clave) {
    delete almacenamientoLocal[clave];
}

// Función para manejo de formularios AJAX básico
function enviarFormularioAjax(formulario, callback) {
    const formData = new FormData(formulario);
    const xhr = new XMLHttpRequest();
    
    xhr.open('POST', formulario.action || window.location.href);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const respuesta = JSON.parse(xhr.responseText);
                callback(respuesta);
            } catch (e) {
                callback({ success: false, message: 'Error al procesar respuesta' });
            }
        } else {
            callback({ success: false, message: 'Error de conexión' });
        }
    };
    
    xhr.send(formData);
}

// Función para copiar al portapapeles
function copiarAlPortapapeles(texto) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(texto).then(function() {
            mostrarMensaje('Copiado al portapapeles', 'success');
        });
    } else {
        // Fallback para navegadores antiguos
        const textArea = document.createElement('textarea');
        textArea.value = texto;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        mostrarMensaje('Copiado al portapapeles', 'success');
    }
}

// Función para formatear números
function formatearNumero(numero) {
    return new Intl.NumberFormat('es-ES').format(numero);
}

// Funciones específicas para modales
function mostrarFormulario() {
    const modal = document.getElementById('modalAgregarMascota');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function cerrarFormulario() {
    const modal = document.getElementById('modalAgregarMascota');
    if (modal) {
        modal.style.display = 'none';
    }
}

console.log('Huella Segura - Scripts cargados correctamente');