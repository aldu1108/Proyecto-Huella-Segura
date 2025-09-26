// Variables globales
let adopcionSeleccionada = null;
let nombreMascotaSeleccionada = '';

// Modal solicitud de adopción
function mostrarSolicitudAdopcion(idAdopcion, nombreMascota) {
    adopcionSeleccionada = idAdopcion;
    nombreMascotaSeleccionada = nombreMascota;
    
    document.getElementById('idAdopcionSolicitud').value = idAdopcion;
    document.getElementById('nombreMascotaSolicitud').textContent = `Solicitar adopción de: ${nombreMascota}`;
    document.getElementById('modalSolicitud').style.display = 'flex';
}

function cerrarSolicitud() {
    document.getElementById('modalSolicitud').style.display = 'none';
    document.getElementById('formularioSolicitud').reset();
}

// Modal crear publicación
function mostrarFormularioPublicacion() {
    document.getElementById('modalCrearPublicacion').style.display = 'flex';
}

function cerrarPublicacion() {
    document.getElementById('modalCrearPublicacion').style.display = 'none';
    document.getElementById('formularioPublicacion').reset();
}

// Cerrar modales al hacer clic fuera
document.getElementById('modalSolicitud').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarSolicitud();
    }
});

document.getElementById('modalCrearPublicacion').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarPublicacion();
    }
});

// Funcionalidad de filtros
document.querySelectorAll('.filtro-adopcion').forEach(filtro => {
    filtro.addEventListener('click', function() {
        document.querySelectorAll('.filtro-adopcion').forEach(f => f.classList.remove('activo'));
        this.classList.add('activo');
        
        const tipoFiltro = this.textContent.trim();
        filtrarMascotas(tipoFiltro);
    });
});

function filtrarMascotas(tipo) {
    const tarjetas = document.querySelectorAll('.tarjeta-adopcion');
    
    tarjetas.forEach(tarjeta => {
        if (tipo === '❤️ Todos') {
            tarjeta.style.display = 'block';
        } else {
            const tipoMascota = tarjeta.querySelector('.detalles-basicos').textContent;
            let mostrar = false;
            
            if (tipo === '🐕 Perros' && tipoMascota.toLowerCase().includes('perro')) {
                mostrar = true;
            } else if (tipo === '🐱 Gatos' && tipoMascota.toLowerCase().includes('gato')) {
                mostrar = true;
            } else if (tipo === '⚙️ Otros' && !tipoMascota.toLowerCase().includes('perro') && !tipoMascota.toLowerCase().includes('gato')) {
                mostrar = true;
            }
            
            tarjeta.style.display = mostrar ? 'block' : 'none';
        }
    });
}

// Animaciones de entrada para las tarjetas
function animarTarjetas() {
    const tarjetas = document.querySelectorAll('.tarjeta-adopcion');
    tarjetas.forEach((tarjeta, index) => {
        tarjeta.style.animationDelay = `${index * 0.1}s`;
    });
}

// Cerrar modales con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarSolicitud();
        cerrarPublicacion();
    }
});

// Ejecutar animaciones al cargar
document.addEventListener('DOMContentLoaded', function() {
    animarTarjetas();
    
    // Manejar mensajes de la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('exito') || urlParams.get('error')) {
        // Limpiar URL después de mostrar el mensaje
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 5000);
    }
});

console.log('Sistema de adopciones cargado correctamente');

// Delegación: abrir modal de solicitud al hacer click en botones .boton-interesa-adoptar
document.addEventListener('DOMContentLoaded', function() {
    if (document.body.dataset.adopcionesInit === '1') return;
    document.body.dataset.adopcionesInit = '1';

    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest && e.target.closest('.boton-interesa-adoptar');
        if (!btn) return;

        // Si tiene un data-alert mostramos alerta (caso ejemplo)
        if (btn.dataset.alert) {
            alert(btn.dataset.alert);
            return;
        }

        const id = btn.dataset.idAdopcion || btn.getAttribute('data-id-adopcion');
        const nombre = btn.dataset.nombre || btn.getAttribute('data-nombre');

        if (id !== undefined && id !== null) {
            // Llamar a la función centralizada
            mostrarSolicitudAdopcion(parseInt(id, 10), nombre || 'la mascota');
        }
    });
});