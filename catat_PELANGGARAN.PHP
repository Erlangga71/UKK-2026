
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";
require_once "auth.php";

hanyaRole(['admin', 'guru', 'wali_kelas']);

$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? 'Pengguna';
$email = $_SESSION['email'] ?? '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = "";
$jenis_pesan = "";

$guru_id = null;
$kelas_id = 0;
$tahun_ajaran_id = 0;
$nama_kelas = "";

/* =========================
   TAHUN AJARAN AKTIF
========================= */

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM tahun_ajaran
     WHERE aktif = 1
     LIMIT 1"
);

$nama_tahun = "";

if ($query_tahun && mysqli_num_rows($query_tahun) > 0) {
    $data_tahun = mysqli_fetch_assoc($query_tahun);
    $tahun_ajaran_id = (int)$data_tahun['id'];
    $nama_tahun = $data_tahun['nama'];
}

/* =========================
   IDENTITAS GURU / WALI KELAS
========================= */

if ($role === 'guru' || $role === 'wali_kelas') {

    $stmt = $koneksi->prepare(
        "SELECT guru_id
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die("Query akun gagal: " . $koneksi->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $hasil = $stmt->get_result();

    if ($hasil && $hasil->num_rows > 0) {
        $data = $hasil->fetch_assoc();
        $guru_id = (int)($data['guru_id'] ?? 0);
    }

    $stmt->close();

    // Jika guru_id belum terhubung, cari berdasarkan email.
    if (!$guru_id) {

        $stmt = $koneksi->prepare(
            "SELECT id
             FROM guru
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            die("Query guru gagal: " . $koneksi->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $hasil = $stmt->get_result();

        if ($hasil && $hasil->num_rows > 0) {
            $data = $hasil->fetch_assoc();
            $guru_id = (int)$data['id'];
        }

        $stmt->close();
    }

    if (!$guru_id) {
        die("Akun belum terhubung dengan data guru. Hubungi administrator.");
    }
}

/* =========================
   IDENTITAS KELAS WALI KELAS
========================= */

if ($role === 'wali_kelas') {

    if ($tahun_ajaran_id <= 0) {
        die("Belum ada tahun ajaran aktif. Hubungi administrator.");
    }

    $stmt = $koneksi->prepare(
        "SELECT wk.kelas_id, k.nama_kelas
         FROM wali_kelas wk
         JOIN kelas k ON k.id = wk.kelas_id
         WHERE wk.guru_id = ?
           AND wk.tahun_ajaran_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die("Query kelas gagal: " . $koneksi->error);
    }

    $stmt->bind_param(
        "ii",
        $guru_id,
        $tahun_ajaran_id
    );

    $stmt->execute();

    $hasil = $stmt->get_result();

    if ($hasil && $hasil->num_rows > 0) {
        $data = $hasil->fetch_assoc();
        $kelas_id = (int)$data['kelas_id'];
        $nama_kelas = $data['nama_kelas'];
    }

    $stmt->close();

    if ($kelas_id <= 0) {
        die("Anda belum ditetapkan sebagai wali kelas pada tahun ajaran aktif.");
    }
}

/* =========================
   PROSES SIMPAN PELANGGARAN
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {

        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
        $jenis_pesan = "danger";

    } else {

        $siswa_id = (int)($_POST['siswa_id'] ?? 0);
        $jenis_id = (int)($_POST['jenis_id'] ?? 0);
        $tanggal = trim($_POST['tanggal'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($siswa_id <= 0 || $jenis_id <= 0 || $tanggal === '') {

            $pesan = "Siswa, jenis pelanggaran, dan tanggal wajib diisi.";
            $jenis_pesan = "danger";

        } else {

            $tanggal_obj = DateTime::createFromFormat('Y-m-d', $tanggal);

            if (
                !$tanggal_obj ||
                $tanggal_obj->format('Y-m-d') !== $tanggal
            ) {

                $pesan = "Format tanggal tidak valid.";
                $jenis_pesan = "danger";

            } else {

                /* CEK SISWA */

                if ($role === 'wali_kelas') {

                    $stmt = $koneksi->prepare(
                        "SELECT s.id
                         FROM siswa s
                         JOIN penempatan_siswa ps
                            ON ps.siswa_id = s.id
                         WHERE s.id = ?
                           AND s.aktif = 1
                           AND ps.kelas_id = ?
                           AND ps.tahun_ajaran_id = ?
                         LIMIT 1"
                    );

                    if (!$stmt) {
                        die("Query siswa gagal: " . $koneksi->error);
                    }

                    $stmt->bind_param(
                        "iii",
                        $siswa_id,
                        $kelas_id,
                        $tahun_ajaran_id
                    );

                } else {

                    $stmt = $koneksi->prepare(
                        "SELECT id
                         FROM siswa
                         WHERE id = ?
                           AND aktif = 1
                         LIMIT 1"
                    );

                    if (!$stmt) {
                        die("Query siswa gagal: " . $koneksi->error);
                    }

                    $stmt->bind_param("i", $siswa_id);
                }

                $stmt->execute();
                $cek_siswa = $stmt->get_result();
                $stmt->close();

                /* CEK JENIS PELANGGARAN */

                $stmt = $koneksi->prepare(
                    "SELECT id
                     FROM jenis_pelanggaran
                     WHERE id = ?
                     LIMIT 1"
                );

                if (!$stmt) {
                    die("Query jenis pelanggaran gagal: " . $koneksi->error);
                }

                $stmt->bind_param("i", $jenis_id);
                $stmt->execute();

                $cek_jenis = $stmt->get_result();

                $stmt->close();

                if ($cek_siswa->num_rows === 0) {

                    $pesan = "Siswa tidak ditemukan, tidak aktif, atau bukan siswa kelas Anda.";
                    $jenis_pesan = "danger";

                } elseif ($cek_jenis->num_rows === 0) {

                    $pesan = "Jenis pelanggaran tidak ditemukan.";
                    $jenis_pesan = "danger";

                } else {

                    $koneksi->begin_transaction();

                    try {

                        /* SIMPAN PELANGGARAN */

                        $stmt = $koneksi->prepare(
                            "INSERT INTO pelanggaran
                            (siswa_id, jenis_id, guru_id, tanggal, keterangan)
                            VALUES (?, ?, ?, ?, ?)"
                        );

                        if (!$stmt) {
                            throw new Exception(
                                "Query simpan pelanggaran gagal: " . $koneksi->error
                            );
                        }

                        $stmt->bind_param(
                            "iiiss",
                            $siswa_id,
                            $jenis_id,
                            $guru_id,
                            $tanggal,
                            $keterangan
                        );

                        if (!$stmt->execute()) {
                            throw new Exception(
                                "Gagal menyimpan pelanggaran: " . $stmt->error
                            );
                        }

                        $stmt->close();

                        $koneksi->commit();

                        $pesan = "Data pelanggaran berhasil disimpan.";
                        $jenis_pesan = "success";

                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    } catch (Exception $e) {

                        $koneksi->rollback();

                        $pesan = $e->getMessage();
                        $jenis_pesan = "danger";
                    }
                }
            }
        }
    }
}

