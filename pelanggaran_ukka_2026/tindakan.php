<?php
include 'database.php';

// TAMBAH
if(isset($_POST['simpan'])){
    $pel = $_POST['pelanggaran'];
    $tind = $_POST['tindakan'];
    $tgl = date('Y-m-d');
    mysqli_query($koneksi,"INSERT INTO tbl_tindakan (id_pelanggaran, tindakan, tgl_tindakan) VALUES ('$pel','$tind','$tgl')");
    header("Location: tindakan.php");
}

// HAPUS
if(isset($_GET['hapus'])){
    mysqli_query($koneksi,"DELETE FROM tbl_tindakan WHERE id='$_GET[hapus]'");
    header("Location: tindakan.php");
}

// EDIT
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $tind = $_POST['tindakan'];
    mysqli_query($koneksi,"UPDATE tbl_tindakan SET tindakan='$tind' WHERE id='$id'");
    header("Location: tindakan.php");
}

$edit_data = null;
if(isset($_GET['edit'])){
    $edit_data = mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT * FROM tbl_tindakan WHERE id='$_GET[edit]'"));
}
?>
<h2>Kelola Tindakan</h2>
<a href="dashboard.php">Kembali</a>
<hr>

<?php if($edit_data):?>
<h3>Edit Tindakan</h3>
<form method="post">
    <input type="hidden" name="id" value="<?=$edit_data['id']?>">
    Tindakan: <input type="text" name="tindakan" value="<?=$edit_data['tindakan']?>" required>
    <button name="update">Update</button>
    <a href="tindakan.php">Batal</a>
</form>
<?php else:?>
<h3>Tambah Tindakan</h3>
<form method="post">
    Pelanggaran:
    <select name="pelanggaran" required>
        <option value="">-- Pilih Pelanggaran --</option>
        <?php
        $q=mysqli_query($koneksi,"SELECT p.id, s.nama, j.nama_jenis FROM tbl_pelanggaran p JOIN tbl_siswa s ON p.nis=s.nis JOIN tbl_jenis j ON p.id_jenis=j.id");
        while($d=mysqli_fetch_assoc($q)){
            echo "<option value='$d[id]'>$d[id] - $d[nama] - $d[nama_jenis]</option>";
        }
       ?>
    </select>
    Tindakan: <input type="text" name="tindakan" placeholder="Skors, Panggilan Orang Tua, dll" required>
    <button name="simpan">Simpan</button>
</form>
<?php endif;?>

<br>
<table border="1" cellpadding="8" cellspacing="0">
<tr><th>No</th><th>Tgl</th><th>Siswa</th><th>Pelanggaran</th><th>Tindakan</th><th>Aksi</th></tr>
<?php
$no=1;
$q=mysqli_query($koneksi,"SELECT t.*, s.nama, j.nama_jenis FROM tbl_tindakan t JOIN tbl_pelanggaran p ON t.id_pelanggaran=p.id JOIN tbl_siswa s ON p.nis=s.nis JOIN tbl_jenis j ON p.id_jenis=j.id ORDER BY t.tgl_tindakan DESC");
while($d=mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?=$no++?></td>
    <td><?=$d['tgl_tindakan']?></td>
    <td><?=$d['nama']?></td>
    <td><?=$d['nama_jenis']?></td>
    <td><?=$d['tindakan']?></td>
    <td>
        <a href="?edit=<?=$d['id']?>">Edit</a> |
        <a href="?hapus=<?=$d['id']?>" onclick="return confirm('Hapus tindakan ini?')">Hapus</a>
    </td>
</tr>
<?php }?>
</table>