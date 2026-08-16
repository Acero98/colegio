<div class="mb-6">
    <p class="text-sm font-medium text-blue-600">Portal de familia</p>
    <h1 class="text-2xl font-bold">Resumen de mis hijos</h1>
    <p class="mt-1 text-sm text-slate-500">Consulte la asistencia reciente y los pagos pendientes.</p>
</div>
<section class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3"><?php foreach ($hijos as $h): ?><article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-600"><i class="bi bi-backpack2 text-xl"></i></span>
                <div>
                    <h2 class="font-bold"><?= htmlspecialchars($h['alumno']) ?></h2>
                    <p class="text-sm text-slate-500"><?= htmlspecialchars($h['codigo_alumno'] . ' · ' . $h['seccion']) ?></p>
                </div>
            </div>
        </article><?php endforeach; ?><?php if (!$hijos): ?><p class="rounded-2xl border border-dashed p-6 text-sm text-slate-500">No hay alumnos vinculados a esta cuenta.</p><?php endif; ?></section>
<div class="grid gap-6 xl:grid-cols-2">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b p-5">
            <h2 class="font-bold">Asistencia reciente</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Alumno</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Entrada</th>
                    </tr>
                </thead>
                <tbody class="divide-y"><?php foreach ($asistencias as $a): ?><tr>
                            <td class="px-4 py-3"><?= $a['fecha'] ?></td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($a['alumno']) ?></td>
                            <td class="px-4 py-3"><?= $a['estado'] ?></td>
                            <td class="px-4 py-3"><?= $a['hora_entrada'] ?: '—' ?></td>
                        </tr><?php endforeach; ?><?php if (!$asistencias): ?><tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Sin registros recientes.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b p-5">
            <h2 class="font-bold">Pagos pendientes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Alumno</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Vence</th>
                        <th class="px-4 py-3">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y"><?php foreach ($deudas as $d): ?><tr>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($d['alumno']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($d['concepto']) ?></td>
                            <td class="px-4 py-3"><?= $d['fecha_vencimiento'] ?: '—' ?></td>
                            <td class="px-4 py-3 font-semibold">S/ <?= number_format((float)$d['saldo'], 2) ?></td>
                        </tr><?php endforeach; ?><?php if (!$deudas): ?><tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No tiene pagos pendientes.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </section>
</div>