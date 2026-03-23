<?php
require_once "../auth/auth_check.php";
require_once "../config/db.php";

$id_usuario = $_SESSION['id_usuario'];

/* ── Búsqueda ───────────────────────────────────────────────── */
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

/* ── Paginación ─────────────────────────────────────────────── */
$por_pagina = 10;
$pagina     = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset     = ($pagina - 1) * $por_pagina;

$where = "WHERE 1=1";
if($buscar != ''){
    $b = mysqli_real_escape_string($conn, $buscar);
    $where .= " AND (e.nombre_empresa LIKE '%$b%' OR t.nombre_tipo LIKE '%$b%' OR a.ubicacion_fisica LIKE '%$b%')";
}

$total_res   = mysqli_query($conn, "
    SELECT COUNT(*) FROM actas a
    JOIN empresas e ON a.id_empresa = e.id_empresa
    LEFT JOIN tipos_acta t ON a.id_tipo = t.id_tipo
    $where
");
$total_filas = mysqli_fetch_row($total_res)[0];
$total_pags  = max(1, ceil($total_filas / $por_pagina));
$pagina      = min($pagina, $total_pags);
$num_inicio  = $offset + 1;

$search_qs = $buscar ? 'buscar=' . urlencode($buscar) . '&' : '';

$actas = mysqli_query($conn, "
    SELECT
        a.*,
        e.nombre_empresa,
        t.nombre_tipo,
        (
            SELECT p.estado FROM prestamos p
            WHERE p.id_acta = a.id_acta
              AND p.id_usuario = $id_usuario
              AND p.estado IN ('pendiente','prestado','devolucion_pendiente')
            LIMIT 1
        ) AS estado_usuario,
        (
            SELECT p.estado FROM prestamos p
            WHERE p.id_acta = a.id_acta
              AND p.estado IN ('pendiente','prestado','devolucion_pendiente')
            LIMIT 1
        ) AS estado_global
    FROM actas a
    JOIN empresas e ON a.id_empresa = e.id_empresa
    LEFT JOIN tipos_acta t ON a.id_tipo = t.id_tipo
    $where
    ORDER BY a.id_acta DESC
    LIMIT $por_pagina OFFSET $offset
");
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar préstamo | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root, [data-theme="light"] {
            --bg:              #F4F5F7;
            --surface:         #FFFFFF;
            --border:          #E2E5EA;
            --text-main:       #1A1D23;
            --text-soft:       #6B7280;
            --nav-bg:          #DC2626;
            --nav-border:      #C41F1F;
            --nav-text:        #FFFFFF;
            --nav-soft:        rgba(255,255,255,0.75);
            --nav-hover:       rgba(255,255,255,0.12);
            --table-head:      #F8F9FB;
            --table-row-hover: #F4F6FF;
            --input-bg:        #FFFFFF;
        }

        [data-theme="dark"] {
            --bg:              #0F1117;
            --surface:         #1A1D27;
            --border:          #2A2D3A;
            --text-main:       #F0F2F8;
            --text-soft:       #7A8090;
            --nav-bg:          #B91C1C;
            --nav-border:      #991B1B;
            --nav-text:        #FFFFFF;
            --nav-soft:        rgba(255,255,255,0.75);
            --nav-hover:       rgba(255,255,255,0.12);
            --table-head:      #1F2235;
            --table-row-hover: #1E2236;
            --input-bg:        #1F2235;
        }

        :root { --c-prestamo: #0F766E; }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text-main); min-height: 100vh; transition: background 0.3s ease, color 0.3s ease; }

        /* ── NAVBAR ──────────────────────────────────────── */
        nav { background: var(--nav-bg); border-bottom: 1px solid var(--nav-border); height: 56px; display: flex; align-items: center; padding: 0 28px; justify-content: space-between; position: sticky; top: 0; z-index: 200; transition: background 0.3s ease, border-color 0.3s ease; }
        .nav-left { display: flex; align-items: center; gap: 4px; }
        .nav-brand { font-size: 15px; font-weight: 600; letter-spacing: -0.3px; color: var(--nav-text); display: flex; align-items: center; gap: 8px; text-decoration: none; margin-right: 20px; white-space: nowrap; }
        .nav-brand::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.9); display: block; flex-shrink: 0; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-link-item { font-size: 13px; font-weight: 500; color: var(--nav-soft); text-decoration: none; padding: 5px 10px; border-radius: 6px; transition: all 0.15s ease; white-space: nowrap; }
        .nav-link-item:hover { color: var(--nav-text); background: var(--nav-hover); }
        .nav-link-item.active { color: var(--nav-text); background: var(--nav-hover); font-weight: 600; }
        .nav-right { display: flex; align-items: center; gap: 10px; }
        .nav-user { font-size: 13px; font-weight: 500; color: var(--nav-soft); font-family: 'DM Mono', monospace; }
        .btn-logout { font-size: 12px; font-weight: 500; color: var(--nav-text); border: 1px solid rgba(255,255,255,0.4); background: transparent; padding: 5px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; transition: all 0.15s ease; white-space: nowrap; }
        .btn-logout:hover { background: var(--nav-hover); border-color: rgba(255,255,255,0.6); }
        .btn-theme { width: 34px; height: 34px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.4); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; color: var(--nav-text); flex-shrink: 0; }
        .btn-theme:hover { background: var(--nav-hover); }
        .btn-theme svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        .icon-sun { display: none; } .icon-moon { display: block; }
        [data-theme="dark"] .icon-sun { display: block; } [data-theme="dark"] .icon-moon { display: none; }

        /* ── HAMBURGUESA ─────────────────────────────────── */
        .btn-hamburger { display: none; width: 34px; height: 34px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.4); background: transparent; cursor: pointer; align-items: center; justify-content: center; color: var(--nav-text); flex-shrink: 0; transition: all 0.2s ease; }
        .btn-hamburger:hover { background: var(--nav-hover); }
        .btn-hamburger svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; }

        /* ── MENÚ MÓVIL ──────────────────────────────────── */
        .mobile-menu { display: none; position: fixed; top: 56px; left: 0; right: 0; background: var(--nav-bg); border-bottom: 1px solid var(--nav-border); padding: 12px 16px; flex-direction: column; gap: 4px; z-index: 199; box-shadow: 0 8px 24px rgba(0,0,0,0.08); animation: slideDown 0.2s ease; }
        .mobile-menu.open { display: flex; }
        .mobile-menu .nav-link-item { padding: 10px 14px; border-radius: 8px; font-size: 14px; }
        .mobile-menu-footer { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px 4px; border-top: 1px solid rgba(255,255,255,0.2); margin-top: 4px; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        /* ── CONTENIDO ───────────────────────────────────── */
        .main { max-width: 1100px; margin: 40px auto; padding: 0 24px; display: flex; flex-direction: column; gap: 20px; }

        .card { background: var(--surface); border: 1.5px solid #C0C5CF; box-shadow: 0 4px 16px rgba(0,0,0,0.07); border-radius: 12px; padding: 24px; transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease; }
        [data-theme="dark"] .card { border: 1px solid var(--border); box-shadow: none; }

        .card-title { font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; letter-spacing: -0.2px; }

        /* ── BUSCADOR ────────────────────────────────────── */
        .search-row { display: flex; gap: 8px; align-items: center; }

        .search-wrap { flex: 1; display: flex; align-items: center; gap: 8px; background: var(--input-bg); border: 1px solid var(--border); border-radius: 7px; padding: 0 12px; transition: border-color 0.15s ease, box-shadow 0.15s ease; }
        .search-wrap:focus-within { border-color: var(--c-prestamo); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-prestamo) 12%, transparent); }
        .search-wrap svg { width: 14px; height: 14px; stroke: var(--text-soft); fill: none; stroke-width: 2; stroke-linecap: round; flex-shrink: 0; }
        .search-wrap input { border: none; background: transparent; outline: none; font-size: 13px; font-family: 'DM Sans', sans-serif; color: var(--text-main); width: 100%; padding: 8px 0; }
        .search-wrap input::placeholder { color: var(--text-soft); opacity: 0.7; }

        .search-result-info { font-size: 12px; color: var(--text-soft); margin-top: 10px; }
        .search-result-info strong { color: var(--c-prestamo); }

        /* ── TABLA ───────────────────────────────────────── */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead tr { background: var(--table-head); border-bottom: 1px solid var(--border); }
        thead th { padding: 10px 14px; font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-soft); text-align: left; white-space: nowrap; }
        thead th.center { text-align: center; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s ease; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--table-row-hover); }
        tbody td { padding: 11px 14px; color: var(--text-main); vertical-align: middle; }
        tbody td.center { text-align: center; }
        .col-num { width: 48px; text-align: center; font-family: 'DM Mono', monospace; font-size: 12px; color: var(--text-soft); }
        .thumb { width: 42px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); cursor: pointer; transition: opacity 0.15s ease; }
        .thumb:hover { opacity: 0.8; }
        .no-foto { font-size: 12px; color: var(--text-soft); font-style: italic; }

        /* ── BADGES ──────────────────────────────────────── */
        .badge-status { font-size: 11px; font-weight: 600; font-family: 'DM Sans', sans-serif; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
        .badge-pendiente    { background: color-mix(in srgb,#D97706 12%,transparent); color:#D97706; }
        .badge-prestado     { background: color-mix(in srgb,#16A34A 12%,transparent); color:#16A34A; }
        .badge-devolucion   { background: color-mix(in srgb,#0369A1 12%,transparent); color:#0369A1; }
        .badge-nodisponible { background: color-mix(in srgb,#6B7280 12%,transparent); color:var(--text-soft); }

        /* ── BOTÓN SOLICITAR ─────────────────────────────── */
        .btn-solicitar { font-size: 12px; font-weight: 600; font-family: 'DM Sans', sans-serif; padding: 5px 14px; border-radius: 6px; border: none; cursor: pointer; transition: opacity 0.15s ease, transform 0.1s ease; background: var(--c-prestamo); color: #fff; }
        .btn-solicitar:hover  { opacity: 0.88; }
        .btn-solicitar:active { transform: scale(0.98); }

        /* ── PAGINACIÓN ──────────────────────────────────── */
        .pagination-wrap { display: flex; align-items: center; justify-content: space-between; margin-top: 18px; flex-wrap: wrap; gap: 10px; }
        .pagination-info { font-size: 12px; color: var(--text-soft); }
        .pagination { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .page-btn { min-width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text-soft); text-decoration: none; cursor: pointer; transition: all 0.15s ease; padding: 0 6px; }
        .page-btn:hover { background: var(--bg); color: var(--text-main); border-color: var(--c-prestamo); }
        .page-btn.active { background: var(--c-prestamo); color: #fff; border-color: var(--c-prestamo); font-weight: 600; }
        .page-btn.disabled { opacity: 0.35; pointer-events: none; }
        .page-dots { font-size: 13px; color: var(--text-soft); padding: 0 4px; line-height: 34px; }

        /* ── TARJETAS MÓVIL ──────────────────────────────── */
        .card-list { display: none; flex-direction: column; gap: 10px; }
        .acta-card { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px; display: flex; gap: 14px; align-items: flex-start; }
        .acta-card-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); flex-shrink: 0; cursor: pointer; }
        .acta-card-thumb-empty { width: 52px; height: 52px; border-radius: 8px; border: 1px dashed var(--border); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-soft); text-align: center; }
        .acta-card-body { flex: 1; min-width: 0; }
        .acta-card-num     { font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 600; background: color-mix(in srgb, var(--c-prestamo) 10%, transparent); color: var(--c-prestamo); border-radius: 6px; padding: 2px 7px; display: inline-block; margin-bottom: 5px; }
        .acta-card-empresa { font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 2px; }
        .acta-card-tipo    { font-size: 12px; color: var(--text-soft); margin-bottom: 2px; }
        .acta-card-ubic    { font-size: 12px; color: var(--text-soft); margin-bottom: 10px; }

        /* ── MODAL ───────────────────────────────────────── */
        .modal-content { background: var(--surface) !important; border: 1px solid var(--border) !important; border-radius: 12px !important; color: var(--text-main) !important; }
        .modal-header  { border-bottom: 1px solid var(--border) !important; padding: 18px 22px !important; }
        .modal-title   { font-size: 15px !important; font-weight: 600 !important; color: var(--text-main) !important; }
        .modal-body    { padding: 20px 22px !important; }
        #imgGrande { border-radius: 8px; max-height: 70vh; object-fit: contain; }
        .btn-close { filter: var(--btn-close-filter, none); }
        [data-theme="dark"] .btn-close { filter: invert(1); }

        /* ── TOAST ───────────────────────────────────────── */
        .toast-notif { position: fixed; bottom: 28px; right: 28px; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 14px 20px; font-size: 13.5px; font-weight: 500; color: var(--text-main); display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 28px rgba(0,0,0,0.12); z-index: 9999; opacity: 0; transform: translateY(12px); transition: opacity 0.25s ease, transform 0.25s ease; pointer-events: none; min-width: 220px; }
        .toast-notif.show { opacity: 1; transform: translateY(0); }
        .toast-notif .t-icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px; }
        .toast-notif.success .t-icon { background: color-mix(in srgb,#16A34A 15%,transparent); color:#16A34A; }
        .toast-notif.error   .t-icon { background: color-mix(in srgb,#DC2626 15%,transparent); color:#DC2626; }
        .toast-notif.info    .t-icon { background: color-mix(in srgb,#0369A1 15%,transparent); color:#0369A1; }

        /* ── CONFIRM ─────────────────────────────────────── */
        .confirm-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9998; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; }
        .confirm-overlay.show { opacity: 1; pointer-events: all; }
        .confirm-box { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 28px 28px 22px; width: 100%; max-width: 360px; text-align: center; transform: translateY(12px) scale(0.97); transition: transform 0.2s ease; }
        .confirm-overlay.show .confirm-box { transform: translateY(0) scale(1); }
        .confirm-icon { width: 44px; height: 44px; border-radius: 50%; background: color-mix(in srgb,var(--c-prestamo) 10%,transparent); color:var(--c-prestamo); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 20px; }
        .confirm-title { font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 6px; }
        .confirm-desc  { font-size: 13px; color: var(--text-soft); margin-bottom: 22px; }
        .confirm-actions { display: flex; gap: 10px; justify-content: center; }
        .confirm-cancel { padding: 9px 20px; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; background: transparent; border: 1px solid var(--border); color: var(--text-soft); border-radius: 7px; cursor: pointer; transition: all 0.15s ease; flex: 1; }
        .confirm-cancel:hover { background: var(--bg); color: var(--text-main); }
        .confirm-ok { padding: 9px 20px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; background: var(--c-prestamo); color: #fff; border: none; border-radius: 7px; cursor: pointer; transition: opacity 0.15s ease; flex: 1; }
        .confirm-ok:hover { opacity: 0.88; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .card:nth-child(1) { animation: fadeUp 0.35s 0.05s ease both; }
        .card:nth-child(2) { animation: fadeUp 0.35s 0.12s ease both; }

        /* ── TABLET (≤ 900px) ────────────────────────────── */
        @media (max-width: 900px) {
            .main { margin: 28px auto; }
            .nav-links { display: none; } .nav-user { display: none; }
            .btn-logout { display: none; } .btn-hamburger { display: flex; }
            .nav-brand { margin-right: 10px; }
        }

        /* ── MÓVIL (≤ 640px) ─────────────────────────────── */
        @media (max-width: 640px) {
            nav { padding: 0 16px; }
            .main { margin: 16px auto; padding: 0 16px; gap: 14px; }
            .card { padding: 16px; }
            .table-wrap { display: none; }
            .card-list  { display: flex; }
            .toast-notif { bottom: 16px; right: 16px; left: 16px; min-width: unset; }
            .confirm-box { padding: 22px 20px 18px; }
            .pagination-wrap { justify-content: center; }
            .pagination-info { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-left">
        <a href="empresas.php" class="nav-brand">Control de Actas</a>
        <div class="nav-links">
            <a href="empresas.php"          class="nav-link-item">Inicio</a>
            <a href="solicitar_prestamo.php" class="nav-link-item active">Solicitar préstamo</a>
            <a href="mis_prestamos.php"      class="nav-link-item">Mis préstamos</a>
            <a href="historial.php"          class="nav-link-item">Historial</a>
        </div>
    </div>
    <div class="nav-right">
        <span class="nav-user"><?php echo $_SESSION['usuario']; ?></span>
        <button class="btn-theme" id="themeToggle" title="Cambiar tema">
            <svg class="icon-moon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <svg class="icon-sun" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
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
    <a href="empresas.php"          class="nav-link-item">Inicio</a>
    <a href="solicitar_prestamo.php" class="nav-link-item active">Solicitar préstamo</a>
    <a href="mis_prestamos.php"      class="nav-link-item">Mis préstamos</a>
    <a href="historial.php"          class="nav-link-item">Historial</a>
    <div class="mobile-menu-footer">
        <span class="nav-user" style="display:block"><?php echo $_SESSION['usuario']; ?></span>
        <a href="../auth/logout.php" class="btn-logout" style="display:block">Cerrar sesión</a>
    </div>
</div>

<!-- CONTENIDO -->
<div class="main">

    <!-- ── CARD: BUSCADOR ─────────────────────────────── -->
    <div class="card">
        <div class="card-title">Solicitar préstamo de acta</div>
        <form method="GET" action="solicitar_prestamo.php">
            <div class="search-row">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="buscar"
                           placeholder="Buscar por empresa, tipo o ubicación..."
                           value="<?= htmlspecialchars($buscar) ?>">
                </div>
            </div>
            <?php if($buscar != ''): ?>
            <div class="search-result-info">
                <?= $total_filas ?> resultado<?= $total_filas != 1 ? 's' : '' ?> para
                <strong>"<?= htmlspecialchars($buscar) ?>"</strong>
                — <a href="solicitar_prestamo.php" style="color:var(--text-soft);font-size:12px">Limpiar</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- ── CARD: LISTADO ──────────────────────────────── -->
    <div class="card">
        <div class="card-title">Actas disponibles</div>

        <!-- Tabla — PC y tablet -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-num center">#</th>
                        <th class="center">Portada</th>
                        <th>Empresa</th>
                        <th>Tipo de acta</th>
                        <th>Ubicación</th>
                        <th class="center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php $num = $num_inicio; while ($a = mysqli_fetch_assoc($actas)) { ?>
                    <tr>
                        <td class="col-num center"><?= $num++ ?></td>
                        <td class="center">
                            <?php if ($a['foto_portada']) { ?>
                                <img src="../<?= $a['foto_portada'] ?>" class="thumb"
                                     onclick="verImagen('../<?= $a['foto_portada'] ?>')" alt="Portada acta">
                            <?php } else { ?>
                                <span class="no-foto">Sin foto</span>
                            <?php } ?>
                        </td>
                        <td><?= $a['nombre_empresa'] ?></td>
                        <td><?= $a['nombre_tipo'] ?? 'Sin tipo' ?></td>
                        <td><?= $a['ubicacion_fisica'] ?></td>
                        <td class="center" id="accion_<?= $a['id_acta'] ?>">
                            <?php if ($a['estado_global'] == null) { ?>
                                <button class="btn-solicitar" onclick="solicitarPrestamo(<?= $a['id_acta'] ?>)">Solicitar</button>
                            <?php } elseif ($a['estado_usuario']) {
                                if ($a['estado_usuario'] == 'pendiente') { ?>
                                    <span class="badge-status badge-pendiente">Solicitud pendiente</span>
                                <?php } elseif ($a['estado_usuario'] == 'prestado') { ?>
                                    <span class="badge-status badge-prestado">Acta prestada</span>
                                <?php } elseif ($a['estado_usuario'] == 'devolucion_pendiente') { ?>
                                    <span class="badge-status badge-devolucion">Devolución pendiente</span>
                                <?php } } else { ?>
                                <span class="badge-status badge-nodisponible">No disponible</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Tarjetas — solo móvil -->
        <div class="card-list">
            <?php mysqli_data_seek($actas, 0); $num = $num_inicio; while ($a = mysqli_fetch_assoc($actas)) { ?>
                <div class="acta-card">
                    <?php if ($a['foto_portada']) { ?>
                        <img src="../<?= $a['foto_portada'] ?>" class="acta-card-thumb"
                             onclick="verImagen('../<?= $a['foto_portada'] ?>')" alt="Portada">
                    <?php } else { ?>
                        <div class="acta-card-thumb-empty">Sin foto</div>
                    <?php } ?>
                    <div class="acta-card-body">
                        <div class="acta-card-num">#<?= $num++ ?></div>
                        <div class="acta-card-empresa"><?= $a['nombre_empresa'] ?></div>
                        <div class="acta-card-tipo">Tipo: <?= $a['nombre_tipo'] ?? 'Sin tipo' ?></div>
                        <div class="acta-card-ubic">Ubicación: <?= $a['ubicacion_fisica'] ?></div>
                        <div id="accion_mob_<?= $a['id_acta'] ?>">
                            <?php if ($a['estado_global'] == null) { ?>
                                <button class="btn-solicitar" onclick="solicitarPrestamo(<?= $a['id_acta'] ?>)">Solicitar</button>
                            <?php } elseif ($a['estado_usuario']) {
                                if ($a['estado_usuario'] == 'pendiente') { ?>
                                    <span class="badge-status badge-pendiente">Solicitud pendiente</span>
                                <?php } elseif ($a['estado_usuario'] == 'prestado') { ?>
                                    <span class="badge-status badge-prestado">Acta prestada</span>
                                <?php } elseif ($a['estado_usuario'] == 'devolucion_pendiente') { ?>
                                    <span class="badge-status badge-devolucion">Devolución pendiente</span>
                                <?php } } else { ?>
                                <span class="badge-status badge-nodisponible">No disponible</span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- PAGINACIÓN -->
        <?php if($total_pags > 1): ?>
        <div class="pagination-wrap">
            <div class="pagination-info">
                Mostrando <?= $num_inicio ?>–<?= min($offset + $por_pagina, $total_filas) ?> de <?= $total_filas ?> actas
            </div>
            <div class="pagination">
                <a class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>" href="?<?= $search_qs ?>p=<?= $pagina - 1 ?>">&#8249;</a>
                <?php
                $rango = 2; $ini = max(1, $pagina - $rango); $fin = min($total_pags, $pagina + $rango);
                if($ini > 1){ echo "<a class=\"page-btn\" href=\"?{$search_qs}p=1\">1</a>"; if($ini > 2) echo '<span class="page-dots">···</span>'; }
                for($i = $ini; $i <= $fin; $i++){ $act = $i === $pagina ? 'active' : ''; echo "<a class=\"page-btn $act\" href=\"?{$search_qs}p=$i\">$i</a>"; }
                if($fin < $total_pags){ if($fin < $total_pags - 1) echo '<span class="page-dots">···</span>'; echo "<a class=\"page-btn\" href=\"?{$search_qs}p={$total_pags}\">{$total_pags}</a>"; }
                ?>
                <a class="page-btn <?= $pagina >= $total_pags ? 'disabled' : '' ?>" href="?<?= $search_qs ?>p=<?= $pagina + 1 ?>">&#8250;</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- TOAST -->
<div id="toast" class="toast-notif">
    <span id="toast-icon" class="t-icon"></span>
    <span id="toast-msg"></span>
</div>

<!-- CONFIRM -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon">📋</div>
        <div class="confirm-title" id="confirmTitle">¿Solicitar préstamo?</div>
        <div class="confirm-desc"  id="confirmDesc">Se enviará una solicitud al administrador.</div>
        <div class="confirm-actions">
            <button class="confirm-cancel" onclick="cerrarConfirm()">Cancelar</button>
            <button class="confirm-ok"     id="confirmOkBtn">Confirmar</button>
        </div>
    </div>
</div>

<!-- MODAL VER IMAGEN -->
<div class="modal fade" id="modalImagen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Portada del acta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imgGrande" src="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const toggle = document.getElementById('themeToggle');
    const html   = document.documentElement;
    if (localStorage.getItem('theme') === 'dark') html.setAttribute('data-theme', 'dark');
    toggle.addEventListener('click', () => {
        const isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });

    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    menuToggle.addEventListener('click', (e) => { e.stopPropagation(); mobileMenu.classList.toggle('open'); });
    document.addEventListener('click', (e) => {
        if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) mobileMenu.classList.remove('open');
    });

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const icons = { success: '✓', error: '✕', info: 'i' };
        toast.className = 'toast-notif ' + type;
        document.getElementById('toast-icon').className   = 't-icon';
        document.getElementById('toast-icon').textContent = icons[type];
        document.getElementById('toast-msg').textContent  = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function showConfirm(title, desc, onOk) {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmDesc').textContent  = desc;
        document.getElementById('confirmOverlay').classList.add('show');
        document.getElementById('confirmOkBtn').onclick = function() { cerrarConfirm(); onOk(); };
    }

    function cerrarConfirm() { document.getElementById('confirmOverlay').classList.remove('show'); }

    document.getElementById('confirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) cerrarConfirm();
    });

    window.alert = function(msg) {
        const type = (msg.toLowerCase().includes('error') || msg.toLowerCase().includes('falló') ||
                      msg.toLowerCase().includes('incorrecto') || msg.toLowerCase().includes('obligatorio'))
                      ? 'error' : 'success';
        showToast(msg, type);
    };
</script>

<script src="../js/prestamos_usuario.js"></script>

</body>
</html>


