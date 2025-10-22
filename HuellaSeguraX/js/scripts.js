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
    
    // Obtener eventos del mes y calcular días con eventos
    const eventosMes = window.indexCalendarioData ? window.indexCalendarioData.eventosMes : [];
    const diasConEventos = [];
    
    eventosMes.forEach(evento => {
        const fechaEvento = new Date(evento.fecha);
        if (fechaEvento.getMonth() === mes && fechaEvento.getFullYear() === año) {
            const dia = fechaEvento.getDate();
            if (!diasConEventos.includes(dia)) {
                diasConEventos.push(dia);
            }
        }
    });
    
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
    
    if (event && event.target) {
        event.target.classList.add('seleccionado');
    }
    
    const fechaSeleccionada = `${añoActual}-${String(mesActual + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    mostrarEventosDiaIndex(fechaSeleccionada, dia);
}

function mostrarEventosDiaIndex(fecha, dia) {
    const eventos = window.indexCalendarioData ? 
        window.indexCalendarioData.eventosMes.filter(e => e.fecha.startsWith(fecha)) : [];
    
    const hoy = new Date();
    const [año, mes, diaStr] = fecha.split('-');
    const fechaSelec = new Date(parseInt(año), parseInt(mes) - 1, parseInt(diaStr));
    
    let tituloFecha = '';
    
    if (fechaSelec.getDate() === hoy.getDate() && 
        fechaSelec.getMonth() === hoy.getMonth() && 
        fechaSelec.getFullYear() === hoy.getFullYear()) {
        tituloFecha = 'Hoy';
    } else {
        const opciones = { day: 'numeric', month: 'long' };
        tituloFecha = fechaSelec.toLocaleDateString('es-ES', opciones);
    }
    
    const tituloElement = document.getElementById('tituloEventosDia');
    const contadorElement = document.getElementById('contadorEventosDia');
    
    if (tituloElement) {
        tituloElement.textContent = `📅 ${tituloFecha}`;
    }
    if (contadorElement) {
        contadorElement.textContent = eventos.length;
    }
    
    const listaEventos = document.getElementById('listaEventosDia');
    if (!listaEventos) return;
    
    if (eventos.length === 0) {
        listaEventos.innerHTML = `
            <div class="sin-eventos">
                <div class="icono-grande">📅</div>
                <p>No hay eventos para este día</p>
                <small>Agenda una cita o crea un recordatorio</small>
            </div>
        `;
        return;
    }
    
    let html = '';
    eventos.forEach(evento => {
        let icono = '📝';
        let tipoTexto = 'Recordatorio';
        let nombreMascota = evento.nombre_mascota ? ` • ${evento.nombre_mascota}` : '';
        
        if (evento.tipo === 'recordatorio') {
            icono = '📝';
            tipoTexto = 'Recordatorio';
        } else if (evento.tipo === 'cita') {
            icono = '💊';
            tipoTexto = 'Cita Veterinaria';
        } else if (evento.tipo === 'evento') {
            icono = '🎉';
            tipoTexto = 'Evento Comunidad';
            nombreMascota = '';
        }
        
        const fechaEvento = new Date(evento.fecha);
        const hora = fechaEvento.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
        
        const ahora = new Date();
        const esVencido = fechaEvento < ahora;
        const claseVencido = esVencido ? 'evento-vencido' : '';
        
        const urlDestino = evento.tipo === 'cita' ? 'veterinaria.php' : 
                         evento.tipo === 'evento' ? 'comunidad.php' : 
                         evento.id_mascota ? `perfil-mascota.php?id=${evento.id_mascota}` : '#';
        
        const esClickeable = evento.tipo === 'cita' || evento.tipo === 'evento' || evento.id_mascota;
        
        html += `
            <div class="evento-hoy ${claseVencido}" ${esClickeable ? `onclick="window.location.href='${urlDestino}'" style="cursor: pointer;"` : ''}>
                <div class="icono-evento">${icono}</div>
                <div class="info-evento">
                    <div class="titulo-evento">${evento.titulo}${nombreMascota}</div>
                    <div class="detalles-evento">${tipoTexto} • ${hora}</div>
                </div>
                ${evento.tipo === 'recordatorio' ? `
                    <div class="acciones-evento" onclick="event.stopPropagation()">
                        <button class="btn-accion-evento btn-editar" onclick="event.stopPropagation(); editarEvento('${evento.tipo}', ${evento.id_evento})" title="Editar">✏️</button>
                        <button class="btn-accion-evento btn-eliminar" onclick="event.stopPropagation(); eliminarEvento('${evento.tipo}', ${evento.id_evento})" title="Eliminar">🗑️</button>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    listaEventos.innerHTML = html;
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
// ==========================================
// GESTIÓN DE MODALES
// ==========================================

function mostrarModalRecordatorio() {
    const modal = document.getElementById('modalRecordatorio');
    if (modal) {
        const form = modal.querySelector('form');
        form.reset();
        form.action = 'procesar-recordatorio.php';
        
        modal.querySelector('.modal-header h3').textContent = '📝 Nuevo Recordatorio';
        
        // Eliminar input hidden si existe
        const idRecordatorioInput = form.querySelector('input[name="id_recordatorio"]');
        if (idRecordatorioInput) {
            idRecordatorioInput.remove();
        }
        
        // Establecer fecha mínima de hoy
        const hoy = new Date().toISOString().split('T')[0];
        const inputFecha = form.querySelector('input[name="fecha"]');
        if (inputFecha) {
            inputFecha.setAttribute('min', hoy);
            inputFecha.value = hoy;
        }
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModalRecordatorio() {
    const modal = document.getElementById('modalRecordatorio');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ==========================================
// EDITAR Y ELIMINAR EVENTOS (INDEX) - VERSIÓN MEJORADA
// ==========================================

function editarEvento(tipo, id) {
    // Prevenir propagación del evento
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }

    if (tipo === 'recordatorio') {
        // Mostrar indicador de carga
        const btn = event.target;
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = '⏳';
        btn.disabled = true;

        fetch(`obtener-recordatorio.php?id=${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const modal = document.getElementById('modalRecordatorio');
                    if (!modal) {
                        throw new Error('Modal no encontrado');
                    }

                    const form = modal.querySelector('form');
                    
                    // Cambiar título del modal
                    modal.querySelector('.modal-header h3').textContent = '✏️ Editar Recordatorio';
                    
                    // Llenar formulario
                    form.querySelector('input[name="titulo"]').value = data.recordatorio.titulo;
                    const descTextarea = form.querySelector('textarea[name="descripcion"]');
                    if (descTextarea) {
                        descTextarea.value = data.recordatorio.descripcion || '';
                    }
                    
                    // Procesar fecha y hora
                    const fechaHora = new Date(data.recordatorio.fecha);
                    const fechaStr = fechaHora.toISOString().split('T')[0];
                    const horaStr = fechaHora.toTimeString().substring(0, 5);
                    
                    form.querySelector('input[name="fecha"]').value = fechaStr;
                    form.querySelector('input[name="hora"]').value = horaStr;
                    
                    // Seleccionar mascotas asociadas
                    const checkboxesMascotas = form.querySelectorAll('input[name="mascotas[]"]');
                    if (checkboxesMascotas.length > 0 && data.recordatorio.mascotas) {
                        checkboxesMascotas.forEach(checkbox => {
                            checkbox.checked = data.recordatorio.mascotas.includes(parseInt(checkbox.value));
                        });
                    }
                    
                    // Agregar o actualizar input hidden con ID
                    let idInput = form.querySelector('input[name="id_recordatorio"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id_recordatorio';
                        form.appendChild(idInput);
                    }
                    idInput.value = id;
                    
                    // Cambiar action del formulario
                    form.action = 'editar-recordatorio.php';
                    
                    // Mostrar modal
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    throw new Error(data.message || 'Error al cargar el recordatorio');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar el recordatorio: ' + error.message);
            })
            .finally(() => {
                // Restaurar botón
                if (btn) {
                    btn.innerHTML = textoOriginal;
                    btn.disabled = false;
                }
            });
    }
}

function eliminarEvento(tipo, id) {
    // Prevenir propagación del evento
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }

    const mensaje = tipo === 'recordatorio' ? 'recordatorio' : 'cita';
    const confirmacion = confirm(`¿Estás seguro de que deseas eliminar este ${mensaje}?\n\nEsta acción no se puede deshacer.`);
    
    if (confirmacion) {
        // Mostrar indicador de carga
        const btn = event.target;
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = '⏳';
        btn.disabled = true;
        
        window.location.href = `eliminar-evento.php?tipo=${tipo}&id=${id}`;
    }
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalRecordatorio();
    }
});

// Cerrar modal haciendo clic fuera
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modalRecordatorio');
    if (modal && e.target === modal) {
        cerrarModalRecordatorio();
    }
});

// Exportar funciones globalmente
window.mostrarModalRecordatorio = mostrarModalRecordatorio;
window.cerrarModalRecordatorio = cerrarModalRecordatorio;
window.editarEvento = editarEvento;
window.eliminarEvento = eliminarEvento;

console.log('Scripts de Huella Segura cargados completamente');