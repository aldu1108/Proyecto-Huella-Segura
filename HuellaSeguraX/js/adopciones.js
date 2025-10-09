// Variables globales
let adopcionSeleccionada = null;
let nombreMascotaSeleccionada = '';
let datosModalActual = {};

// Modal solicitud de adopción
function mostrarSolicitudAdopcion(idAdopcion, nombreMascota) {
    adopcionSeleccionada = idAdopcion;
    nombreMascotaSeleccionada = nombreMascota;

    document.getElementById('idAdopcionSolicitud').value = idAdopcion;
    document.getElementById('nombreMascotaSolicitud').textContent = `Solicitar adopción de: ${nombreMascota}`;
    document.getElementById('modalSolicitud').style.display = 'flex';
}

function cerrarSolicitud() {
    document.getElementById('modalSolicitud').style.display = 'none';
    document.getElementById('formularioSolicitud').reset();
}

// Modal crear publicación
function mostrarFormularioPublicacion() {
    document.getElementById('modalCrearPublicacion').style.display = 'flex';
}

function cerrarPublicacion() {
    document.getElementById('modalCrearPublicacion').style.display = 'none';
    document.getElementById('formularioPublicacion').reset();
}

// Función para marcar mascota como adoptada (con modal sofisticado)
function marcarComoAdoptada(id, nombre, foto = 'mascota-default.jpg') {
    mostrarModalAdoptada(id, nombre, foto);
}

// Función para editar adopción (con modal sofisticado)
function editarAdopcion(id, condiciones, lugar, nombre) {
    mostrarModalEditar(id, condiciones, lugar, nombre);
}

// Función para eliminar adopción (con modal sofisticado)
function eliminarAdopcion(id, nombre, foto = 'mascota-default.jpg') {
    mostrarModalEliminar(id, nombre, foto);
}

// Hacer funciones globalmente disponibles
window.marcarComoAdoptada = marcarComoAdoptada;
window.editarAdopcion = editarAdopcion;
window.eliminarAdopcion = eliminarAdopcion;
window.mostrarSolicitudAdopcion = mostrarSolicitudAdopcion;
window.cerrarSolicitud = cerrarSolicitud;
window.mostrarFormularioPublicacion = mostrarFormularioPublicacion;
window.cerrarPublicacion = cerrarPublicacion;

// Cerrar modales al hacer clic fuera
document.addEventListener('DOMContentLoaded', function () {
    const modalSolicitud = document.getElementById('modalSolicitud');
    const modalCrearPublicacion = document.getElementById('modalCrearPublicacion');

    if (modalSolicitud) {
        modalSolicitud.addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarSolicitud();
            }
        });
    }

    if (modalCrearPublicacion) {
        modalCrearPublicacion.addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarPublicacion();
            }
        });
    }
});

// Funcionalidad de filtros
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.filtro-adopcion').forEach(filtro => {
        filtro.addEventListener('click', function () {
            document.querySelectorAll('.filtro-adopcion').forEach(f => f.classList.remove('activo'));
            this.classList.add('activo');

            const tipoFiltro = this.textContent.trim();
            filtrarMascotas(tipoFiltro);
        });
    });
});

function filtrarMascotas(tipo) {
    const tarjetas = document.querySelectorAll('.tarjeta-adopcion, .tarjeta-reporte');

    tarjetas.forEach(tarjeta => {
        if (tipo === '❤️ Todos') {
            tarjeta.style.display = 'block';
        } else {
            const infoReporte = tarjeta.querySelector('.info-reporte');
            if (!infoReporte) {
                tarjeta.style.display = 'block';
                return;
            }

            const tipoMascota = infoReporte.textContent;
            let mostrar = false;

            if (tipo === '🐕 Perros' && tipoMascota.toLowerCase().includes('perro')) {
                mostrar = true;
            } else if (tipo === '🐱 Gatos' && tipoMascota.toLowerCase().includes('gato')) {
                mostrar = true;
            } else if (tipo === '⚙️ Otros' && !tipoMascota.toLowerCase().includes('perro') && !tipoMascota.toLowerCase().includes('gato')) {
                mostrar = true;
            }

            tarjeta.style.display = mostrar ? 'block' : 'none';
        }
    });
}

// Animaciones de entrada para las tarjetas
function animarTarjetas() {
    const tarjetas = document.querySelectorAll('.tarjeta-adopcion, .tarjeta-reporte');
    tarjetas.forEach((tarjeta, index) => {
        tarjeta.style.animationDelay = `${index * 0.1}s`;
    });
}

// Cerrar modales con tecla Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarSolicitud();
        cerrarPublicacion();
    }
});

