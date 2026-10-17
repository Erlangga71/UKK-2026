<?php

include "database.php";

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "DELETE FROM penempatan_siswa WHERE id='$id'"
);

header("Location: penempatan_siswa.php");
exit;

?>