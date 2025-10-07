// Prevenir que scripts.js sobrescriba las funciones del perfil
(function() {
    'use strict';

// ==========================================
// GESTIÓN DE MODALES
// ==========================================

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
        const form = modal.querySelector('form');
        form.reset();
        form.action = 'procesar-peso.php';
        
        modal.querySelector('.modal-header h3').textContent = '📊 Registrar Peso';
        
        const idPesoInput = form.querySelector('input[name="id_peso"]');
        if (idPesoInput) {
            idPesoInput.remove();
        }
        
        modal.querySelector('input[name="fecha"]').value = new Date().toISOString().split('T')[0];
        
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
        const form = modal.querySelector('form');
        form.reset();
        form.action = 'procesar-recordatorio.php';
        
        modal.querySelector('.modal-header h3').textContent = '📝 Nuevo Recordatorio';
        
        const idRecordatorioInput = form.querySelector('input[name="id_recordatorio"]');
        if (idRecordatorioInput) {
            idRecordatorioInput.remove();
        }
        
        // Permitir fecha de HOY en adelante
        const hoy = new Date().toISOString().split('T')[0];
        modal.querySelector('input[name="fecha"]').setAttribute('min', hoy);
        
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

// ==========================================
// PREVISUALIZACIÓN DE FOTO
// ==========================================

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

// ==========================================
// CALENDARIO
// ==========================================

let mesActual = new Date().getMonth();
let añoActual = new Date().getFullYear();

const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

function generarCalendario(mes, año) {
    const primerDia = new Date(año, mes, 1).getDay();
    const diasEnMes = new Date(año, mes + 1, 0).getDate();
    const hoy = new Date();
    const esHoy = (dia) => hoy.getDate() === dia && hoy.getMonth() === mes && hoy.getFullYear() === año;
    
    const eventosMes = window.mascotaData.eventosMes || [];
    const diasConEventos = [];
    
    eventosMes.forEach(evento => {
        const fechaEvento = new Date(evento.fecha);
        if (fechaEvento.getMonth() === mes && fechaEvento.getFullYear() === año) {
            const dia = fechaEvento.getDate();
            if (!diasConEventos.includes(dia)) {
                diasConEventos.push(dia);
            }
        }
    });
    
    let html = '';
    
    for (let i = 0; i < primerDia; i++) {
        const diasMesAnterior = new Date(año, mes, 0).getDate();
        const dia = diasMesAnterior - primerDia + i + 1;
        html += `<div class="dia-calendario" style="opacity: 0.3;">${dia}</div>`;
    }
    
    for (let dia = 1; dia <= diasEnMes; dia++) {
        let clases = 'dia-calendario';
        if (esHoy(dia)) clases += ' hoy';
        if (diasConEventos.includes(dia)) clases += ' evento';
        
        html += `<div class="${clases}" onclick="seleccionarDia(${dia})">${dia}</div>`;
    }
    
    const celdasTotales = 35;
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
        mesActualElement.textContent = `${meses[mes]} ${año}`;
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
    
    const fechaSeleccionada = `${añoActual}-${String(mesActual + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    mostrarEventosDia(fechaSeleccionada, dia);
}

function mostrarEventosDia(fecha, dia) {
    const eventos = window.mascotaData.eventosMes.filter(e => e.fecha.startsWith(fecha));
    
    const hoy = new Date();
    const [año, mes, diaStr] = fecha.split('-');
    const fechaSelec = new Date(parseInt(año), parseInt(mes) - 1, parseInt(diaStr));
    
    let tituloFecha = '';
    
    if (fechaSelec.getDate() === hoy.getDate() && 
        fechaSelec.getMonth() === hoy.getMonth() && 
        fechaSelec.getFullYear() === hoy.getFullYear()) {
        tituloFecha = 'Hoy';
    } else {
        const opciones = { day: 'numeric', month: 'long' };
        tituloFecha = fechaSelec.toLocaleDateString('es-ES', opciones);
    }
    
    document.getElementById('tituloEventosDia').textContent = `📅 ${tituloFecha}`;
    document.getElementById('contadorEventosDia').textContent = eventos.length;
    
    const listaEventos = document.getElementById('listaEventosDia');
    
    if (eventos.length === 0) {
        listaEventos.innerHTML = `
            <div class="sin-eventos">
                <div class="icono-grande">📅</div>
                <p>No hay eventos para este día</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    eventos.forEach(evento => {
        let icono = '📝';
        let tipoTexto = 'Recordatorio';
        
        if (evento.tipo === 'recordatorio') {
            icono = '📝';
            tipoTexto = 'Recordatorio';
        } else if (evento.tipo === 'cita') {
            icono = '💊';
            tipoTexto = 'Cita Veterinaria';
        } else if (evento.tipo === 'evento') {
            icono = '🎉';
            tipoTexto = 'Evento Comunidad';
        }
        
        // Obtener hora del evento
        const fechaEvento = new Date(evento.fecha);
        const hora = fechaEvento.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
        
        // Verificar si está vencido
        const ahora = new Date();
        const esVencido = fechaEvento < ahora;
        const claseVencido = esVencido ? 'evento-vencido' : '';
        
        html += `
            <div class="evento-hoy ${claseVencido}" ${evento.tipo === 'cita' ? 'onclick="window.location.href=\'veterinaria.php\'" style="cursor: pointer;"' : ''}>
                <div class="icono-evento">${icono}</div>
                <div class="info-evento">
                    <div class="titulo-evento">${evento.titulo}</div>
                    <div class="detalles-evento">${tipoTexto} • ${hora}</div>
                </div>
                ${evento.tipo === 'recordatorio' ? `
                    <div class="acciones-evento" onclick="event.stopPropagation()">
                        <button class="btn-accion-evento btn-editar" onclick="event.stopPropagation(); editarEvento('${evento.tipo}', ${evento.id_evento})" title="Editar">✏️</button>
                        <button class="btn-accion-evento btn-eliminar" onclick="event.stopPropagation(); eliminarEvento('${evento.tipo}', ${evento.id_evento})" title="Eliminar">🗑️</button>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    listaEventos.innerHTML = html;
}

// ==========================================
// EDITAR Y ELIMINAR EVENTOS
// ==========================================

function editarEvento(tipo, id) {
    event.stopPropagation();

    if (tipo === 'recordatorio') {
        fetch(`obtener-recordatorio.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = document.getElementById('modalRecordatorio');
                    const form = modal.querySelector('form');
                    
                    modal.querySelector('.modal-header h3').textContent = '✏️ Editar Recordatorio';
                    
                    form.querySelector('input[name="titulo"]').value = data.recordatorio.titulo;
                    form.querySelector('textarea[name="descripcion"]').value = data.recordatorio.descripcion || '';
                    
                    const fechaHora = new Date(data.recordatorio.fecha);
                    const fechaStr = fechaHora.toISOString().split('T')[0];
                    const horaStr = fechaHora.toTimeString().substring(0, 5);
                    
                    form.querySelector('input[name="fecha"]').value = fechaStr;
                    form.querySelector('input[name="hora"]').value = horaStr;
                    
                    // Agregar input hidden con ID
                    let idInput = form.querySelector('input[name="id_recordatorio"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id_recordatorio';
                        form.appendChild(idInput);
                    }
                    idInput.value = id;
                    
                    form.action = 'editar-recordatorio.php';
                    
                    modal.classList.add('activo');
                    document.body.style.overflow = 'hidden';
                } else {
                    alert('Error al cargar el recordatorio');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar el recordatorio');
            });
    } else {
        window.location.href = `editar-cita.php?id=${id}&mascota=${window.mascotaData.mascotaId}`;
    }
}

function eliminarEvento(tipo, id) {
    event.stopPropagation();

    const mensaje = tipo === 'recordatorio' ? 'recordatorio' : 'cita';
    const confirmacion = confirm(`¿Estás seguro de que deseas eliminar este ${mensaje}?`);
    
    if (confirmacion) {
        window.location.href = `eliminar-evento.php?tipo=${tipo}&id=${id}&mascota=${window.mascotaData.mascotaId}`;
    }
}

// ==========================================
// EDITAR Y ELIMINAR PESO
// ==========================================

function editarPeso(id, peso, fecha) {
    event.stopPropagation();
    
    const modal = document.getElementById('modalPeso');
    const form = modal.querySelector('form');
    
    modal.querySelector('.modal-header h3').textContent = '✏️ Editar Peso';
    
    form.querySelector('input[name="peso"]').value = peso;
    form.querySelector('input[name="fecha"]').value = fecha;
    
    let idPesoInput = form.querySelector('input[name="id_peso"]');
    if (!idPesoInput) {
        idPesoInput = document.createElement('input');
        idPesoInput.type = 'hidden';
        idPesoInput.name = 'id_peso';
        form.appendChild(idPesoInput);
    }
    idPesoInput.value = id;
    
    form.action = 'editar-peso.php';
    
    modal.classList.add('activo');
    document.body.style.overflow = 'hidden';
}

function eliminarPeso(id) {
    event.stopPropagation();
    
    if (confirm('¿Eliminar este registro de peso?')) {
        window.location.href = `eliminar-peso.php?id=${id}&mascota=${window.mascotaData.mascotaId}`;
    }
}

// ==========================================
// GESTIÓN DE TOOLTIPS
// ==========================================

function gestionarTooltips() {
    const puntos = document.querySelectorAll('.punto-dato');
    
    puntos.forEach(punto => {
        const tooltip = punto.querySelector('.tooltip-peso');
        
        punto.addEventListener('mouseenter', function() {
            tooltip.style.opacity = '1';
            tooltip.style.visibility = 'visible';
        });
        
        punto.addEventListener('mouseleave', function(e) {
            // Solo cerrar si el cursor no está sobre el tooltip
            setTimeout(() => {
                if (!tooltip.matches(':hover')) {
                    tooltip.style.opacity = '0';
                    tooltip.style.visibility = 'hidden';
                }
            }, 100);
        });
        
        tooltip.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
            tooltip.style.visibility = 'hidden';
        });
    });
}

// ==========================================
// INICIALIZACIÓN
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('fotoMascota');
    if (inputFoto) {
        inputFoto.addEventListener('change', function() {
            previsualizarFoto(this);
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalPeso();
            cerrarModalEditar();
            cerrarModalRecordatorio();
        }
    });

    const modales = document.querySelectorAll('.modal');
    modales.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('activo');
                document.body.style.overflow = 'auto';
            }
        });
    });

    const mensajes = document.querySelectorAll('.mensaje-exito, .mensaje-error');
    mensajes.forEach(mensaje => {
        setTimeout(() => {
            mensaje.style.transition = 'opacity 0.5s';
            mensaje.style.opacity = '0';
            setTimeout(() => mensaje.remove(), 500);
        }, 5000);
    });

    setTimeout(() => {
        generarCalendario(mesActual, añoActual);
        gestionarTooltips();
    }, 100);
});

if (typeof window.perfilMascotaPage === 'undefined') {
    window.perfilMascotaPage = true;
    
    window.mostrarModalEditar = mostrarModalEditar;
    window.cerrarModalEditar = cerrarModalEditar;
    window.mostrarModalPeso = mostrarModalPeso;
    window.cerrarModalPeso = cerrarModalPeso;
    window.mostrarModalRecordatorio = mostrarModalRecordatorio;
    window.cerrarModalRecordatorio = cerrarModalRecordatorio;
    window.previsualizarFoto = previsualizarFoto;
    window.cambiarMes = cambiarMes;
    window.seleccionarDia = seleccionarDia;
    window.editarEvento = editarEvento;
    window.eliminarEvento = eliminarEvento;
    window.editarPeso = editarPeso;
    window.eliminarPeso = eliminarPeso;
}
})();