<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

$role = $_SESSION['role'] ?? '';

if ($role != 'admin') {
    die("Akses ditolak.");
}

/* =====================================================
   AMBIL ID DARI URL
===================================================== */

$id = intval($_GET['id'] ?? $_GET['edit'] ?? 0);

if ($id <= 0) {
    header("Location: penempatan_siswa.php");
    exit;
}

$query_edit = mysqli_query(
    $koneksi,
    "SELECT p.*, s.nis, s.nama AS nama_siswa 
     FROM penempatan_siswa p
     LEFT JOIN siswa s ON p.siswa_id = s.id
     WHERE p.id = $id"
);

if (mysqli_num_rows($query_edit) == 0) {
    header("Location: penempatan_siswa.php");
    exit;
}

$edit_data = mysqli_fetch_assoc($query_edit);


/* =====================================================
   PROSES UPDATE DATA
===================================================== */

if (isset($_POST['update'])) {

    $kelas_id = intval($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = intval($_POST['tahun_ajaran_id'] ?? 0);

    if ($kelas_id <= 0 || $tahun_ajaran_id <= 0) {

        $pesan = "Kelas dan Tahun Ajaran harus diisi.";

    } else {

        $update = mysqli_query(
            $koneksi,
            "UPDATE penempatan_siswa
             SET kelas_id = $kelas_id,
                 tahun_ajaran_id = $tahun_ajaran_id
             WHERE id = $id"
        );

        if ($update) {

            header("Location: penempatan_siswa.php");
            exit;

        } else {

            $pesan = "Data gagal diperbarui: " . mysqli_error($koneksi);
        }
    }
}


/* =====================================================
   DATA DROPDOWN
===================================================== */

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC"
);

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM tahun_ajaran WHERE aktif = 1 ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Penempatan Siswa</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    color: #333;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 220px;
    height: 100vh;
    background: #687280;
    padding: 20px 12px;
    overflow-y: auto;
}

.sidebar-title {
    color: white;
    font-size: 19px;
    font-weight: bold;
    margin: 0 8px 25px;
    line-height: 1.4;
}

.sidebar a {
    display: block;
    padding: 11px 13px;
    margin-bottom: 5px;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
}

.sidebar a:hover {
    background: #56606d;
}

.sidebar a.active {
    background: #4f5965;
    font-weight: bold;
}

.sidebar .logout {
    margin-top: 20px;
    background: #b42323;
}

.sidebar .logout:hover {
    background: #941d1d;
}

.content {
    margin-left: 220px;
    padding: 30px;
}

.header {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 20px;
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    margin: 7px 0 0;
    color: #777;
    font-size: 14px;
}

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 20px;
    margin-bottom: 20px;
}

.box h2 {
    margin-top: 0;
    font-size: 18px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 6px;
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 9px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
}

.form-group input[readonly] {
    background: #e9ecef;
    color: #555;
}

.btn {
    background: #687280;
    color: white;
    border: none;
    padding: 9px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    display: inline-block;
}

.btn:hover {
    background: #56606d;
}

.btn-batal {
    background: #b42323;
    margin-left: 5px;
}

.btn-batal:hover {
    background: #941d1d;
}

.pesan {
    background: #fff3cd;
    border: 1px solid #ffe69c;
    color: #664d03;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 13px;
}

@media (max-width: 700px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .content {
        margin-left: 0;
        padding: 15px;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-title">
        Pelanggaran<br>
        Siswa
    </div>

    <a href="dashboard.php">Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php" class="active">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">About Me</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

    <div class="header">
        <h1>Edit Penempatan Siswa</h1>
        <p>Ubah kelas atau tahun ajaran untuk siswa terpilih.</p>
    </div>

    <?php if (!empty($pesan)) { ?>
        <div class="pesan">
            <?= htmlspecialchars($pesan); ?>
        </div>
    <?php } ?>

    <div class="box">
        <h2>Form Edit Penempatan</h2>

        <form method="POST">
            <div class="form-group">
                <label>Siswa</label>
                <input type="text" value="<?= htmlspecialchars($edit_data['nis'] . ' - ' . $edit_data['nama_siswa']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <select name="kelas_id" required>
                 <option value="">-- Pilih Kelas --</option>
                 <?php while ($k = mysqli_fetch_assoc($query_kelas)) { ?>
                   <option value="<?= $k['id']; ?>" <?= ($edit_data['kelas_id'] == $k['id']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($k['nama_kelas']); ?>
                 </option>
                   <?php } ?>
                </select>
            </div>

            <div class="form-group">
              <label>Tahun Ajaran</label>
              <select name="tahun_ajaran_id" required>
                <option value="">-- Pilih Tahun Ajaran --</option>
                 <?php while ($ta = mysqli_fetch_assoc($query_tahun)) { ?>
                <option value="<?= $ta['id']; ?>" <?= ($edit_data['tahun_ajaran_id'] == $ta['id']) ? 'selected' : ''; ?>>
                 <?= htmlspecialchars($ta['nama']); ?>
                    </option>
                 <?php } ?>
             </select>
            </div>

            <button type="submit" name="update" class="btn">Simpan Perubahan</button>
            <a href="penempatan_siswa.php" class="btn btn-batal">Batal</a>
        </form>
    </div>

</div>

</body>
</html>
