<?php
require_once "../auth/auth_check.php";
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/usuario/empresas.css" rel="stylesheet">

</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-left">
        <span class="nav-brand">Control de Actas</span>
    </div>

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

        <button class="btn-hamburger" id="menuToggle" title="Menú">
            <svg viewBox="0 0 24 24">
                <line x1="3" y1="6"  x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
    </div>
</nav>

<!-- MENÚ MÓVIL -->
<div class="mobile-menu" id="mobileMenu">
    <a href="solicitar_prestamo.php" class="nav-link-item">Solicitar préstamo</a>
    <a href="mis_prestamos.php" class="nav-link-item">Mis actas prestadas</a>
    <a href="historial.php" class="nav-link-item">Historial</a>
    <div class="mobile-menu-footer">
        <span class="nav-user" style="display:block;color:rgba(255,255,255,0.75)"><?php echo $_SESSION['usuario']; ?></span>
        <a href="../auth/logout.php" class="btn-logout" style="display:block">Cerrar sesión</a>
    </div>
</div>

<!-- CONTENIDO -->
<div class="container-main">
    <div class="welcome">
        <h1>Panel de Usuario</h1>
        <p>Consulta las actas disponibles y gestiona tus solicitudes de préstamo.</p>
    </div>

    <div class="modules-grid">

        <!-- CARD: SOLICITAR PRÉSTAMO -->
        <a href="solicitar_prestamo.php" class="module-card mod-prestamo">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <line x1="10" y1="9"  x2="8" y2="9"/>
                </svg>
            </div>
            <div class="mod-text">
                <div class="mod-label">Solicitar préstamo</div>
                <div class="mod-desc">Consulta las actas disponibles y solicita el préstamo de documentos.</div>
            </div>
        </a>

        <!-- CARD: MIS ACTAS PRESTADAS -->
        <a href="mis_prestamos.php" class="module-card mod-mis-actas">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.74 19a19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 3.09 4.2 2 2 0 0 1 5.07 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
            </div>
            <div class="mod-text">
                <div class="mod-label">Mis actas prestadas</div>
                <div class="mod-desc">Consulta el estado de tus solicitudes y devoluciones.</div>
            </div>
        </a>

        <!-- CARD: HISTORIAL -->
        <a href="historial.php" class="module-card mod-historial">
            <div class="mod-icon">
                <svg viewBox="0 0 24 24">
                    <polyline points="12 8 12 12 14 14"/>
                    <path d="M3.05 11a9 9 0 1 1 .5 4M3 21v-4h4"/>
                </svg>
            </div>
            <div class="mod-text">
                <div class="mod-label">Historial de préstamos</div>
                <div class="mod-desc">Consulta el historial de todos tus préstamos.</div>
            </div>
        </a>

    </div>
</div>

<script>
    const toggle     = document.getElementById('themeToggle');
    const html       = document.documentElement;
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');

    if (localStorage.getItem('theme') === 'dark') {
        html.setAttribute('data-theme', 'dark');
    }

    toggle.addEventListener('click', () => {
        const isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });

    menuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        mobileMenu.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
        if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
            mobileMenu.classList.remove('open');
        }
    });
</script>

</body>
</html>


