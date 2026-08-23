<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-emerald-600">Revisión del cálculo</p>
        <h1 class="text-2xl font-bold"><?= htmlspecialchars($periodo['nombre']) ?></h1>
        <p class="text-sm text-slate-500">Estado: <?= htmlspecialchars($periodo['estado']) ?><?php if (!empty($periodo['revisado_en'])): ?> · aprobada <?= htmlspecialchars($periodo['revisado_en']) ?><?php endif; ?><?php if (!empty($periodo['cerrado_en'])): ?> · cálculo cerrado <?= htmlspecialchars($periodo['cerrado_en']) ?><?php endif; ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php if ($periodo['estado'] === 'CALCULADO'): ?>
            <a href="index.php?url=planilla_ajustes&id=<?= $periodo['id_periodo_pago'] ?>" class="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-amber-950">Ajustes manuales</a>
            <form method="post" action="index.php?url=planilla_aprobar" data-confirmar-accion="planilla-aprobar"><?= campoCsrf() ?><input type="hidden" name="id_periodo_pago" value="<?= $periodo['id_periodo_pago'] ?>"><button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Aprobar revisión</button></form>
        <?php elseif ($periodo['estado'] === 'REVISADO' && tienePermiso('planilla.cerrar')): ?>
            <form method="post" action="index.php?url=planilla_cerrar" data-confirmar-accion="planilla-cerrar"><?= campoCsrf() ?><input type="hidden" name="id_periodo_pago" value="<?= $periodo['id_periodo_pago'] ?>"><button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Cerrar cálculo</button></form>
        <?php elseif (in_array($periodo['estado'], ['CERRADO', 'PAGADO', 'FINALIZADO'], true) && tienePermiso('planilla.cerrar')): ?>
            <a href="index.php?url=planilla_pagos&id=<?= $periodo['id_periodo_pago'] ?>" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Gestionar pagos</a>
        <?php endif; ?>

        <?php if (in_array($periodo['estado'], ['CERRADO', 'PAGADO', 'FINALIZADO'], true) && tienePermiso('planilla.cerrar') && (int) $periodo['pagos_existentes'] === 0): ?>
            <form id="form-reabrir-planilla" method="post" action="index.php?url=planilla_reabrir"><?= campoCsrf() ?><input type="hidden" name="id_periodo_pago" value="<?= $periodo['id_periodo_pago'] ?>"><input type="hidden" name="estado_anterior" value="<?= htmlspecialchars($periodo['estado']) ?>"><input type="hidden" name="motivo_reapertura"><button type="button" id="btn-reabrir-planilla" class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700">Reabrir cálculo</button></form>
        <?php endif; ?>
        <a href="index.php?url=planilla" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Volver</a>
    </div>
</div>

<?php if (in_array($periodo['estado'], ['CERRADO', 'PAGADO', 'FINALIZADO'], true)): ?>
    <div class="mb-5 rounded-2xl border <?= (int) $periodo['pagos_existentes'] > 0 ? 'border-slate-200 bg-slate-50' : 'border-violet-200 bg-violet-50' ?> p-4 text-sm">
        <?php if ((int) $periodo['pagos_existentes'] > 0): ?>
            <strong class="text-slate-800"><i class="bi bi-shield-lock"></i> El cálculo ya no puede reabrirse.</strong>
            <p class="mt-1 text-slate-600">Existen <?= (int) $periodo['pagos_existentes'] ?> operaciones de pago. Las correcciones deben realizarse mediante ajustes o una planilla complementaria.</p>
        <?php else: ?>
            <strong class="text-violet-900"><i class="bi bi-arrow-counterclockwise"></i> Reapertura controlada disponible.</strong>
            <p class="mt-1 text-violet-700">Como aún no existen pagos, un usuario autorizado puede reabrir el cálculo indicando el motivo.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<section class="space-y-4">
    <?php foreach ($detalles as $detalle): ?>
        <article class="rounded-2xl border bg-white p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div><h2 class="font-bold"><?= htmlspecialchars($detalle['trabajador']) ?></h2><p class="text-xs text-slate-500"><?= htmlspecialchars($detalle['tipo'] . ' · ' . $detalle['modalidad_pago']) ?></p></div>
                <div class="text-right"><p class="text-xs text-slate-500">Neto estimado</p><p class="text-xl font-bold text-emerald-700">S/ <?= number_format((float) $detalle['monto_pagar'], 2) ?></p></div>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-3"><div class="rounded-xl bg-blue-50 p-3 text-sm">Base<br><b>S/ <?= number_format((float) $detalle['monto_base'], 2) ?></b></div><div class="rounded-xl bg-emerald-50 p-3 text-sm">Bonificaciones<br><b>S/ <?= number_format((float) $detalle['bonificaciones'], 2) ?></b></div><div class="rounded-xl bg-rose-50 p-3 text-sm">Descuentos<br><b>S/ <?= number_format((float) $detalle['descuentos'], 2) ?></b></div></div>
            <div class="mt-4 overflow-x-auto"><table class="min-w-full text-xs"><thead class="text-left uppercase text-slate-500"><tr><th class="py-2">Concepto</th><th>Cantidad</th><th>Importe</th></tr></thead><tbody><?php foreach ($detalle['conceptos'] as $concepto): ?><tr class="border-t"><td class="py-2"><?= htmlspecialchars($concepto['nombre']) ?></td><td><?= number_format((float) $concepto['cantidad'], 2) ?></td><td class="font-semibold <?= (float) $concepto['importe'] < 0 ? 'text-rose-700' : 'text-emerald-700' ?>">S/ <?= number_format((float) $concepto['importe'], 2) ?></td></tr><?php endforeach; ?></tbody></table></div>
        </article>
    <?php endforeach; ?>
    <?php if (!$detalles): ?><div class="rounded-2xl border bg-white p-8 text-center text-slate-500">El período todavía no tiene un borrador.</div><?php endif; ?>
</section>

<script>
document.getElementById('btn-reabrir-planilla')?.addEventListener('click', async () => {
    const form = document.getElementById('form-reabrir-planilla');
    let motivo = '';
    if (window.Swal) {
        const resultado = await Swal.fire({
            icon: 'warning',
            title: '¿Reabrir el cálculo de planilla?',
            text: 'Se restaurará el estado calculado para permitir correcciones. Esta acción solo es posible porque todavía no existen pagos.',
            input: 'textarea',
            inputLabel: 'Motivo obligatorio',
            inputPlaceholder: 'Explique por qué debe reabrirse…',
            inputAttributes: { maxlength: '500' },
            showCancelButton: true,
            confirmButtonText: 'Sí, reabrir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#7c3aed',
            inputValidator: value => value.trim().length < 5 ? 'Ingrese un motivo de al menos 5 caracteres.' : undefined
        });
        if (!resultado.isConfirmed) return;
        motivo = resultado.value.trim();
    } else {
        motivo = (window.prompt('Indique el motivo de reapertura:') || '').trim();
        if (motivo.length < 5) return;
    }
    form.querySelector('[name="motivo_reapertura"]').value = motivo;
    HTMLFormElement.prototype.submit.call(form);
});
</script>
