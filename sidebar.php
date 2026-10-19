<?php

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = '';
}

$role = $_SESSION['role'];

?>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-title">
         Pelanggaran<br>
        Siswa
    </div>


    <!-- DASHBOARD -->
    <a href="dashboard.php"
       class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
         Dashboard
    </a>


    <?php if ($role == 'admin') { ?>

        <!-- DATA SISWA -->
        <a href="kelola_siswa.php"
           class="<?= ($current_page == 'kelola_siswa.php') ? 'active' : '' ?>">
             Data Siswa
        </a>


        <!-- DATA GURU -->
        <a href="kelola_guru.php"
           class="<?= ($current_page == 'kelola_guru.php') ? 'active' : '' ?>">
             Data Guru
        </a>


        <!-- DATA KELAS -->
        <a href="kelola_kelas.php"
           class="<?= ($current_page == 'kelola_kelas.php') ? 'active' : '' ?>">
             Data Kelas
        </a>


        <!-- TAHUN AJARAN -->
        <a href="kelola_tahun_ajaran.php"
           class="<?= ($current_page == 'kelola_tahun_ajaran.php') ? 'active' : '' ?>">
             Tahun Ajaran
        </a>


        <!-- WALI KELAS -->
        <!-- FILE YANG DIPAKAI: kelola_wali_kelas.php -->
        <a href="kelola_wali_kelas.php"
           class="<?= ($current_page == 'kelola_wali_kelas.php') ? 'active' : '' ?>">
             Wali Kelas
        </a>


        <!-- PENEMPATAN SISWA -->
        <a href="penempatan_siswa.php"
           class="<?= ($current_page == 'penempatan_siswa.php') ? 'active' : '' ?>">
             Penempatan Siswa
        </a>


        <!-- KATEGORI PELANGGARAN -->
        <a href="kelola_pelanggaran_kategori.php"
           class="<?= ($current_page == 'kelola_pelanggaran_kategori.php') ? 'active' : '' ?>">
            ⚠ Kategori Pelanggaran
        </a>


        <!-- DATA PELANGGARAN -->
        <a href="pelanggaran.php"
           class="<?= ($current_page == 'pelanggaran.php') ? 'active' : '' ?>">
             Data Pelanggaran
        </a>


        <!-- DATA TINDAKAN -->
        <a href="tindakan.php"
           class="<?= ($current_page == 'tindakan.php') ? 'active' : '' ?>">
             Data Tindakan
        </a>


        <!-- LAPORAN -->
        <a href="laporan.php"
           class="<?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
            Laporan
        </a>


        <!-- ABOUT -->
        <a href="about_me.php"
           class="<?= ($current_page == 'about_me.php') ? 'active' : '' ?>">
             About Me
        </a>

    <?php } ?>


    <?php if ($role == 'guru') { ?>

        <!-- CATAT PELANGGARAN -->
        <a href="catat_pelanggaran.php"
           class="<?= ($current_page == 'catat_pelanggaran.php') ? 'active' : '' ?>">
         Catat Pelanggaran
        </a>


        <!-- TINDAKAN -->
        <a href="tindakan.php"
           class="<?= ($current_page == 'tindakan.php') ? 'active' : '' ?>">
             Tindakan
        </a>


        <!-- RIWAYAT -->
        <a href="riwayat.php"
           class="<?= ($current_page == 'riwayat.php') ? 'active' : '' ?>">
             Riwayat
        </a>


        <!-- REKAP POIN -->
        <a href="rekap_poin.php"
           class="<?= ($current_page == 'rekap_poin.php') ? 'active' : '' ?>">
             Rekap Poin
        </a>

    <?php } ?>


    <?php if ($role == 'wali_kelas') { ?>

        <!-- DATA PELANGGARAN -->
        <a href="pelanggaran.php"
           class="<?= ($current_page == 'pelanggaran.php') ? 'active' : '' ?>">
             Data Pelanggaran
        </a>


        <!-- LAPORAN -->
        <a href="laporan.php"
           class="<?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
             Laporan
        </a>

    <?php } ?>


    <!-- LOGOUT -->
    <a href="logout.php" class="logout">
         Logout
    </a>

</div>