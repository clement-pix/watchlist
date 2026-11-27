-- phpMyAdmin SQL Dump 
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost 
-- Généré le : mer. 14 mai 2025 à 10:13 ok
-- Version du serveur : 10.11.11-MariaDB-0+deb12u1
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Importer dans une base vide / Import into an empty database.
--

-- --------------------------------------------------------

--
-- Structure de la table `wl_avis`
--

CREATE TABLE `wl_avis` (
  `id_media` int(11) NOT NULL,
  `id` bigint(20) UNSIGNED NOT NULL,
  `note` int(11) NOT NULL,
  `commentaire` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_cache`
--

CREATE TABLE `wl_cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_cache_locks`
--

CREATE TABLE `wl_cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_categorie`
--

CREATE TABLE `wl_categorie` (
  `id_categorie` int(11) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_Composer`
--

CREATE TABLE `wl_Composer` (
  `id_media` int(11) NOT NULL,
  `id_liste` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_failed_jobs`
--

CREATE TABLE `wl_failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_genre`
--

CREATE TABLE `wl_genre` (
  `id_genre` int(11) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_jobs`
--

CREATE TABLE `wl_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_job_batches`
--

CREATE TABLE `wl_job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_liste`
--

CREATE TABLE `wl_liste` (
  `id_liste` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `date_creation` date NOT NULL,
  `id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_media`
--

CREATE TABLE `wl_media` (
  `id_media` int(11) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `description` varchar(1500) NOT NULL,
  `lien_image` varchar(255) NOT NULL,
  `date_ajout` date NOT NULL,
  `id_categorie` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_migrations`
--

CREATE TABLE `wl_migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_password_reset_tokens`
--

CREATE TABLE `wl_password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_posseder`
--

CREATE TABLE `wl_posseder` (
  `id_genre` int(11) NOT NULL,
  `id_media` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_Role`
--

CREATE TABLE `wl_Role` (
  `id_role` int(11) NOT NULL,
  `label` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_sessions`
--

CREATE TABLE `wl_sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wl_users`
--

CREATE TABLE `wl_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pseudo` varchar(50) NOT NULL,
  `id_role` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `wl_avis`
--
ALTER TABLE `wl_avis`
  ADD PRIMARY KEY (`id_media`,`id`),
  ADD KEY `id` (`id`);

--
-- Index pour la table `wl_cache`
--
ALTER TABLE `wl_cache`
  ADD PRIMARY KEY (`key`);

--
-- Index pour la table `wl_cache_locks`
--
ALTER TABLE `wl_cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Index pour la table `wl_categorie`
--
ALTER TABLE `wl_categorie`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `wl_Composer`
--
ALTER TABLE `wl_Composer`
  ADD PRIMARY KEY (`id_media`,`id_liste`),
  ADD KEY `Composer_WL_liste0_FK` (`id_liste`),
  ADD KEY `Composer_WL_users_FK` (`user_id`);

--
-- Index pour la table `wl_failed_jobs`
--
ALTER TABLE `wl_failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wl_failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `wl_genre`
--
ALTER TABLE `wl_genre`
  ADD PRIMARY KEY (`id_genre`);

--
-- Index pour la table `wl_jobs`
--
ALTER TABLE `wl_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wl_jobs_queue_index` (`queue`);

--
-- Index pour la table `wl_job_batches`
--
ALTER TABLE `wl_job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `wl_liste`
--
ALTER TABLE `wl_liste`
  ADD PRIMARY KEY (`id_liste`),
  ADD KEY `id` (`id`);

--
-- Index pour la table `wl_media`
--
ALTER TABLE `wl_media`
  ADD PRIMARY KEY (`id_media`),
  ADD KEY `wl_media_WL_categorie_FK` (`id_categorie`);

--
-- Index pour la table `wl_migrations`
--
ALTER TABLE `wl_migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `wl_password_reset_tokens`
--
ALTER TABLE `wl_password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `wl_posseder`
--
ALTER TABLE `wl_posseder`
  ADD PRIMARY KEY (`id_genre`,`id_media`),
  ADD KEY `posseder_WL_media0_FK` (`id_media`);

--
-- Index pour la table `wl_Role`
--
ALTER TABLE `wl_Role`
  ADD PRIMARY KEY (`id_role`);

--
-- Index pour la table `wl_sessions`
--
ALTER TABLE `wl_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wl_sessions_user_id_index` (`user_id`),
  ADD KEY `wl_sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `wl_users`
--
ALTER TABLE `wl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wl_users_email_unique` (`email`),
  ADD UNIQUE KEY `pseudo` (`pseudo`),
  ADD KEY `id_role` (`id_role`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `wl_categorie`
--
ALTER TABLE `wl_categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_failed_jobs`
--
ALTER TABLE `wl_failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_genre`
--
ALTER TABLE `wl_genre`
  MODIFY `id_genre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_jobs`
--
ALTER TABLE `wl_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_liste`
--
ALTER TABLE `wl_liste`
  MODIFY `id_liste` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_media`
--
ALTER TABLE `wl_media`
  MODIFY `id_media` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_migrations`
--
ALTER TABLE `wl_migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_Role`
--
ALTER TABLE `wl_Role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `wl_users`
--
ALTER TABLE `wl_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `wl_avis`
--
ALTER TABLE `wl_avis`
  ADD CONSTRAINT `wl_avis_WL_media_FK` FOREIGN KEY (`id_media`) REFERENCES `wl_media` (`id_media`),
  ADD CONSTRAINT `wl_avis_ibfk_1` FOREIGN KEY (`id`) REFERENCES `wl_users` (`id`);

--
-- Contraintes pour la table `wl_Composer`
--
ALTER TABLE `wl_Composer`
  ADD CONSTRAINT `Composer_WL_liste0_FK` FOREIGN KEY (`id_liste`) REFERENCES `wl_liste` (`id_liste`),
  ADD CONSTRAINT `Composer_WL_media_FK` FOREIGN KEY (`id_media`) REFERENCES `wl_media` (`id_media`),
  ADD CONSTRAINT `fk_composer_user` FOREIGN KEY (`user_id`) REFERENCES `wl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `wl_liste`
--
ALTER TABLE `wl_liste`
  ADD CONSTRAINT `wl_liste_ibfk_1` FOREIGN KEY (`id`) REFERENCES `wl_users` (`id`);

--
-- Contraintes pour la table `wl_media`
--
ALTER TABLE `wl_media`
  ADD CONSTRAINT `wl_media_WL_categorie_FK` FOREIGN KEY (`id_categorie`) REFERENCES `wl_categorie` (`id_categorie`);

--
-- Contraintes pour la table `wl_posseder`
--
ALTER TABLE `wl_posseder`
  ADD CONSTRAINT `posseder_WL_genre_FK` FOREIGN KEY (`id_genre`) REFERENCES `wl_genre` (`id_genre`),
  ADD CONSTRAINT `posseder_WL_media0_FK` FOREIGN KEY (`id_media`) REFERENCES `wl_media` (`id_media`);

--
-- Contraintes pour la table `wl_users`
--
ALTER TABLE `wl_users`
  ADD CONSTRAINT `wl_users_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `wl_Role` (`id_role`);
-- FR : ces migrations sont déjà représentées par la structure importée.
-- EN: These migrations are already represented by the imported schema.
INSERT INTO `wl_migrations` (`migration`, `batch`) VALUES
  ('0001_01_01_000000_create_users_table', 1),
  ('0001_01_01_000001_create_cache_table', 1),
  ('0001_01_01_000002_create_jobs_table', 1),
  ('2025_03_14_104840_add_firstname_to__w_l_users_table', 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
