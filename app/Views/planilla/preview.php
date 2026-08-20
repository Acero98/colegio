<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Vista previa automática</p>
        <h1 class="text-2xl font-bold"><?= htmlspecialchars($periodo['nombre']) ?></h1>
        <p class="text-sm text-slate-500">Jornadas, sesiones, calendario y asistencia · no modifica la planilla.</p>
    </div><a href="index.php?url=planilla" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Volver</a>
</div>
<section class="mb-5 grid gap-4 sm:grid-cols-3"><?php foreach ([['Ingresos', 'ingresos'], ['Descuentos', 'descuentos'], ['Neto estimado', 'neto']] as $t): ?><article class="rounded-2xl border bg-white p-5">
            <p class="text-xs uppercase text-slate-500"><?= $t[0] ?></p>
            <p class="text-2xl font-bold">S/ <?= number_format($totales[$t[1]], 2) ?></p>
        </article><?php endforeach; ?></section>
<section class="overflow-hidden rounded-2xl border bg-white">
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="p-3">Trabajador</th>
                    <th class="p-3">Programación</th>
                    <th class="p-3">Asistencia</th>
                    <th class="p-3">Ingresos</th>
                    <th class="p-3">Descuento</th>
                    <th class="p-3">Neto</th>
                </tr>
            </thead>
            <tbody><?php foreach ($filas as $f): ?><tr class="border-t align-top">
                        <td class="p-3 font-medium"><?= htmlspecialchars($f['v']['trabajador']) ?><small class="block text-slate-500"><?= htmlspecialchars($f['v']['tipo'] . ' · ' . $f['v']['modalidad_pago']) ?></small><?php foreach ($f['advertencias'] as $aviso): ?><span class="mt-1 block rounded bg-amber-100 px-2 py-1 text-xs text-amber-800"><?= htmlspecialchars($aviso) ?></span><?php endforeach; ?></td>
                        <td class="p-3 text-xs"><?= $f['jornada'] ? htmlspecialchars($f['jornada']['jornada']) : 'Sin jornada individual' ?><br><?= $f['diasVigentes'] ?> días · <?= $f['feriados'] ?> feriados<br><?= $f['sesionesEsperadas'] ?> sesiones esperadas</td>
                        <td class="p-3 text-xs"><?= $f['a']['asistidos'] ?> días con entrada<br><?= $f['a']['tardanza'] ?> min tarde · <?= $f['a']['faltas'] ?> faltas<br><?= $f['a']['sesiones'] ?> sesiones cumplidas</td>
                        <td class="p-3 text-xs">Mensual: S/ <?= number_format($f['mensual'], 2) ?><br><?= $f['horas'] ?> h: S/ <?= number_format($f['pagoHora'], 2) ?><br>Sesiones: S/ <?= number_format($f['pagoSesion'], 2) ?><br>Bonificaciones: S/ <?= number_format($f['bonificaciones'], 2) ?></td>
                        <td class="p-3 text-rose-700">S/ <?= number_format($f['descuentos'], 2) ?></td>
                        <td class="p-3 font-bold text-emerald-700">S/ <?= number_format($f['neto'], 2) ?></td>
                    </tr><?php endforeach; ?><?php if (!$filas): ?><tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">No hay trabajadores vigentes.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
