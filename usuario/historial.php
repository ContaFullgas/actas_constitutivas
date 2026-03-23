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

$where_extra = "";
if($buscar != ''){
    $b = mysqli_real_escape_string($conn, $buscar);
    $where_extra = " AND (e.nombre_empresa LIKE '%$b%' OR t.nombre_tipo LIKE '%$b%' OR a.ubicacion_fisica LIKE '%$b%' OR p.estado LIKE '%$b%')";
}

$total_res = mysqli_query($conn, "
    SELECT COUNT(*) FROM prestamos p
    JOIN actas a ON p.id_acta = a.id_acta
    LEFT JOIN tipos_acta t ON a.id_tipo = t.id_tipo
    JOIN empresas e ON a.id_empresa = e.id_empresa
    WHERE p.id_usuario = $id_usuario
    $where_extra
");
$total_filas = mysqli_fetch_row($total_res)[0];
$total_pags  = max(1, ceil($total_filas / $por_pagina));
$pagina      = min($pagina, $total_pags);
$num_inicio  = $offset + 1;

$search_qs = $buscar ? 'buscar=' . urlencode($buscar) . '&' : '';

$historial = mysqli_query($conn, "
    SELECT
        p.estado,
        p.fecha_solicitud,
        p.fecha_prestamo,
        p.fecha_devolucion,
        t.nombre_tipo,
        a.ubicacion_fisica,
        e.nombre_empresa
    FROM prestamos p
    JOIN actas a ON p.id_acta = a.id_acta
    LEFT JOIN tipos_acta t ON a.id_tipo = t.id_tipo
    JOIN empresas e ON a.id_empresa = e.id_empresa
    WHERE p.id_usuario = $id_usuario
    $where_extra
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
    <link href="../css/usuario/historial.css" rel="stylesheet">

</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-left">
        <a href="empresas.php" class="nav-brand">Control de Actas</a>
        <div class="nav-links">
            <a href="empresas.php"           class="nav-link-item">Inicio</a>
            <a href="solicitar_prestamo.php"  class="nav-link-item">Solicitar préstamo</a>
            <a href="mis_prestamos.php"       class="nav-link-item">Mis préstamos</a>
            <a href="historial.php"           class="nav-link-item active">Historial</a>
        </div>
    </div>
    <div class="nav-right">
        <span class="nav-user"><?= $_SESSION['usuario'] ?></span>
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
    <a href="empresas.php"           class="nav-link-item">Inicio</a>
    <a href="solicitar_prestamo.php"  class="nav-link-item">Solicitar préstamo</a>
    <a href="mis_prestamos.php"       class="nav-link-item">Mis préstamos</a>
    <a href="historial.php"           class="nav-link-item active">Historial</a>
    <div class="mobile-menu-footer">
        <span class="nav-user" style="display:block"><?= $_SESSION['usuario'] ?></span>
        <a href="../auth/logout.php" class="btn-logout" style="display:block">Cerrar sesión</a>
    </div>
</div>

<!-- CONTENIDO -->
<div class="main">

    <!-- ── CARD: BUSCADOR ─────────────────────────────── -->
    <div class="card">
        <div class="card-title">Historial de préstamos</div>
        <form method="GET" action="historial.php">
            <div class="search-row">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="buscar"
                           placeholder="Buscar por empresa, tipo, ubicación o estado..."
                           value="<?= htmlspecialchars($buscar) ?>">
                </div>
            </div>
            <?php if($buscar != ''): ?>
            <div class="search-result-info">
                <?= $total_filas ?> resultado<?= $total_filas != 1 ? 's' : '' ?> para
                <strong>"<?= htmlspecialchars($buscar) ?>"</strong>
                — <a href="historial.php" style="color:var(--text-soft);font-size:12px">Limpiar</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- ── CARD: LISTADO ──────────────────────────────── -->
    <div class="card">
        <div class="card-title">Registros</div>

        <!-- Tabla — PC y tablet -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-num" style="text-align:center">#</th>
                        <th>Empresa</th>
                        <th>Acta</th>
                        <th>Ubicación</th>
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
                        <td><?= $h['nombre_empresa'] ?></td>
                        <td><?= $h['nombre_tipo'] ?? 'Sin tipo' ?></td>
                        <td><?= $h['ubicacion_fisica'] ?></td>
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
                            <div class="historial-card-empresa"><?= $h['nombre_empresa'] ?></div>
                        </div>
                        <span class="badge-estado <?= $cls ?>"><?= $lbl ?></span>
                    </div>
                    <div class="historial-card-meta">Acta: <span><?= $h['nombre_tipo'] ?? 'Sin tipo' ?></span></div>
                    <div class="historial-card-meta">Ubicación: <span><?= $h['ubicacion_fisica'] ?></span></div>
                    <div class="historial-card-meta">Solicitud: <span><?= $h['fecha_solicitud'] ?></span></div>
                    <div class="historial-card-meta">Préstamo: <span><?= $h['fecha_prestamo'] ?? '—' ?></span></div>
                    <div class="historial-card-meta">Devolución: <span><?= $h['fecha_devolucion'] ?? '—' ?></span></div>
                </div>
            <?php } ?>
        </div>

        <!-- PAGINACIÓN -->
        <?php if($total_pags > 1): ?>
        <div class="pagination-wrap">
            <div class="pagination-info">
                Mostrando <?= $num_inicio ?>–<?= min($offset + $por_pagina, $total_filas) ?> de <?= $total_filas ?> registros
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
</script>

</body>
</html>


