-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2025 at 11:12 AM
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
-- Database: `edutrack_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_class_id` int(11) NOT NULL,
  `status` enum('Present','Absent') NOT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_class_id`, `status`, `date`) VALUES
(66, 17, 'Absent', '2025-09-26'),
(67, 6, 'Absent', '2025-09-26'),
(68, 29, 'Present', '2025-09-26'),
(69, 2, 'Present', '2025-09-26'),
(70, 10, 'Present', '2025-09-26'),
(71, 1, 'Present', '2025-09-26'),
(72, 8, 'Present', '2025-09-26'),
(73, 15, 'Present', '2025-09-26'),
(74, 27, 'Present', '2025-09-26'),
(75, 13, 'Present', '2025-09-26'),
(76, 21, 'Present', '2025-09-26'),
(77, 4, 'Present', '2025-09-26'),
(78, 7, 'Present', '2025-09-26'),
(79, 23, 'Present', '2025-09-26'),
(80, 31, 'Present', '2025-09-26'),
(81, 14, 'Present', '2025-09-26'),
(82, 22, 'Present', '2025-09-26'),
(83, 11, 'Present', '2025-09-26'),
(84, 9, 'Absent', '2025-09-26'),
(85, 20, 'Present', '2025-09-26'),
(86, 24, 'Present', '2025-09-26'),
(87, 30, 'Present', '2025-09-26'),
(88, 32, 'Present', '2025-09-26'),
(89, 12, 'Present', '2025-09-26'),
(90, 28, 'Present', '2025-09-26'),
(91, 3, 'Present', '2025-09-26'),
(92, 19, 'Present', '2025-09-26'),
(93, 18, 'Present', '2025-09-26'),
(94, 25, 'Present', '2025-09-26'),
(95, 26, 'Present', '2025-09-26'),
(96, 5, 'Present', '2025-09-26'),
(97, 16, 'Present', '2025-09-26'),
(98, 17, 'Present', '2025-09-27'),
(99, 6, 'Absent', '2025-09-27'),
(100, 29, 'Present', '2025-09-27'),
(101, 2, 'Present', '2025-09-27'),
(102, 10, 'Present', '2025-09-27'),
(103, 1, 'Present', '2025-09-27'),
(104, 8, 'Present', '2025-09-27'),
(105, 15, 'Present', '2025-09-27'),
(106, 27, 'Present', '2025-09-27'),
(107, 13, 'Present', '2025-09-27'),
(108, 21, 'Present', '2025-09-27'),
(109, 4, 'Present', '2025-09-27'),
(110, 7, 'Present', '2025-09-27'),
(111, 23, 'Absent', '2025-09-27'),
(112, 31, 'Present', '2025-09-27'),
(113, 14, 'Present', '2025-09-27'),
(114, 22, 'Present', '2025-09-27'),
(115, 11, 'Present', '2025-09-27'),
(116, 9, 'Present', '2025-09-27'),
(117, 20, 'Present', '2025-09-27'),
(118, 24, 'Present', '2025-09-27'),
(119, 30, 'Present', '2025-09-27'),
(120, 32, 'Present', '2025-09-27'),
(121, 12, 'Present', '2025-09-27'),
(122, 28, 'Present', '2025-09-27'),
(123, 3, 'Present', '2025-09-27'),
(124, 19, 'Absent', '2025-09-27'),
(125, 18, 'Present', '2025-09-27'),
(126, 25, 'Present', '2025-09-27'),
(127, 26, 'Present', '2025-09-27'),
(128, 5, 'Present', '2025-09-27'),
(129, 16, 'Present', '2025-09-27');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `course_title` varchar(250) DEFAULT NULL,
  `course_code` int(11) DEFAULT NULL,
  `course_name` varchar(250) NOT NULL,
  `room` varchar(250) DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `teacher_id`, `course_title`, `course_code`, `course_name`, `room`, `time_from`, `time_to`) VALUES
(1001, 4111322, 'IT6L', 2813, 'Fundamentals of Database Systems', 'PS-302', '10:00:00', '12:00:00'),
(1002, 4111322, 'IT9', 2341, 'Web Development', 'PS-311', '09:00:00', '11:00:00'),
(1003, 4111322, 'CCE 101', 4066, 'Introduction To Computing', 'PS-311', '08:00:00', '10:00:00'),
(1004, 4111321, 'CCE 102', 5262, 'Programming 2', 'PS-402', '14:00:00', '15:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_class_id` int(11) NOT NULL,
  `score` decimal(5,0) DEFAULT NULL,
  `percentage` int(11) DEFAULT NULL,
  `grade` char(1) DEFAULT NULL,
  `activity_type` varchar(150) DEFAULT NULL,
  `activity_name` varchar(250) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `maximum_score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_class_id`, `score`, `percentage`, `grade`, `activity_type`, `activity_name`, `date`, `maximum_score`) VALUES
