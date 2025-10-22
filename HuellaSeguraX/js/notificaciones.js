// js/notificaciones.js - Sistema de notificaciones

let panelNotificacionesAbierto = false;
let notificacionesCargadas = false;

// Toggle del panel de notificaciones
function toggleNotificaciones() {
    const panel = document.getElementById('panelNotificaciones');
    const overlay = document.getElementById('overlayMenu');

    if (!panel) return;

    panelNotificacionesAbierto = !panelNotificacionesAbierto;

    if (panelNotificacionesAbierto) {
        panel.classList.add('activo');
        overlay.classList.add('activo');

        // Cargar notificaciones si no se han cargado
        if (!notificacionesCargadas) {
            cargarNotificaciones();
        }
    } else {
        panel.classList.remove('activo');
        overlay.classList.remove('activo');
    }
}

// Cargar notificaciones desde la API
async function cargarNotificaciones() {
    const listaNotificaciones = document.getElementById('listaNotificaciones');

    try {
        const response = await fetch('notificaciones.php?accion=obtener');
        const data = await response.json();

        if (data.success && data.notificaciones) {
            notificacionesCargadas = true;
            mostrarNotificaciones(data.notificaciones);
        } else {
            throw new Error('Error al cargar notificaciones');
        }
    } catch (error) {
        console.error('Error:', error);
        listaNotificaciones.innerHTML = `
            <div class="empty-notificaciones">
                <div class="empty-notificaciones-icon">⚠️</div>
                <div>Error al cargar notificaciones</div>
            </div>
        `;
    }
}

// Mostrar notificaciones en el panel
function mostrarNotificaciones(notificaciones) {
    const listaNotificaciones = document.getElementById('listaNotificaciones');

    if (!notificaciones || notificaciones.length === 0) {
        listaNotificaciones.innerHTML = `
            <div class="empty-notificaciones">
                <div class="empty-notificaciones-icon">🔔</div>
                <div>No tienes notificaciones</div>
            </div>
        `;
        return;
    }

    listaNotificaciones.innerHTML = notificaciones.map(notif => {
        const avatar = obtenerAvatarNotificacion(notif.tipo);
        const claseLectura = notif.leida == 0 ? 'no-leida' : '';

        return `
            <div class="notificacion-item ${claseLectura}" 
                 onclick="clickNotificacion(${notif.id_notificacion}, '${notif.url_relacionada || ''}')"
                 data-id="${notif.id_notificacion}">
                <div class="notificacion-avatar">${avatar}</div>
                <div class="notificacion-contenido">
                    <div class="notificacion-texto">${notif.mensaje}</div>
                    <div class="notificacion-tiempo">${notif.tiempo_transcurrido}</div>
                </div>
            </div>
        `;
    }).join('');
}

// Obtener avatar según tipo de notificación
function obtenerAvatarNotificacion(tipo) {
    const avatares = {
        'solicitud_adopcion': '❤️',
        'comentario_post': '💬',
        'like_post': '👍',
        'cita_aceptada': '✅',
        'cita_rechazada': '❌',
        'mascota_encontrada': '🎉',
        'mensaje': '📩'
    };
    return avatares[tipo] || '🔔';
}

// Click en una notificación
async function clickNotificacion(idNotificacion, url) {
    try {
        // Marcar como leída
        await fetch('notificaciones.php?accion=marcar_leida', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_notificacion=${idNotificacion}`
        });

        // Actualizar UI
        const item = document.querySelector(`[data-id="${idNotificacion}"]`);
        if (item) {
            item.classList.remove('no-leida');
        }

        actualizarContador();

        // Redirigir si hay URL
        if (url && url !== '' && url !== 'null') {
            window.location.href = url;
        }
    } catch (error) {
        console.error('Error al marcar notificación:', error);
    }
}

// Marcar todas como leídas
async function marcarTodasLeidas() {
    try {
        const response = await fetch('notificaciones.php?accion=marcar_todas_leidas', {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            // Actualizar UI
            document.querySelectorAll('.notificacion-item').forEach(item => {
                item.classList.remove('no-leida');
            });

            actualizarContador();
        }
    } catch (error) {
        console.error('Error al marcar todas como leídas:', error);
    }
}

// Actualizar contador de notificaciones
async function actualizarContador() {
    try {
        const response = await fetch('notificaciones.php?accion=contador');
        const data = await response.json();

        if (data.success) {
            const badge = document.querySelector('.badge-notificaciones');

            if (data.contador > 0) {
                if (badge) {
                    badge.textContent = data.contador;
                    badge.style.display = 'flex';
                } else {
                    // Crear badge si no existe
                    const botonNotif = document.querySelector('.boton-notificaciones');
                    if (botonNotif) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge-notificaciones';
                        newBadge.textContent = data.contador;
                        botonNotif.appendChild(newBadge);
                    }
                }
            } else {
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error al actualizar contador:', error);
    }
}

// Cerrar panel al hacer click fuera
document.addEventListener('click', function (e) {
    const panel = document.getElementById('panelNotificaciones');
    const botonNotif = document.querySelector('.boton-notificaciones');

    if (!panel || !botonNotif) return;

    if (panelNotificacionesAbierto &&
        !panel.contains(e.target) &&
        !botonNotif.contains(e.target)) {
        toggleNotificaciones();
    }
});

// Cerrar con tecla Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && panelNotificacionesAbierto) {
        toggleNotificaciones();
    }
});

// Actualizar contador periódicamente (cada 30 segundos)
setInterval(() => {
    if (!panelNotificacionesAbierto) {
        actualizarContador();
    }
}, 30000);

// Inicializar
document.addEventListener('DOMContentLoaded', function () {
    // Actualizar contador al cargar la página
    actualizarContador();
});

// Hacer funciones globales
window.toggleNotificaciones = toggleNotificaciones;
window.marcarTodasLeidas = marcarTodasLeidas;
window.clickNotificacion = clickNotificacion;

console.log('Sistema de notificaciones cargado correctamente');