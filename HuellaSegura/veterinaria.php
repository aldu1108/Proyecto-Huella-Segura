<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Veterinaria - PetCare</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.4;
        }

        /* Header principal */
        .header-petcare {
            background-color: #F4D03F;
            padding: 12px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            z-index: 100;
        }

        .nav-principal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .btn-menu {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .btn-menu:hover {
            background: rgba(0,0,0,0.1);
        }

        .logo-container {
            flex: 1;
            text-align: center;
        }

        .logo {
            color: #D35400;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .nav-icons {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .btn-icon:hover {
            background: rgba(0,0,0,0.1);
        }

        /* Contenedor principal */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }

        /* Header del sistema veterinario */
        .vet-system-header {
            text-align: left;
            margin-bottom: 30px;
            padding: 0 10px;
        }

        .vet-system-title {
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }

        /* Grid de opciones principales */
        .vet-options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            max-width: 600px;
        }

        .vet-option-card {
            background: white;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .vet-option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .vet-option-icon {
            font-size: 32px;
            margin-bottom: 15px;
            display: block;
        }

        .vet-option-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .vet-option-subtitle {
            font-size: 12px;
            color: #666;
            font-weight: normal;
        }

        /* Sección de resumen de actividad */
        .activity-summary {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .activity-summary h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .activity-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .activity-stat {
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #D35400;
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }

        /* Sección de próximas citas */
        .upcoming-appointments {
            background: #FFF3CD;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 30px;
        }

        .upcoming-appointments h4 {
            font-size: 14px;
            color: #856404;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .appointment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(133, 100, 4, 0.1);
        }

        .appointment-item:last-child {
            border-bottom: none;
        }

        .appointment-time {
            font-size: 12px;
            color: #856404;
            font-weight: 500;
        }

        .appointment-info {
            font-size: 12px;
            color: #856404;
        }

        .appointment-status {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 8px;
            font-weight: 600;
        }

        .appointment-status.confirmed {
            background: #D4EDDA;
            color: #155724;
        }

        .appointment-status.pending {
            background: #FFF3CD;
            color: #856404;
        }

        /* Sección de acciones rápidas */
        .quick-actions {
            margin-bottom: 30px;
        }

        .quick-actions h4 {
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .action-btn {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
        }

        .action-btn.primary {
            background: #D35400;
            color: white;
        }

        .action-btn.primary:hover {
            background: #B8450E;
        }

        .action-btn.secondary {
            background: #007BFF;
            color: white;
        }

        .action-btn.secondary:hover {
            background: #0056B3;
        }

        /* Ver agenda completa link */
        .view-complete-agenda {
            text-align: center;
            margin: 15px 0;
        }

        .view-complete-agenda a {
            color: #D35400;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
        }

        .view-complete-agenda a:hover {
            text-decoration: underline;
        }

        /* Navegación inferior */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #D35400;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .nav-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .nav-btn:hover,
        .nav-btn.active {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .vet-options-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .vet-option-card {
                padding: 20px 15px;
            }

            .vet-option-icon {
                font-size: 28px;
                margin-bottom: 10px;
            }

            .activity-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .main-content {
                padding: 15px;
                padding-bottom: 100px;
            }
        }

        @media (max-width: 480px) {
            .activity-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .vet-option-title {
                font-size: 14px;
            }

            .vet-option-subtitle {
                font-size: 11px;
            }
        }

        /* Estilos para el menú lateral */
        .menu-lateral {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
            z-index: 1000;
            padding-top: 60px;
        }

        .menu-lateral.show {
            left: 0;
        }

        .menu-options {
            padding: 20px;
        }

        .menu-item {
            display: block;
            padding: 15px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .menu-item:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Header principal -->
    <header class="header-petcare">
        <nav class="nav-principal">
            <button class="btn-menu" id="menuHamburguesa">☰</button>
            <div class="logo-container">
                <h1 class="logo">PetCare 🐾</h1>
            </div>
            <div class="nav-icons">
                <button class="btn-icon">🔍</button>
                <button class="btn-icon">⚡</button>
            </div>
        </nav>
        
        <!-- Menú lateral -->
        <div class="menu-lateral" id="menuLateral">
            <div class="menu-options">
                <a href="#" class="menu-item">🏠 Inicio</a>
                <a href="#" class="menu-item">🐕 Mis Mascotas</a>
                <a href="#" class="menu-item">🔍 Mascotas Perdidas</a>
                <a href="#" class="menu-item">❤️ Adopciones</a>
                <a href="#" class="menu-item">👥 Comunidad</a>
                <a href="#" class="menu-item active">🏥 Veterinaria</a>
                <a href="#" class="menu-item">👤 Mi Perfil</a>
                <a href="#" class="menu-item">⚙️ Configuración</a>
                <a href="#" class="menu-item">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <div class="main-content">
        <!-- Header del sistema veterinario -->
        <section class="vet-system-header">
            <h2 class="vet-system-title">Sistema completo de gestión veterinaria</h2>
        </section>

        <!-- Grid de opciones principales -->
        <section class="vet-options-grid">
            <button class="vet-option-card" onclick="navigateToSection('pacientes')">
                <span class="vet-option-icon">👤</span>
                <div class="vet-option-title">Pacientes</div>
                <div class="vet-option-subtitle">Gestionar historiales</div>
            </button>

            <button class="vet-option-card" onclick="navigateToSection('agenda')">
                <span class="vet-option-icon">📅</span>
                <div class="vet-option-title">Agenda</div>
                <div class="vet-option-subtitle">Citas y turnos</div>
            </button>

            <button class="vet-option-card" onclick="navigateToSection('documentos')">
                <span class="vet-option-icon">📄</span>
                <div class="vet-option-title">Documentos</div>
                <div class="vet-option-subtitle">Archivos médicos</div>
            </button>

            <button class="vet-option-card" onclick="navigateToSection('historiales')">
                <span class="vet-option-icon">🩺</span>
                <div class="vet-option-title">Historiales</div>
                <div class="vet-option-subtitle">Registros médicos</div>
            </button>
        </section>

        <!-- Resumen de Actividad -->
        <section class="activity-summary">
            <h3>Resumen de Actividad</h3>
            <div class="activity-stats">
                <div class="activity-stat">
                    <span class="stat-number">5</span>
                    <span class="stat-label">Pacientes Activos</span>
                </div>
                <div class="activity-stat">
                    <span class="stat-number">4</span>
                    <span class="stat-label">Citas Hoy</span>
                </div>
                <div class="activity-stat">
                    <span class="stat-number">5</span>
                    <span class="stat-label">Consultas Total</span>
                </div>
                <div class="activity-stat">
                    <span class="stat-number">15</span>
                    <span class="stat-label">Documentos</span>
                </div>
            </div>
        </section>

        <!-- Próximas Citas de Hoy -->
        <section class="upcoming-appointments">
            <h4>📅 Próximas Citas de Hoy</h4>
            <div class="appointment-item">
                <span class="appointment-time">09:00 - Luna (Consulta General)</span>
                <span class="appointment-status confirmed">Confirmada</span>
            </div>
            <div class="appointment-item">
                <span class="appointment-time">10:30 - Whiskers (Vacunación)</span>
                <span class="appointment-status pending">Pendiente</span>
            </div>
            <div class="appointment-item">
                <span class="appointment-time">12:00 - Max (Post-operatorio)</span>
                <span class="appointment-status confirmed">Confirmada</span>
            </div>
            <div class="view-complete-agenda">
                <a href="#" onclick="showFullAgenda()">Ver agenda completa →</a>
            </div>
        </section>

        <!-- Acciones Rápidas -->
        <section class="quick-actions">
            <h4>Acciones Rápidas</h4>
            <div class="action-buttons">
                <button class="action-btn primary" onclick="registerNewConsultation()">
                    + Registrar Nueva Consulta
                </button>
                <button class="action-btn secondary" onclick="viewTodayAgenda()">
                    📅 Ver Agenda del Día
                </button>
            </div>
        </section>
    </div>

    <!-- Navegación inferior -->
    <nav class="bottom-nav">
        <button class="nav-btn">❤️</button>
        <button class="nav-btn">🔍</button>
        <button class="nav-btn active">🏠</button>
        <button class="nav-btn">👥</button>
        <button class="nav-btn">🏥</button>
    </nav>

    <script src="js/scripts.js">
        // Funcionalidad del menú hamburguesa
        document.addEventListener('DOMContentLoaded', function() {
            const menuHamburguesa = document.getElementById('menuHamburguesa');
            const menuLateral = document.getElementById('menuLateral');
            
            if (menuHamburguesa && menuLateral) {
                menuHamburguesa.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (menuLateral.classList.contains('show')) {
                        menuLateral.classList.remove('show');
                    } else {
                        menuLateral.classList.add('show');
                    }
                });

                // Cerrar menú al hacer clic fuera
                document.addEventListener('click', function(event) {
                    if (!menuHamburguesa.contains(event.target) && !menuLateral.contains(event.target)) {
                        menuLateral.classList.remove('show');
                    }
                });

                // Cerrar menú al hacer clic en un elemento del menú
                const menuItems = menuLateral.querySelectorAll('.menu-item');
                menuItems.forEach(item => {
                    item.addEventListener('click', () => {
                        menuLateral.classList.remove('show');
                    });
                });
            }

            // Animar números de estadísticas
            animateStatNumbers();
        });

        // Funciones de navegación
        function navigateToSection(section) {
            console.log(`Navegando a sección: ${section}`);
            
            // Aquí puedes agregar la lógica real de navegación
            switch(section) {
                case 'pacientes':
                    showMessage('Navegando a Pacientes', 'info');
                    break;
                case 'agenda':
                    showMessage('Navegando a Agenda', 'info');
                    break;
                case 'documentos':
                    showMessage('Navegando a Documentos', 'info');
                    break;
                case 'historiales':
                    showMessage('Navegando a Historiales', 'info');
                    break;
            }
        }

        function showFullAgenda() {
            console.log('Mostrando agenda completa');
            showMessage('Cargando agenda completa...', 'info');
        }

        function registerNewConsultation() {
            console.log('Registrando nueva consulta');
            showMessage('Abriendo formulario de nueva consulta', 'success');
        }

        function viewTodayAgenda() {
            console.log('Viendo agenda del día');
            showMessage('Cargando agenda del día...', 'info');
        }

        // Animar números de estadísticas
        function animateStatNumbers() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const finalNumber = parseInt(stat.textContent);
                let currentNumber = 0;
                const increment = finalNumber / 30;
                
                const animation = setInterval(() => {
                    currentNumber += increment;
                    if (currentNumber >= finalNumber) {
                        stat.textContent = finalNumber;
                        clearInterval(animation);
                    } else {
                        stat.textContent = Math.floor(currentNumber);
                    }
                }, 50);
            });
        }

        // Función para mostrar mensajes
        function showMessage(text, type = 'info') {
            const message = document.createElement('div');
            message.className = `message-toast message-${type}`;
            message.textContent = text;
            
            // Estilos del mensaje
            Object.assign(message.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                padding: '12px 20px',
                borderRadius: '8px',
                color: 'white',
                zIndex: '9999',
                maxWidth: '300px',
                opacity: '0',
                transform: 'translateX(100%)',
                transition: 'all 0.3s ease'
            });
            
            // Colores según tipo
            switch(type) {
                case 'success':
                    message.style.backgroundColor = '#27ae60';
                    break;
                case 'error':
                    message.style.backgroundColor = '#e74c3c';
                    break;
                case 'warning':
                    message.style.backgroundColor = '#f39c12';
                    break;
                default:
                    message.style.backgroundColor = '#3498db';
            }
            
            document.body.appendChild(message);
            
            // Animación de entrada
            setTimeout(() => {
                message.style.opacity = '1';
                message.style.transform = 'translateX(0)';
            }, 100);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transform = 'translateX(100%)';
                
                setTimeout(() => {
                    if (message.parentNode) {
                        message.parentNode.removeChild(message);
                    }
                }, 300);
            }, 3000);
        }

        // Navegación de la barra inferior
        document.querySelectorAll('.nav-btn').forEach((btn, index) => {
            btn.addEventListener('click', function() {
                // Remover clase activa de todos los botones
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                
                // Agregar clase activa al botón clickeado
                this.classList.add('active');
                
                // Simular navegación
                const sections = ['Inicio', 'Comunidad', 'Agenda', 'Documentos', 'Veterinaria'];
                showMessage(`Navegando a ${sections[index]}`, 'info');
            });
        });
    </script>
</body>
</html>