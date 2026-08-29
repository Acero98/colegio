# Manual de usuario y flujos

## Objetivo

El sistema centraliza la información de la comunidad educativa y conecta los procesos académicos, asistencia, cobranzas, caja y planilla. La persona se registra una sola vez y puede adquirir varios perfiles durante su relación con el colegio.

## Orden recomendado de configuración

1. Datos institucionales.
2. Usuarios, roles y permisos.
3. Período académico, niveles, grados y secciones.
4. Personas, perfiles y vínculos laborales.
5. Matrículas y relaciones con apoderados.
6. Jornadas, sesiones, dispositivos y calendario.
7. Fotochecks y QR.
8. Conceptos, tarifas y cuentas por cobrar.
9. Reglas y períodos de planilla.

## Navegación

El menú lateral muestra únicamente módulos autorizados. Puede contraerse para mostrar solo iconos; la preferencia se conserva en el navegador. En teléfonos se abre con el botón de menú y el cierre de sesión permanece disponible en la cabecera.

## 1. Personas

### Registro único

Antes de crear una persona, búsquela por documento. No debe crearse una copia cuando cambie de función. Una misma persona puede ser, por ejemplo:

- Alumno que luego se convierte en trabajador.
- Docente y administrativo simultáneamente.
- Administrativo que pasa a docente.
- Apoderado que además trabaja en el colegio.

Los perfiles se activan o desactivan sobre el mismo registro. La fotografía también puede reemplazarse al editar.

### Códigos

- El código del alumno pertenece a su matrícula y sigue el formato `A-AÑO-NÚMERO`.
- El código del trabajador pertenece a la persona y sigue el criterio `CS-AÑO-NÚMERO`.
- El cargo y la modalidad de pago pertenecen al vínculo laboral, no al perfil personal.

Los listados de alumnos, docentes, administrativos y apoderados son vistas especializadas de la misma base de personas.

## 2. Estructura académica

La jerarquía es:

```text
Período académico → Nivel → Grado → Sección
```

El período define el año escolar. Los niveles agrupan grados y cada sección puede tener una capacidad máxima. El sistema impide promover o matricular por encima de esa capacidad.

Los registros históricos se desactivan o cierran; no conviene borrarlos porque son utilizados por matrículas, asistencia, cobros y reportes.

## 3. Matrículas y promoción

Una matrícula vincula alumno, período, sección, fecha y código de alumno. El alumno puede tener historial en varios años, pero no debe duplicarse dentro del mismo período.

### Cambio de sección durante el año

Edite la matrícula activa y seleccione la sección correspondiente. El historial del período se conserva en la misma matrícula.

### Fin de año

1. Cree el período siguiente como planificado.
2. Cree sus secciones y capacidades.
3. Abra **Promoción pendiente**.
4. Filtre todo el colegio o un grado/sección.
5. Revise el destino recomendado, que corresponde al grado inmediatamente siguiente.
6. Marque una decisión por alumno:
   - Promover: pasa al siguiente grado.
   - Repetir: permanece en el mismo grado y se elige una sección de ese grado.
   - Egresar: finaliza el último grado.
   - Retirar: no continuará en la institución.
7. Confirme la operación.

“Continuar” y “Repetir” son decisiones excluyentes porque producen destinos académicos distintos.

## 4. Fotochecks

El fotocheck está disponible para alumnos, docentes y administrativos. Toma los datos institucionales, fotografía, identificación, código, cargo o sección y un QR renovable.

Al generar un QR nuevo, el anterior queda revocado. Un código vencido, revocado o perteneciente a una persona inactiva no debe registrar asistencia.

Antes de imprimir, compruebe fotografía, datos del colegio, tamaño del QR y período. La vista previa está adaptada al formato de impresión, pero la impresora debe usar escala 100 % y sin encabezados del navegador.

## 5. Asistencia

### Reglas previas

- Los alumnos utilizan una jornada general de su grupo y la salida puede ser opcional.
- Docentes y administrativos necesitan una jornada individual o una sesión docente programada.
- Una persona sin jornada o sesión válida no puede marcar asistencia aunque tenga QR.
- La tolerancia proviene de la jornada o sesión. Solo los minutos posteriores a esa tolerancia se consideran tardanza descontable.

### Escaneo QR

1. Abra **Asistencia → Escanear QR**.
2. Seleccione un dispositivo activo.
3. Permita el acceso a la cámara.
4. Escanee el fotocheck.
5. Verifique la confirmación mostrada por el sistema.

Las lecturas repetidas se identifican como duplicadas. Actualmente el dispositivo se selecciona en pantalla; la vinculación exclusiva de cada equipo mediante token físico es una mejora pendiente.

### Registro manual

Se utiliza cuando el alumno o trabajador no pudo escanear. Seleccione fecha, grupo o persona, tipo de registro y observación. Toda modificación requiere permiso y queda auditada.

### Estados principales

| Estado | Significado |
|---|---|
| Puntual | Entrada dentro de la hora y tolerancia |
| Tardanza | Entrada posterior a la tolerancia |
| Falta | No existe entrada en un día exigible |
| Incompleto | Personal con entrada pero sin salida requerida |
| Salida anticipada | Salida anterior a la hora programada |
| Justificado | Falta aceptada mediante incidencia |
| Permiso o licencia | Ausencia autorizada |
| Anulado | Registro invalidado con trazabilidad |