(15, 1, 12, 80, 'B', 'Quiz', 'Quiz 2', '2025-09-21', 15),
(16, 1, 15, 100, 'A', 'Quiz', 'Quiz 2', '2025-09-21', 15),
(17, 2, 10, 67, 'D', 'Quiz', 'Quiz 2', '2025-09-21', 15),
(18, 2, 13, 65, 'D', 'Quiz', 'Q 2', '2025-09-22', 20),
(19, 1, 18, 90, 'A', 'Quiz', 'Q 2', '2025-09-22', 20),
(24, 10, 18, 90, 'A', 'Quiz', 'Q 2', '2025-09-22', 20),
(25, 17, 100, 100, 'A', 'Exam', 'Final Exam', '2025-09-26', 100),
(26, 6, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(27, 29, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(28, 2, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(29, 10, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(30, 1, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(31, 8, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(32, 15, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(33, 27, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(34, 13, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(35, 21, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(36, 4, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(37, 7, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(38, 23, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(39, 31, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(40, 14, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(41, 22, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(42, 11, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(43, 9, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(44, 20, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(45, 24, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(46, 30, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(47, 32, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(48, 12, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(49, 28, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(50, 3, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(51, 19, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(52, 18, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(53, 25, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(54, 26, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(55, 5, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100),
(56, 16, 0, 0, 'F', 'Exam', 'Final Exam', '2025-09-26', 100);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `last_name`, `address`, `phone`) VALUES
(100001, 'John', 'Doe', 'Buhangin Davao City', '092673568821'),
(100002, 'James', 'Webb Telescope', 'Space', '092673568821'),
(100003, 'Robert', 'Cross', 'Bunawan, Davao City', '09955443242'),
(5111321, 'John', 'Smith', '123 Main St', '09171234501'),
(5111322, 'Mary', 'Johnson', '124 Main St', '09171234502'),
(5111323, 'James', 'Williams', '125 Main St', '09171234503'),
(5111324, 'Patricia', 'Brown', '126 Main St', '09171234504'),
(5111325, 'Robert', 'Jones', '127 Main St', '09171234505'),
(5111326, 'Linda', 'Garcia', '128 Main St', '09171234506'),
(5111327, 'Michael', 'Miller', '129 Main St', '09171234507'),
(5111328, 'Barbara', 'Davis', '130 Main St', '09171234508'),
(5111329, 'William', 'Martinez', '131 Main St', '09171234509'),
(5111330, 'Elizabeth', 'Rodriguez', '132 Main St', '09171234510'),
(5111331, 'David', 'Hernandez', '133 Main St', '09171234511'),
(5111332, 'Jennifer', 'Lopez', '134 Main St', '09171234512'),
(5111333, 'Richard', 'Gonzalez', '135 Main St', '09171234513'),
(5111334, 'Susan', 'Wilson', '136 Main St', '09171234514'),
(5111335, 'Joseph', 'Anderson', '137 Main St', '09171234515'),
(5111336, 'Jessica', 'Thomas', '138 Main St', '09171234516'),
(5111337, 'Charles', 'Taylor', '139 Main St', '09171234517'),
(5111338, 'Sarah', 'Moore', '140 Main St', '09171234518'),
(5111339, 'Christopher', 'Jackson', '141 Main St', '09171234519'),
(5111340, 'Karen', 'Martin', '142 Main St', '09171234520'),
(5111341, 'Daniel', 'Lee', '143 Main St', '09171234521'),
(5111342, 'Nancy', 'Perez', '144 Main St', '09171234522'),
(5111343, 'Matthew', 'Thompson', '145 Main St', '09171234523'),
(5111344, 'Lisa', 'White', '146 Main St', '09171234524'),
(5111345, 'Anthony', 'Harris', '147 Main St', '09171234525'),
(5111346, 'Betty', 'Sanchez', '148 Main St', '09171234526'),
(5111347, 'Mark', 'Clark', '149 Main St', '09171234527'),
(5111348, 'Sandra', 'Ramirez', '150 Main St', '09171234528'),
(5111349, 'Paul', 'Lewis', '151 Main St', '09171234529'),
(5111350, 'Donna', 'Robinson', '152 Main St', '09171234530');

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`id`, `student_id`, `class_id`) VALUES
(1, 100001, 1001),
(2, 100003, 1001),
(3, 5111321, 1001),
(4, 5111322, 1001),
(5, 5111323, 1001),
(6, 5111324, 1001),
(7, 5111325, 1001),
(8, 5111326, 1001),
(9, 5111327, 1001),
(10, 5111328, 1001),
(11, 5111329, 1001),
(12, 5111330, 1001),
(13, 5111331, 1001),
(14, 5111332, 1001),
(15, 5111333, 1001),
(16, 5111334, 1001),
(17, 5111335, 1001),
(18, 5111336, 1001),
(19, 5111337, 1001),
(20, 5111338, 1001),
(21, 5111339, 1001),
(22, 5111340, 1001),
(23, 5111341, 1001),
(24, 5111342, 1001),
(25, 5111343, 1001),
(26, 5111344, 1001),
(27, 5111345, 1001),
(28, 5111346, 1001),
(29, 5111347, 1001),
(30, 5111348, 1001),
(31, 5111349, 1001),
(32, 5111350, 1001);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `first_name`, `last_name`, `username`, `password`) VALUES
(4111321, 'User', 'One', 'user1', 'user123'),
(4111322, 'Michael', 'Velez', 'Michael Aguido Velez', 'user123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_class_ibfk2` (`student_class_id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_class_ibfk1` (`student_class_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5111351;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4111323;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `student_class_ibfk2` FOREIGN KEY (`student_class_id`) REFERENCES `student_classes` (`id`);

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `class_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `student_class_ibfk1` FOREIGN KEY (`student_class_id`) REFERENCES `student_classes` (`id`);

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
