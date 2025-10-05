// perfil-mascota.js - Scripts para el perfil de mascota

// Función para mostrar modales
function mostrarModalEditar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModalEditar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.classList.remove('activo');
        document.body.style.overflow = 'auto';
    }
}

function mostrarModalPeso() {
    const modal = document.getElementById('modalPeso');
    if (modal) {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModalPeso() {
    const modal = document.getElementById('modalPeso');
    if (modal) {
        modal.classList.remove('activo');
        document.body.style.overflow = 'auto';
    }
}

function mostrarModalRecordatorio() {
    const modal = document.getElementById('modalRecordatorio');
    if (modal) {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModalRecordatorio() {
    const modal = document.getElementById('modalRecordatorio');
    if (modal) {
        modal.classList.remove('activo');
        document.body.style.overflow = 'auto';
    }
}

// Previsualizar foto antes de subir
function previsualizarFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('previewFoto');
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Event listener para el input de foto
document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('fotoMascota');
    if (inputFoto) {
        inputFoto.addEventListener('change', function() {
            previsualizarFoto(this);
        });
    }

    // Cerrar modales con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalPeso();
            cerrarModalEditar();
            cerrarModalRecordatorio();
        }
    });

    // Cerrar modales al hacer clic fuera
    const modales = document.querySelectorAll('.modal');
    modales.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('activo');
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Auto-ocultar mensajes después de 5 segundos
    const mensajes = document.querySelectorAll('.mensaje-exito, .mensaje-error');
    mensajes.forEach(mensaje => {
        setTimeout(() => {
            mensaje.style.transition = 'opacity 0.5s';
            mensaje.style.opacity = '0';
            setTimeout(() => mensaje.remove(), 500);
        }, 5000);
    });
});

let mesActual = new Date().getMonth();
let añoActual = new Date().getFullYear();

const meses = [
    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
];

function generarCalendario(mes, año) {
    const primerDia = new Date(año, mes, 1).getDay();
    const diasEnMes = new Date(año, mes + 1, 0).getDate();
    const hoy = new Date();
    const esHoy = (dia) => hoy.getDate() === dia && hoy.getMonth() === mes && hoy.getFullYear() === año;
    
    // Obtener días con eventos desde los datos de PHP
    const diasConEventos = window.mascotaData ? window.mascotaData.diasConEventos : [];
    
    let html = '';
    
    // Días vacíos del mes anterior
    for (let i = 0; i < primerDia; i++) {
        const diasMesAnterior = new Date(año, mes, 0).getDate();
        const dia = diasMesAnterior - primerDia + i + 1;
        html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
    }
    
    // Días del mes actual
    for (let dia = 1; dia <= diasEnMes; dia++) {
        let clases = 'dia-calendario';
        if (esHoy(dia)) clases += ' hoy';
        if (diasConEventos.includes(dia)) clases += ' evento';
        
        html += `<div class="${clases}" onclick="seleccionarDia(${dia})">${dia}</div>`;
    }
    
    // Días del próximo mes para completar la grilla
    const celdasTotales = 35; // 5 semanas
    const celdasUsadas = primerDia + diasEnMes;
    const diasProximoMes = celdasTotales - celdasUsadas;
    
    for (let dia = 1; dia <= diasProximoMes; dia++) {
        html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
    }
    
    const diasCalendario = document.getElementById('diasCalendario');
    if (diasCalendario) {
        diasCalendario.innerHTML = html;
    }
    
    const mesActualElement = document.getElementById('mesActual');
    if (mesActualElement) {
        mesActualElement.textContent = `${meses[mes]} de ${año}`;
    }
}

function cambiarMes(direccion) {
    mesActual += direccion;
    if (mesActual > 11) {
        mesActual = 0;
        añoActual++;
    } else if (mesActual < 0) {
        mesActual = 11;
        añoActual--;
    }
    generarCalendario(mesActual, añoActual);
}

function seleccionarDia(dia) {
    document.querySelectorAll('.dia-calendario.seleccionado').forEach(d => 
        d.classList.remove('seleccionado')
    );
    
    if (event && event.target) {
        event.target.classList.add('seleccionado');
    }
    
    console.log(`Día seleccionado: ${dia}/${mesActual + 1}/${añoActual}`);
}

// Exportar funciones globalmente
window.cambiarMes = cambiarMes;
window.seleccionarDia = seleccionarDia;

// Inicializar calendario cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poquito para asegurar que mascotaData esté disponible
    setTimeout(() => {
        generarCalendario(mesActual, añoActual);
        console.log('Calendario inicializado para mascota');
    }, 100);
});

// Funciones exportadas globalmente
window.mostrarModalEditar = mostrarModalEditar;
window.cerrarModalEditar = cerrarModalEditar;
window.mostrarModalPeso = mostrarModalPeso;
window.cerrarModalPeso = cerrarModalPeso;
window.mostrarModalRecordatorio = mostrarModalRecordatorio;
window.cerrarModalRecordatorio = cerrarModalRecordatorio;
window.previsualizarFoto = previsualizarFoto;

console.log('perfil-mascota.js cargado correctamente');