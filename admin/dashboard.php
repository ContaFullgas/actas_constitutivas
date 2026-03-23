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

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root, [data-theme="light"] {
            --bg:        #F4F5F7;
            --surface:   #FFFFFF;
            --border:    #E2E5EA;
            --text-main: #1A1D23;
            --text-soft: #6B7280;
            --nav-bg:    #FFFFFF;
            --nav-border:#E2E5EA;
        }

        [data-theme="dark"] {
            --bg:        #0F1117;
            --surface:   #1A1D27;
            --border:    #2A2D3A;
            --text-main: #F0F2F8;
            --text-soft: #7A8090;
            --nav-bg:    #14161F;
            --nav-border:#22253A;
        }

        :root {
            --c-empresas:    #2563EB;
            --c-actas:       #0F766E;
            --c-tipos:       #7C3AED;
            --c-solicitudes: #D97706;
            --c-historial:   #0369A1;
            --c-usuarios:    #BE185D;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ── NAVBAR ─────────────────────────────────────── */
        nav {
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 32px;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .nav-brand {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.3px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-brand::before {
            content: '';
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #2563EB;
            display: block;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-user {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-soft);
            font-family: 'DM Mono', monospace;
        }

        .btn-logout {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-soft);
            border: 1px solid var(--border);
            background: transparent;
            padding: 5px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-logout:hover {
            color: var(--text-main);
            background: var(--bg);
        }

        .btn-theme {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease;
            color: var(--text-soft);
            flex-shrink: 0;
        }

        .btn-theme:hover { background: var(--bg); color: var(--text-main); }

        .btn-theme svg {
            width: 16px; height: 16px;
            fill: none; stroke: currentColor;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        .icon-sun  { display: none; }
        .icon-moon { display: block; }
        [data-theme="dark"] .icon-sun  { display: block; }
        [data-theme="dark"] .icon-moon { display: none; }

        /* ── CONTENIDO ──────────────────────────────────── */
        .container {
            max-width: 900px;
            margin: 48px auto;
            padding: 0 24px;
        }

        .welcome { margin-bottom: 32px; }

        .welcome h1 {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: -0.5px;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .welcome p {
            font-size: 14px;
            color: var(--text-soft);
        }

        /* ── GRID ───────────────────────────────────────── */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .module-card {
            background: var(--surface);
            border-radius: 12px;
            padding: 24px 22px 20px;
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: relative;
            overflow: hidden;

            /* ── Opción B aplicada ── */
            border: 1.5px solid #C0C5CF;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);

            transition: box-shadow 0.2s ease, transform 0.2s ease,
                        border-color 0.2s ease, background 0.3s ease;
        }

        /* Dark mode: borde original */
        [data-theme="dark"] .module-card {
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .module-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: var(--mod-color);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.25s ease;
        }

        .module-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .module-card:hover::after { transform: scaleX(1); }

        .mod-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            background: color-mix(in srgb, var(--mod-color) 12%, transparent);
            display: flex; align-items: center; justify-content: center;
        }

        .mod-icon svg {
            width: 18px; height: 18px;
            stroke: var(--mod-color);
            fill: none; stroke-width: 1.8;
            stroke-linecap: round; stroke-linejoin: round;
        }

        .mod-label { font-size: 14px; font-weight: 600; letter-spacing: -0.2px; }
        .mod-desc  { font-size: 12px; color: var(--text-soft); margin-top: -8px; }

        .mod-empresas    { --mod-color: var(--c-empresas); }
        .mod-actas       { --mod-color: var(--c-actas); }
        .mod-tipos       { --mod-color: var(--c-tipos); }
        .mod-solicitudes { --mod-color: var(--c-solicitudes); }
        .mod-historial   { --mod-color: var(--c-historial); }
        .mod-usuarios    { --mod-color: var(--c-usuarios); }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .welcome                  { animation: fadeUp 0.4s ease both; }
        .module-card:nth-child(1) { animation: fadeUp 0.4s 0.05s ease both; }
        .module-card:nth-child(2) { animation: fadeUp 0.4s 0.10s ease both; }
        .module-card:nth-child(3) { animation: fadeUp 0.4s 0.15s ease both; }
        .module-card:nth-child(4) { animation: fadeUp 0.4s 0.20s ease both; }
        .module-card:nth-child(5) { animation: fadeUp 0.4s 0.25s ease both; }
        .module-card:nth-child(6) { animation: fadeUp 0.4s 0.30s ease both; }

        /* ══════════════════════════════════════════════════
           TABLET  (≤ 900px) — 2 columnas
        ══════════════════════════════════════════════════ */
        @media (max-width: 900px) {
            .container { margin: 32px auto; }
            .modules-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* ══════════════════════════════════════════════════
           MÓVIL  (≤ 640px) — 1 columna
        ══════════════════════════════════════════════════ */
        @media (max-width: 640px) {
            nav { padding: 0 16px; }
            .nav-user { display: none; }
            .container { margin: 20px auto; padding: 0 16px; }
            .welcome h1 { font-size: 18px; }
            .modules-grid { grid-template-columns: 1fr; }
            .module-card { padding: 18px 16px; flex-direction: row; align-items: center; gap: 16px; }
            .module-card::after { width: 3px; height: 100%; top: 0; left: 0; right: unset; bottom: unset; transform: scaleY(0); transform-origin: top; }
            .module-card:hover::after { transform: scaleY(1); }
        }
    </style>
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


