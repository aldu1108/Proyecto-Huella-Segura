// Funcionalidades para la sección de comunidad

// Variables globales
let imagenesSeleccionadas = [];
let postActualCompartir = null;
let tituloPostCompartir = '';

// Previsualizar imágenes antes de subir
function previsualizarImagenes(input) {
    const previewContainer = document.getElementById('preview-imagenes');
    const archivos = input.files;
    
    // Limpiar preview anterior
    previewContainer.innerHTML = '';
    imagenesSeleccionadas = [];
    
    // Limitar a 5 imágenes
    const maxImagenes = Math.min(archivos.length, 5);
    
    for (let i = 0; i < maxImagenes; i++) {
        const archivo = archivos[i];
        
        // Validar tipo de archivo
        if (!archivo.type.startsWith('image/')) {
            continue;
        }
        
        // Validar tamaño (5MB max)
        if (archivo.size > 5000000) {
            alert(`La imagen "${archivo.name}" es muy grande. Máximo 5MB por imagen.`);
            continue;
        }
        
        imagenesSeleccionadas.push(archivo);
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="btn-eliminar-preview" onclick="eliminarImagenPreview(${i})">×</button>
            `;
            previewContainer.appendChild(previewItem);
        };
        reader.readAsDataURL(archivo);
    }
    
    if (maxImagenes < archivos.length) {
        alert(`Solo se pueden subir hasta 5 imágenes. Se seleccionaron las primeras 5.`);
    }
}

// Eliminar imagen del preview
function eliminarImagenPreview(indice) {
    const previewContainer = document.getElementById('preview-imagenes');
    const items = previewContainer.querySelectorAll('.preview-item');
    
    if (items[indice]) {
        items[indice].remove();
    }
    
    // Actualizar array de imágenes seleccionadas
    imagenesSeleccionadas.splice(indice, 1);
    
    // Si no quedan imágenes, limpiar el input
    if (imagenesSeleccionadas.length === 0) {
        document.getElementById('imagenes_post').value = '';
    }
}

// Abrir modal de imagen expandida
function abrirModalImagen(srcImagen) {
    const modal = document.getElementById('modalImagen');
    const imagenExpandida = document.getElementById('imagenExpandida');
    
    modal.style.display = 'block';
    imagenExpandida.src = srcImagen;
    
    // Prevenir scroll del body
    document.body.style.overflow = 'hidden';
}

// Cerrar modal de imagen
function cerrarModalImagen() {
    const modal = document.getElementById('modalImagen');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Cerrar modal al presionar ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalImagen();
        cerrarModalCompartir();
    }
});

// Toggle like en posts
function toggleLike(boton) {
    const postId = boton.getAttribute('data-post-id');
    const estaLiked = boton.classList.contains('liked');
    
    // Deshabilitar botón durante la petición
    if (boton.disabled) return;
    boton.disabled = true;
    
    // Hacer petición AJAX para guardar/quitar like
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('accion', estaLiked ? 'quitar' : 'dar');
    
    fetch('ajax/toggle_like.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar UI con el contador del servidor
            boton.innerHTML = `❤️ ${data.total_likes}`;
            
            // Toggle de la clase liked
            if (estaLiked) {
                boton.classList.remove('liked');
            } else {
                boton.classList.add('liked');
            }
            
            // Animación
            boton.style.transform = 'scale(1.2)';
            setTimeout(() => {
                boton.style.transform = 'scale(1)';
            }, 200);
        } else {
            // Mostrar el error solo si realmente hay un problema
            if (data.message && !data.message.includes('Ya diste like')) {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al procesar el like');
    })
    .finally(() => {
        // Rehabilitar botón
        boton.disabled = false;
    });
}

// Compartir post
function compartirPost(postId, titulo) {
    postActualCompartir = postId;
    tituloPostCompartir = titulo;
    
    const modal = document.getElementById('modalCompartir');
    const inputLink = document.getElementById('linkCompartir');
    
    // Generar URL del post
    const urlBase = window.location.origin + window.location.pathname;
    const urlPost = `${urlBase}?post=${postId}`;
    
    inputLink.value = urlPost;
    modal.style.display = 'block';
    
    // Prevenir scroll del body
    document.body.style.overflow = 'hidden';
}

// Cerrar modal de compartir
function cerrarModalCompartir() {
    const modal = document.getElementById('modalCompartir');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Copiar enlace al portapapeles
function copiarEnlace() {
    const input = document.getElementById('linkCompartir');
    input.select();
    input.setSelectionRange(0, 99999); // Para móviles
    
    navigator.clipboard.writeText(input.value).then(() => {
        // Cambiar texto del botón temporalmente
        const boton = event.target;
        const textoOriginal = boton.innerHTML;
        boton.innerHTML = '✓ ¡Copiado!';
        boton.style.background = '#27ae60';
        
        setTimeout(() => {
            boton.innerHTML = textoOriginal;
            boton.style.background = '#667eea';
        }, 2000);
    }).catch(err => {
        alert('Error al copiar el enlace');
        console.error('Error:', err);
    });
}

// Compartir en Facebook
function compartirEnFacebook() {
    const url = document.getElementById('linkCompartir').value;
    const urlFacebook = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    window.open(urlFacebook, '_blank', 'width=600,height=400');
}

// Compartir en Twitter
function compartirEnTwitter() {
    const url = document.getElementById('linkCompartir').value;
    const texto = `${tituloPostCompartir} - Huella Segura`;
    const urlTwitter = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(texto)}`;
    window.open(urlTwitter, '_blank', 'width=600,height=400');
}

// Compartir en Instagram (Instagram no permite compartir directo desde web, solo copiar)
function compartirEnInstagram() {
    alert('Instagram no permite compartir enlaces directamente desde la web.\n\nEl enlace se ha copiado al portapapeles. Puedes pegarlo en tu biografía de Instagram o enviarlo por mensaje directo.');
    copiarEnlace();
}

// Validar formulario antes de enviar
document.addEventListener('DOMContentLoaded', function() {
    const formCrearPost = document.getElementById('formCrearPost');
    
    if (formCrearPost) {
        formCrearPost.addEventListener('submit', function(e) {
            const titulo = document.querySelector('input[name="titulo_post"]').value.trim();
            const contenido = document.querySelector('textarea[name="contenido_post"]').value.trim();
            
            if (!titulo || !contenido) {
                e.preventDefault();
                alert('Por favor completa el título y el contenido del post');
                return false;
            }
            
            if (titulo.length < 3) {
                e.preventDefault();
                alert('El título debe tener al menos 3 caracteres');
                return false;
            }
            
            if (contenido.length < 10) {
                e.preventDefault();
                alert('El contenido debe tener al menos 10 caracteres');
                return false;
            }
            
            // Mostrar indicador de carga
            const botonPublicar = formCrearPost.querySelector('.btn-publicar');
            botonPublicar.innerHTML = '⏳ Publicando...';
            botonPublicar.disabled = true;
        });
    }
    
    // Contador de caracteres para el contenido
    const textareaContenido = document.querySelector('.textarea-contenido-post');
    if (textareaContenido) {
        const maxCaracteres = 500;
        
        // Crear contador
        const contador = document.createElement('div');
        contador.style.cssText = 'text-align: right; font-size: 12px; color: #666; margin-top: 4px;';
        contador.innerHTML = `0 / ${maxCaracteres} caracteres`;
        textareaContenido.parentNode.insertBefore(contador, textareaContenido.nextSibling);
        
        textareaContenido.addEventListener('input', function() {
            const longitud = this.value.length;
            contador.innerHTML = `${longitud} / ${maxCaracteres} caracteres`;
            
            if (longitud > maxCaracteres * 0.9) {
                contador.style.color = '#e74c3c';
            } else if (longitud > maxCaracteres * 0.7) {
                contador.style.color = '#f39c12';
            } else {
                contador.style.color = '#666';
            }
        });
    }
});

// Animación de carga de imágenes
function mostrarCargandoImagen() {
    const loader = document.createElement('div');
    loader.className = 'loader-imagen';
    loader.innerHTML = '<div class="spinner"></div><p>Cargando imagen...</p>';
    document.body.appendChild(loader);
    
    setTimeout(() => {
        loader.remove();
    }, 1000);
}

// Cerrar modales al hacer clic fuera
window.onclick = function(event) {
    const modalImagen = document.getElementById('modalImagen');
    const modalCompartir = document.getElementById('modalCompartir');
    
    if (event.target === modalImagen) {
        cerrarModalImagen();
    }
    
    if (event.target === modalCompartir) {
        cerrarModalCompartir();
    }
}

// Función para formatear fechas relativas
function formatearFechaRelativa(fecha) {
    const ahora = new Date();
    const fechaPost = new Date(fecha);
    const diferencia = ahora - fechaPost;
    
    const segundos = Math.floor(diferencia / 1000);
    const minutos = Math.floor(segundos / 60);
    const horas = Math.floor(minutos / 60);
    const dias = Math.floor(horas / 24);
    
    if (segundos < 60) return 'Ahora mismo';
    if (minutos < 60) return `Hace ${minutos} minuto${minutos > 1 ? 's' : ''}`;
    if (horas < 24) return `Hace ${horas} hora${horas > 1 ? 's' : ''}`;
    if (dias < 7) return `Hace ${dias} día${dias > 1 ? 's' : ''}`;
    
    return fechaPost.toLocaleDateString('es-ES', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
}

// Función para scroll suave a un elemento
function scrollSuave(elementoId) {
    const elemento = document.getElementById(elementoId);
    if (elemento) {
        elemento.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Auto-actualizar timestamps cada minuto
setInterval(function() {
    const timestamps = document.querySelectorAll('[data-timestamp]');
    timestamps.forEach(element => {
        const fecha = element.getAttribute('data-timestamp');
        element.textContent = formatearFechaRelativa(fecha);
    });
}, 60000);

// Lazy loading para imágenes
document.addEventListener('DOMContentLoaded', function() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
});

// Prevenir doble envío del formulario
let formularioEnviado = false;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCrearPost');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (formularioEnviado) {
                e.preventDefault();
                return false;
            }
            formularioEnviado = true;
        });
    }
});

