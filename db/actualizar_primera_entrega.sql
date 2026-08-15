-- Ejecute este archivo SOLO si ya importó una versión anterior de colegio.sql.
-- Si importará colegio.sql desde cero, no lo necesita.

USE colegio;

ALTER TABLE codigos_qr
    ADD COLUMN archivo_qr VARCHAR(150) NOT NULL DEFAULT '' AFTER token_hash;

INSERT IGNORE INTO permisos(clave,descripcion) VALUES
('academico.ver','Consultar estructura académica'),
('academico.gestionar','Gestionar estructura académica'),
('matriculas.ver','Consultar matrículas'),
('matriculas.crear','Registrar matrículas'),
('fotochecks.ver','Consultar fotochecks'),
('fotochecks.generar','Generar o renovar QR');

INSERT IGNORE INTO rol_permiso(id_rol,id_permiso)
SELECT r.id_rol,p.id_permiso
FROM roles r CROSS JOIN permisos p
WHERE r.nombre='Super Administrador'
AND p.clave IN ('academico.ver','academico.gestionar','matriculas.ver','matriculas.crear','fotochecks.ver','fotochecks.generar');
