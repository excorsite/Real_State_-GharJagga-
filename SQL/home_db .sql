-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 08:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `home_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `setup_constraints` ()   BEGIN
    -- Temporarily disable foreign key checks
    SET FOREIGN_KEY_CHECKS = 0;

    -- Add missing PRIMARY KEYS
    -- admins → already has PK → skip

    -- property
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'property'
          AND CONSTRAINT_TYPE = 'PRIMARY KEY'
    ) THEN
        ALTER TABLE `property` ADD PRIMARY KEY (`id`);
    END IF;

    -- users
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'users'
          AND CONSTRAINT_TYPE = 'PRIMARY KEY'
    ) THEN
        ALTER TABLE `users` ADD PRIMARY KEY (`id`);
    END IF;

    -- requests
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'requests'
          AND CONSTRAINT_TYPE = 'PRIMARY KEY'
    ) THEN
        ALTER TABLE `requests` ADD PRIMARY KEY (`id`);
    END IF;

    -- purchases → already has PK → skip

    -- ───────────────────────────────────────────────
    -- Add FOREIGN KEY constraints
    -- ───────────────────────────────────────────────

    -- property → users
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'property'
          AND CONSTRAINT_NAME = 'fk_property_user_id'
    ) THEN
        ALTER TABLE `property`
            ADD CONSTRAINT `fk_property_user_id`
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    -- purchases → users (buyer)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'purchases'
          AND CONSTRAINT_NAME = 'fk_purchases_buyer_id'
    ) THEN
        ALTER TABLE `purchases`
            ADD CONSTRAINT `fk_purchases_buyer_id`
            FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    -- purchases → users (seller)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'purchases'
          AND CONSTRAINT_NAME = 'fk_purchases_seller_id'
    ) THEN
        ALTER TABLE `purchases`
            ADD CONSTRAINT `fk_purchases_seller_id`
            FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    -- purchases → property
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'purchases'
          AND CONSTRAINT_NAME = 'fk_purchases_property_id'
    ) THEN
        ALTER TABLE `purchases`
            ADD CONSTRAINT `fk_purchases_property_id`
            FOREIGN KEY (`property_id`) REFERENCES `property` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    -- requests → property
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'requests'
          AND CONSTRAINT_NAME = 'fk_requests_property_id'
    ) THEN
        ALTER TABLE `requests`
            ADD CONSTRAINT `fk_requests_property_id`
            FOREIGN KEY (`property_id`) REFERENCES `property` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    -- requests → users (sender)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'requests'
          AND CONSTRAINT_NAME = 'fk_requests_sender'
    ) THEN
        ALTER TABLE `requests`
            ADD CONSTRAINT `fk_requests_sender`
            FOREIGN KEY (`sender`) REFERENCES `users` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    -- requests → users (receiver)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'home_db'
          AND TABLE_NAME = 'requests'
          AND CONSTRAINT_NAME = 'fk_requests_receiver'
    ) THEN
        ALTER TABLE `requests`
            ADD CONSTRAINT `fk_requests_receiver`
            FOREIGN KEY (`receiver`) REFERENCES `users` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE;
    END IF;

    -- Re-enable foreign key checks
    SET FOREIGN_KEY_CHECKS = 1;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `password`) VALUES
(0, 'admin', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `number` varchar(10) NOT NULL,
  `message` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `property_name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `price` varchar(10) NOT NULL,
  `type` varchar(10) NOT NULL,
  `offer` varchar(10) NOT NULL,
  `status` enum('pending','approved','sold') NOT NULL DEFAULT 'pending',
  `furnished` varchar(50) DEFAULT NULL,
  `bhk` varchar(20) DEFAULT NULL,
  `deposite` varchar(10) NOT NULL,
  `bedroom` int(11) DEFAULT NULL,
  `bathroom` int(11) DEFAULT NULL,
  `balcony` int(11) DEFAULT NULL,
  `carpet` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `total_floors` int(11) DEFAULT NULL,
  `room_floor` int(11) DEFAULT NULL,
  `loan` varchar(50) NOT NULL,
  `lift` varchar(3) NOT NULL DEFAULT 'no',
  `security_guard` varchar(3) NOT NULL DEFAULT 'no',
  `play_ground` varchar(3) NOT NULL DEFAULT 'no',
  `garden` varchar(3) NOT NULL DEFAULT 'no',
  `water_supply` varchar(3) NOT NULL DEFAULT 'no',
  `power_backup` varchar(3) NOT NULL DEFAULT 'no',
  `parking_area` varchar(3) NOT NULL DEFAULT 'no',
  `gym` varchar(3) NOT NULL DEFAULT 'no',
  `shopping_mall` varchar(3) NOT NULL DEFAULT 'no',
  `hospital` varchar(3) NOT NULL DEFAULT 'no',
  `school` varchar(3) NOT NULL DEFAULT 'no',
  `market_area` varchar(3) NOT NULL DEFAULT 'no',
  `image_01` varchar(50) NOT NULL,
  `image_02` varchar(50) NOT NULL,
  `image_03` varchar(50) NOT NULL,
  `image_04` varchar(50) NOT NULL,
  `image_05` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `total_area` varchar(100) DEFAULT NULL,
  `road_access` varchar(100) DEFAULT NULL,
  `facing` varchar(100) DEFAULT NULL,
  `plot_shape` varchar(100) DEFAULT NULL,
  `ana` varchar(100) DEFAULT NULL,
  `ownership` varchar(100) DEFAULT NULL,
  `registration` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`id`, `user_id`, `property_name`, `address`, `price`, `type`, `offer`, `status`, `furnished`, `bhk`, `deposite`, `bedroom`, `bathroom`, `balcony`, `carpet`, `age`, `total_floors`, `room_floor`, `loan`, `lift`, `security_guard`, `play_ground`, `garden`, `water_supply`, `power_backup`, `parking_area`, `gym`, `shopping_mall`, `hospital`, `school`, `market_area`, `image_01`, `image_02`, `image_03`, `image_04`, `image_05`, `description`, `date`, `approved`, `total_area`, `road_access`, `facing`, `plot_shape`, `ana`, `ownership`, `registration`) VALUES
