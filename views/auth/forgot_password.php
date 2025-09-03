<?php
session_start();
/* require_once '../../config.php'; */                                                                  /* PENDIENTE A HACER */

$message = '';
$message_type = '';

// Procesar solicitud de recuperación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $message = 'Por favor ingresa tu correo electrónico';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Por favor ingresa un email válido';
        $message_type = 'error';
    } else {
        // Aquí iría la lógica para enviar email de recuperación
        // Por ahora simulamos el envío exitoso
        $message = 'Se ha enviado un enlace de recuperación a tu correo electrónico';
        $message_type = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - PetCare</title>
    <link rel="stylesheet" href="../../frontend/css/auth.css">
    <link rel="stylesheet" href="../../frontend/css/global.css">
</head>

<body class="auth-body">
    <div class="auth-container">
        <!-- Header Section -->
        <div class="auth-header">
            <div class="logo-section">
                <div class="logo-icon">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        class="lock-icon">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <circle cx="12" cy="16" r="1"></circle>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1 class="logo-title">Recuperar Contraseña</h1>
                <p class="logo-subtitle">Te ayudamos a recuperar tu cuenta</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="auth-form-container">
            <div class="form-header">
                <h2>¿Olvidaste tu contraseña?</h2>
                <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña</p>
            </div>

            <?php if ($message): ?>
                <div class="<?php echo $message_type; ?>-message">
                    <?php if ($message_type === 'error'): ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                        </svg>
                    <?php else: ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                        </svg>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($message_type !== 'success'): ?>
                <form class="auth-form" method="POST" action="">
                    <div class="form-group">
                        <div class="input-container">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input type="email" name="email" placeholder="Correo electrónico" required autocomplete="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <span>Enviar Enlace de Recuperación</span>
                        <div class="btn-loader" style="display: none;"></div>
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-actions">
                <a href="login.php" class="back-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    Volver al inicio de sesión
                </a>
            </div>
        </div>

        <!-- Security Tips Section -->
        <div class="security-tips">
            <div class="tips-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <h3>Consejos de seguridad</h3>
            </div>
            <ul class="tips-list">
                <li>• Revisa tu bandeja de spam si no recibes el correo</li>
                <li>• El enlace será válido por 24 horas</li>
                <li>• Si tienes problemas, contacta nuestro soporte</li>
            </ul>
        </div>
    </div>

    <script src="../../frontend/js/auth.js"></script>
    <script>
        // Form submission with loading state
        document.querySelector('.auth-form')?.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('.btn-primary');
            const btnText = submitBtn.querySelector('span');
            const btnLoader = submitBtn.querySelector('.btn-loader');

            btnText.style.opacity = '0';
            btnLoader.style.display = 'block';
            submitBtn.disabled = true;
        });

        // Auto focus on email input
        document.addEventListener('DOMContentLoaded', function () {
            const emailInput = document.querySelector('input[name="email"]');
            if (emailInput) {
                emailInput.focus();
            }
        });

        // Add animation to lock icon
        const lockIcon = document.querySelector('.lock-icon');
        if (lockIcon) {
            setInterval(() => {
                lockIcon.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    lockIcon.style.transform = 'scale(1)';
                }, 200);
            }, 3000);
        }
    </script>

    <style>
        .lock-icon {
            color: #8b4513;
            transition: transform 0.2s ease;
        }

        .security-tips {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid #e9ecef;
        }

        .tips-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: #495057;
        }

        .tips-header svg {
            color: #6c757d;
        }

        .tips-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .tips-list li {
            margin-bottom: 0.5rem;
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 1.5rem;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #f3c623;
        }

        .logo-icon {
            margin-bottom: 1rem;
        }

        .auth-header .logo-title {
            color: #8b4513;
            margin-bottom: 0.5rem;
        }

        .auth-header .logo-subtitle {
            color: #6d4c07;
        }

        /* Success state styling */
        .success-message {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
        }

        .success-message svg {
            color: #28a745;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .auth-container {
                padding: 1rem;
            }

            .security-tips {
                margin-top: 1rem;
                padding: 1rem;
            }

            .tips-list {
                font-size: 0.85rem;
            }
        }
    </style>
</body>

</html>