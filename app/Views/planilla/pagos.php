<?php
$hayPagables = false;
foreach ($detalles as $detalle) {
    if ((float) $detalle['saldo_pendiente'] > 0 && ($detalle['decision_pago'] ?? 'PENDIENTE') === 'PENDIENTE') { $hayPagables = true; break; }
}
$estadoEstilos = [
    'PENDIENTE' => 'bg-amber-100 text-amber-800',
    'RETENIDO' => 'bg-violet-100 text-violet-800',
    'EXCLUIDO' => 'bg-slate-200 text-slate-800',
    'PAGADO' => 'bg-emerald-100 text-emerald-800',
    'PARCIAL' => 'bg-blue-100 text-blue-800',
];
?>

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div><p class="text-sm font-medium text-emerald-600">Pagos al personal</p><h1 class="text-2xl font-bold"><?= htmlspecialchars($periodo['nombre']) ?></h1><p class="text-sm text-slate-500">Estado: <?= htmlspecialchars($periodo['estado']) ?> · pagos totales, parciales y decisiones documentadas.</p></div>
    <a href="index.php?url=planilla_revision&id=<?= $periodo['id_periodo_pago'] ?>" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Volver al detalle</a>
</div>

<section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
    <?php foreach ([['Trabajadores',$resumen['total'],'bg-slate-50 text-slate-800'],['Pagados',$resumen['pagados'],'bg-emerald-50 text-emerald-800'],['Parciales',$resumen['parciales'],'bg-blue-50 text-blue-800'],['Pendientes',$resumen['pendientes'],'bg-amber-50 text-amber-800'],['Retenidos',$resumen['retenidos'],'bg-violet-50 text-violet-800'],['Excluidos',$resumen['excluidos'],'bg-rose-50 text-rose-800']] as [$etiqueta,$valor,$estilo]): ?>
        <article class="rounded-xl border border-slate-200 <?= $estilo ?> p-4"><p class="text-xs font-semibold"><?= $etiqueta ?></p><strong class="mt-1 block text-2xl"><?= number_format($valor) ?></strong></article>
    <?php endforeach; ?>
</section>

<div class="mb-5 grid gap-3 sm:grid-cols-2">
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Saldo pendiente</p><strong class="mt-1 block text-2xl text-amber-900">S/ <?= number_format($resumen['saldo'], 2) ?></strong><p class="mt-1 text-xs text-amber-700">Incluye pagos retenidos o postergados.</p></div>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Saldo excluido</p><strong class="mt-1 block text-2xl text-slate-900">S/ <?= number_format($resumen['excluido'], 2) ?></strong><p class="mt-1 text-xs text-slate-600">No se pagará en este período y conserva motivo y responsable.</p></div>
</div>

<?php if ($hayPagables && $periodo['estado'] === 'CERRADO'): ?>
<section class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
    <h2 class="font-semibold">Registrar pago</h2>
    <form method="post" action="index.php?url=planilla_pago_registrar" data-confirmar-accion="planilla-pago-registrar" class="mt-4 grid gap-3 sm:grid-cols-2">
        <?= campoCsrf() ?><input type="hidden" name="id_periodo_pago" value="<?= $periodo['id_periodo_pago'] ?>">
        <label class="form-label sm:col-span-2">Trabajador<select required name="id_planilla_detalle" class="form-input"><option value="">Seleccione</option><?php foreach ($detalles as $detalle): if ((float) $detalle['saldo_pendiente'] <= 0 || ($detalle['decision_pago'] ?? 'PENDIENTE') !== 'PENDIENTE') continue; ?><option value="<?= $detalle['id_planilla_detalle'] ?>"><?= htmlspecialchars($detalle['trabajador'] . ' · Saldo S/ ' . number_format($detalle['saldo_pendiente'], 2)) ?></option><?php endforeach; ?></select></label>
        <label class="form-label">Importe<input required min="0.01" step="0.01" type="number" name="importe" class="form-input"></label>
        <label class="form-label">Medio<select name="medio_pago" class="form-input"><option value="TRANSFERENCIA">Transferencia</option><option value="DEPOSITO">Depósito</option><option value="EFECTIVO">Efectivo</option><option value="OTRO">Otro</option></select></label>
        <label class="form-label">Referencia<input name="referencia" maxlength="120" class="form-input"></label><label class="form-label">Observación<input name="observacion" maxlength="250" class="form-input"></label>
        <button class="rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white sm:col-span-2">Registrar pago</button>
    </form>
