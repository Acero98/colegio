<?php $totalCaja = array_sum(array_column($reportes['caja'], 'total'));
$totalMora = array_sum(array_column($reportes['morosidad'], 'deuda'));
$flujo=$reportes['flujo']; $tf=$flujo['totales']; ?>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Reportes</p>
        <h1 class="text-2xl font-bold">Reportes financieros</h1>
        <p class="mt-1 text-sm text-slate-500">Flujo de dinero, resultado operativo, cobranzas, planilla y morosidad institucional.</p>
    </div><div class="flex flex-wrap gap-2"><a target="_blank" href="index.php?url=finanzas_reporte_imprimir&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-center text-sm font-semibold text-white"><i class="bi bi-printer"></i> Imprimir cierre</a><a href="index.php?url=finanzas_movimientos" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-center text-sm font-semibold text-blue-700"><i class="bi bi-arrow-left-right"></i> Movimientos</a><a href="index.php?url=cobros" class="rounded-xl bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white"><i class="bi bi-arrow-left"></i> Volver a Cobros</a></div>
</div>
<form class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4"><input type="hidden" name="url" value="reportes_financieros"><label class="form-label">Desde<input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>" class="form-input"></label><label class="form-label">Hasta<input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>" class="form-input"></label><button class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white">Actualizar reporte</button></form>
<section class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    <article class="rounded-2xl bg-emerald-50 p-5"><p class="text-xs font-semibold uppercase text-emerald-700">Entradas totales</p><p class="mt-2 text-2xl font-bold text-emerald-800">S/ <?= number_format($tf['ingresos'],2) ?></p><small class="text-emerald-700">Cobros + otros ingresos</small></article>
    <article class="rounded-2xl bg-rose-50 p-5"><p class="text-xs font-semibold uppercase text-rose-700">Salidas totales</p><p class="mt-2 text-2xl font-bold text-rose-800">S/ <?= number_format($tf['egresos'],2) ?></p><small class="text-rose-700">Planilla + otros egresos</small></article>
    <article class="rounded-2xl bg-blue-50 p-5"><p class="text-xs font-semibold uppercase text-blue-700">Flujo neto</p><p class="mt-2 text-2xl font-bold <?= $tf['flujo_neto']>=0?'text-blue-800':'text-rose-700' ?>">S/ <?= number_format($tf['flujo_neto'],2) ?></p><small class="text-blue-700">Entradas menos salidas</small></article>
    <article class="rounded-2xl bg-violet-50 p-5"><p class="text-xs font-semibold uppercase text-violet-700">Resultado operativo</p><p class="mt-2 text-2xl font-bold <?= $tf['resultado_operativo']>=0?'text-violet-800':'text-rose-700' ?>">S/ <?= number_format($tf['resultado_operativo'],2) ?></p><small class="text-violet-700">Excluye capital y préstamos</small></article>
    <article class="rounded-2xl bg-amber-50 p-5"><p class="text-xs font-semibold uppercase text-amber-700">Deuda vencida actual</p><p class="mt-2 text-2xl font-bold text-amber-800">S/ <?= number_format((float)$totalMora,2) ?></p></article>
