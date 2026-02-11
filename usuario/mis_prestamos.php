<?php
require_once "../auth/auth_check.php";
require_once "../config/db.php";

$id_usuario = $_SESSION['id_usuario'];

$prestamos = mysqli_query($conn, "
    SELECT 
        p.id_prestamo,
        p.estado,
        p.fecha_prestamo,
        a.tipo_acta,
        a.ubicacion_fisica,
        e.nombre_empresa
    FROM prestamos p
    JOIN actas a ON p.id_acta = a.id_acta
    JOIN empresas e ON a.id_empresa = e.id_empresa
    WHERE p.id_usuario = $id_usuario
      AND p.estado IN ('prestado','devolucion_pendiente')
    ORDER BY p.fecha_prestamo DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis préstamos</title>

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
            <a href="solicitar_prestamo.php" class="nav-link">Solicitar prestamo</a>
            <a href="mis_prestamos.php" class="nav-link active">Mis préstamos</a>
            <a href="historial.php" class="nav-link">Historial</a>
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

            <h4 class="mb-3">Mis préstamos activos</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Empresa</th>
                            <th>Acta</th>
                            <th>Ubicación</th>
                            <th>Fecha préstamo</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while($p = mysqli_fetch_assoc($prestamos)){ ?>
                        <tr>
                            <td><?= $p['nombre_empresa'] ?></td>
                            <td><?= $p['tipo_acta'] ?></td>
                            <td><?= $p['ubicacion_fisica'] ?></td>
                            <td><?= $p['fecha_prestamo'] ?></td>
                            <td class="text-center">

                            <?php if($p['estado'] == 'prestado'){ ?>
                                <button class="btn btn-sm btn-warning"
                                    onclick="solicitarDevolucion(<?= $p['id_prestamo'] ?>)">
                                    Solicitar devolución
                                </button>
                            <?php } else { ?>
                                <span class="badge bg-info text-dark">
                                    Devolución pendiente
                                </span>
                            <?php } ?>

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

<script src="../js/devolucion_usuario.js"></script>

</body>
</html>
