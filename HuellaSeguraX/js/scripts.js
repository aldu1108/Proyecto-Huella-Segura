// Scripts para Huella Segura
console.log('Iniciando scripts de Huella Segura...');

// Funcionalidad del menú hamburguesa
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Inicializando menú...');
    
    const menuHamburguesa = document.getElementById('menuHamburguesa');
    const menuLateral = document.getElementById('menuLateral');
    const overlayMenu = document.getElementById('overlayMenu');
    
    if (menuHamburguesa && menuLateral && overlayMenu) {
        console.log('Elementos del menú encontrados');
        
        // Abrir menú
        menuHamburguesa.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Click en hamburguesa');
            
            menuLateral.classList.add('activo');
            overlayMenu.classList.add('activo');
            document.body.style.overflow = 'hidden';
        });
        
        // Cerrar menú con overlay
        overlayMenu.addEventListener('click', function() {
            console.log('Click en overlay');
            menuLateral.classList.remove('activo');
            overlayMenu.classList.remove('activo');
            document.body.style.overflow = '';
        });
        
        // Cerrar menú con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && menuLateral.classList.contains('activo')) {
                menuLateral.classList.remove('activo');
                overlayMenu.classList.remove('activo');
                document.body.style.overflow = '';
            }
        });
        
        // Cerrar menú al hacer clic en opciones
        const opcionesMenu = menuLateral.querySelectorAll('.opcion-menu');
        opcionesMenu.forEach(function(opcion) {
            opcion.addEventListener('click', function() {
                menuLateral.classList.remove('activo');
                overlayMenu.classList.remove('activo');
                document.body.style.overflow = '';
            });
        });
        
        console.log('Menú hamburguesa inicializado correctamente');
    } else {
        console.error('Elementos del menú no encontrados:', {
            menuHamburguesa: !!menuHamburguesa,
            menuLateral: !!menuLateral,
            overlayMenu: !!overlayMenu
        });
    }
    
    // Inicializar otras funcionalidades
    initNavegacion();
    initCalendario();
    initBusqueda();
    initPosts();
    animarTarjetas();
});


// Función para mostrar mensajes
function mostrarMensaje(texto, tipo = 'info') {
    const mensaje = document.createElement('div');
    mensaje.className = 'mensaje-flotante mensaje-' + tipo;
    mensaje.textContent = texto;
    
    Object.assign(mensaje.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '1rem 1.5rem',
        borderRadius: '8px',
        color: 'white',
        zIndex: '9999',
        maxWidth: '300px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
        animation: 'slideIn 0.3s ease'
    });
    
    // Colores según tipo
    const colores = {
        success: '#27ae60',
        error: '#e74c3c',
        warning: '#f39c12',
        info: '#3498db'
    };
    mensaje.style.backgroundColor = colores[tipo] || colores.info;
    
    document.body.appendChild(mensaje);
    
    setTimeout(() => {
        mensaje.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => mensaje.remove(), 300);
    }, 3000);
}

// Navegación inferior
function initNavegacion() {
    const currentPath = window.location.pathname;
    const navButtons = document.querySelectorAll('.nav-btn');
    
    navButtons.forEach(btn => btn.classList.remove('active'));
    
    if (currentPath.includes('adopciones')) {
        navButtons[0]?.classList.add('active');
    } else if (currentPath.includes('mascotas-perdidas')) {
        navButtons[1]?.classList.add('active');
    } else if (currentPath.includes('index') || currentPath === '/') {
        navButtons[2]?.classList.add('active');
    } else if (currentPath.includes('comunidad')) {
        navButtons[3]?.classList.add('active');
    } else if (currentPath.includes('veterinaria')) {
        navButtons[4]?.classList.add('active');
    }
}

// Funcionalidad del calendario
let mesActual = new Date().getMonth();
let añoActual = new Date().getFullYear();

const meses = [
    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
];

function initCalendario() {
    if (window.perfilMascotaPage) {
        return;
    }
    
    const diasCalendario = document.getElementById('diasCalendario');
    if (diasCalendario) {
        generarCalendario(mesActual, añoActual);
    }
}

function generarCalendario(mes, año) {
    if (window.perfilMascotaPage) {
        return;
    }

    const primerDia = new Date(año, mes, 1).getDay();
    const diasEnMes = new Date(año, mes + 1, 0).getDate();
    const hoy = new Date();
    const esHoy = (dia) => hoy.getDate() === dia && hoy.getMonth() === mes && hoy.getFullYear() === año;
    
    // Obtener días con eventos desde PHP (se pasan como variable global)
    const diasConEventos = window.diasConEventosCalendario || [];
    
    let html = '';
    
    // Días vacíos del mes anterior
    for (let i = 0; i < primerDia; i++) {
        const diasMesAnterior = new Date(año, mes, 0).getDate();
        const dia = diasMesAnterior - primerDia + i + 1;
        html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
    }
    
    // Días del mes actual
    for (let dia = 1; dia <= diasEnMes; dia++) {
        let clases = 'dia-calendario';
        if (esHoy(dia)) clases += ' hoy';
        if (diasConEventos.includes(dia)) clases += ' evento';
        
        html += `<div class="${clases}" onclick="seleccionarDia(${dia})">${dia}</div>`;
    }
    
    // Días del próximo mes
    const celdasTotales = 35;
    const celdasUsadas = primerDia + diasEnMes;
    const diasProximoMes = celdasTotales - celdasUsadas;
    
    for (let dia = 1; dia <= diasProximoMes; dia++) {
        html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
    }
    
    const diasCalendario = document.getElementById('diasCalendario');
    if (diasCalendario) {
        diasCalendario.innerHTML = html;
    }
    
    const mesActualElement = document.getElementById('mesActual');
    if (mesActualElement) {
        mesActualElement.textContent = `${meses[mes]} de ${año}`;
    }
}

