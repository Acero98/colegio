# Colegio · Gestión académica, asistencia y finanzas

Sistema web para administrar personas, estructura académica, matrículas, fotochecks QR, asistencia, cobros, caja, movimientos financieros, planilla, reportes, usuarios y permisos.

## Estado del proyecto

El sistema dispone de los módulos funcionales principales y una base demostrativa integral. Antes de utilizarlo con información real debe completarse la lista de verificación de producción, definir responsables y probar los flujos con las reglas del colegio.

## Requisitos principales

- Apache 2.4 con PHP 7.4 o superior.
- MySQL 8.0.
- Extensiones PHP: PDO MySQL, mbstring, GD y JSON.
- Composer para la biblioteca de códigos QR.
- Navegador moderno con cámara y HTTPS cuando se escanee desde otro equipo.

## Inicio rápido

1. Cree una base MySQL vacía denominada `colegio`.
2. Importe uno de estos archivos, nunca ambos:
   - `db/colegio_limpio.sql`: estructura completa y un único Super Administrador.
   - `db/colegio_demo.sql`: estructura y casos demostrativos para todos los módulos.
3. Copie `config/local.example.php` como `config/local.php` y complete la conexión.
4. Ejecute `composer install` si la carpeta `vendor` no está disponible.
5. Configure Apache para que `public` sea el `DocumentRoot` recomendado.
6. Abra `http://colegio.test/` o la URL local configurada.

El SQL limpio conserva el usuario y la contraseña del Super Administrador con los que fue exportado. El SQL demostrativo incorpora usuarios terminados en `.demo` con la contraseña común `Demo2026*`.

## Documentación

- [Instalación y puesta en marcha](docs/01_INSTALACION.md)
- [Manual de usuario y flujos](docs/02_MANUAL_USUARIO.md)
- [Administración, permisos y seguridad](docs/03_ADMINISTRACION_SEGURIDAD.md)
- [Manual técnico y arquitectura](docs/04_MANUAL_TECNICO.md)
- [Diccionario funcional de datos](docs/05_DICCIONARIO_DATOS.md)
- [Guía de datos demostrativos](db/GUIA_DATOS_DEMO.md)

## Seguridad básica

- `config/local.php` contiene las credenciales locales y está excluido de Git.
- El acceso directo a `config`, `db`, `storage`, `vendor` y `.git` está bloqueado desde la raíz.
- Las operaciones sensibles utilizan sesión, permisos, CSRF y auditoría.
- Los errores técnicos no se muestran al usuario. Se consultan en `storage/logs/app-errors.log` mediante su código de referencia.
- Los pagos y movimientos no se borran: se anulan o rectifican conservando trazabilidad.

## Alcances que deben completarse antes de producción

- Automatizar mediante una tarea del servidor el cierre de asistencia de las 23:00. El cierre manual ya está disponible y la base admite cierres automáticos.
- Integrar comprobantes electrónicos tributarios si se requiere SUNAT; los recibos actuales son constancias internas.
- Implementar envío real de alertas por WhatsApp o correo si se contrata ese canal.
- Vincular cada dispositivo físico mediante un token exclusivo; actualmente el operador selecciona un dispositivo activo.
- Definir el procedimiento de planilla complementaria cuando un período ya tiene operaciones de pago y no puede reabrirse.
- Ejecutar pruebas de aceptación, copias de seguridad y restauración antes de cargar información real.
