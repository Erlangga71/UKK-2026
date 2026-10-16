<?php

include "database.php";

$id = $_GET['id'];

mysqli_query(
    $koneksi,
    "DELETE FROM tindakan WHERE id='$id'"
);

header("Location: tindakan.php");
exit;

?>