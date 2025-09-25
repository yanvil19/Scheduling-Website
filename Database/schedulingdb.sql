-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Sep 25, 2025 at 02:38 PM
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
-- Database: `schedulingdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `days_table`
--

CREATE TABLE `days_table` (
  `day_id` int(255) NOT NULL,
  `day` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `days_table`
--

INSERT INTO `days_table` (`day_id`, `day`) VALUES
(1, 'Monday'),
(2, 'Tuesday'),
(3, 'Wednesday'),
(4, 'Thursday'),
(5, 'Friday'),
(6, 'Saturday');

-- --------------------------------------------------------

--
-- Table structure for table `duration_table`
--

CREATE TABLE `duration_table` (
  `duration_id` int(255) NOT NULL,
  `duration` decimal(10,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `duration_table`
--

INSERT INTO `duration_table` (`duration_id`, `duration`) VALUES
(1, 1.335),
(2, 2.000);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `ProgramID` int(11) NOT NULL,
  `Code` varchar(20) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`ProgramID`, `Code`, `Name`, `Priority`) VALUES
(1, 'IT', 'Information Technology', 1),
(2, 'IS', 'Information Systems', 2),
(3, 'CS', 'Computer Science', 3);

-- --------------------------------------------------------

--
-- Table structure for table `programyearcounts`
--

CREATE TABLE `programyearcounts` (
  `CountID` int(11) NOT NULL,
  `ProgramID` int(11) NOT NULL,
  `YearLevelID` int(11) NOT NULL,
  `StudentCount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programyearcounts`
--

INSERT INTO `programyearcounts` (`CountID`, `ProgramID`, `YearLevelID`, `StudentCount`) VALUES
(1, 1, 4, 80),
(2, 1, 3, 80),
(3, 1, 2, 80),
(4, 1, 1, 80),
(5, 2, 4, 40),
(6, 2, 3, 40),
(7, 2, 2, 40),
(8, 2, 1, 40),
(9, 3, 4, 80),
(10, 3, 3, 80),
(11, 3, 2, 80),
(12, 3, 1, 80);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `RoomID` int(11) NOT NULL,
  `RoomName` varchar(50) NOT NULL,
  `RoomType` enum('Lecture','Lab','Online') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`RoomID`, `RoomName`, `RoomType`) VALUES
(1, 'Room 203', 'Lecture'),
(2, 'Room 204', 'Lecture'),
(3, 'ComLab 1', 'Lab'),
(4, 'ComLab 5', 'Lab'),
(5, 'ComLab 6', 'Lab'),
(6, 'V-Room 203', 'Online'),
(7, 'V-Room 204', 'Online'),
(8, 'V-ComLab 1', 'Online'),
(9, 'V-ComLab 5', 'Online'),
(10, 'V-ComLab 6', 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `ScheduleID` int(11) NOT NULL,
  `SectionID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL,
  `RoomID` int(11) NOT NULL,
  `TimeSlotID` int(11) NOT NULL,
  `day_id` int(11) NOT NULL,
  `duration_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`ScheduleID`, `SectionID`, `SubjectID`, `RoomID`, `TimeSlotID`, `day_id`, `duration_id`) VALUES
(1, 7, 39, 1, 1, 1, 2),
(2, 7, 40, 3, 1, 2, 2),
(3, 7, 41, 1, 1, 3, 2),
(4, 7, 42, 1, 1, 4, 2),
(5, 7, 43, 3, 1, 5, 2),
(6, 8, 39, 1, 1, 2, 2),
(7, 8, 40, 3, 1, 1, 2),
(8, 8, 41, 2, 1, 3, 2),
(9, 8, 42, 1, 1, 5, 2),
(10, 8, 43, 3, 1, 4, 2),
(11, 12, 20, 2, 1, 1, 1),
(12, 12, 21, 4, 1, 2, 2),
(13, 12, 3, 1, 2, 1, 1),
(14, 12, 4, 3, 1, 3, 2),
(15, 19, 1, 2, 1, 2, 1),
(16, 19, 2, 4, 1, 1, 2),
(17, 19, 3, 2, 2, 1, 1),
(18, 19, 4, 4, 1, 3, 2),
(19, 20, 1, 1, 2, 2, 1),
(20, 20, 2, 5, 1, 1, 2),
(21, 20, 3, 1, 2, 3, 1),
(22, 20, 4, 5, 1, 2, 2),
(23, 5, 44, 1, 1, 6, 2),
(24, 5, 45, 4, 1, 4, 2),
(25, 5, 46, 2, 1, 5, 2),
(26, 5, 47, 5, 1, 3, 2),
(27, 5, 48, 1, 2, 4, 2),
(28, 5, 49, 3, 2, 1, 2),
(29, 5, 50, 1, 2, 5, 2),
(30, 5, 51, 3, 2, 2, 2),
(31, 5, 52, 1, 2, 6, 2),
(32, 5, 53, 3, 2, 3, 2),
(33, 5, 54, 1, 3, 1, 2),
(34, 5, 55, 3, 3, 2, 2),
(35, 6, 44, 2, 1, 4, 2),
(36, 6, 45, 3, 1, 6, 2),
(37, 6, 46, 2, 2, 2, 2),
(38, 6, 47, 4, 1, 5, 2),
(39, 6, 48, 2, 2, 3, 2),
(40, 6, 49, 3, 2, 4, 2),
(41, 6, 50, 2, 2, 5, 2),
(42, 6, 51, 3, 2, 6, 2),
(43, 6, 52, 1, 3, 2, 2),
(44, 6, 53, 4, 2, 1, 2),
(45, 6, 54, 1, 3, 3, 2),
(46, 6, 55, 3, 3, 1, 2),
(47, 11, 24, 2, 3, 1, 1),
(48, 11, 24, 2, 3, 4, 1),
(49, 11, 25, 4, 2, 2, 2),
(50, 11, 25, 4, 2, 5, 2),
(51, 11, 9, 2, 1, 6, 2),
(52, 11, 10, 5, 1, 4, 2),
(53, 11, 28, 2, 3, 2, 1),
(54, 11, 28, 2, 3, 5, 1),
(55, 11, 29, 4, 2, 3, 2),
(56, 11, 29, 4, 2, 6, 2),
(57, 11, 30, 2, 3, 3, 2),
(58, 11, 30, 2, 3, 6, 2),
(59, 17, 5, 1, 4, 1, 1),
(60, 17, 5, 1, 4, 4, 1),
(61, 17, 6, 5, 2, 1, 2),
(62, 17, 6, 5, 2, 4, 2),
(63, 17, 7, 1, 4, 2, 1),
(64, 17, 7, 1, 4, 5, 1),
(65, 17, 8, 5, 2, 2, 2),
(66, 17, 8, 5, 2, 5, 2),
(67, 17, 9, 2, 2, 6, 2),
(68, 17, 10, 4, 1, 6, 2),
(69, 18, 5, 1, 4, 3, 1),
(70, 18, 5, 1, 4, 6, 1),
(71, 18, 6, 5, 2, 3, 2),
(72, 18, 6, 5, 2, 6, 2),
(73, 18, 7, 2, 4, 1, 1),
(74, 18, 7, 2, 4, 4, 1),
(75, 18, 8, 3, 3, 3, 2),
(76, 18, 8, 3, 3, 6, 2),
(77, 18, 9, 2, 2, 4, 2),
(78, 18, 10, 5, 1, 5, 2),
(79, 3, 56, 1, 3, 4, 2),
(80, 3, 57, 5, 1, 6, 2),
(81, 3, 58, 2, 4, 2, 2),
(82, 3, 58, 2, 4, 5, 2),
(83, 3, 59, 1, 3, 5, 2),
(84, 3, 60, 3, 2, 5, 2),
(85, 4, 56, 1, 3, 6, 2),
(86, 4, 57, 4, 2, 4, 2),
(87, 4, 58, 2, 4, 3, 2),
(88, 4, 58, 2, 4, 6, 2),
(89, 4, 59, 1, 5, 1, 2),
(90, 4, 60, 3, 3, 4, 2),
(91, 10, 31, 1, 5, 2, 2),
(92, 10, 31, 1, 5, 5, 2),
(93, 10, 32, 1, 5, 3, 1),
(94, 10, 32, 1, 5, 6, 1),
(95, 10, 33, 4, 3, 1, 2),
(96, 10, 33, 4, 3, 4, 2),
(97, 10, 34, 2, 5, 1, 1),
(98, 10, 34, 2, 5, 4, 1),
(99, 10, 35, 4, 3, 2, 2),
(100, 10, 35, 4, 3, 5, 2),
(101, 10, 36, 1, 6, 1, 1),
(102, 10, 36, 1, 6, 4, 1),
(103, 10, 37, 4, 3, 3, 2),
(104, 10, 37, 4, 3, 6, 2),
(105, 15, 11, 2, 5, 2, 1),
(106, 15, 11, 2, 5, 5, 1),
(107, 15, 12, 5, 3, 1, 2),
(108, 15, 12, 5, 3, 4, 2),
(109, 15, 13, 2, 5, 3, 1),
(110, 15, 13, 2, 5, 6, 1),
(111, 15, 14, 5, 3, 2, 2),
(112, 15, 14, 5, 3, 5, 2),
(113, 15, 15, 1, 6, 2, 1),
(114, 15, 15, 1, 6, 5, 1),
(115, 15, 16, 5, 3, 3, 2),
(116, 15, 16, 5, 3, 6, 2),
(117, 16, 11, 1, 6, 3, 1),
(118, 16, 11, 1, 6, 6, 1),
(119, 16, 12, 3, 4, 1, 2),
(120, 16, 12, 3, 4, 4, 2),
(121, 16, 13, 2, 6, 1, 1),
(122, 16, 13, 2, 6, 4, 1),
(123, 16, 14, 3, 4, 2, 2),
(124, 16, 14, 3, 4, 5, 2),
(125, 16, 15, 2, 6, 2, 1),
(126, 16, 15, 2, 6, 5, 1),
(127, 16, 16, 3, 4, 3, 2),
(128, 16, 16, 3, 4, 6, 2),
(129, 1, 61, 2, 6, 3, 2),
(130, 1, 61, 2, 6, 6, 2),
(131, 2, 61, 1, 7, 1, 2),
(132, 2, 61, 1, 7, 4, 2),
(133, 9, 38, 1, 7, 2, 2),
(134, 9, 38, 1, 7, 5, 2),
(135, 13, 17, 4, 4, 1, 2),
(136, 13, 17, 4, 4, 4, 2),
(137, 13, 18, 1, 7, 3, 1),
(138, 13, 18, 1, 7, 6, 1),
(139, 13, 19, 4, 4, 2, 2),
(140, 13, 19, 4, 4, 5, 2),
(141, 14, 17, 4, 4, 3, 2),
(142, 14, 17, 4, 4, 6, 2),
(143, 14, 18, 2, 7, 1, 1),
(144, 14, 18, 2, 7, 4, 1),
(145, 14, 19, 5, 4, 1, 2),
(146, 14, 19, 5, 4, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `SectionID` int(11) NOT NULL,
  `SectionName` varchar(50) NOT NULL,
  `ProgramID` int(11) NOT NULL,
  `YearLevelID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`SectionID`, `SectionName`, `ProgramID`, `YearLevelID`) VALUES
