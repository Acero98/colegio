# Manual técnico y arquitectura

## Arquitectura

Aplicación PHP MVC sin framework principal:

```text
Navegador
  → public/index.php
  → routes/web.php
  → app/Controllers
  → app/Models y app/Services
  → MySQL / storage
  → app/Views
```

`public/index.php` inicia la sesión, carga configuración y seguridad, instala el manejador global de errores y delega al enrutador. `routes/web.php` resuelve `?url=...`, comprueba autenticación y llama al controlador.

## Directorios

| Ruta | Responsabilidad |
|---|---|
| `app/Controllers` | Autorización, validación de solicitud y navegación |
| `app/Models` | Consultas y reglas transaccionales |
| `app/Services` | QR y auditoría |
| `app/Views` | Interfaz e impresión |
| `config` | Configuración general y conexión local |
| `routes` | Mapa de rutas |
| `public` | Única superficie pública y recursos web |
| `storage` | Fotografías, QR, evidencias, logos y logs |
| `db` | Esquemas, migraciones, demostración y validadores |
| `vendor` | Dependencias Composer |

## Tecnologías

- PHP 7.4 compatible.
- MySQL 8.0 y PDO con prepares nativos.
- Tailwind CSS generado localmente.
- Alpine.js para estado del menú.
- DataTables para listados extensos.
- SweetAlert2 para confirmaciones.
- html5-qrcode para cámara.
- chillerlan/php-qrcode para PNG.

Los recursos principales se sirven localmente; no deben depender de CDN durante la operación normal.

## Configuración

`config/config.php` define nombre, zona horaria y ruta base. `config/local.php` retorna host, base, usuario y contraseña MySQL. Este último está ignorado por Git.

La conexión usa:

- `PDO::ERRMODE_EXCEPTION`
- `PDO::FETCH_ASSOC`
- `PDO::ATTR_EMULATE_PREPARES = false`
- `utf8mb4`

## Seguridad de solicitud

Los controladores llaman `autorizar('permiso')`. Las operaciones POST deben invocar `validarCsrf()`. Los mensajes transitorios se almacenan en sesión y se representan mediante SweetAlert.

El manejador de `app/Helpers/errores.php`:

1. Oculta errores técnicos del navegador.
2. Clasifica fallos de conexión PDO como HTTP 503.
3. Genera una referencia única.
4. Registra una traza sin argumentos en `storage/logs/app-errors.log`.
5. Representa una vista que no consulta la base de datos.

## Transacciones y trazabilidad

Cobros, caja, promociones, cálculo de planilla y otras operaciones compuestas utilizan transacciones. Los pagos y movimientos se anulan; las diferencias de caja se rectifican; los vínculos laborales se versionan. Esto preserva el historial utilizado por reportes.

No agregue eliminaciones físicas a una operación histórica sin analizar todas sus relaciones.

## Códigos internos

| Entidad | Formato |
|---|---|
| Alumno | `A-AÑO-0001` |
| Trabajador | `CS-AÑO-0001` |
| Recibo | `R-AÑO-000001` |
| Pago de planilla | `PL-AÑO-000001` |
| Movimiento financiero | `MF-AÑO-000001` |
| Error | `ERR-FECHA-HORA-ALEATORIO` |

Los correlativos deben calcularse dentro de una transacción o bloqueo cuando puedan existir operaciones simultáneas.

## Base de datos

La versión exportada contiene 50 tablas y dos triggers de saldo de planilla. Consulte `05_DICCIONARIO_DATOS.md` para su agrupación funcional.

Archivos de referencia:

- `db/colegio_limpio.sql`: despliegue nuevo.
- `db/colegio_demo.sql`: capacitación y pruebas.
- `db/colegioV01.sql`: respaldo histórico previo.
- `db/reiniciar_datos_demo.php`: reconstrucción controlada de la demostración.
- `db/validar_datos_demo.php`: comprobaciones de consistencia.

Para recrear la demostración:

```powershell
php db\reiniciar_datos_demo.php --simular
php db\reiniciar_datos_demo.php --confirmar
php db\validar_datos_demo.php
```

El modo `--confirmar` elimina datos y exige un respaldo válido; úselo exclusivamente en bases de demostración.

## Recursos persistentes

La base almacena los nombres de archivos, no su contenido. Al mover o restaurar el sistema debe copiarse `storage` junto con MySQL. Valide nombres mediante `basename`, tipos MIME y tamaños antes de aceptar archivos nuevos.

## Frontend

Para reconstruir Tailwind:

```powershell
npm install
npm run tailwind:build
```

`npm run tailwind:watch` se utiliza durante desarrollo. El sistema puede ejecutarse con los recursos compilados sin iniciar Node.js.

## Pruebas mínimas después de cambios

1. Ejecutar `php -l` sobre archivos PHP modificados.
2. Verificar `git diff --check`.
3. Probar inicio de sesión y permisos con más de un rol.
4. Ejecutar el flujo modificado con éxito y con datos inválidos.
5. Confirmar rollback cuando una operación compuesta falla.
6. Verificar listados, impresión y móvil.
7. Ejecutar validadores financieros o de demostración cuando corresponda.

## Despliegue de cambios de esquema

No importe nuevamente un esquema completo sobre producción. Cree una migración idempotente, respalde, pruébela sobre una copia y ejecútela una sola vez. Actualice después `colegio_limpio.sql`, `colegio_demo.sql` y este manual.

## Limitaciones técnicas conocidas

- La ejecución automática del cierre de asistencia requiere una tarea programada externa aún no incluida.
- Los recibos no integran facturación electrónica SUNAT.
- No existe envío real de WhatsApp/correo.
- La selección de dispositivo no está vinculada de forma exclusiva al equipo físico.
- No existe todavía un flujo completo de planilla complementaria después de pagos.
- Debe realizarse una auditoría de seguridad y carga antes del despliegue público.
