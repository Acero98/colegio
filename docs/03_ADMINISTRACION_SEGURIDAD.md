# Administración, permisos y seguridad

## Principio general

Cada trabajador debe tener su propia cuenta. El rol determina qué módulos puede ver y cada acción sensible vuelve a comprobar su permiso en el servidor; ocultar un botón no es la única protección.

## Roles incluidos

| Rol | Responsabilidad sugerida |
|---|---|
| Super Administrador | Configuración completa, seguridad y mantenimiento |
| Administrador | Operación general delegada |
| Director | Supervisión académica, incidencias, reportes y revisión |
| Responsable de asistencia | QR, registros, cierres, incidencias y horarios |
| Responsable de cobros | Caja, cuentas, pagos, recibos y movimientos |
| Responsable de planilla | Vínculos, cálculo, revisión, cierre y pagos |
| Docente | Consulta académica y de asistencia según permisos asignados |
| Apoderado | Portal familiar |
| Consulta | Lectura de información autorizada |

Los roles son configurables. Aplique el principio de mínimo privilegio: conceda solo lo necesario para el trabajo habitual.

## Permisos funcionales

| Grupo | Permisos principales |
|---|---|
| Personas | `personas.ver`, `personas.crear`, `alumnos.ver`, `apoderados.ver` |
| Académico | `academico.ver`, `academico.gestionar`, `matriculas.ver`, `matriculas.crear` |
| Fotocheck | `fotochecks.ver`, `fotochecks.generar` |
| Asistencia | `asistencia.ver`, `asistencia.escanear`, `asistencia.modificar`, `asistencia.configurar` |
| Horarios | `horarios.ver`, `horarios.gestionar` |
| Incidencias | `incidencias.ver`, `incidencias.gestionar` |
| Finanzas | `cobros.gestionar`, `caja.rectificar` |
| Planilla | `planilla.calcular`, `planilla.cerrar` |
| Reportes | `reportes.ver`, `auditoria.ver` |
| Sistema | `configuracion.gestionar`, `usuarios.gestionar` |
| Familia | `portal.ver` |

## Creación de usuarios

1. Registre primero la persona si la cuenta pertenece a un trabajador.
2. Abra **Seguridad → Usuarios**.
3. Seleccione la persona, nombre de usuario y rol.
4. Utilice una contraseña temporal segura y solicite cambiarla.
5. Desactive la cuenta al terminar la relación laboral; no la elimine.

El Super Administrador puede existir sin una persona asociada. Una persona solo puede tener una cuenta de usuario.

## Controles implementados

- Contraseñas almacenadas mediante `password_hash`.
- Sesiones con cookie `HttpOnly` y `SameSite=Lax`.
- Regeneración del identificador al iniciar sesión.
- Tokens CSRF en formularios sensibles.
- Consultas preparadas PDO.
- Permisos comprobados en controladores.
- Auditoría de operaciones relevantes.
- Anulación y desactivación en lugar de borrado de operaciones.
- Página segura para errores 403, 404, 419, 500 y 503.
- Archivos sensibles bloqueados fuera de `public`.

## Auditoría

La tabla `auditoria` identifica usuario, acción, entidad, registro, valores anteriores/posteriores, IP y fecha. Debe revisarse ante cambios no reconocidos, anulaciones, cierres o ajustes.

No reutilice cuentas como “Usuario” o “Caja”. Si dos cajeros comparten credenciales, el historial no permite determinar quién realizó cada operación.

## Registro de errores

El usuario recibe un código parecido a:

```text
ERR-20260822-184530-A3F912
```

El técnico lo busca en:

```text
storage/logs/app-errors.log
```

PowerShell:

```powershell
Get-Content storage\logs\app-errors.log -Tail 30
Get-Content storage\logs\app-errors.log -Wait
Select-String -Path storage\logs\app-errors.log -Pattern "ERR-20260822-184530-A3F912"
```

No active `display_errors` en un servidor accesible por usuarios. El log excluye los argumentos de la traza para evitar exponer contraseñas.

## Respuesta ante incidentes

1. Conserve el código de referencia, usuario, fecha y ruta.
2. Realice un respaldo antes de corregir datos.
3. Revise auditoría y log técnico.
4. Reproduzca el problema en una copia, no en producción.
5. Corrija mediante la operación funcional correspondiente: incidencia, anulación, ajuste o rectificación.
6. Documente la decisión y vuelva a validar saldos o estados.

## Recomendaciones de producción

- HTTPS obligatorio fuera de `localhost`.
- Contraseñas únicas y rotación al cambiar responsables.
- Usuario MySQL sin privilegios de administración global.
- Copia diaria de base y `storage`.
- Actualizaciones probadas primero en una copia.
- Restricción de acceso a respaldos SQL.
- Política de conservación de evidencias y datos personales.
- Revisión periódica de usuarios activos, roles y dispositivos.
