
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
$pesan = '';

$pelanggaran_id = filter_input(
    INPUT_GET,
    'pelanggaran_id',
    FILTER_VALIDATE_INT
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pelanggaran_id = filter_input(
        INPUT_POST,
        'pelanggaran_id',
        FILTER_VALIDATE_INT
    );
}

if (!$pelanggaran_id || $pelanggaran_id < 1) {
    die("ID pelanggaran tidak valid. Silakan kembali ke Tindakan.");
}

// =====================================
// AMBIL PETUGAS BERDASARKAN AKUN LOGIN
// =====================================
$email_login = $_SESSION['email'] ?? '';
$id_login = (int)($_SESSION['id'] ?? 0);

if ($email_login === '' || $id_login < 1) {
    die("Data akun login tidak lengkap. Silakan login kembali.");
}

$stmt_akun = mysqli_prepare($koneksi, "
    SELECT id, nama, email, role
    FROM users
    WHERE id = ?
      AND email = ?
      AND role = ?
      AND aktif = 1
    LIMIT 1
");

if (!$stmt_akun) {
    die("Gagal menyiapkan data petugas: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param(
    $stmt_akun,
    "iss",
    $id_login,
    $email_login,
    $role
);

mysqli_stmt_execute($stmt_akun);

$hasil_akun = mysqli_stmt_get_result($stmt_akun);
$data_petugas = mysqli_fetch_assoc($hasil_akun);

mysqli_stmt_close($stmt_akun);

if (!$data_petugas) {
    die("Akun petugas tidak ditemukan atau tidak aktif. Silakan login kembali.");
}

$petugas_id = (int)$data_petugas['id'];

// =====================================
// FUNGSI MENGECEK KELAS WALI KELAS
// =====================================
function ambilDataWali($koneksi)
{
    $email = $_SESSION['email'] ?? '';

    if ($email === '') {
        return false;
    }

    $stmt = mysqli_prepare($koneksi, "
        SELECT
            wk.kelas_id,
            ta.id AS tahun_ajaran_id
        FROM users u
        LEFT JOIN guru g
            ON g.id = u.guru_id
            OR (u.guru_id IS NULL AND g.email = u.email)
        INNER JOIN wali_kelas wk
            ON wk.guru_id = COALESCE(u.guru_id, g.id)
        INNER JOIN tahun_ajaran ta
            ON wk.tahun_ajaran_id = ta.id
        WHERE u.email = ?
          AND u.role = 'wali_kelas'
          AND u.aktif = 1
          AND ta.aktif = 1
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    return $data ?: false;
}

// =====================================
// FUNGSI MENGAMBIL DATA PELANGGARAN
// =====================================
function ambilPelanggaran(
    $koneksi,
    $pelanggaran_id,
    $role,
    $kelas_id = 0,
    $tahun_id = 0
) {
    $sql = "
        SELECT
            p.id,
            p.tanggal,
            p.status,
            s.nama AS nama_siswa,
            s.nis,
            jp.nama AS nama_pelanggaran,
            jp.poin
        FROM pelanggaran p
        INNER JOIN siswa s
            ON p.siswa_id = s.id
        INNER JOIN jenis_pelanggaran jp
            ON p.jenis_id = jp.id
        WHERE p.id = ?
          AND p.status = 'tercatat'
    ";

    if ($role === 'wali_kelas') {
        $sql .= "
            AND EXISTS (
                SELECT 1
                FROM penempatan_siswa ps
                WHERE ps.siswa_id = p.siswa_id
                  AND ps.kelas_id = ?
                  AND ps.tahun_ajaran_id = ?
            )
        ";
    }

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        return false;
    }

    if ($role === 'wali_kelas') {
        mysqli_stmt_bind_param(
            $stmt,
            "iii",
            $pelanggaran_id,
            $kelas_id,
            $tahun_id
        );
    } else {
        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $pelanggaran_id
        );
    }

    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    return $data ?: false;
}

// =====================================
// BATAS AKSES WALI KELAS
// =====================================
$kelas_wali_id = 0;
$tahun_aktif_id = 0;

if ($role === 'wali_kelas') {
    $data_wali = ambilDataWali($koneksi);

    if (!$data_wali) {
        die("Penempatan Wali Kelas aktif tidak ditemukan.");
    }

    $kelas_wali_id = (int)$data_wali['kelas_id'];
    $tahun_aktif_id = (int)$data_wali['tahun_ajaran_id'];
}

// =====================================
// AMBIL DATA PELANGGARAN
// =====================================
$data_pelanggaran = ambilPelanggaran(
    $koneksi,
    $pelanggaran_id,
    $role,
    $kelas_wali_id,
    $tahun_aktif_id
);

if (!$data_pelanggaran) {
    die("Pelanggaran tidak ditemukan, sudah diproses, atau bukan kewenangan Anda.");
}

// =====================================
// PROSES SIMPAN TINDAKAN
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tanggal = trim($_POST['tanggal'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($tanggal === '' || $tindakan === '') {

        $pesan = "Tanggal dan tindakan wajib diisi.";

    } else {

        $tanggal_obj = DateTime::createFromFormat('Y-m-d', $tanggal);

        if (!$tanggal_obj || $tanggal_obj->format('Y-m-d') !== $tanggal) {

            $pesan = "Format tanggal tidak valid.";

        } else {

            mysqli_begin_transaction($koneksi);

            try {

                // Cek ulang akun petugas yang sedang login.
                $stmt_validasi = mysqli_prepare($koneksi, "
                    SELECT id
                    FROM users
                    WHERE id = ?
                      AND email = ?
                      AND role = ?
                      AND aktif = 1
                    LIMIT 1
                ");

                mysqli_stmt_bind_param(
                    $stmt_validasi,
                    "iss",
                    $petugas_id,
                    $email_login,
                    $role
                );

                mysqli_stmt_execute($stmt_validasi);

                $hasil_validasi = mysqli_stmt_get_result($stmt_validasi);
                $akun_valid = mysqli_fetch_assoc($hasil_validasi);

                mysqli_stmt_close($stmt_validasi);

                if (!$akun_valid) {
                    throw new Exception(
                        "Akun petugas tidak valid atau sudah tidak aktif."
                    );
                }

                // Cek ulang kewenangan Wali Kelas.
                if ($role === 'wali_kelas') {

                    $data_wali = ambilDataWali($koneksi);

                    if (!$data_wali) {
                        throw new Exception(
                            "Penempatan Wali Kelas tidak ditemukan."
                        );
                    }

                    $kelas_wali_id = (int)$data_wali['kelas_id'];
                    $tahun_aktif_id = (int)$data_wali['tahun_ajaran_id'];
                }

                // Pastikan pelanggaran masih boleh diproses.
                $data_cek = ambilPelanggaran(
                    $koneksi,
                    $pelanggaran_id,
                    $role,
                    $kelas_wali_id,
                    $tahun_aktif_id
                );

                if (!$data_cek) {
                    throw new Exception(
                        "Pelanggaran sudah diproses atau bukan kewenangan Anda."
                    );
                }

                // Kunci data pelanggaran.
                $stmt_kunci = mysqli_prepare($koneksi, "
                    SELECT id
                    FROM pelanggaran
                    WHERE id = ?
                      AND status = 'tercatat'
                    FOR UPDATE
                ");

                mysqli_stmt_bind_param(
                    $stmt_kunci,
                    "i",
                    $pelanggaran_id
                );

                mysqli_stmt_execute($stmt_kunci);

                $hasil_kunci = mysqli_stmt_get_result($stmt_kunci);
                $data_kunci = mysqli_fetch_assoc($hasil_kunci);

                mysqli_stmt_close($stmt_kunci);

                if (!$data_kunci) {
                    throw new Exception(
                        "Pelanggaran sudah diproses."
                    );
                }

                // Cegah tindakan ganda.
                $stmt_duplikat = mysqli_prepare($koneksi, "
                    SELECT pelanggaran_id
                    FROM tindakan
                    WHERE pelanggaran_id = ?
                    LIMIT 1
                ");

                mysqli_stmt_bind_param(
                    $stmt_duplikat,
                    "i",
                    $pelanggaran_id
                );

                mysqli_stmt_execute($stmt_duplikat);

                $hasil_duplikat = mysqli_stmt_get_result($stmt_duplikat);
                $duplikat = mysqli_fetch_assoc($hasil_duplikat);

                mysqli_stmt_close($stmt_duplikat);

                if ($duplikat) {
                    throw new Exception(
                        "Tindakan untuk pelanggaran ini sudah pernah disimpan."
                    );
                }

                // Simpan tindakan dengan petugas otomatis dari akun login.
                $stmt_simpan = mysqli_prepare($koneksi, "
                    INSERT INTO tindakan
                    (
                        pelanggaran_id,
                        tanggal,
                        tindakan,
                        petugas_id,
                        catatan
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                mysqli_stmt_bind_param(
                    $stmt_simpan,
                    "issis",
                    $pelanggaran_id,
                    $tanggal,
                    $tindakan,
                    $petugas_id,
                    $catatan
                );

                if (!mysqli_stmt_execute($stmt_simpan)) {
                    throw new Exception(
                        mysqli_stmt_error($stmt_simpan)
                    );
                }

                mysqli_stmt_close($stmt_simpan);

                // Perbarui status pelanggaran.
                $stmt_update = mysqli_prepare($koneksi, "
                    UPDATE pelanggaran
                    SET status = 'ditindaklanjuti'
                    WHERE id = ?
                      AND status = 'tercatat'
                ");

                mysqli_stmt_bind_param(
                    $stmt_update,
                    "i",
                    $pelanggaran_id
                );

                if (
                    !mysqli_stmt_execute($stmt_update) ||
                    mysqli_stmt_affected_rows($stmt_update) !== 1
                ) {
                    throw new Exception(
                        "Status pelanggaran gagal diperbarui."
                    );
                }

                mysqli_stmt_close($stmt_update);

                mysqli_commit($koneksi);

                header("Location: tindakan.php?berhasil=1");
                exit;

            } catch (Throwable $e) {

                mysqli_rollback($koneksi);

                $pesan = "Gagal menyimpan tindakan: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Proses Tindakan</title>

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

.header {
    background: #687280;
    color: white;
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h2 {
    margin: 0;
    font-size: 20px;
}

.kembali {
    background: #56606d;
    color: white;
    text-decoration: none;
    padding: 9px 14px;
    border-radius: 5px;
}

.container {
    width: 90%;
    max-width: 800px;
    margin: 30px auto;
}

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 25px;
}

.box h1 {
    margin-top: 0;
    font-size: 23px;
}

.info {
    background: #f4f6f8;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
    line-height: 1.8;
}

.pesan {
    background: #fff3cd;
    border: 1px solid #ffe69c;
    color: #664d03;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input,
textarea {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    font-size: 14px;
}

textarea {
    resize: vertical;
}

.petugas-info {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: #f4f6f8;
    line-height: 1.7;
}

.btn {
    background: #687280;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.btn:hover {
    background: #56606d;
}
</style>
</head>

<body>

<div class="header">
    <h2>PELANGGARAN SISWA</h2>

    <a href="tindakan.php" class="kembali">
        ← Kembali
    </a>
</div>

<div class="container">

    <div class="box">

        <h1>Proses Tindakan</h1>

        <div class="info">
            <strong>Nama Siswa:</strong>
            <?= htmlspecialchars($data_pelanggaran['nama_siswa']); ?><br>

            <strong>NIS:</strong>
            <?= htmlspecialchars($data_pelanggaran['nis']); ?><br>

            <strong>Jenis Pelanggaran:</strong>
            <?= htmlspecialchars($data_pelanggaran['nama_pelanggaran']); ?><br>

            <strong>Poin:</strong>
            <?= (int)$data_pelanggaran['poin']; ?><br>

            <strong>Tanggal Pelanggaran:</strong>
            <?= htmlspecialchars($data_pelanggaran['tanggal']); ?>
        </div>

        <?php if ($pesan !== '') { ?>
            <div class="pesan">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php } ?>

        <form method="POST">

            <input
                type="hidden"
                name="pelanggaran_id"
                value="<?= (int)$pelanggaran_id; ?>"
            >

            <div class="form-group">
                <label>Tanggal Tindakan</label>

                <input
                    type="date"
                    name="tanggal"
                    value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Tindakan</label>

                <textarea
                    name="tindakan"
                    rows="4"
                    placeholder="Contoh: Teguran, pembinaan, pemanggilan orang tua, dll."
                    required
                ><?= htmlspecialchars($_POST['tindakan'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Petugas</label>

                <div class="petugas-info">
                    <strong><?= htmlspecialchars($data_petugas['nama']); ?></strong><br>
                    <?= htmlspecialchars($data_petugas['email']); ?><br>
                    <?= htmlspecialchars($data_petugas['role']); ?>
                </div>
            </div>

            <div class="form-group">
                <label>Catatan</label>

                <textarea
                    name="catatan"
                    rows="4"
                    placeholder="Catatan tambahan"
                ><?= htmlspecialchars($_POST['catatan'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn">
                Simpan Tindakan
            </button>

        </form>

    </div>

</div>

</body>
</html>
