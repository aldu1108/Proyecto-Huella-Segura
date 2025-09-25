
// Auto-hide mensajes después de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const mensajeExito = document.getElementById('mensajeExito');
        const mensajeError = document.getElementById('mensajeError');
        
        if (mensajeExito) {
            mensajeExito.style.opacity = '0';
            setTimeout(() => mensajeExito.remove(), 300);
        }
        if (mensajeError) {
            mensajeError.style.opacity = '0';
            setTimeout(() => mensajeError.remove(), 300);
        }
    }, 5000);
});

// Navegación entre secciones
document.addEventListener('DOMContentLoaded', function() {
    const botonesSeccion = document.querySelectorAll('.boton-seccion-vet');
    const secciones = document.querySelectorAll('.seccion-veterinaria');

    botonesSeccion.forEach(boton => {
        boton.addEventListener('click', function() {
            const seccionTarget = this.dataset.seccion;
            
            // Remover clase activa de todos los botones
            botonesSeccion.forEach(btn => btn.classList.remove('activo'));
            this.classList.add('activo');
            
            // Ocultar todas las secciones
            secciones.forEach(seccion => seccion.classList.remove('activa'));
            
            // Mostrar sección seleccionada
            const seccionActiva = document.getElementById('seccion' + seccionTarget.charAt(0).toUpperCase() + seccionTarget.slice(1));
            if (seccionActiva) {
                seccionActiva.classList.add('activa');
            }
        });
    });
});

// Funciones para los botones de acción
function verAgendaCompleta() {
    showMessage('Mostrando todas las citas programadas...', 'info');
    
    // Cambiar a la sección agenda
    const btnAgenda = document.querySelector('[data-seccion="agenda"]');
    if (btnAgenda) {
        btnAgenda.click();
    }
}

function verAgendaDelDia() {
    showMessage('Mostrando citas de hoy...', 'info');
    
    const citasHoy = document.querySelectorAll('.tarjeta-cita.hoy');
    if (citasHoy.length > 0) {
        showMessage(`Tienes ${citasHoy.length} cita(s) programada(s) para hoy`, 'success');
    } else {
        showMessage('No tienes citas programadas para hoy', 'info');
    }
}

function registrarNuevaConsulta() {
    document.getElementById('modalNuevaConsulta').style.display = 'flex';
    
    // Establecer fecha de hoy como máximo
    const inputFecha = document.querySelector('input[name="fecha_consulta"]');
    if (inputFecha) {
        const hoy = new Date();
        const fechaMax = hoy.toISOString().split('T')[0];
        inputFecha.max = fechaMax;
        inputFecha.value = fechaMax;
    }
}

// Modal para nueva cita
function mostrarFormularioCita() {
    document.getElementById('modalNuevaCita').style.display = 'flex';
    
    const inputFecha = document.querySelector('input[name="fecha"]');
    if (inputFecha) {
        const hoy = new Date();
        const fechaMin = hoy.toISOString().split('T')[0];
        inputFecha.min = fechaMin;
        inputFecha.value = fechaMin;
    }
}

function cerrarModalCita() {
    document.getElementById('modalNuevaCita').style.display = 'none';
    document.getElementById('formularioCita').reset();
}

function guardarCita() {
    const form = document.getElementById('formularioCita');
    
    // Validar campos requeridos
    const camposRequeridos = form.querySelectorAll('[required]');
    let formValido = true;
    
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#E74C3C';
            formValido = false;
        } else {
            campo.style.borderColor = '#27AE60';
        }
    });
    
    if (!formValido) {
        showMessage('Por favor completa todos los campos requeridos', 'error');
        return;
    }
    
    // Enviar formulario
    form.submit();
}

function cerrarModalConsulta() {
    document.getElementById('modalNuevaConsulta').style.display = 'none';
    document.getElementById('formularioConsulta').reset();
}

function guardarConsulta() {
    const form = document.getElementById('formularioConsulta');
    
    // Validar campos requeridos
    const camposRequeridos = form.querySelectorAll('[required]');
    let formValido = true;
    
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#E74C3C';
            formValido = false;
        } else {
            campo.style.borderColor = '#27AE60';
        }
    });
    
    if (!formValido) {
        showMessage('Por favor completa todos los campos requeridos', 'error');
        return;
    }
    
    // Enviar formulario
    form.submit();
}

// Cerrar modales al hacer clic fuera
document.getElementById('modalNuevaCita').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalCita();
    }
});

document.getElementById('modalNuevaConsulta').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalConsulta();
    }
});

// Funciones para pacientes
function verHistorialPaciente(idMascota) {
    showMessage('Cargando historial médico...', 'info');
    
    // Cambiar a la sección historial
    const btnHistorial = document.querySelector('[data-seccion="historial"]');
    if (btnHistorial) {
        btnHistorial.click();
    }
    
    // Filtrar por mascota después de un breve delay
    setTimeout(() => {
        filtrarHistorial(idMascota);
    }, 300);
}

