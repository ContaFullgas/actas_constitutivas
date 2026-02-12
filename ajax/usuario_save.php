<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id'] ?? 0);
$usuario = trim($_POST['usuario'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$password = trim($_POST['password'] ?? '');
$rol = trim($_POST['rol'] ?? '');

if($usuario=='' || $nombre==''){
    echo "Campos obligatorios.";
    exit;
}

if($id > 0){

    if($id == $_SESSION['id_usuario']){
        echo "No puedes modificar tu propio usuario.";
        exit;
    }

    if($password!=''){
        $sql = "UPDATE usuarios SET
                usuario='$usuario',
                nombre='$nombre',
                password='$password',
                rol='$rol'
                WHERE id_usuario=$id";
    } else {
        $sql = "UPDATE usuarios SET
                usuario='$usuario',
                nombre='$nombre',
                rol='$rol'
                WHERE id_usuario=$id";
    }

} else {

    if($password==''){
        echo "Debe ingresar password.";
        exit;
    }

    $sql = "INSERT INTO usuarios
            (usuario,nombre,password,rol,activo)
            VALUES ('$usuario','$nombre','$password','$rol',1)";
}

mysqli_query($conn,$sql);

echo "Guardado correctamente.";
