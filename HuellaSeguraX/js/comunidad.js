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

console.log('✓ Funcionalidades de comunidad cargadas correctamente');