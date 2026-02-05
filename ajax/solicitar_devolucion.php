<?php
require_once "../auth/auth_check.php";
require_once "../config/db.php";

$id_prestamo = $_POST['id_prestamo'];

/*
  Validación: solo si está prestado
*/
$check = mysqli_query($conn, "
    SELECT estado
    FROM prestamos
    WHERE id_prestamo = $id_prestamo
      AND estado = 'prestado'
");

if(mysqli_num_rows($check) == 0){
    echo "No se puede solicitar devolución en este estado.";
    exit;
}

/*
  Cambiar estado
*/
mysqli_query($conn, "
    UPDATE prestamos
    SET estado = 'devolucion_pendiente'
    WHERE id_prestamo = $id_prestamo
");

echo "Solicitud de devolución enviada. Espera autorización.";
