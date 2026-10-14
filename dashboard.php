<?php
require 'database.php';
wajib_login();

function jumlah($koneksi, $sql) {
    return (int)$koneksi->query($sql)->fetch_row()[0];
}

$total_siswa = jumlah(
    $koneksi,
    "SELECT COUNT(*) FROM siswa WHERE aktif = 1"
);

$total_jenis = jumlah(
    $koneksi,
    "SELECT COUNT(*) FROM jenis_pelanggaran"
);

$total_pelanggaran = jumlah(
    $koneksi,
    "SELECT COUNT(*) FROM pelanggaran"
);

$total_poin = jumlah(
    $koneksi,
    "SELECT COALESCE(SUM(j.poin),0)
     FROM pelanggaran p
     JOIN jenis_pelanggaran j ON j.id = p.jenis_id"
);

$terbaru = $koneksi->query(
    "SELECT p.tanggal, s.nama AS siswa,
            j.nama AS jenis, j.poin
     FROM pelanggaran p
     JOIN siswa s ON s.id = p.siswa_id
     JOIN jenis_pelanggaran j ON j.id = p.jenis_id
     ORDER BY p.tanggal DESC, p.id DESC
     LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <strong>SI Pelanggaran Siswa</strong>
    <span><?= e($_SESSION['nama']) ?> ·
        <?= e($_SESSION['role']) ?></span>
</header>

<div class="layout">
<aside class="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_kategori.php">Kategori Pelanggaran</a>
    <a href="kelola_jenis_pelanggaran.php">Jenis Pelanggaran</a>
    <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
    <a href="tindakan.php">Tindakan</a>
    <a href="riwayat.php">Riwayat</a>
    <a href="rekap_point.php">Rekap Poin</a>
    <a href="laporan.php">Laporan</a>
    <a href="logout.php">Logout</a>
</aside>

<main class="content">
    <h1>Dashboard</h1>
    <p>Selamat datang, <?= e($_SESSION['nama']) ?>!</p>

    <div class="stats">
        <div class="stat">
            <span>Total Siswa</span>
            <h2><?= $total_siswa ?></h2>
        </div>
        <div class="stat">
            <span>Jenis Pelanggaran</span>
            <h2><?= $total_jenis ?></h2>
        </div>
        <div class="stat">
            <span>Kejadian Pelanggaran</span>
            <h2><?= $total_pelanggaran ?></h2>
        </div>
        <div class="stat">
            <span>Total Poin</span>
            <h2><?= $total_poin ?></h2>
        </div>
    </div>

    <h2>10 Pelanggaran Terbaru</h2>
    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Nama Siswa</th>
            <th>Pelanggaran</th>
            <th>Poin</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($r = $terbaru->fetch_assoc()): ?>
        <tr>
            <td><?= e($r['tanggal']) ?></td>
            <td><?= e($r['siswa']) ?></td>
            <td><?= e($r['jenis']) ?></td>
            <td><?= (int)$r['poin'] ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</main>
</div>
</body>
</html>