<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id       = $_POST['id_prestamo'];
$condicion = isset($_POST['condicion']) ? mysqli_real_escape_string($conn, $_POST['condicion']) : 'bueno';

mysqli_query($conn, "
    UPDATE prestamos
    SET estado          = 'devuelto',
        fecha_devolucion = NOW(),
        condicion_devolucion = '$condicion'
    WHERE id_prestamo = $id
");

echo "Devolución autorizada";


