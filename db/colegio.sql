CREATE DATABASE IF NOT EXISTS colegio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE colegio;

CREATE TABLE institucion (
  id_institucion TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
  nombre VARCHAR(180) NOT NULL, ruc VARCHAR(20) NULL, direccion VARCHAR(250) NULL,
  telefono VARCHAR(30) NULL, correo VARCHAR(150) NULL, logo VARCHAR(250) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE personas (
  id_persona BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('DNI','CE','PASAPORTE','OTRO') NOT NULL DEFAULT 'DNI',
  numero_documento VARCHAR(25) NOT NULL, nombres VARCHAR(120) NOT NULL,
  apellidos VARCHAR(120) NOT NULL, fecha_nacimiento DATE NULL, sexo ENUM('F','M','OTRO') NULL,
  telefono VARCHAR(30) NULL, correo VARCHAR(150) NULL, direccion VARCHAR(250) NULL,
  fotografia VARCHAR(250) NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, actualizado_en DATETIME NULL,
  UNIQUE KEY uq_persona_documento (tipo_documento, numero_documento)
) ENGINE=InnoDB;

CREATE TABLE persona_perfiles (
  id_persona_perfil BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_persona BIGINT UNSIGNED NOT NULL,
  tipo ENUM('ALUMNO','DOCENTE','ADMINISTRATIVO','APODERADO') NOT NULL,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_persona_perfil (id_persona, tipo),
  CONSTRAINT fk_perfil_persona FOREIGN KEY (id_persona) REFERENCES personas(id_persona)
) ENGINE=InnoDB;

CREATE TABLE periodos_academicos (
  id_periodo_academico INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE NOT NULL,
  estado ENUM('PLANIFICADO','ACTIVO','CERRADO') NOT NULL DEFAULT 'PLANIFICADO',
  UNIQUE KEY uq_periodo_nombre (nombre)
) ENGINE=InnoDB;
CREATE TABLE niveles (id_nivel INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(80) NOT NULL, orden TINYINT UNSIGNED NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO');
CREATE TABLE grados (id_grado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_nivel INT UNSIGNED NOT NULL, nombre VARCHAR(80) NOT NULL, orden TINYINT UNSIGNED NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO', UNIQUE KEY uq_grado (id_nivel,nombre), CONSTRAINT fk_grado_nivel FOREIGN KEY(id_nivel) REFERENCES niveles(id_nivel));
CREATE TABLE secciones (id_seccion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_grado INT UNSIGNED NOT NULL, nombre VARCHAR(30) NOT NULL, capacidad SMALLINT UNSIGNED NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO', UNIQUE KEY uq_seccion(id_grado,nombre), CONSTRAINT fk_seccion_grado FOREIGN KEY(id_grado) REFERENCES grados(id_grado));

CREATE TABLE matriculas (
  id_matricula BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_persona BIGINT UNSIGNED NOT NULL, id_periodo_academico INT UNSIGNED NOT NULL,
  id_seccion INT UNSIGNED NOT NULL, codigo_alumno VARCHAR(30) NOT NULL,
  fecha_matricula DATE NOT NULL, estado ENUM('ACTIVO','RETIRADO','EGRESADO','ANULADO') NOT NULL DEFAULT 'ACTIVO',
  UNIQUE KEY uq_matricula_periodo_alumno(id_persona,id_periodo_academico),
  UNIQUE KEY uq_matricula_codigo(codigo_alumno),
  CONSTRAINT fk_matricula_alumno FOREIGN KEY(id_persona) REFERENCES personas(id_persona),
  CONSTRAINT fk_matricula_periodo FOREIGN KEY(id_periodo_academico) REFERENCES periodos_academicos(id_periodo_academico),
  CONSTRAINT fk_matricula_seccion FOREIGN KEY(id_seccion) REFERENCES secciones(id_seccion)
) ENGINE=InnoDB;

CREATE TABLE alumno_apoderado (
  id_alumno_apoderado BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_alumno BIGINT UNSIGNED NOT NULL, id_apoderado BIGINT UNSIGNED NOT NULL,
  parentesco VARCHAR(50) NOT NULL, es_principal BOOLEAN NOT NULL DEFAULT FALSE,
  responsable_economico BOOLEAN NOT NULL DEFAULT FALSE, contacto_emergencia BOOLEAN NOT NULL DEFAULT FALSE,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  UNIQUE KEY uq_alumno_apoderado(id_alumno,id_apoderado),
  CONSTRAINT fk_aa_alumno FOREIGN KEY(id_alumno) REFERENCES personas(id_persona),
  CONSTRAINT fk_aa_apoderado FOREIGN KEY(id_apoderado) REFERENCES personas(id_persona)
) ENGINE=InnoDB;

CREATE TABLE areas (id_area INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO', UNIQUE KEY uq_area_nombre(nombre));
CREATE TABLE cargos (id_cargo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_area INT UNSIGNED NULL, nombre VARCHAR(100) NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO', CONSTRAINT fk_cargo_area FOREIGN KEY(id_area) REFERENCES areas(id_area));
CREATE TABLE vinculos_laborales (
  id_vinculo BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_persona BIGINT UNSIGNED NOT NULL,
  id_area INT UNSIGNED NULL, id_cargo INT UNSIGNED NULL, tipo ENUM('DOCENTE','ADMINISTRATIVO') NOT NULL,
  modalidad_pago ENUM('MENSUAL','HORA','SESION') NOT NULL, monto_base DECIMAL(12,2) NOT NULL DEFAULT 0,
  fecha_inicio DATE NOT NULL, fecha_fin DATE NULL, estado ENUM('ACTIVO','INACTIVO','FINALIZADO') NOT NULL DEFAULT 'ACTIVO',
  CONSTRAINT fk_vinculo_persona FOREIGN KEY(id_persona) REFERENCES personas(id_persona),
  CONSTRAINT fk_vinculo_area FOREIGN KEY(id_area) REFERENCES areas(id_area),
  CONSTRAINT fk_vinculo_cargo FOREIGN KEY(id_cargo) REFERENCES cargos(id_cargo)
) ENGINE=InnoDB;
CREATE TABLE asignaturas (id_asignatura INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO', UNIQUE KEY uq_asignatura(nombre));
CREATE TABLE carga_academica (
  id_carga BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_vinculo BIGINT UNSIGNED NOT NULL,
  id_periodo_academico INT UNSIGNED NOT NULL, id_seccion INT UNSIGNED NOT NULL, id_asignatura INT UNSIGNED NOT NULL,
  dia_semana TINYINT UNSIGNED NOT NULL COMMENT '1=Lunes, 7=Domingo', hora_inicio TIME NOT NULL, hora_fin TIME NOT NULL,
  tolerancia_minutos SMALLINT UNSIGNED NOT NULL DEFAULT 0, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  CONSTRAINT fk_carga_vinculo FOREIGN KEY(id_vinculo) REFERENCES vinculos_laborales(id_vinculo),
  CONSTRAINT fk_carga_periodo FOREIGN KEY(id_periodo_academico) REFERENCES periodos_academicos(id_periodo_academico),
  CONSTRAINT fk_carga_seccion FOREIGN KEY(id_seccion) REFERENCES secciones(id_seccion),
  CONSTRAINT fk_carga_asignatura FOREIGN KEY(id_asignatura) REFERENCES asignaturas(id_asignatura)
) ENGINE=InnoDB;

CREATE TABLE jornadas (
  id_jornada INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL,
  aplica_a ENUM('ALUMNO','DOCENTE','ADMINISTRATIVO') NOT NULL,
  hora_entrada TIME NOT NULL, hora_salida TIME NOT NULL, tolerancia_minutos SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  requiere_salida BOOLEAN NOT NULL DEFAULT TRUE, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB;
CREATE TABLE asignaciones_horario (
  id_asignacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_persona BIGINT UNSIGNED NOT NULL,
  id_jornada INT UNSIGNED NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  CONSTRAINT fk_asighor_persona FOREIGN KEY(id_persona) REFERENCES personas(id_persona),
  CONSTRAINT fk_asighor_jornada FOREIGN KEY(id_jornada) REFERENCES jornadas(id_jornada)
) ENGINE=InnoDB;
CREATE TABLE calendario_institucional (
  id_calendario BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fecha DATE NOT NULL, tipo ENUM('FERIADO','NO_LABORABLE','SUSPENSION') NOT NULL,
  descripcion VARCHAR(200) NOT NULL, UNIQUE KEY uq_calendario_fecha_tipo(fecha,tipo)
) ENGINE=InnoDB;

CREATE TABLE codigos_qr (
  id_qr BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_persona BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL, archivo_qr VARCHAR(150) NOT NULL, version SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  estado ENUM('ACTIVO','REVOCADO','VENCIDO') NOT NULL DEFAULT 'ACTIVO',
  emitido_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, vence_en DATETIME NULL, revocado_en DATETIME NULL,
  UNIQUE KEY uq_qr_token(token_hash), KEY ix_qr_persona(id_persona),
  CONSTRAINT fk_qr_persona FOREIGN KEY(id_persona) REFERENCES personas(id_persona)
) ENGINE=InnoDB;
CREATE TABLE dispositivos_escaneo (
  id_dispositivo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, codigo VARCHAR(50) NOT NULL,
  token_hash CHAR(64) NOT NULL, punto_control VARCHAR(120) NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  ultimo_sync_en DATETIME NULL, UNIQUE KEY uq_dispositivo_codigo(codigo), UNIQUE KEY uq_dispositivo_token(token_hash)
) ENGINE=InnoDB;
CREATE TABLE marcaciones_asistencia (
  id_marcacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid_cliente CHAR(36) NOT NULL,
  id_persona BIGINT UNSIGNED NOT NULL, id_dispositivo INT UNSIGNED NULL, id_usuario BIGINT UNSIGNED NULL,
  fecha_hora_dispositivo DATETIME NOT NULL, fecha_hora_servidor DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tipo ENUM('ENTRADA','SALIDA','INICIO_REFRIGERIO','FIN_REFRIGERIO') NOT NULL,
  metodo ENUM('QR','MANUAL','SINCRONIZACION') NOT NULL, estado_validacion ENUM('VALIDA','DUPLICADA','ANULADA') NOT NULL DEFAULT 'VALIDA',
  observacion VARCHAR(250) NULL, KEY ix_marcacion_persona_fecha(id_persona,fecha_hora_dispositivo),
  UNIQUE KEY uq_marcacion_uuid(uuid_cliente),
  CONSTRAINT fk_marcacion_persona FOREIGN KEY(id_persona) REFERENCES personas(id_persona),
  CONSTRAINT fk_marcacion_dispositivo FOREIGN KEY(id_dispositivo) REFERENCES dispositivos_escaneo(id_dispositivo)
) ENGINE=InnoDB;
CREATE TABLE asistencias_diarias (
  id_asistencia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_persona BIGINT UNSIGNED NOT NULL, fecha DATE NOT NULL,
  id_jornada INT UNSIGNED NULL, id_matricula BIGINT UNSIGNED NULL, id_vinculo BIGINT UNSIGNED NULL, id_carga BIGINT UNSIGNED NULL,
  hora_entrada TIME NULL, hora_salida TIME NULL, minutos_tardanza SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  minutos_salida_anticipada SMALLINT UNSIGNED NOT NULL DEFAULT 0, minutos_trabajados SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  estado ENUM('PUNTUAL','TARDANZA','FALTA','JUSTIFICADO','PERMISO','LICENCIA','SALIDA_ANTICIPADA','INCOMPLETO','FERIADO','NO_LABORABLE','SUSPENDIDO','ANULADO') NOT NULL,
  observacion VARCHAR(250) NULL, calculado_en DATETIME NULL,
  UNIQUE KEY uq_asistencia_alcance(id_persona,fecha,id_jornada,id_matricula,id_vinculo,id_carga),
  KEY ix_asistencia_fecha_estado(fecha,estado),
  CONSTRAINT fk_asistencia_persona FOREIGN KEY(id_persona) REFERENCES personas(id_persona)
) ENGINE=InnoDB;
CREATE TABLE incidencias_asistencia (
  id_incidencia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_asistencia BIGINT UNSIGNED NOT NULL,
  tipo ENUM('JUSTIFICACION_FALTA','JUSTIFICACION_TARDANZA','PERMISO','LICENCIA','OBSERVACION') NOT NULL,
  motivo VARCHAR(500) NOT NULL, estado ENUM('PENDIENTE','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  registrado_por BIGINT UNSIGNED NULL, resuelto_por BIGINT UNSIGNED NULL, creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, resuelto_en DATETIME NULL,
  CONSTRAINT fk_incidencia_asistencia FOREIGN KEY(id_asistencia) REFERENCES asistencias_diarias(id_asistencia)
) ENGINE=InnoDB;

CREATE TABLE conceptos_cobro (
  id_concepto_cobro INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL,
  tipo ENUM('MATRICULA','MENSUALIDAD','TALLER','CUOTA','OTRO') NOT NULL, admite_parcial BOOLEAN NOT NULL DEFAULT TRUE,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB;
CREATE TABLE tarifas_cobro (
  id_tarifa BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_concepto_cobro INT UNSIGNED NOT NULL,
  id_periodo_academico INT UNSIGNED NOT NULL, id_grado INT UNSIGNED NULL, importe DECIMAL(12,2) NOT NULL,
  vigencia_inicio DATE NOT NULL, vigencia_fin DATE NULL, CONSTRAINT fk_tarifa_concepto FOREIGN KEY(id_concepto_cobro) REFERENCES conceptos_cobro(id_concepto_cobro),
  CONSTRAINT fk_tarifa_periodo FOREIGN KEY(id_periodo_academico) REFERENCES periodos_academicos(id_periodo_academico)
) ENGINE=InnoDB;
CREATE TABLE cuentas_cobrar (
  id_cuenta BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_matricula BIGINT UNSIGNED NOT NULL, id_concepto_cobro INT UNSIGNED NOT NULL,
  periodo_cobro DATE NOT NULL, fecha_vencimiento DATE NULL, importe_original DECIMAL(12,2) NOT NULL, descuento DECIMAL(12,2) NOT NULL DEFAULT 0,
  saldo DECIMAL(12,2) NOT NULL, estado ENUM('PENDIENTE','PARCIAL','PAGADA','ANULADA','VENCIDA') NOT NULL DEFAULT 'PENDIENTE',
  UNIQUE KEY uq_cuenta(id_matricula,id_concepto_cobro,periodo_cobro),
  CONSTRAINT fk_cuenta_matricula FOREIGN KEY(id_matricula) REFERENCES matriculas(id_matricula),
  CONSTRAINT fk_cuenta_concepto FOREIGN KEY(id_concepto_cobro) REFERENCES conceptos_cobro(id_concepto_cobro)
) ENGINE=InnoDB;
CREATE TABLE pagos_alumno (
  id_pago BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, numero_recibo VARCHAR(30) NOT NULL,
  id_apoderado BIGINT UNSIGNED NULL, fecha_pago DATETIME NOT NULL, medio_pago ENUM('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','OTRO') NOT NULL,
  importe_total DECIMAL(12,2) NOT NULL, estado ENUM('REGISTRADO','ANULADO') NOT NULL DEFAULT 'REGISTRADO',
  registrado_por BIGINT UNSIGNED NULL, observacion VARCHAR(250) NULL, UNIQUE KEY uq_recibo(numero_recibo),
  CONSTRAINT fk_pago_apoderado FOREIGN KEY(id_apoderado) REFERENCES personas(id_persona)
) ENGINE=InnoDB;
CREATE TABLE pagos_alumno_detalles (
  id_pago_detalle BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_pago BIGINT UNSIGNED NOT NULL, id_cuenta BIGINT UNSIGNED NOT NULL, importe DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_pago_det_pago FOREIGN KEY(id_pago) REFERENCES pagos_alumno(id_pago),
  CONSTRAINT fk_pago_det_cuenta FOREIGN KEY(id_cuenta) REFERENCES cuentas_cobrar(id_cuenta)
) ENGINE=InnoDB;

CREATE TABLE periodos_pago (
  id_periodo_pago INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE NOT NULL,
  estado ENUM('ABIERTO','CALCULADO','REVISADO','CERRADO','PAGADO') NOT NULL DEFAULT 'ABIERTO', UNIQUE KEY uq_periodo_pago(nombre)
) ENGINE=InnoDB;
CREATE TABLE conceptos_pago (
  id_concepto_pago INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL,
  naturaleza ENUM('INGRESO','DESCUENTO') NOT NULL, tipo ENUM('BASE','BONIFICACION','TARDANZA','FALTA','HORA','MANUAL') NOT NULL,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB;
CREATE TABLE reglas_descuento (
  id_regla INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL,
  evento ENUM('TARDANZA','FALTA','SALIDA_ANTICIPADA','HORA_NO_TRABAJADA') NOT NULL,
  formula JSON NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB;
CREATE TABLE planilla_detalles (
  id_planilla_detalle BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_periodo_pago INT UNSIGNED NOT NULL, id_vinculo BIGINT UNSIGNED NOT NULL,
  monto_base DECIMAL(12,2) NOT NULL, bonificaciones DECIMAL(12,2) NOT NULL DEFAULT 0, descuentos DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_pagar DECIMAL(12,2) NOT NULL, estado ENUM('CALCULADO','REVISADO','CERRADO','PAGADO') NOT NULL DEFAULT 'CALCULADO',
  calculado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_planilla_trabajador(id_periodo_pago,id_vinculo),
  CONSTRAINT fk_planilla_periodo FOREIGN KEY(id_periodo_pago) REFERENCES periodos_pago(id_periodo_pago),
  CONSTRAINT fk_planilla_vinculo FOREIGN KEY(id_vinculo) REFERENCES vinculos_laborales(id_vinculo)
) ENGINE=InnoDB;
CREATE TABLE planilla_detalle_conceptos (
  id_detalle_concepto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_planilla_detalle BIGINT UNSIGNED NOT NULL,
  id_concepto_pago INT UNSIGNED NOT NULL, id_regla INT UNSIGNED NULL, cantidad DECIMAL(12,2) NOT NULL DEFAULT 1,
  importe DECIMAL(12,2) NOT NULL, explicacion JSON NULL,
  CONSTRAINT fk_pdc_planilla FOREIGN KEY(id_planilla_detalle) REFERENCES planilla_detalles(id_planilla_detalle),
  CONSTRAINT fk_pdc_concepto FOREIGN KEY(id_concepto_pago) REFERENCES conceptos_pago(id_concepto_pago),
  CONSTRAINT fk_pdc_regla FOREIGN KEY(id_regla) REFERENCES reglas_descuento(id_regla)
) ENGINE=InnoDB;

CREATE TABLE roles (id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(80) NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO', UNIQUE KEY uq_rol(nombre));
CREATE TABLE permisos (id_permiso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, clave VARCHAR(100) NOT NULL, descripcion VARCHAR(180) NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO', UNIQUE KEY uq_permiso(clave));
CREATE TABLE usuarios (
  id_usuario BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_persona BIGINT UNSIGNED NULL, usuario VARCHAR(80) NOT NULL,
  contrasena VARCHAR(255) NOT NULL, estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  ultimo_acceso_en DATETIME NULL, creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuario(usuario), UNIQUE KEY uq_usuario_persona(id_persona), CONSTRAINT fk_usuario_persona FOREIGN KEY(id_persona) REFERENCES personas(id_persona)
) ENGINE=InnoDB;
CREATE TABLE usuario_rol (id_usuario BIGINT UNSIGNED NOT NULL, id_rol INT UNSIGNED NOT NULL, PRIMARY KEY(id_usuario,id_rol), CONSTRAINT fk_ur_usuario FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario), CONSTRAINT fk_ur_rol FOREIGN KEY(id_rol) REFERENCES roles(id_rol));
CREATE TABLE rol_permiso (id_rol INT UNSIGNED NOT NULL, id_permiso INT UNSIGNED NOT NULL, PRIMARY KEY(id_rol,id_permiso), CONSTRAINT fk_rp_rol FOREIGN KEY(id_rol) REFERENCES roles(id_rol), CONSTRAINT fk_rp_permiso FOREIGN KEY(id_permiso) REFERENCES permisos(id_permiso));
CREATE TABLE auditoria (
  id_auditoria BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, id_usuario BIGINT UNSIGNED NULL, accion VARCHAR(80) NOT NULL,
  entidad VARCHAR(80) NOT NULL, id_entidad BIGINT UNSIGNED NULL, valores_antes JSON NULL, valores_despues JSON NULL,
  ip VARCHAR(45) NULL, creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY ix_auditoria_entidad(entidad,id_entidad), KEY ix_auditoria_fecha(creado_en),
  CONSTRAINT fk_auditoria_usuario FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

INSERT INTO roles(nombre) VALUES ('Super Administrador'),('Administrador'),('Director'),('Responsable de asistencia'),('Responsable de cobros'),('Responsable de planilla'),('Docente'),('Apoderado'),('Consulta');
INSERT INTO permisos(clave,descripcion) VALUES
('personas.ver','Consultar personas'),('personas.crear','Registrar personas'),('alumnos.ver','Consultar alumnos'),('apoderados.ver','Consultar apoderados'),('academico.ver','Consultar estructura académica'),('academico.gestionar','Gestionar estructura académica'),('matriculas.ver','Consultar matrículas'),('matriculas.crear','Registrar matrículas'),('fotochecks.ver','Consultar fotochecks'),('fotochecks.generar','Generar o renovar QR'),('asistencia.ver','Consultar asistencia'),('asistencia.escanear','Registrar asistencia QR'),('asistencia.configurar','Configurar jornadas y dispositivos'),
('horarios.ver','Consultar horarios'),('horarios.gestionar','Asignar jornadas y sesiones'),
('incidencias.ver','Consultar incidencias'),('incidencias.gestionar','Registrar y resolver incidencias'),
('asistencia.modificar','Modificar asistencia calculada'),('cobros.gestionar','Gestionar cobros'),('planilla.calcular','Calcular planilla'),('planilla.cerrar','Cerrar periodos de planilla'),('reportes.ver','Consultar reportes'),('auditoria.ver','Consultar auditoría'),('configuracion.gestionar','Gestionar configuración');
INSERT INTO rol_permiso(id_rol,id_permiso) SELECT 1,id_permiso FROM permisos;
INSERT INTO institucion(id_institucion,nombre) VALUES(1,'Institución educativa') ON DUPLICATE KEY UPDATE nombre=nombre;
