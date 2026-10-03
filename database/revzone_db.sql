-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Oct 03, 2026 at 06:14 PM
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
-- Database: `revzone_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `address` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `city`, `address`) VALUES
(1, 'Haifa - Downtown', 'Haifa', '12 HaAtzmaut St'),
(2, 'Tel Aviv - Center', 'Tel Aviv-Jaffa', '45 Dizengoff St'),
(3, 'Carmiel - Mall', 'Carmiel', 'Big Center Mall');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `username` varchar(30) NOT NULL,
  `product_SKU` varchar(30) NOT NULL,
  `amount` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `name`) VALUES
(1, 'oils'),
(2, 'filters'),
(3, 'brakes'),
(4, 'wheels');

-- --------------------------------------------------------

--
-- Table structure for table `city`
--

CREATE TABLE `city` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `region` enum('North','Center','South') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `city`
--

INSERT INTO `city` (`id`, `name`, `region`) VALUES
(1, 'Acre', 'North'),
(2, 'Afula', 'North'),
(3, 'Beit Shean', 'North'),
(4, 'Carmiel', 'North'),
(5, 'Haifa', 'North'),
(6, 'Hadera', 'North'),
(7, 'Kiryat Ata', 'North'),
(8, 'Kiryat Bialik', 'North'),
(9, 'Kiryat Motzkin', 'North'),
(10, 'Kiryat Yam', 'North'),
(11, 'Ma\'alot-Tarshiha', 'North'),
(12, 'Migdal HaEmek', 'North'),
(13, 'Nahariya', 'North'),
(14, 'Nazareth', 'North'),
(15, 'Nesher', 'North'),
(16, 'Nof HaGalil', 'North'),
(17, 'Safed', 'North'),
(18, 'Tiberias', 'North'),
(19, 'Tirat Carmel', 'North'),
(20, 'Bat Yam', 'Center'),
(21, 'Bnei Brak', 'Center'),
(22, 'Elad', 'Center'),
(23, 'Givatayim', 'Center'),
(24, 'Givat Shmuel', 'Center'),
(25, 'Herzliya', 'Center'),
(26, 'Hod HaSharon', 'Center'),
(27, 'Holon', 'Center'),
(28, 'Jerusalem', 'Center'),
(29, 'Kafr Qasim', 'Center'),
(30, 'Kfar Saba', 'Center'),
(31, 'Lod', 'Center'),
(32, 'Modiin-Maccabim-Reut', 'Center'),
(33, 'Ness Ziona', 'Center'),
(34, 'Netanya', 'Center'),
(35, 'Or Akiva', 'Center'),
(36, 'Or Yehuda', 'Center'),
(37, 'Petah Tikva', 'Center'),
(38, 'Raanana', 'Center'),
(39, 'Ramla', 'Center'),
(40, 'Ramat Gan', 'Center'),
(41, 'Ramat HaSharon', 'Center'),
(42, 'Rehovot', 'Center'),
(43, 'Rishon LeZion', 'Center'),
(44, 'Rosh HaAyin', 'Center'),
(45, 'Tel Aviv-Jaffa', 'Center'),
(46, 'Yavne', 'Center'),
(47, 'Yehud-Monosson', 'Center'),
(48, 'Yokneam Illit', 'Center'),
(49, 'Arad', 'South'),
(50, 'Ashdod', 'South'),
(51, 'Ashkelon', 'South'),
(52, 'Beersheba', 'South'),
(53, 'Dimona', 'South'),
(54, 'Eilat', 'South'),
(55, 'Netivot', 'South'),
(56, 'Ofakim', 'South'),
(57, 'Rahat', 'South'),
(58, 'Sderot', 'South');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `chat` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacth`
--

CREATE TABLE `contacth` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `chat` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courier`
--

CREATE TABLE `courier` (
  `id` int(11) NOT NULL,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `region` enum('North','Center','South','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courier`
--

