-- DATOS DE DEMOSTRACIÓN
-- Ejecute este archivo después de importar colegio.sql.
-- No lo use en producción con información real.

USE colegio;

INSERT INTO institucion (id_institucion, nombre, ruc, direccion, telefono, correo)
VALUES (1, 'I.E. Modelo San Martín', '20123456789', 'Av. Principal 123', '999 111 222', 'contacto@colegiomodelo.edu.pe')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), ruc=VALUES(ruc), direccion=VALUES(direccion), telefono=VALUES(telefono), correo=VALUES(correo);

INSERT IGNORE INTO periodos_academicos (nombre, fecha_inicio, fecha_fin, estado)
VALUES ('Año escolar 2026', '2026-03-02', '2026-12-18', 'ACTIVO');

INSERT IGNORE INTO niveles (nombre, orden, estado) VALUES
('Inicial', 1, 'ACTIVO'), ('Primaria', 2, 'ACTIVO'), ('Secundaria', 3, 'ACTIVO');

INSERT IGNORE INTO grados (id_nivel, nombre, orden, estado)
SELECT id_nivel, '3 años', 1, 'ACTIVO' FROM niveles WHERE nombre='Inicial';
INSERT IGNORE INTO grados (id_nivel, nombre, orden, estado)
SELECT id_nivel, '1.° grado', 1, 'ACTIVO' FROM niveles WHERE nombre='Primaria';
INSERT IGNORE INTO grados (id_nivel, nombre, orden, estado)
SELECT id_nivel, '2.° grado', 2, 'ACTIVO' FROM niveles WHERE nombre='Primaria';
INSERT IGNORE INTO grados (id_nivel, nombre, orden, estado)
SELECT id_nivel, '1.° año', 1, 'ACTIVO' FROM niveles WHERE nombre='Secundaria';

INSERT IGNORE INTO secciones (id_grado, nombre, capacidad, estado)
SELECT id_grado, 'A', 28, 'ACTIVO' FROM grados WHERE nombre='1.° grado';
INSERT IGNORE INTO secciones (id_grado, nombre, capacidad, estado)
SELECT id_grado, 'B', 28, 'ACTIVO' FROM grados WHERE nombre='1.° grado';
INSERT IGNORE INTO secciones (id_grado, nombre, capacidad, estado)
SELECT id_grado, 'A', 30, 'ACTIVO' FROM grados WHERE nombre='1.° año';

INSERT IGNORE INTO personas (tipo_documento,numero_documento,nombres,apellidos,telefono,correo,estado) VALUES
('DNI','70000001','Valeria','Quispe Flores','999100101','valeria.qusipe@demo.pe','ACTIVO'),
('DNI','70000002','Diego','Mamani Condori','999100102','diego.mamani@demo.pe','ACTIVO'),
('DNI','70000003','Sofía','Torres Salazar','999100103','sofia.torres@demo.pe','ACTIVO'),
('DNI','40000001','Elena','Flores Ramos','999200101','elena.flores@demo.pe','ACTIVO'),
('DNI','40000002','Carlos','Condori Huamán','999200102','carlos.condori@demo.pe','ACTIVO'),
('DNI','40000003','Rosa','Salazar Vega','999200103','rosa.salazar@demo.pe','ACTIVO'),
('DNI','30000001','María','Paredes Soto','999300101','maria.paredes@demo.pe','ACTIVO'),
('DNI','30000002','Jorge','Vargas León','999300102','jorge.vargas@demo.pe','ACTIVO');

INSERT IGNORE INTO persona_perfiles (id_persona,tipo,estado)
SELECT id_persona,'ALUMNO','ACTIVO' FROM personas WHERE numero_documento IN ('70000001','70000002','70000003');
INSERT IGNORE INTO persona_perfiles (id_persona,tipo,estado)
SELECT id_persona,'APODERADO','ACTIVO' FROM personas WHERE numero_documento IN ('40000001','40000002','40000003');
INSERT IGNORE INTO persona_perfiles (id_persona,tipo,estado)
SELECT id_persona,'DOCENTE','ACTIVO' FROM personas WHERE numero_documento='30000001';
INSERT IGNORE INTO persona_perfiles (id_persona,tipo,estado)
SELECT id_persona,'ADMINISTRATIVO','ACTIVO' FROM personas WHERE numero_documento='30000002';

