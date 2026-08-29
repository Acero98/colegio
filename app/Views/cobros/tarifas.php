<?php $colores = ['ACTIVO' => 'bg-emerald-50 text-emerald-700', 'INACTIVO' => 'bg-slate-100 text-slate-600']; ?>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Finanzas escolares</p>
        <h1 class="text-2xl font-bold">Lista de tarifas</h1>
        <p class="mt-1 text-sm text-slate-500">Los importes activos se usan al generar las cuentas por cobrar.</p>
    </div>
    <div class="flex gap-2"><button onclick="document.getElementById('modal-configurar-tarifas').classList.remove('hidden')" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-center text-sm font-semibold text-blue-700"><i class="bi bi-sliders"></i> Conceptos y Tarifas</button><a href="index.php?url=cobros" class="rounded-xl bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white"><i class="bi bi-arrow-left"></i> Volver a Cobros</a></div>
</div>
<section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-bold">Conceptos</h2>
        <p class="text-sm text-slate-500">Define qué se cobra y si permite abonos.</p>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaConceptosCobro" data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Concepto</th>
                    <th class="px-5 py-3">Tipo</th>
                    <th class="px-5 py-3">Pagos parciales</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="no-sort px-5 py-3">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($conceptos as $concepto): ?><tr>
                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($concepto['nombre']) ?></td>
                        <td class="px-5 py-3"><?= htmlspecialchars($concepto['tipo']) ?></td>
                        <td class="px-5 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $concepto['admite_parcial'] ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' ?>"><?= $concepto['admite_parcial'] ? 'Sí, admite abonos' : 'No, pago completo' ?></span></td>
                        <td class="px-5 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $concepto['estado'] === 'ACTIVO' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>"><?= $concepto['estado'] ?></span></td>
                        <td class="px-5 py-3">
                            <form method="post" action="index.php?url=cobros_concepto_estado" data-confirmar-accion="<?= $concepto['estado'] === 'ACTIVO' ? 'concepto-desactivar' : 'concepto-activar' ?>"><?= campoCsrf() ?><input type="hidden" name="id_concepto_cobro" value="<?= $concepto['id_concepto_cobro'] ?>"><input type="hidden" name="estado" value="<?= $concepto['estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO' ?>"><button class="rounded-lg px-3 py-2 text-xs font-semibold <?= $concepto['estado'] === 'ACTIVO' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>"><?= $concepto['estado'] === 'ACTIVO' ? 'Desactivar' : 'Activar' ?></button></form>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$conceptos): ?><tr>
                        <td colspan="5" class="px-5 py-6 text-center text-slate-500">No hay conceptos registrados.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-bold">Tarifas</h2>
        <p class="text-sm text-slate-500">Define cuanto se cobra, en que periodo, a que grados y la vigencia que tiene.</p>
    </div>
    <div class="overflow-x-auto">
        <table id="tablaTarifas" data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Concepto</th>
                    <th class="px-5 py-4">Período</th>
                    <th class="px-5 py-4">Grado</th>
                    <th class="px-5 py-4">Importe</th>
                    <th class="px-5 py-4">Vigencia</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="no-sort px-5 py-4">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($tarifas as $tarifa): ?><tr>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($tarifa['concepto']) ?><span class="block text-xs font-normal text-slate-500"><?= htmlspecialchars($tarifa['tipo']) ?></span></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($tarifa['periodo']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($tarifa['grado'] ?: 'General') ?></td>
                        <td class="px-5 py-4 font-semibold">S/ <?= number_format((float)$tarifa['importe'], 2) ?></td>
                        <td class="px-5 py-4 text-xs"><?= htmlspecialchars($tarifa['vigencia_inicio']) ?><?= $tarifa['vigencia_fin'] ? ' al ' . htmlspecialchars($tarifa['vigencia_fin']) : ' en adelante' ?></td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $colores[$tarifa['estado']] ?>"><?= $tarifa['estado'] ?></span></td>
                        <td class="px-5 py-4">
                            <form method="post" action="index.php?url=cobros_tarifa_estado" data-confirmar-accion="<?= $tarifa['estado'] === 'ACTIVO' ? 'tarifa-desactivar' : 'tarifa-activar' ?>"><?= campoCsrf() ?><input type="hidden" name="id_tarifa" value="<?= $tarifa['id_tarifa'] ?>"><input type="hidden" name="estado" value="<?= $tarifa['estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO' ?>"><button class="rounded-lg px-3 py-2 text-xs font-semibold <?= $tarifa['estado'] === 'ACTIVO' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>"><?= $tarifa['estado'] === 'ACTIVO' ? 'Desactivar' : 'Activar' ?></button></form>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$tarifas): ?><tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">Aún no hay tarifas registradas.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-configurar-tarifas" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-10 max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex justify-between">
            <div>
                <h2 class="text-lg font-bold">Configurar tarifas y conceptos</h2>
                <p class="text-sm text-slate-500">La tarifa por grado tiene prioridad sobre la general.</p>
            </div><button onclick="this.closest('.fixed').classList.add('hidden')" class="p-2"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="mt-5 grid gap-6 md:grid-cols-2">
            <form method="post" action="index.php?url=cobros_configurar" data-confirmar-accion="guardar" class="grid gap-3 rounded-xl bg-slate-50 p-4"><?= campoCsrf() ?><input type="hidden" name="accion" value="concepto">
                <h3 class="font-bold">Nuevo concepto</h3><label class="form-label">Nombre<input required name="nombre" class="form-input"></label><label class="form-label">Tipo<select name="tipo" class="form-input"><?php foreach (['MATRICULA', 'MENSUALIDAD', 'TALLER', 'CUOTA', 'OTRO'] as $tipo): ?><option><?= $tipo ?></option><?php endforeach; ?></select></label><label class="flex gap-2 text-sm"><input type="checkbox" name="admite_parcial" checked> Admite pagos parciales</label><button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Crear concepto</button>
            </form>
            <form method="post" action="index.php?url=cobros_configurar" data-confirmar-accion="guardar" class="grid gap-3 rounded-xl bg-slate-50 p-4"><?= campoCsrf() ?><input type="hidden" name="accion" value="tarifa">
                <h3 class="font-bold">Nueva tarifa</h3><label class="form-label">Concepto<select required name="id_concepto_cobro" class="form-input">
                        <option value="">Seleccione</option><?php foreach ($opciones['conceptos'] as $concepto): ?><option value="<?= $concepto['id_concepto_cobro'] ?>"><?= htmlspecialchars($concepto['nombre']) ?></option><?php endforeach; ?>
                    </select></label><label class="form-label">Período<select required name="id_periodo_academico" class="form-input">
                        <option value="">Seleccione</option><?php foreach ($opciones['periodos'] as $periodo): ?><option value="<?= $periodo['id_periodo_academico'] ?>"><?= htmlspecialchars($periodo['nombre']) ?></option><?php endforeach; ?>
                    </select></label><label class="form-label">Grado<select name="id_grado" class="form-input">
                        <option value="">Tarifa general</option><?php foreach ($grados as $grado): ?><option value="<?= $grado['id_grado'] ?>"><?= htmlspecialchars($grado['nombre']) ?></option><?php endforeach; ?>
                    </select></label><label class="form-label">Importe<input required min="0" step="0.01" type="number" name="importe" class="form-input"></label><label class="form-label">Vigencia desde<input required type="date" name="vigencia_inicio" value="<?= date('Y-m-d') ?>" class="form-input"></label><label class="form-label">Vigencia hasta<input type="date" name="vigencia_fin" class="form-input"></label><button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Registrar tarifa</button>
            </form>
        </div>
    </div>
</div>