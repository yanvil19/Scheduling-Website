-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Sep 30, 2025 at 09:44 AM
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
(1, 1, 4, 120),
(2, 1, 3, 120),
(3, 1, 2, 120),
(4, 1, 1, 120),
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
(6, 'Virtual Room', 'Online');

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
(1070, 17, 17, 5, 4, 2, 2),
(1071, 17, 19, 4, 4, 3, 2),
(1072, 17, 17, 5, 4, 5, 2),
(1073, 17, 19, 4, 4, 6, 2),
(1074, 18, 19, 3, 5, 1, 2),
(1075, 18, 17, 5, 4, 3, 2),
(1076, 18, 19, 3, 5, 4, 2),
(1077, 18, 17, 5, 4, 6, 2),
(1078, 19, 14, 4, 4, 1, 2),
(1079, 19, 11, 2, 6, 1, 1),
(1080, 19, 12, 5, 3, 2, 2),
(1081, 19, 16, 3, 4, 2, 2),
(1082, 19, 13, 2, 6, 2, 1),
(1083, 19, 15, 2, 6, 3, 1),
(1084, 19, 14, 4, 4, 4, 2),
(1085, 19, 11, 2, 6, 4, 1),
(1086, 19, 12, 5, 3, 5, 2),
(1087, 19, 16, 3, 4, 5, 2),
(1088, 19, 13, 2, 6, 5, 1),
(1089, 19, 15, 2, 6, 6, 1),
(1090, 20, 12, 5, 4, 1, 2),
(1091, 20, 11, 1, 7, 1, 1),
(1092, 20, 14, 4, 4, 2, 2),
(1093, 20, 13, 1, 7, 2, 1),
(1094, 20, 16, 3, 4, 3, 2),
(1095, 20, 15, 1, 7, 3, 1),
(1096, 20, 12, 5, 4, 4, 2),
(1097, 20, 11, 1, 7, 4, 1),
(1098, 20, 14, 4, 4, 5, 2),
(1099, 20, 13, 1, 7, 5, 1),
(1100, 20, 16, 3, 4, 6, 2),
(1101, 20, 15, 1, 7, 6, 1),
(1102, 21, 8, 5, 3, 1, 2),
(1103, 21, 7, 2, 4, 1, 1),
(1104, 21, 6, 5, 2, 3, 2),
(1105, 21, 5, 2, 3, 3, 1),
(1106, 21, 10, 5, 1, 4, 2),
(1107, 21, 8, 3, 3, 4, 2),
(1108, 21, 7, 2, 4, 4, 1),
(1109, 21, 9, 2, 3, 5, 2),
(1110, 21, 6, 4, 2, 6, 2),
(1111, 21, 5, 1, 3, 6, 1),
(1112, 22, 9, 1, 5, 1, 2),
(1113, 22, 6, 3, 3, 2, 2),
(1114, 22, 5, 1, 4, 2, 1),
(1115, 22, 8, 4, 3, 3, 2),
(1116, 22, 7, 1, 4, 3, 1),
(1117, 22, 10, 4, 1, 5, 2),
(1118, 22, 6, 3, 3, 5, 2),
(1119, 22, 5, 1, 4, 5, 1),
(1120, 22, 8, 4, 3, 6, 2),
(1121, 22, 7, 1, 4, 6, 1),
(1122, 23, 1, 2, 1, 1, 1),
(1123, 23, 2, 4, 1, 2, 2),
(1124, 23, 3, 1, 2, 2, 1),
(1125, 23, 4, 4, 1, 3, 2),
(1126, 23, 1, 6, 1, 4, 1),
(1127, 23, 2, 6, 1, 5, 2),
(1128, 23, 3, 6, 2, 5, 1),
(1129, 23, 4, 6, 1, 6, 2),
(1130, 24, 2, 3, 1, 1, 2),
(1131, 24, 1, 1, 1, 2, 1),
(1132, 24, 3, 2, 2, 2, 1),
(1133, 24, 4, 5, 1, 3, 2),
(1134, 24, 2, 6, 1, 4, 2),
(1135, 24, 1, 6, 1, 5, 1),
(1136, 24, 3, 6, 2, 5, 1),
(1137, 24, 4, 6, 1, 6, 2),
(1138, 14, 37, 3, 4, 1, 2),
(1139, 14, 32, 1, 6, 1, 1),
(1140, 14, 33, 4, 3, 2, 2),
(1141, 14, 34, 1, 6, 2, 1),
(1142, 14, 35, 5, 3, 3, 2),
(1143, 14, 31, 2, 5, 3, 2),
(1144, 14, 36, 1, 6, 3, 1),
(1145, 14, 37, 3, 4, 4, 2),
(1146, 14, 32, 1, 6, 4, 1),
(1147, 14, 33, 4, 3, 5, 2),
(1148, 14, 34, 1, 6, 5, 1),
(1149, 14, 35, 5, 3, 6, 2),
(1150, 14, 31, 2, 5, 6, 2),
(1151, 14, 36, 1, 6, 6, 1),
(1152, 15, 24, 2, 3, 1, 1),
(1153, 15, 30, 1, 4, 1, 2),
(1154, 15, 28, 2, 3, 2, 1),
(1155, 15, 25, 4, 2, 3, 2),
(1156, 15, 29, 3, 3, 3, 2),
(1157, 15, 10, 4, 1, 4, 2),
(1158, 15, 24, 2, 3, 4, 1),
(1159, 15, 30, 1, 4, 4, 2),
(1160, 15, 9, 2, 2, 5, 2),
(1161, 15, 28, 1, 3, 5, 1),
(1162, 15, 25, 3, 2, 6, 2),
(1163, 15, 29, 3, 3, 6, 2),
(1164, 16, 20, 1, 1, 1, 1),
(1165, 16, 3, 2, 2, 1, 1),
(1166, 16, 21, 3, 1, 2, 2),
(1167, 16, 4, 3, 1, 3, 2),
(1168, 16, 20, 6, 1, 4, 1),
(1169, 16, 3, 6, 2, 4, 1),
(1170, 16, 21, 6, 1, 5, 2),
(1171, 16, 4, 6, 1, 6, 2),
(1172, 1, 61, 2, 7, 1, 2),
(1173, 1, 61, 2, 7, 4, 2),
(1174, 2, 61, 2, 7, 2, 2),
(1175, 2, 61, 2, 7, 5, 2),
(1176, 3, 61, 2, 7, 3, 2),
(1177, 3, 61, 2, 7, 6, 2),
(1178, 4, 58, 2, 4, 2, 2),
(1179, 4, 59, 2, 4, 3, 2),
(1180, 4, 57, 5, 1, 5, 2),
(1181, 4, 58, 2, 4, 5, 2),
(1182, 4, 60, 4, 1, 6, 2),
(1183, 4, 56, 2, 3, 6, 2),
(1184, 5, 58, 2, 5, 1, 2),
(1185, 5, 59, 1, 5, 2, 2),
(1186, 5, 60, 4, 2, 4, 2),
(1187, 5, 58, 1, 5, 4, 2),
(1188, 5, 57, 5, 1, 6, 2),
(1189, 5, 56, 2, 4, 6, 2),
(1190, 6, 56, 2, 5, 2, 2),
(1191, 6, 58, 1, 5, 3, 2),
(1192, 6, 57, 5, 2, 4, 2),
(1193, 6, 59, 2, 5, 4, 2),
(1194, 6, 60, 5, 2, 5, 2),
(1195, 6, 58, 1, 5, 6, 2),
(1196, 7, 45, 4, 1, 1, 2),
(1197, 7, 49, 3, 2, 1, 2),
(1198, 7, 55, 3, 3, 1, 2),
(1199, 7, 44, 2, 1, 2, 2),
(1200, 7, 51, 3, 2, 2, 2),
(1201, 7, 50, 1, 2, 3, 2),
(1202, 7, 46, 1, 1, 4, 2),
(1203, 7, 52, 1, 2, 4, 2),
(1204, 7, 47, 3, 1, 5, 2),
(1205, 7, 53, 3, 2, 5, 2),
(1206, 7, 48, 1, 1, 6, 2),
(1207, 7, 54, 1, 2, 6, 2),
(1208, 8, 45, 5, 1, 1, 2),
(1209, 8, 49, 4, 2, 1, 2),
(1210, 8, 55, 4, 3, 1, 2),
(1211, 8, 47, 5, 1, 2, 2),
(1212, 8, 51, 4, 2, 2, 2),
(1213, 8, 50, 2, 2, 3, 2),
(1214, 8, 44, 2, 1, 4, 2),
(1215, 8, 52, 2, 2, 4, 2),
(1216, 8, 46, 1, 1, 5, 2),
(1217, 8, 53, 4, 2, 5, 2),
(1218, 8, 48, 2, 1, 6, 2),
(1219, 8, 54, 2, 2, 6, 2),
(1220, 9, 49, 5, 2, 1, 2),
(1221, 9, 48, 1, 3, 1, 2),
(1222, 9, 51, 5, 2, 2, 2),
(1223, 9, 50, 1, 3, 2, 2),
(1224, 9, 53, 3, 2, 3, 2),
(1225, 9, 52, 1, 3, 3, 2),
(1226, 9, 45, 3, 1, 4, 2),
(1227, 9, 55, 3, 2, 4, 2),
(1228, 9, 54, 1, 3, 4, 2),
(1229, 9, 44, 2, 1, 5, 2),
(1230, 9, 46, 1, 2, 5, 2),
(1231, 9, 47, 3, 1, 6, 2),
(1232, 10, 39, 6, 1, 1, 2),
(1233, 10, 40, 6, 1, 2, 2),
(1234, 10, 41, 1, 1, 3, 2),
(1235, 10, 42, 6, 1, 4, 2),
(1236, 10, 43, 6, 1, 5, 2),
(1237, 10, 41, 6, 1, 6, 2),
(1238, 11, 39, 6, 1, 1, 2),
(1239, 11, 40, 6, 1, 2, 2),
(1240, 11, 41, 2, 1, 3, 2),
(1241, 11, 42, 6, 1, 4, 2),
(1242, 11, 43, 6, 1, 5, 2),
(1243, 11, 41, 6, 1, 6, 2),
(1244, 12, 39, 6, 1, 1, 2),
(1245, 12, 41, 1, 2, 1, 2),
(1246, 12, 40, 6, 1, 2, 2),
(1247, 12, 42, 6, 1, 3, 2),
(1248, 12, 43, 6, 1, 4, 2),
(1249, 12, 41, 6, 2, 4, 2);

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
(3, 'BSIT221C', 1, 4),
(4, 'BSIT231A', 1, 3),
(5, 'BSIT231B', 1, 3),
(6, 'BSIT231C', 1, 3),
(7, 'BSIT241A', 1, 2),
(8, 'BSIT241B', 1, 2),
(9, 'BSIT241C', 1, 2),
(10, 'BSIT251A', 1, 1),
(11, 'BSIT251B', 1, 1),
(12, 'BSIT251C', 1, 1),
(13, 'BSIS221A', 2, 4),
(14, 'BSIS231A', 2, 3),
(15, 'BSIS241A', 2, 2),
(16, 'BSIS251A', 2, 1),
(17, 'BSCS221A', 3, 4),
(18, 'BSCS221B', 3, 4),
(19, 'BSCS231A', 3, 3),
(20, 'BSCS231B', 3, 3),
(21, 'BSCS241A', 3, 2),
(22, 'BSCS241B', 3, 2),
(23, 'BSCS251A', 3, 1),
(24, 'BSCS251B', 3, 1);

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
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1250;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `SectionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