INSERT IGNORE INTO alumno_apoderado (id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,contacto_emergencia,estado)
SELECT a.id_persona, p.id_persona, 'Madre', TRUE, TRUE, TRUE, 'ACTIVO'
FROM personas a CROSS JOIN personas p WHERE a.numero_documento='70000001' AND p.numero_documento='40000001';
INSERT IGNORE INTO alumno_apoderado (id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,contacto_emergencia,estado)
SELECT a.id_persona, p.id_persona, 'Padre', TRUE, TRUE, TRUE, 'ACTIVO'
FROM personas a CROSS JOIN personas p WHERE a.numero_documento='70000002' AND p.numero_documento='40000002';
INSERT IGNORE INTO alumno_apoderado (id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,contacto_emergencia,estado)
SELECT a.id_persona, p.id_persona, 'Madre', TRUE, TRUE, TRUE, 'ACTIVO'
FROM personas a CROSS JOIN personas p WHERE a.numero_documento='70000003' AND p.numero_documento='40000003';

INSERT IGNORE INTO matriculas (id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado)
SELECT p.id_persona, pa.id_periodo_academico, s.id_seccion, 'A-2026-0001', '2026-02-15', 'ACTIVO'
FROM personas p CROSS JOIN periodos_academicos pa
INNER JOIN secciones s ON s.nombre='A'
INNER JOIN grados g ON g.id_grado=s.id_grado AND g.nombre='1.° grado'
WHERE p.numero_documento='70000001' AND pa.nombre='Año escolar 2026';
INSERT IGNORE INTO matriculas (id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado)
SELECT p.id_persona, pa.id_periodo_academico, s.id_seccion, 'A-2026-0002', '2026-02-15', 'ACTIVO'
FROM personas p CROSS JOIN periodos_academicos pa
INNER JOIN secciones s ON s.nombre='B'
INNER JOIN grados g ON g.id_grado=s.id_grado AND g.nombre='1.° grado'
WHERE p.numero_documento='70000002' AND pa.nombre='Año escolar 2026';
INSERT IGNORE INTO matriculas (id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado)
SELECT p.id_persona, pa.id_periodo_academico, s.id_seccion, 'A-2026-0003', '2026-02-15', 'ACTIVO'
FROM personas p CROSS JOIN periodos_academicos pa
INNER JOIN secciones s ON s.nombre='A'
INNER JOIN grados g ON g.id_grado=s.id_grado AND g.nombre='1.° año'
WHERE p.numero_documento='70000003' AND pa.nombre='Año escolar 2026';

INSERT IGNORE INTO areas (nombre,estado) VALUES ('Académica','ACTIVO'),('Administración','ACTIVO');
INSERT IGNORE INTO cargos (id_area,nombre,estado)
SELECT id_area,'Docente de primaria','ACTIVO' FROM areas WHERE nombre='Académica';
INSERT IGNORE INTO cargos (id_area,nombre,estado)
SELECT id_area,'Asistente administrativo','ACTIVO' FROM areas WHERE nombre='Administración';

INSERT IGNORE INTO vinculos_laborales (id_persona,id_area,id_cargo,tipo,modalidad_pago,monto_base,fecha_inicio,estado)
SELECT p.id_persona,a.id_area,c.id_cargo,'DOCENTE','MENSUAL',2200.00,'2026-03-01','ACTIVO'
FROM personas p INNER JOIN areas a ON a.nombre='Académica' INNER JOIN cargos c ON c.nombre='Docente de primaria'
WHERE p.numero_documento='30000001';
INSERT IGNORE INTO vinculos_laborales (id_persona,id_area,id_cargo,tipo,modalidad_pago,monto_base,fecha_inicio,estado)
SELECT p.id_persona,a.id_area,c.id_cargo,'ADMINISTRATIVO','MENSUAL',1800.00,'2026-03-01','ACTIVO'
FROM personas p INNER JOIN areas a ON a.nombre='Administración' INNER JOIN cargos c ON c.nombre='Asistente administrativo'
WHERE p.numero_documento='30000002';

INSERT IGNORE INTO jornadas (nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,estado) VALUES
('Jornada alumnos mañana','ALUMNO','07:45:00','13:00:00',10,TRUE,'ACTIVO'),
('Jornada docentes mañana','DOCENTE','07:30:00','14:00:00',10,TRUE,'ACTIVO'),
('Jornada administrativos','ADMINISTRATIVO','08:00:00','17:00:00',10,TRUE,'ACTIVO');

INSERT IGNORE INTO conceptos_cobro (nombre,tipo,admite_parcial,estado) VALUES
('Matrícula 2026','MATRICULA',FALSE,'ACTIVO'),('Pensión mensual','MENSUALIDAD',TRUE,'ACTIVO'),('Taller de inglés','TALLER',TRUE,'ACTIVO');

INSERT IGNORE INTO periodos_pago (nombre,fecha_inicio,fecha_fin,estado) VALUES
('Marzo 2026','2026-03-01','2026-03-31','ABIERTO');

-- Para ver fotochecks, abra el módulo Fotochecks y genere QR para los alumnos de ejemplo.
