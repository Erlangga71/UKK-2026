<?php
require 'database.php';
wajib_login();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';

    if (!hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('Token formulir tidak valid. Muat ulang halaman.');
    }

    $siswa_id = filter_input(
        INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT
    );
    $jenis_id = filter_input(
        INPUT_POST, 'jenis_id', FILTER_VALIDATE_INT
    );
    $tanggal = $_POST['tanggal'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    $tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);
    if (
        !$siswa_id || !$jenis_id ||
        !$tanggal_obj ||
        $tanggal_obj->format('Y-m-d') !== $tanggal
    ) {
        $pesan = 'Periksa kembali data yang dimasukkan.';
    } else {
        $stmt = $koneksi->prepare(
            "SELECT id FROM siswa WHERE id = ? AND aktif = 1"
        );
        $stmt->bind_param('i', $siswa_id);
        $stmt->execute();
        $siswa_valid = $stmt->get_result()->num_rows > 0;

        $stmt = $koneksi->prepare(
            "SELECT id FROM jenis_pelanggaran WHERE id = ?"
        );
        $stmt->bind_param('i', $jenis_id);
        $stmt->execute();
        $jenis_valid = $stmt->get_result()->num_rows > 0;

        if (!$siswa_valid || !$jenis_valid) {
            $pesan = 'Siswa atau jenis pelanggaran tidak valid.';
        } else {
            $guru_id = null;
            $uid = (int)$_SESSION['user_id'];

            $stmt = $koneksi->prepare(
                "SELECT id FROM guru WHERE user_id = ?"
            );
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $guru = $stmt->get_result()->fetch_assoc();

            if ($guru) {
                $guru_id = (int)$guru['id'];
            }

            $stmt = $koneksi->prepare(
                "INSERT INTO pelanggaran
                 (siswa_id, jenis_id, guru_id, tanggal, keterangan)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'iiiss',
                $siswa_id,
                $jenis_id,
                $guru_id,
                $tanggal,
                $keterangan
            );
            $stmt->execute();

            header('Location: riwayat.php?sukses=1');
            exit;
        }
    }
}

$siswa = $koneksi->query(
    "SELECT id, nis, nama FROM siswa
     WHERE aktif = 1 ORDER BY nama"
);

$jenis = $koneksi->query(
    "SELECT id, nama, poin FROM jenis_pelanggaran
     ORDER BY nama"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Catat Pelanggaran</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <strong>Catat Pelanggaran Siswa</strong>
    <a class="btn" href="dashboard.php">Kembali</a>
</header>

<main class="content">
    <div class="panel">
        <h1>Form Pencatatan Pelanggaran</h1>

        <?php if ($pesan): ?>
            <p class="error"><?= e($pesan) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf"
                   value="<?= e($_SESSION['csrf']) ?>">

            <label>Nama Siswa</label>
            <select name="siswa_id" required>
                <option value="">-- Pilih Siswa --</option>
                <?php while ($s = $siswa->fetch_assoc()): ?>
                <option value="<?= (int)$s['id'] ?>">
                    <?= e($s['nis']) ?> - <?= e($s['nama']) ?>
                </option>
                <?php endwhile; ?>
            </select>

            <label>Jenis Pelanggaran</label>
            <select name="jenis_id" required>
                <option value="">-- Pilih Pelanggaran --</option>
                <?php while ($j = $jenis->fetch_assoc()): ?>
                <option value="<?= (int)$j['id'] ?>">
                    <?= e($j['nama']) ?>
                    (<?= (int)$j['poin'] ?> poin)
                </option>
                <?php endwhile; ?>
            </select>

            <label>Tanggal Kejadian</label>
            <input type="date" name="tanggal"
                   value="<?= date('Y-m-d') ?>" required>

            <label>Keterangan</label>
            <textarea name="keterangan" rows="4"
                      placeholder="Jelaskan kejadian pelanggaran"></textarea>

            <button type="submit">Simpan Pelanggaran</button>
        </form>
    </div>
</main>
</body>
</html>