<?php
require_once "../config/db.php";

$id_empresa = $_POST['id_empresa'];
$tipo = $_POST['tipo'];
$ubicacion = $_POST['ubicacion'];

$sql = "INSERT INTO actas
(id_empresa, tipo_acta, ubicacion_fisica)
VALUES ($id_empresa, '$tipo', '$ubicacion')";

mysqli_query($conn, $sql);
echo "Acta registrada";
