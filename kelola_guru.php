
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

$error = "";
$tampil_form = isset($_GET['tambah']);

/* =========================
   TAMBAH GURU
========================= */

if (isset($_POST['simpan'])) {

    $nip      = trim($_POST['nip'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $no_hp    = trim($_POST['no_hp'] ?? '');

    if ($nama == '' || $email == '' || $password == '') {

        $error = "Nama, Gmail dan password wajib diisi.";
        $tampil_form = true;

    } elseif ($nip != '' && (!ctype_digit($nip) || strlen($nip) > 18)) {

        $error = "NIP hanya boleh angka dan maksimal 18 digit.";
        $tampil_form = true;

    } else {

        // CEK NIP DUPLIKAT
        $nip_ada = false;

        if ($nip != '') {
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM guru WHERE nip = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "s", $nip);
            mysqli_stmt_execute($stmt);

            $cek_nip = mysqli_stmt_get_result($stmt);
            $nip_ada = mysqli_num_rows($cek_nip) > 0;

            mysqli_stmt_close($stmt);
        }

        if ($nip_ada) {

            $error = "NIP tersebut sudah terdaftar.";
            $tampil_form = true;

        } else {

            // CEK EMAIL / USERNAME
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM users
                 WHERE email = ? OR username = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "ss", $email, $email);
            mysqli_stmt_execute($stmt);

            $cek_user = mysqli_stmt_get_result($stmt);
            $user_ada = mysqli_num_rows($cek_user) > 0;

            mysqli_stmt_close($stmt);

            // CEK EMAIL GURU
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM guru WHERE email = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $cek_guru = mysqli_stmt_get_result($stmt);
            $guru_ada = mysqli_num_rows($cek_guru) > 0;

            mysqli_stmt_close($stmt);

            if ($user_ada || $guru_ada) {

                $error = "Gmail tersebut sudah digunakan.";
                $tampil_form = true;

            } else {

                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                mysqli_begin_transaction($koneksi);

                try {

                    // BUAT AKUN LOGIN GURU
                    $stmt_user = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO users
                        (nama, username, email, password, role, aktif)
                        VALUES (?, ?, ?, ?, 'guru', 1)"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_user,
                        "ssss",
                        $nama,
                        $email,
                        $email,
                        $password_hash
                    );

                    if (!mysqli_stmt_execute($stmt_user)) {
                        throw new Exception(mysqli_error($koneksi));
                    }

                    mysqli_stmt_close($stmt_user);

                    // SIMPAN DATA GURU
                    $nip_db = ($nip == '') ? null : $nip;
                    $no_hp_db = ($no_hp == '') ? null : $no_hp;

                    $stmt_guru = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO guru (nip, nama, email, no_hp)
                         VALUES (?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_guru,
                        "ssss",
                        $nip_db,
                        $nama,
                        $email,
                        $no_hp_db
                    );

                    if (!mysqli_stmt_execute($stmt_guru)) {
                        throw new Exception(mysqli_error($koneksi));
                    }

                    mysqli_stmt_close($stmt_guru);

                    mysqli_commit($koneksi);

                    header("Location: kelola_guru.php");
                    exit;

                } catch (Exception $e) {

                    mysqli_rollback($koneksi);

                    $error = "Gagal menyimpan data guru: " . $e->getMessage();
                    $tampil_form = true;
                }
            }
        }
    }
}


/* =========================
   HAPUS GURU
========================= */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT email FROM guru WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $hasil_guru = mysqli_stmt_get_result($stmt);
        $guru_hapus = mysqli_fetch_assoc($hasil_guru);

        mysqli_stmt_close($stmt);

        if ($guru_hapus) {

            // CEK DATA PELANGGARAN
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT COUNT(*) AS total
                 FROM pelanggaran
                 WHERE guru_id = ?"
            );

            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);

            $hasil_cek = mysqli_stmt_get_result($stmt);
            $jumlah = (int)mysqli_fetch_assoc($hasil_cek)['total'];

            mysqli_stmt_close($stmt);

            if ($jumlah > 0) {

                $error = "Guru tidak bisa dihapus karena sudah memiliki data pelanggaran.";

            } else {

                mysqli_begin_transaction($koneksi);

                try {

                    // HAPUS DATA GURU
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "DELETE FROM guru WHERE id = ?"
                    );

                    mysqli_stmt_bind_param($stmt, "i", $id);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_error($koneksi));
                    }

                    mysqli_stmt_close($stmt);

                    // HAPUS AKUN LOGIN GURU
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "DELETE FROM users
                         WHERE email = ? AND role = 'guru'"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "s",
                        $guru_hapus['email']
                    );

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_error($koneksi));
                    }

                    mysqli_stmt_close($stmt);

                    mysqli_commit($koneksi);

                    header("Location: kelola_guru.php");
                    exit;

                } catch (Exception $e) {

                    mysqli_rollback($koneksi);

                    $error = "Gagal menghapus guru: " . $e->getMessage();
                }
            }
        }
    }
}