Una tardanza aprobada puede seguir mostrando la condición “Tardanza” como hecho horario y, adicionalmente, la justificación aprobada. Esto conserva lo ocurrido y evita aplicar una consecuencia injustificada.

### Incidencias

Se registran para fechas donde existe una ausencia, tardanza u observación susceptible de justificación. No corresponde justificar una asistencia puntual. Dirección o el responsable con `incidencias.gestionar` aprueba o rechaza indicando una observación.

La bandeja principal muestra pendientes; aprobadas y rechazadas se consultan mediante historial o filtros. Un período de planilla ya cerrado limita cambios que alteren el cálculo del personal.

### Cierre

El cierre de alumnos genera faltas faltantes. El cierre de personal genera faltas e incompletos según jornada o sesión. No se permiten fechas futuras y se registra fecha, hora y responsable.

El cierre manual está operativo. La base admite el tipo automático, pero la tarea de las 23:00 debe configurarse en el servidor antes de considerarlo automático en producción.

## 6. Finanzas y cobros

### Configuración

1. Cree conceptos: matrícula, mensualidad, taller, movilidad, materiales u otros.
2. Indique si admiten pagos parciales.
3. Cree tarifas generales o específicas por grado y período.
4. Active únicamente las tarifas vigentes.

### Generación de cuentas

Genere las cuentas para un mes, un grupo o un alumno. El sistema busca matrículas y tarifas vigentes, evita duplicados y conserva el período original de cada deuda.

Una deuda del año anterior se paga sobre su cuenta histórica. No se traslada ni se vuelve a crear en el año actual.

### Apertura de caja

Debe existir una única caja abierta para registrar cobros, ingresos o egresos, sin importar el medio. Puede abrir y cerrar varios turnos en el mismo día, pero nunca dos simultáneamente.

### Registrar un pago

1. Busque al alumno en **Estado de cuenta**.
2. Revise deudas actuales y anteriores.
3. Distribuya el importe entre una o varias cuentas.
4. Agregue uno o varios medios: efectivo, Yape, Plin, transferencia, tarjeta, depósito u otro.
5. En efectivo, registre cuánto entregó el cliente; el sistema calcula el vuelto.
6. En medios digitales, registre referencia o evidencia fotográfica.
7. Confirme y emita el recibo interno.

La suma aplicada a las deudas debe coincidir con la suma de los medios. Un concepto que no admite pagos parciales debe cancelarse completamente.

### Recibos y anulaciones

Los recibos son constancias internas correlativas y pueden imprimirse desde **Recibos**. No son comprobantes electrónicos SUNAT. Los pagos erróneos se anulan con motivo y responsable; el saldo de las cuentas se restaura automáticamente.

### Ingresos y egresos

Permiten registrar operaciones distintas de las pensiones: aportes de capital, préstamos, compras, alquiler, servicios y otras categorías. Todos requieren caja abierta y quedan asociados al turno. Los digitales necesitan referencia o evidencia.

### Cierre y rectificación de caja

El sistema calcula por cada medio:

```text
Monto esperado = apertura + ingresos y cobros − egresos
Diferencia = monto declarado − monto esperado
```

El cajero declara lo contado o verificado. Una diferencia no se edita silenciosamente: un usuario autorizado registra rectificación, justificación o regularización, con motivo, responsable y evidencia cuando corresponda.

## 7. Planilla

### Preparación

1. Cree áreas y cargos.
2. Registre el vínculo laboral y su vigencia.
3. Defina modalidad mensual, por hora, por sesión o mixta.
4. Asigne jornadas o carga académica.
5. Complete feriados, días no laborables y suspensiones.
6. Configure reglas de tardanza, falta, salida anticipada, bonos y ajustes.

### Cálculo

El motor toma vínculo, días laborables, calendario, jornadas, sesiones y asistencia. En modalidad mensual calcula la base proporcional y descuentos; en horas/sesiones paga lo cumplido; en modalidad mixta suma ambos componentes.

Flujo de estados:

```text
Abierto → Calculado → Revisado → Cerrado → Pagado o Finalizado
```

Antes de aprobar, revise monto base, horas, tardanzas, faltas, bonificaciones, descuentos y neto. Los ajustes manuales exigen motivo y quedan auditados.

### Pagos

Una planilla cerrada admite pagos totales o parciales y genera constancias `PL-AÑO-NÚMERO`. También puede marcarse un trabajador como retenido o excluido con motivo.

La planilla solo puede reabrirse si no existe ninguna operación de pago, incluso anulada. Si ya existen pagos, la corrección debe realizarse mediante ajustes o un procedimiento de planilla complementaria; este último debe definirse antes de producción.

## 8. Reportes

- Reportes generales académicos y de asistencia.
- Estado de cuenta, vencimientos y morosidad.
- Cobros por concepto y medio.
- Caja por turnos y diferencias.
- Ingresos, egresos, resultado operativo y flujo neto.
- Costo y pagos de planilla.

Los reportes respetan los filtros seleccionados. Antes de entregar cifras oficiales, confirme período, fechas y estado de los registros.

## 9. Cierre de sesión

Use siempre **Cerrar sesión**, especialmente en caja y equipos compartidos. No comparta usuarios: la identidad individual es necesaria para auditoría, aprobaciones, cierres y anulaciones.
