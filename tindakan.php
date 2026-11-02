
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "database.php";

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   CEK ROLE
========================= */

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'guru', 'wali_kelas'])) {
    die("Akses ditolak.");
}

/* =========================
   TAMBAH TINDAKAN
========================= */

if (isset($_POST['simpan'])) {

    $pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal'] ?? '');
    $tindakan = mysqli_real_escape_string($koneksi, trim($_POST['tindakan'] ?? ''));
    $catatan = mysqli_real_escape_string($koneksi, trim($_POST['catatan'] ?? ''));

    $petugas_id = (int)($_SESSION['user_id'] ?? 0);

    if ($pelanggaran_id <= 0 || $tanggal == '' || $tindakan == '') {

        $error = "Semua data wajib diisi.";

    } else {

        $query = mysqli_query(
            $koneksi,
            "INSERT INTO tindakan
            (pelanggaran_id, tanggal, tindakan, petugas_id, catatan)
            VALUES
            ($pelanggaran_id, '$tanggal', '$tindakan', $petugas_id, '$catatan')"
        );

        if ($query) {
            header("Location: tindakan.php");
            exit;
        } else {
            $error = "Gagal menyimpan tindakan: " . mysqli_error($koneksi);
        }
    }
}

/* =========================
   HAPUS TINDAKAN
========================= */

if (isset($_GET['hapus'])) {

    if ($role != 'admin') {
        die("Akses ditolak.");
    }

    $id = (int)$_GET['hapus'];

    if ($id > 0) {
        mysqli_query(
            $koneksi,
            "DELETE FROM tindakan WHERE id = $id"
        );
    }

    header("Location: tindakan.php");
    exit;
}

/* =========================
   DATA PELANGGARAN
========================= */

$pelanggaran = mysqli_query(
    $koneksi,
    "SELECT
        p.id,
        s.nama AS nama_siswa,
        jp.nama AS nama_pelanggaran
    FROM pelanggaran p
    INNER JOIN siswa s ON p.siswa_id = s.id
    INNER JOIN jenis_pelanggaran jp ON p.jenis_id = jp.id
    ORDER BY p.id DESC"
);

if (!$pelanggaran) {
    die("Gagal mengambil data pelanggaran: " . mysqli_error($koneksi));
}

/* =========================
   DATA TINDAKAN
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT
        t.id,
        t.tanggal,
        t.tindakan,
        t.catatan,
        s.nama AS nama_siswa,
        jp.nama AS nama_pelanggaran,
        u.nama AS nama_petugas
    FROM tindakan t
    INNER JOIN pelanggaran p ON t.pelanggaran_id = p.id
    INNER JOIN siswa s ON p.siswa_id = s.id
    INNER JOIN jenis_pelanggaran jp ON p.jenis_id = jp.id
    LEFT JOIN users u ON t.petugas_id = u.id
    ORDER BY t.id DESC"
);

if (!$data) {
    die("Gagal mengambil data tindakan: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Tindakan</title>

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

/* SIDEBAR */

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

.sidebar a:hover,
.sidebar a.active {
    background: #4f5965;
}

.logout {
    margin-top: 20px;
    background: #b42323;
}

/* CONTENT */

.content {
    margin-left: 220px;
    padding: 30px;
}

/* HEADER */

.header {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    color: #777;
    margin-bottom: 0;
}

/* BOX */

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 22px;
    margin-bottom: 20px;
}

.box h2 {
    margin-top: 0;
}

/* TOMBOL */

.btn {
    display: inline-block;
    padding: 10px 16px;
    border: none;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-tambah {
    background: #087f5b;
}

.btn-tambah:hover {
    background: #066b4d;
}

.btn-simpan {
    background: #687280;
}

.btn-batal {
    background: #777;
}

.btn-hapus {
    background: #b42323;
}

.btn-hapus:hover {
    background: #941d1d;
}

/* FORM */

.form-group {
    margin-bottom: 16px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
}

select,
input,
textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: white;
    font-family: Arial, sans-serif;
}

textarea {
    min-height: 90px;
    resize: vertical;
}

.form-actions {
    margin-top: 20px;
}

/* ERROR */

.error {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

/* TABLE */

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #687280;
    color: white;
    padding: 11px;
    text-align: left;
    font-size: 13px;
}

td {
    padding: 11px;
    border-bottom: 1px solid #ddd;
    font-size: 13px;
}

tr:hover {
    background: #f7f7f7;
}

/* RESPONSIVE */

