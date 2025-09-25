// Auto-llenar campos para facilitar pruebas (solo en desarrollo)
document.addEventListener('DOMContentLoaded', function() {
    // Solo para desarrollo - remove en producción
    const isDevelopment = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    
    if (isDevelopment) {
        document.querySelector('input[name="email"]').value = 'admin@huellasegura.com';
        document.querySelector('input[name="contraseña"]').value = 'admin123';
    }
});