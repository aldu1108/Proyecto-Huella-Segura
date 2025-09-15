// Scripts para Huella Segura

// Funcionalidad del menú hamburguesa
document.addEventListener('DOMContentLoaded', function() {
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

    // Navegación entre secciones de comunidad
    const sectionButtons = document.querySelectorAll('.section-btn');
    sectionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const section = this.dataset.section;
            
            // Remover clase activa de todos los botones
            sectionButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Ocultar todas las secciones
            const sections = document.querySelectorAll('.feed-section, .eventos-section, .grupos-section');
            sections.forEach(sec => sec.style.display = 'none');
            
            // Mostrar sección seleccionada
            const targetSection = document.getElementById(section + 'Section');
            if (targetSection) {
                targetSection.style.display = 'block';
            }
        });
    });

    // Navegación inferior
    const navButtons = document.querySelectorAll('.nav-btn');
    navButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            navButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Navegación básica
            switch(index) {
                case 0: // Adopciones
                    if (window.location.pathname !== '/adopciones.php') {
                        window.location.href = 'adopciones.php';
                    }
                    break;
                case 1: // Búsqueda
                    if (window.location.pathname !== '/mascotas-perdidas.php') {
                        window.location.href = 'mascotas-perdidas.php';
                    }
                    break;
                case 2: // Inicio
                    if (window.location.pathname !== '/index.php' && window.location.pathname !== '/') {
                        window.location.href = 'index.php';
                    }
                    break;
                case 3: // Comunidad
                    if (window.location.pathname !== '/comunidad.php') {
                        window.location.href = 'comunidad.php';
                    }
                    break;
                case 4: // Veterinaria
                    if (window.location.pathname !== '/veterinaria.php') {
                        window.location.href = 'veterinaria.php';
                    }
                    break;
            }
        });
    });

    // Marcar navegación activa según página actual
    setActiveNavigation();

    // Funcionalidad del calendario
    initCalendar();

    // Búsqueda en tiempo real
    initSearch();

    // Funcionalidad de posts
    initPosts();
});

// Función para establecer navegación activa
function setActiveNavigation() {
    const currentPath = window.location.pathname;
    const navButtons = document.querySelectorAll('.nav-btn');
    
    // Remover todas las clases activas
    navButtons.forEach(btn => btn.classList.remove('active'));
    
    // Establecer activo según la página
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

// Inicializar funcionalidad del calendario
function initCalendar() {
    const calendarDays = document.querySelectorAll('.day');
    
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            if (!this.classList.contains('today') && !this.classList.contains('event')) {
                // Remover selección previa
                calendarDays.forEach(d => d.classList.remove('selected'));
                this.classList.add('selected');
                
                // Aquí podrías cargar eventos para el día seleccionado
                console.log('Día seleccionado:', this.textContent);
            }
        });
    });

    // Navegación de mes
    const navArrows = document.querySelectorAll('.nav-arrow');
    navArrows.forEach((arrow, index) => {
        arrow.addEventListener('click', function() {
            const monthLabel = document.querySelector('.mes-actual');
            if (monthLabel) {
                // Lógica básica de navegación de mes
                console.log(index === 0 ? 'Mes anterior' : 'Mes siguiente');
                // Aquí implementarías la lógica real de cambio de mes
            }
        });
    });
}

// Inicializar búsqueda
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Limpiar timeout anterior
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Búsqueda con delay para evitar muchas consultas
            searchTimeout = setTimeout(() => {
                if (query.length > 2) {
                    performSearch(query);
                } else if (query.length === 0) {
                    clearSearch();
                }
            }, 300);
        });
    }
}

// Realizar búsqueda
function performSearch(query) {
    console.log('Buscando:', query);
    
    // Aquí implementarías la lógica real de búsqueda
    // Por ahora solo simulamos el filtrado local
    const searchableElements = document.querySelectorAll('[data-searchable]');
    
    searchableElements.forEach(element => {
        const text = element.textContent.toLowerCase();
        if (text.includes(query.toLowerCase())) {
            element.style.display = 'block';
        } else {
            element.style.display = 'none';
        }
    });
}

// Limpiar búsqueda
function clearSearch() {
    const searchableElements = document.querySelectorAll('[data-searchable]');
    searchableElements.forEach(element => {
        element.style.display = 'block';
    });
}

// Inicializar funcionalidad de posts
function initPosts() {
    // Botones de like
    const likeButtons = document.querySelectorAll('.action-btn');
    likeButtons.forEach(button => {
        if (button.textContent.includes('❤️')) {
            button.addEventListener('click', function() {
                toggleLike(this);
            });
        }
    });

    // Textarea de crear post
    const createPostTextarea = document.querySelector('.create-post textarea');
    if (createPostTextarea) {
        createPostTextarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                createPost(this.value);
                this.value = '';
            }
        });
    }
}

