<?php $totalCaja = array_sum(array_column($reportes['caja'], 'total'));
$totalMora = array_sum(array_column($reportes['morosidad'], 'deuda')); ?>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Reportes</p>
        <h1 class="text-2xl font-bold">Reportes financieros</h1>
        <p class="mt-1 text-sm text-slate-500">Caja, cobranzas y morosidad institucional.</p>
    </div><a href="index.php?url=cobros" class="rounded-xl bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white"><i class="bi bi-arrow-left"></i> Volver a Cobros</a>
</div>
<form class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4"><input type="hidden" name="url" value="reportes_financieros"><label class="form-label">Desde<input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>" class="form-input"></label><label class="form-label">Hasta<input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>" class="form-input"></label><button class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white">Actualizar reporte</button></form>
<section class="mb-5 grid gap-4 sm:grid-cols-2">
    <article class="rounded-2xl bg-emerald-50 p-5">
        <p class="text-xs font-semibold uppercase text-emerald-700">Cobrado en el rango</p>
        <p class="mt-2 text-2xl font-bold text-emerald-800">S/ <?= number_format((float)$totalCaja, 2) ?></p>
    </article>
    <article class="rounded-2xl bg-red-50 p-5">
        <p class="text-xs font-semibold uppercase text-red-700">Deuda vencida actual</p>
        <p class="mt-2 text-2xl font-bold text-red-800">S/ <?= number_format((float)$totalMora, 2) ?></p>
    </article>
</section>
<section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-bold">Caja diaria</h2>
        <p class="text-sm text-slate-500">Pagos registrados, agrupados por fecha y medio de pago.</p>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaCaja" class="min-w-full text-left text-sm">
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
        <table id="tablaConceptosReporte" class="min-w-full text-left text-sm">
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
        <table id="tablaMora" class="min-w-full text-left text-sm">
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.simpleDatatables) return;
        ['tablaCaja', 'tablaConceptosReporte', 'tablaMora'].forEach(function(id) {
            var tabla = document.getElementById(id);
            if (tabla) new simpleDatatables.DataTable(tabla, {
                perPage: 10,
                perPageSelect: [5, 10, 25, 50],
                searchable: true,
                sortable: true,
                labels: {
                    placeholder: 'Buscar...',
                    perPage: 'Mostrar',
                    noRows: 'No hay registros',
                    noResults: 'No se encontraron resultados',
                    info: 'Mostrando {start} a {end} de {rows} registros'
                }
            })
        });
    });
</script>