
<?php

// =====================================
// PENGATURAN WAKTU INDONESIA (WIB)
// =====================================
date_default_timezone_set('Asia/Jakarta');

// =====================================
// KONEKSI DATABASE INFINITYFREE
// =====================================
$host = "sql108.byetcluster.com";
$user = "if0_42827682";
$pass = "erlanggaweb22";
$db   = "if0_42827682_db_ukk";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// =====================================
// PENGATURAN WAKTU DATABASE
// =====================================
mysqli_query($koneksi, "SET time_zone = '+07:00'");

?>