</section>
<?php elseif (!$hayPagables): ?>
    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">No existen saldos activos para pagar. Revise los trabajadores pagados, retenidos o excluidos.</div>
<?php endif; ?>

<section class="mb-5 overflow-hidden rounded-2xl border bg-white">
    <div class="border-b p-4"><h2 class="font-semibold">Estado por trabajador</h2><p class="text-sm text-slate-500">Retener mantiene la deuda; excluir resuelve el saldo del período sin registrarlo como pago.</p></div>
    <div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Trabajador</th><th class="p-4">Neto</th><th class="p-4">Pagado</th><th class="p-4">Saldo</th><th class="p-4">Decisión</th><th class="no-sort p-4">Acciones</th></tr></thead><tbody>
    <?php foreach ($detalles as $detalle):
        $decision = $detalle['decision_pago'] ?? 'PENDIENTE';
        $estadoVisible = $decision !== 'PENDIENTE' ? $decision : ((float) $detalle['saldo_pendiente'] <= 0 ? 'PAGADO' : ((float) $detalle['monto_pagado'] > 0 ? 'PARCIAL' : 'PENDIENTE'));
        $formId = 'decision-' . (int) $detalle['id_planilla_detalle'];
    ?>
        <tr class="border-t"><td class="p-4 font-medium"><?= htmlspecialchars($detalle['trabajador']) ?><?php if (!empty($detalle['decision_motivo'])): ?><small class="mt-1 block max-w-xs text-slate-500"><?= htmlspecialchars($detalle['decision_motivo']) ?><?php if ($detalle['decision_responsable']): ?> · <?= htmlspecialchars($detalle['decision_responsable']) ?><?php endif; ?></small><?php endif; ?></td><td class="p-4">S/ <?= number_format($detalle['monto_pagar'], 2) ?></td><td class="p-4 text-emerald-700">S/ <?= number_format($detalle['monto_pagado'], 2) ?></td><td class="p-4 text-rose-700">S/ <?= number_format($detalle['saldo_pendiente'], 2) ?><?php if ((float) $detalle['monto_excluido'] > 0): ?><small class="block text-slate-500">Excluido: S/ <?= number_format($detalle['monto_excluido'], 2) ?></small><?php endif; ?></td><td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $estadoEstilos[$estadoVisible] ?? 'bg-slate-100 text-slate-700' ?>"><?= htmlspecialchars($estadoVisible) ?></span></td><td class="p-4">
            <?php if ((float) $detalle['saldo_pendiente'] > 0 || $decision !== 'PENDIENTE'): ?>
                <form id="<?= $formId ?>" method="post" action="index.php?url=planilla_pago_decision"><?= campoCsrf() ?><input type="hidden" name="id_periodo_pago" value="<?= $periodo['id_periodo_pago'] ?>"><input type="hidden" name="id_planilla_detalle" value="<?= $detalle['id_planilla_detalle'] ?>"><input type="hidden" name="decision_pago"><input type="hidden" name="decision_motivo"></form>
                <div class="flex flex-wrap gap-2">
                    <?php if ($decision === 'PENDIENTE' && (float) $detalle['saldo_pendiente'] > 0): ?><button type="button" data-planilla-decision="RETENIDO" data-form="<?= $formId ?>" class="rounded-lg bg-violet-100 px-3 py-2 text-xs font-semibold text-violet-700">Retener</button><button type="button" data-planilla-decision="EXCLUIDO" data-form="<?= $formId ?>" class="rounded-lg bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700">No se pagará</button><?php else: ?><button type="button" data-planilla-decision="PENDIENTE" data-form="<?= $formId ?>" class="rounded-lg bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-700"><?= $decision === 'EXCLUIDO' ? 'Revertir exclusión' : 'Reactivar pago' ?></button><?php endif; ?>
                </div>
            <?php else: ?><span class="text-xs text-slate-400">Sin acciones</span><?php endif; ?>
        </td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>

