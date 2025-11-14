-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2025 at 05:00 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_banksampah`
--
CREATE DATABASE IF NOT EXISTS `db_banksampah` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_banksampah`;

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('admin','petugas','warga') NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(15) DEFAULT NULL COMMENT 'Digunakan untuk cek info publik warga',
  `saldo` decimal(10,2) DEFAULT 0.00,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--
-- Password untuk admin: admin123
-- Password untuk petugas: petugas123
-- Password warga di-generate otomatis dan tidak digunakan untuk login.
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `username`, `password`, `level`, `alamat`, `no_telepon`, `saldo`) VALUES
(1, 'Administrator Utama', 'admin', '$2y$10$OQ4wl7ky9pv./gSXpGdIKexnquBeYSOTr52XpZuVmlCS6L8fVoPDC', 'admin', 'Kantor Pusat', '081200000001', 0.00),
(2, 'Petugas Lapangan 1', 'petugas1', '$2y$10$XhZh00u8Bn/k5kaEkhTkx..1fys3QgRuk180laSYH276qHg5hzh9m', 'petugas', 'Pos Petugas A', '081200000002', 0.00),
(3, 'Budi Santoso', '081234567001', '$2y$10$3g.K0.jM4R5e.g8hL1j.K.uV0wX1eK.L3mN4oP5qR6sT7uV8wX9y', 'warga', 'Jl. Merdeka No. 10', '081234567001', 50000.00),
(4, 'Siti Aminah', '081234567002', '$2y$10$9z.A1.bC2d.eF3g.H4i.J.kL5mN6oP7qR8sT9uV0wX1yZ2aB3c4D', 'warga', 'Jl. Pahlawan No. 5', '081234567002', 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `jenis_sampah`
--

DROP TABLE IF EXISTS `jenis_sampah`;
CREATE TABLE `jenis_sampah` (
  `id_jenis_sampah` int(11) NOT NULL,
  `nama_sampah` varchar(100) NOT NULL,
  `harga_per_kg` decimal(10,2) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `satuan` varchar(10) DEFAULT 'kg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_sampah`
--

INSERT INTO `jenis_sampah` (`id_jenis_sampah`, `nama_sampah`, `harga_per_kg`, `deskripsi`, `satuan`) VALUES
(1, 'Plastik Botol (PET)', 3000.00, 'Botol plastik bekas air mineral, minuman soda, dll.', 'kg'),
(2, 'Kardus', 1500.00, 'Kardus bekas kemasan.', 'kg'),
(3, 'Kertas HVS/Buku', 1200.00, 'Kertas bekas HVS, buku tulis, koran.', 'kg'),
(4, 'Logam (Besi)', 2500.00, 'Besi tua, kaleng, dll.', 'kg'),
(5, 'Logam (Aluminium)', 8000.00, 'Kaleng minuman, bekas peralatan aluminium.', 'kg'),
(6, 'Gelas Plastik (PP)', 2200.00, 'Gelas plastik bekas minuman teh, kopi, dll.', 'kg');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_warga` int(11) NOT NULL,
  `id_petugas_pencatat` int(11) NOT NULL,
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipe_transaksi` enum('setor','tarik_saldo') NOT NULL,
  `total_nilai` decimal(10,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_warga`, `id_petugas_pencatat`, `tanggal_transaksi`, `tipe_transaksi`, `total_nilai`, `keterangan`) VALUES
(1, 3, 2, '2025-05-31 02:06:08', 'setor', 7500.00, 'Setoran sampah rutin'),
(2, 4, 1, '2025-05-31 02:06:08', 'tarik_saldo', 10000.00, 'Penarikan tunai');

-- --------------------------------------------------------

--
-- Table structure for table `detail_setoran`
--

DROP TABLE IF EXISTS `detail_setoran`;
CREATE TABLE `detail_setoran` (
  `id_detail_setoran` int(11) NOT NULL,
  `id_transaksi_setor` int(11) NOT NULL,
  `id_jenis_sampah` int(11) NOT NULL,
  `berat_kg` decimal(5,2) NOT NULL,
  `harga_saat_setor` decimal(10,2) NOT NULL,
  `subtotal_nilai` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_setoran`
--

INSERT INTO `detail_setoran` (`id_detail_setoran`, `id_transaksi_setor`, `id_jenis_sampah`, `berat_kg`, `harga_saat_setor`, `subtotal_nilai`) VALUES
(1, 1, 1, 1.50, 3000.00, 4500.00),
(2, 1, 2, 2.00, 1500.00, 3000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `no_telepon` (`no_telepon`);

--
-- Indexes for table `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  ADD PRIMARY KEY (`id_jenis_sampah`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_warga` (`id_warga`),
  ADD KEY `id_petugas_pencatat` (`id_petugas_pencatat`);

--
-- Indexes for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  ADD PRIMARY KEY (`id_detail_setoran`),
  ADD KEY `id_transaksi_setor` (`id_transaksi_setor`),
  ADD KEY `id_jenis_sampah` (`id_jenis_sampah`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  MODIFY `id_jenis_sampah` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  MODIFY `id_detail_setoran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_warga`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_petugas_pencatat`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  ADD CONSTRAINT `detail_setoran_ibfk_1` FOREIGN KEY (`id_transaksi_setor`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_setoran_ibfk_2` FOREIGN KEY (`id_jenis_sampah`) REFERENCES `jenis_sampah` (`id_jenis_sampah`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;