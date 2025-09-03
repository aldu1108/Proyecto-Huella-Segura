// Auth Pages JavaScript - Enhanced UX

// DOM Ready
document.addEventListener('DOMContentLoaded', function () {
    initializeAuthPage();
});

// Initialize auth page functionality
function initializeAuthPage() {
    setupFormValidation();
    setupPasswordStrength();
    setupPasswordToggle();
    setupInputAnimations();
    setupFormSubmission();
    setupKeyboardNavigation();
    setupAccessibility();
}

// Form Validation
function setupFormValidation() {
    const forms = document.querySelectorAll('.auth-form');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input');

        inputs.forEach(input => {
            // Real-time validation
            input.addEventListener('input', function () {
                validateInput(this);
            });

            // Blur validation
            input.addEventListener('blur', function () {
                validateInput(this);
            });

            // Focus enhancement
            input.addEventListener('focus', function () {
                clearInputError(this);
            });
        });

        // Form submission validation
        form.addEventListener('submit', function (e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showFormErrors(this);
            }
        });
    });
}

// Individual input validation
function validateInput(input) {
    const value = input.value.trim();
    const type = input.type;
    const name = input.name;
    let isValid = true;
    let message = '';

    // Required field validation
    if (input.hasAttribute('required') && !value) {
        isValid = false;
        message = 'Este campo es obligatorio';
    }

    // Email validation
    else if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Por favor ingresa un email válido';
        }
    }

    // Password validation
    else if (type === 'password' && name === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            message = 'La contraseña debe tener al menos 6 caracteres';
        }
    }

    // Confirm password validation
    else if (name === 'confirm_password' && value) {
        const passwordInput = document.querySelector('input[name="password"]');
        if (passwordInput && value !== passwordInput.value) {
            isValid = false;
            message = 'Las contraseñas no coinciden';
        }
    }

    // Name validation
    else if (name === 'name' && value) {
        if (value.length < 2) {
            isValid = false;
            message = 'El nombre debe tener al menos 2 caracteres';
        }
    }

    updateInputValidation(input, isValid, message);
    return isValid;
}

// Update input validation state
function updateInputValidation(input, isValid, message) {
    const container = input.closest('.input-container') || input.parentElement;
    const existingError = container.querySelector('.input-error');

    // Remove existing error
    if (existingError) {
        existingError.remove();
    }

    // Update input classes
    input.classList.toggle('error', !isValid);
    input.classList.toggle('valid', isValid && input.value.trim());

    // Add error message if invalid
    if (!isValid && message) {
        const errorElement = document.createElement('div');
        errorElement.className = 'input-error';
        errorElement.textContent = message;
        container.appendChild(errorElement);

        // Announce error to screen readers
        errorElement.setAttribute('aria-live', 'polite');
        input.setAttribute('aria-describedby', 'error-' + input.name);
        errorElement.id = 'error-' + input.name;
    } else {
        input.removeAttribute('aria-describedby');
    }
}

// Clear input error state
function clearInputError(input) {
    const container = input.closest('.input-container') || input.parentElement;
    const existingError = container.querySelector('.input-error');

    if (existingError) {
        existingError.remove();
    }

    input.classList.remove('error');
    input.removeAttribute('aria-describedby');
}

// Validate entire form
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], input[type="email"], input[name="confirm_password"]');
    let isFormValid = true;

    inputs.forEach(input => {
        if (!validateInput(input)) {
            isFormValid = false;
        }
    });

    return isFormValid;
}

