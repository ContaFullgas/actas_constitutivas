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
                            <th>Empresa</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while($a = mysqli_fetch_assoc($actas)){ ?>
                        <tr>
                            <td><?= $a['nombre_empresa'] ?></td>
                            <td><?= $a['tipo_acta'] ?></td>
                            <td><?= $a['ubicacion_fisica'] ?></td>
                            <td class="text-center">
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

<!-- JS -->
<script src="../js/actas.js"></script>

</body>
</html>
