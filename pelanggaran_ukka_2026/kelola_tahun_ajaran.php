<?php include 'database.php';
if(isset($_POST['simpan'])){ mysqli_query($koneksi,"INSERT INTO tbl_tahun_ajaran VALUES(NULL,'$_POST[tahun]','$_POST[semester]')"); }
if(isset($_GET['hapus'])){ mysqli_query($koneksi,"DELETE FROM tbl_tahun_ajaran WHERE id='$_GET[hapus]'"); }
?>
<h2>Kelola Tahun Ajaran</h2><a href="dashboard.php">Kembali</a>
<form method="post">
Tahun: <input name="tahun" placeholder="2025/2026" required>
Semester: <select name="semester"><option>Ganjil</option><option>Genap</option></select>
<button name="simpan">Simpan</button>
</form>
<table border="1" cellpadding="5"><tr><th>No</th><th>Tahun</th><th>Semester</th><th>Aksi</th></tr>
<?php $no=1; $q=mysqli_query($koneksi,"SELECT * FROM tbl_tahun_ajaran"); while($d=mysqli_fetch_assoc($q)){?>
<tr><td><?=$no++?></td><td><?=$d['tahun']?></td><td><?=$d['semester']?></td><td><a href="?hapus=<?=$d['id']?>">Hapus</a></td></tr>
<?php }?></table>