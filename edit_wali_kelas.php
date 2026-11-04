
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

if (($_SESSION['role'] ?? '') != 'admin') {
    echo "Akses ditolak.";
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_wali_kelas.php");
    exit;
}

// Ambil data wali kelas
$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM wali_kelas
    WHERE id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wali = mysqli_fetch_assoc($result);

if (!$wali) {
    echo "Data wali kelas tidak ditemukan.";
    exit;
}

$error = "";

// Proses update
if (isset($_POST['update'])) {
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);

    if ($guru_id <= 0 || $kelas_id <= 0 || $tahun_ajaran_id <= 0) {
        $error = "Semua data harus dipilih.";
    } else {
        // Pastikan tahun ajaran yang dipilih aktif,
        // atau merupakan tahun lama yang sedang diedit.
        $cekTahun = mysqli_prepare($koneksi, "
            SELECT id
            FROM tahun_ajaran
            WHERE id = ?
            AND (aktif = 1 OR id = ?)
            LIMIT 1
        ");
        mysqli_stmt_bind_param(
            $cekTahun,
            "ii",
            $tahun_ajaran_id,
            $wali['tahun_ajaran_id']
        );
        mysqli_stmt_execute($cekTahun);

        if (mysqli_num_rows(mysqli_stmt_get_result($cekTahun)) == 0) {
            $error = "Tahun ajaran tidak aktif atau tidak ditemukan.";
        } else {
            // Pastikan kelas belum dipakai wali kelas lain
            $cekKelas = mysqli_prepare($koneksi, "
                SELECT id
                FROM wali_kelas
                WHERE kelas_id = ?
                AND tahun_ajaran_id = ?
                AND id != ?
                LIMIT 1
            ");
            mysqli_stmt_bind_param(
                $cekKelas,
                "iii",
                $kelas_id,
                $tahun_ajaran_id,
                $id
            );
            mysqli_stmt_execute($cekKelas);

            if (mysqli_num_rows(mysqli_stmt_get_result($cekKelas)) > 0) {
                $error = "Kelas tersebut sudah memiliki wali kelas pada tahun ajaran ini.";
            } else {
                $update = mysqli_prepare($koneksi, "
                    UPDATE wali_kelas
                    SET guru_id = ?,
                        kelas_id = ?,
                        tahun_ajaran_id = ?
                    WHERE id = ?
                ");

                mysqli_stmt_bind_param(
                    $update,
                    "iiii",
                    $guru_id,
                    $kelas_id,
                    $tahun_ajaran_id,
                    $id
                );

                if (mysqli_stmt_execute($update)) {
                    header("Location: kelola_wali_kelas.php");
                    exit;
                } else {
                    $error = "Gagal mengubah data: " . mysqli_error($koneksi);
                }
            }
        }
    }

    // Pertahankan pilihan terakhir jika validasi gagal
    $wali['guru_id'] = $guru_id;
    $wali['kelas_id'] = $kelas_id;
    $wali['tahun_ajaran_id'] = $tahun_ajaran_id;
}

// Ambil daftar guru
$query_guru = mysqli_query($koneksi, "
    SELECT id, nama
    FROM guru
    ORDER BY nama ASC
");

// Ambil tahun aktif dan tahun yang sedang diedit
$query_tahun = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM tahun_ajaran
    WHERE aktif = 1 OR id = ?
    ORDER BY id DESC
");
mysqli_stmt_bind_param($query_tahun, "i", $wali['tahun_ajaran_id']);
mysqli_stmt_execute($query_tahun);
$result_tahun = mysqli_stmt_get_result($query_tahun);

// Ambil kelas yang belum memiliki wali pada tahun terpilih,
// ditambah kelas yang sedang diedit.
$tahunTerpilih = (int)$wali['tahun_ajaran_id'];

$query_kelas = mysqli_prepare($koneksi, "
    SELECT k.id, k.nama_kelas
    FROM kelas k
    WHERE k.status_aktif = 1
    AND (
        k.id = ?
        OR NOT EXISTS (
            SELECT 1
            FROM wali_kelas wk
            WHERE wk.kelas_id = k.id
            AND wk.tahun_ajaran_id = ?
            AND wk.id != ?
        )
    )
    ORDER BY k.nama_kelas ASC
");
mysqli_stmt_bind_param(
    $query_kelas,
    "iii",
    $wali['kelas_id'],
    $tahunTerpilih,
    $id
);
mysqli_stmt_execute($query_kelas);
$result_kelas = mysqli_stmt_get_result($query_kelas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Wali Kelas</title>

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
    width: 220px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #687280;
    padding: 20px 0;
    color: white;
}

.sidebar h2 {
    font-size: 19px;
    text-align: center;
    margin: 5px 10px 25px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    font-size: 14px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #4f5965;
}

.sidebar a.logout {
    margin-top: 20px;
    background: #dc3545;
}

.main {
    margin-left: 220px;
    padding: 30px;
}

.box {
    max-width: 750px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 25px;
}

h1 {
    margin: 0 0 8px;
    font-size: 24px;
}

.keterangan {
    color: #777;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
    font-weight: bold;
}

select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: white;
}

button,
.kembali {
    display: inline-block;
    padding: 10px 16px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

button {
    background: #28a745;
    color: white;
}

button:hover {
    background: #218838;
}

.kembali {
    background: #ddd;
    color: #333;
    margin-left: 5px;
}

.error {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 18px;
}

@media (max-width: 700px) {
    .sidebar {
        width: 180px;
    }

    .main {
        margin-left: 180px;
        padding: 15px;
    }
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>UKK 2026</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php" class="active">Wali Kelas</a>
    <a href="kelola_kategori.php">Kategori Pelanggaran</a>
    <a href="kelola_pelanggaran.php">Data Pelanggaran</a>
    <a href="kelola_tindakan.php">Data Tindakan</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main">
    <div class="box">

        <h1>Edit Data Wali Kelas</h1>
        <p class="keterangan">
            Ubah guru, kelas, atau tahun ajaran.
        </p>

        <?php if ($error != ""): ?>
            <div class="error">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Guru</label>
                <select name="guru_id" required>
                    <option value="">-- Pilih Guru --</option>

                    <?php while ($guru = mysqli_fetch_assoc($query_guru)): ?>
                        <option
                            value="<?= $guru['id']; ?>"
                            <?= ($guru['id'] == $wali['guru_id']) ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($guru['nama']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tahun Ajaran</label>
                <select name="tahun_ajaran_id" required>
                    <option value="">-- Pilih Tahun Ajaran --</option>

                    <?php while ($tahun = mysqli_fetch_assoc($result_tahun)): ?>
                        <option
                            value="<?= $tahun['id']; ?>"
                            <?= ($tahun['id'] == $wali['tahun_ajaran_id']) ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($tahun['nama']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <select name="kelas_id" required>
                    <option value="">-- Pilih Kelas --</option>

                    <?php while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                        <option
                            value="<?= $kelas['id']; ?>"
                            <?= ($kelas['id'] == $wali['kelas_id']) ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars($kelas['nama_kelas']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" name="update">
                Simpan Perubahan
            </button>

            <a href="kelola_wali_kelas.php" class="kembali">
                Kembali
            </a>

        </form>
    </div>
</div>

</body>
</html>