@media(max-width:700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .content {
        margin-left: 0;
        padding: 15px;
    }

    .header {
        display: block;
    }

    .btn-tambah {
        margin-top: 15px;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-title">
        ⚖ Pelanggaran<br>
        Siswa
    </div>

    <?php if ($role == 'admin') { ?>

        <!-- MENU ADMIN -->

        <a href="dashboard.php">🏠 Dashboard</a>

        <a href="kelola_siswa.php">Data Siswa</a>

        <a href="kelola_guru.php">Data Guru</a>

        <a href="kelola_kelas.php">Data Kelas</a>

        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>

        <a href="kelola_wali_kelas.php">Wali Kelas</a>

        <a href="penempatan_siswa.php">Penempatan Siswa</a>

        <a href="kelola_pelanggaran_kategori.php">
            ⚠ Kategori Pelanggaran
        </a>

        <a href="pelanggaran.php">Data Pelanggaran</a>

        <a href="tindakan.php" class="active">Data Tindakan</a>

        <a href="laporan.php">Laporan</a>

        <a href="about_me.php">ℹ About Me</a>

    <?php } else { ?>

        <!-- MENU GURU DAN WALI KELAS -->

        <a href="dashboard.php">🏠 Dashboard</a>

        <a href="catat_pelanggaran.php">Catat Pelanggaran</a>

        <a href="tindakan.php" class="active">Tindakan</a>

        <a href="riwayat.php">Riwayat</a>

        <a href="rekap_poin.php">Rekap Poin</a>

    <?php } ?>

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>


<!-- CONTENT -->

<div class="content">

    <div class="header">

        <div>
            <h1>Data Tindakan</h1>

            <p>
                Mengelola tindakan terhadap pelanggaran siswa.
            </p>
        </div>

        <?php if ($role == 'admin' || $role == 'guru') { ?>

            <a href="tindakan.php?tambah=1"
               class="btn btn-tambah">
                + Tambah Tindakan
            </a>

        <?php } ?>

    </div>


    <?php if (isset($error)) { ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- FORM TAMBAH -->

    <?php if (isset($_GET['tambah']) && $_GET['tambah'] == '1') { ?>

        <?php if ($role == 'admin' || $role == 'guru') { ?>

            <div class="box">

                <h2>Tambah Tindakan</h2>

                <form method="POST">

                    <div class="form-group">

                        <label>Pelanggaran Siswa</label>

                        <select name="pelanggaran_id" required>

                            <option value="">
                                -- Pilih Pelanggaran --
                            </option>

                            <?php while ($p = mysqli_fetch_assoc($pelanggaran)) { ?>

                                <option value="<?= $p['id']; ?>">
                                    <?= htmlspecialchars($p['nama_siswa']); ?>
                                    -
                                    <?= htmlspecialchars($p['nama_pelanggaran']); ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="form-group">

                        <label>Tanggal Tindakan</label>

                        <input
                            type="date"
                            name="tanggal"
                            value="<?= date('Y-m-d'); ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label>Tindakan</label>

                        <textarea
                            name="tindakan"
                            placeholder="Masukkan tindakan yang diberikan..."
                            required
                        ></textarea>

                    </div>

                    <div class="form-group">

                        <label>Catatan</label>

                        <textarea
                            name="catatan"
                            placeholder="Catatan tambahan (opsional)"
                        ></textarea>

                    </div>

                    <div class="form-actions">

                        <button
                            type="submit"
                            name="simpan"
                            class="btn btn-simpan"
                        >
                            Simpan Tindakan
                        </button>

                        <a href="tindakan.php" class="btn btn-batal">
                            Batal
                        </a>

                    </div>

                </form>

            </div>

        <?php } ?>

    <?php } ?>


    <!-- DAFTAR TINDAKAN -->

    <div class="box">

        <h2>Daftar Tindakan Siswa</h2>

        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Tanggal</th>
                        <th>Tindakan</th>
                        <th>Petugas</th>
                        <th>Catatan</th>

                        <?php if ($role == 'admin') { ?>
                            <th>Aksi</th>
                        <?php } ?>
                    </tr>

                </thead>

                <tbody>

                    <?php

                    $no = 1;

                    if (mysqli_num_rows($data) > 0) {

                        while ($row = mysqli_fetch_assoc($data)) {

                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <td>
                                <b><?= htmlspecialchars($row['nama_siswa']); ?></b>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nama_pelanggaran']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['tanggal']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['tindakan']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nama_petugas'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['catatan'] ?? '-'); ?>
                            </td>

                            <?php if ($role == 'admin') { ?>

                                <td>

                                    <a
                                        href="edit_tindakan.php?id=<?= $row['id']; ?>"
                                        class="btn btn-simpan"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="tindakan.php?hapus=<?= $row['id']; ?>"
                                        class="btn btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus tindakan ini?')"
                                    >
                                        Hapus
                                    </a>

                                </td>

                            <?php } ?>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>
                            <td
                                colspan="<?= $role == 'admin' ? 8 : 7; ?>"
                                style="text-align:center;padding:25px;"
                            >
                                Belum ada data tindakan.
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>
