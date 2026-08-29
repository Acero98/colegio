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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.ajustes_cobro: ~5 rows (aproximadamente)
INSERT INTO `ajustes_cobro` (`id_ajuste`, `id_cuenta`, `tipo`, `importe`, `motivo`, `registrado_por`, `creado_en`) VALUES
	(32, 112, 'DESCUENTO', 20.00, 'Descuento promocional', 49, '2026-08-22 19:54:21'),
	(33, 117, 'BECA', 100.00, 'Beca de rendimiento', 49, '2026-08-22 19:54:21'),
	(34, 118, 'RECARGO', 15.00, 'Recargo por mora', 49, '2026-08-22 19:54:21'),
	(35, 121, 'RECARGO', 30.00, 'Recargo historico', 49, '2026-08-22 19:54:21'),
	(36, 125, 'EXONERACION', 200.00, 'Exoneracion total aprobada', 47, '2026-08-22 19:54:21'),
	(37, 126, 'DESCUENTO', 50.00, 'Precio familiar.', 46, '2026-08-22 22:30:43');

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
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.alumno_apoderado: ~8 rows (aproximadamente)
INSERT INTO `alumno_apoderado` (`id_alumno_apoderado`, `id_alumno`, `id_apoderado`, `parentesco`, `es_principal`, `responsable_economico`, `contacto_emergencia`, `estado`) VALUES
	(56, 188, 200, 'MADRE', 1, 1, 1, 'ACTIVO'),
	(57, 189, 200, 'MADRE', 1, 1, 1, 'ACTIVO'),
	(58, 190, 201, 'PADRE', 1, 1, 1, 'ACTIVO'),
	(59, 191, 202, 'MADRE', 1, 1, 1, 'ACTIVO'),
	(60, 192, 203, 'PADRE', 1, 0, 1, 'ACTIVO'),
	(61, 196, 204, 'TIA', 1, 1, 1, 'ACTIVO'),
	(62, 197, 204, 'TIA', 1, 1, 1, 'ACTIVO'),
	(63, 198, 205, 'MADRE', 1, 1, 1, 'INACTIVO'),
	(64, 217, 218, 'Mamá', 1, 1, 0, 'ACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.aperturas_caja: ~5 rows (aproximadamente)
INSERT INTO `aperturas_caja` (`id_apertura`, `fecha`, `monto_apertura`, `observacion`, `abierto_por`, `abierto_en`, `estado`) VALUES
	(26, '2026-08-23', 500.00, 'Caja abierta para continuar las pruebas', 49, '2026-08-23 08:00:00', 'CERRADA'),
	(27, '2026-08-22', 0.00, NULL, 53, '2026-08-22 22:36:55', 'CERRADA');

-- Volcando estructura para tabla colegio.areas
CREATE TABLE IF NOT EXISTS `areas` (
  `id_area` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_area`),
  UNIQUE KEY `uq_area_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.areas: ~3 rows (aproximadamente)
INSERT INTO `areas` (`id_area`, `nombre`, `estado`) VALUES
	(22, 'Academica', 'ACTIVO'),
	(23, 'Administracion', 'ACTIVO'),
	(24, 'Direccion', 'ACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.asignaciones_horario: ~9 rows (aproximadamente)
INSERT INTO `asignaciones_horario` (`id_asignacion`, `id_persona`, `id_jornada`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(62, 206, 36, '2026-01-01', NULL, 'ACTIVO'),
	(63, 209, 36, '2026-01-01', NULL, 'ACTIVO'),
	(64, 210, 37, '2026-01-01', NULL, 'ACTIVO'),
	(65, 211, 37, '2026-01-01', NULL, 'ACTIVO'),
	(66, 212, 37, '2026-01-01', NULL, 'ACTIVO'),
	(67, 213, 37, '2026-01-01', NULL, 'ACTIVO'),
	(68, 214, 37, '2026-01-01', NULL, 'ACTIVO'),
	(69, 199, 38, '2026-08-01', NULL, 'ACTIVO'),
	(70, 210, 39, '2025-01-01', '2025-12-31', 'INACTIVO');

-- Volcando estructura para tabla colegio.asignaturas
CREATE TABLE IF NOT EXISTS `asignaturas` (
  `id_asignatura` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_asignatura`),
  UNIQUE KEY `uq_asignatura` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.asignaturas: ~6 rows (aproximadamente)
INSERT INTO `asignaturas` (`id_asignatura`, `nombre`, `estado`) VALUES
	(39, 'Comunicacion', 'ACTIVO'),
	(40, 'Matematica', 'ACTIVO'),
	(41, 'Ciencia y Tecnologia', 'ACTIVO'),
	(42, 'Ingles', 'ACTIVO'),
	(43, 'Arte', 'ACTIVO'),
	(44, 'Tutoria', 'ACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.asistencias_diarias: ~29 rows (aproximadamente)
INSERT INTO `asistencias_diarias` (`id_asistencia`, `id_persona`, `fecha`, `id_jornada`, `id_matricula`, `id_vinculo`, `id_carga`, `hora_entrada`, `hora_salida`, `minutos_tardanza`, `minutos_salida_anticipada`, `minutos_trabajados`, `estado`, `observacion`, `calculado_en`) VALUES
	(154, 188, '2026-08-23', 35, 92, NULL, NULL, '07:56:00', NULL, 0, 0, 0, 'PUNTUAL', 'Salida no obligatoria para alumnos', '2026-08-22 19:54:21'),
	(155, 189, '2026-08-23', 35, 93, NULL, NULL, '08:17:00', NULL, 7, 0, 0, 'TARDANZA', 'Supero la tolerancia en 7 minutos', '2026-08-22 19:54:21'),
	(156, 190, '2026-08-23', NULL, 94, NULL, NULL, NULL, NULL, 0, 0, 0, 'FALTA', 'Generada en el cierre diario', '2026-08-22 19:54:21'),
	(157, 191, '2026-08-23', NULL, 95, NULL, NULL, NULL, NULL, 0, 0, 0, 'JUSTIFICADO', 'Cita medica', '2026-08-22 19:54:21'),
	(158, 192, '2026-08-23', NULL, 96, NULL, NULL, NULL, NULL, 0, 0, 0, 'PERMISO', 'Permiso familiar', '2026-08-22 19:54:21'),
	(159, 196, '2026-08-23', 35, 97, NULL, NULL, '07:58:00', '12:10:00', 0, 170, 252, 'SALIDA_ANTICIPADA', 'Recojo autorizado', '2026-08-22 19:54:21'),
	(160, 197, '2026-08-23', NULL, 98, NULL, NULL, NULL, NULL, 0, 0, 0, 'ANULADO', 'Registro manual anulado', '2026-08-22 19:54:21'),
	(161, 198, '2026-08-23', 35, 99, NULL, NULL, '08:03:00', NULL, 0, 0, 0, 'PUNTUAL', 'Registro manual', '2026-08-22 19:54:21'),
	(162, 188, '2026-08-22', 35, 92, NULL, NULL, '08:01:00', NULL, 0, 0, 0, 'PUNTUAL', NULL, '2026-08-22 19:54:21'),
	(163, 189, '2026-08-22', NULL, 93, NULL, NULL, NULL, NULL, 0, 0, 0, 'FALTA', 'Falta sin justificar', '2026-08-22 19:54:21'),
	(164, 190, '2026-08-22', NULL, 94, NULL, NULL, NULL, NULL, 0, 0, 0, 'JUSTIFICADO', 'Descanso medico aprobado', '2026-08-22 19:54:21'),
	(165, 206, '2026-08-23', 36, NULL, 79, NULL, '07:28:00', '15:31:00', 0, 0, 483, 'PUNTUAL', NULL, '2026-08-22 19:54:21'),
	(166, 210, '2026-08-23', 37, NULL, 84, NULL, '08:18:00', '17:02:00', 8, 0, 524, 'TARDANZA', '18 minutos desde la entrada; 8 descontables tras tolerancia', '2026-08-22 19:54:21'),
	(167, 211, '2026-08-23', 37, NULL, 85, NULL, '07:59:00', NULL, 0, 0, 0, 'INCOMPLETO', 'No registro salida', '2026-08-22 19:54:21'),
	(168, 212, '2026-08-23', 37, NULL, 86, NULL, NULL, NULL, 0, 0, 0, 'FALTA', 'Generada por cierre de personal', '2026-08-22 19:54:21'),
	(169, 213, '2026-08-23', 37, NULL, 87, NULL, NULL, NULL, 0, 0, 0, 'LICENCIA', 'Licencia aprobada', '2026-08-22 19:54:21'),
	(170, 214, '2026-08-23', 37, NULL, 88, NULL, NULL, NULL, 0, 0, 0, 'PERMISO', 'Comision de servicio', '2026-08-22 19:54:21'),
	(171, 199, '2026-08-23', 38, NULL, 89, NULL, '08:03:00', '13:01:00', 0, 0, 298, 'PUNTUAL', NULL, '2026-08-22 19:54:21'),
	(172, 207, '2026-08-21', NULL, NULL, 80, 35, '07:59:00', '10:01:00', 0, 0, 122, 'PUNTUAL', NULL, '2026-08-22 19:54:21'),
	(173, 207, '2026-08-22', NULL, NULL, 80, 36, '10:42:00', '12:30:00', 7, 0, 108, 'TARDANZA', NULL, '2026-08-22 19:54:21'),
	(174, 208, '2026-08-22', NULL, NULL, 81, 37, '14:02:00', '15:00:00', 0, 30, 58, 'SALIDA_ANTICIPADA', 'Sesion finalizada antes de hora', '2026-08-22 19:54:21'),
	(175, 209, '2026-08-23', NULL, NULL, 83, 38, '07:58:00', '10:03:00', 0, 0, 125, 'PUNTUAL', NULL, '2026-08-22 19:54:21'),
	(176, 199, '2026-08-22', 38, NULL, NULL, NULL, '20:50:31', '23:58:05', 765, 0, 187, 'TARDANZA', NULL, '2026-08-22 23:58:05');

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
) ENGINE=InnoDB AUTO_INCREMENT=311 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.auditoria: ~46 rows (aproximadamente)
INSERT INTO `auditoria` (`id_auditoria`, `id_usuario`, `accion`, `entidad`, `id_entidad`, `valores_antes`, `valores_despues`, `ip`, `creado_en`) VALUES
	(277, 46, 'INICIO_DATOS_DEMO', 'sistema', NULL, NULL, '{"version": "V01"}', '127.0.0.1', '2026-08-22 19:54:21'),
	(278, 47, 'ACTUALIZAR', 'periodos_academicos', 22, '{"estado": "PLANIFICADO"}', '{"estado": "ACTIVO"}', '127.0.0.1', '2026-08-22 19:54:21'),
	(279, 48, 'REGISTRAR_MANUAL', 'asistencias_diarias', 161, NULL, '{"estado": "PUNTUAL"}', '127.0.0.1', '2026-08-22 19:54:21'),
	(280, 49, 'REGISTRAR_PAGO', 'pagos_alumno', 61, NULL, '{"importe": 920}', '127.0.0.1', '2026-08-22 19:54:21'),
	(281, 50, 'CERRAR_PLANILLA', 'periodos_pago', 22, '{"estado": "REVISADO"}', '{"estado": "CERRADO"}', '127.0.0.1', '2026-08-22 19:54:21'),
	(283, 47, 'INICIO_SESION', 'usuarios', 47, NULL, NULL, '::1', '2026-08-22 20:08:29'),
	(284, 48, 'INICIO_SESION', 'usuarios', 48, NULL, NULL, '::1', '2026-08-22 20:12:12'),
	(285, 49, 'INICIO_SESION', 'usuarios', 49, NULL, NULL, '::1', '2026-08-22 20:12:50'),
	(286, 50, 'INICIO_SESION', 'usuarios', 50, NULL, NULL, '::1', '2026-08-22 20:13:23'),
	(287, 51, 'INICIO_SESION', 'usuarios', 51, NULL, NULL, '::1', '2026-08-22 20:15:39'),
	(288, 52, 'INICIO_SESION', 'usuarios', 52, NULL, NULL, '::1', '2026-08-22 20:16:21'),
	(289, 46, 'INICIO_SESION', 'usuarios', 46, NULL, NULL, '::1', '2026-08-22 20:17:24'),
	(290, 46, 'ACTUALIZAR', 'institucion', 1, '{"ruc": "20601234567", "lema": "Educación con propósito", "logo": null, "ugel": "UGEL 03", "correo": "informes@calebschool.demo", "nombre": "CALEB SCHOOL · DEMOSTRACIÓN", "telefono": "999 888 777", "creado_en": "2026-08-22 19:54:20", "direccion": "Av. Educación 125, Lima", "sitio_web": "https://calebschool.demo", "nombre_corto": "Caleb School", "razon_social": "Caleb School S.A.C.", "actualizado_en": null, "codigo_modular": "1234567", "id_institucion": 1, "actualizado_por": null, "representante_cargo": "Directora general", "representante_nombre": "María Elena Torres"}', '{"ruc": "20601234567", "lema": "Educación con propósito", "logo": "53084a134af733e9b92d65c4975c6b91.jpg", "ugel": "UGEL 03", "correo": "informes@calebschool.demo", "nombre": "CALEB SCHOOL", "telefono": "999 888 777", "creado_en": "2026-08-22 19:54:20", "direccion": "Av. Educación 125, Lima", "sitio_web": "https://calebschool.demo", "nombre_corto": "Caleb School", "razon_social": "Caleb School S.A.C.", "actualizado_en": "2026-08-22 20:17:47", "codigo_modular": "1234567", "id_institucion": 1, "actualizado_por": 46, "representante_cargo": "Directora general", "representante_nombre": "María Elena Torres"}', '::1', '2026-08-22 20:17:47'),
	(291, 46, 'INICIO_SESION', 'usuarios', 46, NULL, NULL, '::1', '2026-08-22 20:18:02'),
	(292, 46, 'INICIO_SESION', 'usuarios', 46, NULL, NULL, '192.168.100.195', '2026-08-22 20:50:21'),
	(293, 46, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "ENTRADA", "persona": 199}', '192.168.100.195', '2026-08-22 20:50:31'),
	(294, 46, 'CREAR', 'personas', 216, NULL, '{"correo": "luna@ejemplo.com", "nombres": "Mayra", "perfiles": ["ADMINISTRATIVO"], "telefono": "999", "apellidos": "Luna", "fotografia": null, "tipo_documento": "DNI", "numero_documento": "777"}', '::1', '2026-08-22 20:59:51'),
	(295, 46, 'CREAR', 'usuarios', 53, NULL, NULL, '::1', '2026-08-22 21:00:36'),
	(296, 53, 'INICIO_SESION', 'usuarios', 53, NULL, NULL, '::1', '2026-08-22 21:04:26'),
	(297, 53, 'INICIO_SESION', 'usuarios', 53, NULL, NULL, '::1', '2026-08-22 21:31:16'),
	(298, 46, 'CREAR', 'personas', 217, NULL, '{"correo": "", "nombres": "Pablo", "perfiles": ["ALUMNO"], "telefono": "", "apellidos": "Lima", "fotografia": null, "tipo_documento": "DNI", "numero_documento": "987"}', '::1', '2026-08-22 22:08:41'),
	(299, 46, 'CREAR', 'personas', 218, NULL, '{"correo": "", "nombres": "Carla", "perfiles": ["APODERADO"], "telefono": "", "apellidos": "Lima", "fotografia": null, "tipo_documento": "DNI", "numero_documento": "9876"}', '::1', '2026-08-22 22:09:59'),
	(300, 46, 'CREAR', 'matriculas', 106, NULL, '{"id_alumno": "217", "csrf_token": "53664113b70bb7949e201b8c3898cdec335e73f9262f34e2e93281643b669235", "id_periodo": "22", "id_seccion": "104", "parentesco": "Apoderado", "id_apoderado": "218", "codigo_alumno": "", "fecha_matricula": "2026-08-22"}', '::1', '2026-08-22 22:12:29'),
	(301, 46, 'EDITAR', 'matriculas', 106, NULL, '{"estado": "ACTIVO", "csrf_token": "53664113b70bb7949e201b8c3898cdec335e73f9262f34e2e93281643b669235", "id_periodo": "22", "id_seccion": "104", "parentesco": "Mamá", "id_apoderado": "218", "id_matricula": "106", "codigo_alumno": "A-2026-0011", "fecha_matricula": "2026-08-22"}', '::1', '2026-08-22 22:12:49'),
	(302, 46, 'GENERAR_QR', 'codigos_qr', 0, NULL, '{"id_persona": 217}', '::1', '2026-08-22 22:13:10'),
	(303, 46, 'GENERAR', 'cuentas_cobrar', NULL, NULL, '{"mes": "2026-08", "alcance": "ALUMNO", "id_alumno": "217", "csrf_token": "53664113b70bb7949e201b8c3898cdec335e73f9262f34e2e93281643b669235", "id_seccion": "", "fecha_vencimiento": "2026-08-26", "id_concepto_cobro": "55", "id_periodo_academico": "22"}', '::1', '2026-08-22 22:26:42'),
	(304, 46, 'AJUSTAR', 'cuentas_cobrar', 126, NULL, '{"mes": "2026-08", "tipo": "DESCUENTO", "motivo": "Precio familiar.", "importe": "50", "id_cuenta": "126", "csrf_token": "53664113b70bb7949e201b8c3898cdec335e73f9262f34e2e93281643b669235"}', '::1', '2026-08-22 22:30:43'),
	(305, 53, 'ABRIR_CAJA', 'aperturas_caja', NULL, NULL, '{"fecha": "2026-08-22", "csrf_token": "a32c4d58e7e7320ab340746a9217608c972a31f0182f122d9578fc1bce4a991e", "observacion": "", "monto_apertura": "0.00"}', '::1', '2026-08-22 22:36:55'),
	(306, 46, 'COBRAR', 'pagos_alumno', 66, NULL, '{"mes": "2026-08", "medios": {"TRANSFERENCIA": {"importe": "250", "referencia": "456"}}, "detalles": {"126": "250"}, "csrf_token": "53664113b70bb7949e201b8c3898cdec335e73f9262f34e2e93281643b669235", "observacion": "Este pago aun no se refleja en la cuenta, se debe de verificar aquí de dos días.", "numero_recibo": "R-2026-000006", "evidencias_pago": {"TRANSFERENCIA": "a2b2e7180bbc2d422d61ad1f029f71a110de178e.jpg"}}', '::1', '2026-08-22 22:45:59'),
	(307, 46, 'INICIO_SESION', 'usuarios', 46, NULL, NULL, '::1', '2026-08-22 23:55:07'),
	(308, 46, 'VERSIONAR_VINCULO', 'vinculos_laborales', 91, '{"tipo": "ADMINISTRATIVO", "cargo": "Auxiliar administrativo", "estado": "ACTIVO", "id_area": 23, "persona": "Alumno Trabajador, Raúl", "id_cargo": 68, "fecha_fin": null, "id_persona": 199, "id_vinculo": 89, "monto_base": "1700.00", "monto_hora": "0.00", "fecha_inicio": "2026-08-01", "monto_sesion": "0.00", "monto_mensual": "1700.00", "motivo_cambio": null, "registrado_en": "2026-08-22 19:54:21", "modalidad_pago": "MENSUAL", "registrado_por": 50, "dias_laborables": "1,2,3,4,5", "id_vinculo_anterior": null}', '{"tipo": "ADMINISTRATIVO", "cargo": "Auxiliar administrativo", "estado": "ACTIVO", "id_area": 23, "persona": "Alumno Trabajador, Raúl", "id_cargo": 68, "fecha_fin": null, "id_persona": 199, "id_vinculo": 91, "monto_base": "1700.00", "monto_hora": "0.00", "fecha_inicio": "2026-08-22", "monto_sesion": "0.00", "monto_mensual": "1700.00", "motivo_cambio": "Se cambio para realizar prueba.", "registrado_en": "2026-08-22 23:57:33", "modalidad_pago": "MENSUAL", "registrado_por": 46, "dias_laborables": "1,2,3,4,5,6,7", "id_vinculo_anterior": 89}', '::1', '2026-08-22 23:57:33'),
	(309, 46, 'REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', NULL, NULL, '{"tipo": "SALIDA", "persona": 199}', '192.168.100.195', '2026-08-22 23:58:05'),
	(310, 46, 'CERRAR_CAJA', 'cierres_caja', NULL, NULL, '{"fecha": "2026-08-22", "csrf_token": "8704a4c236f6e106a0ab454adb7572a859aef3a929350da24c218a3c10fb6d12", "declarados": {"OTRO": "0.00", "PLIN": "0.00", "YAPE": "0.00", "TARJETA": "0.00", "DEPOSITO": "0.00", "EFECTIVO": "0.00", "TRANSFERENCIA": "250.00"}, "id_apertura": "27", "observacion": ""}', '::1', '2026-08-23 10:46:20');

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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.calendario_institucional: ~4 rows (aproximadamente)
INSERT INTO `calendario_institucional` (`id_calendario`, `fecha`, `tipo`, `descripcion`, `estado`, `creado_por`, `creado_en`, `actualizado_por`, `actualizado_en`) VALUES
	(26, '2026-07-28', 'FERIADO', 'Fiestas Patrias', 'ACTIVO', 50, '2026-08-22 19:54:21', NULL, NULL),
	(27, '2026-10-09', 'NO_LABORABLE', 'Jornada institucional', 'ACTIVO', 50, '2026-08-22 19:54:21', NULL, NULL),
	(28, '2026-11-06', 'SUSPENSION', 'Mantenimiento preventivo', 'ACTIVO', 50, '2026-08-22 19:54:21', NULL, NULL),
	(29, '2026-09-18', 'NO_LABORABLE', 'Registro cancelado de demostracion', 'INACTIVO', 50, '2026-08-22 19:54:21', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.carga_academica: ~5 rows (aproximadamente)
INSERT INTO `carga_academica` (`id_carga`, `id_vinculo`, `id_periodo_academico`, `id_seccion`, `id_asignatura`, `dia_semana`, `hora_inicio`, `hora_fin`, `tolerancia_minutos`, `estado`) VALUES
	(35, 80, 22, 105, 40, 1, '08:00:00', '10:00:00', 5, 'ACTIVO'),
	(36, 80, 22, 103, 40, 2, '10:30:00', '12:30:00', 5, 'ACTIVO'),
	(37, 81, 22, 104, 43, 2, '14:00:00', '15:30:00', 10, 'ACTIVO'),
	(38, 83, 22, 103, 39, 3, '08:00:00', '10:00:00', 5, 'ACTIVO'),
	(39, 79, 22, 103, 44, 5, '12:00:00', '13:00:00', 5, 'INACTIVO');

-- Volcando estructura para tabla colegio.cargos
CREATE TABLE IF NOT EXISTS `cargos` (
  `id_cargo` int unsigned NOT NULL AUTO_INCREMENT,
  `id_area` int unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_cargo`),
  KEY `fk_cargo_area` (`id_area`),
  CONSTRAINT `fk_cargo_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cargos: ~9 rows (aproximadamente)
INSERT INTO `cargos` (`id_cargo`, `id_area`, `nombre`, `estado`) VALUES
	(60, 22, 'Docente de primaria', 'ACTIVO'),
	(61, 22, 'Docente de secundaria', 'ACTIVO'),
	(62, 22, 'Tallerista', 'ACTIVO'),
	(63, 24, 'Director', 'ACTIVO'),
	(64, 23, 'Secretaria', 'ACTIVO'),
	(65, 23, 'Cajera', 'ACTIVO'),
	(66, 23, 'Analista de planilla', 'ACTIVO'),
	(67, 23, 'Responsable de asistencia', 'ACTIVO'),
	(68, 23, 'Auxiliar administrativo', 'ACTIVO');

-- Volcando estructura para tabla colegio.categorias_movimiento_financiero
CREATE TABLE IF NOT EXISTS `categorias_movimiento_financiero` (
  `id_categoria` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('INGRESO','EGRESO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `clasificacion` enum('OPERATIVO','FINANCIAMIENTO','CAPITAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPERATIVO',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uq_categoria_movimiento` (`nombre`,`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.categorias_movimiento_financiero: ~13 rows (aproximadamente)
INSERT INTO `categorias_movimiento_financiero` (`id_categoria`, `nombre`, `tipo`, `clasificacion`, `estado`) VALUES
	(61, 'Aporte o inyección de capital', 'INGRESO', 'CAPITAL', 'ACTIVO'),
	(62, 'Préstamo recibido', 'INGRESO', 'FINANCIAMIENTO', 'ACTIVO'),
	(63, 'Otros ingresos', 'INGRESO', 'OPERATIVO', 'ACTIVO'),
	(64, 'Compras', 'EGRESO', 'OPERATIVO', 'ACTIVO'),
	(65, 'Alquiler', 'EGRESO', 'OPERATIVO', 'ACTIVO'),
	(66, 'Servicios básicos', 'EGRESO', 'OPERATIVO', 'ACTIVO'),
	(67, 'Pago de préstamo', 'EGRESO', 'FINANCIAMIENTO', 'ACTIVO');

-- Volcando estructura para tabla colegio.cierres_asistencia
CREATE TABLE IF NOT EXISTS `cierres_asistencia` (
  `id_cierre` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `alcance` enum('ALUMNOS','PERSONAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('MANUAL','AUTOMATICO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MANUAL',
  `ejecutado_por` bigint unsigned DEFAULT NULL,
  `proceso` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faltas_generadas` int unsigned NOT NULL DEFAULT '0',
  `incompletas_generadas` int unsigned NOT NULL DEFAULT '0',
  `detalle` json DEFAULT NULL,
  `ejecutado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cierre`),
  UNIQUE KEY `uq_cierre_asistencia_fecha_alcance` (`fecha`,`alcance`),
  KEY `ix_cierre_asistencia_ejecucion` (`ejecutado_en`),
  KEY `fk_cierre_asistencia_usuario` (`ejecutado_por`),
  CONSTRAINT `fk_cierre_asistencia_usuario` FOREIGN KEY (`ejecutado_por`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cierres_asistencia: ~2 rows (aproximadamente)
INSERT INTO `cierres_asistencia` (`id_cierre`, `fecha`, `alcance`, `tipo`, `ejecutado_por`, `proceso`, `faltas_generadas`, `incompletas_generadas`, `detalle`, `ejecutado_en`) VALUES
	(5, '2026-08-22', 'ALUMNOS', 'MANUAL', 48, 'usuario', 1, 0, '{"observacion": "Cierre manual de demostración", "faltas_generadas": 1}', '2026-08-22 23:00:00'),
	(6, '2026-08-21', 'PERSONAL', 'AUTOMATICO', NULL, 'tarea_programada', 0, 1, '{"incompletas": 1, "faltas_sesion": 0, "faltas_jornada": 0}', '2026-08-21 23:00:00'),
	(7, '2026-08-22', 'PERSONAL', 'MANUAL', 46, NULL, 0, 0, '{"faltas_sesion": 0, "faltas_jornada": 0}', '2026-08-22 23:58:27');

-- Volcando estructura para tabla colegio.cierres_caja
CREATE TABLE IF NOT EXISTS `cierres_caja` (
  `id_cierre` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `efectivo_sistema` decimal(12,2) NOT NULL DEFAULT '0.00',
  `efectivo_declarado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `otros_sistema` decimal(12,2) NOT NULL DEFAULT '0.00',
  `otros_declarado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado_revision` enum('SIN_DIFERENCIA','PENDIENTE','RECTIFICADO','JUSTIFICADO','REGULARIZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SIN_DIFERENCIA',
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cerrado_por` bigint unsigned DEFAULT NULL,
  `cerrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_apertura` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id_cierre`),
  UNIQUE KEY `uq_cierre_apertura` (`id_apertura`),
  KEY `ix_cierre_caja_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cierres_caja: ~3 rows (aproximadamente)
INSERT INTO `cierres_caja` (`id_cierre`, `fecha`, `efectivo_sistema`, `efectivo_declarado`, `otros_sistema`, `otros_declarado`, `diferencia`, `estado_revision`, `observacion`, `cerrado_por`, `cerrado_en`, `id_apertura`) VALUES
	(24, '2026-08-22', 100.00, 100.00, 920.00, 920.00, 0.00, 'RECTIFICADO', 'Turno conciliado', 49, '2026-08-22 12:30:00', 24),
	(25, '2026-08-22', 250.00, 245.00, 80.00, 80.00, -5.00, 'PENDIENTE', 'Diferencia de caja demostrativa', 49, '2026-08-22 17:45:00', 25),
	(26, '2026-08-23', 250.00, 250.00, -1480.00, -1480.00, 0.00, 'SIN_DIFERENCIA', NULL, 18, '2026-08-22 20:03:38', 26),
	(27, '2026-08-22', 0.00, 0.00, 250.00, 250.00, 0.00, 'SIN_DIFERENCIA', NULL, 46, '2026-08-23 10:46:20', 27);

-- Volcando estructura para tabla colegio.cierres_caja_medios
CREATE TABLE IF NOT EXISTS `cierres_caja_medios` (
  `id_cierre_medio` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_cierre` bigint unsigned NOT NULL,
  `medio` enum('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `importe_sistema` decimal(12,2) NOT NULL DEFAULT '0.00',
  `importe_declarado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_cierre_medio`),
  UNIQUE KEY `uq_cierre_caja_medio` (`id_cierre`,`medio`),
  CONSTRAINT `fk_cierre_medio_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `cierres_caja` (`id_cierre`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cierres_caja_medios: ~13 rows (aproximadamente)
INSERT INTO `cierres_caja_medios` (`id_cierre_medio`, `id_cierre`, `medio`, `importe_sistema`, `importe_declarado`, `diferencia`) VALUES
	(28, 24, 'EFECTIVO', 100.00, 100.00, 0.00),
	(29, 24, 'TRANSFERENCIA', 920.00, 920.00, 0.00),
	(30, 25, 'EFECTIVO', 250.00, 245.00, -5.00),
	(31, 25, 'YAPE', 80.00, 80.00, 0.00),
	(32, 26, 'EFECTIVO', 250.00, 250.00, 0.00),
	(33, 26, 'YAPE', -380.00, -380.00, 0.00),
	(34, 26, 'PLIN', 0.00, 0.00, 0.00),
	(35, 26, 'TRANSFERENCIA', -1100.00, -1100.00, 0.00),
	(36, 26, 'TARJETA', 0.00, 0.00, 0.00),
	(37, 26, 'DEPOSITO', 0.00, 0.00, 0.00),
	(38, 26, 'OTRO', 0.00, 0.00, 0.00),
	(39, 27, 'EFECTIVO', 0.00, 0.00, 0.00),
	(40, 27, 'YAPE', 0.00, 0.00, 0.00),
	(41, 27, 'PLIN', 0.00, 0.00, 0.00),
	(42, 27, 'TRANSFERENCIA', 250.00, 250.00, 0.00),
	(43, 27, 'TARJETA', 0.00, 0.00, 0.00),
	(44, 27, 'DEPOSITO', 0.00, 0.00, 0.00),
	(45, 27, 'OTRO', 0.00, 0.00, 0.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.codigos_qr: ~16 rows (aproximadamente)
INSERT INTO `codigos_qr` (`id_qr`, `id_persona`, `token_hash`, `archivo_qr`, `version`, `estado`, `emitido_en`, `vence_en`, `revocado_en`) VALUES
	(37, 188, '3e65ec48477e00dc818c3ea2af78ee66ed76a3b397169a2eb5cd5b6c7366ecf2', 'demo_qr_revocado.png', 1, 'REVOCADO', '2026-08-21 08:00:00', NULL, '2026-08-22 09:00:00'),
	(38, 215, '5d2a92e15d1c92a874345ca357956eb3494a200d6d1a4ebaa0ae9a85610ba657', 'demo_qr_vencido.png', 1, 'VENCIDO', '2025-01-01 08:00:00', '2025-12-31 23:59:59', NULL),
	(39, 188, '05462f31cf659e16815e8da9d878a7655998827e75263300dbab3089238a45d6', 'qr_188_bafb7f413a9283a4.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(40, 189, 'e7e80df3b8c7d92a9260e21d0d9e10a10355134caa4de0654f300f59d9ea9b3d', 'qr_189_11c8fa6be31bc148.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(41, 190, 'ec5132b2a2411d0afb91135f31ecde8ad075b35bc032637c45ce3a972e9c0aa8', 'qr_190_e2c7b292d0b37fe8.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(42, 191, '2a87c23316c45c590644caf25234f5e948425c7f18eef381168f7a04d4fcfd47', 'qr_191_34c7fc0b5041573d.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(43, 192, 'ac7a30047bfd653825f58d9dcb2e6111a43b7102d2d9a55996028db120e0f7ba', 'qr_192_4e1d96d92dcc6bd6.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(44, 206, '5ecb0d4678603cae91854aac5f17bac14a29a703d9efd4ebd61c0a43450b1b53', 'qr_206_28fe6c6c93d6fe6b.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(45, 207, 'b6ff9e7dffbe31851f8874bf15af8a3681f3965c45ec6cbc29a45d0f452c9955', 'qr_207_9049b07731f9bd68.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(46, 209, '234eba107cf4d1ba50fba9647d1b687759445c3821750e491268537bc9cf6d2e', 'qr_209_57bee5ce2a0bceb4.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(47, 210, 'be28d41eac6722d752ad84d6d7d32c3e5d3a8d643400c13a604349c84d84b1db', 'qr_210_a78c210ca2bad4ac.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(48, 211, '80d637885c68eb1e7b4c94ba9448d0f0a20dcfd037fc0f6b007ea6c312d5b561', 'qr_211_4b5ae7d7be7f5348.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(49, 212, 'dab81fc86522ec06ffaf86c550cd1303f5b445d3addfaddaa3f58da566b2cdde', 'qr_212_2d662896cbea80c5.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(50, 213, '50b4f6b1d688a04a40a5a1395632506c8edf6993cdba01ac297c6d6c2935e566', 'qr_213_7d7b562e86bb2f82.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(51, 214, '5a91790ce75756167231a63621a25276b0d9fcfe63cc853d10502a232e0689b8', 'qr_214_9a3c2cc69cb66f14.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(52, 199, 'f172ae609c421d3f6a762bc2e5a99c950f94d1fba7ad3dae59bda9a79ccbe7ae', 'qr_199_5f193b28470ca497.png', 1, 'ACTIVO', '2026-08-22 19:54:21', NULL, NULL),
	(53, 217, 'b01477de104dfa3b4fdb18c7a5ea2a9c43acdcca6d2ae191efd3f8d9f62eda16', 'qr_217_f1252e745651fea6.png', 1, 'ACTIVO', '2026-08-22 22:13:10', NULL, NULL);

-- Volcando estructura para tabla colegio.conceptos_cobro
CREATE TABLE IF NOT EXISTS `conceptos_cobro` (
  `id_concepto_cobro` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('MATRICULA','MENSUALIDAD','TALLER','CUOTA','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `admite_parcial` tinyint(1) NOT NULL DEFAULT '1',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_concepto_cobro`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.conceptos_cobro: ~8 rows (aproximadamente)
INSERT INTO `conceptos_cobro` (`id_concepto_cobro`, `nombre`, `tipo`, `admite_parcial`, `estado`) VALUES
	(55, 'Matricula', 'MATRICULA', 0, 'ACTIVO'),
	(56, 'Pension mensual', 'MENSUALIDAD', 1, 'ACTIVO'),
	(57, 'Taller extracurricular', 'TALLER', 1, 'ACTIVO'),
	(58, 'Movilidad escolar', 'OTRO', 1, 'ACTIVO'),
	(59, 'Material educativo', 'OTRO', 0, 'ACTIVO'),
	(60, 'Fotocheck', 'OTRO', 0, 'ACTIVO'),
	(61, 'Cuota extraordinaria', 'CUOTA', 1, 'ACTIVO'),
	(62, 'Concepto antiguo', 'OTRO', 1, 'INACTIVO'),
	(63, 'Matricula 2026-II', 'MATRICULA', 0, 'ACTIVO');

-- Volcando estructura para tabla colegio.conceptos_pago
CREATE TABLE IF NOT EXISTS `conceptos_pago` (
  `id_concepto_pago` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `naturaleza` enum('INGRESO','DESCUENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('BASE','BONIFICACION','TARDANZA','FALTA','HORA','MANUAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_concepto_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.conceptos_pago: ~10 rows (aproximadamente)
INSERT INTO `conceptos_pago` (`id_concepto_pago`, `nombre`, `naturaleza`, `tipo`, `estado`) VALUES
	(64, 'Remuneracion mensual', 'INGRESO', 'BASE', 'ACTIVO'),
	(65, 'Pago por horas', 'INGRESO', 'HORA', 'ACTIVO'),
	(66, 'Pago por sesiones', 'INGRESO', 'HORA', 'ACTIVO'),
	(67, 'Bono institucional', 'INGRESO', 'BONIFICACION', 'ACTIVO'),
	(68, 'Descuento por tardanza', 'DESCUENTO', 'TARDANZA', 'ACTIVO'),
	(69, 'Descuento por falta', 'DESCUENTO', 'FALTA', 'ACTIVO'),
	(70, 'Descuento por salida anticipada', 'DESCUENTO', 'MANUAL', 'ACTIVO'),
	(71, 'Ajuste manual ingreso', 'INGRESO', 'MANUAL', 'ACTIVO'),
	(72, 'Ajuste manual descuento', 'DESCUENTO', 'MANUAL', 'ACTIVO'),
	(73, 'Concepto de planilla antiguo', 'INGRESO', 'MANUAL', 'INACTIVO'),
	(74, 'Salida anticipada', 'DESCUENTO', 'MANUAL', 'ACTIVO'),
	(75, 'Falta injustificada', 'DESCUENTO', 'FALTA', 'ACTIVO'),
	(76, 'Minuto de tardanza', 'DESCUENTO', 'TARDANZA', 'ACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.cuentas_cobrar: ~15 rows (aproximadamente)
INSERT INTO `cuentas_cobrar` (`id_cuenta`, `id_matricula`, `id_concepto_cobro`, `periodo_cobro`, `fecha_vencimiento`, `importe_original`, `descuento`, `recargo`, `saldo`, `estado`) VALUES
	(111, 92, 55, '2026-03-01', '2026-03-10', 500.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(112, 92, 56, '2026-08-01', '2026-08-28', 450.00, 20.00, 0.00, 330.00, 'PARCIAL'),
	(113, 97, 56, '2026-08-01', '2026-08-28', 420.00, 0.00, 0.00, 0.00, 'PAGADA'),
	(114, 96, 57, '2026-08-01', '2026-08-30', 160.00, 0.00, 0.00, 80.00, 'PARCIAL'),
	(115, 93, 56, '2026-08-01', '2026-08-30', 450.00, 0.00, 0.00, 450.00, 'PENDIENTE'),
	(116, 93, 59, '2026-03-01', '2026-08-25', 180.00, 0.00, 0.00, 180.00, 'PENDIENTE'),
	(117, 95, 56, '2026-08-01', '2026-08-27', 420.00, 100.00, 0.00, 320.00, 'PENDIENTE'),
	(118, 95, 61, '2026-07-01', '2026-08-08', 150.00, 0.00, 15.00, 165.00, 'VENCIDA'),
	(119, 97, 56, '2026-06-01', '2026-07-09', 420.00, 0.00, 0.00, 420.00, 'VENCIDA'),
	(120, 102, 56, '2025-11-01', '2026-05-25', 380.00, 0.00, 0.00, 380.00, 'VENCIDA'),
	(121, 104, 56, '2025-12-01', '2025-12-10', 350.00, 0.00, 30.00, 280.00, 'VENCIDA'),
	(122, 98, 56, '2026-09-01', '2026-09-10', 420.00, 0.00, 0.00, 420.00, 'PENDIENTE'),
	(123, 99, 58, '2026-08-01', '2026-08-28', 120.00, 0.00, 0.00, 120.00, 'ANULADA'),
	(124, 100, 56, '2026-05-01', '2026-06-14', 450.00, 0.00, 0.00, 450.00, 'VENCIDA'),
	(125, 98, 59, '2026-03-01', '2026-03-15', 200.00, 200.00, 0.00, 0.00, 'PAGADA'),
	(126, 106, 55, '2026-08-01', '2026-08-26', 300.00, 50.00, 0.00, 0.00, 'PAGADA');

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.dispositivos_escaneo: ~3 rows (aproximadamente)
INSERT INTO `dispositivos_escaneo` (`id_dispositivo`, `nombre`, `codigo`, `token_hash`, `punto_control`, `estado`, `ultimo_sync_en`) VALUES
	(20, 'Entrada principal', 'entrada-principal', '911356bcb4ff48d9de5556289aeb3a6ec770334b0b3d8b2e3d82e291b998e2e4', 'Puerta principal', 'ACTIVO', '2026-08-22 23:58:05'),
	(21, 'Movil coordinacion', 'movil-coordinacion', '1a980629b883cd2ed9319046bf8dbcef762ed845db1187a810ac03a86a4e9bbc', 'Patio', 'ACTIVO', '2026-08-22 19:54:21'),
	(22, 'Lector retirado', 'lector-retirado', 'de2cf9264a88c66ab6e3d4f1a44492971fae4d2691bf9f15425a75b3c40869df', 'Puerta posterior', 'INACTIVO', '2026-08-22 19:54:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.grados: ~14 rows (aproximadamente)
INSERT INTO `grados` (`id_grado`, `id_nivel`, `nombre`, `orden`, `estado`) VALUES
	(95, 22, '3 años', 1, 'ACTIVO'),
	(96, 22, '4 años', 2, 'ACTIVO'),
	(97, 22, '5 años', 3, 'ACTIVO'),
	(98, 23, '1.° grado', 1, 'ACTIVO'),
	(99, 23, '2.° grado', 2, 'ACTIVO'),
	(100, 23, '3.° grado', 3, 'ACTIVO'),
	(101, 23, '4.° grado', 4, 'ACTIVO'),
	(102, 23, '5.° grado', 5, 'ACTIVO'),
	(103, 23, '6.° grado', 6, 'ACTIVO'),
	(104, 24, '1.° año', 1, 'ACTIVO'),
	(105, 24, '2.° año', 2, 'ACTIVO'),
	(106, 24, '3.° año', 3, 'ACTIVO'),
	(107, 24, '4.° año', 4, 'ACTIVO'),
	(108, 24, '5.° año', 5, 'ACTIVO');

-- Volcando estructura para tabla colegio.incidencias_asistencia
CREATE TABLE IF NOT EXISTS `incidencias_asistencia` (
  `id_incidencia` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_asistencia` bigint unsigned NOT NULL,
  `tipo` enum('JUSTIFICACION_FALTA','JUSTIFICACION_TARDANZA','PERMISO','LICENCIA','OBSERVACION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `registrado_por` bigint unsigned DEFAULT NULL,
  `resuelto_por` bigint unsigned DEFAULT NULL,
  `resolucion_observacion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resuelto_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_incidencia`),
  KEY `fk_incidencia_asistencia` (`id_asistencia`),
  CONSTRAINT `fk_incidencia_asistencia` FOREIGN KEY (`id_asistencia`) REFERENCES `asistencias_diarias` (`id_asistencia`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.incidencias_asistencia: ~9 rows (aproximadamente)
INSERT INTO `incidencias_asistencia` (`id_incidencia`, `id_asistencia`, `tipo`, `motivo`, `estado`, `registrado_por`, `resuelto_por`, `resolucion_observacion`, `creado_en`, `resuelto_en`) VALUES
	(35, 156, 'JUSTIFICACION_FALTA', 'Presentara certificado medico', 'PENDIENTE', 51, NULL, NULL, '2026-08-22 19:54:21', NULL),
	(36, 155, 'JUSTIFICACION_TARDANZA', 'Transporte escolar demorado', 'APROBADA', 51, 48, 'Sustento verificado por el responsable de asistencia.', '2026-08-23 09:00:00', '2026-08-23 09:20:00'),
	(37, 169, 'LICENCIA', 'Licencia personal documentada', 'APROBADA', 48, 47, 'Licencia autorizada por Dirección.', '2026-08-22 16:00:00', '2026-08-23 07:30:00'),
	(38, 168, 'OBSERVACION', 'Solicitud sin sustento', 'RECHAZADA', 50, 48, 'Solicitud rechazada por falta de sustento.', '2026-08-23 10:00:00', '2026-08-23 11:00:00');

-- Volcando estructura para tabla colegio.institucion
CREATE TABLE IF NOT EXISTS `institucion` (
  `id_institucion` tinyint unsigned NOT NULL DEFAULT '1',
  `nombre` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_corto` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razon_social` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lema` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_modular` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ugel` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `representante_nombre` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `representante_cargo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_por` bigint unsigned DEFAULT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_institucion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.institucion: ~1 rows (aproximadamente)
INSERT INTO `institucion` (`id_institucion`, `nombre`, `nombre_corto`, `razon_social`, `ruc`, `direccion`, `telefono`, `correo`, `sitio_web`, `lema`, `codigo_modular`, `ugel`, `representante_nombre`, `representante_cargo`, `logo`, `creado_en`, `actualizado_por`, `actualizado_en`) VALUES
	(1, 'CALEB SCHOOL', 'Caleb School', 'Caleb School S.A.C.', '20601234567', 'Av. Educación 125, Lima', '999 888 777', 'informes@calebschool.demo', 'https://calebschool.demo', 'Educación con propósito', '1234567', 'UGEL 03', 'María Elena Torres', 'Directora general', '53084a134af733e9b92d65c4975c6b91.jpg', '2026-08-22 19:54:20', 46, '2026-08-22 20:17:47');

-- Volcando estructura para tabla colegio.jornadas
CREATE TABLE IF NOT EXISTS `jornadas` (
  `id_jornada` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aplica_a` enum('ALUMNO','DOCENTE','ADMINISTRATIVO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `tolerancia_minutos` smallint unsigned NOT NULL DEFAULT '0',
  `requiere_salida` tinyint(1) NOT NULL DEFAULT '1',
  `es_predeterminada` tinyint(1) NOT NULL DEFAULT '0',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_jornada`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.jornadas: ~5 rows (aproximadamente)
INSERT INTO `jornadas` (`id_jornada`, `nombre`, `aplica_a`, `hora_entrada`, `hora_salida`, `tolerancia_minutos`, `requiere_salida`, `es_predeterminada`, `estado`) VALUES
	(35, 'Jornada escolar general', 'ALUMNO', '08:00:00', '15:00:00', 10, 0, 1, 'ACTIVO'),
	(36, 'Docentes tiempo completo', 'DOCENTE', '07:30:00', '15:30:00', 5, 1, 0, 'ACTIVO'),
	(37, 'Administrativos', 'ADMINISTRATIVO', '08:00:00', '17:00:00', 10, 1, 0, 'ACTIVO'),
	(38, 'Medio tiempo', 'ADMINISTRATIVO', '08:00:00', '13:00:00', 5, 1, 0, 'ACTIVO'),
	(39, 'Jornada anterior', 'ADMINISTRATIVO', '09:00:00', '18:00:00', 5, 1, 0, 'INACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.marcaciones_asistencia: ~23 rows (aproximadamente)
INSERT INTO `marcaciones_asistencia` (`id_marcacion`, `uuid_cliente`, `id_persona`, `id_dispositivo`, `id_usuario`, `fecha_hora_dispositivo`, `fecha_hora_servidor`, `tipo`, `metodo`, `estado_validacion`, `observacion`) VALUES
	(151, '10000000-0000-4000-8000-000000000001', 188, 20, NULL, '2026-08-23 07:56:00', '2026-08-23 07:56:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(152, '10000000-0000-4000-8000-000000000002', 189, 20, NULL, '2026-08-23 08:17:00', '2026-08-23 08:17:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(153, '10000000-0000-4000-8000-000000000003', 196, 20, NULL, '2026-08-23 07:58:00', '2026-08-23 07:58:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(154, '10000000-0000-4000-8000-000000000004', 196, 20, NULL, '2026-08-23 12:10:00', '2026-08-23 12:10:00', 'SALIDA', 'QR', 'VALIDA', NULL),
	(155, '10000000-0000-4000-8000-000000000005', 198, NULL, 48, '2026-08-23 08:03:00', '2026-08-23 08:03:00', 'ENTRADA', 'MANUAL', 'VALIDA', 'Registro manual: PUNTUAL'),
	(156, '10000000-0000-4000-8000-000000000010', 206, 20, NULL, '2026-08-23 07:28:00', '2026-08-23 07:28:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(157, '10000000-0000-4000-8000-000000000011', 206, 20, NULL, '2026-08-23 15:31:00', '2026-08-23 15:31:00', 'SALIDA', 'QR', 'VALIDA', NULL),
	(158, '10000000-0000-4000-8000-000000000012', 210, 20, NULL, '2026-08-23 08:18:00', '2026-08-23 08:18:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(159, '10000000-0000-4000-8000-000000000013', 210, 20, NULL, '2026-08-23 17:02:00', '2026-08-23 17:02:00', 'SALIDA', 'QR', 'VALIDA', NULL),
	(160, '10000000-0000-4000-8000-000000000014', 211, 20, NULL, '2026-08-23 07:59:00', '2026-08-23 07:59:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(161, '10000000-0000-4000-8000-000000000015', 199, NULL, 48, '2026-08-23 08:03:00', '2026-08-23 08:03:00', 'ENTRADA', 'MANUAL', 'VALIDA', 'Ingreso registrado por responsable'),
	(162, '10000000-0000-4000-8000-000000000016', 199, NULL, 48, '2026-08-23 13:01:00', '2026-08-23 13:01:00', 'SALIDA', 'MANUAL', 'VALIDA', 'Salida registrada por responsable'),
	(163, '10000000-0000-4000-8000-000000000020', 207, 20, NULL, '2026-08-21 07:59:00', '2026-08-21 07:59:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(164, '10000000-0000-4000-8000-000000000021', 207, 20, NULL, '2026-08-21 10:01:00', '2026-08-21 10:01:00', 'SALIDA', 'QR', 'VALIDA', NULL),
	(165, '10000000-0000-4000-8000-000000000022', 207, 20, NULL, '2026-08-22 10:42:00', '2026-08-22 10:42:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(166, '10000000-0000-4000-8000-000000000023', 207, 20, NULL, '2026-08-22 12:30:00', '2026-08-22 12:30:00', 'SALIDA', 'QR', 'VALIDA', NULL),
	(167, '10000000-0000-4000-8000-000000000024', 208, 20, NULL, '2026-08-22 14:02:00', '2026-08-22 14:02:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(168, '10000000-0000-4000-8000-000000000025', 208, 20, NULL, '2026-08-22 15:00:00', '2026-08-22 15:00:00', 'SALIDA', 'QR', 'VALIDA', NULL),
	(169, '10000000-0000-4000-8000-000000000026', 209, 20, NULL, '2026-08-23 07:58:00', '2026-08-23 07:58:00', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(170, '10000000-0000-4000-8000-000000000027', 209, 20, NULL, '2026-08-23 10:03:00', '2026-08-23 10:03:00', 'SALIDA', 'QR', 'VALIDA', NULL),
	(171, '10000000-0000-4000-8000-000000000030', 188, 20, NULL, '2026-08-23 07:56:03', '2026-08-23 07:56:03', 'ENTRADA', 'QR', 'DUPLICADA', 'Lectura repetida en tres segundos'),
	(172, '10000000-0000-4000-8000-000000000031', 197, NULL, 48, '2026-08-23 08:05:00', '2026-08-23 08:05:00', 'ENTRADA', 'MANUAL', 'ANULADA', 'Marcacion corregida'),
	(173, '7da20917-6043-478c-ab0a-fc4bc2e2e0eb', 199, 20, 46, '2026-08-22 20:50:31', '2026-08-22 20:50:31', 'ENTRADA', 'QR', 'VALIDA', NULL),
	(174, '11a6c32d-c968-458c-9a52-cfd5c8241a07', 199, 20, 46, '2026-08-22 23:58:05', '2026-08-22 23:58:05', 'SALIDA', 'QR', 'VALIDA', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.matriculas: ~14 rows (aproximadamente)
INSERT INTO `matriculas` (`id_matricula`, `id_persona`, `id_periodo_academico`, `id_seccion`, `codigo_alumno`, `fecha_matricula`, `estado`) VALUES
	(92, 188, 22, 103, 'A-2026-0001', '2026-02-10', 'ACTIVO'),
	(93, 189, 22, 103, 'A-2026-0002', '2026-02-10', 'ACTIVO'),
	(94, 190, 22, 103, 'A-2026-0003', '2026-02-11', 'ACTIVO'),
	(95, 191, 22, 105, 'A-2026-0004', '2026-02-12', 'ACTIVO'),
	(96, 192, 22, 105, 'A-2026-0005', '2026-02-12', 'ACTIVO'),
	(97, 196, 22, 105, 'A-2026-0006', '2026-02-13', 'ACTIVO'),
	(98, 197, 22, 105, 'A-2026-0007', '2026-02-13', 'ACTIVO'),
	(99, 198, 22, 104, 'A-2026-0008', '2026-02-14', 'ACTIVO'),
	(100, 193, 22, 104, 'A-2026-0009', '2026-02-15', 'RETIRADO'),
	(101, 194, 22, 104, 'A-2026-0010', '2026-02-15', 'ANULADO'),
	(102, 197, 21, 103, 'A-2025-0001', '2025-02-12', 'ACTIVO'),
	(103, 196, 21, 105, 'A-2025-0002', '2025-02-12', 'ACTIVO'),
	(104, 190, 21, 104, 'A-2025-0003', '2025-02-13', 'ACTIVO'),
	(105, 195, 21, 114, 'A-2025-0004', '2025-02-13', 'EGRESADO'),
	(106, 217, 22, 104, 'A-2026-0011', '2026-08-22', 'ACTIVO');

-- Volcando estructura para tabla colegio.movimientos_financieros
CREATE TABLE IF NOT EXISTS `movimientos_financieros` (
  `id_movimiento` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('INGRESO','EGRESO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_categoria` int unsigned NOT NULL,
  `fecha_movimiento` datetime NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `medio` enum('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tercero` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documento` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `evidencia_archivo` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_apertura` bigint unsigned DEFAULT NULL,
  `estado` enum('REGISTRADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGISTRADO',
  `registrado_por` bigint unsigned DEFAULT NULL,
  `registrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `anulado_por` bigint unsigned DEFAULT NULL,
  `anulado_en` datetime DEFAULT NULL,
  `motivo_anulacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_movimiento`),
  UNIQUE KEY `uq_movimiento_codigo` (`codigo`),
  KEY `ix_movimiento_fecha` (`fecha_movimiento`,`estado`),
  KEY `ix_movimiento_tipo` (`tipo`,`id_categoria`),
  KEY `ix_movimiento_apertura` (`id_apertura`),
  KEY `fk_movimiento_categoria` (`id_categoria`),
  CONSTRAINT `fk_movimiento_apertura` FOREIGN KEY (`id_apertura`) REFERENCES `aperturas_caja` (`id_apertura`),
  CONSTRAINT `fk_movimiento_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_movimiento_financiero` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.movimientos_financieros: ~0 rows (aproximadamente)
INSERT INTO `movimientos_financieros` (`id_movimiento`, `codigo`, `tipo`, `id_categoria`, `fecha_movimiento`, `monto`, `medio`, `tercero`, `documento`, `referencia`, `descripcion`, `evidencia_archivo`, `id_apertura`, `estado`, `registrado_por`, `registrado_en`, `anulado_por`, `anulado_en`, `motivo_anulacion`) VALUES
	(17, 'MF-2026-000001', 'INGRESO', 61, '2026-08-01 09:00:00', 5000.00, 'TRANSFERENCIA', 'Socios del colegio', NULL, 'APORTE-2026', 'Capital de trabajo para el período', NULL, NULL, 'REGISTRADO', 49, '2026-08-22 19:54:21', NULL, NULL, NULL),
	(18, 'MF-2026-000002', 'INGRESO', 62, '2026-08-01 10:00:00', 3000.00, 'DEPOSITO', 'Banco de demostración', NULL, 'PREST-2026', 'Préstamo recibido para equipamiento', NULL, NULL, 'REGISTRADO', 49, '2026-08-22 19:54:21', NULL, NULL, NULL),
	(19, 'MF-2026-000003', 'EGRESO', 65, '2026-08-23 09:20:00', 1200.00, 'TRANSFERENCIA', 'Arrendador de demostración', NULL, 'OP-ALQ-001', 'Pago del alquiler institucional', NULL, 26, 'REGISTRADO', 49, '2026-08-22 19:54:21', NULL, NULL, NULL),
	(20, 'MF-2026-000004', 'EGRESO', 64, '2026-08-23 10:30:00', 250.00, 'EFECTIVO', 'Librería escolar', NULL, 'B001-125', 'Compra de útiles administrativos', NULL, 26, 'REGISTRADO', 49, '2026-08-22 19:54:21', NULL, NULL, NULL),
	(21, 'MF-2026-000005', 'EGRESO', 66, '2026-08-23 11:00:00', 380.00, 'YAPE', 'Empresa de servicios', NULL, 'OP-SERV-001', 'Pago de servicios básicos', NULL, 26, 'REGISTRADO', 49, '2026-08-22 19:54:21', NULL, NULL, NULL);

-- Volcando estructura para tabla colegio.niveles
CREATE TABLE IF NOT EXISTS `niveles` (
  `id_nivel` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint unsigned NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.niveles: ~3 rows (aproximadamente)
INSERT INTO `niveles` (`id_nivel`, `nombre`, `orden`, `estado`) VALUES
	(22, 'Inicial', 1, 'ACTIVO'),
	(23, 'Primaria', 2, 'ACTIVO'),
	(24, 'Secundaria', 3, 'ACTIVO');

-- Volcando estructura para tabla colegio.pagos_alumno
CREATE TABLE IF NOT EXISTS `pagos_alumno` (
  `id_pago` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero_recibo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_apoderado` bigint unsigned DEFAULT NULL,
  `fecha_pago` datetime NOT NULL,
  `medio_pago` enum('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO','MIXTO') COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.pagos_alumno: ~13 rows (aproximadamente)
INSERT INTO `pagos_alumno` (`id_pago`, `numero_recibo`, `id_apoderado`, `fecha_pago`, `medio_pago`, `importe_total`, `estado`, `registrado_por`, `observacion`, `motivo_anulacion`, `anulado_por`, `anulado_en`, `id_apertura`) VALUES
	(61, 'R-2026-000001', 200, '2026-08-22 09:15:00', 'TRANSFERENCIA', 920.00, 'REGISTRADO', 49, 'Pago de demostracion', NULL, NULL, NULL, 24),
	(62, 'R-2026-000002', 200, '2026-08-22 14:20:00', 'EFECTIVO', 100.00, 'REGISTRADO', 49, 'Pago de demostracion', NULL, NULL, NULL, 25),
	(63, 'R-2026-000003', 203, '2026-08-22 15:10:00', 'YAPE', 80.00, 'REGISTRADO', 49, 'Pago de demostracion', NULL, NULL, NULL, 25),
	(64, 'R-2026-000004', 201, '2026-08-23 10:05:00', 'TRANSFERENCIA', 100.00, 'REGISTRADO', 49, 'Pago de demostracion', NULL, NULL, NULL, 26),
	(65, 'R-2026-000005', 200, '2026-08-23 11:15:00', 'PLIN', 180.00, 'ANULADO', 49, 'Pago de demostracion', 'Pago registrado en una cuenta equivocada', 47, '2026-08-23 11:15:00', 26),
	(66, 'R-2026-000006', 218, '2026-08-22 22:45:59', 'TRANSFERENCIA', 250.00, 'REGISTRADO', 46, 'Este pago aun no se refleja en la cuenta, se debe de verificar aquí de dos días.', NULL, NULL, NULL, 27);

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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.pagos_alumno_detalles: ~11 rows (aproximadamente)
INSERT INTO `pagos_alumno_detalles` (`id_pago_detalle`, `id_pago`, `id_cuenta`, `importe`) VALUES
	(71, 61, 111, 500.00),
	(72, 61, 113, 420.00),
	(73, 62, 112, 100.00),
	(74, 63, 114, 80.00),
	(75, 64, 121, 100.00),
	(76, 65, 116, 180.00),
	(77, 66, 126, 250.00);

-- Volcando estructura para tabla colegio.pagos_alumno_medios
CREATE TABLE IF NOT EXISTS `pagos_alumno_medios` (
  `id_pago_medio` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pago` bigint unsigned NOT NULL,
  `medio` enum('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `importe_aplicado` decimal(12,2) NOT NULL,
  `importe_recibido` decimal(12,2) NOT NULL,
  `vuelto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `referencia` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidencia_archivo` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_pago_medio`),
  KEY `ix_pago_medio_pago` (`id_pago`),
  KEY `ix_pago_medio_tipo` (`medio`),
  CONSTRAINT `fk_pago_medio_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos_alumno` (`id_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.pagos_alumno_medios: ~16 rows (aproximadamente)
INSERT INTO `pagos_alumno_medios` (`id_pago_medio`, `id_pago`, `medio`, `importe_aplicado`, `importe_recibido`, `vuelto`, `referencia`, `evidencia_archivo`) VALUES
	(34, 61, 'TRANSFERENCIA', 920.00, 920.00, 0.00, 'OP-DEMO-00061', NULL),
	(35, 62, 'EFECTIVO', 100.00, 100.00, 0.00, NULL, NULL),
	(36, 63, 'YAPE', 80.00, 80.00, 0.00, 'OP-DEMO-00063', NULL),
	(37, 64, 'TRANSFERENCIA', 100.00, 100.00, 0.00, 'OP-DEMO-00064', NULL),
	(38, 65, 'PLIN', 180.00, 180.00, 0.00, 'OP-DEMO-00065', NULL),
	(39, 66, 'TRANSFERENCIA', 250.00, 250.00, 0.00, '456', 'a2b2e7180bbc2d422d61ad1f029f71a110de178e.jpg');

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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.pagos_planilla: ~10 rows (aproximadamente)
INSERT INTO `pagos_planilla` (`id_pago_planilla`, `id_planilla_detalle`, `importe`, `medio_pago`, `referencia`, `observacion`, `pagado_por`, `pagado_en`, `estado`, `anulado_por`, `anulado_en`, `motivo_anulacion`) VALUES
	(54, 97, 2450.00, 'TRANSFERENCIA', 'NOM-2026-0701', 'Pago de demostracion', 50, '2026-08-02 10:00:00', 'REGISTRADO', NULL, NULL, NULL),
	(55, 98, 1775.00, 'DEPOSITO', 'NOM-2026-0702', 'Pago de demostracion', 50, '2026-08-02 10:00:00', 'REGISTRADO', NULL, NULL, NULL),
	(56, 99, 840.00, 'EFECTIVO', 'NOM-2026-0703', 'Pago de demostracion', 50, '2026-08-02 10:00:00', 'REGISTRADO', NULL, NULL, NULL),
	(57, 100, 2060.00, 'TRANSFERENCIA', 'NOM-2026-0704', 'Pago de demostracion', 50, '2026-08-02 10:00:00', 'REGISTRADO', NULL, NULL, NULL),
	(58, 102, 870.00, 'TRANSFERENCIA', 'NOM-2026-0801', 'Pago de demostracion', 50, '2026-08-23 12:00:00', 'REGISTRADO', NULL, NULL, NULL),
	(59, 103, 350.00, 'EFECTIVO', 'NOM-2026-0802', 'Pago de demostracion', 50, '2026-08-23 12:10:00', 'REGISTRADO', NULL, NULL, NULL),
	(60, 104, 2060.00, 'DEPOSITO', 'NOM-2026-0803', 'Pago de demostracion', 50, '2026-08-23 12:20:00', 'REGISTRADO', NULL, NULL, NULL),
	(61, 105, 1000.00, 'TRANSFERENCIA', 'NOM-2026-0804', 'Pago de demostracion', 50, '2026-08-23 12:30:00', 'REGISTRADO', NULL, NULL, NULL),
	(62, 107, 2250.00, 'TRANSFERENCIA', 'NOM-2026-0805', 'Pago de demostracion', 50, '2026-08-23 12:40:00', 'REGISTRADO', NULL, NULL, NULL),
	(63, 101, 100.00, 'OTRO', 'ERR-2026-0001', 'Pago de demostracion', 50, '2026-08-23 12:50:00', 'ANULADO', 50, '2026-08-23 12:50:00', 'Pago duplicado');

-- Volcando estructura para tabla colegio.periodos_academicos
CREATE TABLE IF NOT EXISTS `periodos_academicos` (
  `id_periodo_academico` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('PLANIFICADO','ACTIVO','CERRADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PLANIFICADO',
  PRIMARY KEY (`id_periodo_academico`),
  UNIQUE KEY `uq_periodo_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.periodos_academicos: ~3 rows (aproximadamente)
INSERT INTO `periodos_academicos` (`id_periodo_academico`, `nombre`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(21, 'Año escolar 2025', '2025-03-03', '2025-12-19', 'CERRADO'),
	(22, 'Año escolar 2026', '2026-03-02', '2026-12-18', 'ACTIVO'),
	(23, 'Año escolar 2027', '2027-03-01', '2027-12-17', 'PLANIFICADO');

-- Volcando estructura para tabla colegio.periodos_pago
CREATE TABLE IF NOT EXISTS `periodos_pago` (
  `id_periodo_pago` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('ABIERTO','CALCULADO','REVISADO','CERRADO','PAGADO','FINALIZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTO',
  `revisado_por` bigint unsigned DEFAULT NULL,
  `revisado_en` datetime DEFAULT NULL,
  `cerrado_por` bigint unsigned DEFAULT NULL,
  `cerrado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_periodo_pago`),
  UNIQUE KEY `uq_periodo_pago` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.periodos_pago: ~3 rows (aproximadamente)
INSERT INTO `periodos_pago` (`id_periodo_pago`, `nombre`, `fecha_inicio`, `fecha_fin`, `estado`, `revisado_por`, `revisado_en`, `cerrado_por`, `cerrado_en`) VALUES
	(21, 'Julio 2026', '2026-07-01', '2026-07-31', 'PAGADO', 50, '2026-08-01 09:00:00', 50, '2026-08-01 11:00:00'),
	(22, 'Agosto 2026', '2026-08-01', '2026-08-31', 'CALCULADO', NULL, NULL, NULL, NULL),
	(23, 'Septiembre 2026', '2026-09-01', '2026-09-30', 'ABIERTO', NULL, NULL, NULL, NULL);

-- Volcando estructura para tabla colegio.permisos
CREATE TABLE IF NOT EXISTS `permisos` (
  `id_permiso` int unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `uq_permiso` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.permisos: ~26 rows (aproximadamente)
INSERT INTO `permisos` (`id_permiso`, `clave`, `descripcion`, `estado`) VALUES
	(208, 'academico.gestionar', 'Gestionar estructura académica', 'ACTIVO'),
	(209, 'academico.ver', 'Consultar estructura académica', 'ACTIVO'),
	(210, 'alumnos.ver', 'Consultar alumnos', 'ACTIVO'),
	(211, 'apoderados.ver', 'Consultar apoderados', 'ACTIVO'),
	(212, 'asistencia.configurar', 'Configurar jornadas y dispositivos', 'ACTIVO'),
	(213, 'asistencia.escanear', 'Registrar asistencia QR', 'ACTIVO'),
	(214, 'asistencia.modificar', 'Modificar asistencia calculada', 'ACTIVO'),
	(215, 'asistencia.ver', 'Consultar asistencia', 'ACTIVO'),
	(216, 'auditoria.ver', 'Consultar auditoría', 'ACTIVO'),
	(217, 'caja.rectificar', 'Rectificar y resolver diferencias de caja', 'ACTIVO'),
	(218, 'cobros.gestionar', 'Gestionar cobros', 'ACTIVO'),
	(219, 'configuracion.gestionar', 'Gestionar configuración', 'ACTIVO'),
	(220, 'fotochecks.generar', 'Generar o renovar QR', 'ACTIVO'),
	(221, 'fotochecks.ver', 'Consultar fotochecks', 'ACTIVO'),
	(222, 'horarios.gestionar', 'Asignar jornadas y sesiones', 'ACTIVO'),
	(223, 'horarios.ver', 'Consultar horarios', 'ACTIVO'),
	(224, 'incidencias.gestionar', 'Registrar y resolver incidencias', 'ACTIVO'),
	(225, 'incidencias.ver', 'Consultar incidencias', 'ACTIVO'),
	(226, 'matriculas.crear', 'Registrar matrículas', 'ACTIVO'),
	(227, 'matriculas.ver', 'Consultar matrículas', 'ACTIVO'),
	(228, 'personas.crear', 'Registrar personas', 'ACTIVO'),
	(229, 'personas.ver', 'Consultar personas', 'ACTIVO'),
	(230, 'planilla.calcular', 'Calcular planilla', 'ACTIVO'),
	(231, 'planilla.cerrar', 'Cerrar periodos de planilla', 'ACTIVO'),
	(232, 'portal.ver', 'Consultar portal de familia', 'ACTIVO'),
	(233, 'reportes.ver', 'Consultar reportes', 'ACTIVO'),
	(234, 'usuarios.gestionar', 'Gestionar usuarios y sus roles', 'ACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=219 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.personas: ~28 rows (aproximadamente)
INSERT INTO `personas` (`id_persona`, `tipo_documento`, `numero_documento`, `nombres`, `apellidos`, `fecha_nacimiento`, `sexo`, `telefono`, `correo`, `direccion`, `fotografia`, `codigo_trabajador`, `estado`, `creado_en`, `actualizado_en`) VALUES
	(188, 'DNI', '75000001', 'Ana', 'Torres Mendoza', '2025-05-12', 'F', '990100001', 'ana.torres@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(189, 'DNI', '75000002', 'Bruno', 'Díaz Mendoza', '2025-09-20', 'M', '990100002', 'bruno.diaz@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(190, 'DNI', '75000003', 'Carla', 'Ruiz Salas', '2024-02-08', 'F', '990100003', 'carla.ruiz@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(191, 'DNI', '75000004', 'Diego', 'Pérez Núñez', '2023-11-16', 'M', '990100004', 'diego.perez@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(192, 'DNI', '75000005', 'Elena', 'Soto Campos', '2023-07-04', 'F', '990100005', 'elena.soto@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(193, 'DNI', '75000006', 'Felipe', 'León Arias', '2024-01-23', 'M', '990100006', 'felipe.leon@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(194, 'DNI', '75000007', 'Gabriela', 'Ramos Ortiz', '2024-06-14', 'F', '990100007', 'gabriela.ramos@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(195, 'DNI', '75000008', 'Hugo', 'Medina Vera', '2009-03-19', 'M', '990100008', 'hugo.medina@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(196, 'DNI', '75000009', 'Inés', 'Castro Luna', '2023-08-30', 'F', '990100009', 'ines.castro@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(197, 'DNI', '75000010', 'José', 'Vega Ríos', '2023-12-02', 'M', '990100010', 'jose.vega@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(198, 'DNI', '75000011', 'Lucía', 'Morales Peña', '2024-10-11', 'F', '990100011', 'lucia.morales@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(199, 'DNI', '75000012', 'Raúl', 'Alumno Trabajador', '2003-04-10', 'M', '990200012', 'raul.trabajador@demo.test', 'Lima, Perú', NULL, 'CS-2026-0010', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(200, 'DNI', '45000001', 'Paula', 'Mendoza Rojas', '1991-02-14', 'F', '991100001', 'paula.mendoza@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(201, 'DNI', '45000002', 'Roberto', 'Ruiz Flores', '1987-07-22', 'M', '991100002', 'roberto.ruiz@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(202, 'DNI', '45000003', 'Sandra', 'Núñez Silva', '1992-03-03', 'F', '991100003', 'sandra.nunez@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(203, 'DNI', '45000004', 'Miguel', 'Soto Reyes', '1985-05-18', 'M', '991100004', 'miguel.soto@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(204, 'DNI', '45000005', 'Patricia', 'Castro Díaz', '1989-09-09', 'F', '991100005', 'patricia.apoderada@demo.test', 'Lima, Perú', NULL, NULL, 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(205, 'DNI', '45000006', 'Olga', 'Morales Torres', '1994-01-15', 'F', '991100006', 'olga.morales@demo.test', 'Lima, Perú', NULL, NULL, 'INACTIVO', '2026-08-23 00:54:20', NULL),
	(206, 'DNI', '35000001', 'María', 'Paredes Soto', '1994-04-06', 'F', '992100001', 'maria.paredes@demo.test', 'Lima, Perú', NULL, 'CS-2022-0001', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(207, 'DNI', '35000002', 'Carlos', 'Rivera León', '1996-06-17', 'M', '992100002', 'carlos.rivera@demo.test', 'Lima, Perú', NULL, 'CS-2023-0002', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(208, 'DNI', '35000003', 'Lucía', 'Gómez Vidal', '1998-10-24', 'F', '992100003', 'lucia.gomez@demo.test', 'Lima, Perú', NULL, 'CS-2024-0003', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(209, 'DNI', '35000004', 'Mariana', 'López Cruz', '1990-12-01', 'F', '992100004', 'mariana.lopez@demo.test', 'Lima, Perú', NULL, 'CS-2020-0004', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(210, 'DNI', '35000005', 'Jorge', 'Vargas León', '1988-08-12', 'M', '992100005', 'jorge.vargas@demo.test', 'Lima, Perú', NULL, 'CS-2021-0005', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(211, 'DNI', '35000006', 'Rosa', 'Salazar Vega', '1995-03-29', 'F', '992100006', 'rosa.salazar@demo.test', 'Lima, Perú', NULL, 'CS-2024-0006', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(212, 'DNI', '35000007', 'Patricia', 'Quispe Mora', '1993-11-08', 'F', '992100007', 'patricia.planilla@demo.test', 'Lima, Perú', NULL, 'CS-2023-0007', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(213, 'DNI', '35000008', 'Ernesto', 'Cáceres Ruiz', '1986-05-25', 'M', '992100008', 'ernesto.asistencia@demo.test', 'Lima, Perú', NULL, 'CS-2019-0008', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(214, 'DNI', '35000009', 'Diana', 'Fernández Prado', '1983-02-20', 'F', '992100009', 'diana.directora@demo.test', 'Lima, Perú', NULL, 'CS-2015-0009', 'ACTIVO', '2026-08-23 00:54:20', NULL),
	(215, 'DNI', '35000010', 'Pedro', 'Retirado Demo', '1980-07-07', 'M', '992100010', 'pedro.retirado@demo.test', 'Lima, Perú', NULL, 'CS-2017-0011', 'INACTIVO', '2026-08-23 00:54:20', NULL),
	(216, 'DNI', '777', 'Mayra', 'Luna', NULL, NULL, '999', 'luna@ejemplo.com', NULL, NULL, NULL, 'ACTIVO', '2026-08-22 20:59:51', NULL),
	(217, 'DNI', '987', 'Pablo', 'Lima', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVO', '2026-08-22 22:08:41', NULL),
	(218, 'DNI', '9876', 'Carla', 'Lima', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVO', '2026-08-22 22:09:59', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.persona_perfiles: ~30 rows (aproximadamente)
INSERT INTO `persona_perfiles` (`id_persona_perfil`, `id_persona`, `tipo`, `estado`, `creado_en`) VALUES
	(215, 188, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(216, 189, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(217, 190, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(218, 191, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(219, 192, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(220, 193, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(221, 194, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(222, 196, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(223, 197, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(224, 198, 'ALUMNO', 'ACTIVO', '2026-08-22 19:54:20'),
	(225, 195, 'ALUMNO', 'INACTIVO', '2026-08-22 19:54:20'),
	(226, 199, 'ALUMNO', 'INACTIVO', '2026-08-22 19:54:20'),
	(227, 199, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 19:54:20'),
	(228, 200, 'APODERADO', 'ACTIVO', '2026-08-22 19:54:20'),
	(229, 201, 'APODERADO', 'ACTIVO', '2026-08-22 19:54:20'),
	(230, 202, 'APODERADO', 'ACTIVO', '2026-08-22 19:54:20'),
	(231, 203, 'APODERADO', 'ACTIVO', '2026-08-22 19:54:20'),
	(232, 204, 'APODERADO', 'ACTIVO', '2026-08-22 19:54:20'),
	(233, 205, 'APODERADO', 'INACTIVO', '2026-08-22 19:54:20'),
	(234, 206, 'DOCENTE', 'ACTIVO', '2026-08-22 19:54:20'),
	(235, 207, 'DOCENTE', 'ACTIVO', '2026-08-22 19:54:21'),
	(236, 208, 'DOCENTE', 'ACTIVO', '2026-08-22 19:54:21'),
	(237, 209, 'DOCENTE', 'ACTIVO', '2026-08-22 19:54:21'),
	(238, 209, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 19:54:21'),
	(239, 210, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 19:54:21'),
	(240, 211, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 19:54:21'),
	(241, 212, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 19:54:21'),
	(242, 213, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 19:54:21'),
	(243, 214, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 19:54:21'),
	(244, 215, 'DOCENTE', 'INACTIVO', '2026-08-22 19:54:21'),
	(245, 216, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-22 20:59:51'),
	(246, 217, 'ALUMNO', 'ACTIVO', '2026-08-22 22:08:41'),
	(247, 218, 'APODERADO', 'ACTIVO', '2026-08-22 22:09:59');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.planilla_ajustes: ~2 rows (aproximadamente)
INSERT INTO `planilla_ajustes` (`id_ajuste`, `id_planilla_detalle`, `id_detalle_concepto`, `naturaleza`, `importe`, `motivo`, `registrado_por`, `registrado_en`, `estado`, `anulado_por`, `anulado_en`, `motivo_anulacion`) VALUES
	(12, 107, NULL, 'BONIFICACION', 150.00, 'Responsabilidad adicional', 50, '2026-08-22 19:54:21', 'ACTIVO', NULL, NULL, NULL),
	(13, 101, NULL, 'DESCUENTO', 75.00, 'Descuento ingresado por error', 50, '2026-08-22 11:00:00', 'ANULADO', 50, '2026-08-22 11:15:00', 'Importe equivocado');

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
  `decision_pago` enum('PENDIENTE','RETENIDO','EXCLUIDO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `monto_excluido` decimal(12,2) NOT NULL DEFAULT '0.00',
  `decision_motivo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decision_por` bigint unsigned DEFAULT NULL,
  `decision_en` datetime DEFAULT NULL,
  `estado` enum('CALCULADO','REVISADO','CERRADO','PARCIAL','PAGADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CALCULADO',
  `calculado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_planilla_detalle`),
  UNIQUE KEY `uq_planilla_trabajador` (`id_periodo_pago`,`id_vinculo`),
  KEY `fk_planilla_vinculo` (`id_vinculo`),
  CONSTRAINT `fk_planilla_periodo` FOREIGN KEY (`id_periodo_pago`) REFERENCES `periodos_pago` (`id_periodo_pago`),
  CONSTRAINT `fk_planilla_vinculo` FOREIGN KEY (`id_vinculo`) REFERENCES `vinculos_laborales` (`id_vinculo`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.planilla_detalles: ~14 rows (aproximadamente)
INSERT INTO `planilla_detalles` (`id_planilla_detalle`, `id_periodo_pago`, `id_vinculo`, `monto_base`, `bonificaciones`, `descuentos`, `monto_pagar`, `monto_pagado`, `saldo_pendiente`, `decision_pago`, `monto_excluido`, `decision_motivo`, `decision_por`, `decision_en`, `estado`, `calculado_en`) VALUES
	(97, 21, 79, 2400.00, 100.00, 50.00, 2450.00, 2450.00, 0.00, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'PAGADO', '2026-08-22 19:54:21'),
	(98, 21, 80, 1800.00, 0.00, 25.00, 1775.00, 1775.00, 0.00, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'PAGADO', '2026-08-22 19:54:21'),
	(99, 21, 81, 840.00, 0.00, 0.00, 840.00, 840.00, 0.00, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'PAGADO', '2026-08-22 19:54:21'),
	(100, 21, 83, 2000.00, 100.00, 40.00, 2060.00, 2060.00, 0.00, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'PAGADO', '2026-08-22 19:54:21'),
	(101, 22, 79, 2400.00, 100.00, 0.40, 2499.60, 0.00, 2499.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(102, 22, 80, 172.35, 100.00, 3.90, 268.45, 0.00, 268.45, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(103, 22, 81, 0.00, 100.00, 0.40, 99.60, 0.00, 99.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(104, 22, 83, 1604.00, 100.00, 0.40, 1703.60, 0.00, 1703.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(105, 22, 84, 1900.00, 100.00, 0.40, 1999.60, 0.00, 1999.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(106, 22, 85, 1650.00, 100.00, 0.40, 1749.60, 0.00, 1749.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(107, 22, 86, 2100.00, 100.00, 0.40, 2199.60, 0.00, 2199.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(108, 22, 87, 1800.00, 100.00, 0.40, 1899.60, 0.00, 1899.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(109, 22, 88, 3500.00, 100.00, 0.40, 3599.60, 0.00, 3599.60, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:10:48'),
	(132, 22, 91, 1762.68, 100.00, 382.90, 1479.78, 0.00, 1479.78, 'PENDIENTE', 0.00, NULL, NULL, NULL, 'CALCULADO', '2026-08-23 10:24:20');

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
) ENGINE=InnoDB AUTO_INCREMENT=345 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.planilla_detalle_conceptos: ~28 rows (aproximadamente)
INSERT INTO `planilla_detalle_conceptos` (`id_detalle_concepto`, `id_planilla_detalle`, `id_concepto_pago`, `id_regla`, `cantidad`, `importe`, `explicacion`) VALUES
	(176, 97, 64, NULL, 22.00, 2400.00, '{"dias": 22, "feriados": 1}'),
	(177, 97, 67, 34, 1.00, 100.00, '{"automatico": true}'),
	(178, 97, 68, 31, 100.00, -50.00, '{"minutos": 100, "tolerancia_aplicada": true}'),
	(179, 98, 65, NULL, 40.00, 1800.00, '{"horas": 40}'),
	(180, 98, 68, 31, 50.00, -25.00, '{"minutos": 50}'),
	(181, 99, 66, NULL, 12.00, 840.00, '{"sesiones": 12, "esperadas": 12}'),
	(182, 100, 64, NULL, 22.00, 1500.00, '{"dias": 22}'),
	(183, 100, 65, NULL, 10.00, 500.00, '{"horas": 10}'),
	(184, 100, 67, 34, 1.00, 100.00, '{"automatico": true}'),
	(185, 100, 68, 31, 80.00, -40.00, '{"minutos": 80}'),
	(312, 108, 64, NULL, 21.00, 1800.00, '{"dias": 21, "feriados": 0}'),
	(313, 108, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(314, 108, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(315, 109, 64, NULL, 21.00, 3500.00, '{"dias": 21, "feriados": 0}'),
	(316, 109, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(317, 109, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(318, 103, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(319, 103, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(320, 104, 64, NULL, 21.00, 1500.00, '{"dias": 21, "feriados": 0}'),
	(321, 104, 65, NULL, 2.08, 104.00, '{"horas": 2.08}'),
	(322, 104, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(323, 104, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(324, 101, 64, NULL, 21.00, 2400.00, '{"dias": 21, "feriados": 0}'),
	(325, 101, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(326, 101, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(327, 107, 64, NULL, 21.00, 2100.00, '{"dias": 21, "feriados": 0}'),
	(328, 107, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(329, 107, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(330, 102, 65, NULL, 3.83, 172.35, '{"horas": 3.83}'),
	(331, 102, 76, 31, 7.00, -3.50, '{"evento": "TARDANZA", "automatico": true}'),
	(332, 102, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(333, 102, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(334, 106, 64, NULL, 21.00, 1650.00, '{"dias": 21, "feriados": 0}'),
	(335, 106, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(336, 106, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(337, 105, 64, NULL, 21.00, 1900.00, '{"dias": 21, "feriados": 0}'),
	(338, 105, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(339, 105, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}'),
	(340, 132, 64, NULL, 15.00, 1214.29, '{"dias": 15, "desde": "2026-08-01", "hasta": "2026-08-21", "tramo": true, "modalidad": "MENSUAL", "id_vinculo": 89, "dias_laborables": "1,2,3,4,5"}'),
	(341, 132, 64, NULL, 10.00, 548.39, '{"dias": 10, "desde": "2026-08-22", "hasta": "2026-08-31", "tramo": true, "modalidad": "MENSUAL", "id_vinculo": 91, "dias_laborables": "1,2,3,4,5,6,7"}'),
	(342, 132, 76, 31, 765.00, -382.50, '{"evento": "TARDANZA", "automatico": true}'),
	(343, 132, 74, 33, 1.00, -0.40, '{"evento": "SALIDA_ANTICIPADA", "automatico": true}'),
	(344, 132, 67, 34, 1.00, 100.00, '{"evento": "FIJO", "automatico": true}');

-- Volcando estructura para tabla colegio.rectificaciones_caja
CREATE TABLE IF NOT EXISTS `rectificaciones_caja` (
  `id_rectificacion` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_cierre` bigint unsigned NOT NULL,
  `accion` enum('RECTIFICACION','JUSTIFICACION','REGULARIZACION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo_tipo` enum('ERROR_CONTEO','ERROR_DIGITACION','MOVIMIENTO_OMITIDO','DIFERENCIA_REAL','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia_regularizacion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidencia_archivo` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diferencia_anterior` decimal(12,2) NOT NULL,
  `diferencia_nueva` decimal(12,2) NOT NULL,
  `registrado_por` bigint unsigned DEFAULT NULL,
  `registrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rectificacion`),
  KEY `ix_rectificacion_cierre` (`id_cierre`,`registrado_en`),
  KEY `fk_rectificacion_usuario` (`registrado_por`),
  CONSTRAINT `fk_rectificacion_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `cierres_caja` (`id_cierre`),
  CONSTRAINT `fk_rectificacion_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.rectificaciones_caja: ~0 rows (aproximadamente)
INSERT INTO `rectificaciones_caja` (`id_rectificacion`, `id_cierre`, `accion`, `motivo_tipo`, `motivo`, `referencia_regularizacion`, `evidencia_archivo`, `diferencia_anterior`, `diferencia_nueva`, `registrado_por`, `registrado_en`) VALUES
	(2, 24, 'RECTIFICACION', 'ERROR_CONTEO', 'Se volvió a contar el efectivo y se corrigió el importe declarado.', NULL, NULL, -5.00, 0.00, 47, '2026-08-22 12:45:00');

-- Volcando estructura para tabla colegio.rectificaciones_caja_medios
CREATE TABLE IF NOT EXISTS `rectificaciones_caja_medios` (
  `id_rectificacion_medio` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_rectificacion` bigint unsigned NOT NULL,
  `medio` enum('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `importe_sistema` decimal(12,2) NOT NULL,
  `importe_anterior` decimal(12,2) NOT NULL,
  `importe_nuevo` decimal(12,2) NOT NULL,
  `diferencia_anterior` decimal(12,2) NOT NULL,
  `diferencia_nueva` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id_rectificacion_medio`),
  UNIQUE KEY `uq_rectificacion_medio` (`id_rectificacion`,`medio`),
  CONSTRAINT `fk_rectificacion_medio` FOREIGN KEY (`id_rectificacion`) REFERENCES `rectificaciones_caja` (`id_rectificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.rectificaciones_caja_medios: ~0 rows (aproximadamente)
INSERT INTO `rectificaciones_caja_medios` (`id_rectificacion_medio`, `id_rectificacion`, `medio`, `importe_sistema`, `importe_anterior`, `importe_nuevo`, `diferencia_anterior`, `diferencia_nueva`) VALUES
	(3, 2, 'EFECTIVO', 100.00, 95.00, 100.00, -5.00, 0.00),
	(4, 2, 'TRANSFERENCIA', 920.00, 920.00, 920.00, 0.00, 0.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.reglas_descuento: ~5 rows (aproximadamente)
INSERT INTO `reglas_descuento` (`id_regla`, `nombre`, `naturaleza`, `evento`, `formula`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(31, 'Minuto de tardanza', 'DESCUENTO', 'TARDANZA', '{"monto_unitario": 0.5}', '2026-01-01', NULL, 'ACTIVO'),
	(32, 'Falta injustificada', 'DESCUENTO', 'FALTA', '{"monto_unitario": 80}', '2026-01-01', NULL, 'ACTIVO'),
	(33, 'Salida anticipada', 'DESCUENTO', 'SALIDA_ANTICIPADA', '{"monto_unitario": 0.4}', '2026-01-01', NULL, 'ACTIVO'),
	(34, 'Bono institucional', 'BONIFICACION', 'FIJO', '{"monto_unitario": 100}', '2026-01-01', NULL, 'ACTIVO'),
	(35, 'Regla anterior', 'DESCUENTO', 'TARDANZA', '{"monto_unitario": 1}', '2025-01-01', '2025-12-31', 'INACTIVO');

-- Volcando estructura para tabla colegio.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_rol` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.roles: ~9 rows (aproximadamente)
INSERT INTO `roles` (`id_rol`, `nombre`, `estado`) VALUES
	(64, 'Super Administrador', 'ACTIVO'),
	(65, 'Administrador', 'ACTIVO'),
	(66, 'Director', 'ACTIVO'),
	(67, 'Responsable de asistencia', 'ACTIVO'),
	(68, 'Responsable de cobros', 'ACTIVO'),
	(69, 'Responsable de planilla', 'ACTIVO'),
	(70, 'Docente', 'ACTIVO'),
	(71, 'Apoderado', 'ACTIVO'),
	(72, 'Consulta', 'ACTIVO');

-- Volcando estructura para tabla colegio.rol_permiso
CREATE TABLE IF NOT EXISTS `rol_permiso` (
  `id_rol` int unsigned NOT NULL,
  `id_permiso` int unsigned NOT NULL,
  PRIMARY KEY (`id_rol`,`id_permiso`),
  KEY `fk_rp_permiso` (`id_permiso`),
  CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.rol_permiso: ~109 rows (aproximadamente)
INSERT INTO `rol_permiso` (`id_rol`, `id_permiso`) VALUES
	(64, 208),
	(65, 208),
	(64, 209),
	(65, 209),
	(66, 209),
	(67, 209),
	(70, 209),
	(72, 209),
	(64, 210),
	(65, 210),
	(66, 210),
	(67, 210),
	(68, 210),
	(70, 210),
	(72, 210),
	(64, 211),
	(65, 211),
	(66, 211),
	(68, 211),
	(72, 211),
	(64, 212),
	(65, 212),
	(67, 212),
	(64, 213),
	(65, 213),
	(67, 213),
	(64, 214),
	(65, 214),
	(67, 214),
	(64, 215),
	(65, 215),
	(66, 215),
	(67, 215),
	(69, 215),
	(70, 215),
	(72, 215),
	(64, 216),
	(65, 216),
	(66, 216),
	(69, 216),
	(64, 217),
	(65, 217),
	(66, 217),
	(64, 218),
	(65, 218),
	(68, 218),
	(64, 219),
	(65, 219),
	(64, 220),
	(65, 220),
	(64, 221),
	(65, 221),
	(66, 221),
	(67, 221),
	(72, 221),
	(64, 222),
	(65, 222),
	(67, 222),
	(64, 223),
	(65, 223),
	(66, 223),
	(67, 223),
	(69, 223),
	(70, 223),
	(72, 223),
	(64, 224),
	(65, 224),
	(66, 224),
	(67, 224),
	(64, 225),
	(65, 225),
	(66, 225),
	(67, 225),
	(69, 225),
	(70, 225),
	(72, 225),
	(64, 226),
	(65, 226),
	(64, 227),
	(65, 227),
	(66, 227),
	(67, 227),
	(68, 227),
	(70, 227),
	(72, 227),
	(64, 228),
	(65, 228),
	(64, 229),
	(65, 229),
	(66, 229),
	(67, 229),
	(68, 229),
	(69, 229),
	(72, 229),
	(64, 230),
	(65, 230),
	(69, 230),
	(64, 231),
	(65, 231),
	(69, 231),
	(64, 232),
	(65, 232),
	(71, 232),
	(64, 233),
	(65, 233),
	(66, 233),
	(67, 233),
	(68, 233),
	(69, 233),
	(70, 233),
	(72, 233),
	(64, 234),
	(65, 234);

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
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.secciones: ~15 rows (aproximadamente)
INSERT INTO `secciones` (`id_seccion`, `id_grado`, `nombre`, `capacidad`, `estado`) VALUES
	(100, 95, 'A', 18, 'ACTIVO'),
	(101, 96, 'A', 20, 'ACTIVO'),
	(102, 97, 'A', 20, 'ACTIVO'),
	(103, 98, 'A', 3, 'ACTIVO'),
	(104, 98, 'B', 5, 'ACTIVO'),
	(105, 99, 'A', 5, 'ACTIVO'),
	(106, 100, 'A', 25, 'ACTIVO'),
	(107, 101, 'A', 25, 'ACTIVO'),
	(108, 102, 'A', 25, 'ACTIVO'),
	(109, 103, 'A', 25, 'ACTIVO'),
	(110, 104, 'A', 30, 'ACTIVO'),
	(111, 105, 'A', 30, 'ACTIVO'),
	(112, 106, 'A', 30, 'ACTIVO'),
	(113, 107, 'A', 30, 'ACTIVO'),
	(114, 108, 'A', 2, 'ACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.tarifas_cobro: ~11 rows (aproximadamente)
INSERT INTO `tarifas_cobro` (`id_tarifa`, `id_concepto_cobro`, `id_periodo_academico`, `id_grado`, `importe`, `vigencia_inicio`, `vigencia_fin`, `estado`) VALUES
	(73, 55, 22, NULL, 500.00, '2026-01-01', '2026-04-30', 'ACTIVO'),
	(74, 56, 22, NULL, 420.00, '2026-03-01', '2026-12-31', 'ACTIVO'),
	(75, 56, 22, 98, 450.00, '2026-03-01', '2026-12-31', 'ACTIVO'),
	(76, 57, 22, NULL, 160.00, '2026-03-01', '2026-12-31', 'ACTIVO'),
	(77, 58, 22, NULL, 120.00, '2026-03-01', '2026-12-31', 'ACTIVO'),
	(78, 59, 22, NULL, 200.00, '2026-01-01', '2026-12-31', 'ACTIVO'),
	(79, 60, 22, NULL, 35.00, '2026-01-01', '2026-12-31', 'ACTIVO'),
	(80, 61, 22, NULL, 150.00, '2026-06-01', '2026-12-31', 'ACTIVO'),
	(81, 55, 21, NULL, 450.00, '2025-01-01', '2025-04-30', 'ACTIVO'),
	(82, 56, 21, NULL, 380.00, '2025-03-01', '2025-12-31', 'ACTIVO'),
	(83, 56, 22, NULL, 400.00, '2026-01-01', '2026-02-28', 'INACTIVO'),
	(84, 55, 22, NULL, 300.00, '2026-08-22', '2026-12-31', 'ACTIVO'),
	(85, 63, 22, NULL, 350.00, '2026-08-22', '2026-12-31', 'ACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.usuarios: ~7 rows (aproximadamente)
INSERT INTO `usuarios` (`id_usuario`, `id_persona`, `usuario`, `contrasena`, `estado`, `ultimo_acceso_en`, `creado_en`) VALUES
	(46, NULL, 'administrador', '$2y$10$BAEMzLwTJtqkcg6XBMYlUOPQrd9xRrqEMoh7raNluhGwSxolnfWN2', 'ACTIVO', NULL, '2026-08-22 19:54:20'),
	(47, 214, 'director.demo', '$2y$10$Te.UBnVoPaQHavYAFQhXauQo5kkZM4SFlpgmZIKFuyiLSrCw6AnSG', 'ACTIVO', NULL, '2026-08-22 19:54:21'),
	(48, 213, 'asistencia.demo', '$2y$10$Te.UBnVoPaQHavYAFQhXauQo5kkZM4SFlpgmZIKFuyiLSrCw6AnSG', 'ACTIVO', NULL, '2026-08-22 19:54:21'),
	(49, 211, 'caja.demo', '$2y$10$Te.UBnVoPaQHavYAFQhXauQo5kkZM4SFlpgmZIKFuyiLSrCw6AnSG', 'ACTIVO', NULL, '2026-08-22 19:54:21'),
	(50, 212, 'planilla.demo', '$2y$10$Te.UBnVoPaQHavYAFQhXauQo5kkZM4SFlpgmZIKFuyiLSrCw6AnSG', 'ACTIVO', NULL, '2026-08-22 19:54:21'),
	(51, 206, 'docente.demo', '$2y$10$Te.UBnVoPaQHavYAFQhXauQo5kkZM4SFlpgmZIKFuyiLSrCw6AnSG', 'ACTIVO', NULL, '2026-08-22 19:54:21'),
	(52, 200, 'apoderado.demo', '$2y$10$Te.UBnVoPaQHavYAFQhXauQo5kkZM4SFlpgmZIKFuyiLSrCw6AnSG', 'ACTIVO', NULL, '2026-08-22 19:54:21'),
	(53, 216, 'mayra.luna', '$2y$10$iuEmrfgtejU6QnSSsYucL.YIWoA/JdYImGwttI1GepVjJFHKlNEFG', 'ACTIVO', NULL, '2026-08-22 21:00:36');

-- Volcando estructura para tabla colegio.usuario_rol
CREATE TABLE IF NOT EXISTS `usuario_rol` (
  `id_usuario` bigint unsigned NOT NULL,
  `id_rol` int unsigned NOT NULL,
  PRIMARY KEY (`id_usuario`,`id_rol`),
  KEY `fk_ur_rol` (`id_rol`),
  CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.usuario_rol: ~7 rows (aproximadamente)
INSERT INTO `usuario_rol` (`id_usuario`, `id_rol`) VALUES
	(46, 64),
	(53, 65),
	(47, 66),
	(48, 67),
	(49, 68),
	(50, 69),
	(51, 70),
	(52, 71);

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
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.vinculos_laborales: ~12 rows (aproximadamente)
INSERT INTO `vinculos_laborales` (`id_vinculo`, `id_persona`, `id_vinculo_anterior`, `id_area`, `id_cargo`, `tipo`, `modalidad_pago`, `monto_base`, `monto_mensual`, `monto_hora`, `monto_sesion`, `dias_laborables`, `fecha_inicio`, `fecha_fin`, `estado`, `motivo_cambio`, `registrado_por`, `registrado_en`) VALUES
	(79, 206, NULL, 22, 60, 'DOCENTE', 'MENSUAL', 2400.00, 2400.00, 0.00, 0.00, '1,2,3,4,5', '2022-03-01', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(80, 207, NULL, 22, 61, 'DOCENTE', 'HORA', 45.00, 0.00, 45.00, 0.00, '1,2,3,4,5', '2023-03-01', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(81, 208, NULL, 22, 62, 'DOCENTE', 'SESION', 70.00, 0.00, 0.00, 70.00, '2,4', '2024-03-01', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(82, 209, NULL, 23, 64, 'ADMINISTRATIVO', 'MENSUAL', 1750.00, 1750.00, 0.00, 0.00, '1,2,3,4,5', '2020-01-02', '2025-12-31', 'FINALIZADO', 'Cambio de administrativo a docente', 50, '2026-08-22 19:54:21'),
	(83, 209, 82, 22, 60, 'DOCENTE', 'MIXTO', 1500.00, 1500.00, 50.00, 0.00, '1,2,3,4,5', '2026-01-01', NULL, 'ACTIVO', 'Cambio de puesto y modalidad mixta', 50, '2026-08-22 19:54:21'),
	(84, 210, NULL, 23, 68, 'ADMINISTRATIVO', 'MENSUAL', 1900.00, 1900.00, 0.00, 0.00, '1,2,3,4,5', '2021-01-02', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(85, 211, NULL, 23, 65, 'ADMINISTRATIVO', 'MENSUAL', 1650.00, 1650.00, 0.00, 0.00, '1,2,3,4,5', '2024-01-02', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(86, 212, NULL, 23, 66, 'ADMINISTRATIVO', 'MENSUAL', 2100.00, 2100.00, 0.00, 0.00, '1,2,3,4,5', '2023-01-02', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(87, 213, NULL, 23, 67, 'ADMINISTRATIVO', 'MENSUAL', 1800.00, 1800.00, 0.00, 0.00, '1,2,3,4,5', '2019-01-02', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(88, 214, NULL, 24, 63, 'ADMINISTRATIVO', 'MENSUAL', 3500.00, 3500.00, 0.00, 0.00, '1,2,3,4,5', '2015-01-02', NULL, 'ACTIVO', NULL, 50, '2026-08-22 19:54:21'),
	(89, 199, NULL, 23, 68, 'ADMINISTRATIVO', 'MENSUAL', 1700.00, 1700.00, 0.00, 0.00, '1,2,3,4,5', '2026-08-01', '2026-08-21', 'FINALIZADO', NULL, 50, '2026-08-22 19:54:21'),
	(90, 215, NULL, 22, 60, 'DOCENTE', 'MENSUAL', 2000.00, 2000.00, 0.00, 0.00, '1,2,3,4,5', '2017-03-01', '2025-12-20', 'FINALIZADO', 'Cese laboral', 50, '2026-08-22 19:54:21'),
	(91, 199, 89, 23, 68, 'ADMINISTRATIVO', 'MENSUAL', 1700.00, 1700.00, 0.00, 0.00, '1,2,3,4,5,6,7', '2026-08-22', NULL, 'ACTIVO', 'Se cambio para realizar prueba.', 46, '2026-08-22 23:57:33');

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