// Toggle like en posts
function toggleLike(button) {
    const currentText = button.textContent;
    const heartIcon = '❤️';
    const number = parseInt(currentText.match(/\d+/)[0]);
    
    if (button.classList.contains('liked')) {
        button.classList.remove('liked');
        button.textContent = `${heartIcon} ${number - 1}`;
        button.style.color = '#666';
    } else {
        button.classList.add('liked');
        button.textContent = `${heartIcon} ${number + 1}`;
        button.style.color = '#E74C3C';
    }
    
    // Animación de like
    button.style.transform = 'scale(1.2)';
    setTimeout(() => {
        button.style.transform = 'scale(1)';
    }, 150);
}

// Crear nuevo post
function createPost(content) {
    if (content.trim().length === 0) return;
    
    console.log('Creando post:', content);
    
    // Aquí implementarías la lógica para enviar el post al servidor
    // Por ahora solo mostramos un mensaje
    showMessage('Post creado exitosamente', 'success');
}

// Función para mostrar mensajes
function showMessage(text, type = 'info') {
    const message = document.createElement('div');
    message.className = `message-toast message-${type}`;
    message.textContent = text;
    
    // Estilos del mensaje
    Object.assign(message.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '8px',
        color: 'white',
        zIndex: '9999',
        maxWidth: '300px',
        opacity: '0',
        transform: 'translateX(100%)',
        transition: 'all 0.3s ease'
    });
    
    // Colores según tipo
    switch(type) {
        case 'success':
            message.style.backgroundColor = '#27ae60';
            break;
        case 'error':
            message.style.backgroundColor = '#e74c3c';
            break;
        case 'warning':
            message.style.backgroundColor = '#f39c12';
            break;
        default:
            message.style.backgroundColor = '#3498db';
    }
    
    document.body.appendChild(message);
    
    // Animación de entrada
    setTimeout(() => {
        message.style.opacity = '1';
        message.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        message.style.opacity = '0';
        message.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            if (message.parentNode) {
                message.parentNode.removeChild(message);
            }
        }, 300);
    }, 3000);
}

// Función para validar formularios
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
        
        // Validar email específicamente
        if (input.type === 'email' && input.value && !validateEmail(input.value)) {
            input.style.borderColor = '#e74c3c';
            input.classList.add('error');
            isValid = false;
        }
    });
    
    return isValid;
}

// Validar email
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Función para formatear fechas
function formatDate(date) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(date).toLocaleDateString('es-ES', options);
}

// Función para calcular tiempo transcurrido
function timeAgo(date) {
    const now = new Date();
    const diffTime = Math.abs(now - new Date(date));
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) return 'Hace 1 día';
    if (diffDays < 7) return `Hace ${diffDays} días`;
    if (diffDays < 30) return `Hace ${Math.ceil(diffDays / 7)} semanas`;
    return `Hace ${Math.ceil(diffDays / 30)} meses`;
}

// Función para manejo de errores global
window.addEventListener('error', function(e) {
    debug('Error capturado:', {
        message: e.message,
        filename: e.filename,
        line: e.lineno
    });
});

// Función para precargar imágenes importantes
function preloadImages(imageUrls) {
    imageUrls.forEach(url => {
        const img = new Image();
        img.src = url;
    });
}

// Función para optimizar rendimiento
function optimizePerformance() {
    // Lazy loading para imágenes
    initLazyLoading();
    
    // Precargar imágenes críticas
    const criticalImages = [
        'imagenes/logo.png',
        'imagenes/mascota-default.jpg'
    ];
    preloadImages(criticalImages);
    
    // Debounce para eventos de scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(handleScroll, 16); // ~60fps
    });
}

// Manejar scroll
function handleScroll() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Ocultar/mostrar navegación superior al hacer scroll
    const header = document.querySelector('.header-petcare');
    if (header) {
        if (scrollTop > 100) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
    }
}

// Función para manejar estados de carga
function setLoadingState(element, isLoading) {
    if (isLoading) {
        element.classList.add('loading');
        element.disabled = true;
    } else {
        element.classList.remove('loading');
        element.disabled = false;
    }
}

// Función para hacer peticiones AJAX
function makeRequest(url, options = {}) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        const method = options.method || 'GET';
        
        xhr.open(method, url);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(`HTTP Error: ${xhr.status}`));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network Error'));
        };
        
        if (options.data) {
            const formData = new FormData();
            Object.keys(options.data).forEach(key => {
                formData.append(key, options.data[key]);
            });
            xhr.send(formData);
        } else {
            xhr.send();
        }
    });
}

