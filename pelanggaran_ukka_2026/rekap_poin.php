<?php
include 'database.php';
?>
<h2>Rekap Poin Pelanggaran</h2>
<a href="dashboard.php">Kembali</a>
<hr>
<table border="1" cellpadding="8" cellspacing="0">
<tr><th>No</th><th>NIS</th><th>Nama Siswa</th><th>Total Poin</th><th>Jumlah Kasus</th></tr>
<?php
$no=1;
$q=mysqli_query($koneksi,"
    SELECT s.nis, s.nama, SUM(j.poin) as total_poin, COUNT(p.id) as jml
    FROM tbl_siswa s
    LEFT JOIN tbl_pelanggaran p ON p.nis=s.nis
    LEFT JOIN tbl_jenis j ON p.id_jenis=j.id
    GROUP BY s.nis, s.nama
    ORDER BY total_poin DESC
");
while($d=mysqli_fetch_assoc($q)){
    $total = $d['total_poin'] ?? 0;
?>
<tr>
    <td><?=$no++?></td>
    <td><?=$d['nis']?></td>
    <td><?=$d['nama']?></td>
    <td><b><?=$total?></b></td>
    <td><?=$d['jml']?></td>
</tr>
<?php }?>
</table>