# Fixed SQL Dump (LearningportalBackup)

This is the corrected dump with the database name `LearningportalBackup` and a primary key added to `exercise_questions` before enabling `AUTO_INCREMENT`.

```sql
-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.infinityfree.com
-- Generation Time: 09.12.2025 klo 10:15
-- Server version: 10.6.22-MariaDB
-- PHP Version: 7.2.22

CREATE DATABASE IF NOT EXISTS `LearningportalBackup`;
USE `LearningportalBackup`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table structure for table `achievements`
-- --------------------------------------------------------

CREATE TABLE `achievements` (
  `Achv_Id` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `class`
-- --------------------------------------------------------

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `exercises`
-- --------------------------------------------------------

CREATE TABLE `exercises` (
  `Exercise_Id` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Type` enum('mcq','true_false','match','ordering','fill_blank','mixed') NOT NULL,
  `Is_Template` tinyint(1) DEFAULT 0,
  `Created_By` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping data for table `exercises`
-- --------------------------------------------------------

INSERT INTO `exercises` (`Exercise_Id`, `Title`, `Description`, `Type`, `Is_Template`, `Created_By`) VALUES
(69, 'Lisa handlar', 'Lisa gick till affären för att köpa mjölk. På vägen träffade hon sin vän Max som hade en hund med sig. Tillsammans gick de till affären och köpte glass. Sedan satte de sig i parken för att äta den.', 'mcq', 0, NULL),
(70, 'Den borttappade mössan (nivå 1/10)', 'Emma gick till skolan en kall morgon. När hon kom fram märkte hon att hennes mössa var borta. Hon blev ledsen och började leta. Hon gick hela vägen tillbaka hem. På vägen såg hon mössan ligga i snön. Emma tog upp den och borstade bort snön. Sedan sprang hon till skolan igen och hann precis i tid.', 'ordering', 0, NULL),
(71, 'Glass i Parken', 'Lisa gick till parken med sin pappa en solig dag. De köpte varsin glass vid kiosken. Lisa valde jordgubbssmak och hennes pappa tog choklad. De satte sig på en bänk och tittade på ankorna i dammen. En liten pojke tappade sin glass på marken och började gråta. Lisa gav honom lite av sin egen glass och han blev glad igen.', 'true_false', 0, NULL),
(72, 'Tågresan', 'Albin skulle åka tåg till sin mormor för första gången ensam. Hans mamma följde honom till stationen och hjälpte honom hitta rätt perrong. Tåget var stort och fullt av folk. Albin hittade sin plats vid fönstret och la väskan på hyllan ovanför.\n\nNär tåget började rulla kände han sig lite nervös men också stolt. Han såg hur staden försvann bakom honom och skogarna började ta över utanför fönstret. En äldre dam som satt bredvid frågade vart han skulle, och de började prata. Hon bjöd honom på en karamell, och tiden gick fort.\n\nNär tåget stannade vid mormors station stod hon redan och väntade på perrongen. Albin vinkade och sprang fram för att krama henne. Det hade gått mycket bättre än han trott.', 'mcq', 0, NULL),
(73, 'Ett nytt husdjur” – Nivå 10', 'Sara hade alltid velat ha ett eget husdjur. En dag gick hennes familj till djuraffären ”Djurvännen”. Där fanns kaniner, marsvin och fiskar, men Sara fastnade direkt för en liten sköldpadda som låg stilla på en sten.\n\nSäljaren berättade att sköldpaddor kunde leva i många år om man tog väl hand om dem. De behövde värme, rent vatten och mat varje dag. Sara lyssnade noga och skrev upp allt i sin anteckningsbok.\n\nHemma fick sköldpaddan ett akvarium med sand, en liten grotta och en värmelampa. Hon döpte den till Skalis. De första dagarna var Sara försiktig, men snart vågade hon mata och till och med klappa den försiktigt på skalet. Skalis verkade trivas och brukade simma fram när hon kom nära glaset.', 'match', 0, NULL),
(75, 'Final build test MIXED', 'THIS IS DEMO DATA HELLO!', 'mixed', 0, 8),
(78, 'patch test', '', 'mixed', 0, 8),
(80, 'SCHNECKE', 'DEDEDE', 'fill_blank', 0, 8);

