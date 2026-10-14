<?php
require 'database.php';
wajib_login();

$sql = "SELECT s.nis, s.nama,
               COALESCE(SUM(j.poin), 0) AS total_poin,
               COUNT(p.id) AS jumlah_pelanggaran
        FROM siswa s
        LEFT JOIN pelanggaran p ON p.siswa_id = s.id
        LEFT JOIN jenis_pelanggaran j ON j.id = p.jenis_id
        WHERE s.aktif = 1
        GROUP BY s.id, s.nis, s.nama
        ORDER BY total_poin DESC, s.nama ASC";

$data = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rekap Poin</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <strong>Rekap Poin Siswa</strong>
    <a class="btn" href="dashboard.php">Dashboard</a>
</header>
<main class="content">
    <h1>Rekapitulasi Poin Pelanggaran</h1>
    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Jumlah Kejadian</th>
            <th>Total Poin</th>
        </tr>
        </thead>
        <tbody>
        <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= e($r['nis']) ?></td>
            <td><?= e($r['nama']) ?></td>
            <td><?= (int)$r['jumlah_pelanggaran'] ?></td>
            <td><?= (int)$r['total_poin'] ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
    <button onclick="window.print()">Cetak Rekap</button>
</main>
</body>
</html>