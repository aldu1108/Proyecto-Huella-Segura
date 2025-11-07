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

    // Configurar fecha mínima para nueva cita: solo fechas desde hoy en adelante
    const fechaCita = document.querySelector('#formularioCita [name="fecha"]');
    if (fechaCita) {
        const hoy = new Date();
        const fechaMinima = hoy.toISOString().split('T')[0];
        fechaCita.min = fechaMinima;
    }

    // Para nueva consulta: sin restricción de fechas (puede ser pasada o futura)
    const fechaConsulta = document.querySelector('#formularioConsulta [name="fecha_consulta"]');
    if (fechaConsulta) {
        fechaConsulta.removeAttribute('max');
    }

    // Script para redirección automática a sección
    const urlParams = new URLSearchParams(window.location.search);
    const seccion = urlParams.get('seccion');
    
    if (seccion) {
        const botonSeccion = document.querySelector(`[data-seccion="${seccion}"]`);
        if (botonSeccion) {
            setTimeout(() => botonSeccion.click(), 100);
        }
    }
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
    
    // No establecer restricciones de fecha para consultas (pueden ser pasadas o futuras)
    const inputFecha = document.querySelector('input[name="fecha_consulta"]');
    if (inputFecha) {
        const hoy = new Date();
        const fechaHoy = hoy.toISOString().split('T')[0];
        inputFecha.value = fechaHoy;
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
    let valido = true;
    
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#e74c3c';
            valido = false;
        } else {
            campo.style.borderColor = '#E8F4FD';
        }
    });
    
    if (!valido) {
        showMessage('Por favor completa todos los campos requeridos', 'error');
        return;
    }
    
    // VALIDACIÓN: Fecha no puede ser pasada
    const fecha = form.querySelector('[name="fecha"]').value;
    const fechaHoy = new Date();
    fechaHoy.setHours(0, 0, 0, 0); // Resetear horas para comparar solo la fecha
    
    const fechaSeleccionada = new Date(fecha + 'T00:00:00');
    
    if (fechaSeleccionada < fechaHoy) {
        showMessage('No puedes agendar citas para fechas pasadas. Selecciona hoy o una fecha futura.', 'error');
        form.querySelector('[name="fecha"]').style.borderColor = '#e74c3c';
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
document.addEventListener('DOMContentLoaded', function() {
    const modalCita = document.getElementById('modalNuevaCita');
    const modalConsulta = document.getElementById('modalNuevaConsulta');
    
    if (modalCita) {
        modalCita.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalCita();
            }
        });
    }
    
    if (modalConsulta) {
        modalConsulta.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalConsulta();
            }
        });
    }
});

// ========================================
// FUNCIONES PARA PACIENTES (VETERINARIO)
// ========================================

