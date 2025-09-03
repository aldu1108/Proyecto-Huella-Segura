<?php
session_start();
/* require_once '../../config.php'; */                                                                     

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

$error_message = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Por favor completa todos los campos';
    } else {
        // Aquí iría la validación con base de datos
        // Por ahora simulamos la validación
        if ($email === 'demo@petcare.com' && $password === 'demo123') {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = 'Usuario Demo';
            header('Location: /dashboard');
            exit;
        } else {
            $error_message = 'Credenciales incorrectas';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - PetCare</title>
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
                <h2>Iniciar Sesión</h2>
                <p>Bienvenido de vuelta a PetCare</p>
            </div>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
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
                            autocomplete="current-password"
                            id="passwordInput"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <a href="forgot_password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>Iniciar Sesión</span>
                    <div class="btn-loader" style="display: none;"></div>
                </button>
            </form>

            <div class="divider">
                <span>o</span>
            </div>

            <button class="btn btn-demo" onclick="fillDemoCredentials()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Probar con Cuenta Demo
            </button>

            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="register.php">Registrarse</a></p>
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
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.querySelector('.password-toggle .eye-icon');
            
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

        function fillDemoCredentials() {
            document.querySelector('input[name="email"]').value = 'demo@petcare.com';
            document.querySelector('input[name="password"]').value = 'demo123';
        }

        // Form submission with loading state
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-primary');
            const btnText = submitBtn.querySelector('span');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            
            btnText.style.opacity = '0';
            btnLoader.style.display = 'block';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>