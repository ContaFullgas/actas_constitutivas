<?php
require_once "../config/db.php";

$id = $_POST['id'];

mysqli_query($conn, "DELETE FROM empresas WHERE id_empresa=$id");
echo "Empresa eliminada";