function agendarCitaPaciente(idMascota) {
    mostrarFormularioCita();
    
    // Preseleccionar la mascota
    setTimeout(() => {
        const selectMascota = document.querySelector('select[name="id_mascota"]');
        if (selectMascota) {
            selectMascota.value = idMascota;
        }
    }, 100);
}

function filtrarHistorial(idMascota) {
    const registros = document.querySelectorAll('.registro-medico');
    let registrosVisibles = 0;
    
    registros.forEach(registro => {
        if (!idMascota || registro.dataset.mascota === idMascota) {
            registro.style.display = 'block';
            registrosVisibles++;
        } else {
            registro.style.display = 'none';
        }
    });
    
    // Actualizar el select
    const mascotaSelect = document.querySelector('.filtro-mascota');
    if (mascotaSelect && idMascota) {
        mascotaSelect.value = idMascota;
        
        const nombreMascota = mascotaSelect.options[mascotaSelect.selectedIndex]?.text || 'esta mascota';
        showMessage(`Mostrando ${registrosVisibles} registros de ${nombreMascota}`, 'info');
    } else if (!idMascota) {
        showMessage(`Mostrando todos los registros (${registrosVisibles} total)`, 'info');
    }
}

function mostrarSubirDocumento() {
    showMessage('Función de subir documento en desarrollo. Pronto podrás subir archivos médicos.', 'info');
}

function verDocumento(archivo) {
    showMessage(`Intentando abrir documento: ${archivo}`, 'info');
    // Aquí podrías implementar la lógica para abrir el documento
    // window.open('documentos/' + archivo, '_blank');
}

function showMessage(mensaje, tipo = 'info', duracion = 4000) {
    // Remover mensajes existentes
    const mensajesExistentes = document.querySelectorAll('.mensaje-veterinaria');
    mensajesExistentes.forEach(m => m.remove());
    
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `mensaje-veterinaria mensaje-${tipo}`;
    
    const iconos = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    mensajeDiv.innerHTML = `
        <span class="icono-mensaje">${iconos[tipo] || iconos.info}</span>
        <span class="texto-mensaje">${mensaje}</span>
        <button class="boton-cerrar-mensaje" onclick="this.parentElement.remove()">×</button>
    `;
    
    // Estilos del mensaje
    Object.assign(mensajeDiv.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: getColorMensaje(tipo),
        color: 'white',
        padding: '16px 20px',
        borderRadius: '12px',
        boxShadow: '0 4px 16px rgba(0,0,0,0.2)',
        zIndex: '10000',
        maxWidth: '400px',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        animation: 'slideInRight 0.3s ease',
        fontSize: '14px',
        fontWeight: '500'
    });
    
    // Estilo del botón cerrar
    const botonCerrar = mensajeDiv.querySelector('.boton-cerrar-mensaje');
    Object.assign(botonCerrar.style, {
        background: 'rgba(255,255,255,0.2)',
        border: 'none',
        color: 'white',
        padding: '4px 8px',
        borderRadius: '50%',
        cursor: 'pointer',
        fontSize: '12px'
    });
    
    document.body.appendChild(mensajeDiv);
    
    // Auto-remover después de la duración especificada
    setTimeout(() => {
        if (mensajeDiv.parentElement) {
            mensajeDiv.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => mensajeDiv.remove(), 300);
        }
    }, duracion);
}

function getColorMensaje(tipo) {
    const colores = {
        success: '#27ae60',
        error: '#e74c3c',
        warning: '#f39c12',
        info: '#3498db'
    };
    return colores[tipo] || colores.info;
}

// Agregar estilos para las animaciones
const estilosAnimaciones = document.createElement('style');
estilosAnimaciones.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .mensaje-veterinaria .boton-cerrar-mensaje:hover {
        background: rgba(255,255,255,0.3) !important;
    }
    
    .mensaje-exito, .mensaje-error {
        transition: opacity 0.3s ease;
    }
    
    @media (max-width: 768px) {
        .acciones-paciente {
            flex-direction: row !important;
            gap: 6px !important;
        }
        
        .boton-ver-historial, .boton-nueva-cita-paciente {
            font-size: 10px !important;
            padding: 6px 8px !important;
        }
        
        .foto-paciente, .placeholder-paciente {
            width: 50px !important;
            height: 50px !important;
            margin-right: 12px !important;
        }
        
        .tarjeta-paciente {
            padding: 12px !important;
        }
        
        .info-cita {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 8px !important;
        }
        
        .mensaje-veterinaria {
            max-width: 300px !important;
            right: 10px !important;
            top: 10px !important;
            font-size: 13px !important;
        }
        
        .filtros-historial {
            flex-direction: column !important;
            gap: 8px !important;
        }
        
        .encabezado-historial {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 16px !important;
        }
        
        .encabezado-documentos {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 16px !important;
        }
    }
`;
document.head.appendChild(estilosAnimaciones);

console.log('Veterinaria.php funcional cargado correctamente');