// Función para formatear números
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

// Función para animar números
function animateNumber(element, targetNumber, duration = 1000) {
    const startNumber = 0;
    const increment = targetNumber / (duration / 16);
    let currentNumber = startNumber;
    
    const timer = setInterval(() => {
        currentNumber += increment;
        if (currentNumber >= targetNumber) {
            element.textContent = formatNumber(targetNumber);
            clearInterval(timer);
        } else {
            element.textContent = formatNumber(Math.floor(currentNumber));
        }
    }, 16);
}

// Función para validar archivos
function validateFile(file, maxSize = 5000000, allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    if (!file) return { valid: false, message: 'No se seleccionó archivo' };
    
    if (file.size > maxSize) {
        return { valid: false, message: 'El archivo es demasiado grande' };
    }
    
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: 'Tipo de archivo no permitido' };
    }
    
    return { valid: true };
}

// Función para comprimir imágenes
function compressImage(file, maxWidth = 800, quality = 0.8) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = function() {
            const ratio = Math.min(maxWidth / img.width, maxWidth / img.height);
            canvas.width = img.width * ratio;
            canvas.height = img.height * ratio;
            
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            
            canvas.toBlob(resolve, 'image/jpeg', quality);
        };
        
        img.src = URL.createObjectURL(file);
    });
}

// Función para gestionar el estado de la aplicación
const AppState = {
    currentUser: null,
    currentPage: 'index',
    notifications: [],
    
    setCurrentUser: function(user) {
        this.currentUser = user;
        this.saveToStorage();
    },
    
    setCurrentPage: function(page) {
        this.currentPage = page;
    },
    
    addNotification: function(notification) {
        this.notifications.push({
            id: Date.now(),
            ...notification,
            timestamp: new Date()
        });
        this.saveToStorage();
        this.showNotification(notification);
    },
    
    removeNotification: function(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
        this.saveToStorage();
    },
    
    saveToStorage: function() {
        const data = {
            currentUser: this.currentUser,
            notifications: this.notifications
        };
        // En lugar de localStorage, guardar en variable temporal
        window.tempAppData = data;
    },
    
    loadFromStorage: function() {
        const data = window.tempAppData || {};
        this.currentUser = data.currentUser || null;
        this.notifications = data.notifications || [];
    },
    
    showNotification: function(notification) {
        showMessage(notification.message, notification.type || 'info');
    }
};

// Inicializar estado de la aplicación
AppState.loadFromStorage();

// Funciones de utilidad para fechas
const DateUtils = {
    formatSpanish: function(date) {
        const months = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];
        
        const d = new Date(date);
        return `${d.getDate()} de ${months[d.getMonth()]} de ${d.getFullYear()}`;
    },
    
    getRelativeTime: function(date) {
        const now = new Date();
        const diff = now - new Date(date);
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (minutes < 1) return 'Ahora mismo';
        if (minutes < 60) return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
        if (hours < 24) return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
        if (days < 7) return `Hace ${days} día${days > 1 ? 's' : ''}`;
        
        return this.formatSpanish(date);
    },
    
    isToday: function(date) {
        const today = new Date();
        const checkDate = new Date(date);
        return today.toDateString() === checkDate.toDateString();
    },
    
    isTomorrow: function(date) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const checkDate = new Date(date);
        return tomorrow.toDateString() === checkDate.toDateString();
    }
};

// Sistema de eventos personalizado
const EventBus = {
    events: {},
    
    on: function(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(callback);
    },
    
    emit: function(event, data) {
        if (this.events[event]) {
            this.events[event].forEach(callback => callback(data));
        }
    },
    
    off: function(event, callback) {
        if (this.events[event]) {
            this.events[event] = this.events[event].filter(cb => cb !== callback);
        }
    }
};

// Inicializar optimizaciones cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    optimizePerformance();
    
    // Animar estadísticas si están visibles
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const number = parseInt(stat.textContent);
        if (number && !isNaN(number)) {
            animateNumber(stat, number, 2000);
        }
    });
});

// Exportar funciones principales para uso global
window.PetCareApp = {
    showMessage,
    validateForm,
    validateEmail,
    formatDate,
    timeAgo,
    getCurrentLocation,
    copyToClipboard,
    makeRequest,
    AppState,
    DateUtils,
    EventBus
};

console.log('PetCare Scripts v1.0 - Cargados correctamente'); 
function handleFileUpload(input, preview) {
    if (input && preview) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    showMessage('Por favor selecciona una imagen válida', 'error');
                }
            }
        });
    }
}

// Función para lazy loading de imágenes
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
        // Fallback para navegadores sin soporte
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
}

