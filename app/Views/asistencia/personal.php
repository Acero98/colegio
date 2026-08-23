<section class="mb-5 rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm text-violet-950">
    <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
        <div>
            <strong class="block text-base">Cierre manual de docentes y administrativos</strong>
            <p class="mt-1 text-violet-800">Evalúa jornadas y sesiones asignadas, genera faltas y detecta salidas obligatorias pendientes.</p>
            <form method="get" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                <input type="hidden" name="url" value="asistencia_personal">
                <label class="form-label w-full sm:w-52">Fecha
                    <input required max="<?= date('Y-m-d') ?>" type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" class="form-input">
                </label>
                <button class="h-[42px] rounded-xl border border-violet-300 bg-white px-4 text-sm font-semibold text-violet-700">Consultar</button>
            </form>
        </div>
        <div class="lg:text-right">
            <?php if ($cierreSeleccionado): ?>
                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">CERRADA</span>
                <p class="mt-2 text-xs text-slate-600"><?= htmlspecialchars($cierreSeleccionado['responsable']) ?> · <?= htmlspecialchars($cierreSeleccionado['ejecutado_en']) ?></p>
            <?php elseif ($planillaCerrada): ?>
                <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">BLOQUEADA POR PLANILLA</span>
                <p class="mt-2 max-w-sm text-xs text-rose-700">La planilla que contiene esta fecha ya está cerrada o pagada.</p>
            <?php elseif (tienePermiso('asistencia.modificar')): ?>
                <form method="post" action="index.php?url=asistencia_cerrar_personal" data-confirmar-accion="asistencia-cerrar-personal">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                    <button class="h-[42px] rounded-xl bg-violet-600 px-5 font-semibold text-white">Cerrar fecha seleccionada</button>
                </form>
            <?php else: ?>
                <span class="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-600">SOLO CONSULTA</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold">Asistencia de personal · <?= htmlspecialchars($fecha) ?></h2></div>
    <div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25,50" data-order="1:desc,4:desc" class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="no-sort px-5 py-4">N°</th><th class="px-5 py-4">Fecha</th><th class="px-5 py-4">Personal</th><th class="px-5 py-4">Alcance</th><th class="px-5 py-4">Entrada</th><th class="px-5 py-4">Salida</th><th class="px-5 py-4">Estado</th><th class="px-5 py-4">Tardanza</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach($asistencias as $indice => $a): $estado=$a['estado']; $colorEstado=['PUNTUAL'=>'bg-emerald-100 text-emerald-700','TARDANZA'=>'bg-amber-100 text-amber-800','FALTA'=>'bg-red-100 text-red-700','JUSTIFICADO'=>'bg-sky-100 text-sky-700','PERMISO'=>'bg-violet-100 text-violet-700','INCOMPLETO'=>'bg-orange-100 text-orange-700','LICENCIA'=>'bg-slate-200 text-slate-700','SALIDA_ANTICIPADA'=>'bg-pink-100 text-pink-700'][$estado]??'bg-slate-100 text-slate-700'; ?><tr><td class="px-5 py-4 text-slate-500"><?= $indice + 1 ?></td><td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($a['fecha']) ?></td><td class="px-5 py-4 font-medium"><?= htmlspecialchars($a['persona']) ?></td><td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($a['alcance']) ?></td><td class="px-5 py-4"><?= htmlspecialchars($a['hora_entrada']?:'—') ?></td><td class="px-5 py-4"><?= htmlspecialchars($a['hora_salida']?:'—') ?></td><td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= $colorEstado ?>"><?= htmlspecialchars($estado) ?></span></td><td class="px-5 py-4"><?= (int)$a['minutos_tardanza'] ?> min.</td></tr><?php endforeach; ?><?php if(!$asistencias): ?><tr><td colspan="8" class="px-5 py-12 text-center text-slate-500">No hay asistencia de personal para esta fecha.</td></tr><?php endif; ?></tbody></table></div>
</section>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold">Historial de cierres de personal</h2><p class="text-sm text-slate-500">Incluye docentes y administrativos.</p></div>
    <div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25,50" data-order="0:desc" class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-4">Fecha</th><th class="px-5 py-4">Tipo</th><th class="px-5 py-4">Responsable / ejecución</th><th class="px-5 py-4">Faltas</th><th class="px-5 py-4">Incompletas</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach($cierres as $cierre): ?><tr><td class="px-5 py-4 font-medium"><?= htmlspecialchars($cierre['fecha']) ?></td><td class="px-5 py-4"><span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700"><?= htmlspecialchars($cierre['tipo']) ?></span></td><td class="px-5 py-4"><?= htmlspecialchars($cierre['responsable']) ?><small class="block text-slate-500"><?= htmlspecialchars($cierre['ejecutado_en']) ?></small></td><td class="px-5 py-4 font-semibold text-red-700"><?= (int)$cierre['faltas_generadas'] ?></td><td class="px-5 py-4 font-semibold text-orange-700"><?= (int)$cierre['incompletas_generadas'] ?></td></tr><?php endforeach; ?><?php if(!$cierres): ?><tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Todavía no hay cierres registrados.</td></tr><?php endif; ?></tbody></table></div>
</section>