// Función para mostrar notificaciones toast
function mostrarNotificacion(mensaje, tipo = 'info') {
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion-toast notificacion-${tipo}`;
    notificacion.textContent = mensaje;
    
    notificacion.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${tipo === 'success' ? '#27ae60' : tipo === 'error' ? '#e74c3c' : '#3498db'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        font-weight: 500;
    `;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notificacion.remove(), 300);
    }, 3000);
}

// Agregar estilos de animación
const estilosAnimacion = document.createElement('style');
estilosAnimacion.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(estilosAnimacion);

// Funciones globales para comentarios
function toggleComentarios(postId) {
    const seccionComentarios = document.getElementById('comentarios-' + postId);
    
    if (seccionComentarios) {
        if (seccionComentarios.style.display === 'none' || seccionComentarios.style.display === '') {
            seccionComentarios.style.display = 'block';
            setTimeout(() => {
                seccionComentarios.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
            const textarea = seccionComentarios.querySelector('.comentario-input');
            if (textarea) {
                setTimeout(() => textarea.focus(), 300);
            }
        } else {
            seccionComentarios.style.display = 'none';
        }
    }
}

function enviarComentario(textarea) {
    const contenido = textarea.value.trim();
    const postId = textarea.getAttribute('data-post-id');
    
    if (!contenido) {
        alert('El comentario no puede estar vacio');
        return;
    }
    
    if (contenido.length < 2) {
        alert('El comentario debe tener al menos 2 caracteres');
        return;
    }
    
    textarea.disabled = true;
    const botonEnviar = textarea.nextElementSibling;
    const textoOriginalBoton = botonEnviar.innerHTML;
    botonEnviar.innerHTML = '...';
    botonEnviar.disabled = true;
    
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('contenido', contenido);
    
    fetch('ajax/crear_comentario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            textarea.value = '';
            location.reload();
        } else {
            alert(data.message || 'Error al publicar comentario');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexion al publicar comentario');
    })
    .finally(() => {
        textarea.disabled = false;
        botonEnviar.innerHTML = textoOriginalBoton;
        botonEnviar.disabled = false;
    });
}
// ===== FUNCIONES DE EVENTOS =====

// Unirse o salirse de un evento
function toggleParticipacion(boton) {
    const eventoId = boton.getAttribute('data-evento-id');
    const estaParticipando = boton.classList.contains('btn-joined');
    
    if (boton.disabled) return;
    boton.disabled = true;
    
    const formData = new FormData();
    formData.append('evento_id', eventoId);
    formData.append('accion', estaParticipando ? 'salir' : 'unirse');
    
    fetch('ajax/toggle_participacion_evento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (estaParticipando) {
                boton.classList.remove('btn-joined');
                boton.textContent = 'Unirse al Evento';
            } else {
                boton.classList.add('btn-joined');
                boton.textContent = 'Participando';
            }
            
            const eventoCard = boton.closest('.evento-card');
            const detalles = eventoCard.querySelector('.evento-details');
            const match = detalles.textContent.match(/(\d+)\s+asistiran/);
            if (match) {
                const nuevoTexto = detalles.textContent.replace(match[1], data.total_participantes);
                detalles.textContent = nuevoTexto;
            }
            
            boton.style.transform = 'scale(1.05)';
            setTimeout(() => {
                boton.style.transform = 'scale(1)';
            }, 200);
            
            alert(data.message);
        } else {
            alert(data.message || 'Error al procesar la participacion');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexion al procesar la participacion');
    })
    .finally(() => {
        boton.disabled = false;
    });
}

