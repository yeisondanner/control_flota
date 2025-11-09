-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-10-2025 a las 06:15:38
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
-- Base de datos: `sis_vpice`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE `contratos` (
  `idcontrato` int(11) NOT NULL,
  `idhabilitacion_personal` int(11) NOT NULL,
  `id_proyectos` int(11) NOT NULL,
  `fecha_registro` date NOT NULL DEFAULT curdate(),
  `cargo` varchar(200) NOT NULL,
  `servicio` varchar(150) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `sueldo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contratos`
--

INSERT INTO `contratos` (`idcontrato`, `idhabilitacion_personal`, `id_proyectos`, `fecha_registro`, `cargo`, `servicio`, `fecha_inicio`, `fecha_fin`, `sueldo`) VALUES
(15, 36, 14, '2025-09-24', 'sisas', 'todoeldia', '2025-09-25', '2025-10-31', 1600.00),
(16, 38, 17, '2025-09-30', 'INSPECTOR', 'XXX-001', '2025-09-30', '2025-10-03', 3000.00),
(17, 41, 19, '2025-10-03', 'Sistemas', 'Soporte tecnico', '2025-10-01', '2026-05-04', 2200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `idcurso` int(11) NOT NULL,
  `id_tipo_curso` int(11) DEFAULT NULL,
  `nombre_curso` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`idcurso`, `id_tipo_curso`, `nombre_curso`) VALUES
(13, 3, 'CURSO ESPECIFICO 01'),
(14, 3, 'CURSO ESPECIFICO 02'),
(15, 3, 'CURSO ESPECIFICO 03'),
(16, 1, 'CURSO IND 01'),
(17, 1, 'CURSO IND 02'),
(18, 1, 'CURSO IND 03'),
(19, 2, 'CURSO RRCC 01'),
(20, 2, 'CURSO RRCC 02'),
(21, 2, 'CURSO RRCC 03'),
(22, 3, 'Trabajo en alturas'),
(23, 3, 'Caliente 001'),
(24, 3, 'Herramientas manuales 001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examen_medico`
--

CREATE TABLE `examen_medico` (
  `idexamenmedico` int(11) NOT NULL,
  `idtipo_examenmedico` int(11) NOT NULL,
  `nombre_examenmedico` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `examen_medico`
--

INSERT INTO `examen_medico` (`idexamenmedico`, `idtipo_examenmedico`, `nombre_examenmedico`) VALUES
(1, 1, 'NOMBRE DE TIPO 1'),
(2, 2, 'NOMBRE DE TIPO 2'),
(8, 3, 'NOMBRE DE TIPO 3'),
(9, 1, 'NOMBRE DE TIPO 1.1'),
(10, 2, 'NOMBRE DE TIPO 2.2'),
(11, 3, 'NOMBRE DE TIPO 3.3'),
(12, 1, 'ERICK'),
(13, 1, 'ASENCIOt'),
(14, 2, 'hemoglobina'),
(15, 1, 'Chequeo general 001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habilitacion_personal`
--

CREATE TABLE `habilitacion_personal` (
  `idhabilitacion_personal` int(11) NOT NULL,
  `id_postulante` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('ACTIVO','CESADO') NOT NULL DEFAULT 'ACTIVO',
  `fecha_cese` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habilitacion_personal`
--

INSERT INTO `habilitacion_personal` (`idhabilitacion_personal`, `id_postulante`, `fecha_registro`, `estado`, `fecha_cese`) VALUES
(36, 11, '2025-09-24 11:32:35', 'ACTIVO', NULL),
(37, 12, '2025-09-24 14:52:10', 'CESADO', '2025-10-04'),
(38, 16, '2025-09-30 15:35:04', 'ACTIVO', NULL),
(39, 15, '2025-09-30 15:35:07', 'ACTIVO', NULL),
(40, 17, '2025-09-30 15:35:10', 'ACTIVO', NULL),
(41, 19, '2025-10-02 16:32:29', 'ACTIVO', NULL);

--
-- Disparadores `habilitacion_personal`
--
DELIMITER $$
CREATE TRIGGER `tr_hp_auto_fecha_cese` BEFORE UPDATE ON `habilitacion_personal` FOR EACH ROW BEGIN
  -- Si pasa a CESADO y no viene fecha, pon hoy
  IF NEW.estado = 'CESADO'
     AND OLD.estado <> 'CESADO'
     AND (NEW.fecha_cese IS NULL OR NEW.fecha_cese = '0000-00-00') THEN
    SET NEW.fecha_cese = CURDATE();
  END IF;

  -- Si vuelve a ACTIVO, limpia la fecha
  IF NEW.estado = 'ACTIVO' AND OLD.estado <> 'ACTIVO' THEN
    SET NEW.fecha_cese = NULL;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `idinstitucion` int(11) NOT NULL,
  `nombre_institucion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instituciones`
--

INSERT INTO `instituciones` (`idinstitucion`, `nombre_institucion`) VALUES
(5, 'INSTITUCION 01'),
(6, 'INSTITUCION 02'),
(7, 'INSTITUCION 03'),
(8, 'INSTITUCION 04'),
(9, 'CLINICA 01'),
(10, 'CLINICA 02'),
(11, 'CLINICA 03'),
(12, 'CLINICA 04'),
(13, 'TECSUP'),
(14, 'NATCLAR'),
(15, 'Natclar 001'),
(16, 'Tecsup 001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `id_persona` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` char(8) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id_persona`, `nombres`, `apellidos`, `dni`, `direccion`, `telefono`, `email`, `fecha_nacimiento`) VALUES
(1, 'Erick', 'Peñaayd', '71115880', 'C. los nelumbios 991', '993760069', 'erick.pena@vpice.com', '1999-09-15'),
(9, 'Sleyter', 'Ruiz Montoya', '71107131', NULL, NULL, NULL, NULL),
(16, 'Rogelio', 'Diaz Samam', '78952358', NULL, NULL, NULL, NULL),
(19, 'Samuel', 'Vela Llanos', '42565875', 'C. los nelumbios', '985623569', 'samuel@vpice.com', '2025-08-20'),
(20, 'Carlos', 'Campos Davila', '78512365', 'dsadasdasd', '987523659', 'carlos@gmail.com', '2025-08-22'),
(21, 'Sofia', 'Altamirano Ramirez', '75526684', 'Lima', '985632147', 'erick@vpice.com', '2025-08-14'),
(22, 'Carlos', 'Marin', '755555', 'rgdgdfg', '8675', 'sdsd@gmail.com', '2025-08-28'),
(23, 'Sleyter', 'Ruiz Montoya', '75892568', 'C. los nelumbios 991', '985623578', 'sleyter@gmail.com', '1999-09-15'),
(29, 'WALTER JAVIER ', 'ESQUIVEL  PAREDES', '18822932', 'Calle Espinela 770. Urbanizacion San Isidro. Trujillo', '957201440', 'wesqui-52379@hotmail.com', '1966-02-15'),
(30, 'JUSTO GUILLERMO', ' MAMANI MAMANI ', '73441192', 'J1 de mayo s/n barrio asprovi Antauta', '941614078', 'mjustoguillermo@gmail.com', '2002-02-10'),
(31, 'EDWIN', 'POTOCINO LAIME', '45936267', 'Anco', '945665821', 'edwin.potocinol@gmail.com', '1989-08-28'),
(32, ' OSCAR', ' QUISPE QUISPE', '46412181', 'Cabanaa', '986493893', 'rokaxx18@gmail.com', '1990-06-04'),
(33, 'ROSSEL', ' TURPO CHUSI', '70751125', 'Comunidad campesina puerto arturo Ajoyani', '930837826', 'Rosselturpochusi@gmail.com', '1994-01-15'),
(34, 'Erick ', 'Peña Asencio 01', '44015665', 'C. los nelumbios 991', '956454545', 'erickpena@gmail.com', '1999-09-15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulante`
--

CREATE TABLE `postulante` (
  `id_postulante` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `id_proyectos` int(11) NOT NULL,
  `fecha_postulacion` date NOT NULL,
  `puesto_postulado` varchar(100) NOT NULL,
  `grado_academico` varchar(255) NOT NULL,
  `experiencia` varchar(500) DEFAULT NULL,
  `lugar_residencial` varchar(100) DEFAULT NULL,
  `estado_civil` varchar(100) DEFAULT NULL,
  `disponibilidad` varchar(255) NOT NULL,
  `estado` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulante`
--

INSERT INTO `postulante` (`id_postulante`, `id_persona`, `id_proyectos`, `fecha_postulacion`, `puesto_postulado`, `grado_academico`, `experiencia`, `lugar_residencial`, `estado_civil`, `disponibilidad`, `estado`) VALUES
(8, 19, 1, '2025-08-20', 'Sistemas', 'Titulado', 'Minima', 'Lima', 'Soltero', 'Inmediat', 'Proceso'),
(9, 20, 5, '2025-08-22', 'Contador', 'Bachiller', 'Intermedio', 'Piura', 'Casado', 'Una semana', 'Proceso'),
(10, 21, 1, '2025-08-28', 'CIVIL MECANICO', 'TITULADO', '5 AÑOS', 'Lima', 'Soltero', '10 dias', 'Proceso'),
(11, 22, 1, '2025-08-29', 'fffffff', 'ggbgbgbg', 'gfhgfh', 'ghgfhgfh', 'Soltero', 'gygygy', 'Proceso'),
(12, 23, 1, '2025-09-09', 'Carpintero', 'Bachiller', 'Dos años de experienciaaa', 'Lima', 'Soltero', 'Inmediata', 'Proceso'),
(14, 29, 17, '2025-09-30', 'RESIDENTE', 'Falta en su excel ', 'falta en su excel', 'Lima', 'Casado', 'Falta en su excel', 'Proceso'),
(15, 30, 17, '2025-09-30', 'OFICIAL MECANICO', 'Falta en su excel', 'Falta en su excel', 'Falta en su excel', 'Soltero', 'Falta en su excel', 'Proceso'),
(16, 31, 17, '2025-09-30', 'OPERARIO TUBERO', 'Falta en su excel', 'Falta en su excel', 'Falta en su excel', 'Soltero', 'Falta en su excel', 'Proceso'),
(17, 32, 17, '2025-09-30', 'ELECTRICISTA', 'Falta en el excel', 'Falta en el excel', 'Falta en el excel', 'Casado', 'Falta en el excel', 'Proceso'),
(18, 33, 17, '2025-09-30', 'OFICIAL CIVIL', 'Falta en el excel', 'Falta en el excel', 'Falta en el excel', 'Casado', 'Falta en el excel', 'Proceso'),
(19, 34, 19, '2025-10-02', 'Ingeniero de sistemas', 'Titulado', '2 años', 'Lima', 'Soltero', 'Inmediata ', 'Proceso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulante_cursos`
--

CREATE TABLE `postulante_cursos` (
  `id_postulante_cursos` int(11) NOT NULL,
  `id_postulante` int(11) NOT NULL,
  `idproyecto_institucion_cursos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulante_cursos`
--

INSERT INTO `postulante_cursos` (`id_postulante_cursos`, `id_postulante`, `idproyecto_institucion_cursos`) VALUES
(25, 9, 41),
(33, 14, 44),
(34, 18, 44),
(35, 17, 44),
(36, 15, 43),
(37, 16, 43);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulante_examenmedico`
--

CREATE TABLE `postulante_examenmedico` (
  `id_postulante_examenmedico` int(11) NOT NULL,
  `id_postulante` int(11) DEFAULT NULL,
  `idproyecto_institucion_examenmedico` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulante_examenmedico`
--

INSERT INTO `postulante_examenmedico` (`id_postulante_examenmedico`, `id_postulante`, `idproyecto_institucion_examenmedico`) VALUES
(9, 9, 8),
(14, 16, 15),
(15, 15, 15),
(16, 17, 15),
(18, 14, 15),
(62, 10, 6),
(63, 11, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `id_proyectos` int(11) NOT NULL,
  `nombre_proyecto` varchar(100) DEFAULT NULL,
  `unidad_minera` varchar(200) NOT NULL,
  `compania` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id_proyectos`, `nombre_proyecto`, `unidad_minera`, `compania`) VALUES
(1, 'CAJAMARQUILLA', 'gato', 'perro'),
(5, 'MINSURR', 'erewrwe', 'ewrewr'),
(7, 'Pucamarca', 'erewrwe', 'gggg'),
(9, 'SAN RAFAEL', 'ftrtrtrt', 'fdfsdfsdf'),
(10, 'SAN RAFAEL PROYECTO01', 'rtrtrtr', 'rtrtrt'),
(11, 'SAN RAFAEL PROYECTO02', 'rtrtrtr', 'rtrtrtr'),
(12, 'PROYECTO-MINSUR01', 'rtrtrtrt', 'rtrtrtrt'),
(14, 'FLUJO 7Y8', 'ttttdfdsf', 'ffffff'),
(17, 'XXX-001', 'Pucamarca 001', 'Minsur 001'),
(19, 'Relavera B4', 'U.M San Rafael', 'Minsur SA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_institucion_cursos`
--

CREATE TABLE `proyecto_institucion_cursos` (
  `idproyecto_institucion_cursos` int(11) NOT NULL,
  `id_proyectos` int(11) NOT NULL,
  `idinstitucion` int(11) NOT NULL,
  `idcurso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyecto_institucion_cursos`
--

INSERT INTO `proyecto_institucion_cursos` (`idproyecto_institucion_cursos`, `id_proyectos`, `idinstitucion`, `idcurso`) VALUES
(21, 1, 5, 19),
(22, 1, 5, 20),
(36, 10, 7, 17),
(41, 5, 8, 16),
(43, 17, 16, 23),
(44, 17, 16, 24),
(55, 12, 15, 13),
(64, 19, 14, 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_institucion_examenmedico`
--

CREATE TABLE `proyecto_institucion_examenmedico` (
  `idproyecto_institucion_examenmedico` int(11) NOT NULL,
  `id_proyectos` int(11) NOT NULL,
  `idinstitucion` int(11) NOT NULL,
  `idexamenmedico` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyecto_institucion_examenmedico`
--

INSERT INTO `proyecto_institucion_examenmedico` (`idproyecto_institucion_examenmedico`, `id_proyectos`, `idinstitucion`, `idexamenmedico`) VALUES
(6, 1, 9, 1),
(7, 1, 10, 11),
(8, 5, 10, 2),
(9, 5, 11, 10),
(11, 1, 12, 12),
(12, 1, 12, 13),
(15, 17, 15, 15),
(31, 17, 12, 8),
(37, 19, 15, 2),
(38, 19, 12, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado_cursos_postulante`
--

CREATE TABLE `resultado_cursos_postulante` (
  `idresultado_cursos_postulante` int(11) NOT NULL,
  `id_postulante_cursos` int(11) NOT NULL,
  `fecha_resultado` date NOT NULL,
  `resultado` varchar(50) NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resultado_cursos_postulante`
--

INSERT INTO `resultado_cursos_postulante` (`idresultado_cursos_postulante`, `id_postulante_cursos`, `fecha_resultado`, `resultado`, `observaciones`) VALUES
(37, 25, '2025-09-27', 'Aprobado', 'sdsd'),
(38, 36, '2025-09-30', 'Desaprobado', 'OK'),
(39, 36, '2025-09-30', 'Aprobado', 'OOK'),
(40, 37, '2025-10-01', 'Aprobado', 'OK'),
(41, 35, '2025-10-10', 'Aprobado', 'rere');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado_examenmedico_postulante`
--

CREATE TABLE `resultado_examenmedico_postulante` (
  `idresultado_examenmedico_postulante` int(11) NOT NULL,
  `id_postulante_examenmedico` int(11) NOT NULL,
  `fecha_resultado` date NOT NULL,
  `resultado` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resultado_examenmedico_postulante`
--

INSERT INTO `resultado_examenmedico_postulante` (`idresultado_examenmedico_postulante`, `id_postulante_examenmedico`, `fecha_resultado`, `resultado`, `observaciones`) VALUES
(5, 18, '2025-09-30', 'Apto', 'OK'),
(6, 15, '2025-09-30', 'Apto', '6 meses hemoglobina'),
(7, 14, '2025-10-01', 'No apto', 'enfero'),
(8, 14, '2025-10-08', 'Apto', 'ook'),
(9, 16, '2025-09-30', 'Apto', 'ttt'),
(10, 9, '2025-09-30', 'No apto', 'f');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_curso`
--

CREATE TABLE `tipo_curso` (
  `id_tipo_curso` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_curso`
--

INSERT INTO `tipo_curso` (`id_tipo_curso`, `nombre`) VALUES
(3, 'Curso Específicos'),
(1, 'Curso IND'),
(2, 'Curso RRCC');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_examenmedico`
--

CREATE TABLE `tipo_examenmedico` (
  `idtipo_examenmedico` int(11) NOT NULL,
  `nombre_tipoexamen` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_examenmedico`
--

INSERT INTO `tipo_examenmedico` (`idtipo_examenmedico`, `nombre_tipoexamen`) VALUES
(1, 'TIPO A'),
(2, 'TIPO B'),
(3, 'TIPO C');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_persona`, `username`, `password`, `rol`) VALUES
(2, 1, 'erick', '123456', 'Administrador'),
(10, 9, 'sleyer', '9898', 'Peluquero'),
(12, 16, 'rogelio123', '123456', 'RRHH');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`idcontrato`),
  ADD KEY `fk_contratos_habilitacion_personal` (`idhabilitacion_personal`),
  ADD KEY `fk_contratos_proyectos` (`id_proyectos`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`idcurso`),
  ADD KEY `fk_cursos_tipo` (`id_tipo_curso`);

--
-- Indices de la tabla `examen_medico`
--
ALTER TABLE `examen_medico`
  ADD PRIMARY KEY (`idexamenmedico`),
  ADD KEY `fk_examen_tipo` (`idtipo_examenmedico`);

--
-- Indices de la tabla `habilitacion_personal`
--
ALTER TABLE `habilitacion_personal`
  ADD PRIMARY KEY (`idhabilitacion_personal`),
  ADD KEY `fk_hp_postulante` (`id_postulante`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`idinstitucion`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`id_persona`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indices de la tabla `postulante`
--
ALTER TABLE `postulante`
  ADD PRIMARY KEY (`id_postulante`),
  ADD KEY `id_persona` (`id_persona`),
  ADD KEY `id_proyectos` (`id_proyectos`);

--
-- Indices de la tabla `postulante_cursos`
--
ALTER TABLE `postulante_cursos`
  ADD PRIMARY KEY (`id_postulante_cursos`),
  ADD KEY `fk_postulante` (`id_postulante`),
  ADD KEY `fk_proyecto_institucion_cursos` (`idproyecto_institucion_cursos`);

--
-- Indices de la tabla `postulante_examenmedico`
--
ALTER TABLE `postulante_examenmedico`
  ADD PRIMARY KEY (`id_postulante_examenmedico`),
  ADD KEY `id_postulante` (`id_postulante`),
  ADD KEY `idproyecto_institucion_examenmedico` (`idproyecto_institucion_examenmedico`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id_proyectos`);

--
-- Indices de la tabla `proyecto_institucion_cursos`
--
ALTER TABLE `proyecto_institucion_cursos`
  ADD PRIMARY KEY (`idproyecto_institucion_cursos`),
  ADD KEY `fk_pic_proyecto` (`id_proyectos`),
  ADD KEY `fk_pic_institucion` (`idinstitucion`),
  ADD KEY `fk_pic_curso` (`idcurso`);

--
-- Indices de la tabla `proyecto_institucion_examenmedico`
--
ALTER TABLE `proyecto_institucion_examenmedico`
  ADD PRIMARY KEY (`idproyecto_institucion_examenmedico`),
  ADD KEY `fk_proyecto_examen` (`idexamenmedico`),
  ADD KEY `fk_proyecto_piem` (`id_proyectos`),
  ADD KEY `fk_institucion_piem` (`idinstitucion`);

--
-- Indices de la tabla `resultado_cursos_postulante`
--
ALTER TABLE `resultado_cursos_postulante`
  ADD PRIMARY KEY (`idresultado_cursos_postulante`),
  ADD KEY `fk_resultado_postulante_cursos` (`id_postulante_cursos`);

--
-- Indices de la tabla `resultado_examenmedico_postulante`
--
ALTER TABLE `resultado_examenmedico_postulante`
  ADD PRIMARY KEY (`idresultado_examenmedico_postulante`),
  ADD KEY `fk_postulante_examenmedico` (`id_postulante_examenmedico`);

--
-- Indices de la tabla `tipo_curso`
--
ALTER TABLE `tipo_curso`
  ADD PRIMARY KEY (`id_tipo_curso`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tipo_examenmedico`
--
ALTER TABLE `tipo_examenmedico`
  ADD PRIMARY KEY (`idtipo_examenmedico`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_persona` (`id_persona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `idcontrato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `idcurso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `examen_medico`
--
ALTER TABLE `examen_medico`
  MODIFY `idexamenmedico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `habilitacion_personal`
--
ALTER TABLE `habilitacion_personal`
  MODIFY `idhabilitacion_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `idinstitucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `postulante`
--
ALTER TABLE `postulante`
  MODIFY `id_postulante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `postulante_cursos`
--
ALTER TABLE `postulante_cursos`
  MODIFY `id_postulante_cursos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `postulante_examenmedico`
--
ALTER TABLE `postulante_examenmedico`
  MODIFY `id_postulante_examenmedico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id_proyectos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `proyecto_institucion_cursos`
--
ALTER TABLE `proyecto_institucion_cursos`
  MODIFY `idproyecto_institucion_cursos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `proyecto_institucion_examenmedico`
--
ALTER TABLE `proyecto_institucion_examenmedico`
  MODIFY `idproyecto_institucion_examenmedico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `resultado_cursos_postulante`
--
ALTER TABLE `resultado_cursos_postulante`
  MODIFY `idresultado_cursos_postulante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `resultado_examenmedico_postulante`
--
ALTER TABLE `resultado_examenmedico_postulante`
  MODIFY `idresultado_examenmedico_postulante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tipo_curso`
--
ALTER TABLE `tipo_curso`
  MODIFY `id_tipo_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_examenmedico`
--
ALTER TABLE `tipo_examenmedico`
  MODIFY `idtipo_examenmedico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `fk_contratos_habilitacion_personal` FOREIGN KEY (`idhabilitacion_personal`) REFERENCES `habilitacion_personal` (`idhabilitacion_personal`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contratos_proyectos` FOREIGN KEY (`id_proyectos`) REFERENCES `proyectos` (`id_proyectos`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `fk_cursos_tipo` FOREIGN KEY (`id_tipo_curso`) REFERENCES `tipo_curso` (`id_tipo_curso`);

--
-- Filtros para la tabla `examen_medico`
--
ALTER TABLE `examen_medico`
  ADD CONSTRAINT `fk_examen_tipo` FOREIGN KEY (`idtipo_examenmedico`) REFERENCES `tipo_examenmedico` (`idtipo_examenmedico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `habilitacion_personal`
--
ALTER TABLE `habilitacion_personal`
  ADD CONSTRAINT `fk_hp_postulante` FOREIGN KEY (`id_postulante`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `postulante`
--
ALTER TABLE `postulante`
  ADD CONSTRAINT `postulante_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`),
  ADD CONSTRAINT `postulante_ibfk_2` FOREIGN KEY (`id_proyectos`) REFERENCES `proyectos` (`id_proyectos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `postulante_cursos`
--
ALTER TABLE `postulante_cursos`
  ADD CONSTRAINT `fk_postulante` FOREIGN KEY (`id_postulante`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proyecto_institucion_cursos` FOREIGN KEY (`idproyecto_institucion_cursos`) REFERENCES `proyecto_institucion_cursos` (`idproyecto_institucion_cursos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `postulante_examenmedico`
--
ALTER TABLE `postulante_examenmedico`
  ADD CONSTRAINT `postulante_examenmedico_ibfk_1` FOREIGN KEY (`id_postulante`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `postulante_examenmedico_ibfk_2` FOREIGN KEY (`idproyecto_institucion_examenmedico`) REFERENCES `proyecto_institucion_examenmedico` (`idproyecto_institucion_examenmedico`);

--
-- Filtros para la tabla `proyecto_institucion_cursos`
--
ALTER TABLE `proyecto_institucion_cursos`
  ADD CONSTRAINT `fk_pic_curso` FOREIGN KEY (`idcurso`) REFERENCES `cursos` (`idcurso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pic_institucion` FOREIGN KEY (`idinstitucion`) REFERENCES `instituciones` (`idinstitucion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pic_proyecto` FOREIGN KEY (`id_proyectos`) REFERENCES `proyectos` (`id_proyectos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proyecto_institucion_examenmedico`
--
ALTER TABLE `proyecto_institucion_examenmedico`
  ADD CONSTRAINT `fk_institucion_piem` FOREIGN KEY (`idinstitucion`) REFERENCES `instituciones` (`idinstitucion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proyecto_examen` FOREIGN KEY (`idexamenmedico`) REFERENCES `examen_medico` (`idexamenmedico`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proyecto_piem` FOREIGN KEY (`id_proyectos`) REFERENCES `proyectos` (`id_proyectos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `resultado_cursos_postulante`
--
ALTER TABLE `resultado_cursos_postulante`
  ADD CONSTRAINT `fk_resultado_postulante_cursos` FOREIGN KEY (`id_postulante_cursos`) REFERENCES `postulante_cursos` (`id_postulante_cursos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `resultado_examenmedico_postulante`
--
ALTER TABLE `resultado_examenmedico_postulante`
  ADD CONSTRAINT `fk_postulante_examenmedico` FOREIGN KEY (`id_postulante_examenmedico`) REFERENCES `postulante_examenmedico` (`id_postulante_examenmedico`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
