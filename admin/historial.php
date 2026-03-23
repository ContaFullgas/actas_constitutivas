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
    <link href="../css/admin/historial.css" rel="stylesheet">
  
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