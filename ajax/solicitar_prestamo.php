<?php
session_start();
require_once "../config/db.php";

$id_acta = $_POST['id_acta'];
$id_usuario = $_SESSION['id_usuario'];

/*
  1️⃣ Verificar si ya existe una solicitud activa
*/
$check = mysqli_query($conn, "
    SELECT id_prestamo
    FROM prestamos
    WHERE id_acta = $id_acta
      AND id_usuario = $id_usuario
      AND estado IN ('pendiente','prestado','devolucion_pendiente')
");

if(mysqli_num_rows($check) > 0){
    echo "Ya tienes una solicitud activa para esta acta.";
    exit;
}

/*
  2️⃣ Insertar si no hay duplicado
*/
$sql = "INSERT INTO prestamos
(id_acta, id_usuario, fecha_solicitud, estado)
VALUES ($id_acta, $id_usuario, NOW(), 'pendiente')";

mysqli_query($conn, $sql);

echo "Solicitud enviada correctamente. Espera autorización.";
