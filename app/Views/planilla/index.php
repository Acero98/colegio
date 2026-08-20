<form method="get" class="mb-5 flex gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-4"><input type="hidden" name="url" value="planilla_previa"><select required name="id" class="form-input mt-0">
        <option value="">Vista previa de un período</option><?php foreach ($periodos as $p): ?><option value="<?= $p['id_periodo_pago'] ?>"><?= htmlspecialchars($p['nombre']) ?></option><?php endforeach; ?>
    </select><button class="rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white">Previsualizar</button></form>
<div class="mb-4 flex justify-end gap-2"><a href="index.php?url=planilla_historial" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">Historial por trabajador</a><a href="index.php?url=planilla_reglas" class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700">Gestionar reglas</a></div>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Pagos al personal</p>
        <h1 class="text-2xl font-bold">Planilla</h1>
        <p class="mt-1 text-sm text-slate-500">Cree períodos y configure descuentos; los vínculos se gestionan en una sola pantalla.</p>
    </div>
    <div class="flex flex-wrap gap-2"><a href="index.php?url=vinculos_laborales" class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700">Vínculos laborales</a><button type="button" onclick="document.getElementById('modal-periodo').classList.remove('hidden')" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Nuevo período</button></div>
</div>
<section class="mb-6 overflow-hidden rounded-2xl border bg-white">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold">Períodos de pago</h2>
    </div>
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-4">Período</th>
                    <th class="px-5 py-4">Rango</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="no-sort px-5 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y"><?php foreach ($periodos as $p): ?><tr>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($p['nombre']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($p['fecha_inicio']) ?> a <?= htmlspecialchars($p['fecha_fin']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($p['estado']) ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3"><?php if(in_array($p['estado'],['ABIERTO','CALCULADO'],true)): ?><form method="post" action="index.php?url=planilla_calcular" data-confirmar-accion="planilla-calcular"><?= campoCsrf() ?><input type="hidden" name="id_periodo_pago" value="<?= $p['id_periodo_pago'] ?>"><button class="text-sm font-semibold text-blue-600"><?= $p['estado']==='CALCULADO'?'Recalcular':'Generar borrador' ?></button></form><?php endif; ?><?php if($p['estado']!=='ABIERTO'): ?><a href="index.php?url=planilla_revision&id=<?= $p['id_periodo_pago'] ?>" class="text-sm font-semibold text-emerald-700"><?= $p['estado']==='CALCULADO'?'Revisar':'Ver detalle' ?></a><?php endif; ?></div>
                        </td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<section class="overflow-hidden rounded-2xl border bg-white">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold">Vínculos registrados</h2>
        <p class="text-sm text-slate-500">Los componentes se sumarán automáticamente en el período que corresponda.</p>
    </div>
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-4">Trabajador</th>
                    <th class="px-5 py-4">Tipo</th>
                    <th class="px-5 py-4">Modalidad</th>
                    <th class="px-5 py-4">Componentes de pago</th>
                    <th class="px-5 py-4">Días</th>
                    <th class="px-5 py-4">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y"><?php foreach ($vinculos as $v): ?><tr>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($v['persona']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($v['tipo']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($v['modalidad_pago']) ?></td>
                        <td class="px-5 py-4 text-xs">Mensual: S/ <?= number_format((float)($v['monto_mensual'] ?? 0), 2) ?><br>Hora: S/ <?= number_format((float)($v['monto_hora'] ?? 0), 2) ?><br>Sesión: S/ <?= number_format((float)($v['monto_sesion'] ?? 0), 2) ?></td>
                        <td class="px-5 py-4 text-xs"><?= htmlspecialchars($v['dias_laborables'] ?? '1,2,3,4,5') ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($v['estado']) ?></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-periodo" class="fixed inset-0 z-[60] hidden bg-slate-900/45 p-4">
    <div class="mx-auto mt-10 max-w-lg rounded-2xl bg-white p-6">
        <h2 class="text-lg font-bold">Período de pago</h2>
        <form method="post" action="index.php?url=planilla_periodo" class="mt-4 grid gap-3"><?= campoCsrf() ?><label class="form-label">Nombre<input required name="nombre" placeholder="Ej. Agosto 2026" class="form-input"></label>
            <div class="grid grid-cols-2 gap-3"><label class="form-label">Inicio<input required type="date" name="fecha_inicio" class="form-input"></label><label class="form-label">Fin<input required type="date" name="fecha_fin" class="form-input"></label></div><button class="rounded-xl bg-blue-600 p-2 text-sm font-semibold text-white">Crear período</button>
        </form>
    </div>
</div>
<div id="modal-regla" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-8 max-w-xl rounded-2xl bg-white p-6">
        <h2 class="text-lg font-bold">Reglas de descuento</h2>
        <p class="mt-1 text-sm text-slate-500">La tardanza usa los minutos ya calculados por Asistencia, después de aplicar su tolerancia.</p>
        <form method="post" action="index.php?url=planilla_regla" class="mt-4 grid gap-3"><?= campoCsrf() ?><input required name="nombre" placeholder="Ej. S/ 1 por minuto de tardanza" class="form-input"><select name="evento" class="form-input">
                <option value="TARDANZA">Tardanza</option>
                <option value="FALTA">Falta</option>
            </select><input required min="0" step=".01" type="number" name="monto_unitario" placeholder="Descuento por unidad (S/)" class="form-input"><input required type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" class="form-input"><input type="date" name="fecha_fin" class="form-input"><button class="rounded-xl bg-blue-600 p-2 text-sm font-semibold text-white">Guardar regla</button></form><?php if ($reglas): ?><div class="mt-5 border-t pt-4 text-sm"><?php foreach ($reglas as $r): $f = json_decode($r['formula'], true); ?><p class="mb-2 rounded-lg bg-slate-50 p-3"><b><?= htmlspecialchars($r['nombre']) ?></b><span class="ml-2 text-slate-500"><?= htmlspecialchars($r['evento']) ?> · S/ <?= number_format((float)($f['monto_unitario'] ?? 0), 2) ?></span></p><?php endforeach; ?></div><?php endif; ?>
    </div>
</div>
