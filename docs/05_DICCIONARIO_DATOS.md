# Diccionario funcional de datos

La base está compuesta por 50 tablas. Este documento explica su responsabilidad; para columnas, tipos, índices y claves exactas consulte `db/colegio_limpio.sql`.

## Institución y personas

| Tabla | Contenido |
|---|---|
| `institucion` | Identidad, RUC, dirección, representante y logo |
| `personas` | Identificación única, contacto, fotografía y código de trabajador |
| `persona_perfiles` | Perfiles alumno, apoderado, docente o administrativo |
| `alumno_apoderado` | Parentesco, responsable económico y contacto de emergencia |
| `areas` | Áreas laborales |
| `cargos` | Cargos asociados a áreas |
| `vinculos_laborales` | Tipo, modalidad, importes, días y vigencia laboral |

## Académico

| Tabla | Contenido |
|---|---|
| `periodos_academicos` | Años escolares planificados, activos o cerrados |
| `niveles` | Inicial, primaria, secundaria u otros |
| `grados` | Grados ordenados dentro del nivel |
| `secciones` | Aulas y capacidad máxima |
| `matriculas` | Alumno, período, sección, código y estado |
| `asignaturas` | Cursos o materias |
| `carga_academica` | Sesiones docentes por sección, curso, día y hora |

## Asistencia

| Tabla | Contenido |
|---|---|
| `jornadas` | Entrada, salida, tolerancia y obligatoriedad de salida |
| `asignaciones_horario` | Jornada individual y vigencia |
| `calendario_institucional` | Feriados, no laborables y suspensiones |
| `codigos_qr` | Token cifrado, archivo, vigencia y estado |
| `dispositivos_escaneo` | Lectores disponibles y punto de control |
| `marcaciones_asistencia` | Lecturas crudas QR o manuales |
| `asistencias_diarias` | Resultado calculado diario de alumno o trabajador |
| `incidencias_asistencia` | Solicitudes y resolución de justificaciones |
| `cierres_asistencia` | Cierre manual/automático por fecha y alcance |

## Cobros y caja

| Tabla | Contenido |
|---|---|
| `conceptos_cobro` | Matrícula, pensión y demás conceptos |
| `tarifas_cobro` | Importe por período y opcionalmente grado |
| `cuentas_cobrar` | Deuda original, descuentos, recargos y saldo |
| `ajustes_cobro` | Beca, descuento, exoneración o recargo |
| `pagos_alumno` | Cabecera del recibo |
| `pagos_alumno_detalles` | Distribución del pago entre cuentas |
| `pagos_alumno_medios` | Distribución por efectivo o medios digitales |
| `aperturas_caja` | Turnos, monto inicial y responsable |
| `cierres_caja` | Totales esperados/declarados y diferencia |
| `cierres_caja_medios` | Conciliación de cada medio |
| `rectificaciones_caja` | Corrección, justificación o regularización |
| `rectificaciones_caja_medios` | Valores anteriores y nuevos por medio |

## Ingresos y egresos

| Tabla | Contenido |
|---|---|
| `categorias_movimiento_financiero` | Categoría operativa, financiamiento o capital |
| `movimientos_financieros` | Ingresos y egresos ajenos a pagos de alumnos |

## Planilla

| Tabla | Contenido |
|---|---|
| `periodos_pago` | Mes y estado del proceso de planilla |
| `conceptos_pago` | Base, horas, bonos y descuentos |
| `reglas_descuento` | Fórmulas y vigencia de reglas automáticas |
| `planilla_detalles` | Neto, pagado, saldo y decisión por trabajador |
| `planilla_detalle_conceptos` | Desglose del cálculo |
| `planilla_ajustes` | Ajustes manuales activos o anulados |
| `pagos_planilla` | Pagos, constancias y anulaciones |

## Seguridad

| Tabla | Contenido |
|---|---|
| `usuarios` | Credencial, persona y estado |
| `roles` | Perfiles de acceso |
| `permisos` | Acciones autorizables |
| `usuario_rol` | Roles asignados a cada usuario |
| `rol_permiso` | Permisos incluidos en el rol |
| `auditoria` | Trazabilidad transversal de acciones |

## Relaciones relevantes

```text
personas ─┬─ persona_perfiles
          ├─ matriculas ─ secciones ─ grados ─ niveles
          ├─ alumno_apoderado
          ├─ vinculos_laborales ─ cargos/areas
          ├─ codigos_qr
          └─ asistencias_diarias

matriculas ─ cuentas_cobrar ─ pagos_alumno_detalles ─ pagos_alumno
pagos_alumno ─ pagos_alumno_medios

aperturas_caja ─┬─ pagos_alumno
                ├─ movimientos_financieros
                └─ cierres_caja ─ rectificaciones_caja

vinculos_laborales ─ planilla_detalles ─ pagos_planilla
```

## Reglas de conservación

- No elimine personas para cambiar su perfil.
- No elimine matrículas históricas ni deudas pagadas.
- No elimine recibos, pagos o movimientos; utilice anulación.
- No sobrescriba un vínculo laboral histórico; cree una nueva versión.
- No modifique silenciosamente una caja cerrada; utilice rectificación.
- No altere directamente una planilla cerrada o pagada.
