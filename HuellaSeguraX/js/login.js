function loginDemo() {
    // Crear usuario demo temporalmente
    fetch('crear-demo.php', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Enviar formulario con datos demo
                let form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                <input type="hidden" name="email" value="${data.email}">
                <input type="hidden" name="contraseña" value="${data.password}">
            `;
                document.body.appendChild(form);
                form.submit();
            }
        });
}