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
    mensaje.innerHTML = (tipo === 'error' ? '❌ ' : '✅ ') + texto;
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
}

console.log('Perfil de usuario cargado correctamente - Huella Segura v1.0');});