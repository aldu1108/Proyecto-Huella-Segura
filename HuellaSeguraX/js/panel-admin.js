function verUsuarios() {
    window.location.href = 'admin-usuarios.php';
}

function verVeterinarios() {
    window.location.href = 'admin-veterinarios.php';
}

function verReportes() {
    window.location.href = 'admin-reportes.php';
}

function configurarSistema() {
    mostrarMensaje('Configuración del sistema - Próximamente', 'info');
}

function verUsuario(email) {
    mostrarMensaje('Ver detalles de: ' + email, 'info');
}

function editarUsuario(email) {
    mostrarMensaje('Editar usuario: ' + email, 'info');
}

// Función para mensajes
function mostrarMensaje(texto, tipo) {
    const mensaje = document.createElement('div');
    mensaje.className = 'mensaje-admin mensaje-' + tipo;
    mensaje.innerHTML = `
        <span>${texto}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;padding:0 10px;font-size:18px;">×</button>
    `;
    
    const contenedor = document.querySelector('.contenedor-admin');
    contenedor.insertBefore(mensaje, contenedor.firstChild);
    
    setTimeout(() => {
        mensaje.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => mensaje.remove(), 300);
    }, 3000);
}

// Auto-cerrar mensajes existentes
document.addEventListener('DOMContentLoaded', function() {
    const mensajes = document.querySelectorAll('.mensaje-admin');
    mensajes.forEach(mensaje => {
        setTimeout(() => {
            if (mensaje.parentElement) {
                mensaje.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => mensaje.remove(), 300);
            }
        }, 5000);
    });
});