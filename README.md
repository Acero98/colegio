# Colegio · Control de Asistencia y Pagos

Proyecto independiente basado visualmente en ESTTARI.

## Instalación inicial

1. Cree la base importando el archivo db/colegio.sql en MySQL.
2. Copie config/local.example.php como config/local.php y complete las credenciales locales.
3. Abra http://localhost/colegio/public/install.php desde el servidor local para crear el Super Administrador.
4. Ingrese por http://localhost/colegio/public/.
5. Después de instalar, elimine o bloquee public/install.php.

### Datos de demostración

Para cargar una institución ficticia, tres alumnos, tres apoderados, personal y matrículas, importe después db/datos_ejemplo.sql. No use ese archivo en producción.

Si ya había importado una versión anterior del esquema, ejecute antes db/actualizar_primera_entrega.sql.

## Primera entrega

- Base MVC independiente.
- Sesiones seguras, CSRF en acciones principales y auditoría.
- Roles y permisos por acción.
- Personas con perfiles múltiples.
- Listados iniciales de alumnos y apoderados.
- Fotocheck CR80 con QR seguro y renovable.
- Esquema de datos para asistencia QR/offline, cobros familiares y planilla.

Los módulos de matrícula, fotocheck QR, escaneo, cobros y planilla se desarrollarán por etapas.
