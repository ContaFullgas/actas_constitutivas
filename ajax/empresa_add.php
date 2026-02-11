<?php
require_once "../config/db.php";

$nombre = trim($_POST['nombre'] ?? '');
$rfc    = trim($_POST['rfc'] ?? '');
$fecha  = trim($_POST['fecha'] ?? '');

if($nombre == '' || $rfc == '' || $fecha == ''){
    echo "Todos los campos son obligatorios.";
    exit;
}

/* Validar RFC mínimo 12 caracteres */
if(strlen($rfc) < 12){
    echo "El RFC no es válido.";
    exit;
}

/* Insertar */
$sql = "INSERT INTO empresas 
(nombre_empresa, rfc, fecha_constitucion)
VALUES ('$nombre','$rfc','$fecha')";

mysqli_query($conn, $sql);

echo "Empresa registrada correctamente.";
