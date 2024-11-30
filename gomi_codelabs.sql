-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Nov 2024 pada 22.05
-- Versi server: 10.4.24-MariaDB
-- Versi PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gomi_codelabs`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_sampah`
--

CREATE TABLE `data_sampah` (
  `id` int(11) NOT NULL,
  `kategori_sampah` enum('Plastik','Kaleng','Kaca') NOT NULL,
  `poin_per_kg` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `data_sampah`
--

INSERT INTO `data_sampah` (`id`, `kategori_sampah`, `poin_per_kg`) VALUES
(1, 'Plastik', 5),
(2, 'Kaleng', 10),
(3, 'Kaca', 15);

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_user`
--

CREATE TABLE `data_user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `point` int(11) DEFAULT 0,
  `sampah_terkumpul` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `data_user`
--

INSERT INTO `data_user` (`id`, `nama`, `email`, `password`, `point`, `sampah_terkumpul`) VALUES
(1, 'MUHAMMAD IKHSAN FADILLAH', 'ikhsangrowtopia5@gmail.com', '$2y$10$j7QDmOxtztI7PW6z070LWuIg6vOKpkiUq0DpVMV6W8ASjcrNqKHG.', 472, 151),
(3, 'IkhsannDUA', 'ikhsanf103@gmail.com', '$2y$10$7qR2g720tFRy.pX8HuNZw.Hvi2j6OYCwP2fsSRW5aSUxXCmzfS.K6', 439, 63);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('uang','hadiah') DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `points_used` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `user_id`, `type`, `value`, `points_used`, `created_at`) VALUES
(1, 1, 'uang', '50000', 100, '2024-11-29 00:41:17'),
(2, 1, 'uang', '50000', 100, '2024-11-29 00:41:43'),
(3, 1, 'uang', '50000', 100, '2024-11-29 00:42:58'),
(4, 1, 'uang', '50000', 100, '2024-11-29 13:18:17'),
(5, 1, 'uang', '150000', 250, '2024-11-29 13:19:09'),
(6, 1, 'hadiah', '100000', 150, '2024-11-29 13:21:15'),
(7, 1, 'uang', '150000', 250, '2024-11-29 13:25:26'),
(8, 1, 'hadiah', '100000', 150, '2024-11-29 13:28:15'),
(9, 1, 'uang', '50000', 100, '2024-11-30 03:32:54'),
(10, 1, 'uang', '100000', 180, '2024-11-30 13:02:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_sampah`
--

CREATE TABLE `transaksi_sampah` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sampah_id` int(11) NOT NULL,
  `berat_kg` float NOT NULL,
  `poin_ditambahkan` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `transaksi_sampah`
--

INSERT INTO `transaksi_sampah` (`id`, `user_id`, `sampah_id`, `berat_kg`, `poin_ditambahkan`, `created_at`) VALUES
(1, 1, 1, 10, 51, '2024-11-30 00:37:40'),
(2, 1, 1, 10, 51, '2024-11-30 00:46:31'),
(3, 1, 1, 34, 171, '2024-11-30 00:46:57'),
(4, 1, 1, 39, 198, '2024-11-30 00:50:50'),
(5, 3, 2, 21, 216, '2024-11-30 00:54:11'),
(6, 3, 1, 10, 54, '2024-11-30 00:59:24'),
(7, 3, 1, 10, 54, '2024-11-30 01:01:15'),
(8, 1, 1, 7, 40, '2024-11-30 01:04:48'),
(9, 1, 1, 14, 70, '2024-11-30 01:09:31'),
(10, 3, 1, 11, 58, '2024-11-30 01:10:19'),
(11, 1, 2, 17, 172, '2024-11-30 01:11:58'),
(12, 3, 1, 11, 57, '2024-11-30 13:00:07');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_sampah`
--
ALTER TABLE `data_sampah`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_user`
--
ALTER TABLE `data_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `transaksi_sampah`
--
ALTER TABLE `transaksi_sampah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sampah_id` (`sampah_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `data_sampah`
--
ALTER TABLE `data_sampah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `data_user`
--
ALTER TABLE `data_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `transaksi_sampah`
--
ALTER TABLE `transaksi_sampah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `data_user` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi_sampah`
--
ALTER TABLE `transaksi_sampah`
  ADD CONSTRAINT `transaksi_sampah_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `data_user` (`id`),
  ADD CONSTRAINT `transaksi_sampah_ibfk_2` FOREIGN KEY (`sampah_id`) REFERENCES `data_sampah` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
