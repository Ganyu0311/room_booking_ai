-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2026 at 02:47 PM
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
-- Database: `room_booking_ai`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_logs`
--

CREATE TABLE `ai_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `query` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `check_in_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `date`, `start_time`, `end_time`, `status`, `check_in_time`) VALUES
(51, 3, 1, '2025-12-28', '09:00:00', '09:30:00', 'expired', NULL),
(52, 3, 5, '2025-12-28', '12:00:00', '12:30:00', 'expired', NULL),
(53, 2, 23, '2025-12-29', '08:00:00', '08:30:00', 'cancelled', NULL),
(56, 2, 13, '2025-12-29', '15:00:00', '15:30:00', 'expired', NULL),
(58, 3, 13, '2025-12-30', '17:00:00', '17:30:00', 'expired', NULL),
(59, 2, 18, '2025-12-31', '08:00:00', '08:30:00', 'expired', NULL),
(60, 2, 5, '2025-12-31', '08:00:00', '08:30:00', 'expired', NULL),
(62, 5, 34, '2026-01-01', '15:00:00', '15:30:00', 'expired', NULL),
(65, 4, 11, '2025-12-31', '18:00:00', '18:30:00', 'checked_in', '2025-12-31 18:03:30'),
(67, 5, 11, '2025-12-31', '18:30:00', '19:00:00', 'checked_in', '2025-12-31 18:35:51'),
(69, 3, 5, '2025-12-31', '19:30:00', '20:00:00', 'checked_in', '2025-12-31 19:32:39'),
(70, 4, 25, '2025-12-31', '19:30:00', '20:00:00', 'expired', NULL),
(71, 5, 54, '2025-12-31', '19:30:00', '20:00:00', 'expired', NULL),
(73, 5, 1, '2026-01-02', '08:00:00', '08:30:00', 'cancelled', NULL),
(74, 5, 23, '2026-01-01', '17:30:00', '18:00:00', 'checked_in', '2026-01-01 17:31:00'),
(77, 4, 5, '2026-01-01', '18:00:00', '18:30:00', 'expired', NULL),
(79, 5, 4, '2026-01-01', '19:00:00', '19:30:00', 'expired', NULL),
(80, 5, 39, '2026-01-01', '19:00:00', '19:30:00', 'cancelled', NULL),
(85, 5, 2, '2026-01-01', '19:00:00', '19:30:00', 'cancelled', NULL),
(99, 5, 6, '2026-01-02', '08:00:00', '08:30:00', 'cancelled', NULL),
(100, 5, 41, '2026-01-02', '08:00:00', '08:30:00', 'expired', NULL),
(101, 3, 33, '2026-01-02', '17:00:00', '17:30:00', 'checked_in', '2026-01-02 17:01:38'),
(102, 4, 17, '2026-01-02', '17:00:00', '17:30:00', 'expired', NULL),
(104, 2, 27, '2026-01-02', '17:30:00', '18:00:00', 'cancelled', NULL),
(105, 5, 9, '2026-01-02', '18:00:00', '18:30:00', 'cancelled', NULL),
(106, 5, 45, '2026-01-02', '18:30:00', '19:00:00', 'cancelled', NULL),
(107, 5, 45, '2026-01-02', '19:30:00', '20:00:00', 'expired', NULL),
(108, 5, 50, '2026-01-03', '08:00:00', '08:30:00', 'cancelled', NULL),
(109, 5, 48, '2026-01-03', '09:30:00', '10:00:00', 'cancelled', NULL),
(110, 5, 48, '2026-01-03', '09:30:00', '10:00:00', 'cancelled', NULL),
(111, 5, 6, '2026-01-03', '08:00:00', '08:30:00', 'cancelled', NULL),
(112, 5, 6, '2026-01-03', '08:00:00', '08:30:00', 'cancelled', NULL),
(113, 3, 53, '2026-01-03', '08:00:00', '08:30:00', 'cancelled', NULL),
(114, 3, 53, '2026-01-03', '08:00:00', '08:30:00', 'cancelled', NULL),
(115, 5, 47, '2026-01-03', '08:00:00', '08:30:00', 'expired', NULL),
(116, 4, 3, '2026-01-04', '10:00:00', '11:00:00', 'expired', NULL),
(117, 6, 44, '2026-01-04', '17:00:00', '17:30:00', 'expired', NULL),
(118, 1, 41, '2026-01-04', '05:00:00', '03:00:00', 'cancelled', NULL),
(119, 3, 3, '2026-01-04', '19:30:00', '20:00:00', 'checked_in', '2026-01-04 19:31:35'),
(120, 6, 52, '2026-01-05', '18:00:00', '18:30:00', 'cancelled', NULL),
(121, 6, 25, '2026-01-05', '18:00:00', '18:30:00', 'checked_in', '2026-01-05 18:16:11'),
(122, 6, 30, '2026-01-05', '18:00:00', '18:30:00', 'checked_in', '2026-01-05 18:07:11'),
(123, 6, 41, '2026-01-05', '18:30:00', '19:00:00', 'checked_in', '2026-01-05 18:42:13'),
(124, 4, 43, '2026-01-05', '18:30:00', '19:00:00', 'checked_in', '2026-01-05 18:42:56'),
(125, 4, 3, '2026-01-05', '19:00:00', '20:00:00', 'checked_in', '2026-01-05 19:22:12'),
(126, 6, 41, '2026-01-05', '19:30:00', '20:00:00', 'checked_in', '2026-01-05 19:30:07'),
(127, 6, 3, '2026-01-06', '08:00:00', '08:30:00', 'expired', NULL),
(128, 6, 4, '2026-01-06', '08:00:00', '08:30:00', 'cancelled', NULL),
(129, 3, 56, '2026-01-06', '15:00:00', '16:00:00', 'expired', NULL),
(130, 5, 13, '2026-01-07', '15:30:00', '16:00:00', 'expired', NULL),
(131, 2, 34, '2026-01-08', '19:00:00', '19:30:00', 'checked_in', '2026-01-08 19:07:00'),
(132, 2, 14, '2026-01-08', '19:00:00', '19:30:00', 'checked_in', '2026-01-08 19:07:08'),
(133, 3, 3, '2026-01-09', '15:30:00', '16:00:00', 'expired', NULL),
(134, 2, 5, '2026-01-10', '19:30:00', '20:00:00', 'expired', NULL),
(135, 2, 19, '2026-01-10', '16:30:00', '17:00:00', 'expired', NULL),
(136, 5, 52, '2026-01-10', '17:30:00', '18:00:00', 'checked_in', '2026-01-10 17:31:32'),
(137, 6, 48, '2026-01-10', '17:30:00', '18:00:00', 'checked_in', '2026-01-10 17:31:41'),
(138, 6, 22, '2026-01-10', '19:30:00', '20:00:00', 'expired', NULL),
(139, 5, 24, '2026-01-18', '12:00:00', '12:30:00', 'expired', NULL),
(140, 5, 5, '2026-01-19', '15:00:00', '16:00:00', 'expired', NULL),
(141, 5, 24, '2026-01-19', '08:00:00', '08:30:00', 'checked_in', '2026-01-19 08:02:32'),
(142, 3, 11, '2026-01-19', '08:00:00', '08:30:00', 'expired', NULL),
(143, 4, 3, '2026-01-19', '10:00:00', '11:00:00', 'expired', NULL),
(144, 4, 3, '2026-01-19', '08:00:00', '08:30:00', 'expired', NULL),
(145, 3, 18, '2026-01-19', '08:30:00', '09:00:00', 'cancelled', NULL),
(146, 3, 18, '2026-01-19', '08:30:00', '09:00:00', 'expired', NULL),
(150, 6, 17, '2026-01-19', '15:00:00', '16:00:00', 'expired', NULL),
(151, 6, 28, '2026-01-19', '13:30:00', '14:00:00', 'expired', NULL),
(152, 7, 9, '2026-01-20', '15:00:00', '16:00:00', 'expired', NULL),
(153, 7, 6, '2026-01-20', '17:00:00', '17:30:00', 'cancelled', NULL),
(154, 7, 3, '2026-01-20', '15:00:00', '15:30:00', 'expired', NULL),
(155, 4, 17, '2026-01-20', '16:00:00', '16:30:00', 'expired', NULL),
(156, 8, 4, '2026-01-20', '16:30:00', '17:00:00', 'cancelled', NULL),
(157, 8, 5, '2026-01-20', '16:30:00', '17:00:00', 'expired', NULL),
(158, 5, 3, '2026-01-21', '10:00:00', '11:00:00', 'expired', NULL),
(159, 2, 4, '2026-01-20', '17:00:00', '17:30:00', 'expired', NULL),
(160, 2, 3, '2026-01-20', '17:00:00', '17:30:00', 'expired', NULL),
(161, 2, 1, '2026-01-21', '08:00:00', '08:30:00', 'expired', NULL),
(162, 5, 1, '2026-01-30', '08:00:00', '08:30:00', 'expired', NULL),
(163, 5, 17, '2026-01-30', '12:00:00', '12:30:00', 'expired', NULL),
(164, 11, 37, '2026-02-06', '09:00:00', '09:30:00', 'expired', NULL),
(165, 11, 5, '2026-02-06', '09:00:00', '09:30:00', 'expired', NULL),
(166, 11, 3, '2026-02-07', '10:00:00', '11:00:00', 'expired', NULL),
(167, 11, 17, '2026-02-07', '02:00:00', '04:00:00', 'expired', NULL),
(168, 11, 5, '2026-02-07', '10:00:00', '11:00:00', 'expired', NULL),
(169, 12, 45, '2026-02-06', '09:00:00', '09:30:00', 'expired', NULL),
(170, 12, 48, '2026-02-06', '09:00:00', '10:30:00', 'expired', NULL),
(171, 13, 5, '2026-02-06', '09:30:00', '10:00:00', 'checked_in', '2026-02-06 09:36:30'),
(172, 5, 20, '2026-02-07', '02:00:00', '07:00:00', 'expired', NULL),
(173, 5, 9, '2026-10-13', '18:30:00', '19:00:00', 'expired', NULL),
(174, 5, 4, '2026-10-16', '08:00:00', '08:30:00', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `checkin_logs`
--

CREATE TABLE `checkin_logs` (
  `log_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `checkin_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkin_logs`