// ===== MODAL CREAR EVENTO =====

function mostrarModalCrearEvento() {
    const modal = document.getElementById('modalCrearEvento');
    modal.style.display = 'block';
    
    // Establecer fecha mínima como hoy
    const fechaInput = document.getElementById('fecha_evento');
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.min = hoy;
}

function cerrarModalCrearEvento() {
    const modal = document.getElementById('modalCrearEvento');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Limpiar formulario
    document.getElementById('formCrearEvento').reset();
}

function enviarEvento(event) {
    event.preventDefault();
    
    const form = document.getElementById('formCrearEvento');
    const btnSubmit = form.querySelector('.btn-crear-evento');
    const textoOriginal = btnSubmit.textContent;
    
    // Validar campos
    const titulo = document.getElementById('titulo_evento').value.trim();
    const descripcion = document.getElementById('descripcion_evento').value.trim();
    const fecha = document.getElementById('fecha_evento').value;
    const hora = document.getElementById('hora_evento').value;
    const ubicacion = document.getElementById('ubicacion_evento').value.trim();
    
    if (!titulo || !descripcion || !fecha || !hora || !ubicacion) {
        alert('Por favor completa todos los campos obligatorios');
        return false;
    }
    
    // Validar que la fecha no sea pasada
    const fechaEvento = new Date(fecha + ' ' + hora);
    const ahora = new Date();
    
    if (fechaEvento < ahora) {
        alert('La fecha y hora del evento no puede ser en el pasado');
        return false;
    }
    
    // Deshabilitar botón
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Creando...';
    
    // Enviar datos
    const formData = new FormData(form);
    
    fetch('ajax/crear_evento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Evento creado exitosamente');
            cerrarModalCrearEvento();
            // Recargar página para mostrar el nuevo evento
            location.reload();
        } else {
            alert(data.message || 'Error al crear el evento');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al crear el evento');
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.textContent = textoOriginal;
    });
    
    return false;
}

