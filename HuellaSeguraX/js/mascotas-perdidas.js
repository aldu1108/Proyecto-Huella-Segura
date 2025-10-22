let pasoActual = 1;
const totalPasos = 3;
let datosReporte = {};
let datosModalPerdidaActual = {};

function mostrarFormularioReporte() {
    document.getElementById('modalReporte').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    pasoActual = 1;
    mostrarPaso(1);
}

function cerrarFormularioReporte() {
    document.getElementById('modalReporte').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('formularioReporte').reset();
    document.getElementById('campoRecompensa').style.display = 'none';
    pasoActual = 1;
    mostrarPaso(1);
    datosReporte = {};
}

function mostrarPaso(numeroPaso) {
    // Ocultar todos los pasos
    for (let i = 1; i <= totalPasos; i++) {
        const paso = document.getElementById('paso' + i);
        if (paso) {
            paso.style.display = 'none';
        }
    }

    // Mostrar paso actual
    const pasoActual = document.getElementById('paso' + numeroPaso);
    if (pasoActual) {
        pasoActual.style.display = 'block';
    }

    // Actualizar progreso
    const pasos = document.querySelectorAll('.paso-progreso');
    pasos.forEach((paso, index) => {
        if (index < numeroPaso) {
            paso.classList.add('activo');
        }
    });
}

function siguientePaso(siguientePasoNum) {
    // Si estamos en modo edición, no validar (el submit normal se encarga)
    if (window.modoEdicion) {
        return true;
    }

    if (validarPasoActual()) {
        guardarDatosPaso();
        pasoActual = siguientePasoNum;
        mostrarPaso(siguientePasoNum);

        if (siguientePasoNum === 3) {
            generarResumen();
        }
    }
}

function anteriorPaso(anteriorPasoNum) {
    pasoActual = anteriorPasoNum;
    mostrarPaso(anteriorPasoNum);
}

function validarPasoActual() {
    // Si estamos en modo edición, no validar
    if (window.modoEdicion) {
        return true;
    }

    const pasoDiv = document.getElementById('paso' + pasoActual);

    // Solo validar campos que estén visibles (display !== 'none')
    const camposRequeridos = Array.from(pasoDiv.querySelectorAll('input[required], select[required]'))
        .filter(campo => {
            // Verificar que el campo y sus padres estén visibles
            let elemento = campo;
            while (elemento && elemento !== pasoDiv) {
                if (window.getComputedStyle(elemento).display === 'none') {
                    return false;
                }
                elemento = elemento.parentElement;
            }
            return true;
        });

    let esValido = true;
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#E74C3C';
            esValido = false;
        } else {
            campo.style.borderColor = '#e8e8e8';
        }
    });

    if (!esValido) {
        alert('Por favor completa todos los campos requeridos');
    }

    return esValido;
}

function guardarDatosPaso() {
    const pasoDiv = document.getElementById('paso' + pasoActual);
    const inputs = pasoDiv.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        if (input.name) {
            if (input.type === 'checkbox') {
                datosReporte[input.name] = input.checked;
            } else {
                datosReporte[input.name] = input.value;
            }
        }
    });
}

function generarResumen() {
    const selectMascota = document.querySelector('select[name="id_mascota"]');
    const nombreMascota = selectMascota.options[selectMascota.selectedIndex]?.text || 'Mascota seleccionada';

    let resumen = `
        <div class="resumen-reporte">
            <div class="item-resumen">
                <strong>🐕 Mascota:</strong> ${nombreMascota}
            </div>
            <div class="item-resumen">
                <strong>📅 Fecha perdida:</strong> ${datosReporte.fecha_perdida ? new Date(datosReporte.fecha_perdida).toLocaleDateString('es-ES') : 'No especificada'}
            </div>
            ${datosReporte.hora_perdida ? `
                <div class="item-resumen">
                    <strong>⏰ Hora:</strong> ${datosReporte.hora_perdida}
                </div>
            ` : ''}
            <div class="item-resumen">
                <strong>📍 Ubicación:</strong> ${datosReporte.ultima_ubicacion || 'No especificada'}
            </div>
            ${datosReporte.descripcion ? `
                <div class="item-resumen">
                    <strong>📝 Descripción:</strong> ${datosReporte.descripcion}
                </div>
            ` : ''}
            ${datosReporte.ofrecer_recompensa ? `
                <div class="item-resumen destacado">
                    <strong>💰 Recompensa:</strong> €${datosReporte.recompensa || '0'}
                </div>
            ` : ''}
        </div>
    `;

    document.getElementById('resumenReporte').innerHTML = resumen;
}

function toggleRecompensa() {
    const checkbox = document.getElementById('checkboxRecompensa');
    const campo = document.getElementById('campoRecompensa');
    campo.style.display = checkbox.checked ? 'block' : 'none';
}

