-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 28 avr. 2026 à 21:17
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `rentit`
--

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` enum('PC portable','Écran','Accessoire','Serveur','Autre') COLLATE utf8mb4_unicode_ci DEFAULT 'Autre',
  `prix_jour` decimal(8,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '1',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.jpg',
  `disponible` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `description`, `categorie`, `prix_jour`, `stock`, `image`, `disponible`, `created_at`) VALUES
(1, 'Dell XPS 15 — i7 / 32 Go / 1 To SSD', 'PC portable haute performance idéal pour les présentations et le développement. Écran 15,6\" OLED 4K, clavier rétroéclairé, autonomie 12h.', 'PC portable', 25.00, 3, 'default.jpg', 1, '2026-04-28 22:56:20'),
(2, 'MacBook Pro 14\" M3 — 16 Go / 512 Go', 'PC portable Apple dernière génération. Performances exceptionnelles pour la création et la bureautique. Autonomie jusqu\'à 18h.', 'PC portable', 35.00, 2, 'default.jpg', 1, '2026-04-28 22:56:20'),
(3, 'Lenovo ThinkPad X1 Carbon — i5 / 16 Go', 'PC portable professionnel ultra-léger (1,12 kg). Robuste, fiable, idéal pour les déplacements. Clavier de référence.', 'PC portable', 20.00, 4, 'default.jpg', 1, '2026-04-28 22:56:20'),
(4, 'HP EliteBook 840 G10 — i5 / 16 Go', 'PC portable professionnel certifié MIL-STD. Sécurité renforcée (lecteur d\'empreinte, webcam IR). Idéal en entreprise.', 'PC portable', 18.00, 5, 'default.jpg', 1, '2026-04-28 22:56:20'),
(5, 'Écran Dell UltraSharp 27\" 4K', 'Écran professionnel 27 pouces résolution 4K UHD. Dalle IPS, calibration colorimétrique, pied réglable. Compatible USB-C.', 'Écran', 10.00, 6, 'default.jpg', 1, '2026-04-28 22:56:20'),
(6, 'Écran LG UltraWide 34\" Curved', 'Écran ultra-large 34 pouces incurvé. Résolution 3440×1440, parfait pour le multitâche et les présentations larges.', 'Écran', 12.00, 3, 'default.jpg', 1, '2026-04-28 22:56:20'),
(7, 'Pack Clavier + Souris sans fil Logitech MX', 'Combo clavier + souris haut de gamme Logitech MX Keys + MX Master 3. Connexion Bluetooth multi-appareils.', 'Accessoire', 5.00, 8, 'default.jpg', 1, '2026-04-28 22:56:20'),
(8, 'Webcam Logitech BRIO 4K', 'Webcam 4K ultra HD avec mise au point automatique et correction de l\'éclairage. Idéale pour les visioconférences professionnelles.', 'Accessoire', 6.00, 5, 'default.jpg', 1, '2026-04-28 22:56:20'),
(9, 'Switch réseau 8 ports Gigabit', 'Switch réseau non manageable 8 ports Gigabit pour installer un réseau temporaire lors d\'un événement ou formation.', 'Accessoire', 8.00, 4, 'default.jpg', 1, '2026-04-28 22:56:20'),
(10, 'Serveur NAS Synology DS923+', 'NAS 4 baies avec 2×8 To configurés en RAID. Idéal pour le stockage partagé lors d\'un projet ou événement.', 'Serveur', 45.00, 1, 'default.jpg', 1, '2026-04-28 22:56:20');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `produit_id` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `nb_jours` int NOT NULL,
  `prix_total` decimal(8,2) NOT NULL,
  `statut` enum('en_attente','confirmée','en_cours','terminée','annulée') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `produit_id` (`produit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `produit_id`, `date_debut`, `date_fin`, `nb_jours`, `prix_total`, `statut`, `message`, `created_at`) VALUES
(1, 2, 4, '2026-04-29', '2026-04-30', 1, 18.00, 'en_attente', 'Test', '2026-04-28 23:10:36');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `mot_de_passe`, `telephone`, `role`, `created_at`) VALUES
(2, 'Miguel Peixoto', 'mipeixoto@mail.fr', '$2y$10$hS6wmbGIA/Ly1RjZw1fspeWvGdo9SW2wu9skoshJYp.yRlbZOTw6q', '06 00 00 00 00', 'user', '2026-04-28 23:09:23'),
(3, 'Admin', 'admin@mail.fr', '$2y$10$KVeG/gMSqs3w4neQF3AOzebdKxpyocvs/77rw5nQMywMES41chihC', '06 00 00 00 00', 'admin', '2026-04-28 23:11:25');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
