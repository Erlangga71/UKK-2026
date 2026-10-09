<?php include 'database.php';
if(isset($_POST['simpan'])){ mysqli_query($koneksi,"INSERT INTO tbl_kategori VALUES(NULL,'$_POST[kategori]')"); }
if(isset($_GET['hapus'])){ mysqli_query($koneksi,"DELETE FROM tbl_kategori WHERE id='$_GET[hapus]'"); }
?>
<h2>Kelola Kategori Pelanggaran</h2><a href="dashboard.php">Kembali</a>
<form method="post">
Kategori: <input name="kategori" placeholder="Kedisiplinan / Kerajinan" required>
<button name="simpan">Simpan</button>
</form>
<table border="1" cellpadding="5"><tr><th>No</th><th>Kategori</th><th>Aksi</th></tr>
<?php $no=1; $q=mysqli_query($koneksi,"SELECT * FROM tbl_kategori"); while($d=mysqli_fetch_assoc($q)){?>
<tr><td><?=$no++?></td><td><?=$d['nama_kategori']?></td><td><a href="?hapus=<?=$d['id']?>">Hapus</a></td></tr>
<?php }?></table>