<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$solicitudes = mysqli_query($conn, "
    SELECT 
        p.id_prestamo,
        p.estado,
        p.fecha_solicitud,
        u.nombre,
        u.usuario,
        a.tipo_acta,
        e.nombre_empresa
    FROM prestamos p
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    JOIN actas a ON p.id_acta = a.id_acta
    JOIN empresas e ON a.id_empresa = e.id_empresa
    WHERE p.estado IN ('pendiente','devolucion_pendiente')
    ORDER BY p.fecha_solicitud DESC
");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes | Admin</title>

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
            <a href="dashboard.php" class="nav-link">Inicio</a>
            <a href="empresas.php" class="nav-link">Empresas</a>
            <a href="actas.php" class="nav-link">Actas</a>
            <a href="solicitudes.php" class="nav-link active">Solicitudes</a>
            <a href="historial.php" class="nav-link active">Historial</a>
        </div>

        <span class="navbar-brand ms-2">Control de Actas</span>

        <div class="d-flex text-white">
            <span class="me-3"><?php echo $_SESSION['usuario']; ?></span>
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

            <h4 class="mb-3">Solicitudes pendientes</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Usuario</th>
                            <th>Empresa</th>
                            <th>Acta</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while($s = mysqli_fetch_assoc($solicitudes)){ ?>
                        <tr>
                            <td>
                                <strong><?= $s['nombre'] ?></strong><br>
                                <small class="text-muted"><?= $s['usuario'] ?></small>
                            </td>

                            <td><?= $s['nombre_empresa'] ?></td>
                            <td><?= $s['tipo_acta'] ?></td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <?= $s['estado'] ?>
                                </span>
                            </td>
                            <td><?= $s['fecha_solicitud'] ?></td>
                            <td class="text-center">

                            <?php if($s['estado'] == 'pendiente'){ ?>
                                <button class="btn btn-sm btn-success"
                                    onclick="autorizarPrestamo(<?= $s['id_prestamo'] ?>)">
                                    Autorizar
                                </button>

                                <button class="btn btn-sm btn-danger"
                                    onclick="rechazarPrestamo(<?= $s['id_prestamo'] ?>)">
                                    Rechazar
                                </button>
                            <?php } ?>

                            <?php if($s['estado'] == 'devolucion_pendiente'){ ?>
                                <button class="btn btn-sm btn-primary"
                                    onclick="autorizarDevolucion(<?= $s['id_prestamo'] ?>)">
                                    Autorizar devolución
                                </button>
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

<script src="../js/solicitudes_admin.js"></script>

</body>
</html>
