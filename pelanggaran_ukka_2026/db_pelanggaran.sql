-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2026 at 09:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pelanggaran`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_catat`
--

CREATE TABLE `tbl_catat` (
  `id_catat` int(11) NOT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `id_pelanggaran` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_jenis`
--

CREATE TABLE `tbl_jenis` (
  `id_jenis` int(11) NOT NULL,
  `nama_jenis` varchar(100) DEFAULT NULL,
  `poin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_jenis`
--

INSERT INTO `tbl_jenis` (`id_jenis`, `nama_jenis`, `poin`) VALUES
(1, 'Terlambat', 10),
(2, 'Tidak Pakai Seragam', 15),
(3, 'Bolus / Alpa', 25),
(4, 'Merokok', 50),
(5, 'Berkelahi', 75),
(6, 'Terlambat', 10),
(7, 'Tidak Pakai Seragam', 15),
(8, 'Merokok', 50);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kelas`
--

CREATE TABLE `tbl_kelas` (
  `id_kelas` int(11) NOT NULL,
  `nama_kelas` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pelanggaran`
--

CREATE TABLE `tbl_pelanggaran` (
  `id_pelanggaran` int(11) NOT NULL,
  `id_siswa` int(11) DEFAULT NULL,
  `id_jenis` int(11) DEFAULT NULL,
  `tgl` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pelanggaran`
--

INSERT INTO `tbl_pelanggaran` (`id_pelanggaran`, `id_siswa`, `id_jenis`, `tgl`) VALUES
(1, 12345, 4, '2026-10-07'),
(2, 12347, 2, '2026-10-07'),
(3, 12347, 2, '2026-10-07'),
(4, 12347, 2, '2026-10-07'),
(5, 12347, 2, '2026-10-07'),
(6, 12345, 1, '2026-10-07'),
(7, 12347, 1, '2026-10-07'),
(8, 12347, 1, '2026-10-07'),
(9, 12347, 1, '2026-10-07'),
(10, 12347, 1, '2026-10-07');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_siswa`
--

CREATE TABLE `tbl_siswa` (
  `id_siswa` int(11) NOT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_siswa`
--

INSERT INTO `tbl_siswa` (`id_siswa`, `nis`, `nama`) VALUES
(12347, '12345', 'Budi Santoso'),
(12348, '12346', 'Siti Aminah'),
(12349, '12347', 'Rudi Hermawan'),
(12351, '12348', 'agus');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `role` enum('admin','guru','wali') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin', 'admin'),
(2, 'guru', 'guru', 'guru'),
(3, 'wali', 'wali', 'wali');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_catat`
--
ALTER TABLE `tbl_catat`
  ADD PRIMARY KEY (`id_catat`);

--
-- Indexes for table `tbl_jenis`
--
ALTER TABLE `tbl_jenis`
  ADD PRIMARY KEY (`id_jenis`);

--
-- Indexes for table `tbl_kelas`
--
ALTER TABLE `tbl_kelas`
  ADD PRIMARY KEY (`id_kelas`);

--
-- Indexes for table `tbl_pelanggaran`
--
ALTER TABLE `tbl_pelanggaran`
  ADD PRIMARY KEY (`id_pelanggaran`);

--
-- Indexes for table `tbl_siswa`
--
ALTER TABLE `tbl_siswa`
  ADD PRIMARY KEY (`id_siswa`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_catat`
--
ALTER TABLE `tbl_catat`
  MODIFY `id_catat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_jenis`
--
ALTER TABLE `tbl_jenis`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_kelas`
--
ALTER TABLE `tbl_kelas`
  MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_pelanggaran`
--
ALTER TABLE `tbl_pelanggaran`
  MODIFY `id_pelanggaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_siswa`
--
ALTER TABLE `tbl_siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12352;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
