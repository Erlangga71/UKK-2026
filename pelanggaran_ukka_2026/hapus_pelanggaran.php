<?php
include 'database.php';
$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM tbl_pelanggaran WHERE id_pelanggaran='$id'");
header("Location: laporan.php");
?>