// Función para obtener ubicación
function getCurrentLocation(callback) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const location = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                callback(location);
            },
            function() {
                showMessage('No se pudo obtener la ubicación', 'warning');
            }
        );
    } else {
        showMessage('Geolocalización no soportada', 'error');
    }
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showMessage('Copiado al portapapeles', 'success');
        });
    } else {
        // Fallback
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showMessage('Copiado al portapapeles', 'success');
    }
}

// Función para detectar si es dispositivo móvil
function isMobile() {
    return window.innerWidth <= 768;
}

// Función para ajustar layout responsivo
function adjustLayout() {
    const body = document.body;
    
    if (isMobile()) {
        body.classList.add('mobile');
        body.classList.remove('desktop');
    } else {
        body.classList.add('desktop');
        body.classList.remove('mobile');
    }
    
    // Ajustar altura del calendario en móvil
    const calendar = document.querySelector('.calendar-grid');
    if (calendar && isMobile()) {
        calendar.style.fontSize = '12px';
    }
}

// Función para animar elementos al hacer scroll
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        animatedElements.forEach(el => observer.observe(el));
    }
}

// Event listeners para resize
window.addEventListener('load', adjustLayout);
window.addEventListener('resize', adjustLayout);

// Inicializar animaciones y lazy loading
document.addEventListener('DOMContentLoaded', function() {
    initLazyLoading();
    initScrollAnimations();
});

// Función para debugging (solo en desarrollo)
function debug(variable, label = 'DEBUG') {
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log(label + ':', variable);
    }
}

// Funcionalidades adicionales para mascotas perdidas y adopciones

// Variables globales para el modal de reporte
let pasoActual = 1;
const totalPasos = 4;
let datosFormulario = {};

// Inicialización cuando se carga el documento
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar funcionalidades existentes
    initExistingFeatures();
    
    // Nuevas funcionalidades
    initMascotasPerdidas();
    initAdopciones();
});

function initExistingFeatures() {
    // Mantener funcionalidades existentes del script original
    const menuHamburguesa = document.getElementById('menuHamburguesa');
    const menuLateral = document.getElementById('menuLateral');
    
    if (menuHamburguesa && menuLateral) {
        menuHamburguesa.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (menuLateral.classList.contains('show')) {
                menuLateral.classList.remove('show');
            } else {
                menuLateral.classList.add('show');
            }
        });

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            if (!menuHamburguesa.contains(event.target) && !menuLateral.contains(event.target)) {
                menuLateral.classList.remove('show');
            }
        });
    }
}

// Funcionalidades específicas para mascotas perdidas
function initMascotasPerdidas() {
    // Solo ejecutar en la página de mascotas perdidas
    if (!document.querySelector('.titulo-mascotas-perdidas')) return;
    
    // Configurar modal de reporte
    setupModalReporte();
    
    // Configurar preview de imagen
    setupImagePreview();
    
    // Configurar geolocalización
    setupGeolocation();
}

function setupModalReporte() {
    const modal = document.getElementById('modalReporte');
    if (!modal) return;
    
    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            cerrarFormularioReporte();
        }
    });
    
    // Prevenir scroll del body cuando el modal está abierto
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'style') {
                if (modal.style.display === 'flex') {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
        });
    });
    
    observer.observe(modal, { attributes: true });
}

