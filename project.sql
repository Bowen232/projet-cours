-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 14, 2024 at 12:20 PM
-- Server version: 5.7.11
-- PHP Version: 5.6.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `entrainement`
--

CREATE TABLE `entrainement` (
  `id_entrainement` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `categorie` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `heure` time DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `chemin_image_title` varchar(255) DEFAULT NULL,
  `chemin_image` varchar(255) DEFAULT NULL,
  `participants_max` int(11) DEFAULT NULL,
  `cree_par` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `entrainement`
--

INSERT INTO `entrainement` (`id_entrainement`, `titre`, `description`, `categorie`, `date`, `heure`, `lieu`, `chemin_image_title`, `chemin_image`, `participants_max`, `cree_par`) VALUES
(45, 'Match de Football amical', 'Un match de football amical pour tous les niveaux. Rejoignez-nous pour passer un bon moment et faire de l’exercice en équipe.', 'Sport', '2024-12-01', '15:00:00', 'Stade Municipal, Paris', NULL, NULL, 22, 1),
(46, 'Cours de Yoga en Plein Air', 'Venez vous détendre et améliorer votre flexibilité avec notre séance de yoga en plein air. Apportez votre propre tapis.', 'Bien-être', '2024-11-20', '10:30:00', 'Parc des Buttes-Chaumont, Paris', NULL, NULL, 15, 1),
(47, 'Entraînement de Course à Pied pour Débutants', 'Une séance de course à pied destinée aux débutants. Nous apprendrons les techniques de base pour améliorer votre endurance.', 'Sport', '2024-11-25', '07:00:00', 'Bois de Boulogne, Paris', NULL, NULL, 20, 1),
(48, 'Entraînement de Basketball', 'Session d’entraînement de basketball pour joueurs de niveau intermédiaire. Venez perfectionner vos compétences en équipe.', 'Sport', '2024-12-05', '18:00:00', 'Gymnase de la Ville', NULL, NULL, 12, 1),
(49, 'Séance de Natation', 'Rejoignez-nous pour une séance de natation ouverte à tous les niveaux.', 'Sport', '2024-11-30', '09:00:00', 'Piscine Olympique, Lyon', NULL, NULL, 25, 1),
(50, 'Atelier de Danse Salsa', 'Apprenez les bases de la danse salsa avec notre instructeur professionnel.', 'Danse', '2024-12-10', '19:00:00', 'Studio de Danse Central', NULL, NULL, 30, 1),
(51, 'Randonnée en Montagne', 'Une randonnée guidée pour découvrir les beautés de la montagne.', 'Nature', '2024-12-15', '08:00:00', 'Point de Rencontre: Gare Routière', NULL, NULL, 50, 1),
(52, 'Cours de Cuisine Végétarienne', 'Découvrez de délicieuses recettes végétariennes lors de cet atelier pratique.', 'Cuisine', '2024-12-05', '14:00:00', 'Atelier Gourmand', NULL, NULL, 10, 1),
(53, 'Séance de Méditation Guidée', 'Détendez-vous et trouvez la paix intérieure avec notre séance de méditation guidée.', 'Bien-être', '2024-11-28', '17:30:00', 'Centre Zen', NULL, NULL, 20, 1),
(54, 'Atelier de Photographie Urbaine', 'Explorez la ville avec votre appareil photo et améliorez vos compétences en photographie urbaine.', 'Art', '2024-12-08', '10:00:00', 'Place de la République', NULL, NULL, 15, 1),
(55, 'Initiation à l\'Escalade', 'Venez découvrir l\'escalade en salle avec des moniteurs expérimentés.', 'Sport', '2024-12-12', '16:00:00', 'Salle d\'Escalade Bloc & Co', NULL, NULL, 12, 1),
(56, 'Atelier de Théâtre Improvisé', 'Libérez votre créativité et votre expression personnelle dans cet atelier de théâtre d\'improvisation.', 'Art', '2024-12-03', '18:30:00', 'Théâtre du Soleil', NULL, NULL, 20, 1),
(57, 'Café Linguistique Français-Anglais', 'Pratiquez votre français ou anglais dans une ambiance conviviale autour d\'un café.', 'Éducation', '2024-11-27', '19:00:00', 'Café des Langues', NULL, NULL, 25, 1),
(58, 'Atelier DIY : Fabriquer ses Cosmétiques Naturels', 'Apprenez à fabriquer vos propres cosmétiques naturels et respectueux de l\'environnement.', 'Bien-être', '2024-12-06', '14:00:00', 'Maison des Associations', NULL, NULL, 10, 1),
(59, 'Tournoi de Jeux de Société', 'Participez à notre tournoi de jeux de société et rencontrez d\'autres passionnés.', 'Divertissement', '2024-12-09', '13:00:00', 'Ludothèque Municipale', NULL, NULL, 30, 1),
(60, 'Séance de CrossFit', 'Un entraînement intensif pour améliorer votre force et votre endurance.', 'Sport', '2024-11-29', '18:00:00', 'Salle de Sport Fitness Plus', NULL, NULL, 15, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inscription`
--

CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL,
  `id_entrainement` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `inscription`
--

INSERT INTO `inscription` (`id_inscription`, `id_entrainement`, `id_user`) VALUES
(21, 47, 4);

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE `photo` (
  `id_photo` int(11) NOT NULL,
  `id_entrainement` int(11) DEFAULT NULL,
  `url_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `photo`
--



-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `type_user` enum('admin','membre') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `nom`, `email`, `mot_de_passe`, `type_user`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$xM7FRAG7la/kREVo7BDP1.Poza/cNpRUD7LuxehbOFWL1HykyC4o6', 'admin'),
(2, 'XU', '1234@qq.com', '$2y$10$nt5T9eyruCPfBtfBsuGBNet0t50mLXefQNsNPzWa6mC9Tv1Mqri3S', 'membre');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entrainement`
--
ALTER TABLE `entrainement`
  ADD PRIMARY KEY (`id_entrainement`),
  ADD KEY `cree_par` (`cree_par`);

--
-- Indexes for table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id_inscription`),
  ADD KEY `id_entrainement` (`id_entrainement`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `photo`
--
ALTER TABLE `photo`
  ADD PRIMARY KEY (`id_photo`),
  ADD KEY `id_entrainement` (`id_entrainement`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entrainement`
--
ALTER TABLE `entrainement`
  MODIFY `id_entrainement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;
--
-- AUTO_INCREMENT for table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `photo`
--
ALTER TABLE `photo`
  MODIFY `id_photo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;
--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `entrainement`
--
ALTER TABLE `entrainement`
  ADD CONSTRAINT `entrainement_ibfk_1` FOREIGN KEY (`cree_par`) REFERENCES `utilisateur` (`id_user`);

--
-- Constraints for table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`id_entrainement`) REFERENCES `entrainement` (`id_entrainement`),
  ADD CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Constraints for table `photo`
--
ALTER TABLE `photo`
  ADD CONSTRAINT `photo_ibfk_1` FOREIGN KEY (`id_entrainement`) REFERENCES `entrainement` (`id_entrainement`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
