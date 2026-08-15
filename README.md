# Colegio · Control de Asistencia y Pagos

Proyecto independiente basado visualmente en ESTTARI.

## Instalación inicial

1. Cree la base importando el archivo db/colegio.sql en MySQL.
2. Copie config/local.example.php como config/local.php y complete las credenciales locales.
3. Abra http://localhost/colegio/public/install.php desde el servidor local para crear el Super Administrador.
4. Ingrese por http://localhost/colegio/public/.
5. Después de instalar, elimine o bloquee public/install.php.

## Primera entrega

- Base MVC independiente.
- Sesiones seguras, CSRF en acciones principales y auditoría.
- Roles y permisos por acción.
- Personas con perfiles múltiples.
- Listados iniciales de alumnos y apoderados.
- Esquema de datos para asistencia QR/offline, cobros familiares y planilla.

Los módulos de matrícula, fotocheck QR, escaneo, cobros y planilla se desarrollarán por etapas.