// Show form errors
function showFormErrors(form) {
    const firstError = form.querySelector('.error');
    if (firstError) {
        firstError.focus();
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Password Strength Indicator
function setupPasswordStrength() {
    const passwordInputs = document.querySelectorAll('input[name="password"]');

    passwordInputs.forEach(input => {
        if (input.closest('form').querySelector('.password-strength')) {
            input.addEventListener('input', function () {
                updatePasswordStrength(this);
            });
        }
    });
}

function updatePasswordStrength(input) {
    const password = input.value;
    const strengthContainer = input.closest('form').querySelector('.password-strength');

    if (!strengthContainer) return;

    const strengthBar = strengthContainer.querySelector('.strength-fill');
    const strengthText = strengthContainer.querySelector('.strength-text');

    let strength = 0;
    let text = '';
    let color = '';

    if (password.length === 0) {
        text = 'Ingresa tu contraseña';
        color = '#ddd';
        strength = 0;
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

        // Length bonus
        if (password.length >= 8) score += 1;
        if (password.length >= 12) score += 1;

        // Character variety
        if (/[a-z]/.test(password)) score += 1;
        if (/[A-Z]/.test(password)) score += 1;
        if (/[0-9]/.test(password)) score += 1;
        if (/[^A-Za-z0-9]/.test(password)) score += 1;

        // Common patterns (reduce score)
        if (/(.)\1{2,}/.test(password)) score -= 1; // Repeated characters
        if (/123|abc|qwe/i.test(password)) score -= 1; // Common sequences

        if (score <= 2) {
            strength = 50;
            text = 'Regular';
            color = '#fdcb6e';
        } else if (score <= 4) {
            strength = 75;
            text = 'Fuerte';
            color = '#6c5ce7';
        } else {
            strength = 100;
            text = 'Muy fuerte';
            color = '#00b894';
        }
    }

    // Update UI
    strengthBar.style.width = strength + '%';
    strengthBar.style.backgroundColor = color;
    strengthText.textContent = text;
    strengthText.style.color = color;

    // Add accessibility
    input.setAttribute('aria-describedby', 'password-strength-text');
    strengthText.setAttribute('aria-live', 'polite');
    strengthText.id = 'password-strength-text';
}

// Password Toggle Functionality
function setupPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.password-toggle');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            togglePasswordVisibility(this);
        });
    });
}

function togglePasswordVisibility(button) {
    const input = button.previousElementSibling;
    const eyeIcon = button.querySelector('.eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        button.setAttribute('aria-label', 'Ocultar contraseña');
        eyeIcon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
    } else {
        input.type = 'password';
        button.setAttribute('aria-label', 'Mostrar contraseña');
        eyeIcon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
    }
}

// Input Animations and Effects
function setupInputAnimations() {
    const inputs = document.querySelectorAll('.input-container input');

    inputs.forEach(input => {
        // Floating label effect
        setupFloatingLabel(input);

        // Ripple effect on focus
        input.addEventListener('focus', function () {
            createInputRipple(this);
        });

        // Icon color change
        input.addEventListener('focus', function () {
            animateInputIcon(this, true);
        });

        input.addEventListener('blur', function () {
            animateInputIcon(this, false);
        });
    });
}

function setupFloatingLabel(input) {
    const container = input.closest('.input-container');
    if (!container || container.querySelector('.floating-label')) return;

    const placeholder = input.placeholder;
    if (!placeholder) return;

    // Create floating label
    const label = document.createElement('label');
    label.className = 'floating-label';
    label.textContent = placeholder;
    label.setAttribute('for', input.id || input.name);

    container.appendChild(label);

    // Update label state
    function updateLabelState() {
        const hasValue = input.value.trim().length > 0;
        const isFocused = document.activeElement === input;

        label.classList.toggle('active', hasValue || isFocused);
    }

    input.addEventListener('focus', updateLabelState);
    input.addEventListener('blur', updateLabelState);
    input.addEventListener('input', updateLabelState);

    updateLabelState();
}

