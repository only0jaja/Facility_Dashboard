-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2025 at 05:53 AM
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
-- Database: `facility_control_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_log`
--

CREATE TABLE `access_log` (
  `Log_id` int(11) NOT NULL,
  `User_id` int(11) DEFAULT NULL,
  `Rfid_tag` varchar(50) NOT NULL,
  `Room_id` int(11) NOT NULL,
  `Schedule_id` int(11) DEFAULT NULL,
  `Access_time` datetime DEFAULT current_timestamp(),
  `Access_type` enum('Entry','Exit') NOT NULL,
  `Status` enum('granted','denied') DEFAULT 'denied'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `access_log`
--

INSERT INTO `access_log` (`Log_id`, `User_id`, `Rfid_tag`, `Room_id`, `Schedule_id`, `Access_time`, `Access_type`, `Status`) VALUES
(1, NULL, '1,82 04 10 01', 1, NULL, '2025-10-09 15:38:44', 'Entry', 'denied'),
(2, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:38:56', 'Entry', 'denied'),
(3, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:38:59', 'Entry', 'denied'),
(4, NULL, '1,82 04 10 01', 1, NULL, '2025-10-09 15:40:14', 'Entry', 'denied'),
(5, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:40:19', 'Entry', 'denied'),
(6, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:40:27', 'Entry', 'denied'),
(7, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:45:18', 'Entry', 'denied'),
(8, 1, '82 04 10 01', 1, 3, '2025-10-09 15:48:46', 'Entry', 'granted'),
(9, 2, 'D3 CB B1 38', 1, NULL, '2025-10-09 15:49:41', 'Entry', 'denied'),
(10, NULL, '2,D3 CB B1 38', 2, NULL, '2025-10-09 15:49:47', 'Entry', 'denied'),
(11, NULL, '2,D3 CB B1 38', 2, NULL, '2025-10-09 15:49:51', 'Entry', 'denied'),
(12, 1, '82 04 10 01', 1, 3, '2025-10-09 15:49:54', 'Entry', 'granted'),
(13, 1, '82 04 10 01', 1, 3, '2025-10-09 15:50:13', 'Entry', 'granted'),
(14, 1, '82 04 10 01', 1, 3, '2025-10-09 15:50:43', 'Entry', 'granted'),
(15, NULL, 'A4 12 3D 05', 1, NULL, '2025-10-09 15:51:02', 'Entry', 'denied'),
(16, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 15:51:26', 'Exit', 'granted'),
(17, 1, '82 04 10 01', 1, 3, '2025-10-09 15:51:33', 'Entry', 'granted'),
(18, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 15:51:40', 'Exit', 'granted'),
(19, 1, '82 04 10 01', 1, NULL, '2025-10-09 15:52:05', 'Entry', 'denied'),
(20, 1, '82 04 10 01', 1, NULL, '2025-10-09 15:52:16', 'Entry', 'denied'),
(21, 1, '82 04 10 01', 1, NULL, '2025-10-09 15:52:21', 'Entry', 'denied'),
(22, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:53:03', 'Entry', 'denied'),
(23, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:53:08', 'Entry', 'denied'),
(24, 1, '82 04 10 01', 1, 3, '2025-10-09 15:53:13', 'Entry', 'granted'),
(25, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 15:53:35', 'Exit', 'granted'),
(26, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 15:53:37', 'Entry', 'granted'),
(27, NULL, '2,A4 12 3D 05', 2, NULL, '2025-10-09 15:53:46', 'Entry', 'denied'),
(28, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 16:13:01', 'Entry', 'denied'),
(29, 1, '82 04 10 01', 1, 3, '2025-10-09 16:13:22', 'Entry', 'granted'),
(30, 1, '82 04 10 01', 2, 3, '2025-10-09 16:14:30', 'Entry', 'granted'),
(31, 1, '82 04 10 01', 1, 3, '2025-10-09 16:14:34', 'Entry', 'granted'),
(32, 1, '82 04 10 01', 2, 3, '2025-10-09 16:15:53', 'Entry', 'granted'),
(33, 8, 'A4 12 3D 05', 2, NULL, '2025-10-09 16:15:59', 'Exit', 'granted'),
(34, 2, 'D3 CB B1 38', 2, NULL, '2025-10-09 16:16:03', 'Entry', 'denied'),
(35, 2, 'D3 CB B1 38', 2, 1, '2025-10-09 16:19:29', 'Entry', 'granted'),
(36, 2, 'D3 CB B1 38', 1, 1, '2025-10-09 16:19:32', 'Entry', 'granted'),
(37, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 16:22:40', 'Exit', 'granted'),
(38, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 16:22:43', 'Entry', 'granted'),
(39, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 16:22:51', 'Exit', 'granted'),
(40, 2, 'D3 CB B1 38', 1, NULL, '2025-10-09 16:22:55', 'Entry', 'denied'),
(41, 2, 'D3 CB B1 38', 2, 1, '2025-10-09 16:23:00', 'Entry', 'granted'),
(42, 1, '82 04 10 01', 2, NULL, '2025-10-09 16:23:09', 'Entry', 'denied'),
(43, 1, '82 04 10 01', 1, NULL, '2025-10-09 16:23:12', 'Entry', 'denied'),
(44, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 16:23:46', 'Entry', 'granted'),
(45, 8, 'A4 12 3D 05', 1, NULL, '2025-10-09 16:23:53', 'Exit', 'granted'),
(46, 2, 'D3 CB B1 38', 1, NULL, '2025-10-09 16:23:57', 'Entry', 'denied'),
(47, 2, 'D3 CB B1 38', 2, 1, '2025-10-09 16:24:04', 'Entry', 'granted'),
(48, 2, 'D3 CB B1 38', 2, 1, '2025-10-09 16:24:20', 'Entry', 'granted'),
(49, 2, 'D3 CB B1 38', 2, 1, '2025-10-09 16:24:28', 'Entry', 'granted'),
(50, 1, '82 04 10 01', 2, NULL, '2025-10-09 16:24:50', 'Entry', 'denied'),
(51, 1, '82 04 10 01', 1, 3, '2025-10-09 16:25:01', 'Entry', 'granted'),
(52, 1, '82 04 10 01', 1, 3, '2025-10-09 16:26:30', 'Entry', 'granted'),
(53, 1, '82 04 10 01', 2, NULL, '2025-10-09 16:26:34', 'Entry', 'denied'),
(54, 1, '82 04 10 01', 2, NULL, '2025-10-09 16:28:41', 'Entry', 'denied'),
(55, 1, '82 04 10 01', 2, NULL, '2025-10-09 16:28:43', 'Entry', 'denied'),
(56, 1, '82 04 10 01', 1, 3, '2025-10-09 16:28:45', 'Entry', 'granted'),
(57, 2, 'D3 CB B1 38', 2, 1, '2025-10-09 16:28:52', 'Entry', 'granted'),
(58, 2, 'D3 CB B1 38', 1, NULL, '2025-10-09 16:28:55', 'Entry', 'denied'),
(59, 2, 'D3 CB B1 38', 2, NULL, '2025-10-10 10:28:40', 'Entry', 'denied'),
(60, 2, 'D3 CB B1 38', 2, NULL, '2025-10-10 10:30:37', 'Entry', 'denied'),
(61, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:30:41', 'Entry', 'denied'),
(62, 1, '82 04 10 01', 1, NULL, '2025-10-10 10:39:08', 'Entry', 'denied'),
(63, 1, '82 04 10 01', 1, NULL, '2025-10-10 10:39:11', 'Entry', 'denied'),
(64, 1, '82 04 10 01', 1, NULL, '2025-10-10 10:39:14', 'Entry', 'denied'),
(65, 1, '82 04 10 01', 1, NULL, '2025-10-10 10:39:20', 'Entry', 'denied'),
(66, 1, '82 04 10 01', 1, NULL, '2025-10-10 10:39:23', 'Entry', 'denied'),
(67, 2, 'D3 CB B1 38', 1, NULL, '2025-10-10 10:40:18', 'Entry', 'denied'),
(68, 2, 'D3 CB B1 38', 1, 1, '2025-10-10 10:41:06', 'Entry', 'granted'),
(69, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:41:11', 'Entry', 'granted'),
(70, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:41:19', 'Entry', 'granted'),
(71, 1, '82 04 10 01', 2, 3, '2025-10-10 10:41:27', 'Entry', 'granted'),
(72, 1, '82 04 10 01', 1, 3, '2025-10-10 10:41:31', 'Entry', 'granted'),
(73, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:44:08', 'Entry', 'granted'),
(74, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:45:11', 'Entry', 'granted'),
(75, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:45:47', 'Entry', 'denied'),
(76, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:45:52', 'Entry', 'denied'),
(77, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:45:56', 'Entry', 'granted'),
(78, 2, 'D3 CB B1 38', 1, NULL, '2025-10-10 10:46:13', 'Entry', 'denied'),
(79, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:46:17', 'Entry', 'granted'),
(80, 1, '82 04 10 01', 1, 3, '2025-10-10 10:46:23', 'Entry', 'granted'),
(81, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:46:28', 'Entry', 'denied'),
(82, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:48:03', 'Entry', 'granted'),
(83, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:48:26', 'Entry', 'granted'),
(84, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:48:33', 'Entry', 'granted'),
(85, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 10:48:41', 'Exit', 'granted'),
(86, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 10:48:48', 'Entry', 'granted'),
(87, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:48:56', 'Entry', 'denied'),
(88, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:48:58', 'Entry', 'denied'),
(89, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:49:01', 'Entry', 'denied'),
(90, 1, '82 04 10 01', 2, NULL, '2025-10-10 10:49:04', 'Entry', 'denied'),
(91, 1, '82 04 10 01', 1, 3, '2025-10-10 10:49:06', 'Entry', 'granted'),
(92, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 10:49:10', 'Entry', 'granted'),
(93, 2, 'D3 CB B1 38', 1, NULL, '2025-10-10 10:49:16', 'Entry', 'denied'),
(94, 2, 'D3 CB B1 38', 1, NULL, '2025-10-10 10:49:18', 'Entry', 'denied'),
(95, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 11:02:20', 'Entry', 'granted'),
(96, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 11:02:31', 'Entry', 'granted'),
(97, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 11:02:45', 'Entry', 'granted'),
(98, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:05:31', 'Exit', 'granted'),
(99, 1, '82 04 10 01', 2, NULL, '2025-10-10 11:05:35', 'Entry', 'denied'),
(100, 1, '82 04 10 01', 2, NULL, '2025-10-10 11:05:38', 'Entry', 'denied'),
(101, 1, '82 04 10 01', 2, NULL, '2025-10-10 11:05:47', 'Entry', 'denied'),
(102, 1, '82 04 10 01', 2, NULL, '2025-10-10 11:05:49', 'Entry', 'denied'),
(103, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 11:05:55', 'Entry', 'granted'),
(104, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 11:06:02', 'Entry', 'granted'),
(105, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:06:11', 'Exit', 'granted'),
(106, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:06:17', 'Entry', 'granted'),
(107, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:06:24', 'Exit', 'granted'),
(108, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:06:31', 'Entry', 'granted'),
(109, 1, '82 04 10 01', 2, NULL, '2025-10-10 11:07:24', 'Entry', 'denied'),
(110, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:07:28', 'Exit', 'granted'),
(111, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:07:32', 'Entry', 'granted'),
(112, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:07:40', 'Exit', 'granted'),
(113, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:07:43', 'Entry', 'granted'),
(114, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 11:09:03', 'Entry', 'granted'),
(115, 2, 'D3 CB B1 38', 2, 1, '2025-10-10 11:09:23', 'Entry', 'granted'),
(116, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:09:30', 'Exit', 'granted'),
(117, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:09:33', 'Entry', 'granted'),
(118, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:09:41', 'Exit', 'granted'),
(119, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:10:28', 'Entry', 'granted'),
(120, 8, 'A4 12 3D 05', 1, NULL, '2025-10-10 11:10:36', 'Exit', 'granted'),
(121, 8, 'A4 12 3D 05', 1, NULL, '2025-10-10 11:10:40', 'Entry', 'granted'),
(122, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:13:13', 'Exit', 'granted'),
(123, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:13:15', 'Entry', 'granted'),
(124, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:13:51', 'Exit', 'granted'),
(125, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:13:55', 'Entry', 'granted'),
(126, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:14:04', 'Exit', 'granted'),
(127, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:14:09', 'Entry', 'granted'),
(128, 8, 'A4 12 3D 05', 2, NULL, '2025-10-10 11:14:18', 'Exit', 'granted');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `Room_id` int(11) NOT NULL,
  `Room_code` varchar(50) NOT NULL,
  `Status` enum('Occupied','Unoccupied') DEFAULT 'Unoccupied',
  `Classroom_type` varchar(255) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`Room_id`, `Room_code`, `Status`, `Classroom_type`, `Capacity`) VALUES
(1, 'ROOM101', 'Occupied', 'CLASSROOM', 50),
(2, 'ROOM102', 'Unoccupied', 'CLASSROOM', 30);

-- --------------------------------------------------------

--
-- Table structure for table `course_section`
--

CREATE TABLE `course_section` (
  `CourseSection_id` int(11) NOT NULL,
  `CourseSection` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_section`
--

INSERT INTO `course_section` (`CourseSection_id`, `CourseSection`) VALUES
(2, 'BSCS 1-21'),
(1, 'BSIT 1-11'),
(3, 'BSIT 2-11');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_reader`
--

CREATE TABLE `rfid_reader` (
  `Reader_id` int(11) NOT NULL,
  `Room_id` int(11) NOT NULL,
  `Port_name` varchar(50) NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Last_online` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfid_reader`
--

INSERT INTO `rfid_reader` (`Reader_id`, `Room_id`, `Port_name`, `Status`, `Last_online`) VALUES
(1, 1, 'COM9', 'Active', NULL),
(2, 2, 'COM10', 'Active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `Schedule_id` int(11) NOT NULL,
  `Subject_id` int(11) NOT NULL,
  `Room_id` int(11) NOT NULL,
  `Faculty_id` int(11) NOT NULL,
  `Day` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `Start_time` time NOT NULL,
  `End_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`Schedule_id`, `Subject_id`, `Room_id`, `Faculty_id`, `Day`, `Start_time`, `End_time`) VALUES
(1, 1, 2, 6, 'Fri', '10:00:00', '18:00:00'),
(2, 1, 1, 6, 'Mon', '10:00:00', '12:00:00'),
(3, 2, 1, 8, 'Fri', '10:00:00', '18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_access`
--

CREATE TABLE `schedule_access` (
  `Rule_id` int(11) NOT NULL,
  `Schedule_id` int(11) NOT NULL,
  `CourseSection_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_access`
--

INSERT INTO `schedule_access` (`Rule_id`, `Schedule_id`, `CourseSection_id`) VALUES
(1, 1, 2),
(2, 2, 2),
(3, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `Subject_id` int(11) NOT NULL,
  `Code` varchar(50) NOT NULL,
  `Description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`Subject_id`, `Code`, `Description`) VALUES
(1, 'IT101', 'Introduction to Information Technology'),
(2, 'ITP311', 'Human Computer Interaction');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_id` int(11) NOT NULL,
  `Rfid_tag` varchar(50) NOT NULL,
  `F_name` varchar(100) NOT NULL,
  `L_name` varchar(100) NOT NULL,
  `CourseSection_id` int(11) DEFAULT NULL,
  `Role` enum('Student','Faculty','Admin') DEFAULT 'Student',
  `Status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_id`, `Rfid_tag`, `F_name`, `L_name`, `CourseSection_id`, `Role`, `Status`) VALUES
(1, '82 04 10 01', 'John', 'Doe', 1, 'Student', 'Active'),
(2, 'D3 CB B1 38', 'Jane', 'Smith', 2, 'Student', 'Active'),
(3, 'STU3944', 'Mark', 'Reyes', 3, 'Student', 'Active'),
(6, 'FAC001', 'Anna', 'Cruz', NULL, 'Faculty', 'Active'),
(7, 'FAC002', 'Paul', 'Santos', NULL, 'Faculty', 'Active'),
(8, 'A4 12 3D 05', 'Michael', 'Tan', NULL, 'Admin', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_log`
--
ALTER TABLE `access_log`
  ADD PRIMARY KEY (`Log_id`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `Room_id` (`Room_id`),
  ADD KEY `Schedule_id` (`Schedule_id`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`Room_id`),
  ADD UNIQUE KEY `Room_code` (`Room_code`);

--
-- Indexes for table `course_section`
--
ALTER TABLE `course_section`
  ADD PRIMARY KEY (`CourseSection_id`),
  ADD UNIQUE KEY `CourseSection` (`CourseSection`);

--
-- Indexes for table `rfid_reader`
--
ALTER TABLE `rfid_reader`
  ADD PRIMARY KEY (`Reader_id`),
  ADD KEY `Room_id` (`Room_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`Schedule_id`),
  ADD KEY `Subject_id` (`Subject_id`),
  ADD KEY `Room_id` (`Room_id`),
  ADD KEY `Faculty_id` (`Faculty_id`);

--
-- Indexes for table `schedule_access`
--
ALTER TABLE `schedule_access`
  ADD PRIMARY KEY (`Rule_id`),
  ADD KEY `Schedule_id` (`Schedule_id`),
  ADD KEY `CourseSection_id` (`CourseSection_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`Subject_id`),
  ADD UNIQUE KEY `Code` (`Code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_id`),
  ADD UNIQUE KEY `Rfid_tag` (`Rfid_tag`),
  ADD KEY `CourseSection_id` (`CourseSection_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_log`
--
ALTER TABLE `access_log`
  MODIFY `Log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `Room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_section`
--
ALTER TABLE `course_section`
  MODIFY `CourseSection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rfid_reader`
--
ALTER TABLE `rfid_reader`
  MODIFY `Reader_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `Schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedule_access`
--
ALTER TABLE `schedule_access`
  MODIFY `Rule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `Subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_log`
--
ALTER TABLE `access_log`
  ADD CONSTRAINT `fk_access_room` FOREIGN KEY (`Room_id`) REFERENCES `classrooms` (`Room_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_access_schedule` FOREIGN KEY (`Schedule_id`) REFERENCES `schedule` (`Schedule_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_access_user` FOREIGN KEY (`User_id`) REFERENCES `users` (`User_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rfid_reader`
--
ALTER TABLE `rfid_reader`
  ADD CONSTRAINT `rfid_reader_ibfk_1` FOREIGN KEY (`Room_id`) REFERENCES `classrooms` (`Room_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `fk_schedule_faculty` FOREIGN KEY (`Faculty_id`) REFERENCES `users` (`User_id`),
  ADD CONSTRAINT `fk_schedule_room` FOREIGN KEY (`Room_id`) REFERENCES `classrooms` (`Room_id`),
  ADD CONSTRAINT `fk_schedule_subject` FOREIGN KEY (`Subject_id`) REFERENCES `subject` (`Subject_id`);

--
-- Constraints for table `schedule_access`
--
ALTER TABLE `schedule_access`
  ADD CONSTRAINT `fk_schedule_access_course` FOREIGN KEY (`CourseSection_id`) REFERENCES `course_section` (`CourseSection_id`),
  ADD CONSTRAINT `fk_schedule_access_schedule` FOREIGN KEY (`Schedule_id`) REFERENCES `schedule` (`Schedule_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_course` FOREIGN KEY (`CourseSection_id`) REFERENCES `course_section` (`CourseSection_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
