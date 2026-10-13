<?php include 'database.php';
if(isset($_POST['simpan'])){ mysqli_query($koneksi,"INSERT INTO tbl_wali_kelas VALUES(NULL,'$_POST[guru]','$_POST[kelas]','$_POST[tahun]')"); }
?>
<h2>Kelola Wali Kelas</h2><a href="dashboard.php">Kembali</a>
<form method="post">
Guru: <select name="guru"><?php $q=mysqli_query($koneksi,"SELECT * FROM tbl_guru"); while($d=mysqli_fetch_assoc($q)) echo "<option value='$d[nip]'>$d[nama]</option>";?></select>
Kelas: <select name="kelas"><?php $q=mysqli_query($koneksi,"SELECT * FROM tbl_kelas"); while($d=mysqli_fetch_assoc($q)) echo "<option value='$d[id]'>$d[nama_kelas]</option>";?></select>
Tahun: <select name="tahun"><?php $q=mysqli_query($koneksi,"SELECT * FROM tbl_tahun_ajaran"); while($d=mysqli_fetch_assoc($q)) echo "<option value='$d[id]'>$d[tahun]-$d[semester]</option>";?></select>
<button name="simpan">Simpan</button>
</form>
<table border="1" cellpadding="5"><tr><th>Guru</th><th>Kelas</th><th>Tahun</th></tr>
<?php $q=mysqli_query($koneksi,"SELECT w.*, g.nama, k.nama_kelas, t.tahun FROM tbl_wali_kelas w JOIN tbl_guru g ON w.nip_guru=g.nip JOIN tbl_kelas k ON w.id_kelas=k.id JOIN tbl_tahun_ajaran t ON w.id_tahun=t.id"); while($d=mysqli_fetch_assoc($q)){?>
<tr><td><?=$d['nama']?></td><td><?=$d['nama_kelas']?></td><td><?=$d['tahun']?></td></tr>
<?php }?></table>