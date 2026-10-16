<?php

include "database.php";

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "DELETE FROM tahun_ajaran WHERE id='$id'"
);

header("Location: tahun_ajaran.php");
exit;

?>