</section>
<section class="mb-5 grid gap-5 xl:grid-cols-2">
    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b p-5"><h2 class="font-bold">Resumen por origen</h2><p class="text-sm text-slate-500">Diferencia los movimientos automáticos de los registros adicionales.</p></div><div class="grid gap-3 p-5 sm:grid-cols-2"><div class="rounded-xl bg-emerald-50 p-4"><span class="text-xs text-emerald-700">Cobros de alumnos</span><b class="block text-xl text-emerald-800">S/ <?= number_format($tf['cobros'],2) ?></b></div><div class="rounded-xl bg-emerald-50 p-4"><span class="text-xs text-emerald-700">Otros ingresos</span><b class="block text-xl text-emerald-800">S/ <?= number_format($tf['otros_ingresos'],2) ?></b></div><div class="rounded-xl bg-rose-50 p-4"><span class="text-xs text-rose-700">Pagos de planilla</span><b class="block text-xl text-rose-800">S/ <?= number_format($tf['planilla'],2) ?></b></div><div class="rounded-xl bg-rose-50 p-4"><span class="text-xs text-rose-700">Otros egresos</span><b class="block text-xl text-rose-800">S/ <?= number_format($tf['otros_egresos'],2) ?></b></div></div><div class="border-t px-5 py-4 text-sm text-slate-600">Financiamiento neto: <b>S/ <?= number_format($tf['financiamiento_neto'],2) ?></b> · Capital neto: <b>S/ <?= number_format($tf['capital_neto'],2) ?></b></div></article>
    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b p-5"><h2 class="font-bold">Flujo por medio</h2><p class="text-sm text-slate-500">Cuánto entró y salió por efectivo, bancos y medios digitales.</p></div><div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25" class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Medio</th><th class="p-4">Entró</th><th class="p-4">Salió</th><th class="p-4">Neto</th></tr></thead><tbody><?php foreach($flujo['por_medio'] as $fila): ?><tr class="border-t"><td class="p-4 font-medium"><?= htmlspecialchars($fila['medio']) ?></td><td class="p-4 text-emerald-700">S/ <?= number_format($fila['ingreso'],2) ?></td><td class="p-4 text-rose-700">S/ <?= number_format($fila['egreso'],2) ?></td><td class="p-4 font-bold">S/ <?= number_format($fila['neto'],2) ?></td></tr><?php endforeach; ?></tbody></table></div></article>
