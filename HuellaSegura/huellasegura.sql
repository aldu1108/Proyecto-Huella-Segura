-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-09-2025 a las 05:46:54
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
-- Base de datos: `aa`
--

CREATE DATABASE huellasegura;
USE huellasegura;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas_veterinarias`
--

CREATE TABLE `citas_veterinarias` (
  `id_cita` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` varchar(30) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_veterinario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `contenido` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
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
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL,
  `fecha` date NOT NULL,
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
  `fecha` date NOT NULL,
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
  `fecha` date NOT NULL,
  `diagnostico` varchar(25) NOT NULL,
  `tratamiento` varchar(50) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_veterinario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opinion_veterinario`
--

CREATE TABLE `opinion_veterinario` (
  `id_opinion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `comentario` varchar(100) NOT NULL,
  `puntuacion` tinyint(4) NOT NULL,
  `id_veterinario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paseos`
--

CREATE TABLE `paseos` (
  `id_paseo` int(11) NOT NULL,
  `fecha` date NOT NULL,
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
  `titulo` varchar(50) NOT NULL,
  `contenido` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id_anuncio` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(10) NOT NULL,
  `titulo` varchar(25) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicacion_perdida`
--

CREATE TABLE `publicacion_perdida` (
  `id_perdida` int(11) NOT NULL,
  `ultima_ubicacion` varchar(25) NOT NULL,
  `fecha_perdida` date NOT NULL,
  `recompensa` float NOT NULL,
  `id_publicacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
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
  `fecha` date NOT NULL,
  `estado` varchar(10) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_adopcion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
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
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
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
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documento_medico`
--
ALTER TABLE `documento_medico`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
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
-- AUTO_INCREMENT de la tabla `historiales_medicos`
--
ALTER TABLE `historiales_medicos`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id_mascota` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `opinion_veterinario`
--
ALTER TABLE `opinion_veterinario`
  MODIFY `id_opinion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paseos`
--
ALTER TABLE `paseos`
  MODIFY `id_paseo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `post_comunidad`
--
ALTER TABLE `post_comunidad`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id_anuncio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicacion_adopcion`
--
ALTER TABLE `publicacion_adopcion`
  MODIFY `id_adopcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicacion_perdida`
--
ALTER TABLE `publicacion_perdida`
  MODIFY `id_perdida` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitud_adopcion`
--
ALTER TABLE `solicitud_adopcion`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `veterinario`
--
ALTER TABLE `veterinario`
  MODIFY `id_veterinario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas_veterinarias`
--
ALTER TABLE `citas_veterinarias`
  ADD CONSTRAINT `fk_cita_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  ADD CONSTRAINT `fk_cita_veterinario` FOREIGN KEY (`id_veterinario`) REFERENCES `veterinario` (`id_veterinario`);

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `fk_comentario_post` FOREIGN KEY (`id_post`) REFERENCES `post_comunidad` (`id_post`),
  ADD CONSTRAINT `fk_comentario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `documento_medico`
--
ALTER TABLE `documento_medico`
  ADD CONSTRAINT `fk_documento_historial` FOREIGN KEY (`id_historial`) REFERENCES `historiales_medicos` (`id_historial`);

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_evento_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  ADD CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `fk_gasto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `historiales_medicos`
--
ALTER TABLE `historiales_medicos`
  ADD CONSTRAINT `fk_historial_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  ADD CONSTRAINT `fk_historial_veterinario` FOREIGN KEY (`id_veterinario`) REFERENCES `veterinario` (`id_veterinario`);

--
-- Filtros para la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD CONSTRAINT `fk_mascota_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `opinion_veterinario`
--
ALTER TABLE `opinion_veterinario`
  ADD CONSTRAINT `fk_opinion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_opinion_veterinario` FOREIGN KEY (`id_veterinario`) REFERENCES `veterinario` (`id_veterinario`);

--
-- Filtros para la tabla `paseos`
--
ALTER TABLE `paseos`
  ADD CONSTRAINT `fk_paseo_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  ADD CONSTRAINT `fk_paseo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `post_comunidad`
--
ALTER TABLE `post_comunidad`
  ADD CONSTRAINT `fk_post_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `fk_publicacion_mascota` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`),
  ADD CONSTRAINT `fk_publicacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `publicacion_adopcion`
--
ALTER TABLE `publicacion_adopcion`
  ADD CONSTRAINT `fk_adopcion_publicacion` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id_anuncio`);

--
-- Filtros para la tabla `publicacion_perdida`
--
ALTER TABLE `publicacion_perdida`
  ADD CONSTRAINT `fk_perdida_publicacion` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id_anuncio`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reporte_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `comentarios` (`id_comentario`),
  ADD CONSTRAINT `fk_reporte_post` FOREIGN KEY (`id_post`) REFERENCES `post_comunidad` (`id_post`),
  ADD CONSTRAINT `fk_reporte_publicacion` FOREIGN KEY (`id_publicaciones`) REFERENCES `publicaciones` (`id_anuncio`),
  ADD CONSTRAINT `fk_reporte_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `solicitud_adopcion`
--
ALTER TABLE `solicitud_adopcion`
  ADD CONSTRAINT `fk_solicitud_adopcion` FOREIGN KEY (`id_adopcion`) REFERENCES `publicacion_adopcion` (`id_adopcion`),
  ADD CONSTRAINT `fk_solicitud_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `veterinario`
--
ALTER TABLE `veterinario`
  ADD CONSTRAINT `fk_veterinario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
