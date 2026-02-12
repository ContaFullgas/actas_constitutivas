<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$res = mysqli_query($conn, "SELECT * FROM empresas");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas | Control de Actas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <div class="navbar-nav">
            <a href="dashboard.php" class="nav-link">Inicio</a>
            <a href="empresas.php" class="nav-link active">Empresas</a>
            <a href="actas.php" class="nav-link">Actas</a>
            <a href="solicitudes.php" class="nav-link">Solicitudes</a>
            <a href="historial.php" class="nav-link">Historial</a>
        </div>

        <span class="navbar-brand ms-2">
            Control de Empresas
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

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <h4 class="mb-3">Registrar empresa</h4>

            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" id="nombre" class="form-control" placeholder="Nombre de la empresa">
                </div>

                <div class="col-md-3">
                    <input type="text" id="rfc" class="form-control" placeholder="RFC" maxlength="13">
                </div>

                <div class="col-md-2">
                    <input type="date" id="fecha" class="form-control">
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary" onclick="agregarEmpresa()">
                        Agregar
                    </button>
                </div>
            </div>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-3">Listado de empresas</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Empresa</th>
                            <th>RFC</th>
                            <th>Fecha constitución</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while($row = mysqli_fetch_assoc($res)){ ?>
                        <tr>
                            <td><?= $row['nombre_empresa'] ?></td>
                            <td><?= $row['rfc'] ?></td>
                            <td><?= $row['fecha_constitucion'] ?></td>
                            <td class="text-center">

                                <button class="btn btn-sm btn-warning"
                                    onclick="abrirEditarEmpresa(
                                        <?= $row['id_empresa'] ?>,
                                        '<?= htmlspecialchars($row['nombre_empresa'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($row['rfc'], ENT_QUOTES) ?>',
                                        '<?= $row['fecha_constitucion'] ?>'
                                    )">
                                    Editar
                                </button>

                                <button class="btn btn-sm btn-danger"
                                    onclick="eliminarEmpresa(<?= $row['id_empresa'] ?>)">
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

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/empresas.js"></script>

<!-- MODAL EDITAR EMPRESA -->
<div class="modal fade" id="modalEditarEmpresa" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Editar empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="edit_id">

        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" id="edit_nombre" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">RFC</label>
          <input type="text" id="edit_rfc" class="form-control" maxlength="13">
        </div>

        <div class="mb-3">
          <label class="form-label">Fecha constitución</label>
          <input type="date" id="edit_fecha" class="form-control">
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" onclick="guardarEdicionEmpresa()">
          Guardar cambios
        </button>
      </div>

    </div>
  </div>
</div>


</body>
</html>
