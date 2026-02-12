<?php
require_once "../auth/admin_check.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Control de Actas</span>

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

    <div class="row">
        <div class="col-md-12">

            <div class="card shadow-sm">
                <div class="card-body">

                    <h4 class="mb-3">Panel de Administración</h4>

                    <p>Desde aquí podrás administrar empresas, actas y solicitudes.</p>

                    <div class="d-flex gap-2">
                        <a href="empresas.php" class="btn btn-primary">
                            Empresas
                        </a>

                        <a href="actas.php" class="btn btn-secondary">
                            Actas
                        </a>

                        <a href="solicitudes.php" class="btn btn-warning">
                            Solicitudes
                        </a>

                        <a href="historial.php" class="btn btn-info">
                            Historial
                        </a>

                        <a href="usuarios.php" class="btn btn-secondary">
                            Usuarios
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>
