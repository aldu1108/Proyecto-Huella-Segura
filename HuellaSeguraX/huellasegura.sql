-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-11-2025 a las 03:14:08
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

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
-- Estructura de tabla para la tabla `asistentes_evento`
--

CREATE TABLE `asistentes_evento` (
  `id_asistente` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_union` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistentes_evento`
--

INSERT INTO `asistentes_evento` (`id_asistente`, `id_evento`, `id_usuario`, `fecha_union`) VALUES
(8, 5, 12, '2025-10-21 04:11:13');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(18, '2025-10-07 14:00:00', 'Revisión', 'aceptada', 10, 3),
(19, '2025-10-04 19:00:00', 'Vacunación', 'rechazada', 9, NULL),
(21, '2025-10-22 17:00:00', 'Control', 'aceptada', 13, 3),
(22, '2025-11-02 15:00:00', 'Vacunación', 'pendiente', 14, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento_medico`
--

CREATE TABLE `documento_medico` (
  `id_documento` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `id_historial` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_comunidad`
--

CREATE TABLE `eventos_comunidad` (
  `id_evento` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `hora` time DEFAULT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `estado` varchar(10) NOT NULL,
  `contador_asistentes` int(11) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos_comunidad`
--

INSERT INTO `eventos_comunidad` (`id_evento`, `fecha`, `hora`, `titulo`, `descripcion`, `ubicacion`, `estado`, `contador_asistentes`, `id_mascota`, `id_usuario`) VALUES
(5, '4444-04-04 04:04:00', '04:04:00', '4444444444', '4444444444', '4444444', 'activo', 1, 9, 6);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fichas_de_salud`
--

INSERT INTO `fichas_de_salud` (`id_ficha`, `vacunas`, `esterilizado`, `peso`, `altura`, `documento`, `id_mascota`) VALUES
(1, '', 0, 13, 0, '', 9);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_comunidad`
--

CREATE TABLE `grupos_comunidad` (
  `id_grupo` int(11) NOT NULL,
  `nombre_grupo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `icono` varchar(50) DEFAULT '?',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(10) DEFAULT 'activo',
  `contador_miembros` int(11) NOT NULL DEFAULT 1,
  `id_creador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupos_comunidad`
--

INSERT INTO `grupos_comunidad` (`id_grupo`, `nombre_grupo`, `descripcion`, `icono`, `fecha_creacion`, `estado`, `contador_miembros`, `id_creador`) VALUES
(2, 'Gatos de Madrid', 'Comunidad de amantes de gatos en Madrid', '🐱', '2025-10-07 01:50:40', 'activo', 191, 6),
(3, 'Primeros Auxilios Pet', 'Aprende a cuidar la salud de tu mascota', '🏥', '2025-10-07 01:50:40', 'activo', 158, 6),
(9, 'asdasdas', 'dsadsadsa', '❤️', '2025-10-21 00:50:02', 'activo', 1, 6);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `likes_post`
--

CREATE TABLE `likes_post` (
  `id_like` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `likes_post`
--

INSERT INTO `likes_post` (`id_like`, `id_post`, `id_usuario`, `fecha`) VALUES
(55, 4, 6, '2025-10-22 20:14:52'),
(57, 4, 13, '2025-10-29 21:01:34');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`id_mascota`, `id_usuario`, `tipo`, `sexo`, `nombre_mascota`, `edad_mascota`, `cumpleaños_mascota`, `foto_mascota`, `estado`) VALUES
(1, 5, 'perro', 'hembra', 'Luna', 3, '2021-05-15', 'luna-demo.jpg', 'perdido'),
(2, 5, 'perro', 'hembra', 'morena', 13, '1212-12-12', 'mascota_5_1757988699.png', 'perdido'),
(3, 5, 'gato', 'macho', 'javier', 5, '5555-05-05', 'mascota-default.jpg', 'perdido'),
(4, 5, 'otro', 'macho', 'carlos', 5, '2025-09-05', 'mascota-default.jpg', 'perdido'),
(6, 5, 'perro', 'hembra', 'mora', 11, '2025-09-15', 'mascota-default.jpg', 'activo'),
(7, 5, 'gato', 'hembra', 'dulce', 3, '2025-09-06', 'mascota-default.jpg', 'activo'),
(8, 0, 'perro', 'hembra', 'Lola', 14, '2011-01-01', 'mascota-default.jpg', 'activo'),
(9, 6, 'perro', 'hembra', 'Lola', 14, '2011-01-01', 'mascota_6_1759628874.jpg', 'activo'),
(10, 12, 'perro', 'macho', 'Juan', 3, '2022-01-01', 'mascota-default.jpg', 'activo'),
(13, 6, 'otro', 'macho', 'coqui', 4, '2021-01-01', 'mascota_6_1761174632.jpg', 'activo'),
(14, 13, 'gato', 'macho', 'eze', 7, '2018-01-01', 'mascota-default.jpg', 'activo'),
(15, 15, 'perro', 'macho', 'juan', 6, '2025-10-01', 'mascota-default.jpg', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `miembros_grupo`
--

CREATE TABLE `miembros_grupo` (
  `id_miembro` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_union` datetime NOT NULL DEFAULT current_timestamp(),
  `rol` enum('admin','miembro') DEFAULT 'miembro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `miembros_grupo`
--

INSERT INTO `miembros_grupo` (`id_miembro`, `id_grupo`, `id_usuario`, `fecha_union`, `rol`) VALUES
(7, 3, 6, '2025-10-21 00:11:01', 'miembro'),
(9, 2, 6, '2025-10-21 00:15:35', 'miembro'),
(10, 9, 6, '2025-10-21 00:50:02', 'admin'),
(11, 2, 12, '2025-10-21 00:53:23', 'miembro'),
(13, 3, 12, '2025-10-21 00:59:45', 'miembro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario_destino` int(11) NOT NULL,
  `id_usuario_origen` int(11) DEFAULT NULL,
  `tipo` enum('solicitud_adopcion','comentario_post','like_post','cita_aceptada','cita_rechazada','mascota_encontrada','mensaje') NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `url_relacionada` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario_destino`, `id_usuario_origen`, `tipo`, `titulo`, `mensaje`, `leida`, `url_relacionada`, `fecha_creacion`) VALUES
(1, 13, 14, 'solicitud_adopcion', 'Nueva solicitud de adopción', '<strong>Alguien</strong> quiere adoptar a <strong>eze</strong>', 1, 'mis-solicitudes-adopcion.php', '2025-10-22 02:58:03'),
(2, 13, NULL, 'mensaje', 'Test', 'Esta es una notificación de prueba', 1, NULL, '2025-10-22 03:10:48'),
(3, 13, NULL, 'mensaje', 'Test', 'Esta es una notificación de prueba', 1, NULL, '2025-10-22 03:11:45'),
(4, 13, NULL, 'mensaje', 'Test', 'Esta es una notificación de prueba', 1, NULL, '2025-10-22 03:12:21'),
(5, 13, NULL, 'mensaje', 'Test', 'Esta es una notificación de prueba', 1, NULL, '2025-10-22 03:13:14'),
(6, 13, NULL, 'mensaje', 'Test', 'Esta es una notificación de prueba', 1, NULL, '2025-10-22 03:27:19'),
(7, 13, NULL, 'mensaje', 'Test', 'Esta es una notificación de prueba', 1, NULL, '2025-10-22 03:31:08'),
(8, 13, NULL, 'mensaje', 'Test', 'Esta es una notificación de prueba', 1, NULL, '2025-10-22 03:32:35'),
(9, 13, 14, 'solicitud_adopcion', 'Nueva solicitud de adopción', '<strong>Alguien</strong> quiere adoptar a <strong>eze</strong>', 1, 'mis-solicitudes-adopcion.php', '2025-10-22 03:59:49');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `conteo_comentarios` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `post_comunidad`
--

INSERT INTO `post_comunidad` (`id_post`, `titulo`, `contenido`, `tipo_post`, `imagen_post`, `conteo_likes`, `conteo_comentarios`, `fecha`, `id_usuario`) VALUES
(2, 'Holaa chicos este es el primer post', 'tomaaa el primer poust', 'general', NULL, 0, 0, '2025-10-01 02:52:46', 6),
(10, 'holissss', 'likeen si se ve!!!', 'logro', 'post_6_1759364737_0.jpeg', 0, 0, '2025-10-01 21:25:37', 6);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id_anuncio`, `fecha`, `estado`, `titulo`, `descripcion`, `foto`, `id_mascota`, `id_usuario`) VALUES
(1, '2025-09-16 03:00:00', 'activo', 'Se busca: javier (Gato)', '???? MASCOTA PERDIDA ????\n\nNombre: javier\nTipo: Gato\nFecha perdida: 12/09/2025 a las 07:15\nÚltima ubicación: tandil\n\nDetalles: nosé\n\n???? RECOMPENSA: €50.00\n\n¿Has visto a javier? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 3, 5),
(2, '2025-09-16 03:00:00', 'activo', 'Se busca: morena (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: morena\nTipo: Perro\nFecha perdida: 04/09/2025 a las 07:28\nÚltima ubicación: tandil\n\nDetalles: nosé\n\n???? RECOMPENSA: €300.00\n\n¿Has visto a morena? ¡Contacta inmediatamente! ????', 'mascota_5_1757988699.png', 2, 5),
(3, '2025-09-16 03:00:00', 'activo', 'Se busca: Luna (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: Luna\nTipo: Perro\nFecha perdida: 02/09/2025 a las 07:30\nÚltima ubicación: necochea\n\nDetalles: persiguió a otro perro\n\n¿Has visto a Luna? ¡Contacta inmediatamente! ????', 'luna-demo.jpg', 1, 5),
(4, '2025-09-16 03:00:00', 'activo', 'Se busca: carlos (Otro)', '???? MASCOTA PERDIDA ????\n\nNombre: carlos\nTipo: Otro\nFecha perdida: 05/09/2025 a las 07:34\nÚltima ubicación: tandil\n\nDetalles: nosé\n\n???? RECOMPENSA: €20.00\n\n¿Has visto a carlos? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 4, 5),
(12, '2025-09-24 03:00:00', 'activo', 'En adopción: mora (Perro)', '???? BUSCA HOGAR ????\n\nNombre: mora\nTipo: Perro\nSexo: Hembra\nEdad: 11 años\n\nMotivo: cccccc\n\nCondiciones de adopción:\nffffff\n\nLugar de entrega: tandil\n\n¿Le darías un hogar lleno de amor a mora? ¡Contáctanos! ❤️', 'mascota-default.jpg', 6, 5),
(13, '2025-09-24 03:00:00', 'activo', 'En adopción: dulce (Gato)', '???? BUSCA HOGAR ????\n\nNombre: dulce\nTipo: Gato\nSexo: Hembra\nEdad: 3 años\n\nMotivo: kkkkkk\n\nCondiciones de adopción:\nfffff\n\nLugar de entrega: tandil\n\n¿Le darías un hogar lleno de amor a dulce? ¡Contáctanos! ❤️', 'mascota-default.jpg', 7, 5),
(15, '2025-10-04 03:24:17', 'activo', 'Se busca: Lola (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: Lola\nTipo: Perro\nFecha perdida: 21/04/2025 a las 03:42\nÚltima ubicación: mi casa\n\nDetalles: fdsfsdsd\n\n???? RECOMPENSA: €333.00\n\n¿Has visto a Lola? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 9, 6),
(18, '2025-11-11 01:49:49', 'activo', 'En adopción: eze (Perro)', '???? BUSCA HOGAR ????\r\n\r\nNombre: eze\r\nTipo: Perro\r\nSexo: Macho\r\nEdad: 7 años\r\n\r\nMotivo: rompe todo\r\n\r\nCondiciones de adopción:\r\npatio\r\n\r\nLugar de entrega: tandil\r\n\r\n¿Le darías un hogar lleno de amor a eze? ¡Contáctanos! ❤️', 'mascota-default.jpg', 14, 13),
(19, '2025-11-11 01:50:00', 'activo', 'Se busca: eze (Perro)', '???? MASCOTA PERDIDA ????\n\nNombre: eze\nTipo: Gato\nFecha perdida: 03/10/2025 a las 07:09\nÚltima ubicación: necochea\n\nDetalles: tormenta\n\n???? RECOMPENSA: €50.00\n\n¿Has visto a eze? ¡Contacta inmediatamente! ????', 'mascota-default.jpg', 14, 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicacion_adopcion`
--

CREATE TABLE `publicacion_adopcion` (
  `id_adopcion` int(11) NOT NULL,
  `condiciones` varchar(50) NOT NULL,
  `lugar_adopcion` varchar(25) NOT NULL,
  `id_publicacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `publicacion_adopcion`
--

INSERT INTO `publicacion_adopcion` (`id_adopcion`, `condiciones`, `lugar_adopcion`, `id_publicacion`) VALUES
(1, 'le gusta los interiores', 'tandil', 7),
(2, 'ffffff', 'tandil', 12),
(3, 'fffff', 'tandil', 13),
(7, 'patio', 'tandil', 18);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `publicacion_perdida`
--

INSERT INTO `publicacion_perdida` (`id_perdida`, `ultima_ubicacion`, `fecha_perdida`, `recompensa`, `id_publicacion`) VALUES
(1, 'tandil', '2025-09-12 03:00:00', 50, 0),
(2, 'tandil', '2025-09-04 03:00:00', 300, 2),
(3, 'necochea', '2025-09-02 03:00:00', 0, 3),
(4, 'tandil', '2025-09-05 03:00:00', 20, 4),
(5, 'necochea', '2025-09-05 03:00:00', 0, 5),
(6, 'tandil', '2025-09-13 03:00:00', 49, 6),
(7, 'mi casa', '2025-04-21 03:00:00', 333, 15),
(8, 'necochea', '2025-10-03 10:09:00', 50, 19);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recordatorios_personales`
--

INSERT INTO `recordatorios_personales` (`id_recordatorio`, `titulo`, `descripcion`, `fecha`, `id_usuario`, `completado`) VALUES
(1, 'pastillita', 'para lola', '2025-02-21 12:54:00', 6, 0),
(2, 'pastillita', 'jiji', '2025-10-05 12:54:00', 6, 0),
(3, 'pastillita', 'jiji', '2025-10-05 12:54:00', 6, 0),
(8, 'paseo', 'gfdgdfg', '2025-11-12 15:31:00', 13, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recordatorio_mascota`
--

CREATE TABLE `recordatorio_mascota` (
  `id` int(11) NOT NULL,
  `id_recordatorio` int(11) NOT NULL,
  `id_mascota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recordatorio_mascota`
--

INSERT INTO `recordatorio_mascota` (`id`, `id_recordatorio`, `id_mascota`) VALUES
(1, 2, 9),
(2, 4, 9),
(3, 5, 9),
(4, 6, 13),
(5, 7, 9),
(6, 8, 14);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_peso`
--

CREATE TABLE `seguimiento_peso` (
  `id_peso` int(11) NOT NULL,
  `peso` decimal(5,2) NOT NULL,
  `fecha` date NOT NULL,
  `id_mascota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `seguimiento_peso`
--

INSERT INTO `seguimiento_peso` (`id_peso`, `peso`, `fecha`, `id_mascota`) VALUES
(1, 12.00, '2025-04-05', 9),
(2, 16.00, '2025-10-05', 9),
(3, 15.00, '2025-10-02', 9),
(4, 2.00, '2024-08-15', 9),
(5, 34.00, '2025-10-29', 14),
(6, 20.00, '2025-10-08', 14);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitud_adopcion`
--

INSERT INTO `solicitud_adopcion` (`id_solicitud`, `fecha`, `estado`, `descripcion`, `id_usuario`, `id_adopcion`) VALUES
(5, '2025-10-22 03:59:49', 'pendiente', 'kkkkkk\n\nInformación adicional: jjjjjj', 14, 7);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `email_usuario`, `contraseña_usuario`, `telefono_usuario`, `nombre_usuario`, `apellido_usuario`, `foto_usuario`, `estado`, `rol`) VALUES
(6, 'paula06@gmail.com', 'PaulaG2006', '2494234567', 'Paula', 'Gonzalez', 'usuario-default.jpg', 'activo', 'usuario'),
(7, 'veterinariahs@gmail.com', 'HuellaSegura01', '2494123456', 'Veterinario', 'Huella Segura', 'veterinario-default.jpg', 'activo', 'veterinario'),
(12, 'valen@gmail.com', '123456', '111111', 'Valen', 'Michou', 'usuario-default.jpg', 'activo', 'usuario'),
(13, 'vitovignoli2006@gmail.com', '123456', '02494634099', 'vito', 'vignoli', 'usuario-default.jpg', 'activo', 'usuario'),
(14, 'dantevignoli2011@gmail.com', '1234567', '02494634099', 'dante', 'vignoli', 'usuario-default.jpg', 'activo', 'usuario'),
(15, 'juamba@gmail.com', '123456789', '5676756763', 'juamba', 'perez', 'usuario-default.jpg', 'activo', 'usuario'),
(16, 'ector@gmail.com', 'hector123', '123456789', 'hector ', 'alvarez', 'veterinario-default.jpg', 'activo', 'usuario');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `veterinario`
--

INSERT INTO `veterinario` (`id_veterinario`, `certificado`, `especialidad`, `clinica`, `horarios_de_atencion`, `id_usuario`) VALUES
(1, 1, 'Cirugía', 'ramon santamarina', 'lunes a miercoles de 12:0', 2),
(3, 1, 'Cirugía', 'huella segura', 'todos los días', 7),
(4, 1, 'Medicina General', 'huella segura', 'siempre', 8),
(5, 0, 'Cardiología', 'huella segura', 'casi siempre', 9),
(6, 0, 'Dermatología', 'huella segura', 'lun a vie', 10),
(7, 1, 'Cirugía', 'aca', 't', 11),
(8, 0, 'Cirugía', 'ramon santamarina', 'lunes a lunes', 16);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistentes_evento`
--
ALTER TABLE `asistentes_evento`
  ADD PRIMARY KEY (`id_asistente`),
  ADD UNIQUE KEY `unique_asistente` (`id_evento`,`id_usuario`),
  ADD KEY `idx_asistente_evento` (`id_evento`),
  ADD KEY `idx_asistente_usuario` (`id_usuario`);

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
-- Indices de la tabla `grupos_comunidad`
--
ALTER TABLE `grupos_comunidad`
  ADD PRIMARY KEY (`id_grupo`),
  ADD KEY `fk_grupo_creador` (`id_creador`);

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
-- Indices de la tabla `miembros_grupo`
--
ALTER TABLE `miembros_grupo`
  ADD PRIMARY KEY (`id_miembro`),
  ADD UNIQUE KEY `unique_miembro` (`id_grupo`,`id_usuario`),
  ADD KEY `fk_miembro_grupo` (`id_grupo`),
  ADD KEY `fk_miembro_usuario` (`id_usuario`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_usuario_destino` (`id_usuario_destino`),
  ADD KEY `idx_leida` (`leida`),
  ADD KEY `idx_fecha` (`fecha_creacion`),
  ADD KEY `fk_notif_usuario_origen` (`id_usuario_origen`);

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
-- Indices de la tabla `recordatorio_mascota`
--
ALTER TABLE `recordatorio_mascota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recordatorio` (`id_recordatorio`),
  ADD KEY `fk_mascota` (`id_mascota`);

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
-- Indices de la tabla `seguimiento_peso`
--
ALTER TABLE `seguimiento_peso`
  ADD PRIMARY KEY (`id_peso`),
  ADD KEY `fk_peso_mascota` (`id_mascota`);

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
-- AUTO_INCREMENT de la tabla `asistentes_evento`
--
ALTER TABLE `asistentes_evento`
  MODIFY `id_asistente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `citas_veterinarias`
--
ALTER TABLE `citas_veterinarias`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `comentarios_comunidad`
--
ALTER TABLE `comentarios_comunidad`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `documento_medico`
--
ALTER TABLE `documento_medico`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_comunidad`
--
ALTER TABLE `eventos_comunidad`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `fichas_de_salud`
--
ALTER TABLE `fichas_de_salud`
  MODIFY `id_ficha` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupos_comunidad`
--
ALTER TABLE `grupos_comunidad`
  MODIFY `id_grupo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `likes_post`
--
ALTER TABLE `likes_post`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id_mascota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `miembros_grupo`
--
ALTER TABLE `miembros_grupo`
  MODIFY `id_miembro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `post_comunidad`
--
ALTER TABLE `post_comunidad`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id_anuncio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `publicacion_adopcion`
--
ALTER TABLE `publicacion_adopcion`
  MODIFY `id_adopcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `publicacion_perdida`
--
ALTER TABLE `publicacion_perdida`
  MODIFY `id_perdida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `recordatorios_personales`
--
ALTER TABLE `recordatorios_personales`
  MODIFY `id_recordatorio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `recordatorio_mascota`
--
ALTER TABLE `recordatorio_mascota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `seguimiento_peso`
--
ALTER TABLE `seguimiento_peso`
  MODIFY `id_peso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `solicitud_adopcion`
--
ALTER TABLE `solicitud_adopcion`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `veterinario`
--
ALTER TABLE `veterinario`
  MODIFY `id_veterinario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistentes_evento`
--
ALTER TABLE `asistentes_evento`
  ADD CONSTRAINT `fk_asistente_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos_comunidad` (`id_evento`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_asistente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_usuario_destino` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notif_usuario_origen` FOREIGN KEY (`id_usuario_origen`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
