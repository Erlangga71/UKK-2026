<?php
include 'database.php';

if(isset($_POST['simpan'])){
    mysqli_query($koneksi,"INSERT INTO tbl_jenis VALUES(NULL,'$_POST[kategori]','$_POST[jenis]','$_POST[poin]')");
    header("Location: kelola_jenis_pelanggaran.php");
}

if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    // HAPUS BERANTAI BIAR GAK NYANGKUT FK
    mysqli_query($koneksi,"DELETE FROM tbl_tindakan WHERE id_pelanggaran IN (SELECT id FROM tbl_pelanggaran WHERE id_jenis='$id')");
    mysqli_query($koneksi,"DELETE FROM tbl_pelanggaran WHERE id_jenis='$id'");
    mysqli_query($koneksi,"DELETE FROM tbl_jenis WHERE id='$id'");
    echo "<script>alert('Berhasil dihapus + data pelanggaran terkait ikut bersih!'); window.location='kelola_jenis_pelanggaran.php';</script>";
}
?>
<h2>Kelola Jenis Pelanggaran</h2>
<a href="dashboard.php">Kembali</a>
<hr>
<form method="post">
Kategori: <select name="kategori" required><?php $k=mysqli_query($koneksi,"SELECT * FROM tbl_kategori"); while($r=mysqli_fetch_assoc($k)) echo "<option value='$r[id]'>$r[nama_kategori]</option>";?></select>
Jenis: <input name="jenis" required>
Poin: <input name="poin" type="number" required>
<button name="simpan">Simpan</button>
</form>
<br>
<table border="1" cellpadding="8" cellspacing="0">
<tr><th>No</th><th>Kategori</th><th>Jenis</th><th>Poin</th><th>Aksi</th></tr>
<?php $no=1; $q=mysqli_query($koneksi,"SELECT j.*, k.nama_kategori FROM tbl_jenis j JOIN tbl_kategori k ON j.id_kategori=k.id"); while($d=mysqli_fetch_assoc($q)){?>
<tr><td><?=$no++?></td><td><?=$d['nama_kategori']?></td><td><?=$d['nama_jenis']?></td><td><?=$d['poin']?></td><td><a href="?hapus=<?=$d['id']?>" onclick="return confirm('Hapus? Semua pelanggaran & tindakan yg pake jenis ini ikut kehapus!')">Hapus</a></td></tr>
<?php }?>
</table>