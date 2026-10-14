<?php
require 'database.php';
wajib_login();

$sql = "SELECT p.id, p.tanggal, p.keterangan, p.status,
               s.nis, s.nama AS nama_siswa,
               j.nama AS nama_pelanggaran, j.poin
        FROM pelanggaran p
        JOIN siswa s ON s.id = p.siswa_id
        JOIN jenis_pelanggaran j ON j.id = p.jenis_id
        ORDER BY p.tanggal DESC, p.id DESC";

$data = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Pelanggaran</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <strong>Riwayat Pelanggaran</strong>
    <a class="btn" href="dashboard.php">Dashboard</a>
</header>
<main class="content">
    <h1>Riwayat Pelanggaran Siswa</h1>

    <?php if (isset($_GET['sukses'])): ?>
        <p>Data pelanggaran berhasil disimpan.</p>
    <?php endif; ?>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Pelanggaran</th>
            <th>Poin</th>
            <th>Status</th>
            <th>Keterangan</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($r = $data->fetch_assoc()): ?>
        <tr>
            <td><?= e($r['tanggal']) ?></td>
            <td><?= e($r['nis']) ?></td>
            <td><?= e($r['nama_siswa']) ?></td>
            <td><?= e($r['nama_pelanggaran']) ?></td>
            <td><?= (int)$r['poin'] ?></td>
            <td><?= e($r['status']) ?></td>
            <td><?= e($r['keterangan']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</main>
</body>
</html>