function setupImagePreview() {
    const inputFoto = document.getElementById('inputFoto');
    const previewFoto = document.getElementById('previewFoto');
    
    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tipo de archivo
                if (!file.type.startsWith('image/')) {
                    showMessage('Por favor selecciona una imagen válida', 'error');
                    return;
                }
                
                // Validar tamaño (5MB max)
                if (file.size > 5000000) {
                    showMessage('La imagen es demasiado grande (máximo 5MB)', 'error');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFoto.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

function setupGeolocation() {
    // Esta función se llamará desde el botón GPS
    window.obtenerUbicacion = function() {
        const inputUbicacion = document.querySelector('input[name="ubicacion"]');
        const mensajeEstado = document.querySelector('small');
        
        if (!navigator.geolocation) {
            showMessage('Tu navegador no soporta geolocalización', 'error');
            return;
        }
        
        mensajeEstado.textContent = 'Obteniendo ubicación...';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // En una implementación real, usarías un servicio de geocodificación inversa
                // Por ahora, mostraremos las coordenadas
                inputUbicacion.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                mensajeEstado.textContent = 'Ubicación obtenida correctamente';
                mensajeEstado.style.color = '#27ae60';
            },
            function(error) {
                let mensaje = 'No se pudo obtener la ubicación';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        mensaje = 'Permisos de ubicación denegados';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        mensaje = 'Ubicación no disponible';
                        break;
                    case error.TIMEOUT:
                        mensaje = 'Tiempo de espera agotado';
                        break;
                }
                mensajeEstado.textContent = mensaje;
                mensajeEstado.style.color = '#e74c3c';
                showMessage(mensaje, 'error');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    };
}

// Funciones del modal de reporte
function mostrarFormularioReporte() {
    const modal = document.getElementById('modalReporte');
    if (modal) {
        modal.style.display = 'flex';
        pasoActual = 1;
        mostrarPaso(1);
        
        // Reset form data
        datosFormulario = {};
    }
}

function cerrarFormularioReporte() {
    const modal = document.getElementById('modalReporte');
    if (modal) {
        modal.style.display = 'none';
        pasoActual = 1;
        mostrarPaso(1);
        
        // Limpiar formulario
        const form = document.getElementById('formularioReporte');
        if (form) {
            form.reset();
        }
    }
}

function mostrarPaso(numeroPaso) {
    // Ocultar todos los pasos
    for (let i = 1; i <= totalPasos; i++) {
        const paso = document.getElementById('paso' + i);
        if (paso) {
            paso.style.display = 'none';
        }
    }
    
    // Mostrar paso actual
    const pasoActual = document.getElementById('paso' + numeroPaso);
    if (pasoActual) {
        pasoActual.style.display = 'block';
    }
}

function siguientePaso(siguientePasoNum) {
    if (validarPasoActual()) {
        guardarDatosPaso();
        pasoActual = siguientePasoNum;
        mostrarPaso(siguientePasoNum);
        
        if (siguientePasoNum === 4) {
            generarResumen();
        }
    }
}

function anteriorPaso(anteriorPasoNum) {
    pasoActual = anteriorPasoNum;
    mostrarPaso(anteriorPasoNum);
}

function validarPasoActual() {
    const pasoDiv = document.getElementById('paso' + pasoActual);
    if (!pasoDiv) return false;
    
    const camposRequeridos = pasoDiv.querySelectorAll('input[required], select[required], textarea[required]');
    let esValido = true;
    
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#E74C3C';
            campo.classList.add('error');
            esValido = false;
        } else {
            campo.style.borderColor = '#e8e8e8';
            campo.classList.remove('error');
        }
    });

    if (!esValido) {
        showMessage('Por favor completa todos los campos requeridos', 'error');
    }

    return esValido;
}

function guardarDatosPaso() {
    const pasoDiv = document.getElementById('paso' + pasoActual);
    if (!pasoDiv) return;
    
    const inputs = pasoDiv.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (input.name) {
            datosFormulario[input.name] = input.value;
        }
    });
}

function generarResumen() {
    const resumenDiv = document.getElementById('resumenReporte');
    if (!resumenDiv) return;
    
    let resumen = '<div style="background: #f8f9fa; padding: 16px; border-radius: 12px;">';
    
    resumen += '<h5 style="margin-bottom: 12px; color: #333;">📋 Información de la mascota:</h5>';
    resumen += `<p><strong>Nombre:</strong> ${datosFormulario.nombre_mascota || 'No especificado'}</p>`;
    resumen += `<p><strong>Tipo:</strong> ${datosFormulario.tipo_animal || 'No especificado'}</p>`;
    resumen += `<p><strong>Raza:</strong> ${datosFormulario.raza || 'No especificada'}</p>`;
    resumen += `<p><strong>Color:</strong> ${datosFormulario.color || 'No especificado'}</p>`;
    resumen += `<p><strong>Tamaño:</strong> ${datosFormulario.tamaño || 'No especificado'}</p>`;
    
    resumen += '<h5 style="margin: 16px 0 8px; color: #333;">📍 Información del incidente:</h5>';
    resumen += `<p><strong>Fecha:</strong> ${datosFormulario.fecha_perdida ? formatearFecha(datosFormulario.fecha_perdida) : 'No especificada'}</p>`;
    resumen += `<p><strong>Hora:</strong> ${datosFormulario.hora_perdida || 'No especificada'}</p>`;
    resumen += `<p><strong>Ubicación:</strong> ${datosFormulario.ubicacion || 'No especificada'}</p>`;
    
    resumen += '<h5 style="margin: 16px 0 8px; color: #333;">📞 Información de contacto:</h5>';
    resumen += `<p><strong>Nombre:</strong> ${datosFormulario.nombre_propietario || 'No especificado'}</p>`;
    resumen += `<p><strong>Teléfono:</strong> ${datosFormulario.telefono || 'No especificado'}</p>`;
    
    if (datosFormulario.ofrecer_recompensa) {
        resumen += '<p style="color: #f39c12;"><strong>💰 Ofrece recompensa</strong></p>';
    }
    
    resumen += '</div>';
    
    resumen += '<div style="background: #fff8e1; border: 1px solid #ffecb5; padding: 12px; border-radius: 8px; margin-top: 16px;">';
    resumen += '<p style="font-size: 14px; color: #856404; margin: 0;">⚠️ <strong>Recuerda:</strong> Una vez creado el reporte, se compartirá automáticamente en la comunidad y redes sociales para maximizar las posibilidades de encontrar a tu mascota.</p>';
    resumen += '</div>';
    
    resumenDiv.innerHTML = resumen;
}

