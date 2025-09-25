// Funcionalidad del calendario
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
    
    // Días con eventos (ejemplo - podrías conectar esto con la base de datos)
    const diasConEventos = [15, 16, 21];
    
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
    const celdasTotales = 42; // 6 semanas × 7 días
    const celdasUsadas = primerDia + diasEnMes;
    const diasProximoMes = celdasTotales - celdasUsadas;
    
    for (let dia = 1; dia <= diasProximoMes; dia++) {
        html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
    }
    
    document.getElementById('diasCalendario').innerHTML = html;
    document.getElementById('mesActual').textContent = `${meses[mes]} de ${año}`;
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
    // Remover selección previa
    document.querySelectorAll('.dia-calendario.seleccionado').forEach(d => 
        d.classList.remove('seleccionado')
    );
    
    // Agregar selección al día clickeado
    event.target.classList.add('seleccionado');
    
    // Aquí podrías cargar eventos específicos del día
    console.log(`Día seleccionado: ${dia}/${mesActual + 1}/${añoActual}`);
}

// Inicializar calendario
document.addEventListener('DOMContentLoaded', function() {
    generarCalendario(mesActual, añoActual);
    
    // Funcionalidad de búsqueda
    const inputBusqueda = document.querySelector('.input-busqueda-principal');
    let timeoutBusqueda;
    
    inputBusqueda.addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);
        const termino = this.value.trim();
        
        timeoutBusqueda = setTimeout(() => {
            if (termino.length > 2) {
                realizarBusqueda(termino);
            }
        }, 300);
    });
});

function realizarBusqueda(termino) {
    console.log('Buscando:', termino);
    // Aquí implementarías la búsqueda real
    // Por ahora solo mostramos un mensaje
    if (termino.toLowerCase().includes('veterinario')) {
        window.location.href = 'veterinaria.php';
    } else if (termino.toLowerCase().includes('perdida')) {
        window.location.href = 'mascotas-perdidas.php';
    } else if (termino.toLowerCase().includes('adopcion')) {
        window.location.href = 'adopciones.php';
    }
}

// Animaciones de entrada para las tarjetas
function animarTarjetas() {
    const tarjetas = document.querySelectorAll('.mascota-card-principal, .perdida-item-index');
    tarjetas.forEach((tarjeta, index) => {
        tarjeta.style.opacity = '0';
        tarjeta.style.transform = 'translateY(20px)';
        tarjeta.style.transition = `all 0.3s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            tarjeta.style.opacity = '1';
            tarjeta.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Ejecutar animaciones cuando se carga la página
document.addEventListener('DOMContentLoaded', animarTarjetas);