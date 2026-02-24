<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

/* Filtros */
$usuario = $_GET['usuario'] ?? '';
$estado  = $_GET['estado'] ?? '';
$empresa = $_GET['empresa'] ?? '';
$desde   = $_GET['desde'] ?? '';
$hasta   = $_GET['hasta'] ?? '';

$where = "WHERE 1=1";

if($usuario != ''){
    $where .= " AND u.nombre LIKE '%$usuario%'";
}
if($estado != ''){
    $where .= " AND p.estado = '$estado'";
}
if($empresa != ''){
    $where .= " AND e.id_empresa = $empresa";
}
if($desde != ''){
    $where .= " AND p.fecha_solicitud >= '$desde'";
}
if($hasta != ''){
    $where .= " AND p.fecha_solicitud <= '$hasta 23:59:59'";
}

/* Datos */
$empresas = mysqli_query($conn, "SELECT id_empresa, nombre_empresa FROM empresas");

$historial = mysqli_query($conn, "
    SELECT 
        p.id_prestamo,
        p.estado,
        p.fecha_solicitud,
        p.fecha_prestamo,
        p.fecha_devolucion,
        u.nombre,
        u.usuario,
        a.tipo_acta,
        e.nombre_empresa
    FROM prestamos p
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    JOIN actas a ON p.id_acta = a.id_acta
    JOIN empresas e ON a.id_empresa = e.id_empresa
    $where
    ORDER BY p.fecha_solicitud DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial | Admin</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <div class="navbar-nav">
            <a href="dashboard.php" class="nav-link">Inicio</a>
            <a href="empresas.php" class="nav-link">Empresas</a>
            <a href="actas.php" class="nav-link">Actas</a>
            <a href="tipos_acta.php" class="nav-link">Tipos actas</a>
            <a href="solicitudes.php" class="nav-link">Solicitudes</a>
            <a href="historial.php" class="nav-link active">Historial</a>
            <a href="usuarios.php" class="nav-link">Usuarios</a>
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

    <!-- FILTROS -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <h5 class="mb-3">Filtros</h5>

            <form method="GET" class="row g-2">

                <div class="col-md-3">
                    <input type="text" name="usuario" class="form-control"
                           placeholder="Nombre del usuario"
                           value="<?= htmlspecialchars($usuario) ?>">
                </div>

                <div class="col-md-2">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <?php
                        $estados = ['pendiente','prestado','devolucion_pendiente','devuelto','rechazado'];
                        foreach($estados as $e){
                            $sel = ($estado == $e) ? 'selected' : '';
                            echo "<option $sel value='$e'>$e</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="empresa" class="form-select">
                        <option value="">Todas las empresas</option>
                        <?php while($emp = mysqli_fetch_assoc($empresas)){ ?>
                            <option value="<?= $emp['id_empresa'] ?>"
                                <?= ($empresa == $emp['id_empresa']) ? 'selected' : '' ?>>
                                <?= $emp['nombre_empresa'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" name="desde" class="form-control"
                           value="<?= $desde ?>">
                </div>

                <div class="col-md-2">
                    <input type="date" name="hasta" class="form-control"
                           value="<?= $hasta ?>">
                </div>

                <div class="col-md-12 d-grid mt-2">
                    <button class="btn btn-primary">Aplicar filtros</button>
                </div>

            </form>

        </div>
    </div>

    <!-- TABLA -->
    <div class="card shadow-sm">
        <div class="card-body">

            <h5 class="mb-3">Historial de préstamos</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Usuario</th>
                            <th>Empresa</th>
                            <th>Acta</th>
                            <th>Estado</th>
                            <th>Solicitud</th>
                            <th>Préstamo</th>
                            <th>Devolución</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while($h = mysqli_fetch_assoc($historial)){ ?>
                        <tr>
                            <td>
                                <strong><?= $h['nombre'] ?></strong><br>
                                <small class="text-muted"><?= $h['usuario'] ?></small>
                            </td>
                            <td><?= $h['nombre_empresa'] ?></td>
                            <td><?= $h['tipo_acta'] ?></td>
                            <td>
                                <span class="badge bg-secondary">
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
