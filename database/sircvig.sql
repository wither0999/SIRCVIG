-- ==============================================================================
-- BASE DE DATOS: sircvig
-- Fecha: 04-02-2026
-- Descripción: Script de estructura y datos iniciales ordenado por dependencias.
-- ==============================================================================

-- 1. CONFIGURACIÓN DE ENTORNO
SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT;
SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS;
SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION;
SET NAMES utf8mb4;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- 2. CREACIÓN DE BASE DE DATOS
DROP DATABASE IF EXISTS `sircvig`;
CREATE DATABASE IF NOT EXISTS `sircvig` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sircvig`;

-- 3. ELIMINACIÓN DE TABLAS (LIMPIEZA)
DROP TABLE IF EXISTS `asignaciones`;
DROP TABLE IF EXISTS `documentos`;
DROP TABLE IF EXISTS `puestos`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `vigilantes`;
DROP TABLE IF EXISTS `clientes`;
DROP TABLE IF EXISTS `roles`;

-- ==============================================================================
-- 4. CREACIÓN DE TABLAS (ORDEN DE DEPENDENCIA)
-- ==============================================================================

--
-- TABLA 1: ROLES (Independiente)
--
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- TABLA 2: CLIENTES (Independiente)
--
CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `rif_cedula` varchar(20) NOT NULL,
  `nombre_cliente` varchar(200) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `parroquia` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `rif_cedula` (`rif_cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- TABLA 3: VIGILANTES (Independiente)
--
CREATE TABLE `vigilantes` (
  `id_vigilante` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `parroquia` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estatus` enum('Aspirante','Activo','Inactivo') DEFAULT 'Aspirante',
  `fecha_ingreso` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_vigilante`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `idx_vigilante_estatus` (`estatus`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- TABLA 4: USUARIOS (Depende de roles)
--
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `pregunta_seguridad` varchar(255) DEFAULT NULL,
  `respuesta_seguridad` varchar(255) DEFAULT NULL,
  `pregunta_seguridad_2` varchar(255) DEFAULT NULL,
  `respuesta_seguridad_2` varchar(255) DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `estado_cuenta` enum('Activa','Bloqueada') DEFAULT 'Activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `email` (`email`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- TABLA 5: PUESTOS (Depende de clientes)
--
CREATE TABLE `puestos` (
  `id_puesto` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) DEFAULT NULL,
  `nombre_cliente` varchar(200) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_puesto`),
  KEY `fk_puesto_cliente` (`id_cliente`),
  CONSTRAINT `fk_puesto_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- TABLA 6: DOCUMENTOS (Depende de vigilantes)
--
CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL AUTO_INCREMENT,
  `cedula_vigilante` varchar(20) NOT NULL,
  `tipo_documento` enum('Foto','CedulaEscaneada','Antecedentes') NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_documento`),
  KEY `cedula_vigilante` (`cedula_vigilante`),
  KEY `idx_documento_tipo` (`tipo_documento`),
  CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`cedula_vigilante`) REFERENCES `vigilantes` (`cedula`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- TABLA 7: ASIGNACIONES (Depende de vigilantes y puestos)
--
CREATE TABLE `asignaciones` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `cedula_vigilante` varchar(20) NOT NULL,
  `id_puesto` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `rol_guardia` varchar(50) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Activa','Completada','Cancelada','Eliminada','Caducada') DEFAULT 'Activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_asignacion`),
  KEY `idx_vigilante_fechas` (`cedula_vigilante`,`fecha_inicio`,`fecha_fin`),
  KEY `idx_puesto_fechas` (`id_puesto`,`fecha_inicio`,`fecha_fin`),
  KEY `idx_asignacion_estatus` (`estatus`),
  CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`cedula_vigilante`) REFERENCES `vigilantes` (`cedula`),
  CONSTRAINT `asignaciones_ibfk_2` FOREIGN KEY (`id_puesto`) REFERENCES `puestos` (`id_puesto`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 5. POBLADO DE DATOS (DUMP)
-- ==============================================================================

-- VOLCANDO DATOS: roles
LOCK TABLES `roles` WRITE;
INSERT INTO `roles` VALUES 
(1,'Administrador','Acceso completo al sistema','2026-01-23 22:35:09'),
(2,'Secretario','Gestión de expedientes y asignaciones','2026-01-23 22:35:09'),
(3,'Supervisor','Supervisión y consulta de información','2026-01-23 22:35:09');
UNLOCK TABLES;

-- VOLCANDO DATOS: clientes
LOCK TABLES `clientes` WRITE;
-- (Sin datos)
UNLOCK TABLES;

-- VOLCANDO DATOS: vigilantes
LOCK TABLES `vigilantes` WRITE;
-- (Sin datos)
UNLOCK TABLES;

-- VOLCANDO DATOS: usuarios
LOCK TABLES `usuarios` WRITE;
INSERT INTO `usuarios` VALUES 
(1,'admin',NULL,NULL,NULL,NULL,NULL,'6287e4a4aa6c51bc688dcd28248422a826aab1cce97df5cd195be18c8cab85dd',NULL,NULL,NULL,NULL,1,0,'Activa','2026-01-23 22:35:10','2026-01-26 01:05:18'),
(3,'JhortM','V-33476677','Jhort','Moreno','jhortmoreno02@gmail.com','04248476696','21f9679a33a14701abb7d95cc25e018c2ec6bc4fe5a9b03ad486a96734055ab6','¿Cuál es el nombre de tu primera mascota?','3ae643d65f437d9103f74f38ab66465aafb6e1b521a1662359d5165d447ce494','¿Cómo se llamaba tu primer jefe?','548831b7e4aec2af721010c520de7fd622a22d96b6d51a26e53614c330ae1c21',1,0,'Activa','2026-01-24 22:00:44','2026-02-01 23:32:23');
UNLOCK TABLES;

-- VOLCANDO DATOS: puestos
LOCK TABLES `puestos` WRITE;
-- (Sin datos)
UNLOCK TABLES;

-- VOLCANDO DATOS: documentos
LOCK TABLES `documentos` WRITE;
-- (Sin datos)
UNLOCK TABLES;

-- VOLCANDO DATOS: asignaciones
LOCK TABLES `asignaciones` WRITE;
-- (Sin datos)
UNLOCK TABLES;

-- 6. RESTAURACIÓN DE ENTORNO
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT;
SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS;
SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION;
SET SQL_MODE=@OLD_SQL_MODE;

-- FIN DEL SCRIPT
