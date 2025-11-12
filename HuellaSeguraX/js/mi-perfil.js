// Funcionalidad del modal de contraseña
function mostrarModalPassword() {
    document.getElementById('modalPassword').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function cerrarModalPassword() {
    document.getElementById('modalPassword').classList.remove('show');
    document.body.style.overflow = '';
    
    // Limpiar formulario
    const form = document.querySelector('#modalPassword form');
    if (form) {
        form.reset();
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalPassword').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalPassword();
    }
});

// Confirmar cerrar sesión
function confirmarCerrarSesion() {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
        window.location.href = 'logout.php';
    }
}

// Validación en tiempo real para contraseñas
document.addEventListener('DOMContentLoaded', function() {
    const passwordNueva = document.querySelector('input[name="password_nueva"]');
    const passwordConfirmar = document.querySelector('input[name="password_confirmar"]');
    
    if (passwordNueva && passwordConfirmar) {
        function validarPasswords() {
            if (passwordNueva.value && passwordConfirmar.value) {
                if (passwordNueva.value === passwordConfirmar.value) {
                    passwordConfirmar.style.borderColor = '#27AE60';
                } else {
                    passwordConfirmar.style.borderColor = '#E74C3C';
                }
            } else {
                passwordConfirmar.style.borderColor = '#e8e8e8';
            }
        }
        
        passwordNueva.addEventListener('input', validarPasswords);
        passwordConfirmar.addEventListener('input', validarPasswords);
    }
});

