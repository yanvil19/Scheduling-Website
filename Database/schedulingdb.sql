-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Sep 12, 2025 at 09:48 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `RoomID` int(11) NOT NULL,
  `RoomName` varchar(50) NOT NULL,
  `RoomType` enum('Lecture','Lab') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`RoomID`,`RoomName`, `RoomType`) VALUES
(1, 'Room 203', 'Lecture'),
(2, 'Room 204', 'Lecture'),
(3, 'ComLab 1', 'Lab'),
(4, 'ComLab 5', 'Lab'),
(5, 'ComLab 6', 'Lab');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `ScheduleID` int(11) NOT NULL,
  `SectionID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL,
  `RoomID` int(11) NOT NULL,
  `TimeSlotID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `term2courses`
--

CREATE TABLE `term2courses` (
  `SubjectID` INT(11) NOT NULL AUTO_INCREMENT,
  `SubjectCode` VARCHAR(20) NOT NULL,
  `SubjectName` VARCHAR(100) NOT NULL,
  `IsLab` TINYINT(1) NOT NULL,       -- 0 = Lecture, 1 = Lab
  `ProgramID` INT(11) NOT NULL,
  `YearLevelID` INT(11) NOT NULL,
  `TotalTime` DECIMAL(4,2) NOT NULL, -- e.g., 2.00, 2.67, 4.00
  PRIMARY KEY (`SubjectID`)
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

INSERT INTO TimeSlots (StartTime, EndTime) VALUES
('07:00:00', '09:00:00'),
('09:00:00', '11:00:00'),
('11:00:00', '13:00:00'),
('13:00:00', '15:00:00'),
('15:00:00', '17:00:00'),
('17:00:00', '19:00:00');

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
  ADD KEY `TimeSlotID` (`TimeSlotID`);

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
  MODIFY `CountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `RoomID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `SectionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `term2courses`
--
ALTER TABLE `term2courses`
  MODIFY `SubjectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `timeslots`
--
ALTER TABLE `timeslots`
  MODIFY `TimeSlotID` int(11) NOT NULL AUTO_INCREMENT;

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