function obtenerUbicacion() {
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización');
        return;
    }

    const botonGps = document.querySelector('.boton-gps');
    const inputUbicacion = document.querySelector('input[name="ultima_ubicacion"]');

    botonGps.innerHTML = '⏳';

    navigator.geolocation.getCurrentPosition(
        function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            inputUbicacion.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            botonGps.innerHTML = '✅';

            setTimeout(() => {
                botonGps.innerHTML = 'GPS';
            }, 2000);
        },
        function (error) {
            let mensaje = 'No se pudo obtener la ubicación';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    mensaje = 'Permisos de ubicación denegados';
                    break;
                case error.POSITION_UNAVAILABLE:
                    mensaje = 'Ubicación no disponible';
                    break;
            }
            alert(mensaje);
            botonGps.innerHTML = 'GPS';
        }
    );
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalReporte').addEventListener('click', function (e) {
    if (e.target === this) {
        cerrarFormularioReporte();
    }
});

// Funciones para los botones de acción
function contactarPropietario(nombre, telefono) {
    if (telefono && telefono !== 'null' && telefono !== '') {
        if (confirm(`¿Deseas contactar al propietario de ${nombre}?\nTeléfono: ${telefono}`)) {
            window.location.href = `tel:${telefono}`;
        }
    } else {
        alert('Información de contacto no disponible');
    }
}

function compartirReporte(nombre) {
    if (navigator.share) {
        navigator.share({
            title: `Mascota perdida: ${nombre}`,
            text: `Ayuda a encontrar a ${nombre}. Mascota perdida en la zona.`,
            url: window.location.href
        });
    } else {
        const url = window.location.href;
        const texto = `Ayuda a encontrar a ${nombre}. Mascota perdida: ${url}`;

        if (navigator.clipboard) {
            navigator.clipboard.writeText(texto).then(() => {
                alert('Enlace copiado al portapapeles. Compártelo en redes sociales.');
            });
        } else {
            prompt('Copia este enlace para compartir:', texto);
        }
    }
}

function verDetallesReporte(idReporte) {
    alert(`Ver detalles del reporte ID: ${idReporte}`);
}

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarFormularioReporte();
    }
});

// Variable global para detectar modo edición
let modoEdicionGlobal = false;

// ==================== FUNCIONES CON MODALES SOFISTICADOS ====================

// Marcar mascota como encontrada (con modal sofisticado)
function marcarComoEncontrada(idPublicacion, nombreMascota, foto = 'mascota-default.jpg') {
    mostrarModalEncontrada(idPublicacion, nombreMascota, foto);
}

// Editar reporte - redirige con parámetro de edición
function editarReporte(idPublicacion, nombreMascota = 'esta mascota') {
    // Por ahora mantiene la redirección, pero con el modal preparado
    window.location.href = `mascotas-perdidas.php?editar=${idPublicacion}`;
    // Para usar el modal en el futuro: mostrarModalEditarPerdida(idPublicacion, nombreMascota);
}

// Eliminar reporte (con modal sofisticado)
function eliminarReporte(idPublicacion, nombreMascota, foto = 'mascota-default.jpg') {
    mostrarModalEliminarPerdida(idPublicacion, nombreMascota, foto);
}

// ==================== MODAL ENCONTRADA ====================
function mostrarModalEncontrada(idPublicacion, nombreMascota, foto = 'mascota-default.jpg') {
    datosModalPerdidaActual = { idPublicacion, nombreMascota };

    document.getElementById('mascotaInfoEncontrada').innerHTML = `
        <img src="imagenes/${foto}" alt="${nombreMascota}" class="modal-mascota-avatar-perdidas" onerror="this.src='imagenes/mascota-default.jpg'">
        <div class="modal-mascota-datos-perdidas">
            <h4>${nombreMascota}</h4>
            <p>Se marcará como encontrada</p>
        </div>
    `;

    document.getElementById('modalEncontrada').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalEncontrada() {
    document.getElementById('modalEncontrada').classList.remove('active');
    document.body.style.overflow = '';
    datosModalPerdidaActual = {};
}

function confirmarEncontrada() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'procesar-estado-mascota.php';
    form.innerHTML = `
        <input type="hidden" name="id_publicacion" value="${datosModalPerdidaActual.idPublicacion}">
        <input type="hidden" name="accion" value="encontrada">
    `;
    document.body.appendChild(form);
    form.submit();
}

// ==================== MODAL EDITAR ====================
function mostrarModalEditarPerdida(idPublicacion, nombreMascota) {
    datosModalPerdidaActual = { idPublicacion, nombreMascota };

    // Aquí deberías cargar los datos actuales del reporte
    // Por ahora, redirigimos a la página de edición como antes
    window.location.href = `mascotas-perdidas.php?editar=${idPublicacion}`;
}