/* =========================
   AMBIL DATA SISWA
========================= */

$daftar_siswa = [];

if ($role === 'wali_kelas') {

    $stmt_siswa = $koneksi->prepare(
        "SELECT DISTINCT s.id, s.nis, s.nama
         FROM siswa s
         JOIN penempatan_siswa ps
            ON ps.siswa_id = s.id
         WHERE s.aktif = 1
           AND ps.kelas_id = ?
           AND ps.tahun_ajaran_id = ?
         ORDER BY s.nama ASC"
    );

    if (!$stmt_siswa) {
        die("Gagal mengambil data siswa: " . $koneksi->error);
    }

    $stmt_siswa->bind_param(
        "ii",
        $kelas_id,
        $tahun_ajaran_id
    );

    $stmt_siswa->execute();

    $hasil_siswa = $stmt_siswa->get_result();

} else {

    $hasil_siswa = mysqli_query(
        $koneksi,
        "SELECT id, nis, nama
         FROM siswa
         WHERE aktif = 1
         ORDER BY nama ASC"
    );

    if (!$hasil_siswa) {
        die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
    }
}

// Simpan hasil query ke array agar bisa digunakan oleh JavaScript.
while ($siswa = $hasil_siswa->fetch_assoc()) {
    $daftar_siswa[] = [
        'id' => (int)$siswa['id'],
        'nis' => (string)$siswa['nis'],
        'nama' => (string)$siswa['nama']
    ];
}

