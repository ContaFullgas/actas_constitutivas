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
    <link href="../css/usuario/solicitar_prestamo.css" rel="stylesheet">

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


