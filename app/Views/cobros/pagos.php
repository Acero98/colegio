<?php $colores = ['REGISTRADO' => 'bg-emerald-50 text-emerald-700', 'ANULADO' => 'bg-red-50 text-red-700']; ?>
<style>
    #tablaPagos { width: 100%; }
    #tablaPagos th, #tablaPagos td { padding: .75rem; vertical-align: middle; }
    #tablaPagos .pagos-numero { width: 44px; text-align: center; }
    #tablaPagos .pagos-recibo { white-space: nowrap; }
    #tablaPagos .pagos-fecha { min-width: 105px; white-space: nowrap; }
    #tablaPagos .pagos-alumno { min-width: 190px; max-width: 260px; }
    #tablaPagos .pagos-medio { min-width: 155px; max-width: 230px; overflow-wrap: anywhere; }
    #tablaPagos .pagos-cantidad { width: 66px; text-align: center; }
    #tablaPagos .pagos-importe { min-width: 90px; white-space: nowrap; }
    #tablaPagos .pagos-estado { min-width: 92px; }
    #tablaPagos .pagos-acciones { width: 86px; text-align: center; }
    @media (max-width: 1100px) { #tablaPagos { min-width: 950px; } }
</style>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Finanzas escolares</p>
        <h1 class="text-2xl font-bold">Recibos y pagos</h1>
        <p class="mt-1 text-sm text-slate-500">Los recibos anulados se conservan como historial y restauran sus saldos.</p>
    </div><a href="index.php?url=cobros" class="rounded-xl bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white"><i class="bi bi-arrow-left"></i> Volver a Cobros</a>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="max-w-full overflow-x-auto">
        <table id="tablaPagos" data-datatable data-page-options="5,10,25,50" data-order="2:desc" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="pagos-numero no-sort">N°</th>
                    <th class="pagos-recibo">Recibo</th>
                    <th class="pagos-fecha">Fecha</th>
                    <th class="pagos-alumno">Alumno</th>
                    <th class="pagos-medio">Medio</th>
                    <th class="pagos-cantidad">Deudas</th>
                    <th class="pagos-importe">Importe</th>
                    <th class="pagos-estado">Estado</th>
                    <th class="pagos-acciones no-sort">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($pagos as $indice => $pago): ?><tr>
                        <td class="pagos-numero text-slate-500"><?= $indice + 1 ?></td>
                        <td class="pagos-recibo font-semibold"><?= htmlspecialchars($pago['numero_recibo']) ?></td>
                        <td class="pagos-fecha"><span class="block"><?= htmlspecialchars(date('d/m/Y', strtotime($pago['fecha_pago']))) ?></span><span class="block text-xs text-slate-500"><?= htmlspecialchars(date('H:i', strtotime($pago['fecha_pago']))) ?></span></td>
                        <td class="pagos-alumno"><span class="font-medium text-slate-800"><?= htmlspecialchars($pago['alumno'] ?: 'Alumno no identificado') ?></span><?php if (!empty($pago['codigos_alumno'])): ?><span class="block text-xs text-slate-500"><?= htmlspecialchars($pago['codigos_alumno']) ?></span><?php endif; ?><span class="mt-1 block text-xs text-blue-700">Apoderado: <?= htmlspecialchars(trim($pago['apoderado'], ', ') ?: 'Sin apoderado') ?></span></td>
                        <td class="pagos-medio"><?= htmlspecialchars($pago['medios_resumen'] ?: $pago['medio_pago']) ?><?php if ((float)$pago['vuelto'] > 0): ?><span class="block text-xs text-emerald-700">Vuelto S/ <?= number_format((float)$pago['vuelto'], 2) ?></span><?php endif; ?><?php if ((int)$pago['evidencias'] > 0): ?><span class="mt-1 inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700" title="Evidencias digitales adjuntas"><i class="bi bi-camera mr-1"></i><?= (int)$pago['evidencias'] ?></span><?php endif; ?></td>
                        <td class="pagos-cantidad"><?= $pago['deudas'] ?></td>
                        <td class="pagos-importe font-semibold">S/ <?= number_format((float)$pago['importe_total'], 2) ?></td>
                        <td class="pagos-estado"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $colores[$pago['estado']] ?>"><?= $pago['estado'] ?></span><?php if ($pago['motivo_anulacion']): ?><span class="mt-1 block text-xs text-slate-500"><?= htmlspecialchars($pago['motivo_anulacion']) ?></span><?php endif; ?></td>
                        <td class="pagos-acciones"><div class="flex items-center justify-center gap-2"><a target="_blank" href="index.php?url=cobros_recibo&id=<?= $pago['id_pago'] ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700 transition hover:bg-blue-200" title="Ver e imprimir recibo" aria-label="Ver e imprimir recibo"><i class="bi bi-printer"></i></a><?php if ($pago['estado'] === 'REGISTRADO'): ?><button type="button" onclick="anularRecibo(<?= $pago['id_pago'] ?>,'<?= htmlspecialchars($pago['numero_recibo'], ENT_QUOTES) ?>')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-700 transition hover:bg-red-200" title="Anular recibo" aria-label="Anular recibo"><i class="bi bi-x-circle"></i></button><?php endif; ?></div></td>
                    </tr><?php endforeach; ?><?php if (!$pagos): ?><tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-500">Aún no hay pagos registrados.</td>
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
</script>
