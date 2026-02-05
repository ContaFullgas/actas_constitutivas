<?php
session_start();
require_once "../config/db.php";

$usuario  = $_POST['usuario'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios 
        WHERE usuario='$usuario' 
        AND password='$password' 
        AND activo=1";

$res = mysqli_query($conn, $sql);

if(mysqli_num_rows($res) == 1){
    $row = mysqli_fetch_assoc($res);

    $_SESSION['id_usuario'] = $row['id_usuario'];
    $_SESSION['usuario']    = $row['usuario'];
    $_SESSION['rol']        = $row['rol'];

    echo $row['rol']; // admin | usuario
}else{
    echo "error";
}
