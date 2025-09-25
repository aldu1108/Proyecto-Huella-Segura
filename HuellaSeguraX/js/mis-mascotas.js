document.addEventListener('DOMContentLoaded', function() {
    const btnAgregar = document.getElementById('btnAgregarMascota');
    const modal = document.getElementById('modalAgregarMascota');
    
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            modal.style.display = 'flex';
        });
    }
    
    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            cerrarModalMascota();
        }
    });
});

// Cerrar modal
function cerrarModalMascota() {
    const modal = document.getElementById('modalAgregarMascota');
    const formulario = document.getElementById('formularioMascota');
    const preview = document.getElementById('previewFotoMascota');
    
    modal.style.display = 'none';
    formulario.reset();
    preview.style.display = 'none';
}

// Preview de imagen
function previewImagen(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('previewFotoMascota');
            const img = preview.querySelector('img');
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalMascota();
    }
});