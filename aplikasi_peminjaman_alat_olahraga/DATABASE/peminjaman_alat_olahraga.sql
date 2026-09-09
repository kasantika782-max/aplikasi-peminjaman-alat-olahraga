-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Sep 2026 pada 02.52
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
-- Database: `peminjaman_alat_olahraga`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alat`
--

CREATE TABLE `alat` (
  `id_alat` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_alat` varchar(70) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alat`
--

INSERT INTO `alat` (`id_alat`, `id_kategori`, `nama_alat`, `stok`) VALUES
(1, 1, 'Bola sepak bola', 10),
(2, 1, 'Rompi Latihan Sepak Bola', 20),
(3, 1, 'Cone Latihan', 30),
(4, 2, 'Bet Tenis Meja', 10),
(5, 2, 'Bola Tenis Meja', 50),
(6, 2, 'Net Tenis Meja', 5),
(7, 3, 'Bola Voli', 10),
(8, 3, 'Net Voli', 5),
(9, 3, 'Peluit Wasit', 10),
(10, 4, 'Bola Basket', 10),
(11, 4, 'Ring Basket', 0),
(12, 4, 'Rompi Latihan Basket', 20),
(13, 5, 'Raket Bulu Tangkis', 15),
(14, 5, 'Shuttlecock', 50),
(15, 5, 'Net Bulu Tangkis', 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `keterangan_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `keterangan_kategori`) VALUES
(1, 'Sepak Bola', 'Peralatan yang digunakan untuk kegiatan sepak bola'),
(2, 'Tenis Meja', 'Peralatan yang digunakan untuk kegiatan tenis meja'),
(3, 'Voli', 'Peralatan yang digunakan untuk kegiatan bola voli'),
(4, 'Basket', 'Peralatan yang digunakan untuk kegiatan bola basket'),
(5, 'Bulu Tangkis', 'Peralatan yang digunakan untuk kegiatan bulu tangkis');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aksi` varchar(255) NOT NULL,
  `waktu` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `id_user`, `aksi`, `waktu`) VALUES
(1, 1, 'Admin mengubah data peminjaman ID: 1', '2026-04-23 05:39:01'),
(2, 7, 'User melakukan request pinjam alat baru', '2026-04-23 05:46:59'),
(3, 2, 'Petugas menyetujui peminjaman ID: 2', '2026-04-23 05:49:41'),
(4, 2, 'Petugas mengonfirmasi pengembalian alat ID: 2', '2026-04-23 05:49:51'),
(5, 7, 'User login ke sistem', '2026-04-24 07:58:22'),
(6, 14, 'User melakukan request pinjam alat baru', '2026-05-07 00:37:28'),
(7, 2, 'Petugas menyetujui peminjaman ID: 5', '2026-05-12 11:06:43'),
(8, 2, 'Petugas mengonfirmasi pengembalian alat ID: 5', '2026-05-13 04:00:29'),
(9, 23, 'User login ke sistem', '2026-07-29 10:19:56'),
(10, 23, 'User melakukan request pinjam alat baru', '2026-07-29 11:12:25'),
(11, 2, 'Petugas menyetujui peminjaman ID: 6', '2026-07-29 11:12:44'),
(12, 45, 'User login ke sistem', '2026-08-02 00:46:46'),
(13, 45, 'User melakukan request pinjam alat baru', '2026-08-02 01:08:22'),
(14, 7, 'User melakukan request pinjam alat baru', '2026-08-02 10:53:26'),
(15, 2, 'Petugas menyetujui peminjaman ID: 10', '2026-08-02 10:53:52'),
(16, 1, 'User login ke sistem', '2026-09-08 02:02:11'),
(17, 14, 'User login ke sistem', '2026-09-08 02:18:18'),
(18, 14, 'User login ke sistem', '2026-09-08 05:30:22'),
(19, 14, 'User login ke sistem', '2026-09-08 06:19:01'),
(20, 14, 'User login ke sistem', '2026-09-09 00:03:28'),
(21, 2, 'User login ke sistem', '2026-09-09 00:04:11'),
(22, 1, 'User login ke sistem', '2026-09-09 00:06:21'),
(23, 2, 'User login ke sistem', '2026-09-09 00:13:18'),
(24, 1, 'User login ke sistem', '2026-09-09 00:26:36'),
(25, 55, 'User login ke sistem', '2026-09-09 00:27:53'),
(26, 2, 'User login ke sistem', '2026-09-09 00:30:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_alat` int(11) DEFAULT NULL,
  `jumlah_pinjam` int(11) DEFAULT NULL,
  `tgl_pinjam` timestamp NULL DEFAULT NULL,
  `tgl_kembali_asli` timestamp NULL DEFAULT NULL,
  `status` enum('pending','dipinjam','kembali') DEFAULT 'pending',
  `kondisi_keluar` varchar(50) DEFAULT 'Bagus',
  `kondisi_masuk` varchar(50) DEFAULT NULL,
  `jaminan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_user`, `id_alat`, `jumlah_pinjam`, `tgl_pinjam`, `tgl_kembali_asli`, `status`, `kondisi_keluar`, `kondisi_masuk`, `jaminan`) VALUES