-- --------------------------------------------------------
-- Table structure for table `exercise_questions`
-- --------------------------------------------------------

CREATE TABLE `exercise_questions` (
  `Question_Id` int(11) NOT NULL,
  `Exercise_Id` int(11) NOT NULL,
  `Statement` text NOT NULL,
  `Correct` tinyint(1) DEFAULT NULL,
  `Question_Type` enum('true_false','mcq','ordering','match','fill_blank') DEFAULT 'mcq',
  `Data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`Question_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping data for table `exercise_questions`
-- --------------------------------------------------------

INSERT INTO `exercise_questions` (`Question_Id`, `Exercise_Id`, `Statement`, `Correct`, `Question_Type`, `Data`) VALUES
(109, 73, 'Para ihop orden till vänster med rätt förklaring från texten.', NULL, 'match', '{\"type\":\"match\",\"data\":{\"text\":\"Para ihop orden till vänster med rätt förklaring från texten.\",\"pairs\":[{\"left\":\"Sara valde sitt husdjur i affären som hette …\",\"right\":\"Djurvännen\"},{\"left\":\"Djuret som Sara valde var …\",\"right\":\"Sköldpadda\"},{\"left\":\"Skalis bor hemma i …\",\"right\":\"Akvarium\"},{\"left\":\"Lampan i Saras djurbur heter\",\"right\":\"Värmelamppa\"},{\"left\":\"Personen som berättade hur man tar hand om sköldpaddan var …\",\"right\":\"Försäljaren\"},{\"left\":\"Sara skrev ner information i sin...\",\"right\":\"Anteckningsbok\"},{\"left\":\"På botten av akvariet låg det...\",\"right\":\"Sand\"},{\"left\":\"I akvariet fans det ochkså en liten...\",\"right\":\"Grotta\"},{\"left\":\"Skalis simmade fram till glaset när Sara …\",\"right\":\"Kom nära\"},{\"left\":\"När Sara tog hand om Skalis ordentligt, verkade hon …\",\"right\":\"Trivas\"}]}}'),
(110, 72, 'Albin reste tillsammans med sin mamma hela vägen.', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Albin reste tillsammans med sin mamma hela vägen.\",\"answer\":false}}'),
(111, 72, 'Albin satt bredvid en äldre dam som bjöd honom på karamell.', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Albin satt bredvid en äldre dam som bjöd honom på karamell.\",\"answer\":true}}'),
(112, 72, 'Tåget stannade vid mormors station och hon väntade där.', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Tåget stannade vid mormors station och hon väntade där.\",\"answer\":true}}'),
(113, 72, 'Det var första gången Albin åkte tåg ensam.', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Det var första gången Albin åkte tåg ensam.\",\"answer\":true}}'),
(114, 71, 'Lisa gick till parken med sin mamma', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Lisa gick till parken med sin mamma\",\"answer\":false}}'),
(115, 71, 'Det var en solig dag när de gick till parken.', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Det var en solig dag när de gick till parken.\",\"answer\":true}}'),
(116, 71, 'Lisa valde jordgubbsglass', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Lisa valde jordgubbsglass\",\"answer\":true}}'),
(117, 71, 'De tittade på ankor i dammen', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"De tittade på ankor i dammen\",\"answer\":true}}'),
(118, 71, 'Lisa skrattade till pojken som tappade glassen', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"Lisa skrattade till pojken som tappade glassen\",\"answer\":false}}'),
(119, 70, 'Sorteringsövning – ordna meningar i rätt ordning', NULL, 'ordering', '{\"type\":\"ordering\",\"data\":{\"text\":\"Sorteringsövning – ordna meningar i rätt ordning\",\"items\":[\"På vägen såg hon mössan ligga i snön.\",\"Hon blev ledsen och började leta.\",\"Emma gick till skolan en kall morgon.\",\"Sedan sprang hon till skolan igen och hann precis i tid.\",\"Hon gick hela vägen tillbaka hem\",\"När hon kom fram märkte hon att hennes mössa var borta.\"]}}'),
(120, 69, 'Vart gick Lisa först', NULL, 'mcq', '{\"type\":\"mcq\",\"data\":{\"text\":\"Vart gick Lisa först\",\"options\":[{\"text\":\"Till skolan\",\"isCorrect\":false},{\"text\":\"Till affären\",\"isCorrect\":true},{\"text\":\"Till parken\",\"isCorrect\":false}]}}'),
(121, 69, 'Vem träffade Lisa på vägen', NULL, 'mcq', '{\"type\":\"mcq\",\"data\":{\"text\":\"Vem träffade Lisa på vägen\",\"options\":[{\"text\":\"Max\",\"isCorrect\":true},{\"text\":\"En hund\",\"isCorrect\":false},{\"text\":\"Sin mamma\",\"isCorrect\":false}]}}'),
(122, 69, 'Vad gjorde de efter att det handlat?', NULL, 'mcq', '{\"type\":\"mcq\",\"data\":{\"text\":\"Vad gjorde de efter att det handlat?\",\"options\":[{\"text\":\"Gick hem\",\"isCorrect\":false},{\"text\":\"Satte sig i parken\",\"isCorrect\":true},{\"text\":\"Åkte buss\",\"isCorrect\":false}]}}'),
(129, 75, 'vilket bilmärke van ALMS 2005?', NULL, 'mcq', '{\"type\":\"mcq\",\"data\":{\"text\":\"vilket bilmärke van ALMS 2005?\",\"options\":[{\"text\":\"BMW\",\"isCorrect\":true},{\"text\":\"PORSCHE\",\"isCorrect\":false},{\"text\":\"CHEVROLET\",\"isCorrect\":false}]}}'),
(130, 75, 'ÄR BMW M3 GTR VÄXELÅDA HELICAL?', NULL, 'true_false', '{\"type\":\"true_false\",\"data\":{\"text\":\"ÄR BMW M3 GTR VÄXELÅDA HELICAL?\",\"answer\":false}}'),
(131, 75, 'Oj Nej! Ordena har farit fel, fixa dem och gör en mening!', NULL, 'ordering', '{\"type\":\"ordering\",\"data\":{\"text\":\"Oj Nej! Ordena har farit fel, fixa dem och gör en mening!\",\"items\":[\"BMW\",\"2005\",\"VAN\",\"ALMS\",\"CUP\"]}}'),
(132, 75, 'Para ihop bilmärken med landet det kommer från!', NULL, 'match', '{\"type\":\"match\",\"data\":{\"text\":\"Para ihop bilmärken med landet det kommer från!\",\"pairs\":[{\"left\":\"BMW\",\"right\":\"TYSKT\"},{\"left\":\"TOYOTA\",\"right\":\"JAPANSKT\"},{\"left\":\"VOLVO\",\"right\":\"SVENSKT\"},{\"left\":\"DODGE\",\"right\":\"U.S.A\"},{\"left\":\"LADA\",\"right\":\"USSR\"}]}}'),
(133, 75, 'Min bil är färgad...', NULL, 'fill_blank', '{\"type\":\"fill_blank\",\"data\":{\"text\":\"Min bil är färgad...\",\"answers\":[],\"options\":[\"Svart\",\"blå\",\"gul\",\"hotpink\"]}}'),
(137, 78, 'BMW VAN ____ CUP', NULL, 'fill_blank', '{\"type\":\"fill_blank\",\"data\":{\"text\":\"BMW VAN ____ CUP\",\"answers\":[\"2005\"],\"options\":[{\"text\":\"2005\",\"isCorrect\":true},{\"text\":\"2006\",\"isCorrect\":false}]}}'),
(139, 80, 'BMW 2005', NULL, 'fill_blank', '{\"type\":\"fill_blank\",\"data\":{\"text\":\"BMW 2005\",\"blanks\":[{\"index\":0,\"word\":\"BMW\"}],\"answers\":[\"BMW\"],\"words\":[\"BMW\",\"AUDI\",\"MERC\"]}}');

-- --------------------------------------------------------
-- Table structure for table `experience_levels`
-- --------------------------------------------------------

CREATE TABLE `experience_levels` (
  `Level_Id` int(11) NOT NULL,
  `Level_Name` varchar(50) DEFAULT NULL,
  `XP_Required` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping data for table `experience_levels`
-- --------------------------------------------------------

INSERT INTO `experience_levels` (`Level_Id`, `Level_Name`, `XP_Required`) VALUES
(1, 'Level 1', 0),
(2, 'Level 2', 100),
(3, 'Level 3', 300),
(4, 'Level 4', 600),
(5, 'Level 5', 1000),
(6, 'Level 6', 1500),
(7, 'Level 7', 2100),
(8, 'Level 8', 2800),
(9, 'Level 9', 3600),
(10, 'Level 10', 4500),
(11, 'Level 11', 5500),
(12, 'Level 12', 6600),
(13, 'Level 13', 7800),
(14, 'Level 14', 9100),
(15, 'Level 15', 10500),
(16, 'Level 16', 12000),
(17, 'Level 17', 13600),
(18, 'Level 18', 15300),
(19, 'Level 19', 17100),
(20, 'Level 20', 19000),
(21, 'Level 21', 21000),
(22, 'Level 22', 23100),
(23, 'Level 23', 25300),
(24, 'Level 24', 27600),
(25, 'Level 25', 30000),
(26, 'Level 26', 32500),
(27, 'Level 27', 35100),
(28, 'Level 28', 37800),
(29, 'Level 29', 40600),
(30, 'Level 30', 43500),
(31, 'Level 31', 46500),
(32, 'Level 32', 49600),
(33, 'Level 33', 52800),
(34, 'Level 34', 56100),
(35, 'Level 35', 59500),
(36, 'Level 36', 63000),
(37, 'Level 37', 66600),
(38, 'Level 38', 70300),
(39, 'Level 39', 74100),
(40, 'Level 40', 78000),
(41, 'Level 41', 82000),
(42, 'Level 42', 86100),
(43, 'Level 43', 90300),
(44, 'Level 44', 94600),
(45, 'Level 45', 99000),
(46, 'Level 46', 103500),
(47, 'Level 47', 108100),
(48, 'Level 48', 112800),
(49, 'Level 49', 117600),
(50, 'Level 50', 122500),
(51, 'Level 51', 127500),
(52, 'Level 52', 132600),
(53, 'Level 53', 137800),
(54, 'Level 54', 143100),
(55, 'Level 55', 148500),
(56, 'Level 56', 154000),
(57, 'Level 57', 159600),
(58, 'Level 58', 165300),
(59, 'Level 59', 171100),
(60, 'Level 60', 177000),
(61, 'Level 61', 183000),
(62, 'Level 62', 189100),
(63, 'Level 63', 195300),
(64, 'Level 64', 201600),
(65, 'Level 65', 208000),
(66, 'Level 66', 214500),
(67, 'Level 67', 221100),
(68, 'Level 68', 227800),
(69, 'Level 69', 234600),
(70, 'Level 70', 241500),
(71, 'Level 71', 248500),
(72, 'Level 72', 255600),
(73, 'Level 73', 262800),
(74, 'Level 74', 270100),
(75, 'Level 75', 277500),
(76, 'Level 76', 285000),
(77, 'Level 77', 292600),
(78, 'Level 78', 300300),
(79, 'Level 79', 308100),
(80, 'Level 80', 316000),
(81, 'Level 81', 324000),
(82, 'Level 82', 332100),
(83, 'Level 83', 340300),
(84, 'Level 84', 348600),
(85, 'Level 85', 357000),
(86, 'Level 86', 365500),
(87, 'Level 87', 374100),
(88, 'Level 88', 382800),
(89, 'Level 89', 391600),
(90, 'Level 90', 400500),
(91, 'Level 91', 409500),
(92, 'Level 92', 418600),
(93, 'Level 93', 427800),
(94, 'Level 94', 437100),
(95, 'Level 95', 446500),
(96, 'Level 96', 456000),
(97, 'Level 97', 465600),
(98, 'Level 98', 475300),
(99, 'Level 99', 485100),
(100, 'Level 100', 495000);

-- --------------------------------------------------------
-- Table structure for table `materials`
-- --------------------------------------------------------

CREATE TABLE `materials` (
  `Material_Id` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `Created_By` int(11) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping data for table `materials`
-- --------------------------------------------------------

INSERT INTO `materials` (`Material_Id`, `Title`, `Content`, `Created_By`, `Created_At`) VALUES
(2, 'Why Leopard 2a4 is superior to t72a', 'yes', 8, '2025-12-07 18:48:18'),
(3, 'AS', 'AS', 8, '2025-12-07 20:43:11'),
(4, 'Lisa handlar', 'Lisa gick till affären för att köpa mjölk. På vägen träffade hon sin vän Max som hade en hund med sig. Tillsammans gick de till affären och köpte glass. Sedan satte de sig i parken för att äta den.', NULL, '2025-12-08 07:20:42'),
(5, 'Den borttappade mössan (nivå 1/10)', 'Emma gick till skolan en kall morgon. När hon kom fram märkte hon att hennes mössa var borta. Hon blev ledsen och började leta. Hon gick hela vägen tillbaka hem. På vägen såg hon mössan ligga i snön. Emma tog upp den och borstade bort snön. Sedan sprang hon till skolan igen och hann precis i tid.', NULL, '2025-12-08 07:32:16'),
(6, 'demo', 'mo', 8, '2025-12-08 23:05:55'),
(7, 'demo', 'd', 8, '2025-12-08 23:06:01'),
(8, 'de', 'd', 8, '2025-12-08 23:06:03');

-- --------------------------------------------------------
-- Table structure for table `question_options`
-- --------------------------------------------------------

CREATE TABLE `question_options` (
  `Option_Id` int(11) NOT NULL,
  `Question_Id` int(11) NOT NULL,
  `Option_Text` varchar(255) NOT NULL,
  `Is_Correct` tinyint(1) DEFAULT 0,
  `Order_Number` int(11) DEFAULT NULL,
  `Pair_Group` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping data for table `roles`
-- --------------------------------------------------------

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'student'),
(2, 'teacher'),
(3, 'admin');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `xp` int(11) DEFAULT 0,
  `class_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping data for table `users`
-- --------------------------------------------------------

INSERT INTO `users` (`u_id`, `username`, `email`, `password`, `role_id`, `xp`, `class_id`, `created_at`) VALUES
(1, 'teacher1', 'teacher@example.com', '$2y$10$bgus0/pJXjmRRQ6trWQSiuNdwT1/KBk2CHGh.5PIgvpYh7tAjYHNq', 3, 0, NULL, '2025-11-10 21:12:02'),
(2, 'Student1', NULL, 'student123', 1, 0, NULL, '2025-11-21 18:49:06'),
(3, 'test1', NULL, 'test1', 1, 830, NULL, '2025-11-21 18:50:58'),
(6, 'topgunmaverick', NULL, 'Enkulla1', 1, 0, NULL, '2025-12-07 15:52:32'),
(7, 'topgunmavericktest244', 'topgun@gmail.com', '$2y$10$VpxE/5wy5oaYhSU0hwCj3uH2hcSSpyzZDXaiNx8O5cgBIJrEE8yoW', 1, 0, NULL, '2025-12-07 15:54:43'),
(8, 'Admin', 'Admin@gmail.com', '$2y$10$WAMRpNeeJEcRMufajztwpuR1Z8wX9z/kFtT6k5X1v9qU6Uc/bKWDK', 3, 0, NULL, '2025-12-07 17:56:54'),
(12, 'viktor1', 'testing@gmail.c', '$2y$10$DPDL7mvrt40wpcFDWsgNme.nW3teRHcOg99ng.t9Clsu.wGQZoRQS', 1, 50, NULL, '2025-12-07 18:18:55'),
(15, 'testing9.12', '912@gmai.ds', '$2y$10$EfYKLZULX2XPL2OTIDB99.klOEYhzqTZB1yAqQrzg5GD/CcVA2gCW', 1, 0, NULL, '2025-12-09 07:16:23'),
(16, 'test2', 'esst@gmail.com', '$2y$10$YcEPbUbwvH53dpZvoPhch.O1JrvjZmWIDhsmsY89.gt9boCEG9dYG', 1, 0, NULL, '2025-12-09 07:58:22');

-- --------------------------------------------------------
-- Table structure for table `user_achievements`
-- --------------------------------------------------------

CREATE TABLE `user_achievements` (
  `User_Id` int(11) NOT NULL,
  `Achv_Id` int(11) NOT NULL,
  `Earned_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `user_results`
-- --------------------------------------------------------

CREATE TABLE `user_results` (
  `Result_Id` int(11) NOT NULL,
  `User_Id` int(11) NOT NULL,
  `Exercise_Id` int(11) NOT NULL,
  `Score` int(11) DEFAULT 0,
  `Total_Questions` int(11) NOT NULL DEFAULT 0,
  `Completed` tinyint(1) DEFAULT 0,
  `Completed_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping data for table `user_results`
-- --------------------------------------------------------

INSERT INTO `user_results` (`Result_Id`, `User_Id`, `Exercise_Id`, `Score`, `Total_Questions`, `Completed`, `Completed_At`) VALUES
(24, 3, 70, 100, 0, 1, '2025-12-08 11:56:04'),
(26, 3, 71, 60, 0, 0, '2025-12-08 11:56:45'),
(27, 3, 69, 67, 0, 0, '2025-12-08 11:57:25'),
(28, 3, 72, 0, 0, 0, '2025-12-08 12:04:28'),
(31, 3, 73, 0, 0, 0, '2025-12-08 23:01:27'),
(32, 3, 73, 0, 0, 0, '2025-12-08 23:01:35'),
(33, 3, 75, 60, 0, 0, '2025-12-08 23:02:35'),
(34, 3, 71, 80, 0, 1, '2025-12-08 23:04:20');

-- --------------------------------------------------------
-- Indexes for dumped tables
-- --------------------------------------------------------

ALTER TABLE `achievements`
  ADD PRIMARY KEY (`Achv_Id`);

ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

ALTER TABLE `exercises`
  ADD PRIMARY KEY (`Exercise_Id`),
  ADD KEY `Created_By` (`Created_By`);

ALTER TABLE `experience_levels`
  ADD PRIMARY KEY (`Level_Id`);

ALTER TABLE `materials`
  ADD PRIMARY KEY (`Material_Id`),
  ADD KEY `Created_By` (`Created_By`);

ALTER TABLE `question_options`
  ADD PRIMARY KEY (`Option_Id`),
  ADD KEY `Question_Id` (`Question_Id`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);

ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`User_Id`,`Achv_Id`),
  ADD KEY `Achv_Id` (`Achv_Id`);

ALTER TABLE `user_results`
  ADD PRIMARY KEY (`Result_Id`),
  ADD KEY `User_Id` (`User_Id`),
  ADD KEY `Exercise_Id` (`Exercise_Id`);

-- Ensure exercise_questions has a key so AUTO_INCREMENT is allowed
ALTER TABLE `exercise_questions`
  ADD PRIMARY KEY (`Question_Id`);

-- --------------------------------------------------------
-- AUTO_INCREMENT for dumped tables
-- --------------------------------------------------------

ALTER TABLE `achievements`
  MODIFY `Achv_Id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `exercises`
  MODIFY `Exercise_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

ALTER TABLE `exercise_questions`
  MODIFY `Question_Id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `experience_levels`
  MODIFY `Level_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

ALTER TABLE `materials`
  MODIFY `Material_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `question_options`
  MODIFY `Option_Id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `user_results`
  MODIFY `Result_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

-- --------------------------------------------------------
-- Constraints for dumped tables
-- --------------------------------------------------------

ALTER TABLE `exercises`
  ADD CONSTRAINT `exercises_ibfk_1` FOREIGN KEY (`Created_By`) REFERENCES `users` (`u_id`) ON DELETE SET NULL;

ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`Created_By`) REFERENCES `users` (`u_id`) ON DELETE SET NULL;

ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`Question_Id`) REFERENCES `exercise_questions` (`Question_Id`) ON DELETE CASCADE;

ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`Achv_Id`) REFERENCES `achievements` (`Achv_Id`) ON DELETE CASCADE;

ALTER TABLE `user_results`
  ADD CONSTRAINT `user_results_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_results_ibfk_2` FOREIGN KEY (`Exercise_Id`) REFERENCES `exercises` (`Exercise_Id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
```
