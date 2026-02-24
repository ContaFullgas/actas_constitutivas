<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');

if($nombre==''){
    echo "El nombre es obligatorio.";
    exit;
}

/* Verificar duplicado */
$check = mysqli_query($conn,"
SELECT id_tipo
FROM tipos_acta
WHERE nombre_tipo = '$nombre'
AND id_tipo != $id
");

if(mysqli_num_rows($check) > 0){
    echo "Ya existe un tipo con ese nombre.";
    exit;
}

if($id > 0){

    mysqli_query($conn,"
    UPDATE tipos_acta
    SET nombre_tipo = '$nombre'
    WHERE id_tipo = $id
    ");

} else {

    mysqli_query($conn,"
    INSERT INTO tipos_acta (nombre_tipo,activo)
    VALUES ('$nombre',1)
    ");
}

echo "Guardado correctamente.";