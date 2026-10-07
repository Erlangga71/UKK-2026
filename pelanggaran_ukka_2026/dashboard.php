<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<style>
body { font-family: Arial; }
h2 { text-align: center; margin-top: 30px; }
.menu { 
  width: 600px; 
  margin: 30px auto; 
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 15px;
}
.box { 
  border: 1px solid black; 
  width: 170px; 
  height: 70px;
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  text-decoration: none; 
  color: black; 
  font-weight: bold;
}
.box:hover { background: #f0f0f0; }
</style>
</head>
<body>
<h2> PELANGGARAN SISWA SMK MUHAMMADIYAH</h2>
<p style="text-align:center;">Selamat datang, <?php echo $_SESSION['username'] ?? 'admin'; ?> | <a href="logout.php">Logout</a></p>

<div class="menu">
  <a class="box" href="kelola_siswa.php">Data Siswa</a>
  <a class="box" href="kelola_jenis_pelanggaran.php">Data Jenis<br>Pelanggaran</a>
  <a class="box" href="catat_pelanggaran.php">Catat Pelanggaran</a>
  <a class="box" href="laporan.php">Laporan Pelanggaran</a>
  <a class="box" href="rekap_poin.php">Rekap Poin</a>
</div>

</body>
</html>