-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2023 at 04:14 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jetstream_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `bahan`
--

CREATE TABLE `bahan` (
  `id_bahan` int(10) UNSIGNED NOT NULL,
  `nama_bahan` varchar(255) NOT NULL,
  `harga_beli` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bahan`
--

INSERT INTO `bahan` (`id_bahan`, `nama_bahan`, `harga_beli`, `stok`, `satuan`, `created_at`, `updated_at`) VALUES
(1, 'Beras', 12500, 8, 'Kg', '2023-01-09 19:58:20', '2023-01-19 04:48:39'),
(2, 'Gula', 13500, 0, 'Kg', '2023-01-09 20:00:27', '2023-01-19 04:17:40'),
(3, 'Minyak', 18500, 10, 'Liter', '2023-01-10 02:22:35', '2023-01-19 04:48:39'),
(6, 'BonCabe', 1500, 0, 'Pcs', '2023-01-10 02:24:22', '2023-01-19 02:19:40'),
(7, 'Garam', 3000, 0, 'Pcs', '2023-01-11 22:02:07', '2023-01-19 04:17:40');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id_category` int(10) UNSIGNED NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id_category`, `nama_kategori`, `created_at`, `updated_at`) VALUES
(1, 'Makanan', '2023-01-05 02:10:09', '2023-01-05 04:14:39'),
(2, 'Minuman', '2023-01-05 02:13:25', '2023-01-05 04:12:58'),
(3, 'Snack', '2023-01-05 04:14:43', '2023-01-05 04:14:43'),
(9, 'Packaging', '2023-01-19 04:21:18', '2023-01-19 04:21:18');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(6, '2020_05_21_100000_create_teams_table', 1),
(7, '2020_05_21_200000_create_team_user_table', 1),
(8, '2020_05_21_300000_create_team_invitations_table', 1),
(10, '2014_10_12_000000_create_users_table', 2),
(11, '2014_10_12_100000_create_password_resets_table', 2),
(12, '2014_10_12_200000_add_two_factor_columns_to_users_table', 2),
(13, '2019_08_19_000000_create_failed_jobs_table', 2),
(14, '2019_12_14_000001_create_personal_access_tokens_table', 2),
(15, '2022_12_27_031738_create_category_table', 2),
(16, '2022_12_27_032028_create_produk_table', 2),
(17, '2022_12_27_032454_create_supplier_table', 2),
(18, '2022_12_27_032732_create_pembelian_table', 2),
(19, '2022_12_27_033011_create_pembeliandetail_table', 2),
(20, '2022_12_27_033115_create_penjualan_table', 2),
(21, '2022_12_27_033125_create_penjualandetail_table', 2),
(22, '2022_12_27_033208_create_setting_table', 2),
(23, '2022_12_27_034936_create_pengeluaran_table', 2),
(24, '2023_01_03_142838_create_sessions_table', 2),
(25, '2023_01_08_071314_tambah_foreign_key_to_produk_table', 3),
(26, '2023_01_08_140143_create_bahan_table', 4),
(27, '2023_01_10_090452_create_pembeliandetail_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembelian`
--

CREATE TABLE `pembelian` (
  `id_pembelian` int(10) UNSIGNED NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `total_item` int(11) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `diskon` int(11) NOT NULL DEFAULT 0,
  `bayar` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembelian`
--

INSERT INTO `pembelian` (`id_pembelian`, `id_supplier`, `total_item`, `total_harga`, `diskon`, `bayar`, `created_at`, `updated_at`) VALUES
(43, 2, 9, 142500, 0, 142500, '2023-01-19 04:46:19', '2023-01-19 04:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `pembeliandetail`
--

CREATE TABLE `pembeliandetail` (
  `id_pembelian_detail` int(10) UNSIGNED NOT NULL,
  `id_pembelian` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `harga_beli` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembeliandetail`
--

INSERT INTO `pembeliandetail` (`id_pembelian_detail`, `id_pembelian`, `id_bahan`, `harga_beli`, `jumlah`, `subtotal`, `created_at`, `updated_at`) VALUES
(65, 42, 3, 18500, 4, 74000, '2023-01-19 04:33:56', '2023-01-19 04:34:42'),
(67, 42, 2, 13500, 1, 13500, '2023-01-19 04:34:11', '2023-01-19 04:34:11'),
(69, 43, 1, 12500, 4, 50000, '2023-01-19 04:46:51', '2023-01-19 04:47:10'),
(70, 43, 3, 18500, 5, 92500, '2023-01-19 04:47:20', '2023-01-19 04:47:24');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(10) UNSIGNED NOT NULL,
  `deskripsi` text NOT NULL,
  `nominal` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `metode` varchar(20) NOT NULL DEFAULT 'Tunai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`id_pengeluaran`, `deskripsi`, `nominal`, `created_at`, `updated_at`, `metode`) VALUES
(2, 'Bayar Wifi Bulanan', 120000, '2023-01-11 21:57:29', '2023-01-21 15:25:03', 'Tunai'),
(7, 'Bahan Bebek', 20000, '2023-01-22 02:34:55', '2023-01-22 02:34:55', 'Tunai'),
(8, 'Salah Pesanan - Tata', 5000, '2023-01-22 04:24:31', '2023-01-22 04:24:31', 'Tunai'),
(9, 'Sedotan', 10000, '2023-01-23 08:59:03', '2023-01-23 08:59:08', 'Debit'),
(10, 'Beli Kaos Pegawai', 300000, '2023-01-23 09:11:01', '2023-01-23 09:11:01', 'Tunai'),
(11, 'Pembayaran Listrik', 100000, '2023-01-23 11:25:35', '2023-01-23 11:25:35', 'Debit'),
(12, 'Bayar Gaji Pegawai', 200000, '2023-01-23 13:01:39', '2023-01-23 13:01:39', 'Debit'),
(13, 'Beli Ayam', 50000, '2023-01-26 13:02:36', '2023-01-26 13:02:36', 'Tunai'),
(14, 'Belanja Bebek', 100000, '2023-01-27 13:57:50', '2023-01-27 13:57:50', 'Tunai');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(10) UNSIGNED NOT NULL,
  `metode` varchar(20) DEFAULT NULL,
  `total_item` int(11) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `diskon` int(11) NOT NULL DEFAULT 0,
  `bayar` int(11) NOT NULL DEFAULT 0,
  `diterima` int(11) NOT NULL DEFAULT 0,
  `id_user` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `metode`, `total_item`, `total_harga`, `diskon`, `bayar`, `diterima`, `id_user`, `created_at`, `updated_at`) VALUES
(37, 'Tunai', 1, 2000, 0, 2000, 5000, 1, '2023-01-21 05:49:13', '2023-01-21 06:05:24'),
(40, 'Tunai', 3, 27000, 0, 27000, 50000, 1, '2023-01-21 07:11:00', '2023-01-21 07:21:52'),
(41, 'Debit', 1, 2000, 0, 2000, 5000, 1, '2023-01-21 07:28:00', '2023-01-21 07:28:22'),
(42, 'Tunai', 1, 17000, 0, 17000, 20000, 1, '2023-01-21 11:48:00', '2023-01-21 11:48:15'),
(43, 'Tunai', 1, 10000, 0, 10000, 15000, 1, '2023-01-21 11:48:33', '2023-01-21 11:49:04'),
(46, 'Tunai', 8, 68000, 0, 68000, 70000, 1, '2023-01-21 14:37:39', '2023-01-21 15:24:04'),
(56, 'Debit', 8, 148000, 30, 103600, 103600, 1, '2023-01-23 07:34:55', '2023-01-23 07:37:13'),
(57, 'Debit', 10, 185000, 0, 185000, 200000, 1, '2023-01-23 09:00:11', '2023-01-23 09:00:37'),
(58, 'Debit', 52, 740000, 0, 740000, 740000, 1, '2023-01-23 09:09:19', '2023-01-23 09:10:00'),
(60, 'Tunai', 9, 126000, 0, 126000, 130000, 1, '2023-01-23 11:26:32', '2023-01-23 11:29:28'),
(61, 'Debit', 4, 44000, 0, 44000, 80000, 1, '2023-01-23 13:01:42', '2023-01-23 13:02:09'),
(63, 'Tunai', 20, 235000, 0, 235000, 240000, 1, '2023-01-24 02:26:42', '2023-01-24 02:30:14'),
(64, 'Tunai', 3, 27000, 0, 27000, 30000, 1, '2023-01-24 02:32:42', '2023-01-24 02:33:35'),
(65, 'Debit', 3, 35000, 0, 35000, 35000, 1, '2023-01-24 02:33:51', '2023-01-24 02:34:23'),
(66, 'Tunai', 4, 50000, 0, 50000, 100000, 1, '2023-01-24 04:43:49', '2023-01-24 04:44:26'),
(67, 'Tunai', 2, 22000, 0, 22000, 25000, 1, '2023-01-25 00:43:24', '2023-01-25 00:43:41'),
(68, 'Tunai', 3, 51000, 0, 51000, 55000, 1, '2023-01-25 00:52:16', '2023-01-25 00:52:33'),
(69, 'Debit', 3, 35000, 0, 35000, 35000, 1, '2023-01-25 06:18:51', '2023-01-25 06:19:12'),
(70, 'Tunai', 4, 47000, 0, 47000, 50000, 3, '2023-01-25 06:55:34', '2023-01-25 06:56:05'),
(71, 'Debit', 5, 100000, 0, 100000, 100000, 3, '2023-01-25 06:56:16', '2023-01-25 06:56:38'),
(74, 'Tunai', 3, 35000, 0, 35000, 40000, 4, '2023-01-25 10:47:38', '2023-01-25 10:48:00'),
(76, 'Tunai', 8, 88000, 0, 88000, 88000, 1, '2023-01-25 10:57:42', '2023-01-25 10:58:09'),
(78, 'Tunai', 1, 17000, 0, 17000, 17000, 1, '2023-01-25 14:57:46', '2023-01-25 14:57:56'),
(79, 'Tunai', 4, 34000, 0, 34000, 35000, 1, '2023-01-26 01:46:31', '2023-01-26 01:47:02'),
(80, 'Debit', 7, 69000, 10, 62100, 62100, 1, '2023-01-26 01:53:33', '2023-01-26 01:54:20'),
(81, 'Tunai', 4, 80000, 0, 80000, 100000, 1, '2023-01-26 12:05:52', '2023-01-26 12:06:17'),
(82, 'Tunai', 2, 25000, 0, 25000, 50000, 2, '2023-01-27 11:33:34', '2023-01-27 11:33:56'),
(84, 'Tunai', 6, 84000, 0, 84000, 84000, 1, '2023-01-27 14:01:42', '2023-01-27 14:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `penjualandetail`
--

CREATE TABLE `penjualandetail` (
  `id_penjualan_detail` int(10) UNSIGNED NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `jumlah` int(11) DEFAULT 1,
  `subtotal` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penjualandetail`
--

INSERT INTO `penjualandetail` (`id_penjualan_detail`, `id_penjualan`, `id_produk`, `harga_jual`, `jumlah`, `subtotal`, `created_at`, `updated_at`) VALUES
(85, 37, 8, 2000, 1, 2000, '2023-01-21 05:49:39', '2023-01-21 05:49:39'),
(86, 40, 2, 5000, 1, 5000, '2023-01-21 07:20:01', '2023-01-21 07:20:01'),
(87, 40, 8, 2000, 1, 2000, '2023-01-21 07:21:01', '2023-01-21 07:21:01'),
(88, 40, 1, 20000, 1, 20000, '2023-01-21 07:21:35', '2023-01-21 07:21:35'),
(89, 41, 8, 2000, 1, 2000, '2023-01-21 07:28:06', '2023-01-21 07:28:06'),
(90, 42, 4, 17000, 1, 17000, '2023-01-21 11:48:06', '2023-01-21 11:48:06'),
(91, 43, 3, 10000, 1, 10000, '2023-01-21 11:48:56', '2023-01-21 11:48:56'),
(97, 46, 2, 5000, 4, 20000, '2023-01-21 15:17:51', '2023-01-21 15:17:59'),
(98, 46, 9, 12000, 4, 48000, '2023-01-21 15:18:05', '2023-01-21 15:23:51'),
(113, 56, 4, 17000, 3, 51000, '2023-01-23 07:35:30', '2023-01-23 07:36:33'),
(114, 56, 4, 17000, 1, 17000, '2023-01-23 07:35:34', '2023-01-23 07:36:41'),
(115, 56, 1, 20000, 4, 80000, '2023-01-23 07:35:37', '2023-01-23 07:36:39'),
(116, 57, 4, 17000, 5, 85000, '2023-01-23 09:00:15', '2023-01-23 09:00:27'),
(117, 57, 1, 20000, 5, 100000, '2023-01-23 09:00:19', '2023-01-23 09:00:24'),
(118, 58, 3, 10000, 14, 140000, '2023-01-23 09:09:22', '2023-01-23 09:09:25'),
(119, 58, 1, 20000, 18, 360000, '2023-01-23 09:09:29', '2023-01-23 09:09:32'),
(120, 58, 9, 12000, 20, 240000, '2023-01-23 09:09:36', '2023-01-23 09:09:42'),
(121, 60, 1, 20000, 3, 60000, '2023-01-23 11:27:11', '2023-01-23 11:27:30'),
(122, 60, 4, 17000, 3, 51000, '2023-01-23 11:27:15', '2023-01-23 11:27:40'),
(123, 60, 5, 5000, 3, 15000, '2023-01-23 11:27:18', '2023-01-23 11:27:42'),
(124, 61, 4, 17000, 1, 17000, '2023-01-23 13:01:46', '2023-01-23 13:01:46'),
(125, 61, 3, 10000, 1, 10000, '2023-01-23 13:01:50', '2023-01-23 13:01:50'),
(126, 61, 9, 12000, 1, 12000, '2023-01-23 13:01:53', '2023-01-23 13:01:53'),
(127, 61, 2, 5000, 1, 5000, '2023-01-23 13:01:58', '2023-01-23 13:01:58'),
(128, 63, 1, 20000, 5, 100000, '2023-01-24 02:26:46', '2023-01-24 02:26:48'),
(129, 63, 3, 10000, 5, 50000, '2023-01-24 02:26:53', '2023-01-24 02:26:55'),
(130, 63, 5, 5000, 5, 25000, '2023-01-24 02:27:18', '2023-01-24 02:27:20'),
(131, 63, 9, 12000, 5, 60000, '2023-01-24 02:27:26', '2023-01-24 02:27:29'),
(133, 64, 1, 20000, 1, 20000, '2023-01-24 02:33:04', '2023-01-24 02:33:04'),
(134, 64, 5, 5000, 1, 5000, '2023-01-24 02:33:08', '2023-01-24 02:33:08'),
(135, 64, 8, 2000, 1, 2000, '2023-01-24 02:33:17', '2023-01-24 02:33:17'),
(136, 65, 3, 10000, 1, 10000, '2023-01-24 02:33:59', '2023-01-24 02:33:59'),
(137, 65, 5, 5000, 1, 5000, '2023-01-24 02:34:02', '2023-01-24 02:34:02'),
(138, 65, 1, 20000, 1, 20000, '2023-01-24 02:34:05', '2023-01-24 02:34:05'),
(139, 66, 2, 5000, 2, 10000, '2023-01-24 04:43:58', '2023-01-24 04:44:00'),
(140, 66, 1, 20000, 2, 40000, '2023-01-24 04:44:03', '2023-01-24 04:44:05'),
(141, 67, 8, 2000, 1, 2000, '2023-01-25 00:43:27', '2023-01-25 00:43:27'),
(142, 67, 1, 20000, 1, 20000, '2023-01-25 00:43:34', '2023-01-25 00:43:34'),
(143, 68, 4, 17000, 3, 51000, '2023-01-25 00:52:20', '2023-01-25 00:52:23'),
(144, 69, 3, 10000, 1, 10000, '2023-01-25 06:18:54', '2023-01-25 06:18:54'),
(145, 69, 1, 20000, 1, 20000, '2023-01-25 06:18:59', '2023-01-25 06:18:59'),
(146, 69, 2, 5000, 1, 5000, '2023-01-25 06:19:02', '2023-01-25 06:19:02'),
(147, 70, 4, 17000, 1, 17000, '2023-01-25 06:55:40', '2023-01-25 06:55:40'),
(148, 70, 1, 20000, 1, 20000, '2023-01-25 06:55:43', '2023-01-25 06:55:43'),
(149, 70, 2, 5000, 2, 10000, '2023-01-25 06:55:47', '2023-01-25 06:55:49'),
(150, 71, 1, 20000, 5, 100000, '2023-01-25 06:56:25', '2023-01-25 06:56:28'),
(151, 74, 1, 20000, 1, 20000, '2023-01-25 10:47:45', '2023-01-25 10:47:45'),
(152, 74, 3, 10000, 1, 10000, '2023-01-25 10:47:47', '2023-01-25 10:47:47'),
(153, 74, 2, 5000, 1, 5000, '2023-01-25 10:47:50', '2023-01-25 10:47:50'),
(154, 76, 2, 5000, 4, 20000, '2023-01-25 10:57:48', '2023-01-25 10:57:52'),
(155, 76, 4, 17000, 4, 68000, '2023-01-25 10:57:55', '2023-01-25 10:57:58'),
(158, 78, 4, 17000, 1, 17000, '2023-01-25 14:57:50', '2023-01-25 14:57:50'),
(159, 79, 4, 17000, 1, 17000, '2023-01-26 01:46:39', '2023-01-26 01:46:39'),
(160, 79, 3, 10000, 1, 10000, '2023-01-26 01:46:43', '2023-01-26 01:46:43'),
(161, 79, 8, 2000, 1, 2000, '2023-01-26 01:46:49', '2023-01-26 01:46:49'),
(162, 79, 5, 5000, 1, 5000, '2023-01-26 01:46:53', '2023-01-26 01:46:53'),
(163, 80, 4, 17000, 1, 17000, '2023-01-26 01:53:38', '2023-01-26 01:53:38'),
(164, 80, 1, 20000, 1, 20000, '2023-01-26 01:53:41', '2023-01-26 01:53:41'),
(165, 80, 9, 12000, 1, 12000, '2023-01-26 01:53:45', '2023-01-26 01:53:45'),
(166, 80, 5, 5000, 4, 20000, '2023-01-26 01:53:53', '2023-01-26 01:53:56'),
(167, 81, 1, 20000, 4, 80000, '2023-01-26 12:05:56', '2023-01-26 12:05:58'),
(168, 82, 2, 5000, 1, 5000, '2023-01-27 11:33:40', '2023-01-27 11:33:40'),
(169, 82, 1, 20000, 1, 20000, '2023-01-27 11:33:45', '2023-01-27 11:33:45'),
(170, 84, 4, 17000, 2, 34000, '2023-01-27 14:03:41', '2023-01-27 14:08:46'),
(171, 84, 1, 20000, 2, 40000, '2023-01-27 14:04:32', '2023-01-27 14:04:36'),
(172, 84, 5, 5000, 2, 10000, '2023-01-27 14:04:46', '2023-01-27 14:04:48');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(10) UNSIGNED NOT NULL,
  `id_category` int(10) UNSIGNED NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_category`, `nama_produk`, `harga_jual`, `stok`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nasi Bebek Endul', 20000, 90, '2023-01-08 07:59:16', '2023-01-28 01:41:27'),
(2, 2, 'Es Jeruk', 5000, 79, '2023-01-08 08:45:19', '2023-01-27 11:33:56'),
(3, 3, 'Mendoan', 10000, 53, '2023-01-08 08:48:38', '2023-01-26 01:47:03'),
(4, 1, 'Nasi Ayam Endul', 17000, 66, '2023-01-08 08:50:07', '2023-01-27 14:49:57'),
(5, 2, 'Es Teh', 5000, 142, '2023-01-08 08:52:06', '2023-01-27 14:49:57'),
(8, 9, 'Kotak', 2000, 93, '2023-01-19 04:24:08', '2023-01-28 01:41:27'),
(9, 3, 'Pisang Crispy', 12000, 66, '2023-01-19 04:42:01', '2023-01-26 01:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4btJw10769Iyu9pMfWQC81mMwUPu7zFqsir10zYA', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiY3c3bjVHMmJmZHF6bVN2MkpZUHBGdlF0TDh3a1NGN0RBWFVTMmYwQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly9sb2NhbGhvc3QvcHJvamVjdF9jYWZlL3B1YmxpYy9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTI6ImlkX3Blbmp1YWxhbiI7aTo4OTt9', 1674872626);

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE `setting` (
  `id_setting` int(10) UNSIGNED NOT NULL,
  `nama_usaha` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `kota` varchar(20) DEFAULT NULL,
  `telepon` varchar(255) NOT NULL,
  `path_logo` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`id_setting`, `nama_usaha`, `alamat`, `kota`, `telepon`, `path_logo`, `created_at`, `updated_at`) VALUES
(1, 'Warung Altari', 'Ruko Natura Residences EE-9', 'Sidoarjo', '085778121249', '/img/altari.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(10) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama`, `alamat`, `telepon`, `created_at`, `updated_at`) VALUES
(1, 'Joko', 'Bakul Bebek', '085778121249', '2023-01-10 01:30:39', '2023-01-19 04:27:23'),
(2, 'Imam Wahyudi', 'Toko Sembako', '08778121239', '2023-01-11 21:54:44', '2023-01-19 04:30:15'),
(3, 'Mas Umar', 'Bakul Beras', '090190910', '2023-01-19 02:17:25', '2023-01-19 04:27:12');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `personal_team` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `user_id`, `name`, `personal_team`, `created_at`, `updated_at`) VALUES
(1, 1, 'masjuve\'s Team', 1, '2023-01-03 07:31:40', '2023-01-03 07:31:40');

-- --------------------------------------------------------

--
-- Table structure for table `team_invitations`
--

CREATE TABLE `team_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_user`
--

CREATE TABLE `team_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `level` tinyint(4) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `foto`, `level`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'administrator@altari.com', NULL, '$2y$10$pDQuRhlyUGIYMiT2jcPoq.KMRUYDgb8fTn70fXefxUSpZb2fRdOYO', NULL, NULL, NULL, '/img/logo-20230125135220.png', 1, NULL, '2023-01-03 08:21:12', '2023-01-25 06:52:20'),
(2, 'Tata', 'tata@altari.com', NULL, '$2y$10$XiNNA7rSmi5KstmrEaRFQeohpN4c1l/KJro6pM3xugm5H/T4o0SBq', NULL, NULL, NULL, '/img/logo-20230127193747.png', 2, NULL, '2023-01-25 06:32:33', '2023-01-27 12:37:47'),
(3, 'Mas Adi', 'masadi@altari.com', NULL, '$2y$10$L1Hng0uFcrMuGp61//.OvuQwnSiDGIvK6CxOF0EewsKmJd436gqEa', NULL, NULL, NULL, '/img/logo-20230125135525.png', 2, NULL, '2023-01-25 06:32:57', '2023-01-25 06:55:25'),
(4, 'Juve', 'juve@gmail.com', NULL, '$2y$10$D.B2L5HuPw75SH103NXhM.VvE7J/XuaFlBCQnBy1eZ/gW5ZgkVi5K', NULL, NULL, NULL, '/img/logo-20230125140255.png', 2, NULL, '2023-01-25 07:02:03', '2023-01-25 07:02:55'),
(5, 'Toni', 'toni@altari.com', NULL, '$2y$10$3XtfE3GvylxLo336InbGUOKvRlrI7Ikczf.8cEOrJ20EA4jythiwe', NULL, NULL, NULL, '/img/person.jpg', 2, NULL, '2023-01-27 14:14:22', '2023-01-27 14:14:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bahan`
--
ALTER TABLE `bahan`
  ADD PRIMARY KEY (`id_bahan`),
  ADD UNIQUE KEY `bahan_nama_bahan_unique` (`nama_bahan`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id_category`),
  ADD UNIQUE KEY `category_nama_kategori_unique` (`nama_kategori`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id_pembelian`);

--
-- Indexes for table `pembeliandetail`
--
ALTER TABLE `pembeliandetail`
  ADD PRIMARY KEY (`id_pembelian_detail`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`);

--
-- Indexes for table `penjualandetail`
--
ALTER TABLE `penjualandetail`
  ADD PRIMARY KEY (`id_penjualan_detail`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `produk_nama_produk_unique` (`nama_produk`),
  ADD KEY `produk_id_category_foreign` (`id_category`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `setting`
--
ALTER TABLE `setting`
  ADD PRIMARY KEY (`id_setting`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teams_user_id_index` (`user_id`);

--
-- Indexes for table `team_invitations`
--
ALTER TABLE `team_invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_invitations_team_id_email_unique` (`team_id`,`email`);

--
-- Indexes for table `team_user`
--
ALTER TABLE `team_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_user_team_id_user_id_unique` (`team_id`,`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bahan`
--
ALTER TABLE `bahan`
  MODIFY `id_bahan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id_category` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id_pembelian` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `pembeliandetail`
--
ALTER TABLE `pembeliandetail`
  MODIFY `id_pembelian_detail` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `penjualandetail`
--
ALTER TABLE `penjualandetail`
  MODIFY `id_penjualan_detail` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `setting`
--
ALTER TABLE `setting`
  MODIFY `id_setting` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_invitations`
--
ALTER TABLE `team_invitations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_user`
--
ALTER TABLE `team_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_id_category_foreign` FOREIGN KEY (`id_category`) REFERENCES `category` (`id_category`);

--
-- Constraints for table `team_invitations`
--
ALTER TABLE `team_invitations`
  ADD CONSTRAINT `team_invitations_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