function verHistorialPaciente(idMascota) {
    // Cambiar a la sección historial
    const btnHistorial = document.querySelector('[data-seccion="historial"]');
    if (btnHistorial) {
        btnHistorial.click();
    }
    
    // Filtrar por mascota después de un breve delay
    setTimeout(() => {
        // Filtrar los registros médicos por mascota
        const registros = document.querySelectorAll('.registro-medico');
        let registrosVisibles = 0;
        
        registros.forEach(registro => {
            if (registro.dataset.mascota === idMascota.toString()) {
                registro.style.display = 'block';
                registrosVisibles++;
            } else {
                registro.style.display = 'none';
            }
        });
        
        // Actualizar el select del filtro para que coincida
        const mascotaSelect = document.querySelector('#seccionHistorial .filtro-mascota');
        if (mascotaSelect) {
            // Si el select filtra por dueño (veterinario), obtener el dueño de la mascota
            const primeraOpcion = mascotaSelect.querySelector('option:not([value=""])');
            if (primeraOpcion && primeraOpcion.textContent.includes('@')) {
                // Es un select de usuarios, buscar el dueño de esta mascota
                const tarjetaPaciente = document.querySelector(`.tarjeta-paciente [onclick*="verHistorialPaciente(${idMascota})"]`);
                if (tarjetaPaciente) {
                    const tarjeta = tarjetaPaciente.closest('.tarjeta-paciente');
                    const idDueno = tarjeta?.dataset.dueno;
                    if (idDueno) {
                        mascotaSelect.value = idDueno;
                    }
                }
            }
        }
        
        // Obtener nombre de la mascota para el mensaje
        const tarjetaPaciente = document.querySelector(`.tarjeta-paciente [onclick*="verHistorialPaciente(${idMascota})"]`);
        let nombreMascota = 'el paciente';
        if (tarjetaPaciente) {
            const tarjeta = tarjetaPaciente.closest('.tarjeta-paciente');
            const nombreElement = tarjeta?.querySelector('.detalles-cita h5');
            if (nombreElement) {
                nombreMascota = nombreElement.textContent;
            }
        }
        
        if (registrosVisibles > 0) {
            showMessage(`📋 Mostrando ${registrosVisibles} registro(s) médico(s) de ${nombreMascota}`, 'success');
        } else {
            showMessage(`ℹ️ ${nombreMascota} no tiene registros médicos aún`, 'info');
        }
    }, 300);
}

function agendarCitaPaciente(idMascota) {
    // Mostrar modal especial para veterinario
    mostrarModalCitaPaciente(idMascota);
}

// Modal específico para agendar cita a paciente (veterinario)
function mostrarModalCitaPaciente(idMascota) {
    // Crear modal si no existe
    let modal = document.getElementById('modalCitaPaciente');
    
    if (!modal) {
        modal = crearModalCitaPaciente();
        document.body.appendChild(modal);
    }
    
    // Mostrar modal
    modal.style.display = 'flex';
    
    // Asignar id de mascota al campo hidden
    const inputIdMascota = document.getElementById('inputIdMascotaPaciente');
    if (inputIdMascota) {
        inputIdMascota.value = idMascota;
    }
    
    // Configurar fecha mínima (hoy)
    const inputFecha = document.querySelector('#formularioCitaPaciente [name="fecha"]');
    if (inputFecha) {
        const hoy = new Date();
        const fechaMin = hoy.toISOString().split('T')[0];
        inputFecha.min = fechaMin;
        inputFecha.value = fechaMin;
    }
    
    // Obtener nombre de la mascota para mostrarlo
    obtenerNombreMascota(idMascota);
}

function crearModalCitaPaciente() {
    const modal = document.createElement('div');
    modal.id = 'modalCitaPaciente';
    modal.className = 'modal-nueva-cita';
    
    modal.innerHTML = `
        <div class="contenido-modal-cita">
            <div class="encabezado-modal-cita">
                <h3 class="titulo-modal-cita">📅 Agendar Cita para Paciente</h3>
                <button class="boton-cerrar-modal-cita" onclick="cerrarModalCitaPaciente()">×</button>
            </div>
            
            <form class="formulario-cita" id="formularioCitaPaciente" method="POST" action="agendar-cita-paciente.php">
                <input type="hidden" name="id_mascota" id="inputIdMascotaPaciente">
                
                <div class="info-paciente-seleccionado">
                    <p>🐾 <strong>Paciente:</strong> <span id="nombreMascotaSeleccionada">Cargando...</span></p>
                </div>
                
                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita requerido">Motivo de la Cita</label>
                    <select class="select-cita" name="motivo" required>
                        <option value="">Seleccionar motivo</option>
                        <option value="Consulta General">Consulta General</option>
                        <option value="Vacunación">Vacunación</option>
                        <option value="Revisión">Revisión</option>
                        <option value="Urgencia">Urgencia</option>
                        <option value="Control">Control</option>
                        <option value="Cirugía">Cirugía</option>
                        <option value="Análisis">Análisis</option>
                        <option value="Desparasitación">Desparasitación</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita requerido">Fecha</label>
                    <input type="date" class="input-cita" name="fecha" required>
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita requerido">Hora</label>
                    <select class="select-cita" name="hora" required>
                        <option value="">Seleccionar hora</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="17:00">05:00 PM</option>
                        <option value="18:00">06:00 PM</option>
                    </select>
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita">Clínica</label>
                    <input type="text" class="input-cita" name="clinica" placeholder="Nombre de la clínica">
                </div>

                <div class="grupo-input-cita">
                    <label class="etiqueta-input-cita">Observaciones</label>
                    <textarea class="textarea-cita" name="observaciones" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </form>

            <div class="botones-modal-cita">
                <button type="button" class="boton-cancelar-cita" onclick="cerrarModalCitaPaciente()">Cancelar</button>
                <button type="button" class="boton-agendar-cita" onclick="guardarCitaPaciente()">Agendar Cita</button>
            </div>
        </div>
    `;
    
    return modal;
}

