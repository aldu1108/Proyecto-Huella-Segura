-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-10-2025 a las 01:50:03
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
-- Base de datos: `huellasegura`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas_veterinarias`
--

CREATE TABLE `citas_veterinarias` (
  `id_cita` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(30) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_veterinario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas_veterinarias`
--

INSERT INTO `citas_veterinarias` (`id_cita`, `fecha`, `motivo`, `estado`, `id_mascota`, `id_veterinario`) VALUES
(1, '2025-09-25 03:00:00', 'Vacunación', 'programada', 6, 1),
(2, '2025-09-25 03:00:00', 'Consulta General', 'programada', 8, 1),
(3, '2025-09-23 03:00:00', 'Consulta General', 'programada', 8, 1),
(4, '2025-09-30 03:00:00', 'Consulta General', 'programada', 9, 1),
(10, '2025-09-28 03:00:00', 'Vacunación', 'programada', 9, 1),
(11, '2025-10-03 13:00:00', 'Vacunación', 'programada', 9, 1),
(12, '2025-10-02 18:00:00', 'Cirugía', 'programada', 9, 1),
(13, '2025-10-02 19:00:00', 'Vacunación', 'programada', 9, 1),
(17, '2025-10-05 14:00:00', 'Consulta General', 'rechazada', 10, NULL),
(18, '2025-10-07 14:00:00', 'Revisión', 'aceptada', 10, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_comunidad`
--

CREATE TABLE `comentarios_comunidad` (
  `id_comentario` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_post` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento_medico`
--

CREATE TABLE `documento_medico` (
  `id_documento` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `id_historial` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_comunidad`
--

CREATE TABLE `eventos_comunidad` (
  `id_evento` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `titulo` varchar(15) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichas_de_salud`
--

CREATE TABLE `fichas_de_salud` (
  `id_ficha` int(11) NOT NULL,
  `vacunas` varchar(60) NOT NULL,
  `esterilizado` tinyint(1) NOT NULL,
  `peso` float NOT NULL,
  `altura` int(11) NOT NULL,
  `documento` varchar(50) NOT NULL,
  `id_mascota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `monto` float NOT NULL,
  `Titulo` varchar(20) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historiales_medicos`
--

CREATE TABLE `historiales_medicos` (
  `id_historial` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `diagnostico` varchar(25) NOT NULL,
  `tratamiento` varchar(50) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_veterinario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `likes_post`
--

CREATE TABLE `likes_post` (
  `id_like` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `likes_post`
--

INSERT INTO `likes_post` (`id_like`, `id_post`, `id_usuario`, `fecha`) VALUES
(37, 8, 6, '2025-10-01 04:03:57'),
(41, 8, 7, '2025-10-01 04:13:38'),
(44, 9, 7, '2025-10-01 04:13:44'),
(46, 9, 6, '2025-10-01 21:22:11'),
(47, 5, 6, '2025-10-01 21:22:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `id_mascota` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `nombre_mascota` varchar(20) NOT NULL,
  `edad_mascota` int(11) NOT NULL,
  `cumpleaños_mascota` date NOT NULL,
  `foto_mascota` varchar(255) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`id_mascota`, `id_usuario`, `tipo`, `sexo`, `nombre_mascota`, `edad_mascota`, `cumpleaños_mascota`, `foto_mascota`, `estado`) VALUES
(1, 5, 'perro', 'hembra', 'Luna', 3, '2021-05-15', 'luna-demo.jpg', 'perdido'),
(2, 5, 'perro', 'hembra', 'morena', 13, '1212-12-12', 'mascota_5_1757988699.png', 'perdido'),
(3, 5, 'gato', 'macho', 'javier', 5, '5555-05-05', 'mascota-default.jpg', 'perdido'),
(4, 5, 'otro', 'macho', 'carlos', 5, '2025-09-05', 'mascota-default.jpg', 'perdido'),
(5, 5, 'perro', 'macho', 'menem', 8, '2025-08-31', 'mascota-default.jpg', 'activo'),
(6, 5, 'perro', 'hembra', 'mora', 11, '2025-09-15', 'mascota-default.jpg', 'activo'),
(7, 5, 'gato', 'hembra', 'dulce', 3, '2025-09-06', 'mascota-default.jpg', 'activo'),
(8, 0, 'perro', 'hembra', 'Lola', 14, '2011-01-01', 'mascota-default.jpg', 'activo'),
(9, 6, 'perro', 'hembra', 'Lola', 14, '2011-01-01', 'mascota-default.jpg', 'activo'),
(10, 12, 'perro', 'macho', 'Juan', 3, '2022-01-01', 'mascota-default.jpg', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opinion_veterinario`
--

CREATE TABLE `opinion_veterinario` (
  `id_opinion` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `comentario` varchar(100) NOT NULL,
  `puntuacion` tinyint(4) NOT NULL,
  `id_veterinario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paseos`
--

CREATE TABLE `paseos` (
  `id_paseo` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `duracion` int(11) NOT NULL,
  `recorrido` double NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `post_comunidad`
--

CREATE TABLE `post_comunidad` (
  `id_post` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `contenido` text NOT NULL,
  `tipo_post` enum('logro','paseo','ayuda','general') DEFAULT 'general',
  `imagen_post` text DEFAULT NULL,
  `conteo_likes` int(11) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `post_comunidad`
--

INSERT INTO `post_comunidad` (`id_post`, `titulo`, `contenido`, `tipo_post`, `imagen_post`, `conteo_likes`, `fecha`, `id_usuario`) VALUES
(1, 'aaaaaaaa', 'aaaaaaaaaaaaaaaaaaaaaaa', 'general', NULL, 0, '2025-10-01 02:51:34', 6),
(2, 'Holaa chicos este es el primer post', 'tomaaa el primer poust', 'general', NULL, 0, '2025-10-01 02:52:46', 6),
(3, 'holaaa', 'aaaaaaaaaaaa', 'general', 'post_6_1759300344_0.jpeg', 0, '2025-10-01 03:32:24', 6),
(4, 'aaaaaaaaaaaa', 'aaaaaaaaaaaaa', 'general', 'post_6_1759300406_0.jpg', 0, '2025-10-01 03:33:26', 6),
(5, 'aaaaaaaa', 'aaaaaaaaaaaaaaaa', 'general', 'post_6_1759300421_0.jpg,post_6_1759300421_1.jpeg,post_6_1759300421_2.jpg', 1, '2025-10-01 03:33:41', 6),
(6, 'se logro el poust', 'faltan los likes', 'logro', NULL, 0, '2025-10-01 03:36:23', 6),
(7, 'test', 'aaaaaaaaaaaa', 'paseo', NULL, 0, '2025-10-01 03:36:42', 6),
(8, 'ayuden', 'aaaaaaaaaaaaa', 'ayuda', NULL, 2, '2025-10-01 03:36:52', 6),
(9, 'aaaaaaaaaaaa', 'bbbbbbbbbbbbb', 'general', NULL, 2, '2025-10-01 04:01:49', 6),
(10, 'holissss', 'likeen si se ve!!!', 'logro', 'post_6_1759364737_0.jpeg', 0, '2025-10-01 21:25:37', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id_anuncio` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` varchar(10) NOT NULL,
  `titulo` varchar(25) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id_anuncio`, `fecha`, `estado`, `titulo`, `descripcion`, `foto`, `id_mascota`, `id_usuario`) VALUES
(1, '2025-09-16 03:00:00', 'activo', 'Se busca: javier (Gato)', '???? MASCOTA PERDIDA ????\n\nNombre: javier\nTipo: Gato\nFecha perdida: 12/09/2025 a las 07:15\nÚltima ubicación: tandil\n\nDetalles: nosé\n\n???? RECOMPENSA: €50.00\n\n¿Has visto a javier? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 3, 5),
(2, '2025-09-16 03:00:00', 'activo', 'Se busca: morena (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: morena\nTipo: Perro\nFecha perdida: 04/09/2025 a las 07:28\nÚltima ubicación: tandil\n\nDetalles: nosé\n\n???? RECOMPENSA: €300.00\n\n¿Has visto a morena? ¡Contacta inmediatamente! ????', 'mascota_5_1757988699.png', 2, 5),
(3, '2025-09-16 03:00:00', 'activo', 'Se busca: Luna (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: Luna\nTipo: Perro\nFecha perdida: 02/09/2025 a las 07:30\nÚltima ubicación: necochea\n\nDetalles: persiguió a otro perro\n\n¿Has visto a Luna? ¡Contacta inmediatamente! ????', 'luna-demo.jpg', 1, 5),
(4, '2025-09-16 03:00:00', 'activo', 'Se busca: carlos (Otro)', '???? MASCOTA PERDIDA ????\n\nNombre: carlos\nTipo: Otro\nFecha perdida: 05/09/2025 a las 07:34\nÚltima ubicación: tandil\n\nDetalles: nosé\n\n???? RECOMPENSA: €20.00\n\n¿Has visto a carlos? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 4, 5),
(5, '2025-09-16 03:00:00', 'activo', 'Se busca: menem (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: menem\nTipo: Perro\nFecha perdida: 05/09/2025 a las 06:50\nÚltima ubicación: necochea\n\nDetalles: nosé\n\n¿Has visto a menem? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 5, 5),
(6, '2025-09-16 03:00:00', 'activo', 'Se busca: menem (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: menem\nTipo: Perro\nFecha perdida: 13/09/2025 a las 17:40\nÚltima ubicación: tandil\n\nDetalles: nosé\n\n???? RECOMPENSA: €49.00\n\n¿Has visto a menem? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 5, 5),
(7, '2025-09-16 03:00:00', 'activo', 'En adopción: menem (Perro', '???? BUSCA HOGAR ????\n\nNombre: menem\nTipo: Perro\nSexo: Macho\nEdad: 8 años\n\nMotivo: no puedo cuidarlo\n\nCondiciones de adopción:\nle gusta los interiores\n\nLugar de entrega: tandil\n\n¿Le darías un hogar lleno de amor a menem? ¡Contáctanos! ❤️', 'mascota-default.jpg', 5, 5),
(12, '2025-09-24 03:00:00', 'activo', 'En adopción: mora (Perro)', '???? BUSCA HOGAR ????\n\nNombre: mora\nTipo: Perro\nSexo: Hembra\nEdad: 11 años\n\nMotivo: cccccc\n\nCondiciones de adopción:\nffffff\n\nLugar de entrega: tandil\n\n¿Le darías un hogar lleno de amor a mora? ¡Contáctanos! ❤️', 'mascota-default.jpg', 6, 5),
(13, '2025-09-24 03:00:00', 'activo', 'En adopción: dulce (Gato)', '???? BUSCA HOGAR ????\n\nNombre: dulce\nTipo: Gato\nSexo: Hembra\nEdad: 3 años\n\nMotivo: kkkkkk\n\nCondiciones de adopción:\nfffff\n\nLugar de entrega: tandil\n\n¿Le darías un hogar lleno de amor a dulce? ¡Contáctanos! ❤️', 'mascota-default.jpg', 7, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicacion_adopcion`
--

CREATE TABLE `publicacion_adopcion` (
  `id_adopcion` int(11) NOT NULL,
  `condiciones` varchar(50) NOT NULL,
  `lugar_adopcion` varchar(25) NOT NULL,
  `id_publicacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicacion_adopcion`
--

INSERT INTO `publicacion_adopcion` (`id_adopcion`, `condiciones`, `lugar_adopcion`, `id_publicacion`) VALUES
(1, 'le gusta los interiores', 'tandil', 7),
(2, 'ffffff', 'tandil', 12),
(3, 'fffff', 'tandil', 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicacion_perdida`
--

CREATE TABLE `publicacion_perdida` (
  `id_perdida` int(11) NOT NULL,
  `ultima_ubicacion` varchar(25) NOT NULL,
  `fecha_perdida` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recompensa` float NOT NULL,
  `id_publicacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicacion_perdida`
--

INSERT INTO `publicacion_perdida` (`id_perdida`, `ultima_ubicacion`, `fecha_perdida`, `recompensa`, `id_publicacion`) VALUES
(1, 'tandil', '2025-09-12 03:00:00', 50, 0),
(2, 'tandil', '2025-09-04 03:00:00', 300, 2),
(3, 'necochea', '2025-09-02 03:00:00', 0, 3),
(4, 'tandil', '2025-09-05 03:00:00', 20, 4),
(5, 'necochea', '2025-09-05 03:00:00', 0, 5),
(6, 'tandil', '2025-09-13 03:00:00', 49, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recordatorios_personales`
--

CREATE TABLE `recordatorios_personales` (
  `id_recordatorio` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `completado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recordatorios_personales`
--

INSERT INTO `recordatorios_personales` (`id_recordatorio`, `titulo`, `descripcion`, `fecha`, `id_usuario`, `completado`) VALUES
(1, 'pastillita', 'para lola', '2025-02-21 12:54:00', 6, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` varchar(10) NOT NULL,
  `id_publicaciones` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `id_comentario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_adopcion`
--

CREATE TABLE `solicitud_adopcion` (
  `id_solicitud` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` varchar(10) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_adopcion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitud_adopcion`
--

INSERT INTO `solicitud_adopcion` (`id_solicitud`, `fecha`, `estado`, `descripcion`, `id_usuario`, `id_adopcion`) VALUES
(1, '2025-09-24 03:00:00', 'pendiente', 'eeeee\n\nInformación adicional: ddddd', 1, 3),
(2, '2025-09-25 03:00:00', 'pendiente', 'dddddd\n\nInformación adicional: jjjjjjj', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `email_usuario` varchar(100) NOT NULL,
  `contraseña_usuario` varchar(255) NOT NULL,
  `telefono_usuario` varchar(20) NOT NULL,
  `nombre_usuario` varchar(15) NOT NULL,
  `apellido_usuario` varchar(15) NOT NULL,
  `foto_usuario` varchar(255) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `rol` enum('demo','usuario','veterinario','admin','pendiente') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `email_usuario`, `contraseña_usuario`, `telefono_usuario`, `nombre_usuario`, `apellido_usuario`, `foto_usuario`, `estado`, `rol`) VALUES
(6, 'ae@a', 'hola1234', '', 'aa', 'ee', 'usuario-default.jpg', 'activo', 'usuario'),
(7, 'vete@v', 'veterinario', '', 'veterinario', 'iuju', 'veterinario-default.jpg', 'activo', 'veterinario'),
(12, 'valen@gmail.com', '123456', '111111', 'Valen', 'Michou', 'usuario-default.jpg', 'activo', 'usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `veterinario`
--

CREATE TABLE `veterinario` (
  `id_veterinario` int(11) NOT NULL,
  `certificado` tinyint(1) NOT NULL,
  `especialidad` varchar(20) NOT NULL,
  `clinica` varchar(50) NOT NULL,
  `horarios_de_atencion` varchar(25) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `veterinario`
--

INSERT INTO `veterinario` (`id_veterinario`, `certificado`, `especialidad`, `clinica`, `horarios_de_atencion`, `id_usuario`) VALUES
(1, 1, 'Cirugía', 'ramon santamarina', 'lunes a miercoles de 12:0', 2),
(3, 1, 'Cirugía', 'huella segura', 'todos los días', 7),
(4, 1, 'Medicina General', 'huella segura', 'siempre', 8),
(5, 0, 'Cardiología', 'huella segura', 'casi siempre', 9),
(6, 0, 'Dermatología', 'huella segura', 'lun a vie', 10),
(7, 1, 'Cirugía', 'aca', 't', 11);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas_veterinarias`
--
ALTER TABLE `citas_veterinarias`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `fk_cita_mascota` (`id_mascota`),
  ADD KEY `fk_cita_veterinario` (`id_veterinario`);

--
-- Indices de la tabla `comentarios_comunidad`
--
ALTER TABLE `comentarios_comunidad`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `fk_comentario_post` (`id_post`),
  ADD KEY `fk_comentario_usuario` (`id_usuario`);

--
-- Indices de la tabla `documento_medico`
--
ALTER TABLE `documento_medico`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `fk_documento_historial` (`id_historial`);

--
-- Indices de la tabla `eventos_comunidad`
--
ALTER TABLE `eventos_comunidad`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `fk_evento_mascota` (`id_mascota`),
  ADD KEY `fk_evento_usuario` (`id_usuario`);

--
-- Indices de la tabla `fichas_de_salud`
--
ALTER TABLE `fichas_de_salud`
  ADD PRIMARY KEY (`id_ficha`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id_gasto`),
  ADD KEY `fk_gasto_usuario` (`id_usuario`);

--
-- Indices de la tabla `historiales_medicos`
--
ALTER TABLE `historiales_medicos`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_mascota` (`id_mascota`),
  ADD KEY `fk_historial_veterinario` (`id_veterinario`);

--
-- Indices de la tabla `likes_post`
--
ALTER TABLE `likes_post`
  ADD PRIMARY KEY (`id_like`),
  ADD UNIQUE KEY `unique_like` (`id_post`,`id_usuario`),
  ADD KEY `fk_like_post` (`id_post`),
  ADD KEY `fk_like_usuario` (`id_usuario`);

--
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`id_mascota`),
  ADD KEY `fk_mascota_usuario` (`id_usuario`);

--
-- Indices de la tabla `opinion_veterinario`
--
ALTER TABLE `opinion_veterinario`
  ADD PRIMARY KEY (`id_opinion`),
  ADD KEY `fk_opinion_veterinario` (`id_veterinario`),
  ADD KEY `fk_opinion_usuario` (`id_usuario`);

--
-- Indices de la tabla `paseos`
--
ALTER TABLE `paseos`
  ADD PRIMARY KEY (`id_paseo`),
  ADD KEY `fk_paseo_mascota` (`id_mascota`),
  ADD KEY `fk_paseo_usuario` (`id_usuario`);

--
-- Indices de la tabla `post_comunidad`
--
ALTER TABLE `post_comunidad`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `fk_post_usuario` (`id_usuario`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id_anuncio`),
  ADD KEY `fk_publicacion_mascota` (`id_mascota`),
  ADD KEY `fk_publicacion_usuario` (`id_usuario`);

--
-- Indices de la tabla `publicacion_adopcion`
--
ALTER TABLE `publicacion_adopcion`
  ADD PRIMARY KEY (`id_adopcion`),
  ADD KEY `fk_adopcion_publicacion` (`id_publicacion`);

--
-- Indices de la tabla `publicacion_perdida`
--
ALTER TABLE `publicacion_perdida`
  ADD PRIMARY KEY (`id_perdida`),
  ADD KEY `fk_perdida_publicacion` (`id_publicacion`);

--
-- Indices de la tabla `recordatorios_personales`
--
ALTER TABLE `recordatorios_personales`
  ADD PRIMARY KEY (`id_recordatorio`),
  ADD KEY `fk_recordatorio_usuario` (`id_usuario`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_usuario` (`id_usuario`),
  ADD KEY `fk_reporte_publicacion` (`id_publicaciones`),
  ADD KEY `fk_reporte_post` (`id_post`),
  ADD KEY `fk_reporte_comentario` (`id_comentario`);

--
-- Indices de la tabla `solicitud_adopcion`
--
ALTER TABLE `solicitud_adopcion`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `fk_solicitud_usuario` (`id_usuario`),
  ADD KEY `fk_solicitud_adopcion` (`id_adopcion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `veterinario`
--
ALTER TABLE `veterinario`
  ADD PRIMARY KEY (`id_veterinario`),
  ADD KEY `fk_veterinario_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas_veterinarias`
--
ALTER TABLE `citas_veterinarias`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `comentarios_comunidad`
--
ALTER TABLE `comentarios_comunidad`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documento_medico`
--
ALTER TABLE `documento_medico`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_comunidad`
--
ALTER TABLE `eventos_comunidad`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fichas_de_salud`
--
ALTER TABLE `fichas_de_salud`
  MODIFY `id_ficha` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `likes_post`
--
ALTER TABLE `likes_post`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id_mascota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `post_comunidad`
--
ALTER TABLE `post_comunidad`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id_anuncio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `publicacion_adopcion`
--
ALTER TABLE `publicacion_adopcion`
  MODIFY `id_adopcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `publicacion_perdida`
--
ALTER TABLE `publicacion_perdida`
  MODIFY `id_perdida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `recordatorios_personales`
--
ALTER TABLE `recordatorios_personales`
  MODIFY `id_recordatorio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `solicitud_adopcion`
--
ALTER TABLE `solicitud_adopcion`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `veterinario`
--
ALTER TABLE `veterinario`
  MODIFY `id_veterinario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
