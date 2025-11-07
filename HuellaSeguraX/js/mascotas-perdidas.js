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
                <strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#F19E39"><path d="M192.23-480Q152-480 124-507.77q-28-27.78-28-68Q96-616 123.77-644q27.78-28 68-28Q232-672 260-644.23q28 27.78 28 68Q288-536 260.23-508q-27.78 28-68 28Zm168-144Q320-624 292-651.77q-28-27.78-28-68Q264-760 291.77-788q27.78-28 68-28Q400-816 428-788.23q28 27.78 28 68Q456-680 428.23-652q-27.78 28-68 28Zm240 0Q560-624 532-651.77q-28-27.78-28-68Q504-760 531.77-788q27.78-28 68-28Q640-816 668-788.23q28 27.78 28 68Q696-680 668.23-652q-27.78 28-68 28Zm178 151Q736-473 706-502.77q-30-29.78-30-72Q676-617 705.77-647q29.78-30 72-30Q820-677 850-647.23q30 29.78 30 72Q880-533 850.23-503q-29.78 30-72 30ZM285-95q-38 0-65-31t-27-76q0-47 32-81t63-69q26-30 46-61t43-62q20-26 45.5-39.5T480-528q32 0 58 13t45 39q23 31 43 61.5t46 61.5q30 36 63 69.5t33 81.82Q768-158 740.5-127 713-96 674-96q-50 0-97-12t-97-12q-50 0-97.5 12.5T285-95Z"/></svg> Mascota:</strong> ${nombreMascota}
            </div>
            <div class="item-resumen">
                <strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Z"/></svg> Fecha perdida:</strong> ${datosReporte.fecha_perdida ? new Date(datosReporte.fecha_perdida).toLocaleDateString('es-ES') : 'No especificada'}
            </div>
            ${datosReporte.hora_perdida ? `
                <div class="item-resumen">
                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#434343"><path d="M480-96q-70 0-131.13-26.6-61.14-26.6-106.4-71.87-45.27-45.26-71.87-106.4Q144-362 144-432t26.6-131.13q26.6-61.14 71.87-106.4 45.26-45.27 106.4-71.87Q410-768 480-768t131.13 26.6q61.14 26.6 106.4 71.87 45.27 45.26 71.87 106.4Q816-502 816-432t-26.6 131.13q-26.6 61.14-71.87 106.4-45.26 45.27-106.4 71.87Q550-96 480-96Zm100-200 51-51-115-115v-162h-72v192l136 136ZM237-845l51 51-170 170-51-51 170-170Zm486 0 170 170-51 51-170-170 51-51Z"/></svg> Hora:</strong> ${datosReporte.hora_perdida}
                </div>
            ` : ''}
            <div class="item-resumen">
                <strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#EA3323"><path d="M480.21-480Q510-480 531-501.21t21-51Q552-582 530.79-603t-51-21Q450-624 429-602.79t-21 51Q408-522 429.21-501t51 21ZM480-96Q323.03-227.11 245.51-339.55 168-452 168-549q0-134 89-224.5T479.5-864q133.5 0 223 90.5T792-549q0 97-77 209T480-96Z"/></svg> Ubicación:</strong> ${datosReporte.ultima_ubicacion || 'No especificada'}
            </div>
            ${datosReporte.descripcion ? `
                <div class="item-resumen">
                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5985E1"><path d="M336-240h288v-72H336v72Zm0-144h288v-72H336v72ZM263.72-96Q234-96 213-117.15T192-168v-624q0-29.7 21.15-50.85Q234.3-864 264-864h312l192 192v504q0 29.7-21.16 50.85Q725.68-96 695.96-96H263.72ZM528-624h168L528-792v168Z"/></svg> Descripción:</strong> ${datosReporte.descripcion}
                </div>
            ` : ''}
            ${datosReporte.ofrecer_recompensa ? `
                <div class="item-resumen destacado">
                    <strong><svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#75FB4C"><path d="M444-144v-80q-51-11-87.5-46T305-357l74-30q8 36 40.5 64.5T487-294q39 0 64-20t25-52q0-30-22.5-50T474-456q-78-28-114-61.5T324-604q0-50 32.5-86t87.5-47v-79h72v79q72 12 96.5 55t25.5 45l-70 29q-8-26-32-43t-53-17q-35 0-58 18t-23 44q0 26 25 44.5t93 41.5q70 23 102 60t32 94q0 57-37 96t-101 49v77h-72Z"/></svg> Recompensa:</strong> €${datosReporte.recompensa || '0'}
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