function cerrarModalEditarPerdida() {
    document.getElementById('modalEditarPerdida').classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('formEditarPerdida').reset();
    document.getElementById('campoRecompensaEditar').style.display = 'none';
    datosModalPerdidaActual = {};
}

function toggleRecompensaModal() {
    const checkbox = document.getElementById('checkboxRecompensaEditar');
    const campo = document.getElementById('campoRecompensaEditar');
    campo.style.display = checkbox.checked ? 'block' : 'none';
}

function obtenerUbicacionModal() {
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización');
        return;
    }

    const input = document.getElementById('ubicacionEditar');
    const boton = event.target;
    boton.textContent = '⏳';

    navigator.geolocation.getCurrentPosition(
        function (position) {
            const lat = position.coords.latitude.toFixed(6);
            const lng = position.coords.longitude.toFixed(6);
            input.value = `${lat}, ${lng}`;
            boton.textContent = '✅';
            setTimeout(() => boton.textContent = '📍', 2000);
        },
        function (error) {
            alert('No se pudo obtener la ubicación');
            boton.textContent = '📍';
        }
    );
}

function confirmarEditarPerdida() {
    const fecha = document.getElementById('fechaPerdidaEditar').value;
    const hora = document.getElementById('horaPerdidaEditar').value;
    const ubicacion = document.getElementById('ubicacionEditar').value;
    const descripcion = document.getElementById('descripcionEditar').value;
    const recompensa = document.getElementById('checkboxRecompensaEditar').checked
        ? document.getElementById('recompensaEditar').value
        : '0';

    if (!fecha || !ubicacion) {
        alert('Por favor completa los campos requeridos');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'procesar-mascota-perdida.php';
    form.innerHTML = `
        <input type="hidden" name="id_publicacion" value="${datosModalPerdidaActual.idPublicacion}">
        <input type="hidden" name="fecha_perdida" value="${fecha}">
        <input type="hidden" name="hora_perdida" value="${hora}">
        <input type="hidden" name="ultima_ubicacion" value="${ubicacion}">
        <input type="hidden" name="descripcion" value="${descripcion}">
        <input type="hidden" name="recompensa" value="${recompensa}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// ==================== MODAL ELIMINAR ====================
function mostrarModalEliminarPerdida(idPublicacion, nombreMascota, foto = 'mascota-default.jpg') {
    datosModalPerdidaActual = { idPublicacion, nombreMascota };

    document.getElementById('mascotaInfoEliminarPerdida').innerHTML = `
        <img src="imagenes/${foto}" alt="${nombreMascota}" class="modal-mascota-avatar-perdidas" onerror="this.src='imagenes/mascota-default.jpg'">
        <div class="modal-mascota-datos-perdidas">
            <h4>${nombreMascota}</h4>
            <p>Reporte de mascota perdida</p>
        </div>
    `;

    document.getElementById('modalEliminarPerdida').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalEliminarPerdida() {
    document.getElementById('modalEliminarPerdida').classList.remove('active');
    document.body.style.overflow = '';
    datosModalPerdidaActual = {};
}

function confirmarEliminarPerdida() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'procesar-estado-mascota.php';
    form.innerHTML = `
        <input type="hidden" name="id_publicacion" value="${datosModalPerdidaActual.idPublicacion}">
        <input type="hidden" name="accion" value="eliminar">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Cerrar modales con ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModalEncontrada();
        cerrarModalEditarPerdida();
        cerrarModalEliminarPerdida();
    }
});

// Cerrar al hacer clic fuera
document.querySelectorAll('.modal-overlay-perdidas').forEach(modal => {
    modal.addEventListener('click', function (e) {
        if (e.target === this) {
            cerrarModalEncontrada();
            cerrarModalEditarPerdida();
            cerrarModalEliminarPerdida();
        }
    });
});

// Hacer funciones globales
window.mostrarModalEncontrada = mostrarModalEncontrada;
window.mostrarModalEditarPerdida = mostrarModalEditarPerdida;
window.mostrarModalEliminarPerdida = mostrarModalEliminarPerdida;
window.cerrarModalEncontrada = cerrarModalEncontrada;
window.cerrarModalEditarPerdida = cerrarModalEditarPerdida;
window.cerrarModalEliminarPerdida = cerrarModalEliminarPerdida;

// Hacer funciones globales
window.marcarComoEncontrada = marcarComoEncontrada;
window.editarReporte = editarReporte;
window.eliminarReporte = eliminarReporte;

console.log('Sistema de mascotas perdidas cargado correctamente');