// Cerrar modal al hacer clic fuera
window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalCrearEvento');
    if (event.target === modal) {
        cerrarModalCrearEvento();
    }
});

// Navegación entre secciones
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.section-btn').forEach(button => {
        button.addEventListener('click', function() {
            const section = this.dataset.section;
            
            // Remover clase activa
            document.querySelectorAll('.section-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Ocultar todas las secciones
            document.querySelectorAll('.feed-section, .eventos-section, .grupos-section').forEach(sec => sec.style.display = 'none');
            
            // Mostrar sección seleccionada
            document.getElementById(section + 'Section').style.display = 'block';
        });
    });

    // Auto-ocultar mensajes de éxito/error
    setTimeout(function() {
        const mensajes = document.querySelectorAll('.mensaje-exito, .mensaje-error');
        mensajes.forEach(mensaje => {
            mensaje.style.opacity = '0';
            setTimeout(() => mensaje.remove(), 300);
        });
    }, 3000);
});

// ===== FUNCIONES DE GRUPOS =====
function toggleMiembroGrupo(boton) {
    const grupoId = boton.getAttribute('data-grupo-id');
    const esMiembro = boton.classList.contains('btn-joined');
    
    if (boton.disabled) return;
    boton.disabled = true;
    
    const formData = new FormData();
    formData.append('grupo_id', grupoId);
    formData.append('accion', esMiembro ? 'salir' : 'unirse');
    
    fetch('ajax/toggle_miembro_grupo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (esMiembro) {
                boton.classList.remove('btn-joined');
                boton.textContent = 'Unirse';
            } else {
                boton.classList.add('btn-joined');
                boton.textContent = 'Miembro';
            }
            
            const grupoCard = boton.closest('.grupo-card');
            const miembrosP = grupoCard.querySelector('.grupo-info p');
            miembrosP.textContent = data.total_miembros + ' miembros';
            
            alert(data.message);
        } else {
            alert(data.message || 'Error al procesar');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexion');
    })
    .finally(() => {
        boton.disabled = false;
    });
}

