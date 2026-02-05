<?php
$conn = mysqli_connect("localhost", "root", "", "actas_constitutivas");

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