function createInputRipple(input) {
    const container = input.closest('.input-container');
    const ripple = document.createElement('span');

    ripple.className = 'input-ripple';
    container.appendChild(ripple);

    // Animate ripple
    setTimeout(() => {
        ripple.classList.add('active');
    }, 10);

    // Remove ripple after animation
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

function animateInputIcon(input, isFocused) {
    const icon = input.previousElementSibling;
    if (!icon || !icon.classList.contains('input-icon')) return;

    if (isFocused) {
        icon.style.transform = 'scale(1.1)';
        icon.style.color = '#f3c623';
    } else {
        icon.style.transform = 'scale(1)';
        icon.style.color = '#6c757d';
    }
}

// Form Submission Enhancement
function setupFormSubmission() {
    const forms = document.querySelectorAll('.auth-form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitButton = this.querySelector('.btn-primary');
            if (submitButton) {
                handleFormSubmission(submitButton);
            }
        });
    });
}

function handleFormSubmission(button) {
    const buttonText = button.querySelector('span');
    const loader = button.querySelector('.btn-loader');

    if (!buttonText || !loader) return;

    // Show loading state
    button.disabled = true;
    buttonText.style.opacity = '0';
    loader.style.display = 'block';

    // Add pulse animation to button
    button.classList.add('loading');

    // Simulate processing time (remove in production)
    setTimeout(() => {
        button.classList.remove('loading');
    }, 2000);
}

// Keyboard Navigation
function setupKeyboardNavigation() {
    const forms = document.querySelectorAll('.auth-form');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, button');

        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function (e) {
                handleKeyboardNavigation(e, inputs, index);
            });
        });
    });
}

function handleKeyboardNavigation(event, inputs, currentIndex) {
    const key = event.key;

    if (key === 'ArrowDown' || (key === 'Tab' && !event.shiftKey)) {
        // Move to next input
        const nextIndex = (currentIndex + 1) % inputs.length;
        inputs[nextIndex].focus();
        event.preventDefault();
    } else if (key === 'ArrowUp' || (key === 'Tab' && event.shiftKey)) {
        // Move to previous input
        const prevIndex = currentIndex === 0 ? inputs.length - 1 : currentIndex - 1;
        inputs[prevIndex].focus();
        event.preventDefault();
    } else if (key === 'Enter' && event.target.type !== 'submit') {
        // Move to next input or submit
        const nextInput = inputs[currentIndex + 1];
        if (nextInput) {
            nextInput.focus();
            event.preventDefault();
        }
    }
}

// Accessibility Enhancements
function setupAccessibility() {
    // Add ARIA labels
    addAriaLabels();

    // Announce form errors
    announceErrors();

    // Add skip links
    addSkipLinks();

    // Enhance focus management
    enhanceFocusManagement();
}

function addAriaLabels() {
    const inputs = document.querySelectorAll('input');

    inputs.forEach(input => {
        if (!input.getAttribute('aria-label') && input.placeholder) {
            input.setAttribute('aria-label', input.placeholder);
        }

        if (input.hasAttribute('required')) {
            const label = input.getAttribute('aria-label') || input.placeholder || input.name;
            input.setAttribute('aria-label', label + ' (obligatorio)');
        }
    });

    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        button.setAttribute('aria-label', 'Mostrar contraseña');
        button.setAttribute('type', 'button');
    });
}

function announceErrors() {
    const errorMessages = document.querySelectorAll('.error-message, .success-message');

    errorMessages.forEach(message => {
        message.setAttribute('role', 'alert');
        message.setAttribute('aria-live', 'assertive');

        // Focus first input on error
        if (message.classList.contains('error-message')) {
            const firstInput = document.querySelector('input');
            if (firstInput) {
                setTimeout(() => {
                    firstInput.focus();
                }, 100);
            }
        }
    });
}

function addSkipLinks() {
    const form = document.querySelector('.auth-form');
    if (!form) return;

    const skipLink = document.createElement('a');
    skipLink.href = '#main-form';
    skipLink.className = 'skip-link sr-only';
    skipLink.textContent = 'Saltar al formulario principal';

    form.id = 'main-form';
    document.body.insertBefore(skipLink, document.body.firstChild);

    skipLink.addEventListener('focus', function () {
        this.classList.remove('sr-only');
    });

    skipLink.addEventListener('blur', function () {
        this.classList.add('sr-only');
    });
}

