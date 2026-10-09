<?php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start();
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }
include 'database.php';

if(isset($_POST['tambah'])){
  $nis = $_POST['nis']; $nama = $_POST['nama'];
  mysqli_query($koneksi, "INSERT INTO tbl_siswa (nis, nama) VALUES ('$nis','$nama')");
  header("Location: kelola_siswa.php"); exit;
}
if(isset($_GET['hapus'])){
  mysqli_query($koneksi, "DELETE FROM tbl_siswa WHERE nis='$_GET[hapus]'");
  header("Location: kelola_siswa.php"); exit;
}
?>
<h2>Data Siswa</h2>
<form method="post">
NIS: <input name="nis" required>
Nama: <input name="nama" required>
<button name="tambah">Tambah</button>
</form>
<br>
<table border="1" cellpadding="5">
<tr><th>NIS</th><th>Nama</th><th>Aksi</th></tr>
<?php
$q = mysqli_query($koneksi,"SELECT * FROM tbl_siswa");
while($r=mysqli_fetch_assoc($q)){
  echo "<tr><td>$r[nis]</td><td>$r[nama]</td><td><a href='?hapus=$r[nis]'>Hapus</a></td></tr>";
}
?>
</table>
<br>
<a href="dashboard.php">Kembali</a>