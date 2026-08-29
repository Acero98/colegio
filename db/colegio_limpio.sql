-- COLEGIO - BASE LIMPIA
-- Esquema completo sin datos operativos ni institucionales.
-- Conserva solo roles, permisos y un Super Administrador para permitir el acceso inicial.
-- Despues de importar, complete Administrar institucion y la configuracion del nuevo periodo.

-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: colegio
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ajustes_cobro`
--

DROP TABLE IF EXISTS `ajustes_cobro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ajustes_cobro` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alumno_apoderado`
--

DROP TABLE IF EXISTS `alumno_apoderado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumno_apoderado` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aperturas_caja`
--

DROP TABLE IF EXISTS `aperturas_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aperturas_caja` (
  `id_apertura` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `monto_apertura` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abierto_por` bigint unsigned DEFAULT NULL,
  `abierto_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('ABIERTA','CERRADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTA',
  PRIMARY KEY (`id_apertura`),
  KEY `ix_apertura_caja_fecha` (`fecha`,`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id_area` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_area`),
  UNIQUE KEY `uq_area_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asignaciones_horario`
--

DROP TABLE IF EXISTS `asignaciones_horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaciones_horario` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asignaturas`
--

DROP TABLE IF EXISTS `asignaturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaturas` (
  `id_asignatura` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_asignatura`),
  UNIQUE KEY `uq_asignatura` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asistencias_diarias`
--

DROP TABLE IF EXISTS `asistencias_diarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencias_diarias` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendario_institucional`
--

