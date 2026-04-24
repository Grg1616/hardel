-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 12:34 PM
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
-- Database: `hardel`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `customer_id`, `product_id`, `quantity`, `price`, `added_at`) VALUES
(1, 1, 18, 1, 2300.00, '2025-05-05 07:42:00'),
(2, 6, 21, 1, 3200.00, '2025-10-19 14:23:04'),
(3, 6, 27, 1, 99.00, '2025-10-19 14:23:09'),
(4, 6, 22, 1, 11.00, '2025-10-19 14:23:21'),
(5, 6, 18, 1, 2300.00, '2025-10-19 14:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `municipalities` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `street` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `user_id`, `name`, `phone`, `email`, `province`, `municipalities`, `barangay`, `street`, `latitude`, `longitude`) VALUES
(1, 1, 'Greg', '09213961890', NULL, 'Batangas', 'nasugbu', 'Utod', 'Utod Road', 14.1189170, 120.6471820),
(2, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 9, 'Greg Tomco', '09734273784', NULL, 'Batangas', '', 'Baclaran', 'Baclaran Barangay Road', 13.9290440, 120.7729130),
(5, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 11, 'Greg tomco', '09213961890', NULL, 'Batangas', 'Nasugbu', 'Natipuan', 'yanara', 14.1205710, 120.6182020),
(7, 13, 'greg tomco', '09127272773', NULL, 'Batangas', '', 'Bunducan', 'J. P. Laurel Street', 14.0778840, 120.6307870);

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `driver_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `driver_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`driver_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `phone`, `driver_image`) VALUES
(1, 3, 'greg', '', 'tomco', '093894283', 'uploads/1746406670_WIN_20241201_12_46_56_Pro.jpg'),
(2, 0, 'Mark John', '', 'Cabral', '098788234293', ''),
(3, 6, 'vhan', '', 'bonifacio', '0983783', ''),
(4, 0, 'boss', '', 'kila', '09213961890', '');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `order_status` varchar(50) DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'cash_on_delivery',
  `subtotal` decimal(10,2) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `delivery_start` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `driver_id`, `customer_id`, `order_date`, `order_status`, `payment_method`, `subtotal`, `shipping_fee`, `total_amount`, `delivery_start`) VALUES
(1, NULL, 1, '2025-05-05 07:42:11', 'cancelled', 'cod', 1600.00, 50.00, 1650.00, NULL),
(2, 3, 1, '2025-05-05 08:08:45', 'shipped', 'cod', 2300.00, 50.00, 2350.00, NULL),
(3, 1, 6, '2025-10-19 14:30:21', 'shipped', 'cod', 1400.00, 50.00, 1450.00, NULL),
(4, NULL, 6, '2025-10-20 00:48:05', 'Pending', 'cod', 3200.00, 50.00, 3250.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 23, 1, 1600.00),
(2, 2, 18, 1, 2300.00),
(3, 3, 41, 1, 1400.00),
(4, 4, 21, 1, 3200.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock_quantity`, `category`, `product_image`, `created_at`) VALUES
(18, 'Stanley', 'Stanley 18in. Heavy Duty Bolt Cutter', 2300.00, 9, 'Tools', 'uploads/681359cd1c555.avif', '2025-05-01 11:23:57'),
(19, 'Stanley', 'Stanley 30in. Heavy Duty Bolt Cutter', 3700.00, 5, 'Tools', 'uploads/68135a42d2ba7.avif', '2025-05-01 11:25:54'),
(20, 'Polar Bear', 'Polar Bear Hook(Non Residual)HK920C-3\'S/PK', 100.00, 45, 'Hardware', 'uploads/68135ac286713.webp', '2025-05-01 11:28:02'),
(21, 'Zenith', 'Zenith 3-Pc Bath in a Box Set', 3200.00, 2, 'Houseware', 'uploads/68135b316d30b.webp', '2025-05-01 11:29:53'),
(22, 'HS111A', 'Material: Ceramic, Type: Full body, Water Absorption: 3-6%, Traffic: Moderate, Thickness: 12mm, Print Technology: Inkjet print, Rectified: No, Feature: Slip-resistant', 11.00, 10000, 'Tiles', 'uploads/68135be19630e.jpg', '2025-05-01 11:32:49'),
(23, 'Prestone', 'Prestone Longlife Coolant Concentrate 3L', 1600.00, 54, 'Automotive', 'uploads/68135c278924f.webp', '2025-05-01 11:33:59'),
(24, 'AXIS', 'AXIS BASIN TAP YARRA AXS01FD250S', 1000.00, 25, 'Plumbing', 'uploads/68135c6f64e58.webp', '2025-05-01 11:35:11'),
(25, 'Firefly', 'Firefly LED Circular Tube 20W ECL820DL', 500.00, 35, 'Electrical', 'uploads/68135cb666ee8.webp', '2025-05-01 11:36:22'),
(26, 'Ledtec', 'Ledtec 24 Hour Mechanical Timer', 250.00, 32, 'Furniture', 'uploads/68135cff93f39.webp', '2025-05-01 11:37:35'),
(27, 'Centro', 'Centro Lacquer Thinner 350cc.', 99.00, 45, 'Paints & Sundries', 'uploads/68135d43deac0.webp', '2025-05-01 11:38:43'),
(29, 'Hava Asia', 'Hava Asia Stainless Steel Single Layer Glass Shelf CT32302\r\n', 1300.00, 24, 'Houseware', 'uploads/68176ec46b752.webp', '2025-05-04 13:42:28'),
(30, 'Interdesign', 'Interdesign 60463 Classico Suction Shower Shelves Chrome\r\n', 800.00, 34, 'Houseware', 'uploads/68176f0c70c78.webp', '2025-05-04 13:43:40'),
(31, 'Zenith', 'Zenith Space-saver Bath Shelves 23.25X69H (Chrome)\r\n', 3800.00, 12, 'Houseware', 'uploads/68176f89aecb0.webp', '2025-05-04 13:45:45'),
(32, 'Omni', 'OMNI DICE USB POWER STRIP EXTENSION CORD USB-321\r\n', 800.00, 23, 'Electrical', 'uploads/68176fddb33d5.webp', '2025-05-04 13:47:09'),
(33, 'Royu', 'Royu 4-Gang Universal Convenience Extension Cord\r\n', 250.00, 23, 'Electrical', 'uploads/68177014a8e3e.webp', '2025-05-04 13:48:04'),
(34, 'Akari', 'Akari 5-Gang Extension Cord\r\n', 600.00, 9, 'Electrical', 'uploads/68177044db7ca.webp', '2025-05-04 13:48:52'),
(35, 'Panasonic', 'Panasonic DH-3VS1PW Water Heater\r\n', 7700.00, 7, 'Plumbing', 'uploads/681770c26050d.webp', '2025-05-04 13:50:58'),
(36, 'Stiebel Eltron', 'Stiebel Eltron Water Heater Model XG 45EC\r\n', 10000.00, 4, 'Plumbing', 'uploads/681770f9e5cfb.webp', '2025-05-04 13:51:53'),
(37, 'ShopVac', 'ShopVac 20 L Classic 20 Wet/Dry Vacuum 4010-SQ14\r\n', 6500.00, 7, 'Appliances', 'uploads/681771538bc32.webp', '2025-05-04 13:53:23'),
(38, 'IWATA', 'IWATA AIRBLASTER 5\r\n', 30000.00, 3, 'Appliances', 'uploads/68177190b0e79.webp', '2025-05-04 13:54:24'),
(39, 'Type S', 'Type S Motorcycle Cover Waterproof- Small\r\n', 1300.00, 6, 'Automotive', 'uploads/681771ea58cb7.webp', '2025-05-04 13:55:54'),
(40, 'Air Spencer', 'Air Spencer Car Air Freshener - Aqua Shower\r\n', 300.00, 66, 'Automotive', 'uploads/6817721d5fef9.webp', '2025-05-04 13:56:45'),
(41, 'Pozzi Ovalty', 'Pozzi Ovalty White Round Countertop Lavatory\r\n', 1400.00, 5, 'Sanitary Waves', 'uploads/6817732566740.webp', '2025-05-04 14:01:09'),
(42, 'Pozzi Caleb', 'Pozzi Caleb Wall Hung Lavatory\r\n', 1800.00, 3, 'Sanitary Waves', 'uploads/68177374b628b.webp', '2025-05-04 14:02:28'),
(43, 'Bestway', 'Bestway QUEEN AIRBED + GRAFFITI AIR CHAIR\r\n', 2500.00, 12, 'Outdoor Living', 'uploads/681773bf8a878.webp', '2025-05-04 14:03:43'),
(44, 'Homer', 'Homer Mini Half Barrel Grill HOMKY1816\r\n', 1700.00, 4, 'Outdoor Living', 'uploads/681774092b50f.webp', '2025-05-04 14:04:57'),
(45, 'Living Accents', 'Living Accents ARTIFICIAL GRASS 1MX4M\r\n', 3200.00, 23, 'Outdoor Living', 'uploads/6817743f3c486.webp', '2025-05-04 14:05:51'),
(46, 'Uratex', 'Uratex Brooklyn Chair (Black)\n', 200.00, 23, 'Furniture', 'uploads/681774d2189b5.webp', '2025-05-04 14:08:18'),
(47, 'Alexander', 'Alexander 4 Seater Dining Set\r\n', 24000.00, 3, 'Furniture', 'uploads/681775b442593.webp', '2025-05-04 14:12:04'),
(48, 'Hi-Tech', 'Hi-Tech Paint Roller with Tray 7in.\r\n', 100.00, 34, 'Paints & Sundries', 'uploads/681775fed75b4.webp', '2025-05-04 14:13:18');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `email`, `password`, `user_type`) VALUES
(1, 'Gregg', 'imgregtomco@gmail.co', '$2y$10$1//uQ5QFPOdt3.UYTjIX9.fCm12C.UwdwnK2C.H0S6WRpYhNC0xy.', 'customer'),
(2, 'admin', 'admin', '$2y$10$1//uQ5QFPOdt3.UYTjIX9.fCm12C.UwdwnK2C.H0S6WRpYhNC0xy.', 'admin'),
(3, 'gregy', '', '$2y$10$k.ZkgssLcivF6SgKz.hlRuq1CTfu.La.mmuoEGlpdckPpa09Gnjta', 'driver'),
(5, 'driver', '', '$2y$10$k.ZkgssLcivF6SgKz.hlRuq1CTfu.La.mmuoEGlpdckPpa09Gnjta', 'driver'),
(6, 'vhan', '', '$2y$10$dnBNXaVnPNQoqbgr5euFmuFvPJq3LKGRGCQmwQfE0sXsXry5ODIfG', 'driver'),
(7, 'samsam', 'sammacawili971@gmail.com', '$2y$10$DTF66d61d1tfl30/tlmE8OGRySXAbHZ.7.1cWw/RTG0MgYBJ12je2', 'customer'),
(8, 'bpss', 'gg@gmail', '$2y$10$HMJDlYPe7u5V7yj1HID0seMP21vhXiCdsYqLlzXE3YEU98.Si/VYK', 'customer'),
(9, 'tomco', 'bess@gg', '$2y$10$5m2.tmChuHhiIHZBxOqAg..Sb4IWS4hpM2/F1qv2BhHdI3opAH40W', 'customer'),
(10, 'boy', 'kilo@gg', '$2y$10$eiDAxFivbcB1F8LM80eAi.h8uSr8j9bHpn0hRTaNJ8JjnHM1UKJWG', 'customer'),
(11, 'basta', '22-78921@g.batstate-u.edu.ph', '$2y$10$k.ZkgssLcivF6SgKz.hlRuq1CTfu.La.mmuoEGlpdckPpa09Gnjta', 'customer'),
(12, 'kabirdie', '', '$2y$10$g9h2I7Bt0zIyKYnC70iB1OQddFi1uh9eTVF50lmvW.K5u1HMXDeku', 'driver'),
(13, 'gregyboi', 'imgregtomco@gmail.com', '$2y$10$eimH.SHaeanklXoyqJMWn.NSb/yKsau5od5I29BxBC9coaVzl3Sy6', 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `verify`
--

CREATE TABLE `verify` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verify`
--

INSERT INTO `verify` (`id`, `email`, `code`, `created_at`) VALUES
(3, 'gg@gmail', '4157', '2025-05-06 19:21:17'),
(4, 'bess@gg', '1391', '2025-05-06 19:30:07'),
(5, 'kilo@gg', '3496', '2025-05-06 19:40:27'),
(6, '22-78921@g.batstate-u.edu.ph', '7500', '2025-10-19 13:42:28'),
(7, 'imgregtomco@gmail.com', '6788', '2026-04-22 15:20:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`driver_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `verify`
--
ALTER TABLE `verify`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `verify`
--
ALTER TABLE `verify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