function cerrarModalCitaPaciente() {
    const modal = document.getElementById('modalCitaPaciente');
    if (modal) {
        modal.style.display = 'none';
        const form = document.getElementById('formularioCitaPaciente');
        if (form) {
            form.reset();
        }
    }
}

function guardarCitaPaciente() {
    const form = document.getElementById('formularioCitaPaciente');
    
    // Validar campos requeridos
    const camposRequeridos = form.querySelectorAll('[required]');
    let valido = true;
    
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#e74c3c';
            valido = false;
        } else {
            campo.style.borderColor = '#E8F4FD';
        }
    });
    
    if (!valido) {
        showMessage('Por favor completa todos los campos requeridos', 'error');
        return;
    }
    
    // VALIDACIÓN: Fecha no puede ser pasada
    const fecha = form.querySelector('[name="fecha"]').value;
    const fechaHoy = new Date();
    fechaHoy.setHours(0, 0, 0, 0);
    
    const fechaSeleccionada = new Date(fecha + 'T00:00:00');
    
    if (fechaSeleccionada < fechaHoy) {
        showMessage('No puedes agendar citas para fechas pasadas. Selecciona hoy o una fecha futura.', 'error');
        form.querySelector('[name="fecha"]').style.borderColor = '#e74c3c';
        return;
    }
    
    // Enviar formulario
    form.submit();
}

function obtenerNombreMascota(idMascota) {
    // Buscar el nombre de la mascota en las tarjetas de pacientes
    const todasLasTarjetas = document.querySelectorAll('.tarjeta-paciente');
    
    todasLasTarjetas.forEach(tarjeta => {
        // Verificar si esta tarjeta tiene un botón con el id correcto
        const botonCita = tarjeta.querySelector(`button[onclick*="agendarCitaPaciente(${idMascota})"]`);
        if (botonCita) {
            const nombreMascota = tarjeta.querySelector('.detalles-cita h5');
            if (nombreMascota) {
                const spanNombre = document.getElementById('nombreMascotaSeleccionada');
                if (spanNombre) {
                    spanNombre.textContent = nombreMascota.textContent;
                }
            }
        }
    });
}

// Cerrar modal de cita paciente al hacer clic fuera
document.addEventListener('click', function(e) {
    const modalCitaPaciente = document.getElementById('modalCitaPaciente');
    if (modalCitaPaciente && e.target === modalCitaPaciente) {
        cerrarModalCitaPaciente();
    }
});

// ========================================
// FIN FUNCIONES PARA PACIENTES
// ========================================

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
    
    // Actualizar el select en la sección historial
    const mascotaSelect = document.querySelector('#seccionHistorial .filtro-mascota');
    if (mascotaSelect && idMascota) {
        mascotaSelect.value = idMascota;
        
        const nombreMascota = mascotaSelect.options[mascotaSelect.selectedIndex]?.text || 'esta mascota';
        showMessage(`Mostrando ${registrosVisibles} registros de ${nombreMascota}`, 'info');
    } else if (!idMascota) {
        showMessage(`Mostrando todos los registros (${registrosVisibles} total)`, 'info');
    }
}

