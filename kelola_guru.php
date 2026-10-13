<?php include 'database.php';
if(isset($_POST['simpan'])){ mysqli_query($koneksi,"INSERT INTO tbl_guru VALUES('$_POST[nip]','$_POST[nama]','$_POST[hp]')"); }
if(isset($_GET['hapus'])){ mysqli_query($koneksi,"DELETE FROM tbl_guru WHERE nip='$_GET[hapus]'"); }
?>
<h2>Kelola Guru</h2><a href="dashboard.php">Kembali</a>
<form method="post">
NIP: <input name="nip" required> Nama: <input name="nama" required> HP: <input name="hp">
<button name="simpan">Simpan</button>
</form>
<table border="1" cellpadding="5"><tr><th>NIP</th><th>Nama</th><th>HP</th><th>Aksi</th></tr>
<?php $q=mysqli_query($koneksi,"SELECT * FROM tbl_guru"); while($d=mysqli_fetch_assoc($q)){?>
<tr><td><?=$d['nip']?></td><td><?=$d['nama']?></td><td><?=$d['no_hp']?></td><td><a href="?hapus=<?=$d['nip']?>">Hapus</a></td></tr>
<?php }?></table>