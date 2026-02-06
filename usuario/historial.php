<?php
require_once "../auth/auth_check.php";
require_once "../config/db.php";

$id_usuario = $_SESSION['id_usuario'];

$historial = mysqli_query($conn, "
    SELECT 
        p.estado,
        p.fecha_solicitud,
        p.fecha_prestamo,
        p.fecha_devolucion,
        a.tipo_acta,
        a.ubicacion_fisica,
        e.nombre_empresa
    FROM prestamos p
    JOIN actas a ON p.id_acta = a.id_acta
    JOIN empresas e ON a.id_empresa = e.id_empresa
    WHERE p.id_usuario = $id_usuario
    ORDER BY p.fecha_solicitud DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi historial de préstamos</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <div class="navbar-nav">
            <a href="empresas.php" class="nav-link">Inicio</a>
            <a href="mis_prestamos.php" class="nav-link">Mis préstamos</a>
            <a href="historial.php" class="nav-link active">Historial</a>
        </div>

        <span class="navbar-brand ms-2">Control de Actas</span>

        <div class="d-flex text-white">
            <span class="me-3"><?= $_SESSION['usuario'] ?></span>
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

            <h4 class="mb-3">Historial de préstamos</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
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

                    <?php while($h = mysqli_fetch_assoc($historial)){ ?>
                        <tr>
                            <td><?= $h['nombre_empresa'] ?></td>
                            <td><?= $h['tipo_acta'] ?></td>
                            <td><?= $h['ubicacion_fisica'] ?></td>
                            <td>
                                <?php
                                $color = 'secondary';
                                if($h['estado'] == 'pendiente') $color = 'warning';
                                if($h['estado'] == 'prestado') $color = 'success';
                                if($h['estado'] == 'devolucion_pendiente') $color = 'info';
                                if($h['estado'] == 'rechazado') $color = 'danger';
                                ?>
                                <span class="badge bg-<?= $color ?>">
                                    <?= $h['estado'] ?>
                                </span>
                            </td>
                            <td><?= $h['fecha_solicitud'] ?></td>
                            <td><?= $h['fecha_prestamo'] ?? '-' ?></td>
                            <td><?= $h['fecha_devolucion'] ?? '-' ?></td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

</body>
</html>
