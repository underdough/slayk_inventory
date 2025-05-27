-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-05-2025 a las 06:25:50
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
-- Base de datos: `slayk`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categorias` int(3) NOT NULL,
  `nombre_cat` varchar(25) DEFAULT NULL,
  `subcategoria` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(2) NOT NULL,
  `numd_doc` int(2) DEFAULT NULL,
  `nombre_d` varchar(20) DEFAULT NULL,
  `apellido` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante`
--

CREATE TABLE `comprobante` (
  `id_comprobante` int(5) NOT NULL,
  `num_comprobante` varchar(10) DEFAULT NULL,
  `fecha_entrada` datetime DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `cliente` int(2) DEFAULT NULL,
  `direccion` varchar(40) DEFAULT NULL,
  `encargado` int(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles`
--

CREATE TABLE `detalles` (
  `id_detalles` int(4) DEFAULT NULL,
  `id_material` int(4) DEFAULT NULL,
  `id_comprobante` int(5) DEFAULT NULL,
  `cantidades` int(3) DEFAULT NULL,
  `stock_actual` int(3) DEFAULT NULL,
  `descripcion_prod` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id_documentos` int(6) NOT NULL,
  `fk_novedad` text DEFAULT NULL,
  `fk_material` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fk_materiales_proveedores`
--

CREATE TABLE `fk_materiales_proveedores` (
  `id_material` int(2) NOT NULL,
  `id_proveedor` int(2) NOT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `fecha_entrada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `implicados`
--

CREATE TABLE `implicados` (
  `id_implicados` int(6) NOT NULL,
  `doc_implicadosistema` int(12) DEFAULT NULL,
  `doc_implicadoexterno` int(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `id_material` int(4) NOT NULL,
  `id_categorias` int(2) DEFAULT NULL,
  `nombre_material` varchar(60) DEFAULT NULL,
  `stock` int(4) DEFAULT NULL,
  `disponibilidad` tinyint(1) DEFAULT NULL,
  `minimo_alarma` tinyint(1) DEFAULT NULL,
  `fk_reporte` int(3) DEFAULT NULL,
  `fk_ubicacion` int(4) DEFAULT NULL,
  `conf_recibido` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int(2) NOT NULL,
  `entidad` varchar(25) DEFAULT NULL,
  `id_material` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(3) NOT NULL,
  `fecha_actual` datetime DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_reporte` datetime DEFAULT NULL,
  `tipo_incidente` varchar(20) DEFAULT NULL,
  `notificador` varchar(20) DEFAULT NULL,
  `estado` varchar(10) DEFAULT NULL,
  `prioridad` varchar(5) DEFAULT NULL,
  `soporte` int(6) DEFAULT NULL,
  `fk_seguimiento` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_roles` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_roles`) VALUES
('administrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

CREATE TABLE `seguimiento` (
  `id_seguimiento` int(3) NOT NULL,
  `fk_reporte` varchar(40) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `devolucion` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--

CREATE TABLE `subcategorias` (
  `id_subcategorias` int(4) DEFAULT NULL,
  `fk_material` varchar(60) DEFAULT NULL,
  `nombre_subcategoria` varchar(35) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id_ubicaciones` int(4) NOT NULL,
  `nombre_ubicacion` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `num_doc` int(12) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(25) NOT NULL,
  `rol` varchar(12) NOT NULL,
  `cargos` varchar(30) NOT NULL,
  `correo` varchar(64) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `num_telefono` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`num_doc`, `nombre`, `apellido`, `rol`, `cargos`, `correo`, `contrasena`, `num_telefono`) VALUES
(100000001, 'Admin', 'Sistema', 'administrado', 'Administrador general', 'admin@inventario.com', '60fe74406e7f353ed979f350f2fbb6a2e8690a5fa7d1b0c32983d1d8b3f95f67', 1234567890);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categorias`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `comprobante`
--
ALTER TABLE `comprobante`
  ADD PRIMARY KEY (`id_comprobante`),
  ADD KEY `fk_comprobante_encargado` (`encargado`),
  ADD KEY `fk_comprobante_cliente` (`cliente`);

--
-- Indices de la tabla `detalles`
--
ALTER TABLE `detalles`
  ADD KEY `fk_detalles_comprobante` (`id_comprobante`),
  ADD KEY `fk_detalles_material` (`id_material`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documentos`),
  ADD KEY `fk_documentos_materiales` (`fk_material`);

--
-- Indices de la tabla `fk_materiales_proveedores`
--
ALTER TABLE `fk_materiales_proveedores`
  ADD PRIMARY KEY (`id_material`,`id_proveedor`),
  ADD KEY `fk_fk_materiales_proveedores_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `implicados`
--
ALTER TABLE `implicados`
  ADD PRIMARY KEY (`id_implicados`),
  ADD KEY `fk_implicados_usuarios` (`doc_implicadosistema`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id_material`),
  ADD UNIQUE KEY `nombre_material` (`nombre_material`),
  ADD KEY `fk_materiales_reportes` (`fk_reporte`),
  ADD KEY `fk_materiales_ubicaciones` (`fk_ubicacion`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reportes_seguimiento` (`fk_seguimiento`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_roles`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id_seguimiento`);

--
-- Indices de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD KEY `fk_subcategorias_material` (`fk_material`),
  ADD KEY `fk_subcategorias_categorias` (`id_subcategorias`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`id_ubicaciones`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`num_doc`),
  ADD KEY `fk_usuarios_roles` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `num_doc` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100000002;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comprobante`
--
ALTER TABLE `comprobante`
  ADD CONSTRAINT `fk_comprobante_cliente` FOREIGN KEY (`cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_comprobante_encargado` FOREIGN KEY (`encargado`) REFERENCES `usuarios` (`num_doc`);

--
-- Filtros para la tabla `detalles`
--
ALTER TABLE `detalles`
  ADD CONSTRAINT `fk_detalles_comprobante` FOREIGN KEY (`id_comprobante`) REFERENCES `comprobante` (`id_comprobante`),
  ADD CONSTRAINT `fk_detalles_material` FOREIGN KEY (`id_material`) REFERENCES `materiales` (`id_material`);

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `fk_documentos_materiales` FOREIGN KEY (`fk_material`) REFERENCES `materiales` (`id_material`);

--
-- Filtros para la tabla `fk_materiales_proveedores`
--
ALTER TABLE `fk_materiales_proveedores`
  ADD CONSTRAINT `fk_fk_materiales_proveedores_material` FOREIGN KEY (`id_material`) REFERENCES `materiales` (`id_material`),
  ADD CONSTRAINT `fk_fk_materiales_proveedores_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`);

--
-- Filtros para la tabla `implicados`
--
ALTER TABLE `implicados`
  ADD CONSTRAINT `fk_implicados_usuarios` FOREIGN KEY (`doc_implicadosistema`) REFERENCES `usuarios` (`num_doc`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `fk_materiales_reportes` FOREIGN KEY (`fk_reporte`) REFERENCES `reportes` (`id_reporte`),
  ADD CONSTRAINT `fk_materiales_ubicaciones` FOREIGN KEY (`fk_ubicacion`) REFERENCES `ubicaciones` (`id_ubicaciones`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reportes_seguimiento` FOREIGN KEY (`fk_seguimiento`) REFERENCES `seguimiento` (`id_seguimiento`);

--
-- Filtros para la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD CONSTRAINT `fk_subcategorias_categorias` FOREIGN KEY (`id_subcategorias`) REFERENCES `categorias` (`id_categorias`),
  ADD CONSTRAINT `fk_subcategorias_material` FOREIGN KEY (`fk_material`) REFERENCES `materiales` (`nombre_material`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`rol`) REFERENCES `roles` (`id_roles`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