--

INSERT INTO `checkin_logs` (`log_id`, `booking_id`, `user_id`, `room_id`, `checkin_time`) VALUES
(7, 65, 4, 11, '2025-12-31 18:03:30'),
(9, 67, 5, 11, '2025-12-31 18:35:51'),
(12, 69, 3, 5, '2025-12-31 19:32:39'),
(13, 74, 5, 23, '2026-01-01 17:31:00'),
(14, 101, 3, 33, '2026-01-02 17:01:38'),
(15, 119, 3, 3, '2026-01-04 19:31:35'),
(16, 122, 6, 30, '2026-01-05 18:07:11'),
(17, 121, 6, 25, '2026-01-05 18:16:11'),
(18, 123, 6, 41, '2026-01-05 18:42:13'),
(19, 124, 4, 43, '2026-01-05 18:42:56'),
(20, 125, 4, 3, '2026-01-05 19:22:12'),
(21, 126, 6, 41, '2026-01-05 19:30:07'),
(22, 131, 2, 34, '2026-01-08 19:07:00'),
(23, 132, 2, 14, '2026-01-08 19:07:08'),
(24, 136, 5, 52, '2026-01-10 17:31:32'),
(25, 137, 6, 48, '2026-01-10 17:31:41'),
(26, 141, 5, 24, '2026-01-19 08:02:32'),
(27, 171, 13, 5, '2026-02-06 09:36:30');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `equipment` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `capacity`, `equipment`, `location`, `status`) VALUES
(1, 'LAB 1', 30, 'GAMING PC', 'LEVEL 2 / APU CAMPUS', 'active'),
(2, 'LAB2', 20, 'PROJECTOR', 'LEVEL 3 / APU CAMPUS', 'active'),
(3, 'LAB3', 25, 'PROJECTOR', 'LEVEL 4 / APU CAMPUS', 'active'),
(4, 'LAB5', 25, 'PROJECTOR', 'LEVEL 5 / APU CAMPUS', 'active'),
(5, 'LAB6', 15, 'WHITEBOARD,PROJECTOR', 'LEVEL 6 / APU CAMPUS', 'active'),
(6, 'A-1-1', 12, 'WHITEBOARD,PROJECTOR', 'LEVEL 1 / APU CAMPUS', 'active'),
(7, 'A-1-2', 18, 'PROJECTOR,SPEAKER SYSTEM', 'LEVEL 1 / APU CAMPUS', 'active'),
(8, 'A-1-3', 25, 'SMART TV,WHITEBOARD', 'LEVEL 1 / APU CAMPUS', 'active'),
(9, 'A-1-4', 30, 'PROJECTOR,WHITEBOARD,MIC SYSTEM', 'LEVEL 1 / APU CAMPUS', 'active'),
(10, 'A-1-5', 45, 'GAMING PC,PROJECTOR,WHITEBOARD', 'LEVEL 1 / APU CAMPUS', 'active'),
(11, 'A-1-6', 20, 'VIDEO CONFERENCING,SMART TV', 'LEVEL 1 / APU CAMPUS', 'active'),
(12, 'A-1-7', 15, 'WHITEBOARD', 'LEVEL 1 / APU CAMPUS', 'active'),
(13, 'A-1-8', 35, 'PROJECTOR,SMART TV,SPEAKER SYSTEM', 'LEVEL 1 / APU CAMPUS', 'active'),
(14, 'A-2-1', 10, 'WHITEBOARD,PROJECTOR', 'LEVEL 2 / APU CAMPUS', 'active'),
(15, 'A-2-2', 22, 'PROJECTOR,DOCUMENT CAMERA', 'LEVEL 2 / APU CAMPUS', 'active'),
(16, 'A-2-3', 28, 'SMART TV,WHITEBOARD', 'LEVEL 2 / APU CAMPUS', 'active'),
(17, 'A-2-4', 40, 'PROJECTOR,WHITEBOARD,VIDEO CONFERENCING', 'LEVEL 2 / APU CAMPUS', 'active'),
(18, 'A-2-5', 50, 'GAMING PC,PROJECTOR,SMART TV', 'LEVEL 2 / APU CAMPUS', 'active'),
(19, 'A-2-6', 16, 'WHITEBOARD,SPEAKER SYSTEM', 'LEVEL 2 / APU CAMPUS', 'active'),
(20, 'A-2-7', 33, 'PROJECTOR,MIC SYSTEM', 'LEVEL 2 / APU CAMPUS', 'active'),
(21, 'A-2-8', 26, 'SMART TV,VIDEO CONFERENCING', 'LEVEL 2 / APU CAMPUS', 'active'),
(22, 'A-3-1', 14, 'WHITEBOARD', 'LEVEL 3 / APU CAMPUS', 'active'),
(23, 'A-3-2', 20, 'PROJECTOR,WHITEBOARD', 'LEVEL 3 / APU CAMPUS', 'active'),
(24, 'A-3-3', 27, 'SMART TV,SPEAKER SYSTEM', 'LEVEL 3 / APU CAMPUS', 'active'),
(25, 'A-3-4', 38, 'PROJECTOR,VIDEO CONFERENCING,MIC SYSTEM', 'LEVEL 3 / APU CAMPUS', 'active'),
(26, 'A-3-5', 48, 'GAMING PC,PROJECTOR,WHITEBOARD,SPEAKER SYSTEM', 'LEVEL 3 / APU CAMPUS', 'active'),
(27, 'A-3-6', 24, 'DOCUMENT CAMERA,WHITEBOARD', 'LEVEL 3 / APU CAMPUS', 'active'),
(28, 'A-3-7', 32, 'SMART TV,WHITEBOARD,PROJECTOR', 'LEVEL 3 / APU CAMPUS', 'active'),
(29, 'A-3-8', 19, 'PROJECTOR,SPEAKER SYSTEM', 'LEVEL 3 / APU CAMPUS', 'active'),
(30, 'A-3-9', 41, 'VIDEO CONFERENCING,SMART TV,MIC SYSTEM', 'LEVEL 3 / APU CAMPUS', 'active'),
(31, 'A-4-1', 11, 'WHITEBOARD', 'LEVEL 4 / APU CAMPUS', 'active'),
(32, 'A-4-2', 17, 'PROJECTOR,WHITEBOARD', 'LEVEL 4 / APU CAMPUS', 'active'),
(33, 'A-4-3', 23, 'SMART TV,WHITEBOARD', 'LEVEL 4 / APU CAMPUS', 'active'),
(34, 'A-4-4', 29, 'PROJECTOR,DOCUMENT CAMERA,SPEAKER SYSTEM', 'LEVEL 4 / APU CAMPUS', 'active'),
(35, 'A-4-5', 36, 'VIDEO CONFERENCING,PROJECTOR,MIC SYSTEM', 'LEVEL 4 / APU CAMPUS', 'active'),
(36, 'A-4-6', 44, 'PROJECTOR,WHITEBOARD,SPEAKER SYSTEM', 'LEVEL 4 / APU CAMPUS', 'active'),
(37, 'A-4-7', 50, 'GAMING PC,SMART TV,PROJECTOR', 'LEVEL 4 / APU CAMPUS', 'active'),
(38, 'A-4-8', 21, 'WHITEBOARD,SPEAKER SYSTEM', 'LEVEL 4 / APU CAMPUS', 'active'),
(39, 'A-5-1', 13, 'WHITEBOARD', 'LEVEL 5 / APU CAMPUS', 'active'),
(40, 'A-5-2', 19, 'PROJECTOR,WHITEBOARD', 'LEVEL 5 / APU CAMPUS', 'active'),
(41, 'A-5-3', 26, 'SMART TV,SPEAKER SYSTEM', 'LEVEL 5 / APU CAMPUS', 'active'),
(42, 'A-5-4', 31, 'PROJECTOR,VIDEO CONFERENCING', 'LEVEL 5 / APU CAMPUS', 'active'),
(43, 'A-5-5', 37, 'PROJECTOR,WHITEBOARD,MIC SYSTEM', 'LEVEL 5 / APU CAMPUS', 'active'),
(44, 'A-5-6', 42, 'SMART TV,VIDEO CONFERENCING,SPEAKER SYSTEM', 'LEVEL 5 / APU CAMPUS', 'active'),
(45, 'A-5-7', 49, 'GAMING PC,PROJECTOR,WHITEBOARD', 'LEVEL 5 / APU CAMPUS', 'active'),
(46, 'A-5-8', 34, 'DOCUMENT CAMERA,PROJECTOR', 'LEVEL 5 / APU CAMPUS', 'active'),
(47, 'A-6-1', 10, 'WHITEBOARD', 'LEVEL 6 / APU CAMPUS', 'active'),
(48, 'A-6-2', 15, 'PROJECTOR,WHITEBOARD', 'LEVEL 6 / APU CAMPUS', 'active'),
(49, 'A-6-3', 22, 'SMART TV,SPEAKER SYSTEM', 'LEVEL 6 / APU CAMPUS', 'active'),
(50, 'A-6-4', 28, 'PROJECTOR,VIDEO CONFERENCING,MIC SYSTEM', 'LEVEL 6 / APU CAMPUS', 'active'),
(51, 'A-6-5', 33, 'PROJECTOR,DOCUMENT CAMERA,WHITEBOARD', 'LEVEL 6 / APU CAMPUS', 'active'),
(52, 'A-6-6', 40, 'SMART TV,PROJECTOR,WHITEBOARD', 'LEVEL 6 / APU CAMPUS', 'active'),
(53, 'A-6-7', 46, 'GAMING PC,PROJECTOR,SMART TV,SPEAKER SYSTEM', 'LEVEL 6 / APU CAMPUS', 'active'),
(54, 'A-6-8', 50, 'PROJECTOR,WHITEBOARD,VIDEO CONFERENCING,MIC SYSTEM', 'LEVEL 6 / APU CAMPUS', 'active'),
(56, 'LAB4', 30, 'WHITEBOARD.PROJECTOR', 'LEVEL 4 / APU CAMPUS', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `student_directory`
--

CREATE TABLE `student_directory` (
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `course` varchar(150) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_directory`
--

INSERT INTO `student_directory` (`student_id`, `full_name`, `course`, `status`) VALUES
('TP063536', 'TAVANENDRAN A/L KALAIRASAN @ ANTHONY', 'MSc in Software Engineering', 'active'),
('TP066336', 'ARISH A/L TAYALAN', 'MSc in Software Engineering', 'active'),
('TP085603', 'AYANA KISHIMOTO', 'MSc in Software Engineering', 'active'),
('TP087279', 'MIN THAT WAI', 'MSc in Software Engineering', 'active'),
('TP090101', 'MUHAMMAD AIDIL HAKIM', 'MSc in Software Engineering', 'active'),
('TP090102', 'NUR SYAFIQAH BINTI AZMI', 'MSc in Software Engineering', 'active'),
('TP090103', 'TAN WEI JIE', 'MSc in Software Engineering', 'active'),
('TP090104', 'LIM JIA YI', 'MSc in Software Engineering', 'active'),
('TP090105', 'ONG KAI SHEN', 'MSc in Software Engineering', 'active'),
('TP090106', 'CHONG YI XUAN', 'MSc in Software Engineering', 'active'),
('TP090107', 'LEE YONG HUI', 'MSc in Software Engineering', 'active'),
('TP090108', 'GOH ZE YU', 'MSc in Software Engineering', 'active'),
('TP090109', 'WONG ZI HAO', 'MSc in Software Engineering', 'active'),
('TP090110', 'NG CHUN HAO', 'MSc in Software Engineering', 'active'),
('TP090111', 'RAJESH KUMAR A/L MURUGAN', 'MSc in Software Engineering', 'active'),
('TP090112', 'PRIYA A/P RAMESH', 'MSc in Software Engineering', 'active'),
('TP090113', 'KAVIN A/L SUBRAMANIAM', 'MSc in Software Engineering', 'active'),
('TP090114', 'SHARMILA A/P GANESAN', 'MSc in Software Engineering', 'active'),
('TP090115', 'VISHAL A/L ARUMUGAM', 'MSc in Software Engineering', 'active'),
('TP090116', 'ANITHA A/P KANNAN', 'MSc in Software Engineering', 'active'),
('TP090117', 'SURESH A/L RAMASAMY', 'MSc in Software Engineering', 'active'),
('TP090118', 'DIVYA A/P SELVAM', 'MSc in Software Engineering', 'active'),
('TP090119', 'NAVEEN A/L KRISHNAN', 'MSc in Software Engineering', 'active'),
('TP090120', 'MEERA A/P SATHISH', 'MSc in Software Engineering', 'active'),
('TP090121', 'NUR AINA BINTI RAZAK', 'MSc in Data Science', 'active'),
('TP090122', 'AHMAD FARIS BIN HASSAN', 'MSc in Data Science', 'active'),
('TP090123', 'SITI NURSYAZWANI BINTI SALLEH', 'MSc in Data Science', 'active'),
('TP090124', 'MUHAMMAD DANISH BIN ZULKIFLI', 'MSc in Data Science', 'active'),
('TP090125', 'NURUL HUSNA BINTI ISMAIL', 'MSc in Data Science', 'active'),
('TP090126', 'CHAN YEE LING', 'MSc in Artificial Intelligence', 'active'),
('TP090127', 'KOH MING XUAN', 'MSc in Artificial Intelligence', 'active'),
('TP090128', 'TEOH JIA WEI', 'MSc in Artificial Intelligence', 'active'),
('TP090129', 'TAN SHU EN', 'MSc in Artificial Intelligence', 'active'),
('TP090130', 'YAP XIN YI', 'MSc in Artificial Intelligence', 'active'),
('TP090131', 'GANESH A/L BALAKRISHNAN', 'MSc in Cybersecurity', 'active'),
('TP090132', 'KIRAN A/L SURENDRAN', 'MSc in Cybersecurity', 'active'),
('TP090133', 'RESHMA A/P PRABAKARAN', 'MSc in Cybersecurity', 'active'),
('TP090134', 'ARJUN A/L NARAYANAN', 'MSc in Cybersecurity', 'active'),
('TP090135', 'PAVITHRA A/P KUMARAN', 'MSc in Cybersecurity', 'active'),
('TP090136', 'NUR ADAM BIN MOKHTAR', 'MSc in Software Engineering', 'inactive'),
('TP090137', 'NUR HAZIQ BIN ADNAN', 'MSc in Software Engineering', 'active'),
('TP090138', 'NUR IZZAH BINTI MOHD NOR', 'MSc in Software Engineering', 'active'),
('TP090139', 'MUHAMMAD HAZIM BIN ROSLI', 'MSc in Software Engineering', 'active'),
('TP090140', 'NUR AFIQAH BINTI HARUN', 'MSc in Software Engineering', 'active'),
('TP090141', 'LIM SZE YING', 'MSc in Software Engineering', 'active'),
('TP090142', 'ONG JIA HAO', 'MSc in Software Engineering', 'active'),
('TP090143', 'LEE PEI EN', 'MSc in Software Engineering', 'active'),
('TP090144', 'GOH JUN WEI', 'MSc in Software Engineering', 'active'),
('TP090145', 'WONG MIN KAI', 'MSc in Software Engineering', 'active'),
('TP090146', 'NG XIN YU', 'MSc in Software Engineering', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','lecturer','admin') DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `student_id`, `name`, `email`, `password`, `role`) VALUES
(1, NULL, 'ARISH', 'arish.t@yahoo.com', '$2y$10$RURdvKdhfj4zhilWTOpPp.ppSIcNbP9Cm0/Wo3tyboQqL3BvrWFiq', 'admin'),
(2, 'TP066336', 'ARISH A/L TAYALAN', 'arishtayalan0@gmail.com', '$2y$10$zLNL5jqUivsfYgsQaEKO5OpXyo857RzMFGpqA/8SbtoidrhNsQYUC', 'student'),
(3, 'TP090113', 'KAVIN A/L SUBRAMANIAM', 'Vighnesh@yahoo.com', '$2y$10$uryoJ1bxALrqfa5Ocr3EmeEdJVAcGRJNSfPKiBf1XKrnjo7mcuTEe', 'student'),
(4, 'TP090134', 'ARJUN A/L NARAYANAN', 'thanees290101@gmail.com', '$2y$10$WlTWzvR06J1q7N1Cks8Qie66grVUT7wVOvRJX72vKjF4vw.W94RgS', 'student'),
(5, 'TP090103', 'TAN WEI JIE', 'luffy290101@gmail.com', '$2y$10$o2l2kFbh1q2z0VN4m1ToNOV1ZfZPqwCsC7oqXkcvX5SMng7DGZuly', 'student'),
(6, 'TP090135', 'PAVITHRA A/P KUMARAN', 'nami@yahoo.com', '$2y$10$n1GvzbKI7mFNQCG/7zHZJ.unwXJEmnXM7SSP6mJFb.KYTKhMckdzq', 'student'),
(7, 'TP085603', 'AYANA KISHIMOTO', 'ayana1710@gmail.com', '$2y$10$rRFTGgzoDvgOqgbad3HSheU1ipZspP6AxNzQGvcQLoXxLUWb85kga', 'student'),
(8, 'TP090101', 'MUHAMMAD AIDIL HAKIM', 'arish1.t@yahoo.com', '$2y$10$4WHLYuBhyqajZRkmOIsXserVBOIYYRRY9jhPYWoEV9NNzstwQWIoW', 'student'),
(9, 'TP090105', 'ONG KAI SHEN', 'ongkai@yahoo.com', '$2y$10$6r/2z72mXe08jucv6guUIe659i.k91wDm/rEyq4o5zZhupHJoka2y', 'student'),
(10, 'TP090106', 'CHONG YI XUAN', 'ching23@yahoo.com', '$2y$10$iiY3Fkn/kRZ0equxUUoUzuuWxJ1jbv088X.YOU9cffjgTSVRfQXX6', 'student'),
(11, 'TP090112', 'PRIYA A/P RAMESH', 'priya@yahoo.com', '$2y$10$HdtwqjpCOxMenHAwA.ZZPukArAELn/wmG0daUjRf.j2aSTNeHzDqO', 'student'),
(12, 'TP090115', 'VISHAL A/L ARUMUGAM', 'vishal@yahoo.com', '$2y$10$6eCL8rhN9bZ7fcxNooOqBe47yJWJRsz4ayroSQPviDRjTf4mPl9bm', 'student'),
(13, 'TP090126', 'CHAN YEE LING', 'chan@yahoo.com', '$2y$10$xcuutCCmxot36T99xZvmfOppqKY0zI/I8scbAFio1pnJlDQktNCNO', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `checkin_logs`
--
ALTER TABLE `checkin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `checkin_logs_ibfk_1` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `student_directory`
--
ALTER TABLE `student_directory`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uniq_email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `uniq_student_id` (`student_id`),
  ADD UNIQUE KEY `uq_student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_logs`
--
ALTER TABLE `ai_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `checkin_logs`
--
ALTER TABLE `checkin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD CONSTRAINT `ai_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `checkin_logs`
--
ALTER TABLE `checkin_logs`
  ADD CONSTRAINT `checkin_logs_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `checkin_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `checkin_logs_ibfk_3` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