('1grhELY4ECnC0epAPpWf', 'hDVmmoISWhDDsr6nPfaC', '765432', 'Lolang,Kathmandu', '-15000000', 'land', 'sale', 'approved', 'unfurnished', '1', '10000000', 0, 0, 0, '', 0, NULL, NULL, 'available', 'no', 'no', 'no', 'no', 'yes', 'yes', 'yes', 'no', 'no', 'no', 'yes', 'yes', 'T8neAGcyGXr6sFNMB6VX.jpg', 'D9t6dgYaVDWyGZjjjxuZ.jpg', 'D8gdBhzforAIsigiTz1D.jpg', 'heNkWkObDecrSpqAQMwv.jpg', '', 'Residential land available in a prime location perfect for building your dream house.', '2025-12-29', 1, '', '', 'East', 'Rectangular', '', 'Individual', 'Ready for immediate transfer'),
('CXtjffJQnVdnHv4qZf0e', 'U81ustJharxDus6si8wG', 'Shree Rajya Laxmi Shah (owner)', 'Manamaiju,Kathmandu', '250000000', 'home', 'resale', 'approved', 'unfurnished', '2', '2000000', 3, 2, 1, '1500', 3, 2, 2, 'available', 'no', 'no', 'no', 'yes', 'yes', 'yes', 'yes', 'no', 'no', 'yes', 'yes', 'yes', 'M1PFlyoxzkZTPE07AMsh.jpg', 'R8wEbHlNSmKQZCO3Pwjg.jpeg', 'USK5gi70VY4ApbtYbzpm.jpeg', 'RuReneyiTzTgUu9t6Azt.jpeg', 'O8k6dukSC30Ts0c37Vty.jpeg', 'A well designed house with strong construction, modern finishes and a warm environment for your loved ones.', '2025-12-29', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('g8wO7DrC1fVeK7SZlYaX', 'Px5TTwvu4KGblYMcZWie', 'Ashish Bhetwal (owner)  ', 'Banasathali,Kathmandu', '1500000000', 'land', 'resale', 'approved', 'unfurnished', '1', '1000000', 0, 0, 0, '', 0, 0, 0, 'available', 'no', 'no', 'yes', 'no', 'yes', 'no', 'yes', 'no', 'no', 'no', 'no', 'yes', 'Bpgq3Z3eXrpBjYPdRxyy.jpeg', '61F31hOYvJqXprQ2sbqs.jpeg', 'laGvYOsq0naJIuVY4akV.jpeg', 'cJsSylRCr0sOZYbdrfm8.jpeg', '', 'Affordable land with clear boundaries and legal documents, ready for immediate purchase.', '2025-12-29', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('IvC07A5p0J1Rfu5MY84Y', 'hDVmmoISWhDDsr6nPfaC', 'Bipin Poudel(owner)', 'Basundhara,Kathmandu', '20000000', 'home', 'sale', 'approved', 'semi-furnished', '2', '15000000', 3, 2, 1, '2500', 3, 3, 3, 'not available', 'no', 'no', 'no', 'no', 'yes', 'yes', 'yes', 'no', 'no', 'yes', 'yes', 'no', 'LlB2hIhh4JnE0pAHvjV5.jpeg', 'eixwcbtbZTMuP96nQvvs.jpeg', 'dcYlrrIPk57hFXwouMEI.jpeg', 'wrlZNXQaklRwCfOqDhet.jpeg', 'MRa722x1lz8VNF4tkEhW.jpeg', 'A modern home built with comfort and style in mind, offering ample space, natural light and all the facilities you need for quality living. ', '2025-12-29', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('PVzY6eCUUC5JAxiYsCyc', 'Px5TTwvu4KGblYMcZWie', 'Mina Rana Magar (owner)', 'Baneshwor,Kathmandu,', '20000000', 'land', 'sale', 'approved', 'unfurnished', '1', '1000000', 0, 0, 0, '', 0, 0, 0, 'available', 'no', 'no', 'no', 'yes', 'yes', 'no', 'no', 'no', 'no', 'yes', 'yes', 'yes', 'wUF3w98VnoD9fnXa3WJ2.jpg', 'zlyrbSqgEnLhEBea5T3I.jpg', 'zT7froxGPqVr4ehaSzy0.jpg', '', '', 'Spacious land in a scenic location, offering a perfect balance of nature and connectivity.', '2025-12-29', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('WPKbItJszG0n5CsWnoZM', 'U81ustJharxDus6si8wG', 'Deepak Luitel (owner)', 'Swayambhu,Kathmandu', '30000000', 'home', 'sale', 'approved', 'furnished', '2', '10000000', 3, 2, 1, '1700', 1, 2, 2, 'available', 'no', 'no', 'no', 'no', 'yes', 'yes', 'yes', 'no', 'no', 'no', 'yes', 'yes', 'u3RPvLuB6B441vNc5drF.jpg', '7B9W07qPIUC2GpzfWknA.jpeg', 'wnAsK7A3Lj7OiY8WbptS.jpeg', 'b0F3g8fGaVBcvum2lRpA.jpeg', 'oqk8WVtN3HhK1lx0NyoF.jpeg', 'Bright, spacious home offering comfort convenience and a calm living environment.', '2025-12-29', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` varchar(20) NOT NULL,
  `buyer_id` varchar(20) NOT NULL,
  `seller_id` varchar(20) NOT NULL,
  `property_id` varchar(20) NOT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `buyer_id`, `seller_id`, `property_id`, `status`, `created_at`) VALUES
('pur_694a553d2d7db', 'ZtRWyzVTYBWeVW4rCr9p', '7lv032Hc9bADtmziJOwH', 'oXJTBo38u7b5ZJl1hn01', 'completed', '2025-12-23 14:24:25'),
('pur_694a554d53398', 'ZtRWyzVTYBWeVW4rCr9p', 'hrfUsww3L6PffzdpA52S', 'WTitJFD5aLKxOg5LqsVq', 'completed', '2025-12-23 14:24:41'),
('pur_695127613ace9', 'JyJ3rf2wRazSL1nZvvTI', 'Px5TTwvu4KGblYMcZWie', '94bnOSaj7GcRjr9Cejun', 'completed', '2025-12-28 18:34:37'),
('pur_695263954b39d', 'hDVmmoISWhDDsr6nPfaC', 'KUFo1Wxbs8GhT7Cm48Mc', 'l5IosgjVdRt8BkNUa8IY', 'completed', '2025-12-29 17:03:45'),
('pur_6952644e1c064', 'hDVmmoISWhDDsr6nPfaC', 'hrfUsww3L6PffzdpA52S', 'oRBvnLvH0qM1TWS1lpMj', 'completed', '2025-12-29 17:06:50'),
('pur_6952645d4ff9d', 'hDVmmoISWhDDsr6nPfaC', '7lv032Hc9bADtmziJOwH', 'oXJTBo38u7b5ZJl1hn01', 'completed', '2025-12-29 17:07:05'),
('pur_695264c059673', 'JyJ3rf2wRazSL1nZvvTI', 'hrfUsww3L6PffzdpA52S', 'oRBvnLvH0qM1TWS1lpMj', 'completed', '2025-12-29 17:08:44'),
('pur_6952732a33811', 'JyJ3rf2wRazSL1nZvvTI', 'hDVmmoISWhDDsr6nPfaC', 'IvC07A5p0J1Rfu5MY84Y', 'completed', '2025-12-29 18:10:14'),
('pur_695fcca1696de', 'JyJ3rf2wRazSL1nZvvTI', 'hDVmmoISWhDDsr6nPfaC', '1grhELY4ECnC0epAPpWf', 'completed', '2026-01-08 21:11:25'),
('pur_69aa6df6e5cb9', '3LGue9ROUGTtphRXeUq7', 'U81ustJharxDus6si8wG', 'WPKbItJszG0n5CsWnoZM', 'completed', '2026-03-06 11:47:30'),
('pur_69afc4df10c5a', '3LGue9ROUGTtphRXeUq7', 'hDVmmoISWhDDsr6nPfaC', '1grhELY4ECnC0epAPpWf', 'completed', '2026-03-10 12:59:39');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` varchar(20) NOT NULL,
  `property_id` varchar(20) NOT NULL,
  `sender` varchar(20) NOT NULL,
  `receiver` varchar(20) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `property_id`, `sender`, `receiver`, `date`) VALUES
('10iIxewy0IPexIZbMKmh', 'zyYkSvm53k4CsIIeOAjm', 'hDVmmoISWhDDsr6nPfaC', '7lv032Hc9bADtmziJOwH', '2025-12-29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `number` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` enum('buyer','seller') NOT NULL DEFAULT 'buyer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `number`, `email`, `password`, `type`, `created_at`) VALUES
('3LGue9ROUGTtphRXeUq7', 'Prenu Magar', '9700000001', 'prenu@gmail.com', '$2y$10$ps1oZd7Fbac7S3gMG8YZgOFOfkLmzLYQEMxoDI7c1.c6oUkmN89.i', 'buyer', '2026-03-06 06:00:06'),
('hDVmmoISWhDDsr6nPfaC', 'Prenaka Tamang', '9800000000', 'prenaka@gmail.com', '$2y$10$s8.kpyRUBaGRm.z.LR2fA.Txz/fPgeuBSU1Timp3JuEYSE4TdCELu', 'seller', '2025-12-23 09:37:05'),
('Px5TTwvu4KGblYMcZWie', 'Prasamsha Khanal', '9800000000', 'prasamsha@gmail.com', '$2y$10$Z2LbRW6mkbLTaf0HphDZf.vsh/wuRg76aZnNCKKT2.xUiU5xMQvFW', 'seller', '2025-12-23 07:19:05'),
('U81ustJharxDus6si8wG', 'Preeya Khadka', '9865000000', 'preeya@gmail.com', '$2y$10$K/w2tjoB2S6RPVPZ6T9coOEn/S3AKw2sWShYphFxnYJeHtHz9dG0y', 'seller', '2025-12-29 11:55:50'),
('YhKLr52RgQq77r7NcV1G', 'Prasu Sharma', '9700000000', 'prasu@gmail.com', '$2y$10$cT94.jI5nsrdswx1JA0c7eBuY2lspiwzQXy3i.pmyY0qWW.aSu4vK', 'buyer', '2026-03-06 05:59:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_property_user_id` (`user_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchases_buyer_id` (`buyer_id`),
  ADD KEY `fk_purchases_seller_id` (`seller_id`),
  ADD KEY `fk_purchases_property_id` (`property_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_requests_property_id` (`property_id`),
  ADD KEY `fk_requests_sender` (`sender`),
  ADD KEY `fk_requests_receiver` (`receiver`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `property`
--
ALTER TABLE `property`
  ADD CONSTRAINT `fk_property_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchases_buyer_id` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_purchases_property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_purchases_seller_id` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_requests_property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_requests_receiver` FOREIGN KEY (`receiver`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_requests_sender` FOREIGN KEY (`sender`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
