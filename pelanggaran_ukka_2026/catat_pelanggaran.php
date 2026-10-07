<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
session_start();
include 'database.php';

if(isset($_POST['simpan'])){
  $id_siswa = (int)$_POST['id_siswa'];
  $id_jenis = (int)$_POST['id_jenis'];

  $sql = "INSERT INTO tbl_pelanggaran (id_siswa, id_jenis, tgl) VALUES ('$id_siswa', '$id_jenis', CURDATE())";
  $exec = mysqli_query($koneksi, $sql);

  if(!$exec){
    echo "<h3>GAGAL BRE:</h3> ".mysqli_error($koneksi)."<br>SQL: $sql";
    exit;
  }
  // jangan pake header, pake js biar gak putih
  echo "<script>alert('Berhasil disimpan bre!'); window.location.href='laporan.php';</script>";
  exit;
}
?>
<h2>Catat Pelanggaran</h2>
<form method="post">
Siswa:
<select name="id_siswa" required>
<option value="">-- Pilih Siswa --</option>
<?php $q=mysqli_query($koneksi,"SELECT * FROM tbl_siswa"); while($r=mysqli_fetch_assoc($q)){ echo "<option value='$r[id_siswa]'>$r[nama] - $r[nis]</option>"; }?>
</select><br><br>

Jenis:
<select name="id_jenis" required>
<option value="">-- Pilih Jenis --</option>
<?php $q=mysqli_query($koneksi,"SELECT * FROM tbl_jenis"); while($r=mysqli_fetch_assoc($q)){ echo "<option value='$r[id_jenis]'>$r[nama_jenis] ($r[poin])</option>"; }?>
</select><br><br>

<button type="submit" name="simpan">Simpan</button>
</form>
<br><a href="dashboard.php">Kembali</a>