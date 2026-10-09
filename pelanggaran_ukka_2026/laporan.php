<?php
include 'database.php';
?>
<!DOCTYPE html>
<html>
<head><title>Laporan Pelanggaran</title></head>
<body>
<h2>Laporan Pelanggaran</h2>
<a href="dashboard.php">Kembali ke Dashboard</a> | 
<a href="cetak_export.php"><button>Cetak</button></a>
<hr>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nama Siswa</th>
    <th>NIS</th>
    <th>Pelanggaran</th>
    <th>Poin</th>
    <th>Keterangan</th>
</tr>
<?php
$no=1;
// FIX: pake p.id bukan p.id_pelanggaran, p.nis bukan p.id_siswa
$q = mysqli_query($koneksi,"
    SELECT p.*, s.nama, j.nama_jenis, j.poin 
    FROM tbl_pelanggaran p 
    JOIN tbl_siswa s ON p.nis = s.nis 
    JOIN tbl_jenis j ON p.id_jenis = j.id 
    ORDER BY p.tgl DESC
");

if(!$q){
    echo "<tr><td colspan='7'>Error: ".mysqli_error($koneksi)."</td></tr>";
} else {
    while($d=mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?=$no++?></td>
    <td><?=$d['tgl']?></td>
    <td><?=$d['nama']?></td>
    <td><?=$d['nis']?></td>
    <td><?=$d['nama_jenis']?></td>
    <td><?=$d['poin']?></td>
    <td><?=$d['keterangan']?></td>
</tr>
<?php 
    }
    if($no==1) echo "<tr><td colspan='7' align='center'>Belum ada data pelanggaran</td></tr>";
}
?>
</table>

</body>
</html>