// Ejecutar animaciones al cargar
document.addEventListener('DOMContentLoaded', function () {
    animarTarjetas();

    // Manejar mensajes de la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('exito') || urlParams.get('error')) {
        // Limpiar URL después de mostrar el mensaje
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 5000);
    }
});

// Delegación: abrir modal de solicitud al hacer click en botones .boton-interesa-adoptar
document.addEventListener('DOMContentLoaded', function () {
    if (document.body.dataset.adopcionesInit === '1') return;
    document.body.dataset.adopcionesInit = '1';

    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest && e.target.closest('.boton-interesa-adoptar');
        if (!btn) return;

        // Si tiene un data-alert mostramos alerta (caso ejemplo)
        if (btn.dataset.alert) {
            alert(btn.dataset.alert);
            return;
        }

        const id = btn.dataset.idAdopcion || btn.getAttribute('data-id-adopcion');
        const nombre = btn.dataset.nombre || btn.getAttribute('data-nombre');

        if (id !== undefined && id !== null) {
            mostrarSolicitudAdopcion(parseInt(id, 10), nombre || 'la mascota');
        }
    });
});

// MODAL ADOPTADA
function mostrarModalAdoptada(id, nombre, foto = 'mascota-default.jpg') {
    datosModalActual = { id, nombre };

    document.getElementById('mascotaInfoAdoptada').innerHTML = `
        <img src="imagenes/${foto}" alt="${nombre}" class="modal-mascota-avatar">
        <div class="modal-mascota-datos">
            <h4>${nombre}</h4>
            <p>Se marcará como adoptada</p>
        </div>
    `;

    document.getElementById('modalAdoptada').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalAdoptada() {
    document.getElementById('modalAdoptada').classList.remove('active');
    document.body.style.overflow = '';
    datosModalActual = {};
}

function confirmarAdoptada() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'gestionar-adopcion.php?accion=adoptada';
    form.innerHTML = `<input type="hidden" name="id_adopcion" value="${datosModalActual.id}">`;
    document.body.appendChild(form);
    form.submit();
}

// MODAL EDITAR
function mostrarModalEditar(id, condiciones, lugar, nombre) {
    datosModalActual = { id, nombre };

    document.getElementById('idAdopcionEditar').value = id;
    document.getElementById('condicionesEditar').value = condiciones;
    document.getElementById('lugarEditar').value = lugar;
    document.getElementById('subtituloEditar').textContent = `Editando publicación de ${nombre}`;

    document.getElementById('modalEditar').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('formEditar').reset();
    datosModalActual = {};
}

function confirmarEditar() {
    const condiciones = document.getElementById('condicionesEditar').value.trim();
    const lugar = document.getElementById('lugarEditar').value.trim();
    const id = document.getElementById('idAdopcionEditar').value;

    if (!condiciones || !lugar) {
        alert('Por favor completa todos los campos');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'gestionar-adopcion.php?accion=editar';
    form.innerHTML = `
        <input type="hidden" name="id_adopcion" value="${id}">
        <input type="hidden" name="condiciones" value="${condiciones}">
        <input type="hidden" name="lugar_adopcion" value="${lugar}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// MODAL ELIMINAR
function mostrarModalEliminar(id, nombre, foto = 'mascota-default.jpg') {
    datosModalActual = { id, nombre };

    document.getElementById('mascotaInfoEliminar').innerHTML = `
        <img src="imagenes/${foto}" alt="${nombre}" class="modal-mascota-avatar">
        <div class="modal-mascota-datos">
            <h4>${nombre}</h4>
            <p>Publicación de adopción</p>
        </div>
    `;

    document.getElementById('modalEliminar').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').classList.remove('active');
    document.body.style.overflow = '';
    datosModalActual = {};
}

function confirmarEliminar() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'gestionar-adopcion.php?accion=eliminar';
    form.innerHTML = `<input type="hidden" name="id_adopcion" value="${datosModalActual.id}">`;
    document.body.appendChild(form);
    form.submit();
}

// Cerrar modales con ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModalAdoptada();
        cerrarModalEditar();
        cerrarModalEliminar();
    }
});

// Cerrar al hacer clic fuera
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function (e) {
        if (e.target === this) {
            cerrarModalAdoptada();
            cerrarModalEditar();
            cerrarModalEliminar();
        }
    });
});

// Hacer funciones globales
window.mostrarModalAdoptada = mostrarModalAdoptada;
window.mostrarModalEditar = mostrarModalEditar;
window.mostrarModalEliminar = mostrarModalEliminar;
window.cerrarModalAdoptada = cerrarModalAdoptada;
window.cerrarModalEditar = cerrarModalEditar;
window.cerrarModalEliminar = cerrarModalEliminar;

console.log('Sistema de adopciones cargado correctamente');