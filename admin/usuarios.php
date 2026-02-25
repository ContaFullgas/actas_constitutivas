<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$usuarios = mysqli_query($conn, "
    SELECT * FROM usuarios
    ORDER BY activo DESC, id_usuario ASC
");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios | Control de Actas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <a href="tipos_acta.php" class="nav-link">Tipos actas</a>
                <a href="solicitudes.php" class="nav-link">Solicitudes</a>
                <a href="historial.php" class="nav-link">Historial</a>
                <a href="usuarios.php" class="nav-link active">Usuarios</a>
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

    <div class="container mt-4">

        <h3 class="mb-3">Usuarios</h3>

        <button class="btn btn-primary mb-3" onclick="abrirNuevo()">
            Nuevo usuario
        </button>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>

                <?php while ($u = mysqli_fetch_assoc($usuarios)) { ?>
                    <tr>
                        <td><?= $u['usuario'] ?></td>
                        <td><?= $u['nombre'] ?></td>
                        <td><?= $u['departamento'] ?></td>
                        <td><?= $u['rol'] ?></td>
                        <td>
                            <?php if ($u['activo'] == 1) { ?>
                                <span class="badge bg-success">Activo</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php } ?>
                        </td>

                        <td class="text-center">

                            <?php if ($u['id_usuario'] != $_SESSION['id_usuario']) { ?>

                                <button class="btn btn-sm btn-warning" onclick="abrirEditar(
<?= $u['id_usuario'] ?>,
'<?= htmlspecialchars($u['usuario'], ENT_QUOTES) ?>',
'<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>',
'<?= htmlspecialchars($u['departamento'], ENT_QUOTES) ?>',
'<?= $u['rol'] ?>'
)">
                                    Editar
                                </button>

                                <?php if ($u['activo'] == 1) { ?>
                                    <button class="btn btn-sm btn-danger" onclick="cambiarEstado(<?= $u['id_usuario'] ?>,0)">
                                        Desactivar
                                    </button>
                                <?php } else { ?>
                                    <button class="btn btn-sm btn-success" onclick="cambiarEstado(<?= $u['id_usuario'] ?>,1)">
                                        Activar
                                    </button>
                                <?php } ?>

                            <?php } else { ?>
                                <span class="badge bg-info">Tu usuario</span>
                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>
    </div>

    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal -->
                <div class="modal-body">

                    <input type="hidden" id="id_usuario">

                    <div class="mb-3">
                        <label>Usuario</label>
                        <input type="text" id="usuario" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" id="nombre" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Departamento</label>
                        <select id="departamento" class="form-select">
                            <option value="">Seleccionar departamento...</option>
                            <option value="Contabilidad">Contabilidad</option>
                            <option value="Facturación">Facturación</option>
                            <option value="Sistemas">Sistemas</option>
                            <option value="Nominas">Nominas</option>
                            <option value="Fiscal">Fiscal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="text" id="password" class="form-control">
                        <small class="text-muted">Dejar vacío para no cambiar</small>
                    </div>

                    <div class="mb-3">
                        <label>Rol</label>
                        <select id="rol" class="form-select">
                            <option value="usuario">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="guardarUsuario()">
                        Guardar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/usuarios.js"></script>
</body>

</html>