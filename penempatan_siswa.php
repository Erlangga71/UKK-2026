<?php
require 'database.php';

wajib_login();

/* SIMPAN */
if (isset($_POST['simpan'])) {

    $siswa_id = (int) $_POST['siswa_id'];
    $kelas_id = (int) $_POST['kelas_id'];
    $tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];

    $sql = "INSERT INTO penempatan_siswa 
            (siswa_id, kelas_id, tahun_ajaran_id)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $siswa_id,
        $kelas_id,
        $tahun_ajaran_id
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
                alert('Penempatan siswa berhasil!');
                window.location='penempatan_siswa.php';
              </script>";
        exit;
    } else {
        echo "Gagal menyimpan: " . mysqli_error($koneksi);
    }
}

/* HAPUS */
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query(
        $koneksi,
        "DELETE FROM penempatan_siswa WHERE id = $id"
    );

    header("Location: penempatan_siswa.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penempatan Siswa</title>
</head>

<body>

<h2>Penempatan Siswa</h2>

<a href="dashboard.php">Kembali ke Dashboard</a>

<hr>

<form method="post">

    <p>
        <label>Siswa:</label><br>

        <select name="siswa_id" required>
            <option value="">-- Pilih Siswa --</option>

            <?php
            $q = mysqli_query(
                $koneksi,
                "SELECT id, nis, nama
                 FROM siswa
                 WHERE aktif = 1
                 ORDER BY nama"
            );

            while ($s = mysqli_fetch_assoc($q)) {
                echo "<option value='{$s['id']}'>
                        {$s['nis']} - {$s['nama']}
                      </option>";
            }
            ?>

        </select>
    </p>

    <p>
        <label>Kelas:</label><br>

        <select name="kelas_id" required>
            <option value="">-- Pilih Kelas --</option>

            <?php
            $q = mysqli_query(
                $koneksi,
                "SELECT id, nama_kelas
                 FROM kelas
                 ORDER BY nama_kelas"
            );

            while ($k = mysqli_fetch_assoc($q)) {
                echo "<option value='{$k['id']}'>
                        {$k['nama_kelas']}
                      </option>";
            }
            ?>

        </select>
    </p>

    <p>
        <label>Tahun Ajaran:</label><br>

        <select name="tahun_ajaran_id" required>
            <option value="">-- Pilih Tahun Ajaran --</option>

            <?php
            $q = mysqli_query(
                $koneksi,
                "SELECT id, nama
                 FROM tahun_ajaran
                 ORDER BY id DESC"
            );

            while ($t = mysqli_fetch_assoc($q)) {
                echo "<option value='{$t['id']}'>
                        {$t['nama']}
                      </option>";
            }
            ?>

        </select>
    </p>

    <button type="submit" name="simpan">
        Simpan
    </button>

</form>

<hr>

<h3>Data Penempatan Siswa</h3>

<table border="1" cellpadding="8" cellspacing="0">

    <tr>
        <th>No</th>
        <th>NIS</th>
        <th>Nama Siswa</th>
        <th>Kelas</th>
        <th>Tahun Ajaran</th>
        <th>Aksi</th>
    </tr>

<?php

$no = 1;

$sql = "
    SELECT 
        ps.id,
        s.nis,
        s.nama,
        k.nama_kelas,
        ta.nama AS tahun_ajaran

    FROM penempatan_siswa ps

    JOIN siswa s
        ON ps.siswa_id = s.id

    JOIN kelas k
        ON ps.kelas_id = k.id

    JOIN tahun_ajaran ta
        ON ps.tahun_ajaran_id = ta.id

    ORDER BY k.nama_kelas, s.nama
";

$q = mysqli_query($koneksi, $sql);

while ($d = mysqli_fetch_assoc($q)) {
?>

<tr>

    <td><?= $no++ ?></td>

    <td><?= e($d['nis']) ?></td>

    <td><?= e($d['nama']) ?></td>

    <td><?= e($d['nama_kelas']) ?></td>

    <td><?= e($d['tahun_ajaran']) ?></td>

    <td>
        <a
            href="?hapus=<?= $d['id'] ?>"
            onclick="return confirm('Yakin ingin menghapus data ini?')"
        >
            Hapus
        </a>
    </td>

</tr>

<?php
}
?>

</table>

</body>
</html>