<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = intval($_POST['id'] ?? 0);
$id_empresa = intval($_POST['id_empresa'] ?? 0);
$id_tipo = intval($_POST['id_tipo'] ?? 0);
$ubicacion = trim($_POST['ubicacion'] ?? '');

if($id <= 0 || $id_empresa <= 0 || $id_tipo <= 0 || $ubicacion == ''){
    echo "Todos los campos son obligatorios.";
    exit;
}

/* Obtener imagen actual */
$consultaActual = mysqli_query($conn, "
    SELECT foto_portada 
    FROM actas 
    WHERE id_acta = $id
");

$actaActual = mysqli_fetch_assoc($consultaActual);
$fotoAnterior = $actaActual['foto_portada'] ?? null;

$fotoSQL = "";

/* Si se sube nueva imagen */
if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){

    $permitidos = ['image/jpeg','image/png'];

    if(!in_array($_FILES['foto']['type'], $permitidos)){
        echo "Formato de imagen no permitido.";
        exit;
    }

    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nombre = "acta_" . time() . "." . $ext;
    $destino = "../uploads/actas/" . $nombre;

    if(move_uploaded_file($_FILES['foto']['tmp_name'], $destino)){

        $fotoRuta = "uploads/actas/" . $nombre;

        /* 🔥 Eliminar imagen anterior si existe */
        if($fotoAnterior && file_exists("../" . $fotoAnterior)){
            unlink("../" . $fotoAnterior);
        }

        $fotoSQL = ", foto_portada = '$fotoRuta'";
    }
}

/* Actualizar datos */
$sql = "UPDATE actas SET
        id_empresa = $id_empresa,
        id_tipo = $id_tipo,
        ubicacion_fisica = '$ubicacion'
        $fotoSQL
        WHERE id_acta = $id";

mysqli_query($conn, $sql);

echo "Acta actualizada correctamente.";
