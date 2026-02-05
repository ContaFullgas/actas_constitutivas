<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = $_POST['id_prestamo'];

mysqli_query($conn, "
    UPDATE prestamos
    SET estado='devuelto',
        fecha_devolucion = NOW()
    WHERE id_prestamo = $id
");

echo "Devolución autorizada";