function filtrarHistorialPorDueno(idDueno) {
    // Filtrar registros médicos por dueño (para veterinarios)
    const registros = document.querySelectorAll('.registro-medico');
    let registrosVisibles = 0;
    
    registros.forEach(registro => {
        if (!idDueno || registro.dataset.dueno === idDueno) {
            registro.style.display = 'block';
            registrosVisibles++;
        } else {
            registro.style.display = 'none';
        }
    });
    
    // Actualizar el select en la sección historial
    const duenoSelect = document.querySelector('#seccionHistorial .filtro-mascota');
    if (duenoSelect && idDueno) {
        duenoSelect.value = idDueno;
        
        const nombreDueno = duenoSelect.options[duenoSelect.selectedIndex]?.text || 'este dueño';
        showMessage(`Mostrando ${registrosVisibles} registro(s) de ${nombreDueno}`, 'info');
    } else if (!idDueno) {
        showMessage(`Mostrando todos los registros (${registrosVisibles} total)`, 'info');
    }
}

function filtrarAgenda(idMascota) {
    // Filtrar tarjetas de citas en la sección Mi Agenda (para usuarios normales)
    const citas = document.querySelectorAll('#seccionAgenda .tarjeta-cita, #seccionAgenda .tarjeta-cita-pendiente');
    let citasVisibles = 0;
    
    citas.forEach(cita => {
        if (!idMascota || cita.dataset.mascota === idMascota) {
            cita.style.display = 'block';
            citasVisibles++;
        } else {
            cita.style.display = 'none';
        }
    });
    
    // Actualizar el select en la sección agenda
    const mascotaSelect = document.querySelector('#seccionAgenda .filtro-mascota');
    if (mascotaSelect && idMascota) {
        mascotaSelect.value = idMascota;
        
        const nombreMascota = mascotaSelect.options[mascotaSelect.selectedIndex]?.text || 'esta mascota';
        showMessage(`Mostrando ${citasVisibles} cita(s) de ${nombreMascota}`, 'info');
    } else if (!idMascota) {
        showMessage(`Mostrando todas las citas (${citasVisibles} total)`, 'info');
    }
}

function filtrarAgendaPorDueno(idDueno) {
    // Filtrar tarjetas de citas por dueño (para veterinarios)
    const citas = document.querySelectorAll('#seccionAgenda .tarjeta-cita, #seccionAgenda .tarjeta-cita-pendiente');
    let citasVisibles = 0;
    
    citas.forEach(cita => {
        if (!idDueno || cita.dataset.dueno === idDueno) {
            cita.style.display = 'block';
            citasVisibles++;
        } else {
            cita.style.display = 'none';
        }
    });
    
    // Actualizar el select en la sección agenda
    const duenoSelect = document.querySelector('#seccionAgenda .filtro-mascota');
    if (duenoSelect && idDueno) {
        duenoSelect.value = idDueno;
        
        const nombreDueno = duenoSelect.options[duenoSelect.selectedIndex]?.text || 'este dueño';
        showMessage(`Mostrando ${citasVisibles} cita(s) de ${nombreDueno}`, 'info');
    } else if (!idDueno) {
        showMessage(`Mostrando todas las citas (${citasVisibles} total)`, 'info');
    }
}

