<?php

include "database.php";

$id = $_GET['id'] ?? 0;

mysqli_query(
    $koneksi,
    "DELETE FROM guru WHERE id='$id'"
);

header("Location: guru.php");
exit;

?>