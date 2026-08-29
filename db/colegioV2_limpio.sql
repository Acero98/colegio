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
) ENGINE=InnoDB AUTO_INCREMENT=331 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.auditoria: ~46 rows (aproximadamente)
INSERT INTO `auditoria` (`id_auditoria`, `id_usuario`, `accion`, `entidad`, `id_entidad`, `valores_antes`, `valores_despues`, `ip`, `creado_en`) VALUES
	(329, 46, 'CREAR', 'personas', 219, NULL, '{"correo": "blademir@ejemplo.com", "nombres": "Blademir", "perfiles": ["ADMINISTRATIVO"], "telefono": "999", "apellidos": "Acero Oliva", "fotografia": null, "tipo_documento": "DNI", "numero_documento": "99887766"}', '::1', '2026-08-23 13:02:00'),
	(330, 46, 'TRANSICION_LABORAL', 'vinculos_laborales', NULL, NULL, '{"tipo": "ADMINISTRATIVO", "cargo": "Gerente General", "fecha_fin": "", "csrf_token": "8704a4c236f6e106a0ab454adb7572a859aef3a929350da24c218a3c10fb6d12", "id_persona": "219", "monto_hora": "0", "fecha_inicio": "2026-08-23", "monto_sesion": "0", "monto_mensual": "2000", "dias_laborables": ["1", "2", "3", "4", "5"]}', '::1', '2026-08-23 13:03:07');

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

-- Volcando estructura para tabla colegio.cargos
CREATE TABLE IF NOT EXISTS `cargos` (
  `id_cargo` int unsigned NOT NULL AUTO_INCREMENT,
  `id_area` int unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_cargo`),
  KEY `fk_cargo_area` (`id_area`),
  CONSTRAINT `fk_cargo_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
	(68, 23, 'Auxiliar administrativo', 'ACTIVO'),
	(69, NULL, 'Gerente General', 'ACTIVO');

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
	(95, 22, '3 años', 1, 'INACTIVO'),
	(96, 22, '4 años', 2, 'INACTIVO'),
	(97, 22, '5 años', 3, 'INACTIVO'),
	(98, 23, '1.° grado', 1, 'ACTIVO'),
	(99, 23, '2.° grado', 2, 'ACTIVO'),
	(100, 23, '3.° grado', 3, 'ACTIVO'),
	(101, 23, '4.° grado', 4, 'ACTIVO'),
	(102, 23, '5.° grado', 5, 'ACTIVO'),
	(103, 23, '6.° grado', 6, 'ACTIVO'),
	(104, 24, '1.° año', 1, 'INACTIVO'),
	(105, 24, '2.° año', 2, 'INACTIVO'),
	(106, 24, '3.° año', 3, 'INACTIVO'),
	(107, 24, '4.° año', 4, 'INACTIVO'),
	(108, 24, '5.° año', 5, 'INACTIVO');

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
	(22, 'Inicial', 1, 'INACTIVO'),
	(23, 'Primaria', 2, 'ACTIVO'),
	(24, 'Secundaria', 3, 'INACTIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.personas: ~28 rows (aproximadamente)
INSERT INTO `personas` (`id_persona`, `tipo_documento`, `numero_documento`, `nombres`, `apellidos`, `fecha_nacimiento`, `sexo`, `telefono`, `correo`, `direccion`, `fotografia`, `codigo_trabajador`, `estado`, `creado_en`, `actualizado_en`) VALUES
	(219, 'DNI', '99887766', 'Blademir', 'Acero Oliva', NULL, NULL, '999', 'blademir@ejemplo.com', NULL, NULL, 'CS-2026-0001', 'ACTIVO', '2026-08-23 13:02:00', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.persona_perfiles: ~30 rows (aproximadamente)
INSERT INTO `persona_perfiles` (`id_persona_perfil`, `id_persona`, `tipo`, `estado`, `creado_en`) VALUES
	(248, 219, 'ADMINISTRATIVO', 'ACTIVO', '2026-08-23 13:02:00');

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
	(100, 95, 'A', 18, 'INACTIVO'),
	(101, 96, 'A', 20, 'INACTIVO'),
	(102, 97, 'A', 20, 'INACTIVO'),
	(103, 98, 'A', 3, 'ACTIVO'),
	(104, 98, 'B', 5, 'ACTIVO'),
	(105, 99, 'A', 5, 'ACTIVO'),
	(106, 100, 'A', 25, 'ACTIVO'),
	(107, 101, 'A', 25, 'ACTIVO'),
	(108, 102, 'A', 25, 'ACTIVO'),
	(109, 103, 'A', 25, 'ACTIVO'),
	(110, 104, 'A', 30, 'INACTIVO'),
	(111, 105, 'A', 30, 'INACTIVO'),
	(112, 106, 'A', 30, 'INACTIVO'),
	(113, 107, 'A', 30, 'INACTIVO'),
	(114, 108, 'A', 2, 'INACTIVO');

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
	(46, NULL, 'administrador', '$2y$10$BAEMzLwTJtqkcg6XBMYlUOPQrd9xRrqEMoh7raNluhGwSxolnfWN2', 'ACTIVO', NULL, '2026-08-22 19:54:20');

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
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla colegio.vinculos_laborales: ~12 rows (aproximadamente)
INSERT INTO `vinculos_laborales` (`id_vinculo`, `id_persona`, `id_vinculo_anterior`, `id_area`, `id_cargo`, `tipo`, `modalidad_pago`, `monto_base`, `monto_mensual`, `monto_hora`, `monto_sesion`, `dias_laborables`, `fecha_inicio`, `fecha_fin`, `estado`, `motivo_cambio`, `registrado_por`, `registrado_en`) VALUES
	(92, 219, NULL, NULL, 69, 'ADMINISTRATIVO', 'MENSUAL', 2000.00, 2000.00, 0.00, 0.00, '1,2,3,4,5', '2026-08-23', NULL, 'ACTIVO', NULL, NULL, '2026-08-23 13:03:07');

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
