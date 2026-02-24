<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$tipos = mysqli_query($conn,"
    SELECT * FROM tipos_acta
    ORDER BY activo DESC, nombre_tipo ASC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tipos de Acta | Control de Actas</title>

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
<a href="tipos_acta.php" class="nav-link active">Tipos actas</a>
<a href="solicitudes.php" class="nav-link">Solicitudes</a>
<a href="historial.php" class="nav-link">Historial</a>
<a href="usuarios.php" class="nav-link">Usuarios</a>
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

<h3 class="mb-3">Tipos de Acta</h3>

<button class="btn btn-primary mb-3" onclick="abrirNuevo()">
Nuevo tipo
</button>

<table class="table table-bordered table-hover">
<thead class="table-dark">
<tr>
<th>Nombre</th>
<th>Estado</th>
<th class="text-center">Acciones</th>
</tr>
</thead>
<tbody>

<?php while($t = mysqli_fetch_assoc($tipos)){ ?>
<tr>
<td><?= $t['nombre_tipo'] ?></td>
<td>
<?php if($t['activo']){ ?>
<span class="badge bg-success">Activo</span>
<?php } else { ?>
<span class="badge bg-secondary">Inactivo</span>
<?php } ?>
</td>

<td class="text-center">

<button class="btn btn-sm btn-warning"
onclick="abrirEditar(
<?= $t['id_tipo'] ?>,
'<?= htmlspecialchars($t['nombre_tipo'], ENT_QUOTES) ?>'
)">
Editar
</button>

<?php if($t['activo']){ ?>
<button class="btn btn-sm btn-danger"
onclick="cambiarEstado(<?= $t['id_tipo'] ?>,0)">
Desactivar
</button>
<?php } else { ?>
<button class="btn btn-sm btn-success"
onclick="cambiarEstado(<?= $t['id_tipo'] ?>,1)">
Activar
</button>
<?php } ?>

</td>
</tr>
<?php } ?>

</tbody>
</table>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalTipo" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Tipo de Acta</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" id="id_tipo">

<div class="mb-3">
<label>Nombre del tipo</label>
<input type="text" id="nombre_tipo" class="form-control">
</div>

</div>

<div class="modal-footer">
<button class="btn btn-primary" onclick="guardarTipo()">
Guardar
</button>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/tipos.js"></script>
</body>
</html>