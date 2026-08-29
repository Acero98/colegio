<?php
$parametros = http_build_query([
    'url' => 'reportes_generales_imprimir',
    'anio' => $anio,
    'periodo' => $periodo['id_periodo_academico'] ?? 0,
    'seccion' => $idSeccion,
    'desde' => $desde,
    'hasta' => $hasta,
]);
$colorPorcentaje = static function ($valor): string {
    if ($valor >= 90) return 'bg-emerald-100 text-emerald-800';
    if ($valor >= 75) return 'bg-amber-100 text-amber-800';
    return 'bg-rose-100 text-rose-800';
};
$coloresEstado = ['PENDIENTE' => 'bg-amber-100 text-amber-800', 'APROBADA' => 'bg-emerald-100 text-emerald-800', 'RECHAZADA' => 'bg-rose-100 text-rose-800'];
?>

<div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-sm font-semibold text-indigo-600">Información institucional</p>
        <h1 class="text-2xl font-bold">Reportes generales</h1>
        <p class="mt-1 text-sm text-slate-500">Matrículas, asistencia de alumnos, asistencia del personal e incidencias.</p>
    </div>
    <div class="flex flex-wrap gap-2"><?php if (tienePermiso('cobros.gestionar')): ?><a href="index.php?url=reportes_financieros" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800"><i class="bi bi-cash-stack"></i> Finanzas</a><?php endif; ?><?php if (tienePermiso('planilla.calcular')): ?><a href="index.php?url=planilla_reporte" class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-800"><i class="bi bi-wallet2"></i> Planilla</a><?php endif; ?><a target="_blank" href="index.php?<?= htmlspecialchars($parametros) ?>" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"><i class="bi bi-printer"></i> Imprimir</a></div>
</div>

<?php if (!$periodo): ?>
    <div class="rounded-2xl border border-amber-300 bg-amber-100 p-5 text-amber-900">Debe registrar al menos un período académico para generar reportes.</div>
