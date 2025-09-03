<?php
session_start();
/*require_once '../config.php'; */                                                 /* PENDIENTE A HACER */
require_once '../reutilizables/header.php';
require_once '../reutilizables/bottom_nav.php';
/*
// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

// Datos simulados para el dashboard
$pets = [
    [
        'id' => 1,
        'name' => 'Max',
        'breed' => 'Golden Retriever',
        'age' => '3 años',
        'image' => '/frontend/img/pets/max.jpg',
        'next_event' => 'Vacuna anual - 14:00 Urgente'
    ],
    [
        'id' => 2,
        'name' => 'Luna',
        'breed' => 'Gato Persa',
        'age' => '2 años',
        'image' => '/frontend/img/pets/luna.jpg',
        'next_event' => 'Medicina para alergias - 18:30'
    ]
];

$calendar_events = [
    ['date' => '1', 'has_events' => true],
    ['date' => '2', 'has_events' => false],
    ['date' => '3', 'has_events' => true],
    ['date' => '8', 'has_events' => true]
];

$upcoming_events = [
    [
        'pet' => 'Max',
        'type' => 'Vacuna',
        'date' => 'Mañana',
        'priority' => 'high'
    ],
    [
        'pet' => 'Luna',
        'type' => 'Medicina',
        'date' => 'mié, 3 sept',
        'priority' => 'medium'
    ],
    [
        'pet' => 'Max',
        'type' => 'Cita veterinario',
        'date' => 'lun, 8 sept',
        'priority' => 'low'
    ]
];

$reminders_today = [
    [
        'pet' => 'Max',
        'task' => 'Vacuna',
        'time' => '14:00',
        'priority' => 'urgent'
    ],
    [
        'pet' => 'Luna',
        'task' => 'Medicina',
        'time' => '18:30',
        'priority' => 'medium'
    ]
];

$lost_pets = [
    [
        'name' => 'Buddy',
        'breed' => 'Perro Labrador',
        'location' => 'Parque del Retiro',
        'days' => 3,
        'image' => '/frontend/img/lost_pets/buddy.jpg'
    ],
    [
        'name' => 'Mimi',
        'breed' => 'Gato Siamés',
        'location' => 'Gran Vía',
        'days' => 5,
        'image' => '/frontend/img/lost_pets/mimi.jpg'
    ]
];
*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PetCare</title>
    <link rel="stylesheet" href="../frontend/css/global.css">
    <link rel="stylesheet" href="../frontend/css/dashboard.css">
</head>
<body>
    <?php /* renderHeader("PetCare", true, true, true); */ ?>                       <!-- PENDIENTE A HACER -->
    
    <main class="main-content">
        <div class="container">
            
            <!-- Welcome Banner -->
            <section class="welcome-banner">
                <div class="banner-content">
                    <div class="banner-text">
                        <h2>¡Hola! 👋</h2>
                        <p>¿Cómo están tus mascotas hoy?</p>
                    </div>
                    <div class="banner-actions">
                        <button class="quick-action-btn" onclick="showNotifications()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-badge">2</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Search Bar -->
            <section class="search-section">
                <div class="search-container-main">
                    <div class="search-bar-main">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" class="search-input-main" placeholder="Buscar mascotas, veterinarios, recordatorios...">
                        <button class="filter-btn-main">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                            </svg>
                        </button>
                    </div>
                </div>
            </section>

            <!-- My Pets Section -->
            <section class="pets-section">
                <div class="section-header">
                    <h3>Mis Mascotas</h3>
                    <button class="add-btn" onclick="addNewPet()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Agregar
                    </button>
                </div>
                
                <div class="pets-grid">
                    <?php /* foreach ($pets as $pet): */ ?>                                                                          <!--PENDIENTE A HACER-->
                        <div class="pet-card" onclick="openPetProfile(<?php /* echo $pet['id']; */ ?>)">                             <!--PENDIENTE A HACER-->
                            <div class="pet-image">
                                <!--<img src="<?php /* echo $pet['image']; */ ?>" alt="<?php /* echo $pet['name']; */ ?>" onerror="this.src='/frontend/img/placeholder-pet.jpg'"> -->                         <!--PENDIENTE A HACER-->
                                <div class="pet-status active"></div>
                            </div>
                            <div class="pet-info">
                                <h4><?php /* echo htmlspecialchars($pet['name']); */ ?></h4>                                         <!--PENDIENTE A HACER-->
                                <p class="pet-breed"><?php /* echo htmlspecialchars($pet['breed']); */ ?></p>                        <!--PENDIENTE A HACER-->
                                <p class="pet-age"><?php /* echo htmlspecialchars($pet['age']); */ ?></p>                            <!--PENDIENTE A HACER-->
                            </div>
                            <?php /* if (!empty($pet['next_event'])): */ ?>                                                          <!--PENDIENTE A HACER-->
                                <div class="pet-next-event">
                                    <span class="event-text"><?php /* echo htmlspecialchars($pet['next_event']); */ ?></span>        <!--PENDIENTE A HACER-->
                                </div>
                            <?php /* endif; */ ?>                                                                                    <!--PENDIENTE A HACER-->
                        </div>
                    <?php /* endforeach; */  ?>                                                                                      <!--PENDIENTE A HACER-->
                </div>
            </section>

            <!-- Adoption Banner -->
            <section class="adoption-banner">
                <div class="adoption-content">
                    <div class="adoption-text">
                        <div class="adoption-icon">❤️</div>
                        <div>
                            <h4>¿Buscas una nueva mascota?</h4>
                            <p>Hay mascotas esperando un hogar. La adopción es amor puro.</p>
                        </div>
                    </div>
                    <div class="adoption-pets">
                        <span class="pet-emoji">🐕</span>
                        <span class="pet-emoji">🐱</span>
                    </div>
                </div>
                <button class="adoption-btn" onclick="viewAdoptions()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    Ver Mascotas en Adopción
                </button>
            </section>

            <!-- Calendar Section -->
            <section class="calendar-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <h3>Calendario de Cuidados</h3>
                    </div>
                    <span class="events-count">5 eventos programados</span>
                </div>
                
                <div class="calendar-widget">
                    <div class="calendar-header">
                        <button class="nav-btn" onclick="previousMonth()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15,18 9,12 15,6"></polyline>
                            </svg>
                        </button>
                        <h4>septiembre de 2025</h4>
                        <button class="nav-btn" onclick="nextMonth()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9,6 15,12 9,18"></polyline>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="calendar-subheader">
                        <button class="month-nav" onclick="previousMonth()">‹</button>
                        <span class="month-year">September 2025</span>
                        <button class="month-nav" onclick="nextMonth()">›</button>
                    </div>
                    
                    <div class="calendar-grid">
                        <div class="day-header">Su</div>
                        <div class="day-header">Mo</div>
                        <div class="day-header">Tu</div>
                        <div class="day-header">We</div>
                        <div class="day-header">Th</div>
                        <div class="day-header">Fr</div>
                        <div class="day-header">Sa</div>
                        
                        <div class="day prev-month">31</div>
                        <div class="day today">1</div>
                        <div class="day">2</div>
                        <div class="day has-event">3</div>
                        <div class="day">4</div>
                        <div class="day">5</div>
                        <div class="day">6</div>
                        <div class="day">7</div>
                        <div class="day has-event">8</div>
                        <div class="day">9</div>
                        <div class="day">10</div>
                        <div class="day">11</div>
                        <div class="day">12</div>
                        <div class="day">13</div>
                        <div class="day">14</div>
                        <div class="day">15</div>
                        <div class="day">16</div>
                        <div class="day">17</div>
                        <div class="day">18</div>
                        <div class="day">19</div>
                        <div class="day">20</div>
                        <div class="day">21</div>
                        <div class="day">22</div>
                        <div class="day">23</div>
                        <div class="day">24</div>
                        <div class="day">25</div>
                        <div class="day">26</div>
                        <div class="day">27</div>
                        <div class="day">28</div>
                        <div class="day">29</div>
                        <div class="day">30</div>
                        <div class="day next-month">1</div>
                        <div class="day next-month">2</div>
                        <div class="day next-month">3</div>
                        <div class="day next-month">4</div>
                    </div>
                    
                    <div class="calendar-legend">
                        <div class="legend-item">
                            <div class="legend-dot has-events"></div>
                            <span>Días con eventos programados</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Today's Events -->
            <section class="today-events-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12,6 12,12 16,14"></polyline>
                        </svg>
                        <h3>Hoy</h3>
                    </div>
                    <span class="events-badge">2</span>
                </div>
                
                <div class="events-list">
                    <?php /* foreach ($reminders_today as $reminder): */ ?>                                                                                             <!--PENDIENTE A HACER-->
                        <div class="event-item priority-<?php /* echo $reminder['priority']; */ ?>" onclick="openReminder(<?php /* echo $reminder['pet']; */ ?>)">      <!--PENDIENTE A HACER-->
                            <div class="event-icon">
                                <?php /* if ($reminder['task'] === 'Vacuna'): */ ?>                                                                                     <!--PENDIENTE A HACER-->
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19 14l-1.5-1.5L15 15l-2.5-2.5L10 15l-2.5-2.5L5 15l1.5 1.5L5 18h14l-1.5-1.5L19 14z"/>
                                        </svg>
                                <?php /* else: */ ?>                                                                                                                    <!--PENDIENTE A HACER-->
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M21 12c0 1.66-1.34 3-3 3s-3-1.34-3-3 1.34-3 3-3 3 1.34 3 3zM9 12c0 1.66-1.34 3-3 3s-3-1.34-3-3 1.34-3 3-3 3 1.34 3 3z"/>
                                        </svg>
                                <?php /* endif;*/ ?>                                                                                                                    <!--PENDIENTE A HACER-->
                            </div>
                            <div class="event-info">
                                <div class="event-header">
                                    <span class="pet-name"><?php /* echo htmlspecialchars($reminder['pet']); */ ?></span>                                               <!--PENDIENTE A HACER-->
                                    <span class="event-time"><?php /* echo htmlspecialchars($reminder['time']); */ ?></span>                                            <!--PENDIENTE A HACER-->
                                </div>
                                <div class="event-task"><?php /* echo htmlspecialchars($reminder['task']); */ ?></div>                                                  <!--PENDIENTE A HACER-->
                                <?php /* if ($reminder['priority'] === 'urgent'): */ ?>                                                                                 <!--PENDIENTE A HACER-->
                                        <div class="event-priority">Urgente</div>
                                <?php /* endif; */ ?>                                                                                                                   <!--PENDIENTE A HACER-->
                            </div>
                            <button class="event-action">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9,18 15,12 9,6"></polyline>
                                </svg>
                            </button>
                        </div>
                    <?php /* endforeach; */ ?>                                                                                                                          <!--PENDIENTE A HACER-->
                </div>
            </section>

            <!-- Upcoming Events -->
            <section class="upcoming-events-section">
                <div class="section-header">
                    <h3>Próximos eventos esta semana</h3>
                </div>
                
                <div class="upcoming-events-list">
                    <?php /* foreach ($upcoming_events as $event): */ ?>                                                        <!--PENDIENTE A HACER-->
                        <div class="upcoming-event priority-<?php /* echo $event['priority']; */ ?>">                           <!--PENDIENTE A HACER-->
                            <div class="event-indicator"></div>
                            <div class="event-content">
                                <div class="event-pet"><?php /* echo htmlspecialchars($event['pet']); */ ?></div>               <!--PENDIENTE A HACER-->
                                <div class="event-type"><?php /* echo htmlspecialchars($event['type']); */ ?></div>             <!--PENDIENTE A HACER-->
                                <div class="event-date"><?php /* echo htmlspecialchars($event['date']); */ ?></div>             <!--PENDIENTE A HACER-->
                            </div>
                        </div>
                    <?php /* endforeach; */ ?>                                                                                  <!--PENDIENTE A HACER-->
                </div>
            </section>

            <!-- Urgent Reminders -->
            <section class="urgent-reminders-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <h3>Recordatorios Urgentes</h3>
                    </div>
                    <span class="urgent-count">2 para hoy</span>
                </div>
                
                <div class="urgent-list">
                    <!-- Para hoy -->
                    <div class="urgent-group">
                        <h4 class="urgent-group-title">• Para hoy</h4>
                        <?php /* foreach ($reminders_today as $reminder): */ ?>                                                                             <!--PENDIENTE A HACER-->
                            <div class="urgent-item">
                                <div class="urgent-content">
                                    <div class="urgent-header">
                                        <span class="urgent-pet"><?php /* echo htmlspecialchars($reminder['pet']); */ ?></span>                             <!--PENDIENTE A HACER-->
                                        <span class="urgent-task"><?php /* echo htmlspecialchars($reminder['task']); */ ?></span>                           <!--PENDIENTE A HACER-->
                                    </div>
                                    <div class="urgent-time">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12,6 12,12 16,14"></polyline>
                                        </svg>
                                        <?php /* echo htmlspecialchars($reminder['time']); */ ?>                                                            <!--PENDIENTE A HACER-->
                                        <?php /* if ($reminder['priority'] === 'urgent'): */ ?>                                                             <!--PENDIENTE A HACER-->
                                                <span class="urgent-badge">Urgente</span>
                                        <?php /* endif; */ ?>                                                                                               <!--PENDIENTE A HACER-->
                                    </div>
                                </div>
                                <button class="urgent-action">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9,18 15,12 9,6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        <?php /* endforeach; */ ?>                                                                                                          <!--PENDIENTE A HACER-->
                    </div>

                    <!-- Próximamente -->
                    <div class="urgent-group">
                        <h4 class="urgent-group-title">• Próximamente</h4>
                        <div class="upcoming-item">
                            <div class="upcoming-content">
                                <div class="upcoming-header">
                                    <span class="upcoming-date">📅</span>
                                    <span class="upcoming-text">Mañana</span>
                                    <span class="upcoming-pet">Max</span>
                                    <span class="upcoming-task">Cita veterinario</span>
                                </div>
                                <div class="upcoming-time">10:00</div>
                            </div>
                            <button class="upcoming-action">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9,18 15,12 9,6"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button class="view-all-reminders" onclick="viewAllReminders()">
                    Ver todos los recordatorios
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </button>
            </section>

            <!-- Lost Pets Section -->
            <section class="lost-pets-section">
                <div class="section-header">
                    <div class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <h3>Mascotas Perdidas</h3>
                    </div>
                    <a href="/lost-pets" class="view-all-link">Ver todas</a>
                </div>
                
                <div class="lost-pets-list">
                    <?php /* foreach ($lost_pets as $lost_pet): */ ?>                                                                                                                                   <!--PENDIENTE A HACER-->
                        <div class="lost-pet-item">
                            <div class="lost-pet-image">
                                <!--<img src="<?php /* echo $lost_pet['image']; */ ?>" alt="<?php /* echo $lost_pet['name']; */ ?>" onerror="this.src='/frontend/img/placeholder-pet.jpg'"> -->         <!--PENDIENTE A HACER-->
                                <div class="lost-badge">PERDIDO</div>
                            </div>
                            <div class="lost-pet-info">
                                <h4><?php /* echo htmlspecialchars($lost_pet['name']); */ ?></h4>                                                                                                       <!--PENDIENTE A HACER-->
                                <p class="lost-breed"><?php /* echo htmlspecialchars($lost_pet['breed']); */ ?></p>                                                                                     <!--PENDIENTE A HACER-->
                                <div class="lost-details">
                                    <span class="lost-location">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <?php /* echo htmlspecialchars($lost_pet['location']); */ ?>                                                                                                    <!--PENDIENTE A HACER-->
                                    </span>
                                    <span class="lost-time">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12,6 12,12 16,14"></polyline>
                                        </svg>
                                        Hace <?php /* echo $lost_pet['days']; */ ?> días                                                                                                                <!--PENDIENTE A HACER-->
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php /* endforeach; */ ?>                                                                                                                                                          <!--PENDIENTE A HACER-->
                </div>
                
                <!-- Help Banner -->
                <div class="help-banner">
                    <div class="help-content">
                        <div class="help-icon">💡</div>
                        <div class="help-text">
                            <h4>¿Has visto alguna mascota perdida?</h4>
                            <p>Tu ayuda puede ser crucial para reunir a una familia con su mascota.</p>
                        </div>
                    </div>
                    <button class="help-btn" onclick="viewAllLostPets()">
                        Ver Todas las Mascotas Perdidas
                    </button>
                </div>
            </section>

            <!-- Report Lost Pet Button -->
            <section class="report-section">
                <button class="report-btn" onclick="reportLostPet()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    ¡Reportar Mascota Perdida!
                </button>
            </section>

        </div>
    </main>

    <?php /* renderBottomNav('home'); */ ?>

    <script src="../frontend/js/dashboard.js"></script>
    <script>
        // JavaScript functions
        function toggleMenu() {
            const sideMenu = document.getElementById('sideMenu');
            sideMenu.classList.toggle('active');
        }

        function toggleSearch() {
            const searchContainer = document.getElementById('searchContainer');
            const searchInput = document.getElementById('searchInput');
            searchContainer.classList.toggle('active');
            if (searchContainer.classList.contains('active')) {
                searchInput.focus();
            }
        }

        function shareApp() {
            if (navigator.share) {
                navigator.share({
                    title: 'PetCare',
                    text: 'Tu compañero para el cuidado de mascotas',
                    url: window.location.href
                });
            }
        }

        function showFilters() {
            alert('Filtros próximamente');
        }

        function showNotifications() {
            alert('Tienes 2 recordatorios pendientes');
        }

        function addNewPet() {
            window.location.href = '/pets/add';
        }

        function openPetProfile(petId) {
            window.location.href = `/pets/${petId}`;
        }

        function viewAdoptions() {
            window.location.href = '/adoptions';
        }

        function previousMonth() {
            console.log('Previous month');
        }

        function nextMonth() {
            console.log('Next month');
        }

        function openReminder(pet) {
            alert(`Abrir recordatorio para ${pet}`);
        }

        function viewAllReminders() {
            window.location.href = '/reminders';
        }

        function viewAllLostPets() {
            window.location.href = '/lost-pets';
        }

        function reportLostPet() {
            window.location.href = '/lost-pets/report';
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add any initialization code here
        });
    </script>
</body>
</html>