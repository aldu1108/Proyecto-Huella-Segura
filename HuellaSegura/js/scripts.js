// Scripts para PetCare

// Funcionalidad del menú hamburguesa
document.addEventListener('DOMContentLoaded', function() {
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

        // Cerrar menú al hacer clic en un elemento del menú
        const menuItems = menuLateral.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                menuLateral.classList.remove('show');
            });
        });
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

// Función para mane