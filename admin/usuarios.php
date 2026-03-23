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
    $where .= " AND (usuario LIKE '%$b%' OR nombre LIKE '%$b%' OR departamento LIKE '%$b%')";
}

$total_res   = mysqli_query($conn, "SELECT COUNT(*) FROM usuarios $where");
$total_filas = mysqli_fetch_row($total_res)[0];
$total_pags  = max(1, ceil($total_filas / $por_pagina));
$pagina      = min($pagina, $total_pags);
$num_inicio  = $offset + 1;

$search_qs = $buscar ? 'buscar=' . urlencode($buscar) . '&' : '';

$usuarios = mysqli_query($conn, "
    SELECT * FROM usuarios
    $where
    ORDER BY activo DESC, id_usuario ASC
    LIMIT $por_pagina OFFSET $offset
");
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="../css/admin/usuarios.css" rel="stylesheet">

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
            <a href="historial.php"  class="nav-link-item">Historial</a>
            <a href="usuarios.php"   class="nav-link-item active">Usuarios</a>
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
    <a href="empresas.php"   class="nav-link-item">Empresas</a>
    <a href="actas.php"      class="nav-link-item">Actas</a>
    <a href="tipos_acta.php" class="nav-link-item">Tipos actas</a>
    <a href="solicitudes.php"class="nav-link-item">Solicitudes</a>
    <a href="historial.php"  class="nav-link-item">Historial</a>
    <a href="usuarios.php"   class="nav-link-item active">Usuarios</a>
    <div class="mobile-menu-footer">
        <span class="nav-user" style="display:block"><?php echo $_SESSION['usuario']; ?></span>
        <a href="../auth/logout.php" class="btn-logout" style="display:block">Cerrar sesión</a>
    </div>
</div>

<!-- CONTENIDO -->
<div class="main">

    <!-- ── CARD: BUSCADOR + BOTÓN NUEVO ───────────────── -->
    <div class="card">
        <div class="card-title">Monitoreo de usuarios</div>
        <form method="GET" action="usuarios.php">
            <div class="search-row">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="buscar"
                           placeholder="Buscar por usuario, nombre o departamento..."
                           value="<?= htmlspecialchars($buscar) ?>">
                </div>
                <button type="button" class="btn-nuevo" onclick="abrirNuevo()">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nuevo usuario
                </button>
            </div>
            <?php if($buscar != ''): ?>
            <div class="search-result-info">
                <?= $total_filas ?> resultado<?= $total_filas != 1 ? 's' : '' ?> para
                <strong>"<?= htmlspecialchars($buscar) ?>"</strong>
                — <a href="usuarios.php" style="color:var(--text-soft);font-size:12px">Limpiar</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- ── CARD: LISTADO ──────────────────────────────── -->
    <div class="card">
        <div class="card-title">Listado de usuarios</div>

        <!-- Tabla — PC y tablet -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-num center">#</th>
                        <th>Usuario</th><th>Nombre</th><th>Departamento</th>
                        <th>Rol</th><th>Estado</th><th class="center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php $num = $num_inicio; while($u = mysqli_fetch_assoc($usuarios)){ ?>
                    <tr>
                        <td class="col-num center"><?= $num++ ?></td>
                        <td><span class="user-handle"><?= $u['usuario'] ?></span></td>
                        <td><?= $u['nombre'] ?></td>
                        <td><?= $u['departamento'] ?></td>
                        <td><?php if($u['rol']=='admin'){ ?><span class="badge-admin">Admin</span><?php }else{ ?><span class="badge-usuario">Usuario</span><?php } ?></td>
                        <td><?php if($u['activo']==1){ ?><span class="badge-activo">Activo</span><?php }else{ ?><span class="badge-inactivo">Inactivo</span><?php } ?></td>
                        <td class="center">
                            <?php if($u['id_usuario'] != $_SESSION['id_usuario']){ ?>
                                <button class="btn-edit" onclick="abrirEditar(<?= $u['id_usuario'] ?>,'<?= htmlspecialchars($u['usuario'],ENT_QUOTES) ?>','<?= htmlspecialchars($u['nombre'],ENT_QUOTES) ?>','<?= htmlspecialchars($u['departamento'],ENT_QUOTES) ?>','<?= $u['rol'] ?>')">Editar</button>
                                <?php if($u['activo']==1){ ?><button class="btn-deactivate" onclick="cambiarEstado(<?= $u['id_usuario'] ?>,0)">Desactivar</button>
                                <?php }else{ ?><button class="btn-activate" onclick="cambiarEstado(<?= $u['id_usuario'] ?>,1)">Activar</button><?php } ?>
                            <?php }else{ ?><span class="badge-yo">Tu usuario</span><?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Tarjetas — solo móvil -->
        <div class="card-list">
            <?php mysqli_data_seek($usuarios, 0); $num = $num_inicio; while($u = mysqli_fetch_assoc($usuarios)){ ?>
                <div class="usuario-card">
                    <div class="usuario-card-header">
                        <div>
                            <div class="usuario-card-num">#<?= $num++ ?></div>
                            <div class="usuario-card-handle"><?= $u['usuario'] ?></div>
                            <div class="usuario-card-name"><?= $u['nombre'] ?></div>
                        </div>
                        <?php if($u['id_usuario']==$_SESSION['id_usuario']){ ?>
                            <span class="badge-yo">Tu usuario</span>
                        <?php }elseif($u['activo']==1){ ?>
                            <span class="badge-activo">Activo</span>
                        <?php }else{ ?>
                            <span class="badge-inactivo">Inactivo</span>
                        <?php } ?>
                    </div>
                    <div class="usuario-card-badges">
                        <?php if($u['rol']=='admin'){ ?><span class="badge-admin">Admin</span><?php }else{ ?><span class="badge-usuario">Usuario</span><?php } ?>
                    </div>
                    <div class="usuario-card-meta">Depto: <span><?= $u['departamento'] ?></span></div>
                    <?php if($u['id_usuario'] != $_SESSION['id_usuario']){ ?>
                        <div class="usuario-card-actions">
                            <button class="btn-edit" onclick="abrirEditar(<?= $u['id_usuario'] ?>,'<?= htmlspecialchars($u['usuario'],ENT_QUOTES) ?>','<?= htmlspecialchars($u['nombre'],ENT_QUOTES) ?>','<?= htmlspecialchars($u['departamento'],ENT_QUOTES) ?>','<?= $u['rol'] ?>')">Editar</button>
                            <?php if($u['activo']==1){ ?><button class="btn-deactivate" onclick="cambiarEstado(<?= $u['id_usuario'] ?>,0)">Desactivar</button>
                            <?php }else{ ?><button class="btn-activate" onclick="cambiarEstado(<?= $u['id_usuario'] ?>,1)">Activar</button><?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <!-- PAGINACIÓN -->
        <?php if($total_pags > 1): ?>
        <div class="pagination-wrap">
            <div class="pagination-info">
                Mostrando <?= $num_inicio ?>–<?= min($offset + $por_pagina, $total_filas) ?> de <?= $total_filas ?> usuarios
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
        <div class="confirm-icon">⚠</div>
        <div class="confirm-title" id="confirmTitle">¿Estás seguro?</div>
        <div class="confirm-desc"  id="confirmDesc">Esta acción no se puede deshacer.</div>
        <div class="confirm-actions">
            <button class="confirm-cancel" onclick="cerrarConfirm()">Cancelar</button>
            <button class="confirm-ok"     id="confirmOkBtn">Confirmar</button>
        </div>
    </div>
</div>

<!-- MODAL USUARIO -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_usuario">
                <div class="mb-3"><label>Usuario</label><input type="text" id="usuario" class="form-control"></div>
                <div class="mb-3"><label>Nombre</label><input type="text" id="nombre" class="form-control"></div>
                <div class="mb-3">
                    <label>Departamento</label>
                    <select id="departamento" class="form-select">
                        <option value="">Seleccionar departamento...</option>
                        <option value="Contabilidad">Contabilidad</option>
                        <option value="Facturación">Facturación</option>
                        <option value="Sistemas">Sistemas</option>
                        <option value="Nominas">Nominas</option>
                        <option value="Fiscal">Fiscal</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="text" id="password" class="form-control">
                    <div class="form-hint">Dejar vacío para no cambiar</div>
                </div>
                <div class="mb-3">
                    <label>Rol</label>
                    <select id="rol" class="form-select">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" data-bs-dismiss="modal">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Cancelar
                </button>
                <button class="btn-modal-add" onclick="guardarUsuario()">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Guardar
                </button>
            </div>
        </div>
    </div>
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

    function abrirNuevo() {
        document.getElementById('id_usuario').value  = '';
        document.getElementById('usuario').value     = '';
        document.getElementById('nombre').value      = '';
        document.getElementById('password').value    = '';
        document.getElementById('departamento').value = '';
        document.getElementById('rol').value         = 'usuario';
        new bootstrap.Modal(document.getElementById('modalUsuario')).show();
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

    window.alert = function(msg) {
        const type = (msg.toLowerCase().includes('error')      ||
                      msg.toLowerCase().includes('falló')      ||
                      msg.toLowerCase().includes('incorrecto') ||
                      msg.toLowerCase().includes('obligatorio'))
                      ? 'error' : 'success';
        showToast(msg, type);
    };
</script>

<script src="../js/usuarios.js"></script>

</body>
</html>


