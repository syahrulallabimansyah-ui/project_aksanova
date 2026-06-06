-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 06, 2026 at 11:18 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aksa_nova`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_profile`
--

CREATE TABLE `admin_profile` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `display_name` varchar(100) NOT NULL DEFAULT 'Admin',
  `foto` varchar(255) NOT NULL DEFAULT '',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_profile`
--

INSERT INTO `admin_profile` (`id`, `user_id`, `display_name`, `foto`, `updated_at`) VALUES
(1, 1, 'Hoshimi Miyabi', 'uploads/profil/admin_6a1b57cec5404.jpeg', '2026-05-30 21:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(255) NOT NULL DEFAULT '',
  `isbn` varchar(50) NOT NULL DEFAULT '',
  `genre` varchar(100) NOT NULL DEFAULT '',
  `sinopsis` text,
  `stok` int NOT NULL DEFAULT '0',
  `gambar` varchar(500) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `judul`, `penulis`, `isbn`, `genre`, `sinopsis`, `stok`, `gambar`, `created_at`, `updated_at`) VALUES
(30, 'jujutsu kaisen volume 2', 'gege akutami', '1233-1234-1234', 'Novel Horor', 'yudi itadori', 4, 'uploads/gambar/buku_6a1b59fc8a5b2.jpg', '2026-05-30 21:43:24', '2026-06-04 08:46:10'),
(31, 'unyu', '', '', '', '', 0, 'uploads/gambar/buku_6a1bb4ddb252c.jpg', '2026-05-31 04:11:09', '2026-05-31 04:11:09'),
(32, 'Apollo 11', 'Robert', '1234', 'Novel', 'Hi', 5, 'uploads/gambar/buku_6a213b2eb09aa.jpeg', '2026-06-04 08:45:34', '2026-06-04 08:45:34');

-- --------------------------------------------------------

--
-- Table structure for table `buku_favorites`
--

CREATE TABLE `buku_favorites` (
  `id` int NOT NULL,
  `buku_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buku_likes`
--

CREATE TABLE `buku_likes` (
  `id` int NOT NULL,
  `buku_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buku_ratings`
--

CREATE TABLE `buku_ratings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `buku_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int NOT NULL,
  `buku_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_peminjam` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_pinjam` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `batas_kembali` datetime NOT NULL,
  `waktu_kembali` datetime DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dipinjam',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `buku_id`, `user_id`, `nama_peminjam`, `waktu_pinjam`, `batas_kembali`, `waktu_kembali`, `status`, `created_at`) VALUES
(12, 30, NULL, 'bim', '2026-05-30 21:43:35', '2026-06-06 00:00:00', '2026-05-30 22:00:35', 'dikembalikan', '2026-05-30 21:43:35'),
(13, 30, NULL, 'Syahril', '2026-06-04 08:46:10', '2026-06-05 00:00:00', NULL, 'dipinjam', '2026-06-04 08:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin', 'admin@aksanova.com', '$2y$10$fG5Q3mZaf8XRV5K/s2S7yOULagsXHbA5rZP43ubCi9.Xq9yNerW9y', 'admin', '2026-05-20 04:39:17'),
(2, 'Budi Santoso', 'budi', 'budi@email.com', '$2y$10$TKh8H1.PyfSRnxKG/x7.2eSRHMYT1xsXRTxUH2m7iCDerQZ8y0iWi', 'member', '2026-05-20 04:39:17'),
(3, 'syahrul', 'syahrul', 'hi@gmail.com', '$2y$10$D/YVjtVbrI9a3mkocVpc9OywvyRodCe4lQFDBchzfVNp6OY60TjRS', 'member', '2026-05-20 04:44:29'),
(4, 'ali', 'ali', 'ali@gmail.com', '$2y$10$wbwTa2Khu.aL0qYmS6n.eOmcacpDEP4ts5DYFpIfeIhlDjkFpRCZS', 'member', '2026-05-20 06:23:21'),
(5, 'bima', 'aruru', 'arul@gmail.com', '$2y$10$s987YJ5CNsFyztAClUTnqukOazzwsH9qZ1vJqguzgUhqAM62Bh0B.', 'member', '2026-05-20 12:14:48'),
(6, 'bima', 'arul', 'arul@aksanova.com', '$2y$10$rSPPu5ooZI4dloCBTlFvU.6cOT3gIjQBXhue7GQXQG65zgSeT8XJq', 'member', '2026-05-25 01:05:04'),
(7, 'ooki', 'bima', 'bima123@gmail.com', '$2y$10$fVTGqFOxJAulbaX6Q4TFs.fhOITME5qHKg1aG2.eGV88zirijsPFe', 'member', '2026-05-25 22:52:25'),
(8, 'syahrul bima x', 'deku', 'deku@gmail.com', '$2y$10$JFLxg.j36CQlemymflDCpO0.YwTBSr38Fmyzlu3SPIiRAPqVUldRm', 'member', '2026-06-06 04:02:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_user` (`user_id`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buku_favorites`
--
ALTER TABLE `buku_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fav` (`buku_id`,`user_id`),
  ADD KEY `fk_favorites_user` (`user_id`);

--
-- Indexes for table `buku_likes`
--
ALTER TABLE `buku_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_like` (`buku_id`,`user_id`),
  ADD KEY `fk_likes_user` (`user_id`);

--
-- Indexes for table `buku_ratings`
--
ALTER TABLE `buku_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_buku` (`user_id`,`buku_id`),
  ADD KEY `fk_ratings_buku` (`buku_id`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_peminjaman_buku` (`buku_id`),
  ADD KEY `fk_peminjaman_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_profile`
--
ALTER TABLE `admin_profile`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `buku_favorites`
--
ALTER TABLE `buku_favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `buku_likes`
--
ALTER TABLE `buku_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `buku_ratings`
--
ALTER TABLE `buku_ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD CONSTRAINT `fk_admin_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `buku_favorites`
--
ALTER TABLE `buku_favorites`
  ADD CONSTRAINT `fk_favorites_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `buku_likes`
--
ALTER TABLE `buku_likes`
  ADD CONSTRAINT `fk_likes_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `buku_ratings`
--
ALTER TABLE `buku_ratings`
  ADD CONSTRAINT `fk_ratings_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ratings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_peminjaman_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
