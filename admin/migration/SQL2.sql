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
-- Estrutura para tabela `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `actions`:
--

--
-- Despejando dados para a tabela `actions`
--

INSERT INTO `actions` (`id`, `name`) VALUES
(1, 'create'),
(2, 'delete'),
(3, 'edit');

-- --------------------------------------------------------

--
-- Estrutura para tabela `auditlogs`
--

CREATE TABLE `auditlogs` (
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
-- RELACIONAMENTOS PARA TABELAS `auditlogs`:
--   `action_id`
--       `actions` -> `id`
--   `plant_id`
--       `plants` -> `id`
--   `changed_by`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `classes`:
--

--
-- Despejando dados para a tabela `classes`
--

INSERT INTO `classes` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-09-10 01:23:16', NULL),
(1, 'Magnoliopsida (Dicotiledonae)', '2024-10-12 22:57:28', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `divisions`
--

CREATE TABLE `divisions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `divisions`:
--

--
-- Despejando dados para a tabela `divisions`
--

INSERT INTO `divisions` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-10-10 02:04:56', NULL),
(1, 'Magnoliophyta (Angiospermae)', '2024-10-12 22:55:28', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `families`
--

CREATE TABLE `families` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `families`:
--

--
-- Despejando dados para a tabela `families`
--

INSERT INTO `families` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-10-10 02:07:30', NULL),
(1, 'Fabaceae (Leguminosae: Caesalpinioideae)', '2024-10-12 22:57:42', NULL),

-- --------------------------------------------------------

--
-- Estrutura para tabela `genus`
--

CREATE TABLE `genus` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `genus`:
--

--
-- Despejando dados para a tabela `genus`
--

INSERT INTO `genus` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-09-24 02:39:08', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `imagem` longblob NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `plants_property_id` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ordenation` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `images`:
--   `plants_property_id`
--       `plantsproperties` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `orders`:
--

--
-- Despejando dados para a tabela `orders`
--

INSERT INTO `orders` (`id`, `name`, `created_at`, `deleted_at`) VALUES
(0, 'Não informado', '2024-10-10 02:10:26', NULL),
(1, 'Fabales', '2024-10-12 22:57:37', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `passwordresets`
--

CREATE TABLE `passwordresets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `passwordresets`:
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `plants`
--

CREATE TABLE `plants` (
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
-- RELACIONAMENTOS PARA TABELAS `plants`:
--   `class_id`
--       `classes` -> `id`
--   `division_id`
--       `divisions` -> `id`
--   `family_id`
--       `families` -> `id`
--   `genus_id`
--       `genus` -> `id`
--   `order_id`
--       `orders` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `plantsproperties`
--

CREATE TABLE `plantsproperties` (
  `id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `plantsproperties`:
--   `plant_id`
--       `plants` -> `id`
--   `property_id`
--       `properties` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ordenation` int(11) DEFAULT NULL,
  `name_ref` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `properties`:
--

--
-- Despejando dados para a tabela `properties`
--

INSERT INTO `properties` (`id`, `name`, `ordenation`, `name_ref`) VALUES
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
-- Estrutura para tabela `qrcodeurl`
--

CREATE TABLE `qrcodeurl` (
  `id` int(11) NOT NULL,
  `url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `qrcodeurl`:
--
--
-- Despejando dados para a tabela `QrCodeUrl`
--

INSERT INTO `qrcodeurl` (`id`, `url`) VALUES
(1, 'http://localhost/uriPlants/public/Plants');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usefullinks`
--

CREATE TABLE `usefullinks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `usefullinks`:
--   `plant_id`
--       `plants` -> `id`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_administrator` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `fname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONAMENTOS PARA TABELAS `users`:
--

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `is_administrator`, `created_at`, `deleted_at`, `fname`) VALUES
(1, 'arborea@uricer.edu.br', '$2y$10$Drwx.3dT62cqP64CM1hlh.yn7mkFc0EkktC76Bnvv1NNSheLIdhn2', 1, '2024-09-16 01:41:52', NULL, 'Arborea');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Actions_Name` (`name`);

--
-- Índices de tabela `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Plants_AuditLogs` (`plant_id`),
  ADD KEY `FK_Actions_AuditLogs` (`action_id`),
  ADD KEY `FK_Users_AuditLogs` (`changed_by`);

--
-- Índices de tabela `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Classes_Name` (`name`);

--
-- Índices de tabela `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Divisions_Name` (`name`);

--
-- Índices de tabela `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Families_Name` (`name`);

--
-- Índices de tabela `genus`
--
ALTER TABLE `genus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Genus_Name` (`name`);

--
-- Índices de tabela `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_PlantsProperties_Images` (`plants_property_id`);

--
-- Índices de tabela `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Orders_Name` (`name`);

--
-- Índices de tabela `passwordresets`
--
ALTER TABLE `passwordresets`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `plants`
--
ALTER TABLE `plants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Divisions_Plants` (`division_id`),
  ADD KEY `FK_Classes_Plants` (`class_id`),
  ADD KEY `FK_Orders_Plants` (`order_id`),
  ADD KEY `FK_Families_Plants` (`family_id`),
  ADD KEY `FK_Genus_Plants` (`genus_id`);

--
-- Índices de tabela `plantsproperties`
--
ALTER TABLE `plantsproperties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Plants_PlantsProperties` (`plant_id`),
  ADD KEY `FK_Properties_PlantsProperties` (`property_id`);

--
-- Índices de tabela `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Properties_Name` (`name`),
  ADD UNIQUE KEY `UQ_Properties_Ordenation` (`ordenation`);

--
-- Índices de tabela `qrcodeurl`
--
ALTER TABLE `qrcodeurl`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usefullinks`
--
ALTER TABLE `usefullinks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Plants_UsefulLinks` (`plant_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_Users_Email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `families`
--
ALTER TABLE `families`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `genus`
--
ALTER TABLE `genus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `passwordresets`
--
ALTER TABLE `passwordresets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `plants`
--
ALTER TABLE `plants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `plantsproperties`
--
ALTER TABLE `plantsproperties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `usefullinks`
--
ALTER TABLE `usefullinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD CONSTRAINT `FK_Actions_AuditLogs` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`),
  ADD CONSTRAINT `FK_Plants_AuditLogs` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`),
  ADD CONSTRAINT `FK_Users_AuditLogs` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `FK_PlantsProperties_Images` FOREIGN KEY (`plants_property_id`) REFERENCES `plantsproperties` (`id`);

--
-- Restrições para tabelas `plants`
--
ALTER TABLE `plants`
  ADD CONSTRAINT `FK_Classes_Plants` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `FK_Divisions_Plants` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `FK_Families_Plants` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`),
  ADD CONSTRAINT `FK_Genus_Plants` FOREIGN KEY (`genus_id`) REFERENCES `genus` (`id`),
  ADD CONSTRAINT `FK_Orders_Plants` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Restrições para tabelas `plantsproperties`
--
ALTER TABLE `plantsproperties`
  ADD CONSTRAINT `FK_Plants_PlantsProperties` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`),
  ADD CONSTRAINT `FK_Properties_PlantsProperties` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Restrições para tabelas `usefullinks`
--
ALTER TABLE `usefullinks`
  ADD CONSTRAINT `FK_Plants_UsefulLinks` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