<section class="overflow-hidden rounded-2xl border bg-white"><div class="border-b p-4"><h2 class="font-semibold">Pagos y constancias</h2><p class="text-sm text-slate-500">Cada pago genera su propio código y documento imprimible.</p></div><div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Código</th><th class="p-4">Trabajador</th><th class="p-4">Importe / medio</th><th class="p-4">Responsable / fecha</th><th class="p-4">Estado</th><th class="no-sort p-4">Acciones</th></tr></thead><tbody>
<?php foreach ($pagos as $pago): $codigo = 'PL-' . date('Y', strtotime($pago['pagado_en'])) . '-' . str_pad((string) $pago['id_pago_planilla'], 6, '0', STR_PAD_LEFT); ?><tr class="border-t"><td class="p-4 font-semibold text-blue-700"><?= $codigo ?></td><td class="p-4 font-medium"><?= htmlspecialchars($pago['trabajador']) ?></td><td class="p-4">S/ <?= number_format($pago['importe'], 2) ?><small class="block text-slate-500"><?= htmlspecialchars($pago['medio_pago'] . ' · ' . ($pago['referencia'] ?: 'Sin referencia')) ?></small></td><td class="p-4 text-xs"><?= htmlspecialchars($pago['responsable']) ?><br><?= htmlspecialchars($pago['pagado_en']) ?></td><td class="p-4"><?= htmlspecialchars($pago['estado']) ?><?php if ($pago['motivo_anulacion']): ?><small class="block text-rose-600"><?= htmlspecialchars($pago['motivo_anulacion']) ?></small><?php endif; ?></td><td class="p-4"><div class="flex flex-wrap gap-2"><a target="_blank" href="index.php?url=planilla_pago_constancia&id=<?= $pago['id_pago_planilla'] ?>" class="rounded-lg bg-blue-100 px-3 py-2 text-xs font-semibold text-blue-700"><i class="bi bi-printer"></i> Imprimir constancia</a><?php if ($pago['estado'] === 'REGISTRADO'): ?><form method="post" action="index.php?url=planilla_pago_anular" data-confirmar-accion="planilla-pago-anular" class="flex gap-2"><?= campoCsrf() ?><input type="hidden" name="id_periodo_pago" value="<?= $periodo['id_periodo_pago'] ?>"><input type="hidden" name="id_pago_planilla" value="<?= $pago['id_pago_planilla'] ?>"><input required minlength="3" name="motivo_anulacion" class="form-input mt-0 w-36" placeholder="Motivo"><button class="rounded-lg bg-rose-100 px-3 text-xs font-semibold text-rose-700">Anular</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
<?php if (!$pagos): ?><tr><td colspan="6" class="p-8 text-center text-slate-500">Todavía no hay pagos; la constancia aparecerá después de registrar el primero.</td></tr><?php endif; ?>
</tbody></table></div></section>

<script>
document.querySelectorAll('[data-planilla-decision]').forEach(button => {
    button.addEventListener('click', async () => {
        const decision = button.dataset.planillaDecision;
        const form = document.getElementById(button.dataset.form);
        const configuraciones = {
            RETENIDO: { icon: 'warning', title: '¿Retener este pago?', text: 'El saldo seguirá pendiente y podrá reactivarse después.', confirm: 'Sí, retener', color: '#7c3aed', label: 'Motivo de la retención' },
            EXCLUIDO: { icon: 'warning', title: '¿Marcar que no se pagará?', text: 'El saldo se excluirá de este período sin registrarse como pago. La decisión quedará auditada.', confirm: 'Sí, excluir', color: '#e11d48', label: 'Motivo de la exclusión' },
            PENDIENTE: { icon: 'question', title: '¿Reactivar este pago?', text: 'El saldo volverá a estar disponible para registrar pagos.', confirm: 'Sí, reactivar', color: '#059669', label: 'Motivo de la reactivación' }
        };
        const config = configuraciones[decision];
        let motivo = '';
        if (window.Swal) {
            const resultado = await Swal.fire({ icon: config.icon, title: config.title, text: config.text, input: 'textarea', inputLabel: config.label, inputPlaceholder: 'Escriba el motivo…', inputAttributes: { maxlength: '500' }, showCancelButton: true, confirmButtonText: config.confirm, cancelButtonText: 'Cancelar', confirmButtonColor: config.color, inputValidator: value => value.trim().length < 5 ? 'Ingrese un motivo de al menos 5 caracteres.' : undefined });
            if (!resultado.isConfirmed) return;
            motivo = resultado.value.trim();
        } else {
            motivo = (window.prompt(config.label + ':') || '').trim();
            if (motivo.length < 5) return;
        }
        form.querySelector('[name="decision_pago"]').value = decision;
        form.querySelector('[name="decision_motivo"]').value = motivo;
        HTMLFormElement.prototype.submit.call(form);
    });
});
</script>
