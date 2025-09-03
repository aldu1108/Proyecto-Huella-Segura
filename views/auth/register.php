<?php
session_start();
/* require_once '../../config.php'; */                                                                      /* PENDIENTE A HACER */

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

$error_message = '';
$success_message = '';

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Por favor completa todos los campos';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Por favor ingresa un email válido';
    } elseif (strlen($password) < 6) {
        $error_message = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Las contraseñas no coinciden';
    } else {
        // Aquí iría la inserción en base de datos
        // Por ahora simulamos el registro exitoso
        $success_message = 'Cuenta creada exitosamente. Puedes iniciar sesión ahora.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - PetCare</title>
    <link rel="stylesheet" href="../../frontend/css/auth.css">
    <link rel="stylesheet" href="../../frontend/css/global.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <!-- Header Section -->
        <div class="auth-header">
            <div class="logo-section">
                <div class="logo-paws">
                    <span class="paw">🐾</span>
                    <span class="paw">🐾</span>
                </div>
                <h1 class="logo-title">PetCare</h1>
                <p class="logo-subtitle">Tu compañero para el cuidado de mascotas</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="auth-form-container">
            <div class="form-header">
                <h2>Crear Cuenta</h2>
                <p>Únete a la comunidad de cuidadores de mascotas</p>
            </div>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <div class="input-container">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input 
                            type="text" 
                            name="name" 
                            placeholder="Nombre completo" 
                            required 
                            autocomplete="name"
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Correo electrónico" 
                            required 
                            autocomplete="email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <circle cx="12" cy="16" r="1"></circle>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Contraseña" 
                            required 
                            autocomplete="new-password"
                            id="passwordInput"
                            minlength="6"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('passwordInput')">
                            <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <span class="strength-text">Ingresa tu contraseña</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <circle cx="12" cy="16" r="1"></circle>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input 
                            type="password" 
                            name="confirm_password" 
                            placeholder="Confirmar contraseña" 
                            required 
                            autocomplete="new-password"
                            id="confirmPasswordInput"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPasswordInput')">
                            <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>Crear Cuenta</span>
                    <div class="btn-loader" style="display: none;"></div>
                </button>
            </form>

            <div class="divider">
                <span>o</span>
            </div>

            <button class="btn btn-demo" onclick="window.location.href='login.php'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Probar con Cuenta Demo
            </button>

            <div class="auth-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a></p>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="feature-item">
                <div class="feature-icon">📋</div>
                <span>Historial médico completo</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📅</div>
                <span>Recordatorios personalizados</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🔍</div>
                <span>Búsqueda de mascotas perdidas</span>
            </div>
        </div>
    </div>

    <script src="../../frontend/js/auth.js"></script>
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.nextElementSibling.querySelector('.eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        }

        // Password strength checker
        document.getElementById('passwordInput').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.querySelector('.strength-fill');
            const strengthText = document.querySelector('.strength-text');
            
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length === 0) {
                text = 'Ingresa tu contraseña';
                color = '#ddd';
            } else if (password.length < 6) {
                strength = 20;
                text = 'Muy débil';
                color = '#ff4757';
            } else if (password.length < 8) {
                strength = 40;
                text = 'Débil';
                color = '#ff7675';
            } else {
                let score = 0;
                if (/[a-z]/.test(password)) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/[0-9]/.test(password)) score++;
                if (/[^A-Za-z0-9]/.test(password)) score++;
                
                if (score < 2) {
                    strength = 50;
                    text = 'Regular';
                    color = '#fdcb6e';
                } else if (score < 3) {
                    strength = 75;
                    text = 'Fuerte';
                    color = '#6c5ce7';
                } else {
                    strength = 100;
                    text = 'Muy fuerte';
                    color = '#00b894';
                }
            }
            
            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        });

        // Form submission with loading state
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-primary');
            const btnText = submitBtn.querySelector('span');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            
            btnText.style.opacity = '0';
            btnLoader.style.display = 'block';
            submitBtn.disabled = true;
        });

        // Validate password match
        const confirmPassword = document.getElementById('confirmPasswordInput');
        confirmPassword.addEventListener('blur', function() {
            const password = document.getElementById('passwordInput').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.setCustomValidity('Las contraseñas no coinciden');
                this.classList.add('error');
            } else {
                this.setCustomValidity('');
                this.classList.remove('error');
            }
        });
    </script>
</body>
</html>