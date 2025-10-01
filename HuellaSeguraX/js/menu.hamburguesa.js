document.addEventListener('DOMContentLoaded', function() {
    const menuHamburguesa = document.getElementById('menuHamburguesa');
    const menuLateral = document.getElementById('menuLateral');
    const overlayMenu = document.getElementById('overlayMenu');
    
    function abrirMenu() {
        menuLateral.classList.add('activo');
        overlayMenu.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }
    
    function cerrarMenu() {
        menuLateral.classList.remove('activo');
        overlayMenu.classList.remove('activo');
        document.body.style.overflow = '';
    }
    
    // Abrir menú al hacer clic en hamburguesa
    if (menuHamburguesa) {
        menuHamburguesa.addEventListener('click', abrirMenu);
    }
    
    // Cerrar menú al hacer clic en overlay
    if (overlayMenu) {
        overlayMenu.addEventListener('click', cerrarMenu);
    }
    
    // Cerrar menú al presionar Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarMenu();
        }
    });
    
    // Cerrar menú al hacer clic en cualquier enlace
    const enlacesMenu = document.querySelectorAll('.opcion-menu');
    enlacesMenu.forEach(enlace => {
        enlace.addEventListener('click', cerrarMenu);
    });
});

// Función placeholder para notificaciones
function toggleNotificaciones() {
    alert('Función de notificaciones en desarrollo');
}