function filtrarPacientesPorDueno(idDueno) {
    const tarjetasPacientes = document.querySelectorAll('#seccionPacientes .tarjeta-paciente');
    let pacientesVisibles = 0;
    
    tarjetasPacientes.forEach(tarjeta => {
        if (!idDueno || tarjeta.dataset.dueno === idDueno) {
            tarjeta.style.display = 'flex';
            pacientesVisibles++;
        } else {
            tarjeta.style.display = 'none';
        }
    });
    
    // Actualizar el select
    const duenoSelect = document.querySelector('#seccionPacientes .filtro-mascota');
    if (duenoSelect && idDueno) {
        duenoSelect.value = idDueno;
        
        const nombreDueno = duenoSelect.options[duenoSelect.selectedIndex]?.text || 'este dueño';
        showMessage(`Mostrando ${pacientesVisibles} paciente(s) de ${nombreDueno}`, 'info');
    } else if (!idDueno) {
        showMessage(`Mostrando todos los pacientes (${pacientesVisibles} total)`, 'info');
    }
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
    
    /* Estilos para modal de cita paciente */
    .info-paciente-seleccionado {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .info-paciente-seleccionado p {
        margin: 0;
        font-size: 15px;
        font-weight: 500;
    }

    .info-paciente-seleccionado strong {
        font-weight: 600;
    }

    #nombreMascotaSeleccionada {
        font-weight: 700;
        text-decoration: underline;
    }

    #modalCitaPaciente {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    #modalCitaPaciente .contenido-modal-cita {
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
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

        .info-paciente-seleccionado {
            padding: 12px 16px;
            font-size: 14px;
        }

        .info-paciente-seleccionado p {
            font-size: 13px;
        }
    }