function enviarReporte() {
    // Guardar datos del paso final
    guardarDatosPaso();
    
    // Aquí normalmente enviarías los datos al servidor
    console.log('Datos del reporte:', datosFormulario);
    
    // Simular envío exitoso
    showMessage('¡Reporte creado exitosamente! Hemos notificado a la comunidad local.', 'success');
    
    // Cerrar modal
    setTimeout(() => {
        cerrarFormularioReporte();
    }, 2000);
}

// Funcionalidades específicas para adopciones
function initAdopciones() {
    // Solo ejecutar en la página de adopciones
    if (!document.querySelector('.titulo-adopciones')) return;
    
    setupFiltrosAdopcion();
    setupBotonesAdopcion();
    setupAnimacionesTarjetas();
}

function setupFiltrosAdopcion() {
    const filtros = document.querySelectorAll('.filtro-adopcion');
    
    filtros.forEach(filtro => {
        filtro.addEventListener('click', function() {
            // Remover clase activo de todos
            filtros.forEach(f => f.classList.remove('activo'));
            // Agregar clase activo al seleccionado
            this.classList.add('activo');
            
            const tipoFiltro = this.textContent.trim();
            filtrarTarjetas(tipoFiltro);
        });
    });
}

function filtrarTarjetas(filtro) {
    const tarjetas = document.querySelectorAll('.tarjeta-adopcion');
    
    tarjetas.forEach(tarjeta => {
        const tipoMascota = tarjeta.querySelector('.detalles-basicos').textContent;
        let mostrar = true;
        
        if (filtro.includes('Perros') && !tipoMascota.toLowerCase().includes('pastor') && !tipoMascota.toLowerCase().includes('mestizo')) {
            mostrar = false;
        } else if (filtro.includes('Gatos') && !tipoMascota.toLowerCase().includes('siamés')) {
            mostrar = false;
        } else if (filtro.includes('Otros') && (tipoMascota.toLowerCase().includes('siamés') || tipoMascota.toLowerCase().includes('pastor') || tipoMascota.toLowerCase().includes('mestizo'))) {
            mostrar = false;
        }
        
        if (mostrar) {
            tarjeta.style.display = 'block';
            tarjeta.style.animation = 'slideUp 0.3s ease forwards';
        } else {
            tarjeta.style.display = 'none';
        }
    });
}

function setupBotonesAdopcion() {
    const botones = document.querySelectorAll('.boton-interesa-adoptar');
    
    botones.forEach(boton => {
        boton.addEventListener('click', function() {
            const tarjeta = this.closest('.tarjeta-adopcion');
            const nombreMascota = tarjeta.querySelector('h3').textContent;
            const tipoMascota = tarjeta.querySelector('.detalles-basicos').textContent;
            
            // Animación del botón
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            // Mostrar modal de interés o redirigir
            mostrarModalInteres(nombreMascota, tipoMascota);
        });
    });
}

