
<?php

// =====================================
// PENGATURAN WAKTU INDONESIA (WIB)
// =====================================
date_default_timezone_set('Asia/Jakarta');

// =====================================
// KONEKSI DATABASE
// =====================================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ukk_2026";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// =====================================
// PENGATURAN WAKTU DATABASE
// =====================================
mysqli_query($koneksi, "SET time_zone = '+07:00'");

?>
