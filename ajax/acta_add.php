<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id_empresa = $_POST['id_empresa'];
$tipo = $_POST['tipo'];
$ubicacion = $_POST['ubicacion'];

$fotoRuta = null;

/*
  Si viene imagen
*/
if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){

    $permitidos = ['image/jpeg','image/png'];

    if(in_array($_FILES['foto']['type'], $permitidos)){

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre = "acta_" . time() . "." . $ext;
        $destino = "../uploads/actas/" . $nombre;

        move_uploaded_file($_FILES['foto']['tmp_name'], $destino);

        // Ruta que se guarda en BD (relativa)
        $fotoRuta = "uploads/actas/" . $nombre;
    }
}

/*
  Insertar acta
*/
$sql = "INSERT INTO actas
(id_empresa, tipo_acta, ubicacion_fisica, foto_portada)
VALUES ($id_empresa, '$tipo', '$ubicacion', ".($fotoRuta ? "'$fotoRuta'" : "NULL").")";

mysqli_query($conn, $sql);

echo "Acta registrada correctamente";
