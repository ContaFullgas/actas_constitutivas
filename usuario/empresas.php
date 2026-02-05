<?php
require_once "../auth/auth_check.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio | Usuario</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <div class="navbar-nav">
            <a href="empresas.php" class="nav-link active">
                Inicio
            </a>
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

    <h4 class="mb-4">Panel de Usuario</h4>

    <div class="row">

        <!-- CARD: SOLICITAR PRÉSTAMO -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Solicitar préstamo</h5>
                    <p class="card-text">
                        Consulta las actas disponibles y solicita el préstamo de documentos.
                    </p>
                    <a href="solicitar_prestamo.php" class="btn btn-primary">
                        Ir
                    </a>
                </div>
            </div>
        </div>

        <!-- CARD: MIS PRÉSTAMOS (futuro) -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Mis préstamos</h5>
                    <p class="card-text">
                        Consulta el estado de tus solicitudes y devoluciones.
                    </p>
                    <a href="mis_prestamos.php" class="btn btn-primary">
                        Ir
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
