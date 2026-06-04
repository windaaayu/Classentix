-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 09:40 AM
-- Server version: 10.4.32-MariaDB
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
(8, 8, '199003122015041002', 14),
(9, 9, '199211052018032001', 15),
(10, 10, '199506182020122002', 7),
(11, 11, '198508142011011003', 17),
(12, 12, '199102282016051001', 16),
(13, 13, '198805232014021001', 10),
(14, 14, '197812302003121001', 9),
(15, 15, '199402182022032001', 9),
(16, 16, '198201252008012003', 8),
(17, 17, '198007192005012004', 11),
(18, 18, '198710102012011002', 13),
(19, 19, '198304022009021002', 12);

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
(14, 8, 13),
(15, 8, 12),
(16, 9, 13),
(17, 9, 12),
(18, 10, 14),
(19, 10, 17),
(20, 11, 16),
(21, 11, 15),
(22, 12, 17),
(23, 13, 14),
(24, 13, 17),
(25, 13, 16),
(26, 13, 15),
(27, 14, 14),
(28, 14, 13),
(29, 14, 12),
(30, 15, 17),
(31, 15, 16),
(32, 15, 15),
(33, 16, 14),
(34, 16, 17),
(35, 16, 13),
(36, 16, 16),
(37, 16, 12),
(38, 16, 15),
(39, 17, 14),
(40, 17, 17),
(41, 17, 13),
(42, 17, 16),
(43, 17, 12),
(44, 17, 15),
(45, 18, 14),
(46, 18, 17),
(47, 18, 13),
(48, 18, 16),
(49, 18, 12),
(50, 18, 15),
(51, 19, 14),
(52, 19, 17),
(53, 19, 13),
(54, 19, 16),
(55, 19, 12),
(56, 19, 15);

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
(20, 14, 7, 10, 'Senin', '07:00:00', '09:15:00'),
(21, 14, 8, 16, 'Senin', '09:15:00', '12:00:00'),
(22, 14, 10, 13, 'Senin', '12:45:00', '14:15:00'),
(23, 14, 9, 14, 'Selasa', '07:00:00', '09:15:00'),
(24, 14, 11, 17, 'Selasa', '09:15:00', '12:00:00'),
(25, 14, 7, 10, 'Selasa', '12:45:00', '14:15:00'),
(26, 14, 12, 19, 'Rabu', '07:00:00', '09:15:00'),
(27, 14, 13, 18, 'Rabu', '09:15:00', '12:00:00'),
(28, 14, 8, 16, 'Rabu', '12:45:00', '14:15:00'),
(29, 14, 10, 13, 'Kamis', '07:00:00', '09:15:00'),
(30, 14, 9, 14, 'Kamis', '09:15:00', '12:00:00'),
(31, 14, 7, 10, 'Kamis', '12:45:00', '14:15:00'),
(32, 14, 13, 18, 'Jumat', '07:00:00', '09:15:00'),
(33, 14, 11, 17, 'Jumat', '09:15:00', '12:00:00'),
(34, 17, 9, 15, 'Sabtu', '07:00:00', '09:15:00'),
(35, 14, 8, 16, 'Sabtu', '09:15:00', '12:00:00'),
(36, 17, 16, 12, 'Senin', '07:00:00', '09:15:00'),
(37, 17, 9, 15, 'Senin', '09:15:00', '12:00:00'),
(38, 17, 7, 10, 'Senin', '12:45:00', '14:15:00'),
(39, 17, 10, 13, 'Selasa', '07:00:00', '09:15:00'),
(40, 17, 7, 10, 'Selasa', '09:15:00', '12:00:00'),
(41, 17, 16, 12, 'Selasa', '12:45:00', '14:15:00'),
(42, 17, 11, 17, 'Rabu', '07:00:00', '09:15:00'),
(43, 17, 8, 16, 'Rabu', '09:15:00', '12:00:00'),
(44, 17, 12, 19, 'Rabu', '12:45:00', '14:15:00'),
(45, 17, 13, 18, 'Kamis', '07:00:00', '09:15:00'),
(46, 17, 16, 12, 'Kamis', '09:15:00', '12:00:00'),
(47, 17, 9, 15, 'Kamis', '12:45:00', '14:15:00'),
(48, 17, 16, 12, 'Jumat', '07:00:00', '09:15:00'),
(49, 17, 10, 13, 'Jumat', '09:15:00', '12:00:00'),
(50, 14, 8, 16, 'Sabtu', '07:00:00', '09:15:00'),
(51, 17, 13, 18, 'Sabtu', '09:15:00', '12:00:00');

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
(14, 'X RPL'),
(17, 'X TKJ'),
(13, 'XI RPL'),
(16, 'XI TKJ'),
(12, 'XII RPL'),
(15, 'XII TKJ');

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
(7, 'PMD-01', 'Pemrograman Dasar'),
(8, 'BI-01', 'Bahasa Indonesia'),
(9, 'MTK-01', 'Matematika'),
(10, 'SK-01', 'Sistem Komputer'),
(11, 'SJ-01', 'Sejarah'),
(12, 'PJO-01', 'Olahraga'),
(13, 'AGM-01', 'Agama'),
(14, 'PWB-01', 'Pemrograman Web & Perangkat Bergerak'),
(15, 'PBO-01', 'Pemrograman Berorientasi Objek'),
(16, 'DTKJT-01', 'Dasar-Dasar TJKT'),
(17, 'ASJ-01', 'Administrasi Sistem Jaringan');

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
(19, 1, 'Muhammad Ilham', 14),
(20, 2, 'Rina Sulistiyowati', 14),
(21, 1, 'Aditya Pratama', 13),
(22, 2, 'Dwi Utami', 13),
(23, 1, 'Wahyu Hidayat', 12),
(24, 2, 'Sri Rahayu', 12),
(25, 1, 'Agus Setiawan', 17),
(26, 2, 'Dewi Rahmawati', 17),
(27, 1, 'Angga Saputra', 16),
(28, 2, 'Nur Azizah', 16),
(29, 1, 'Tri Mulyono', 15),
(30, 2, 'Siti Maesaroh', 15);

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
(1, 'Suisei', '$2y$10$E9ltti9/i1GnHafP6eulgu5AbGHhUKvOd3INN/C9UXVvZ3XvJbmhe', 'admin_Suisei_1780555541.png', 'Hoshimachi Suisei', 'admin', 'aktif', '081234567890'),
(8, 'Gathan, S.Kom.', '$2y$10$jyNlQJJAU74hDWGWml8icucriNdW5c3Eqe/RjqDqigF16n0OwsrBq', NULL, 'Gathan Alvaro Mahendra, S.Kom.', 'guru', 'aktif', '081234567890'),
(9, 'Kireina, S.Kom.', '$2y$10$FQxaRLSu/XArJHL6MDB/FeHsNRsQHea1NiJmgxBY/bYS1yhguAcu.', NULL, 'Kireina Ayudia Larasati, S.Kom.', 'guru', 'aktif', '081456789012'),
(10, 'Nayla, S.Kom.', '$2y$10$81D3JpxdH.Q8QvJjYbyY/exXJZhxEHhNQXsqm8NycvpJN7feRF7Ua', NULL, 'Nayla Carissa Putri, S.Kom.', 'guru', 'aktif', '081990124567'),
(11, 'Danendra, S.T.', '$2y$10$IFY0T7JrG5zW3SDeobHFlu1rALjAu87v9GCQ0qeOA.4C/N9ok8lHm', NULL, 'Danendra Tirta Dewanto, S.T.', 'guru', 'aktif', '081567890123'),
(12, 'Nicholas, S.T.', '$2y$10$MkQgJKcFXkLnSFfPe5Q63OPqJFCIhPnDLDbWWgd8oS8L5SIZJfjBi', NULL, 'Nicholas Kevin Fernando, S.T.', 'guru', 'aktif', '082334567890'),
(13, 'Reyham, ST.', '$2y$10$yeUMkjeVrsk61VYDnSrZFu0agA2obaoNre8R4L64BMDfo27lsy6lC', NULL, 'Reyhan Adiputra Wijaya, S.T.', 'guru', 'aktif', '081345678901'),
(14, 'Banyu, S.Pd.', '$2y$10$IWmuweQh1eGLWAzqAXFZ/u2p.wcnOA4ReKD.Ls1/TY.VjsmtUI0Dq', NULL, 'Banyu Bening Wicaksono, S.Pd.', 'guru', 'aktif', '081889013456'),
(15, 'Ferinda, S.Pd.', '$2y$10$MTKjh0H88.zK9PcKNQTYe.rig6eOUs1k6EEadsIzIo2SQ7Mzy4lXm', NULL, 'Ferinda Eka, S.Pd.', 'guru', 'aktif', '082456789012'),
(16, 'Citra, S.Pd.', '$2y$10$iam0vpnESwXJCxPBMj7aP.i/Rc2IfVlUygP/..9qeY2UUcRAD.tWa', NULL, 'Citra Resmi Rahayu, S.Pd.', 'guru', 'aktif', '082112345678'),
(17, 'Nawang, S.Pd.', '$2y$10$tT0sCPa2I0vSuHhN9rqy..hHc9gl/k7Qisz/pgfbtmeOjpdlVrzgG', NULL, 'Nawang Wulan Lestari, S.Pd.', 'guru', 'aktif', '081778902345'),
(18, 'Ahmad, S.Pd.I.', '$2y$10$Bj/4vonbE7zWB09ND14IQuQa3zXzM6Ftn0XW8WMBYxnYY0FrWcMoO', NULL, 'Ahmad Dzaki Al-Fatih, S.Pd.I.', 'guru', 'aktif', '082223456789'),
(19, 'Adrian, S.Pd.', '$2y$10$07G0drNjtVRINNBmAgstq.KtZ8xYgtlzx8SNtnpE6Fs5qga59hrw.', NULL, 'Adrian Jonathan Syahputra, S.Pd.', 'guru', 'aktif', '081678901234');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `guru_kelas`
--
ALTER TABLE `guru_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `jadwal_pelajaran`
--
ALTER TABLE `jadwal_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
