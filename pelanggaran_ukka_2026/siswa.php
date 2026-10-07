<?php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start();
include 'database.php';

$q = mysqli_query($koneksi, "SELECT * FROM tbl_siswa");
if(!$q) die("Error siswa: ".mysqli_error($koneksi));
?>
<h2>Data Siswa</h2>
<table border=1 cellpadding=5>
<tr><th>NIS</th><th>Nama</th></tr>
<?php while($r=mysqli_fetch_assoc($q)){ echo "<tr><td>$r[nis]</td><td>$r[nama]</td></tr>"; }?>
</table>
<a href="dashboard.php">Kembali</a>