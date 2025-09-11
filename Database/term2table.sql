-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Sep 11, 2025 at 01:09 PM
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
-- Database: `coursesdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `term2table`
--

CREATE TABLE `term2table` (
  `COL 1` varchar(8) DEFAULT NULL,
  `COL 2` varchar(42) DEFAULT NULL,
  `COL 3` varchar(4) DEFAULT NULL,
  `COL 4` varchar(13) DEFAULT NULL,
  `COL 5` varchar(7) DEFAULT NULL,
  `COL 6` varchar(7) DEFAULT NULL,
  `COL 7` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `term2table`
--

INSERT INTO `term2table` (`COL 1`, `COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`, `COL 7`) VALUES
('Code', 'Course Title', 'Term', 'Enrollee Year', 'Program', 'Lec hrs', 'Lab Hrs'),
('CCDISTR1', 'DISCRETE STRUCTURES 1', '2', '1', 'BSCS', '2.67', '4'),
('CCPRGG2X', 'INTERMEDIATE PROGRAMMING - LEC', '2', '1', 'BSCS', '2.67', ''),
('CCPRG2BL', 'INTERMEDIATE PROGRAMMING - LAB', '2', '1', 'BSCS', '', '4'),
('CCOMPORL', 'COMPUTER ARCHITECTURE AND ORGANIZATION', '2', '2', 'BSCS', '2.67', '4'),
('CCDATSYL', 'DATABASE SYSTEMS', '2', '2', 'BSCS', '2.67', '4'),
('CCSPEL2X', 'SPORTS ELECTIVE 2 ( E- GAMES DESIGN II )', '2', '2', 'BSCS', '2', '2'),
('CCSFEN1L', 'SOFTWARE ENGINEERING 1', '2', '3', 'BSCS', '2.67', '4'),
('CTINASSL', 'INFORMATION ASSURANCE AND SECURITY', '2', '3', 'BSCS', '2.67', '4'),
('CCNETCOL', 'NETWORK AND COMMUNICATIONS', '2', '3', 'BSCS', '2.67', '4'),
('CCCSINTX', 'INTERNSHIP (MIN. 162 HRS)', '2', '4', 'BSCS', '', '4'),
('CCSELE2L', 'C.S. ELECTIVE 2', '2', '4', 'BSCS', '2.67', '4'),
('CCFUINSY', 'FUNDAMENTALS OF INFORMATION SYSTEMS', '2', '1', 'BSIS', '2.67', '4'),
('CCPRGG2X', 'INTERMEDIATE PROGRAMMING - LEC', '2', '1', 'BSIS', '2.67', ''),
('CCPRG2BL', 'INTERMEDIATE PROGRAMMING - LAB', '2', '1', 'BSIS', '', '4'),
('CIENTARL', 'ENTERPRISE ARCHITECTURE', '2', '2', 'BSIS', '2.67', '4'),
('CCSPEL2X', 'SPORTS ELECTIVE 2 ( E- GAMES DESIGN II )', '2', '2', 'BSIS', '2', '2'),
('CCQUAMET', 'QUANTITATIVE METHODS', '2', '2', 'BSIS', '2.67', '4'),
('BUPRDEMA', 'BUSINESS PROCESS MANAGEMENT', '2', '2', 'BSIS', '4', ''),
('CISECDEL', 'E-COMMERCE AND DIGITAL ENTREPRENEURSHIP', '2', '3', 'BSIS', '4', ''),
('CTSYSADL', 'SYSTEMS ANALYSIS AND DESIGN', '2', '3', 'BSIS', '2.67', '4'),
('CISPRMAL', 'I.S. PROJECT MANAGEMENT', '2', '3', 'BSIS', '2.67', '4'),
('CISELE1L', 'I.S. ELECTIVE 1', '2', '3', 'BSIS', '2.67', '4'),
('CCISINTX', 'INTERNSHIP (486 HRS)', '2', '4', 'BSIS', '4', ''),
('INPROLA', 'INTRODUCTION TO PROGRAMMING &amp; THEORIES', '2', '1', 'BSIT', '2', '2'),
('MANPRIN', 'MANAGEMENT PRINCIPLES', '2', '1', 'BSIT', '4', ''),
('OPESYST', 'OPERATING SYSTEMS', '2', '1', 'BSIT', '2', '2'),
('WEBPROG', 'WEB PROGAMMING (for BSIT)', '2', '2', 'BSIT', '2', '2'),
('MOBPROG', 'MOBILE PROGRAMMING', '2', '2', 'BSIT', '2', '2'),
('DATAMA2', 'DATABASE MANAGEMENT 2 and ADMINISTRATION', '2', '2', 'BSIT', '2', '2'),
('PEMBEDS', 'PROGRAMMING EMBEDDED SYSTEMS', '2', '2', 'BSIT', '2', '2'),
('ELECTV1', 'ELECTIVE 1', '2', '2', 'BSIT', '2', '2'),
('CLDCOMP', 'CLOUD COMPUTING &amp; VIRTUALIZATION', '2', '2', 'BSIT', '2', '2'),
('MCSPROJ', 'APPLIED PROJECT - SYSTEM PHOTOTYPE', '2', '3', 'BSIT', '2', '2'),
('TENTREP', 'TECHNOPRENEURSHIP', '2', '3', 'BSIT', '4', ''),
('ELECTV4', 'ELECTIVE 4', '2', '3', 'BSIT', '2', '2'),
('xINTERN1', 'INTERNSHIP 1', '2', '4', 'BSIT', '4', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
