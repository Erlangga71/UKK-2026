<?php

include "database.php";

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "DELETE FROM wali_kelas WHERE id='$id'"
);

header("Location: wali_kelas.php");

exit;

?>