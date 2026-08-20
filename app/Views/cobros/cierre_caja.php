<?php $totalSistema = (float)$resumenCaja['total'];
$abierta = $estadoCaja && $estadoCaja['estado'] === 'ABIERTA'; ?>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Finanzas escolares</p>
        <h1 class="text-2xl font-bold">Caja por turnos</h1>
        <p class="mt-1 text-sm text-slate-500">Cada apertura y cierre corresponde a un turno independiente.</p>
    </div><a href="index.php?url=cobros" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Volver a Cobros</a>
</div>
<form class="mb-5 flex gap-2 rounded-2xl border border-slate-200 bg-white p-4"><input type="hidden" name="url" value="cobros_cierre_caja"><input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" class="form-input max-w-xs"><button class="rounded-xl bg-slate-800 px-4 text-sm font-semibold text-white">Ver fecha</button></form>
<section class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><?php foreach ([['Último estado', $estadoCaja ? $estadoCaja['estado'] : 'SIN TURNOS'], ['Operaciones del turno', $resumenCaja['operaciones']], ['Efectivo del turno', 'S/ ' . number_format((float)$resumenCaja['efectivo'], 2)], ['Total del turno', 'S/ ' . number_format($totalSistema, 2)]] as $dato): ?><article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase text-slate-500"><?= $dato[0] ?></p>
            <p class="mt-1 text-2xl font-bold"><?= $dato[1] ?></p>
        </article><?php endforeach; ?></section>
<?php if (!$abierta): ?><section class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <h2 class="font-bold text-emerald-900">Abrir nuevo turno</h2>
        <form method="post" action="index.php?url=cobros_apertura_guardar" data-confirmar-accion="apertura-caja" class="mt-4 grid gap-4 sm:grid-cols-2"><?= campoCsrf() ?><input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>"><label class="form-label">Monto inicial<input required min="0" step="0.01" name="monto_apertura" value="0.00" class="form-input"></label><label class="form-label">Observación<input maxlength="250" name="observacion" class="form-input" placeholder="Ej.: turno tarde"></label><button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white sm:col-span-2">Abrir turno</button></form>
    </section><?php else: ?><section class="mb-5 rounded-2xl border border-violet-200 bg-violet-50 p-5">
        <h2 class="font-bold text-violet-900">Cerrar turno actual</h2>
        <form method="post" action="index.php?url=cobros_cierre_guardar" data-confirmar-accion="cierre-caja" class="mt-4 grid gap-4 sm:grid-cols-2"><?= campoCsrf() ?><input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>"><input type="hidden" name="id_apertura" value="<?= $estadoCaja['id_apertura'] ?>"><label class="form-label">Efectivo declarado<input required min="0" step="0.01" type="number" name="efectivo_declarado" value="<?= number_format((float)$resumenCaja['efectivo'], 2, '.', '') ?>" class="form-input"></label><label class="form-label">Otros medios declarados<input required min="0" step="0.01" type="number" name="otros_declarado" value="<?= number_format((float)$resumenCaja['otros'], 2, '.', '') ?>" class="form-input"></label><label class="form-label sm:col-span-2">Observación<input maxlength="250" name="observacion" class="form-input"></label><button class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white sm:col-span-2">Cerrar turno</button></form>
    </section><?php endif; ?>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-bold">Historial de turnos</h2>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaCajas" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="p-4">Fecha</th>
                    <th class="p-4">Monto inicial</th>
                    <th class="p-4">Estado</th>
                    <th class="p-4">Cobrado / cierre</th>
                    <th class="p-4">Diferencia</th>
                    <th class="p-4">Responsables</th>
                    <th class="p-4">Observación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($cajas as $caja): $nombreApertura = trim($caja['usuario_apertura'], ', ') ?: ($caja['login_apertura'] ?: 'Usuario');
                                                            $nombreCierre = trim($caja['usuario_cierre'], ', ') ?: ($caja['login_cierre'] ?: 'Usuario'); ?><tr>
                        <td class="p-4 font-medium"><?= htmlspecialchars($caja['fecha']) ?></td>
                        <td class="p-4">S/ <?= number_format((float)$caja['monto_apertura'], 2) ?><span class="block text-xs text-slate-500"><?= htmlspecialchars($caja['abierto_en']) ?></span></td>
                        <td class="p-4"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $caja['estado'] === 'ABIERTA' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' ?>"><?= $caja['estado'] ?></span></td>
                        <td class="p-4"><b class="text-blue-700">Cobrado: S/ <?= number_format((float)$caja['total_cobrado'], 2) ?></b><?php if ($caja['estado'] === 'CERRADA'): ?><span class="block text-xs text-slate-500">Cierre declarado: S/ <?= number_format((float)$caja['total_cierre_declarado'], 2) ?></span><span class="block text-xs text-slate-500"><?= htmlspecialchars($caja['cerrado_en']) ?></span><?php endif; ?></td>
                        <td class="p-4 font-semibold <?= (float)$caja['diferencia'] === 0.0 ? 'text-emerald-700' : 'text-red-700' ?>"><?php if ($caja['estado'] === 'CERRADA'): ?>S/ <?= number_format((float)$caja['diferencia'], 2) ?><?php else: ?>—<?php endif; ?></td>
                        <td class="p-4 text-xs">Abrió: <?= htmlspecialchars($nombreApertura) ?><?php if ($caja['estado'] === 'CERRADA'): ?><span class="block">Cerró: <?= htmlspecialchars($nombreCierre) ?></span><?php endif; ?></td>
                        <td class="p-4 text-xs"><?= htmlspecialchars($caja['observacion'] ?: '—') ?></td>
                    </tr><?php endforeach; ?><?php if (!$cajas): ?><tr>
                        <td colspan="7" class="p-6 text-center text-slate-500">Aún no hay turnos registrados.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.simpleDatatables && document.querySelector('#tablaCajas')) new simpleDatatables.DataTable('#tablaCajas', {
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
</script>