-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3307
-- Tiempo de generación: 27-02-2026 a las 19:59:29
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `actas_constitutivas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actas`
--

CREATE TABLE `actas` (
  `id_acta` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `ubicacion_fisica` varchar(100) DEFAULT NULL,
  `foto_portada` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actas`
--

INSERT INTO `actas` (`id_acta`, `id_empresa`, `id_tipo`, `ubicacion_fisica`, `foto_portada`) VALUES
(3, 4, NULL, 'oficina', 'uploads/actas/acta_1770917477.jpeg'),
(5, 1, 4, 'oficina', 'uploads/actas/acta_1770415766.png'),
(9, 4, 2, 'oficina', 'uploads/actas/acta_1771971284.png'),
(10, 1, 3, 'oficina', 'uploads/actas/acta_1771976341.jpeg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(150) NOT NULL,
  `rfc` varchar(20) DEFAULT NULL,
  `fecha_constitucion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `nombre_empresa`, `rfc`, `fecha_constitucion`) VALUES
(1, 'Dimobi', 'RDL0904102F41', '2026-02-05'),
(4, 'Maya Kan', 'RDL0904101111', '2026-02-12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `id_acta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_solicitud` datetime NOT NULL,
  `fecha_prestamo` datetime DEFAULT NULL,
  `fecha_devolucion` datetime DEFAULT NULL,
  `estado` enum('pendiente','prestado','devolucion_pendiente','devuelto','rechazado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `id_acta`, `id_usuario`, `fecha_solicitud`, `fecha_prestamo`, `fecha_devolucion`, `estado`) VALUES
(3, 3, 2, '2026-02-05 16:35:29', '2026-02-11 12:57:37', '2026-02-11 15:13:27', 'devuelto'),
(4, 3, 3, '2026-02-11 12:59:33', '2026-02-11 13:00:05', '2026-02-11 13:52:39', 'devuelto'),
(5, 5, 2, '2026-02-19 17:13:57', '2026-02-19 17:14:35', '2026-02-27 11:40:55', 'devuelto'),
(6, 3, 2, '2026-02-24 17:03:45', NULL, NULL, 'rechazado'),
(7, 10, 2, '2026-02-25 17:18:19', NULL, NULL, 'rechazado'),
(8, 9, 2, '2026-02-25 17:35:17', NULL, NULL, 'rechazado'),
(9, 10, 2, '2026-02-25 17:35:50', NULL, NULL, 'rechazado'),
(10, 10, 2, '2026-02-27 11:40:25', '2026-02-27 11:40:44', '2026-02-27 11:42:47', 'devuelto'),
(11, 5, 2, '2026-02-27 11:41:16', NULL, NULL, 'rechazado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_acta`
--

CREATE TABLE `tipos_acta` (
  `id_tipo` int(11) NOT NULL,
  `nombre_tipo` varchar(100) NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_acta`
--

INSERT INTO `tipos_acta` (`id_tipo`, `nombre_tipo`, `activo`) VALUES
(1, 'Acta Constitutiva', 1),
(2, 'Acta de Asamblea', 1),
(3, 'Acta de Modificación', 1),
(4, 'Acta de Poder Notarial', 1),
(5, 'prueba', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `departamento` enum('Contabilidad','Facturación','Sistemas','Nominas','Fiscal') NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `rol` enum('admin','usuario') NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `departamento`, `usuario`, `password`, `rol`, `activo`) VALUES
(1, NULL, 'Contabilidad', 'admin', '1234', 'admin', 1),
(2, 'Jorge Trejo', 'Contabilidad', 'jtrejo', 'jtrejo', 'usuario', 1),
(3, 'Fernando Gomez', 'Facturación', 'fgomez', 'fgomez', 'usuario', 1),
(7, 'prueba', 'Facturación', 'prueba', '123', 'usuario', 0),
(8, 'prueba1', 'Sistemas', 'prueba1', '123', 'usuario', 1),
(9, 'prueba2', 'Nominas', 'prueba2', '123', 'usuario', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actas`
--
ALTER TABLE `actas`
  ADD PRIMARY KEY (`id_acta`),
  ADD KEY `fk_acta_empresa` (`id_empresa`),
  ADD KEY `fk_tipo_acta` (`id_tipo`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `fk_prestamo_acta` (`id_acta`),
  ADD KEY `fk_prestamo_usuario` (`id_usuario`);

--
-- Indices de la tabla `tipos_acta`
--
ALTER TABLE `tipos_acta`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `nombre_tipo` (`nombre_tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actas`
--
ALTER TABLE `actas`
  MODIFY `id_acta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tipos_acta`
--
ALTER TABLE `tipos_acta`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actas`
--
ALTER TABLE `actas`
  ADD CONSTRAINT `fk_acta_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tipo_acta` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_acta` (`id_tipo`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `fk_prestamo_acta` FOREIGN KEY (`id_acta`) REFERENCES `actas` (`id_acta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prestamo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
