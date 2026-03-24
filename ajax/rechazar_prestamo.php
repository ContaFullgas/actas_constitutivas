<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id_prestamo'] ?? 0);
$obs = trim($_POST['observaciones'] ?? '');

$obs = mysqli_real_escape_string($conn, $obs);

if($id <= 0){
    echo "ID inválido.";
    exit;
}

$sql = "
    UPDATE prestamos
    SET estado = 'rechazado',
        observaciones = '$obs'
    WHERE id_prestamo = $id
";

if(mysqli_query($conn, $sql)){
    echo "Solicitud rechazada correctamente";
} else {
    echo "Error: " . mysqli_error($conn);
}