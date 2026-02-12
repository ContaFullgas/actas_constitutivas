<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$rfc = trim($_POST['rfc'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');

if($id <= 0 || $nombre == '' || $rfc == '' || $fecha == ''){
    echo "Todos los campos son obligatorios.";
    exit;
}

/* Actualizar */
$sql = "UPDATE empresas
        SET nombre_empresa = '$nombre',
            rfc = '$rfc',
            fecha_constitucion = '$fecha'
        WHERE id_empresa = $id";

mysqli_query($conn, $sql);

echo "Empresa actualizada correctamente.";
