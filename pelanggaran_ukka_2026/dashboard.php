<?php
session_start();
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }
$role = $_SESSION['role'] ?? 'admin';
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard - Sistem Informasi Pelanggaran Siswa</title>
<style>
body{
    font-family: Arial, sans-serif;
    background: #f8f9fa;
    margin:0;
    padding:20px;
    text-align:center;
}
.container{
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
h2{ margin-bottom:5px; }
.login-info{ font-size:14px; margin-bottom:25px; }
.section-title{
    background:#000;
    color:#fff;
    display:inline-block;
    padding:6px 14px;
    border-radius:5px;
    margin:25px 0 15px 0;
    font-size:14px;
}
.menu{
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    justify-items: center;
    margin-bottom: 10px;
}
.menu.transaksi{
    grid-template-columns: repeat(2, 160px);
    justify-content: center;
}
.menu.laporan{
    grid-template-columns: repeat(4, 160px);
    justify-content: center;
}
.box{
    border:2px solid #000;
    border-radius:8px;
    padding:16px 10px;
    width:150px;
    min-height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    font-size:13px;
    background:white;
    cursor:pointer;
    transition:0.2s;
    line-height:1.3;
}
.box:hover{
    background:#000;
    color:#fff;
    transform: translateY(-2px);
}
a{ text-decoration:none; color:#000; }
a:hover .box{ color:#fff; }
a.logout{ color:blue; text-decoration:underline; }
@media(max-width:768px){
    .menu{ grid-template-columns: repeat(2, 1fr); }
    .menu.transaksi, .menu.laporan{ grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>
<div class="container">
<h2>Sistem Informasi Pelanggaran Siswa</h2>
<p class="login-info">Login sebagai: <b><?php echo $_SESSION['login']." ($role)"; ?></b> | <a class="logout" href="logout.php">Logout</a></p>

<div class="section-title">Data Pelanggar  (Admin)</div>
<div class="menu">
    <a href="kelola_tahun_ajaran.php"><div class="box">Kelola Tahun<br>Ajaran</div></a>
    <a href="kelola_kelas.php"><div class="box">Kelola Kelas</div></a>
    <a href="kelola_guru.php"><div class="box">Kelola Guru</div></a>
    <a href="kelola_siswa.php"><div class="box">Kelola Siswa</div></a>
    <a href="penempatan_siswa.php"><div class="box">Penempatan Siswa</div></a>
    <a href="kelola_wali_kelas.php"><div class="box">Kelola Wali Kelas</div></a>
    <a href="kelola_kategori.php"><div class="box">Kelola Kategori Pelanggaran</div></a>
    <a href="kelola_jenis_pelanggaran.php"><div class="box">Kelola Jenis Pelanggaran</div></a>
</div>

<div class="section-title">Transaksi</div>
<div class="menu transaksi">
    <a href="catat_pelanggaran.php"><div class="box">Catat Pelanggaran</div></a>
    <a href="tindakan.php"><div class="box">Tindakan</div></a>
</div>

<div class="section-title">Laporan</div>
<div class="menu laporan">
    <a href="laporan.php"><div class="box">Laporan</div></a>
    <a href="riwayat.php"><div class="box">Riwayat</div></a>
    <a href="rekap_poin.php"><div class="box">Rekap Poin</div></a>
    <a href="cetak_export.php"><div class="box">Cetak / Export</div></a>
</div>

</div>
</body>
</html>