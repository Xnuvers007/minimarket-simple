-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql111.infinityfree.com
-- Waktu pembuatan: 01 Jan 2026 pada 04.10
-- Versi server: 11.4.9-MariaDB
-- Versi PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40803099_minimarket_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'cancel_order', 'Batalkan order #4: sdadsa', '103.18.34.140', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-01 09:09:40'),
(2, 2, 'confirm_payment', 'Konfirmasi pembayaran order #4, buat transaksi INV-20260101160947-496', '103.18.34.140', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-01 09:09:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `icon`, `status`, `created_at`) VALUES
(1, 'Makanan Ringan', 'Snack dan makanan ringan', 'fa-cookie-bite', 'active', '2026-01-01 08:35:16'),
(2, 'Minuman', 'Minuman kemasan dan segar', 'fa-glass-water', 'active', '2026-01-01 08:35:16'),
(3, 'Sembako', 'Kebutuhan pokok sehari-hari', 'fa-wheat-awn', 'active', '2026-01-01 08:35:16'),
(4, 'Toiletries', 'Perlengkapan mandi dan kebersihan', 'fa-soap', 'active', '2026-01-01 08:35:16'),
(5, 'Elektronik', 'Barang elektronik rumah tangga', 'fa-plug', 'active', '2026-01-01 08:35:16'),
(6, 'Alat Tulis', 'Perlengkapan tulis menulis', 'fa-pen', 'active', '2026-01-01 08:35:16'),
(7, 'Bumbu Dapur', 'Bumbu dan rempah-rempah', 'fa-pepper-hot', 'active', '2026-01-01 08:35:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  `total_amount` decimal(15,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `STATUS` enum('pending','processing','ready','shipped','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `payment_method` enum('cod','transfer','ewallet','qris') DEFAULT 'cod',
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `order_date`, `total_amount`, `shipping_address`, `STATUS`, `payment_status`, `payment_method`, `notes`, `updated_at`) VALUES
(1, 'ORD-20260101-9BE281', 4, '2026-01-01 08:49:13', '7000.00', 'dasdas', 'pending', 'unpaid', 'cod', 'sdad', '2026-01-01 08:49:13'),
(2, 'ORD-20260101-EB77EA', 4, '2026-01-01 08:53:02', '3500.00', 'dsfs', 'pending', 'unpaid', 'cod', 'asdasd', '2026-01-01 08:53:02'),
(3, 'ORD-20260101-B8CD30', 4, '2026-01-01 08:59:07', '3500.00', 'dasd', 'pending', 'unpaid', 'cod', '', '2026-01-01 08:59:07'),
(4, 'ORD-20260101-850B29', 4, '2026-01-01 09:00:24', '3500.00', 'test', 'completed', 'paid', 'cod', '\nDibatalkan: sdadsa', '2026-01-01 09:09:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 8, '', 2, '3500.00', '7000.00'),
(2, 2, 8, '', 1, '3500.00', '3500.00'),
(3, 3, 8, '', 1, '3500.00', '3500.00'),
(4, 4, 8, '', 1, '3500.00', '3500.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 10,
  `unit` varchar(20) DEFAULT 'pcs',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `product_name`, `sku`, `barcode`, `description`, `price`, `cost_price`, `stock`, `min_stock`, `unit`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Indomie Goreng', 'IDM-GRG-001', '8992388101015', 'Mie instan goreng original', '3500.00', '2800.00', 200, 50, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(2, 1, 1, 'Chitato Rasa Sapi Panggang', 'CHT-SPG-001', '8992388105013', 'Keripik kentang rasa sapi panggang 68gr', '10500.00', '8500.00', 150, 30, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(3, 1, 4, 'Biskuat Coklat', 'BSK-CKL-001', '8992388201018', 'Biskuit rasa coklat 120gr', '8500.00', '7000.00', 100, 30, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(4, 1, 4, 'Tango Wafer Coklat', 'TNG-WFR-001', '8992388301012', 'Wafer coklat 176gr', '9500.00', '7800.00', 80, 25, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(5, 2, 5, 'Coca-Cola 390ml', 'CCL-390-001', '8992388401019', 'Minuman berkarbonasi 390ml', '6500.00', '5200.00', 300, 100, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(6, 2, 5, 'Sprite 390ml', 'SPR-390-001', '8992388501016', 'Minuman berkarbonasi rasa lemon 390ml', '6500.00', '5200.00', 250, 80, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(7, 2, 1, 'Teh Botol Sosro 450ml', 'TBS-450-001', '8992388601013', 'Teh kemasan botol 450ml', '5500.00', '4500.00', 400, 120, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(8, 2, 2, 'Air Mineral Aqua 600ml', 'AQA-600-001', '8992388701010', 'Air mineral 600ml', '3500.00', '2800.00', 495, 150, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 09:09:40'),
(9, 3, 1, 'Beras Rojolele 5kg', 'BRS-RJL-005', '8992388801017', 'Beras premium 5kg', '75000.00', '65000.00', 100, 20, 'pack', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(10, 3, 1, 'Minyak Goreng Bimoli 2L', 'MYK-BML-002', '8992388901014', 'Minyak goreng kemasan 2 liter', '35000.00', '30000.00', 80, 25, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(11, 3, 1, 'Gula Pasir Gulaku 1kg', 'GLP-GLK-001', '8992389001018', 'Gula pasir putih 1kg', '15000.00', '12500.00', 150, 40, 'pack', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(12, 3, 1, 'Telur Ayam Negeri (10 butir)', 'TLR-AYN-010', '8992389101015', 'Telur ayam segar 10 butir', '25000.00', '21000.00', 100, 30, 'pack', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(13, 4, 2, 'Sabun Lifebuoy 85gr', 'SBN-LFB-085', '8992389201012', 'Sabun mandi batangan 85gr', '4500.00', '3500.00', 200, 50, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(14, 4, 2, 'Shampo Pantene 170ml', 'SHP-PNT-170', '8992389301019', 'Shampo anti rontok 170ml', '18500.00', '15000.00', 120, 30, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(15, 4, 2, 'Pasta Gigi Pepsodent 190gr', 'PSG-PPD-190', '8992389401016', 'Pasta gigi keluarga 190gr', '12500.00', '10000.00', 150, 40, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(16, 4, 3, 'Detergen Rinso 800gr', 'DTG-RNS-800', '8992389501013', 'Detergen bubuk 800gr', '22000.00', '18000.00', 100, 25, 'pack', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(17, 5, NULL, 'Lampu LED Philips 9W', 'LMP-PHP-009', '8992389601010', 'Lampu LED hemat energi 9 watt', '25000.00', '20000.00', 50, 15, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(18, 5, NULL, 'Baterai ABC AA (4 pcs)', 'BTR-ABC-AA4', '8992389701017', 'Baterai alkaline AA isi 4', '15000.00', '12000.00', 80, 20, 'pack', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(19, 6, NULL, 'Pulpen Standard AE7 Hitam', 'PPN-STD-HT1', '8992389801014', 'Pulpen standar warna hitam', '2500.00', '2000.00', 200, 50, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(20, 6, NULL, 'Buku Tulis Sinar Dunia 58 Lembar', 'BKT-SND-058', '8992389901011', 'Buku tulis folio bergaris 58 lembar', '5000.00', '4000.00', 150, 40, 'pcs', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(21, 7, 1, 'Kecap Manis Bango 220ml', 'KCP-BNG-220', '8992390001015', 'Kecap manis 220ml', '12500.00', '10000.00', 100, 25, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(22, 7, 1, 'Saos Sambal ABC 335ml', 'SOS-ABC-335', '8992390101012', 'Saos sambal pedas 335ml', '15000.00', '12000.00', 90, 25, 'btl', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(23, 7, 1, 'Royco Ayam 100gr', 'RYC-AYM-100', '8992390201019', 'Bumbu penyedap rasa ayam 100gr', '8500.00', '7000.00', 120, 30, 'pack', NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'store_name', 'Minimarket Sejahtera', 'Nama toko', '2026-01-01 08:35:16'),
(2, 'store_address', 'Jl. Raya Sejahtera No. 123, Jakarta', 'Alamat toko', '2026-01-01 08:35:16'),
(3, 'store_phone', '021-12345678', 'Telepon toko', '2026-01-01 08:35:16'),
(4, 'store_email', 'info@minimarket-sejahtera.com', 'Email toko', '2026-01-01 08:35:16'),
(5, 'tax_percentage', '10', 'Persentase pajak (%)', '2026-01-01 08:35:16'),
(6, 'currency', 'IDR', 'Mata uang', '2026-01-01 08:35:16'),
(7, 'receipt_footer', 'Terima kasih atas kunjungan Anda!', 'Footer untuk struk', '2026-01-01 08:35:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_history`
--

CREATE TABLE `stock_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_before` int(11) DEFAULT 0,
  `quantity_change` int(11) NOT NULL,
  `quantity_after` int(11) DEFAULT 0,
  `type` enum('in','out','adjustment','sale','return') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `stock_history`
--

INSERT INTO `stock_history` (`id`, `product_id`, `quantity_before`, `quantity_change`, `quantity_after`, `type`, `reference`, `notes`, `created_by`, `created_at`) VALUES
(1, 8, 0, -1, 0, 'out', 'INV-20260101-6E2B6F', NULL, 2, '2026-01-01 08:47:50'),
(2, 8, 0, -2, 0, 'out', 'ORD-20260101-9BE281', NULL, 4, '2026-01-01 08:49:13'),
(3, 8, 0, -1, 0, 'out', 'ORD-20260101-EB77EA', NULL, 4, '2026-01-01 08:53:02'),
(4, 8, 0, -1, 0, 'out', 'ORD-20260101-B8CD30', NULL, 4, '2026-01-01 08:59:07'),
(5, 8, 0, -1, 0, 'out', 'ORD-20260101-850B29', NULL, 4, '2026-01-01 09:00:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `phone`, `email`, `address`, `status`, `created_at`) VALUES
(1, 'PT Indofood CBP', 'Budi Hartono', '0215551234', 'contact@indofood.com', 'Jl. Jend. Sudirman Kav 76-78, Jakarta Selatan', 'active', '2026-01-01 08:35:16'),
(2, 'PT Unilever Indonesia', 'Siti Nurhaliza', '0215551235', 'contact@unilever.co.id', 'Green Office Park, BSD City, Tangerang', 'active', '2026-01-01 08:35:16'),
(3, 'PT Wings Surya', 'Ahmad Dahlan', '0215551236', 'contact@wings.co.id', 'Jl. Raya Pegangsaan Dua, Kelapa Gading, Jakarta Utara', 'active', '2026-01-01 08:35:16'),
(4, 'PT Mayora Indah', 'Rini Soemarno', '0215551237', 'contact@mayora.com', 'Jl. Tomang Raya No. 21-23, Jakarta Barat', 'active', '2026-01-01 08:35:16'),
(5, 'PT Coca-Cola Indonesia', 'Andi Wijaya', '0215551238', 'contact@coca-cola.co.id', 'Jl. Pulo Lentut No. 3, Kawasan Industri Pulogadung, Jakarta Timur', 'active', '2026-01-01 08:35:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `kasir_id` int(11) NOT NULL,
  `transaction_date` timestamp NULL DEFAULT current_timestamp(),
  `total_amount` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) DEFAULT 0.00,
  `tax` decimal(15,2) DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','debit','credit','ewallet','qris','cod','transfer','other') NOT NULL DEFAULT 'cash',
  `payment_amount` decimal(15,2) DEFAULT NULL,
  `change_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'completed',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `invoice_number`, `customer_id`, `kasir_id`, `transaction_date`, `total_amount`, `discount`, `tax`, `grand_total`, `payment_method`, `payment_amount`, `change_amount`, `status`, `notes`) VALUES
(1, 'INV-20260101-6E2B6F', NULL, 2, '2026-01-01 08:47:50', '3500.00', '0.00', '0.00', '3500.00', 'cash', '5000.00', '1500.00', 'completed', NULL),
(2, 'INV-20260101160947-496', NULL, 2, '2026-01-01 09:09:47', '3500.00', '0.00', '0.00', '3500.00', 'cod', '3500.00', '0.00', 'completed', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_details`
--

CREATE TABLE `transaction_details` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `transaction_details`
--

INSERT INTO `transaction_details` (`id`, `transaction_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 8, '', 1, '3500.00', '3500.00'),
(2, 2, 8, '', 1, '3500.00', '3500.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','kasir','customer') NOT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `address`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@minimarket.com', '081234567890', 'admin', NULL, NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(2, 'kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Satu', 'kasir1@minimarket.com', '081234567891', 'kasir', NULL, NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(3, 'kasir2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Dua', 'kasir2@minimarket.com', '081234567892', 'kasir', NULL, NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(4, 'customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'budi@gmail.com', '081234567893', 'customer', NULL, NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16'),
(5, 'customer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah', 'siti@gmail.com', '081234567894', 'customer', NULL, NULL, 'active', '2026-01-01 08:35:16', '2026-01-01 08:35:16');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`STATUS`);

--
-- Indeks untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeks untuk tabel `stock_history`
--
ALTER TABLE `stock_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `kasir_id` (`kasir_id`);

--
-- Indeks untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `stock_history`
--
ALTER TABLE `stock_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `stock_history`
--
ALTER TABLE `stock_history`
  ADD CONSTRAINT `stock_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `transaction_details_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