</section>
<section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b p-5"><h2 class="font-bold">Control y diferencias de caja</h2><p class="text-sm text-slate-500">Los importes rectificados conservan su historial; los reportes financieros continúan basándose en las operaciones registradas.</p></div><div class="grid gap-3 border-b p-5 sm:grid-cols-4"><div class="rounded-xl bg-blue-50 p-4"><small class="text-blue-700">Turnos cerrados</small><b class="block text-xl text-blue-900"><?= $flujo['control_caja']['cierres'] ?></b></div><div class="rounded-xl bg-rose-50 p-4"><small class="text-rose-700">Con diferencia actual</small><b class="block text-xl text-rose-900"><?= $flujo['control_caja']['con_diferencia'] ?></b></div><div class="rounded-xl bg-amber-50 p-4"><small class="text-amber-700">Pendientes de revisión</small><b class="block text-xl text-amber-900"><?= $flujo['control_caja']['pendientes'] ?></b></div><div class="rounded-xl bg-violet-50 p-4"><small class="text-violet-700">Revisiones registradas</small><b class="block text-xl text-violet-900"><?= $flujo['control_caja']['revisiones'] ?></b></div></div><div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25,50" data-order="1:desc" class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="no-sort p-4">N°</th><th class="p-4">Cierre</th><th class="p-4">Turno</th><th class="p-4">Responsable</th><th class="p-4">Diferencia actual</th><th class="p-4">Estado</th><th class="p-4">Revisiones</th></tr></thead><tbody><?php foreach($flujo['cierres'] as $indice=>$cierre): ?><tr class="border-t"><td class="p-4"><?= $indice+1 ?></td><td class="p-4"><?= htmlspecialchars($cierre['cerrado_en']) ?></td><td class="p-4">#<?= (int)$cierre['id_apertura'] ?></td><td class="p-4"><?= htmlspecialchars($cierre['responsable']) ?></td><td class="p-4 font-bold <?= abs((float)$cierre['diferencia'])<0.009?'text-emerald-700':'text-rose-700' ?>">S/ <?= number_format((float)$cierre['diferencia'],2) ?></td><td class="p-4"><?= str_replace('_',' ',htmlspecialchars($cierre['estado_revision'])) ?></td><td class="p-4"><?= (int)$cierre['revisiones'] ?></td></tr><?php endforeach; ?><?php if(!$flujo['cierres']): ?><tr><td colspan="7" class="p-8 text-center text-slate-500">No hay cierres en el período.</td></tr><?php endif; ?></tbody></table></div></section>
<section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b p-5"><h2 class="font-bold">Otros ingresos y egresos por categoría</h2><p class="text-sm text-slate-500">Préstamos y aportes afectan el flujo, pero se mantienen separados del resultado operativo.</p></div><div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Tipo</th><th class="p-4">Categoría</th><th class="p-4">Clasificación</th><th class="p-4">Medio</th><th class="p-4">Operaciones</th><th class="p-4">Total</th></tr></thead><tbody><?php foreach($flujo['manuales'] as $fila): ?><tr class="border-t"><td class="p-4"><?= $fila['tipo'] ?></td><td class="p-4 font-medium"><?= htmlspecialchars($fila['categoria']) ?></td><td class="p-4"><?= $fila['clasificacion'] ?></td><td class="p-4"><?= $fila['medio'] ?></td><td class="p-4"><?= $fila['operaciones'] ?></td><td class="p-4 font-bold <?= $fila['tipo']==='INGRESO'?'text-emerald-700':'text-rose-700' ?>">S/ <?= number_format((float)$fila['total'],2) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-bold">Caja diaria</h2>
        <p class="text-sm text-slate-500">Pagos registrados, agrupados por fecha y medio de pago.</p>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaCaja" data-datatable data-page-options="5,10,25,50" data-order="0:desc" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="p-4">Fecha</th>
                    <th class="p-4">Medio de pago</th>
                    <th class="p-4">Operaciones</th>
                    <th class="p-4">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($reportes['caja'] as $fila): ?><tr>
                        <td class="p-4"><?= htmlspecialchars($fila['fecha']) ?></td>
                        <td class="p-4"><?= htmlspecialchars($fila['medio_pago']) ?></td>
                        <td class="p-4"><?= $fila['operaciones'] ?></td>
                        <td class="p-4 font-semibold">S/ <?= number_format((float)$fila['total'], 2) ?></td>
                    </tr><?php endforeach; ?><?php if (!$reportes['caja']): ?><tr>
                        <td colspan="4" class="p-6 text-center text-slate-500">No hay pagos en el rango seleccionado.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-bold">Cobranzas por concepto</h2>
        <p class="text-sm text-slate-500">Importe efectivamente recibido por cada concepto.</p>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaConceptosReporte" data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="p-4">Concepto</th>
                    <th class="p-4">Tipo</th>
                    <th class="p-4">Recibos</th>
                    <th class="p-4">Cobrado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($reportes['conceptos'] as $fila): ?><tr>
                        <td class="p-4 font-medium"><?= htmlspecialchars($fila['nombre']) ?></td>
                        <td class="p-4"><?= htmlspecialchars($fila['tipo']) ?></td>
                        <td class="p-4"><?= $fila['recibos'] ?></td>
                        <td class="p-4 font-semibold">S/ <?= number_format((float)$fila['cobrado'], 2) ?></td>
                    </tr><?php endforeach; ?><?php if (!$reportes['conceptos']): ?><tr>
                        <td colspan="4" class="p-6 text-center text-slate-500">No hay cobranzas en el rango seleccionado.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-bold">Morosidad por nivel y grado</h2>
        <p class="text-sm text-slate-500">Deuda con fecha de vencimiento superada.</p>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaMora" data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="p-4">Nivel</th>
                    <th class="p-4">Grado</th>
                    <th class="p-4">Alumnos</th>
                    <th class="p-4">Cuentas</th>
                    <th class="p-4">Deuda</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($reportes['morosidad'] as $fila): ?><tr>
                        <td class="p-4"><?= htmlspecialchars($fila['nivel']) ?></td>
                        <td class="p-4 font-medium"><?= htmlspecialchars($fila['grado']) ?></td>
                        <td class="p-4"><?= $fila['alumnos'] ?></td>
                        <td class="p-4"><?= $fila['cuentas'] ?></td>
                        <td class="p-4 font-semibold text-red-700">S/ <?= number_format((float)$fila['deuda'], 2) ?></td>
                    </tr><?php endforeach; ?><?php if (!$reportes['morosidad']): ?><tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">No hay deudas vencidas.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
