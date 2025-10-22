function verUsuarios() {
    mostrarMensaje('✅ Función de gestión de usuarios - Por implementar', 'info');
}

function verVeterinarios() {
    mostrarMensaje('✅ Función de verificación de veterinarios - Por implementar', 'info');
}

function verReportes() {
    mostrarMensaje('✅ Función de reportes del sistema - Por implementar', 'info');
}

function configurarSistema() {
    mostrarMensaje('✅ Función de configuración del sistema - Por implementar', 'info');
}

function verUsuario(email) {
    mostrarMensaje(`📋 Viendo usuario: ${email}`, 'info');
}

function editarUsuario(email) {
    mostrarMensaje(`✏️ Editando usuario: ${email}`, 'warning');
}

function aprobarVeterinario(email) {
    // Función removida - ahora se usa enlace directo
}

function rechazarVeterinario(email) {
    // Función removida - ahora se usa enlace directo
}

function mostrarMensaje(mensaje, tipo) {
    const div = document.createElement('div');
    div.className = `mensaje-admin mensaje-${tipo}`;
    div.innerHTML = `
        <span>${mensaje}</span>
        <button onclick="this.parentElement.remove()">✕</button>
    `;

    div.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${tipo === 'success' ? '#27ae60' : tipo === 'error' ? '#e74c3c' : tipo === 'warning' ? '#f39c12' : '#3498db'};
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    `;

    document.body.appendChild(div);
    setTimeout(() => div.remove(), 4000);
}