<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

/* ── Filtros ─────────────────────────────────────────────────── */
$usuario      = $_GET['usuario']      ?? '';
$departamento = $_GET['departamento'] ?? '';
$estado       = $_GET['estado']       ?? '';
$empresa      = $_GET['empresa']      ?? '';
$desde        = $_GET['desde']        ?? '';
$hasta        = $_GET['hasta']        ?? '';

$where = "WHERE 1=1";
if($usuario      != '') $where .= " AND u.nombre LIKE '%$usuario%'";
if($departamento != '') $where .= " AND u.departamento = '$departamento'";
if($estado       != '') $where .= " AND p.estado = '$estado'";
if($empresa      != '') $where .= " AND e.id_empresa = $empresa";
if($desde        != '') $where .= " AND p.fecha_solicitud >= '$desde'";
if($hasta        != '') $where .= " AND p.fecha_solicitud <= '$hasta 23:59:59'";

/* ── Paginación ─────────────────────────────────────────────── */
$por_pagina  = 10;
$pagina      = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset      = ($pagina - 1) * $por_pagina;

$total_res   = mysqli_query($conn, "
    SELECT COUNT(*) FROM prestamos p
    JOIN usuarios u   ON p.id_usuario = u.id_usuario
    JOIN actas a      ON p.id_acta    = a.id_acta
    LEFT JOIN tipos_acta t ON a.id_tipo = t.id_tipo
    JOIN empresas e   ON a.id_empresa = e.id_empresa
    $where
");
$total_filas = mysqli_fetch_row($total_res)[0];
$total_pags  = max(1, ceil($total_filas / $por_pagina));
$pagina      = min($pagina, $total_pags);
$num_inicio  = $offset + 1;

/* Parámetros de filtro para mantenerlos en los links de paginación */
$filter_qs = http_build_query([
    'usuario'      => $usuario,
    'departamento' => $departamento,
    'estado'       => $estado,
    'empresa'      => $empresa,
    'desde'        => $desde,
    'hasta'        => $hasta,
]);
$filter_prefix = $filter_qs ? $filter_qs . '&' : '';

$empresas = mysqli_query($conn, "SELECT id_empresa, nombre_empresa FROM empresas");

$historial = mysqli_query($conn, "
    SELECT 
        p.id_prestamo, p.estado, p.fecha_solicitud, p.fecha_prestamo, p.fecha_devolucion,
        u.nombre, u.usuario, u.departamento,
        t.nombre_tipo, e.nombre_empresa
    FROM prestamos p
    JOIN usuarios u   ON p.id_usuario = u.id_usuario
    JOIN actas a      ON p.id_acta    = a.id_acta
    LEFT JOIN tipos_acta t ON a.id_tipo    = t.id_tipo
    JOIN empresas e   ON a.id_empresa = e.id_empresa
    $where
    ORDER BY p.fecha_solicitud DESC
    LIMIT $por_pagina OFFSET $offset
");
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root, [data-theme="light"] {
            --bg:              #F4F5F7;
            --surface:         #FFFFFF;
            --border:          #E2E5EA;
            --text-main:       #1A1D23;
            --text-soft:       #6B7280;
            --nav-bg:          #FFFFFF;
            --nav-border:      #E2E5EA;
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
            --nav-bg:          #14161F;
            --nav-border:      #22253A;
            --table-head:      #1F2235;
            --table-row-hover: #1E2236;
            --input-bg:        #1F2235;
        }

        :root { --c-historial: #0369A1; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg); color: var(--text-main);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ── NAVBAR ──────────────────────────────────────── */
        nav {
            background: var(--nav-bg); border-bottom: 1px solid var(--nav-border);
            height: 56px; display: flex; align-items: center;
            padding: 0 28px; justify-content: space-between;
            position: sticky; top: 0; z-index: 200;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .nav-left { display: flex; align-items: center; gap: 4px; }

        .nav-brand {
            font-size: 15px; font-weight: 600; letter-spacing: -0.3px;
            color: var(--text-main); display: flex; align-items: center;
            gap: 8px; text-decoration: none; margin-right: 20px; white-space: nowrap;
        }

        .nav-brand::before {
            content: ''; width: 8px; height: 8px; border-radius: 50%;
            background: var(--c-historial); display: block; flex-shrink: 0;
        }

        .nav-links { display: flex; align-items: center; gap: 4px; }

        .nav-link-item {
            font-size: 13px; font-weight: 500; color: var(--text-soft);
            text-decoration: none; padding: 5px 10px; border-radius: 6px;
            transition: all 0.15s ease; white-space: nowrap;
        }

        .nav-link-item:hover { color: var(--text-main); background: var(--bg); }

        .nav-link-item.active {
            color: var(--c-historial);
            background: color-mix(in srgb, var(--c-historial) 8%, transparent);
            font-weight: 600;
        }

        .nav-right { display: flex; align-items: center; gap: 10px; }
        .nav-user { font-size: 13px; font-weight: 500; color: var(--text-soft); font-family: 'DM Mono', monospace; }

        .btn-logout {
            font-size: 12px; font-weight: 500; color: var(--text-soft);
            border: 1px solid var(--border); background: transparent;
            padding: 5px 14px; border-radius: 6px; cursor: pointer;
            text-decoration: none; transition: all 0.15s ease;
        }
        .btn-logout:hover { color: var(--text-main); background: var(--bg); }

        .btn-theme {
            width: 34px; height: 34px; border-radius: 8px;
            border: 1px solid var(--border); background: transparent;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease; color: var(--text-soft); flex-shrink: 0;
        }
        .btn-theme:hover { background: var(--bg); color: var(--text-main); }
        .btn-theme svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

        .icon-sun  { display: none; }
        .icon-moon { display: block; }
        [data-theme="dark"] .icon-sun  { display: block; }
        [data-theme="dark"] .icon-moon { display: none; }

        /* ── HAMBURGUESA ─────────────────────────────────── */
        .btn-hamburger {
            display: none; width: 34px; height: 34px; border-radius: 8px;
            border: 1px solid var(--border); background: transparent;
            cursor: pointer; align-items: center; justify-content: center;
            color: var(--text-soft); flex-shrink: 0; transition: all 0.2s ease;
        }
        .btn-hamburger:hover { background: var(--bg); color: var(--text-main); }
        .btn-hamburger svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; }

        /* ── MENÚ DESPLEGABLE ────────────────────────────── */
        .mobile-menu {
            display: none; position: fixed; top: 56px; left: 0; right: 0;
            background: var(--nav-bg); border-bottom: 1px solid var(--nav-border);
            padding: 12px 16px; flex-direction: column; gap: 4px;
            z-index: 199; box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            animation: slideDown 0.2s ease;
        }
        .mobile-menu.open { display: flex; }
        .mobile-menu .nav-link-item { padding: 10px 14px; border-radius: 8px; font-size: 14px; }
        .mobile-menu-footer { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px 4px; border-top: 1px solid var(--border); margin-top: 4px; }

        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        /* ── CONTENIDO ───────────────────────────────────── */
        .main {
            max-width: 1200px; margin: 40px auto;
            padding: 0 24px; display: flex; flex-direction: column; gap: 20px;
        }

        .page-header { animation: fadeUp 0.35s ease both; }
        .page-header h1 { font-size: 20px; font-weight: 600; letter-spacing: -0.4px; color: var(--text-main); }

        .card {
            background: var(--surface);
            border: 1.5px solid #C0C5CF;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
            border-radius: 12px; padding: 24px;
            transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }
        [data-theme="dark"] .card { border: 1px solid var(--border); box-shadow: none; }

        .card:nth-child(1) { animation: fadeUp 0.35s 0.05s ease both; }
        .card:nth-child(2) { animation: fadeUp 0.35s 0.12s ease both; }

        .card-title { font-size: 14px; font-weight: 600; color: var(--text-main); margin-bottom: 16px; letter-spacing: -0.2px; }

        /* ── FILTROS ─────────────────────────────────────── */
        .filters-grid {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .form-group label { display: block; font-size: 11.5px; font-weight: 500; color: var(--text-soft); margin-bottom: 4px; }

        .form-control, .form-select {
            width: 100%; padding: 8px 12px; font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            background: var(--input-bg) !important; border: 1px solid var(--border) !important;
            border-radius: 7px !important; color: var(--text-main) !important;
            outline: none; transition: border-color 0.15s ease, background 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--c-historial) !important;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-historial) 12%, transparent) !important;
        }
        .form-control::placeholder { color: var(--text-soft); opacity: 0.7; }
        [data-theme="dark"] option { background: var(--surface); color: var(--text-main); }

        .filter-actions { display: flex; align-items: flex-end; gap: 8px; margin-top: 4px; }

        .btn-filter {
            padding: 8px 18px; font-size: 13px; font-weight: 600;
            font-family: 'DM Sans', sans-serif; background: var(--c-historial);
            color: #fff; border: none; border-radius: 7px; cursor: pointer;
            transition: opacity 0.15s ease, transform 0.1s ease;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-filter svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .btn-filter:hover  { opacity: 0.88; }
        .btn-filter:active { transform: scale(0.98); }

        .btn-clear {
            padding: 8px 14px; font-size: 12px; font-weight: 500;
            font-family: 'DM Sans', sans-serif; color: var(--text-soft);
            border: 1px solid var(--border); background: transparent;
            border-radius: 7px; cursor: pointer; text-decoration: none;
            white-space: nowrap; transition: all 0.15s ease;
        }
        .btn-clear:hover { color: var(--text-main); background: var(--bg); }

        /* ── TABLA ───────────────────────────────────────── */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: var(--table-head); border-bottom: 1px solid var(--border); }
        thead th { padding: 10px 14px; font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-soft); text-align: left; white-space: nowrap; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s ease; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--table-row-hover); }
        tbody td { padding: 11px 14px; color: var(--text-main); vertical-align: middle; white-space: nowrap; }

        /* Columna # */
        .col-num { width: 48px; text-align: center; font-family: 'DM Mono', monospace; font-size: 12px; color: var(--text-soft); }

        .user-name   { font-weight: 600; font-size: 13px; }
        .user-handle { font-size: 11.5px; color: var(--text-soft); font-family: 'DM Mono', monospace; margin-top: 1px; }
        .date-empty  { color: var(--text-soft); font-size: 12px; }

        /* ── BADGES ──────────────────────────────────────── */
        .badge-estado { font-size: 11px; font-weight: 600; font-family: 'DM Sans', sans-serif; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
        .badge-pendiente  { background: color-mix(in srgb,#D97706 12%,transparent); color:#D97706; }
        .badge-prestado   { background: color-mix(in srgb,#0369A1 12%,transparent); color:#0369A1; }
        .badge-devolucion { background: color-mix(in srgb,#7C3AED 12%,transparent); color:#7C3AED; }
        .badge-devuelto   { background: color-mix(in srgb,#16A34A 12%,transparent); color:#16A34A; }
        .badge-rechazado  { background: color-mix(in srgb,#DC2626 12%,transparent); color:#DC2626; }

        /* ── PAGINACIÓN ──────────────────────────────────── */
        .pagination-wrap { display: flex; align-items: center; justify-content: space-between; margin-top: 18px; flex-wrap: wrap; gap: 10px; }
        .pagination-info { font-size: 12px; color: var(--text-soft); }
        .pagination { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .page-btn { min-width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text-soft); text-decoration: none; cursor: pointer; transition: all 0.15s ease; padding: 0 6px; }
        .page-btn:hover { background: var(--bg); color: var(--text-main); border-color: #2563EB; }
        .page-btn.active { background: #2563EB; color: #fff; border-color: #2563EB; font-weight: 600; }
        .page-btn.disabled { opacity: 0.35; pointer-events: none; }
        .page-dots { font-size: 13px; color: var(--text-soft); padding: 0 4px; line-height: 34px; }

        /* ── TARJETAS MÓVIL ──────────────────────────────── */
        .card-list { display: none; flex-direction: column; gap: 12px; }
        .historial-card { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px; }
        .historial-card-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px; gap: 10px; }
        .historial-card-num    { font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 600; background: color-mix(in srgb, #2563EB 10%, transparent); color: #2563EB; border-radius: 6px; padding: 2px 7px; display: inline-block; margin-bottom: 5px; }
        .historial-card-name   { font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 2px; }
        .historial-card-handle { font-size: 11px; font-family: 'DM Mono', monospace; color: var(--text-soft); }
        .historial-card-meta   { font-size: 12px; color: var(--text-soft); margin-bottom: 3px; }
        .historial-card-meta span { color: var(--text-main); font-weight: 500; }

        /* ── TOAST ───────────────────────────────────────── */
        .toast-notif { position: fixed; bottom: 28px; right: 28px; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 14px 20px; font-size: 13.5px; font-weight: 500; color: var(--text-main); display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 28px rgba(0,0,0,0.12); z-index: 9999; opacity: 0; transform: translateY(12px); transition: opacity 0.25s ease, transform 0.25s ease; pointer-events: none; min-width: 220px; }
        .toast-notif.show { opacity: 1; transform: translateY(0); }
        .toast-notif .t-icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px; }
        .toast-notif.success .t-icon { background: color-mix(in srgb,#16A34A 15%,transparent); color:#16A34A; }
        .toast-notif.error   .t-icon { background: color-mix(in srgb,#DC2626 15%,transparent); color:#DC2626; }
        .toast-notif.info    .t-icon { background: color-mix(in srgb,#0369A1 15%,transparent); color:#0369A1; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ══════════════════════════════════════════════════
           TABLET  (≤ 900px)
        ══════════════════════════════════════════════════ */
        @media (max-width: 900px) {
            .main { margin: 28px auto; }
            .nav-links { display: none; } .nav-user { display: none; }
            .btn-logout { display: none; } .btn-hamburger { display: flex; }
            .nav-brand { margin-right: 10px; }
            .filters-grid { grid-template-columns: 1fr 1fr 1fr; }
        }

        /* ══════════════════════════════════════════════════
           MÓVIL  (≤ 640px)
        ══════════════════════════════════════════════════ */
        @media (max-width: 640px) {
            nav { padding: 0 16px; }
            .main { margin: 16px auto; padding: 0 16px; gap: 14px; }
            .card { padding: 16px; }
            .page-header h1 { font-size: 17px; }
            .filters-grid { grid-template-columns: 1fr; }
            .filter-actions { flex-direction: column; gap: 8px; }
            .btn-filter, .btn-clear { width: 100%; justify-content: center; text-align: center; }
            .table-wrap { display: none; }
            .card-list  { display: flex; }
            .toast-notif { bottom: 16px; right: 16px; left: 16px; min-width: unset; }
            .pagination-wrap { justify-content: center; }
            .pagination-info { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-left">
        <a href="dashboard.php" class="nav-brand">Control de Actas</a>
        <div class="nav-links">
            <a href="dashboard.php"  class="nav-link-item">Inicio</a>
            <a href="empresas.php"   class="nav-link-item">Empresas</a>
            <a href="actas.php"      class="nav-link-item">Actas</a>
            <a href="tipos_acta.php" class="nav-link-item">Tipos actas</a>
            <a href="solicitudes.php"class="nav-link-item">Solicitudes</a>
            <a href="historial.php"  class="nav-link-item active">Historial</a>
            <a href="usuarios.php"   class="nav-link-item">Usuarios</a>
        </div>
    </div>

    <div class="nav-right">
        <span class="nav-user"><?= $_SESSION['usuario'] ?></span>

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

<!-- MENÚ DESPLEGABLE -->
<div class="mobile-menu" id="mobileMenu">
    <a href="dashboard.php"  class="nav-link-item">Inicio</a>
    <a href="empresas.php"   class="nav-link-item">Empresas</a>
    <a href="actas.php"      class="nav-link-item">Actas</a>
    <a href="tipos_acta.php" class="nav-link-item">Tipos actas</a>
    <a href="solicitudes.php"class="nav-link-item">Solicitudes</a>
    <a href="historial.php"  class="nav-link-item active">Historial</a>
    <a href="usuarios.php"   class="nav-link-item">Usuarios</a>
    <div class="mobile-menu-footer">
        <span class="nav-user" style="display:block"><?= $_SESSION['usuario'] ?></span>
        <a href="../auth/logout.php" class="btn-logout" style="display:block">Cerrar sesión</a>
    </div>
</div>

<!-- CONTENIDO -->
<div class="main">

    <div class="page-header">
        <h1>Historial de préstamos</h1>
    </div>

    <!-- FILTROS -->
    <div class="card">
        <div class="card-title">Filtros</div>
        <form method="GET">
            <div class="filters-grid">

                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" class="form-control"
                           placeholder="Nombre del usuario"
                           value="<?= htmlspecialchars($usuario) ?>">
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <?php
                        $estados = ['pendiente','prestado','devolucion_pendiente','devuelto','rechazado'];
                        $labels  = ['Pendiente','Prestado','Devolución pendiente','Devuelto','Rechazado'];
                        foreach($estados as $i => $e){
                            $sel = ($estado == $e) ? 'selected' : '';
                            echo "<option $sel value='$e'>{$labels[$i]}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Departamento</label>
                    <select name="departamento" class="form-select">
                        <option value="">Todos</option>
                        <?php
                        $deps = ['Contabilidad','Facturación','Sistemas','Nominas','Fiscal'];
                        foreach($deps as $d){
                            $sel = ($departamento == $d) ? 'selected' : '';
                            echo "<option $sel value='$d'>$d</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Empresa</label>
                    <select name="empresa" class="form-select">
                        <option value="">Todas</option>
                        <?php while($emp = mysqli_fetch_assoc($empresas)){ ?>
                            <option value="<?= $emp['id_empresa'] ?>"
                                <?= ($empresa == $emp['id_empresa']) ? 'selected' : '' ?>>
                                <?= $emp['nombre_empresa'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Desde</label>
                    <input type="date" name="desde" class="form-control" value="<?= $desde ?>">
                </div>

                <div class="form-group">
                    <label>Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="<?= $hasta ?>">
                </div>

            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Aplicar filtros
                </button>
                <a href="historial.php" class="btn-clear">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- TABLA + TARJETAS -->
    <div class="card">
        <div class="card-title">
            Resultados
            <?php if($total_filas > 0): ?>
                <span style="font-size:12px;font-weight:400;color:var(--text-soft);margin-left:8px"><?= $total_filas ?> registro<?= $total_filas != 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>

        <!-- Tabla — PC y tablet -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-num" style="text-align:center">#</th>
                        <th>Usuario</th>
                        <th>Departamento</th>
                        <th>Empresa</th>
                        <th>Acta</th>
                        <th>Estado</th>
                        <th>Solicitud</th>
                        <th>Préstamo</th>
                        <th>Devolución</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $estadoMap = ['pendiente'=>'badge-pendiente','prestado'=>'badge-prestado','devolucion_pendiente'=>'badge-devolucion','devuelto'=>'badge-devuelto','rechazado'=>'badge-rechazado'];
                $labelMap  = ['pendiente'=>'Pendiente','prestado'=>'Prestado','devolucion_pendiente'=>'Dev. pendiente','devuelto'=>'Devuelto','rechazado'=>'Rechazado'];
                $num = $num_inicio;
                while($h = mysqli_fetch_assoc($historial)){
                    $cls = $estadoMap[$h['estado']] ?? 'badge-pendiente';
                    $lbl = $labelMap[$h['estado']]  ?? $h['estado'];
                ?>
                    <tr>
                        <td class="col-num" style="text-align:center"><?= $num++ ?></td>
                        <td>
                            <div class="user-name"><?= $h['nombre'] ?></div>
                            <div class="user-handle"><?= $h['usuario'] ?></div>
                        </td>
                        <td><?= $h['departamento'] ?></td>
                        <td><?= $h['nombre_empresa'] ?></td>
                        <td><?= $h['nombre_tipo'] ?? 'Sin tipo' ?></td>
                        <td><span class="badge-estado <?= $cls ?>"><?= $lbl ?></span></td>
                        <td><?= $h['fecha_solicitud'] ?></td>
                        <td><?= $h['fecha_prestamo']   ?? '<span class="date-empty">—</span>' ?></td>
                        <td><?= $h['fecha_devolucion'] ?? '<span class="date-empty">—</span>' ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Tarjetas — solo móvil -->
        <div class="card-list">
            <?php
            mysqli_data_seek($historial, 0);
            $num = $num_inicio;
            while($h = mysqli_fetch_assoc($historial)){
                $cls = $estadoMap[$h['estado']] ?? 'badge-pendiente';
                $lbl = $labelMap[$h['estado']]  ?? $h['estado'];
            ?>
                <div class="historial-card">
                    <div class="historial-card-header">
                        <div>
                            <div class="historial-card-num">#<?= $num++ ?></div>
                            <div class="historial-card-name"><?= $h['nombre'] ?></div>
                            <div class="historial-card-handle"><?= $h['usuario'] ?></div>
                        </div>
                        <span class="badge-estado <?= $cls ?>"><?= $lbl ?></span>
                    </div>
                    <div class="historial-card-meta">Depto: <span><?= $h['departamento'] ?></span></div>
                    <div class="historial-card-meta">Empresa: <span><?= $h['nombre_empresa'] ?></span></div>
                    <div class="historial-card-meta">Acta: <span><?= $h['nombre_tipo'] ?? 'Sin tipo' ?></span></div>
                    <div class="historial-card-meta">Solicitud: <span><?= $h['fecha_solicitud'] ?></span></div>
                    <div class="historial-card-meta">Préstamo: <span><?= $h['fecha_prestamo'] ?? '—' ?></span></div>
                    <div class="historial-card-meta">Devolución: <span><?= $h['fecha_devolucion'] ?? '—' ?></span></div>
                </div>
            <?php } ?>
        </div>

        <!-- PAGINACIÓN — conserva filtros activos -->
        <?php if($total_pags > 1): ?>
        <div class="pagination-wrap">
            <div class="pagination-info">
                Mostrando <?= $num_inicio ?>–<?= min($offset + $por_pagina, $total_filas) ?> de <?= $total_filas ?> registros
            </div>
            <div class="pagination">
                <a class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>"
                   href="?<?= $filter_prefix ?>p=<?= $pagina - 1 ?>">&#8249;</a>
                <?php
                $rango = 2; $ini = max(1, $pagina - $rango); $fin = min($total_pags, $pagina + $rango);
                if($ini > 1){ echo "<a class=\"page-btn\" href=\"?{$filter_prefix}p=1\">1</a>"; if($ini > 2) echo '<span class="page-dots">···</span>'; }
                for($i = $ini; $i <= $fin; $i++){ $act = $i === $pagina ? 'active' : ''; echo "<a class=\"page-btn $act\" href=\"?{$filter_prefix}p=$i\">$i</a>"; }
                if($fin < $total_pags){ if($fin < $total_pags - 1) echo '<span class="page-dots">···</span>'; echo "<a class=\"page-btn\" href=\"?{$filter_prefix}p={$total_pags}\">{$total_pags}</a>"; }
                ?>
                <a class="page-btn <?= $pagina >= $total_pags ? 'disabled' : '' ?>"
                   href="?<?= $filter_prefix ?>p=<?= $pagina + 1 ?>">&#8250;</a>
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

<!-- JS -->
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
</script>

</body>
</html>