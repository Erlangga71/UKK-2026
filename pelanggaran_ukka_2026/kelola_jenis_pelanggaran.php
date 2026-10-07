<?php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start(); include 'database.php';
if(isset($_POST['tambah'])){
 $n=$_POST['nama_jenis']; $p=$_POST['poin'];
 mysqli_query($koneksi,"INSERT INTO tbl_jenis (nama_jenis,poin) VALUES ('$n','$p')");
 header("Location: kelola_jenis_pelanggaran.php"); exit;
}
?>
<h2>Kelola Jenis Pelanggaran</h2>
<form method="post">Jenis: <input name="nama_jenis" required> Poin: <input type="number" name="poin" required> <button name="tambah">Tambah</button></form><br>
<table border=1 cellpadding=5><tr><th>Jenis</th><th>Poin</th></tr>
<?php $q=mysqli_query($koneksi,"SELECT * FROM tbl_jenis"); while($r=mysqli_fetch_assoc($q)){ echo "<tr><td>$r[nama_jenis]</td><td>$r[poin]</td></tr>"; }?>
</table><br><a href="dashboard.php">Kembali</a>