if (isset($stmt_siswa)) {
    $stmt_siswa->close();
}

/* =========================
   JENIS PELANGGARAN
========================= */

$query_jenis = mysqli_query(
    $koneksi,
    "SELECT id, nama, poin
     FROM jenis_pelanggaran
     ORDER BY nama ASC"
);

if (!$query_jenis) {
    die("Gagal mengambil jenis pelanggaran: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catat Pelanggaran - Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            color: #333;
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
            z-index: 1000;
        }

        .sidebar-title {
            color: white;
            font-size: 19px;
            font-weight: bold;
            margin: 0 8px 25px;
            line-height: 1.4;
        }

        .sidebar .nav-link {
            color: white;
            font-size: 14px;
            padding: 11px 13px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #4f5965;
            color: white;
        }

        .sidebar .logout {
            background: #b42323;
            margin-top: 20px;
        }

        .sidebar .logout:hover {
            background: #941b1b;
        }

        .content {
            margin-left: 220px;
            padding: 30px;
            min-height: 100vh;
        }

        .header-box,
        .main-box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        .header-box {
            padding: 22px;
            margin-bottom: 20px;
        }

        .main-box {
            padding: 20px;
            width: 100%;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            font-size: 14px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        /* PENCARIAN SISWA */

        .hasil-pencarian {
            display: none;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #ced4da;
            border-radius: 5px;
            background: white;
            margin-top: 5px;
        }

        .hasil-pencarian button {
            display: block;
            width: 100%;
            text-align: left;
            background: white;
            border: none;
            border-bottom: 1px solid #eee;
            padding: 10px 12px;
            font-size: 14px;
            color: #333;
        }

        .hasil-pencarian button:hover {
            background: #f0f2f4;
        }

        .hasil-pencarian .tidak-ada {
            padding: 12px;
            color: #777;
            font-size: 13px;
        }

        .info-siswa {
            display: none;
            margin-top: 8px;
            padding: 9px 12px;
            background: #eaf5ed;
            border: 1px solid #c8e6cf;
            border-radius: 5px;
            color: #276738;
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

<?php
$menu_aktif = 'catat_pelanggaran';
require_once "sidebar.php";
?>

<div class="content">

    <div class="header-box">

        <h3 class="mb-1">Catat Pelanggaran Siswa</h3>

        <p class="text-secondary mb-2">
            Sistem Informasi Pelanggaran Siswa
        </p>

        <small>
            Login sebagai:
            <strong><?= htmlspecialchars($nama); ?></strong>
            (<?= htmlspecialchars($role); ?>)
        </small>

        <?php if ($role === 'wali_kelas') { ?>
            <br>
            <small>
                Kelas:
                <strong><?= htmlspecialchars($nama_kelas); ?></strong>
                | Tahun Ajaran:
                <strong><?= htmlspecialchars($nama_tahun); ?></strong>
            </small>
        <?php } ?>

    </div>

    <div class="main-box">

        <h5 class="mb-2">Form Catat Pelanggaran</h5>

        <p class="text-secondary small mb-4">
            Isi data pelanggaran siswa. Pemberian tindakan dilakukan melalui menu Tindakan.
        </p>

        <?php if ($pesan !== '') { ?>

            <div class="alert alert-<?= htmlspecialchars($jenis_pesan); ?> alert-dismissible fade show">

                <?= htmlspecialchars($pesan); ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php } ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
            >

            <div class="section-title">
                Data Pelanggaran
            </div>

            <!-- PENCARIAN SISWA -->

            <div class="mb-3">

                <label class="form-label">
                    Cari Siswa
                </label>

                <input
                    type="search"
                    id="cari_siswa"
                    class="form-control"
                    placeholder="Ketik nama siswa atau NIS..."
                    autocomplete="off"
                >

                <div class="form-text">
                    Cari berdasarkan nama atau NIS, lalu pilih siswa dari hasil pencarian.
                </div>

                <div id="hasil_pencarian" class="hasil-pencarian"></div>

                <input
                    type="hidden"
                    name="siswa_id"
                    id="siswa_id"
                    required
                >

                <div id="info_siswa" class="info-siswa"></div>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Jenis Pelanggaran
                </label>

                <select name="jenis_id" class="form-select" required>

                    <option value="">
                        -- Pilih Pelanggaran --
                    </option>

                    <?php while ($jenis = mysqli_fetch_assoc($query_jenis)) { ?>

                        <option value="<?= (int)$jenis['id']; ?>">

                            <?= htmlspecialchars($jenis['nama']); ?>
                            (<?= (int)$jenis['poin']; ?> poin)

                        </option>

                    <?php } ?>

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Tanggal Pelanggaran
                </label>

                <input
                    type="date"
                    name="tanggal"
                    class="form-control"
                    value="<?= date('Y-m-d'); ?>"
                    required
                >

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Keterangan Pelanggaran
                </label>

                <textarea
                    name="keterangan"
                    class="form-control"
                    rows="3"
                    placeholder="Masukkan keterangan pelanggaran..."
                ></textarea>

            </div>

            <button type="submit" class="btn btn-success">
                Simpan Pelanggaran
            </button>

            <a href="dashboard.php" class="btn btn-secondary">
                Kembali
            </a>

        </form>

    </div>

</div>

<script>
    // Data siswa dari database.
    const daftarSiswa = <?= json_encode(
        $daftar_siswa,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ); ?>;

    const inputCari = document.getElementById('cari_siswa');
    const inputSiswaId = document.getElementById('siswa_id');
    const hasilPencarian = document.getElementById('hasil_pencarian');
    const infoSiswa = document.getElementById('info_siswa');

    function tampilkanHasil(kataKunci = '') {

        const kata = kataKunci.trim().toLowerCase();

        const hasil = daftarSiswa.filter(function(siswa) {

            const nama = siswa.nama.toLowerCase();
            const nis = siswa.nis.toLowerCase();

            return nama.includes(kata) || nis.includes(kata);
        });

        hasilPencarian.innerHTML = '';
        hasilPencarian.style.display = 'block';

        if (hasil.length === 0) {

            const pesan = document.createElement('div');
            pesan.className = 'tidak-ada';
            pesan.textContent = 'Data siswa tidak ditemukan.';

            hasilPencarian.appendChild(pesan);
            return;
        }

        const maksimalHasil = 50;

        hasil.slice(0, maksimalHasil).forEach(function(siswa) {

            const tombol = document.createElement('button');
            tombol.type = 'button';

            tombol.textContent = siswa.nis + ' - ' + siswa.nama;

            tombol.addEventListener('click', function() {

                inputSiswaId.value = siswa.id;
                inputCari.value = siswa.nis + ' - ' + siswa.nama;

                infoSiswa.textContent =
                    'Siswa dipilih: ' + siswa.nama + ' (NIS: ' + siswa.nis + ')';

                infoSiswa.style.display = 'block';
                hasilPencarian.style.display = 'none';

            });

            hasilPencarian.appendChild(tombol);
        });

        if (hasil.length > maksimalHasil) {

            const info = document.createElement('div');
            info.className = 'tidak-ada';
            info.textContent =
                'Menampilkan 50 hasil pertama. Perjelas pencarian untuk hasil lainnya.';

            hasilPencarian.appendChild(info);
        }
    }

    // Tampilkan hasil ketika pengguna mengetik.
    inputCari.addEventListener('input', function() {

        inputSiswaId.value = '';
        infoSiswa.style.display = 'none';

        tampilkanHasil(this.value);
    });

    // Tampilkan daftar siswa ketika kolom pencarian diklik.
    inputCari.addEventListener('focus', function() {

        if (inputSiswaId.value === '') {
            tampilkanHasil(this.value);
        }
    });

    // Sembunyikan hasil ketika pengguna mengklik area lain.
    document.addEventListener('click', function(event) {

        if (
            !inputCari.contains(event.target) &&
            !hasilPencarian.contains(event.target)
        ) {
            hasilPencarian.style.display = 'none';
        }
    });

    // Pastikan siswa sudah dipilih sebelum form dikirim.
    document.querySelector('form').addEventListener('submit', function(event) {

        if (!inputSiswaId.value) {

            event.preventDefault();

            alert('Silakan cari dan pilih siswa terlebih dahulu.');

            inputCari.focus();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