function mostrarModalCrearGrupo() {
    const modal = document.getElementById('modalCrearGrupo');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModalCrearGrupo() {
    const modal = document.getElementById('modalCrearGrupo');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('formCrearGrupo').reset();
}

function enviarGrupo(event) {
    event.preventDefault();
    
    const form = document.getElementById('formCrearGrupo');
    const btnSubmit = form.querySelector('.btn-crear-evento');
    
    const formData = new FormData(form);
    
    fetch('ajax/crear_grupo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Grupo creado exitosamente');
            location.reload();
        } else {
            alert(data.message || 'Error al crear el grupo');
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Crear Grupo';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexion');
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Crear Grupo';
    });
    
    return false;
}
// Función para eliminar un comentario
function eliminarComentario(comentarioId, elemento) {
    if (!confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('comentario_id', comentarioId);
    
    fetch('ajax/eliminar_comentario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            mostrarNotificacion(data.message, 'success');
            
            // Eliminar el elemento del DOM con animación
            const comentarioItem = elemento.closest('.comentario-item');
            comentarioItem.style.transition = 'opacity 0.3s ease';
            comentarioItem.style.opacity = '0';
            
            setTimeout(() => {
                comentarioItem.remove();
                
                // Actualizar el contador de comentarios en el botón del post
                const postId = elemento.getAttribute('data-post-id');
                const btnComentarios = document.querySelector(`button[onclick="toggleComentarios(${postId})"]`);
                if (btnComentarios) {
                    btnComentarios.innerHTML = `💬 ${data.nuevo_conteo}`;
                }
                
                // Si no hay más comentarios, mostrar mensaje
                const comentariosLista = document.querySelector(`#comentarios-${postId} .comentarios-lista`);
                if (comentariosLista && comentariosLista.children.length === 0) {
                    comentariosLista.innerHTML = '<p class="sin-comentarios">No hay comentarios aún. ¡Sé el primero en comentar!</p>';
                }
            }, 300);
            
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar el comentario', 'error');
    });
}