`;
document.head.appendChild(estilosAnimaciones);

console.log('Veterinaria.js funcional cargado correctamente');

// Función adicional para crear consulta desde cita pasada
function crearConsultaDesdeCita(idMascota, fecha) {
    registrarNuevaConsulta();
    
    // Pre-seleccionar la mascota y fecha
    setTimeout(() => {
        const selectMascota = document.querySelector('#formularioConsulta [name="id_mascota"]');
        const inputFecha = document.querySelector('#formularioConsulta [name="fecha_consulta"]');
        
        if (selectMascota) {
            selectMascota.value = idMascota;
        }
        if (inputFecha) {
            inputFecha.value = fecha;
        }
    }, 100);
}

// Función para mostrar modal de confirmación de eliminación de consulta
function confirmarEliminarConsulta(idHistorial, nombreMascota) {
    document.getElementById('idHistorialEliminar').value = idHistorial;
    document.getElementById('mascotaEliminar').textContent = nombreMascota;
    document.getElementById('modalConfirmarEliminar').style.display = 'flex';
}

// Función para cerrar modal de eliminación de consulta
function cerrarModalEliminar() {
    document.getElementById('modalConfirmarEliminar').style.display = 'none';
}

// Función para eliminar consulta
function eliminarConsulta() {
    const form = document.getElementById('formularioEliminarConsulta');
    form.submit();
}

// Función para mostrar modal de confirmación de eliminación de cita
function confirmarEliminarCita(idCita, nombreMascota, motivo) {
    document.getElementById('idCitaEliminar').value = idCita;
    document.getElementById('mascotaEliminarCita').textContent = nombreMascota;
    document.getElementById('motivoEliminarCita').textContent = motivo;
    document.getElementById('modalConfirmarEliminarCita').style.display = 'flex';
}

// Función para cerrar modal de eliminación de cita
function cerrarModalEliminarCita() {
    document.getElementById('modalConfirmarEliminarCita').style.display = 'none';
}

// Función para eliminar cita
function eliminarCita() {
    const form = document.getElementById('formularioEliminarCita');
    form.submit();
}

// Cerrar modales con click fuera o ESC
document.addEventListener('click', function(event) {
    const modalEliminar = document.getElementById('modalConfirmarEliminar');
    const modalEliminarCita = document.getElementById('modalConfirmarEliminarCita');
    
    if (modalEliminar && event.target === modalEliminar) {
        cerrarModalEliminar();
    }
    if (modalEliminarCita && event.target === modalEliminarCita) {
        cerrarModalEliminarCita();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalEliminar();
        cerrarModalEliminarCita();
        cerrarModalCita();
        cerrarModalConsulta();
        cerrarModalCitaPaciente();
    }
});

// ========================================
// FUNCIONES MODAL AGREGAR PACIENTE
// ========================================

function mostrarModalAgregarPaciente() {
    const modal = document.getElementById('modalAgregarPaciente');
    if (modal) {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
        
        // Asegurar que los campos de nuevo dueño estén visibles por defecto
        const camposNuevoDueno = document.getElementById('camposNuevoDueno');
        if (camposNuevoDueno) {
            camposNuevoDueno.style.display = 'block';
        }
    }
}

function cerrarModalAgregarPaciente() {
    const modal = document.getElementById('modalAgregarPaciente');
    if (modal) {
        modal.classList.remove('activo');
        document.body.style.overflow = 'auto';
        
        // Limpiar formulario
        const formulario = document.getElementById('formularioPaciente');
        if (formulario) {
            formulario.reset();
        }
        
        // Limpiar preview
        eliminarPreview();
        
        // Reset selector de dueño
        const selectDueno = document.getElementById('selectDuenoExistente');
        if (selectDueno) {
            selectDueno.value = '';
        }
        
        // Mostrar campos de nuevo dueño
        const camposNuevoDueno = document.getElementById('camposNuevoDueno');
        if (camposNuevoDueno) {
            camposNuevoDueno.style.display = 'block';
        }
    }
}

function toggleNuevoDueno() {
    const select = document.getElementById('selectDuenoExistente');
    const camposNuevoDueno = document.getElementById('camposNuevoDueno');
    
    // Campos del nuevo dueño
    const inputNombre = document.getElementById('inputNombreDueno');
    const inputApellido = document.getElementById('inputApellidoDueno');
    const inputEmail = document.getElementById('inputEmailDueno');
    const inputPassword = document.getElementById('inputPasswordDueno');
    
    if (select.value === '') {
        // Mostrar campos de nuevo dueño
        camposNuevoDueno.style.display = 'block';
        
        // Hacer campos requeridos
        inputNombre.required = true;
        inputApellido.required = true;
        inputEmail.required = true;
        inputPassword.required = true;
    } else {
        // Ocultar campos de nuevo dueño
        camposNuevoDueno.style.display = 'none';
        
        // Quitar requerimiento
        inputNombre.required = false;
        inputApellido.required = false;
        inputEmail.required = false;
        inputPassword.required = false;
        
        // Limpiar valores
        inputNombre.value = '';
        inputApellido.value = '';
        inputEmail.value = '';
        inputPassword.value = '';
    }
}

function previewImagenPaciente(input) {
    const preview = document.getElementById('previewFotoPaciente');
    const img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

function eliminarPreview() {
    const preview = document.getElementById('previewFotoPaciente');
    const input = document.getElementById('inputFotoPaciente');
    
    if (preview) {
        preview.style.display = 'none';
        const img = preview.querySelector('img');
        if (img) {
            img.src = '';
        }
    }
    
    if (input) {
        input.value = '';
    }
}

function mostrarCamposNuevoDueno() {
    const select = document.getElementById('selectDuenoExistente');
    if (select) {
        select.value = '';
        toggleNuevoDueno();
    }
}

// Cerrar modal agregar paciente al hacer clic fuera
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modalAgregarPaciente');
    if (modal && e.target === modal) {
        cerrarModalAgregarPaciente();
    }
});

// Cerrar modal agregar paciente con tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modalAgregarPaciente');
        if (modal && modal.classList.contains('activo')) {
            cerrarModalAgregarPaciente();
        }
    }
});

function mostrarSubirDocumento(tipoDocumento = '') {
    const modal = document.getElementById('modalSubirDocumento');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Si se especifica un tipo, preseleccionarlo
        if (tipoDocumento) {
            const selectTipo = document.getElementById('tipoDocumento');
            if (selectTipo) {
                selectTipo.value = tipoDocumento;
            }
        }
        
        // Establecer fecha de hoy por defecto
        const inputFecha = document.getElementById('fechaDocumento');
        if (inputFecha) {
            const hoy = new Date();
            inputFecha.value = hoy.toISOString().split('T')[0];
        }
    }
}

function cerrarModalSubirDocumento() {
    const modal = document.getElementById('modalSubirDocumento');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Limpiar formulario
        const formulario = document.getElementById('formularioSubirDocumento');
        if (formulario) {
            formulario.reset();
        }
        
        // Limpiar preview
        eliminarPreviewDocumento();
    }
}

function previewArchivoDocumento(input) {
    const preview = document.getElementById('previewArchivoDocumento');
    const nombreArchivo = preview.querySelector('.nombre-archivo');
    
    if (input.files && input.files[0]) {
        const archivo = input.files[0];
        const tamanoMB = (archivo.size / 1024 / 1024).toFixed(2);
        
        // Validar tamaño
        if (archivo.size > 10000000) {
            showMessage('El archivo es demasiado grande. Máximo 10MB', 'error');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Validar extensión
        const extension = archivo.name.split('.').pop().toLowerCase();
        const extensionesPermitidas = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        
        if (!extensionesPermitidas.includes(extension)) {
            showMessage('Formato de archivo no permitido', 'error');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Mostrar preview
        nombreArchivo.textContent = `${archivo.name} (${tamanoMB} MB)`;
        preview.style.display = 'flex';
        
        // Cambiar texto del label
        const textoFile = input.parentElement.querySelector('.texto-file');
        if (textoFile) {
            textoFile.textContent = 'Cambiar archivo';
        }
    } else {
        preview.style.display = 'none';
        const textoFile = input.parentElement.querySelector('.texto-file');
        if (textoFile) {
            textoFile.textContent = 'Seleccionar archivo';
        }
    }
}

function eliminarPreviewDocumento() {
    const preview = document.getElementById('previewArchivoDocumento');
    const input = document.getElementById('inputArchivoDocumento');
    
    if (preview) {
        preview.style.display = 'none';
        const nombreArchivo = preview.querySelector('.nombre-archivo');
        if (nombreArchivo) {
            nombreArchivo.textContent = '';
        }
    }
    
    if (input) {
        input.value = '';
        const textoFile = input.parentElement.querySelector('.texto-file');
        if (textoFile) {
            textoFile.textContent = 'Seleccionar archivo';
        }
    }
}

// Manejar el envío del formulario de subir documento
document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('formularioSubirDocumento');
    
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar campos
            const tipoDocumento = document.getElementById('tipoDocumento').value;
            const mascota = document.getElementById('mascotaDocumento').value;
            const titulo = document.getElementById('tituloDocumento').value.trim();
            const fecha = document.getElementById('fechaDocumento').value;
            const archivo = document.getElementById('inputArchivoDocumento').files[0];
            
            if (!tipoDocumento || !mascota || !titulo || !fecha || !archivo) {
                showMessage('Por favor completa todos los campos obligatorios', 'error');
                return;
            }
            
            // Mostrar loading
            const btnSubmit = document.getElementById('btnSubirDocumento');
            const textoBoton = btnSubmit.querySelector('.texto-boton');
            const spinnerBoton = btnSubmit.querySelector('.spinner-boton');
            
            btnSubmit.disabled = true;
            textoBoton.style.display = 'none';
            spinnerBoton.style.display = 'inline';
            
            // Crear FormData
            const formData = new FormData(formulario);
            
            // Enviar con fetch
            fetch('ajax/subir_documento.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    cerrarModalSubirDocumento();
                    
                    // Recargar la página después de 1.5 segundos
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage(data.message || 'Error al subir el documento', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error al procesar la solicitud', 'error');
            })
            .finally(() => {
                // Restaurar botón
                btnSubmit.disabled = false;
                textoBoton.style.display = 'inline';
                spinnerBoton.style.display = 'none';
            });
        });
    }
});
function confirmarEliminarDocumento(idDocumento, idHistorial, archivo, nombreMascota) {
    document.getElementById('idDocumentoEliminar').value = idDocumento;
    document.getElementById('idHistorialEliminar').value = idHistorial;
    document.getElementById('archivoEliminar').value = archivo;
    document.getElementById('mascotaEliminarDoc').textContent = nombreMascota;
    document.getElementById('archivoEliminarDoc').textContent = archivo;
    document.getElementById('modalConfirmarEliminarDocumento').style.display = 'flex';
}

function cerrarModalEliminarDocumento() {
    document.getElementById('modalConfirmarEliminarDocumento').style.display = 'none';
}

function eliminarDocumento() {
    const idDocumento = document.getElementById('idDocumentoEliminar').value;
    const idHistorial = document.getElementById('idHistorialEliminar').value;
    const archivo = document.getElementById('archivoEliminar').value;
    
    // Crear FormData
    const formData = new FormData();
    formData.append('id_documento', idDocumento);
    formData.append('id_historial', idHistorial);
    formData.append('archivo', archivo);
    
    // Mostrar loading
    const btnEliminar = document.querySelector('.boton-confirmar-eliminar');
    const textoOriginal = btnEliminar.textContent;
    btnEliminar.disabled = true;
    btnEliminar.textContent = 'Eliminando...';
    
    // Enviar petición
    fetch('ajax/eliminar_documento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Respuesta del servidor:', text);
        
        try {
            const data = JSON.parse(text);
            
            if (data.success) {
                showMessage(data.message, 'success');
                cerrarModalEliminarDocumento();
                
                // Eliminar el elemento del DOM con animación
                const documentoItem = document.querySelector(`[data-documento-id="${idDocumento}"]`);
                if (documentoItem) {
                    documentoItem.style.transition = 'all 0.3s ease';
                    documentoItem.style.opacity = '0';
                    documentoItem.style.transform = 'translateX(-20px)';
                    
                    setTimeout(() => {
                        documentoItem.remove();
                        
                        // Verificar si no quedan documentos en la categoría
                        const categoria = documentoItem.closest('.categoria-doc');
                        const listaDocumentos = categoria.querySelector('.lista-documentos');
                        const documentosRestantes = listaDocumentos.querySelectorAll('.documento-item');
                        
                        if (documentosRestantes.length === 0) {
                            // Mostrar mensaje de "sin documentos"
                            listaDocumentos.innerHTML = `
                                <div class="sin-citas" style="padding: 20px;">
                                    <p>No hay documentos en esta categoría</p>
                                </div>
                            `;
                        }
                    }, 300);
                }
            } else {
                showMessage(data.message || 'Error al eliminar el documento', 'error');
            }
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            console.error('Texto recibido:', text);
            showMessage('Error en la respuesta del servidor', 'error');
        }
    })
    .catch(error => {
        console.error('Error de red:', error);
        showMessage('Error de conexión con el servidor', 'error');
    })
    .finally(() => {
        // Restaurar botón
        btnEliminar.disabled = false;
        btnEliminar.textContent = textoOriginal;
    });
}

// Cerrar modal con ESC o click fuera
document.addEventListener('click', function(event) {
    const modal = document.getElementById('modalConfirmarEliminarDocumento');
    if (modal && event.target === modal) {
        cerrarModalEliminarDocumento();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalEliminarDocumento();
    }
});

function verDocumento(archivo) {
    window.open('ver_documento.php?archivo=' + encodeURIComponent(archivo), '_blank');
}

function descargarDocumento(archivo) {
    window.location.href = 'ver_documento.php?archivo=' + encodeURIComponent(archivo) + '&descargar=1';
    showMessage('Descargando documento...', 'info');
}