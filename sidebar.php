
<?php
$role = $_SESSION['role'] ?? '';
$menu_aktif = $menu_aktif ?? '';

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

        <a href="dashboard.php"
           class="nav-link <?= menuAktif('dashboard', $menu_aktif); ?>">
            Dashboard
        </a>

        <?php if ($role === 'admin'): ?>

            <a href="kelola_siswa.php" class="nav-link <?= menuAktif('siswa', $menu_aktif); ?>">Data Siswa</a>

            <a href="kelola_guru.php" class="nav-link <?= menuAktif('guru', $menu_aktif); ?>">Data Guru</a>

            <a href="kelola_kelas.php" class="nav-link <?= menuAktif('kelas', $menu_aktif); ?>">Data Kelas</a>

            <a href="kelola_tahun_ajaran.php" class="nav-link <?= menuAktif('tahun_ajaran', $menu_aktif); ?>">Tahun Ajaran</a>

            <a href="kelola_wali_kelas.php" class="nav-link <?= menuAktif('wali_kelas', $menu_aktif); ?>">Wali Kelas</a>

            <a href="penempatan_siswa.php" class="nav-link <?= menuAktif('penempatan_siswa', $menu_aktif); ?>">Penempatan Siswa</a>

            <a href="kelola_pelanggaran_kategori.php" class="nav-link <?= menuAktif('kategori', $menu_aktif); ?>">Kategori Pelanggaran</a>

            <a href="pelanggaran.php" class="nav-link <?= menuAktif('pelanggaran', $menu_aktif); ?>">Data Pelanggaran</a>

            <a href="laporan.php" class="nav-link <?= menuAktif('laporan', $menu_aktif); ?>">Laporan</a>

            <a href="riwayat.php" class="nav-link <?= menuAktif('riwayat', $menu_aktif); ?>">Riwayat</a>

            <a href="rekap_poin.php" class="nav-link <?= menuAktif('rekap_poin', $menu_aktif); ?>">Rekap Poin</a>

            <a href="about_me.php" class="nav-link <?= menuAktif('about_me', $menu_aktif); ?>">About Me</a>

        <?php elseif ($role === 'guru'): ?>

            <a href="catat_pelanggaran.php" class="nav-link <?= menuAktif('catat_pelanggaran', $menu_aktif); ?>">Catat Pelanggaran</a>

            <a href="tindakan.php" class="nav-link <?= menuAktif('tindakan', $menu_aktif); ?>">Tindakan</a>

            <a href="laporan.php" class="nav-link <?= menuAktif('laporan', $menu_aktif); ?>">Laporan</a>

            <a href="riwayat.php" class="nav-link <?= menuAktif('riwayat', $menu_aktif); ?>">Riwayat</a>

            <a href="rekap_poin.php" class="nav-link <?= menuAktif('rekap_poin', $menu_aktif); ?>">Rekap Poin</a>

        <?php elseif ($role === 'wali_kelas'): ?>

            <a href="catat_pelanggaran.php" class="nav-link <?= menuAktif('catat_pelanggaran', $menu_aktif); ?>">Catat Pelanggaran</a>

            <a href="tindakan.php" class="nav-link <?= menuAktif('tindakan', $menu_aktif); ?>">Tindakan</a>

            <a href="riwayat.php" class="nav-link <?= menuAktif('riwayat', $menu_aktif); ?>">Riwayat</a>

            <a href="rekap_poin.php" class="nav-link <?= menuAktif('rekap_poin', $menu_aktif); ?>">Rekap Poin</a>

            <a href="laporan.php" class="nav-link <?= menuAktif('laporan', $menu_aktif); ?>">Laporan</a>

        <?php endif; ?>

        <a href="logout.php" class="nav-link logout">
            Logout
        </a>

    </nav>

</div>
