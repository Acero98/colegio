<?php $colores = ['REGISTRADO' => 'bg-emerald-50 text-emerald-700', 'ANULADO' => 'bg-red-50 text-red-700']; ?>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Finanzas escolares</p>
        <h1 class="text-2xl font-bold">Recibos y pagos</h1>
        <p class="mt-1 text-sm text-slate-500">Los recibos anulados se conservan como historial y restauran sus saldos.</p>
    </div><a href="index.php?url=cobros" class="rounded-xl bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white"><i class="bi bi-arrow-left"></i> Volver a Cobros</a>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table id="tablaPagos" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Recibo</th>
                    <th class="px-5 py-4">Fecha</th>
                    <th class="px-5 py-4">Apoderado</th>
                    <th class="px-5 py-4">Medio</th>
                    <th class="px-5 py-4">Deudas</th>
                    <th class="px-5 py-4">Importe</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($pagos as $pago): ?><tr>
                        <td class="px-5 py-4 font-semibold"><?= htmlspecialchars($pago['numero_recibo']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($pago['fecha_pago']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars(trim($pago['apoderado'], ', ') ?: 'Sin apoderado') ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($pago['medio_pago']) ?></td>
                        <td class="px-5 py-4 text-center"><?= $pago['deudas'] ?></td>
                        <td class="px-5 py-4 font-semibold">S/ <?= number_format((float)$pago['importe_total'], 2) ?></td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $colores[$pago['estado']] ?>"><?= $pago['estado'] ?></span><?php if ($pago['motivo_anulacion']): ?><span class="mt-1 block text-xs text-slate-500"><?= htmlspecialchars($pago['motivo_anulacion']) ?></span><?php endif; ?></td>
                        <td class="px-5 py-4"><?php if ($pago['estado'] === 'REGISTRADO'): ?><button type="button" onclick="anularRecibo(<?= $pago['id_pago'] ?>,'<?= htmlspecialchars($pago['numero_recibo'], ENT_QUOTES) ?>')" class="rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700">Anular</button><?php endif; ?></td>
                    </tr><?php endforeach; ?><?php if (!$pagos): ?><tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">Aún no hay pagos registrados.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-anular-recibo" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-20 max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Anular recibo</h2>
        <p id="anular-recibo-texto" class="mt-1 text-sm text-slate-500"></p>
        <form method="post" action="index.php?url=cobros_pago_anular" data-confirmar-accion="pago-anular" class="mt-4 grid gap-3"><?= campoCsrf() ?><input id="anular-recibo-id" type="hidden" name="id_pago"><label class="form-label">Motivo<input required maxlength="250" name="motivo" class="form-input" placeholder="Explique el motivo de la anulación"></label><button class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white">Anular y restaurar saldos</button></form>
    </div>
</div>
<script>
    function anularRecibo(id, numero) {
        document.getElementById('anular-recibo-id').value = id;
        document.getElementById('anular-recibo-texto').textContent = 'Recibo ' + numero + '. Esta acción restaurará sus saldos pendientes.';
        document.getElementById('modal-anular-recibo').classList.remove('hidden');
    }
    document.addEventListener('DOMContentLoaded', function() {
        if (window.simpleDatatables && document.querySelector('#tablaPagos')) new simpleDatatables.DataTable('#tablaPagos', {
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