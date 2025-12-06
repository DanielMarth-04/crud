-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 06-12-2025 a las 16:30:07
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `laboratorio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `id` int NOT NULL,
  `area` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `area`
--

INSERT INTO `area` (`id`, `area`, `descripcion`, `estado`) VALUES
(4, 'laboratorio de calibracion', 'calibracion de equipos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DniRuc` bigint NOT NULL,
  `correo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` int NOT NULL,
  `contacto` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombres`, `DniRuc`, `correo`, `telefono`, `contacto`, `direccion`, `estado`) VALUES
(8, 'Software y Hadware Ingenieros E.I.R.L', 20231363255, 'nicoleortiz0105@gmail.com', 952467185, 'Daniel Martinez', 'predio los arenales sub lote B1 C', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalleproforma`
--

CREATE TABLE `detalleproforma` (
  `id` int NOT NULL,
  `idproforma` int NOT NULL,
  `idservicio` int NOT NULL,
  `idtipo` int DEFAULT NULL,
  `idarea` int NOT NULL,
  `valor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalleproforma`
--

INSERT INTO `detalleproforma` (`id`, `idproforma`, `idservicio`, `idtipo`, `idarea`, `valor`) VALUES
(40, 31, 8, 6, 4, 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detgrec`
--

CREATE TABLE `detgrec` (
  `id` int NOT NULL,
  `idgrecepcion` int NOT NULL,
  `idtipo` int NOT NULL,
  `idexp` int NOT NULL DEFAULT '0',
  `descripcion` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codingr` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `feching` timestamp NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detgrec`
--

INSERT INTO `detgrec` (`id`, `idgrecepcion`, `idtipo`, `idexp`, `descripcion`, `codingr`, `feching`, `estado`) VALUES
(15, 50, 6, 7, 'FYUGY', '650', '2025-12-02 22:18:50', 'recepcionado'),
(16, 50, 7, 8, 'huidjbhb', '650', '2025-12-02 22:18:50', 'recepcionado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detotra`
--

CREATE TABLE `detotra` (
  `id` int NOT NULL,
  `idotrabajo` int NOT NULL,
  `idexp` int NOT NULL,
  `servicio` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codingre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fingreso` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` int NOT NULL,
  `firma` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombres`, `dni`, `firma`, `area`, `cargo`, `estado`) VALUES
(1, 'Daniel Martinez', 74868432, 'null', 'sistemas', 'programador', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expcal`
--

CREATE TABLE `expcal` (
  `id` int NOT NULL,
  `idtipo` int NOT NULL,
  `iddetotra` int NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fcreacion` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grecepcion`
--

CREATE TABLE `grecepcion` (
  `id` int NOT NULL,
  `idproforma` int NOT NULL,
  `idtrabajador` int NOT NULL,
  `idcliente` int NOT NULL,
  `costotal` float NOT NULL,
  `codigo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grecepcion`
--

INSERT INTO `grecepcion` (`id`, `idproforma`, `idtrabajador`, `idcliente`, `costotal`, `codigo`, `estado`) VALUES
(50, 31, 1, 8, 400, 'GR-2025-00001', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `otrabajo`
--

CREATE TABLE `otrabajo` (
  `id` int NOT NULL,
  `idproforma` int NOT NULL,
  `idcliente` int NOT NULL,
  `idempleado` int NOT NULL,
  `fecha` datetime NOT NULL,
  `descripcion` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `methodo` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codotr` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `otrabajo`
--

INSERT INTO `otrabajo` (`id`, `idproforma`, `idcliente`, `idempleado`, `fecha`, `descripcion`, `methodo`, `codotr`, `estado`) VALUES
(1, 31, 8, 1, '2025-12-04 00:00:00', 'SERVICIO DE CALIBRACIÓN ACREDITADO POR INACAL - DA', 'MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-017 \"PROCEDIMIENTO PARA LA CALIBRACIÓN DE TERMÓMETROS DIGITALES\" (2da ed., 2012)', 'OT-2025-00001', 1),
(2, 31, 8, 1, '2025-12-05 00:00:00', 'SERVICIO DE CALIBRACIÓN CONFORME A NTP ISO/IEC 17025', 'MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-026 \"PROCEDIMIENTO PARA LA CALIBRACIÓN DE HIGRÓMETROS Y TERMÓMETROS AMBIENTALES\" (1era ed., 2019)', 'OT-2025-00002', 1),
(3, 31, 8, 1, '2025-12-05 00:00:00', 'SERVICIO DE CALIBRACIÓN ACREDITADO POR INACAL - DA', 'MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-017 \"PROCEDIMIENTO PARA LA CALIBRACIÓN DE TERMÓMETROS DIGITALES\" (2da ed., 2012)', 'OT-2025-00003', 1),
(4, 31, 8, 1, '2025-12-05 00:00:00', 'SERVICIO DE CALIBRACIÓN ACREDITADO POR INACAL - DA', 'MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-026 \"PROCEDIMIENTO PARA LA CALIBRACIÓN DE HIGRÓMETROS Y TERMÓMETROS AMBIENTALES\" (1era ed., 2019)', 'OT-2025-00004', 1),
(5, 31, 8, 1, '2025-12-05 00:00:00', 'SERVICIO DE CALIBRACIÓN ACREDITADO POR INACAL - DA', 'MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-026 \"PROCEDIMIENTO PARA LA CALIBRACIÓN DE HIGRÓMETROS Y TERMÓMETROS AMBIENTALES\" (1era ed., 2019)', 'OT-2025-00005', 1),
(6, 31, 8, 1, '2025-12-05 00:00:00', 'SERVICIO DE CALIBRACIÓN ACREDITADO POR INACAL - DA', 'MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-026 \"PROCEDIMIENTO PARA LA CALIBRACIÓN DE HIGRÓMETROS Y TERMÓMETROS AMBIENTALES\" (1era ed., 2019)', 'OT-2025-00006', 1),
(7, 31, 8, 1, '2025-12-12 00:00:00', 'SERVICIO DE CALIBRACIÓN ACREDITADO POR INACAL - DA', 'MÉTODO DE COMPARACIÓN DIRECTA, SEGÚN EL PC-026 \"PROCEDIMIENTO PARA LA CALIBRACIÓN DE HIGRÓMETROS Y TERMÓMETROS AMBIENTALES\" (1era ed., 2019)', 'OT-2025-00007', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proforma`
--

CREATE TABLE `proforma` (
  `id` int NOT NULL,
  `idcliente` int NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` float NOT NULL,
  `fecha` timestamp NOT NULL,
  `estado` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proforma`
--

INSERT INTO `proforma` (`id`, `idcliente`, `codigo`, `precio`, `fecha`, `estado`) VALUES
(31, 8, 'PF-2025-00001', 50, '2025-12-02 22:18:04', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `roles` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `roles`, `estado`) VALUES
(1, 'administrador', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int NOT NULL,
  `idarea` int NOT NULL,
  `servicio` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `idarea`, `servicio`, `descripcion`, `estado`) VALUES
(8, 4, 'SERVICIO DE CALIBRACION DE THERMOHIDROMETRO DIGITAL', 'servicio de Calibracion de seraphines', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo`
--

CREATE TABLE `tipo` (
  `id` int NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo`
--

INSERT INTO `tipo` (`id`, `tipo`, `estado`) VALUES
(1, 'proformas', 1),
(4, 'Grecepcion', 1),
(5, 'Otrabajo', 1),
(6, 'instrumentos', 1),
(7, 'otros', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `idrol` int NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` int NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasenia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` bigint NOT NULL,
  `fecCreado` timestamp NOT NULL,
  `fecAct` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `idrol`, `nombres`, `apellidos`, `dni`, `usuario`, `contrasenia`, `estado`, `fecCreado`, `fecAct`) VALUES
(1, 1, 'Daniel ', 'Martinez', 74868432, 'admin', '$2y$10$P3arQ4tlJ27MqKG7.c0tb.VPdY5T65AHlkoP0vjg2Q06J3G9HPvZO', 1, '2025-10-13 18:03:59', '2025-10-13 18:03:59');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalleproforma`
--
ALTER TABLE `detalleproforma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proforma` (`idproforma`),
  ADD KEY `id_servicio` (`idservicio`),
  ADD KEY `fk_detalle_area` (`idarea`),
  ADD KEY `fk_detalle_tipo` (`idtipo`);

--
-- Indices de la tabla `detgrec`
--
ALTER TABLE `detgrec`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idgrecepcion` (`idgrecepcion`),
  ADD KEY `idtipo` (`idtipo`),
  ADD KEY `idtipo_2` (`idtipo`),
  ADD KEY `idcliente` (`idtipo`),
  ADD KEY `idtipo_3` (`idtipo`),
  ADD KEY `fk_detgrec_exp` (`idexp`);

--
-- Indices de la tabla `detotra`
--
ALTER TABLE `detotra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idotrabajo` (`idotrabajo`),
  ADD KEY `idexp` (`idexp`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `expcal`
--
ALTER TABLE `expcal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expcal_tipo` (`idtipo`),
  ADD KEY `idotra` (`iddetotra`);

--
-- Indices de la tabla `grecepcion`
--
ALTER TABLE `grecepcion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idtrabajador` (`idtrabajador`),
  ADD KEY `idcliente` (`idcliente`);

--
-- Indices de la tabla `otrabajo`
--
ALTER TABLE `otrabajo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idproforma` (`idproforma`),
  ADD KEY `idcliente` (`idcliente`),
  ADD KEY `idempleado` (`idempleado`);

--
-- Indices de la tabla `proforma`
--
ALTER TABLE `proforma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcliente` (`idcliente`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_servicios_area` (`idarea`);

--
-- Indices de la tabla `tipo`
--
ALTER TABLE `tipo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idrol` (`idrol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `area`
--
ALTER TABLE `area`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `detalleproforma`
--
ALTER TABLE `detalleproforma`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `detgrec`
--
ALTER TABLE `detgrec`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `detotra`
--
ALTER TABLE `detotra`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `expcal`
--
ALTER TABLE `expcal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `grecepcion`
--
ALTER TABLE `grecepcion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `otrabajo`
--
ALTER TABLE `otrabajo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `proforma`
--
ALTER TABLE `proforma`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tipo`
--
ALTER TABLE `tipo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalleproforma`
--
ALTER TABLE `detalleproforma`
  ADD CONSTRAINT `detalleproforma_ibfk_1` FOREIGN KEY (`idproforma`) REFERENCES `proforma` (`id`),
  ADD CONSTRAINT `fk_detalle_area` FOREIGN KEY (`idarea`) REFERENCES `area` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_tipo` FOREIGN KEY (`idtipo`) REFERENCES `tipo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detgrec`
--
ALTER TABLE `detgrec`
  ADD CONSTRAINT `detgrec_ibfk_1` FOREIGN KEY (`idgrecepcion`) REFERENCES `grecepcion` (`id`),
  ADD CONSTRAINT `detgrec_ibfk_3` FOREIGN KEY (`idtipo`) REFERENCES `tipo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detotra`
--
ALTER TABLE `detotra`
  ADD CONSTRAINT `detotra_ibfk_1` FOREIGN KEY (`idotrabajo`) REFERENCES `otrabajo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detotra_ibfk_2` FOREIGN KEY (`idexp`) REFERENCES `expcal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `expcal`
--
ALTER TABLE `expcal`
  ADD CONSTRAINT `fk_expcal_tipo` FOREIGN KEY (`idtipo`) REFERENCES `tipo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `grecepcion`
--
ALTER TABLE `grecepcion`
  ADD CONSTRAINT `grecepcion_ibfk_2` FOREIGN KEY (`idtrabajador`) REFERENCES `empleados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grecepcion_ibfk_3` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `otrabajo`
--
ALTER TABLE `otrabajo`
  ADD CONSTRAINT `otrabajo_ibfk_1` FOREIGN KEY (`idproforma`) REFERENCES `proforma` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `otrabajo_ibfk_2` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `otrabajo_ibfk_3` FOREIGN KEY (`idempleado`) REFERENCES `empleados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proforma`
--
ALTER TABLE `proforma`
  ADD CONSTRAINT `fk_proformas_clientes` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `fk_servicios_area` FOREIGN KEY (`idarea`) REFERENCES `area` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`idrol`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
