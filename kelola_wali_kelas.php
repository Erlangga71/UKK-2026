
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "database.php";

if (($_SESSION['role'] ?? '') != 'admin') {
    echo "Akses ditolak.";
    exit;
}

$error = "";

/* =========================
   AJAX: AMBIL KELAS TERSEDIA
========================= */

if (isset($_GET['ajax']) && $_GET['ajax'] == 'kelas') {

    header('Content-Type: application/json; charset=utf-8');

    $tahun_id = intval($_GET['tahun_ajaran_id'] ?? 0);

    if ($tahun_id <= 0) {
        echo json_encode([]);
        exit;
    }

    // Pastikan tahun ajaran benar-benar aktif
    $cek_tahun = mysqli_prepare(
        $koneksi,
        "SELECT id FROM tahun_ajaran
         WHERE id = ? AND aktif = 1"
    );

    mysqli_stmt_bind_param($cek_tahun, "i", $tahun_id);
    mysqli_stmt_execute($cek_tahun);

    $hasil_tahun = mysqli_stmt_get_result($cek_tahun);
    $tahun_valid = mysqli_num_rows($hasil_tahun) > 0;

    mysqli_stmt_close($cek_tahun);

    if (!$tahun_valid) {
        echo json_encode([]);
        exit;
    }

    // Ambil kelas yang belum memiliki wali kelas
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT k.id, k.nama_kelas
         FROM kelas k
         WHERE NOT EXISTS (
             SELECT 1
             FROM wali_kelas wk
             WHERE wk.kelas_id = k.id
             AND wk.tahun_ajaran_id = ?
         )
         ORDER BY k.nama_kelas ASC"
    );

    mysqli_stmt_bind_param($stmt, "i", $tahun_id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);

    $kelas_tersedia = [];

    while ($row = mysqli_fetch_assoc($hasil)) {
        $kelas_tersedia[] = $row;
    }

    mysqli_stmt_close($stmt);

    echo json_encode($kelas_tersedia);
    exit;
}

/* =========================
   TAMBAH WALI KELAS
========================= */

if (isset($_POST['simpan'])) {

    $guru_id = intval($_POST['guru_id'] ?? 0);
    $kelas_id = intval($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = intval($_POST['tahun_ajaran_id'] ?? 0);

    if ($guru_id <= 0 || $kelas_id <= 0 || $tahun_ajaran_id <= 0) {

        $error = "Guru, kelas, dan tahun ajaran wajib dipilih.";

    } else {

        // CEK GURU
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM guru WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $guru_id);
        mysqli_stmt_execute($stmt);

        $cek_guru = mysqli_stmt_get_result($stmt);
        $guru_valid = mysqli_num_rows($cek_guru) > 0;

        mysqli_stmt_close($stmt);

        // CEK KELAS
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM kelas WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);

        $cek_kelas = mysqli_stmt_get_result($stmt);
        $kelas_valid = mysqli_num_rows($cek_kelas) > 0;

        mysqli_stmt_close($stmt);

        // CEK TAHUN AJARAN HARUS AKTIF
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM tahun_ajaran
             WHERE id = ? AND aktif = 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
        mysqli_stmt_execute($stmt);

        $cek_tahun = mysqli_stmt_get_result($stmt);
        $tahun_valid = mysqli_num_rows($cek_tahun) > 0;

        mysqli_stmt_close($stmt);

        if (!$guru_valid || !$kelas_valid || !$tahun_valid) {

            $error = "Data guru, kelas, atau tahun ajaran tidak valid. Pastikan tahun ajaran masih aktif.";

        } else {

            // CEK KELAS SUDAH MEMILIKI WALI KELAS ATAU BELUM
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM wali_kelas
                 WHERE kelas_id = ?
                 AND tahun_ajaran_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $kelas_id,
                $tahun_ajaran_id
            );

            mysqli_stmt_execute($stmt);

            $cek = mysqli_stmt_get_result($stmt);
            $sudah_ada = mysqli_num_rows($cek) > 0;

            mysqli_stmt_close($stmt);

            if ($sudah_ada) {

                $error = "Kelas tersebut sudah memiliki wali kelas pada tahun ajaran ini.";

            } else {

                // SIMPAN DATA
                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO wali_kelas
                    (guru_id, kelas_id, tahun_ajaran_id)
                    VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $guru_id,
                    $kelas_id,
                    $tahun_ajaran_id
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: kelola_wali_kelas.php");
                    exit;

                } else {

                    $error = "Gagal menambahkan wali kelas: " . mysqli_stmt_error($stmt);

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

/* =========================
   HAPUS WALI KELAS
========================= */

if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM wali_kelas WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header("Location: kelola_wali_kelas.php");
            exit;

        } else {

            $error = "Gagal menghapus data: " . mysqli_stmt_error($stmt);

            mysqli_stmt_close($stmt);
        }
    }
}