INSERT INTO `courier` (`id`, `fname`, `lname`, `phone`, `region`) VALUES
(3, 'amir', 'aa', '0500000103', 'North'),
(4, 'yossi', 'bb', '0500000104', 'Center'),
(5, 'sami', 'cc', '0500000105', 'South'),
(6, 'mor', 'dd', '0500000106', 'North');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `order_id` int(11) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `status` enum('pending','en route','delivered','') NOT NULL,
  `courier_id` int(11) DEFAULT NULL,
  `arrival_time` datetime DEFAULT NULL,
  `delivery_type` enum('ship','pickup') NOT NULL DEFAULT 'ship',
  `branch_id` int(11) DEFAULT NULL,
  `pickup_time_from` time DEFAULT NULL,
  `pickup_time_to` time DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderh`
--

CREATE TABLE `orderh` (
  `order_id` int(11) NOT NULL,
  `product_SKU` varchar(30) NOT NULL,
  `amount` int(100) NOT NULL,
  `price` float NOT NULL,
  `order_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` varchar(30) NOT NULL,
  `order_date` datetime NOT NULL,
  `shipping` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `Product_SKU` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`Product_SKU`, `name`, `category_id`, `price`, `stock`, `image`, `active`) VALUES
('BRK-DRUM-ATE-DS120', 'ATE Drum Brake Shoes DS-120', 3, 189.9, 20, 'img/ATEDS.png', 1),
('BRK-DRUM-ATE-DS240', 'ATE Drum Brake Shoes DS-240', 3, 199.9, 20, 'img/ATEDS.png', 1),
('BRK-DRUM-BOSCH-DS300', 'Bosch Drum Brake Shoes DS-300', 3, 209.9, 20, 'img/boschDS.png', 1),
('BRK-PADS-BOSCH-BP180', 'Bosch Disc Brake Pads BP-180', 3, 219.9, 20, 'img/boschBP.png', 1),
('BRK-PADS-BREMBO-BP210', 'Brembo Disc Brake Pads BP-210', 3, 229.9, 20, 'img/bremboBP.png', 1),
('BRK-PADS-BREMBO-BP340', 'Brembo Disc Brake Pads BP-340', 3, 249.9, 20, 'img/bremboBP.png', 1),
('BRK-ROTOR-BOSCH-BR300', 'Bosch Brake Rotor BR-300', 3, 289.9, 20, 'img/boschBR.png', 1),
('BRK-ROTOR-BREMBO-BR280', 'Brembo Brake Rotor BR-280', 3, 299.9, 20, 'img/bremboBR.png', 1),
('BRK-ROTOR-BREMBO-BR320', 'Brembo Brake Rotor BR-320', 3, 329.9, 20, 'img/bremboBR.png', 1),
('FIL-AIR-BOSCH-AF60', 'Bosch Air Filter AF-60', 2, 59.9, 20, 'img/boschAF.png', 1),
('FIL-AIR-BOSCH-AF90', 'Bosch Air Filter AF-90', 2, 64.9, 19, 'img/boschAF.png', 1),
('FIL-AIR-MANN-AF120', 'MANN Air Filter AF-120', 2, 69.9, 17, 'img/mannAF.png', 1),
('FIL-FUEL-BOSCH-FF50', 'Bosch Fuel Filter FF-50', 2, 79.9, 16, 'img/boschFF.png', 1),
('FIL-FUEL-MAHLE-FF90', 'Mahle Fuel Filter FF-90', 2, 89.9, 20, 'img/mahleFF.png', 1),
('FIL-FUEL-MANN-FF70', 'MANN Fuel Filter FF-70', 2, 84.9, 20, 'img/mannFF.png', 1),
('FIL-OIL-BOSCH-OF330', 'Bosch Oil Filter OF-330', 2, 44.9, 20, 'img/boschOF.png', 1),
('FIL-OIL-MANN-OF101', 'MANN Oil Filter OF-101', 2, 34.9, 20, 'img/mannOF.png', 1),
('FIL-OIL-MANN-OF220', 'MANN Oil Filter OF-220', 2, 39.9, 20, 'img/mannOF.png', 1),
('OIL-CASTROL-2D110', 'Castrol Motor Oil 2D-110', 1, 189.9, 19, 'img/castrol.png', 1),
('OIL-CASTROL-2D26', 'Castrol Motor Oil 2D-26', 1, 179.9, 20, 'img/castrol.png', 1),
('OIL-CASTROL-2D94', 'Castrol Motor Oil 2D-94', 1, 179.9, 20, 'img/castrol.png', 1),
('OIL-MOTUL-5W30', 'Motul Motor Oil 5W-30', 1, 199.9, 20, 'img/motul.png', 1),
('OIL-MOTUL-5W40', 'Motul Motor Oil 5W-40', 1, 219.9, 20, 'img/motul.png', 1),
('OIL-MOTUL-6W30', 'Motul Motor Oil 6W-30', 1, 209.9, 20, 'img/motul.png', 1),
('OIL-SHELL-HX710W40', 'Shell Motor Oil HX7 10W-40', 1, 169.9, 20, 'img/shell.png', 1),
('OIL-SHELL-HX85W30', 'Shell Motor Oil HX8 5W-30', 1, 179.9, 20, 'img/shell.png', 1),
('OIL-SHELL-ULTRA5W40', 'Shell Motor Oil Helix Ultra 5W-40', 1, 199.9, 20, 'img/shell.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(30) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `role` tinyint(1) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pass3` varchar(255) NOT NULL,
  `pass2` varchar(255) NOT NULL,
  `pass1` varchar(255) NOT NULL,
  `changeP` tinyint(1) NOT NULL,
  `blocked` tinyint(1) NOT NULL,
  `loginh` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `yearFrom` int(11) NOT NULL,
  `yearTo` int(11) NOT NULL,
  `engine` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `model`, `yearFrom`, `yearTo`, `engine`) VALUES
