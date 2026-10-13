<?php
session_start();
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }
include 'database.php';
$role = $_SESSION['role'] ?? 'admin';
?>
<h2>Riwayat Pelanggaran Siswa</h2>
<a href="dashboard.php">Kembali</a>
<hr>

<form method="get">
Cari NIS / Nama: <input type="text" name="cari" value="<?= $_GET['cari'] ?? '' ?>">
<button type="submit">Cari</button> <a href="riwayat.php">Reset</a>
</form>
<br>

<table border="1" cellpadding="8" cellspacing="0">
<tr><th>No</th><th>Tanggal</th><th>NIS</th><th>Nama Siswa</th><th>Jenis Pelanggaran</th><th>Poin</th><th>Tindakan</th></tr>
<?php
$no=1;
$cari = $_GET['cari'] ?? '';
$where = "";
if($cari != ""){
    $where = "WHERE s.nis LIKE '%$cari%' OR s.nama LIKE '%$cari%'";
}

$q = mysqli_query($koneksi,"
    SELECT p.*, s.nama, j.nama_jenis, j.poin, t.tindakan 
    FROM tbl_pelanggaran p 
    JOIN tbl_siswa s ON p.nis=s.nis 
    JOIN tbl_jenis j ON p.id_jenis=j.id 
    LEFT JOIN tbl_tindakan t ON t.id_pelanggaran=p.id
    $where
    ORDER BY p.tgl DESC
");
while($d=mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?=$no++?></td>
    <td><?=$d['tgl']?></td>
    <td><?=$d['nis']?></td>
    <td><?=$d['nama']?></td>
    <td><?=$d['nama_jenis']?></td>
    <td><?=$d['poin']?></td>
    <td><?=$d['tindakan'] ?? '<i>Belum ditindak</i>'?></td>
</tr>
<?php }?>
</table>