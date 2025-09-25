let pasoActual = 1;
const totalPasos = 3;
let datosReporte = {};

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
    const pasoDiv = document.getElementById('paso' + pasoActual);
    const camposRequeridos = pasoDiv.querySelectorAll('input[required], select[required]');

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
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarFormularioReporte();
    }
});