(1, 'abarth', '500', 2009, 2024, '1.4 tjet'),
(2, 'audi', 'a3', 2020, 2026, '1.5 tfsi'),
(3, 'bmw', '3 series', 2012, 2019, '2.0'),
(4, 'bmw', '5 series', 2011, 2017, '2.0'),
(5, 'mercedes', 'c class', 2010, 2018, '2.0'),
(6, 'mercedes', 'e class', 2012, 2019, '2.0'),
(7, 'toyota', 'corolla', 2014, 2021, '1.6'),
(8, 'toyota', 'camry', 2012, 2019, '2.5'),
(9, 'honda', 'civic', 2012, 2020, '1.8'),
(10, 'honda', 'accord', 2013, 2020, '2.0'),
(11, 'volkswagen', 'golf', 2013, 2020, '1.4'),
(12, 'hyundai', 'elantra', 2016, 2022, '1.6');

-- --------------------------------------------------------

--
-- Table structure for table `vehiclesr`
--

CREATE TABLE `vehiclesr` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `required_keyword` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehiclesr`
--

INSERT INTO `vehiclesr` (`id`, `vehicle_id`, `category_id`, `required_keyword`) VALUES
(1, 1, 1, '10W-40'),
(2, 1, 2, 'Oil Filter'),
(3, 1, 3, 'Brembo'),
(4, 2, 1, '5W-30'),
(5, 2, 2, 'Bosch'),
(6, 2, 3, 'Bosch'),
(7, 1, 2, 'Oil Filter'),
(8, 1, 3, 'Brembo'),
(9, 2, 1, '5W-30'),
(10, 2, 2, 'Bosch'),
(11, 2, 3, 'Bosch'),
(12, 3, 1, '5W-30'),
(13, 3, 2, 'Oil Filter'),
(14, 3, 3, 'Bosch'),
(15, 4, 1, '5W-40'),
(16, 4, 2, 'Oil Filter'),
(17, 4, 3, 'Bosch'),
(18, 5, 1, '5W-40'),
(19, 5, 2, 'Bosch'),
(20, 5, 3, 'Brembo'),
(21, 6, 1, '5W-40'),
(22, 6, 2, 'Bosch'),
(23, 6, 3, 'Brembo'),
(24, 7, 1, '5W-30'),
(25, 7, 2, 'Air Filter'),
(26, 7, 3, 'Bosch'),
(27, 8, 1, '5W-30'),
(28, 8, 2, 'Air Filter'),
(29, 8, 3, 'Brembo'),
(30, 9, 1, '5W-30'),
(31, 9, 2, 'Air Filter'),
(32, 9, 3, 'Bosch'),
(33, 10, 1, '5W-30'),
(34, 10, 2, 'Air Filter'),
(35, 10, 3, 'Brembo'),
(36, 11, 1, '5W-30'),
(37, 11, 2, 'Oil Filter'),
(38, 11, 3, 'Bosch'),
(39, 12, 1, '5W-30'),
(40, 12, 2, 'Air Filter'),
(41, 12, 3, 'Bosch');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`username`,`product_SKU`),
  ADD KEY `fk_cart_product` (`product_SKU`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_contact_username` (`username`),
  ADD KEY `idx_contact_date` (`date`);

