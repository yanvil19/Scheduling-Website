-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Oct 20, 2025 at 03:47 PM
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
-- Table structure for table `actual_time`
--

CREATE TABLE `actual_time` (
  `ActualTimeID` int(11) NOT NULL,
  `ActualStartTime` time NOT NULL,
  `ActualEndTime` time NOT NULL,
  `DurationLabel` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `actual_time`
--

INSERT INTO `actual_time` (`ActualTimeID`, `ActualStartTime`, `ActualEndTime`, `DurationLabel`) VALUES
(359, '15:00:00', '17:00:00', '0h 2m'),
(360, '15:00:00', '17:00:00', '0h 2m'),
(361, '15:00:00', '17:00:00', '0h 2m'),
(362, '15:00:00', '17:00:00', '0h 2m'),
(363, '15:00:00', '17:00:00', '0h 2m'),
(364, '15:00:00', '17:00:00', '0h 2m'),
(365, '15:00:00', '17:00:00', '0h 2m'),
(366, '15:00:00', '17:00:00', '0h 2m'),
(367, '13:00:00', '15:00:00', '0h 2m'),
(368, '17:00:00', '18:20:00', '0h 1m'),
(369, '13:00:00', '15:00:00', '0h 2m'),
(370, '17:00:00', '18:20:00', '0h 1m'),
(371, '13:00:00', '15:00:00', '0h 2m'),
(372, '15:00:00', '16:20:00', '0h 1m'),
(373, '13:00:00', '15:00:00', '0h 2m'),
(374, '17:00:00', '18:20:00', '0h 1m'),
(375, '13:00:00', '15:00:00', '0h 2m'),
(376, '17:00:00', '18:20:00', '0h 1m'),
(377, '13:00:00', '15:00:00', '0h 2m'),
(378, '15:00:00', '16:20:00', '0h 1m'),
(379, '13:00:00', '15:00:00', '0h 2m'),
(380, '19:00:00', '20:20:00', '0h 1m'),
(381, '13:00:00', '15:00:00', '0h 2m'),
(382, '19:00:00', '20:20:00', '0h 1m'),
(383, '13:00:00', '15:00:00', '0h 2m'),
(384, '17:00:00', '18:20:00', '0h 1m'),
(385, '13:00:00', '15:00:00', '0h 2m'),
(386, '19:00:00', '20:20:00', '0h 1m'),
(387, '13:00:00', '15:00:00', '0h 2m'),
(388, '19:00:00', '20:20:00', '0h 1m'),
(389, '13:00:00', '15:00:00', '0h 2m'),
(390, '17:00:00', '18:20:00', '0h 1m'),
(391, '11:00:00', '13:00:00', '0h 2m'),
(392, '13:00:00', '14:20:00', '0h 1m'),
(393, '11:00:00', '12:20:00', '0h 1m'),
(394, '09:00:00', '11:00:00', '0h 2m'),
(395, '11:00:00', '13:00:00', '0h 2m'),
(396, '13:00:00', '14:20:00', '0h 1m'),
(397, '07:00:00', '09:00:00', '0h 2m'),
(398, '11:00:00', '12:20:00', '0h 1m'),
(399, '09:00:00', '11:00:00', '0h 2m'),
(400, '11:00:00', '13:00:00', '0h 2m'),
(401, '11:00:00', '13:00:00', '0h 2m'),
(402, '13:00:00', '14:20:00', '0h 1m'),
(403, '11:00:00', '13:00:00', '0h 2m'),
(404, '13:00:00', '14:20:00', '0h 1m'),
(405, '09:00:00', '11:00:00', '0h 2m'),
(406, '11:00:00', '13:00:00', '0h 2m'),
(407, '11:00:00', '13:00:00', '0h 2m'),
(408, '13:00:00', '14:20:00', '0h 1m'),
(409, '11:00:00', '13:00:00', '0h 2m'),
(410, '13:00:00', '14:20:00', '0h 1m'),
(411, '07:00:00', '08:20:00', '0h 1m'),
(412, '09:00:00', '10:20:00', '0h 1m'),
(413, '07:00:00', '09:00:00', '0h 2m'),
(414, '07:00:00', '09:00:00', '0h 2m'),
(415, '07:00:00', '08:20:00', '0h 1m'),
(416, '09:00:00', '10:20:00', '0h 1m'),
(417, '07:00:00', '09:00:00', '0h 2m'),
(418, '07:00:00', '09:00:00', '0h 2m'),
(419, '07:00:00', '09:00:00', '0h 2m'),
(420, '09:00:00', '10:20:00', '0h 1m'),
(421, '09:00:00', '10:20:00', '0h 1m'),
(422, '07:00:00', '09:00:00', '0h 2m'),
(423, '07:00:00', '09:00:00', '0h 2m'),
(424, '09:00:00', '10:20:00', '0h 1m'),
(425, '09:00:00', '10:20:00', '0h 1m'),
(426, '07:00:00', '09:00:00', '0h 2m'),
(427, '19:00:00', '21:00:00', '0h 2m'),
(428, '19:00:00', '21:00:00', '0h 2m'),
(429, '13:00:00', '15:00:00', '0h 2m'),
(430, '17:00:00', '18:20:00', '0h 1m'),
(431, '13:00:00', '15:00:00', '0h 2m'),
(432, '17:00:00', '18:20:00', '0h 1m'),
(433, '13:00:00', '15:00:00', '0h 2m'),
(434, '15:00:00', '17:00:00', '0h 2m'),
(435, '17:00:00', '18:20:00', '0h 1m'),
(436, '13:00:00', '15:00:00', '0h 2m'),
(437, '17:00:00', '18:20:00', '0h 1m'),
(438, '13:00:00', '15:00:00', '0h 2m'),
(439, '17:00:00', '18:20:00', '0h 1m'),
(440, '13:00:00', '15:00:00', '0h 2m'),
(441, '15:00:00', '17:00:00', '0h 2m'),
(442, '17:00:00', '18:20:00', '0h 1m'),
(443, '11:00:00', '13:00:00', '0h 2m'),
(444, '13:00:00', '15:00:00', '0h 2m'),
(445, '09:00:00', '11:00:00', '0h 2m'),
(446, '11:00:00', '12:20:00', '0h 1m'),
(447, '11:00:00', '12:20:00', '0h 1m'),
(448, '11:00:00', '13:00:00', '0h 2m'),
(449, '13:00:00', '15:00:00', '0h 2m'),
(450, '07:00:00', '09:00:00', '0h 2m'),
(451, '09:00:00', '11:00:00', '0h 2m'),
(452, '11:00:00', '12:20:00', '0h 1m'),
(453, '09:00:00', '11:00:00', '0h 2m'),
(454, '11:00:00', '12:20:00', '0h 1m'),
(455, '07:00:00', '08:20:00', '0h 1m'),
(456, '09:00:00', '11:00:00', '0h 2m'),
(457, '07:00:00', '09:00:00', '0h 2m'),
(458, '07:00:00', '08:20:00', '0h 1m'),
(459, '07:00:00', '08:20:00', '0h 1m'),
(460, '09:00:00', '11:00:00', '0h 2m'),
(461, '07:00:00', '09:00:00', '0h 2m'),
(462, '07:00:00', '08:20:00', '0h 1m'),
(463, '19:00:00', '21:00:00', '0h 2m'),
(464, '19:00:00', '21:00:00', '0h 2m'),
(465, '19:00:00', '21:00:00', '0h 2m'),
(466, '19:00:00', '21:00:00', '0h 2m'),
(467, '19:00:00', '21:00:00', '0h 2m'),
(468, '19:00:00', '21:00:00', '0h 2m'),
(469, '13:00:00', '15:00:00', '0h 2m'),
(470, '13:00:00', '15:00:00', '0h 2m'),
(471, '09:00:00', '11:00:00', '0h 2m'),
(472, '09:00:00', '11:00:00', '0h 2m'),
(473, '13:00:00', '15:00:00', '0h 2m'),
(474, '13:00:00', '15:00:00', '0h 2m'),
(475, '15:00:00', '17:00:00', '0h 2m'),
(476, '15:00:00', '17:00:00', '0h 2m'),
(477, '09:00:00', '11:00:00', '0h 2m'),
(478, '15:00:00', '17:00:00', '0h 2m'),
(479, '11:00:00', '13:00:00', '0h 2m'),
(480, '15:00:00', '17:00:00', '0h 2m'),
(481, '15:00:00', '17:00:00', '0h 2m'),
(482, '15:00:00', '17:00:00', '0h 2m'),
(483, '11:00:00', '13:00:00', '0h 2m'),
(484, '15:00:00', '17:00:00', '0h 2m'),
(485, '15:00:00', '17:00:00', '0h 2m'),
(486, '11:00:00', '13:00:00', '0h 2m'),
(487, '07:00:00', '09:00:00', '0h 2m'),
(488, '09:00:00', '11:00:00', '0h 2m'),
(489, '11:00:00', '13:00:00', '0h 2m'),
(490, '09:00:00', '11:00:00', '0h 2m'),
(491, '07:00:00', '09:00:00', '0h 2m'),
(492, '09:00:00', '11:00:00', '0h 2m'),
(493, '07:00:00', '09:00:00', '0h 2m'),
(494, '09:00:00', '11:00:00', '0h 2m'),
(495, '07:00:00', '09:00:00', '0h 2m'),
(496, '09:00:00', '11:00:00', '0h 2m'),
(497, '07:00:00', '09:00:00', '0h 2m'),
(498, '09:00:00', '11:00:00', '0h 2m'),
(499, '07:00:00', '09:00:00', '0h 2m'),
(500, '09:00:00', '11:00:00', '0h 2m'),
(501, '11:00:00', '13:00:00', '0h 2m'),
(502, '09:00:00', '11:00:00', '0h 2m'),
(503, '09:00:00', '11:00:00', '0h 2m'),
(504, '07:00:00', '09:00:00', '0h 2m'),
(505, '09:00:00', '11:00:00', '0h 2m'),
(506, '11:00:00', '13:00:00', '0h 2m'),
(507, '07:00:00', '09:00:00', '0h 2m'),
(508, '09:00:00', '11:00:00', '0h 2m'),
(509, '07:00:00', '09:00:00', '0h 2m'),
(510, '09:00:00', '11:00:00', '0h 2m'),
(511, '11:00:00', '13:00:00', '0h 2m'),
(512, '09:00:00', '11:00:00', '0h 2m'),
(513, '09:00:00', '11:00:00', '0h 2m'),
(514, '11:00:00', '13:00:00', '0h 2m'),
(515, '09:00:00', '11:00:00', '0h 2m'),
(516, '11:00:00', '13:00:00', '0h 2m'),
(517, '07:00:00', '09:00:00', '0h 2m'),
(518, '09:00:00', '11:00:00', '0h 2m'),
(519, '11:00:00', '13:00:00', '0h 2m'),
(520, '07:00:00', '09:00:00', '0h 2m'),
(521, '09:00:00', '11:00:00', '0h 2m'),
(522, '11:00:00', '13:00:00', '0h 2m'),
(523, '07:00:00', '09:00:00', '0h 2m'),
(524, '07:00:00', '09:00:00', '0h 2m'),
(525, '07:00:00', '09:00:00', '0h 2m'),
(526, '07:00:00', '09:00:00', '0h 2m'),
(527, '07:00:00', '09:00:00', '0h 2m'),
(528, '07:00:00', '09:00:00', '0h 2m'),
(529, '07:00:00', '09:00:00', '0h 2m'),
(530, '07:00:00', '09:00:00', '0h 2m'),
(531, '07:00:00', '09:00:00', '0h 2m'),
(532, '07:00:00', '09:00:00', '0h 2m'),
(533, '07:00:00', '09:00:00', '0h 2m'),
(534, '07:00:00', '09:00:00', '0h 2m'),
(535, '07:00:00', '09:00:00', '0h 2m'),
(536, '07:00:00', '09:00:00', '0h 2m'),
(537, '07:00:00', '09:00:00', '0h 2m'),
(538, '07:00:00', '09:00:00', '0h 2m'),
(539, '07:00:00', '09:00:00', '0h 2m'),
(540, '07:00:00', '09:00:00', '0h 2m');

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
  `duration` decimal(10,3) DEFAULT NULL,
  `duration_label` varchar(20) DEFAULT NULL,
  `minutes` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `duration_table`
--

INSERT INTO `duration_table` (`duration_id`, `duration`, `duration_label`, `minutes`) VALUES
(1, 1.335, '1:20:00', 80),
(2, 2.000, '2:00:00', 120);

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
  `duration_id` int(11) NOT NULL,
  `ActualTimeID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`ScheduleID`, `SectionID`, `SubjectID`, `RoomID`, `TimeSlotID`, `day_id`, `duration_id`, `ActualTimeID`) VALUES
(1610, 17, 17, 3, 5, 1, 2, 359),
(1611, 17, 19, 3, 5, 2, 2, 360),
(1612, 17, 17, 3, 5, 4, 2, 361),
(1613, 17, 19, 3, 5, 5, 2, 362),
(1614, 18, 17, 4, 5, 1, 2, 363),
(1615, 18, 19, 4, 5, 2, 2, 364),
(1616, 18, 17, 4, 5, 4, 2, 365),
(1617, 18, 19, 4, 5, 5, 2, 366),
(1618, 19, 12, 4, 4, 1, 2, 367),
(1619, 19, 13, 2, 6, 1, 1, 368),
(1620, 19, 14, 4, 4, 2, 2, 369),
(1621, 19, 15, 2, 6, 2, 1, 370),
(1622, 19, 16, 4, 4, 3, 2, 371),
(1623, 19, 11, 2, 5, 3, 1, 372),
(1624, 19, 12, 4, 4, 4, 2, 373),
(1625, 19, 13, 2, 6, 4, 1, 374),
(1626, 19, 14, 4, 4, 5, 2, 375),
(1627, 19, 15, 2, 6, 5, 1, 376),
(1628, 19, 16, 4, 4, 6, 2, 377),
(1629, 19, 11, 2, 5, 6, 1, 378),
(1630, 20, 12, 5, 4, 1, 2, 379),
(1631, 20, 13, 1, 7, 1, 1, 380),
(1632, 20, 14, 5, 4, 2, 2, 381),
(1633, 20, 15, 1, 7, 2, 1, 382),
(1634, 20, 16, 5, 4, 3, 2, 383),
(1635, 20, 11, 2, 6, 3, 1, 384),
(1636, 20, 12, 5, 4, 4, 2, 385),
(1637, 20, 13, 1, 7, 4, 1, 386),
(1638, 20, 14, 5, 4, 5, 2, 387),
(1639, 20, 15, 1, 7, 5, 1, 388),
(1640, 20, 16, 5, 4, 6, 2, 389),
(1641, 20, 11, 2, 6, 6, 1, 390),
(1642, 21, 8, 5, 3, 1, 2, 391),
(1643, 21, 7, 2, 4, 1, 1, 392),
(1644, 21, 5, 2, 3, 2, 1, 393),
(1645, 21, 6, 4, 2, 3, 2, 394),
(1646, 21, 8, 5, 3, 4, 2, 395),
(1647, 21, 7, 2, 4, 4, 1, 396),
(1648, 21, 10, 5, 1, 5, 2, 397),
(1649, 21, 5, 2, 3, 5, 1, 398),
(1650, 21, 6, 5, 2, 6, 2, 399),
(1651, 21, 9, 2, 3, 6, 2, 400),
(1652, 22, 6, 3, 3, 2, 2, 401),
(1653, 22, 5, 1, 4, 2, 1, 402),
(1654, 22, 8, 3, 3, 3, 2, 403),
(1655, 22, 7, 1, 4, 3, 1, 404),
(1656, 22, 10, 4, 2, 4, 2, 405),
(1657, 22, 9, 2, 3, 4, 2, 406),
(1658, 22, 6, 4, 3, 5, 2, 407),
(1659, 22, 5, 1, 4, 5, 1, 408),
(1660, 22, 8, 4, 3, 6, 2, 409),
(1661, 22, 7, 1, 4, 6, 1, 410),
(1662, 23, 1, 2, 1, 1, 1, 411),
(1663, 23, 3, 1, 2, 1, 1, 412),
(1664, 23, 2, 5, 1, 2, 2, 413),
(1665, 23, 4, 3, 1, 3, 2, 414),
(1666, 23, 1, 6, 1, 4, 1, 415),
(1667, 23, 3, 6, 2, 4, 1, 416),
(1668, 23, 2, 6, 1, 5, 2, 417),
(1669, 23, 4, 6, 1, 6, 2, 418),
(1670, 24, 2, 3, 1, 1, 2, 419),
(1671, 24, 1, 2, 2, 1, 1, 420),
(1672, 24, 3, 1, 2, 2, 1, 421),
(1673, 24, 4, 4, 1, 3, 2, 422),
(1674, 24, 2, 6, 1, 4, 2, 423),
(1675, 24, 1, 6, 2, 4, 1, 424),
(1676, 24, 3, 6, 2, 5, 1, 425),
(1677, 24, 4, 6, 1, 6, 2, 426),
(1678, 13, 38, 2, 7, 3, 2, 427),
(1679, 13, 38, 2, 7, 6, 2, 428),
(1680, 14, 33, 3, 4, 1, 2, 429),
(1681, 14, 32, 1, 6, 1, 1, 430),
(1682, 14, 35, 3, 4, 2, 2, 431),
(1683, 14, 34, 1, 6, 2, 1, 432),
(1684, 14, 37, 3, 4, 3, 2, 433),
(1685, 14, 31, 1, 5, 3, 2, 434),
(1686, 14, 36, 1, 6, 3, 1, 435),
(1687, 14, 33, 3, 4, 4, 2, 436),
(1688, 14, 32, 1, 6, 4, 1, 437),
(1689, 14, 35, 3, 4, 5, 2, 438),
(1690, 14, 34, 1, 6, 5, 1, 439),
(1691, 14, 37, 3, 4, 6, 2, 440),
(1692, 14, 31, 1, 5, 6, 2, 441),
(1693, 14, 36, 1, 6, 6, 1, 442),
(1694, 15, 29, 4, 3, 1, 2, 443),
(1695, 15, 30, 1, 4, 1, 2, 444),
(1696, 15, 25, 5, 2, 2, 2, 445),
(1697, 15, 24, 1, 3, 2, 1, 446),
(1698, 15, 28, 2, 3, 3, 1, 447),
(1699, 15, 29, 4, 3, 4, 2, 448),
(1700, 15, 30, 1, 4, 4, 2, 449),
(1701, 15, 10, 4, 1, 5, 2, 450),
(1702, 15, 25, 4, 2, 5, 2, 451),
(1703, 15, 24, 1, 3, 5, 1, 452),
(1704, 15, 9, 2, 2, 6, 2, 453),
(1705, 15, 28, 1, 3, 6, 1, 454),
(1706, 16, 20, 1, 1, 1, 1, 455),
(1707, 16, 4, 3, 2, 1, 2, 456),
(1708, 16, 21, 4, 1, 2, 2, 457),
(1709, 16, 3, 2, 1, 3, 1, 458),
(1710, 16, 20, 6, 1, 4, 1, 459),
(1711, 16, 4, 6, 2, 4, 2, 460),
(1712, 16, 21, 6, 1, 5, 2, 461),
(1713, 16, 3, 6, 1, 6, 1, 462),
(1714, 1, 61, 2, 7, 1, 2, 463),
(1715, 1, 61, 2, 7, 4, 2, 464),
(1716, 2, 61, 2, 7, 2, 2, 465),
(1717, 2, 61, 2, 7, 5, 2, 466),
(1718, 3, 61, 1, 7, 3, 2, 467),
(1719, 3, 61, 1, 7, 6, 2, 468),
(1720, 4, 59, 2, 4, 2, 2, 469),
(1721, 4, 58, 2, 4, 3, 2, 470),
(1722, 4, 57, 5, 2, 4, 2, 471),
(1723, 4, 60, 5, 2, 5, 2, 472),
(1724, 4, 56, 2, 4, 5, 2, 473),
(1725, 4, 58, 2, 4, 6, 2, 474),
(1726, 5, 56, 1, 5, 1, 2, 475),
(1727, 5, 58, 1, 5, 2, 2, 476),
(1728, 5, 57, 5, 2, 3, 2, 477),
(1729, 5, 59, 1, 5, 4, 2, 478),
(1730, 5, 60, 5, 3, 5, 2, 479),
(1731, 5, 58, 1, 5, 5, 2, 480),
(1732, 6, 56, 2, 5, 1, 2, 481),
(1733, 6, 58, 2, 5, 2, 2, 482),
(1734, 6, 57, 4, 3, 3, 2, 483),
(1735, 6, 59, 2, 5, 4, 2, 484),
(1736, 6, 58, 2, 5, 5, 2, 485),
(1737, 6, 60, 5, 3, 6, 2, 486),
(1738, 7, 45, 4, 1, 1, 2, 487),
(1739, 7, 49, 4, 2, 1, 2, 488),
(1740, 7, 55, 3, 3, 1, 2, 489),
(1741, 7, 54, 2, 2, 2, 2, 490),
(1742, 7, 47, 5, 1, 3, 2, 491),
(1743, 7, 52, 1, 2, 3, 2, 492),
(1744, 7, 44, 1, 1, 4, 2, 493),
(1745, 7, 50, 1, 2, 4, 2, 494),
(1746, 7, 46, 1, 1, 5, 2, 495),
(1747, 7, 51, 3, 2, 5, 2, 496),
(1748, 7, 48, 1, 1, 6, 2, 497),
(1749, 7, 53, 3, 2, 6, 2, 498),
(1750, 8, 45, 5, 1, 1, 2, 499),
(1751, 8, 49, 5, 2, 1, 2, 500),
(1752, 8, 54, 1, 3, 1, 2, 501),
(1753, 8, 53, 3, 2, 2, 2, 502),
(1754, 8, 51, 3, 2, 3, 2, 503),
(1755, 8, 44, 2, 1, 4, 2, 504),
(1756, 8, 48, 2, 2, 4, 2, 505),
(1757, 8, 55, 3, 3, 4, 2, 506),
(1758, 8, 46, 2, 1, 5, 2, 507),
(1759, 8, 50, 1, 2, 5, 2, 508),
(1760, 8, 47, 5, 1, 6, 2, 509),
(1761, 8, 52, 1, 2, 6, 2, 510),
(1762, 9, 50, 2, 3, 1, 2, 511),
(1763, 9, 51, 4, 2, 2, 2, 512),
(1764, 9, 48, 2, 2, 3, 2, 513),
(1765, 9, 54, 1, 3, 3, 2, 514),
(1766, 9, 47, 3, 2, 4, 2, 515),
(1767, 9, 52, 1, 3, 4, 2, 516),
(1768, 9, 45, 3, 1, 5, 2, 517),
(1769, 9, 46, 2, 2, 5, 2, 518),
(1770, 9, 53, 3, 3, 5, 2, 519),
(1771, 9, 44, 2, 1, 6, 2, 520),
(1772, 9, 49, 4, 2, 6, 2, 521),
(1773, 9, 55, 3, 3, 6, 2, 522),
(1774, 10, 39, 6, 1, 1, 2, 523),
(1775, 10, 41, 1, 1, 2, 2, 524),
(1776, 10, 42, 6, 1, 3, 2, 525),
(1777, 10, 40, 3, 1, 4, 2, 526),
(1778, 10, 41, 6, 1, 5, 2, 527),
(1779, 10, 43, 3, 1, 6, 2, 528),
(1780, 11, 39, 6, 1, 1, 2, 529),
(1781, 11, 41, 2, 1, 2, 2, 530),
(1782, 11, 42, 6, 1, 3, 2, 531),
(1783, 11, 40, 4, 1, 4, 2, 532),
(1784, 11, 41, 6, 1, 5, 2, 533),
(1785, 11, 43, 4, 1, 6, 2, 534),
(1786, 12, 39, 6, 1, 1, 2, 535),
(1787, 12, 43, 3, 1, 2, 2, 536),
(1788, 12, 41, 1, 1, 3, 2, 537),
(1789, 12, 40, 5, 1, 4, 2, 538),
(1790, 12, 42, 6, 1, 5, 2, 539),
(1791, 12, 41, 6, 1, 6, 2, 540);

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
-- Indexes for table `actual_time`
--
ALTER TABLE `actual_time`
  ADD PRIMARY KEY (`ActualTimeID`);

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
  ADD KEY `duration_id` (`duration_id`),
  ADD KEY `ActualTimeID` (`ActualTimeID`);

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
-- AUTO_INCREMENT for table `actual_time`
--
ALTER TABLE `actual_time`
  MODIFY `ActualTimeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=541;

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
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1792;

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
  ADD CONSTRAINT `schedule_ibfk_4` FOREIGN KEY (`TimeSlotID`) REFERENCES `timeslots` (`TimeSlotID`),
  ADD CONSTRAINT `schedule_ibfk_5` FOREIGN KEY (`ActualTimeID`) REFERENCES `actual_time` (`ActualTimeID`) ON DELETE SET NULL ON UPDATE CASCADE;

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