/* =========================
   PENCARIAN
========================= */

$cari = trim($_GET['cari'] ?? '');

$where = "";

if ($cari != '') {

    $cari_aman = mysqli_real_escape_string($koneksi, $cari);

    $where = "WHERE
        g.nama LIKE '%$cari_aman%'
        OR g.nip LIKE '%$cari_aman%'
        OR k.nama_kelas LIKE '%$cari_aman%'
        OR t.nama LIKE '%$cari_aman%'";
}

/* =========================
   PAGINATION
========================= */

$batas = 5;

$halaman = max(1, intval($_GET['halaman'] ?? 1));

$query_jumlah = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM wali_kelas wk
     INNER JOIN guru g ON wk.guru_id = g.id
     INNER JOIN kelas k ON wk.kelas_id = k.id
     INNER JOIN tahun_ajaran t ON wk.tahun_ajaran_id = t.id
     $where"
);

if (!$query_jumlah) {
    die("Gagal menghitung data: " . mysqli_error($koneksi));
}

$total_data = mysqli_fetch_assoc($query_jumlah)['total'];

$total_halaman = max(1, ceil($total_data / $batas));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $batas;

/* =========================
   DATA GURU
========================= */

$guru_data = mysqli_query(
    $koneksi,
    "SELECT id, nip, nama
     FROM guru
     ORDER BY nama ASC"
);

if (!$guru_data) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

/* =========================
   DATA TAHUN AJARAN AKTIF
========================= */

$tahun_data = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM tahun_ajaran
     WHERE aktif = 1
     ORDER BY id DESC"
);

if (!$tahun_data) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}

$jumlah_tahun_aktif = mysqli_num_rows($tahun_data);

