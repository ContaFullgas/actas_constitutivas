<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$empresas = mysqli_query($conn, "SELECT * FROM empresas");

/* ── Búsqueda ───────────────────────────────────────────────── */
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

/* ── Paginación ─────────────────────────────────────────────── */
$por_pagina  = 10;
$pagina      = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset      = ($pagina - 1) * $por_pagina;

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
    SELECT a.*, e.nombre_empresa, t.nombre_tipo
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
    <title>Actas | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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

        :root { --c-actas: #0F766E; }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text-main); min-height: 100vh; transition: background 0.3s ease, color 0.3s ease; }

        /* ── NAVBAR ──────────────────────────────────────── */
        nav { background: var(--nav-bg); border-bottom: 1px solid var(--nav-border); height: 56px; display: flex; align-items: center; padding: 0 28px; justify-content: space-between; position: sticky; top: 0; z-index: 200; transition: background 0.3s ease, border-color 0.3s ease; }
        .nav-left { display: flex; align-items: center; gap: 4px; }
        .nav-brand { font-size: 15px; font-weight: 600; letter-spacing: -0.3px; color: var(--text-main); display: flex; align-items: center; gap: 8px; text-decoration: none; margin-right: 20px; white-space: nowrap; }
        .nav-brand::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--c-actas); display: block; flex-shrink: 0; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-link-item { font-size: 13px; font-weight: 500; color: var(--text-soft); text-decoration: none; padding: 5px 10px; border-radius: 6px; transition: all 0.15s ease; white-space: nowrap; }
        .nav-link-item:hover { color: var(--text-main); background: var(--bg); }
        .nav-link-item.active { color: var(--c-actas); background: color-mix(in srgb, var(--c-actas) 8%, transparent); font-weight: 600; }
        .nav-right { display: flex; align-items: center; gap: 10px; }
        .nav-user { font-size: 13px; font-weight: 500; color: var(--text-soft); font-family: 'DM Mono', monospace; }
        .btn-logout { font-size: 12px; font-weight: 500; color: var(--text-soft); border: 1px solid var(--border); background: transparent; padding: 5px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; transition: all 0.15s ease; }
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
        .main { max-width: 1100px; margin: 40px auto; padding: 0 24px; display: flex; flex-direction: column; gap: 20px; }

        .card { background: var(--surface); border-radius: 12px; padding: 24px; border: 1.5px solid #C0C5CF; box-shadow: 0 4px 16px rgba(0,0,0,0.07); transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease; }
        [data-theme="dark"] .card { border: 1px solid var(--border); box-shadow: none; }

        .card-title { font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 18px; letter-spacing: -0.2px; }

        /* ── BUSCADOR + BOTÓN AGREGAR ────────────────────── */
        .search-row { display: flex; gap: 8px; align-items: center; }
        .search-wrap { flex: 1; display: flex; align-items: center; gap: 8px; background: var(--input-bg); border: 1px solid var(--border); border-radius: 7px; padding: 0 12px; transition: border-color 0.15s ease, box-shadow 0.15s ease; }
        .search-wrap:focus-within { border-color: #2563EB; box-shadow: 0 0 0 3px color-mix(in srgb, #2563EB 12%, transparent); }
        .search-wrap svg { width: 14px; height: 14px; stroke: var(--text-soft); fill: none; stroke-width: 2; stroke-linecap: round; flex-shrink: 0; }
        .search-wrap input { border: none; background: transparent; outline: none; font-size: 13px; font-family: 'DM Sans', sans-serif; color: var(--text-main); width: 100%; padding: 8px 0; }
        .search-wrap input::placeholder { color: var(--text-soft); opacity: 0.7; }
        .btn-agregar { display: flex; align-items: center; justify-content: center; gap: 7px; width: 152px; height: 36px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; background: #2563EB; color: #fff; border: none; border-radius: 7px; cursor: pointer; white-space: nowrap; transition: opacity 0.15s ease; flex-shrink: 0; }
        .btn-agregar:hover { opacity: 0.88; }
        .btn-agregar svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2.5; stroke-linecap: round; }
        .search-result-info { font-size: 12px; color: var(--text-soft); margin-top: 10px; }
        .search-result-info strong { color: #2563EB; }

        /* ── SELECT2 DARK MODE ───────────────────────────── */
        [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection { background: var(--input-bg) !important; border-color: var(--border) !important; color: var(--text-main) !important; }
        [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered { color: var(--text-main) !important; }
        [data-theme="dark"] .select2-dropdown { background: var(--surface) !important; border-color: var(--border) !important; color: var(--text-main) !important; }
        [data-theme="dark"] .select2-results__option { color: var(--text-main) !important; }
        [data-theme="dark"] .select2-results__option--highlighted { background: color-mix(in srgb, var(--c-actas) 20%, transparent) !important; color: var(--text-main) !important; }
        [data-theme="dark"] .select2-results__option { background: var(--surface) !important; }
        .select2-search--dropdown { display: none !important; }

        /* ── TABLA ───────────────────────────────────────── */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead tr { background: var(--table-head); border-bottom: 1px solid var(--border); }
        thead th { padding: 10px 14px; font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-soft); text-align: left; }
        thead th.center { text-align: center; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s ease; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--table-row-hover); }
        tbody td { padding: 11px 14px; color: var(--text-main); vertical-align: middle; }
        tbody td.center { text-align: center; }
        .col-num { width: 48px; text-align: center; font-family: 'DM Mono', monospace; font-size: 12px; color: var(--text-soft); }
        .thumb { width: 38px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); cursor: pointer; transition: opacity 0.15s ease; }
        .thumb:hover { opacity: 0.8; }
        .no-foto { font-size: 12px; color: var(--text-soft); font-style: italic; }

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
        .card-list { display: none; flex-direction: column; gap: 10px; }
        .acta-card { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px; display: flex; gap: 14px; align-items: flex-start; }
        .acta-card-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); flex-shrink: 0; cursor: pointer; }
        .acta-card-thumb-empty { width: 52px; height: 52px; border-radius: 8px; border: 1px dashed var(--border); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-soft); text-align: center; }
        .acta-card-body { flex: 1; min-width: 0; }
        .acta-card-num { font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 600; background: color-mix(in srgb, #2563EB 10%, transparent); color: #2563EB; border-radius: 6px; padding: 2px 7px; display: inline-block; margin-bottom: 5px; }
        .acta-card-empresa { font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 2px; }
        .acta-card-tipo    { font-size: 12px; color: var(--text-soft); margin-bottom: 2px; }
        .acta-card-ubic    { font-size: 12px; color: var(--text-soft); margin-bottom: 10px; }
        .acta-card-actions { display: flex; gap: 8px; }

        @media (max-width: 640px) {
            .acta-card-actions { flex-direction: column; gap: 6px; }
            .btn-edit, .btn-delete { width: 100%; text-align: center; padding: 7px 12px; }
        }

        /* ── BOTONES ACCIÓN ──────────────────────────────── */
        .btn-edit, .btn-delete { font-size: 12px; font-weight: 500; font-family: 'DM Sans', sans-serif; padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer; transition: opacity 0.15s ease; }
        .btn-edit   { background: color-mix(in srgb, #2563EB 12%, transparent); color: #2563EB; }
        .btn-delete { background: color-mix(in srgb, #DC2626 12%, transparent); color: #DC2626; }
        .btn-edit:hover, .btn-delete:hover { opacity: 0.75; }
        [data-theme="dark"] .btn-edit { background: color-mix(in srgb, #3B82F6 15%, transparent); color: #3B82F6; }

        /* ── MODALES ─────────────────────────────────────── */
        .modal-content { background: var(--surface) !important; border: 1px solid var(--border) !important; border-radius: 14px !important; color: var(--text-main) !important; }
        .modal-header  { border-bottom: 1px solid var(--border) !important; padding: 18px 22px !important; }
        .modal-title   { font-size: 15px !important; font-weight: 600 !important; color: var(--text-main) !important; display: flex; align-items: center; gap: 8px; }
        .modal-title svg { width: 18px; height: 18px; stroke: var(--c-actas); fill: none; stroke-width: 1.8; stroke-linecap: round; flex-shrink: 0; }
        .modal-body    { padding: 22px 22px 16px !important; }
        .modal-footer  { border-top: 1px solid var(--border) !important; padding: 14px 22px !important; display: flex; gap: 10px; }
        .modal-body .form-label { font-size: 12px; font-weight: 500; color: var(--text-soft); margin-bottom: 5px; display: block; }
        .modal-body .form-control, .modal-body .form-select { background: var(--input-bg) !important; border-color: var(--border) !important; color: var(--text-main) !important; border-radius: 7px !important; }
        .modal-body .form-control:focus, .modal-body .form-select:focus { border-color: var(--c-actas) !important; box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-actas) 12%, transparent) !important; }
        #imgGrande { border-radius: 8px; max-height: 70vh; object-fit: contain; }
        .btn-close { filter: var(--btn-close-filter, none); }
        [data-theme="dark"] .btn-close { filter: invert(1); }

        /* ── DRAG & DROP ZONA ────────────────────────────── */
        .drop-zone {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 20px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
            background: var(--input-bg);
        }

        .drop-zone:hover,
        .drop-zone.drag-over {
            border-color: var(--c-actas);
            background: color-mix(in srgb, var(--c-actas) 5%, transparent);
        }

        .drop-zone.has-file {
            border-color: var(--c-actas);
            border-style: solid;
        }

        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .drop-zone-icon {
            width: 36px; height: 36px;
            margin: 0 auto 8px;
            color: var(--text-soft);
        }

        .drop-zone-icon svg {
            width: 100%; height: 100%;
            fill: none; stroke: currentColor;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }

        .drop-zone-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 3px;
        }

        .drop-zone-hint {
            font-size: 11.5px;
            color: var(--text-soft);
        }

        .drop-zone-filename {
            display: none;
            font-size: 12px;
            font-weight: 600;
            color: var(--c-actas);
            margin-top: 6px;
            word-break: break-all;
        }

        .drop-zone.has-file .drop-zone-filename { display: block; }
        .drop-zone.has-file .drop-zone-label    { color: var(--text-soft); font-size: 12px; }

        .drop-zone-preview {
            display: none;
            width: 64px; height: 64px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border);
            margin: 0 auto 8px;
        }

        .drop-zone.has-file .drop-zone-preview  { display: block; }
        .drop-zone.has-file .drop-zone-icon     { display: none; }

        /* Botones del modal */
        .btn-modal-cancel { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 20px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; background: color-mix(in srgb, #DC2626 10%, transparent); color: #DC2626; border: 1.5px solid color-mix(in srgb, #DC2626 30%, transparent); border-radius: 7px; cursor: pointer; transition: opacity 0.15s ease; flex: 1; }
        .btn-modal-cancel:hover { opacity: 0.8; }
        .btn-modal-cancel svg { width: 13px; height: 13px; stroke: #DC2626; fill: none; stroke-width: 2.5; stroke-linecap: round; }
        .btn-modal-add { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 20px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; background: #16A34A; color: #fff; border: none; border-radius: 7px; cursor: pointer; transition: opacity 0.15s ease, transform 0.1s ease; flex: 1; }
        .btn-modal-add:hover  { opacity: 0.88; }
        .btn-modal-add:active { transform: scale(0.98); }
        .btn-modal-add svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2.5; stroke-linecap: round; }

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
        .confirm-icon { width: 44px; height: 44px; border-radius: 50%; background: color-mix(in srgb,#DC2626 10%,transparent); color:#DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 20px; }
        .confirm-title { font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 6px; }
        .confirm-desc  { font-size: 13px; color: var(--text-soft); margin-bottom: 22px; }
        .confirm-actions { display: flex; gap: 10px; justify-content: center; }
        .confirm-cancel { padding: 9px 20px; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; background: transparent; border: 1px solid var(--border); color: var(--text-soft); border-radius: 7px; cursor: pointer; transition: all 0.15s ease; flex: 1; }
        .confirm-cancel:hover { background: var(--bg); color: var(--text-main); }
        .confirm-ok { padding: 9px 20px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; background: #DC2626; color: #fff; border: none; border-radius: 7px; cursor: pointer; transition: opacity 0.15s ease; flex: 1; }
        .confirm-ok:hover { opacity: 0.88; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .card:nth-child(1) { animation: fadeUp 0.35s 0.05s ease both; }
        .card:nth-child(2) { animation: fadeUp 0.35s 0.12s ease both; }

        @media (max-width: 900px) {
            .main { margin: 28px auto; }
            .nav-links { display: none; } .nav-user { display: none; }
            .btn-logout { display: none; } .btn-hamburger { display: flex; }
            .nav-brand { margin-right: 10px; }
        }

        @media (max-width: 640px) {
            nav { padding: 0 16px; }
            .main { margin: 16px auto; padding: 0 16px; gap: 14px; }
            .card { padding: 16px; }
            .search-row { flex-wrap: wrap; }
            .btn-agregar { width: 100%; }
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
            <a href="empresas.php"   class="nav-link-item">Empresas</a>
            <a href="actas.php"      class="nav-link-item active">Actas</a>
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
                <line x1="12" y1="1"  x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
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
    <a href="empresas.php"   class="nav-link-item">Empresas</a>
    <a href="actas.php"      class="nav-link-item active">Actas</a>
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

    <div class="card">
        <div class="card-title">Actas</div>
        <form method="GET" action="actas.php">
            <div class="search-row">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="buscar"
                           placeholder="Buscar por empresa, tipo o ubicación..."
                           value="<?= htmlspecialchars($buscar) ?>">
                </div>
                <button type="button" class="btn-agregar" onclick="abrirModalAgregarActa()">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Agregar acta
                </button>
            </div>
            <?php if($buscar != ''): ?>
            <div class="search-result-info">
                <?= $total_filas ?> resultado<?= $total_filas != 1 ? 's' : '' ?> para
                <strong>"<?= htmlspecialchars($buscar) ?>"</strong>
                — <a href="actas.php" style="color:var(--text-soft);font-size:12px">Limpiar</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Listado de actas</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-num center">#</th>
                        <th class="center">Portada</th>
                        <th>Empresa</th>
                        <th>Tipo</th>
                        <th>Ubicación</th>
                        <th class="center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php $num = $num_inicio; while($a = mysqli_fetch_assoc($actas)){ ?>
                    <tr>
                        <td class="col-num center"><?= $num++ ?></td>
                        <td class="center">
                            <?php if($a['foto_portada']){ ?>
                                <img src="../<?= $a['foto_portada'] ?>" class="thumb" onclick="verImagen('../<?= $a['foto_portada'] ?>')" alt="Portada acta">
                            <?php } else { ?>
                                <span class="no-foto">Sin foto</span>
                            <?php } ?>
                        </td>
                        <td><?= $a['nombre_empresa'] ?></td>
                        <td><?= $a['nombre_tipo'] ?? 'Sin tipo' ?></td>
                        <td><?= $a['ubicacion_fisica'] ?></td>
                        <td class="center">
                            <button class="btn-edit" onclick="abrirEditarActa(<?= $a['id_acta'] ?>,<?= $a['id_empresa'] ?>,<?= $a['id_tipo'] ?? 'null' ?>,'<?= htmlspecialchars($a['ubicacion_fisica'], ENT_QUOTES) ?>','<?= $a['foto_portada'] ? '../'.$a['foto_portada'] : '' ?>')">Editar</button>
                            <button class="btn-delete" onclick="eliminarActa(<?= $a['id_acta'] ?>)">Eliminar</button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="card-list">
            <?php mysqli_data_seek($actas, 0); $num = $num_inicio; while($a = mysqli_fetch_assoc($actas)){ ?>
                <div class="acta-card">
                    <?php if($a['foto_portada']){ ?>
                        <img src="../<?= $a['foto_portada'] ?>" class="acta-card-thumb" onclick="verImagen('../<?= $a['foto_portada'] ?>')" alt="Portada">
                    <?php } else { ?>
                        <div class="acta-card-thumb-empty">Sin foto</div>
                    <?php } ?>
                    <div class="acta-card-body">
                        <div class="acta-card-num">#<?= $num++ ?></div>
                        <div class="acta-card-empresa"><?= $a['nombre_empresa'] ?></div>
                        <div class="acta-card-tipo">Tipo: <?= $a['nombre_tipo'] ?? 'Sin tipo' ?></div>
                        <div class="acta-card-ubic">Ubicación: <?= $a['ubicacion_fisica'] ?></div>
                        <div class="acta-card-actions">
                            <button class="btn-edit" onclick="abrirEditarActa(<?= $a['id_acta'] ?>,<?= $a['id_empresa'] ?>,<?= $a['id_tipo'] ?? 'null' ?>,'<?= htmlspecialchars($a['ubicacion_fisica'], ENT_QUOTES) ?>','<?= $a['foto_portada'] ? '../'.$a['foto_portada'] : '' ?>')">Editar</button>
                            <button class="btn-delete" onclick="eliminarActa(<?= $a['id_acta'] ?>)">Eliminar</button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if($total_pags > 1): ?>
        <div class="pagination-wrap">
            <div class="pagination-info">
                Mostrando <?= $num_inicio ?>–<?= min($offset + $por_pagina, $total_filas) ?> de <?= $total_filas ?> actas
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

<!-- CONFIRM -->
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

<!-- MODAL AGREGAR ACTA -->
<div class="modal fade" id="modalAgregarActa" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Nueva acta
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Empresa</label>
            <select id="empresa" class="form-select">
                <option value="">Seleccionar empresa...</option>
                <?php mysqli_data_seek($empresas, 0); while($e = mysqli_fetch_assoc($empresas)){ ?>
                    <option value="<?= $e['id_empresa'] ?>"><?= $e['nombre_empresa'] ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo de acta</label>
            <select id="tipo" class="form-select">
                <option value="">Seleccionar tipo...</option>
                <?php
                $tipos = mysqli_query($conn,"SELECT * FROM tipos_acta WHERE activo = 1 ORDER BY nombre_tipo ASC");
                while($t = mysqli_fetch_assoc($tipos)){
                ?>
                    <option value="<?= $t['id_tipo'] ?>"><?= $t['nombre_tipo'] ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Ubicación física</label>
            <input type="text" id="ubicacion" class="form-control" placeholder="Archivo / Caja / Estante">
        </div>
        <div class="mb-3">
            <label class="form-label">Foto de portada (opcional)</label>
            <div class="drop-zone" id="dropZoneAdd">
                <input type="file" id="foto" accept="image/png, image/jpeg, image/webp">
                <img class="drop-zone-preview" id="previewAdd" src="" alt="">
                <div class="drop-zone-icon">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div class="drop-zone-label">Arrastra una imagen aquí o haz clic</div>
                <div class="drop-zone-hint">PNG, JPG o WebP</div>
                <div class="drop-zone-filename" id="filenameAdd"></div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Cancelar
        </button>
        <button class="btn-modal-add" onclick="agregarActa()">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Agregar acta
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDITAR ACTA -->
<div class="modal fade" id="modalEditarActa" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar acta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_id_acta">
        <div class="mb-3"><label class="form-label">Empresa</label>
          <select id="edit_empresa" class="form-select">
            <option value="">Seleccionar empresa...</option>
            <?php $empresas2 = mysqli_query($conn, "SELECT * FROM empresas ORDER BY nombre_empresa ASC"); while($e2 = mysqli_fetch_assoc($empresas2)){ ?>
            <option value="<?= $e2['id_empresa'] ?>"><?= $e2['nombre_empresa'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Tipo de acta</label>
          <select id="edit_tipo" class="form-select">
            <option value="">Seleccionar tipo...</option>
            <?php $tiposEdit = mysqli_query($conn,"SELECT * FROM tipos_acta WHERE activo = 1 ORDER BY nombre_tipo ASC"); while($te = mysqli_fetch_assoc($tiposEdit)){ ?>
              <option value="<?= $te['id_tipo'] ?>"><?= $te['nombre_tipo'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Ubicación física</label><input type="text" id="edit_ubicacion" class="form-control"></div>
        <div class="mb-3">
            <label class="form-label">Cambiar foto (opcional)</label>
            <div class="drop-zone" id="dropZoneEdit">
                <input type="file" id="edit_foto" accept="image/png, image/jpeg, image/webp">
                <img class="drop-zone-preview" id="previewEdit" src="" alt="">
                <div class="drop-zone-icon">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div class="drop-zone-label">Arrastra una imagen aquí o haz clic</div>
                <div class="drop-zone-hint">PNG, JPG o WebP</div>
                <div class="drop-zone-filename" id="filenameEdit"></div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-modal-add" onclick="guardarEdicionActa()">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

    function abrirModalAgregarActa() {
        document.getElementById('ubicacion').value = '';
        resetDropZone('dropZoneAdd', 'foto', 'previewAdd', 'filenameAdd');
        new bootstrap.Modal(document.getElementById('modalAgregarActa')).show();
    }

    // ── DRAG & DROP ──────────────────────────────────────
    function initDropZone(zoneId, inputId, previewId, filenameId) {
        const zone     = document.getElementById(zoneId);
        const input    = document.getElementById(inputId);
        const preview  = document.getElementById(previewId);
        const filename = document.getElementById(filenameId);

        function handleFile(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;

            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                zone.classList.add('has-file');
                filename.textContent = file.name;
            };
            reader.readAsDataURL(file);
        }

        input.addEventListener('change', () => {
            if (input.files[0]) handleFile(input.files[0]);
        });

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('drag-over');
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            handleFile(file);
        });
    }

    function resetDropZone(zoneId, inputId, previewId, filenameId) {
        const zone     = document.getElementById(zoneId);
        const input    = document.getElementById(inputId);
        const preview  = document.getElementById(previewId);
        const filename = document.getElementById(filenameId);
        zone.classList.remove('has-file', 'drag-over');
        preview.src    = '';
        filename.textContent = '';
        input.value    = '';
    }

    // Inicializar ambas zonas
    initDropZone('dropZoneAdd',  'foto',      'previewAdd',  'filenameAdd');
    initDropZone('dropZoneEdit', 'edit_foto', 'previewEdit', 'filenameEdit');

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
        const oldBtn = document.getElementById('confirmOkBtn');
        const newBtn = oldBtn.cloneNode(true);
        oldBtn.parentNode.replaceChild(newBtn, oldBtn);
        newBtn.addEventListener('click', function () { cerrarConfirm(); onOk(); });
        document.getElementById('confirmOverlay').classList.add('show');
    }

    function cerrarConfirm() { document.getElementById('confirmOverlay').classList.remove('show'); }

    document.getElementById('confirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) cerrarConfirm();
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

<script src="../js/actas.js"></script>

</body>
</html>


