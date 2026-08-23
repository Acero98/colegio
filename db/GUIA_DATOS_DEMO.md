# Guía de datos demostrativos

Esta base contiene información ficticia y fue preparada para explicar el funcionamiento integral del sistema al cliente. El año, los meses y las fechas cercanas se calculan al ejecutar `reiniciar_datos_demo.php`, por lo que las demostraciones siempre quedan alineadas con el período actual.

## Accesos

El usuario **Super Administrador conserva exactamente su usuario y contraseña anteriores**.

Los siguientes accesos usan la contraseña común `Demo2026*`:

| Usuario | Rol | Uso sugerido |
|---|---|---|
| `director.demo` | Director | Consultas académicas, asistencia y reportes |
| `asistencia.demo` | Responsable de asistencia | QR, registro manual, incidencias y horarios |
| `caja.demo` | Responsable de cobros | Caja, cobros, recibos y estado de cuenta |
| `planilla.demo` | Responsable de planilla | Vínculos, cálculo, cierre y pagos |
| `docente.demo` | Docente | Consulta de sus operaciones académicas |
| `apoderado.demo` | Apoderado | Portal familiar |

## Casos preparados por módulo

### Personas y seguridad

- Personas activas e inactivas.
- Alumno, apoderado, docente y administrativo.
- Persona con dos perfiles activos: docente y administrativo.
- Exalumno que posteriormente se convierte en trabajador.
- Trabajador que cambia de administrativo a docente sin duplicar a la persona.
- Usuarios con permisos separados por función.

### Académico y matrículas

- Inicial, primaria y secundaria con todos sus grados.
- Secciones con distintas capacidades; `Primaria · 1.° grado A` está llena.
- Matrículas activas, retiradas, anuladas y egresadas.
- Alumno promovido y alumno repitente entre el año anterior y el actual.
- Período anterior cerrado, actual activo y siguiente planificado para probar la promoción.

### Fotocheck y asistencia

- QR activos reales para alumnos, docentes y administrativos.
- QR revocado y QR vencido.
- Jornada escolar con salida opcional.
- Jornadas individuales de docentes y administrativos con salida requerida.
- Sesiones docentes por horas.
- Puntualidad, tardanza posterior a tolerancia, falta, justificación, permiso, licencia, salida anticipada, registro incompleto y anulado.
- Incidencias pendientes, aprobadas y rechazadas, con responsable, fecha y observación de resolución. Dirección autoriza permisos/licencias y Asistencia resuelve justificaciones.
- Marcaciones QR y manuales, además de lecturas duplicadas y anuladas.
- Incidencias pendientes, aprobadas y rechazadas.

### Finanzas

- Matrícula, pensión, taller, movilidad, material, fotocheck y cuota extraordinaria.
- Conceptos que admiten y no admiten pagos parciales.
- Tarifas generales y una tarifa específica por grado.
- Cuentas pendientes, parciales, pagadas, vencidas y anuladas.
- Deudas con mora de 1–30, 31–60 y más de 60 días.
- Deuda del año anterior pagada parcialmente en el año actual.
- Descuento, beca, exoneración y recargo.
- Recibo que distribuye un pago entre dos cuentas.
- Pago anulado sin alterar el saldo vigente.
- Dos turnos cerrados el mismo día, uno con diferencia demostrativa, y un turno actual abierto.

### Planilla

- Vínculos mensuales, por hora, por sesión y mixtos.
- Cambio laboral versionado y trabajador cesado.
- Días laborables, jornadas, sesiones y calendario institucional.
- Feriado, día no laborable, suspensión y registro de calendario inactivo.
- Reglas automáticas de tardanza, falta, salida anticipada y bono fijo.
- Período anterior completamente pagado, período actual cerrado con pagos totales, parciales y pendientes, y período siguiente abierto.
- Ajuste activo y ajuste anulado.
- Pago de planilla anulado y constancias disponibles desde el historial.

## Alumno recomendado para explicar finanzas

- **Ana Torres Mendoza**: matrícula pagada y pensión con pago parcial.
- **Carla Ruiz Salas**: deuda del año anterior con abono realizado actualmente.
- **Diego Pérez Núñez**: beca y recargo por mora.
- **José Vega Ríos**: deuda mayor a 60 días y cuenta exonerada.

## Verificación y regeneración

Antes de regenerar, el proceso exige que exista `db/colegioV01.sql` como respaldo.

```bash
php db/reiniciar_datos_demo.php --simular
php db/reiniciar_datos_demo.php --confirmar
php db/validar_datos_demo.php
```

La simulación valida todas las relaciones dentro de una transacción y revierte los cambios. La confirmación reemplaza los datos activos y la validación comprueba saldos, pagos, caja, planilla, accesos y archivos QR.
