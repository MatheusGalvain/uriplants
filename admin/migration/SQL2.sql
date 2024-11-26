-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 26/11/2024 às 04:27
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `uriplants`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Actions`
--

CREATE TABLE `Actions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Actions`:
--

--
-- Despejando dados para a tabela `Actions`
--

INSERT INTO `Actions` (`id`, `name`) VALUES
(1, 'create'),
(2, 'delete'),
(3, 'edit');

-- --------------------------------------------------------

--
-- Estrutura para tabela `AuditLogs`
--

CREATE TABLE `AuditLogs` (
  `id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `action_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `AuditLogs`:
--   `action_id`
--       `Actions` -> `id`
--   `plant_id`
--       `Plants` -> `id`
--   `changed_by`
--       `Users` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Classes`
--

CREATE TABLE `Classes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Classes`:
--

--
-- Despejando dados para a tabela `Classes`
--

INSERT INTO `Classes` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-09-10 01:23:16', NULL),
(1, 'Magnoliopsida (Dicotiledonae)', '2024-10-12 22:57:28', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Divisions`
--

CREATE TABLE `Divisions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Divisions`:
--

--
-- Despejando dados para a tabela `Divisions`
--

INSERT INTO `Divisions` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-10-10 02:04:56', NULL),
(1, 'Magnoliophyta (Angiospermae)', '2024-10-12 22:55:28', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Families`
--

CREATE TABLE `Families` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Families`:
--

--
-- Despejando dados para a tabela `Families`
--

INSERT INTO `Families` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-10-10 02:07:30', NULL),
(1, 'Fabaceae (Leguminosae: Caesalpinioideae)', '2024-10-12 22:57:42', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Genus`
--

CREATE TABLE `Genus` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Genus`:
--

--
-- Despejando dados para a tabela `Genus`
--

INSERT INTO `Genus` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-09-24 02:39:08', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Images`
--

CREATE TABLE `Images` (
  `id` int(11) NOT NULL,
  `imagem` longblob NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `plants_property_id` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ordenation` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Images`:
--   `plants_property_id`
--       `PlantsProperties` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Orders`
--

CREATE TABLE `Orders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Orders`:
--

--
-- Despejando dados para a tabela `Orders`
--

INSERT INTO `Orders` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-10-10 02:10:26', NULL),
(1, 'Fabales', '2024-10-12 22:57:37', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `PasswordResets`
--

CREATE TABLE `PasswordResets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `PasswordResets`:
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Plants`
--

CREATE TABLE `Plants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `common_names` text DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `family_id` int(11) DEFAULT NULL,
  `genus_id` int(11) DEFAULT NULL,
  `species` varchar(255) DEFAULT NULL,
  `biology_description` text DEFAULT NULL,
  `uses_description` text DEFAULT NULL,
  `curious_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `bark_description` text DEFAULT NULL,
  `trunk_description` text DEFAULT NULL,
  `leaf_description` text DEFAULT NULL,
  `flower_description` text DEFAULT NULL,
  `fruit_description` text DEFAULT NULL,
  `seed_description` text DEFAULT NULL,
  `region_name` varchar(255) DEFAULT NULL,
  `region_source` varchar(255) DEFAULT NULL,
  `region_description` varchar(255) DEFAULT NULL,
  `region_image` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Plants`:
--   `class_id`
--       `Classes` -> `id`
--   `division_id`
--       `Divisions` -> `id`
--   `family_id`
--       `Families` -> `id`
--   `genus_id`
--       `Genus` -> `id`
--   `order_id`
--       `Orders` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `PlantsProperties`
--

CREATE TABLE `PlantsProperties` (
  `id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `PlantsProperties`:
--   `plant_id`
--       `Plants` -> `id`
--   `property_id`
--       `Properties` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Properties`
--

CREATE TABLE `Properties` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ordenation` int(11) DEFAULT NULL,
  `name_ref` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Properties`:
--

--
-- Despejando dados para a tabela `Properties`
--

INSERT INTO `Properties` (`id`, `name`, `ordenation`, `name_ref`) VALUES
(1, 'Planta', 0, 'plant'),
(2, 'Biologia', 1, 'biology'),
(3, 'Tronco', 2, 'trunk'),
(4, 'Casca', 3, 'bark'),
(5, 'Folha', 4, 'leaf'),
(6, 'Flor', 5, 'flower'),
(7, 'Fruto', 6, 'fruit'),
(8, 'Semente', 7, 'seed'),
(9, 'Curiosidades', 8, 'curious'),
(10, 'Produtos e Usos', 9, 'uses');

-- --------------------------------------------------------

--
-- Estrutura para tabela `QrCodeUrl`
--

CREATE TABLE `QrCodeUrl` (
  `id` int(11) NOT NULL,
  `url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `QrCodeUrl`:
--

--
-- Despejando dados para a tabela `QrCodeUrl`
--

INSERT INTO `QrCodeUrl` (`id`, `url`) VALUES
(1, 'http://localhost/uriPlants/public/Plants');

-- --------------------------------------------------------

--
-- Estrutura para tabela `UsefulLinks`
--

CREATE TABLE `UsefulLinks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `UsefulLinks`:
--   `plant_id`
--       `Plants` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_administrator` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `fname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `Users`:
--

--
-- Despejando dados para a tabela `Users`
--

INSERT INTO `Users` (`id`, `email`, `password`, `is_administrator`, `created_at`, `deleted_at`, `fname`) VALUES
(1, 'arborea@uricer.edu.br', '$2y$10$Drwx.3dT62cqP64CM1hlh.yn7mkFc0EkktC76Bnvv1NNSheLIdhn2', 1, '2024-09-16 01:41:52', NULL, 'Arborea');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `Actions`
--
ALTER TABLE `Actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Actions_Name` (`name`);

--
-- Índices de tabela `AuditLogs`
--
ALTER TABLE `AuditLogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Plants_AuditLogs` (`plant_id`),
  ADD KEY `FK_Actions_AuditLogs` (`action_id`),
  ADD KEY `FK_Users_AuditLogs` (`changed_by`);

--
-- Índices de tabela `Classes`
--
ALTER TABLE `Classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Classes_Name` (`name`);

--
-- Índices de tabela `Divisions`
--
ALTER TABLE `Divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Divisions_Name` (`name`);

--
-- Índices de tabela `Families`
--
ALTER TABLE `Families`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Families_Name` (`name`);

--
-- Índices de tabela `Genus`
--
ALTER TABLE `Genus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Genus_Name` (`name`);

--
-- Índices de tabela `Images`
--
ALTER TABLE `Images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_PlantsProperties_Images` (`plants_property_id`);

--
-- Índices de tabela `Orders`
--
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Orders_Name` (`name`);

--
-- Índices de tabela `PasswordResets`
--
ALTER TABLE `PasswordResets`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Plants`
--
ALTER TABLE `Plants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Divisions_Plants` (`division_id`),
  ADD KEY `FK_Classes_Plants` (`class_id`),
  ADD KEY `FK_Orders_Plants` (`order_id`),
  ADD KEY `FK_Families_Plants` (`family_id`),
  ADD KEY `FK_Genus_Plants` (`genus_id`);

--
-- Índices de tabela `PlantsProperties`
--
ALTER TABLE `PlantsProperties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Plants_PlantsProperties` (`plant_id`),
  ADD KEY `FK_Properties_PlantsProperties` (`property_id`);

--
-- Índices de tabela `Properties`
--
ALTER TABLE `Properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Properties_Name` (`name`),
  ADD UNIQUE KEY `UQ_Properties_Ordenation` (`ordenation`);

--
-- Índices de tabela `QrCodeUrl`
--
ALTER TABLE `QrCodeUrl`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `UsefulLinks`
--
ALTER TABLE `UsefulLinks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Plants_UsefulLinks` (`plant_id`);

--
-- Índices de tabela `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Users_Email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `Actions`
--
ALTER TABLE `Actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `AuditLogs`
--
ALTER TABLE `AuditLogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `Classes`
--
ALTER TABLE `Classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `Divisions`
--
ALTER TABLE `Divisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `Families`
--
ALTER TABLE `Families`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `Genus`
--
ALTER TABLE `Genus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `Images`
--
ALTER TABLE `Images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `Orders`
--
ALTER TABLE `Orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `PasswordResets`
--
ALTER TABLE `PasswordResets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `Plants`
--
ALTER TABLE `Plants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `PlantsProperties`
--
ALTER TABLE `PlantsProperties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `Properties`
--
ALTER TABLE `Properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `UsefulLinks`
--
ALTER TABLE `UsefulLinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `AuditLogs`
--
ALTER TABLE `AuditLogs`
  ADD CONSTRAINT `FK_Actions_AuditLogs` FOREIGN KEY (`action_id`) REFERENCES `Actions` (`id`),
  ADD CONSTRAINT `FK_Plants_AuditLogs` FOREIGN KEY (`plant_id`) REFERENCES `Plants` (`id`),
  ADD CONSTRAINT `FK_Users_AuditLogs` FOREIGN KEY (`changed_by`) REFERENCES `Users` (`id`);

--
-- Restrições para tabelas `Images`
--
ALTER TABLE `Images`
  ADD CONSTRAINT `FK_PlantsProperties_Images` FOREIGN KEY (`plants_property_id`) REFERENCES `PlantsProperties` (`id`);

--
-- Restrições para tabelas `Plants`
--
ALTER TABLE `Plants`
  ADD CONSTRAINT `FK_Classes_Plants` FOREIGN KEY (`class_id`) REFERENCES `Classes` (`id`),
  ADD CONSTRAINT `FK_Divisions_Plants` FOREIGN KEY (`division_id`) REFERENCES `Divisions` (`id`),
  ADD CONSTRAINT `FK_Families_Plants` FOREIGN KEY (`family_id`) REFERENCES `Families` (`id`),
  ADD CONSTRAINT `FK_Genus_Plants` FOREIGN KEY (`genus_id`) REFERENCES `Genus` (`id`),
  ADD CONSTRAINT `FK_Orders_Plants` FOREIGN KEY (`order_id`) REFERENCES `Orders` (`id`);

--
-- Restrições para tabelas `PlantsProperties`
--
ALTER TABLE `PlantsProperties`
  ADD CONSTRAINT `FK_Plants_PlantsProperties` FOREIGN KEY (`plant_id`) REFERENCES `Plants` (`id`),
  ADD CONSTRAINT `FK_Properties_PlantsProperties` FOREIGN KEY (`property_id`) REFERENCES `Properties` (`id`);

--
-- Restrições para tabelas `UsefulLinks`
--
ALTER TABLE `UsefulLinks`
  ADD CONSTRAINT `FK_Plants_UsefulLinks` FOREIGN KEY (`plant_id`) REFERENCES `Plants` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
