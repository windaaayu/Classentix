-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 03:15 PM
-- Server version: 10.11.11-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `classentix_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `id_mapel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `id_user`, `nip`, `id_mapel`) VALUES
(1, 2, '100420', 2),
(3, 3, '220219', 1),
(4, 4, '1906019', 1),
(5, 5, '010101', NULL),
(7, 7, '000000', 6);

-- --------------------------------------------------------

--
-- Table structure for table `guru_kelas`
--

CREATE TABLE `guru_kelas` (
  `id` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru_kelas`
--

INSERT INTO `guru_kelas` (`id`, `id_guru`, `id_kelas`) VALUES
(1, 3, 5),
(2, 3, 1),
(5, 1, 3),
(7, 7, 5),
(8, 7, 7),
(9, 7, 1),
(10, 7, 3),
(11, 7, 10),
(12, 4, 5),
(13, 4, 10);

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_pelajaran`
--

CREATE TABLE `jadwal_pelajaran` (
  `id` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_pelajaran`
--

INSERT INTO `jadwal_pelajaran` (`id`, `id_kelas`, `id_mapel`, `id_guru`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(2, 5, 2, 3, 'Rabu', '12:00:00', '17:00:00'),
(5, 5, 1, 4, 'Selasa', '07:00:00', '09:15:00'),
(7, 5, 1, 3, 'Jumat', '07:00:00', '10:45:00'),
(8, 3, 2, 1, 'Senin', '07:00:00', '09:15:00'),
(9, 5, 1, 4, 'Senin', '09:15:00', '12:00:00'),
(10, 3, 2, 1, 'Senin', '09:15:00', '12:00:00'),
(11, 7, 6, 7, 'Senin', '07:00:00', '09:15:00'),
(12, 1, 6, 7, 'Senin', '09:15:00', '12:00:00'),
(13, 5, 6, 7, 'Selasa', '12:45:00', '15:00:00'),
(14, 3, 6, 7, 'Rabu', '14:15:00', '16:30:00'),
(15, 10, 6, 7, 'Kamis', '09:15:00', '12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`) VALUES
(5, 'Hololives'),
(7, 'Umineko'),
(1, 'X RPL 1'),
(3, 'XI RPL 1'),
(10, 'XII RPL 1');

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id` int(11) NOT NULL,
  `code_mapel` varchar(20) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id`, `code_mapel`, `nama_mapel`) VALUES
(1, 'H-01', 'Singing'),
(2, 'H-02', 'Dance'),
(6, 'U-00', 'Writing');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `absen` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `id_kelas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `absen`, `nama`, `id_kelas`) VALUES
(1, 1, 'Mori Calliope', 5),
(2, 2, 'Takanashi Kiara', 5),
(3, 3, 'Ninomae Ina\'nis', 5),
(4, 4, 'Gawr Gura', 5),
(5, 5, 'Watson Amelia', 5),
(9, 4, 'Muhammad Fatihul Ihsan', 3),
(12, 6, 'Muhammad Fatihul Ihsans', 5),
(13, 2, 'Palyer2', 1),
(14, 1, 'Featherine Augustus Aurora', 7),
(15, 2, 'Bernkastel', 7),
(16, 3, 'Battler', 7),
(17, 4, 'Lambdadelta', 7),
(18, 5, 'Piece', 7);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','guru') NOT NULL,
  `status_validasi` enum('pending','aktif') DEFAULT 'pending',
  `no_telp` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `foto`, `nama_lengkap`, `role`, `status_validasi`, `no_telp`) VALUES
(1, 'Suisei', '$2y$10$E9ltti9/i1GnHafP6eulgu5AbGHhUKvOd3INN/C9UXVvZ3XvJbmhe', 'admin_Suisei_1778762613.png', 'Hoshimachi Suisei', 'admin', 'aktif', '081234567890'),
(2, 'Ayunda Risu', '$2y$10$Qf1rVCgTG5xzvkQY6sFh..zran0.bAUC.b23gcn8Dg35HmW6uGU4m', NULL, 'Risu', 'guru', 'aktif', '081234567890'),
(3, 'Okayu', '$2y$10$.pTPbA2oV3XiqWRcNZZGMuV5.IBnxLBXlegJe5r9ogTGOMb2VWQKW', NULL, 'Nekomata Okayu', 'guru', 'aktif', '081234567890'),
(4, 'Mufaih9', '$2y$10$M3sV07cIEG48OgcesH2KeeVO0ILTUYWHy4YDjKFgieM/ddD6qa3fm', NULL, 'Mufaih019', 'guru', 'aktif', '0812345678909'),
(5, 'gr', '$2y$10$tw8qAdg2yith3M7VI3FwE.Gn/v0/b6WxMxjHXJAxfk2xQ5tVSRrTu', NULL, 'Guru1', 'guru', 'pending', '08123456789'),
(7, 'Beato', '$2y$10$DVdNI5p5vz/M.SNkS8xy5Ov76RkznlKYPJTSLcx4C.0894x1XBRlO', 'Beato_1778846521.png', 'Beatorīche', 'guru', 'aktif', '00000000000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_guru_mapel` (`id_mapel`);

--
-- Indexes for table `guru_kelas`
--
ALTER TABLE `guru_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_guru` (`id_guru`),
  ADD KEY `idx_id_kelas` (`id_kelas`);

--
-- Indexes for table `jadwal_pelajaran`
--
ALTER TABLE `jadwal_pelajaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `id_mapel` (`id_mapel`),
  ADD KEY `id_guru` (`id_guru`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kelas` (`nama_kelas`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_mapel` (`code_mapel`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `guru_kelas`
--
ALTER TABLE `guru_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jadwal_pelajaran`
--
ALTER TABLE `jadwal_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fk_guru_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `guru_kelas`
--
ALTER TABLE `guru_kelas`
  ADD CONSTRAINT `fk_gk_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gk_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jadwal_pelajaran`
--
ALTER TABLE `jadwal_pelajaran`
  ADD CONSTRAINT `jadwal_pelajaran_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_pelajaran_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_pelajaran_ibfk_3` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