/* =========================
   PENCARIAN
========================= */

$search = trim($_GET['cari'] ?? '');
$cari = "%$search%";


/* =========================
   PAGINATION
========================= */

$per_halaman = 10;

$halaman = max(1, (int)($_GET['halaman'] ?? 1));


/* =========================
   HITUNG DATA
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) AS total
         FROM guru
         WHERE nip LIKE ?
         OR nama LIKE ?
         OR email LIKE ?
         OR no_hp LIKE ?"
    );

    $stmt->bind_param("ssss", $cari, $cari, $cari, $cari);

} else {

    $stmt = $koneksi->prepare(
        "SELECT COUNT(*) AS total FROM guru"
    );
}

$stmt->execute();

$total_data = (int)$stmt->get_result()->fetch_assoc()['total'];

$stmt->close();

$total_halaman = max(1, (int)ceil($total_data / $per_halaman));

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
}

$mulai = ($halaman - 1) * $per_halaman;


/* =========================
   AMBIL DATA GURU
========================= */

if ($search != '') {

    $stmt = $koneksi->prepare(
        "SELECT id, nip, nama, email, no_hp
         FROM guru
         WHERE nip LIKE ?
         OR nama LIKE ?
         OR email LIKE ?
         OR no_hp LIKE ?
         ORDER BY id DESC
         LIMIT ?, ?"
    );

    $stmt->bind_param(
        "ssssii",
        $cari,
        $cari,
        $cari,
        $cari,
        $mulai,
        $per_halaman
    );

} else {

    $stmt = $koneksi->prepare(
        "SELECT id, nip, nama, email, no_hp
         FROM guru
         ORDER BY id DESC
         LIMIT ?, ?"
    );

    $stmt->bind_param("ii", $mulai, $per_halaman);
}

$stmt->execute();
$data = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Guru</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    margin: 0;
    background: #f4f6f8;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #212529;
}

/* SIDEBAR */

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

/* CONTENT */

.content {
    margin-left: 220px;
    padding: 30px;
}

/* PANEL */

.card {
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: none;
}

.card-body {
    padding: 20px;
}

/* BUTTON */

.btn-stabilo {
    background: #9dff00;
    color: #1a3300;
    font-weight: bold;
    border: none;
}

.btn-stabilo:hover {
    background: #86e000;
    color: #1a3300;
}

/* TABLE */

.table {
    font-size: 12px;
    margin-bottom: 10px;
}

.table th,
.table td {
    padding: 10px;
    vertical-align: middle;
}

/* PAGINATION */

.page-link {
    color: #687280;
}

.page-item.active .page-link {
    background: #687280;
    border-color: #687280;
    color: white;
}

/* RESPONSIVE */

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
        ⚖ Pelanggaran<br>Siswa
    </div>

    <a href="dashboard.php"> Dashboard</a>
    <a href="kelola_siswa.php">Data Siswa</a>
    <a href="kelola_guru.php" class="active">Data Guru</a>
    <a href="kelola_kelas.php">Data Kelas</a>
    <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
    <a href="kelola_wali_kelas.php">Wali Kelas</a>
    <a href="penempatan_siswa.php">Penempatan Siswa</a>
    <a href="kelola_pelanggaran_kategori.php">Kategori Pelanggaran</a>
    <a href="pelanggaran.php">Data Pelanggaran</a>
    <a href="tindakan.php">Data Tindakan</a>
    <a href="laporan.php">Laporan</a>
    <a href="about_me.php">ℹ About Me</a>

    <a href="logout.php" class="logout">Logout</a>

</div>


<!-- CONTENT -->

