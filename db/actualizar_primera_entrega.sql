-- Ejecute este archivo SOLO si ya importó una versión anterior de colegio.sql.
-- Si importará colegio.sql desde cero, no lo necesita.

USE colegio;

-- MySQL 8.0.30 no admite ADD COLUMN IF NOT EXISTS en todos los entornos.
-- Por ello se valida la columna antes de crearla.
SET @existe_archivo_qr := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'codigos_qr'
      AND COLUMN_NAME = 'archivo_qr'
);
SET @sql_archivo_qr := IF(
    @existe_archivo_qr = 0,
    'ALTER TABLE codigos_qr ADD COLUMN archivo_qr VARCHAR(150) NOT NULL DEFAULT '''' AFTER token_hash',
    'SELECT 1'
);
PREPARE sentencia_archivo_qr FROM @sql_archivo_qr;
EXECUTE sentencia_archivo_qr;
DEALLOCATE PREPARE sentencia_archivo_qr;

INSERT IGNORE INTO permisos(clave,descripcion) VALUES
('academico.ver','Consultar estructura académica'),
('academico.gestionar','Gestionar estructura académica'),
('matriculas.ver','Consultar matrículas'),
('matriculas.crear','Registrar matrículas'),
('fotochecks.ver','Consultar fotochecks'),
('fotochecks.generar','Generar o renovar QR');
INSERT IGNORE INTO permisos(clave,descripcion) VALUES
('asistencia.ver','Consultar asistencia'),
('asistencia.escanear','Registrar asistencia QR'),
('asistencia.configurar','Configurar jornadas y dispositivos');
INSERT IGNORE INTO permisos(clave,descripcion) VALUES
('horarios.ver','Consultar horarios'),
('horarios.gestionar','Asignar jornadas y sesiones');
INSERT IGNORE INTO permisos(clave,descripcion) VALUES
('incidencias.ver','Consultar incidencias'),
('incidencias.gestionar','Registrar y resolver incidencias');

INSERT IGNORE INTO rol_permiso(id_rol,id_permiso)
SELECT r.id_rol,p.id_permiso
FROM roles r CROSS JOIN permisos p
WHERE r.nombre='Super Administrador'
AND p.clave IN ('academico.ver','academico.gestionar','matriculas.ver','matriculas.crear','fotochecks.ver','fotochecks.generar','asistencia.ver','asistencia.escanear','asistencia.configurar','horarios.ver','horarios.gestionar','incidencias.ver','incidencias.gestionar');
