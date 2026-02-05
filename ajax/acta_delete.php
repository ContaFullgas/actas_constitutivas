<?php
require_once "../config/db.php";

$id = $_POST['id'];

mysqli_query($conn, "DELETE FROM actas WHERE id_acta=$id");
echo "Acta eliminada";