function mostrarModalInteres(nombre, tipo) {
    // Crear modal dinámico para mostrar interés en adopción
    const modalHTML = `
        <div class="modal-reporte" id="modalInteres" style="display: flex;">
            <div class="contenido-modal-reporte" style="max-width: 400px;">
                <div class="encabezado-modal-reporte">
                    <h3 class="titulo-modal-reporte">¡Interés en Adopción!</h3>
                    <button class="boton-cerrar-modal" onclick="cerrarModalInteres()">×</button>
                </div>
                
                <div class="formulario-reporte">
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 48px; margin-bottom: 16px;">❤️</div>
                        <h4 style="color: #333; margin-bottom: 8px;">Te interesa adoptar a ${nombre}</h4>
                        <p style="color: #666; margin-bottom: 20px;">${tipo}</p>
                        <p style="font-size: 14px; color: #555; line-height: 1.5; margin-bottom: 24px;">
                            ¡Excelente decisión! Te pondremos en contacto con el refugio para que puedas conocer más sobre ${nombre} y comenzar el proceso de adopción.
                        </p>
                        
                        <div style="background: #f8f9fa; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
                            <h5 style="margin-bottom: 12px; color: #333;">📋 Próximos pasos:</h5>
                            <ul style="text-align: left; font-size: 14px; color: #666;">
                                <li style="margin-bottom: 8px;">Te contactaremos en las próximas 24 horas</li>
                                <li style="margin-bottom: 8px;">Coordinaremos una visita al refugio</li>
                                <li style="margin-bottom: 8px;">Evaluaremos la compatibilidad</li>
                                <li style="margin-bottom: 8px;">Procesaremos la adopción</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="botones-formulario">
                        <button type="button" class="boton-anterior" onclick="cerrarModalInteres()">Cancelar</button>
                        <button type="button" class="boton-siguiente" onclick="confirmarInteres('${nombre}')">Confirmar Interés</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Insertar modal en el DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Cerrar al hacer clic fuera
    const modal = document.getElementById('modalInteres');
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            cerrarModalInteres();
        }
    });
}

function cerrarModalInteres() {
    const modal = document.getElementById('modalInteres');
    if (modal) {
        modal.remove();
    }
}

function confirmarInteres(nombreMascota) {
    // Aquí enviarías la solicitud al servidor
    showMessage(`¡Solicitud enviada! Te contactaremos pronto sobre ${nombreMascota}.`, 'success');
    cerrarModalInteres();
}

function setupAnimacionesTarjetas() {
    // Observador de intersección para animar tarjetas cuando entran en vista
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        const tarjetas = document.querySelectorAll('.tarjeta-adopcion');
        tarjetas.forEach((tarjeta, index) => {
            tarjeta.style.opacity = '0';
            tarjeta.style.transform = 'translateY(30px)';
            tarjeta.style.transition = `all 0.3s ease ${index * 0.1}s`;
            observer.observe(tarjeta);
        });
    }
}

// Funciones utilitarias
function formatearFecha(fecha) {
    const opciones = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
}

// Función de validación mejorada para formularios
function validarFormularioCompleto(formulario) {
    const campos = formulario.querySelectorAll('input, select, textarea');
    let esValido = true;
    let primerCampoInvalido = null;
    
    campos.forEach(campo => {
        let campoValido = true;
        
        // Validación básica: campos requeridos
        if (campo.hasAttribute('required') && !campo.value.trim()) {
            campoValido = false;
        }
        
        // Validación específica por tipo
        if (campo.value.trim()) {
            switch (campo.type) {
                case 'email':
                    if (!validarEmail(campo.value)) {
                        campoValido = false;
                    }
                    break;
                case 'tel':
                    if (!validarTelefono(campo.value)) {
                        campoValido = false;
                    }
                    break;
                case 'date':
                    if (!validarFecha(campo.value)) {
                        campoValido = false;
                    }
                    break;
            }
        }
        
        // Aplicar estilos de validación
        if (campoValido) {
            campo.style.borderColor = '#e8e8e8';
            campo.classList.remove('error');
        } else {
            campo.style.borderColor = '#E74C3C';
            campo.classList.add('error');
            esValido = false;
            
            if (!primerCampoInvalido) {
                primerCampoInvalido = campo;
            }
        }
    });
    
    // Enfocar primer campo inválido
    if (!esValido && primerCampoInvalido) {
        primerCampoInvalido.focus();
        primerCampoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    return esValido;
}

function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validarTelefono(telefono) {
    const regex = /^[\+]?[\d\s\-\(\)]{9,}$/;
    return regex.test(telefono);
}

function validarFecha(fecha) {
    const fechaSeleccionada = new Date(fecha);
    const hoy = new Date();
    const hace30Dias = new Date();
    hace30Dias.setDate(hace30Dias.getDate() - 30);
    
    return fechaSeleccionada >= hace30Dias && fechaSeleccionada <= hoy;
}

// Función mejorada para mostrar mensajes
function showMessage(mensaje, tipo = 'info', duracion = 4000) {
    // Remover mensajes existentes
    const mensajesExistentes = document.querySelectorAll('.mensaje-notificacion');
    mensajesExistentes.forEach(m => m.remove());
    
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `mensaje-notificacion mensaje-${tipo}`;
    
    // Iconos según el tipo
    const iconos = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    mensajeDiv.innerHTML = `
        <span class="icono-mensaje">${iconos[tipo] || iconos.info}</span>
        <span class="texto-mensaje">${mensaje}</span>
        <button class="boton-cerrar-mensaje" onclick="this.parentElement.remove()">×</button>
    `;
    
    // Estilos inline para el mensaje
    Object.assign(mensajeDiv.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: getColorMensaje(tipo),
        color: 'white',
        padding: '16px 20px',
        borderRadius: '12px',
        boxShadow: '0 4px 16px rgba(0,0,0,0.2)',
        zIndex: '10000',
        maxWidth: '400px',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        animation: 'slideIn 0.3s ease',
        fontSize: '14px',
        fontWeight: '500'
    });
    
    mensajeDiv.querySelector('.boton-cerrar-mensaje').style.cssText = `
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 4px 8px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 12px;
    `;
    
    document.body.appendChild(mensajeDiv);
    
    // Auto-remover después de la duración especificada
    setTimeout(() => {
        if (mensajeDiv.parentElement) {
            mensajeDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => mensajeDiv.remove(), 300);
        }
    }, duracion);
}

function getColorMensaje(tipo) {
    const colores = {
        success: '#27ae60',
        error: '#e74c3c',
        warning: '#f39c12',
        info: '#3498db'
    };
    return colores[tipo] || colores.info;
}

// Agregar estilos CSS para las animaciones de mensajes
const estilosMensajes = document.createElement('style');
estilosMensajes.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .mensaje-notificacion .boton-cerrar-mensaje:hover {
        background: rgba(255,255,255,0.3) !important;
    }
`;
document.head.appendChild(estilosMensajes);

// Función para detectar dispositivos táctiles
function eDispositivoTactil() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
}

