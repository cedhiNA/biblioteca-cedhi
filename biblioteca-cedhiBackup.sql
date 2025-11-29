-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 29-11-2025 a las 11:43:35
-- Versión del servidor: 10.6.24-MariaDB
-- Versión de PHP: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cedhinue_biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modules`
--

INSERT INTO `modules` (`id`, `module_name`) VALUES
(1, 'Biblioteca Virtual'),
(2, 'Repositorio de Planes de Negocios'),
(3, 'Repositorios Externos'),
(4, 'Sala de Lectura');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `module_admins`
--

CREATE TABLE `module_admins` (
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `module_admins`
--

INSERT INTO `module_admins` (`user_id`, `module_id`) VALUES
(44, 2),
(45, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role` enum('owner','admin','bibliotecario','tutor','general_user') NOT NULL DEFAULT 'general_user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `google_id`, `first_name`, `last_name`, `email`, `role`, `created_at`, `estado`) VALUES
(44, NULL, 'Administrador1', '', 'sistemas@cedhinuevaarequipa.edu.pe', 'admin', '2025-11-04 22:13:46', 'activo'),
(45, NULL, 'Administrador2', '', 'cedhi@cedhinuevaarequipa.edu.pe', 'admin', '2025-11-04 22:13:46', 'activo'),
(46, NULL, 'Bibliotecario', '', 'biblioteca@cedhinuevaarequipa.edu.pe', 'bibliotecario', '2025-11-04 22:13:46', 'activo'),
(47, NULL, 'Nadia Jessica', 'Condori Mamani', 'ncondori@cedhinuevaarequipa.edu.pe', 'bibliotecario', '2025-11-04 22:13:46', 'activo'),
(48, '107379357215629794672', 'Sandra María', 'MANUELO PUMA', 'smanuelo@cedhinuevaarequipa.edu.pe', 'bibliotecario', '2025-11-04 22:13:46', 'activo'),
(49, NULL, 'Cesar Augusto', 'Ccaya Sueros', 'cccacya@cedhinuevaarequipa.edu.pe', 'bibliotecario', '2025-11-04 22:13:46', 'activo'),
(50, '116663163840277099028', 'TEST', 'Sistemas CEDHI', 'test@cedhinuevaarequipa.edu.pe', 'owner', '2025-11-05 12:47:58', 'activo'),
(51, '110169052912022335038', 'César Mezzich', 'Ramírez Vargas', 'cramirez@cedhinuevaarequipa.edu.pe', 'general_user', '2025-11-11 18:39:59', 'activo'),
(52, '104370381968506161202', 'Yoel Cristian', 'Huarcaya Flores', '74247385@cedhinuevaarequipa.edu.pe', 'general_user', '2025-11-11 19:01:31', 'activo'),
(53, '103628553573565762773', 'ITZEL MELANIE', 'VILLAVICENCIO PAUCAR', '60864716@cedhinuevaarequipa.edu.pe', 'general_user', '2025-11-12 12:04:35', 'activo'),
(54, '104697768973802405609', 'Diana', 'Nuñez', 'dnunnez@cedhinuevaarequipa.edu.pe', 'general_user', '2025-11-26 13:56:18', 'activo'),
(55, '100553952534453807526', 'Yerely Mayely', 'Pino Quispe', '74214597@cedhinuevaarequipa.edu.pe', 'general_user', '2025-11-29 16:39:23', 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `module_admins`
--
ALTER TABLE `module_admins`
  ADD PRIMARY KEY (`user_id`,`module_id`),
  ADD KEY `fk_module` (`module_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
