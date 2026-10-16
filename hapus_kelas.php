<?php

include "database.php";

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "DELETE FROM kelas WHERE id='$id'"
);

header("Location: kelas.php");
exit;

?>