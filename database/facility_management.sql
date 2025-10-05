-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 05, 2025 at 05:35 AM
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
-- Database: `facility_management`
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
(58, NULL, 'RFID: Access Denied', 1, NULL, '2025-10-02 14:11:47', 'Entry', 'denied'),
(59, NULL, 'User ID Tag :  82 04 10 01', 1, NULL, '2025-10-02 14:11:52', 'Entry', 'denied'),
(60, NULL, 'RFID: Access Granted', 1, NULL, '2025-10-02 14:11:52', 'Entry', 'denied'),
(61, NULL, 'User ID Tag :  A4 12 3D 05', 1, NULL, '2025-10-02 14:11:57', 'Entry', 'denied'),
(62, NULL, 'RFID: Access Denied', 1, NULL, '2025-10-02 14:11:57', 'Entry', 'denied'),
(63, NULL, 'Pls scan your RFID card...', 1, NULL, '2025-10-02 14:14:20', 'Entry', 'denied'),
(64, NULL, 'Pls scan your RFID card...', 1, NULL, '2025-10-02 16:39:01', 'Entry', 'denied'),
(65, NULL, 'User ID Tag :  D3 CB B1 38', 1, NULL, '2025-10-02 16:39:05', 'Entry', 'denied'),
(66, NULL, 'RFID: Access Denied', 1, NULL, '2025-10-02 16:39:05', 'Entry', 'denied'),
(67, NULL, 'User ID Tag :  82 04 10 01', 1, NULL, '2025-10-02 16:40:00', 'Entry', 'denied'),
(68, NULL, 'RFID: Access Granted', 1, NULL, '2025-10-02 16:40:00', 'Entry', 'denied'),
(69, NULL, 'User ID Tag :  04 35 1B 12 B8 5E 80', 1, NULL, '2025-10-02 16:40:21', 'Entry', 'denied'),
(70, NULL, 'RFID: Access Denied', 1, NULL, '2025-10-02 16:40:21', 'Entry', 'denied'),
(71, NULL, 'User ID Tag :  04 35 1B 12 B8 5E 80', 1, NULL, '2025-10-02 16:41:11', 'Entry', 'denied'),
(72, NULL, 'RFID: Access Denied', 1, NULL, '2025-10-02 16:41:12', 'Entry', 'denied'),
(73, 1, '82 04 10 01', 1, NULL, '2025-10-02 16:46:39', 'Entry', 'granted'),
(74, 1, '82 04 10 01', 1, NULL, '2025-10-02 16:46:54', 'Entry', 'granted'),
(75, NULL, 'D3 CB B1 38', 1, NULL, '2025-10-02 16:47:02', 'Entry', 'denied'),
(76, 1, '82 04 10 01', 1, NULL, '2025-10-02 16:48:10', 'Entry', 'granted'),
(77, NULL, 'D3 CB B1 38', 1, NULL, '2025-10-02 16:54:44', 'Entry', 'denied'),
(78, 1, '82 04 10 01', 1, NULL, '2025-10-02 16:54:55', 'Entry', 'granted'),
(79, 1, '82 04 10 01', 1, NULL, '2025-10-02 16:59:17', 'Entry', 'granted'),
(80, NULL, '04 35 1B 12 B8 5E 80', 1, NULL, '2025-10-02 16:59:21', 'Entry', 'denied'),
(81, NULL, 'D3 CB B1 38', 1, NULL, '2025-10-02 16:59:28', 'Entry', 'denied'),
(82, 1, '82 04 10 01', 1, NULL, '2025-10-02 16:59:38', 'Entry', 'granted'),
(83, NULL, 'D3 CB B1 38', 1, NULL, '2025-10-02 17:00:05', 'Entry', 'denied'),
(84, NULL, '92 90 AD AB', 1, NULL, '2025-10-02 17:05:13', 'Entry', 'denied'),
(85, NULL, '92 90 AD AB', 1, NULL, '2025-10-02 17:05:32', 'Entry', 'denied'),
(86, NULL, '92 90 AD AB', 1, NULL, '2025-10-02 17:05:53', 'Entry', 'denied'),
(87, NULL, 'D3 CB B1 38', 1, NULL, '2025-10-02 17:16:45', 'Entry', 'denied'),
(88, 1, '82 04 10 01', 1, NULL, '2025-10-02 17:16:53', 'Entry', 'granted'),
(89, NULL, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:02:26', 'Entry', 'denied'),
(90, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:02:49', 'Entry', 'denied'),
(91, NULL, '82041001', 1, NULL, '2025-10-02 23:05:02', 'Entry', 'denied'),
(92, NULL, 'A4123D05', 1, NULL, '2025-10-02 23:05:07', 'Entry', 'denied'),
(93, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:16:06', 'Entry', 'denied'),
(94, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:16:25', 'Entry', 'denied'),
(95, NULL, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:16:32', 'Entry', 'denied'),
(96, NULL, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:19:39', 'Entry', 'denied'),
(97, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:19:45', 'Entry', 'denied'),
(98, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:19:48', 'Entry', 'denied'),
(99, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:20:01', 'Entry', 'denied'),
(100, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:20:06', 'Entry', 'denied'),
(101, NULL, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:20:08', 'Entry', 'denied'),
(102, NULL, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:22:05', 'Entry', 'denied'),
(103, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:22:09', 'Entry', 'denied'),
(104, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:22:13', 'Entry', 'denied'),
(105, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:23:26', 'Entry', 'denied'),
(106, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:23:29', 'Entry', 'denied'),
(107, NULL, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:24:55', 'Entry', 'denied'),
(108, NULL, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:24:58', 'Entry', 'denied'),
(109, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:25:03', 'Entry', 'denied'),
(110, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:29:37', 'Entry', 'denied'),
(111, 5, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:29:42', 'Entry', 'denied'),
(112, 5, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:29:58', 'Entry', 'denied'),
(113, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:30:58', 'Entry', 'denied'),
(114, 5, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:31:04', 'Entry', 'denied'),
(115, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:31:09', 'Entry', 'denied'),
(116, 2, 'D3 CB B1 38', 1, NULL, '2025-10-02 23:32:13', 'Entry', 'denied'),
(117, 1, '82 04 10 01', 1, NULL, '2025-10-02 23:32:18', 'Entry', 'denied'),
(118, 5, 'A4 12 3D 05', 1, NULL, '2025-10-02 23:32:22', 'Entry', 'granted'),
(119, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:42:58', 'Entry', 'granted'),
(120, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:43:05', 'Entry', 'granted'),
(121, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:43:07', 'Entry', 'granted'),
(122, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:43:36', 'Entry', 'granted'),
(123, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 10:43:39', 'Entry', 'granted'),
(124, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 10:43:41', 'Entry', 'granted'),
(125, 1, '82 04 10 01', 1, NULL, '2025-10-04 10:43:43', 'Entry', 'granted'),
(126, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:45:14', 'Entry', 'denied'),
(127, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:45:22', 'Entry', 'denied'),
(128, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:45:49', 'Entry', 'denied'),
(129, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 10:46:05', 'Entry', 'denied'),
(130, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 10:46:32', 'Entry', 'granted'),
(131, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 10:47:04', 'Entry', 'granted'),
(132, 1, '82 04 10 01', 1, NULL, '2025-10-04 10:47:13', 'Entry', 'denied'),
(133, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:12:48', 'Entry', 'granted'),
(134, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:12:51', 'Entry', 'granted'),
(135, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:13:08', 'Entry', 'granted'),
(136, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:13:19', 'Entry', 'denied'),
(137, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:13:23', 'Entry', 'denied'),
(138, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:13:26', 'Entry', 'granted'),
(139, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:13:29', 'Entry', 'denied'),
(140, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:14:34', 'Entry', 'granted'),
(141, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:14:40', 'Entry', 'denied'),
(142, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:14:54', 'Entry', 'denied'),
(143, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:15:48', 'Entry', 'denied'),
(144, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:16:03', 'Entry', 'denied'),
(145, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:16:34', 'Entry', 'denied'),
(146, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:16:43', 'Entry', 'granted'),
(147, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:23:21', 'Entry', 'denied'),
(148, 1, '82 04 10 01', 1, NULL, '2025-10-04 11:23:27', 'Entry', 'denied'),
(149, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:24:53', 'Entry', 'denied'),
(150, 1, '82 04 10 01', 1, NULL, '2025-10-04 11:24:58', 'Entry', 'denied'),
(151, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:25:02', 'Entry', 'granted'),
(152, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:25:40', 'Entry', 'denied'),
(153, 1, '82 04 10 01', 1, NULL, '2025-10-04 11:25:56', 'Entry', 'denied'),
(154, 1, '82 04 10 01', 1, NULL, '2025-10-04 11:26:37', 'Entry', 'denied'),
(155, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 11:42:33', 'Entry', 'denied'),
(156, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 11:51:57', 'Entry', 'granted'),
(157, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 12:04:46', 'Entry', 'denied'),
(158, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 12:04:52', 'Entry', 'granted'),
(159, 1, '82 04 10 01', 1, NULL, '2025-10-04 12:04:56', 'Entry', 'denied'),
(160, 1, '82 04 10 01', 1, NULL, '2025-10-04 12:14:52', 'Entry', 'denied'),
(161, 2, 'D3 CB B1 38', 1, NULL, '2025-10-04 12:14:59', 'Entry', 'denied'),
(162, 5, 'A4 12 3D 05', 1, NULL, '2025-10-04 12:15:03', 'Entry', 'granted');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `Room_id` int(11) NOT NULL,
  `Room_code` varchar(50) NOT NULL,
  `Status` enum('Occupied','Unoccupied') DEFAULT 'Unoccupied',
  `classroom_type` varchar(255) DEFAULT NULL,
  `capacity` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`Room_id`, `Room_code`, `Status`, `classroom_type`, `capacity`) VALUES
(1, 'ROOM101', 'Unoccupied', 'CLASSROOM', '50'),
(2, 'ROOM102', 'Unoccupied', 'CLASSROOM', '30'),
(3, 'LAB201', 'Occupied', 'COMLAB', '40');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `Schedule_id` int(11) NOT NULL,
  `Subject_id` int(11) NOT NULL,
  `Room_id` int(11) NOT NULL,
  `Faculty_id` int(11) NOT NULL,
  `Section` varchar(50) DEFAULT NULL,
  `Day` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') DEFAULT NULL,
  `Start_time` time NOT NULL,
  `End_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`Schedule_id`, `Subject_id`, `Room_id`, `Faculty_id`, `Section`, `Day`, `Start_time`, `End_time`) VALUES
(1, 1, 1, 4, 'A', 'Mon', '08:00:00', '10:00:00'),
(2, 2, 2, 4, 'B', 'Tue', '10:00:00', '12:00:00'),
(3, 3, 3, 4, 'A', 'Wed', '13:00:00', '15:00:00'),
(4, 3, 2, 4, 'A', 'Thu', '23:20:00', '23:50:00'),
(5, 1, 1, 1, 'A', 'Thu', '19:00:00', '23:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_access`
--

CREATE TABLE `schedule_access` (
  `Rule_id` int(11) NOT NULL,
  `Schedule_id` int(11) NOT NULL,
  `Course` varchar(100) DEFAULT NULL,
  `Section` varchar(50) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL,
  `Semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_access`
--

INSERT INTO `schedule_access` (`Rule_id`, `Schedule_id`, `Course`, `Section`, `Year`, `Semester`) VALUES
(1, 1, 'BSIT', 'A', 1, 1),
(2, 2, 'BSCS', 'B', 2, 1),
(3, 3, 'BSIT', 'A', 1, 1),
(4, 4, 'BSIT', 'A', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `Subject_id` int(11) NOT NULL,
  `Code` varchar(50) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`Subject_id`, `Code`, `Description`, `room_id`) VALUES
(1, 'IT101', 'Introduction to Information Technology', NULL),
(2, 'CS201', 'Data Structures and Algorithms', NULL),
(3, 'IT301', 'Database Management Systems', NULL),
(4, 'CS101', 'Intro to Programming', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_id` int(11) NOT NULL,
  `Rfid_tag` varchar(50) NOT NULL,
  `F_name` varchar(100) NOT NULL,
  `L_name` varchar(100) NOT NULL,
  `Course` varchar(100) DEFAULT NULL,
  `Section` varchar(50) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL,
  `Semester` int(11) DEFAULT NULL,
  `Role` enum('Student','Faculty','Admin') DEFAULT 'Student',
  `Status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_id`, `Rfid_tag`, `F_name`, `L_name`, `Course`, `Section`, `Year`, `Semester`, `Role`, `Status`) VALUES
(1, '82 04 10 01', 'John', 'Doe', 'BSIT', 'A', 1, 1, 'Student', 'Active'),
(2, 'D3 CB B1 38', 'Jane', 'Smith', 'BSIT', 'A', 1, 1, 'Student', 'Active'),
(3, 'RFID003', 'Mark', 'Reyes', 'BSCS', 'B', 2, 1, 'Student', 'Active'),
(4, 'RFID004', 'Anna', 'Cruz', NULL, NULL, NULL, NULL, 'Faculty', 'Active'),
(5, 'A4 12 3D 05', 'Paul', 'Santos', NULL, NULL, NULL, NULL, 'Admin', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_log`
--
ALTER TABLE `access_log`
  ADD PRIMARY KEY (`Log_id`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `Rfid_tag` (`Rfid_tag`),
  ADD KEY `Room_id` (`Room_id`),
  ADD KEY `Schedule_id` (`Schedule_id`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`Room_id`),
  ADD UNIQUE KEY `Room_code` (`Room_code`);

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
  ADD KEY `Schedule_id` (`Schedule_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`Subject_id`),
  ADD UNIQUE KEY `Code` (`Code`),
  ADD KEY `fk_course` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_id`),
  ADD UNIQUE KEY `Rfid_tag` (`Rfid_tag`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_log`
--
ALTER TABLE `access_log`
  MODIFY `Log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `Room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `Schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schedule_access`
--
ALTER TABLE `schedule_access`
  MODIFY `Rule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `Subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_log`
--
ALTER TABLE `access_log`
  ADD CONSTRAINT `access_log_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `users` (`User_id`),
  ADD CONSTRAINT `access_log_ibfk_3` FOREIGN KEY (`Room_id`) REFERENCES `classrooms` (`Room_id`),
  ADD CONSTRAINT `access_log_ibfk_4` FOREIGN KEY (`Schedule_id`) REFERENCES `schedule` (`Schedule_id`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`Subject_id`) REFERENCES `subject` (`Subject_id`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`Room_id`) REFERENCES `classrooms` (`Room_id`),
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`Faculty_id`) REFERENCES `users` (`User_id`);

--
-- Constraints for table `schedule_access`
--
ALTER TABLE `schedule_access`
  ADD CONSTRAINT `schedule_access_ibfk_1` FOREIGN KEY (`Schedule_id`) REFERENCES `schedule` (`Schedule_id`);

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`room_id`) REFERENCES `classrooms` (`Room_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