--
-- Indexes for table `contacth`
--
ALTER TABLE `contacth`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contacth_username` (`username`),
  ADD KEY `idx_contacth_status` (`status`);

--
-- Indexes for table `courier`
--
ALTER TABLE `courier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courier_region` (`region`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_delivery_courier` (`courier_id`),
  ADD KEY `fk_delivery_city` (`city_id`),
  ADD KEY `fk_delivery_branch` (`branch_id`),
  ADD KEY `idx_delivery_status` (`status`),
  ADD KEY `idx_delivery_type` (`delivery_type`);

--
-- Indexes for table `orderh`
--
ALTER TABLE `orderh`
  ADD PRIMARY KEY (`order_id`,`product_SKU`),
  ADD KEY `fk_orderh_product` (`product_SKU`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_user_date` (`user_id`,`order_date`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`Product_SKU`),
  ADD KEY `categorgy_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehiclesr`
--
ALTER TABLE `vehiclesr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacth`
--
ALTER TABLE `contacth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courier`
--
ALTER TABLE `courier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vehiclesr`
--
ALTER TABLE `vehiclesr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_SKU`) REFERENCES `products` (`Product_SKU`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `fk_contact_user` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contacth`
--
ALTER TABLE `contacth`
  ADD CONSTRAINT `fk_contacth_user` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `fk_delivery_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_delivery_city` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_delivery_courier` FOREIGN KEY (`courier_id`) REFERENCES `courier` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_delivery_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orderh`
--
ALTER TABLE `orderh`
  ADD CONSTRAINT `fk_orderh_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderh_product` FOREIGN KEY (`product_SKU`) REFERENCES `products` (`Product_SKU`) ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON UPDATE CASCADE;

--
-- Constraints for table `vehiclesr`
--
ALTER TABLE `vehiclesr`
  ADD CONSTRAINT `fk_vehiclesr_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehiclesr_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------
-- Demo accounts (the passwords are written in README.md)
-- --------------------------------------------------------
INSERT INTO `users` (`username`, `fname`, `lname`, `birth_date`, `role`, `address`, `phone`, `email`, `password`, `pass3`, `pass2`, `pass1`, `changeP`, `blocked`) VALUES
('demo_admin', 'Demo', 'Admin', '1990-01-01', 1, '1 Demo St', '0500000001', 'admin@example.com', '$2y$10$TNOhEV3DphKZjMnpRayFNOMH5AJ6w2sTXL4E.wnaoBg9s9X/Jn7Va', '$2y$10$TNOhEV3DphKZjMnpRayFNOMH5AJ6w2sTXL4E.wnaoBg9s9X/Jn7Va', '$2y$10$TNOhEV3DphKZjMnpRayFNOMH5AJ6w2sTXL4E.wnaoBg9s9X/Jn7Va', '$2y$10$TNOhEV3DphKZjMnpRayFNOMH5AJ6w2sTXL4E.wnaoBg9s9X/Jn7Va', 0, 0),
('demo_customer', 'Demo', 'Customer', '1995-01-01', 0, '2 Demo St', '0500000002', 'customer@example.com', '$2y$10$g.OxamsanBJu94WX0/Z.ye.kUBm4dIdy.JDZDVL9bgd2Y6Zr380SW', '$2y$10$g.OxamsanBJu94WX0/Z.ye.kUBm4dIdy.JDZDVL9bgd2Y6Zr380SW', '$2y$10$g.OxamsanBJu94WX0/Z.ye.kUBm4dIdy.JDZDVL9bgd2Y6Zr380SW', '$2y$10$g.OxamsanBJu94WX0/Z.ye.kUBm4dIdy.JDZDVL9bgd2Y6Zr380SW', 0, 0);