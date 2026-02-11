<?php
require_once "../auth/auth_check.php";
require_once "../config/db.php";

$id_usuario = $_SESSION['id_usuario'];

$actas = mysqli_query($conn, "
    SELECT 
        a.*,
        e.nombre_empresa,
        (
            SELECT p.estado
            FROM prestamos p
            WHERE p.id_acta = a.id_acta
              AND p.id_usuario = $id_usuario
              AND p.estado IN ('pendiente','prestado','devolucion_pendiente')
            LIMIT 1
        ) AS estado_prestamo
    FROM actas a
    JOIN empresas e ON a.id_empresa = e.id_empresa
");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitar préstamo</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <div class="navbar-nav">
            <a href="empresas.php" class="nav-link">Inicio</a>
            <a href="solicitar_prestamo.php" class="nav-link active">Solicitar prestamo</a>
            <a href="mis_prestamos.php" class="nav-link">Mis préstamos</a>
            <a href="historial.php" class="nav-link ">Historial</a>
        </div>

        <span class="navbar-brand ms-2">Control de Actas</span>

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

    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-3">Solicitar préstamo de acta</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Portada</th>
                            <th>Empresa</th>
                            <th>Tipo de acta</th>
                            <th>Ubicación</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while($a = mysqli_fetch_assoc($actas)){ ?>
                        <tr>
                            <!-- TD para fotografía de acta -->
                            <td class="text-center">
                                <?php if($a['foto_portada']){ ?>
                                    <img
                                        src="../<?= $a['foto_portada'] ?>"
                                        class="img-thumbnail"
                                        style="width:60px; cursor:pointer;"
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

                                <?php
                                if ($a['estado_prestamo'] == null) {
                                ?>
                                    <button class="btn btn-sm btn-primary"
                                        onclick="solicitarPrestamo(<?= $a['id_acta'] ?>)">
                                        Solicitar
                                    </button>
                                <?php
                                } else if ($a['estado_prestamo'] == 'pendiente') {
                                ?>
                                    <span class="badge bg-warning text-dark">
                                        Solicitud pendiente
                                    </span>
                                <?php
                                } else if ($a['estado_prestamo'] == 'prestado') {
                                ?>
                                    <span class="badge bg-success">
                                        Acta prestada
                                    </span>
                                <?php
                                } else if ($a['estado_prestamo'] == 'devolucion_pendiente') {
                                ?>
                                    <span class="badge bg-info text-dark">
                                        Devolución pendiente
                                    </span>
                                <?php
                                }
                                ?>

                            </td>

                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>

            <div id="msg" class="mt-3"></div>

        </div>
    </div>

</div>

<!-- MODAL VER IMAGEN -->
<div class="modal fade" id="modalImagen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
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
<script src="../js/prestamos_usuario.js"></script>


</body>
</html>