// Mejorar la experiencia táctil en dispositivos móviles
if (eDispositivoTactil()) {
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar clase para dispositivos táctiles
        document.body.classList.add('dispositivo-tactil');
        
        // Mejorar los botones táctiles
        const botones = document.querySelectorAll('button, .btn, .boton-reporte-perdida, .boton-interesa-adoptar');
        botones.forEach(boton => {
            boton.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
            }, { passive: true });
            
            boton.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            }, { passive: true });
        });
    });
}

// Función de búsqueda mejorada para mascotas perdidas
function initBusquedaInteligente() {
    const barraBusqueda = document.querySelector('.barra-busqueda');
    if (!barraBusqueda) return;
    
    let timeoutBusqueda;
    
    barraBusqueda.addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);
        const termino = this.value.trim().toLowerCase();
        
        timeoutBusqueda = setTimeout(() => {
            if (termino.length > 2) {
                realizarBusqueda(termino);
            } else if (termino.length === 0) {
                limpiarBusqueda();
            }
        }, 300);
    });
}

function realizarBusqueda(termino) {
    // Aquí implementarías la búsqueda real
    console.log('Buscando:', termino);
    
    // Simular resultados de búsqueda
    const elementos = document.querySelectorAll('[data-searchable]');
    elementos.forEach(elemento => {
        const texto = elemento.textContent.toLowerCase();
        if (texto.includes(termino)) {
            elemento.style.display = 'block';
            resaltarTermino(elemento, termino);
        } else {
            elemento.style.display = 'none';
        }
    });
}

function resaltarTermino(elemento, termino) {
    // Implementar resaltado de términos de búsqueda
    const textoOriginal = elemento.textContent;
    const regex = new RegExp(`(${termino})`, 'gi');
    const textoResaltado = textoOriginal.replace(regex, '<mark>$1</mark>');
    
    // Solo aplicar si el elemento no contiene otros elementos importantes
    if (elemento.children.length === 0) {
        elemento.innerHTML = textoResaltado;
    }
}

function limpiarBusqueda() {
    const elementos = document.querySelectorAll('[data-searchable]');
    elementos.forEach(elemento => {
        elemento.style.display = 'block';
        // Limpiar resaltado
        const marcas = elemento.querySelectorAll('mark');
        marcas.forEach(marca => {
            marca.replaceWith(marca.textContent);
        });
    });
}

// Inicializar búsqueda inteligente
document.addEventListener('DOMContentLoaded', function() {
    initBusquedaInteligente();
});

// Función para manejar errores de carga de imágenes
function initManejadorImagenes() {
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG') {
            // Reemplazar con imagen por defecto
            e.target.src = 'imagenes/mascota-default.jpg';
            e.target.alt = 'Imagen no disponible';
        }
    }, true);
}

// Función para optimizar rendimiento en dispositivos lentos
function optimizarRendimiento() {
    // Lazy loading mejorado
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
}

// Inicializar todas las funciones de optimización
document.addEventListener('DOMContentLoaded', function() {
    initManejadorImagenes();
    optimizarRendimiento();
});

// Exportar funciones principales para uso global
window.MascotasPerdidas = {
    mostrarFormularioReporte,
    cerrarFormularioReporte,
    siguientePaso,
    anteriorPaso,
    enviarReporte
};

window.Adopciones = {
    mostrarModalInteres,
    cerrarModalInteres,
    confirmarInteres
};

console.log('Funcionalidades de mascotas perdidas y adopciones cargadas correctamente');