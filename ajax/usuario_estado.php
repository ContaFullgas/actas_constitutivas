<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id'] ?? 0);
$estado = intval($_POST['estado'] ?? 0);

/* No puede cambiar su propio estado */
if($id == $_SESSION['id_usuario']){
    echo "No puedes modificar tu propio estado.";
    exit;
}

/* Obtener usuario */
$user = mysqli_query($conn,"
SELECT rol FROM usuarios
WHERE id_usuario = $id
");
$datos = mysqli_fetch_assoc($user);

/* Si es admin y lo quieren desactivar */
if($datos['rol'] == 'admin' && $estado == 0){

    $admins = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM usuarios
    WHERE rol='admin' AND activo=1
    ");

    $total = mysqli_fetch_assoc($admins)['total'];

    if($total <= 1){
        echo "No se puede desactivar el último administrador activo.";
        exit;
    }
}

/* Actualizar */
mysqli_query($conn,"
UPDATE usuarios
SET activo = $estado
WHERE id_usuario = $id
");

echo "Estado actualizado.";
