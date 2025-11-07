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
        if (strong && strong.textContent.includes('<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> Hora:')) {
            const now = new Date();
            const horas = String(now.getHours()).padStart(2, '0');
            const minutos = String(now.getMinutes()).padStart(2, '0');
            const segundos = String(now.getSeconds()).padStart(2, '0');
            // Reemplazar todo el contenido del item manteniendo el strong
            item.innerHTML = `<strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> Hora:</strong> ${horas}:${minutos}:${segundos}`;
        }
    });
}

// Función para confirmar eliminación
function confirmarEliminacion(nombre) {
    return confirm(`¿Estás seguro de que deseas eliminar al usuario ${nombre}? Esta acción no se puede deshacer.`);
}