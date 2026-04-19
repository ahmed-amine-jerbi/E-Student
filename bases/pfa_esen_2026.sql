-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 19, 2026 at 08:28 AM
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
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `Id` int(11) NOT NULL,
  `EnsId` int(11) NOT NULL,
  `FiliereId` int(11) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Cle` varchar(50) NOT NULL,
  `Libelle` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devoirs`
--

CREATE TABLE `devoirs` (
  `Id` int(11) NOT NULL,
  `FiliereId` int(11) NOT NULL,
  `NiveauId` int(11) NOT NULL,
  `GroupeId` int(11) NOT NULL,
  `Titre` varchar(50) NOT NULL,
  `Deadline` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emplois`
--

CREATE TABLE `emplois` (
  `Id` int(11) NOT NULL,
  `ClassId` int(11) NOT NULL,
  `Jour` enum('Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
  `HoraireId` int(11) NOT NULL,
  `EnsId` int(11) NOT NULL,
  `Salle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `joined_channels`
--

CREATE TABLE `joined_channels` (
  `channelId` int(11) NOT NULL,
  `utilisateurId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `Contenu` text NOT NULL,
  `DateEnvoi` datetime NOT NULL,
  `UserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(8, 'Ahmed', 'Jerbi', 'ahmedjerbi@gmail.com', '$2y$10$Jd5jpIGHoZaBeT89ZBUU/.T3itApHPvM5fs.tVymW4Q2ctzCLOAiK', 'Administration', 7, '2026-04-16 07:23:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `EnsId` (`EnsId`),
  ADD KEY `FiliereId` (`FiliereId`);

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
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ClassId` (`ClassId`),
  ADD KEY `EnsId` (`EnsId`);

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
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devoirs`
--
ALTER TABLE `devoirs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emplois`
--
ALTER TABLE `emplois`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filieres`
--
ALTER TABLE `filieres`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `groupes`
--
ALTER TABLE `groupes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `horaires`
--
ALTER TABLE `horaires`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matieres`
--
ALTER TABLE `matieres`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  ADD CONSTRAINT `emplois_ibfk_2` FOREIGN KEY (`ClassId`) REFERENCES `groupes` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`GroupeId`) REFERENCES `groupes` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