function cambiarMes(direccion) {
    if (window.perfilMascotaPage) {
        return;
    }

    mesActual += direccion;
    if (mesActual > 11) {
        mesActual = 0;
        añoActual++;
    } else if (mesActual < 0) {
        mesActual = 11;
        añoActual--;
    }
    generarCalendario(mesActual, añoActual);
}

function seleccionarDia(dia) {
    if (window.perfilMascotaPage) {
        return;
    }
    
    document.querySelectorAll('.dia-calendario.seleccionado').forEach(d => 
        d.classList.remove('seleccionado')
    );
    
    event.target.classList.add('seleccionado');
    console.log(`Día seleccionado: ${dia}/${mesActual + 1}/${añoActual}`);
}

// Funcionalidad de búsqueda
function initBusqueda() {
    const inputBusqueda = document.querySelector('.input-busqueda-principal');
    if (!inputBusqueda) return;
    
    let timeoutBusqueda;
    
    inputBusqueda.addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);
        const termino = this.value.trim();
        
        timeoutBusqueda = setTimeout(() => {
            if (termino.length > 2) {
                realizarBusqueda(termino);
            }
        }, 300);
    });
}

function realizarBusqueda(termino) {
    console.log('Buscando:', termino);
    
    if (termino.toLowerCase().includes('veterinario')) {
        window.location.href = 'veterinaria.php';
    } else if (termino.toLowerCase().includes('perdida')) {
        window.location.href = 'mascotas-perdidas.php';
    } else if (termino.toLowerCase().includes('adopcion')) {
        window.location.href = 'adopciones.php';
    }
}

// Funcionalidad de posts (comunidad)
function initPosts() {
    const likeButtons = document.querySelectorAll('.action-btn');
    likeButtons.forEach(button => {
        if (button.textContent.includes('❤️')) {
            button.addEventListener('click', function() {
                toggleLike(this);
            });
        }
    });
}

function toggleLike(button) {
    const currentText = button.textContent;
    const heartIcon = '❤️';
    const numberMatch = currentText.match(/\d+/);
    
    if (!numberMatch) return;
    
    const number = parseInt(numberMatch[0]);
    
    if (button.classList.contains('liked')) {
        button.classList.remove('liked');
        button.textContent = `${heartIcon} ${number - 1}`;
        button.style.color = '#666';
    } else {
        button.classList.add('liked');
        button.textContent = `${heartIcon} ${number + 1}`;
        button.style.color = '#E74C3C';
    }
    
    button.style.transform = 'scale(1.2)';
    setTimeout(() => {
        button.style.transform = 'scale(1)';
    }, 150);
}

// Animaciones de entrada para tarjetas
function animarTarjetas() {
    const tarjetas = document.querySelectorAll('.mascota-card-principal, .perdida-item-index');
    tarjetas.forEach((tarjeta, index) => {
        tarjeta.style.opacity = '0';
        tarjeta.style.transform = 'translateY(20px)';
        tarjeta.style.transition = `all 0.3s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            tarjeta.style.opacity = '1';
            tarjeta.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Validación de formularios
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#e74c3c';
            input.classList.add('error');
            isValid = false;
        } else {
            input.style.borderColor = '#e8e8e8';
            input.classList.remove('error');
        }
        
        if (input.type === 'email' && input.value && !validateEmail(input.value)) {
            input.style.borderColor = '#e74c3c';
            input.classList.add('error');
            isValid = false;
        }
    });
    
    return isValid;
}

function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Formatear fechas
function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('es-ES', options);
}

function timeAgo(date) {
    const now = new Date();
    const diffTime = Math.abs(now - new Date(date));
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) return 'Hace 1 día';
    if (diffDays < 7) return `Hace ${diffDays} días`;
    if (diffDays < 30) return `Hace ${Math.ceil(diffDays / 7)} semanas`;
    return `Hace ${Math.ceil(diffDays / 30)} meses`;
}

// Lazy loading de imágenes
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => observer.observe(img));
    } else {
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
}

// Detectar dispositivo móvil
function isMobile() {
    return window.innerWidth <= 768;
}

// Copiar al portapapeles
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            mostrarMensaje('Copiado al portapapeles', 'success');
        });
    } else {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        mostrarMensaje('Copiado al portapapeles', 'success');
    }
}

// Manejo de errores de imágenes
document.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG') {
        e.target.src = 'imagenes/mascota-default.jpg';
        e.target.alt = 'Imagen no disponible';
    }
}, true);

// Animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100%); }
    }
`;
document.head.appendChild(style);

// Inicializar lazy loading cuando carga la página
window.addEventListener('load', function() {
    initLazyLoading();
});

// Exportar funciones para uso global
window.PetCareApp = {
    mostrarMensaje,
    validateForm,
    validateEmail,
    formatDate,
    timeAgo,
    copyToClipboard,
    isMobile
};

function mostrarModalRecordatorio() {
    document.getElementById('modalRecordatorio').style.display = 'flex';
}

function cerrarModalRecordatorio() {
    document.getElementById('modalRecordatorio').style.display = 'none';
}

// Función toggle password
function togglePassword(button) {
  const input = button.previousElementSibling;
  const icon = button.querySelector('i');

  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}

console.log('Scripts de Huella Segura cargados completamente');