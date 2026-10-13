<?php include 'database.php';
if(isset($_POST['simpan'])){ mysqli_query($koneksi,"INSERT INTO tbl_kelas VALUES(NULL,'$_POST[kelas]','$_POST[tingkat]')"); }
if(isset($_GET['hapus'])){ mysqli_query($koneksi,"DELETE FROM tbl_kelas WHERE id='$_GET[hapus]'"); }
?>
<h2>Kelola Kelas</h2><a href="dashboard.php">Kembali</a>
<form method="post">
Nama Kelas: <input name="kelas" required> Tingkat: <input name="tingkat" placeholder="X/XI/XII" required>
<button name="simpan">Simpan</button>
</form>
<table border="1" cellpadding="5"><tr><th>No</th><th>Kelas</th><th>Tingkat</th><th>Aksi</th></tr>
<?php $no=1; $q=mysqli_query($koneksi,"SELECT * FROM tbl_kelas"); while($d=mysqli_fetch_assoc($q)){?>
<tr><td><?=$no++?></td><td><?=$d['nama_kelas']?></td><td><?=$d['tingkat']?></td><td><a href="?hapus=<?=$d['id']?>">Hapus</a></td></tr>
<?php }?></table>