// Animaciones de entrada
function animarElementos() {
    const elementos = document.querySelectorAll('.stat-card, .seccion-card');
    elementos.forEach((elemento, index) => {
        elemento.style.opacity = '0';
        elemento.style.transform = 'translateY(20px)';
        elemento.style.transition = `all 0.3s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            elemento.style.opacity = '1';
            elemento.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Función para mostrar/ocultar contraseña
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}

// Validación del formulario de perfil
function validarFormularioPerfil(form) {
    const inputs = form.querySelectorAll('input[required]');
    let esValido = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#E74C3C';
            esValido = false;
        } else {
            input.style.borderColor = '#e8e8e8';
        }
        
        // Validación específica de email
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.style.borderColor = '#E74C3C';
                esValido = false;
            }
        }
    });
    
    return esValido;
}

// Interceptar envío de formularios para validación
document.addEventListener('DOMContentLoaded', function() {
    const formularios = document.querySelectorAll('form');
    
    formularios.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validarFormularioPerfil(this)) {
                e.preventDefault();
                mostrarMensaje('Por favor, completa todos los campos correctamente', 'error');
            }
        });
    });
    
    // Ejecutar animaciones
    setTimeout(animarElementos, 300);
    //
    // ACOMODARRRRRRR!!!!!!!!!!
    //
    // Si hay errores de contraseña, mostrar modal automáticamente 
//    <?php if (!empty($errores_password) || isset($mensaje_error_password) || isset($mensaje_exito_password)): ?>
//        mostrarModalPassword();
//    <?php endif; ?>
//});

// Función para mostrar mensajes temporales
function mostrarMensaje(texto, tipo = 'info', duracion = 4000) {
    const mensaje = document.createElement('div');
    mensaje.className = `mensaje-perfil mensaje-${tipo === 'error' ? 'error' : 'exito'}`;
    mensaje.innerHTML = (tipo === 'error' ? '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#EA3323"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg> ' : '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#75FB4C"><path d="m424-312 282-282-56-56-226 226-114-114-56 56 170 170ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Z"/></svg> ') + texto;
    mensaje.style.position = 'fixed';
    mensaje.style.top = '80px';
    mensaje.style.right = '20px';
    mensaje.style.zIndex = '10000';
    mensaje.style.minWidth = '300px';
    mensaje.style.animation = 'slideIn 0.3s ease';
    
    document.body.appendChild(mensaje);
    
    setTimeout(() => {
        if (mensaje.parentElement) {
            mensaje.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => mensaje.remove(), 300);
        }
    }, duracion);
}

// Funcionalidad para los switches
document.addEventListener('DOMContentLoaded', function() {
    const switches = document.querySelectorAll('.switch input');
    
    switches.forEach(switchElement => {
        switchElement.addEventListener('change', function() {
            const opcion = this.closest('.opcion-config');
            const titulo = opcion.querySelector('.titulo-opcion').textContent;
            
            // Aquí podrías enviar la configuración al servidor via AJAX
            console.log(`${titulo}: ${this.checked ? 'Activado' : 'Desactivado'}`);
            
            // Mostrar mensaje de confirmación
            mostrarMensaje(`${titulo} ${this.checked ? 'activado' : 'desactivado'}`, 'exito', 2000);
        });
    });
});

// Función para copiar ID de usuario al portapapeles
function copiarIDUsuario() {
    const userId = '<?php echo $usuario_id; ?>';
    navigator.clipboard.writeText(userId).then(() => {
        mostrarMensaje('ID de usuario copiado al portapapeles', 'exito', 2000);
    });
}// AGREGA ESTA FUNCIÓN al archivo mi-perfil.js existente

/**
 * Subir foto de perfil
 * @param {HTMLInputElement} input - Input file element
 */
function subirFotoPerfil(input) {
    const archivo = input.files[0];
    
    if (!archivo) {
        return;
    }
    
    // Validar tipo de archivo
    const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!tiposPermitidos.includes(archivo.type)) {
        mostrarMensajeFoto('Solo se permiten imágenes JPG, PNG o WEBP', 'error');
        input.value = '';
        return;
    }
    
    // Validar tamaño (máximo 5MB)
    const tamañoMaximo = 5 * 1024 * 1024; // 5MB en bytes
    if (archivo.size > tamañoMaximo) {
        mostrarMensajeFoto('La imagen no debe superar los 5MB', 'error');
        input.value = '';
        return;
    }
    
    // Mostrar preview inmediato
    const reader = new FileReader();
    reader.onload = function(e) {
        const avatarPerfil = document.getElementById('avatarPerfil');
        const imagenExistente = avatarPerfil.querySelector('img');
        
        if (imagenExistente) {
            imagenExistente.src = e.target.result;
        } else {
            // Si no hay imagen, crear una nueva
            const svgExistente = avatarPerfil.querySelector('svg');
            if (svgExistente) {
                svgExistente.remove();
            }
            
            const nuevaImagen = document.createElement('img');
            nuevaImagen.src = e.target.result;
            nuevaImagen.alt = 'Foto de perfil';
            nuevaImagen.id = 'imagenPerfil';
            avatarPerfil.appendChild(nuevaImagen);
        }
    };
    reader.readAsDataURL(archivo);
    
    // Preparar FormData para enviar
    const formData = new FormData();
    formData.append('foto_perfil', archivo);
    
    // Mostrar indicador de carga
    const avatarPerfil = document.getElementById('avatarPerfil');
    avatarPerfil.classList.add('cargando');
    
    const btnCambiarFoto = document.querySelector('.btn-cambiar-foto');
    const textoOriginalBtn = btnCambiarFoto.innerHTML;
    btnCambiarFoto.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q17 0 28.5 11.5T520-840q0 17-11.5 28.5T480-800q-133 0-226.5 93.5T160-480q0 133 93.5 226.5T480-160q133 0 226.5-93.5T800-480q0-17 11.5-28.5T840-520q17 0 28.5 11.5T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Z"/></svg> Subiendo...';
    btnCambiarFoto.disabled = true;
    
    // Enviar al servidor
    fetch('ajax/subir-foto-perfil.php', {
    method: 'POST',
    body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Response raw:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Error parsing JSON:', e);
            console.error('Response was:', text);
            throw new Error('Respuesta del servidor no es JSON válido');
        }
    })
    .then(data => {
        console.log('Response data:', data);
        
        // Mostrar información de debug si existe
        if (data.debug) {
            console.group('Debug Info:');
            data.debug.forEach(msg => console.log(msg));
            console.groupEnd();
        }
        
        avatarPerfil.classList.remove('cargando');
        btnCambiarFoto.innerHTML = textoOriginalBtn;
        btnCambiarFoto.disabled = false;
        
        if (data.success) {
            mostrarMensajeFoto(data.message, 'exito');
            
            // Actualizar la imagen con la URL del servidor
            const imagenPerfil = document.getElementById('imagenPerfil');
            if (imagenPerfil) {
                imagenPerfil.src = data.foto_url + '?t=' + new Date().getTime();
            }
            
            // Actualizar también el menú hamburguesa si existe
            const avatarMenu = document.querySelector('.usuario-info img');
            if (avatarMenu) {
                avatarMenu.src = data.foto_url + '?t=' + new Date().getTime();
            }
        } else {
            console.error('Error del servidor:', data.message);
            mostrarMensajeFoto(data.message, 'error');
            // Revertir la imagen en caso de error
            setTimeout(() => location.reload(), 3000);
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        avatarPerfil.classList.remove('cargando');
        btnCambiarFoto.innerHTML = textoOriginalBtn;
        btnCambiarFoto.disabled = false;
        mostrarMensajeFoto('Error al subir la imagen. Revisa la consola para más detalles.', 'error');
        
        // Recargar la página para mostrar la imagen anterior
        setTimeout(() => location.reload(), 3000);
    })
    .finally(() => {
        // Limpiar el input para permitir subir la misma imagen de nuevo si es necesario
        input.value = '';
    });
}

<<<<<<< Updated upstream
    /**
     * Subir foto de perfil
     * @param {HTMLInputElement} input - Input file element
     */
    function subirFotoPerfil(input) {
        const archivo = input.files[0];

        if (!archivo) {
            return;
        }

        // Validar tipo de archivo
        const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!tiposPermitidos.includes(archivo.type)) {
            mostrarMensajeFoto('Solo se permiten imágenes JPG, PNG o WEBP', 'error');
            input.value = '';
            return;
        }

        // Validar tamaño (máximo 5MB)
        const tamañoMaximo = 5 * 1024 * 1024; // 5MB en bytes
        if (archivo.size > tamañoMaximo) {
            mostrarMensajeFoto('La imagen no debe superar los 5MB', 'error');
            input.value = '';
            return;
        }

        // Mostrar preview inmediato
        const reader = new FileReader();
        reader.onload = function (e) {
            const avatarPerfil = document.getElementById('avatarPerfil');
            const imagenExistente = avatarPerfil.querySelector('img');

            if (imagenExistente) {
                imagenExistente.src = e.target.result;
            } else {
                // Si no hay imagen, crear una nueva
                const svgExistente = avatarPerfil.querySelector('svg');
                if (svgExistente) {
                    svgExistente.remove();
                }

                const nuevaImagen = document.createElement('img');
                nuevaImagen.src = e.target.result;
                nuevaImagen.alt = 'Foto de perfil';
                nuevaImagen.id = 'imagenPerfil';
                avatarPerfil.appendChild(nuevaImagen);
            }
        };
        reader.readAsDataURL(archivo);

        // Preparar FormData para enviar
        const formData = new FormData();
        formData.append('foto_perfil', archivo);

        // Mostrar indicador de carga
        const avatarPerfil = document.getElementById('avatarPerfil');
        avatarPerfil.classList.add('cargando');

        const btnCambiarFoto = document.querySelector('.btn-cambiar-foto');
        const textoOriginalBtn = btnCambiarFoto.innerHTML;
        btnCambiarFoto.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q17 0 28.5 11.5T520-840q0 17-11.5 28.5T480-800q-133 0-226.5 93.5T160-480q0 133 93.5 226.5T480-160q133 0 226.5-93.5T800-480q0-17 11.5-28.5T840-520q17 0 28.5 11.5T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Z"/></svg> Subiendo...';
        btnCambiarFoto.disabled = true;

        // Enviar al servidor
        fetch('ajax/subir-foto-perfil.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response raw:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    console.error('Response was:', text);
                    throw new Error('Respuesta del servidor no es JSON válido');
                }
            })
            .then(data => {
                console.log('Response data:', data);

                // Mostrar información de debug si existe
                if (data.debug) {
                    console.group('Debug Info:');
                    data.debug.forEach(msg => console.log(msg));
                    console.groupEnd();
                }

                avatarPerfil.classList.remove('cargando');
                btnCambiarFoto.innerHTML = textoOriginalBtn;
                btnCambiarFoto.disabled = false;

                if (data.success) {
                    mostrarMensajeFoto(data.message, 'exito');

                    // Actualizar la imagen con la URL del servidor
                    const imagenPerfil = document.getElementById('imagenPerfil');
                    if (imagenPerfil) {
                        imagenPerfil.src = data.foto_url + '?t=' + new Date().getTime();
                    }

                    // Actualizar también el menú hamburguesa si existe
                    const avatarMenu = document.querySelector('.usuario-info img');
                    if (avatarMenu) {
                        avatarMenu.src = data.foto_url + '?t=' + new Date().getTime();
                    }
                } else {
                    console.error('Error del servidor:', data.message);
                    mostrarMensajeFoto(data.message, 'error');
                    // Revertir la imagen en caso de error
                    setTimeout(() => location.reload(), 3000);
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                avatarPerfil.classList.remove('cargando');
                btnCambiarFoto.innerHTML = textoOriginalBtn;
                btnCambiarFoto.disabled = false;
                mostrarMensajeFoto('Error al subir la imagen. Revisa la consola para más detalles.', 'error');

                // Recargar la página para mostrar la imagen anterior
                setTimeout(() => location.reload(), 3000);
            })
            .finally(() => {
                // Limpiar el input para permitir subir la misma imagen de nuevo si es necesario
                input.value = '';
            });
    }

    /**
     * Mostrar mensaje temporal sobre la foto
     * @param {string} mensaje - Mensaje a mostrar
     * @param {string} tipo - 'exito' o 'error'
     */
    function mostrarMensajeFoto(mensaje, tipo = 'exito') {
        // Eliminar mensajes anteriores
        const mensajesAnteriores = document.querySelectorAll('.mensaje-foto');
        mensajesAnteriores.forEach(m => m.remove());

        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = `mensaje-foto ${tipo}`;

        const icono = tipo === 'exito'
            ? '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z"/></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56Z"/></svg>';

        mensajeDiv.innerHTML = `
=======
/**
 * Mostrar mensaje temporal sobre la foto
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} tipo - 'exito' o 'error'
 */
function mostrarMensajeFoto(mensaje, tipo = 'exito') {
    // Eliminar mensajes anteriores
    const mensajesAnteriores = document.querySelectorAll('.mensaje-foto');
    mensajesAnteriores.forEach(m => m.remove());
    
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `mensaje-foto ${tipo}`;
    
    const icono = tipo === 'exito' 
        ? '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF"><path d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56Z"/></svg>';
    
    mensajeDiv.innerHTML = `
>>>>>>> Stashed changes
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            ${icono}
            <span>${mensaje}</span>
        </div>
    `;
<<<<<<< Updated upstream

        document.body.appendChild(mensajeDiv);

        // Eliminar después de 4 segundos
        setTimeout(() => {
            mensajeDiv.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => mensajeDiv.remove(), 300);
        }, 4000);
    }

    // Agregar animación de salida al CSS inline
    const styleSlideOut = document.createElement('style');
    styleSlideOut.textContent = `
=======
    
    document.body.appendChild(mensajeDiv);
    
    // Eliminar después de 4 segundos
    setTimeout(() => {
        mensajeDiv.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => mensajeDiv.remove(), 300);
    }, 4000);
}

// Agregar animación de salida al CSS inline
const styleSlideOut = document.createElement('style');
styleSlideOut.textContent = `
>>>>>>> Stashed changes
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
`;
<<<<<<< Updated upstream
    document.head.appendChild(styleSlideOut);

    // Exportar funciones globalmente
    window.subirFotoPerfil = subirFotoPerfil;
    window.mostrarMensajeFoto = mostrarMensajeFoto;
=======
document.head.appendChild(styleSlideOut);

// Exportar funciones globalmente
window.subirFotoPerfil = subirFotoPerfil;
window.mostrarMensajeFoto = mostrarMensajeFoto;

>>>>>>> Stashed changes

console.log('Perfil de usuario cargado correctamente - Huella Segura v1.0');});