<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../database.php';


$nis = $_POST['nis'] ?? '';
$nama = $_POST['nama'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$kelas = $_POST['kelas'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';


$sql = "INSERT INTO siswa
        (nis, nama, jenis_kelamin, kelas, alamat, no_hp)
        VALUES (?, ?, ?, ?, ?, ?)";


$stmt = $pdo->prepare($sql);


$stmt->execute([
    $nis,
    $nama,
    $jenis_kelamin,
    $kelas,
    $alamat,
    $no_hp
]);


header("Location: index.php");

exit;
