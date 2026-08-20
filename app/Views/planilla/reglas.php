<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-sm font-medium text-violet-600">Configuración</p>
        <h1 class="text-2xl font-bold">Reglas de Planilla</h1>
        <p class="text-sm text-slate-500">Bonificaciones y descuentos con vigencia.</p>
    </div><a href="index.php?url=planilla" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Volver</a>
</div>
<section class="grid gap-5 lg:grid-cols-[380px_1fr]">
    <article class="rounded-2xl border bg-white p-5">
        <h2 class="font-semibold">Nueva regla</h2>
        <form method="post" action="index.php?url=planilla_regla_guardar" class="mt-4 grid gap-3"><?= campoCsrf() ?><input required name="nombre" class="form-input" placeholder="Nombre de la regla"><select name="naturaleza" class="form-input">
                <option value="DESCUENTO">Descuento</option>
                <option value="BONIFICACION">Bonificación fija</option>
            </select><select name="evento" class="form-input">
                <option value="TARDANZA">Por minuto de tardanza</option>
                <option value="FALTA">Por día de falta</option>
                <option value="FIJO">Importe fijo</option>
            </select><input required min="0.01" step="0.01" type="number" name="monto_unitario" class="form-input" placeholder="Importe (S/)">
            <div class="grid grid-cols-2 gap-3"><input required type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" class="form-input"><input type="date" name="fecha_fin" class="form-input"></div><button class="rounded-xl bg-violet-600 py-2.5 text-sm font-semibold text-white">Guardar regla</button>
        </form>
    </article>
    <article class="overflow-hidden rounded-2xl border bg-white">
        <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="p-4">Regla</th>
                    <th class="p-4">Tipo</th>
                    <th class="p-4">Importe</th>
                    <th class="p-4">Vigencia</th>
                    <th class="p-4">Estado</th>
                    <th class="no-sort p-4"></th>
                </tr>
            </thead>
            <tbody><?php foreach ($reglas as $r): $f = json_decode($r['formula'], true); ?><tr class="border-t">
                        <td class="p-4 font-medium"><?= htmlspecialchars($r['nombre']) ?></td>
                        <td class="p-4 text-xs"><?= htmlspecialchars(($r['naturaleza'] ?? 'DESCUENTO') . ' · ' . $r['evento']) ?></td>
                        <td class="p-4">S/ <?= number_format((float)($f['monto_unitario'] ?? 0), 2) ?></td>
                        <td class="p-4 text-xs"><?= $r['fecha_inicio'] ?><br><?= $r['fecha_fin'] ?: 'Sin límite' ?></td>
                        <td class="p-4"><?= $r['estado'] ?></td>
                        <td class="p-4">
                            <form method="post" action="index.php?url=planilla_regla_estado"><?= campoCsrf() ?><input type="hidden" name="id_regla" value="<?= $r['id_regla'] ?>"><input type="hidden" name="estado" value="<?= $r['estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO' ?>"><button class="rounded-lg px-3 py-2 text-xs font-semibold <?= $r['estado'] === 'ACTIVO' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' ?>"><?= $r['estado'] === 'ACTIVO' ? 'Desactivar' : 'Activar' ?></button></form>
                        </td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </article>
</section>