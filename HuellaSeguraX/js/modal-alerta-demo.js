// Funciones para el modal de alerta demo
function mostrarModalAlerta(mensaje, beneficios) {
    document.getElementById('mensajeAlertaDemo').textContent = mensaje;
    
    const listaBeneficios = document.getElementById('listaBeneficiosAlerta');
    listaBeneficios.innerHTML = '';
    
    beneficios.forEach(beneficio => {
        const li = document.createElement('li');
        li.textContent = '• ' + beneficio;
        listaBeneficios.appendChild(li);
    });
    
    document.getElementById('modalAlertaDemo').style.display = 'flex';
}

function cerrarModalAlerta() {
    document.getElementById('modalAlertaDemo').style.display = 'none';
}

function irALogin() {
    window.location.href = 'login.php';
}

function irARegistro() {
    window.location.href = 'registro.php';
}

// Cerrar modal con ESC o click fuera
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalAlerta();
    }
});

document.addEventListener('click', function(event) {
    const modal = document.getElementById('modalAlertaDemo');
    if (event.target === modal) {
        cerrarModalAlerta();
    }
});


// Funciones específicas para cada acción
function mostrarModalAgregarMascota() {
    mostrarModalAlerta('Para agregar mascotas necesitas una cuenta registrada.', [
        'Registrar tus mascotas', 
        'Llevar control de salud', 
        'Recibir recordatorios', 
        'Conectar con veterinarios'
    ]);
}

function mostrarModalReportarPerdida() {
    mostrarModalAlerta('Para reportar mascotas perdidas necesitas una cuenta.', [
        'Reportar mascotas perdidas', 
        'Ayudar a reunir familias', 
        'Recibir alertas de avistamientos', 
        'Conectar con la comunidad'
    ]);
}

function mostrarModalAdopcion() {
    mostrarModalAlerta('Para solicitar adopciones necesitas una cuenta registrada.', [
        'Solicitar adoptar mascotas', 
        'Contactar con dueños actuales', 
        'Completar proceso de adopción', 
        'Dar un hogar lleno de amor'
    ]);
}

function mostrarModalPublicarAdopcion() {
    mostrarModalAlerta('Para publicar mascotas en adopción necesitas una cuenta registrada.', [
        'Publicar mascotas en adopción', 
        'Encontrar hogares amorosos', 
        'Conectar con adoptantes', 
        'Gestionar solicitudes'
    ]);
}

function mostrarModalComunidad() {
    mostrarModalAlerta('Para participar en la comunidad necesitas una cuenta.', [
        'Compartir experiencias', 
        'Hacer preguntas', 
        'Conectar con otros dueños', 
        'Recibir consejos'
    ]);
}

function mostrarModalVeterinaria() {
    mostrarModalAlerta('Para acceder a servicios veterinarios necesitas una cuenta.', [
        'Agendar citas veterinarias', 
        'Llevar historial médico', 
        'Recibir recordatorios', 
        'Consultar especialistas'
    ]);
}

// Función original con validación mejorada
function mostrarModalAlerta(mensaje, beneficios) {
    if (!beneficios || !Array.isArray(beneficios)) {
        console.error('Error: beneficios debe ser un array', beneficios);
        beneficios = ['Acceder a funcionalidades completas', 'Guardar tu información', 'Conectar con la comunidad'];
    }
    
    document.getElementById('mensajeAlertaDemo').textContent = mensaje;
    
    const listaBeneficios = document.getElementById('listaBeneficiosAlerta');
    listaBeneficios.innerHTML = '';
    
    beneficios.forEach(beneficio => {
        const li = document.createElement('li');
        li.textContent = '• ' + beneficio;
        listaBeneficios.appendChild(li);
    });
    
    document.getElementById('modalAlertaDemo').style.display = 'flex';
    document.body.classList.add('modal-abierto');
}

function cerrarModalAlerta() {
    document.getElementById('modalAlertaDemo').style.display = 'none';
    document.body.classList.remove('modal-abierto');
}

function irALogin() {
    window.location.href = 'login.php';
}

function irARegistro() {
    window.location.href = 'registro.php';
}

// Cerrar modal con ESC o click fuera
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalAlerta();
    }
});

document.addEventListener('click', function(event) {
    const modal = document.getElementById('modalAlertaDemo');
    if (event.target === modal) {
        cerrarModalAlerta();
    }
});