DROP TABLE IF EXISTS `calendario_institucional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendario_institucional` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carga_academica`
--

DROP TABLE IF EXISTS `carga_academica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carga_academica` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargos` (
  `id_cargo` int unsigned NOT NULL AUTO_INCREMENT,
  `id_area` int unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_cargo`),
  KEY `fk_cargo_area` (`id_area`),
  CONSTRAINT `fk_cargo_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias_movimiento_financiero`
--

DROP TABLE IF EXISTS `categorias_movimiento_financiero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_movimiento_financiero` (
  `id_categoria` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('INGRESO','EGRESO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `clasificacion` enum('OPERATIVO','FINANCIAMIENTO','CAPITAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPERATIVO',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uq_categoria_movimiento` (`nombre`,`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cierres_asistencia`
--

DROP TABLE IF EXISTS `cierres_asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cierres_asistencia` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cierres_caja`
--

DROP TABLE IF EXISTS `cierres_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cierres_caja` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cierres_caja_medios`
--

DROP TABLE IF EXISTS `cierres_caja_medios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cierres_caja_medios` (
  `id_cierre_medio` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_cierre` bigint unsigned NOT NULL,
  `medio` enum('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `importe_sistema` decimal(12,2) NOT NULL DEFAULT '0.00',
  `importe_declarado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_cierre_medio`),
  UNIQUE KEY `uq_cierre_caja_medio` (`id_cierre`,`medio`),
  CONSTRAINT `fk_cierre_medio_cierre` FOREIGN KEY (`id_cierre`) REFERENCES `cierres_caja` (`id_cierre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `codigos_qr`
--

DROP TABLE IF EXISTS `codigos_qr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `codigos_qr` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conceptos_cobro`
--

DROP TABLE IF EXISTS `conceptos_cobro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conceptos_cobro` (
  `id_concepto_cobro` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('MATRICULA','MENSUALIDAD','TALLER','CUOTA','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `admite_parcial` tinyint(1) NOT NULL DEFAULT '1',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_concepto_cobro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conceptos_pago`
--

DROP TABLE IF EXISTS `conceptos_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conceptos_pago` (
  `id_concepto_pago` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `naturaleza` enum('INGRESO','DESCUENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('BASE','BONIFICACION','TARDANZA','FALTA','HORA','MANUAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_concepto_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cuentas_cobrar`
--

DROP TABLE IF EXISTS `cuentas_cobrar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_cobrar` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dispositivos_escaneo`
--

DROP TABLE IF EXISTS `dispositivos_escaneo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispositivos_escaneo` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grados`
--

DROP TABLE IF EXISTS `grados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grados` (
  `id_grado` int unsigned NOT NULL AUTO_INCREMENT,
  `id_nivel` int unsigned NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint unsigned NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_grado`),
  UNIQUE KEY `uq_grado` (`id_nivel`,`nombre`),
  CONSTRAINT `fk_grado_nivel` FOREIGN KEY (`id_nivel`) REFERENCES `niveles` (`id_nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incidencias_asistencia`
--

DROP TABLE IF EXISTS `incidencias_asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incidencias_asistencia` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institucion`
--

DROP TABLE IF EXISTS `institucion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institucion` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jornadas`
--

DROP TABLE IF EXISTS `jornadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jornadas` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marcaciones_asistencia`
--

DROP TABLE IF EXISTS `marcaciones_asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcaciones_asistencia` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `matriculas`
--

DROP TABLE IF EXISTS `matriculas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `matriculas` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movimientos_financieros`
--

DROP TABLE IF EXISTS `movimientos_financieros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_financieros` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `niveles`
--

DROP TABLE IF EXISTS `niveles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `niveles` (
  `id_nivel` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint unsigned NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos_alumno`
--

DROP TABLE IF EXISTS `pagos_alumno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_alumno` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos_alumno_detalles`
--

DROP TABLE IF EXISTS `pagos_alumno_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_alumno_detalles` (
  `id_pago_detalle` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pago` bigint unsigned NOT NULL,
  `id_cuenta` bigint unsigned NOT NULL,
  `importe` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id_pago_detalle`),
  KEY `fk_pago_det_pago` (`id_pago`),
  KEY `fk_pago_det_cuenta` (`id_cuenta`),
  CONSTRAINT `fk_pago_det_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas_cobrar` (`id_cuenta`),
  CONSTRAINT `fk_pago_det_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos_alumno` (`id_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos_alumno_medios`
--

DROP TABLE IF EXISTS `pagos_alumno_medios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_alumno_medios` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pagos_planilla`
--

DROP TABLE IF EXISTS `pagos_planilla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_planilla` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periodos_academicos`
--

DROP TABLE IF EXISTS `periodos_academicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos_academicos` (
  `id_periodo_academico` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('PLANIFICADO','ACTIVO','CERRADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PLANIFICADO',
  PRIMARY KEY (`id_periodo_academico`),
  UNIQUE KEY `uq_periodo_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periodos_pago`
--

DROP TABLE IF EXISTS `periodos_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos_pago` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id_permiso` int unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `uq_permiso` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `persona_perfiles`
--

DROP TABLE IF EXISTS `persona_perfiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persona_perfiles` (
  `id_persona_perfil` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_persona` bigint unsigned NOT NULL,
  `tipo` enum('ALUMNO','DOCENTE','ADMINISTRATIVO','APODERADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_persona_perfil`),
  UNIQUE KEY `uq_persona_perfil` (`id_persona`,`tipo`),
  CONSTRAINT `fk_perfil_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personas`
--

DROP TABLE IF EXISTS `personas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personas` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `planilla_ajustes`
--

DROP TABLE IF EXISTS `planilla_ajustes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planilla_ajustes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `planilla_detalle_conceptos`
--

DROP TABLE IF EXISTS `planilla_detalle_conceptos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planilla_detalle_conceptos` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `planilla_detalles`
--

DROP TABLE IF EXISTS `planilla_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planilla_detalles` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `bi_planilla_inicializar_saldo` BEFORE INSERT ON `planilla_detalles` FOR EACH ROW SET NEW.monto_pagado=0,NEW.saldo_pendiente=NEW.monto_pagar */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `bu_planilla_recalcular_saldo` BEFORE UPDATE ON `planilla_detalles` FOR EACH ROW SET NEW.saldo_pendiente=IF(NEW.estado='CALCULADO',GREATEST(0,NEW.monto_pagar-NEW.monto_pagado),NEW.saldo_pendiente) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `rectificaciones_caja`
--

DROP TABLE IF EXISTS `rectificaciones_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rectificaciones_caja` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rectificaciones_caja_medios`
--

DROP TABLE IF EXISTS `rectificaciones_caja_medios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rectificaciones_caja_medios` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reglas_descuento`
--

DROP TABLE IF EXISTS `reglas_descuento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reglas_descuento` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rol_permiso`
--

DROP TABLE IF EXISTS `rol_permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_permiso` (
  `id_rol` int unsigned NOT NULL,
  `id_permiso` int unsigned NOT NULL,
  PRIMARY KEY (`id_rol`,`id_permiso`),
  KEY `fk_rp_permiso` (`id_permiso`),
  CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_rol` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `secciones`
--

DROP TABLE IF EXISTS `secciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `secciones` (
  `id_seccion` int unsigned NOT NULL AUTO_INCREMENT,
  `id_grado` int unsigned NOT NULL,
  `nombre` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacidad` smallint unsigned DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_seccion`),
  UNIQUE KEY `uq_seccion` (`id_grado`,`nombre`),
  CONSTRAINT `fk_seccion_grado` FOREIGN KEY (`id_grado`) REFERENCES `grados` (`id_grado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tarifas_cobro`
--

DROP TABLE IF EXISTS `tarifas_cobro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tarifas_cobro` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuario_rol`
--

DROP TABLE IF EXISTS `usuario_rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_rol` (
  `id_usuario` bigint unsigned NOT NULL,
  `id_rol` int unsigned NOT NULL,
  PRIMARY KEY (`id_usuario`,`id_rol`),
  KEY `fk_ur_rol` (`id_rol`),
  CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vinculos_laborales`
--

DROP TABLE IF EXISTS `vinculos_laborales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vinculos_laborales` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'colegio'
--

--
-- Dumping routines for database 'colegio'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-22 20:00:57
INSERT INTO `roles` VALUES (64,'Super Administrador','ACTIVO'),(65,'Administrador','ACTIVO'),(66,'Director','ACTIVO'),(67,'Responsable de asistencia','ACTIVO'),(68,'Responsable de cobros','ACTIVO'),(69,'Responsable de planilla','ACTIVO'),(70,'Docente','ACTIVO'),(71,'Apoderado','ACTIVO'),(72,'Consulta','ACTIVO');
INSERT INTO `permisos` VALUES (208,'academico.gestionar','Gestionar estructura académica','ACTIVO'),(209,'academico.ver','Consultar estructura académica','ACTIVO'),(210,'alumnos.ver','Consultar alumnos','ACTIVO'),(211,'apoderados.ver','Consultar apoderados','ACTIVO'),(212,'asistencia.configurar','Configurar jornadas y dispositivos','ACTIVO'),(213,'asistencia.escanear','Registrar asistencia QR','ACTIVO'),(214,'asistencia.modificar','Modificar asistencia calculada','ACTIVO'),(215,'asistencia.ver','Consultar asistencia','ACTIVO'),(216,'auditoria.ver','Consultar auditoría','ACTIVO'),(217,'caja.rectificar','Rectificar y resolver diferencias de caja','ACTIVO'),(218,'cobros.gestionar','Gestionar cobros','ACTIVO'),(219,'configuracion.gestionar','Gestionar configuración','ACTIVO'),(220,'fotochecks.generar','Generar o renovar QR','ACTIVO'),(221,'fotochecks.ver','Consultar fotochecks','ACTIVO'),(222,'horarios.gestionar','Asignar jornadas y sesiones','ACTIVO'),(223,'horarios.ver','Consultar horarios','ACTIVO'),(224,'incidencias.gestionar','Registrar y resolver incidencias','ACTIVO'),(225,'incidencias.ver','Consultar incidencias','ACTIVO'),(226,'matriculas.crear','Registrar matrículas','ACTIVO'),(227,'matriculas.ver','Consultar matrículas','ACTIVO'),(228,'personas.crear','Registrar personas','ACTIVO'),(229,'personas.ver','Consultar personas','ACTIVO'),(230,'planilla.calcular','Calcular planilla','ACTIVO'),(231,'planilla.cerrar','Cerrar periodos de planilla','ACTIVO'),(232,'portal.ver','Consultar portal de familia','ACTIVO'),(233,'reportes.ver','Consultar reportes','ACTIVO'),(234,'usuarios.gestionar','Gestionar usuarios y sus roles','ACTIVO');
INSERT INTO `usuarios` VALUES (46,NULL,'administrador','$2y$10$BAEMzLwTJtqkcg6XBMYlUOPQrd9xRrqEMoh7raNluhGwSxolnfWN2','ACTIVO',NULL,'2026-08-22 19:54:20');
INSERT INTO `rol_permiso` VALUES (64,208),(65,208),(64,209),(65,209),(66,209),(67,209),(70,209),(72,209),(64,210),(65,210),(66,210),(67,210),(68,210),(70,210),(72,210),(64,211),(65,211),(66,211),(68,211),(72,211),(64,212),(65,212),(67,212),(64,213),(65,213),(67,213),(64,214),(65,214),(67,214),(64,215),(65,215),(66,215),(67,215),(69,215),(70,215),(72,215),(64,216),(65,216),(66,216),(69,216),(64,217),(65,217),(66,217),(64,218),(65,218),(68,218),(64,219),(65,219),(64,220),(65,220),(64,221),(65,221),(66,221),(67,221),(72,221),(64,222),(65,222),(67,222),(64,223),(65,223),(66,223),(67,223),(69,223),(70,223),(72,223),(64,224),(65,224),(66,224),(67,224),(64,225),(65,225),(66,225),(67,225),(69,225),(70,225),(72,225),(64,226),(65,226),(64,227),(65,227),(66,227),(67,227),(68,227),(70,227),(72,227),(64,228),(65,228),(64,229),(65,229),(66,229),(67,229),(68,229),(69,229),(72,229),(64,230),(65,230),(69,230),(64,231),(65,231),(69,231),(64,232),(65,232),(71,232),(64,233),(65,233),(66,233),(67,233),(68,233),(69,233),(70,233),(72,233),(64,234),(65,234);
INSERT INTO `usuario_rol` VALUES (46,64);