<?php else: ?>
    <form method="get" class="mb-5 grid gap-3 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 sm:grid-cols-2 xl:grid-cols-5">
        <input type="hidden" name="url" value="reportes_generales">
        <label class="form-label">Año<select name="anio" class="form-input"><?php foreach ($anios as $opcion): ?><option value="<?= $opcion ?>" <?= $anio === $opcion ? 'selected' : '' ?>><?= $opcion ?></option><?php endforeach; ?></select></label>
        <label class="form-label">Período académico<select name="periodo" class="form-input"><?php foreach ($periodos as $opcion): ?><option value="<?= (int)$opcion['id_periodo_academico'] ?>" <?= (int)$periodo['id_periodo_academico'] === (int)$opcion['id_periodo_academico'] ? 'selected' : '' ?>><?= htmlspecialchars($opcion['nombre'] . ' · ' . $opcion['estado']) ?></option><?php endforeach; ?></select></label>
        <label class="form-label">Nivel / grado / sección<select name="seccion" class="form-input">
                <option value="0">Todas las secciones</option><?php foreach ($secciones as $opcion): ?><option value="<?= (int)$opcion['id_seccion'] ?>" <?= $idSeccion === (int)$opcion['id_seccion'] ? 'selected' : '' ?>><?= htmlspecialchars($opcion['nombre']) ?></option><?php endforeach; ?>
            </select></label>
        <label class="form-label">Desde<input type="date" name="desde" min="<?= htmlspecialchars($periodo['fecha_inicio']) ?>" max="<?= htmlspecialchars($periodo['fecha_fin']) ?>" value="<?= htmlspecialchars($desde) ?>" class="form-input"></label>
        <label class="form-label">Hasta<input type="date" name="hasta" min="<?= htmlspecialchars($periodo['fecha_inicio']) ?>" max="<?= htmlspecialchars($periodo['fecha_fin']) ?>" value="<?= htmlspecialchars($hasta) ?>" class="form-input"></label>
        <div class="flex flex-wrap gap-2 sm:col-span-2 xl:col-span-5"><button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white">Aplicar filtros</button><a href="index.php?url=reportes_generales" class="rounded-xl border border-indigo-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700">Limpiar</a></div>
    </form>

    <section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <article class="rounded-2xl border border-blue-200 bg-blue-100 p-4">
            <p class="text-xs font-bold uppercase text-blue-700">Matriculados</p>
            <p class="mt-1 text-2xl font-bold text-blue-950"><?= number_format($kpis['matriculados']) ?></p>
        </article>
        <article class="rounded-2xl border border-cyan-200 bg-cyan-100 p-4">
            <p class="text-xs font-bold uppercase text-cyan-700">Capacidad definida</p>
            <p class="mt-1 text-2xl font-bold text-cyan-950"><?= number_format($kpis['capacidad']) ?></p>
        </article>
        <article class="rounded-2xl border border-teal-200 bg-teal-100 p-4">
            <p class="text-xs font-bold uppercase text-teal-700">Vacantes</p>
            <p class="mt-1 text-2xl font-bold text-teal-950"><?= number_format($kpis['vacantes']) ?></p>
        </article>
        <article class="rounded-2xl border border-emerald-200 bg-emerald-100 p-4">
            <p class="text-xs font-bold uppercase text-emerald-700">Asistencia alumnos</p>
            <p class="mt-1 text-2xl font-bold text-emerald-950"><?= number_format($kpis['asistencia_alumnos'], 1) ?>%</p>
        </article>
        <article class="rounded-2xl border border-violet-200 bg-violet-100 p-4">
            <p class="text-xs font-bold uppercase text-violet-700">Personal reportado</p>
            <p class="mt-1 text-2xl font-bold text-violet-950"><?= number_format($kpis['personal_reportado']) ?></p>
        </article>
        <article class="rounded-2xl border border-amber-200 bg-amber-100 p-4">
            <p class="text-xs font-bold uppercase text-amber-700">Incidencias pendientes</p>
            <p class="mt-1 text-2xl font-bold text-amber-950"><?= number_format($kpis['incidencias_pendientes']) ?></p>
        </article>
    </section>

    <nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border bg-white p-3 text-sm font-semibold"><a href="#reporte-matriculas" class="rounded-xl bg-blue-100 px-4 py-2 text-blue-800">Matrículas</a><a href="#reporte-asistencia-alumnos" class="rounded-xl bg-emerald-100 px-4 py-2 text-emerald-800">Asistencia de alumnos</a><a href="#reporte-asistencia-personal" class="rounded-xl bg-violet-100 px-4 py-2 text-violet-800">Asistencia de personal</a><a href="#reporte-incidencias" class="rounded-xl bg-amber-100 px-4 py-2 text-amber-800">Incidencias</a></nav>

    <section id="reporte-matriculas" class="mb-6 scroll-mt-5 overflow-hidden rounded-2xl border bg-white">
        <div class="border-b p-5">
            <h2 class="font-bold">Matrículas por nivel, grado y sección</h2>
            <p class="text-sm text-slate-500"><?= htmlspecialchars($periodo['nombre']) ?>. La capacidad sin límite no se suma al total de vacantes.</p>
        </div>
        <div class="overflow-x-auto">
            <table data-datatable data-page-options="10,25,50" class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="p-4">Nivel</th>
                        <th class="p-4">Grado</th>
                        <th class="p-4">Sección</th>
                        <th class="p-4">Capacidad</th>
                        <th class="p-4">Activos</th>
                        <th class="p-4">Retirados</th>
                        <th class="p-4">Egresados</th>
                        <th class="p-4">Anulados</th>
                        <th class="p-4">Vacantes</th>
                        <th class="p-4">Ocupación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matriculas as $fila): $capacidad = $fila['capacidad'] === null ? null : (int)$fila['capacidad'];
                        $ocupacion = $capacidad ? ((int)$fila['activos'] / $capacidad) * 100 : 0; ?><tr class="border-t">
                            <td class="p-4"><?= htmlspecialchars($fila['nivel']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($fila['grado']) ?></td>
                            <td class="p-4 font-semibold"><?= htmlspecialchars($fila['seccion']) ?></td>
                            <td class="p-4"><?= $capacidad === null ? 'Sin límite' : $capacidad ?></td>
                            <td class="p-4 font-semibold text-blue-800"><?= (int)$fila['activos'] ?></td>
                            <td class="p-4 text-amber-700"><?= (int)$fila['retirados'] ?></td>
                            <td class="p-4 text-violet-700"><?= (int)$fila['egresados'] ?></td>
                            <td class="p-4 text-slate-500"><?= (int)$fila['anulados'] ?></td>
                            <td class="p-4 font-semibold text-teal-700"><?= $capacidad === null ? '—' : max(0, $capacidad - (int)$fila['activos']) ?></td>
                            <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $capacidad ? $colorPorcentaje($ocupacion) : 'bg-slate-100 text-slate-700' ?>"><?= $capacidad ? number_format($ocupacion, 1) . '%' : 'No aplica' ?></span></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div><?php if (!$matriculas): ?><div class="border-t p-8 text-center text-slate-500">No existen secciones para el filtro seleccionado.</div><?php endif; ?>
    </section>

    <section id="reporte-asistencia-alumnos" class="mb-6 scroll-mt-5 space-y-5">
        <article class="overflow-hidden rounded-2xl border bg-white">
            <div class="border-b p-5">
                <h2 class="font-bold">Resumen de asistencia por sección</h2>
                <p class="text-sm text-slate-500">Porcentaje calculado sobre jornadas registradas entre <?= htmlspecialchars($desde) ?> y <?= htmlspecialchars($hasta) ?>.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">Nivel / grado / sección</th>
                            <th class="p-4">Alumnos</th>
                            <th class="p-4">Jornadas</th>
                            <th class="p-4">Asistencias</th>
                            <th class="p-4">Tardanzas</th>
                            <th class="p-4">Faltas</th>
                            <th class="p-4">Justificadas</th>
                            <th class="p-4">Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($asistenciaSecciones as $fila): ?><tr class="border-t">
                                <td class="p-4 font-semibold"><?= htmlspecialchars($fila['seccion']) ?></td>
                                <td class="p-4"><?= $fila['alumnos'] ?></td>
                                <td class="p-4"><?= $fila['jornadas'] ?></td>
                                <td class="p-4 text-emerald-700"><?= $fila['asistencias'] ?></td>
                                <td class="p-4 text-amber-700"><?= $fila['tardanzas'] ?></td>
                                <td class="p-4 text-rose-700"><?= $fila['faltas'] ?></td>
                                <td class="p-4 text-blue-700"><?= $fila['justificadas'] ?></td>
                                <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $colorPorcentaje($fila['porcentaje']) ?>"><?= number_format($fila['porcentaje'], 1) ?>%</span></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div><?php if (!$asistenciaSecciones): ?><div class="border-t p-8 text-center text-slate-500">No hay alumnos matriculados para generar el resumen.</div><?php endif; ?>
        </article>
        <article class="overflow-hidden rounded-2xl border bg-white">
            <div class="border-b p-5">
                <h3 class="font-bold">Detalle por alumno</h3>
                <p class="text-sm text-slate-500">Incluye alumnos activos, retirados y egresados del período; excluye matrículas anuladas.</p>
            </div>
            <div class="overflow-x-auto">
                <table data-datatable data-page-options="10,25,50,100" class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">Alumno</th>
                            <th class="p-4">Nivel / grado / sección</th>
                            <th class="p-4">Matrícula</th>
                            <th class="p-4">Jornadas</th>
                            <th class="p-4">Puntual</th>
                            <th class="p-4">Tardanzas</th>
                            <th class="p-4">Faltas</th>
                            <th class="p-4">Justificadas</th>
                            <th class="p-4">Min. tardanza</th>
                            <th class="p-4">Asistencia</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($asistenciaAlumnos as $fila): ?><tr class="border-t">
                                <td class="p-4 font-semibold"><?= htmlspecialchars($fila['alumno']) ?><small class="block text-slate-500"><?= htmlspecialchars($fila['codigo_alumno'] . ' · ' . $fila['numero_documento']) ?></small></td>
                                <td class="p-4"><?= htmlspecialchars($fila['nivel'] . ' · ' . $fila['grado'] . ' ' . $fila['seccion']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($fila['estado_matricula']) ?></td>
                                <td class="p-4"><?= (int)$fila['jornadas'] ?></td>
                                <td class="p-4 text-emerald-700"><?= (int)$fila['puntuales'] ?></td>
                                <td class="p-4 text-amber-700"><?= (int)$fila['tardanzas'] ?></td>
                                <td class="p-4 text-rose-700"><?= (int)$fila['faltas'] ?></td>
                                <td class="p-4 text-blue-700"><?= (int)$fila['justificadas'] ?></td>
                                <td class="p-4"><?= (int)$fila['minutos_tardanza'] ?></td>
                                <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $colorPorcentaje($fila['porcentaje']) ?>"><?= number_format($fila['porcentaje'], 1) ?>%</span></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </article>
    </section>

    <section id="reporte-asistencia-personal" class="mb-6 scroll-mt-5 overflow-hidden rounded-2xl border bg-white">
        <div class="border-b p-5">
            <h2 class="font-bold">Asistencia de docentes y administrativos</h2>
            <p class="text-sm text-slate-500">Consolida jornadas individuales y sesiones docentes dentro del rango seleccionado.</p>
        </div>
        <div class="overflow-x-auto">
            <table data-datatable data-page-options="10,25,50" class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="p-4">Trabajador</th>
                        <th class="p-4">Tipo / cargo</th>
                        <th class="p-4">Jornadas</th>
                        <th class="p-4">Puntual</th>
                        <th class="p-4">Tardanzas</th>
                        <th class="p-4">Faltas</th>
                        <th class="p-4">Justificadas</th>
                        <th class="p-4">Incompletas</th>
                        <th class="p-4">Min. tardanza</th>
                        <th class="p-4">Horas trabajadas</th>
                        <th class="p-4">Asistencia</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($asistenciaPersonal as $fila): ?><tr class="border-t">
                            <td class="p-4 font-semibold"><?= htmlspecialchars($fila['trabajador']) ?><small class="block text-slate-500"><?= htmlspecialchars($fila['codigo_trabajador'] ?: 'Sin código') ?></small></td>
                            <td class="p-4"><?= htmlspecialchars(str_replace('_', ' ', $fila['tipo'])) ?><small class="block text-slate-500"><?= htmlspecialchars($fila['cargo']) ?></small></td>
                            <td class="p-4"><?= (int)$fila['jornadas'] ?></td>
                            <td class="p-4 text-emerald-700"><?= (int)$fila['puntuales'] ?></td>
                            <td class="p-4 text-amber-700"><?= (int)$fila['tardanzas'] ?></td>
                            <td class="p-4 text-rose-700"><?= (int)$fila['faltas'] ?></td>
                            <td class="p-4 text-blue-700"><?= (int)$fila['justificadas'] ?></td>
                            <td class="p-4 text-orange-700"><?= (int)$fila['incompletas'] ?></td>
                            <td class="p-4"><?= (int)$fila['minutos_tardanza'] ?></td>
                            <td class="p-4"><?= number_format((int)$fila['minutos_trabajados'] / 60, 1) ?></td>
                            <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $colorPorcentaje($fila['porcentaje']) ?>"><?= number_format($fila['porcentaje'], 1) ?>%</span></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div><?php if (!$asistenciaPersonal): ?><div class="border-t p-8 text-center text-slate-500">No existen asistencias de personal en el rango seleccionado.</div><?php endif; ?>
    </section>

    <section id="reporte-incidencias" class="scroll-mt-5 space-y-5">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"><?php foreach ($incidenciasResumen as $fila): ?><article class="rounded-2xl border p-4 <?= $coloresEstado[$fila['estado']] ?? 'bg-slate-100 text-slate-800' ?>">
                    <p class="text-xs font-bold uppercase"><?= htmlspecialchars(str_replace('_', ' ', $fila['estado'])) ?></p>
                    <p class="mt-1 text-2xl font-bold"><?= (int)$fila['cantidad'] ?></p>
                    <p class="text-xs"><?= htmlspecialchars(str_replace('_', ' ', $fila['tipo'])) ?></p>
                </article><?php endforeach; ?><?php if (!$incidenciasResumen): ?><div class="rounded-2xl border border-emerald-200 bg-emerald-100 p-5 text-emerald-900 sm:col-span-2 xl:col-span-4"><b>Sin incidencias:</b> no se registraron incidencias en el rango seleccionado.</div><?php endif; ?></div>
        <article class="overflow-hidden rounded-2xl border bg-white">
            <div class="border-b p-5">
                <h2 class="font-bold">Detalle de incidencias</h2>
                <p class="text-sm text-slate-500">Justificaciones, permisos, licencias y observaciones registradas.</p>
            </div>
            <div class="overflow-x-auto">
                <table data-datatable data-page-options="10,25,50" class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">Persona</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Motivo</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4">Registrada</th>
                            <th class="p-4">Resuelta</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($incidencias as $fila): ?><tr class="border-t">
                                <td class="p-4"><?= htmlspecialchars($fila['fecha']) ?></td>
                                <td class="p-4 font-semibold"><?= htmlspecialchars($fila['persona']) ?></td>
                                <td class="p-4"><?= htmlspecialchars(str_replace('_', ' ', $fila['tipo'])) ?></td>
                                <td class="max-w-sm p-4"><?= htmlspecialchars($fila['motivo']) ?></td>
                                <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $coloresEstado[$fila['estado']] ?? 'bg-slate-100 text-slate-700' ?>"><?= htmlspecialchars($fila['estado']) ?></span></td>
                                <td class="p-4 text-xs"><?= htmlspecialchars($fila['creado_en']) ?></td>
                                <td class="p-4 text-xs"><?= htmlspecialchars($fila['resuelto_en'] ?: 'Pendiente') ?></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </article>
    </section>
<?php endif; ?>