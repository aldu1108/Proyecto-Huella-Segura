<?php if ($rol_usuario != 'veterinario'): ?>
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='adopciones.php'"  title="Adopciones">❤️</button>
        <button class="nav-btn" onclick="window.location.href='mascotas-perdidas.php'"  title="Mascotas perdidas">🔍</button>
        <button class="nav-btn" onclick="window.location.href='index.php'"  title="Inicio">🏠</button>
        <button class="nav-btn" onclick="window.location.href='comunidad.php'"  title="Comunidad">👥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'"  title="Veterinaria">🏥</button>
    </nav>
<?php elseif ($rol_usuario === 'veterinario'): ?>
    <nav class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='comunidad.php'"  title="Comunidad">👥</button>
        <button class="nav-btn" onclick="window.location.href='veterinaria.php'"  title="Veterinaria">🏥</button>
    </nav>
<?php endif; ?>