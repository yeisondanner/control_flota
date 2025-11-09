-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.12.0.7122
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para control_flota
DROP DATABASE IF EXISTS `control_flota`;
CREATE DATABASE IF NOT EXISTS `control_flota` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `control_flota`;

-- Volcando estructura para tabla control_flota.certificados
DROP TABLE IF EXISTS `certificados`;
CREATE TABLE IF NOT EXISTS `certificados` (
  `id_certificado` int(11) NOT NULL AUTO_INCREMENT,
  `id_vehiculo` int(11) DEFAULT NULL,
  `tipo_certificado` varchar(100) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  PRIMARY KEY (`id_certificado`),
  KEY `id_vehiculo` (`id_vehiculo`),
  CONSTRAINT `certificados_ibfk_1` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.certificados: ~0 rows (aproximadamente)
DELETE FROM `certificados`;
INSERT INTO `certificados` (`id_certificado`, `id_vehiculo`, `tipo_certificado`, `fecha_emision`, `fecha_vencimiento`) VALUES
	(4, 4, 'SOAT', '2025-10-29', '2025-12-31');

-- Volcando estructura para tabla control_flota.conductor
DROP TABLE IF EXISTS `conductor`;
CREATE TABLE IF NOT EXISTS `conductor` (
  `id_conductor` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `categoria_licencia` varchar(100) DEFAULT NULL,
  `numero_licencia` varchar(100) DEFAULT NULL,
  `fvencimiento_licencia` date DEFAULT NULL,
  PRIMARY KEY (`id_conductor`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `conductor_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.conductor: ~3 rows (aproximadamente)
DELETE FROM `conductor`;
INSERT INTO `conductor` (`id_conductor`, `id_usuario`, `categoria_licencia`, `numero_licencia`, `fvencimiento_licencia`) VALUES
	(12, 30, 'A-IIIc', 'Q04067235', '2025-12-12'),
	(13, 31, 'A-IIb', 'Q-43140476', '2026-10-12'),
	(14, 32, 'A-I', 'M1232025', '2030-12-01');

-- Volcando estructura para tabla control_flota.conductor_vehiculo
DROP TABLE IF EXISTS `conductor_vehiculo`;
CREATE TABLE IF NOT EXISTS `conductor_vehiculo` (
  `id_conductorvehiculo` int(11) NOT NULL AUTO_INCREMENT,
  `id_conductor` int(11) DEFAULT NULL,
  `id_vehiculo` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  PRIMARY KEY (`id_conductorvehiculo`),
  KEY `id_conductor` (`id_conductor`),
  KEY `id_vehiculo` (`id_vehiculo`),
  CONSTRAINT `conductor_vehiculo_ibfk_1` FOREIGN KEY (`id_conductor`) REFERENCES `conductor` (`id_conductor`),
  CONSTRAINT `conductor_vehiculo_ibfk_2` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.conductor_vehiculo: ~3 rows (aproximadamente)
DELETE FROM `conductor_vehiculo`;
INSERT INTO `conductor_vehiculo` (`id_conductorvehiculo`, `id_conductor`, `id_vehiculo`, `fecha_registro`) VALUES
	(7, 13, 3, '2025-10-29'),
	(8, 12, 3, '2025-10-29'),
	(10, 13, 4, '2025-10-29');

-- Volcando estructura para tabla control_flota.herramientas
DROP TABLE IF EXISTS `herramientas`;
CREATE TABLE IF NOT EXISTS `herramientas` (
  `id_herramientas` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_herramientas`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.herramientas: ~4 rows (aproximadamente)
DELETE FROM `herramientas`;
INSERT INTO `herramientas` (`id_herramientas`, `nombre`, `descripcion`) VALUES
	(3, 'Filtro petrolio', 'sirvefdsfsdf'),
	(4, 'Filtro aceite', 'drgdfgdg'),
	(5, 'Filtro aire', 'sdfsd'),
	(6, 'fdgfdgd', 'fgfdgd');

-- Volcando estructura para tabla control_flota.kilometraje_semanal
DROP TABLE IF EXISTS `kilometraje_semanal`;
CREATE TABLE IF NOT EXISTS `kilometraje_semanal` (
  `id_kilometrajesemanal` int(11) NOT NULL AUTO_INCREMENT,
  `id_conductor` int(11) DEFAULT NULL,
  `id_vehiculo` int(11) DEFAULT NULL,
  `kilometraje` decimal(11,2) DEFAULT NULL,
  `horas` time DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_kilometrajesemanal`),
  KEY `id_conductor` (`id_conductor`),
  KEY `id_vehiculo` (`id_vehiculo`),
  CONSTRAINT `kilometraje_semanal_ibfk_1` FOREIGN KEY (`id_conductor`) REFERENCES `conductor` (`id_conductor`),
  CONSTRAINT `kilometraje_semanal_ibfk_2` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.kilometraje_semanal: ~0 rows (aproximadamente)
DELETE FROM `kilometraje_semanal`;

-- Volcando estructura para tabla control_flota.mantenimientos
DROP TABLE IF EXISTS `mantenimientos`;
CREATE TABLE IF NOT EXISTS `mantenimientos` (
  `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_vehiculo` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `kilometraje_actual` decimal(10,2) DEFAULT NULL,
  `kilometraje_proximo` decimal(10,2) DEFAULT NULL,
  `hora_actual` time DEFAULT NULL,
  `hora_proxima` time DEFAULT NULL,
  `gasto_mantenimiento` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_mantenimiento`),
  KEY `id_vehiculo` (`id_vehiculo`),
  CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.mantenimientos: ~0 rows (aproximadamente)
DELETE FROM `mantenimientos`;
INSERT INTO `mantenimientos` (`id_mantenimiento`, `id_vehiculo`, `tipo`, `descripcion`, `fecha`, `kilometraje_actual`, `kilometraje_proximo`, `hora_actual`, `hora_proxima`, `gasto_mantenimiento`) VALUES
	(23, 4, 'Preventivo', 'gregregegerger', '2025-10-31', 343242.00, 99999999.99, '23:03:00', '03:42:00', 0.00);

-- Volcando estructura para tabla control_flota.mantenimientos_herramientas
DROP TABLE IF EXISTS `mantenimientos_herramientas`;
CREATE TABLE IF NOT EXISTS `mantenimientos_herramientas` (
  `id_mantenimientos_herramientas` int(11) NOT NULL AUTO_INCREMENT,
  `id_mantenimiento` int(11) DEFAULT NULL,
  `id_herramientas` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT current_timestamp(),
  PRIMARY KEY (`id_mantenimientos_herramientas`),
  KEY `id_mantenimiento` (`id_mantenimiento`),
  KEY `id_herramientas` (`id_herramientas`),
  CONSTRAINT `mantenimientos_herramientas_ibfk_1` FOREIGN KEY (`id_mantenimiento`) REFERENCES `mantenimientos` (`id_mantenimiento`),
  CONSTRAINT `mantenimientos_herramientas_ibfk_2` FOREIGN KEY (`id_herramientas`) REFERENCES `herramientas` (`id_herramientas`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.mantenimientos_herramientas: ~0 rows (aproximadamente)
DELETE FROM `mantenimientos_herramientas`;

-- Volcando estructura para tabla control_flota.mantenimientos_suministros
DROP TABLE IF EXISTS `mantenimientos_suministros`;
CREATE TABLE IF NOT EXISTS `mantenimientos_suministros` (
  `id_mantenimientos_suministros` int(11) NOT NULL AUTO_INCREMENT,
  `id_mantenimiento` int(11) DEFAULT NULL,
  `id_suministros` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT current_timestamp(),
  PRIMARY KEY (`id_mantenimientos_suministros`),
  KEY `id_mantenimiento` (`id_mantenimiento`),
  KEY `id_suministros` (`id_suministros`),
  CONSTRAINT `mantenimientos_suministros_ibfk_1` FOREIGN KEY (`id_mantenimiento`) REFERENCES `mantenimientos` (`id_mantenimiento`),
  CONSTRAINT `mantenimientos_suministros_ibfk_2` FOREIGN KEY (`id_suministros`) REFERENCES `suministros` (`id_suministros`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.mantenimientos_suministros: ~2 rows (aproximadamente)
DELETE FROM `mantenimientos_suministros`;
INSERT INTO `mantenimientos_suministros` (`id_mantenimientos_suministros`, `id_mantenimiento`, `id_suministros`, `fecha_registro`) VALUES
	(13, 23, 5, '2025-10-31'),
	(14, 23, 4, '2025-10-31');

-- Volcando estructura para tabla control_flota.persona
DROP TABLE IF EXISTS `persona`;
CREATE TABLE IF NOT EXISTS `persona` (
  `id_persona` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `dni` char(8) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  PRIMARY KEY (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.persona: ~6 rows (aproximadamente)
DELETE FROM `persona`;
INSERT INTO `persona` (`id_persona`, `nombres`, `apellidos`, `dni`, `direccion`, `telefono`, `email`, `fecha_nacimiento`) VALUES
	(1, 'Erick', 'Peña', '71115880', 'C. los nelumbios 991', '993760069', 'erick.pena@vpice.com', '1999-10-15'),
	(9, 'Keler', 'Sanchez', '45678943', 'Ate', '983434345435', 'keler@vpice.com', '1987-09-20'),
	(27, 'Tito', 'Peña Asencio', '76543219', 'AV. Galilea', '985080210', 'tito@gmail.com', '2002-07-09'),
	(30, 'Yearshino', 'Delgado Bazan', '04067235', 'Calle ate', '985080210', 'yersiono@gmail.com', '2025-10-30'),
	(31, 'Keler', 'Chavez', '43140476', 'dsgfdsf', '325435435', 'dsfsdf@gmail.com', '2025-10-30'),
	(32, 'Yeison Danner', 'Carhuapoma Dett', '73448652', 'Jiron. amazonas N° 208', '910367611', 'yeisoncarhuapoma@gmail.com', '2000-01-01');

-- Volcando estructura para tabla control_flota.suministros
DROP TABLE IF EXISTS `suministros`;
CREATE TABLE IF NOT EXISTS `suministros` (
  `id_suministros` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_suministros`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.suministros: ~2 rows (aproximadamente)
DELETE FROM `suministros`;
INSERT INTO `suministros` (`id_suministros`, `nombre`, `descripcion`) VALUES
	(4, 'ttrtret', 'reter'),
	(5, 'retrete', 'retret');

-- Volcando estructura para tabla control_flota.unidad_minera
DROP TABLE IF EXISTS `unidad_minera`;
CREATE TABLE IF NOT EXISTS `unidad_minera` (
  `id_unidadminera` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_unidad` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_unidadminera`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.unidad_minera: ~4 rows (aproximadamente)
DELETE FROM `unidad_minera`;
INSERT INTO `unidad_minera` (`id_unidadminera`, `nombre_unidad`, `descripcion`) VALUES
	(2, 'Cerro Lindo', 'ok'),
	(3, 'Cajamarquilla', 'ok'),
	(4, 'San Rafael', 'okey'),
	(5, 'Central', 'Ok');

-- Volcando estructura para tabla control_flota.usuario
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `rol` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `id_persona` (`id_persona`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.usuario: ~6 rows (aproximadamente)
DELETE FROM `usuario`;
INSERT INTO `usuario` (`id_usuario`, `id_persona`, `username`, `password`, `rol`) VALUES
	(1, 1, 'erick', '123456', 'Administrador'),
	(9, 9, 'keler', '123456', 'Conductor'),
	(27, 27, 'tito', '123456', 'Conductor'),
	(30, 30, '04067235', '$2y$10$aLAoOsxUU8lMfJLu4dqT7eJXPjnF8IXL3SAsRL31Um7O1wFTSjkEK', 'Conductor'),
	(31, 31, '43140476', '$2y$10$g5dQ/qVXo5ibft13EaMmheKbF4qj1Ic9vrXQ5Kn5VKOmplAkXYGke', 'Conductor'),
	(32, 32, '73448652', '$2y$10$Ok5HGpHbQj9d1B3Kujg5puiz/IcifQG8h1NKRxyMsH6a659b19U0q', 'Conductor');

-- Volcando estructura para tabla control_flota.vehiculos
DROP TABLE IF EXISTS `vehiculos`;
CREATE TABLE IF NOT EXISTS `vehiculos` (
  `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT,
  `matricula` varchar(15) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `id_unidadminera` int(11) DEFAULT NULL,
  `kilometraje` decimal(11,2) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `fecha_registro` date DEFAULT curdate(),
  PRIMARY KEY (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_flota.vehiculos: ~3 rows (aproximadamente)
DELETE FROM `vehiculos`;
INSERT INTO `vehiculos` (`id_vehiculo`, `matricula`, `marca`, `modelo`, `year`, `id_unidadminera`, `kilometraje`, `tipo`, `fecha_registro`) VALUES
	(3, 'BAC-746', 'HINO', 'Ductro N300', '2020', 5, 82000.00, 'Camion baranda', '2025-10-29'),
	(4, 'AML-745', 'Hino', 'Ductro N300', '2022', 3, 90000.00, 'Camion baranda', '2025-10-29'),
	(5, 'M343', '454', '45435', '2025', 3, 999999999.99, 'ERTERTER', '2025-10-31');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
