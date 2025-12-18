-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:8889
-- Tiempo de generación: 18-12-2025 a las 21:08:44
-- Versión del servidor: 8.0.40
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dulceria_pos`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_stock_venta` (IN `p_producto_id` INT, IN `p_cantidad` DECIMAL(10,3), IN `p_usuario_id` INT, IN `p_venta_id` INT)   BEGIN
    DECLARE v_stock_anterior DECIMAL(10,3);
    
    -- Obtener stock actual
    SELECT stock_actual INTO v_stock_anterior
    FROM productos
    WHERE id = p_producto_id;
    
    -- Actualizar stock
    UPDATE productos
    SET stock_actual = stock_actual - p_cantidad
    WHERE id = p_producto_id;
    
    -- Registrar movimiento
    INSERT INTO movimientos_inventario (
        producto_id, tipo_movimiento, cantidad, 
        stock_anterior, stock_nuevo, usuario_id, venta_id
    ) VALUES (
        p_producto_id, 'venta', p_cantidad,
        v_stock_anterior, v_stock_anterior - p_cantidad,
        p_usuario_id, p_venta_id
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `calcular_precio_granel` (IN `p_producto_id` INT, IN `p_peso_gramos` INT, OUT `p_precio` DECIMAL(10,2))   BEGIN
    SELECT precio_calculado INTO p_precio
    FROM precios_granel
    WHERE producto_id = p_producto_id AND peso_gramos = p_peso_gramos;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generar_folio_venta` (OUT `p_folio` VARCHAR(20))   BEGIN
    DECLARE v_numero INT;
    DECLARE v_fecha VARCHAR(8);
    
    SET v_fecha = DATE_FORMAT(NOW(), '%Y%m%d');
    
    SELECT IFNULL(MAX(CAST(SUBSTRING(folio, 10) AS UNSIGNED)), 0) + 1
    INTO v_numero
    FROM ventas
    WHERE folio LIKE CONCAT(v_fecha, '%');
    
    SET p_folio = CONCAT(v_fecha, '-', LPAD(v_numero, 5, '0'));
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `descripcion` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `icono` varchar(50) CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `icono`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Snacks', 'Papas, frituras y botanas saladas', '🥨', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(2, 'Bebidas', 'Refrescos, jugos y bebidas en general', '🥤', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(3, 'Dulces', 'Caramelos, gomitas y dulces variados', '🍬', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(4, 'Paletas', 'Paletas de diversos sabores', '🍭', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(5, 'Chocolate', 'Chocolates y productos con chocolate', '🍫', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(6, 'Chicles', 'Chicles y mentas', '🎈', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(7, 'Galletas', 'Galletas dulces y saladas', '🍪', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(8, 'Mazapanes', 'Mazapanes y dulces de cacahuate', '🥜', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(9, 'Enchilados', 'Dulces enchilados y picosos', '🌶️', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(10, 'Productos de temporada', 'Productos especiales por temporada', '🎃', 1, '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(11, 'Gomitas', 'Productos de Gomitas', '🐼', 1, '2025-12-03 20:02:45', '2025-12-03 20:02:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_impresora`
--

CREATE TABLE `configuracion_impresora` (
  `id` int NOT NULL,
  `nombre_impresora` varchar(255) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `habilitada` tinyint(1) DEFAULT '1',
  `auto_imprimir` tinyint(1) DEFAULT '0',
  `copias` int DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `configuracion_impresora`
--

INSERT INTO `configuracion_impresora` (`id`, `nombre_impresora`, `habilitada`, `auto_imprimir`, `copias`, `creado_en`, `actualizado_en`) VALUES
(1, 'STMicroelectronics_POS58_Printer_USB', 1, 0, 2, '2025-12-01 21:55:26', '2025-12-02 03:02:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cortes_caja`
--

CREATE TABLE `cortes_caja` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha_inicio` timestamp NOT NULL,
  `fecha_fin` timestamp NOT NULL,
  `total_ventas` decimal(10,2) NOT NULL,
  `total_efectivo` decimal(10,2) NOT NULL,
  `total_tarjeta` decimal(10,2) NOT NULL,
  `total_otros` decimal(10,2) NOT NULL,
  `num_transacciones` int NOT NULL,
  `observaciones` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_corte_granel`
--

CREATE TABLE `detalle_corte_granel` (
  `id` int NOT NULL,
  `corte_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `nombre_producto` varchar(200) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `total_kg_vendidos` decimal(10,3) NOT NULL,
  `bolsas_100g` int DEFAULT '0',
  `bolsas_250g` int DEFAULT '0',
  `bolsas_500g` int DEFAULT '0',
  `bolsas_1kg` int DEFAULT '0',
  `total_venta` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int NOT NULL,
  `venta_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `nombre_producto` varchar(200) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tipo_venta` enum('anaquel','granel','pieza') CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `peso_gramos` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id`, `venta_id`, `producto_id`, `nombre_producto`, `cantidad`, `precio_unitario`, `subtotal`, `tipo_venta`, `peso_gramos`, `created_at`) VALUES
(1, 1, 3, 'RING POP 12 pzs', 1.000, 138.32, 138.32, 'anaquel', NULL, '2025-12-02 19:01:49'),
(2, 1, 4, 'Paleta Super Rebanadita', 1.000, 9.15, 9.15, 'pieza', NULL, '2025-12-02 19:01:49'),
(3, 1, 2, 'Nueces de la India Premier', 1.000, 45.05, 45.05, 'granel', 100, '2025-12-02 19:01:49'),
(4, 1, 16, 'Surtido especial De la Rosa ', 1.000, 172.25, 172.25, 'anaquel', NULL, '2025-12-02 20:18:32');

--
-- Disparadores `detalle_ventas`
--
DELIMITER $$
CREATE TRIGGER `before_detalle_venta_insert` BEFORE INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
    DECLARE v_stock_actual DECIMAL(10,3);
    
    SELECT stock_actual INTO v_stock_actual
    FROM productos
    WHERE id = NEW.producto_id;
    
    IF v_stock_actual < NEW.cantidad THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock insuficiente para realizar la venta';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id` int NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id`, `nombre`, `activo`, `created_at`) VALUES
(1, 'Efectivo', 1, '2025-11-04 19:02:03'),
(2, 'Tarjeta', 1, '2025-11-04 19:02:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int NOT NULL,
  `producto_id` int NOT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste','venta','cancelacion') CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `stock_anterior` decimal(10,3) NOT NULL,
  `stock_nuevo` decimal(10,3) NOT NULL,
  `usuario_id` int NOT NULL,
  `venta_id` int DEFAULT NULL,
  `justificacion` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `producto_id`, `tipo_movimiento`, `cantidad`, `stock_anterior`, `stock_nuevo`, `usuario_id`, `venta_id`, `justificacion`, `created_at`) VALUES
(1, 3, 'venta', 1.000, 9.000, 8.000, 1, 1, NULL, '2025-12-02 19:01:49'),
(2, 4, 'venta', 1.000, 7.000, 6.000, 1, 1, NULL, '2025-12-02 19:01:49'),
(3, 2, 'venta', 0.100, 7.650, 7.550, 1, 1, NULL, '2025-12-02 19:01:49'),
(4, 3, 'cancelacion', 1.000, 8.000, 9.000, 1, 1, 'Test', '2025-12-02 19:04:23'),
(5, 4, 'cancelacion', 1.000, 6.000, 7.000, 1, 1, 'Test', '2025-12-02 19:04:23'),
(6, 2, 'cancelacion', 1.000, 7.550, 8.550, 1, 1, 'Test', '2025-12-02 19:04:23'),
(7, 16, 'venta', 1.000, 6.000, 5.000, 1, 1, NULL, '2025-12-02 20:18:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precios_granel`
--

CREATE TABLE `precios_granel` (
  `id` int NOT NULL,
  `producto_id` int NOT NULL,
  `peso_gramos` int NOT NULL,
  `descripcion` varchar(50) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `margen_adicional` decimal(5,2) NOT NULL,
  `precio_calculado` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `precios_granel`
--

INSERT INTO `precios_granel` (`id`, `producto_id`, `peso_gramos`, `descripcion`, `margen_adicional`, `precio_calculado`, `created_at`, `updated_at`) VALUES
(5, 2, 100, '100 gramos', 50.00, 45.05, '2025-11-28 20:51:07', '2025-12-01 20:41:23'),
(6, 2, 250, '1/4 kg', 15.00, 86.34, '2025-11-28 20:51:07', '2025-12-01 20:41:23'),
(7, 2, 500, '1/2 kg', 10.00, 165.17, '2025-11-28 20:51:07', '2025-12-01 20:41:23'),
(9, 49, 100, '100 gramos', 50.00, 17.64, '2025-12-03 20:06:02', '2025-12-03 20:06:02'),
(10, 49, 250, '1/4 kg', 15.00, 33.81, '2025-12-03 20:06:02', '2025-12-03 20:06:02'),
(11, 49, 500, '1/2 kg', 10.00, 64.68, '2025-12-03 20:06:02', '2025-12-03 20:06:02'),
(13, 50, 100, '100 gramos', 50.00, 17.64, '2025-12-03 20:08:57', '2025-12-03 20:08:57'),
(14, 50, 250, '1/4 kg', 15.00, 33.81, '2025-12-03 20:08:57', '2025-12-03 20:08:57'),
(15, 50, 500, '1/2 kg', 10.00, 64.68, '2025-12-03 20:08:57', '2025-12-03 20:08:57'),
(17, 51, 100, '100 gramos', 50.00, 15.70, '2025-12-03 20:12:41', '2025-12-03 20:12:41'),
(18, 51, 250, '1/4 kg', 15.00, 30.09, '2025-12-03 20:12:41', '2025-12-03 20:12:41'),
(19, 51, 500, '1/2 kg', 10.00, 57.57, '2025-12-03 20:12:41', '2025-12-03 20:12:41'),
(21, 52, 100, '100 gramos', 50.00, 17.03, '2025-12-03 20:17:18', '2025-12-03 20:17:18'),
(22, 52, 250, '1/4 kg', 15.00, 32.64, '2025-12-03 20:17:18', '2025-12-03 20:17:18'),
(23, 52, 500, '1/2 kg', 10.00, 62.45, '2025-12-03 20:17:18', '2025-12-03 20:17:18'),
(25, 53, 100, '100 gramos', 50.00, 17.37, '2025-12-03 20:23:45', '2025-12-03 20:23:45'),
(26, 53, 250, '1/4 kg', 15.00, 33.29, '2025-12-03 20:23:45', '2025-12-03 20:23:45'),
(27, 53, 500, '1/2 kg', 10.00, 63.68, '2025-12-03 20:23:45', '2025-12-03 20:23:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `descripcion` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `imagen_url` varchar(255) CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT NULL,
  `upc` varchar(50) CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT NULL,
  `categoria_id` int NOT NULL,
  `tipo_producto` enum('anaquel','granel','pieza') CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `margen_ganancia` decimal(5,2) NOT NULL DEFAULT '30.00',
  `stock_actual` decimal(10,3) NOT NULL DEFAULT '0.000',
  `stock_minimo` decimal(10,3) NOT NULL DEFAULT '2.000',
  `unidad_medida` enum('kg','gramos','piezas','litros') CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT 'piezas',
  `es_temporada` tinyint(1) DEFAULT '0',
  `temporada_id` int DEFAULT NULL,
  `precio_temporada` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `imagen_url`, `upc`, `categoria_id`, `tipo_producto`, `precio_compra`, `precio_venta`, `margen_ganancia`, `stock_actual`, `stock_minimo`, `unidad_medida`, `es_temporada`, `temporada_id`, `precio_temporada`, `activo`, `created_at`, `updated_at`) VALUES
(2, 'Nueces de la India Premier', NULL, '/DulceriaConejos/public/img/productos/producto_692a0b4b62fe1_1764363083.png', '7503029320034', 1, 'granel', 214.50, 300.30, 40.00, 8.550, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-11-28 20:51:07', '2025-12-02 19:19:58'),
(3, 'RING POP 12 pzs', 'Anillo caramelo macizo, varios sabores.', '/DulceriaConejos/public/img/productos/producto_692a0f90a42a4_1764364176.png', '7896624202509', 3, 'anaquel', 106.40, 138.32, 30.00, 9.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-11-28 21:09:37', '2025-12-02 19:04:23'),
(4, 'Paleta Super Rebanadita', NULL, '/DulceriaConejos/public/img/productos/producto_692a1db2c3e2c_1764367794.png', '7502225960020', 4, 'pieza', 6.10, 9.15, 100.00, 7.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-11-28 21:22:14', '2025-12-02 19:04:23'),
(5, 'kinder Sorpresa', NULL, '/DulceriaConejos/public/img/productos/producto_692f3e4feca20_1764703823.png', '80741251', 5, 'pieza', 20.00, 30.00, 100.00, 36.000, 12.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 19:30:24', '2025-12-02 19:30:59'),
(6, 'Paleta Payaso Mini', 'caja 15 pzs.', '/DulceriaConejos/public/img/productos/producto_69308ee30d04f_1764789987.png', '7622202277207', 4, 'anaquel', 108.00, 140.40, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 19:35:29', '2025-12-03 19:26:31'),
(7, 'Paleta Payaso Original', 'caja 10 pzs.', '/DulceriaConejos/public/img/productos/producto_69308f22789f3_1764790050.png', '7622202277269', 4, 'anaquel', 122.50, 159.25, 30.00, 3.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 19:38:10', '2025-12-03 19:27:33'),
(8, 'Arma tu paleta payaso ', 'caja', '/DulceriaConejos/public/img/productos/producto_693089f56638a_1764788725.png', '7503031287240', 5, 'anaquel', 37.00, 48.10, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 19:41:43', '2025-12-03 19:05:26'),
(9, 'Paleta malvavisco', 'De la rosa 20 pzs', '/DulceriaConejos/public/img/productos/producto_69308eabdd7cc_1764789931.png', '724869000314', 4, 'anaquel', 89.00, 115.70, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 19:44:16', '2025-12-03 19:25:36'),
(10, 'Paleta Payaso Snow', 'Caja 12 pzs.', '/DulceriaConejos/public/img/productos/producto_69308f6f67341_1764790127.png', '7622202315886', 4, 'anaquel', 136.35, 177.26, 30.00, 2.000, 2.000, 'piezas', 1, 2, 177.00, 1, '2025-12-02 19:47:41', '2025-12-03 19:28:58'),
(11, 'Bubulubu mini Snow', 'Bolsa 12 pzs.', '/DulceriaConejos/public/img/productos/producto_69308bf0021e7_1764789232.png', '7622202232350', 10, 'anaquel', 46.20, 60.06, 30.00, 4.000, 1.000, 'piezas', 1, 2, 60.00, 1, '2025-12-02 19:53:06', '2025-12-03 19:13:56'),
(12, 'Mazapan de la Rosa Original 30', 'De la Rosa 30 pzs', '/DulceriaConejos/public/img/productos/producto_69308e47c7dad_1764789831.png', '724869100106', 8, 'anaquel', 84.30, 109.59, 30.00, 19.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 19:58:58', '2025-12-03 19:23:53'),
(13, 'Mazapan cubierto de chocolate', 'caja 16 pzs.', '/DulceriaConejos/public/img/productos/producto_69308decb2261_1764789740.png', '724869007825', 8, 'anaquel', 58.00, 75.40, 30.00, 4.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 20:01:37', '2025-12-03 19:22:32'),
(14, 'De la Rosa en polvo', 'Bolsa 908gr.', '/DulceriaConejos/public/img/productos/producto_69308cb95df2d_1764789433.png', '724869005258', 8, 'anaquel', 100.00, 130.00, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 20:03:19', '2025-12-03 19:17:16'),
(15, 'Mazapan de la Rosa original 12', 'Caja 12 pzs.', '/DulceriaConejos/public/img/productos/producto_69308e182370d_1764789784.png', '724869100090', 8, 'anaquel', 34.65, 45.05, 30.00, 6.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 20:09:26', '2025-12-03 19:23:07'),
(16, 'Surtido Especial De la Rosa ', '20 pzs. barras de chocolate', '/DulceriaConejos/public/img/productos/producto_6930927408caf_1764790900.png', '724869008198', 5, 'anaquel', 132.50, 172.25, 30.00, 5.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-02 20:16:49', '2025-12-03 19:41:42'),
(17, 'Winis Colors 32', 'Caramelo suave acidito, 32 tubos con 4 cuadretas', '/DulceriaConejos/public/img/productos/producto_693093459f7ce_1764791109.png', '754177611773', 3, 'anaquel', 47.60, 61.88, 30.00, 39.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:20:57', '2025-12-03 19:45:13'),
(18, 'Winis Colors stick 24', 'Caramelo suave sabores', '/DulceriaConejos/public/img/productos/producto_6930937163d74_1764791153.png', '754177850332', 3, 'anaquel', 30.50, 39.65, 30.00, 20.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:24:28', '2025-12-03 19:45:56'),
(19, 'Cachitos Montes 48 ', 'Chocloso de leche, sabor chocolate. 48 pzs.', '/DulceriaConejos/public/img/productos/producto_69308c4b8519a_1764789323.png', '024142008700', 3, 'anaquel', 32.60, 42.38, 30.00, 29.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:27:39', '2025-12-03 19:15:26'),
(20, 'Bocadin 50pzs.', 'Galleta cubierta sabor chocolate.', '/DulceriaConejos/public/img/productos/producto_69308ae9776a7_1764788969.png', '025046501052', 7, 'anaquel', 60.50, 78.65, 30.00, 8.000, 3.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:31:02', '2025-12-03 19:09:35'),
(21, 'Ositos ICEE 15 pzs', 'Bolsa Gomitas de ositos, 15 pzs. individuales.', '/DulceriaConejos/public/img/productos/producto_69308e83de0f1_1764789891.png', '018804059261', 3, 'anaquel', 75.37, 97.98, 30.00, 6.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:37:51', '2025-12-03 19:24:57'),
(22, 'Banderilla Betamex vitrolero 50 pzs.', 'Dulce de pulpa de tamarindo enchilado en popote.', '/DulceriaConejos/public/img/productos/producto_69308ac955ef5_1764788937.png', '7501624799781', 9, 'anaquel', 134.70, 175.11, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:44:13', '2025-12-03 19:08:59'),
(23, 'Banderilla Betamex mini 50 pzs.', 'Dulce de pulpa de tamarindo enchilado. 1 kg', '/DulceriaConejos/public/img/productos/producto_69308a7c26de8_1764788860.png', '7501624799385', 9, 'anaquel', 100.00, 130.00, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:47:11', '2025-12-03 19:07:42'),
(24, 'Popotix Mini Las delicias 100 pzs.', 'Polvo acidulado frutas.', '/DulceriaConejos/public/img/productos/producto_693090f647518_1764790518.png', '603554100044', 3, 'anaquel', 24.65, 32.05, 30.00, 36.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:51:05', '2025-12-03 19:36:17'),
(25, 'Bubulubu Ricolino mini 25 pzs.', 'Malvavisco relleno fresa, cobertura chocolate. ', '/DulceriaConejos/public/img/productos/producto_69308c271efa6_1764789287.png', '757528023416', 5, 'anaquel', 83.75, 108.88, 30.00, 9.000, 3.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:54:34', '2025-12-03 19:14:50'),
(26, 'Kranky ricolino mini 25 pzs.', 'Hojuela sabor chocolate.', '/DulceriaConejos/public/img/productos/producto_69308d2ba3cca_1764789547.png', '757528023409', 5, 'anaquel', 72.60, 94.38, 30.00, 10.000, 3.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 00:57:57', '2025-12-03 19:19:11'),
(27, 'Malvaviscos Bianchi mini 50 sobres', 'Bolsa 50 sobres individuales malvaviscos mini sabores ', '/DulceriaConejos/public/img/productos/producto_69308db613763_1764789686.png', '724869002240', 3, 'anaquel', 86.80, 112.84, 30.00, 8.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 01:05:08', '2025-12-03 19:21:30'),
(28, 'Chocoretas Ricolino mini 25 pzs.', 'Dulce confitado con chocolate y menta.', '/DulceriaConejos/public/img/productos/producto_69308c8e15a2f_1764789390.png', '074323094169', 5, 'anaquel', 72.60, 94.38, 30.00, 13.000, 3.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 01:15:48', '2025-12-03 19:16:40'),
(29, 'Aciduladito tropical 100 pzs', 'Caramelo suave sabores con chile ', '/DulceriaConejos/public/img/productos/producto_69308a39a1761_1764788793.png', '725226004167', 9, 'anaquel', 50.00, 65.00, 30.00, 17.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 01:32:08', '2025-12-03 19:06:34'),
(30, 'Panditas Ricolino Navideñas 18 pzs', 'Panditas con pinos y estrellas de gomita. ', '/DulceriaConejos/public/img/productos/producto_6930897f9c8c0_1764788607.png', '7622202817199', 10, 'anaquel', 57.52, 74.78, 30.00, 9.000, 2.000, 'piezas', 1, 2, 75.00, 1, '2025-12-03 01:47:43', '2025-12-03 19:03:27'),
(31, 'Vero Mix banda fuego 20 pzs', 'Paletas surtidas con chile', '/DulceriaConejos/public/img/productos/producto_693092ced8c8e_1764790990.png', '7503030572002', 4, 'anaquel', 36.20, 47.06, 30.00, 19.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 01:59:06', '2025-12-03 19:43:17'),
(32, 'Bombas surtidas  Vero. 40 pzs.', 'Surtido paletas ', '/DulceriaConejos/public/img/productos/producto_69308b19728c4_1764789017.png', '759686403123', 4, 'anaquel', 53.90, 70.07, 30.00, 7.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:03:47', '2025-12-03 19:10:17'),
(33, 'Tarrito original Vero 40 pzs.', 'Paleta caramelo tarrito', '/DulceriaConejos/public/img/productos/producto_693092a28eba9_1764790946.png', '7503030374538', 4, 'anaquel', 66.20, 86.06, 30.00, 7.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:07:19', '2025-12-03 19:42:31'),
(34, 'Vero Mix Dulce Club 20 pzs', 'Surtido paletas dulces ', '/DulceriaConejos/public/img/productos/producto_693092f4dc43f_1764791028.png', '7503030374996', 4, 'anaquel', 36.20, 47.06, 30.00, 22.000, 3.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:09:52', '2025-12-03 19:44:10'),
(35, 'Pulparin Pop mini 50 pzs', 'Paletas rellenas de dulce de tamarindo', '/DulceriaConejos/public/img/productos/producto_69309167e9918_1764790631.png', '725226002477', 4, 'anaquel', 50.80, 66.04, 30.00, 25.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:13:06', '2025-12-03 19:37:18'),
(36, 'Pelon Mini Surtido 18pzs', 'Dulce enchilado de sabores ', '/DulceriaConejos/public/img/productos/producto_6930904e8f83a_1764790350.png', '719886120466', 9, 'anaquel', 55.40, 72.02, 30.00, 19.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:15:24', '2025-12-03 19:33:40'),
(37, 'Dragonzitos 100 pzs', 'Polvitos de sabores ', '/DulceriaConejos/public/img/productos/producto_69308ce4f2a66_1764789476.png', '7501345744442', 9, 'anaquel', 27.00, 35.10, 30.00, 30.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:17:37', '2025-12-03 19:18:01'),
(38, 'Malvavisco med. Bianchi 400 gr.', 'Malvavisco de la Rosa', '/DulceriaConejos/public/img/productos/producto_69308d8acf76d_1764789642.png', '724869003292', 3, 'anaquel', 34.65, 45.05, 30.00, 15.000, 3.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:20:38', '2025-12-03 19:20:47'),
(39, 'Pelon Mini Original 18 pzs', 'Dulce enchilado tamarindo', '/DulceriaConejos/public/img/productos/producto_6930900f8e42f_1764790287.png', '719886225086', 9, 'anaquel', 53.20, 69.16, 30.00, 20.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:31:05', '2025-12-03 19:33:39'),
(40, 'Paquete diversion Ricolino 1.38kg', 'Piñatero surtido dulces ricolino', '/DulceriaConejos/public/img/productos/producto_69308fe358e2f_1764790243.png', '7500810016626', 3, 'anaquel', 173.10, 225.03, 30.00, 7.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:33:46', '2025-12-03 19:33:38'),
(41, 'Bubbaloo 4Mix 70 pzs', 'Surtido de gomas de mascar, fresa, platano, mora azul, tutti frutti.', '/DulceriaConejos/public/img/productos/producto_69308b5f33457_1764789087.png', '7622210529220', 6, 'anaquel', 57.70, 75.01, 30.00, 31.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 02:38:32', '2025-12-03 19:11:31'),
(42, 'Chutazo Ricolino 20 pzs', 'chocolate con relleno sabor rompope ', '/DulceriaConejos/public/img/productos/producto_69308d5be4622_1764789595.png', '7501015501078', 5, 'anaquel', 85.40, 111.02, 30.00, 1.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 19:18:52', '2025-12-03 19:20:01'),
(43, 'Suizo Chocolate Leche 16pzs.', 'Chocolate con leche, suizo De la rosa ', '/DulceriaConejos/public/img/productos/producto_69309212c5aaf_1764790802.png', '724869008075', 5, 'anaquel', 98.50, 128.05, 30.00, 5.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 19:22:29', '2025-12-03 19:40:06'),
(44, 'Suizo Extra Leche 16 pzs', 'Chocolate Suizo de la rosa. Extra leche ', '/DulceriaConejos/public/img/productos/producto_693091f3a9e96_1764790771.png', '724869008242', 5, 'anaquel', 98.50, 128.05, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 19:25:23', '2025-12-03 19:39:35'),
(45, 'Suizo con Cacahuate 16 pzs', 'Chocolate Suizo de la Rosa, con cacahuate.', '/DulceriaConejos/public/img/productos/producto_693093ab81ffd_1764791211.png', '724869008112', 5, 'anaquel', 98.50, 128.05, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 19:44:10', '2025-12-03 19:46:57'),
(46, 'Suizo Almendras 16pzs', 'Chocolate de la Rosa, suizo con almendras.', '/DulceriaConejos/public/img/productos/producto_6930940edb0f2_1764791310.png', '724869008136', 5, 'anaquel', 100.80, 131.04, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 19:47:01', '2025-12-03 19:48:37'),
(47, 'Mazapan Untable 400gr', 'Mazapan untable de la Rosa, con trozos de cacahuate', '/DulceriaConejos/public/img/productos/producto_6930950de98f4_1764791565.png', '724869007641', 8, 'anaquel', 58.50, 76.05, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 19:51:29', '2025-12-03 19:52:49'),
(48, 'Mazapan de la Rosa Splenda 18pzs', 'Dulce de cacahuate sin azucar', '/DulceriaConejos/public/img/productos/producto_693099e2c5977_1764792802.png', '724869000741', 8, 'anaquel', 58.50, 76.05, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 19:59:45', '2025-12-03 20:13:28'),
(49, 'Aros Durazno Lucky', 'Aros Lucky Gummy sabor Durazno.', '/DulceriaConejos/public/img/productos/producto_6930982a44c4e_1764792362.png', '744218100090', 11, 'granel', 84.00, 117.60, 40.00, 2.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 20:06:02', '2025-12-03 20:06:08'),
(50, 'Aros Manzana Lucky', 'Aros Lucky Gummy sabor Manzana.', '/DulceriaConejos/public/img/productos/producto_693098d4391c1_1764792532.png', '744218100106', 11, 'granel', 84.00, 117.60, 40.00, 7.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 20:08:57', '2025-12-03 20:08:57'),
(51, 'Lombriz Neon Lucky', 'Lombriz Neon Lucky Gummy', '/DulceriaConejos/public/img/productos/producto_693099b2d482d_1764792754.png', '744218100045', 11, 'granel', 74.77, 104.68, 40.00, 4.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 20:12:40', '2025-12-03 20:12:40'),
(52, 'Ositos Lucky', 'Ositos Lucky Gummy sabor Variado.', '/DulceriaConejos/public/img/productos/producto_69309ac7c93d5_1764793031.png', '744218100014', 11, 'granel', 81.10, 113.54, 40.00, 6.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 20:17:18', '2025-12-03 20:17:18'),
(53, 'Lombriz Lucky', 'Lombriz Lucky Gummy', '/DulceriaConejos/public/img/productos/producto_69309ccb65d81_1764793547.png', '744218100038', 11, 'granel', 82.70, 115.78, 40.00, 7.750, 2.000, 'kg', 0, NULL, NULL, 1, '2025-12-03 20:23:45', '2025-12-03 20:25:54'),
(54, 'Mini malvabon surtido 30 pzs.', 'Malvaviscos surtidos de la Rosa ', '/DulceriaConejos/public/img/productos/default.png', '724869001311', 5, 'anaquel', 50.00, 65.00, 30.00, 5.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 20:57:43', '2025-12-03 20:57:43'),
(55, 'MUIBON ROLL 15 pzs.', 'caja barquillo relleno sabor avellana', '/DulceriaConejos/public/img/productos/default.png', '7501088214387', 5, 'anaquel', 54.00, 70.20, 30.00, 6.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-03 21:11:06', '2025-12-03 21:11:06'),
(56, 'Muibon Roll cookies & cream', 'Barquillo relleno de galleta y crema, cubierto de chocolate ', '/DulceriaConejos/public/img/productos/default.png', '7501088215339', 5, 'anaquel', 54.00, 70.20, 30.00, 5.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 00:02:46', '2025-12-04 00:02:46'),
(57, 'Coconugs 12pzs', 'Dulce de coco cubierto con chocolate ', '/DulceriaConejos/public/img/productos/default.png', '724869000246', 5, 'anaquel', 47.00, 61.10, 30.00, 2.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 01:28:35', '2025-12-04 01:28:35'),
(58, 'Coffee break 300gr', 'Caramelo sabor cafe con chocolate y licor', '/DulceriaConejos/public/img/productos/default.png', '725226005225', 5, 'anaquel', 44.70, 58.11, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 01:32:28', '2025-12-04 01:32:28'),
(59, 'Bubulubu mini piñada 25 pzs', 'Bubulubu relleno piña', '/DulceriaConejos/public/img/productos/default.png', '7622202304941', 5, 'anaquel', 78.60, 102.18, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 01:37:56', '2025-12-04 01:37:56'),
(60, 'Bubulubu original 12 pzs', 'caja', '/DulceriaConejos/public/img/productos/default.png', '074323097474', 5, 'anaquel', 100.00, 130.00, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 01:41:22', '2025-12-04 01:41:22'),
(61, 'Canasta 50 pzs', 'Sabor rompope', '/DulceriaConejos/public/img/productos/default.png', '7501015511107', 5, 'anaquel', 69.25, 90.03, 30.00, 5.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 01:51:07', '2025-12-04 01:51:41'),
(62, 'Poliitos Corona 390gr.', NULL, '/DulceriaConejos/public/img/productos/default.png', '7501031280094', 5, 'anaquel', 78.50, 102.05, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 01:59:46', '2025-12-04 01:59:46'),
(63, 'Surtido chocolates 36 pzs', 'De la Rosa ', '/DulceriaConejos/public/img/productos/default.png', '724869007504', 5, 'anaquel', 96.20, 125.06, 30.00, 2.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:02:23', '2025-12-04 02:02:23'),
(64, 'Malvavisco Chocolate Bianchi 30 pzs', 'caja malvaviscos', '/DulceriaConejos/public/img/productos/default.png', '724869003612', 5, 'anaquel', 53.10, 69.03, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:05:38', '2025-12-04 02:09:14'),
(65, 'Mini Mamut 28 pak', 'caja', '/DulceriaConejos/public/img/productos/default.png', '7501000620630', 5, 'anaquel', 47.00, 61.10, 30.00, 19.000, 5.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:19:01', '2025-12-04 02:19:01'),
(66, 'Oreo Vainilla 4pak', 'caja 252gr', '/DulceriaConejos/public/img/productos/default.png', '7622210575333', 7, 'anaquel', 29.30, 38.09, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:21:53', '2025-12-04 02:21:53'),
(67, 'Oreo Pay de limon', '4pak 252gr', '/DulceriaConejos/public/img/productos/default.png', '7622210575364', 7, 'anaquel', 29.30, 38.09, 30.00, 2.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:23:24', '2025-12-04 02:23:24'),
(68, 'Oreo original 9pak', 'caja 378gr', '/DulceriaConejos/public/img/productos/default.png', '7622210575159', 7, 'anaquel', 54.70, 71.11, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:25:56', '2025-12-04 02:25:56'),
(69, 'Barritas piña', 'caja 12pak 402gr', '/DulceriaConejos/public/img/productos/default.png', '7501030453062', 7, 'anaquel', 34.00, 44.20, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:28:24', '2025-12-04 02:28:24'),
(70, 'Duvalin 18 pzs', 'bisabor avellana fresa', '/DulceriaConejos/public/img/productos/default.png', '025046020614', 5, 'anaquel', 30.80, 40.04, 30.00, 6.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:31:24', '2025-12-04 02:31:24'),
(71, 'Duvalin trisabor 18 pzs', 'avellana, fresa, vainilla', '/DulceriaConejos/public/img/productos/default.png', '025046021499', 5, 'anaquel', 30.80, 40.04, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:33:04', '2025-12-04 02:33:04'),
(72, 'Duvalin 18 pzs', 'bisabor avellana, vainilla', '/DulceriaConejos/public/img/productos/default.png', '025046020621', 5, 'anaquel', 30.80, 40.04, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:34:10', '2025-12-04 02:34:10'),
(73, 'Monedas Ricolino 56pzs', NULL, '/DulceriaConejos/public/img/productos/default.png', '757528000042', 5, 'anaquel', 60.00, 78.00, 30.00, 7.000, 2.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:37:18', '2025-12-04 02:37:18'),
(74, 'cremino avellana 24pzs', 'bicolor', '/DulceriaConejos/public/img/productos/default.png', '7501088210037', 5, 'anaquel', 61.54, 80.00, 30.00, 5.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 02:42:28', '2025-12-04 02:42:28'),
(75, 'Hersheys Coffee 96.4gr', NULL, '/DulceriaConejos/public/img/productos/default.png', '7501024593057', 5, 'anaquel', 74.65, 97.05, 30.00, 2.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 19:01:19', '2025-12-04 19:01:19'),
(76, 'Turin menta tubo', '145gr.', '/DulceriaConejos/public/img/productos/default.png', '7502271916408', 5, 'anaquel', 131.00, 170.30, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 19:06:21', '2025-12-04 19:06:21'),
(77, 'Turin baileys fresa tubo 180gr.', NULL, '/DulceriaConejos/public/img/productos/default.png', '7502271918426', 5, 'anaquel', 136.63, 177.62, 30.00, 3.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 19:10:23', '2025-12-04 19:10:23'),
(78, 'Turin baileys original', 'Tubo 180gr.', '/DulceriaConejos/public/img/productos/default.png', '7502271916279', 5, 'anaquel', 109.00, 141.70, 30.00, 5.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 19:36:16', '2025-12-04 19:36:16'),
(79, 'Conejos mini tubo', '20 pzs. 200 gr.', '/DulceriaConejos/public/img/productos/default.png', '7502271913070', 5, 'anaquel', 104.60, 135.98, 30.00, 6.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 19:39:27', '2025-12-04 19:39:27'),
(80, 'Turin baileys caja 120gr.', NULL, '/DulceriaConejos/public/img/productos/default.png', '7502271916262', 5, 'anaquel', 72.80, 94.64, 30.00, 1.000, 0.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 19:48:37', '2025-12-04 19:48:37'),
(81, 'Turin XMAS bottle ', '180 gr', '/DulceriaConejos/public/img/productos/default.png', '7502271918952', 5, 'anaquel', 159.45, 207.29, 30.00, 4.000, 0.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 20:02:02', '2025-12-04 20:02:02'),
(82, 'Turin Jose Cuervo', 'Bolsa 120gr.', '/DulceriaConejos/public/img/productos/default.png', '756774061692', 5, 'anaquel', 84.50, 109.85, 30.00, 5.000, 0.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 20:04:14', '2025-12-04 20:04:14'),
(83, 'Tapón Bremen 200gr', 'caja', '/DulceriaConejos/public/img/productos/default.png', '742587040269', 5, 'anaquel', 48.50, 63.05, 30.00, 4.000, 1.000, 'piezas', 0, NULL, NULL, 1, '2025-12-04 20:07:18', '2025-12-04 20:07:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `descripcion` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `permisos` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`, `created_at`, `updated_at`) VALUES
(1, 'dueño', 'Acceso completo al sistema', '{\"pos\": [\"vender\", \"cancelar\", \"reimprimir\"], \"ventas\": [\"crear\", \"cancelar\", \"ver\"], \"reportes\": [\"ver\", \"exportar\"], \"usuarios\": [\"crear\", \"editar\", \"eliminar\", \"ver\"], \"productos\": [\"crear\", \"editar\", \"eliminar\", \"ver\"], \"inventario\": [\"ajustar\", \"ver\"], \"temporadas\": [\"crear\", \"editar\", \"eliminar\", \"activar\"]}', '2025-11-04 19:02:03', '2025-11-04 19:02:03'),
(2, 'encargado', 'Acceso limitado para encargados', '{\"permisos\": {\"pos\": [\"vender\"], \"inventario\": [\"ver\"]}, \"visibilidad_menu\": {\"pos\": true, \"ventas\": false, \"reportes\": false, \"usuarios\": false, \"dashboard\": false, \"productos\": false, \"inventario\": true, \"temporadas\": false}}', '2025-11-04 19:02:03', '2025-11-08 22:50:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `token` varchar(500) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `expira_en` timestamp NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `sesiones`
--

INSERT INTO `sesiones` (`id`, `usuario_id`, `token`, `ip_address`, `user_agent`, `expira_en`, `activo`, `created_at`) VALUES
(1, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDExODMsImV4cCI6MTc2NDc4NzU4M30.IcwvzuTgENfIb5W7rK09QT4IotV1S0e-IDZySEgitwE', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-03 18:46:23', 0, '2025-12-02 18:46:23'),
(2, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDExOTEsImV4cCI6MTc2NDc4NzU5MX0.OTmilT_d6bqieDAsRRhgQcHQDJtQWnfD_5q8dzWkh8w', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-03 18:46:31', 0, '2025-12-02 18:46:31'),
(3, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDEzNTUsImV4cCI6MTc2NTMwNjE1NX0.zmkg3ngLNya_PEsY_xTrjlVWmC0xSeobr3dFmRPWrCA', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:49:15', 1, '2025-12-02 18:49:15'),
(4, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE0MzAsImV4cCI6MTc2NTMwNjIzMH0.ZXjplY_kVRsa4i4a-dFUAPA7P31lwoNYovKw_kIy490', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15', '2025-12-09 18:50:30', 1, '2025-12-02 18:50:30'),
(5, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE1MzQsImV4cCI6MTc2NTMwNjMzNH0.FUgMfr-74Mu5zl4YY-HMZ_C2EumhqvsiRurs7bpOXwI', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:52:14', 1, '2025-12-02 18:52:14'),
(6, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE1NjcsImV4cCI6MTc2NTMwNjM2N30.jjnKsqIH7CXtHnx0THfIOzXaIzaforEIVX1MRdbBvJo', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:52:47', 1, '2025-12-02 18:52:47'),
(7, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE1NzYsImV4cCI6MTc2NTMwNjM3Nn0.1PxcrVkG2tkhsxsluiUVoNxjkmhsSMEZIzZWxZPyhRs', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:52:56', 1, '2025-12-02 18:52:56'),
(8, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE2MjIsImV4cCI6MTc2NTMwNjQyMn0.i6g7In5QH0q3dSNU_oxRRaDllZqXK_4Vh5l8gxHqO-0', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:53:42', 1, '2025-12-02 18:53:42'),
(9, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE3MjQsImV4cCI6MTc2NTMwNjUyNH0.esW9O-xyqi4OSNgsrzae63W0QUHnst2Y1AiIr0VG544', '::1', 'curl/8.7.1', '2025-12-09 18:55:24', 1, '2025-12-02 18:55:24'),
(10, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE3MzksImV4cCI6MTc2NTMwNjUzOX0.yQaoIgdPC7kB9FSsnCQMnSdJjvCseuYjcZ-KqSYqULQ', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:55:39', 1, '2025-12-02 18:55:39'),
(11, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE3NjEsImV4cCI6MTc2NTMwNjU2MX0.Sk6lhbJ83vhLzRZxVB6e5JUwadAR-Z2K3wiwuWJs9EE', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:56:01', 1, '2025-12-02 18:56:01'),
(12, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE3OTEsImV4cCI6MTc2NTMwNjU5MX0.o2QgYmv1zoKRVaC-bDdgeVE1IuoqzwPMch0h-zkNKyc', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:56:31', 1, '2025-12-02 18:56:31'),
(13, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE4NDYsImV4cCI6MTc2NTMwNjY0Nn0.2SgNi0yh0T0hszPd4zaSYJNp-jJOn7gSXCP-nT4gO4s', '::1', 'curl/8.7.1', '2025-12-09 18:57:26', 1, '2025-12-02 18:57:26'),
(14, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE4NTQsImV4cCI6MTc2NTMwNjY1NH0.BgxiVuMEKaBuLp6QTpO-vs1yBMRlVPR0aovN0wZ1vns', '::1', 'curl/8.7.1', '2025-12-09 18:57:34', 1, '2025-12-02 18:57:34'),
(15, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDE4NjUsImV4cCI6MTc2NTMwNjY2NX0.67mSwWCNahggB3tcLSm8xxHMqLvb8UNy-gIyAi715l4', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 18:57:45', 0, '2025-12-02 18:57:45'),
(16, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDMxMzgsImV4cCI6MTc2NTMwNzkzOH0.OgyWwwxKJUzz-gHaI65w27WDKttIqa5eUPC6E6Dp2JU', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-12-09 19:18:58', 1, '2025-12-02 19:18:58'),
(17, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3MDg4NTUsImV4cCI6MTc2NTMxMzY1NX0.S7uqiT77p6zH47ASkzuNYE5gI2RT5NAhHSflXU_09Gc', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-09 20:54:15', 0, '2025-12-02 20:54:15'),
(18, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3ODc5OTgsImV4cCI6MTc2NTM5Mjc5OH0.toBPCQXqrlqUxRbkLPAGUWRGDgQn9uEK0vAt16QBN7o', '192.168.100.17', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-10 18:53:18', 0, '2025-12-03 18:53:18'),
(19, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3ODgwOTIsImV4cCI6MTc2NTM5Mjg5Mn0.lfJvEt5ri44iyEIW6gCWwJcB1peq6D8dzeXwPBxd-w0', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-10 18:54:52', 0, '2025-12-03 18:54:52'),
(20, 3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjozLCJlbWFpbCI6ImN1YXVoQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3ODg0MTUsImV4cCI6MTc2NTM5MzIxNX0.v2BtbjrDUaQJqt3-5rWc8NF2utnVZuCfZAJmdI_wibM', '192.168.100.17', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-10 19:00:15', 1, '2025-12-03 19:00:15'),
(21, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3ODg4OTQsImV4cCI6MTc2NTM5MzY5NH0.rSC0TCFDhp0GceRDlDmSmjXqZFHmKBAMjvkNauNUDZQ', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-10 19:08:14', 1, '2025-12-03 19:08:14'),
(22, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjYwOTA4NzYsImV4cCI6MTc2NjY5NTY3Nn0.QYhTuLGo38IPaijWrEVzGXeygIR35Z72m_FCd811b28', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-25 20:47:56', 1, '2025-12-18 20:47:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporadas`
--

CREATE TABLE `temporadas` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `descripcion` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `temporadas`
--

INSERT INTO `temporadas` (`id`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_fin`, `activa`, `created_at`, `updated_at`) VALUES
(1, 'Halloween', 'Dulces especiales para Halloween', '2025-10-01', '2025-10-31', 0, '2025-11-04 19:02:03', '2025-11-11 00:08:56'),
(2, 'Navidad', 'Productos navideños', '2025-12-01', '2025-12-31', 1, '2025-11-04 19:02:03', '2025-11-11 00:08:56'),
(3, 'Día del Niño', 'Dulces para el día del niño', '2025-04-20', '2025-04-30', 0, '2025-11-04 19:02:03', '2025-11-04 19:02:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets_impresos`
--

CREATE TABLE `tickets_impresos` (
  `id` int NOT NULL,
  `venta_id` int NOT NULL,
  `tipo_ticket` enum('cliente','copia') CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `impreso_por` int NOT NULL,
  `numero_reimpresion` int DEFAULT '0',
  `contenido_html` text CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `tickets_impresos`
--

INSERT INTO `tickets_impresos` (`id`, `venta_id`, `tipo_ticket`, `impreso_por`, `numero_reimpresion`, `contenido_html`, `created_at`) VALUES
(1, 1, 'cliente', 1, 0, '\n<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Ticket 20251202-00001</title>\n    <style>\n        @media print {\n            body { margin: 0; }\n            .no-print { display: none; }\n        }\n        body {\n            font-family: \"Courier New\", monospace;\n            width: 300px;\n            margin: 0 auto;\n            padding: 10px;\n            font-size: 12px;\n        }\n        .center { text-align: center; }\n        .bold { font-weight: bold; }\n        .line { border-top: 1px dashed #000; margin: 5px 0; }\n        table { width: 100%; border-collapse: collapse; }\n        td { padding: 2px 0; }\n        .right { text-align: right; }\n        .producto { font-size: 11px; }\n        .total { font-size: 14px; font-weight: bold; }\n    </style>\n</head>\n<body>\n    <div class=\"center bold\">\n        Dulcería El Sabor\n    </div>\n    <div class=\"center\">\n        Calle Principal #123, Col. Centro<br>\n        Tel: (555) 123-4567<br>\n        RFC: ABC123456XYZ\n    </div>\n    <div class=\"line\"></div>\n    <div>\n        <strong>Folio:</strong> 20251202-00001<br>\n        <strong>Fecha:</strong> 02/12/2025 13:01:49<br>\n        <strong>Cajero:</strong> Administrador\n    </div>\n    <div class=\"line\"></div>\n    <table class=\"producto\">\n        <thead>\n            <tr>\n                <td><strong>Producto</strong></td>\n                <td class=\"right\"><strong>Cant.</strong></td>\n                <td class=\"right\"><strong>Precio</strong></td>\n                <td class=\"right\"><strong>Total</strong></td>\n            </tr>\n        </thead>\n        <tbody>\n            <tr>\n                <td colspan=\"4\">RING POP 12 pzs</td>\n            </tr>\n            <tr>\n                <td></td>\n                <td class=\"right\">1.00</td>\n                <td class=\"right\">$138.32</td>\n                <td class=\"right\">$138.32</td>\n            </tr>\n            <tr>\n                <td colspan=\"4\">Paleta Super Rebanadita</td>\n            </tr>\n            <tr>\n                <td></td>\n                <td class=\"right\">1.00</td>\n                <td class=\"right\">$9.15</td>\n                <td class=\"right\">$9.15</td>\n            </tr>\n            <tr>\n                <td colspan=\"4\">Nueces de la India Premier (100g)</td>\n            </tr>\n            <tr>\n                <td></td>\n                <td class=\"right\">1.00</td>\n                <td class=\"right\">$45.05</td>\n                <td class=\"right\">$45.05</td>\n            </tr>\n        </tbody>\n    </table>\n    <div class=\"line\"></div>\n    <table>\n        <tr>\n            <td><strong>Subtotal:</strong></td>\n            <td class=\"right\">$192.52</td>\n        </tr>\n        <tr class=\"total\">\n            <td>TOTAL:</td>\n            <td class=\"right\">$192.52</td>\n        </tr>\n        <tr>\n            <td><strong>Método de pago:</strong></td>\n            <td class=\"right\">Efectivo</td>\n        </tr>\n    </table>\n    <div class=\"line\"></div>\n    <div class=\"center\">\n        ¡Gracias por su compra!<br>\n        <small>Conserve su ticket</small>\n    </div>\n    <br>\n    <div class=\"center no-print\">\n        <button onclick=\"window.print()\">Imprimir</button>\n        <button onclick=\"window.close()\">Cerrar</button>\n    </div>\n</body>\n</html>', '2025-12-02 19:02:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `rol_id` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `session_token` varchar(255) CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT NULL,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol_id`, `activo`, `session_token`, `ultimo_acceso`, `created_at`, `updated_at`) VALUES
(1, 'Naye', 'admin@dulceria.com', '$2y$12$Rl5jak8fbBCCBu0uIrJDBOpXZngxdtMls/rBmCDngFy/tel8tKKwm', 1, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6ImFkbWluQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjYwOTA4NzYsImV4cCI6MTc2NjY5NTY3Nn0.QYhTuLGo38IPaijWrEVzGXeygIR35Z72m_FCd811b28', '2025-12-18 20:47:56', '2025-11-04 19:02:03', '2025-12-18 20:47:56'),
(2, 'Vivian', 'vivian@prueba.com', '$2y$10$3b.W8ZgcW4iiF3oblFwmbuSDk1FySCOnq.MkZJje9IHlMoaUpWwZi', 2, 1, NULL, '2025-12-02 06:12:52', '2025-11-04 22:25:59', '2025-12-02 18:49:05'),
(3, 'Cuauh', 'cuauh@dulceria.com', '$2y$12$Rl5jak8fbBCCBu0uIrJDBOpXZngxdtMls/rBmCDngFy/tel8tKKwm', 1, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c3VhcmlvX2lkIjozLCJlbWFpbCI6ImN1YXVoQGR1bGNlcmlhLmNvbSIsInJvbCI6ImR1ZVx1MDBmMW8iLCJpYXQiOjE3NjQ3ODg0MTUsImV4cCI6MTc2NTM5MzIxNX0.v2BtbjrDUaQJqt3-5rWc8NF2utnVZuCfZAJmdI_wibM', '2025-12-03 19:00:15', '2025-12-03 18:59:56', '2025-12-03 19:00:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int NOT NULL,
  `folio` varchar(20) CHARACTER SET utf32 COLLATE utf32_spanish_ci NOT NULL,
  `usuario_id` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `pago_recibido` decimal(10,2) DEFAULT NULL,
  `cambio` decimal(10,2) DEFAULT NULL,
  `metodo_pago_id` int NOT NULL,
  `estado` enum('completada','cancelada') CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT 'completada',
  `cancelada_por` int DEFAULT NULL,
  `motivo_cancelacion` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `fecha_cancelacion` timestamp NULL DEFAULT NULL,
  `observaciones` text CHARACTER SET utf32 COLLATE utf32_spanish_ci,
  `pdf_ticket` varchar(255) CHARACTER SET utf32 COLLATE utf32_spanish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `folio`, `usuario_id`, `subtotal`, `total`, `pago_recibido`, `cambio`, `metodo_pago_id`, `estado`, `cancelada_por`, `motivo_cancelacion`, `fecha_cancelacion`, `observaciones`, `pdf_ticket`, `created_at`) VALUES
(1, '20251202-00001', 1, 172.25, 172.25, 200.00, 27.75, 1, 'completada', NULL, NULL, NULL, NULL, NULL, '2025-12-02 20:18:32');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_productos_top`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_productos_top` (
`id` int
,`nombre` varchar(200)
,`categoria` varchar(100)
,`num_ventas` bigint
,`cantidad_total_vendida` decimal(32,3)
,`total_vendido` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_stock_bajo`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_stock_bajo` (
`id` int
,`nombre` varchar(200)
,`categoria` varchar(100)
,`tipo_producto` enum('anaquel','granel','pieza')
,`stock_actual` decimal(10,3)
,`stock_minimo` decimal(10,3)
,`unidad_medida` enum('kg','gramos','piezas','litros')
,`cantidad_faltante` decimal(11,3)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ventas_diarias`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_ventas_diarias` (
`fecha` date
,`num_ventas` bigint
,`total_dia` decimal(32,2)
,`promedio_venta` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_productos_top`
--
DROP TABLE IF EXISTS `vista_productos_top`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_productos_top`  AS SELECT `p`.`id` AS `id`, `p`.`nombre` AS `nombre`, `c`.`nombre` AS `categoria`, count(`dv`.`id`) AS `num_ventas`, sum(`dv`.`cantidad`) AS `cantidad_total_vendida`, sum(`dv`.`subtotal`) AS `total_vendido` FROM (((`productos` `p` join `categorias` `c` on((`p`.`categoria_id` = `c`.`id`))) join `detalle_ventas` `dv` on((`p`.`id` = `dv`.`producto_id`))) join `ventas` `v` on((`dv`.`venta_id` = `v`.`id`))) WHERE (`v`.`estado` = 'completada') GROUP BY `p`.`id`, `p`.`nombre`, `c`.`nombre` ORDER BY `total_vendido` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_stock_bajo`
--
DROP TABLE IF EXISTS `vista_stock_bajo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_stock_bajo`  AS SELECT `p`.`id` AS `id`, `p`.`nombre` AS `nombre`, `c`.`nombre` AS `categoria`, `p`.`tipo_producto` AS `tipo_producto`, `p`.`stock_actual` AS `stock_actual`, `p`.`stock_minimo` AS `stock_minimo`, `p`.`unidad_medida` AS `unidad_medida`, (`p`.`stock_minimo` - `p`.`stock_actual`) AS `cantidad_faltante` FROM (`productos` `p` join `categorias` `c` on((`p`.`categoria_id` = `c`.`id`))) WHERE ((`p`.`stock_actual` <= `p`.`stock_minimo`) AND (`p`.`activo` = 1)) ORDER BY (`p`.`stock_minimo` - `p`.`stock_actual`) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ventas_diarias`
--
DROP TABLE IF EXISTS `vista_ventas_diarias`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_ventas_diarias`  AS SELECT cast(`ventas`.`created_at` as date) AS `fecha`, count(`ventas`.`id`) AS `num_ventas`, sum(`ventas`.`total`) AS `total_dia`, avg(`ventas`.`total`) AS `promedio_venta` FROM `ventas` WHERE (`ventas`.`estado` = 'completada') GROUP BY cast(`ventas`.`created_at` as date) ORDER BY `fecha` DESC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `configuracion_impresora`
--
ALTER TABLE `configuracion_impresora`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cortes_caja`
--
ALTER TABLE `cortes_caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `detalle_corte_granel`
--
ALTER TABLE `detalle_corte_granel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `idx_corte` (`corte_id`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta` (`venta_id`),
  ADD KEY `idx_producto` (`producto_id`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_fecha` (`created_at`),
  ADD KEY `idx_tipo` (`tipo_movimiento`);

--
-- Indices de la tabla `precios_granel`
--
ALTER TABLE `precios_granel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_producto_peso` (`producto_id`,`peso_gramos`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria_id`),
  ADD KEY `idx_tipo` (`tipo_producto`),
  ADD KEY `idx_temporada` (`temporada_id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_upc` (`upc`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`(255)),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_expira` (`expira_en`);

--
-- Indices de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activa` (`activa`),
  ADD KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `tickets_impresos`
--
ALTER TABLE `tickets_impresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `impreso_por` (`impreso_por`),
  ADD KEY `idx_venta` (`venta_id`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol_id`),
  ADD KEY `idx_session_token` (`session_token`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `metodo_pago_id` (`metodo_pago_id`),
  ADD KEY `cancelada_por` (`cancelada_por`),
  ADD KEY `idx_folio` (`folio`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_fecha` (`created_at`),
  ADD KEY `idx_estado` (`estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `configuracion_impresora`
--
ALTER TABLE `configuracion_impresora`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cortes_caja`
--
ALTER TABLE `cortes_caja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_corte_granel`
--
ALTER TABLE `detalle_corte_granel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `precios_granel`
--
ALTER TABLE `precios_granel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tickets_impresos`
--
ALTER TABLE `tickets_impresos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cortes_caja`
--
ALTER TABLE `cortes_caja`
  ADD CONSTRAINT `cortes_caja_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `detalle_corte_granel`
--
ALTER TABLE `detalle_corte_granel`
  ADD CONSTRAINT `detalle_corte_granel_ibfk_1` FOREIGN KEY (`corte_id`) REFERENCES `cortes_caja` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_corte_granel_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_3` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `precios_granel`
--
ALTER TABLE `precios_granel`
  ADD CONSTRAINT `precios_granel_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`temporada_id`) REFERENCES `temporadas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tickets_impresos`
--
ALTER TABLE `tickets_impresos`
  ADD CONSTRAINT `tickets_impresos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_impresos_ibfk_2` FOREIGN KEY (`impreso_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`metodo_pago_id`) REFERENCES `metodos_pago` (`id`),
  ADD CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`cancelada_por`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