// Función auxiliar para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo) {
    const notificacion = document.createElement('div');
    notificacion.className = tipo === 'success' ? 'mensaje-exito' : 'mensaje-error';
    notificacion.textContent = mensaje;
    notificacion.style.position = 'fixed';
    notificacion.style.top = '20px';
    notificacion.style.left = '50%';
    notificacion.style.transform = 'translateX(-50%)';
    notificacion.style.zIndex = '10000';
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.style.transition = 'opacity 0.3s ease';
        notificacion.style.opacity = '0';
        setTimeout(() => notificacion.remove(), 300);
    }, 3000);
}
// Función para eliminar un post completo
function eliminarPost(postId, elemento) {
    // Mostrar modal de confirmación personalizado
    if (!confirm('⚠️ ¿Estás seguro de que deseas eliminar este post?\n\nEsta acción eliminará:\n• El post completo\n• Todos los comentarios\n• Todos los likes\n• Las imágenes asociadas\n\nEsta acción NO se puede deshacer.')) {
        return;
    }
    
    // Deshabilitar el botón mientras se procesa
    const btnEliminar = elemento;
    const textoOriginal = btnEliminar.innerHTML;
    btnEliminar.disabled = true;
    btnEliminar.innerHTML = '⏳ Eliminando...';
    btnEliminar.style.opacity = '0.6';
    
    const formData = new FormData();
    formData.append('post_id', postId);
    
    fetch('ajax/eliminar_post.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            mostrarNotificacion(data.message, 'success');
            
            // Eliminar el post del DOM con animación
            const postCard = elemento.closest('.post-card');
            postCard.style.transition = 'all 0.4s ease';
            postCard.style.transform = 'scale(0.95)';
            postCard.style.opacity = '0';
            
            setTimeout(() => {
                postCard.style.height = postCard.offsetHeight + 'px';
                postCard.style.overflow = 'hidden';
                
                setTimeout(() => {
                    postCard.style.height = '0';
                    postCard.style.margin = '0';
                    postCard.style.padding = '0';
                    
                    setTimeout(() => {
                        postCard.remove();
                        
                        // Verificar si quedan posts
                        const postsContainer = document.querySelector('.posts-container');
                        if (postsContainer && postsContainer.querySelectorAll('.post-card').length === 0) {
                            postsContainer.innerHTML = `
                                <div class="mensaje-sin-posts" style="text-align: center; padding: 40px; color: #95a5a6;">
                                    <p style="font-size: 18px; margin-bottom: 10px;">📭</p>
                                    <p>No hay posts para mostrar</p>
                                    <p style="font-size: 14px; margin-top: 10px;">¡Sé el primero en compartir algo!</p>
                                </div>
                            `;
                        }
                    }, 400);
                }, 50);
            }, 400);
            
        } else {
            // Mostrar error y restaurar botón
            mostrarNotificacion(data.message, 'error');
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = textoOriginal;
            btnEliminar.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar el post', 'error');
        btnEliminar.disabled = false;
        btnEliminar.innerHTML = textoOriginal;
        btnEliminar.style.opacity = '1';
    });
}