(1, 7, 1, 1, '2026-04-22 23:10:04', '2026-04-23 17:00:00', 'kembali', 'Bagus', 'Baik', 'ktp'),
(2, 7, 4, 2, '2026-04-23 05:46:59', '2026-04-23 17:00:00', 'kembali', 'Bagus', 'Baik', 'ktp'),
(3, 3, 7, 2, '2026-04-24 05:50:36', '2026-04-25 17:00:00', 'kembali', 'Bagus', 'Baik', 'ktp'),
(4, 14, 10, 1, '2026-05-12 20:57:26', '2026-05-13 17:00:00', 'kembali', 'Bagus', 'Baik', 'ktp'),
(5, 14, 13, 2, '2026-05-13 03:57:26', '2026-05-14 17:00:00', 'kembali', 'Bagus', 'Baik', 'ktp'),
(6, 23, 10, 2, '2026-07-31 17:00:15', '2026-08-01 17:00:00', 'kembali', 'Baik', 'Baik', 'ktp'),
(7, 7, 7, 1, '2026-08-01 17:11:28', '2026-08-01 19:36:47', 'kembali', 'Bagus', 'Baik', 'ktp'),
(8, 45, 1, 2, '2026-08-01 19:17:49', NULL, 'dipinjam', 'Baik', NULL, 'ktp'),
(9, 45, 10, 1, '2026-08-01 19:37:24', NULL, 'pending', 'Bagus', NULL, 'ktp'),
(10, 7, 4, 2, '2026-08-02 03:53:26', NULL, 'dipinjam', 'Bagus', NULL, 'ktp');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','peminjam') DEFAULT NULL,
  `no_hp` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `role`, `no_hp`) VALUES
(1, 'eka', '$2y$12$7gSP.JYklB0kS4Vc992KhO4eT0jhB.3hrlRDqc9Tnis6m4IxRwIGK', 'admin', '12345678'),
(2, 'rizky', '$2y$12$4Zf.13udyZwF7hpj/JYdQeGhJu95NhwxgSe7eP.JYF.TYTfwqoxB2', 'petugas', '1234567890'),
(3, 'rendi R', '$2y$12$ff6xs5kEL2ZuNIcHYBhJq.exrZ5T2CRJx5vYbbeS/bn8siwa2Kmv.', 'peminjam', ''),
(4, 'rezka parsha A', '$2y$10$Y3nz77Ocb3CkhyQwCcyqTekd1qn6R6opBSTHaKb3sUcGQak7kmcNO', 'peminjam', '12345678910'),
(7, 'arif', '$2y$12$dYUkTJaJ9EF.Y7i3wHDYX.Qe1PNNqzxFmE1B91MmuDW6PjjIEwanW', 'peminjam', '+6283836949705'),
(14, 'alfian', '$2y$12$A9Jq7P5jlIUb/C8.hrJejuc7g9jQTS4MSq9Ubz8SAMg03C.FHWCNS', 'peminjam', '1122334455'),
(21, 'gibrin', '$2y$12$KEqXTRlQQOkEnNIMyyhdFOTroY.tUetCCD9znY/d3cqi2Y2Z9PWzW', 'peminjam', ''),
(23, 'hilman', '$2y$12$HqU2dDNH5rEQNzfcGlJ9aOaRhg0QpgHIz6HFq55gxsSxquaFotvGu', 'peminjam', ''),
(29, 'gh', '$2y$12$SHvt7.uPY.3/J5Rq0.wWIuWzJvA4jumBw.q1g/x38wG6067xYzcCG', 'peminjam', ''),
(42, 'dasi', '$2y$12$A1QeHFiRNl365O9xHkb4FOKcMNqHdrzU1Vvc8MraWb8wpkNY9gVBC', 'peminjam', ''),
(45, 'bunda', '$2y$12$yCnmjBWDbGUa8g8g6T9CreOJr7I.oor8IU9ruHbC/bi9SXhP6J9ta', 'peminjam', '083899940333'),
(49, 'dds', '$2y$12$t8C7CkGWTgtxkValuyK4ROOKwkh.WNsN.oND4WwHhq7A3XhxeMTGK', 'peminjam', '1234321'),
(51, 'x', '$2y$12$bxz0v8WylTmZGiQsTJJMdukrH8ss2YvtgLTnMYj/bFdMtR.GbM.kC', 'peminjam', '1234'),
(52, 'sule', '$2y$12$PjRpuAhmBIK0GMv2lyJT8eJVusj03U9xnqpwUyUorZ5o7i/NDUmFe', 'peminjam', '12'),
(53, 'cx', '$2y$12$hX7n2fu4g0u8Cs1DHgCs7uTcZ960XMIf8Ym2LVoPi0Y4XrUGWyRd.', 'peminjam', '1'),
(55, 'rahmat', '$2y$10$ySBWvUPfVGJLZR95qgHiSOu/4cFw.xta0ksvGKhljMP7qCB77Tr8q', 'peminjam', '08123456789');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alat`
--
ALTER TABLE `alat`
  ADD PRIMARY KEY (`id_alat`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_user` (`id_user`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_alat` (`id_alat`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alat`
--
ALTER TABLE `alat`
  MODIFY `id_alat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `alat`
--
ALTER TABLE `alat`
  ADD CONSTRAINT `alat_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id_alat`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
