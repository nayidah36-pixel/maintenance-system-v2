-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 04:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maintenance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notification` tinyint(1) DEFAULT 0,
  `technician_id` int(11) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `technician_status` varchar(50) DEFAULT 'Pending',
  `work_notes` text DEFAULT NULL,
  `admin_status` varchar(50) DEFAULT 'Pending',
  `admin_approved_at` timestamp NULL DEFAULT NULL,
  `tech_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `user_id`, `title`, `description`, `location`, `status`, `created_at`, `notification`, `technician_id`, `service_type`, `technician_status`, `work_notes`, `admin_status`, `admin_approved_at`, `tech_notes`) VALUES
(1, 1, 'Water problem', 'Water leakage', 'Nairobi', 'Pending', '2026-04-21 10:03:56', 1, 0, NULL, 'Pending', NULL, 'Pending', NULL, NULL),
(2, 1, 'Gardening', 'Planting flowers', 'Nairobi', 'Pending', '2026-04-21 10:04:52', 1, 0, NULL, 'Pending', NULL, 'Pending', NULL, NULL),
(3, 1, 'House painting', 'I want a touch of sage and white inside the house and cream on the outside', 'Kiambu - Ruaka', 'Completed', '2026-05-11 13:33:35', 0, 6, 'Painter', 'Completed', NULL, 'Completed', '2026-05-19 19:38:29', 'It\'s done.'),
(4, 1, 'Washing machine problem', 'Water not draining', 'Mombasa - Dunga Beach', 'Completed', '2026-05-14 11:10:19', 0, 4, 'Appliance Repair', 'Completed', NULL, 'Completed', '2026-05-19 19:14:10', ''),
(5, 1, 'I want a solar panel to be installed', 'My recent one is having problems', 'Nakuru - Bahati Constituency', 'Completed', '2026-05-29 04:26:27', 0, 5, 'Solar Technician', 'Completed', NULL, 'Completed', '2026-05-29 04:41:23', 'Done.');

-- --------------------------------------------------------

--
-- Table structure for table `technicians`
--

CREATE TABLE `technicians` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `availability_status` varchar(50) DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technicians`
--

INSERT INTO `technicians` (`id`, `first_name`, `last_name`, `email`, `password`, `availability_status`) VALUES
(1, 'John', 'Electrician', 'john.tech@gmail.com', '$2y$10$usBwlyfkEeWJkAIMPYGYo.UWIqXI7S5V2aiV/r/ZPKxzzTOjiu4ay', 'Available'),
(2, 'Mary', 'Plumber', 'mary.tech@gmail.com', '$2y$10$Z5QXHZ7gY6wluJYBuyoFNeFV81bzSv.RmvJJEkoKHdvfE2L10h7XW', 'Available'),
(3, 'Alex', 'Carpenter', 'alex.tech@gmail.com', '$2y$10$GR0YyH2QK45WTefaRUQFhOOOjt2KoK114F2YgUShTIxPSUf0A13Tq', 'Available'),
(4, 'Brian', 'Repair', 'brian.tech@gmail.com', '$2y$10$h.pVeZxbF2.I3avcAZ..F.7mCkf7XW1yi6OffGgru24.jZEMMUjsS', 'Available'),
(5, 'Kevin', 'Solar', 'kevin.tech@gmail.com', '$2y$10$i4LMmnqBKcOeI/.eQuIHqeBkNVUIgfGp1.vsVzcutOq3BuGNk4X9C', 'Available'),
(6, 'Diana', 'Painter', 'diana.tech@gmail.com', '$2y$10$ycB9aUXrnw3K2NJThU9OhO8BzIIYSBPk9lIfaEbeEZmw9g1ArzV/O', 'Available'),
(7, 'Dennis', 'Technician', 'Dennis.tech@gmail.com', '$2y$10$D6LBhVPiJHstDXDk7fVpzekZmlh8coeb/Xp/8OQBLbgui5ZcKpTDC', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user','technician') DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `first_name`, `last_name`, `profile_image`, `created_at`) VALUES
(1, 'Nova nay', 'nayidah36@gmail.com', '$2y$10$XO0QzltMPbbTgl6mTThEVO4G5gxekKlhVjmpLU1bM4VA31qC0i..O', 'user', NULL, NULL, NULL, '2026-04-21 09:49:10'),
(2, 'Nova', 'admin@gmail.com', '$2y$10$q2/7Msn44Jb2/0bJp.7zO.0Z1.d0Z1.d0Z1.d0Z1.d0Z1.d0Z1', 'admin', 'Elina', 'Bope', NULL, '2026-04-21 10:23:20'),
(21, NULL, 'lexy@gmail.com', '$2y$10$IzkVUcpbodUoMPQUjyj6zObZzZrrSV1zoZKY5crlAJYK091jom3ta', 'user', 'lexy', 'xexe', NULL, '2026-07-22 15:11:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `technicians`
--
ALTER TABLE `technicians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `technicians`
--
ALTER TABLE `technicians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
