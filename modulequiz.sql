-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 16 nov. 2025 à 22:43
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
-- Base de données : `modulequiz`
--

-- --------------------------------------------------------

--
-- Structure de la table `quiz`
--

CREATE TABLE `quiz` (
  `id_quiz` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `statut` enum('actif','inactif') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quiz`
--

INSERT INTO `quiz` (`id_quiz`, `titre`, `description`, `date_creation`, `statut`) VALUES
(1, 'Cybersecurity Knowledge Test', 'Discover your mastery level of essential digital security concepts', '2025-11-14 14:01:12', 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `reponse`
--

CREATE TABLE `reponse` (
  `id_reponse` int(11) NOT NULL,
  `id_quiz` int(11) NOT NULL,
  `question` text NOT NULL,
  `option1` varchar(255) NOT NULL,
  `option2` varchar(255) NOT NULL,
  `option3` varchar(255) NOT NULL,
  `reponse_correcte` int(11) NOT NULL CHECK (`reponse_correcte` in (1,2,3))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reponse`
--

INSERT INTO `reponse` (`id_reponse`, `id_quiz`, `question`, `option1`, `option2`, `option3`, `reponse_correcte`) VALUES
(1, 1, 'What is phishing?', 'An illegal fishing technique', 'An email attack aimed at stealing sensitive information', 'A type of computer virus', 2),
(2, 1, 'What is the main characteristic of a strong password?', 'It contains only letters', 'It\'s short and easy to remember', 'It combines letters, numbers and special characters', 3),
(3, 1, 'What is two-factor authentication (2FA)?', 'A method to verify your identity twice', 'A security system that requires two passwords', 'A procedure using two different methods to verify your identity', 3),
(4, 1, 'What is the main purpose of a firewall?', 'Speed up Internet connection', 'Control incoming and outgoing network traffic', 'Store important files', 2),
(5, 1, 'What is ransomware?', 'Software that encrypts your files and demands a ransom', 'A virus that deletes all your files', 'A program that spies on your online activities', 1),
(6, 1, 'Why is it important to regularly update your software?', 'To have the latest features', 'To fix security vulnerabilities', 'To improve system performance', 2),
(7, 1, 'What is data encryption?', 'Compressing data to save space', 'Transforming data into an unreadable format without a key', 'Automatic data backup', 2),
(8, 1, 'What does VPN stand for?', 'Virtual Private Network', 'Very Protected Network', 'Virtual Public Network', 1),
(9, 1, 'What is the best practice to protect against malware?', 'Use only known websites', 'Install antivirus and keep it updated', 'Turn off your computer after each use', 2),
(10, 1, 'What is a brute force attack?', 'A physical attack on a server', 'A method that tries all possible combinations to find a password', 'A technique that uses force to break a code', 2);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id_quiz`);

--
-- Index pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD PRIMARY KEY (`id_reponse`),
  ADD KEY `id_quiz` (`id_quiz`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id_quiz` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `reponse`
--
ALTER TABLE `reponse`
  MODIFY `id_reponse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD CONSTRAINT `reponse_ibfk_1` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
