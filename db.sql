-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-01-2026 a las 23:51:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `recaudacion_arbitrios`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anios_fiscales`
--

CREATE TABLE `anios_fiscales` (
  `anio` int(11) NOT NULL,
  `valor_uit` decimal(10,2) NOT NULL,
  `interes_moratorio_diario` decimal(10,5) DEFAULT 0.03300,
  `estado` enum('PLANIFICACION','ACTIVO','CERRADO') DEFAULT 'PLANIFICACION'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anios_fiscales`
--

INSERT INTO `anios_fiscales` (`anio`, `valor_uit`, `interes_moratorio_diario`, `estado`) VALUES
(2020, 4300.00, 0.03300, 'CERRADO'),
(2021, 4400.00, 0.03300, 'CERRADO'),
(2022, 4600.00, 0.03300, 'CERRADO'),
(2023, 4950.00, 0.03300, 'CERRADO'),
(2024, 5150.00, 0.03300, 'CERRADO'),
(2025, 5350.00, 0.03300, 'CERRADO'),
(2026, 5500.00, 0.03300, 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_sesiones`
--

CREATE TABLE `caja_sesiones` (
  `id` int(11) NOT NULL,
  `usuario_cajero` varchar(50) NOT NULL,
  `fecha_apertura` datetime DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_recaudado` decimal(10,2) DEFAULT 0.00,
  `estado` enum('ABIERTA','CERRADA') DEFAULT 'ABIERTA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caja_sesiones`
--

INSERT INTO `caja_sesiones` (`id`, `usuario_cajero`, `fecha_apertura`, `fecha_cierre`, `monto_recaudado`, `estado`) VALUES
(1, 'Administrador Principal', '2026-01-08 16:57:03', NULL, 0.00, 'ABIERTA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contribuyentes`
--

CREATE TABLE `contribuyentes` (
  `id` int(11) NOT NULL,
  `tipo_persona` enum('NATURAL','JURIDICA') NOT NULL,
  `numero_documento` varchar(15) NOT NULL,
  `nombre_razon_social` varchar(200) NOT NULL,
  `direccion_fiscal` varchar(255) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `clave_web` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contribuyentes`
--

INSERT INTO `contribuyentes` (`id`, `tipo_persona`, `numero_documento`, `nombre_razon_social`, `direccion_fiscal`, `correo`, `telefono`, `clave_web`) VALUES
(1, 'NATURAL', '12345678', 'Juan Alberto Ramos Perez', 'Av. primero 1234', 'juan@gmail.com', '987654321', NULL),
(2, 'NATURAL', '87654321', 'Ramon Navarro Zarate', 'Las Gardenias A-1', 'ramon@gmail.com', '987654321', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cta_cte_tributaria`
--

CREATE TABLE `cta_cte_tributaria` (
  `id` bigint(20) NOT NULL,
  `anio` int(11) NOT NULL,
  `contribuyente_id` int(11) NOT NULL,
  `predio_id` int(11) NOT NULL,
  `tributo_concepto` enum('IMPUESTO_PREDIAL','LIMPIEZA_PUBLICA','SEGURIDAD_CIUDADANA') NOT NULL,
  `periodo` int(11) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto_insoluto` decimal(10,2) NOT NULL,
  `monto_pagado` decimal(10,2) DEFAULT 0.00,
  `estado` enum('PENDIENTE','PAGADO','FRACCIONADO','ANULADO') DEFAULT 'PENDIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cta_cte_tributaria`
--

INSERT INTO `cta_cte_tributaria` (`id`, `anio`, `contribuyente_id`, `predio_id`, `tributo_concepto`, `periodo`, `fecha_vencimiento`, `monto_insoluto`, `monto_pagado`, `estado`) VALUES
(57, 2025, 1, 2, 'IMPUESTO_PREDIAL', 1, '2025-02-28', 20.45, 20.45, 'PAGADO'),
(58, 2025, 1, 2, 'IMPUESTO_PREDIAL', 2, '2025-05-31', 20.45, 20.45, 'PAGADO'),
(59, 2025, 1, 2, 'IMPUESTO_PREDIAL', 3, '2025-08-31', 20.45, 20.45, 'PAGADO'),
(60, 2025, 1, 2, 'IMPUESTO_PREDIAL', 4, '2025-11-30', 20.45, 20.45, 'PAGADO'),
(61, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 1, '2025-01-31', 2.59, 2.59, 'PAGADO'),
(62, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 1, '2025-01-31', 1.43, 1.43, 'PAGADO'),
(63, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 2, '2025-02-28', 2.59, 2.59, 'PAGADO'),
(64, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 2, '2025-02-28', 1.43, 1.43, 'PAGADO'),
(65, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 3, '2025-03-31', 2.59, 2.59, 'PAGADO'),
(66, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 3, '2025-03-31', 1.43, 1.43, 'PAGADO'),
(67, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 4, '2025-04-30', 2.59, 2.59, 'PAGADO'),
(68, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 4, '2025-04-30', 1.43, 1.43, 'PAGADO'),
(69, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 5, '2025-05-31', 2.59, 2.59, 'PAGADO'),
(70, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 5, '2025-05-31', 1.43, 1.43, 'PAGADO'),
(71, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 6, '2025-06-30', 2.59, 2.59, 'PAGADO'),
(72, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 6, '2025-06-30', 1.43, 1.43, 'PAGADO'),
(73, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 7, '2025-07-31', 2.59, 2.59, 'PAGADO'),
(74, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 7, '2025-07-31', 1.43, 1.43, 'PAGADO'),
(75, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 8, '2025-08-31', 2.59, 2.59, 'PAGADO'),
(76, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 8, '2025-08-31', 1.43, 1.43, 'PAGADO'),
(77, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 9, '2025-09-30', 2.59, 2.59, 'PAGADO'),
(78, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 9, '2025-09-30', 1.43, 1.43, 'PAGADO'),
(79, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 10, '2025-10-31', 2.59, 2.59, 'PAGADO'),
(80, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 10, '2025-10-31', 1.43, 1.43, 'PAGADO'),
(81, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 11, '2025-11-30', 2.59, 2.59, 'PAGADO'),
(82, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 11, '2025-11-30', 1.43, 1.43, 'PAGADO'),
(83, 2025, 1, 2, 'LIMPIEZA_PUBLICA', 12, '2025-12-31', 2.59, 2.59, 'PAGADO'),
(84, 2025, 1, 2, 'SEGURIDAD_CIUDADANA', 12, '2025-12-31', 1.43, 1.43, 'PAGADO'),
(85, 2025, 2, 3, 'IMPUESTO_PREDIAL', 1, '2025-02-28', 32.63, 32.63, 'PAGADO'),
(86, 2025, 2, 3, 'IMPUESTO_PREDIAL', 2, '2025-05-31', 32.63, 32.63, 'PAGADO'),
(87, 2025, 2, 3, 'IMPUESTO_PREDIAL', 3, '2025-08-31', 32.63, 32.63, 'PAGADO'),
(88, 2025, 2, 3, 'IMPUESTO_PREDIAL', 4, '2025-11-30', 32.63, 32.63, 'PAGADO'),
(89, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 1, '2025-01-31', 3.54, 3.54, 'PAGADO'),
(90, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 1, '2025-01-31', 1.43, 1.43, 'PAGADO'),
(91, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 2, '2025-02-28', 3.54, 3.54, 'PAGADO'),
(92, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 2, '2025-02-28', 1.43, 1.43, 'PAGADO'),
(93, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 3, '2025-03-31', 3.54, 3.54, 'PAGADO'),
(94, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 3, '2025-03-31', 1.43, 1.43, 'PAGADO'),
(95, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 4, '2025-04-30', 3.54, 3.54, 'PAGADO'),
(96, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 4, '2025-04-30', 1.43, 1.43, 'PAGADO'),
(97, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 5, '2025-05-31', 3.54, 3.54, 'PAGADO'),
(98, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 5, '2025-05-31', 1.43, 1.43, 'PAGADO'),
(99, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 6, '2025-06-30', 3.54, 3.54, 'PAGADO'),
(100, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 6, '2025-06-30', 1.43, 1.43, 'PAGADO'),
(101, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 7, '2025-07-31', 3.54, 3.54, 'PAGADO'),
(102, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 7, '2025-07-31', 1.43, 1.43, 'PAGADO'),
(103, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 8, '2025-08-31', 3.54, 3.54, 'PAGADO'),
(104, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 8, '2025-08-31', 1.43, 1.43, 'PAGADO'),
(105, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 9, '2025-09-30', 3.54, 3.54, 'PAGADO'),
(106, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 9, '2025-09-30', 1.43, 1.43, 'PAGADO'),
(107, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 10, '2025-10-31', 3.54, 3.54, 'PAGADO'),
(108, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 10, '2025-10-31', 1.43, 1.43, 'PAGADO'),
(109, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 11, '2025-11-30', 3.54, 3.54, 'PAGADO'),
(110, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 11, '2025-11-30', 1.43, 1.43, 'PAGADO'),
(111, 2025, 2, 3, 'LIMPIEZA_PUBLICA', 12, '2025-12-31', 3.54, 3.54, 'PAGADO'),
(112, 2025, 2, 3, 'SEGURIDAD_CIUDADANA', 12, '2025-12-31', 1.43, 1.43, 'PAGADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `predios`
--

CREATE TABLE `predios` (
  `id` int(11) NOT NULL,
  `contribuyente_id` int(11) NOT NULL,
  `anio_declaracion` int(11) NOT NULL,
  `codigo_catastral` varchar(20) DEFAULT NULL,
  `direccion_predio` varchar(255) NOT NULL,
  `tipo_uso_id` int(11) NOT NULL,
  `area_construida` decimal(10,2) NOT NULL DEFAULT 0.00,
  `longitud_frente` decimal(10,2) DEFAULT 0.00,
  `valor_autovaluo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('ACTIVO','INACTIVO','FISCALIZACION') DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `predios`
--

INSERT INTO `predios` (`id`, `contribuyente_id`, `anio_declaracion`, `codigo_catastral`, `direccion_predio`, `tipo_uso_id`, `area_construida`, `longitud_frente`, `valor_autovaluo`, `estado`) VALUES
(2, 1, 2025, '00000001N', 'Av. Primero 1234', 1, 135.00, 8.00, 40900.00, 'ACTIVO'),
(3, 2, 2025, '00000002N', 'Las gardenias A-1', 1, 215.00, 15.00, 65250.00, 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos`
--

CREATE TABLE `recibos` (
  `id` bigint(20) NOT NULL,
  `anio_fiscal` int(11) NOT NULL,
  `contribuyente_id` int(11) NOT NULL,
  `caja_sesion_id` int(11) DEFAULT NULL,
  `numero_serie` varchar(10) NOT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `origen_pago` enum('VENTANILLA_MUNICIPAL','WEB_ONLINE','APP_MOVIL','BANCO_CONVENIO') NOT NULL,
  `metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA') NOT NULL,
  `codigo_operacion_bancaria` varchar(50) DEFAULT NULL,
  `estado` enum('VALIDO','ANULADO') DEFAULT 'VALIDO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recibos`
--

INSERT INTO `recibos` (`id`, `anio_fiscal`, `contribuyente_id`, `caja_sesion_id`, `numero_serie`, `fecha_pago`, `monto_total`, `origen_pago`, `metodo_pago`, `codigo_operacion_bancaria`, `estado`) VALUES
(1, 2025, 1, NULL, '001-000001', '2026-01-08 17:15:57', 137.60, 'VENTANILLA_MUNICIPAL', 'EFECTIVO', NULL, 'VALIDO'),
(2, 2025, 2, NULL, '001-000002', '2026-01-08 17:24:55', 201.22, 'VENTANILLA_MUNICIPAL', 'EFECTIVO', NULL, 'VALIDO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos_detalle`
--

CREATE TABLE `recibos_detalle` (
  `id` bigint(20) NOT NULL,
  `recibo_id` bigint(20) NOT NULL,
  `deuda_id` bigint(20) NOT NULL,
  `monto_capital` decimal(10,2) NOT NULL,
  `monto_mora` decimal(10,2) DEFAULT 0.00,
  `monto_gastos` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recibos_detalle`
--

INSERT INTO `recibos_detalle` (`id`, `recibo_id`, `deuda_id`, `monto_capital`, `monto_mora`, `monto_gastos`) VALUES
(1, 1, 62, 1.43, 0.16, 0.00),
(2, 1, 57, 20.45, 2.12, 0.00),
(3, 1, 61, 2.59, 0.29, 0.00),
(4, 1, 63, 2.59, 0.27, 0.00),
(5, 1, 58, 20.45, 1.50, 0.00),
(6, 1, 64, 1.43, 0.15, 0.00),
(7, 1, 59, 20.45, 0.88, 0.00),
(8, 1, 65, 2.59, 0.24, 0.00),
(9, 1, 66, 1.43, 0.13, 0.00),
(10, 1, 67, 2.59, 0.22, 0.00),
(11, 1, 68, 1.43, 0.12, 0.00),
(12, 1, 60, 20.45, 0.26, 0.00),
(13, 1, 69, 2.59, 0.19, 0.00),
(14, 1, 70, 1.43, 0.10, 0.00),
(15, 1, 72, 1.43, 0.09, 0.00),
(16, 1, 71, 2.59, 0.16, 0.00),
(17, 1, 73, 2.59, 0.14, 0.00),
(18, 1, 74, 1.43, 0.08, 0.00),
(19, 1, 75, 2.59, 0.11, 0.00),
(20, 1, 76, 1.43, 0.06, 0.00),
(21, 1, 78, 1.43, 0.05, 0.00),
(22, 1, 77, 2.59, 0.09, 0.00),
(23, 1, 79, 2.59, 0.06, 0.00),
(24, 1, 80, 1.43, 0.03, 0.00),
(25, 1, 81, 2.59, 0.03, 0.00),
(26, 1, 82, 1.43, 0.02, 0.00),
(27, 1, 83, 2.59, 0.01, 0.00),
(28, 1, 84, 1.43, 0.00, 0.00),
(29, 2, 90, 1.43, 0.16, 0.00),
(30, 2, 85, 32.63, 3.38, 0.00),
(31, 2, 89, 3.54, 0.40, 0.00),
(32, 2, 91, 3.54, 0.37, 0.00),
(33, 2, 86, 32.63, 2.39, 0.00),
(34, 2, 92, 1.43, 0.15, 0.00),
(35, 2, 87, 32.63, 1.40, 0.00),
(36, 2, 93, 3.54, 0.33, 0.00),
(37, 2, 94, 1.43, 0.13, 0.00),
(38, 2, 95, 3.54, 0.30, 0.00),
(39, 2, 96, 1.43, 0.12, 0.00),
(40, 2, 88, 32.63, 0.42, 0.00),
(41, 2, 97, 3.54, 0.26, 0.00),
(42, 2, 98, 1.43, 0.10, 0.00),
(43, 2, 100, 1.43, 0.09, 0.00),
(44, 2, 99, 3.54, 0.22, 0.00),
(45, 2, 101, 3.54, 0.19, 0.00),
(46, 2, 102, 1.43, 0.08, 0.00),
(47, 2, 103, 3.54, 0.15, 0.00),
(48, 2, 104, 1.43, 0.06, 0.00),
(49, 2, 106, 1.43, 0.05, 0.00),
(50, 2, 105, 3.54, 0.12, 0.00),
(51, 2, 107, 3.54, 0.08, 0.00),
(52, 2, 108, 1.43, 0.03, 0.00),
(53, 2, 109, 3.54, 0.05, 0.00),
(54, 2, 110, 1.43, 0.02, 0.00),
(55, 2, 111, 3.54, 0.01, 0.00),
(56, 2, 112, 1.43, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasas_arbitrios_anuales`
--

CREATE TABLE `tasas_arbitrios_anuales` (
  `id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `tipo_uso_id` int(11) NOT NULL,
  `costo_limpieza_m2` decimal(10,4) DEFAULT 0.0000,
  `costo_limpieza_fijo` decimal(10,2) DEFAULT 0.00,
  `costo_seguridad_fijo` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tasas_arbitrios_anuales`
--

INSERT INTO `tasas_arbitrios_anuales` (`id`, `anio`, `tipo_uso_id`, `costo_limpieza_m2`, `costo_limpieza_fijo`, `costo_seguridad_fijo`) VALUES
(1, 2026, 1, 0.1500, 12.50, 18.00),
(2, 2026, 2, 0.3500, 35.00, 45.00),
(3, 2026, 3, 0.5000, 80.00, 120.00),
(4, 2026, 4, 0.0500, 5.00, 10.00),
(5, 2026, 5, 0.2000, 20.00, 30.00),
(11, 2020, 1, 0.1050, 8.75, 12.60),
(12, 2020, 2, 0.2450, 24.50, 31.50),
(13, 2020, 3, 0.3500, 56.00, 84.00),
(14, 2020, 4, 0.0350, 3.50, 7.00),
(15, 2020, 5, 0.1400, 14.00, 21.00),
(16, 2021, 1, 0.1125, 9.38, 13.50),
(17, 2021, 2, 0.2625, 26.25, 33.75),
(18, 2021, 3, 0.3750, 60.00, 90.00),
(19, 2021, 4, 0.0375, 3.75, 7.50),
(20, 2021, 5, 0.1500, 15.00, 22.50),
(21, 2022, 1, 0.1200, 10.00, 14.40),
(22, 2022, 2, 0.2800, 28.00, 36.00),
(23, 2022, 3, 0.4000, 64.00, 96.00),
(24, 2022, 4, 0.0400, 4.00, 8.00),
(25, 2022, 5, 0.1600, 16.00, 24.00),
(26, 2023, 1, 0.1275, 10.63, 15.30),
(27, 2023, 2, 0.2975, 29.75, 38.25),
(28, 2023, 3, 0.4250, 68.00, 102.00),
(29, 2023, 4, 0.0425, 4.25, 8.50),
(30, 2023, 5, 0.1700, 17.00, 25.50),
(31, 2024, 1, 0.1350, 11.25, 16.20),
(32, 2024, 2, 0.3150, 31.50, 40.50),
(33, 2024, 3, 0.4500, 72.00, 108.00),
(34, 2024, 4, 0.0450, 4.50, 9.00),
(35, 2024, 5, 0.1800, 18.00, 27.00),
(36, 2025, 1, 0.1425, 11.88, 17.10),
(37, 2025, 2, 0.3325, 33.25, 42.75),
(38, 2025, 3, 0.4750, 76.00, 114.00),
(39, 2025, 4, 0.0475, 4.75, 9.50),
(40, 2025, 5, 0.1900, 19.00, 28.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_uso_predio`
--

CREATE TABLE `tipos_uso_predio` (
  `id` int(11) NOT NULL,
  `codigo` varchar(10) DEFAULT NULL,
  `descripcion` varchar(100) NOT NULL,
  `factor_limpieza` decimal(5,2) DEFAULT 1.00,
  `factor_seguridad` decimal(5,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_uso_predio`
--

INSERT INTO `tipos_uso_predio` (`id`, `codigo`, `descripcion`, `factor_limpieza`, `factor_seguridad`) VALUES
(1, '01', 'CASA HABITACION', 1.00, 1.00),
(2, '02', 'COMERCIO', 2.50, 1.50),
(3, '03', 'INDUSTRIA', 3.00, 2.00),
(4, '04', 'TERRENO SIN CONSTRUIR', 0.50, 1.00),
(5, '05', 'OTROS FINES', 1.50, 1.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uit_historico`
--

CREATE TABLE `uit_historico` (
  `id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `uit_historico`
--

INSERT INTO `uit_historico` (`id`, `anio`, `valor`) VALUES
(1, 2020, 4300.00),
(2, 2021, 4400.00),
(3, 2022, 4600.00),
(4, 2023, 4950.00),
(5, 2024, 5150.00),
(6, 2025, 5350.00),
(7, 2026, 5500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('ADMIN','CAJERO','RENTAS') NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`, `estado`, `created_at`) VALUES
(1, 'Administrador Principal', 'admin', '$2y$10$eRK.c/yvdwS8D/sfxg00oOEgMI.LhydOj5uyiL.d/H5YiJK2Kxk2e', 'ADMIN', 'ACTIVO', '2026-01-08 15:02:28');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anios_fiscales`
--
ALTER TABLE `anios_fiscales`
  ADD PRIMARY KEY (`anio`);

--
-- Indices de la tabla `caja_sesiones`
--
ALTER TABLE `caja_sesiones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contribuyentes`
--
ALTER TABLE `contribuyentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `cta_cte_tributaria`
--
ALTER TABLE `cta_cte_tributaria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anio` (`anio`),
  ADD KEY `predio_id` (`predio_id`),
  ADD KEY `idx_busqueda_pagos` (`contribuyente_id`,`anio`,`estado`);

--
-- Indices de la tabla `predios`
--
ALTER TABLE `predios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contribuyente_id` (`contribuyente_id`),
  ADD KEY `tipo_uso_id` (`tipo_uso_id`),
  ADD KEY `anio_declaracion` (`anio_declaracion`);

--
-- Indices de la tabla `recibos`
--
ALTER TABLE `recibos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contribuyente_id` (`contribuyente_id`);

--
-- Indices de la tabla `recibos_detalle`
--
ALTER TABLE `recibos_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recibo_id` (`recibo_id`),
  ADD KEY `deuda_id` (`deuda_id`);

--
-- Indices de la tabla `tasas_arbitrios_anuales`
--
ALTER TABLE `tasas_arbitrios_anuales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `anio` (`anio`,`tipo_uso_id`),
  ADD KEY `tipo_uso_id` (`tipo_uso_id`);

--
-- Indices de la tabla `tipos_uso_predio`
--
ALTER TABLE `tipos_uso_predio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `uit_historico`
--
ALTER TABLE `uit_historico`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `anio` (`anio`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `caja_sesiones`
--
ALTER TABLE `caja_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `contribuyentes`
--
ALTER TABLE `contribuyentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cta_cte_tributaria`
--
ALTER TABLE `cta_cte_tributaria`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT de la tabla `predios`
--
ALTER TABLE `predios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `recibos`
--
ALTER TABLE `recibos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `recibos_detalle`
--
ALTER TABLE `recibos_detalle`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `tasas_arbitrios_anuales`
--
ALTER TABLE `tasas_arbitrios_anuales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `tipos_uso_predio`
--
ALTER TABLE `tipos_uso_predio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `uit_historico`
--
ALTER TABLE `uit_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cta_cte_tributaria`
--
ALTER TABLE `cta_cte_tributaria`
  ADD CONSTRAINT `cta_cte_tributaria_ibfk_1` FOREIGN KEY (`anio`) REFERENCES `anios_fiscales` (`anio`),
  ADD CONSTRAINT `cta_cte_tributaria_ibfk_2` FOREIGN KEY (`contribuyente_id`) REFERENCES `contribuyentes` (`id`),
  ADD CONSTRAINT `cta_cte_tributaria_ibfk_3` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`);

--
-- Filtros para la tabla `predios`
--
ALTER TABLE `predios`
  ADD CONSTRAINT `predios_ibfk_1` FOREIGN KEY (`contribuyente_id`) REFERENCES `contribuyentes` (`id`),
  ADD CONSTRAINT `predios_ibfk_2` FOREIGN KEY (`tipo_uso_id`) REFERENCES `tipos_uso_predio` (`id`),
  ADD CONSTRAINT `predios_ibfk_3` FOREIGN KEY (`anio_declaracion`) REFERENCES `anios_fiscales` (`anio`);

--
-- Filtros para la tabla `recibos`
--
ALTER TABLE `recibos`
  ADD CONSTRAINT `recibos_ibfk_1` FOREIGN KEY (`contribuyente_id`) REFERENCES `contribuyentes` (`id`);

--
-- Filtros para la tabla `recibos_detalle`
--
ALTER TABLE `recibos_detalle`
  ADD CONSTRAINT `recibos_detalle_ibfk_1` FOREIGN KEY (`recibo_id`) REFERENCES `recibos` (`id`),
  ADD CONSTRAINT `recibos_detalle_ibfk_2` FOREIGN KEY (`deuda_id`) REFERENCES `cta_cte_tributaria` (`id`);

--
-- Filtros para la tabla `tasas_arbitrios_anuales`
--
ALTER TABLE `tasas_arbitrios_anuales`
  ADD CONSTRAINT `tasas_arbitrios_anuales_ibfk_1` FOREIGN KEY (`anio`) REFERENCES `anios_fiscales` (`anio`),
  ADD CONSTRAINT `tasas_arbitrios_anuales_ibfk_2` FOREIGN KEY (`tipo_uso_id`) REFERENCES `tipos_uso_predio` (`id`);
COMMIT;
