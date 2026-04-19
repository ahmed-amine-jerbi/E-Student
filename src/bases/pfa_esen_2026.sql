-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 19, 2026 at 10:32 PM
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
-- Database: `pfa_esen_2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `Id` int(11) NOT NULL,
  `Titre` varchar(150) NOT NULL,
  `Contenu` text NOT NULL,
  `DatePublication` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`Id`, `Titre`, `Contenu`, `DatePublication`) VALUES
(1, 'Test annonce', 'abcdefghjklmnopqrstuvwxyz1234567890', '2026-04-19 21:20:00'),
(2, 'test', 'Test', '2026-04-18 21:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `EnsId` int(11) NOT NULL,
  `FiliereId` int(11) NOT NULL,
  `NiveauId` int(11) NOT NULL,
  `GroupeId` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Status` enum('Present','Absent') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`Id`, `UserId`, `EnsId`, `FiliereId`, `NiveauId`, `GroupeId`, `Date`, `Status`) VALUES
(1, 9, 8, 3, 2, 7, '2026-04-19', 'Absent'),
(2, 9, 8, 3, 2, 7, '2026-04-03', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `Id` int(11) NOT NULL,
  `NiveauId` int(11) NOT NULL,
  `GroupeId` int(11) NOT NULL,
  `EnsId` int(11) NOT NULL,
  `FiliereId` int(11) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Cle` varchar(50) NOT NULL,
  `Libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `channels`
--

INSERT INTO `channels` (`Id`, `NiveauId`, `GroupeId`, `EnsId`, `FiliereId`, `Description`, `Cle`, `Libelle`) VALUES
(6, 2, 7, 8, 3, 'Cours / TD / TP ASD', 'ASD1', 'chaine 1'),
(7, 2, 7, 8, 3, 'Cours ASD2', 'ASD2', 'chaine 2');

-- --------------------------------------------------------

--
-- Table structure for table `devoirs`
--

CREATE TABLE `devoirs` (
  `Id` int(11) NOT NULL,
  `FiliereId` int(11) NOT NULL,
  `NiveauId` int(11) NOT NULL,
  `GroupeId` int(11) NOT NULL,
  `EnsId` int(11) NOT NULL,
  `Titre` varchar(50) NOT NULL,
  `Deadline` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devoirs`
--

INSERT INTO `devoirs` (`Id`, `FiliereId`, `NiveauId`, `GroupeId`, `EnsId`, `Titre`, `Deadline`) VALUES
(1, 3, 2, 7, 8, 'bla bla bla', '2026-04-30 16:44:00'),
(2, 3, 2, 7, 8, 'AA', '2026-04-19 21:44:00'),
(3, 3, 2, 7, 8, 'EXPIRED', '2026-04-01 16:44:00'),
(4, 3, 2, 7, 8, 'EH', '2026-04-18 16:44:00');

-- --------------------------------------------------------

--
-- Table structure for table `emplois`
--

CREATE TABLE `emplois` (
  `GroupeId` int(11) NOT NULL,
  `NiveauId` int(11) NOT NULL,
  `FiliereId` int(11) NOT NULL,
  `MatiereId` int(11) NOT NULL,
  `Jour` enum('Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
  `HoraireId` int(11) NOT NULL,
  `EnsId` int(11) NOT NULL,
  `Salle` varchar(50) NOT NULL,
  `Type` enum('Cours','TD','TP') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emplois`
--

INSERT INTO `emplois` (`GroupeId`, `NiveauId`, `FiliereId`, `MatiereId`, `Jour`, `HoraireId`, `EnsId`, `Salle`, `Type`) VALUES
(1, 1, 1, 1, 'Lundi', 1, 11, 'A0.1', 'Cours'),
(1, 1, 1, 1, 'Mercredi', 5, 28, 'A0.2', 'TD'),
(1, 1, 1, 1, 'Samedi', 2, 11, 'A0.3', 'TP'),
(1, 1, 1, 2, 'Lundi', 2, 28, 'A0.2', 'TD'),
(1, 1, 1, 2, 'Jeudi', 1, 11, 'A0.3', 'Cours'),
(1, 1, 1, 3, 'Lundi', 5, 11, 'A0.3', 'TP'),
(1, 1, 1, 3, 'Jeudi', 2, 27, 'A0.4', 'TD'),
(1, 1, 1, 4, 'Mardi', 1, 27, 'A0.4', 'Cours'),
(1, 1, 1, 4, 'Jeudi', 5, 28, 'A0.5', 'TP'),
(1, 1, 1, 5, 'Mardi', 2, 28, 'A0.5', 'TD'),
(1, 1, 1, 5, 'Vendredi', 1, 11, 'A0.6', 'Cours'),
(1, 1, 1, 6, 'Mercredi', 1, 11, 'A0.6', 'Cours'),
(1, 1, 1, 6, 'Vendredi', 2, 27, 'A0.1', 'TD'),
(1, 1, 1, 7, 'Mercredi', 2, 27, 'A0.1', 'TP'),
(1, 1, 1, 7, 'Samedi', 1, 28, 'A0.2', 'Cours'),
(2, 1, 1, 1, 'Lundi', 1, 12, 'B0.1', 'Cours'),
(2, 1, 1, 1, 'Mercredi', 5, 30, 'B0.2', 'TD'),
(2, 1, 1, 1, 'Samedi', 2, 12, 'B0.3', 'TP'),
(2, 1, 1, 2, 'Lundi', 2, 30, 'B0.2', 'TD'),
(2, 1, 1, 2, 'Jeudi', 1, 12, 'B0.3', 'Cours'),
(2, 1, 1, 3, 'Lundi', 5, 12, 'B0.3', 'TP'),
(2, 1, 1, 3, 'Jeudi', 2, 29, 'B0.4', 'TD'),
(2, 1, 1, 4, 'Mardi', 1, 29, 'B0.4', 'Cours'),
(2, 1, 1, 4, 'Jeudi', 5, 30, 'B0.5', 'TP'),
(2, 1, 1, 5, 'Mardi', 2, 30, 'B0.5', 'TD'),
(2, 1, 1, 5, 'Vendredi', 1, 12, 'B0.6', 'Cours'),
(2, 1, 1, 6, 'Mercredi', 1, 12, 'B0.6', 'Cours'),
(2, 1, 1, 6, 'Vendredi', 2, 29, 'B0.1', 'TD'),
(2, 1, 1, 7, 'Mercredi', 2, 29, 'B0.1', 'TP'),
(2, 1, 1, 7, 'Samedi', 1, 30, 'B0.2', 'Cours'),
(3, 1, 1, 1, 'Lundi', 1, 13, 'A0.1', 'Cours'),
(3, 1, 1, 1, 'Mercredi', 5, 32, 'A0.2', 'TD'),
(3, 1, 1, 1, 'Samedi', 2, 13, 'A0.3', 'TP'),
(3, 1, 1, 2, 'Lundi', 2, 32, 'A0.2', 'TD'),
(3, 1, 1, 2, 'Jeudi', 1, 13, 'A0.3', 'Cours'),
(3, 1, 1, 3, 'Lundi', 5, 13, 'A0.3', 'TP'),
(3, 1, 1, 3, 'Jeudi', 2, 31, 'A0.4', 'TD'),
(3, 1, 1, 4, 'Mardi', 1, 31, 'A0.4', 'Cours'),
(3, 1, 1, 4, 'Jeudi', 5, 32, 'A0.5', 'TP'),
(3, 1, 1, 5, 'Mardi', 2, 32, 'A0.5', 'TD'),
(3, 1, 1, 5, 'Vendredi', 1, 13, 'A0.6', 'Cours'),
(3, 1, 1, 6, 'Mercredi', 1, 13, 'A0.6', 'Cours'),
(3, 1, 1, 6, 'Vendredi', 2, 31, 'A0.1', 'TD'),
(3, 1, 1, 7, 'Mercredi', 2, 31, 'A0.1', 'TP'),
(3, 1, 1, 7, 'Samedi', 1, 32, 'A0.2', 'Cours'),
(4, 2, 2, 1, 'Mercredi', 1, 14, 'B1.6', 'Cours'),
(4, 2, 2, 1, 'Vendredi', 2, 33, 'B1.1', 'TD'),
(4, 2, 2, 2, 'Mercredi', 2, 33, 'B1.1', 'TP'),
(4, 2, 2, 2, 'Samedi', 1, 34, 'B1.2', 'Cours'),
(4, 2, 2, 8, 'Lundi', 1, 14, 'B1.1', 'Cours'),
(4, 2, 2, 8, 'Mercredi', 5, 34, 'B1.2', 'TD'),
(4, 2, 2, 8, 'Samedi', 2, 14, 'B1.3', 'TP'),
(4, 2, 2, 9, 'Lundi', 2, 34, 'B1.2', 'TD'),
(4, 2, 2, 9, 'Jeudi', 1, 14, 'B1.3', 'Cours'),
(4, 2, 2, 10, 'Lundi', 5, 14, 'B1.3', 'TP'),
(4, 2, 2, 10, 'Jeudi', 2, 33, 'B1.4', 'TD'),
(4, 2, 2, 11, 'Mardi', 1, 33, 'B1.4', 'Cours'),
(4, 2, 2, 11, 'Jeudi', 5, 34, 'B1.5', 'TP'),
(4, 2, 2, 12, 'Mardi', 2, 34, 'B1.5', 'TD'),
(4, 2, 2, 12, 'Vendredi', 1, 14, 'B1.6', 'Cours'),
(5, 2, 2, 1, 'Mercredi', 1, 15, 'A1.6', 'Cours'),
(5, 2, 2, 1, 'Vendredi', 2, 35, 'A1.1', 'TD'),
(5, 2, 2, 2, 'Mercredi', 2, 35, 'A1.1', 'TP'),
(5, 2, 2, 2, 'Samedi', 1, 36, 'A1.2', 'Cours'),
(5, 2, 2, 8, 'Lundi', 1, 15, 'A1.1', 'Cours'),
(5, 2, 2, 8, 'Mercredi', 5, 36, 'A1.2', 'TD'),
(5, 2, 2, 8, 'Samedi', 2, 15, 'A1.3', 'TP'),
(5, 2, 2, 9, 'Lundi', 2, 36, 'A1.2', 'TD'),
(5, 2, 2, 9, 'Jeudi', 1, 15, 'A1.3', 'Cours'),
(5, 2, 2, 10, 'Lundi', 5, 15, 'A1.3', 'TP'),
(5, 2, 2, 10, 'Jeudi', 2, 35, 'A1.4', 'TD'),
(5, 2, 2, 11, 'Mardi', 1, 35, 'A1.4', 'Cours'),
(5, 2, 2, 11, 'Jeudi', 5, 36, 'A1.5', 'TP'),
(5, 2, 2, 12, 'Mardi', 2, 36, 'A1.5', 'TD'),
(5, 2, 2, 12, 'Vendredi', 1, 15, 'A1.6', 'Cours'),
(6, 2, 3, 1, 'Mercredi', 1, 16, 'B1.6', 'Cours'),
(6, 2, 3, 1, 'Vendredi', 2, 37, 'B1.1', 'TD'),
(6, 2, 3, 2, 'Mercredi', 2, 24, 'B1.1', 'TP'),
(6, 2, 3, 2, 'Samedi', 1, 23, 'B1.2', 'Cours'),
(6, 2, 3, 8, 'Lundi', 1, 16, 'B1.1', 'Cours'),
(6, 2, 3, 8, 'Mercredi', 5, 37, 'B1.2', 'TD'),
(6, 2, 3, 8, 'Samedi', 2, 24, 'B1.3', 'TP'),
(6, 2, 3, 9, 'Lundi', 2, 24, 'B1.2', 'TD'),
(6, 2, 3, 9, 'Jeudi', 1, 16, 'B1.3', 'Cours'),
(6, 2, 3, 10, 'Lundi', 5, 37, 'B1.3', 'TP'),
(6, 2, 3, 10, 'Jeudi', 2, 23, 'B1.4', 'TD'),
(6, 2, 3, 11, 'Mardi', 1, 16, 'B1.4', 'Cours'),
(6, 2, 3, 11, 'Jeudi', 5, 24, 'B1.5', 'TP'),
(6, 2, 3, 12, 'Mardi', 2, 23, 'B1.5', 'TD'),
(6, 2, 3, 12, 'Vendredi', 1, 16, 'B1.6', 'Cours'),
(7, 2, 3, 3, 'Lundi', 1, 17, 'A2.1', 'Cours'),
(7, 2, 3, 3, 'Mercredi', 1, 17, 'A2.6', 'Cours'),
(7, 2, 3, 3, 'Jeudi', 5, 26, 'A2.5', 'TP'),
(7, 2, 3, 4, 'Lundi', 2, 26, 'A2.2', 'TD'),
(7, 2, 3, 4, 'Mercredi', 2, 26, 'A2.1', 'TP'),
(7, 2, 3, 4, 'Vendredi', 1, 17, 'A2.6', 'Cours'),
(7, 2, 3, 5, 'Lundi', 5, 38, 'A2.3', 'TP'),
(7, 2, 3, 5, 'Mercredi', 5, 38, 'A2.2', 'TD'),
(7, 2, 3, 5, 'Vendredi', 2, 38, 'A2.1', 'TD'),
(7, 2, 3, 6, 'Mardi', 1, 17, 'A2.4', 'Cours'),
(7, 2, 3, 6, 'Jeudi', 1, 17, 'A2.3', 'Cours'),
(7, 2, 3, 6, 'Samedi', 1, 25, 'A2.2', 'Cours'),
(7, 2, 3, 7, 'Mardi', 2, 25, 'A2.5', 'TD'),
(7, 2, 3, 7, 'Jeudi', 2, 25, 'A2.4', 'TD'),
(7, 2, 3, 7, 'Samedi', 2, 26, 'A2.3', 'TP'),
(8, 2, 4, 1, 'Mercredi', 1, 18, 'B2.6', 'Cours'),
(8, 2, 4, 1, 'Vendredi', 2, 39, 'B2.1', 'TD'),
(8, 2, 4, 2, 'Mercredi', 2, 39, 'B2.1', 'TP'),
(8, 2, 4, 2, 'Samedi', 1, 40, 'B2.2', 'Cours'),
(8, 2, 4, 8, 'Lundi', 1, 18, 'B2.1', 'Cours'),
(8, 2, 4, 8, 'Mercredi', 5, 40, 'B2.2', 'TD'),
(8, 2, 4, 8, 'Samedi', 2, 18, 'B2.3', 'TP'),
(8, 2, 4, 9, 'Lundi', 2, 40, 'B2.2', 'TD'),
(8, 2, 4, 9, 'Jeudi', 1, 18, 'B2.3', 'Cours'),
(8, 2, 4, 10, 'Lundi', 5, 18, 'B2.3', 'TP'),
(8, 2, 4, 10, 'Jeudi', 2, 39, 'B2.4', 'TD'),
(8, 2, 4, 11, 'Mardi', 1, 39, 'B2.4', 'Cours'),
(8, 2, 4, 11, 'Jeudi', 5, 40, 'B2.5', 'TP'),
(8, 2, 4, 12, 'Mardi', 2, 40, 'B2.5', 'TD'),
(8, 2, 4, 12, 'Vendredi', 1, 18, 'B2.6', 'Cours'),
(9, 2, 4, 1, 'Mercredi', 1, 19, 'A2.6', 'Cours'),
(9, 2, 4, 1, 'Vendredi', 2, 41, 'A2.1', 'TD'),
(9, 2, 4, 2, 'Mercredi', 2, 41, 'A2.1', 'TP'),
(9, 2, 4, 2, 'Samedi', 1, 42, 'A2.2', 'Cours'),
(9, 2, 4, 8, 'Lundi', 1, 19, 'A2.1', 'Cours'),
(9, 2, 4, 8, 'Mercredi', 5, 42, 'A2.2', 'TD'),
(9, 2, 4, 8, 'Samedi', 2, 19, 'A2.3', 'TP'),
(9, 2, 4, 9, 'Lundi', 2, 42, 'A2.2', 'TD'),
(9, 2, 4, 9, 'Jeudi', 1, 19, 'A2.3', 'Cours'),
(9, 2, 4, 10, 'Lundi', 5, 19, 'A2.3', 'TP'),
(9, 2, 4, 10, 'Jeudi', 2, 41, 'A2.4', 'TD'),
(9, 2, 4, 11, 'Mardi', 1, 41, 'A2.4', 'Cours'),
(9, 2, 4, 11, 'Jeudi', 5, 42, 'A2.5', 'TP'),
(9, 2, 4, 12, 'Mardi', 2, 42, 'A2.5', 'TD'),
(9, 2, 4, 12, 'Vendredi', 1, 19, 'A2.6', 'Cours'),
(10, 3, 2, 1, 'Mercredi', 1, 20, 'B3.6', 'Cours'),
(10, 3, 2, 1, 'Vendredi', 2, 43, 'B3.1', 'TD'),
(10, 3, 2, 2, 'Mercredi', 2, 43, 'B3.1', 'TP'),
(10, 3, 2, 2, 'Samedi', 1, 44, 'B3.2', 'Cours'),
(10, 3, 2, 8, 'Lundi', 1, 20, 'B3.1', 'Cours'),
(10, 3, 2, 8, 'Mercredi', 5, 44, 'B3.2', 'TD'),
(10, 3, 2, 8, 'Samedi', 2, 20, 'B3.3', 'TP'),
(10, 3, 2, 9, 'Lundi', 2, 44, 'B3.2', 'TD'),
(10, 3, 2, 9, 'Jeudi', 1, 20, 'B3.3', 'Cours'),
(10, 3, 2, 10, 'Lundi', 5, 20, 'B3.3', 'TP'),
(10, 3, 2, 10, 'Jeudi', 2, 43, 'B3.4', 'TD'),
(10, 3, 2, 11, 'Mardi', 1, 43, 'B3.4', 'Cours'),
(10, 3, 2, 11, 'Jeudi', 5, 44, 'B3.5', 'TP'),
(10, 3, 2, 12, 'Mardi', 2, 44, 'B3.5', 'TD'),
(10, 3, 2, 12, 'Vendredi', 1, 20, 'B3.6', 'Cours'),
(11, 3, 3, 1, 'Mercredi', 1, 21, 'A3.6', 'Cours'),
(11, 3, 3, 1, 'Vendredi', 2, 45, 'A3.1', 'TD'),
(11, 3, 3, 2, 'Mercredi', 2, 45, 'A3.1', 'TP'),
(11, 3, 3, 2, 'Samedi', 1, 46, 'A3.2', 'Cours'),
(11, 3, 3, 8, 'Lundi', 1, 21, 'A3.1', 'Cours'),
(11, 3, 3, 8, 'Mercredi', 5, 46, 'A3.2', 'TD'),
(11, 3, 3, 8, 'Samedi', 2, 21, 'A3.3', 'TP'),
(11, 3, 3, 9, 'Lundi', 2, 46, 'A3.2', 'TD'),
(11, 3, 3, 9, 'Jeudi', 1, 21, 'A3.3', 'Cours'),
(11, 3, 3, 10, 'Lundi', 5, 21, 'A3.3', 'TP'),
(11, 3, 3, 10, 'Jeudi', 2, 45, 'A3.4', 'TD'),
(11, 3, 3, 11, 'Mardi', 1, 45, 'A3.4', 'Cours'),
(11, 3, 3, 11, 'Jeudi', 5, 46, 'A3.5', 'TP'),
(11, 3, 3, 12, 'Mardi', 2, 46, 'A3.5', 'TD'),
(11, 3, 3, 12, 'Vendredi', 1, 21, 'A3.6', 'Cours'),
(12, 3, 4, 1, 'Mercredi', 1, 22, 'B3.6', 'Cours'),
(12, 3, 4, 1, 'Vendredi', 2, 47, 'B3.1', 'TD'),
(12, 3, 4, 2, 'Mercredi', 2, 47, 'B3.1', 'TP'),
(12, 3, 4, 2, 'Samedi', 1, 48, 'B3.2', 'Cours'),
(12, 3, 4, 8, 'Lundi', 1, 22, 'B3.1', 'Cours'),
(12, 3, 4, 8, 'Mercredi', 5, 48, 'B3.2', 'TD'),
(12, 3, 4, 8, 'Samedi', 2, 22, 'B3.3', 'TP'),
(12, 3, 4, 9, 'Lundi', 2, 48, 'B3.2', 'TD'),
(12, 3, 4, 9, 'Jeudi', 1, 22, 'B3.3', 'Cours'),
(12, 3, 4, 10, 'Lundi', 5, 22, 'B3.3', 'TP'),
(12, 3, 4, 10, 'Jeudi', 2, 47, 'B3.4', 'TD'),
(12, 3, 4, 11, 'Mardi', 1, 47, 'B3.4', 'Cours'),
(12, 3, 4, 11, 'Jeudi', 5, 48, 'B3.5', 'TP'),
(12, 3, 4, 12, 'Mardi', 2, 48, 'B3.5', 'TD'),
(12, 3, 4, 12, 'Vendredi', 1, 22, 'B3.6', 'Cours');

-- --------------------------------------------------------

--
-- Table structure for table `filieres`
--

CREATE TABLE `filieres` (
  `Id` int(11) NOT NULL,
  `Libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filieres`
--

INSERT INTO `filieres` (`Id`, `Libelle`) VALUES
(1, 'Business Computing'),
(2, 'E-Business'),
(3, 'Business Information System'),
(4, 'Business Intelligence');

-- --------------------------------------------------------

--
-- Table structure for table `filiere_matiere`
--

CREATE TABLE `filiere_matiere` (
  `FiliereId` int(11) NOT NULL,
  `MatiereId` int(11) NOT NULL,
  `ModuleId` int(11) NOT NULL,
  `SemestreId` int(11) NOT NULL,
  `NiveauId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filiere_module`
--

CREATE TABLE `filiere_module` (
  `FiliereId` int(11) NOT NULL,
  `ModuleId` int(11) NOT NULL,
  `SemestreId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filiere_module`
--

INSERT INTO `filiere_module` (`FiliereId`, `ModuleId`, `SemestreId`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 1),
(2, 7, 2),
(2, 8, 2),
(2, 9, 2),
(2, 10, 2),
(2, 12, 2),
(2, 13, 3),
(2, 14, 3),
(2, 15, 3),
(2, 16, 3),
(2, 17, 3),
(2, 19, 4),
(2, 20, 4),
(2, 21, 4),
(2, 22, 4),
(2, 23, 4),
(2, 25, 5),
(2, 26, 5),
(2, 27, 5),
(2, 28, 5),
(2, 66, 5),
(2, 77, 2),
(2, 100, 3),
(2, 101, 4),
(2, 121, 5),
(2, 123, 6),
(3, 7, 2),
(3, 8, 2),
(3, 9, 2),
(3, 10, 2),
(3, 12, 2),
(3, 13, 3),
(3, 14, 3),
(3, 15, 3),
(3, 16, 3),
(3, 17, 3),
(3, 23, 4),
(3, 37, 4),
(3, 38, 4),
(3, 39, 4),
(3, 40, 4),
(3, 42, 5),
(3, 43, 5),
(3, 44, 5),
(3, 45, 5),
(3, 76, 5),
(3, 77, 2),
(3, 100, 3),
(3, 101, 4),
(3, 121, 5),
(3, 123, 6),
(4, 7, 2),
(4, 8, 2),
(4, 9, 2),
(4, 10, 2),
(4, 12, 2),
(4, 13, 3),
(4, 14, 3),
(4, 15, 3),
(4, 16, 3),
(4, 17, 3),
(4, 23, 4),
(4, 48, 4),
(4, 49, 4),
(4, 50, 4),
(4, 51, 5),
(4, 52, 5),
(4, 53, 5),
(4, 58, 4),
(4, 66, 5),
(4, 71, 5),
(4, 77, 2),
(4, 100, 3),
(4, 101, 4),
(4, 121, 5),
(4, 123, 6);

-- --------------------------------------------------------

--
-- Table structure for table `groupes`
--

CREATE TABLE `groupes` (
  `Id` int(11) NOT NULL,
  `FiliereId` int(11) NOT NULL,
  `nivId` int(11) NOT NULL,
  `Libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groupes`
--

INSERT INTO `groupes` (`Id`, `FiliereId`, `nivId`, `Libelle`) VALUES
(1, 1, 1, 'Groupe 1'),
(2, 1, 1, 'Groupe 2'),
(3, 1, 1, 'Groupe 3'),
(4, 2, 2, 'Groupe 1'),
(5, 2, 2, 'Groupe 2'),
(6, 3, 2, 'Groupe 1'),
(7, 3, 2, 'Groupe 2'),
(8, 4, 2, 'Groupe 1'),
(9, 4, 2, 'Groupe 2'),
(10, 2, 3, 'Groupe 1'),
(11, 3, 3, 'Groupe 1'),
(12, 4, 3, 'Groupe 1');

-- --------------------------------------------------------

--
-- Table structure for table `horaires`
--

CREATE TABLE `horaires` (
  `Id` int(11) NOT NULL,
  `HeureDebut` time NOT NULL,
  `HeureFin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `horaires`
--

INSERT INTO `horaires` (`Id`, `HeureDebut`, `HeureFin`) VALUES
(1, '08:30:00', '10:00:00'),
(2, '10:05:00', '11:35:00'),
(3, '11:40:00', '13:10:00'),
(4, '13:10:00', '14:00:00'),
(5, '14:00:00', '15:30:00'),
(6, '15:35:00', '17:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `joined_channels`
--

CREATE TABLE `joined_channels` (
  `channelId` int(11) NOT NULL,
  `utilisateurId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `joined_channels`
--

INSERT INTO `joined_channels` (`channelId`, `utilisateurId`) VALUES
(7, 9);

-- --------------------------------------------------------

--
-- Table structure for table `matieres`
--

CREATE TABLE `matieres` (
  `Id` int(11) NOT NULL,
  `Libelle` varchar(100) NOT NULL,
  `Coefficient` decimal(4,2) NOT NULL DEFAULT 1.00,
  `ModuleId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matieres`
--

INSERT INTO `matieres` (`Id`, `Libelle`, `Coefficient`, `ModuleId`) VALUES
(1, 'ASD1', 3.00, 1),
(2, 'Fondaments des Systémes d\'Exploitation', 1.00, 2),
(3, 'Systémes Logiques', 1.00, 2),
(4, 'Analyse', 1.00, 3),
(5, 'Statistiques et Probabilité', 1.00, 3),
(6, 'Principes de Gestion', 1.00, 6),
(7, 'Comptabilité Générale', 1.00, 6),
(8, 'Compétences Numériques', 1.00, 4),
(9, 'Business Communication 1', 1.00, 4),
(10, 'Introduction au Commerce Electronique', 1.00, 4),
(11, 'Architecture des Ordinateurs', 1.00, 5),
(12, 'Calcul Actuariel', 1.00, 5),
(13, 'Projet Fin d\'Etudes', 15.00, 123);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `Id` int(11) NOT NULL,
  `ChannelId` int(11) NOT NULL,
  `Contenu` text NOT NULL,
  `DateEnvoi` datetime NOT NULL,
  `UserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`Id`, `ChannelId`, `Contenu`, `DateEnvoi`, `UserId`) VALUES
(21, 3, 'tes', '2026-04-19 12:31:19', 8),
(22, 3, 'test', '2026-04-19 12:32:23', 9),
(23, 3, 'abcdefg', '2026-04-19 12:32:31', 9),
(24, 3, '156165165', '2026-04-19 12:55:50', 8),
(25, 3, '79846', '2026-04-19 12:55:53', 8),
(26, 3, '888', '2026-04-19 12:56:20', 8),
(27, 7, 'Message Test', '2026-04-19 21:28:35', 9),
(28, 7, 'Test2', '2026-04-19 21:28:39', 9),
(29, 7, 'testtt', '2026-04-19 21:32:08', 8);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `Id` int(11) NOT NULL,
  `SemestreId` int(11) NOT NULL,
  `Libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`Id`, `SemestreId`, `Libelle`) VALUES
(1, 1, 'Algorithmique et structures de données 1'),
(2, 1, 'Systèmes 1'),
(3, 1, 'Mathématique 1'),
(4, 1, 'Soft skills et culture 1'),
(5, 1, 'Unités optionnelles'),
(6, 1, 'Gestion 1'),
(7, 2, 'Algorithmique et structures de données 2'),
(8, 2, 'Systèmes 2'),
(9, 2, 'Mathématique 2'),
(10, 2, 'Soft skills et culture 2'),
(12, 2, 'Gestion 2'),
(13, 3, 'Programmation avancée 1'),
(14, 3, 'Conception et base de données'),
(15, 3, 'Stat et ia'),
(16, 3, 'Digital et business'),
(17, 3, 'Soft skills et culture 3'),
(19, 4, 'Sgbd et programmation'),
(20, 4, 'Tableau de bord et ro'),
(21, 4, 'Systèmes d\'information'),
(22, 4, 'Data warehouse et crm'),
(23, 4, 'Soft skills et culture 4'),
(25, 5, 'Développement web et mobile'),
(26, 5, 'Informatique décisionnelle'),
(27, 5, 'It security et gestion de projets'),
(28, 5, 'Big data et cloud'),
(31, 6, 'Projet de fin d\'études'),
(37, 4, 'Processus des si'),
(38, 4, 'Développement informatique i'),
(39, 4, 'Sgbd et administration des bds'),
(40, 4, 'Management des si'),
(41, 4, 'Soft skills et culture 5'),
(42, 5, 'Systèmes intégrés'),
(43, 5, 'Génie logiciel et gestion de projets'),
(44, 5, 'Développement informatique ii'),
(45, 5, 'Si décisionnel'),
(47, 4, 'Analyse de données et programmation avancé 1'),
(48, 4, 'Théorie des graphes et recherche opérationnelle'),
(49, 4, 'Ingénierie des logiciels'),
(50, 4, 'Base de données'),
(51, 5, 'Sciences de la décision'),
(52, 5, 'Environnements évolués'),
(53, 5, 'Technologie de l\'information'),
(54, 5, 'Analyse des données et programmation avancée 2'),
(58, 4, 'Analyse de données et programmation avancée 1'),
(66, 5, 'Soft skills et culture 5'),
(71, 5, 'Analyse des données et programmation avancées 2'),
(76, 5, 'Soft skills et langue 5'),
(77, 2, 'Unités optionnelles'),
(100, 3, 'Unités optionnelles'),
(101, 4, 'Unités optionnelles'),
(121, 5, 'Unités optionnelles'),
(123, 6, 'Projet Fin d\'Etudes');

-- --------------------------------------------------------

--
-- Table structure for table `niveaux`
--

CREATE TABLE `niveaux` (
  `Id` int(11) NOT NULL,
  `Libelle` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `niveaux`
--

INSERT INTO `niveaux` (`Id`, `Libelle`) VALUES
(1, 'License 1'),
(2, 'License 2'),
(3, 'License 3'),
(4, 'Master 1'),
(5, 'Master 2');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `UserId` int(11) NOT NULL,
  `MatiereId` int(11) NOT NULL,
  `Valeur` decimal(10,2) NOT NULL
) ;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`UserId`, `MatiereId`, `Valeur`) VALUES
(8, 3, 3.00),
(9, 3, 18.00),
(9, 4, 3.00),
(9, 5, 8.00),
(9, 6, 5.00),
(9, 7, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `semestre`
--

CREATE TABLE `semestre` (
  `Id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semestre`
--

INSERT INTO `semestre` (`Id`) VALUES
(1),
(2),
(3),
(4),
(5),
(6);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `Id` int(11) NOT NULL,
  `Prenom` varchar(50) NOT NULL,
  `Nom` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Etudiant','Enseignant','Administration') NOT NULL,
  `GroupeId` int(11) NOT NULL,
  `dateInscription` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`Id`, `Prenom`, `Nom`, `Email`, `Password`, `Role`, `GroupeId`, `dateInscription`) VALUES
(8, 'Ahmed', 'Jerbi', 'ahmedjerbi@gmail.com', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 7, '2026-04-16 07:23:18'),
(9, 'test2', 'test3', 'test@gmail.com', '$2y$10$Wlqhxpxyr58U/5adWEKPfOJ.tMdZO/6eI17k2eZ.m/zmNE2I4k/0e', 'Etudiant', 7, '2026-04-19 13:31:59'),
(10, 'admin', 'admin', 'admin@gmail.com', '$2a$12$mMzLvq.mIGifzjD6r36UEOYQMHGO3XTtD5y9p/xXF3z86mMBUD54e', 'Administration', 1, '2026-04-19 20:52:25'),
(11, 'Ahmed', 'Ben Ali', 'ahmed.benali@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 1, '2026-04-19 00:00:00'),
(12, 'Mohamed', 'Trabelsi', 'mohamed.trabelsi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 2, '2026-04-19 00:00:00'),
(13, 'Fatma', 'Khelifi', 'fatma.khelifi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 3, '2026-04-19 00:00:00'),
(14, 'Ali', 'Mansouri', 'ali.mansouri@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 4, '2026-04-19 00:00:00'),
(15, 'Amina', 'Gharbi', 'amina.gharbi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 5, '2026-04-19 00:00:00'),
(16, 'Youssef', 'Bouazizi', 'youssef.bouazizi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 6, '2026-04-19 00:00:00'),
(17, 'Leila', 'Hamdi', 'leila.hamdi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 7, '2026-04-19 00:00:00'),
(18, 'Karim', 'Jaziri', 'karim.jaziri@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 8, '2026-04-19 00:00:00'),
(19, 'Nour', 'Chahed', 'nour.chahed@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 9, '2026-04-19 00:00:00'),
(20, 'Sami', 'Belhaj', 'sami.belhaj@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 10, '2026-04-19 00:00:00'),
(21, 'Rania', 'Mejri', 'rania.mejri@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 11, '2026-04-19 00:00:00'),
(22, 'Tarek', 'Zouari', 'tarek.zouari@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 12, '2026-04-19 00:00:00'),
(23, 'Hichem', 'Saidi', 'hichem.saidi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 6, '2026-04-19 00:00:00'),
(24, 'Ines', 'Bouhlel', 'ines.bouhlel@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 6, '2026-04-19 00:00:00'),
(25, 'Jamel', 'Khiari', 'jamel.khiari@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 7, '2026-04-19 00:00:00'),
(26, 'Kamel', 'Ferchichi', 'kamel.ferchichi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 7, '2026-04-19 00:00:00'),
(27, 'Lotfi', 'Ben Salah', 'lotfi.bensalah@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 1, '2026-04-19 00:00:00'),
(28, 'Mariem', 'Tlili', 'mariem.tlili@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 1, '2026-04-19 00:00:00'),
(29, 'Nabil', 'Ghorbel', 'nabil.ghorbel@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 2, '2026-04-19 00:00:00'),
(30, 'Sana', 'Belkhir', 'sana.belkhir@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 2, '2026-04-19 00:00:00'),
(31, 'Riadh', 'Bouallegui', 'riadh.bouallegui@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 3, '2026-04-19 00:00:00'),
(32, 'Wafa', 'Khiari', 'wafa.khiari@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 3, '2026-04-19 00:00:00'),
(33, 'Zied', 'Mansouri', 'zied.mansouri@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 4, '2026-04-19 00:00:00'),
(34, 'Hela', 'Trabelsi', 'hela.trabelsi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 4, '2026-04-19 00:00:00'),
(35, 'Anis', 'Khelifi', 'anis.khelifi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 5, '2026-04-19 00:00:00'),
(36, 'Lina', 'Gharbi', 'lina.gharbi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 5, '2026-04-19 00:00:00'),
(37, 'Mohamed', 'Ali', 'mohamed.ali@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 6, '2026-04-19 00:00:00'),
(38, 'Fatma', 'Ben Amor', 'fatma.benamor@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 7, '2026-04-19 00:00:00'),
(39, 'Sami', 'Bouazizi', 'sami.bouazizi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 8, '2026-04-19 00:00:00'),
(40, 'Leila', 'Mansouri', 'leila.mansouri@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 8, '2026-04-19 00:00:00'),
(41, 'Tarek', 'Belhaj', 'tarek.belhaj@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 9, '2026-04-19 00:00:00'),
(42, 'Ines', 'Hamdi', 'ines.hamdi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 9, '2026-04-19 00:00:00'),
(43, 'Ali', 'Khelifi', 'ali.khelifi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 10, '2026-04-19 00:00:00'),
(44, 'Amina', 'Trabelsi', 'amina.trabelsi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 10, '2026-04-19 00:00:00'),
(45, 'Youssef', 'Gharbi', 'youssef.gharbi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 11, '2026-04-19 00:00:00'),
(46, 'Fatma', 'Zouari', 'fatma.zouari@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 11, '2026-04-19 00:00:00'),
(47, 'Karim', 'Mansouri', 'karim.mansouri@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 12, '2026-04-19 00:00:00'),
(48, 'Sana', 'Hamdi', 'sana.hamdi@esen.tn', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Enseignant', 12, '2026-04-19 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `UserId` (`UserId`,`EnsId`,`FiliereId`,`NiveauId`,`GroupeId`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `EnsId` (`EnsId`),
  ADD KEY `FiliereId` (`FiliereId`),
  ADD KEY `NiveauId` (`NiveauId`,`GroupeId`);

--
-- Indexes for table `devoirs`
--
ALTER TABLE `devoirs`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FiliereId` (`FiliereId`,`NiveauId`,`GroupeId`),
  ADD KEY `NiveauId` (`NiveauId`),
  ADD KEY `GroupeId` (`GroupeId`);

--
-- Indexes for table `emplois`
--
ALTER TABLE `emplois`
  ADD PRIMARY KEY (`GroupeId`,`NiveauId`,`FiliereId`,`MatiereId`,`Jour`,`HoraireId`),
  ADD KEY `ClassId` (`GroupeId`),
  ADD KEY `EnsId` (`EnsId`),
  ADD KEY `GroupId` (`GroupeId`,`NiveauId`,`FiliereId`,`MatiereId`),
  ADD KEY `HoraireId` (`HoraireId`),
  ADD KEY `MatiereId` (`MatiereId`),
  ADD KEY `NiveauId` (`NiveauId`),
  ADD KEY `FiliereId` (`FiliereId`);

--
-- Indexes for table `filieres`
--
ALTER TABLE `filieres`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `filiere_matiere`
--
ALTER TABLE `filiere_matiere`
  ADD PRIMARY KEY (`FiliereId`,`MatiereId`,`ModuleId`,`SemestreId`,`NiveauId`),
  ADD KEY `FiliereId` (`FiliereId`,`MatiereId`,`ModuleId`,`SemestreId`,`NiveauId`),
  ADD KEY `SemestreId` (`SemestreId`),
  ADD KEY `MatiereId` (`MatiereId`),
  ADD KEY `ModuleId` (`ModuleId`),
  ADD KEY `NiveauId` (`NiveauId`);

--
-- Indexes for table `filiere_module`
--
ALTER TABLE `filiere_module`
  ADD PRIMARY KEY (`FiliereId`,`ModuleId`,`SemestreId`),
  ADD KEY `FiliereId` (`FiliereId`,`ModuleId`),
  ADD KEY `ModuleId` (`ModuleId`),
  ADD KEY `SemestreId` (`SemestreId`);

--
-- Indexes for table `groupes`
--
ALTER TABLE `groupes`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FiliereId` (`FiliereId`),
  ADD KEY `nivId` (`nivId`);

--
-- Indexes for table `horaires`
--
ALTER TABLE `horaires`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `joined_channels`
--
ALTER TABLE `joined_channels`
  ADD PRIMARY KEY (`channelId`,`utilisateurId`),
  ADD KEY `channelId` (`channelId`,`utilisateurId`),
  ADD KEY `utilisateurId` (`utilisateurId`);

--
-- Indexes for table `matieres`
--
ALTER TABLE `matieres`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ModuleId` (`ModuleId`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `UserId` (`UserId`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FiliereId` (`SemestreId`);

--
-- Indexes for table `niveaux`
--
ALTER TABLE `niveaux`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`UserId`,`MatiereId`),
  ADD KEY `UserId` (`UserId`,`MatiereId`),
  ADD KEY `MatiereId` (`MatiereId`);

--
-- Indexes for table `semestre`
--
ALTER TABLE `semestre`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ClassId` (`GroupeId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `devoirs`
--
ALTER TABLE `devoirs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `filieres`
--
ALTER TABLE `filieres`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `groupes`
--
ALTER TABLE `groupes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `horaires`
--
ALTER TABLE `horaires`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `matieres`
--
ALTER TABLE `matieres`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `niveaux`
--
ALTER TABLE `niveaux`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `semestre`
--
ALTER TABLE `semestre`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `channels`
--
ALTER TABLE `channels`
  ADD CONSTRAINT `channels_ibfk_1` FOREIGN KEY (`EnsId`) REFERENCES `utilisateurs` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `channels_ibfk_2` FOREIGN KEY (`FiliereId`) REFERENCES `filieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `devoirs`
--
ALTER TABLE `devoirs`
  ADD CONSTRAINT `devoirs_ibfk_1` FOREIGN KEY (`NiveauId`) REFERENCES `niveaux` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `devoirs_ibfk_2` FOREIGN KEY (`GroupeId`) REFERENCES `groupes` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `devoirs_ibfk_3` FOREIGN KEY (`FiliereId`) REFERENCES `filieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `emplois`
--
ALTER TABLE `emplois`
  ADD CONSTRAINT `emplois_ibfk_1` FOREIGN KEY (`EnsId`) REFERENCES `utilisateurs` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emplois_ibfk_2` FOREIGN KEY (`GroupeId`) REFERENCES `groupes` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emplois_ibfk_3` FOREIGN KEY (`MatiereId`) REFERENCES `matieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emplois_ibfk_4` FOREIGN KEY (`NiveauId`) REFERENCES `niveaux` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emplois_ibfk_5` FOREIGN KEY (`FiliereId`) REFERENCES `filieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emplois_ibfk_6` FOREIGN KEY (`HoraireId`) REFERENCES `horaires` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `filiere_matiere`
--
ALTER TABLE `filiere_matiere`
  ADD CONSTRAINT `filiere_matiere_ibfk_1` FOREIGN KEY (`FiliereId`) REFERENCES `filieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filiere_matiere_ibfk_2` FOREIGN KEY (`SemestreId`) REFERENCES `semestre` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filiere_matiere_ibfk_3` FOREIGN KEY (`MatiereId`) REFERENCES `matieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filiere_matiere_ibfk_4` FOREIGN KEY (`ModuleId`) REFERENCES `modules` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filiere_matiere_ibfk_5` FOREIGN KEY (`NiveauId`) REFERENCES `niveaux` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `filiere_module`
--
ALTER TABLE `filiere_module`
  ADD CONSTRAINT `filiere_module_ibfk_1` FOREIGN KEY (`ModuleId`) REFERENCES `modules` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filiere_module_ibfk_2` FOREIGN KEY (`FiliereId`) REFERENCES `filieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filiere_module_ibfk_3` FOREIGN KEY (`SemestreId`) REFERENCES `semestre` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `groupes`
--
ALTER TABLE `groupes`
  ADD CONSTRAINT `groupes_ibfk_1` FOREIGN KEY (`FiliereId`) REFERENCES `filieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `groupes_ibfk_2` FOREIGN KEY (`nivId`) REFERENCES `niveaux` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `joined_channels`
--
ALTER TABLE `joined_channels`
  ADD CONSTRAINT `joined_channels_ibfk_1` FOREIGN KEY (`channelId`) REFERENCES `channels` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `joined_channels_ibfk_2` FOREIGN KEY (`utilisateurId`) REFERENCES `utilisateurs` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `matieres`
--
ALTER TABLE `matieres`
  ADD CONSTRAINT `matieres_ibfk_2` FOREIGN KEY (`ModuleId`) REFERENCES `modules` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `utilisateurs` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_2` FOREIGN KEY (`SemestreId`) REFERENCES `semestre` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `utilisateurs` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`MatiereId`) REFERENCES `matieres` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`GroupeId`) REFERENCES `groupes` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
