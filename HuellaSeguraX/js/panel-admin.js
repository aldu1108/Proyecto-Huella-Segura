// Funciones para gestionar usuarios
function verUsuario(email) {
    window.location.href = `ver-usuario-admin.php?email=${encodeURIComponent(email)}`;
}

function editarUsuario(email) {
    window.location.href = `editar-usuario-admin.php?email=${encodeURIComponent(email)}`;
}

// Funciones para acciones rápidas
function verUsuarios() {
    window.location.href = 'gestionar-usuarios-admin.php';
}

function verVeterinarios() {
    window.location.href = 'gestionar-veterinarios.php';
}

function verReportes() {
    window.location.href = 'ver-reportes.php';
}

function configurarSistema() {
    window.location.href = 'configuracion-sistema.php';
}

// Función para auto-cerrar mensajes después de 5 segundos
document.addEventListener('DOMContentLoaded', function () {
    const mensajes = document.querySelectorAll('.mensaje-admin');
    if (mensajes.length > 0) {
        setTimeout(() => {
            mensajes.forEach(mensaje => {
                mensaje.style.opacity = '0';
                setTimeout(() => mensaje.remove(), 300);
            });
        }, 5000);
    }

    // Actualizar reloj en tiempo real
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
});

// Función para actualizar el reloj
function actualizarReloj() {
    const infoItems = document.querySelectorAll('.info-item');
    infoItems.forEach(item => {
        const strong = item.querySelector('strong');
        if (strong && strong.textContent.includes('⏰ Hora:')) {
            const now = new Date();
            const horas = String(now.getHours()).padStart(2, '0');
            const minutos = String(now.getMinutes()).padStart(2, '0');
            const segundos = String(now.getSeconds()).padStart(2, '0');
            // Reemplazar todo el contenido del item manteniendo el strong
            item.innerHTML = `<strong>⏰ Hora:</strong> ${horas}:${minutos}:${segundos}`;
        }
    });
}

// Función para confirmar eliminación
function confirmarEliminacion(nombre) {
    return confirm(`¿Estás seguro de que deseas eliminar al usuario ${nombre}? Esta acción no se puede deshacer.`);
}