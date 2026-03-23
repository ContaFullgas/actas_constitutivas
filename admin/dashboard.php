<?php
require_once "../auth/admin_check.php";
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin/dashboard.css" rel="stylesheet">

</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-brand">Control de Actas</div>

    <div class="nav-right">
        <span class="nav-user"><?php echo $_SESSION['usuario']; ?></span>

        <button class="btn-theme" id="themeToggle" title="Cambiar tema">
            <svg class="icon-moon" viewBox="0 0 24 24">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <svg class="icon-sun" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1"  x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22"  y1="4.22"  x2="5.64"  y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1"  y1="12" x2="3"  y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22"  y1="19.78" x2="5.64"  y2="18.36"/>
                <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>
            </svg>
        </button>

        <a href="../auth/logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</nav>

<!-- CONTENIDO -->
<div class="container">
    <div class="welcome">
        <h1>Panel de Administración</h1>
        <p>Desde aquí podrás administrar empresas, actas y solicitudes.</p>
    </div>

    <div class="modules-grid">
        <a href="empresas.php" class="module-card mod-empresas">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24"><path d="M3 21h18M9 21V7l6-4v18M9 11h6"/></svg>
            </div>
            <div>
                <div class="mod-label">Empresas</div>
                <div class="mod-desc"> </div>
            </div>
        </a>

        <a href="actas.php" class="module-card mod-actas">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
            </div>
            <div>
                <div class="mod-label">Actas</div>
                <div class="mod-desc"> </div>
            </div>
        </a>

        <a href="tipos_acta.php" class="module-card mod-tipos">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </div>
            <div>
                <div class="mod-label">Tipo de Actas</div>
                <div class="mod-desc"> </div>
            </div>
        </a>

        <a href="solicitudes.php" class="module-card mod-solicitudes">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.74 19a19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 3.09 4.2 2 2 0 0 1 5.07 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
                <div class="mod-label">Solicitudes</div>
                <div class="mod-desc"> </div>
            </div>
        </a>

        <a href="historial.php" class="module-card mod-historial">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 1 .5 4M3 21v-4h4"/></svg>
            </div>
            <div>
                <div class="mod-label">Historial</div>
                <div class="mod-desc"> </div>
            </div>
        </a>

        <a href="usuarios.php" class="module-card mod-usuarios">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="mod-label">Usuarios</div>
                <div class="mod-desc"> </div>
            </div>
        </a>
    </div>
</div>

<script>
    const toggle = document.getElementById('themeToggle');
    const html = document.documentElement;

    if (localStorage.getItem('theme') === 'dark') {
        html.setAttribute('data-theme', 'dark');
    }

    toggle.addEventListener('click', () => {
        const isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });
</script>

</body>
</html>


