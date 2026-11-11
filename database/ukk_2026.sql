-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Nov 2026 pada 09.06
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ukk_2026`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` char(1) DEFAULT NULL,
  `mata_pelajaran` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `jenis_kelamin`, `mata_pelajaran`, `email`, `no_hp`, `alamat`, `status_aktif`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, '80001', 'Ari Sandriana', 'L', 'Produktif RPL', 'arisandriana@gmail.com', '087753673478346', NULL, 1, '2026-10-16 07:23:39', '2026-11-09 01:38:54'),
(2, '80002', 'Aldi Maulana', 'L', 'Bahasa Indonesia', 'aldiganteng@gmail.com', '0877253734832', NULL, 1, '2026-10-16 07:23:39', '2026-11-09 01:35:18'),
(7, '198931871', 'Ruri Handayani', NULL, NULL, 'errxrpl0@gmail.com', '085314171171', NULL, 1, '2026-11-10 22:19:13', '2026-11-10 22:19:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_pelanggaran`
--

CREATE TABLE `jenis_pelanggaran` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `poin` int(11) DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenis_pelanggaran`
--

INSERT INTO `jenis_pelanggaran` (`id`, `kategori_id`, `nama`, `poin`, `keterangan`, `status_aktif`) VALUES
(1, 1, 'Terlambat', 5, 'Datang terlambat ke sekolah', 1),
(2, 1, 'Tidak Mengikuti Upacara', 10, 'Tidak mengikuti upacara', 1),
(3, 2, 'Berkelahi', 25, 'Melakukan perkelahian', 1),
(4, 3, 'Tidak Lengkap Seragam', 5, 'Seragam tidak lengkap', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`) VALUES
(1, 'Kedisiplinan'),
(2, 'Ketertiban'),
(3, 'Seragam');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `tingkat` varchar(10) DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `jurusan`, `tingkat`, `status_aktif`) VALUES
(1, 'X RPL 1', 'RPL', 'X', 1),
(2, 'XI RPL 1', 'RPL', 'XI', 1),
(3, 'XII RPL 1', 'RPL', 'XII', 1),
(4, 'X RPL 2', 'RPL', 'X', 1),
(5, 'XI RPL 2', 'RPL', 'XI', 1),
(6, 'XII RPL 2', 'RPL', 'XII', 1),
(7, 'X RPL 3', 'RPL', 'X', 1),
(8, 'X TKJ 1', 'TKJ', 'X', 1),
(9, 'X TKJ 2', 'TKJ', 'X', 1),
(10, 'X TKJ 3', 'TKJ', 'X', 1),
(11, 'X BDPM 1', 'BDPM', 'X', 1),
(12, 'X BDPM 2', 'BDPM', 'X', 1),
(13, 'X BDPM 3', 'BDPM', 'X', 1),
(14, 'XI RPL 3', 'RPL', 'XI', 1),
(15, 'XI TKJ 1', 'TKJ', 'XI', 1),
(16, 'XI TKJ 2', 'TKJ', 'XI', 1),
(17, 'XI TKJ 3', 'TKJ', 'XI', 1),
(18, 'XI BDPM 1', 'BDPM', 'XI', 1),
(19, 'XI BDPM 2', 'BDPM', 'XI', 1),
(20, 'XI BDPM 3', 'BDPM', 'XI', 1),
(21, 'XII RPL 3', 'RPL', 'XII', 1),
(22, 'XII TKJ 1', 'TKJ', 'XII', 1),
(23, 'XII TKJ 2', 'TKJ', 'XII', 1),
(24, 'XII TKJ 3', 'TKJ', 'XII', 1),
(25, 'XII BDPM 1', 'BDPM', 'XII', 1),
(26, 'XII BDPM 2', 'BDPM', 'XII', 1),
(27, 'XII BDPM 4', 'BDPM', 'XII', 1),
(28, 'XII TKR 1', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggaran`
--

CREATE TABLE `pelanggaran` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `jenis_id` int(11) NOT NULL,
  `guru_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'tercatat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelanggaran`
--

INSERT INTO `pelanggaran` (`id`, `siswa_id`, `jenis_id`, `guru_id`, `tanggal`, `keterangan`, `status`) VALUES
(8, 99, 2, 2, '2026-11-09', 'Bolos saat Upacara bendera', 'ditindaklanjuti'),
(9, 50, 4, 2, '2026-03-04', 'Tidak memagai Logo', 'selesai'),
(10, 54, 3, 1, '2026-06-17', 'Berkelahi Saat jam Istirahat', 'ditindaklanjuti'),
(11, 70, 1, 1, '2026-12-08', 'Terlambat 20 menit', 'selesai'),
(12, 101, 1, 2, '2026-11-10', '', 'tercatat'),
(13, 123, 2, 2, '2026-11-10', 'boloss', 'ditindaklanjuti'),
(14, 71, 4, 1, '2026-11-11', '', 'ditindaklanjuti');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penempatan_siswa`
--

CREATE TABLE `penempatan_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penempatan_siswa`
--

INSERT INTO `penempatan_siswa` (`id`, `siswa_id`, `kelas_id`, `tahun_ajaran_id`) VALUES
(38, 48, 3, 1),
(39, 49, 3, 1),
(40, 50, 3, 1),
(41, 51, 3, 1),
(42, 52, 3, 1),
(43, 53, 3, 1),
(44, 54, 3, 1),
(45, 55, 3, 1),
(46, 56, 3, 1),
(47, 57, 3, 1),
(48, 58, 3, 1),
(49, 59, 3, 1),
(50, 60, 3, 1),
(51, 61, 3, 1),
(52, 62, 3, 1),
(53, 63, 3, 1),
(54, 64, 3, 1),
(55, 65, 3, 1),
(56, 66, 3, 1),
(57, 67, 3, 1),
(58, 68, 3, 1),
(59, 69, 3, 1),
(60, 70, 3, 1),
(61, 71, 3, 1),
(69, 98, 6, 1),
(70, 99, 6, 1),
(71, 100, 6, 1),
(72, 101, 6, 1),
(73, 102, 6, 1),
(74, 103, 6, 1),
(75, 104, 6, 1),
(76, 105, 6, 1),
(77, 106, 6, 1),
(78, 108, 6, 1),
(79, 109, 6, 1),
(80, 110, 6, 1),
(81, 111, 6, 1),
(82, 112, 6, 1),
(83, 113, 6, 1),
(84, 114, 6, 1),
(85, 115, 6, 1),
(86, 116, 6, 1),
(87, 117, 6, 1),
(88, 118, 6, 1),
(89, 120, 6, 1),
(90, 121, 6, 1),
(91, 122, 6, 1),
(92, 123, 6, 1),
(93, 107, 6, 1),
(94, 119, 6, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` char(1) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp_ortu` varchar(20) DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `status_aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nisn`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `no_hp_ortu`, `aktif`, `status_aktif`, `created_at`, `updated_at`) VALUES
(48, '1024', '9990000001', 'Alvin Rahadian', 'L', '2008-08-03', 'Tasikmalaya', '080000000001', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(49, '1025', '9990000002', 'Alisa Ramadhani', 'P', '2008-09-28', 'Tasikmalaya', '080000000002', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(50, '1026', '9990000003', 'Alma Aura Livia', 'P', '2008-08-26', 'Tasikmalaya', '080000000003', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(51, '1027', '9990000004', 'Anis Khoerunnisa N.R', 'P', '2008-09-25', 'Tasikmalaya', '080000000004', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(52, '1028', '9990000005', 'Azmil Amira', 'P', '2009-10-30', 'Tasikmalaya', '080000000005', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(53, '1029', '9990000006', 'Azril Akbar', 'L', '2008-08-21', 'Tasikmalaya', '080000000006', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(54, '1030', '9990000007', 'Erlangga Saputra', 'L', '2008-06-06', 'Tasikmalaya', '080000000007', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(55, '1031', '9990000008', 'Hilma Himmatunnajah', 'P', '2009-03-25', 'Tasikmalaya', '080000000008', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(56, '1032', '9990000009', 'Ilma Nazya', 'P', '2009-02-16', 'Tasikmalaya', '080000000009', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(57, '1033', '9990000010', 'Julianda Dharma Pangestu', 'L', '2008-07-02', 'Tasikmalaya', '080000000010', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(58, '1034', '9990000011', 'Kayila Nur Fadilah', 'P', '2009-07-15', 'Tasikmalaya', '080000000011', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(59, '1035', '9990000012', 'Moza Dina Nabillah', 'P', '2008-11-21', 'Tasikmalaya', '080000000012', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(60, '1036', '9990000013', 'Muhammad Al Farizi', 'L', '2008-11-13', 'Tasikmalaya', '080000000013', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(61, '1037', '9990000014', 'Fahrin', 'L', '2008-12-20', 'Tasikmalaya', '080000000014', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(62, '1038', '9990000015', 'Muhammad Zidan', 'L', '2006-03-30', 'Tasikmalaya', '080000000015', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(63, '1039', '9990000016', 'Muhammad Zulfan', 'L', '2009-01-27', 'Tasikmalaya', '080000000016', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(64, '1040', '9990000017', 'Raditya Jeti Prasetya', 'L', '2009-09-01', 'Tasikmalaya', '080000000017', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(65, '1041', '9990000018', 'Rangga Ramadiyanto Nurhakim', 'L', '2008-05-21', 'Tasikmalaya', '080000000018', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(66, '1042', '9990000019', 'Natiya Nur Azkiya', 'P', '2008-12-26', 'Tasikmalaya', '080000000019', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(67, '1043', '9990000020', 'Novita Puspitasari', 'P', '2008-11-02', 'Tasikmalaya', '080000000020', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(68, '1044', '9990000021', 'Risqi Muhamad Ihwani', 'L', '2009-07-22', 'Tasikmalaya', '080000000021', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(69, '1045', '9990000022', 'Nucky Andreas Febrian', 'L', '2008-02-16', 'Tasikmalaya', '080000000022', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(70, '1046', '9990000023', 'Salwa Sabilatul Barkah', 'P', '2008-09-24', 'Jl. Cikanyere', '080000000023', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(71, '1047', '9990000024', 'Tazkia Zahra', 'P', '2009-01-09', 'Tasikmalaya', '080000000024', 1, 1, '2026-11-09 01:14:20', '2026-11-09 01:14:20'),
(98, '102410544', '9990000025', 'Adittia Nur Lantip', 'L', '2008-01-01', 'Tasikmalaya', '080000000025', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(99, '102410545', '9990000026', 'Agnia Rosya Noerillah', 'P', '2008-01-01', 'Tasikmalaya', '080000000026', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(100, '102410546', '9990000027', 'Anisa Amirulla', 'P', '2008-01-01', 'Tasikmalaya', '080000000027', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(101, '102410547', '9990000028', 'Apadea Kandi', 'P', '2008-01-01', 'Tasikmalaya', '080000000028', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(102, '102410548', '9990000029', 'Arfa Diesa Okmaleliza', 'P', '2008-01-01', 'Tasikmalaya', '080000000029', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(103, '102410549', '9990000030', 'Fitri Nur Syiami', 'P', '2008-01-01', 'Tasikmalaya', '080000000030', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(104, '102410550', '9990000031', 'Junita Putri Nur Rahman', 'P', '2008-01-01', 'Tasikmalaya', '080000000031', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(105, '102410551', '9990000032', 'Mohammad Atalarik Ali Syahbana', 'L', '2008-01-01', 'Tasikmalaya', '080000000032', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(106, '102410552', '9990000033', 'Muhamad Fahril Fahrurrozi', 'L', '2008-01-01', 'Tasikmalaya', '080000000033', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(107, '102410750', '9990000034', 'Muhammad Adnan Syaeful Islami', 'L', '2008-01-01', 'Tasikmalaya', '080000000034', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(108, '102410553', '9990000035', 'Muhammad Zahru Aqila Fikri', 'L', '2008-01-01', 'Tasikmalaya', '080000000035', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(109, '102410554', '9990000036', 'Nadia Zulfa Soraya', 'P', '2008-01-01', 'Tasikmalaya', '080000000036', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(110, '102410555', '9990000037', 'Nazril Ilham', 'L', '2008-01-01', 'Tasikmalaya', '080000000037', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(111, '102410556', '9990000038', 'Puspita Dewi', 'P', '2008-01-01', 'Tasikmalaya', '080000000038', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(112, '102410558', '9990000039', 'Rakha Muhammad Haykal', 'L', '2008-01-01', 'Tasikmalaya', '080000000039', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(113, '102410559', '9990000040', 'Rani Meilani', 'P', '2008-01-01', 'Tasikmalaya', '080000000040', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(114, '102410560', '9990000041', 'Reihan Muzaki', 'L', '2008-01-01', 'Tasikmalaya', '080000000041', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(115, '102410561', '9990000042', 'Resa Juliansah', 'L', '2008-01-01', 'Tasikmalaya', '080000000042', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(116, '102410562', '9990000043', 'Rizki Permata Sugianto', 'L', '2008-01-01', 'Tasikmalaya', '080000000043', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(117, '102410563', '9990000044', 'Sahira Firaj', 'P', '2008-01-01', 'Tasikmalaya', '080000000044', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(118, '102410564', '9990000045', 'Sendi Mardian', 'L', '2008-01-01', 'Tasikmalaya', '080000000045', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(119, '102410755', '9990000046', 'Shahaidan Nizzar', 'L', '2008-01-01', 'Tasikmalaya', '080000000046', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(120, '102410565', '9990000047', 'Sherly Satriani', 'P', '2008-01-01', 'Tasikmalaya', '080000000047', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(121, '102410566', '9990000048', 'Sofi Nurjanah', 'P', '2008-01-01', 'Tasikmalaya', '080000000048', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(122, '102410567', '9990000049', 'Suraya Salsabila Azzahra', 'P', '2008-01-01', 'Tasikmalaya', '080000000049', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04'),
(123, '102410568', '9990000050', 'Toni Antonio', 'L', '2008-01-01', 'Tasikmalaya', '080000000050', 1, 1, '2026-11-09 01:24:04', '2026-11-09 01:24:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `tahun` varchar(20) NOT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `aktif`, `tahun`, `semester`, `status_aktif`) VALUES
(1, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026/2027', 'Ganjil', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tindakan`
--

CREATE TABLE `tindakan` (
  `id` int(11) NOT NULL,
  `pelanggaran_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `tindakan` text NOT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tindakan`
--

INSERT INTO `tindakan` (`id`, `pelanggaran_id`, `tanggal`, `tindakan`, `petugas_id`, `catatan`) VALUES
(6, 8, '2026-11-09', 'Diberi Pembinaan', 6, ''),
(7, 10, '2026-07-15', 'Diberi Pembinaan Oleh BK', 1, 'Surat Peringatan 1'),
(8, 9, '2026-12-09', 'Diberikan Peringatan', 1, ''),
(9, 11, '2026-07-09', 'Peringatan', 1, ''),
(10, 14, '2026-11-11', 'PEMANGGILAN', 6, 'JUI');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(30) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `level` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `guru_id`, `nama`, `username`, `password`, `role`, `aktif`, `level`, `email`) VALUES
(1, NULL, 'Administrator', 'admin', '$2y$10$oj6pFVlCjbAuxI6rmfoCN.KjKAD3OIYwT9RvKFXEoGnGVxu6B.sAi', 'admin', 1, 'admin', 'adminsmkm@gmail.com'),
(3, NULL, 'Guru', 'guru', '$2y$10$oj6pFVlCjbAuxI6rmfoCN.KjKAD3OIYwT9RvKFXEoGnGVxu6B.sAi', 'guru', 1, NULL, 'gurusmkm2@gmail.com'),
(4, NULL, 'Wali Kelas', 'walikelas', 'walikelas', 'wali_kelas', 1, NULL, 'walikelas2@gmail.com'),
(6, 1, 'Ari Sandriana', 'arisandriana@gmail.com', '$2y$10$6DII35FTrTJY54HuTUc17OPhZIFYbUSHGbIMouQzCoUVbWf0VaajK', 'wali_kelas', 1, 'guru', 'arisandriana@gmail.com'),
(7, 2, 'Aldi Maulana', 'aldiganteng@gmail.com', '$2y$10$6DII35FTrTJY54HuTUc17OPhZIFYbUSHGbIMouQzCoUVbWf0VaajK', 'wali_kelas', 1, 'guru', 'aldiganteng@gmail.com'),
(9, NULL, 'Ruri Handayani', 'errxrpl0@gmail.com', '$2y$12$6nmXWonaIDoKMrVJPUL3HOrW6dzEmKTbyjSqn5.p5ohvBokBoC4we', 'guru', 1, NULL, 'errxrpl0@gmail.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wali_kelas`
--

CREATE TABLE `wali_kelas` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `wali_kelas`
--

INSERT INTO `wali_kelas` (`id`, `guru_id`, `kelas_id`, `tahun_ajaran_id`) VALUES
(6, 1, 3, 1),
(7, 2, 6, 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nip` (`nip`);

--
-- Indeks untuk tabel `jenis_pelanggaran`
--
ALTER TABLE `jenis_pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `jenis_id` (`jenis_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indeks untuk tabel `penempatan_siswa`
--
ALTER TABLE `penempatan_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nis` (`nis`);

--
-- Indeks untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tindakan`
--
ALTER TABLE `tindakan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggaran_id` (`pelanggaran_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_users_guru_id` (`guru_id`);

--
-- Indeks untuk tabel `wali_kelas`
--
ALTER TABLE `wali_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `jenis_pelanggaran`
--
ALTER TABLE `jenis_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `penempatan_siswa`
--
ALTER TABLE `penempatan_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tindakan`
--
ALTER TABLE `tindakan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `wali_kelas`
--
ALTER TABLE `wali_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jenis_pelanggaran`
--
ALTER TABLE `jenis_pelanggaran`
  ADD CONSTRAINT `jenis_pelanggaran_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD CONSTRAINT `pelanggaran_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pelanggaran_ibfk_2` FOREIGN KEY (`jenis_id`) REFERENCES `jenis_pelanggaran` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pelanggaran_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penempatan_siswa`
--
ALTER TABLE `penempatan_siswa`
  ADD CONSTRAINT `penempatan_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `penempatan_siswa_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `penempatan_siswa_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tindakan`
--
ALTER TABLE `tindakan`
  ADD CONSTRAINT `tindakan_ibfk_1` FOREIGN KEY (`pelanggaran_id`) REFERENCES `pelanggaran` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tindakan_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wali_kelas`
--
ALTER TABLE `wali_kelas`
  ADD CONSTRAINT `wali_kelas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wali_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wali_kelas_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
