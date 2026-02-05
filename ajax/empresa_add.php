<?php
require_once "../config/db.php";

$nombre = $_POST['nombre'];
$rfc = $_POST['rfc'];
$fecha = $_POST['fecha'];

$sql = "INSERT INTO empresas 
(nombre_empresa, rfc, fecha_constitucion)
VALUES ('$nombre','$rfc','$fecha')";

mysqli_query($conn, $sql);
echo "Empresa registrada";
