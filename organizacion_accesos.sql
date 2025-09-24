-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-09-2025 a las 18:10:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `organizacion_accesos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accesos`
--

CREATE TABLE `accesos` (
  `Acceso_ID` int(11) NOT NULL,
  `Usuario_ID` int(11) NOT NULL,
  `Operador_Ingreso_ID` int(11) DEFAULT NULL,
  `Operador_Egreso_ID` int(11) DEFAULT NULL,
  `FechaHora_Entrada` datetime NOT NULL,
  `FechaHora_Salida` datetime DEFAULT NULL,
  `TipoAcceso` varchar(10) NOT NULL,
  `Motivo` varchar(160) DEFAULT NULL,
  `Estado_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `accesos`
--

INSERT INTO `accesos` (`Acceso_ID`, `Usuario_ID`, `Operador_Ingreso_ID`, `Operador_Egreso_ID`, `FechaHora_Entrada`, `FechaHora_Salida`, `TipoAcceso`, `Motivo`, `Estado_ID`) VALUES
(1, 2, NULL, NULL, '2025-09-14 23:13:08', '2025-09-15 01:13:08', 'INGRESO', NULL, 2),
(2, 3, NULL, NULL, '2025-09-15 21:05:36', '2025-09-15 21:13:01', 'EGRESO', NULL, 2),
(3, 4, NULL, NULL, '2025-09-15 23:06:29', '2025-09-15 23:27:43', 'EGRESO', NULL, 2),
(4, 4, NULL, NULL, '2025-09-15 23:06:29', NULL, 'INGRESO', 'Soporte Tecnico', 1),
(5, 6, NULL, NULL, '2025-09-16 10:52:02', '2025-09-16 10:52:27', 'EGRESO', NULL, 2),
(6, 7, 2, 2, '2025-09-16 11:23:22', '2025-09-16 11:24:50', 'EGRESO', NULL, 2),
(7, 8, 2, 2, '2025-09-16 12:21:04', '2025-09-16 12:21:26', 'EGRESO', NULL, 2),
(8, 9, 2, 2, '2025-09-17 19:06:41', '2025-09-17 19:07:20', 'EGRESO', NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `Alerta_ID` int(11) NOT NULL,
  `Usuario_ID` int(11) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `Motivo` varchar(160) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alertas`
--

INSERT INTO `alertas` (`Alerta_ID`, `Usuario_ID`, `FechaHora`, `Motivo`) VALUES
(1, 1, '2025-09-14 23:13:09', 'Ingreso sin egreso previo detectado'),
(2, 3, '2025-09-15 21:13:19', 'Egreso sin ingreso previo'),
(3, 5, '2025-09-15 23:26:55', 'Egreso sin ingreso previo'),
(4, 6, '2025-09-16 10:53:07', 'Egreso sin ingreso previo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `Direccion_ID` int(11) NOT NULL,
  `Calle` varchar(120) NOT NULL,
  `Altura` varchar(10) NOT NULL,
  `Localidad_ID` int(11) NOT NULL,
  `Codigo_Postal` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`Direccion_ID`, `Calle`, `Altura`, `Localidad_ID`, `Codigo_Postal`) VALUES
(1, 'Av. Mitre', '750', 1, 'B1870'),
(2, 'Güemes', '1450', 1, 'B1870'),
(3, 'Florencio Varela', '100', 1, 'B1870'),
(4, '9 de Julio', '123', 2, 'B1824'),
(5, 'Hipólito Yrigoyen', '4500', 2, 'B1824'),
(6, 'Sarmiento', '980', 2, 'B1824'),
(7, 'Bv. Oroño', '1234', 3, 'S2000'),
(8, 'Av. Pellegrini', '2200', 3, 'S2000'),
(9, 'Av. Colón', '3456', 4, 'X5000'),
(10, 'General Paz', '150', 4, 'X5000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_acceso`
--

CREATE TABLE `estado_acceso` (
  `Estado_ID` int(11) NOT NULL,
  `Nombre` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_acceso`
--

INSERT INTO `estado_acceso` (`Estado_ID`, `Nombre`) VALUES
(1, 'EN_CURSO'),
(2, 'COMPLETADO'),
(3, 'RECHAZADO'),
(4, 'ANULADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `Localidad_ID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Provincia_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `localidades`
--

INSERT INTO `localidades` (`Localidad_ID`, `Nombre`, `Provincia_ID`) VALUES
(1, 'Avellaneda', 1),
(4, 'Córdoba', 6),
(2, 'Lanús', 1),
(3, 'Rosario', 21);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `requested_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `requested_ip`, `user_agent`, `created_at`) VALUES
(1, 2, 'a04914b4a4ca928bebe5f261e90a0fb7d309431db3e27953fcbe1b1519ec9fda', '2025-09-24 10:43:55', '2025-09-24 10:14:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 10:13:55'),
(2, 2, 'b04beb1c5877fdbeddc866a6ebaeca1f97c9e33a817ce81df4af570d123b2f5d', '2025-09-24 12:33:14', '2025-09-24 12:03:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:03:14'),
(3, 2, 'c1e9b27b9fef6c2083d379524e3450fd8c401828c6d40ad4fe130b1025fb1987', '2025-09-24 12:34:00', '2025-09-24 12:17:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:04:00'),
(4, 2, '589a18e1ad120365b6af2fbd0d8ac6be9d676f3adadb404617240fcbc45b0ee5', '2025-09-24 12:47:50', '2025-09-24 12:18:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:17:50'),
(5, 2, 'e21deb7b105cf2bc402287bf5df50055cb97cf5ba32688ad66e65bed363a13a5', '2025-09-24 12:49:36', '2025-09-24 12:20:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:19:36'),
(6, 2, '473bcb2db9e7907aaab050eed2148cc1c3af9e264d807b92fb9e7ed474eb2448', '2025-09-24 12:50:01', '2025-09-24 12:20:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:20:01'),
(7, 10, 'd7ebcd9edfcdaad1491c9b58e07035bf70aada8b37d76354b27a7daeed7509cd', '2025-09-24 13:09:24', '2025-09-24 12:39:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:39:24'),
(8, 2, '8f16e58e7733593fea311318b51a6afe6acfb9a2613bce5f72b85723dd900109', '2025-09-24 13:17:57', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:47:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincias`
--

CREATE TABLE `provincias` (
  `Provincia_ID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `provincias`
--

INSERT INTO `provincias` (`Provincia_ID`, `Nombre`) VALUES
(1, 'Buenos Aires'),
(3, 'Catamarca'),
(4, 'Chaco'),
(5, 'Chubut'),
(2, 'Ciudad Autónoma de Buenos Aires'),
(6, 'Córdoba'),
(7, 'Corrientes'),
(8, 'Entre Ríos'),
(9, 'Formosa'),
(10, 'Jujuy'),
(11, 'La Pampa'),
(12, 'La Rioja'),
(13, 'Mendoza'),
(14, 'Misiones'),
(15, 'Neuquén'),
(16, 'Río Negro'),
(17, 'Salta'),
(18, 'San Juan'),
(19, 'San Luis'),
(20, 'Santa Cruz'),
(21, 'Santa Fe'),
(22, 'Santiago del Estero'),
(23, 'Tierra del Fuego, Antártida e Islas del Atlántico Sur'),
(24, 'Tucumán');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `Reporte_ID` int(11) NOT NULL,
  `Usuario_ID` int(11) NOT NULL,
  `FechaIni` date NOT NULL,
  `FechaFin` date NOT NULL,
  `TipoReporte` varchar(50) NOT NULL,
  `Formato` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reportes`
--

INSERT INTO `reportes` (`Reporte_ID`, `Usuario_ID`, `FechaIni`, `FechaFin`, `TipoReporte`, `Formato`) VALUES
(1, 2, '2025-09-07', '2025-09-14', 'GENERAL', 'PDF'),
(2, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(3, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(4, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'PDF'),
(5, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'PDF'),
(6, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(7, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(8, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(9, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(10, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(11, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'CSV'),
(12, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'PDF'),
(13, 1, '2025-09-08', '2025-09-15', 'GENERAL', 'PDF'),
(14, 1, '2025-09-09', '2025-09-16', 'GENERAL', 'CSV'),
(15, 1, '2025-09-09', '2025-09-16', 'GENERAL', 'CSV'),
(16, 1, '2025-09-09', '2025-09-16', 'GENERAL', 'PDF'),
(17, 1, '2025-09-09', '2025-09-16', 'GENERAL', 'PDF'),
(18, 1, '2025-09-10', '2025-09-17', 'POR_INGRESO', 'CSV');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `Rol_ID` int(11) NOT NULL,
  `Nombre` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`Rol_ID`, `Nombre`) VALUES
(2, 'seguridad'),
(1, 'superusuario'),
(3, 'usuario_app');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefonos`
--

CREATE TABLE `telefonos` (
  `Telefono_ID` int(11) NOT NULL,
  `Usuario_ID` int(11) NOT NULL,
  `Numero` varchar(25) NOT NULL,
  `Tipo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `telefonos`
--

INSERT INTO `telefonos` (`Telefono_ID`, `Usuario_ID`, `Numero`, `Tipo`) VALUES
(1, 2, '11-5555-0001', 'CELULAR'),
(2, 2, '11-5555-0002', 'LABORAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documento`
--

CREATE TABLE `tipo_documento` (
  `TipoDoc_ID` int(11) NOT NULL,
  `Nombre` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_documento`
--

INSERT INTO `tipo_documento` (`TipoDoc_ID`, `Nombre`) VALUES
(1, 'DNI'),
(4, 'LC'),
(3, 'LE'),
(2, 'Pasaporte');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `TipoUsuario_ID` int(11) NOT NULL,
  `Nombre` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`TipoUsuario_ID`, `Nombre`) VALUES
(9, 'Administración'),
(1, 'Alumno'),
(2, 'Alumno FP'),
(4, 'Dirección'),
(3, 'Docente'),
(5, 'Gestión'),
(6, 'Invitado'),
(7, 'Mantenimiento'),
(8, 'Seguridad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `Usuario_ID` int(11) NOT NULL,
  `Nombre` varchar(80) NOT NULL,
  `Apellido` varchar(80) NOT NULL,
  `Correo` varchar(120) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Num_Documento` varchar(20) NOT NULL,
  `Fecha_Registro` date NOT NULL,
  `TipoDoc_ID` int(11) NOT NULL,
  `Direccion_ID` int(11) DEFAULT NULL,
  `Rol_ID` int(11) DEFAULT NULL,
  `TipoUsuario_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`Usuario_ID`, `Nombre`, `Apellido`, `Correo`, `Password`, `Num_Documento`, `Fecha_Registro`, `TipoDoc_ID`, `Direccion_ID`, `Rol_ID`, `TipoUsuario_ID`) VALUES
(1, 'Admin', 'ITB', 'admin@itb.com', 'admin123', '30111222', '2025-09-14', 1, NULL, 1, 9),
(2, 'Carlos', 'Guard', 'seguridad@itb.com', 'seguridad123', '30222333', '2025-09-14', 1, 1, 2, 8),
(3, 'Francisco', 'Martinez', NULL, NULL, '45996300', '0000-00-00', 1, NULL, NULL, 6),
(4, 'Martina', 'Echeverria', NULL, NULL, '41558222', '2025-09-15', 2, NULL, NULL, 6),
(5, 'Martina', 'Echeverria', NULL, NULL, '41558222', '2025-09-15', 1, NULL, NULL, 6),
(6, 'Julian', 'Correa', NULL, NULL, '46885112', '2025-09-16', 1, NULL, NULL, 6),
(7, 'Mariano', 'Martinez', NULL, NULL, '41226553', '2025-09-16', 1, NULL, NULL, 6),
(8, 'Pedro', 'Gomez', NULL, NULL, '47630250', '2025-09-16', 1, NULL, NULL, 6),
(9, 'Nacho', 'Fernandez', NULL, NULL, '49668502', '2025-09-17', 1, NULL, NULL, 6),
(10, 'Lucas', 'Cecenarro', 'cecenarro08@gmail.com', 'lucas1234', '1', '2025-09-24', 1, NULL, 2, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `accesos`
--
ALTER TABLE `accesos`
  ADD PRIMARY KEY (`Acceso_ID`),
  ADD KEY `fk_accesos_usuarios` (`Usuario_ID`),
  ADD KEY `fk_accesos_estado` (`Estado_ID`),
  ADD KEY `fk_accesos_op_ing` (`Operador_Ingreso_ID`),
  ADD KEY `fk_accesos_op_egr` (`Operador_Egreso_ID`);

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`Alerta_ID`),
  ADD KEY `fk_alertas_usuarios` (`Usuario_ID`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`Direccion_ID`),
  ADD KEY `fk_direcciones_localidades` (`Localidad_ID`);

--
-- Indices de la tabla `estado_acceso`
--
ALTER TABLE `estado_acceso`
  ADD PRIMARY KEY (`Estado_ID`);

--
-- Indices de la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD PRIMARY KEY (`Localidad_ID`),
  ADD UNIQUE KEY `uk_localidades_nombre_provincia` (`Nombre`,`Provincia_ID`),
  ADD KEY `fk_localidades_provincias` (`Provincia_ID`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_password_resets_token` (`token_hash`),
  ADD KEY `idx_password_resets_user_used` (`user_id`,`used_at`),
  ADD KEY `idx_password_resets_expires` (`expires_at`);

--
-- Indices de la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`Provincia_ID`),
  ADD UNIQUE KEY `uk_provincias_nombre` (`Nombre`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`Reporte_ID`),
  ADD KEY `fk_reportes_usuarios` (`Usuario_ID`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`Rol_ID`),
  ADD UNIQUE KEY `uk_roles_nombre` (`Nombre`);

--
-- Indices de la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD PRIMARY KEY (`Telefono_ID`),
  ADD UNIQUE KEY `uk_telefonos_usuario_numero` (`Usuario_ID`,`Numero`);

--
-- Indices de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  ADD PRIMARY KEY (`TipoDoc_ID`),
  ADD UNIQUE KEY `uk_tipo_documento_nombre` (`Nombre`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`TipoUsuario_ID`),
  ADD UNIQUE KEY `uk_tipo_usuario_nombre` (`Nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`Usuario_ID`),
  ADD UNIQUE KEY `uk_usuarios_tipodoc_numdoc` (`TipoDoc_ID`,`Num_Documento`),
  ADD KEY `fk_usuarios_tipo_usuario` (`TipoUsuario_ID`),
  ADD KEY `fk_usuarios_roles` (`Rol_ID`),
  ADD KEY `fk_usuarios_direcciones` (`Direccion_ID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `accesos`
--
ALTER TABLE `accesos`
  MODIFY `Acceso_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `Alerta_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `Direccion_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `estado_acceso`
--
ALTER TABLE `estado_acceso`
  MODIFY `Estado_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `Localidad_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `provincias`
--
ALTER TABLE `provincias`
  MODIFY `Provincia_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `Reporte_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `Rol_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `telefonos`
--
ALTER TABLE `telefonos`
  MODIFY `Telefono_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  MODIFY `TipoDoc_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `TipoUsuario_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `Usuario_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `accesos`
--
ALTER TABLE `accesos`
  ADD CONSTRAINT `fk_accesos_estado` FOREIGN KEY (`Estado_ID`) REFERENCES `estado_acceso` (`Estado_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accesos_op_egr` FOREIGN KEY (`Operador_Egreso_ID`) REFERENCES `usuarios` (`Usuario_ID`),
  ADD CONSTRAINT `fk_accesos_op_ing` FOREIGN KEY (`Operador_Ingreso_ID`) REFERENCES `usuarios` (`Usuario_ID`),
  ADD CONSTRAINT `fk_accesos_usuarios` FOREIGN KEY (`Usuario_ID`) REFERENCES `usuarios` (`Usuario_ID`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `fk_alertas_usuarios` FOREIGN KEY (`Usuario_ID`) REFERENCES `usuarios` (`Usuario_ID`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `fk_direcciones_localidades` FOREIGN KEY (`Localidad_ID`) REFERENCES `localidades` (`Localidad_ID`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD CONSTRAINT `fk_localidades_provincias` FOREIGN KEY (`Provincia_ID`) REFERENCES `provincias` (`Provincia_ID`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`Usuario_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reportes_usuarios` FOREIGN KEY (`Usuario_ID`) REFERENCES `usuarios` (`Usuario_ID`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD CONSTRAINT `fk_telefonos_usuarios` FOREIGN KEY (`Usuario_ID`) REFERENCES `usuarios` (`Usuario_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_direcciones` FOREIGN KEY (`Direccion_ID`) REFERENCES `direcciones` (`Direccion_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`Rol_ID`) REFERENCES `roles` (`Rol_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_tipo_documento` FOREIGN KEY (`TipoDoc_ID`) REFERENCES `tipo_documento` (`TipoDoc_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_tipo_usuario` FOREIGN KEY (`TipoUsuario_ID`) REFERENCES `tipo_usuario` (`TipoUsuario_ID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
