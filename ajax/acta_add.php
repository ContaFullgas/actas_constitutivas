<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id_empresa = intval($_POST['id_empresa'] ?? 0);
$tipo = intval($_POST['tipo'] ?? 0);
$ubicacion = trim($_POST['ubicacion'] ?? '');

if($id_empresa <= 0 || $tipo <= 0 || $ubicacion == ''){
    echo "Todos los campos son obligatorios.";
    exit;
}

/* Verificar que la empresa exista */
$checkEmpresa = mysqli_query($conn, "
    SELECT id_empresa 
    FROM empresas 
    WHERE id_empresa = $id_empresa
");

if(mysqli_num_rows($checkEmpresa) == 0){
    echo "La empresa seleccionada no existe.";
    exit;
}

$fotoRuta = null;

/* Validar imagen */
if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){

    $permitidos = ['image/jpeg','image/png'];

    if(!in_array($_FILES['foto']['type'], $permitidos)){
        echo "Formato de imagen no permitido.";
        exit;
    }

    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombre = "acta_" . time() . "." . $ext;
    $destino = "../uploads/actas/" . $nombre;

    move_uploaded_file($_FILES['foto']['tmp_name'], $destino);

    $fotoRuta = "uploads/actas/" . $nombre;
}

/* Insertar */
$sql = "INSERT INTO actas
(id_empresa, id_tipo, ubicacion_fisica, foto_portada)
VALUES ($id_empresa, $tipo, '$ubicacion', ".($fotoRuta ? "'$fotoRuta'" : "NULL").")";

mysqli_query($conn, $sql);

echo "Acta registrada correctamente.";
