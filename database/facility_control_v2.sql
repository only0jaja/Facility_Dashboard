-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2025 at 09:42 AM
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
(6, NULL, '2,82 04 10 01', 2, NULL, '2025-10-09 15:40:27', 'Entry', 'denied');

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
(1, 'ROOM101', 'Unoccupied', 'CLASSROOM', 50),
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
(1, 1, 1, 6, 'Mon', '08:00:00', '10:00:00'),
(2, 1, 1, 6, 'Mon', '10:00:00', '12:00:00'),
(3, 2, 1, 8, 'Thu', '13:00:00', '16:00:00');

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
(1, 1, 1),
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
(3, 'A4 12 3D 05', 'Mark', 'Reyes', 3, 'Student', 'Active'),
(6, 'FAC001', 'Anna', 'Cruz', NULL, 'Faculty', 'Active'),
(7, 'FAC002', 'Paul', 'Santos', NULL, 'Faculty', 'Active'),
(8, 'ADM001', 'Michael', 'Tan', NULL, 'Admin', 'Active');

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
  MODIFY `Log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