// Función para mostrar/ocultar menú de opciones del post
function toggleMenuPost(postId) {
    const menu = document.getElementById(`menu-post-${postId}`);
    
    // Cerrar otros menús abiertos
    document.querySelectorAll('.post-menu-opciones').forEach(m => {
        if (m.id !== `menu-post-${postId}`) {
            m.style.display = 'none';
        }
    });
    
    // Toggle del menú actual
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

// Cerrar menús al hacer clic fuera
document.addEventListener('click', function(event) {
    if (!event.target.closest('.post-menu-container')) {
        document.querySelectorAll('.post-menu-opciones').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Función auxiliar para mostrar notificaciones (si no existe ya)
if (typeof mostrarNotificacion !== 'function') {
    function mostrarNotificacion(mensaje, tipo) {
        const notificacion = document.createElement('div');
        notificacion.className = tipo === 'success' ? 'mensaje-exito' : 'mensaje-error';
        notificacion.textContent = mensaje;
        notificacion.style.position = 'fixed';
        notificacion.style.top = '20px';
        notificacion.style.left = '50%';
        notificacion.style.transform = 'translateX(-50%)';
        notificacion.style.zIndex = '10000';
        notificacion.style.minWidth = '300px';
        notificacion.style.textAlign = 'center';
        
        document.body.appendChild(notificacion);
        
        setTimeout(() => {
            notificacion.style.transition = 'opacity 0.3s ease';
            notificacion.style.opacity = '0';
            setTimeout(() => notificacion.remove(), 300);
        }, 3000);
    }
}
// Función para eliminar un evento
function eliminarEvento(eventoId, elemento) {
    // Mostrar modal de confirmación personalizado
    if (!confirm('⚠️ ¿Estás seguro de que deseas eliminar este evento?\n\nEsta acción eliminará:\n• El evento completo\n• Todas las inscripciones de asistentes\n\nEsta acción NO se puede deshacer.')) {
        return;
    }
    
    // Deshabilitar el botón mientras se procesa
    const btnEliminar = elemento;
    const textoOriginal = btnEliminar.innerHTML;
    btnEliminar.disabled = true;
    btnEliminar.innerHTML = '⏳ Eliminando...';
    btnEliminar.style.opacity = '0.6';
    
    const formData = new FormData();
    formData.append('evento_id', eventoId);
    
    fetch('ajax/eliminar_evento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito con información
            let mensaje = data.message;
            if (data.asistentes_afectados > 0) {
                mensaje += ` (${data.asistentes_afectados} asistente${data.asistentes_afectados > 1 ? 's' : ''} notificado${data.asistentes_afectados > 1 ? 's' : ''})`;
            }
            mostrarNotificacion(mensaje, 'success');
            
            // Eliminar el evento del DOM con animación
            const eventoCard = elemento.closest('.evento-card');
            eventoCard.style.transition = 'all 0.4s ease';
            eventoCard.style.transform = 'scale(0.95)';
            eventoCard.style.opacity = '0';
            
            setTimeout(() => {
                eventoCard.style.height = eventoCard.offsetHeight + 'px';
                eventoCard.style.overflow = 'hidden';
                
                setTimeout(() => {
                    eventoCard.style.height = '0';
                    eventoCard.style.margin = '0';
                    eventoCard.style.padding = '0';
                    
                    setTimeout(() => {
                        eventoCard.remove();
                        
                        // Verificar si quedan eventos
                        const eventosContainer = document.querySelector('.eventos-list');
                        if (eventosContainer && eventosContainer.querySelectorAll('.evento-card').length === 0) {
                            eventosContainer.innerHTML = `
                                <div class="mensaje-sin-eventos" style="text-align: center; padding: 40px; color: #95a5a6;">
                                    <p style="font-size: 18px; margin-bottom: 10px;">📅</p>
                                    <p>No hay eventos próximos</p>
                                    <p style="font-size: 14px; margin-top: 10px;">¡Sé el primero en crear uno!</p>
                                </div>
                            `;
                        }
                    }, 400);
                }, 50);
            }, 400);
            
        } else {
            // Mostrar error y restaurar botón
            mostrarNotificacion(data.message, 'error');
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = textoOriginal;
            btnEliminar.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar el evento', 'error');
        btnEliminar.disabled = false;
        btnEliminar.innerHTML = textoOriginal;
        btnEliminar.style.opacity = '1';
    });
}

// Función para mostrar/ocultar menú de opciones del evento
function toggleMenuEvento(eventoId) {
    const menu = document.getElementById(`menu-evento-${eventoId}`);
    
    // Cerrar otros menús abiertos
    document.querySelectorAll('.evento-menu-opciones').forEach(m => {
        if (m.id !== `menu-evento-${eventoId}`) {
            m.style.display = 'none';
        }
    });
    
    // Toggle del menú actual
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

// Cerrar menús de eventos al hacer clic fuera
document.addEventListener('click', function(event) {
    if (!event.target.closest('.evento-menu-container')) {
        document.querySelectorAll('.evento-menu-opciones').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});
// Función para eliminar un grupo
function eliminarGrupo(grupoId, elemento) {
    // Mostrar modal de confirmación personalizado
    if (!confirm('⚠️ ¿Estás seguro de que deseas eliminar este grupo?\n\nEsta acción eliminará:\n• El grupo completo\n• Todas las membresías de usuarios\n\nEsta acción NO se puede deshacer.')) {
        return;
    }
    
    // Deshabilitar el botón mientras se procesa
    const btnEliminar = elemento;
    const textoOriginal = btnEliminar.innerHTML;
    btnEliminar.disabled = true;
    btnEliminar.innerHTML = '⏳ Eliminando...';
    btnEliminar.style.opacity = '0.6';
    
    const formData = new FormData();
    formData.append('grupo_id', grupoId);
    
    fetch('ajax/eliminar_grupo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito con información
            let mensaje = data.message;
            if (data.miembros_afectados > 0) {
                mensaje += ` (${data.miembros_afectados} miembro${data.miembros_afectados > 1 ? 's' : ''} notificado${data.miembros_afectados > 1 ? 's' : ''})`;
            }
            mostrarNotificacion(mensaje, 'success');
            
            // Eliminar el grupo del DOM con animación
            const grupoCard = elemento.closest('.grupo-card');
            grupoCard.style.transition = 'all 0.4s ease';
            grupoCard.style.transform = 'scale(0.95)';
            grupoCard.style.opacity = '0';
            
            setTimeout(() => {
                grupoCard.style.height = grupoCard.offsetHeight + 'px';
                grupoCard.style.overflow = 'hidden';
                
                setTimeout(() => {
                    grupoCard.style.height = '0';
                    grupoCard.style.margin = '0';
                    grupoCard.style.padding = '0';
                    
                    setTimeout(() => {
                        grupoCard.remove();
                        
                        // Verificar si quedan grupos
                        const gruposContainer = document.querySelector('.grupos-list');
                        if (gruposContainer && gruposContainer.querySelectorAll('.grupo-card').length === 0) {
                            gruposContainer.innerHTML = `
                                <div class="mensaje-sin-grupos" style="text-align: center; padding: 40px; color: #95a5a6;">
                                    <p style="font-size: 18px; margin-bottom: 10px;">👥</p>
                                    <p>No hay grupos disponibles</p>
                                    <p style="font-size: 14px; margin-top: 10px;">¡Sé el primero en crear uno!</p>
                                </div>
                            `;
                        }
                    }, 400);
                }, 50);
            }, 400);
            
        } else {
            // Mostrar error y restaurar botón
            mostrarNotificacion(data.message, 'error');
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = textoOriginal;
            btnEliminar.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar el grupo', 'error');
        btnEliminar.disabled = false;
        btnEliminar.innerHTML = textoOriginal;
        btnEliminar.style.opacity = '1';
    });
}

// Función para mostrar/ocultar menú de opciones del grupo
function toggleMenuGrupo(grupoId) {
    const menu = document.getElementById(`menu-grupo-${grupoId}`);
    
    // Cerrar otros menús abiertos
    document.querySelectorAll('.grupo-menu-opciones').forEach(m => {
        if (m.id !== `menu-grupo-${grupoId}`) {
            m.style.display = 'none';
        }
    });
    
    // Toggle del menú actual
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

// Cerrar menús de grupos al hacer clic fuera
document.addEventListener('click', function(event) {
    if (!event.target.closest('.grupo-menu-container')) {
        document.querySelectorAll('.grupo-menu-opciones').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});