
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "cek_role.php";
wajib_role(['admin', 'guru']);

require_once "database.php";

$role = $_SESSION['role'] ?? '';

/*
|--------------------------------------------------------------------------
| AMBIL DATA LAPORAN
|--------------------------------------------------------------------------
| Mengambil satu riwayat penempatan kelas terbaru untuk setiap siswa.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        s.nama AS nama_siswa,
        k.nama_kelas,
        jp.nama AS jenis_pelanggaran,
        jp.poin,
        p.tanggal,
        p.status

    FROM pelanggaran p

    JOIN siswa s
        ON p.siswa_id = s.id

    LEFT JOIN penempatan_siswa ps
        ON ps.siswa_id = s.id
        AND ps.id = (
            SELECT ps2.id
            FROM penempatan_siswa ps2
            WHERE ps2.siswa_id = s.id
            ORDER BY ps2.tahun_ajaran_id DESC, ps2.id DESC
            LIMIT 1
        )

    LEFT JOIN kelas k
        ON ps.kelas_id = k.id

    JOIN jenis_pelanggaran jp
        ON p.jenis_id = jp.id

    ORDER BY p.tanggal DESC, p.id DESC
";

$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Laporan Pelanggaran Siswa</title>

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

.container {
    margin-left: 220px;
    padding: 30px;
}

.box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 7px;
    padding: 25px;
}

/* JUDUL LAPORAN */

.judul {
    text-align: center;
    margin-bottom: 25px;
}

.judul h1 {
    margin: 0;
    font-size: 22px;
}

.judul h2 {
    margin: 8px 0;
    font-size: 18px;
}

.judul p {
    margin: 5px 0;
    color: #666;
}

/* BAGIAN ATAS */

.atas {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.atas h2 {
    margin: 0;
    font-size: 19px;
}

/* TOMBOL */

.btn {
    display: inline-block;
    background: #687280;
    color: white;
    text-decoration: none;
    border: none;
    padding: 9px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}

.btn:hover {
    background: #56606d;
}

/* TABEL */

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

.kosong {
    text-align: center;
    padding: 25px;
    color: #777;
}

/* TANDA TANGAN */

.ttd {
    margin-top: 60px;
    text-align: right;
    padding-right: 60px;
}

/* TAMPILAN HP */

@media (max-width: 700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .container {
        margin-left: 0;
        padding: 15px;
    }

    .atas {
        display: block;
    }

    .atas .btn {
        margin-top: 10px;
    }

    .ttd {
        padding-right: 10px;
    }

}

/* PENGATURAN CETAK */

@media print {

    @page {
        size: landscape;
        margin: 15mm;
    }

    body {
        background: white;
        color: black;
    }

    .sidebar {
        display: none !important;
    }

    .container {
        margin-left: 0;
        padding: 0;
        width: 100%;
    }

    .box {
        border: none;
        padding: 0;
    }

    .btn,
    .no-print {
        display: none !important;
    }

    .judul {
        margin-bottom: 25px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th {
        background: #ddd !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    th,
    td {
        border: 1px solid #333;
        padding: 8px;
        font-size: 11px;
    }

    tr {
        page-break-inside: avoid;
    }

    .ttd {
        page-break-inside: avoid;
        margin-top: 45px;
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

        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="kelola_siswa.php">Data Siswa</a>
        <a href="kelola_guru.php">Data Guru</a>
        <a href="kelola_kelas.php">Data Kelas</a>
        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="penempatan_siswa.php">Penempatan Siswa</a>
        <a href="kelola_pelanggaran_kategori.php">⚠ Kategori Pelanggaran</a>
        <a href="pelanggaran.php">Data Pelanggaran</a>
        <a href="tindakan.php">Data Tindakan</a>
        <a href="laporan.php" class="active">Laporan</a>
        <a href="about_me.php">ℹ About Me</a>

    <?php } else { ?>

        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="catat_pelanggaran.php">Catat Pelanggaran</a>
        <a href="tindakan.php">Tindakan</a>
        <a href="laporan.php" class="active">Laporan</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="rekap_poin.php">Rekap Poin</a>

    <?php } ?>

    <a href="logout.php" class="logout">🚪 Logout</a>

</div>


<!-- CONTENT -->

<div class="container">

    <div class="box">

        <!-- JUDUL LAPORAN -->

        <div class="judul">

            <h1>LAPORAN PELANGGARAN SISWA</h1>

            <h2>SISTEM INFORMASI PELANGGARAN SISWA</h2>

            <p>SMK</p>

            <p>
                Tanggal Cetak:
                <?= date('d-m-Y'); ?>
            </p>

        </div>


        <!-- TOMBOL -->

        <div class="atas">

            <h2>Data Pelanggaran</h2>

            <button onclick="window.print()" class="btn">
                🖨 Cetak Laporan
            </button>

        </div>


        <!-- TABEL -->

        <div class="table-container">

            <table>

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php

                $no = 1;

                if (mysqli_num_rows($query) > 0) {

                    while ($data = mysqli_fetch_assoc($query)) {

                ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <?= htmlspecialchars($data['nama_siswa'] ?? '-'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['jenis_pelanggaran'] ?? '-'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars((string)($data['poin'] ?? '0')); ?>
                        </td>

                        <td>
                            <?= !empty($data['tanggal'])
                                ? date('d-m-Y', strtotime($data['tanggal']))
                                : '-'; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['status'] ?? '-'); ?>
                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>
                        <td colspan="7" class="kosong">
                            Belum ada data pelanggaran.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>


        <!-- TANDA TANGAN -->

        <div class="ttd">

            <div>Mengetahui,</div>
            <div>Kepala Sekolah</div>

            <br><br><br>

            <strong>Drs. H. Wawan Kurniawan, M.Pd.</strong><br>
            <strong>NIP. 19650101 199003 1 005</strong>

        </div>

    </div>

</div>

</body>
</html>
