<?php
ini_set('display_errors',1); error_reporting(E_ALL);
include 'database.php';
?>
<h2>Laporan Pelanggaran</h2>
<table border=1 cellpadding=8 cellspacing=0>
<tr><th>No</th><th>Tanggal</th><th>Nama Siswa</th><th>NIS</th><th>Pelanggaran</th><th>Poin</th><th>Aksi</th></tr>
<?php
$sql = "SELECT p.id_pelanggaran, p.tgl, s.nama, s.nis, j.nama_jenis, j.poin 
        FROM tbl_pelanggaran p
        LEFT JOIN tbl_siswa s ON s.id_siswa = p.id_siswa OR s.nis = p.id_siswa
        LEFT JOIN tbl_jenis j ON j.id_jenis = p.id_jenis
        ORDER BY p.id_pelanggaran DESC";
$q = mysqli_query($koneksi, $sql);
$no=1;
while($r = mysqli_fetch_assoc($q)){
  echo "<tr>
  <td>$no</td><td>{$r['tgl']}</td><td>{$r['nama']}</td><td>{$r['nis']}</td><td>{$r['nama_jenis']}</td><td>{$r['poin']}</td>
  <td><a href='hapus_pelanggaran.php?id={$r['id_pelanggaran']}' onclick='return confirm(\"Yakin mau hapus data {$r['nama']} - {$r['nama_jenis']}?\")'>Hapus</a></td>
  </tr>";
  $no++;
}
?>
</table>
<br><a href="dashboard.php">Kembali</a>