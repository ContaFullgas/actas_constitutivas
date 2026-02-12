<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$empresas = mysqli_query($conn, "SELECT * FROM empresas");
$actas = mysqli_query($conn, "
    SELECT a.*, e.nombre_empresa 
    FROM actas a 
    JOIN empresas e ON a.id_empresa = e.id_empresa
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actas | Control de Actas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap theme (opcional pero recomendado) -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <div class="navbar-nav">
            <a href="dashboard.php" class="nav-link">Inicio</a>
            <a href="empresas.php" class="nav-link">Empresas</a>
            <a href="actas.php" class="nav-link active">Actas</a>
            <a href="solicitudes.php" class="nav-link">Solicitudes</a>
            <a href="historial.php" class="nav-link">Historial</a>
        </div>

        <span class="navbar-brand ms-2">
            Control de Actas
        </span>

        <div class="d-flex text-white">
            <span class="me-3">
                <?php echo $_SESSION['usuario']; ?>
            </span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                Cerrar sesión
            </a>
        </div>

    </div>
</nav>


<!-- CONTENIDO -->
<div class="container mt-4">

    <!-- FORMULARIO -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <h4 class="mb-3">Registrar acta</h4>

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Empresa</label>
                    <select id="empresa" class="form-select">
                        <option value="">Seleccionar empresa...</option>

                        <?php while($e = mysqli_fetch_assoc($empresas)){ ?>
                            <option value="<?= $e['id_empresa'] ?>">
                                <?= $e['nombre_empresa'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de acta</label>
                    <input type="text" id="tipo" class="form-control" placeholder="Ej. Acta constitutiva">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Ubicación física</label>
                    <input type="text" id="ubicacion" class="form-control" placeholder="Archivo / Caja / Estante">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Foto de la portada (opcional)</label>
                    <input type="file" id="foto" class="form-control"
                        accept="image/png, image/jpeg">
                </div>

                <div class="col-md-1 d-grid align-items-end">
                    <button class="btn btn-primary mt-4" onclick="agregarActa()">
                        Agregar
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- TABLA -->
    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-3">Listado de actas</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Portada</th>
                            <th>Empresa</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while($a = mysqli_fetch_assoc($actas)){ ?>
                        <tr>
                            <td class="text-center">
                                <?php if($a['foto_portada']){ ?>
                                    <img 
                                        src="../<?= $a['foto_portada'] ?>"
                                        class="img-thumbnail"
                                        style="width:40px; cursor:pointer;"
                                        onclick="verImagen('../<?= $a['foto_portada'] ?>')"
                                        alt="Portada acta">
                                <?php } else { ?>
                                    <span class="text-muted">Sin foto</span>
                                <?php } ?>
                            </td>

                            <td><?= $a['nombre_empresa'] ?></td>
                            <td><?= $a['tipo_acta'] ?></td>
                            <td><?= $a['ubicacion_fisica'] ?></td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-warning"
                                    onclick="abrirEditarActa(
                                        <?= $a['id_acta'] ?>,
                                        <?= $a['id_empresa'] ?>,
                                        '<?= htmlspecialchars($a['tipo_acta'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($a['ubicacion_fisica'], ENT_QUOTES) ?>'
                                    )">
                                    Editar
                                </button>

                                <button class="btn btn-sm btn-danger"
                                    onclick="eliminarActa(<?= $a['id_acta'] ?>)">
                                    Eliminar
                                </button>

                            </td>

                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>

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

<!-- MODAL EDITAR ACTA -->
<div class="modal fade" id="modalEditarActa" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Editar acta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="edit_id_acta">

        <div class="mb-3">
          <label class="form-label">Empresa</label>
          <select id="edit_empresa" class="form-select">
            <option value="">Seleccionar empresa...</option>

            <?php
            $empresas2 = mysqli_query($conn, "SELECT * FROM empresas ORDER BY nombre_empresa ASC");
            while($e2 = mysqli_fetch_assoc($empresas2)){
            ?>
            <option value="<?= $e2['id_empresa'] ?>">
                <?= $e2['nombre_empresa'] ?>
            </option>
            <?php } ?>
        </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Tipo de acta</label>
          <input type="text" id="edit_tipo" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Ubicación física</label>
          <input type="text" id="edit_ubicacion" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Cambiar foto (opcional)</label>
          <input type="file" id="edit_foto" class="form-control"
                 accept="image/png, image/jpeg">
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" onclick="guardarEdicionActa()">
          Guardar cambios
        </button>
      </div>

    </div>
  </div>
</div>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="../js/actas.js"></script>

</body>
</html>
