<?php
require_once "../auth/admin_check.php";
require_once "../config/db.php";

$id = (int)$_POST['id'];

/* ── Verificar si la empresa tiene actas vinculadas ── */
$check = mysqli_query($conn, "SELECT COUNT(*) FROM actas WHERE id_empresa = $id");
$en_uso = mysqli_fetch_row($check)[0] > 0;

if($en_uso){
    echo "en_uso";
    exit;
}

/* ── Eliminar si no está en uso ── */
$res = mysqli_query($conn, "DELETE FROM empresas WHERE id_empresa = $id");

if($res){
    echo "Empresa eliminada correctamente.";
} else {
    echo "Error al eliminar la empresa.";
}


