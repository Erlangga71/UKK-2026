<?php
include 'koneksi.php'; // kalo nama file koneksi lu beda, ganti ya

$nis = $_GET['nis'];
mysqli_query($conn, "DELETE FROM siswa WHERE nis='$nis'");

// kalo nama tabel lu t_siswa, pake yang bawah ini, yang atas hapus
// mysqli_query($conn, "DELETE FROM t_siswa WHERE nis='$nis'");

header("Location: kelola_siswa.php");
?>