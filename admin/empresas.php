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
    <link href="../css/admin/empresas.css" rel="stylesheet">

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


