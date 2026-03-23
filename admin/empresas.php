<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

/* ── Búsqueda ───────────────────────────────────────────────── */
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

/* ── Paginación ─────────────────────────────────────────────── */
$por_pagina  = 10;
$pagina      = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset      = ($pagina - 1) * $por_pagina;

$where = "WHERE 1=1";
if($buscar != ''){
    $b = mysqli_real_escape_string($conn, $buscar);
    $where .= " AND (nombre_empresa LIKE '%$b%' OR rfc LIKE '%$b%')";
}

$total_res   = mysqli_query($conn, "SELECT COUNT(*) FROM empresas $where");
$total_filas = mysqli_fetch_row($total_res)[0];
$total_pags  = max(1, ceil($total_filas / $por_pagina));
$pagina      = min($pagina, $total_pags);
$num_inicio  = $offset + 1;

$search_qs = $buscar ? 'buscar=' . urlencode($buscar) . '&' : '';

$res = mysqli_query($conn,
    "SELECT * FROM empresas $where ORDER BY id_empresa DESC
     LIMIT $por_pagina OFFSET $offset"
);
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
            --table-head:#F8F9FB;
            --table-row-hover: #F4F6FF;
            --input-bg:  #FFFFFF;
        }

        [data-theme="dark"] {
            --bg:        #0F1117;
            --surface:   #1A1D27;
            --border:    #2A2D3A;
            --text-main: #F0F2F8;
            --text-soft: #7A8090;
            --nav-bg:    #14161F;
            --nav-border:#22253A;
            --table-head:#1F2235;
            --table-row-hover: #1E2236;
            --input-bg:  #1F2235;
        }

        :root { --c-empresas: #2563EB; }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text-main); min-height: 100vh; transition: background 0.3s ease, color 0.3s ease; }

        /* ── NAVBAR ──────────────────────────────────────── */
        nav { background: var(--nav-bg); border-bottom: 1px solid var(--nav-border); height: 56px; display: flex; align-items: center; padding: 0 28px; justify-content: space-between; position: sticky; top: 0; z-index: 200; transition: background 0.3s ease, border-color 0.3s ease; }
        .nav-left { display: flex; align-items: center; gap: 4px; }
        .nav-brand { font-size: 15px; font-weight: 600; letter-spacing: -0.3px; color: var(--text-main); display: flex; align-items: center; gap: 8px; text-decoration: none; margin-right: 20px; white-space: nowrap; }
        .nav-brand::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--c-empresas); display: block; flex-shrink: 0; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-link-item { font-size: 13px; font-weight: 500; color: var(--text-soft); text-decoration: none; padding: 5px 10px; border-radius: 6px; transition: all 0.15s ease; white-space: nowrap; }
        .nav-link-item:hover { color: var(--text-main); background: var(--bg); }
        .nav-link-item.active { color: var(--c-empresas); background: color-mix(in srgb, var(--c-empresas) 8%, transparent); font-weight: 600; }
        .nav-right { display: flex; align-items: center; gap: 10px; }
        .nav-user { font-size: 13px; font-weight: 500; color: var(--text-soft); font-family: 'DM Mono', monospace; }
        .btn-logout { font-size: 12px; font-weight: 500; color: var(--text-soft); border: 1px solid var(--border); background: transparent; padding: 5px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; transition: all 0.15s ease; white-space: nowrap; }
        .btn-logout:hover { color: var(--text-main); background: var(--bg); }
        .btn-theme { width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; color: var(--text-soft); flex-shrink: 0; }
        .btn-theme:hover { background: var(--bg); color: var(--text-main); }
        .btn-theme svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        .icon-sun { display: none; } .icon-moon { display: block; }
        [data-theme="dark"] .icon-sun { display: block; } [data-theme="dark"] .icon-moon { display: none; }

        /* ── HAMBURGUESA ─────────────────────────────────── */
        .btn-hamburger { display: none; width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border); background: transparent; cursor: pointer; align-items: center; justify-content: center; color: var(--text-soft); flex-shrink: 0; transition: all 0.2s ease; }
        .btn-hamburger:hover { background: var(--bg); color: var(--text-main); }
        .btn-hamburger svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; }

        /* ── MENÚ DESPLEGABLE ────────────────────────────── */
        .mobile-menu { display: none; position: fixed; top: 56px; left: 0; right: 0; background: var(--nav-bg); border-bottom: 1px solid var(--nav-border); padding: 12px 16px; flex-direction: column; gap: 4px; z-index: 199; box-shadow: 0 8px 24px rgba(0,0,0,0.08); animation: slideDown 0.2s ease; }
        .mobile-menu.open { display: flex; }
        .mobile-menu .nav-link-item { padding: 10px 14px; border-radius: 8px; font-size: 14px; }
        .mobile-menu-footer { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px 4px; border-top: 1px solid var(--border); margin-top: 4px; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        /* ── CONTENIDO ───────────────────────────────────── */
        .main { max-width: 1000px; margin: 40px auto; padding: 0 24px; display: flex; flex-direction: column; gap: 20px; }

        .card { background: var(--surface); border-radius: 12px; padding: 24px; border: 1.5px solid #C0C5CF; box-shadow: 0 4px 16px rgba(0,0,0,0.07); transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease; }
        [data-theme="dark"] .card { border: 1px solid var(--border); box-shadow: none; }

        /* ── CARD HEADER ────────── */
        .card-header-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 0;
        }

        .card-title { font-size: 15px; font-weight: 600; color: var(--text-main); letter-spacing: -0.2px; }

        /* ── BOTÓN AGREGAR EMPRESA ───────────────────────── */
        .btn-agregar {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            width: 152px; height: 36px;
            font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif;
            background: var(--c-empresas); color: #fff;
            border: none; border-radius: 7px; cursor: pointer;
            white-space: nowrap; transition: opacity 0.15s ease, transform 0.1s ease;
            flex-shrink: 0;
        }
        .btn-agregar:hover  { opacity: 0.88; }
        .btn-agregar:active { transform: scale(0.98); }
        .btn-agregar svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2.5; stroke-linecap: round; }

        /* ── BUSCADOR ────────────────────────────────────── */
        .search-row { display: flex; gap: 8px; align-items: center; margin-top: 14px; }

        .search-wrap {
            flex: 1; display: flex; align-items: center; gap: 8px;
            background: var(--input-bg); border: 1px solid var(--border);
            border-radius: 7px; padding: 0 12px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .search-wrap:focus-within { border-color: var(--c-empresas); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-empresas) 12%, transparent); }
        .search-wrap svg { width: 14px; height: 14px; stroke: var(--text-soft); fill: none; stroke-width: 2; stroke-linecap: round; flex-shrink: 0; }
        .search-wrap input { border: none; background: transparent; outline: none; font-size: 13px; font-family: 'DM Sans', sans-serif; color: var(--text-main); width: 100%; padding: 8px 0; }
        .search-wrap input::placeholder { color: var(--text-soft); opacity: 0.7; }

        .search-result-info { font-size: 12px; color: var(--text-soft); margin-top: 10px; }
        .search-result-info strong { color: var(--c-empresas); }

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

        /* ── PAGINACIÓN ──────────────────────────────────── */
        .pagination-wrap { display: flex; align-items: center; justify-content: space-between; margin-top: 18px; flex-wrap: wrap; gap: 10px; }
        .pagination-info { font-size: 12px; color: var(--text-soft); }
        .pagination { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .page-btn { min-width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text-soft); text-decoration: none; cursor: pointer; transition: all 0.15s ease; padding: 0 6px; }
        .page-btn:hover { background: var(--bg); color: var(--text-main); border-color: var(--c-empresas); }
        .page-btn.active { background: var(--c-empresas); color: #fff; border-color: var(--c-empresas); font-weight: 600; }
        .page-btn.disabled { opacity: 0.35; pointer-events: none; }
        .page-dots { font-size: 13px; color: var(--text-soft); padding: 0 4px; line-height: 34px; }

        /* ── TARJETAS MÓVIL ──────────────────────────────── */
        .card-list { display: none; flex-direction: column; gap: 10px; }
        .empresa-card { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start; }
        .empresa-card-num { font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 600; background: color-mix(in srgb, var(--c-empresas) 10%, transparent); color: var(--c-empresas); border-radius: 6px; padding: 3px 8px; flex-shrink: 0; margin-top: 2px; }
        .empresa-card-body { flex: 1; min-width: 0; }
        .empresa-card-name  { font-size: 14px; font-weight: 600; color: var(--text-main); margin-bottom: 4px; }
        .empresa-card-rfc   { font-family: 'DM Mono', monospace; font-size: 12px; color: var(--text-soft); margin-bottom: 2px; }
        .empresa-card-fecha { font-size: 12px; color: var(--text-soft); margin-bottom: 12px; }
        .empresa-card-actions { display: flex; gap: 8px; }

        @media (max-width: 640px) {
            .empresa-card-actions { flex-direction: column; gap: 6px; }
            .empresa-card-actions .btn-edit,
            .empresa-card-actions .btn-delete { width: 100%; text-align: center; padding: 7px 12px; }
        }

        /* ── BOTONES ACCIÓN ──────────────────────────────── */
        .btn-edit, .btn-delete { font-size: 12px; font-weight: 500; font-family: 'DM Sans', sans-serif; padding: 6px 14px; border-radius: 6px; border: none; cursor: pointer; transition: opacity 0.15s ease; }
        .btn-edit   { background: color-mix(in srgb, #2563EB 12%, transparent); color: #2563EB; }
        .btn-delete { background: color-mix(in srgb, #DC2626 12%, transparent); color: #DC2626; }
        .btn-edit:hover, .btn-delete:hover { opacity: 0.75; }
        [data-theme="dark"] .btn-edit { background: color-mix(in srgb, #3B82F6 15%, transparent); color: #3B82F6; }

        /* ── MODAL AGREGAR EMPRESA ───────────────────────── */
        .modal-content { background: var(--surface) !important; border: 1px solid var(--border) !important; border-radius: 14px !important; color: var(--text-main) !important; }
        .modal-header  { border-bottom: 1px solid var(--border) !important; padding: 18px 22px !important; }
        .modal-title   { font-size: 15px !important; font-weight: 600 !important; color: var(--text-main) !important; display: flex; align-items: center; gap: 8px; }
        .modal-title svg { width: 18px; height: 18px; stroke: var(--c-empresas); fill: none; stroke-width: 1.8; stroke-linecap: round; flex-shrink: 0; }
        .modal-body    { padding: 22px 22px 16px !important; }
        .modal-footer  { border-top: 1px solid var(--border) !important; padding: 14px 22px !important; gap: 10px; }
        .modal-body .form-label { font-size: 12px; font-weight: 500; color: var(--text-soft); margin-bottom: 5px; display: block; }
        .modal-body .form-control {
            width: 100%; padding: 9px 12px; font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            background: var(--input-bg) !important; border: 1px solid var(--border) !important;
            border-radius: 7px !important; color: var(--text-main) !important;
            outline: none; transition: border-color 0.15s ease, background 0.3s ease;
        }
        .modal-body .form-control:focus { border-color: var(--c-empresas) !important; box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-empresas) 12%, transparent) !important; }
        .modal-body .form-control::placeholder { color: var(--text-soft); opacity: 0.7; }

        /* Botones del modal */
        .btn-modal-cancel {
            display: flex; align-items: center; gap: 6px;
            padding: 9px 20px; font-size: 13px; font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            background: color-mix(in srgb, #DC2626 10%, transparent);
            color: #DC2626;
            border: 1.5px solid color-mix(in srgb, #DC2626 30%, transparent);
            border-radius: 7px; cursor: pointer; transition: opacity 0.15s ease; flex: 1;
        }
        .btn-modal-cancel:hover { opacity: 0.8; }
        .btn-modal-cancel svg { width: 13px; height: 13px; stroke: #DC2626; fill: none; stroke-width: 2.5; stroke-linecap: round; }

        .btn-modal-add {
            display: flex; align-items: center; gap: 6px;
            padding: 9px 20px; font-size: 13px; font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            background: #16A34A; color: #fff;
            border: none; border-radius: 7px; cursor: pointer;
            transition: opacity 0.15s ease, transform 0.1s ease; flex: 1;
            justify-content: center;
        }
        .btn-modal-add:hover  { opacity: 0.88; }
        .btn-modal-add:active { transform: scale(0.98); }
        .btn-modal-add svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2.5; stroke-linecap: round; }

        .btn-close { filter: var(--btn-close-filter, none); }
        [data-theme="dark"] .btn-close { filter: invert(1); }

        /* ── CONFIRM ELIMINAR ────────────────────────────── */
        .confirm-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9998; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; }
        .confirm-overlay.show { opacity: 1; pointer-events: all; }
        .confirm-box { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 28px 28px 22px; width: 100%; max-width: 360px; text-align: center; transform: translateY(12px) scale(0.97); transition: transform 0.2s ease; }
        .confirm-overlay.show .confirm-box { transform: translateY(0) scale(1); }
        .confirm-icon { width: 44px; height: 44px; border-radius: 50%; background: color-mix(in srgb,#DC2626 10%,transparent); color:#DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 20px; }
        .confirm-title { font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 6px; }
        .confirm-desc  { font-size: 13px; color: var(--text-soft); margin-bottom: 22px; }
        .confirm-actions { display: flex; gap: 10px; justify-content: center; }
        .confirm-cancel { padding: 9px 20px; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; background: transparent; border: 1px solid var(--border); color: var(--text-soft); border-radius: 7px; cursor: pointer; transition: all 0.15s ease; flex: 1; }
        .confirm-cancel:hover { background: var(--bg); color: var(--text-main); }
        .confirm-ok { padding: 9px 20px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; background: #DC2626; color: #fff; border: none; border-radius: 7px; cursor: pointer; transition: opacity 0.15s ease; flex: 1; }
        .confirm-ok:hover { opacity: 0.88; }

        /* ── MODAL EMPRESA EN USO ────────────────────────── */
        .en-uso-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; }
        .en-uso-overlay.show { opacity: 1; pointer-events: all; }
        .en-uso-box { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 28px 28px 22px; width: 100%; max-width: 380px; text-align: center; transform: translateY(12px) scale(0.97); transition: transform 0.2s ease; }
        .en-uso-overlay.show .en-uso-box { transform: translateY(0) scale(1); }
        .en-uso-icon-wrap { width: 52px; height: 52px; border-radius: 50%; background: color-mix(in srgb, #D97706 12%, transparent); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .en-uso-icon-wrap svg { width: 24px; height: 24px; stroke: #D97706; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .en-uso-title { font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
        .en-uso-desc { font-size: 13px; color: var(--text-soft); margin-bottom: 22px; line-height: 1.6; }
        .en-uso-desc strong { color: var(--text-main); }
        .en-uso-close { padding: 9px 28px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; background: color-mix(in srgb, #D97706 12%, transparent); color: #D97706; border: 1.5px solid color-mix(in srgb, #D97706 35%, transparent); border-radius: 7px; cursor: pointer; transition: opacity 0.15s ease; }
        .en-uso-close:hover { opacity: 0.8; }

        /* ── TOAST ───────────────────────────────────────── */
        .toast-notif { position: fixed; bottom: 28px; right: 28px; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 14px 20px; font-size: 13.5px; font-weight: 500; color: var(--text-main); display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 28px rgba(0,0,0,0.12); z-index: 10000; opacity: 0; transform: translateY(12px); transition: opacity 0.25s ease, transform 0.25s ease; pointer-events: none; min-width: 220px; }
        .toast-notif.show { opacity: 1; transform: translateY(0); }
        .toast-notif .t-icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px; }
        .toast-notif.success .t-icon { background: color-mix(in srgb,#16A34A 15%,transparent); color:#16A34A; }
        .toast-notif.error   .t-icon { background: color-mix(in srgb,#DC2626 15%,transparent); color:#DC2626; }
        .toast-notif.info    .t-icon { background: color-mix(in srgb,#0369A1 15%,transparent); color:#0369A1; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .card:nth-child(1) { animation: fadeUp 0.35s 0.05s ease both; }
        .card:nth-child(2) { animation: fadeUp 0.35s 0.12s ease both; }

        /* ══════════════════════════════════════════════════
           TABLET  (≤ 900px)
        ══════════════════════════════════════════════════ */
        @media (max-width: 900px) {
            .main { margin: 28px auto; }
            .nav-links { display: none; } .nav-user { display: none; }
            .btn-logout { display: none; } .btn-hamburger { display: flex; }
            .nav-brand { margin-right: 10px; }
        }

        /* ══════════════════════════════════════════════════
           MÓVIL  (≤ 640px)
        ══════════════════════════════════════════════════ */
        @media (max-width: 640px) {
            nav { padding: 0 16px; }
            .main { margin: 16px auto; padding: 0 16px; gap: 14px; }
            .card { padding: 16px; }
            .search-row { flex-wrap: wrap; }
            .btn-agregar { width: 100%; justify-content: center; }
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
        <a href="dashboard.php" class="nav-brand">Control de Actas</a>
        <div class="nav-links">
            <a href="dashboard.php"  class="nav-link-item">Inicio</a>
            <a href="empresas.php"   class="nav-link-item active">Empresas</a>
            <a href="actas.php"      class="nav-link-item">Actas</a>
            <a href="tipos_acta.php" class="nav-link-item">Tipos actas</a>
            <a href="solicitudes.php"class="nav-link-item">Solicitudes</a>
            <a href="historial.php"  class="nav-link-item">Historial</a>
            <a href="usuarios.php"   class="nav-link-item">Usuarios</a>
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

<!-- MENÚ DESPLEGABLE -->
<div class="mobile-menu" id="mobileMenu">
    <a href="dashboard.php"  class="nav-link-item">Inicio</a>
    <a href="empresas.php"   class="nav-link-item active">Empresas</a>
    <a href="actas.php"      class="nav-link-item">Actas</a>
    <a href="tipos_acta.php" class="nav-link-item">Tipos actas</a>
    <a href="solicitudes.php"class="nav-link-item">Solicitudes</a>
    <a href="historial.php"  class="nav-link-item">Historial</a>
    <a href="usuarios.php"   class="nav-link-item">Usuarios</a>
    <div class="mobile-menu-footer">
        <span class="nav-user" style="display:block"><?php echo $_SESSION['usuario']; ?></span>
        <a href="../auth/logout.php" class="btn-logout" style="display:block">Cerrar sesión</a>
    </div>
</div>

<!-- CONTENIDO -->
<div class="main">

    <!-- ── CARD: BUSCADOR + BOTÓN AGREGAR ─────────────── -->
    <div class="card">
        <div class="card-header-row">
            <div class="card-title">Empresas</div>
        </div>

        <form method="GET" action="empresas.php">
            <div class="search-row">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="buscar"
                           placeholder="Buscar por nombre o RFC..."
                           value="<?= htmlspecialchars($buscar) ?>">
                </div>
                <button type="button" class="btn-agregar" onclick="abrirModalAgregar()">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Agregar empresa
                </button>
            </div>
            <?php if($buscar != ''): ?>
            <div class="search-result-info">
                <?= $total_filas ?> resultado<?= $total_filas != 1 ? 's' : '' ?> para
                <strong>"<?= htmlspecialchars($buscar) ?>"</strong>
                — <a href="empresas.php" style="color:var(--text-soft);font-size:12px">Limpiar</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- ── CARD: LISTADO ──────────────────────────────── -->
    <div class="card">
        <div class="card-title">Listado de empresas</div>

        <!-- Tabla — tablet y PC -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-num center">#</th>
                        <th>Empresa</th>
                        <th>RFC</th>
                        <th>Fecha de constitución</th>
                        <th class="center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php $num = $num_inicio; while($row = mysqli_fetch_assoc($res)){ ?>
                    <tr>
                        <td class="col-num center"><?= $num++ ?></td>
                        <td><?= $row['nombre_empresa'] ?></td>
                        <td><?= $row['rfc'] ?></td>
                        <td><?= $row['fecha_constitucion'] ?></td>
                        <td class="center">
                            <button class="btn-edit"   onclick="abrirEditarEmpresa(<?= $row['id_empresa'] ?>,'<?= htmlspecialchars($row['nombre_empresa'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['rfc'],ENT_QUOTES) ?>','<?= $row['fecha_constitucion'] ?>')">Editar</button>
                            <button class="btn-delete" onclick="eliminarEmpresa(<?= $row['id_empresa'] ?>)">Eliminar</button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Tarjetas — solo móvil -->
        <div class="card-list">
            <?php mysqli_data_seek($res, 0); $num = $num_inicio; while($row = mysqli_fetch_assoc($res)){ ?>
                <div class="empresa-card">
                    <div class="empresa-card-num">#<?= $num++ ?></div>
                    <div class="empresa-card-body">
                        <div class="empresa-card-name"><?= $row['nombre_empresa'] ?></div>
                        <div class="empresa-card-rfc">RFC: <?= $row['rfc'] ?></div>
                        <div class="empresa-card-fecha">Constitución: <?= $row['fecha_constitucion'] ?></div>
                        <div class="empresa-card-actions">
                            <button class="btn-edit"   onclick="abrirEditarEmpresa(<?= $row['id_empresa'] ?>,'<?= htmlspecialchars($row['nombre_empresa'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['rfc'],ENT_QUOTES) ?>','<?= $row['fecha_constitucion'] ?>')">Editar</button>
                            <button class="btn-delete" onclick="eliminarEmpresa(<?= $row['id_empresa'] ?>)">Eliminar</button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- PAGINACIÓN -->
        <?php if($total_pags > 1): ?>
        <div class="pagination-wrap">
            <div class="pagination-info">
                Mostrando <?= $num_inicio ?>–<?= min($offset + $por_pagina, $total_filas) ?> de <?= $total_filas ?> empresas
            </div>
            <div class="pagination">
                <a class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>" href="?<?= $search_qs ?>p=<?= $pagina - 1 ?>">&#8249;</a>
                <?php
                $rango = 2; $inicio = max(1, $pagina - $rango); $fin = min($total_pags, $pagina + $rango);
                if($inicio > 1){ echo "<a class=\"page-btn\" href=\"?{$search_qs}p=1\">1</a>"; if($inicio > 2) echo '<span class="page-dots">···</span>'; }
                for($i = $inicio; $i <= $fin; $i++){ $activo = $i === $pagina ? 'active' : ''; echo "<a class=\"page-btn $activo\" href=\"?{$search_qs}p=$i\">$i</a>"; }
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

<!-- CONFIRM ELIMINAR -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon">⚠</div>
        <div class="confirm-title" id="confirmTitle">¿Estás seguro?</div>
        <div class="confirm-desc"  id="confirmDesc">Esta acción no se puede deshacer.</div>
        <div class="confirm-actions">
            <button class="confirm-cancel" onclick="cerrarConfirm()">Cancelar</button>
            <button class="confirm-ok"     id="confirmOkBtn">Eliminar</button>
        </div>
    </div>
</div>

<!-- MODAL EMPRESA EN USO -->
<div class="en-uso-overlay" id="enUsoOverlay">
    <div class="en-uso-box">
        <div class="en-uso-icon-wrap">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="en-uso-title">Empresa en uso</div>
        <div class="en-uso-desc">
            Esta empresa <strong>no puede eliminarse</strong> porque tiene actas registradas vinculadas a ella.<br><br>
            Si deseas eliminarla, primero elimina o reasigna las actas asociadas.
        </div>
        <button class="en-uso-close" onclick="cerrarEnUso()">Entendido</button>
    </div>
</div>

<!-- ── MODAL AGREGAR EMPRESA ──────────────────────── -->
<div class="modal fade" id="modalAgregarEmpresa" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <svg viewBox="0 0 24 24"><path d="M3 21h18M9 21V7l6-4v18M9 11h6"/></svg>
            Nueva empresa
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nombre de la empresa</label>
            <input type="text" id="nombre" class="form-control" placeholder="Ej. Corporativo XYZ S.A.">
        </div>
        <div class="mb-3">
            <label class="form-label">RFC</label>
            <input type="text" id="rfc" class="form-control" placeholder="Ej. XYZ010101ABC" maxlength="13">
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha de constitución</label>
            <input type="date" id="fecha" class="form-control">
        </div>
      </div>
      <div class="modal-footer" style="display:flex; gap:10px;">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Cancelar
        </button>
        <button class="btn-modal-add" onclick="agregarEmpresa()">
            <svg viewBox="0 0 24 24"><path d="M3 21h18M9 21V7l6-4v18M9 11h6"/></svg>
            Agregar empresa
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDITAR EMPRESA -->
<div class="modal fade" id="modalEditarEmpresa" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_id">
        <div class="mb-3"><label class="form-label">Nombre</label><input type="text" id="edit_nombre" class="form-control"></div>
        <div class="mb-3"><label class="form-label">RFC</label><input type="text" id="edit_rfc" class="form-control" maxlength="13"></div>
        <div class="mb-3"><label class="form-label">Fecha constitución</label><input type="date" id="edit_fecha" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button class="btn-modal-add" style="max-width:180px" onclick="guardarEdicionEmpresa()">Guardar cambios</button>
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

    /* Abrir modal agregar */
    function abrirModalAgregar() {
        document.getElementById('nombre').value = '';
        document.getElementById('rfc').value    = '';
        document.getElementById('fecha').value  = '';
        new bootstrap.Modal(document.getElementById('modalAgregarEmpresa')).show();
    }

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

    document.getElementById('enUsoOverlay').addEventListener('click', function(e) {
        if (e.target === this) cerrarEnUso();
    });

    window.alert = function(msg) {
        const type = (msg.toLowerCase().includes('error')      ||
                      msg.toLowerCase().includes('falló')      ||
                      msg.toLowerCase().includes('incorrecto') ||
                      msg.toLowerCase().includes('obligatorio'))
                      ? 'error' : 'success';
        showToast(msg, type);
    };
</script>

<script src="../js/empresas.js"></script>

</body>
</html>


