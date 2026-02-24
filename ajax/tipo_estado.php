<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id'] ?? 0);
$estado = intval($_POST['estado'] ?? 0);

/* Si lo quieren desactivar, verificar que no esté en uso */
if($estado == 0){

    $uso = mysqli_query($conn,"
    SELECT id_acta
    FROM actas
    WHERE id_tipo = $id
    LIMIT 1
    ");

    if(mysqli_num_rows($uso) > 0){
        echo "No se puede desactivar, está siendo usado por actas.";
        exit;
    }
}

mysqli_query($conn,"
UPDATE tipos_acta
SET activo = $estado
WHERE id_tipo = $id
");

echo "Estado actualizado.";