(1, 'BSIT221A', 1, 4),
(2, 'BSIT221B', 1, 4),
(3, 'BSIT231A', 1, 3),
(4, 'BSIT231B', 1, 3),
(5, 'BSIT241A', 1, 2),
(6, 'BSIT241B', 1, 2),
(7, 'BSIT251A', 1, 1),
(8, 'BSIT251B', 1, 1),
(9, 'BSIS221A', 2, 4),
(10, 'BSIS231A', 2, 3),
(11, 'BSIS241A', 2, 2),
(12, 'BSIS251A', 2, 1),
(13, 'BSCS221A', 3, 4),
(14, 'BSCS221B', 3, 4),
(15, 'BSCS231A', 3, 3),
(16, 'BSCS231B', 3, 3),
(17, 'BSCS241A', 3, 2),
(18, 'BSCS241B', 3, 2),
(19, 'BSCS251A', 3, 1),
(20, 'BSCS251B', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `term2courses`
--

CREATE TABLE `term2courses` (
  `SubjectID` int(11) NOT NULL,
  `SubjectCode` varchar(20) NOT NULL,
  `SubjectName` varchar(100) NOT NULL,
  `IsLab` tinyint(1) NOT NULL,
  `ProgramID` int(11) NOT NULL,
  `YearLevelID` int(11) NOT NULL,
  `TotalTime` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `term2courses`
--

INSERT INTO `term2courses` (`SubjectID`, `SubjectCode`, `SubjectName`, `IsLab`, `ProgramID`, `YearLevelID`, `TotalTime`) VALUES
(1, 'CCDISTR1', 'DISCRETE STRUCTURES 1 - LEC', 0, 3, 1, 2.67),
(2, 'CCDISTR1', 'DISCRETE STRUCTURES 1 - LAB', 1, 3, 1, 4.00),
(3, 'CCPRGG2X', 'INTERMEDIATE PROGRAMMING - LEC', 0, 3, 1, 2.67),
(4, 'CCPRG2BL', 'INTERMEDIATE PROGRAMMING - LAB', 1, 3, 1, 4.00),
(5, 'CCOMPORL', 'COMPUTER ARCHITECTURE AND ORGANIZATION - LEC', 0, 3, 2, 2.67),
(6, 'CCOMPORL', 'COMPUTER ARCHITECTURE AND ORGANIZATION - LAB', 1, 3, 2, 4.00),
(7, 'CCDATSYL', 'DATABASE SYSTEMS - LEC', 0, 3, 2, 2.67),
(8, 'CCDATSYL', 'DATABASE SYSTEMS - LAB', 1, 3, 2, 4.00),
(9, 'CCSPEL2X', 'SPORTS ELECTIVE 2 (E-GAMES DESIGN II) - LEC', 0, 3, 2, 2.00),
(10, 'CCSPEL2X', 'SPORTS ELECTIVE 2 (E-GAMES DESIGN II) - LAB', 1, 3, 2, 2.00),
(11, 'CCSFEN1L', 'SOFTWARE ENGINEERING 1 - LEC', 0, 3, 3, 2.67),
(12, 'CCSFEN1L', 'SOFTWARE ENGINEERING 1 - LAB', 1, 3, 3, 4.00),
(13, 'CTINASSL', 'INFORMATION ASSURANCE AND SECURITY - LEC', 0, 3, 3, 2.67),
(14, 'CTINASSL', 'INFORMATION ASSURANCE AND SECURITY - LAB', 1, 3, 3, 4.00),
(15, 'CCNETCOL', 'NETWORK AND COMMUNICATIONS - LEC', 0, 3, 3, 2.67),
(16, 'CCNETCOL', 'NETWORK AND COMMUNICATIONS - LAB', 1, 3, 3, 4.00),
(17, 'CCCSINTX', 'INTERNSHIP (MIN. 162 HRS)', 1, 3, 4, 4.00),
(18, 'CCSELE2L', 'C.S. ELECTIVE 2 - LEC', 0, 3, 4, 2.67),
(19, 'CCSELE2L', 'C.S. ELECTIVE 2 - LAB', 1, 3, 4, 4.00),
(20, 'CCFUINSY', 'FUNDAMENTALS OF INFORMATION SYSTEMS - LEC', 0, 2, 1, 2.67),
(21, 'CCFUINSY', 'FUNDAMENTALS OF INFORMATION SYSTEMS - LAB', 1, 2, 1, 4.00),
(22, 'CCPRGG2X', 'INTERMEDIATE PROGRAMMING - LEC', 0, 2, 1, 2.67),
(23, 'CCPRG2BL', 'INTERMEDIATE PROGRAMMING - LAB', 1, 2, 1, 4.00),
(24, 'CIENTARL', 'ENTERPRISE ARCHITECTURE - LEC', 0, 2, 2, 2.67),
(25, 'CIENTARL', 'ENTERPRISE ARCHITECTURE - LAB', 1, 2, 2, 4.00),
(26, 'CCSPEL2X', 'SPORTS ELECTIVE 2 (E-GAMES DESIGN II) - LEC', 0, 2, 2, 2.00),
(27, 'CCSPEL2X', 'SPORTS ELECTIVE 2 (E-GAMES DESIGN II) - LAB', 1, 2, 2, 2.00),
(28, 'CCQUAMET', 'QUANTITATIVE METHODS - LEC', 0, 2, 2, 2.67),
(29, 'CCQUAMET', 'QUANTITATIVE METHODS - LAB', 1, 2, 2, 4.00),
(30, 'BUPRDEMA', 'BUSINESS PROCESS MANAGEMENT - LEC', 0, 2, 2, 4.00),
(31, 'CISECDEL', 'E-COMMERCE AND DIGITAL ENTREPRENEURSHIP - LEC', 0, 2, 3, 4.00),
(32, 'CTSYSADL', 'SYSTEMS ANALYSIS AND DESIGN - LEC', 0, 2, 3, 2.67),
(33, 'CTSYSADL', 'SYSTEMS ANALYSIS AND DESIGN - LAB', 1, 2, 3, 4.00),
(34, 'CISPRMAL', 'I.S. PROJECT MANAGEMENT - LEC', 0, 2, 3, 2.67),
(35, 'CISPRMAL', 'I.S. PROJECT MANAGEMENT - LAB', 1, 2, 3, 4.00),
(36, 'CISELE1L', 'I.S. ELECTIVE 1 - LEC', 0, 2, 3, 2.67),
(37, 'CISELE1L', 'I.S. ELECTIVE 1 - LAB', 1, 2, 3, 4.00),
(38, 'CCISINTX', 'INTERNSHIP (486 HRS)', 0, 2, 4, 4.00),
(39, 'INPROLA', 'INTRODUCTION TO PROGRAMMING & THEORIES - LEC', 0, 1, 1, 2.00),
(40, 'INPROLA', 'INTRODUCTION TO PROGRAMMING & THEORIES - LAB', 1, 1, 1, 2.00),
(41, 'MANPRIN', 'MANAGEMENT PRINCIPLES - LEC', 0, 1, 1, 4.00),
(42, 'OPESYST', 'OPERATING SYSTEMS - LEC', 0, 1, 1, 2.00),
(43, 'OPESYST', 'OPERATING SYSTEMS - LAB', 1, 1, 1, 2.00),
(44, 'WEBPROG', 'WEB PROGRAMMING (for BSIT) - LEC', 0, 1, 2, 2.00),
(45, 'WEBPROG', 'WEB PROGRAMMING (for BSIT) - LAB', 1, 1, 2, 2.00),
(46, 'MOBPROG', 'MOBILE PROGRAMMING - LEC', 0, 1, 2, 2.00),
(47, 'MOBPROG', 'MOBILE PROGRAMMING - LAB', 1, 1, 2, 2.00),
(48, 'DATAMA2', 'DATABASE MANAGEMENT 2 and ADMINISTRATION - LEC', 0, 1, 2, 2.00),
(49, 'DATAMA2', 'DATABASE MANAGEMENT 2 and ADMINISTRATION - LAB', 1, 1, 2, 2.00),
(50, 'PEMBEDS', 'PROGRAMMING EMBEDDED SYSTEMS - LEC', 0, 1, 2, 2.00),
(51, 'PEMBEDS', 'PROGRAMMING EMBEDDED SYSTEMS - LAB', 1, 1, 2, 2.00),
(52, 'ELECTV1', 'ELECTIVE 1 - LEC', 0, 1, 2, 2.00),
(53, 'ELECTV1', 'ELECTIVE 1 - LAB', 1, 1, 2, 2.00),
(54, 'CLDCOMP', 'CLOUD COMPUTING & VIRTUALIZATION - LEC', 0, 1, 2, 2.00),
(55, 'CLDCOMP', 'CLOUD COMPUTING & VIRTUALIZATION - LAB', 1, 1, 2, 2.00),
(56, 'MCSPROJ', 'APPLIED PROJECT - SYSTEM PROTOTYPE - LEC', 0, 1, 3, 2.00),
(57, 'MCSPROJ', 'APPLIED PROJECT - SYSTEM PROTOTYPE - LAB', 1, 1, 3, 2.00),
(58, 'TENTREP', 'TECHNOPRENEURSHIP - LEC', 0, 1, 3, 4.00),
(59, 'ELECTV4', 'ELECTIVE 4 - LEC', 0, 1, 3, 2.00),
(60, 'ELECTV4', 'ELECTIVE 4 - LAB', 1, 1, 3, 2.00),
(61, 'xINTERN1', 'INTERNSHIP 1', 0, 1, 4, 4.00);

-- --------------------------------------------------------

--
-- Table structure for table `timeslots`
--

CREATE TABLE `timeslots` (
  `TimeSlotID` int(11) NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeslots`
--

INSERT INTO `timeslots` (`TimeSlotID`, `StartTime`, `EndTime`) VALUES
(1, '07:00:00', '09:00:00'),
(2, '09:00:00', '11:00:00'),
(3, '11:00:00', '13:00:00'),
(4, '13:00:00', '15:00:00'),
(5, '15:00:00', '17:00:00'),
(6, '17:00:00', '19:00:00'),
(7, '19:00:00', '21:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `yearlevels`
--

CREATE TABLE `yearlevels` (
  `YearLevelID` int(11) NOT NULL,
  `Level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `yearlevels`
--

INSERT INTO `yearlevels` (`YearLevelID`, `Level`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `days_table`
--
ALTER TABLE `days_table`
  ADD PRIMARY KEY (`day_id`);

--
-- Indexes for table `duration_table`
--
ALTER TABLE `duration_table`
  ADD PRIMARY KEY (`duration_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`ProgramID`);

--
-- Indexes for table `programyearcounts`
--
ALTER TABLE `programyearcounts`
  ADD PRIMARY KEY (`CountID`),
  ADD KEY `ProgramID` (`ProgramID`),
  ADD KEY `YearLevelID` (`YearLevelID`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`RoomID`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `SectionID` (`SectionID`),
  ADD KEY `SubjectID` (`SubjectID`),
  ADD KEY `RoomID` (`RoomID`),
  ADD KEY `TimeSlotID` (`TimeSlotID`),
  ADD KEY `day_id` (`day_id`),
  ADD KEY `duration_id` (`duration_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`SectionID`),
  ADD KEY `ProgramID` (`ProgramID`),
  ADD KEY `YearLevelID` (`YearLevelID`);

--
-- Indexes for table `term2courses`
--
ALTER TABLE `term2courses`
  ADD PRIMARY KEY (`SubjectID`),
  ADD KEY `ProgramID` (`ProgramID`),
  ADD KEY `YearLevelID` (`YearLevelID`);

--
-- Indexes for table `timeslots`
--
ALTER TABLE `timeslots`
  ADD PRIMARY KEY (`TimeSlotID`);

--
-- Indexes for table `yearlevels`
--
ALTER TABLE `yearlevels`
  ADD PRIMARY KEY (`YearLevelID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `ProgramID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `programyearcounts`
--
ALTER TABLE `programyearcounts`
  MODIFY `CountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `RoomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `SectionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `term2courses`
--
ALTER TABLE `term2courses`
  MODIFY `SubjectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `timeslots`
--
ALTER TABLE `timeslots`
  MODIFY `TimeSlotID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `yearlevels`
--
ALTER TABLE `yearlevels`
  MODIFY `YearLevelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `programyearcounts`
--
ALTER TABLE `programyearcounts`
  ADD CONSTRAINT `programyearcounts_ibfk_1` FOREIGN KEY (`ProgramID`) REFERENCES `programs` (`ProgramID`),
  ADD CONSTRAINT `programyearcounts_ibfk_2` FOREIGN KEY (`YearLevelID`) REFERENCES `yearlevels` (`YearLevelID`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `day_id` FOREIGN KEY (`day_id`) REFERENCES `days_table` (`day_id`),
  ADD CONSTRAINT `duration_id` FOREIGN KEY (`duration_id`) REFERENCES `duration_table` (`duration_id`),
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`SectionID`) REFERENCES `sections` (`SectionID`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`SubjectID`) REFERENCES `term2courses` (`SubjectID`),
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`RoomID`) REFERENCES `rooms` (`RoomID`),
  ADD CONSTRAINT `schedule_ibfk_4` FOREIGN KEY (`TimeSlotID`) REFERENCES `timeslots` (`TimeSlotID`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`ProgramID`) REFERENCES `programs` (`ProgramID`),
  ADD CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`YearLevelID`) REFERENCES `yearlevels` (`YearLevelID`);

--
-- Constraints for table `term2courses`
--
ALTER TABLE `term2courses`
  ADD CONSTRAINT `term2courses_ibfk_1` FOREIGN KEY (`ProgramID`) REFERENCES `programs` (`ProgramID`),
  ADD CONSTRAINT `term2courses_ibfk_2` FOREIGN KEY (`YearLevelID`) REFERENCES `yearlevels` (`YearLevelID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