<div class="content">

    <!-- HEADER -->

    <div class="card mb-3">
        <div class="card-body">

            <h4 class="mb-1">Data Guru</h4>

            <p class="text-muted mb-0">
                Kelola data guru dan akun login guru.
            </p>

        </div>
    </div>


    <!-- PESAN ERROR -->

    <?php if ($error != '') { ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- FORM TAMBAH -->

    <?php if ($tampil_form) { ?>

        <div class="card mb-3">
            <div class="card-body">

                <h5 class="mb-3">Tambah Data Guru</h5>

                <form method="POST">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIP</label>

                            <input
                                type="text"
                                name="nip"
                                class="form-control"
                                placeholder="Masukkan NIP"
                                maxlength="18"
                                inputmode="numeric"
                                pattern="[0-9]{1,18}"
                                title="NIP hanya boleh angka dan maksimal 18 digit."
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Guru</label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                placeholder="Masukkan nama guru"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gmail</label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="contoh@gmail.com"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password Login</label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">No HP</label>

                            <input
                                type="text"
                                name="no_hp"
                                class="form-control"
                                placeholder="Masukkan nomor HP"
                            >
                        </div>

                    </div>

                    <div class="mt-3">

                        <button
                            type="submit"
                            name="simpan"
                            class="btn btn-stabilo btn-sm"
                        >
                            Simpan Guru
                        </button>

                        <a
                            href="kelola_guru.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Batal
                        </a>

                    </div>

                </form>

            </div>
        </div>

    <?php } ?>


    <!-- DAFTAR GURU -->

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Daftar Guru</h5>

                <?php if (!$tampil_form) { ?>

                    <a
                        href="kelola_guru.php?tambah=1"
                        class="btn btn-sm btn-stabilo"
                    >
                        + Tambah Guru
                    </a>

                <?php } ?>

            </div>


            <!-- PENCARIAN -->

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-5">

                    <input
                        type="text"
                        name="cari"
                        class="form-control form-control-sm"
                        placeholder="Cari NIP, nama, Gmail, atau No HP..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Cari
                    </button>

                    <?php if ($search != '') { ?>

                        <a
                            href="kelola_guru.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </div>

            </form>


            <!-- TABEL GURU -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                        <tr>
                            <th width="55">No</th>
                            <th>Nama Guru / NIP</th>
                            <th>Gmail</th>
                            <th>No HP</th>
                            <th width="150">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($data && $data->num_rows > 0) { ?>

                        <?php $no = $mulai + 1; ?>

                        <?php while ($row = $data->fetch_assoc()) { ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </strong>

                                    <div class="text-muted small">
                                        NIP:
                                        <?= htmlspecialchars($row['nip'] ?? '-'); ?>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['email'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['no_hp'] ?? '-'); ?>
                                </td>

                                <td>

                                    <a
                                        href="edit_guru.php?id=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-stabilo"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="kelola_guru.php?hapus=<?= (int)$row['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus guru dan akun login guru ini?')"
                                    >
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="5" class="text-center py-4">
                                Belum ada data guru.
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>


            <!-- INFORMASI DATA -->

            <div class="text-muted small">

                Menampilkan
                <?= $total_data > 0 ? $mulai + 1 : 0; ?>
                -
                <?= min($mulai + $per_halaman, $total_data); ?>
                dari
                <?= $total_data; ?>
                data guru.

            </div>


            <!-- PAGINATION -->

            <?php if ($total_halaman > 1) { ?>

                <nav class="mt-3">

                    <ul class="pagination pagination-sm mb-0">

                        <?php if ($halaman > 1) { ?>

                            <li class="page-item">
                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman - 1; ?>&cari=<?= urlencode($search); ?>"
                                >
                                    &laquo;
                                </a>
                            </li>

                        <?php } ?>


                        <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?halaman=<?= $i; ?>&cari=<?= urlencode($search); ?>"
                                >
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php } ?>


                        <?php if ($halaman < $total_halaman) { ?>

                            <li class="page-item">
                                <a
                                    class="page-link"
                                    href="?halaman=<?= $halaman + 1; ?>&cari=<?= urlencode($search); ?>"
                                >
                                    &raquo;
                                </a>
                            </li>

                        <?php } ?>

                    </ul>

                </nav>

            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>

<?php
$stmt->close();
?>
