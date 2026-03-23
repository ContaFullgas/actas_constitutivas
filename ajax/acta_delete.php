<?php
require_once "../config/db.php";

$id = intval($_POST['id']);

// Verificar si tiene préstamos asociados
$check = mysqli_query($conn, "SELECT COUNT(*) as total FROM prestamos WHERE id_acta = $id");
$row   = mysqli_fetch_assoc($check);

if($row['total'] > 0){
    echo "error: No se puede eliminar, el acta tiene préstamos registrados.";
    exit;
}

$result = mysqli_query($conn, "DELETE FROM actas WHERE id_acta = $id");

if($result && mysqli_affected_rows($conn) > 0){
    echo "Acta eliminada correctamente.";
} else {
    echo "error: No se pudo eliminar el acta.";
}


