-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para colegio
CREATE DATABASE IF NOT EXISTS `colegio` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `colegio`;

-- Volcando estructura para tabla colegio.ajustes_cobro
CREATE TABLE IF NOT EXISTS `ajustes_cobro` (
  `id_ajuste` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_cuenta` bigint unsigned NOT NULL,
  `tipo` enum('DESCUENTO','BECA','EXONERACION','RECARGO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `importe` decimal(12,2) NOT NULL,
  `motivo` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registrado_por` bigint unsigned DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ajuste`),
  KEY `fk_ajuste_cuenta` (`id_cuenta`),
  CONSTRAINT `fk_ajuste_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas_cobrar` (`id_cuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.ajustes_cobro: ~0 rows (aproximadamente)
INSERT INTO `ajustes_cobro` (`id_ajuste`, `id_cuenta`, `tipo`, `importe`, `motivo`, `registrado_por`, `creado_en`) VALUES
	(1, 18, 'DESCUENTO', 50.00, 'Es un familiar.', 1, '2026-08-16 23:15:45');

-- Volcando estructura para tabla colegio.alumno_apoderado
CREATE TABLE IF NOT EXISTS `alumno_apoderado` (
  `id_alumno_apoderado` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_alumno` bigint unsigned NOT NULL,
  `id_apoderado` bigint unsigned NOT NULL,
  `parentesco` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  `responsable_economico` tinyint(1) NOT NULL DEFAULT '0',
  `contacto_emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_alumno_apoderado`),
  UNIQUE KEY `uq_alumno_apoderado` (`id_alumno`,`id_apoderado`),
  KEY `fk_aa_apoderado` (`id_apoderado`),
  CONSTRAINT `fk_aa_alumno` FOREIGN KEY (`id_alumno`) REFERENCES `personas` (`id_persona`),
  CONSTRAINT `fk_aa_apoderado` FOREIGN KEY (`id_apoderado`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.alumno_apoderado: ~4 rows (aproximadamente)
INSERT INTO `alumno_apoderado` (`id_alumno_apoderado`, `id_alumno`, `id_apoderado`, `parentesco`, `es_principal`, `responsable_economico`, `contacto_emergencia`, `estado`) VALUES
	(1, 1, 4, 'Madre', 1, 1, 1, 'ACTIVO'),
	(2, 2, 5, 'Padre', 1, 1, 1, 'ACTIVO'),
	(3, 3, 6, 'Madre', 1, 1, 1, 'ACTIVO'),
	(4, 15, 16, 'Madre', 1, 1, 1, 'ACTIVO');

-- Volcando estructura para tabla colegio.aperturas_caja
CREATE TABLE IF NOT EXISTS `aperturas_caja` (
  `id_apertura` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `monto_apertura` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abierto_por` bigint unsigned DEFAULT NULL,
  `abierto_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('ABIERTA','CERRADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTA',
  PRIMARY KEY (`id_apertura`),
  KEY `ix_apertura_caja_fecha` (`fecha`,`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.aperturas_caja: ~3 rows (aproximadamente)
INSERT INTO `aperturas_caja` (`id_apertura`, `fecha`, `monto_apertura`, `observacion`, `abierto_por`, `abierto_en`, `estado`) VALUES
	(1, '2026-08-18', 100.00, NULL, 1, '2026-08-18 22:37:25', 'CERRADA'),
	(2, '2026-08-18', 200.00, NULL, 3, '2026-08-18 23:14:15', 'CERRADA'),
	(3, '2026-08-18', 100.00, NULL, 3, '2026-08-18 23:18:19', 'CERRADA');

-- Volcando estructura para tabla colegio.areas
CREATE TABLE IF NOT EXISTS `areas` (
  `id_area` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_area`),
  UNIQUE KEY `uq_area_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.areas: ~2 rows (aproximadamente)
INSERT INTO `areas` (`id_area`, `nombre`, `estado`) VALUES
	(1, 'Académica', 'ACTIVO'),
	(2, 'Administración', 'ACTIVO'),
	(3, 'DEMO · Académica', 'ACTIVO');

-- Volcando estructura para tabla colegio.asignaciones_horario
CREATE TABLE IF NOT EXISTS `asignaciones_horario` (
  `id_asignacion` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned NOT NULL,
  `id_jornada` int unsigned NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_asignacion`),
  KEY `fk_asighor_persona` (`id_persona`),
  KEY `fk_asighor_jornada` (`id_jornada`),
  CONSTRAINT `fk_asighor_jornada` FOREIGN KEY (`id_jornada`) REFERENCES `jornadas` (`id_jornada`),
  CONSTRAINT `fk_asighor_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.asignaciones_horario: ~6 rows (aproximadamente)
INSERT INTO `asignaciones_horario` (`id_asignacion`, `id_persona`, `id_jornada`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(1, 9, 3, '2026-08-15', NULL, 'ACTIVO'),
	(2, 13, 3, '2026-08-10', '2026-08-16', 'INACTIVO'),
	(4, 8, 3, '2026-08-16', NULL, 'ACTIVO'),
	(5, 7, 2, '2026-08-16', NULL, 'ACTIVO'),
	(6, 17, 4, '2026-08-16', NULL, 'ACTIVO'),
	(7, 18, 4, '2026-08-16', NULL, 'ACTIVO');

-- Volcando estructura para tabla colegio.asignaturas
CREATE TABLE IF NOT EXISTS `asignaturas` (
  `id_asignatura` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_asignatura`),
  UNIQUE KEY `uq_asignatura` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.asignaturas: ~0 rows (aproximadamente)
INSERT INTO `asignaturas` (`id_asignatura`, `nombre`, `estado`) VALUES
	(1, 'Matemática', 'ACTIVO'),
	(2, 'DEMO · Tutoría', 'ACTIVO');

-- Volcando estructura para tabla colegio.asistencias_diarias
CREATE TABLE IF NOT EXISTS `asistencias_diarias` (
  `id_asistencia` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `id_jornada` int unsigned DEFAULT NULL,
  `id_matricula` bigint unsigned DEFAULT NULL,
  `id_vinculo` bigint unsigned DEFAULT NULL,
  `id_carga` bigint unsigned DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `minutos_tardanza` smallint unsigned NOT NULL DEFAULT '0',
  `minutos_salida_anticipada` smallint unsigned NOT NULL DEFAULT '0',
  `minutos_trabajados` smallint unsigned NOT NULL DEFAULT '0',
  `estado` enum('PUNTUAL','TARDANZA','FALTA','JUSTIFICADO','PERMISO','LICENCIA','SALIDA_ANTICIPADA','INCOMPLETO','FERIADO','NO_LABORABLE','SUSPENDIDO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calculado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `uq_asistencia_alcance` (`id_persona`,`fecha`,`id_jornada`,`id_matricula`,`id_vinculo`,`id_carga`),
  KEY `ix_asistencia_fecha_estado` (`fecha`,`estado`),
  CONSTRAINT `fk_asistencia_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.asistencias_diarias: ~9 rows (aproximadamente)
INSERT INTO `asistencias_diarias` (`id_asistencia`, `id_persona`, `fecha`, `id_jornada`, `id_matricula`, `id_vinculo`, `id_carga`, `hora_entrada`, `hora_salida`, `minutos_tardanza`, `minutos_salida_anticipada`, `minutos_trabajados`, `estado`, `observacion`, `calculado_en`) VALUES
	(1, 2, '2026-08-15', 1, 2, NULL, NULL, '20:58:28', '21:00:57', 783, 0, 2, 'TARDANZA', NULL, '2026-08-15 16:00:57'),
	(2, 1, '2026-08-15', 1, 1, NULL, NULL, '21:01:48', NULL, 786, 0, 0, 'TARDANZA', NULL, '2026-08-15 16:01:48'),
	(3, 9, '2026-08-16', 1, 4, NULL, NULL, '03:46:01', '21:00:20', 0, 0, 1034, 'PUNTUAL', NULL, '2026-08-16 16:00:21'),
	(4, 1, '2026-08-16', 1, 1, NULL, NULL, '03:46:22', NULL, 0, 0, 0, 'PUNTUAL', NULL, '2026-08-15 22:46:23'),
	(5, 2, '2026-08-16', NULL, 2, NULL, NULL, '16:41:00', '21:55:53', 0, 0, 314, 'PUNTUAL', 'Registro manual', '2026-08-16 16:55:55'),
	(6, 8, '2026-08-16', 3, NULL, NULL, NULL, '22:27:57', NULL, 857, 0, 0, 'TARDANZA', NULL, '2026-08-16 17:27:58'),
	(7, 13, '2026-08-16', 3, NULL, NULL, NULL, '22:29:57', '22:59:37', 859, 0, 29, 'TARDANZA', NULL, '2026-08-16 17:59:38'),
	(8, 10, '2026-08-16', 1, 5, NULL, NULL, '18:54:14', '19:02:40', 659, 0, 8, 'TARDANZA', NULL, '2026-08-16 19:02:41'),
	(9, 7, '2026-08-16', 2, NULL, NULL, NULL, '19:29:14', NULL, 709, 0, 0, 'TARDANZA', NULL, '2026-08-16 19:29:15'),
	(10, 15, '2026-08-14', NULL, 6, NULL, NULL, '08:03:00', '13:00:00', 0, 0, 297, 'PUNTUAL', 'DEMO · registro para pruebas', NULL);

-- Volcando estructura para tabla colegio.auditoria
CREATE TABLE IF NOT EXISTS `auditoria` (
  `id_auditoria` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint unsigned DEFAULT NULL,
  `accion` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_entidad` bigint unsigned DEFAULT NULL,
  `valores_antes` json DEFAULT NULL,
  `valores_despues` json DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `ix_auditoria_entidad` (`entidad`,`id_entidad`),
  KEY `ix_auditoria_fecha` (`creado_en`),
  KEY `fk_auditoria_usuario` (`id_usuario`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.auditoria: ~179 rows (aproximadamente)
INSERT INTO `auditoria` (`id_auditoria`, `id_usuario`, `accion`, `entidad`, `id_entidad`, `valores_antes`, `valores_despues`, `ip`, `creado_en`) VALUES
	(1, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 15:39:32'),
	(2, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 2}', '::1', '2026-08-15 15:40:39'),
	(3, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '192.168.100.195', '2026-08-15 15:49:30'),
	(4, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 2}', '192.168.100.195', '2026-08-15 15:58:28'),
	(5, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 2}', '192.168.100.195', '2026-08-15 15:58:29'),
	(6, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 2}', '192.168.100.195', '2026-08-15 15:58:31'),
	(7, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 2}', '192.168.100.195', '2026-08-15 15:58:33'),
	(8, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 2}', '192.168.100.195', '2026-08-15 16:00:57'),
	(9, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 1}', '::1', '2026-08-15 16:01:39'),
	(10, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 1}', '192.168.100.195', '2026-08-15 16:01:48'),
	(11, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '192.168.100.195', '2026-08-15 16:03:31'),
	(12, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '192.168.100.195', '2026-08-15 16:04:48'),
	(13, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 16:15:09'),
	(14, 1, 'CREAR', 'personas', 9, NULL, '{"correo": "roman@empresa.com", "nombres": "Roman", "perfiles": ["ALUMNO"], "telefono": "951241733", "apellidos": "Oliva", "fotografia": "5e2264b32d158a9c74dfe54da41d74ff.png", "tipo_documento": "DNI", "numero_documento": "71062875"}', '::1', '2026-08-15 16:15:56'),
	(15, 1, 'CREAR', 'matriculas', 4, NULL, '{"id_alumno": "9", "csrf_token": "7900fa74b74a96403ed4d450218c84cbebc0b69d2793a4831be92aa8fcfd6720", "id_periodo": "1", "id_seccion": "1", "parentesco": "Apoderado", "id_apoderado": "", "codigo_alumno": "", "fecha_matricula": "2026-08-15"}', '::1', '2026-08-15 16:16:35'),
	(16, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 9}', '::1', '2026-08-15 16:16:48'),
	(17, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 21:00:14'),
	(18, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_persona", "fecha_fin": "2026-08-31", "csrf_token": "23e3cea4d8b6134576d8c99532fc8168de83e43a4c7049fcd1fb47d2bbc435c8", "id_jornada": "1", "id_persona": "9", "fecha_inicio": "2026-08-15"}', '::1', '2026-08-15 21:00:54'),
	(19, 1, 'CREAR', 'horarios', NULL, NULL, '{"nombre": "Matemática", "entidad": "asignatura", "csrf_token": "23e3cea4d8b6134576d8c99532fc8168de83e43a4c7049fcd1fb47d2bbc435c8"}', '::1', '2026-08-15 21:01:10'),
	(20, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "sesion", "hora_fin": "09:00", "csrf_token": "23e3cea4d8b6134576d8c99532fc8168de83e43a4c7049fcd1fb47d2bbc435c8", "dia_semana": "1", "id_seccion": "1", "id_vinculo": "1", "hora_inicio": "08:00", "id_asignatura": "1", "tolerancia_minutos": "10", "id_periodo_academico": "1"}', '::1', '2026-08-15 21:01:45'),
	(21, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "sesion", "hora_fin": "09:00", "csrf_token": "23e3cea4d8b6134576d8c99532fc8168de83e43a4c7049fcd1fb47d2bbc435c8", "dia_semana": "2", "id_seccion": "2", "id_vinculo": "1", "hora_inicio": "08:00", "id_asignatura": "1", "tolerancia_minutos": "10", "id_periodo_academico": "1"}', '::1', '2026-08-15 21:02:34'),
	(22, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 21:22:38'),
	(23, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 21:56:10'),
	(24, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 21:57:05'),
	(25, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 21:59:47'),
	(26, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 22:05:08'),
	(27, 1, 'CREAR', 'usuarios', 2, NULL, NULL, '::1', '2026-08-15 22:07:42'),
	(28, 2, 'INICIO_SESION', 'usuarios', 2, NULL, NULL, '::1', '2026-08-15 22:07:58'),
	(29, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 22:15:32'),
	(30, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 22:26:40'),
	(31, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 22:29:04'),
	(32, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 22:29:45'),
	(33, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 22:29:59'),
	(34, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 22:38:08'),
	(35, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '192.168.100.195', '2026-08-15 22:45:50'),
	(36, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 9}', '192.168.100.195', '2026-08-15 22:46:02'),
	(37, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 1}', '192.168.100.195', '2026-08-15 22:46:23'),
	(38, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 3}', '::1', '2026-08-15 22:47:33'),
	(39, 2, 'INICIO_SESION', 'usuarios', 2, NULL, NULL, '::1', '2026-08-15 22:48:41'),
	(40, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-15 23:49:40'),
	(41, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 00:15:58'),
	(42, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 00:21:34'),
	(43, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 00:49:29'),
	(44, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 01:01:43'),
	(45, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 01:11:23'),
	(46, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 01:27:23'),
	(47, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 01:27:46'),
	(48, 1, 'CREAR', 'personas', 10, NULL, '{"correo": "", "nombres": "Prueba", "perfiles": ["ALUMNO"], "telefono": "999", "apellidos": "01", "fotografia": null, "tipo_documento": "DNI", "numero_documento": "777"}', '::1', '2026-08-16 01:30:53'),
	(49, 1, 'CREAR', 'personas', 11, NULL, '{"correo": "", "nombres": "Prueba", "perfiles": ["ALUMNO"], "telefono": "999", "apellidos": "02", "fotografia": null, "tipo_documento": "DNI", "numero_documento": "888"}', '::1', '2026-08-16 01:31:26'),
	(50, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 01:32:17'),
	(51, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 9}', '::1', '2026-08-16 02:01:31'),
	(52, 1, 'EDITAR', 'personas', 10, NULL, NULL, '::1', '2026-08-16 02:02:14'),
	(53, 1, 'DESACTIVAR', 'personas', 10, NULL, NULL, '::1', '2026-08-16 02:16:31'),
	(54, 1, 'ACTIVAR', 'personas', 10, NULL, NULL, '::1', '2026-08-16 02:16:34'),
	(55, 1, 'EDITAR', 'personas', 10, NULL, NULL, '::1', '2026-08-16 02:21:43'),
	(56, 1, 'CREAR', 'personas', 12, NULL, '{"correo": "", "nombres": "Prueba", "perfiles": ["ALUMNO"], "telefono": "999", "apellidos": "03", "fotografia": "774749574339d10bbc69f1f0f0d52b2b.jpg", "tipo_documento": "DNI", "numero_documento": "999"}', '::1', '2026-08-16 02:22:07'),
	(57, 1, 'EDITAR', 'personas', 12, NULL, NULL, '::1', '2026-08-16 02:22:43'),
	(58, 1, 'DESACTIVAR', 'personas', 11, NULL, NULL, '::1', '2026-08-16 02:22:51'),
	(59, 1, 'ACTIVAR', 'personas', 11, NULL, NULL, '::1', '2026-08-16 02:22:57'),
	(60, 1, 'EDITAR', 'personas', 2, NULL, NULL, '::1', '2026-08-16 02:23:44'),
	(61, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 11:34:18'),
	(62, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 11:39:41'),
	(63, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 12:06:42'),
	(64, 1, 'EDITAR', 'personas', 2, NULL, NULL, '::1', '2026-08-16 12:17:32'),
	(65, 1, 'CREAR', 'academico_periodo', NULL, NULL, '{"estado": "ACTIVO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 12:35:44'),
	(66, 1, 'CREAR', 'academico_grado', NULL, NULL, '{"orden": "4", "nombre": "3.° grado", "entidad": "grado", "id_nivel": "2", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:38:17'),
	(67, 1, 'CREAR', 'academico_grado', NULL, NULL, '{"orden": "2", "nombre": "4 años", "entidad": "grado", "id_nivel": "1", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:38:32'),
	(68, 1, 'CREAR', 'academico_grado', NULL, NULL, '{"orden": "4", "nombre": "4.° grado", "entidad": "grado", "id_nivel": "2", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:39:35'),
	(69, 1, 'CREAR', 'academico_grado', NULL, NULL, '{"orden": "5", "nombre": "5.° grado", "entidad": "grado", "id_nivel": "2", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:39:55'),
	(70, 1, 'CREAR', 'academico_grado', NULL, NULL, '{"orden": "6", "nombre": "6.° grado", "entidad": "grado", "id_nivel": "2", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:40:16'),
	(71, 1, 'CREAR', 'academico_seccion', NULL, NULL, '{"nombre": "A", "entidad": "seccion", "id_grado": "3", "capacidad": "20", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:40:51'),
	(72, 1, 'CREAR', 'academico_seccion', NULL, NULL, '{"nombre": "A", "entidad": "seccion", "id_grado": "5", "capacidad": "20", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:41:06'),
	(73, 1, 'CREAR', 'academico_seccion', NULL, NULL, '{"nombre": "A", "entidad": "seccion", "id_grado": "7", "capacidad": "20", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:41:13'),
	(74, 1, 'CREAR', 'academico_seccion', NULL, NULL, '{"nombre": "A", "entidad": "seccion", "id_grado": "8", "capacidad": "20", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:41:52'),
	(75, 1, 'CREAR', 'academico_seccion', NULL, NULL, '{"nombre": "A", "entidad": "seccion", "id_grado": "9", "capacidad": "20", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 12:42:13'),
	(76, 1, 'DESACTIVAR', 'academico_nivel', 3, NULL, NULL, '::1', '2026-08-16 13:02:37'),
	(77, 1, 'ACTIVAR', 'academico_nivel', 3, NULL, NULL, '::1', '2026-08-16 13:02:39'),
	(78, 1, 'EDITAR', 'academico_periodo', 1, NULL, '{"id": "1", "estado": "ACTIVO", "nombre": "Año escolar 2026", "entidad": "periodo", "fecha_fin": "2026-12-18", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2026-03-02"}', '::1', '2026-08-16 13:02:45'),
	(79, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "PLANIFICADO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 13:03:54'),
	(80, 1, 'DESACTIVAR', 'academico_nivel', 1, NULL, NULL, '::1', '2026-08-16 13:19:24'),
	(81, 1, 'ACTIVAR', 'academico_nivel', 1, NULL, NULL, '::1', '2026-08-16 13:19:30'),
	(82, 1, 'EDITAR', 'academico_nivel', 1, NULL, '{"id": "1", "orden": "1", "nombre": "Inicial", "entidad": "nivel", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 13:19:35'),
	(83, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "PLANIFICADO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 13:19:43'),
	(84, 1, 'EDITAR', 'academico_periodo', 1, NULL, '{"id": "1", "estado": "CERRADO", "nombre": "Año escolar 2026", "entidad": "periodo", "fecha_fin": "2026-12-18", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2026-03-02"}', '::1', '2026-08-16 13:22:37'),
	(85, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "ACTIVO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 13:22:47'),
	(86, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "PLANIFICADO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 13:22:54'),
	(87, 1, 'EDITAR', 'academico_periodo', 1, NULL, '{"id": "1", "estado": "ACTIVO", "nombre": "Año escolar 2026", "entidad": "periodo", "fecha_fin": "2026-12-18", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2026-03-02"}', '::1', '2026-08-16 13:23:00'),
	(88, 1, 'EDITAR', 'academico_periodo', 1, NULL, '{"id": "1", "estado": "CERRADO", "nombre": "Año escolar 2026", "entidad": "periodo", "fecha_fin": "2026-12-18", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2026-03-02"}', '::1', '2026-08-16 13:25:25'),
	(89, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "ACTIVO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 13:25:30'),
	(90, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "PLANIFICADO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 13:25:45'),
	(91, 1, 'EDITAR', 'academico_periodo', 1, NULL, '{"id": "1", "estado": "ACTIVO", "nombre": "Año escolar 2026", "entidad": "periodo", "fecha_fin": "2026-12-18", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef", "fecha_inicio": "2026-03-02"}', '::1', '2026-08-16 13:25:50'),
	(92, 1, 'DESACTIVAR', 'academico_nivel', 3, NULL, NULL, '::1', '2026-08-16 13:45:40'),
	(93, 1, 'ACTIVAR', 'academico_nivel', 3, NULL, NULL, '::1', '2026-08-16 13:45:58'),
	(94, 1, 'DESACTIVAR', 'academico_grado', 4, NULL, NULL, '::1', '2026-08-16 13:46:07'),
	(95, 1, 'CREAR', 'academico_seccion', NULL, NULL, '{"nombre": "2.° año A", "entidad": "seccion", "id_grado": "4", "capacidad": "20", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 13:46:26'),
	(96, 1, 'CREAR', 'academico_grado', NULL, NULL, '{"orden": "2", "nombre": "2.° año", "entidad": "grado", "id_nivel": "3", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 13:46:55'),
	(97, 1, 'EDITAR', 'academico_seccion', 9, NULL, '{"id": "9", "nombre": "A", "entidad": "seccion", "id_grado": "10", "capacidad": "20", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 13:47:24'),
	(98, 1, 'ACTIVAR', 'academico_grado', 4, NULL, NULL, '::1', '2026-08-16 13:47:41'),
	(99, 1, 'DESACTIVAR', 'academico_grado', 2, NULL, NULL, '::1', '2026-08-16 13:47:50'),
	(100, 1, 'ACTIVAR', 'academico_grado', 2, NULL, NULL, '::1', '2026-08-16 13:48:10'),
	(101, 1, 'EDITAR', 'academico_grado', 4, NULL, '{"id": "4", "orden": "1", "nombre": "1.° grado", "entidad": "grado", "id_nivel": "3", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 14:24:21'),
	(102, 1, 'EDITAR', 'academico_grado', 10, NULL, '{"id": "10", "orden": "2", "nombre": "2.° grado", "entidad": "grado", "id_nivel": "3", "csrf_token": "5f5d16c7bc572d9dfc73ffc2cfab12f9df25f46c20be3f9674f37bc041d909ef"}', '::1', '2026-08-16 14:24:35'),
	(103, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 14:48:20'),
	(104, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '192.168.100.195', '2026-08-16 15:45:40'),
	(105, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 9}', '192.168.100.195', '2026-08-16 15:53:38'),
	(106, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 15:54:52'),
	(107, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 15:56:33'),
	(108, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 15:57:53'),
	(109, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 9}', '192.168.100.195', '2026-08-16 16:00:21'),
	(110, 1, 'EDITAR', 'academico_periodo', 1, NULL, '{"id": "1", "estado": "CERRADO", "nombre": "Año escolar 2026", "entidad": "periodo", "fecha_fin": "2026-12-18", "csrf_token": "4d045e0167d70cf3b338ea2732bb038a5dcdab7a5540f6a288d23d52befeeb30", "fecha_inicio": "2026-03-02"}', '::1', '2026-08-16 16:06:18'),
	(111, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "ACTIVO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "4d045e0167d70cf3b338ea2732bb038a5dcdab7a5540f6a288d23d52befeeb30", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 16:06:25'),
	(112, 1, 'EDITAR', 'academico_periodo', 1, NULL, '{"id": "1", "estado": "ACTIVO", "nombre": "Año escolar 2026", "entidad": "periodo", "fecha_fin": "2026-12-18", "csrf_token": "4d045e0167d70cf3b338ea2732bb038a5dcdab7a5540f6a288d23d52befeeb30", "fecha_inicio": "2026-03-02"}', '::1', '2026-08-16 16:06:49'),
	(113, 1, 'EDITAR', 'academico_periodo', 2, NULL, '{"id": "2", "estado": "PLANIFICADO", "nombre": "Año escolar 2027", "entidad": "periodo", "fecha_fin": "2027-12-31", "csrf_token": "4d045e0167d70cf3b338ea2732bb038a5dcdab7a5540f6a288d23d52befeeb30", "fecha_inicio": "2027-01-01"}', '::1', '2026-08-16 16:06:53'),
	(114, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 16:38:22'),
	(115, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 2}', '192.168.100.195', '2026-08-16 16:55:55'),
	(116, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 2}', '192.168.100.195', '2026-08-16 16:56:02'),
	(117, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 2}', '192.168.100.195', '2026-08-16 16:56:08'),
	(118, 1, 'CREAR', 'personas', 13, NULL, '{"correo": "blademir@ejemplo.com", "nombres": "Blademir", "perfiles": ["ADMINISTRATIVO"], "telefono": "999", "apellidos": "Acero Oliva", "fotografia": "d8b68f96ac92de8288095b8927213555.png", "tipo_documento": "DNI", "numero_documento": "74211088"}', '::1', '2026-08-16 17:05:54'),
	(119, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_persona", "fecha_fin": "", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_jornada": "3", "id_persona": "13", "fecha_inicio": "2026-08-10"}', '::1', '2026-08-16 17:08:37'),
	(120, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 13}', '::1', '2026-08-16 17:26:08'),
	(121, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 8}', '::1', '2026-08-16 17:27:43'),
	(122, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 8}', '192.168.100.195', '2026-08-16 17:27:58'),
	(123, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 13}', '192.168.100.195', '2026-08-16 17:29:58'),
	(124, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_desactivar", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_asignacion": "2"}', '::1', '2026-08-16 17:51:43'),
	(125, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_actualizar", "fecha_fin": "2026-08-31", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_jornada": "2", "fecha_inicio": "2026-08-15", "id_asignacion": "1"}', '::1', '2026-08-16 17:52:29'),
	(126, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_actualizar", "fecha_fin": "2026-08-31", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_jornada": "3", "fecha_inicio": "2026-08-15", "id_asignacion": "1"}', '::1', '2026-08-16 17:53:11'),
	(127, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_desactivar", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_asignacion": "1"}', '::1', '2026-08-16 17:53:29'),
	(128, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 7}', '::1', '2026-08-16 17:58:24'),
	(129, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_grupo", "personas": ["13", "8"], "fecha_fin": "", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_jornada": "3", "fecha_inicio": "2026-08-16"}', '::1', '2026-08-16 17:59:25'),
	(130, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 13}', '192.168.100.195', '2026-08-16 17:59:38'),
	(131, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_actualizar", "fecha_fin": "2026-12-31", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_jornada": "3", "fecha_inicio": "2026-08-16", "id_asignacion": "3"}', '::1', '2026-08-16 18:14:55'),
	(132, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_actualizar", "fecha_fin": "2026-12-31", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_jornada": "3", "fecha_inicio": "2026-08-16", "id_asignacion": "3"}', '::1', '2026-08-16 18:34:13'),
	(133, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "sesion_desactivar", "id_carga": "1", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54"}', '::1', '2026-08-16 18:40:03'),
	(134, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "sesion", "hora_fin": "09:00", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "dia_semana": "1", "id_seccion": "2", "id_vinculo": "1", "hora_inicio": "08:00", "id_asignatura": "1", "tolerancia_minutos": "10", "id_periodo_academico": "1"}', '::1', '2026-08-16 18:40:40'),
	(135, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 13}', '192.168.100.195', '2026-08-16 18:51:06'),
	(136, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 9}', '192.168.100.195', '2026-08-16 18:51:23'),
	(137, 1, 'CREAR', 'matriculas', 5, NULL, '{"id_alumno": "10", "csrf_token": "31e0966e4c4c89063154b118f909d04bb3b6fd83219a25d5d5211d9aa240ab54", "id_periodo": "1", "id_seccion": "1", "parentesco": "Apoderado", "id_apoderado": "", "codigo_alumno": "", "fecha_matricula": "2026-08-16"}', '::1', '2026-08-16 18:54:01'),
	(138, 1, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 10}', '::1', '2026-08-16 18:54:10'),
	(139, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 10}', '192.168.100.195', '2026-08-16 18:54:15'),
	(140, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 18:56:32'),
	(141, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 10}', '192.168.100.195', '2026-08-16 19:02:41'),
	(142, 1, 'DESACTIVAR', 'horarios', NULL, NULL, '{"entidad": "sesion_desactivar", "id_carga": "1", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 19:16:12'),
	(143, 1, 'DESACTIVAR', 'horarios', NULL, NULL, '{"entidad": "jornada_desactivar", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "id_asignacion": "2"}', '::1', '2026-08-16 19:16:19'),
	(144, 1, 'ACTIVAR', 'horarios', NULL, NULL, '{"entidad": "sesion_activar", "id_carga": "1", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 19:28:28'),
	(145, 1, 'DESACTIVAR', 'horarios', NULL, NULL, '{"entidad": "sesion_desactivar", "id_carga": "1", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 19:28:56'),
	(146, 1, 'CREAR', 'horarios', NULL, NULL, '{"entidad": "jornada_persona", "fecha_fin": "", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "id_jornada": "2", "id_persona": "7", "fecha_inicio": "2026-08-16"}', '::1', '2026-08-16 19:29:09'),
	(147, 1, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 7}', '192.168.100.195', '2026-08-16 19:29:15'),
	(148, 1, 'CREAR', 'incidencias_asistencia', NULL, NULL, '{"tipo": "JUSTIFICACION_TARDANZA", "motivo": "Justificación de tardanza valida xd.", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "id_asistencia": "7"}', '::1', '2026-08-16 19:55:57'),
	(149, 1, 'EDITAR', 'personas', 9, NULL, NULL, '::1', '2026-08-16 20:28:47'),
	(150, 1, 'EDITAR', 'personas', 9, NULL, NULL, '::1', '2026-08-16 20:29:36'),
	(151, 1, 'EDITAR', 'personas', 9, NULL, NULL, '::1', '2026-08-16 20:29:42'),
	(152, 1, 'TRANSICION_LABORAL', 'vinculos_laborales', NULL, NULL, '{"tipo": "ADMINISTRATIVO", "cargo": "Gerente General", "fecha_fin": "2026-12-31", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "id_persona": "13", "monto_base": "2500", "fecha_inicio": "2026-08-16", "modalidad_pago": "MENSUAL"}', '::1', '2026-08-16 21:09:54'),
	(153, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"1": "100", "2": ""}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "YAPE", "observacion": "Se pago al número de bladi"}', '::1', '2026-08-16 21:45:33'),
	(154, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"1": "75", "2": ""}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": "Recibe en fisico."}', '::1', '2026-08-16 21:47:16'),
	(155, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"2": "80"}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 21:48:29'),
	(156, 1, 'GENERAR', 'cuentas_cobrar', NULL, NULL, '{"mes": "2026-08", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "fecha_vencimiento": "2026-08-30", "id_concepto_cobro": "6", "id_periodo_academico": "1"}', '::1', '2026-08-16 22:17:19'),
	(157, 1, 'CAMBIAR_ESTADO', 'tarifas_cobro', 5, NULL, '{"estado": "INACTIVO", "id_tarifa": "5", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 22:29:48'),
	(158, 1, 'CAMBIAR_ESTADO', 'tarifas_cobro', 3, NULL, '{"estado": "INACTIVO", "id_tarifa": "3", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 22:29:52'),
	(159, 1, 'CAMBIAR_ESTADO', 'tarifas_cobro', 1, NULL, '{"estado": "INACTIVO", "id_tarifa": "1", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 22:29:55'),
	(160, 1, 'CAMBIAR_ESTADO', 'tarifas_cobro', 4, NULL, '{"estado": "INACTIVO", "id_tarifa": "4", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 22:29:56'),
	(161, 1, 'CAMBIAR_ESTADO', 'tarifas_cobro', 2, NULL, '{"estado": "INACTIVO", "id_tarifa": "2", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae"}', '::1', '2026-08-16 22:29:58'),
	(162, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-09", "detalles": {"8": "", "13": "120"}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 22:31:48'),
	(163, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"5": "300", "10": "120"}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 22:32:49'),
	(164, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"7": "300", "12": "120"}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 22:32:55'),
	(165, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"6": "300", "11": "120"}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 22:33:01'),
	(166, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"9": "300", "14": "120"}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 22:33:07'),
	(167, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-09", "detalles": {"8": "300"}, "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 22:34:09'),
	(168, 1, 'GENERAR', 'cuentas_cobrar', NULL, NULL, '{"mes": "2026-08", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "fecha_vencimiento": "2026-08-30", "id_concepto_cobro": "2", "id_periodo_academico": "1"}', '::1', '2026-08-16 22:36:19'),
	(169, 1, 'CAMBIAR_ESTADO', 'conceptos_cobro', 4, NULL, '{"estado": "INACTIVO", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "id_concepto_cobro": "4"}', '::1', '2026-08-16 22:48:37'),
	(170, 1, 'CAMBIAR_ESTADO', 'conceptos_cobro', 3, NULL, '{"estado": "INACTIVO", "csrf_token": "2ed9d2edb76d1265a1d2c1f6e787023f95c8c23bd86d21d904f9c7f0fb97f2ae", "id_concepto_cobro": "3"}', '::1', '2026-08-16 22:48:44'),
	(171, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-16 22:59:52'),
	(172, 1, 'ANULAR', 'pagos_alumno', 8, NULL, '{"motivo": "Veo dos deudas.", "id_pago": "8", "csrf_token": "c6ce806245a792a6d1a1104078b85c5f450bfc133392c261b55d4bdd9b9f24c1"}', '::1', '2026-08-16 23:08:02'),
	(173, 1, 'AJUSTAR', 'cuentas_cobrar', 18, NULL, '{"mes": "2026-08", "tipo": "DESCUENTO", "motivo": "Es un familiar.", "importe": "50", "id_cuenta": "18", "csrf_token": "c6ce806245a792a6d1a1104078b85c5f450bfc133392c261b55d4bdd9b9f24c1"}', '::1', '2026-08-16 23:15:45'),
	(174, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"19": "300"}, "csrf_token": "c6ce806245a792a6d1a1104078b85c5f450bfc133392c261b55d4bdd9b9f24c1", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-16 23:42:25'),
	(175, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"9": "", "14": "100", "20": ""}, "csrf_token": "c6ce806245a792a6d1a1104078b85c5f450bfc133392c261b55d4bdd9b9f24c1", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-17 00:04:22'),
	(176, 1, 'CAMBIAR_ESTADO', 'conceptos_cobro', 6, NULL, '{"estado": "INACTIVO", "csrf_token": "c6ce806245a792a6d1a1104078b85c5f450bfc133392c261b55d4bdd9b9f24c1", "id_concepto_cobro": "6"}', '::1', '2026-08-17 00:04:57'),
	(177, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"9": "", "14": "20", "20": ""}, "csrf_token": "c6ce806245a792a6d1a1104078b85c5f450bfc133392c261b55d4bdd9b9f24c1", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-17 00:05:37'),
	(178, 1, 'CERRAR_CAJA', 'cierres_caja', NULL, NULL, '{"fecha": "2026-08-16", "csrf_token": "c6ce806245a792a6d1a1104078b85c5f450bfc133392c261b55d4bdd9b9f24c1", "observacion": "", "otros_declarado": "100.00", "efectivo_declarado": "2135.00"}', '::1', '2026-08-17 00:06:12'),
	(179, 1, 'INICIO_SESION', 'usuarios', 1, NULL, NULL, '::1', '2026-08-18 22:28:14'),
	(180, 1, 'ABRIR_CAJA', 'aperturas_caja', NULL, NULL, '{"fecha": "2026-08-18", "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "observacion": "", "monto_apertura": "100"}', '::1', '2026-08-18 22:37:25'),
	(181, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"9": "300", "20": ""}, "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-18 22:38:07'),
	(182, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"20": "100"}, "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "medio_pago": "YAPE", "observacion": ""}', '::1', '2026-08-18 22:38:42'),
	(183, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"20": "100"}, "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "medio_pago": "PLIN", "observacion": ""}', '::1', '2026-08-18 22:39:21'),
	(184, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"20": "100"}, "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "medio_pago": "TRANSFERENCIA", "observacion": ""}', '::1', '2026-08-18 22:39:30'),
	(185, 1, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"16": "100"}, "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-18 22:39:45'),
	(186, 1, 'CERRAR_CAJA', 'cierres_caja', NULL, NULL, '{"fecha": "2026-08-18", "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "observacion": "", "otros_declarado": "300.00", "efectivo_declarado": "400.00"}', '::1', '2026-08-18 22:40:40'),
	(187, 1, 'CREAR', 'personas', 19, NULL, '{"correo": "", "nombres": "Soporte", "perfiles": ["ADMINISTRATIVO"], "telefono": "999", "apellidos": "Tecnico", "fotografia": null, "tipo_documento": "DNI", "numero_documento": "123"}', '::1', '2026-08-18 23:10:11'),
	(188, 1, 'TRANSICION_LABORAL', 'vinculos_laborales', NULL, NULL, '{"tipo": "ADMINISTRATIVO", "cargo": "Analista TIC", "fecha_fin": "2026-12-31", "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "id_persona": "19", "monto_base": "1135", "fecha_inicio": "2026-08-18", "modalidad_pago": "MENSUAL"}', '::1', '2026-08-18 23:11:20'),
	(189, 1, 'CREAR', 'usuarios', 3, NULL, NULL, '::1', '2026-08-18 23:12:33'),
	(190, 3, 'INICIO_SESION', 'usuarios', 3, NULL, NULL, '::1', '2026-08-18 23:12:54'),
	(191, 3, 'INICIO_SESION', 'usuarios', 3, NULL, NULL, '::1', '2026-08-18 23:13:58'),
	(192, 3, 'ABRIR_CAJA', 'aperturas_caja', NULL, NULL, '{"fecha": "2026-08-18", "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "observacion": "", "monto_apertura": "200"}', '::1', '2026-08-18 23:14:15'),
	(193, 3, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"16": "200"}, "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-18 23:14:29'),
	(194, 3, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"18": "250"}, "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-18 23:14:38'),
	(195, 3, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"15": "150"}, "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "medio_pago": "YAPE", "observacion": ""}', '::1', '2026-08-18 23:15:01'),
	(196, 3, 'CERRAR_CAJA', 'cierres_caja', NULL, NULL, '{"fecha": "2026-08-18", "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "id_apertura": "2", "observacion": "", "otros_declarado": "150.00", "efectivo_declarado": "450.00"}', '::1', '2026-08-18 23:18:06'),
	(197, 3, 'ABRIR_CAJA', 'aperturas_caja', NULL, NULL, '{"fecha": "2026-08-18", "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "observacion": "", "monto_apertura": "100"}', '::1', '2026-08-18 23:18:19'),
	(198, 3, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"15": "100"}, "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-18 23:19:58'),
	(199, 3, 'COBRAR', 'pagos_alumno', NULL, NULL, '{"mes": "2026-08", "detalles": {"15": "50"}, "csrf_token": "c76eedbb0fccf1afcb29a0f655b524d18351d3759a8ba6a08ab008e95d77802e", "medio_pago": "EFECTIVO", "observacion": ""}', '::1', '2026-08-18 23:20:38'),
	(200, 1, 'CERRAR_CAJA', 'cierres_caja', NULL, NULL, '{"fecha": "2026-08-18", "csrf_token": "73bd7f93a43f630507c3fb6e1b94db1ab3b5a0db3d21352f052271b2ddc67066", "id_apertura": "3", "observacion": "", "otros_declarado": "0.00", "efectivo_declarado": "150.00"}', '::1', '2026-08-18 23:21:00'),
	(201, 1, 'CREAR', 'calendario_institucional', 1, NULL, '{"tipo": "FERIADO", "fecha": "2026-08-30", "estado": "ACTIVO", "creado_en": "2026-08-19 20:09:34", "creado_por": 1, "descripcion": "Día de Santa Rosa de Lima", "id_calendario": 1, "actualizado_en": null, "actualizado_por": null}', '::1', '2026-08-19 20:09:34');

-- Volcando estructura para tabla colegio.calendario_institucional
CREATE TABLE IF NOT EXISTS `calendario_institucional` (
  `id_calendario` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `tipo` enum('FERIADO','NO_LABORABLE','SUSPENSION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `creado_por` bigint unsigned DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_por` bigint unsigned DEFAULT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_calendario`),
  UNIQUE KEY `uq_calendario_fecha_tipo` (`fecha`,`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.calendario_institucional: ~0 rows (aproximadamente)
INSERT INTO `calendario_institucional` (`id_calendario`, `fecha`, `tipo`, `descripcion`, `estado`, `creado_por`, `creado_en`, `actualizado_por`, `actualizado_en`) VALUES
	(1, '2026-08-30', 'FERIADO', 'Día de Santa Rosa de Lima', 'ACTIVO', 1, '2026-08-19 20:09:34', NULL, NULL);

-- Volcando estructura para tabla colegio.carga_academica
CREATE TABLE IF NOT EXISTS `carga_academica` (
  `id_carga` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_vinculo` bigint unsigned NOT NULL,
  `id_periodo_academico` int unsigned NOT NULL,
  `id_seccion` int unsigned NOT NULL,
  `id_asignatura` int unsigned NOT NULL,
  `dia_semana` tinyint unsigned NOT NULL COMMENT '1=Lunes, 7=Domingo',
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `tolerancia_minutos` smallint unsigned NOT NULL DEFAULT '0',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_carga`),
  KEY `fk_carga_vinculo` (`id_vinculo`),
  KEY `fk_carga_periodo` (`id_periodo_academico`),
  KEY `fk_carga_seccion` (`id_seccion`),
  KEY `fk_carga_asignatura` (`id_asignatura`),
  CONSTRAINT `fk_carga_asignatura` FOREIGN KEY (`id_asignatura`) REFERENCES `asignaturas` (`id_asignatura`),
  CONSTRAINT `fk_carga_periodo` FOREIGN KEY (`id_periodo_academico`) REFERENCES `periodos_academicos` (`id_periodo_academico`),
  CONSTRAINT `fk_carga_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`),
  CONSTRAINT `fk_carga_vinculo` FOREIGN KEY (`id_vinculo`) REFERENCES `vinculos_laborales` (`id_vinculo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.carga_academica: ~2 rows (aproximadamente)
INSERT INTO `carga_academica` (`id_carga`, `id_vinculo`, `id_periodo_academico`, `id_seccion`, `id_asignatura`, `dia_semana`, `hora_inicio`, `hora_fin`, `tolerancia_minutos`, `estado`) VALUES
	(1, 1, 1, 1, 1, 1, '08:00:00', '09:00:00', 10, 'INACTIVO'),
	(4, 4, 1, 1, 2, 1, '08:00:00', '09:00:00', 0, 'ACTIVO');

-- Volcando estructura para tabla colegio.cargos
CREATE TABLE IF NOT EXISTS `cargos` (
  `id_cargo` int unsigned NOT NULL AUTO_INCREMENT,
  `id_area` int unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_cargo`),
  KEY `fk_cargo_area` (`id_area`),
  CONSTRAINT `fk_cargo_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cargos: ~4 rows (aproximadamente)
INSERT INTO `cargos` (`id_cargo`, `id_area`, `nombre`, `estado`) VALUES
	(1, 1, 'Docente de primaria', 'ACTIVO'),
	(2, 2, 'Asistente administrativo', 'ACTIVO'),
	(3, NULL, 'Gerente General', 'ACTIVO'),
	(4, 3, 'Docente demo', 'ACTIVO'),
	(5, NULL, 'Analista TIC', 'ACTIVO');

-- Volcando estructura para tabla colegio.cierres_caja
CREATE TABLE IF NOT EXISTS `cierres_caja` (
  `id_cierre` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `efectivo_sistema` decimal(12,2) NOT NULL DEFAULT '0.00',
  `efectivo_declarado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `otros_sistema` decimal(12,2) NOT NULL DEFAULT '0.00',
  `otros_declarado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cerrado_por` bigint unsigned DEFAULT NULL,
  `cerrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_apertura` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id_cierre`),
  UNIQUE KEY `uq_cierre_apertura` (`id_apertura`),
  KEY `ix_cierre_caja_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cierres_caja: ~1 rows (aproximadamente)
INSERT INTO `cierres_caja` (`id_cierre`, `fecha`, `efectivo_sistema`, `efectivo_declarado`, `otros_sistema`, `otros_declarado`, `diferencia`, `observacion`, `cerrado_por`, `cerrado_en`, `id_apertura`) VALUES
	(1, '2026-08-16', 2135.00, 2135.00, 100.00, 100.00, 0.00, NULL, 1, '2026-08-17 00:06:12', NULL),
	(2, '2026-08-18', 400.00, 400.00, 300.00, 300.00, 0.00, NULL, 1, '2026-08-18 22:40:40', 1),
	(8, '2026-08-18', 450.00, 450.00, 150.00, 150.00, 0.00, NULL, 3, '2026-08-18 23:18:06', 2),
	(9, '2026-08-18', 150.00, 150.00, 0.00, 0.00, 0.00, NULL, 1, '2026-08-18 23:21:00', 3);

-- Volcando estructura para tabla colegio.codigos_qr
CREATE TABLE IF NOT EXISTS `codigos_qr` (
  `id_qr` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archivo_qr` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `estado` enum('ACTIVO','REVOCADO','VENCIDO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `emitido_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vence_en` datetime DEFAULT NULL,
  `revocado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_qr`),
  UNIQUE KEY `uq_qr_token` (`token_hash`),
  KEY `ix_qr_persona` (`id_persona`),
  CONSTRAINT `fk_qr_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.codigos_qr: ~8 rows (aproximadamente)
INSERT INTO `codigos_qr` (`id_qr`, `id_persona`, `token_hash`, `archivo_qr`, `version`, `estado`, `emitido_en`, `vence_en`, `revocado_en`) VALUES
	(1, 2, '7f78e04ba0f4fce333316a63bb0543848c69d464bc54425913828b2d1de5e304', 'qr_2_c68c4a366fa35298.png', 1, 'ACTIVO', '2026-08-15 15:40:39', NULL, NULL),
	(2, 1, 'dfdb431bb3b780714063b4757dc9bbe058123105303d68cfea875bd880e506b3', 'qr_1_236f7dc9a9b68dca.png', 1, 'ACTIVO', '2026-08-15 16:01:39', NULL, NULL),
	(3, 9, '14280994d82356003f9b7e8598a0eae5687cc204e9f7ff518c974de1922e0ce4', 'qr_9_bd4449ca63db4b22.png', 1, 'REVOCADO', '2026-08-15 16:16:48', NULL, '2026-08-16 02:01:30'),
	(4, 3, '1ca76d13bfe7e3d0e2dcef460e1c325dee484e754416f1a240eba80c2f6fe2eb', 'qr_3_6e9fa1b1bb2ad917.png', 1, 'ACTIVO', '2026-08-15 22:47:33', NULL, NULL),
	(5, 9, '0a913a12eceefbd7207efcb3b25cca04d6223f6bae825ad1ab03e8b3224dfa59', 'qr_9_9935ce06f6e5b899.png', 1, 'ACTIVO', '2026-08-16 02:01:31', NULL, NULL),
	(6, 13, '04081ddc40c775f7d1e51f5bae4f9898747a31862e08dc9a68a72ac9fb0c5159', 'qr_13_80de9a814afece4b.png', 1, 'ACTIVO', '2026-08-16 17:26:08', NULL, NULL),
	(7, 8, 'b1c1ceb987c01e7b259d5b7fa4751c789f4cd0c086f071ea4d1dadbe52899e8b', 'qr_8_13d5206fc9ccdde3.png', 1, 'ACTIVO', '2026-08-16 17:27:43', NULL, NULL),
	(8, 7, 'dc6b3234c45a30caa97a414ded78a038bf4251a7e104b5d5d5af1637a1d104e4', 'qr_7_d5e697ebee3d35b4.png', 1, 'ACTIVO', '2026-08-16 17:58:24', NULL, NULL),
	(9, 10, '3602d63246be983f707e77455ea4eb4710ee2f4e05043c2162c3af2a7172ce3f', 'qr_10_ee353b1ce43a6ecf.png', 1, 'ACTIVO', '2026-08-16 18:54:10', NULL, NULL);

-- Volcando estructura para tabla colegio.conceptos_cobro
CREATE TABLE IF NOT EXISTS `conceptos_cobro` (
  `id_concepto_cobro` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('MATRICULA','MENSUALIDAD','TALLER','CUOTA','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `admite_parcial` tinyint(1) NOT NULL DEFAULT '1',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_concepto_cobro`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.conceptos_cobro: ~6 rows (aproximadamente)
INSERT INTO `conceptos_cobro` (`id_concepto_cobro`, `nombre`, `tipo`, `admite_parcial`, `estado`) VALUES
	(1, 'Matrícula 2026', 'MATRICULA', 0, 'ACTIVO'),
	(2, 'Pensión mensual', 'MENSUALIDAD', 1, 'ACTIVO'),
	(3, 'Taller de inglés', 'TALLER', 1, 'INACTIVO'),
	(4, 'DEMO · Mensualidad', 'MENSUALIDAD', 1, 'INACTIVO'),
	(5, 'DEMO · Taller de ciencia', 'TALLER', 0, 'ACTIVO'),
	(6, 'DEMO · Material educativo', 'CUOTA', 1, 'INACTIVO');

-- Volcando estructura para tabla colegio.conceptos_pago
CREATE TABLE IF NOT EXISTS `conceptos_pago` (
  `id_concepto_pago` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `naturaleza` enum('INGRESO','DESCUENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('BASE','BONIFICACION','TARDANZA','FALTA','HORA','MANUAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_concepto_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.conceptos_pago: ~0 rows (aproximadamente)
INSERT INTO `conceptos_pago` (`id_concepto_pago`, `nombre`, `naturaleza`, `tipo`, `estado`) VALUES
	(1, 'Remuneración mensual', 'INGRESO', 'BASE', 'ACTIVO'),
	(2, 'Ajuste manual', 'INGRESO', 'MANUAL', 'ACTIVO');

-- Volcando estructura para tabla colegio.cuentas_cobrar
CREATE TABLE IF NOT EXISTS `cuentas_cobrar` (
  `id_cuenta` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_matricula` bigint unsigned NOT NULL,
  `id_concepto_cobro` int unsigned NOT NULL,
  `periodo_cobro` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `importe_original` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `recargo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo` decimal(12,2) NOT NULL,
  `estado` enum('PENDIENTE','PARCIAL','PAGADA','ANULADA','VENCIDA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_cuenta`),
  UNIQUE KEY `uq_cuenta` (`id_matricula`,`id_concepto_cobro`,`periodo_cobro`),
  KEY `fk_cuenta_concepto` (`id_concepto_cobro`),
  CONSTRAINT `fk_cuenta_concepto` FOREIGN KEY (`id_concepto_cobro`) REFERENCES `conceptos_cobro` (`id_concepto_cobro`),
  CONSTRAINT `fk_cuenta_matricula` FOREIGN KEY (`id_matricula`) REFERENCES `matriculas` (`id_matricula`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cuentas_cobrar: ~18 rows (aproximadamente)
INSERT INTO `cuentas_cobrar` (`id_cuenta`, `id_matricula`, `id_concepto_cobro`, `periodo_cobro`, `fecha_vencimiento`, `importe_original`, `descuento`, `recargo`, `saldo`, `estado`) VALUES
	(1, 6, 4, '2026-08-01', '2026-08-10', 350.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(2, 6, 5, '2026-08-01', '2026-08-24', 80.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(5, 1, 4, '2026-09-01', '2026-09-10', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(6, 2, 4, '2026-09-01', '2026-09-10', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(7, 4, 4, '2026-09-01', '2026-09-10', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(8, 5, 4, '2026-09-01', '2026-09-10', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(9, 6, 4, '2026-09-01', '2026-09-10', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(10, 1, 6, '2026-08-01', '2026-08-30', 120.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(11, 2, 6, '2026-08-01', '2026-08-30', 120.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(12, 4, 6, '2026-08-01', '2026-08-30', 120.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(13, 5, 6, '2026-08-01', '2026-08-30', 120.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(14, 6, 6, '2026-08-01', '2026-08-30', 120.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(15, 1, 2, '2026-08-01', '2026-08-30', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(16, 2, 2, '2026-08-01', '2026-08-30', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(17, 3, 2, '2026-08-01', '2026-08-30', 300.00, 0.00, 0.00, 300.00, 'PENDIENTE'),
	(18, 4, 2, '2026-08-01', '2026-08-30', 300.00, 50.00, 0.00, 0.00, 'PAGADA'),
	(19, 5, 2, '2026-08-01', '2026-08-30', 300.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(20, 6, 2, '2026-08-01', '2026-08-30', 300.00, 0.00, 0.00, 0.00, 'PAGADA');

-- Volcando estructura para tabla colegio.dispositivos_escaneo
CREATE TABLE IF NOT EXISTS `dispositivos_escaneo` (
  `id_dispositivo` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `punto_control` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `ultimo_sync_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_dispositivo`),
  UNIQUE KEY `uq_dispositivo_codigo` (`codigo`),
  UNIQUE KEY `uq_dispositivo_token` (`token_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.dispositivos_escaneo: ~0 rows (aproximadamente)
INSERT INTO `dispositivos_escaneo` (`id_dispositivo`, `nombre`, `codigo`, `token_hash`, `punto_control`, `estado`, `ultimo_sync_en`) VALUES
	(1, 'Celular Prueba', 'PRUEBA01', '308d63a826eaa8455c203d420cc78370e458c4f149eb2f133fb7199a210d4f30', 'Ingreso Prueba', 'ACTIVO', NULL);

-- Volcando estructura para tabla colegio.grados
CREATE TABLE IF NOT EXISTS `grados` (
  `id_grado` int unsigned NOT NULL AUTO_INCREMENT,
  `id_nivel` int unsigned NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint unsigned NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_grado`),
  UNIQUE KEY `uq_grado` (`id_nivel`,`nombre`),
  CONSTRAINT `fk_grado_nivel` FOREIGN KEY (`id_nivel`) REFERENCES `niveles` (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.grados: ~10 rows (aproximadamente)
INSERT INTO `grados` (`id_grado`, `id_nivel`, `nombre`, `orden`, `estado`) VALUES
	(1, 1, '3 años', 1, 'ACTIVO'),
	(2, 2, '1.° grado', 1, 'ACTIVO'),
	(3, 2, '2.° grado', 2, 'ACTIVO'),
	(4, 3, '1.° grado', 1, 'ACTIVO'),
	(5, 2, '3.° grado', 3, 'ACTIVO'),
	(6, 1, '4 años', 2, 'ACTIVO'),
	(7, 2, '4.° grado', 4, 'ACTIVO'),
	(8, 2, '5.° grado', 5, 'ACTIVO'),
	(9, 2, '6.° grado', 6, 'ACTIVO'),
	(10, 3, '2.° grado', 2, 'ACTIVO');

-- Volcando estructura para tabla colegio.incidencias_asistencia
CREATE TABLE IF NOT EXISTS `incidencias_asistencia` (
  `id_incidencia` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_asistencia` bigint unsigned NOT NULL,
  `tipo` enum('JUSTIFICACION_FALTA','JUSTIFICACION_TARDANZA','PERMISO','LICENCIA','OBSERVACION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `registrado_por` bigint unsigned DEFAULT NULL,
  `resuelto_por` bigint unsigned DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resuelto_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_incidencia`),
  KEY `fk_incidencia_asistencia` (`id_asistencia`),
  CONSTRAINT `fk_incidencia_asistencia` FOREIGN KEY (`id_asistencia`) REFERENCES `asistencias_diarias` (`id_asistencia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.incidencias_asistencia: ~0 rows (aproximadamente)
INSERT INTO `incidencias_asistencia` (`id_incidencia`, `id_asistencia`, `tipo`, `motivo`, `estado`, `registrado_por`, `resuelto_por`, `creado_en`, `resuelto_en`) VALUES
	(1, 7, 'JUSTIFICACION_TARDANZA', 'Justificación de tardanza valida xd.', 'PENDIENTE', 1, NULL, '2026-08-16 19:55:57', NULL);

-- Volcando estructura para tabla colegio.institucion
CREATE TABLE IF NOT EXISTS `institucion` (
  `id_institucion` tinyint unsigned NOT NULL DEFAULT '1',
  `nombre` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_institucion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.institucion: ~0 rows (aproximadamente)
INSERT INTO `institucion` (`id_institucion`, `nombre`, `ruc`, `direccion`, `telefono`, `correo`, `logo`, `creado_en`) VALUES
	(1, 'I.E. Modelo San Martín', '20123456789', 'Av. Principal 123', '999 111 222', 'contacto@colegiomodelo.edu.pe', NULL, '2026-08-15 15:38:02');

-- Volcando estructura para tabla colegio.jornadas
CREATE TABLE IF NOT EXISTS `jornadas` (
  `id_jornada` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aplica_a` enum('ALUMNO','DOCENTE','ADMINISTRATIVO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `tolerancia_minutos` smallint unsigned NOT NULL DEFAULT '0',
  `requiere_salida` tinyint(1) NOT NULL DEFAULT '1',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_jornada`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.jornadas: ~3 rows (aproximadamente)
INSERT INTO `jornadas` (`id_jornada`, `nombre`, `aplica_a`, `hora_entrada`, `hora_salida`, `tolerancia_minutos`, `requiere_salida`, `estado`) VALUES
	(1, 'Jornada alumnos mañana', 'ALUMNO', '07:45:00', '13:00:00', 10, 1, 'ACTIVO'),
	(2, 'Jornada docentes mañana', 'DOCENTE', '07:30:00', '14:00:00', 10, 1, 'ACTIVO'),
	(3, 'Jornada administrativos', 'ADMINISTRATIVO', '08:00:00', '17:00:00', 10, 1, 'ACTIVO'),
	(4, 'DEMO · Jornada personal', 'DOCENTE', '08:00:00', '16:00:00', 10, 1, 'ACTIVO');

-- Volcando estructura para tabla colegio.marcaciones_asistencia
CREATE TABLE IF NOT EXISTS `marcaciones_asistencia` (
  `id_marcacion` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid_cliente` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_persona` bigint unsigned NOT NULL,
  `id_dispositivo` int unsigned DEFAULT NULL,
  `id_usuario` bigint unsigned DEFAULT NULL,
  `fecha_hora_dispositivo` datetime NOT NULL,
  `fecha_hora_servidor` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` enum('ENTRADA','SALIDA','INICIO_REFRIGERIO','FIN_REFRIGERIO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `metodo` enum('QR','MANUAL','SINCRONIZACION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_validacion` enum('VALIDA','DUPLICADA','ANULADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VALIDA',
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_marcacion`),
  UNIQUE KEY `uq_marcacion_uuid` (`uuid_cliente`),
  KEY `ix_marcacion_persona_fecha` (`id_persona`,`fecha_hora_dispositivo`),
  KEY `fk_marcacion_dispositivo` (`id_dispositivo`),
  CONSTRAINT `fk_marcacion_dispositivo` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos_escaneo` (`id_dispositivo`),
  CONSTRAINT `fk_marcacion_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.marcaciones_asistencia: ~15 rows (aproximadamente)
INSERT INTO `marcaciones_asistencia` (`id_marcacion`, `uuid_cliente`, `id_persona`, `id_dispositivo`, `id_usuario`, `fecha_hora_dispositivo`, `fecha_hora_servidor`, `tipo`, `metodo`, `estado_validacion`, `observacion`) VALUES
	(1, '370a17f1-d9de-4cfd-92e7-024b54343654', 2, 1, 1, '2026-08-15 20:58:28', '2026-08-15 15:58:28', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(2, '03f1c601-cfe5-4eca-a594-832a08efebd9', 2, 1, 1, '2026-08-15 21:00:57', '2026-08-15 16:00:57', 'SALIDA', 'QR', 'VALIDA', NULL),
	(3, '42a16585-99ca-4435-967f-3d2298902568', 1, 1, 1, '2026-08-15 21:01:48', '2026-08-15 16:01:48', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(4, '65cd1ea0-b108-4b52-a00a-ea212f262f67', 9, 1, 1, '2026-08-16 03:46:01', '2026-08-15 22:46:02', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(5, 'b7abceb5-863d-4c73-a26d-12d05be1e58c', 1, 1, 1, '2026-08-16 03:46:22', '2026-08-15 22:46:23', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(6, '4094d86a-0d38-4bbd-994a-1fa6c61a38e7', 9, 1, 1, '2026-08-16 20:53:37', '2026-08-16 15:53:38', 'SALIDA', 'QR', 'VALIDA', NULL),
	(7, 'c6d79d26-64eb-4b54-8088-f2342a3015c1', 9, 1, 1, '2026-08-16 21:00:20', '2026-08-16 16:00:21', 'SALIDA', 'QR', 'VALIDA', NULL),
	(8, '889e79ad8ab82d70df7fd1030eb26b50', 2, NULL, 1, '2026-08-16 16:41:00', '2026-08-16 16:41:44', 'ENTRADA', 'MANUAL', 'VALIDA', 'Registro manual: PUNTUAL'),
	(9, '0fcc73bf-fe11-4145-ac02-b95c1928eebf', 2, 1, 1, '2026-08-16 21:55:53', '2026-08-16 16:55:55', 'SALIDA', 'QR', 'VALIDA', NULL),
	(10, '61e5e2cc-382b-49d1-8648-167be9ca799f', 8, 1, 1, '2026-08-16 22:27:57', '2026-08-16 17:27:58', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(11, 'aad50d6d-a6c9-4f10-bbf4-ef7b266f3fa4', 13, 1, 1, '2026-08-16 22:29:57', '2026-08-16 17:29:58', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(12, 'ab50f5a5-c3aa-4ff0-b8ba-94dfaeb907db', 13, 1, 1, '2026-08-16 22:59:37', '2026-08-16 17:59:38', 'SALIDA', 'QR', 'VALIDA', NULL),
	(13, '0fcddef9-b596-476e-bec9-157a5974b58f', 10, 1, 1, '2026-08-16 18:54:14', '2026-08-16 18:54:15', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(14, 'd93a4610-3dea-4c3a-bdfa-682712fdd11b', 10, 1, 1, '2026-08-16 19:02:40', '2026-08-16 19:02:41', 'SALIDA', 'QR', 'VALIDA', NULL),
	(15, 'aaefa280-7e12-45b3-834d-505c17eaad6d', 7, 1, 1, '2026-08-16 19:29:14', '2026-08-16 19:29:15', 'ENTRADA', 'QR', 'VALIDA', NULL);

-- Volcando estructura para tabla colegio.matriculas
CREATE TABLE IF NOT EXISTS `matriculas` (
  `id_matricula` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned NOT NULL,
  `id_periodo_academico` int unsigned NOT NULL,
  `id_seccion` int unsigned NOT NULL,
  `codigo_alumno` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_matricula` date NOT NULL,
  `estado` enum('ACTIVO','RETIRADO','EGRESADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_matricula`),
  UNIQUE KEY `uq_matricula_periodo_alumno` (`id_persona`,`id_periodo_academico`),
  UNIQUE KEY `uq_matricula_codigo` (`codigo_alumno`),
  KEY `fk_matricula_periodo` (`id_periodo_academico`),
  KEY `fk_matricula_seccion` (`id_seccion`),
  CONSTRAINT `fk_matricula_alumno` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`),
  CONSTRAINT `fk_matricula_periodo` FOREIGN KEY (`id_periodo_academico`) REFERENCES `periodos_academicos` (`id_periodo_academico`),
  CONSTRAINT `fk_matricula_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `secciones` (`id_seccion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.matriculas: ~6 rows (aproximadamente)
INSERT INTO `matriculas` (`id_matricula`, `id_persona`, `id_periodo_academico`, `id_seccion`, `codigo_alumno`, `fecha_matricula`, `estado`) VALUES
	(1, 1, 1, 1, 'A-2026-0001', '2026-02-15', 'ACTIVO'),
	(2, 2, 1, 2, 'A-2026-0002', '2026-02-15', 'ACTIVO'),
	(3, 3, 1, 3, 'A-2026-0003', '2026-02-15', 'ACTIVO'),
	(4, 9, 1, 1, 'A-2026-0004', '2026-08-15', 'ACTIVO'),
	(5, 10, 1, 1, 'A-2026-0005', '2026-08-16', 'ACTIVO'),
	(6, 15, 1, 1, 'DEMO-2026-001', '2026-08-16', 'ACTIVO');

-- Volcando estructura para tabla colegio.niveles
CREATE TABLE IF NOT EXISTS `niveles` (
  `id_nivel` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint unsigned NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.niveles: ~3 rows (aproximadamente)
INSERT INTO `niveles` (`id_nivel`, `nombre`, `orden`, `estado`) VALUES
	(1, 'Inicial', 1, 'ACTIVO'),
	(2, 'Primaria', 2, 'ACTIVO'),
	(3, 'Secundaria', 3, 'ACTIVO');

-- Volcando estructura para tabla colegio.pagos_alumno
CREATE TABLE IF NOT EXISTS `pagos_alumno` (
  `id_pago` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero_recibo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_apoderado` bigint unsigned DEFAULT NULL,
  `fecha_pago` datetime NOT NULL,
  `medio_pago` enum('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `importe_total` decimal(12,2) NOT NULL,
  `estado` enum('REGISTRADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGISTRADO',
  `registrado_por` bigint unsigned DEFAULT NULL,
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_anulacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anulado_por` bigint unsigned DEFAULT NULL,
  `anulado_en` datetime DEFAULT NULL,
  `id_apertura` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  UNIQUE KEY `uq_recibo` (`numero_recibo`),
  KEY `fk_pago_apoderado` (`id_apoderado`),
  KEY `ix_pago_apertura` (`id_apertura`),
  CONSTRAINT `fk_pago_apoderado` FOREIGN KEY (`id_apoderado`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.pagos_alumno: ~12 rows (aproximadamente)
INSERT INTO `pagos_alumno` (`id_pago`, `numero_recibo`, `id_apoderado`, `fecha_pago`, `medio_pago`, `importe_total`, `estado`, `registrado_por`, `observacion`, `motivo_anulacion`, `anulado_por`, `anulado_en`, `id_apertura`) VALUES
	(1, 'R-2026-000001', 16, '2026-08-16 21:45:33', 'YAPE', 100.00, 'REGISTRADO', 1, 'Se pago al número de bladi', NULL, NULL, NULL, NULL),
	(2, 'R-2026-000002', 16, '2026-08-16 21:47:16', 'EFECTIVO', 75.00, 'REGISTRADO', 1, 'Recibe en fisico.', NULL, NULL, NULL, NULL),
	(3, 'R-2026-000003', 16, '2026-08-16 21:48:29', 'EFECTIVO', 80.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(4, 'R-2026-000004', NULL, '2026-08-16 22:31:48', 'EFECTIVO', 120.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(5, 'R-2026-000005', 4, '2026-08-16 22:32:49', 'EFECTIVO', 420.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(6, 'R-2026-000006', NULL, '2026-08-16 22:32:55', 'EFECTIVO', 420.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(7, 'R-2026-000007', 5, '2026-08-16 22:33:01', 'EFECTIVO', 420.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(8, 'R-2026-000008', 16, '2026-08-16 22:33:07', 'EFECTIVO', 420.00, 'ANULADO', 1, NULL, 'Veo dos deudas.', 1, '2026-08-16 23:08:02', NULL),
	(9, 'R-2026-000009', NULL, '2026-08-16 22:34:09', 'EFECTIVO', 300.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(10, 'R-2026-000010', NULL, '2026-08-16 23:42:25', 'EFECTIVO', 300.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(11, 'R-2026-000011', 16, '2026-08-17 00:04:22', 'EFECTIVO', 100.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(12, 'R-2026-000012', 16, '2026-08-17 00:05:37', 'EFECTIVO', 20.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, NULL),
	(13, 'R-2026-000013', 16, '2026-08-18 22:38:07', 'EFECTIVO', 300.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, 1),
	(14, 'R-2026-000014', 16, '2026-08-18 22:38:42', 'YAPE', 100.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, 1),
	(15, 'R-2026-000015', 16, '2026-08-18 22:39:21', 'PLIN', 100.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, 1),
	(16, 'R-2026-000016', 16, '2026-08-18 22:39:30', 'TRANSFERENCIA', 100.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, 1),
	(17, 'R-2026-000017', 5, '2026-08-18 22:39:45', 'EFECTIVO', 100.00, 'REGISTRADO', 1, NULL, NULL, NULL, NULL, 1),
	(18, 'R-2026-000018', 5, '2026-08-18 23:14:29', 'EFECTIVO', 200.00, 'REGISTRADO', 3, NULL, NULL, NULL, NULL, 2),
	(19, 'R-2026-000019', NULL, '2026-08-18 23:14:38', 'EFECTIVO', 250.00, 'REGISTRADO', 3, NULL, NULL, NULL, NULL, 2),
	(20, 'R-2026-000020', 4, '2026-08-18 23:15:01', 'YAPE', 150.00, 'REGISTRADO', 3, NULL, NULL, NULL, NULL, 2),
	(21, 'R-2026-000021', 4, '2026-08-18 23:19:58', 'EFECTIVO', 100.00, 'REGISTRADO', 3, NULL, NULL, NULL, NULL, 3),
	(22, 'R-2026-000022', 4, '2026-08-18 23:20:38', 'EFECTIVO', 50.00, 'REGISTRADO', 3, NULL, NULL, NULL, NULL, 3);

-- Volcando estructura para tabla colegio.pagos_alumno_detalles
CREATE TABLE IF NOT EXISTS `pagos_alumno_detalles` (
  `id_pago_detalle` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pago` bigint unsigned NOT NULL,
  `id_cuenta` bigint unsigned NOT NULL,
  `importe` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id_pago_detalle`),
  KEY `fk_pago_det_pago` (`id_pago`),
  KEY `fk_pago_det_cuenta` (`id_cuenta`),
  CONSTRAINT `fk_pago_det_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas_cobrar` (`id_cuenta`),
  CONSTRAINT `fk_pago_det_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos_alumno` (`id_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.pagos_alumno_detalles: ~16 rows (aproximadamente)
INSERT INTO `pagos_alumno_detalles` (`id_pago_detalle`, `id_pago`, `id_cuenta`, `importe`) VALUES
	(1, 1, 1, 100.00),
	(2, 2, 1, 75.00),
	(3, 3, 2, 80.00),
	(4, 4, 13, 120.00),
	(5, 5, 10, 120.00),
	(6, 5, 5, 300.00),
	(7, 6, 12, 120.00),
	(8, 6, 7, 300.00),
	(9, 7, 11, 120.00),
	(10, 7, 6, 300.00),
	(11, 8, 14, 120.00),
	(12, 8, 9, 300.00),
	(13, 9, 8, 300.00),
	(14, 10, 19, 300.00),
	(15, 11, 14, 100.00),
	(16, 12, 14, 20.00),
	(17, 13, 9, 300.00),
	(18, 14, 20, 100.00),
	(19, 15, 20, 100.00),
	(20, 16, 20, 100.00),
	(21, 17, 16, 100.00),
	(22, 18, 16, 200.00),
	(23, 19, 18, 250.00),
	(24, 20, 15, 150.00),
	(25, 21, 15, 100.00),
	(26, 22, 15, 50.00);

-- Volcando estructura para tabla colegio.pagos_planilla
CREATE TABLE IF NOT EXISTS `pagos_planilla` (
  `id_pago_planilla` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_planilla_detalle` bigint unsigned NOT NULL,
  `importe` decimal(12,2) NOT NULL,
  `medio_pago` enum('EFECTIVO','TRANSFERENCIA','DEPOSITO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagado_por` bigint unsigned DEFAULT NULL,
  `pagado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('REGISTRADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGISTRADO',
  `anulado_por` bigint unsigned DEFAULT NULL,
  `anulado_en` datetime DEFAULT NULL,
  `motivo_anulacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_pago_planilla`),
  KEY `ix_pago_planilla_detalle` (`id_planilla_detalle`,`estado`),
  CONSTRAINT `fk_pago_planilla_detalle` FOREIGN KEY (`id_planilla_detalle`) REFERENCES `planilla_detalles` (`id_planilla_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.pagos_planilla: ~0 rows (aproximadamente)
INSERT INTO `pagos_planilla` (`id_pago_planilla`, `id_planilla_detalle`, `importe`, `medio_pago`, `referencia`, `observacion`, `pagado_por`, `pagado_en`, `estado`, `anulado_por`, `anulado_en`, `motivo_anulacion`) VALUES
	(1, 1, 2000.00, 'TRANSFERENCIA', 'No se que referencia', 'Sin observaciones', 1, '2026-08-19 00:59:42', 'REGISTRADO', NULL, NULL, NULL),
	(2, 1, 200.00, 'EFECTIVO', 'No se que referencia', 'todo ok', 1, '2026-08-19 01:00:08', 'REGISTRADO', NULL, NULL, NULL);

-- Volcando estructura para tabla colegio.periodos_academicos
CREATE TABLE IF NOT EXISTS `periodos_academicos` (
  `id_periodo_academico` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('PLANIFICADO','ACTIVO','CERRADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PLANIFICADO',
  PRIMARY KEY (`id_periodo_academico`),
  UNIQUE KEY `uq_periodo_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.periodos_academicos: ~1 rows (aproximadamente)
INSERT INTO `periodos_academicos` (`id_periodo_academico`, `nombre`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(1, 'Año escolar 2026', '2026-03-02', '2026-12-18', 'ACTIVO'),
	(2, 'Año escolar 2027', '2027-01-01', '2027-12-31', 'PLANIFICADO');

-- Volcando estructura para tabla colegio.periodos_pago
CREATE TABLE IF NOT EXISTS `periodos_pago` (
  `id_periodo_pago` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('ABIERTO','CALCULADO','REVISADO','CERRADO','PAGADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTO',
  `revisado_por` bigint unsigned DEFAULT NULL,
  `revisado_en` datetime DEFAULT NULL,
  `cerrado_por` bigint unsigned DEFAULT NULL,
  `cerrado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_periodo_pago`),
  UNIQUE KEY `uq_periodo_pago` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.periodos_pago: ~0 rows (aproximadamente)
INSERT INTO `periodos_pago` (`id_periodo_pago`, `nombre`, `fecha_inicio`, `fecha_fin`, `estado`, `revisado_por`, `revisado_en`, `cerrado_por`, `cerrado_en`) VALUES
	(1, 'Marzo 2026', '2026-03-01', '2026-03-31', 'CERRADO', 1, '2026-08-19 00:52:32', 1, '2026-08-19 00:52:44'),
	(2, 'Agosto', '2026-08-01', '2026-08-31', 'CALCULADO', NULL, NULL, NULL, NULL);

-- Volcando estructura para tabla colegio.permisos
CREATE TABLE IF NOT EXISTS `permisos` (
  `id_permiso` int unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `uq_permiso` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.permisos: ~24 rows (aproximadamente)
INSERT INTO `permisos` (`id_permiso`, `clave`, `descripcion`, `estado`) VALUES
	(1, 'personas.ver', 'Consultar personas', 'ACTIVO'),
	(2, 'personas.crear', 'Registrar personas', 'ACTIVO'),
	(3, 'alumnos.ver', 'Consultar alumnos', 'ACTIVO'),
	(4, 'apoderados.ver', 'Consultar apoderados', 'ACTIVO'),
	(5, 'academico.ver', 'Consultar estructura académica', 'ACTIVO'),
	(6, 'academico.gestionar', 'Gestionar estructura académica', 'ACTIVO'),
	(7, 'matriculas.ver', 'Consultar matrículas', 'ACTIVO'),
	(8, 'matriculas.crear', 'Registrar matrículas', 'ACTIVO'),
	(9, 'fotochecks.ver', 'Consultar fotochecks', 'ACTIVO'),
	(10, 'fotochecks.generar', 'Generar o renovar QR', 'ACTIVO'),
	(11, 'asistencia.ver', 'Consultar asistencia', 'ACTIVO'),
	(12, 'asistencia.escanear', 'Registrar asistencia QR', 'ACTIVO'),
	(13, 'asistencia.configurar', 'Configurar jornadas y dispositivos', 'ACTIVO'),
	(14, 'asistencia.modificar', 'Modificar asistencia calculada', 'ACTIVO'),
	(15, 'cobros.gestionar', 'Gestionar cobros', 'ACTIVO'),
	(16, 'planilla.calcular', 'Calcular planilla', 'ACTIVO'),
	(17, 'planilla.cerrar', 'Cerrar periodos de planilla', 'ACTIVO'),
	(18, 'reportes.ver', 'Consultar reportes', 'ACTIVO'),
	(19, 'auditoria.ver', 'Consultar auditoría', 'ACTIVO'),
	(20, 'configuracion.gestionar', 'Gestionar configuración', 'ACTIVO'),
	(30, 'horarios.ver', 'Consultar horarios', 'ACTIVO'),
	(31, 'horarios.gestionar', 'Asignar jornadas y sesiones', 'ACTIVO'),
	(43, 'incidencias.ver', 'Consultar incidencias', 'ACTIVO'),
	(44, 'incidencias.gestionar', 'Registrar y resolver incidencias', 'ACTIVO'),
	(45, 'portal.ver', 'Consultar portal de familia', 'ACTIVO'),
	(46, 'usuarios.gestionar', 'Gestionar usuarios y sus roles', 'ACTIVO');

-- Volcando estructura para tabla colegio.personas
CREATE TABLE IF NOT EXISTS `personas` (
  `id_persona` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_documento` enum('DNI','CE','PASAPORTE','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DNI',
  `numero_documento` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` enum('F','M','OTRO') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fotografia` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_trabajador` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_persona`),
  UNIQUE KEY `uq_persona_documento` (`tipo_documento`,`numero_documento`),
  UNIQUE KEY `uq_persona_codigo_trabajador` (`codigo_trabajador`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.personas: ~17 rows (aproximadamente)
INSERT INTO `personas` (`id_persona`, `tipo_documento`, `numero_documento`, `nombres`, `apellidos`, `fecha_nacimiento`, `sexo`, `telefono`, `correo`, `direccion`, `fotografia`, `codigo_trabajador`, `estado`, `creado_en`, `actualizado_en`) VALUES
	(1, 'DNI', '70000001', 'Valeria', 'Quispe Flores', NULL, NULL, '999100101', 'valeria.qusipe@demo.pe', NULL, NULL, NULL, 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(2, 'DNI', '70000002', 'Diego Armando', 'Mamani Condori', NULL, NULL, '999100102', 'diego.mamani@demo.pe', NULL, 'c9b1cd348b5e58236eb14f061ac3e678.jpg', NULL, 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(3, 'DNI', '70000003', 'Sofía', 'Torres Salazar', NULL, NULL, '999100103', 'sofia.torres@demo.pe', NULL, NULL, NULL, 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(4, 'DNI', '40000001', 'Elena', 'Flores Ramos', NULL, NULL, '999200101', 'elena.flores@demo.pe', NULL, NULL, NULL, 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(5, 'DNI', '40000002', 'Carlos', 'Condori Huamán', NULL, NULL, '999200102', 'carlos.condori@demo.pe', NULL, NULL, NULL, 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(6, 'DNI', '40000003', 'Rosa', 'Salazar Vega', NULL, NULL, '999200103', 'rosa.salazar@demo.pe', NULL, NULL, NULL, 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(7, 'DNI', '30000001', 'María', 'Paredes Soto', NULL, NULL, '999300101', 'maria.paredes@demo.pe', NULL, NULL, 'CS-2026-0001', 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(8, 'DNI', '30000002', 'Jorge', 'Vargas León', NULL, NULL, '999300102', 'jorge.vargas@demo.pe', NULL, NULL, 'CS-2026-0002', 'ACTIVO', '2026-08-15 15:38:37', NULL),
	(9, 'DNI', '71062875', 'Roman', 'Oliva', NULL, NULL, '951241733', 'roman@empresa.com', NULL, '5e2264b32d158a9c74dfe54da41d74ff.png', NULL, 'ACTIVO', '2026-08-15 16:15:56', NULL),
	(10, 'DNI', '777', 'Prueba', '01', NULL, NULL, '999', NULL, NULL, NULL, NULL, 'ACTIVO', '2026-08-16 01:30:53', NULL),
	(11, 'DNI', '888', 'Prueba', '02', NULL, NULL, '999', NULL, NULL, NULL, NULL, 'ACTIVO', '2026-08-16 01:31:26', NULL),
	(12, 'DNI', '999', 'Prueba', '03', NULL, NULL, '999', NULL, NULL, 'c3fe49cd569530b69ebfa72d71dc5a68.jpg', NULL, 'ACTIVO', '2026-08-16 02:22:07', NULL),
	(13, 'DNI', '74211088', 'Blademir', 'Acero Oliva', NULL, NULL, '999', 'blademir@ejemplo.com', NULL, 'd8b68f96ac92de8288095b8927213555.png', 'CS-2026-0003', 'ACTIVO', '2026-08-16 17:05:54', NULL),
	(15, 'DNI', '90000001', 'Valentina', 'Demostración', NULL, NULL, '999000000', 'demo.90000001@colegio.test', NULL, NULL, NULL, 'ACTIVO', '2026-08-16 21:34:01', NULL),
	(16, 'DNI', '90000002', 'María', 'Apoderada Demo', NULL, NULL, '999000000', 'demo.90000002@colegio.test', NULL, NULL, NULL, 'ACTIVO', '2026-08-16 21:34:01', NULL),
	(17, 'DNI', '90000003', 'Carlos', 'Docente Demo', NULL, NULL, '999000000', 'demo.90000003@colegio.test', NULL, NULL, 'CS-2026-9001', 'ACTIVO', '2026-08-16 21:34:01', NULL),
	(18, 'DNI', '90000004', 'Lucía', 'Administrativa Demo', NULL, NULL, '999000000', 'demo.90000004@colegio.test', NULL, NULL, 'CS-2026-9002', 'ACTIVO', '2026-08-16 21:34:01', NULL),
	(19, 'DNI', '123', 'Soporte', 'Tecnico', NULL, NULL, '999', NULL, NULL, NULL, 'CS-2026-9003', 'ACTIVO', '2026-08-18 23:10:11', NULL);

-- Volcando estructura para tabla colegio.persona_perfiles
CREATE TABLE IF NOT EXISTS `persona_perfiles` (
  `id_persona_perfil` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned NOT NULL,
  `tipo` enum('ALUMNO','DOCENTE','ADMINISTRATIVO','APODERADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_persona_perfil`),
  UNIQUE KEY `uq_persona_perfil` (`id_persona`,`tipo`),
  CONSTRAINT `fk_perfil_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.persona_perfiles: ~18 rows (aproximadamente)
INSERT INTO `persona_perfiles` (`id_persona_perfil`, `id_persona`, `tipo`, `estado`, `creado_en`) VALUES
	(1, 1, 'ALUMNO', 'ACTIVO', '2026-08-15 15:38:37'),
	(2, 2, 'ALUMNO', 'ACTIVO', '2026-08-15 15:38:37'),
	(3, 3, 'ALUMNO', 'ACTIVO', '2026-08-15 15:38:37'),
	(4, 4, 'APODERADO', 'ACTIVO', '2026-08-15 15:38:37'),
	(5, 5, 'APODERADO', 'ACTIVO', '2026-08-15 15:38:37'),
	(6, 6, 'APODERADO', 'ACTIVO', '2026-08-15 15:38:37'),
	(7, 7, 'DOCENTE', 'ACTIVO', '2026-08-15 15:38:37'),
	(8, 8, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-15 15:38:37'),
	(9, 9, 'ALUMNO', 'ACTIVO', '2026-08-15 16:15:56'),
	(10, 10, 'ALUMNO', 'ACTIVO', '2026-08-16 01:30:53'),
	(11, 11, 'ALUMNO', 'ACTIVO', '2026-08-16 01:31:26'),
	(14, 12, 'ALUMNO', 'ACTIVO', '2026-08-16 02:22:07'),
	(18, 13, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-16 17:05:54'),
	(19, 9, 'ADMINISTRATIVO', 'INACTIVO', '2026-08-16 20:28:47'),
	(25, 15, 'ALUMNO', 'ACTIVO', '2026-08-16 21:34:01'),
	(26, 16, 'APODERADO', 'ACTIVO', '2026-08-16 21:34:01'),
	(27, 17, 'DOCENTE', 'ACTIVO', '2026-08-16 21:34:01'),
	(28, 18, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-16 21:34:01'),
	(33, 19, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-18 23:10:11');

-- Volcando estructura para tabla colegio.planilla_ajustes
CREATE TABLE IF NOT EXISTS `planilla_ajustes` (
  `id_ajuste` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_planilla_detalle` bigint unsigned NOT NULL,
  `id_detalle_concepto` bigint unsigned DEFAULT NULL,
  `naturaleza` enum('BONIFICACION','DESCUENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `importe` decimal(12,2) NOT NULL,
  `motivo` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registrado_por` bigint unsigned DEFAULT NULL,
  `registrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('ACTIVO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `anulado_por` bigint unsigned DEFAULT NULL,
  `anulado_en` datetime DEFAULT NULL,
  `motivo_anulacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_ajuste`),
  KEY `ix_ajuste_detalle` (`id_planilla_detalle`,`estado`),
  KEY `fk_ajuste_concepto` (`id_detalle_concepto`),
  CONSTRAINT `fk_ajuste_concepto` FOREIGN KEY (`id_detalle_concepto`) REFERENCES `planilla_detalle_conceptos` (`id_detalle_concepto`) ON DELETE SET NULL,
  CONSTRAINT `fk_ajuste_planilla_detalle` FOREIGN KEY (`id_planilla_detalle`) REFERENCES `planilla_detalles` (`id_planilla_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.planilla_ajustes: ~0 rows (aproximadamente)
INSERT INTO `planilla_ajustes` (`id_ajuste`, `id_planilla_detalle`, `id_detalle_concepto`, `naturaleza`, `importe`, `motivo`, `registrado_por`, `registrado_en`, `estado`, `anulado_por`, `anulado_en`, `motivo_anulacion`) VALUES
	(1, 7, 7, 'BONIFICACION', 100.00, 'Por responsable.', 1, '2026-08-19 00:42:50', 'ACTIVO', NULL, NULL, NULL);

-- Volcando estructura para tabla colegio.planilla_detalles
CREATE TABLE IF NOT EXISTS `planilla_detalles` (
  `id_planilla_detalle` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_periodo_pago` int unsigned NOT NULL,
  `id_vinculo` bigint unsigned NOT NULL,
  `monto_base` decimal(12,2) NOT NULL,
  `bonificaciones` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descuentos` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_pagar` decimal(12,2) NOT NULL,
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_pendiente` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` enum('CALCULADO','REVISADO','CERRADO','PARCIAL','PAGADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CALCULADO',
  `calculado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_planilla_detalle`),
  UNIQUE KEY `uq_planilla_trabajador` (`id_periodo_pago`,`id_vinculo`),
  KEY `fk_planilla_vinculo` (`id_vinculo`),
  CONSTRAINT `fk_planilla_periodo` FOREIGN KEY (`id_periodo_pago`) REFERENCES `periodos_pago` (`id_periodo_pago`),
  CONSTRAINT `fk_planilla_vinculo` FOREIGN KEY (`id_vinculo`) REFERENCES `vinculos_laborales` (`id_vinculo`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.planilla_detalles: ~2 rows (aproximadamente)
INSERT INTO `planilla_detalles` (`id_planilla_detalle`, `id_periodo_pago`, `id_vinculo`, `monto_base`, `bonificaciones`, `descuentos`, `monto_pagar`, `monto_pagado`, `saldo_pendiente`, `estado`, `calculado_en`) VALUES
	(1, 1, 1, 2200.00, 0.00, 0.00, 2200.00, 2200.00, 0.00, 'PAGADO', '2026-08-15 22:16:07'),
	(2, 1, 2, 1800.00, 0.00, 0.00, 1800.00, 0.00, 1800.00, 'CERRADO', '2026-08-15 22:16:07'),
	(7, 2, 3, 1309.52, 100.00, 0.00, 1409.52, 0.00, 1409.52, 'CALCULADO', '2026-08-19 00:37:25'),
	(8, 2, 5, 942.86, 0.00, 0.00, 942.86, 0.00, 942.86, 'CALCULADO', '2026-08-19 00:37:25'),
	(9, 2, 4, 942.86, 0.00, 0.00, 942.86, 0.00, 942.86, 'CALCULADO', '2026-08-19 00:37:25'),
	(10, 2, 1, 1152.38, 0.00, 0.00, 1152.38, 0.00, 1152.38, 'CALCULADO', '2026-08-19 00:37:25'),
	(11, 2, 6, 540.48, 0.00, 0.00, 540.48, 0.00, 540.48, 'CALCULADO', '2026-08-19 00:37:25'),
	(12, 2, 2, 942.86, 0.00, 0.00, 942.86, 0.00, 942.86, 'CALCULADO', '2026-08-19 00:37:25');

-- Volcando estructura para tabla colegio.planilla_detalle_conceptos
CREATE TABLE IF NOT EXISTS `planilla_detalle_conceptos` (
  `id_detalle_concepto` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_planilla_detalle` bigint unsigned NOT NULL,
  `id_concepto_pago` int unsigned NOT NULL,
  `id_regla` int unsigned DEFAULT NULL,
  `cantidad` decimal(12,2) NOT NULL DEFAULT '1.00',
  `importe` decimal(12,2) NOT NULL,
  `explicacion` json DEFAULT NULL,
  PRIMARY KEY (`id_detalle_concepto`),
  KEY `fk_pdc_planilla` (`id_planilla_detalle`),
  KEY `fk_pdc_concepto` (`id_concepto_pago`),
  KEY `fk_pdc_regla` (`id_regla`),
  CONSTRAINT `fk_pdc_concepto` FOREIGN KEY (`id_concepto_pago`) REFERENCES `conceptos_pago` (`id_concepto_pago`),
  CONSTRAINT `fk_pdc_planilla` FOREIGN KEY (`id_planilla_detalle`) REFERENCES `planilla_detalles` (`id_planilla_detalle`),
  CONSTRAINT `fk_pdc_regla` FOREIGN KEY (`id_regla`) REFERENCES `reglas_descuento` (`id_regla`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.planilla_detalle_conceptos: ~0 rows (aproximadamente)
INSERT INTO `planilla_detalle_conceptos` (`id_detalle_concepto`, `id_planilla_detalle`, `id_concepto_pago`, `id_regla`, `cantidad`, `importe`, `explicacion`) VALUES
	(1, 7, 1, NULL, 11.00, 1309.52, '{"dias": 11, "feriados": 0}'),
	(2, 8, 1, NULL, 11.00, 942.86, '{"dias": 11, "feriados": 0}'),
	(3, 9, 1, NULL, 11.00, 942.86, '{"dias": 11, "feriados": 0}'),
	(4, 10, 1, NULL, 11.00, 1152.38, '{"dias": 11, "feriados": 0}'),
	(5, 11, 1, NULL, 10.00, 540.48, '{"dias": 10, "feriados": 0}'),
	(6, 12, 1, NULL, 11.00, 942.86, '{"dias": 11, "feriados": 0}'),
	(7, 7, 2, NULL, 1.00, 100.00, '{"manual": true, "motivo": "Por responsable.", "usuario": 1}');

-- Volcando estructura para tabla colegio.reglas_descuento
CREATE TABLE IF NOT EXISTS `reglas_descuento` (
  `id_regla` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `naturaleza` enum('DESCUENTO','BONIFICACION') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DESCUENTO',
  `evento` enum('TARDANZA','FALTA','SALIDA_ANTICIPADA','HORA_NO_TRABAJADA','FIJO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `formula` json NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_regla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.reglas_descuento: ~0 rows (aproximadamente)

-- Volcando estructura para tabla colegio.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_rol` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.roles: ~9 rows (aproximadamente)
INSERT INTO `roles` (`id_rol`, `nombre`, `estado`) VALUES
	(1, 'Super Administrador', 'ACTIVO'),
	(2, 'Administrador', 'ACTIVO'),
	(3, 'Director', 'ACTIVO'),
	(4, 'Responsable de asistencia', 'ACTIVO'),
	(5, 'Responsable de cobros', 'ACTIVO'),
	(6, 'Responsable de planilla', 'ACTIVO'),
	(7, 'Docente', 'ACTIVO'),
	(8, 'Apoderado', 'ACTIVO'),
	(9, 'Consulta', 'ACTIVO');

-- Volcando estructura para tabla colegio.rol_permiso
CREATE TABLE IF NOT EXISTS `rol_permiso` (
  `id_rol` int unsigned NOT NULL,
  `id_permiso` int unsigned NOT NULL,
  PRIMARY KEY (`id_rol`,`id_permiso`),
  KEY `fk_rp_permiso` (`id_permiso`),
  CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.rol_permiso: ~27 rows (aproximadamente)
INSERT INTO `rol_permiso` (`id_rol`, `id_permiso`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(1, 6),
	(1, 7),
	(1, 8),
	(1, 9),
	(1, 10),
	(1, 11),
	(1, 12),
	(1, 13),
	(1, 14),
	(1, 15),
	(5, 15),
	(1, 16),
	(1, 17),
	(1, 18),
	(1, 19),
	(1, 20),
	(1, 30),
	(1, 31),
	(1, 43),
	(1, 44),
	(1, 45),
	(8, 45),
	(1, 46);

-- Volcando estructura para tabla colegio.secciones
CREATE TABLE IF NOT EXISTS `secciones` (
  `id_seccion` int unsigned NOT NULL AUTO_INCREMENT,
  `id_grado` int unsigned NOT NULL,
  `nombre` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacidad` smallint unsigned DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_seccion`),
  UNIQUE KEY `uq_seccion` (`id_grado`,`nombre`),
  CONSTRAINT `fk_seccion_grado` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.secciones: ~9 rows (aproximadamente)
INSERT INTO `secciones` (`id_seccion`, `id_grado`, `nombre`, `capacidad`, `estado`) VALUES
	(1, 2, 'A', 28, 'ACTIVO'),
	(2, 2, 'B', 28, 'ACTIVO'),
	(3, 4, 'A', 30, 'ACTIVO'),
	(4, 3, 'A', 20, 'ACTIVO'),
	(5, 5, 'A', 20, 'ACTIVO'),
	(6, 7, 'A', 20, 'ACTIVO'),
	(7, 8, 'A', 20, 'ACTIVO'),
	(8, 9, 'A', 20, 'ACTIVO'),
	(9, 10, 'A', 20, 'ACTIVO');

-- Volcando estructura para tabla colegio.tarifas_cobro
CREATE TABLE IF NOT EXISTS `tarifas_cobro` (
  `id_tarifa` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_concepto_cobro` int unsigned NOT NULL,
  `id_periodo_academico` int unsigned NOT NULL,
  `id_grado` int unsigned DEFAULT NULL,
  `importe` decimal(12,2) NOT NULL,
  `vigencia_inicio` date NOT NULL,
  `vigencia_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_tarifa`),
  KEY `fk_tarifa_concepto` (`id_concepto_cobro`),
  KEY `fk_tarifa_periodo` (`id_periodo_academico`),
  CONSTRAINT `fk_tarifa_concepto` FOREIGN KEY (`id_concepto_cobro`) REFERENCES `conceptos_cobro` (`id_concepto_cobro`),
  CONSTRAINT `fk_tarifa_periodo` FOREIGN KEY (`id_periodo_academico`) REFERENCES `periodos_academicos` (`id_periodo_academico`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.tarifas_cobro: ~6 rows (aproximadamente)
INSERT INTO `tarifas_cobro` (`id_tarifa`, `id_concepto_cobro`, `id_periodo_academico`, `id_grado`, `importe`, `vigencia_inicio`, `vigencia_fin`, `estado`) VALUES
	(1, 4, 1, 2, 350.00, '2026-08-16', NULL, 'INACTIVO'),
	(2, 5, 1, 2, 80.00, '2026-08-16', NULL, 'INACTIVO'),
	(3, 6, 1, 2, 120.00, '2026-08-16', NULL, 'INACTIVO'),
	(4, 4, 1, 2, 300.00, '2026-09-01', '2026-09-30', 'INACTIVO'),
	(5, 6, 1, NULL, 100.00, '2026-08-16', '2026-08-30', 'INACTIVO'),
	(6, 2, 1, NULL, 300.00, '2026-01-01', '2026-12-31', 'ACTIVO');

-- Volcando estructura para tabla colegio.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned DEFAULT NULL,
  `usuario` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `ultimo_acceso_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuario` (`usuario`),
  UNIQUE KEY `uq_usuario_persona` (`id_persona`),
  CONSTRAINT `fk_usuario_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.usuarios: ~2 rows (aproximadamente)
INSERT INTO `usuarios` (`id_usuario`, `id_persona`, `usuario`, `contrasena`, `estado`, `ultimo_acceso_en`, `creado_en`) VALUES
	(1, NULL, 'administrador', '$2y$10$BAEMzLwTJtqkcg6XBMYlUOPQrd9xRrqEMoh7raNluhGwSxolnfWN2', 'ACTIVO', NULL, '2026-08-15 15:39:24'),
	(2, 5, 'carlos', '$2y$10$cQGBL4TsigNfdI.g2GOODeN.f0qO5sc3D6Osz8EYeECJt8Vg97xvS', 'ACTIVO', NULL, '2026-08-15 22:07:42'),
	(3, 19, 'soporte', '$2y$10$dd8H3ymPnzyf6tYGSrgbbeicb100JL9SOcmDsJOHps8AM/6JyO7Q.', 'ACTIVO', NULL, '2026-08-18 23:12:33');

-- Volcando estructura para tabla colegio.usuario_rol
CREATE TABLE IF NOT EXISTS `usuario_rol` (
  `id_usuario` bigint unsigned NOT NULL,
  `id_rol` int unsigned NOT NULL,
  PRIMARY KEY (`id_usuario`,`id_rol`),
  KEY `fk_ur_rol` (`id_rol`),
  CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.usuario_rol: ~2 rows (aproximadamente)
INSERT INTO `usuario_rol` (`id_usuario`, `id_rol`) VALUES
	(1, 1),
	(3, 5),
	(2, 8);

-- Volcando estructura para tabla colegio.vinculos_laborales
CREATE TABLE IF NOT EXISTS `vinculos_laborales` (
  `id_vinculo` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned NOT NULL,
  `id_vinculo_anterior` bigint unsigned DEFAULT NULL,
  `id_area` int unsigned DEFAULT NULL,
  `id_cargo` int unsigned DEFAULT NULL,
  `tipo` enum('DOCENTE','ADMINISTRATIVO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalidad_pago` enum('MENSUAL','HORA','SESION','MIXTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto_base` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_mensual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_hora` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_sesion` decimal(12,2) NOT NULL DEFAULT '0.00',
  `dias_laborables` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1,2,3,4,5',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO','FINALIZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `motivo_cambio` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registrado_por` bigint unsigned DEFAULT NULL,
  `registrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vinculo`),
  KEY `fk_vinculo_persona` (`id_persona`),
  KEY `fk_vinculo_area` (`id_area`),
  KEY `fk_vinculo_cargo` (`id_cargo`),
  KEY `ix_vinculo_anterior` (`id_vinculo_anterior`),
  CONSTRAINT `fk_vinculo_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`),
  CONSTRAINT `fk_vinculo_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`),
  CONSTRAINT `fk_vinculo_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.vinculos_laborales: ~5 rows (aproximadamente)
INSERT INTO `vinculos_laborales` (`id_vinculo`, `id_persona`, `id_vinculo_anterior`, `id_area`, `id_cargo`, `tipo`, `modalidad_pago`, `monto_base`, `monto_mensual`, `monto_hora`, `monto_sesion`, `dias_laborables`, `fecha_inicio`, `fecha_fin`, `estado`, `motivo_cambio`, `registrado_por`, `registrado_en`) VALUES
	(1, 7, NULL, 1, 1, 'DOCENTE', 'MENSUAL', 2200.00, 2200.00, 0.00, 0.00, '1,2,3,4,5', '2026-03-01', NULL, 'ACTIVO', NULL, NULL, '2026-08-19 20:19:18'),
	(2, 8, NULL, 2, 2, 'ADMINISTRATIVO', 'MENSUAL', 1800.00, 1800.00, 0.00, 0.00, '1,2,3,4,5', '2026-03-01', NULL, 'ACTIVO', NULL, NULL, '2026-08-19 20:19:18'),
	(3, 13, NULL, NULL, 3, 'ADMINISTRATIVO', 'MENSUAL', 2500.00, 2500.00, 0.00, 0.00, '1,2,3,4,5', '2026-08-16', '2026-12-31', 'ACTIVO', NULL, NULL, '2026-08-19 20:19:18'),
	(4, 17, NULL, 3, 4, 'DOCENTE', 'MENSUAL', 1800.00, 1800.00, 0.00, 0.00, '1,2,3,4,5', '2026-08-16', NULL, 'ACTIVO', NULL, NULL, '2026-08-19 20:19:18'),
	(5, 18, NULL, 3, 4, 'ADMINISTRATIVO', 'MENSUAL', 1800.00, 1800.00, 0.00, 0.00, '1,2,3,4,5', '2026-08-16', NULL, 'ACTIVO', NULL, NULL, '2026-08-19 20:19:18'),
	(6, 19, NULL, NULL, 5, 'ADMINISTRATIVO', 'MENSUAL', 1135.00, 1135.00, 0.00, 0.00, '1,2,3,4,5', '2026-08-18', '2026-12-31', 'ACTIVO', NULL, NULL, '2026-08-19 20:19:18');

-- Volcando estructura para disparador colegio.bi_planilla_inicializar_saldo
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `bi_planilla_inicializar_saldo` BEFORE INSERT ON `planilla_detalles` FOR EACH ROW SET NEW.monto_pagado=0,NEW.saldo_pendiente=NEW.monto_pagar//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador colegio.bu_planilla_recalcular_saldo
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `bu_planilla_recalcular_saldo` BEFORE UPDATE ON `planilla_detalles` FOR EACH ROW SET NEW.saldo_pendiente=IF(NEW.estado='CALCULADO',GREATEST(0,NEW.monto_pagar-NEW.monto_pagado),NEW.saldo_pendiente)//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
