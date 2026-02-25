<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id'] ?? 0);
$usuario = trim($_POST['usuario'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
$password = trim($_POST['password'] ?? '');
$rol = trim($_POST['rol'] ?? '');

if ($usuario == '' || $nombre == '' || $departamento == '') {
    echo "Campos obligatorios.";
    exit;
}

if ($id > 0) {

    if ($id == $_SESSION['id_usuario']) {
        echo "No puedes modificar tu propio usuario.";
        exit;
    }

    if ($password != '') {
        $sql = "UPDATE usuarios SET
            usuario='$usuario',
            nombre='$nombre',
            departamento='$departamento',
            password='$password',
            rol='$rol'
            WHERE id_usuario=$id";
    } else {
        $sql = "UPDATE usuarios SET
            usuario='$usuario',
            nombre='$nombre',
            departamento='$departamento',
            rol='$rol'
            WHERE id_usuario=$id";
    }

} else {

    if ($password == '') {
        echo "Debe ingresar password.";
        exit;
    }

    $sql = "INSERT INTO usuarios
        (usuario,nombre,departamento,password,rol,activo)
        VALUES ('$usuario','$nombre','$departamento','$password','$rol',1)";
}

mysqli_query($conn, $sql);

echo "Guardado correctamente.";
