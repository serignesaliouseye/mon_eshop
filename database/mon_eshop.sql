-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : mer. 20 mai 2026 à 14:54
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
-- Base de données : `mon_eshop`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `description`) VALUES
(1, 'Plats de riz', 'Thiéboudienne, riz au poisson, riz gras…'),
(2, 'Plats en sauce', 'Yassa, mafé, domoda, soupou kandja…'),
(3, 'Grillades', 'Poulet braisé, poisson braisé, brochettes…'),
(4, 'Boissons locales', 'Bissap, gingembre, bouye, ditakh…');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `statut` enum('En attente','Validée','Annulée') DEFAULT 'En attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `adresse`, `statut`, `created_at`) VALUES
(1, 2, 'Quartier Médina, Rue 12, Dakar', 'Validée', '2026-05-17 16:34:53'),
(2, 3, 'HLM Grand Yoff, Villa 45, Dakar', 'Validée', '2026-05-17 16:34:53'),
(3, 6, 'Saint Louis , senegal , Djougob peulh', 'Validée', '2026-05-19 14:44:26'),
(4, 5, 'Dakar, Pikine', 'Validée', '2026-05-19 22:30:31'),
(5, 6, 'Dakar, Grand Yoff', 'Validée', '2026-05-19 22:58:33');

-- --------------------------------------------------------

--
-- Structure de la table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `prix_unitaire`) VALUES
(1, 1, 1, 3500.00),
(2, 1, 10, 500.00),
(3, 2, 4, 3000.00),
(4, 2, 8, 2500.00),
(5, 3, 5, 3500.00),
(6, 4, 2, 3200.00),
(7, 5, 2, 3200.00);

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `nom`, `prix`, `image`, `description`, `categorie_id`, `created_at`) VALUES
(1, 'Thiéboudienne rouge', 3500.00, 'assets/images/plat_1779151896.jpg', 'Le plat national : riz cuit dans une sauce tomate avec du poisson et légumes.', 1, '2026-05-17 16:34:53'),
(2, 'Thiéboudienne blanc', 3200.00, 'assets/images/plat_1779202728.jpg', 'Version blanche du thiébou dieune, légumes assortis et poisson frais.', 1, '2026-05-17 16:34:53'),
(3, 'Riz gras poulet', 2800.00, 'assets/images/plat_1779202993.jpg', 'Riz cuit dans un bouillon épicé avec morceaux de poulet tendres.', 1, '2026-05-17 16:34:53'),
(4, 'Yassa poulet', 3000.00, 'assets/images/plat_1779203217.jpg', 'Poulet mariné mijoté avec oignons caramélisés et citron, servi avec riz.', 2, '2026-05-17 16:34:53'),
(5, 'Mafé bœuf', 3500.00, 'assets/images/plat_1779203627.jpg', 'Ragoût de bœuf à la sauce arachide, servi avec riz blanc.', 2, '2026-05-17 16:34:53'),
(6, 'Soupou kandja', 3200.00, 'assets/images/plat_1779203402.jpg', 'Soupe à base de gombo, viande et poisson fumé, accompagnée de riz.', 2, '2026-05-17 16:34:53'),
(7, 'Domoda', 3000.00, 'assets/images/plat_1779203546.jpg', 'Ragoût à la purée de tomates et arachides, très apprécié au Sénégal.', 2, '2026-05-17 16:34:53'),
(8, 'Poulet braisé', 2500.00, 'assets/images/plat_1779203813.jpg', 'Demi-poulet mariné et grillé au feu de bois, servi avec pain ou riz.', 3, '2026-05-17 16:34:53'),
(9, 'Poisson braisé', 2800.00, 'assets/images/plat_1779203915.jpg', 'Thiof braisé avec sauce oignon-tomate maison.', 3, '2026-05-17 16:34:53'),
(10, 'Bissap', 500.00, 'assets/images/plat_1779204302.jpg', 'Jus d\'hibiscus naturel sucré et rafraîchissant, préparé maison.', 4, '2026-05-17 16:34:53'),
(11, 'Jus de gingembre', 500.00, 'assets/images/plat_1779204318.jpg', 'Jus de gingembre frais légèrement épicé.', 4, '2026-05-17 16:34:53'),
(12, 'Jus de bouye', 600.00, 'assets/images/plat_1779204335.jpg', 'Jus de baobab riche en vitamines, très désaltérant.', 4, '2026-05-17 16:34:53');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `password`, `is_admin`, `created_at`) VALUES
(2, 'Moussa Diallo', 'moussa@email.sn', '$2y$10$8K1p/a0dR1lFkxqp3W3HOeLdLzXf0Z6H1mH5Q6kY8nGZCDxVBuKOi', 0, '2026-05-17 16:34:53'),
(3, 'Fatou Ndiaye', 'fatou@email.sn', '$2y$10$8K1p/a0dR1lFkxqp3W3HOeLdLzXf0Z6H1mH5Q6kY8nGZCDxVBuKOi', 0, '2026-05-17 16:34:53'),
(4, 'Serigne saliou Seye', 'saliou@gmail.com', '$2y$10$GIXe5MfdWWGR3ZERphcLjeUs8uR5IKKHO3XHc3g8Qw3aNxh99nzPa', 0, '2026-05-18 15:08:18'),
(5, 'Admin SenFood', 'admin@senfood.sn', '6a3904d3808c250d33105a2269f69b5b', 1, '2026-05-18 17:50:02'),
(6, 'Fallou Ndiaye', 'fallou@gmail.com', '5346a7fbe388e5cd2318219426feb768', 0, '2026-05-19 14:42:31');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`);

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
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