/* =========================
   DATA WALI KELAS
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT
        wk.id,
        g.nip,
        g.nama AS nama_guru,
        k.nama_kelas,
        t.nama AS nama_tahun,
        t.aktif
     FROM wali_kelas wk
     INNER JOIN guru g ON wk.guru_id = g.id
     INNER JOIN kelas k ON wk.kelas_id = k.id
     INNER JOIN tahun_ajaran t ON wk.tahun_ajaran_id = t.id
     $where
     ORDER BY wk.id DESC
     LIMIT $mulai, $batas"
);

if (!$data) {
    die("Gagal mengambil data wali kelas: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Wali Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
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

        .header-box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .header-box h1 {
            font-size: 24px;
            margin: 0;
        }

        .header-box p {
            color: #777;
            margin: 8px 0 0;
        }

        .btn-tambah {
            background: #9dff00;
            border: 1px solid #9dff00;
            color: #222;
            font-weight: bold;
        }

        .btn-tambah:hover {
            background: #8be000;
            border-color: #8be000;
            color: #222;
        }

        @media(max-width: 700px) {

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
        ⚖ Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php" class="active">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>
    <a href="logout.php" class="logout">🚪 Logout</a>

</div>

<!-- KONTEN -->

<div class="content">

    <div class="header-box">

        <h1>Wali Kelas</h1>

        <p>Kelola guru yang menjadi wali kelas.</p>

    </div>

    <?php if ($error != '') { ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <h5 class="mb-0">Daftar Wali Kelas</h5>

                <button
                    type="button"
                    class="btn btn-tambah"
                    onclick="tampilkanForm()">

                    + Tambah Wali Kelas

                </button>

            </div>

            <!-- PENCARIAN -->

            <form method="GET" class="mb-3">

                <div class="row g-2">

                    <div class="col-md-5">

                        <input
                            type="text"
                            name="cari"
                            class="form-control"
                            placeholder="Cari guru, NIP, kelas, atau tahun ajaran..."
                            value="<?= htmlspecialchars($cari); ?>">

                    </div>

                    <div class="col-auto">

                        <button type="submit" class="btn btn-primary">
                            Cari
                        </button>

                        <a href="kelola_wali_kelas.php" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </div>

            </form>

            <!-- TABEL -->

            <div class="table-responsive">

                <table class="table table-bordered table-striped table-hover align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Guru</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = $mulai + 1;

                    if (mysqli_num_rows($data) > 0) {

                        while ($row = mysqli_fetch_assoc($data)) {

                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($row['nip'] ?? '-'); ?>
                            </td>

                            <td>
                                <b><?= htmlspecialchars($row['nama_guru']); ?></b>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nama_kelas']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nama_tahun']); ?>
                            </td>

                            <td>

                                <?php if ($row['aktif'] == 1) { ?>

                                    <span class="badge bg-success">
                                        Aktif
                                    </span>

                                <?php } else { ?>

                                    <span class="badge bg-danger">
                                        Tidak Aktif
                                    </span>

                                <?php } ?>

                            </td>

                            <td>

                                <a
                                    href="edit_wali_kelas.php?id=<?= $row['id']; ?>"
                                    class="btn btn-sm btn-tambah">

                                    Edit

                                </a>

                                <a
                                    href="kelola_wali_kelas.php?hapus=<?= $row['id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus wali kelas ini?')">

                                    Hapus

                                </a>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td colspan="7" class="text-center">
                                Data wali kelas tidak ditemukan.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

            <!-- PAGINATION -->

            <?php if ($total_halaman > 1) { ?>

                <nav>

                    <ul class="pagination justify-content-center mb-0">

                        <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                            <li class="page-item <?= ($halaman == $i) ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>">

                                    <?= $i; ?>

                                </a>

                            </li>

                        <?php } ?>

                    </ul>

                </nav>

            <?php } ?>

        </div>

    </div>

    <!-- FORM TAMBAH -->

    <div class="card shadow-sm mb-4" id="form-tambah" style="display:none;">

        <div class="card-body">

            <h5 class="mb-3">Tambah Wali Kelas</h5>

            <form method="POST">

                <div class="row g-3">

                    <!-- GURU -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Guru
                        </label>

                        <select
                            name="guru_id"
                            class="form-select"
                            required>

                            <option value="">
                                -- Pilih Guru --
                            </option>

                            <?php while ($g = mysqli_fetch_assoc($guru_data)) { ?>

                                <option value="<?= $g['id']; ?>">

                                    <?= htmlspecialchars($g['nip'] ?? '-'); ?>
                                    - <?= htmlspecialchars($g['nama']); ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- TAHUN AJARAN -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Tahun Ajaran
                        </label>

                        <select
                            name="tahun_ajaran_id"
                            id="tahun_ajaran_id"
                            class="form-select"
                            required
                            onchange="ambilKelas()">

                            <option value="">
                                -- Pilih Tahun Ajaran --
                            </option>

                            <?php while ($t = mysqli_fetch_assoc($tahun_data)) { ?>

                                <option value="<?= $t['id']; ?>">
                                    <?= htmlspecialchars($t['nama']); ?>
                                </option>

                            <?php } ?>

                        </select>

                        <?php if ($jumlah_tahun_aktif == 0) { ?>

                            <small class="text-danger">
                                Belum ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.
                            </small>

                        <?php } ?>

                    </div>

                    <!-- KELAS -->

                    <div class="col-md-12">

                        <label class="form-label">
                            Kelas
                        </label>

                        <select
                            name="kelas_id"
                            id="kelas_id"
                            class="form-select"
                            required
                            disabled>

                            <option value="">
                                -- Pilih tahun ajaran terlebih dahulu --
                            </option>

                        </select>

                        <small class="text-muted">
                            Kelas yang sudah memiliki wali kelas pada tahun ajaran terpilih tidak akan ditampilkan.
                        </small>

                    </div>

                </div>

                <div class="mt-4">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-primary"
                        <?= ($jumlah_tahun_aktif == 0) ? 'disabled' : ''; ?>>

                        Simpan

                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="sembunyikanForm()">

                        Batal

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

function tampilkanForm() {

    document.getElementById("form-tambah").style.display = "block";

    document.getElementById("form-tambah").scrollIntoView({
        behavior: "smooth"
    });

}

function sembunyikanForm() {

    document.getElementById("form-tambah").style.display = "none";

}

function ambilKelas() {

    const tahunId = document.getElementById("tahun_ajaran_id").value;
    const kelasSelect = document.getElementById("kelas_id");

    kelasSelect.innerHTML = '<option value="">Memuat data kelas...</option>';
    kelasSelect.disabled = true;

    if (tahunId === "") {

        kelasSelect.innerHTML = '<option value="">-- Pilih tahun ajaran terlebih dahulu --</option>';

        return;
    }

    fetch("kelola_wali_kelas.php?ajax=kelas&tahun_ajaran_id=" + encodeURIComponent(tahunId))

        .then(response => {

            if (!response.ok) {
                throw new Error("Gagal mengambil data kelas.");
            }

            return response.json();

        })

        .then(data => {

            kelasSelect.innerHTML = "";

            if (data.length === 0) {

                kelasSelect.innerHTML = '<option value="">Tidak ada kelas tersedia</option>';

                kelasSelect.disabled = true;

            } else {

                kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';

                data.forEach(kelas => {

                    const option = document.createElement("option");

                    option.value = kelas.id;
                    option.textContent = kelas.nama_kelas;

                    kelasSelect.appendChild(option);

                });

                kelasSelect.disabled = false;

            }

        })

        .catch(error => {

            kelasSelect.innerHTML = '<option value="">Gagal memuat kelas</option>';

            kelasSelect.disabled = true;

            alert(error.message);

        });

}

</script>

</body>
</html>
