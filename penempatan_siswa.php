<?php
include 'database.php';

if(isset($_POST['simpan'])){
    $nis = $_POST['nis'];
    $kelas = $_POST['kelas'];
    $tahun = $_POST['tahun'];
    $q = mysqli_query($koneksi,"INSERT INTO tbl_penempatan_siswa VALUES(NULL,'$nis','$kelas','$tahun')");
    if($q){ echo "<script>alert('Penempatan berhasil!'); window.location='penempatan_siswa.php';</script>"; }
}

if(isset($_GET['hapus'])){
    mysqli_query($koneksi,"DELETE FROM tbl_penempatan_siswa WHERE id='$_GET[hapus]'");
    header("Location: penempatan_siswa.php");
}
?>
<h2>Penempatan Siswa</h2>
<a href="dashboard.php">Kembali ke Dashboard</a>
<hr>

<form method="post">
    Siswa:
    <select name="nis" required>
        <option value="">-- Pilih Siswa --</option>
        <?php $s=mysqli_query($koneksi,"SELECT * FROM tbl_siswa"); while($d=mysqli_fetch_assoc($s)) echo "<option value='$d[nis]'>$d[nis] - $d[nama]</option>";?>
    </select>

    Kelas:
    <select name="kelas" required>
        <option value="">-- Pilih Kelas --</option>
        <?php $s=mysqli_query($koneksi,"SELECT * FROM tbl_kelas"); while($d=mysqli_fetch_assoc($s)) echo "<option value='$d[id]'>$d[nama_kelas]</option>";?>
    </select>

    Tahun Ajaran:
    <select name="tahun" required>
        <option value="">-- Pilih Tahun --</option>
        <?php $s=mysqli_query($koneksi,"SELECT * FROM tbl_tahun_ajaran"); while($d=mysqli_fetch_assoc($s)) echo "<option value='$d[id]'>$d[tahun] - $d[semester]</option>";?>
    </select>

    <button type="submit" name="simpan">Simpan</button>
</form>

<br>
<table border="1" cellpadding="8" cellspacing="0">
<tr><th>No</th><th>NIS</th><th>Nama Siswa</th><th>Kelas</th><th>Tahun Ajaran</th><th>Aksi</th></tr>
<?php
$no=1;
$q=mysqli_query($koneksi,"SELECT ps.*, s.nama, k.nama_kelas, t.tahun, t.semester FROM tbl_penempatan_siswa ps JOIN tbl_siswa s ON ps.nis=s.nis JOIN tbl_kelas k ON ps.id_kelas=k.id JOIN tbl_tahun_ajaran t ON ps.id_tahun=t.id ORDER BY k.nama_kelas");
while($d=mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?=$no++?></td>
    <td><?=$d['nis']?></td>
    <td><?=$d['nama']?></td>
    <td><?=$d['nama_kelas']?></td>
    <td><?=$d['tahun']?> (<?=$d['semester']?>)</td>
    <td><a href="?hapus=<?=$d['id']?>" onclick="return confirm('Hapus?')">Hapus</a></td>
</tr>
<?php }?>
</table>