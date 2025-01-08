-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 04 jan. 2025 à 00:55
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `locationvoiture`
--

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `idNotification` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `message` text NOT NULL,
  `dateEnvoi` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notification`
--

INSERT INTO `notification` (`idNotification`, `clientId`, `message`, `dateEnvoi`) VALUES
(1, 2, 'Votre réservation est confirmée', '2024-12-28'),
(2, 2, 'Votre réservation est en attente', '2024-12-28');

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `idReservation` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `vehiculeId` int(11) NOT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date NOT NULL,
  `montantTotal` float NOT NULL,
  `statut` enum('Confirmée','Annulée','En attente') DEFAULT 'En attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`idReservation`, `clientId`, `vehiculeId`, `dateDebut`, `dateFin`, `montantTotal`, `statut`) VALUES
(1, 2, 1, '2024-12-29', '2024-12-31', 150, 'En attente'),
(2, 2, 2, '2024-12-30', '2024-12-31', 180, 'Confirmée');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `motDePasse` varchar(255) NOT NULL,
  `role` enum('Admin','Client') NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `cin` varchar(20) NOT NULL,
  `numeroPermis` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `motDePasse`, `role`, `telephone`, `cin`, `numeroPermis`) VALUES
(1, 'admin1@example.com', 'adminpassword1', 'Admin', '0601020304', 'CIN12345', 'P12345'),
(2, 'client1@example.com', 'clientpassword1', 'Client', '0612345678', 'CIN67890', 'P67890'),
(5, 'alaeddine@gmail.com', 'alae2006', 'Client', '0637309887', 'CIN294877', 'P847468'),
(7, 'ichrak@gmail.com', 'ichrak2006', 'Client', '0637309874', 'CIN294876', 'P847468'),
(8, 'alae@gmail.com', 'alae2006', 'Client', '0637309816', 'CIN294875', 'P847434'),
(9, 'elouaddah.a829@ucd.ac.ma', 'alae', 'Client', '222', 'cin123', 'p123'),
(10, 'madjid27@gmail.com', 'aaa', 'Client', '1234', 'aaa', '1eohie'),
(14, 'elouaddah.a829@ucd.ac.m', 'aaa', 'Client', '1234', 'cin12344', 'p123'),
(15, 'hhhh@gmail.com', '$2y$10$ElEs.66GDhXtJ6sEUgnHnuWQ.cWbvmXApyZGMB8xCmz9/Af1AIKWG', 'Client', '05040504503', 'cc123', 'p1234jj'),
(16, 'elouaddah.a829@ucd.ac', '$2y$10$vh.xbtusIa9adfGPAbjZQea8llauKlPhlgWY2yzprwXoT43yFe3rC', 'Client', '0657569048', 'cin12343', 'p12332'),
(17, 'hhh@gmail.com', '$2y$10$sYqOEpDcwcjwLaQnYrVtOeIpZ2GVuPA6PuzNdnAjogFnF/FSDEXFS', 'Client', '06578468378', 'cin12356', 'p35467'),
(18, 'qqqq@gmail.com', '$2y$10$LWh/oJjYeXrka6j6yntRtOh1CeCeuOqKpT93HRJ99.TIGaxeHGKt2', 'Client', '0974', 'ccc09', '870093');

-- --------------------------------------------------------

--
-- Structure de la table `vehicule`
--

CREATE TABLE `vehicule` (
  `idVehicule` int(11) NOT NULL,
  `marque` varchar(100) NOT NULL,
  `modele` varchar(100) NOT NULL,
  `type` enum('Voiture','Moto','Camion') NOT NULL,
  `prixParJour` float NOT NULL,
  `disponible` enum('Disponible','Non Disponible') DEFAULT 'Disponible',
  `nombrePlaces` int(11) NOT NULL,
  `carburant` enum('Essence','Diesel','Electrique','Hybride') NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vehicule`
--

INSERT INTO `vehicule` (`idVehicule`, `marque`, `modele`, `type`, `prixParJour`, `disponible`, `nombrePlaces`, `carburant`, `image`) VALUES
(1, 'Toyota', '[jllj', 'Voiture', 50, 'Non Disponible', 5, 'Essence', NULL),
(2, 'Honda', 'Civic', 'Voiture', 60, '', 5, 'Diesel', NULL),
(3, 'dassia', '323', '', 21, '', 12, 'Essence', 'uploads/alae.5.jpg'),
(85, 'sss', '4545', 'Voiture', 4, 'Non Disponible', 5, 'Essence', '6777a55eb8518_Annotation 2025-01-01 205035.png'),
(88, 'hhhhh', '4545', 'Voiture', 1, 'Disponible', 3, 'Essence', '67785eee3c72c_Annotation 2025-01-01 205035.png'),
(90, 'hhhhh', '4545', 'Voiture', 1, 'Disponible', 1, 'Essence', '677865b8a72b5_Annotation 2025-01-01 205035.png'),
(91, 'hhhhh', 'akjbxkbs', 'Voiture', 1, 'Disponible', 4, 'Essence', '677873341b3e4_Annotation 2025-01-01 205035.png');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`idNotification`),
  ADD KEY `clientId` (`clientId`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`idReservation`),
  ADD KEY `clientId` (`clientId`),
  ADD KEY `vehiculeId` (`vehiculeId`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cin` (`cin`);

--
-- Index pour la table `vehicule`
--
ALTER TABLE `vehicule`
  ADD PRIMARY KEY (`idVehicule`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `idNotification` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`    
  MODIFY `idReservation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `vehicule`
--
ALTER TABLE `vehicule`
  MODIFY `idVehicule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`clientId`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`clientId`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`vehiculeId`) REFERENCES `vehicule` (`idVehicule`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