function enhanceFocusManagement() {
    // Focus trap for modals/forms
    const authContainer = document.querySelector('.auth-container');
    if (!authContainer) return;

    const focusableElements = authContainer.querySelectorAll(
        'input, button, a[href], select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    // Focus first input on page load
    if (firstFocusable) {
        setTimeout(() => {
            firstFocusable.focus();
        }, 100);
    }

    // Trap focus within form
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    lastFocusable.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    firstFocusable.focus();
                    e.preventDefault();
                }
            }
        }
    });
}

// Utility Functions
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Add to DOM
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Demo Functions
function fillDemoCredentials() {
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');

    if (emailInput && passwordInput) {
        // Animate credential filling
        animateTyping(emailInput, 'demo@petcare.com', () => {
            animateTyping(passwordInput, 'demo123', () => {
                showNotification('Credenciales de demo cargadas', 'success');
            });
        });
    }
}

function animateTyping(input, text, callback) {
    input.value = '';
    input.focus();

    let index = 0;
    const typeInterval = setInterval(() => {
        input.value += text.charAt(index);
        index++;

        // Trigger input events for validation
        input.dispatchEvent(new Event('input', { bubbles: true }));

        if (index >= text.length) {
            clearInterval(typeInterval);
            if (callback) callback();
        }
    }, 100);
}

// Error Handling
window.addEventListener('error', function (e) {
    console.error('Auth Page Error:', e.error);
    showNotification('Ha ocurrido un error. Por favor, intenta nuevamente.', 'error');
});

// Performance Monitoring
if ('performance' in window) {
    window.addEventListener('load', function () {
        setTimeout(() => {
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            if (loadTime > 3000) {
                console.warn('Slow page load detected:', loadTime + 'ms');
            }
        }, 0);
    });
}

// Add custom CSS for new features
const additionalCSS = `
.input-error {
    color: #dc3545;
    font-size: 0.8rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.input-error::before {
    content: "⚠";
    font-size: 0.9rem;
}

.input-container input.valid {
    border-color: #28a745;
    background: #f8fff9;
}

.input-ripple {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(243, 198, 35, 0.1), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
    pointer-events: none;
}

.input-ripple.active {
    transform: translateX(100%);
}

.floating-label {
    position: absolute;
    top: 50%;
    left: 3rem;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 1rem;
    transition: all 0.3s ease;
    pointer-events: none;
    background: transparent;
    padding: 0 0.25rem;
}

.floating-label.active {
    top: 0;
    left: 2.75rem;
    transform: translateY(-50%);
    font-size: 0.75rem;
    color: #f3c623;
    background: white;
}

.btn.loading {
    animation: buttonPulse 1.5s ease-in-out infinite;
}

@keyframes buttonPulse {
    0% { box-shadow: 0 4px 20px rgba(238, 90, 36, 0.3); }
    50% { box-shadow: 0 4px 30px rgba(238, 90, 36, 0.5); }
    100% { box-shadow: 0 4px 20px rgba(238, 90, 36, 0.3); }
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    z-index: 9999;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
}

.notification.show {
    transform: translateX(0);
}

.notification-success { background: #28a745; }
.notification-error { background: #dc3545; }
.notification-warning { background: #ffc107; color: #212529; }
.notification-info { background: #17a2b8; }

.skip-link {
    position: absolute;
    top: -40px;
    left: 6px;
    background: #000;
    color: white;
    padding: 8px;
    border-radius: 4px;
    text-decoration: none;
    z-index: 10000;
}

.skip-link:focus {
    top: 6px;
}
`;

// Add styles to document
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalCSS;
document.head.appendChild(styleSheet);