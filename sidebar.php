
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "database.php";

$role = $_SESSION['role'] ?? '';
$email = $_SESSION['email'] ?? '';
$menu_aktif = $menu_aktif ?? '';

/* =====================================
   CEK STATUS GURU WALI KELAS
===================================== */

$is_wali_kelas = false;
$guru_id = 0;
$tahun_ajaran_id = 0;

if ($role === 'guru' && $email !== '') {

    // Cari ID guru berdasarkan email akun login
    $stmt_guru = mysqli_prepare(
        $koneksi,
        "SELECT id FROM guru WHERE email = ? LIMIT 1"
    );

    if ($stmt_guru) {

        mysqli_stmt_bind_param($stmt_guru, "s", $email);
        mysqli_stmt_execute($stmt_guru);

        $hasil_guru = mysqli_stmt_get_result($stmt_guru);

        if ($hasil_guru && mysqli_num_rows($hasil_guru) === 1) {

            $data_guru = mysqli_fetch_assoc($hasil_guru);
            $guru_id = (int) $data_guru['id'];

        }

        mysqli_stmt_close($stmt_guru);
    }

    // Cari tahun ajaran yang sedang aktif
    $query_tahun = mysqli_query(
        $koneksi,
        "SELECT id FROM tahun_ajaran
         WHERE aktif = 1
         LIMIT 1"
    );

    if ($query_tahun && mysqli_num_rows($query_tahun) > 0) {

        $data_tahun = mysqli_fetch_assoc($query_tahun);
        $tahun_ajaran_id = (int) $data_tahun['id'];

    }

    // Periksa apakah guru mendapat tugas sebagai wali kelas
    if ($guru_id > 0 && $tahun_ajaran_id > 0) {

        $stmt_wali = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM wali_kelas
             WHERE guru_id = ?
             AND tahun_ajaran_id = ?
             LIMIT 1"
        );

        if ($stmt_wali) {

            mysqli_stmt_bind_param(
                $stmt_wali,
                "ii",
                $guru_id,
                $tahun_ajaran_id
            );

            mysqli_stmt_execute($stmt_wali);

            $hasil_wali = mysqli_stmt_get_result($stmt_wali);

            if ($hasil_wali && mysqli_num_rows($hasil_wali) > 0) {
                $is_wali_kelas = true;
            }

            mysqli_stmt_close($stmt_wali);
        }
    }
}

/* =====================================
   FUNGSI MENU AKTIF
===================================== */

if (!function_exists('menuAktif')) {

    function menuAktif($nama, $menu_aktif) {
        return $nama === $menu_aktif ? 'active' : '';
    }

}

?>

<div class="sidebar">

    <div class="sidebar-title">
        Pelanggaran<br>Siswa
    </div>

    <nav class="nav flex-column">

        <!-- DASHBOARD -->

        <a href="dashboard.php"
           class="nav-link <?= menuAktif('dashboard', $menu_aktif); ?>">
            Dashboard
        </a>

        <?php if ($role === 'admin'): ?>

            <!-- MENU ADMIN -->

            <a href="kelola_siswa.php"
               class="nav-link <?= menuAktif('siswa', $menu_aktif); ?>">
                Data Siswa
            </a>

            <a href="kelola_guru.php"
               class="nav-link <?= menuAktif('guru', $menu_aktif); ?>">
                Data Guru
            </a>

            <a href="kelola_kelas.php"
               class="nav-link <?= menuAktif('kelas', $menu_aktif); ?>">
                Data Kelas
            </a>

            <a href="kelola_tahun_ajaran.php"
               class="nav-link <?= menuAktif('tahun_ajaran', $menu_aktif); ?>">
                Tahun Ajaran
            </a>

            <a href="kelola_wali_kelas.php"
               class="nav-link <?= menuAktif('wali_kelas', $menu_aktif); ?>">
                Wali Kelas
            </a>

            <a href="penempatan_siswa.php"
               class="nav-link <?= menuAktif('penempatan_siswa', $menu_aktif); ?>">
                Penempatan Siswa
            </a>

            <a href="kelola_pelanggaran_kategori.php"
               class="nav-link <?= menuAktif('kategori', $menu_aktif); ?>">
                Kategori Pelanggaran
            </a>

            <a href="pelanggaran.php"
               class="nav-link <?= menuAktif('pelanggaran', $menu_aktif); ?>">
                Data Pelanggaran
            </a>

            <a href="laporan.php"
               class="nav-link <?= menuAktif('laporan', $menu_aktif); ?>">
                Laporan
            </a>

            <a href="riwayat.php"
               class="nav-link <?= menuAktif('riwayat', $menu_aktif); ?>">
                Riwayat
            </a>

            <a href="rekap_poin.php"
               class="nav-link <?= menuAktif('rekap_poin', $menu_aktif); ?>">
                Rekap Poin
            </a>

            <a href="about_me.php"
               class="nav-link <?= menuAktif('about_me', $menu_aktif); ?>">
                About Me
            </a>

        <?php elseif ($role === 'guru'): ?>

            <!-- MENU GURU -->

            <a href="catat_pelanggaran.php"
               class="nav-link <?= menuAktif('catat_pelanggaran', $menu_aktif); ?>">
                Catat Pelanggaran
            </a>

            <a href="tindakan.php"
               class="nav-link <?= menuAktif('tindakan', $menu_aktif); ?>">
                Tindakan
            </a>

            <a href="riwayat.php"
               class="nav-link <?= menuAktif('riwayat', $menu_aktif); ?>">
                Riwayat
            </a>

            <a href="rekap_poin.php"
               class="nav-link <?= menuAktif('rekap_poin', $menu_aktif); ?>">
                Rekap Poin
            </a>

            <!-- LAPORAN KHUSUS WALI KELAS -->

            <?php if ($is_wali_kelas): ?>

                <a href="laporan.php"
                   class="nav-link <?= menuAktif('laporan', $menu_aktif); ?>">
                    Laporan
                </a>

            <?php endif; ?>

        <?php endif; ?>

        <!-- LOGOUT -->

        <a href="logout.php" class="nav-link logout">
            Logout
        </a>

    </nav>

</div>
