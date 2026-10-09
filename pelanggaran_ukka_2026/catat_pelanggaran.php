<?php
session_start();
if(!isset($_SESSION['login'])){ header("Location: login.php"); exit; }
include 'database.php';

if(isset($_POST['simpan'])){
    $nis = $_POST['nis'];
    $jenis = $_POST['jenis'];
    $tgl = $_POST['tgl'];
    $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $q = mysqli_query($koneksi,"INSERT INTO tbl_pelanggaran (nis, id_jenis, tgl, keterangan) VALUES ('$nis','$jenis','$tgl','$ket')");
    if($q){
        echo "<script>alert('Data pelanggaran berhasil disimpan!'); window.location='catat_pelanggaran.php';</script>";
    } else {
        echo "<script>alert('Gagal: ".mysqli_error($koneksi)."');</script>";
    }
}

// hapus pelanggaran (biar bisa hapus jenis nanti)
if(isset($_GET['hapus'])){
    mysqli_query($koneksi,"DELETE FROM tbl_pelanggaran WHERE id='$_GET[hapus]'");
    header("Location: catat_pelanggaran.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Catat Pelanggaran</title>
<style>
body{font-family:Arial;background:#f8f9fa;padding:20px;}
.container{max-width:1000px;margin:auto;background:white;padding:25px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
input, select, textarea{padding:8px;border:1px solid #000;border-radius:5px;width:300px;}
textarea{width:400px;height:70px;}
button{padding:8px 15px;background:#000;color:#fff;border:none;border-radius:5px;cursor:pointer;font-weight:bold;}
button:hover{background:#333;}
table{border-collapse:collapse;width:100%;margin-top:15px;}
th{background:#000;color:#fff;}
th,td{border:1px solid #000;padding:8px;text-align:center;}
a{color:blue;text-decoration:none;}
</style>
</head>
<body>
<div class="container">
<h2>Catat Pelanggaran</h2>
<a href="dashboard.php">← Kembali ke Dashboard</a>
<hr>

<form method="post">
<table style="border:none" cellpadding="5">
<tr><td style="border:none;text-align:left">Siswa</td><td style="border:none;text-align:left">:
    <select name="nis" required>
        <option value="">-- Pilih Siswa --</option>
        <?php $s=mysqli_query($koneksi,"SELECT * FROM tbl_siswa ORDER BY nama"); while($d=mysqli_fetch_assoc($s)) echo "<option value='$d[nis]'>$d[nis] - $d[nama]</option>";?>
    </select>
</td></tr>
<tr><td style="border:none;text-align:left">Jenis Pelanggaran</td><td style="border:none;text-align:left">:
    <select name="jenis" required>
        <option value="">-- Pilih Jenis --</option>
        <?php $j=mysqli_query($koneksi,"SELECT j.*, k.nama_kategori FROM tbl_jenis j JOIN tbl_kategori k ON j.id_kategori=k.id"); while($r=mysqli_fetch_assoc($j)) echo "<option value='$r[id]'>$r[nama_kategori] - $r[nama_jenis] ($r[poin] poin)</option>";?>
    </select>
</td></tr>
<tr><td style="border:none;text-align:left">Tanggal</td><td style="border:none;text-align:left">: <input type="date" name="tgl" value="<?=date('Y-m-d')?>" required></td></tr>
<tr><td style="border:none;text-align:left;vertical-align:top">Keterangan</td><td style="border:none;text-align:left">: <textarea name="keterangan" required></textarea></td></tr>
<tr><td style="border:none"></td><td style="border:none;text-align:left"><button type="submit" name="simpan">Simpan Pelanggaran</button></td></tr>
</table>
</form>

<hr>
<h3>Data Pelanggaran</h3>
<table>
<tr><th>No</th><th>Tanggal</th><th>NIS</th><th>Nama</th><th>Pelanggaran</th><th>Poin</th><th>Aksi</th></tr>
<?php
$no=1;
$q=mysqli_query($koneksi,"SELECT p.*, s.nama, j.nama_jenis, j.poin FROM tbl_pelanggaran p JOIN tbl_siswa s ON p.nis=s.nis JOIN tbl_jenis j ON p.id_jenis=j.id ORDER BY p.tgl DESC");
if(mysqli_num_rows($q)==0){ echo "<tr><td colspan='7'>Belum ada data</td></tr>"; }
while($d=mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?=$no++?></td>
    <td><?=$d['tgl']?></td>
    <td><?=$d['nis']?></td>
    <td><?=$d['nama']?></td>
    <td><?=$d['nama_jenis']?></td>
    <td><?=$d['poin']?></td>
    <td><a href="?hapus=<?=$d['id']?>" onclick="return confirm('Hapus data ini?')">Hapus</a></td>
</tr>
<?php }?>
</table>

</div>
</body>
</html>