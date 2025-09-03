<?php
// Componente Bottom Navigation reutilizable
function renderBottomNav($activeTab = '')
{
    ?>
    <nav class="bottom-nav">
        <div class="bottom-nav-container">
            <a href="/dashboard" class="nav-item <?php echo $activeTab === 'home' ? 'active' : ''; ?>">
                <div class="nav-icon">
                    <?php if ($activeTab === 'home'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                        </svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                        </svg>
                    <?php endif; ?>
                </div>
                <span class="nav-label">Inicio</span>
            </a>

            <a href="/buscar" class="nav-item <?php echo $activeTab === 'search' ? 'active' : ''; ?>">
                <div class="nav-icon">
                    <?php if ($activeTab === 'search'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                        </svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <span class="nav-label">Buscar</span>
            </a>

            <a href="/mascotas" class="nav-item <?php echo $activeTab === 'pets' ? 'active' : ''; ?>">
                <div class="nav-icon">
                    <?php if ($activeTab === 'pets'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                        </svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="16,12 12,8 8,12"></polyline>
                            <line x1="12" y1="16" x2="12" y2="8"></line>
                        </svg>
                    <?php endif; ?>
                </div>
                <span class="nav-label">Mascotas</span>
            </a>

            <a href="/perfil" class="nav-item <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                <div class="nav-icon">
                    <?php if ($activeTab === 'profile'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    <?php endif; ?>
                </div>
                <span class="nav-label">Perfil</span>
            </a>

            <a href="/calendario" class="nav-item <?php echo $activeTab === 'calendar' ? 'active' : ''; ?>">
                <div class="nav-icon">
                    <?php if ($activeTab === 'calendar'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z" />
                        </svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    <?php endif; ?>
                </div>
                <span class="nav-label">Agenda</span>
            </a>
        </div>
    </nav>

    <style>
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #ee5a24, #ff6b47);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            padding-bottom: env(safe-area-inset-bottom);
            box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.1);
        }

        .bottom-nav-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.75rem 0.5rem;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 12px;
            min-width: 60px;
            position: relative;
        }

        .nav-item:hover {
            color: white;
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
        }

        .nav-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }

        .nav-item:hover .nav-icon {
            transform: scale(1.1);
        }

        .nav-item.active .nav-icon {
            transform: scale(1.15);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .nav-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            margin-top: 0.125rem;
        }

        .nav-item.active .nav-label {
            font-weight: 600;
        }

        /* Badge for notifications */
        .nav-item::after {
            content: attr(data-badge);
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 0.125rem 0.375rem;
            border-radius: 10px;
            line-height: 1;
            display: none;
        }

        .nav-item[data-badge]:not([data-badge=""]):not([data-badge="0"])::after {
            display: block;
            animation: pulse 2s infinite;
        }

        /* Responsive adjustments */
        @media (min-width: 768px) {
            .bottom-nav-container {
                padding: 1rem 1rem;
            }

            .nav-item {
                min-width: 80px;
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .nav-label {
                font-size: 0.875rem;
            }
        }

        @media (min-width: 1024px) {
            .bottom-nav {
                display: none;
                /* Hide on desktop, use sidebar instead */
            }
        }

        /* Animation for badges */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Special effect for active item */
        @keyframes glow {
            0% {
                box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
            }

            50% {
                box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
            }

            100% {
                box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
            }
        }

        .nav-item.active {
            animation: glow 3s ease-in-out infinite;
        }

        /* Add margin bottom to body when bottom nav is present */
        body {
            padding-bottom: 100px;
            /* Adjust based on bottom nav height */
        }

        @media (min-width: 1024px) {
            body {
                padding-bottom: 0;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add ripple effect to nav items
            const navItems = document.querySelectorAll('.nav-item');

            navItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255,255,255,0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;

                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
            document.head.appendChild(style);
        });

        // Function to update badge count
        function updateNavBadge(navItem, count) {
            const item = document.querySelector(`.nav-item[href="${navItem}"]`);
            if (item) {
                if (count > 0) {
                    item.setAttribute('data-badge', count > 99 ? '99+' : count.toString());
                } else {
                    item.removeAttribute('data-badge');
                }
            }
        }

        // Function to set active nav item
        function setActiveNav(activeTab) {
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.classList.remove('active');
            });

            const activeItem = document.querySelector(`.nav-item[data-tab="${activeTab}"]`);
            if (activeItem) {
                activeItem.classList.add('active');
            }
        }
    </script>
    <?php
}
?>