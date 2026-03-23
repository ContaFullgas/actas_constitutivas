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
    <link href="../css/admin/actas.css" rel="stylesheet">
    
    
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


