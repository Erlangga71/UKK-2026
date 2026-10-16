<?php

include "database.php";

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "DELETE FROM pelanggaran WHERE id='$id'"
);

header("Location: pelanggaran.php");
exit;

?>