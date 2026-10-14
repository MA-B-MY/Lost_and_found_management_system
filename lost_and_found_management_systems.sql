-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 10:26 AM
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
-- Database: `lost_and_found_management_systems`
--

-- --------------------------------------------------------

--
-- Table structure for table `admindata`
--

CREATE TABLE `admindata` (
  `id` int(11) NOT NULL,
  `AdminName` varchar(100) NOT NULL,
  `AdminEmail` varchar(100) NOT NULL,
  `AdminID` varchar(50) NOT NULL,
  `AdminPassword` varchar(255) NOT NULL,
  `AdminPicturePath` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admindata`
--

INSERT INTO `admindata` (`id`, `AdminName`, `AdminEmail`, `AdminID`, `AdminPassword`, `AdminPicturePath`, `created_at`) VALUES
(4, 'Ahmad', 'm1w2344@gmail.com', '654389188', '$2y$10$Bo8a/vi.oF1KreqbcR1Dje0dL6jgpLyGZHgHuu8gpYYE2egGbUA8O', '../uploads/654389188_1765943271.jpg', '2025-12-17 03:47:51'),
(5, 'Abu', 'abu@gmail.com', '42510102022', '$2y$10$BqMp4e7Hqthi1Z.J58gKv.YqZVK.uRNGBOl57jfI6eApYPCqYXcZK', '../uploads/42510102022_1768269761.jpg', '2026-01-13 02:02:42'),
(6, 'adli', 'ma2029@gmail.com', '52101323044', '$2y$10$6pZI64cdjvvVcsTzF3RxQOLi6H4scjPwKNOs5Y3yuELEnb75g0Nq.', '../uploads/52101323044_1768272353.jpg', '2026-01-13 02:45:53'),
(7, 'haikal', '122haikal@gmail.com', '521011332', '$2y$10$CREqcFg8f7krC4TiyCKNoOC8vYkXEyiSSxksDxJAxAexmzia6lIFu', '../uploads/521011332_1768379717.jpg', '2026-01-14 08:35:17');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_userdata`
--

CREATE TABLE `deleted_userdata` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `phoneUser` varchar(20) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `choice` int(11) NOT NULL,
  `user_image_path1` varchar(255) DEFAULT NULL,
  `user_image_path2` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by_admin_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `founditemreport`
--

CREATE TABLE `founditemreport` (
  `resolution_id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `floor_level` int(2) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `secret_note` text DEFAULT NULL,
  `picture_path` varchar(255) DEFAULT NULL,
  `evidence_description` text DEFAULT NULL,
  `evidence_picture_path` varchar(255) DEFAULT NULL,
  `date_found` datetime DEFAULT current_timestamp(),
  `item_picture` varchar(255) DEFAULT NULL,
  `resolved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `founditemreport`
--

INSERT INTO `founditemreport` (`resolution_id`, `found_item_id`, `admin_id`, `user_id`, `floor_level`, `item_name`, `item_description`, `secret_note`, `picture_path`, `evidence_description`, `evidence_picture_path`, `date_found`, `item_picture`, `resolved_at`) VALUES
(10, 26, 4, 5, 13, 'Calculator casio FX570X', 'Floor Level: 13', NULL, NULL, 'He know about hellow kitty sticker. ', '', '2026-01-12 21:53:55', '../uploads/item_6964fcf375a7f.jpg', '2026-01-12 14:12:25'),
(11, 29, 4, 5, 9, 'brand \'nothing\' headphone', 'Floor Level: 9', NULL, NULL, 'dia tahu ini rosak', '', '2026-01-12 22:26:32', '../uploads/item_6965049865183.jpg', '2026-01-12 14:29:33'),
(12, 30, 4, 5, 13, 'stanley tumbler', 'Floor Level: 13', NULL, NULL, '', '../uploads/evidence_1768272773_stanley bottle water.jpg', '2026-01-13 10:51:06', '../uploads/item_6965b31ae184e.jpg', '2026-01-13 02:52:53'),
(13, 31, 4, 5, 3, 'tumbler', 'Floor Level: 3', NULL, NULL, 'know what the engrave name\'s write', '', '2026-01-14 16:42:15', '../uploads/item_696756e76becb.jpg', '2026-01-14 08:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `founditemreportfornonuser`
--

CREATE TABLE `founditemreportfornonuser` (
  `resolution_id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `floor_level` int(2) NOT NULL,
  `owner_student_staff_id` varchar(50) DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `owner_email` varchar(255) DEFAULT NULL,
  `owner_phone` varchar(20) DEFAULT NULL,
  `owner_id_picture` varchar(255) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `secret_note` text DEFAULT NULL,
  `picture_path` varchar(255) DEFAULT NULL,
  `evidence_description` text DEFAULT NULL,
  `evidence_picture_path` varchar(255) DEFAULT NULL,
  `date_found` datetime DEFAULT current_timestamp(),
  `item_picture` varchar(255) DEFAULT NULL,
  `resolved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `founditemreportfornonuser`
--

INSERT INTO `founditemreportfornonuser` (`resolution_id`, `found_item_id`, `admin_id`, `floor_level`, `owner_student_staff_id`, `owner_name`, `owner_email`, `owner_phone`, `owner_id_picture`, `item_name`, `item_description`, `secret_note`, `picture_path`, `evidence_description`, `evidence_picture_path`, `date_found`, `item_picture`, `resolved_at`) VALUES
(5, 23, 4, 9, '52101323044', 'adli', 'ma3450533@gmail.com', '010 426 9066', '../uploads/ids_1767879090_Student ID.jpg', 'Leather lanyard', 'Floor Level: 9', NULL, NULL, '', '../uploads/evidence_1767879090_Student ID.jpg', '2026-01-07 21:58:41', '../uploads/item_695e6691b9fa8.jpg', '2026-01-08 13:31:30');

-- --------------------------------------------------------

--
-- Table structure for table `found_items`
--

CREATE TABLE `found_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `floor_level` int(2) NOT NULL,
  `picture_path` varchar(255) NOT NULL,
  `secret_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_items`
--

INSERT INTO `found_items` (`id`, `item_name`, `floor_level`, `picture_path`, `secret_notes`, `created_at`) VALUES
(22, 'Blue water bottle', 7, '../uploads/item_695e52e6d1d0c.jpg', 'Have sticker', '2026-01-07 20:34:46'),
(25, 'Fedora', 15, '../uploads/item_69634d28ac4f6.jpg', 'Red ribbon', '2026-01-11 15:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `missingitemadminside`
--

CREATE TABLE `missingitemadminside` (
  `report_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `floor` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `item_picture_path` varchar(255) DEFAULT NULL,
  `picture_type` varchar(20) NOT NULL,
  `reported_by_name` varchar(255) NOT NULL,
  `reported_by_id` varchar(50) NOT NULL,
  `report_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `missingitemadminside`
--

INSERT INTO `missingitemadminside` (`report_id`, `item_name`, `floor`, `description`, `item_picture_path`, `picture_type`, `reported_by_name`, `reported_by_id`, `report_date`) VALUES
(15, 'Kunci Kereta Honda Jazz', 13, 'Kunci kereta honda jazz berwarna hitam tapi tiada kunci', '../uploads/item_695e6aa250f4e.jpg', 'Photo', 'Mohamad Haikal Al amin Bin Mohd Sofian', '52101323404', '2026-01-07 14:16:02');

-- --------------------------------------------------------

--
-- Table structure for table `missingitems`
--

CREATE TABLE `missingitems` (
  `report_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_level` int(11) NOT NULL,
  `date_lost` date NOT NULL,
  `description` text NOT NULL,
  `picture_type` varchar(20) NOT NULL,
  `item_picture_path` varchar(255) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `missingitems`
--

INSERT INTO `missingitems` (`report_id`, `user_id`, `item_name`, `item_level`, `date_lost`, `description`, `picture_type`, `item_picture_path`, `reported_at`) VALUES
(24, 4, 'headphone', 12, '2025-12-09', 'Black last seen at 1205', 'Placeholder', '../uploads/item_695db371f2bf94.97700215.jpg', '2026-01-07 01:14:25'),
(27, 4, 'Poco x7 pro', 21, '2025-12-28', 'Last seen 2107. It has hello kitty sticker. Colour, half yellow and half black', 'Placeholder', '../uploads/item_695e502d6f86c4.21018241.jpg', '2026-01-07 12:23:09'),
(30, 4, 'Laptop MSI Thin UC', 18, '2026-01-07', 'Last seen 1808. Have anime girl wallpaper', 'Real', '../uploads/item_695e50b561f5f5.61606030.jpg', '2026-01-07 12:25:25'),
(31, 4, 'Poco x8', 10, '2026-01-04', 'Last seen at Coway because I misplace it.', 'Placeholder', '../uploads/item_695e5121538369.50739459.jpg', '2026-01-07 12:27:13'),
(32, 4, 'POCOPHONE 7 BLUE', 10, '2026-01-06', 'Inside of the Man toilet. I forgot to bring it', 'Real', '../uploads/item_695e51a4b47004.52705845.jpg', '2026-01-07 12:29:24'),
(41, 5, 'Headphone', 4, '2026-01-22', 'blue', 'Placeholder', '../uploads/item_696505e0b9f391.50098899.jpg', '2026-01-12 14:32:00');

-- --------------------------------------------------------

--
-- Table structure for table `userdata`
--

CREATE TABLE `userdata` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `phoneUser` varchar(20) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `choice` int(11) NOT NULL,
  `user_image_path1` varchar(255) DEFAULT NULL,
  `user_image_path2` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userdata`
--

INSERT INTO `userdata` (`id`, `email`, `username`, `phoneUser`, `id_number`, `choice`, `user_image_path1`, `user_image_path2`, `password_hash`, `created_at`) VALUES
(3, 'ma3450533@gmail.com', 'Mohamad Adli', '010-4269066', '521987654', 1, '../uploads/3_profile_1765947822.jpg', '../uploads/id_3_1765948400.jpg', '1234567', '2025-12-17 03:52:40'),
(4, 'adli.yusoff@s.unikl.edu.my', 'ADLI', '0123456777', '52101323044', 1, '../uploads/user_pic_69423c409fd253.11566190.jpg', '../uploads/student_id_69423c409fe8e6.16032179.jpg', '12345', '2025-12-17 05:14:40'),
(5, 'Haikal@yahoo.com', 'Mohamad Haikal Al-Amin Bin Mohd Sofian', '013-23445555', '52101323404', 1, '../uploads/user_pic_695e0699f2c6d7.30073505.jpg', '../uploads/student_id_695e0699f3ec46.21027901.jpg', '123456', '2026-01-07 07:09:14'),
(7, 'haikal1232@gmail.com', 'haikal', '012211122', '5210113132', 1, '../uploads/user_pic_696753109c6654.30866279.jpg', '../uploads/student_id_696753109d24e6.31907269.jpg', '1232455', '2026-01-14 08:25:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admindata`
--
ALTER TABLE `admindata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `AdminName` (`AdminName`),
  ADD UNIQUE KEY `AdminEmail` (`AdminEmail`),
  ADD UNIQUE KEY `AdminID` (`AdminID`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deleted_userdata`
--
ALTER TABLE `deleted_userdata`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `founditemreport`
--
ALTER TABLE `founditemreport`
  ADD PRIMARY KEY (`resolution_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `founditemreportfornonuser`
--
ALTER TABLE `founditemreportfornonuser`
  ADD PRIMARY KEY (`resolution_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `found_items`
--
ALTER TABLE `found_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `missingitemadminside`
--
ALTER TABLE `missingitemadminside`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `missingitems`
--
ALTER TABLE `missingitems`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `userdata`
--
ALTER TABLE `userdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phoneUser` (`phoneUser`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admindata`
--
ALTER TABLE `admindata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `founditemreport`
--
ALTER TABLE `founditemreport`
  MODIFY `resolution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `founditemreportfornonuser`
--
ALTER TABLE `founditemreportfornonuser`
  MODIFY `resolution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `found_items`
--
ALTER TABLE `found_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `missingitemadminside`
--
ALTER TABLE `missingitemadminside`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `missingitems`
--
ALTER TABLE `missingitems`
  MODIFY `report_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `userdata`
--
ALTER TABLE `userdata`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `founditemreport`
--
ALTER TABLE `founditemreport`
  ADD CONSTRAINT `founditemreport_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admindata` (`id`);

--
-- Constraints for table `founditemreportfornonuser`
--
ALTER TABLE `founditemreportfornonuser`
  ADD CONSTRAINT `founditemreportfornonuser_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admindata` (`id`);

--
-- Constraints for table `missingitems`
--
ALTER TABLE `missingitems`
  ADD CONSTRAINT `missingitems_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `userdata` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
