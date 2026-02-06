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
            <a href="dashboard.php" class="nav-link active">Inicio</a>
            <a href="empresas.php" class="nav-link active">Empresas</a>
            <a href="actas.php" class="nav-link active">Actas</a>
            <a href="solicitudes.php" class="nav-link active">Solicitudes</a>
            <a href="historial.php" class="nav-link active">Historial</a>
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
                    <input type="text" id="rfc" class="form-control" placeholder="RFC">
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
<script src="../js/empresas.js"></script>

</body>
</html>
