-- Ejecutar una vez en la base de datos colegio.
INSERT INTO permisos(clave,descripcion,estado) SELECT 'portal.ver','Consultar portal de familia','ACTIVO' WHERE NOT EXISTS(SELECT 1 FROM permisos WHERE clave='portal.ver');
INSERT INTO permisos(clave,descripcion,estado) SELECT 'usuarios.gestionar','Gestionar usuarios y sus roles','ACTIVO' WHERE NOT EXISTS(SELECT 1 FROM permisos WHERE clave='usuarios.gestionar');
INSERT INTO rol_permiso(id_rol,id_permiso) SELECT r.id_rol,p.id_permiso FROM roles r JOIN permisos p ON p.clave='portal.ver' WHERE r.nombre='Apoderado' AND NOT EXISTS(SELECT 1 FROM rol_permiso rp WHERE rp.id_rol=r.id_rol AND rp.id_permiso=p.id_permiso);
INSERT INTO rol_permiso(id_rol,id_permiso) SELECT r.id_rol,p.id_permiso FROM roles r JOIN permisos p ON p.clave='usuarios.gestionar' WHERE r.nombre='Super Administrador' AND NOT EXISTS(SELECT 1 FROM rol_permiso rp WHERE rp.id_rol=r.id_rol AND rp.id_permiso=p.id_permiso);

-- Corrección para instalaciones donde se ejecutó una versión anterior de este script.
DELETE rp FROM rol_permiso rp
INNER JOIN roles r ON r.id_rol=rp.id_rol
INNER JOIN permisos p ON p.id_permiso=rp.id_permiso
WHERE r.nombre='Super Administrador' AND p.clave='portal.ver';
