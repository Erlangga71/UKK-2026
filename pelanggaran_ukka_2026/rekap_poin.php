<?php
ini_set('display_errors',1); error_reporting(E_ALL);
include 'database.php';
?>
<h2>Rekap Poin Siswa</h2>
<table border=1 cellpadding=8 cellspacing=0>
<tr><th>No</th><th>Nama</th><th>NIS</th><th>Total Poin</th></tr>
<?php
$sql = "SELECT s.nama, s.nis, SUM(j.poin) as total 
        FROM tbl_pelanggaran p
        LEFT JOIN tbl_siswa s ON s.id_siswa = p.id_siswa OR s.nis = p.id_siswa
        LEFT JOIN tbl_jenis j ON j.id_jenis = p.id_jenis
        GROUP BY s.id_siswa, s.nis, s.nama";
$q = mysqli_query($koneksi, $sql);
if(!$q) die("Error: ".mysqli_error($koneksi));
$no=1;
while($r = mysqli_fetch_assoc($q)){
  echo "<tr><td>$no</td><td>{$r['nama']}</td><td>{$r['nis']}</td><td><b>{$r['total']}</b></td></tr>";
  $no++;
}
?>
</table>
<br><a href="dashboard.php">Kembali</a> | <a href="laporan.php">Lihat Laporan</a>