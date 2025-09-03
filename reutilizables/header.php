<?php
// Componente Header reutilizable
function renderHeader($title = "PetCare", $showSearch = true, $showMenu = true, $showShare = true)
{
    ?>
    <header class="header">
        <div class="header-container">
            <?php if ($showMenu): ?>
                <button class="header-btn menu-btn" onclick="toggleMenu()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            <?php endif; ?>

            <div class="header-logo">
                <div class="logo-paws">
                    <span class="paw">🐾</span>
                    <span class="paw">🐾</span>
                </div>
                <h1 class="logo-text"><?php echo $title; ?></h1>
            </div>

            <div class="header-actions">
                <?php if ($showSearch): ?>
                    <button class="header-btn search-btn" onclick="toggleSearch()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                <?php endif; ?>

                <?php if ($showShare): ?>
                    <button class="header-btn share-btn" onclick="shareApp()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                            <polyline points="16,6 12,2 8,6"></polyline>
                            <line x1="12" y1="2" x2="12" y2="15"></line>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Bar (initially hidden) -->
        <?php if ($showSearch): ?>
            <div class="search-container" id="searchContainer">
                <div class="search-bar">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" class="search-input" placeholder="Buscar mascotas, veterinarios, recordatorios..."
                        id="searchInput">
                    <button class="filter-btn" onclick="showFilters()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <!-- Side Menu -->
    <?php if ($showMenu): ?>
        <div class="side-menu" id="sideMenu">
            <div class="side-menu-overlay" onclick="toggleMenu()"></div>
            <div class="side-menu-content">
                <div class="side-menu-header">
                    <div class="user-info">
                        <div class="user-avatar">👤</div>
                        <div class="user-details">
                            <h3>Usuario</h3>
                            <p>usuario@petcare.com</p>
                        </div>
                    </div>
                    <button class="close-menu-btn" onclick="toggleMenu()">✕</button>
                </div>

                <nav class="side-menu-nav">
                    <a href="/dashboard" class="menu-item">
                        <span class="menu-icon">🏠</span>
                        <span>Inicio</span>
                    </a>
                    <a href="/mascotas" class="menu-item">
                        <span class="menu-icon">🐕</span>
                        <span>Mis Mascotas</span>
                    </a>
                    <a href="/calendario" class="menu-item">
                        <span class="menu-icon">📅</span>
                        <span>Calendario</span>
                    </a>
                    <a href="/recordatorios" class="menu-item">
                        <span class="menu-icon">⏰</span>
                        <span>Recordatorios</span>
                    </a>
                    <a href="/perdidas" class="menu-item">
                        <span class="menu-icon">🔍</span>
                        <span>Mascotas Perdidas</span>
                    </a>
                    <a href="/veterinarios" class="menu-item">
                        <span class="menu-icon">🏥</span>
                        <span>Veterinarios</span>
                    </a>
                    <a href="/perfil" class="menu-item">
                        <span class="menu-icon">👤</span>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="/ajustes" class="menu-item">
                        <span class="menu-icon">⚙️</span>
                        <span>Configuración</span>
                    </a>
                </nav>

                <div class="side-menu-footer">
                    <a href="/logout" class="menu-item logout">
                        <span class="menu-icon">🚪</span>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <style>
        .header {
            background: linear-gradient(135deg, #f7d794, #f3c623);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-btn {
            background: none;
            border: none;
            color: #8b4513;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-btn:hover {
            background: rgba(139, 69, 19, 0.1);
            transform: scale(1.1);
        }

        .header-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .logo-paws {
            display: flex;
            gap: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .paw {
            font-size: 1.2rem;
            animation: bounce 2s infinite;
        }

        .paw:nth-child(2) {
            animation-delay: 0.3s;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #8b4513;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Search Container */
        .search-container {
            display: none;
            padding: 0 1rem 1rem;
            animation: slideDown 0.3s ease;
        }

        .search-container.active {
            display: block;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 25px;
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .search-icon {
            color: #666;
            margin-right: 0.75rem;
        }

        .search-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 1rem;
            background: transparent;
        }

        .search-input::placeholder {
            color: #999;
        }

        .filter-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: 0.5rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            background: #f0f0f0;
            color: #333;
        }

        /* Side Menu */
        .side-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2000;
            visibility: hidden;
            transition: visibility 0.3s ease;
        }

        .side-menu.active {
            visibility: visible;
        }

        .side-menu-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .side-menu.active .side-menu-overlay {
            opacity: 1;
        }

        .side-menu-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 280px;
            height: 100%;
            background: white;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .side-menu.active .side-menu-content {
            transform: translateX(0);
        }

        .side-menu-header {
            padding: 2rem 1.5rem 1.5rem;
            background: linear-gradient(135deg, #f7d794, #f3c623);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #8b4513;
        }

        .user-details h3 {
            margin: 0;
            color: #8b4513;
            font-size: 1.1rem;
        }

        .user-details p {
            margin: 0.25rem 0 0;
            color: #6d4c07;
            font-size: 0.9rem;
        }

        .close-menu-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #8b4513;
            cursor: pointer;
            padding: 0.25rem;
        }

        .side-menu-nav {
            flex: 1;
            padding: 1rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: #333;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: #f8f9fa;
            border-left-color: #f3c623;
            color: #8b4513;
        }

        .menu-item.logout {
            color: #dc3545;
        }

        .menu-item.logout:hover {
            background: #fff5f5;
            border-left-color: #dc3545;
        }

        .menu-icon {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .side-menu-footer {
            padding: 1rem 0;
            border-top: 1px solid #eee;
        }

        /* Animations */
        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (min-width: 768px) {
            .header-container {
                padding: 1.5rem;
            }

            .logo-text {
                font-size: 1.8rem;
            }

            .side-menu-content {
                width: 320px;
            }
        }
    </style>
    <?php
}
?>