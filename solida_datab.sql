-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 15 déc. 2025 à 06:27
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
-- Base de données : `solida_datab`
--

-- --------------------------------------------------------

--
-- Structure de la table `association`
--

CREATE TABLE `association` (
  `id_association` int(11) NOT NULL,
  `nom_association` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `pays` varchar(100) NOT NULL,
  `type_don_supporte` varchar(50) NOT NULL,
  `avis` text DEFAULT NULL COMMENT 'Liste des avis/commentaires au format JSON array',
  `avis_plateforme_global` text DEFAULT NULL COMMENT 'Liste des avis/commentaires globaux sur Solida (stocké dans id_association=1)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `association`
--

INSERT INTO `association` (`id_association`, `nom_association`, `description`, `adresse`, `pays`, `type_don_supporte`, `avis`, `avis_plateforme_global`) VALUES
(1, 'khaireddine bacha', 'b b b b b b b b b b b b b b b ', '18 rue tabibsouria ennaser 2', 'huhbsyuvb', 'azsxxaz', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `id_forum` int(11) NOT NULL,
  `id_auteur` int(11) DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_commentaire` datetime DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  `signale` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `deals`
--

CREATE TABLE `deals` (
  `idDeal` int(11) NOT NULL,
  `intitule` varchar(50) NOT NULL,
  `descriptionD` varchar(255) NOT NULL,
  `prixInitial` decimal(10,2) NOT NULL,
  `reduction` decimal(5,2) NOT NULL,
  `dateDebut` date NOT NULL,
  `periodeValidite` int(11) NOT NULL,
  `note` tinyint(4) DEFAULT NULL CHECK (`note` between 0 and 5),
  `expire` char(3) DEFAULT 'NON' CHECK (`expire` in ('OUI','NON')),
  `idSponsor` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'en_attente',
  `click_count` int(11) NOT NULL DEFAULT 0,
  `has_coupon` enum('OUI','NON') NOT NULL DEFAULT 'NON',
  `coupon_code` varchar(20) DEFAULT NULL,
  `dateAcceptation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `deals`
--

INSERT INTO `deals` (`idDeal`, `intitule`, `descriptionD`, `prixInitial`, `reduction`, `dateDebut`, `periodeValidite`, `note`, `expire`, `idSponsor`, `id_user`, `statut`, `click_count`, `has_coupon`, `coupon_code`, `dateAcceptation`) VALUES
(1, 'jkxsqnjqsjkxbqs', ' c z,c zkelckzc,zkcklzcnklzc', 1615.00, 55.00, '2025-12-18', 15, 5, 'OUI', 1, 1, 'en_attente', 0, 'NON', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `don`
--

CREATE TABLE `don` (
  `id_don` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_association` int(11) NOT NULL,
  `type_don` varchar(50) NOT NULL,
  `date_don` datetime DEFAULT current_timestamp(),
  `statut` varchar(50) DEFAULT 'En attente',
  `montant_donne` decimal(10,2) DEFAULT NULL,
  `methode_paiement` varchar(50) DEFAULT NULL,
  `numero_carte_token` varchar(255) DEFAULT NULL,
  `groupe_sanguin` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `a_maladie` tinyint(1) DEFAULT 0,
  `details_maladie` varchar(255) DEFAULT NULL,
  `details_nourriture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evenements`
--

CREATE TABLE `evenements` (
  `id_evenement` int(11) NOT NULL,
  `titre_evenement` varchar(150) NOT NULL,
  `date_evenement` date NOT NULL,
  `description` text DEFAULT NULL,
  `organisateur` varchar(100) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `max_participants` int(11) NOT NULL DEFAULT 50,
  `frais_participation` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `evenements`
--

INSERT INTO `evenements` (`id_evenement`, `titre_evenement`, `date_evenement`, `description`, `organisateur`, `adresse`, `latitude`, `longitude`, `max_participants`, `frais_participation`, `created_at`) VALUES
(1, 'klk xlk xjkaxa', '2025-12-18', 'v v v v v v v v  v v v v v v v v', 'dezdzedzzd', 'Mornaguia Nord, Délégation Mornaguia, Gouvernorat La Manouba, 1110, Tunisie', 36.74838924, 10.02109196, 50, 121.00, '2025-12-15 04:37:41');

-- --------------------------------------------------------

--
-- Structure de la table `forum`
--

CREATE TABLE `forum` (
  `id_forum` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `categorie` varchar(100) NOT NULL,
  `discussion_g` text NOT NULL,
  `discussion_p` text NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `forum`
--

INSERT INTO `forum` (`id_forum`, `id_user`, `categorie`, `discussion_g`, `discussion_p`, `date_creation`, `likes`) VALUES
(1, 1, 'aide', 'hcbdcbjqjx cjbqj bhj hjxsjh hj hj hjqsj jhs', 'snd chj dsc sdqhc bhqchjc hsj chqcjq schv sdc', '2025-12-15 05:35:51', 1);

-- --------------------------------------------------------

--
-- Structure de la table `participations`
--

CREATE TABLE `participations` (
  `id_participation` int(11) NOT NULL,
  `id_evenement` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `motivation` text DEFAULT NULL,
  `source_information` varchar(100) DEFAULT NULL,
  `type_participation` enum('seul','groupe') NOT NULL,
  `nombre_personnes` int(11) DEFAULT 1,
  `desir_dejeuner` enum('oui','non') DEFAULT 'non',
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamations`
--

CREATE TABLE `reclamations` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gouvernorat` varchar(100) DEFAULT NULL,
  `delegation` varchar(100) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `position_gps` varchar(50) DEFAULT NULL,
  `description_detaillee` text DEFAULT NULL,
  `priorite` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reclamations`
--

INSERT INTO `reclamations` (`id`, `nom`, `prenom`, `telephone`, `email`, `gouvernorat`, `delegation`, `ville`, `position_gps`, `description_detaillee`, `priorite`, `statut`, `date`, `id_user`) VALUES
(1, 'bacha', 'khaireddine', '21870200', 'khaireddinebacha@gmail.com', 'Ben Arous', 'Mourouj', 'el battan', '36.8758415, 10.2406838 (Erreur API)', 'euibv   er r r r r', 'Urgente', 'Nouveau', '2025-12-15 06:25:51', 1);

-- --------------------------------------------------------

--
-- Structure de la table `reponadmin`
--

CREATE TABLE `reponadmin` (
  `id_reponse` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `reponse` text NOT NULL,
  `statut` varchar(50) DEFAULT 'En cours'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `nomEntreprise` varchar(255) NOT NULL,
  `emailContact` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `typeSponsoring` enum('Or','Argent','Bronze','Partenaire') NOT NULL,
  `montantEngage` decimal(10,2) DEFAULT NULL,
  `domaineActivite` varchar(255) DEFAULT NULL,
  `logoUrl` varchar(255) DEFAULT NULL,
  `contratUrl` varchar(255) DEFAULT NULL,
  `dateDebutPartenaire` date DEFAULT NULL,
  `dateFinPartenaire` date DEFAULT NULL,
  `statut` enum('Actif','Inactif','En attente') DEFAULT 'En attente',
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sponsors`
--

INSERT INTO `sponsors` (`id`, `nomEntreprise`, `emailContact`, `telephone`, `adresse`, `typeSponsoring`, `montantEngage`, `domaineActivite`, `logoUrl`, `contratUrl`, `dateDebutPartenaire`, `dateFinPartenaire`, `statut`, `id_user`) VALUES
(1, 'bibxuzecz', 'copezkofez@duziid.fzejf', '21870200', 'Ksar-Said, Délégation Le Bardo, Tunis, Gouvernorat Tunis, 2009, Tunisie', 'Partenaire', 515.00, 'buazbxazuxdb', '/PROJET_WEB_MVC_FINAL/public/uploads/logos/logo_1765773579_9497.jpg', 'https://www.google.com/search?client=opera&hs=ls1&sca_esv=796ee9412d452537&sxsrf=AE3TifN4rdp1dLtRuakq0Vnkr0YsB_zTSw:1765204414894&udm=2&fbs=AIIjpHxU7SXXniUZfeShr2fp4giZ1Y6MJ25_tmWITc7uy4KIeoJTKjrFjVxydQWqI2NcOhYPURIv2wPgv_w_sE_0Sc6Q2Uz8zlVUFOE6tcDqfo9WH77', '2025-12-24', '2025-12-26', 'Inactif', 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `age` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `interests` varchar(255) DEFAULT NULL,
  `is_banned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `age`, `address`, `bio`, `interests`, `is_banned`, `created_at`) VALUES
(1, 'khaireddine bacha', 'khaireddinebacha321@gmail.com', '$2y$10$E0JBvg3BgSS3nSpjFTAiFeQdSmWGR1nVNOZIPOPgcoKtbSDjSU9lq', 'user', 18, '8 rue habib', 'v v v v v v v v v v vv', 'Sports', 0, '2025-12-15 04:34:10'),
(2, 'khalil fakhfakh', 'khalilfakhfakh321@gmail.com', '$2y$10$f78oH3NAICB4cg2jVNbpGedcyBFLO/eezBrXRpuiSPpaSYDH66W0C', 'admin', 22, '12 rue france', 'b b b b b b b b b bb', 'Lecture', 0, '2025-12-15 04:44:39');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `association`
--
ALTER TABLE `association`
  ADD PRIMARY KEY (`id_association`),
  ADD UNIQUE KEY `nom_association` (`nom_association`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `id_forum` (`id_forum`),
  ADD KEY `id_auteur` (`id_auteur`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Index pour la table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`idDeal`),
  ADD KEY `idSponsor` (`idSponsor`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `don`
--
ALTER TABLE `don`
  ADD PRIMARY KEY (`id_don`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_association` (`id_association`);

--
-- Index pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id_evenement`);

--
-- Index pour la table `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`id_forum`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `participations`
--
ALTER TABLE `participations`
  ADD PRIMARY KEY (`id_participation`),
  ADD KEY `id_evenement` (`id_evenement`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `reclamations`
--
ALTER TABLE `reclamations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `reponadmin`
--
ALTER TABLE `reponadmin`
  ADD PRIMARY KEY (`id_reponse`),
  ADD KEY `id` (`id`);

--
-- Index pour la table `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `association`
--
ALTER TABLE `association`
  MODIFY `id_association` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `deals`
--
ALTER TABLE `deals`
  MODIFY `idDeal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `don`
--
ALTER TABLE `don`
  MODIFY `id_don` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id_evenement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `forum`
--
ALTER TABLE `forum`
  MODIFY `id_forum` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `participations`
--
ALTER TABLE `participations`
  MODIFY `id_participation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reclamations`
--
ALTER TABLE `reclamations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `reponadmin`
--
ALTER TABLE `reponadmin`
  MODIFY `id_reponse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`id_forum`) REFERENCES `forum` (`id_forum`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`id_auteur`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commentaire_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `commentaire` (`id_commentaire`) ON DELETE CASCADE;

--
-- Contraintes pour la table `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_ibfk_1` FOREIGN KEY (`idSponsor`) REFERENCES `sponsors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `don`
--
ALTER TABLE `don`
  ADD CONSTRAINT `don_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `don_ibfk_2` FOREIGN KEY (`id_association`) REFERENCES `association` (`id_association`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum`
--
ALTER TABLE `forum`
  ADD CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `participations`
--
ALTER TABLE `participations`
  ADD CONSTRAINT `participations_ibfk_1` FOREIGN KEY (`id_evenement`) REFERENCES `evenements` (`id_evenement`) ON DELETE CASCADE,
  ADD CONSTRAINT `participations_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reclamations`
--
ALTER TABLE `reclamations`
  ADD CONSTRAINT `reclamations_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reponadmin`
--
ALTER TABLE `reponadmin`
  ADD CONSTRAINT `reponadmin_ibfk_1` FOREIGN KEY (`id`) REFERENCES `reclamations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `sponsors`
--
ALTER TABLE `sponsors`
  ADD CONSTRAINT `sponsors_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
