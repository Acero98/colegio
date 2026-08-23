<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th data-columna-numero class="no-sort px-5 py-4">N°</th>
                    <th class="px-5 py-4">Código trabajador</th>
                    <th class="px-5 py-4">Documento</th>
                    <th class="px-5 py-4">Nombres y apellidos</th>
                    <th class="px-5 py-4">Cargo</th>
                    <th class="px-5 py-4">Teléfono</th>
                    <th class="px-5 py-4">Correo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($personas as $indice => $persona): ?>
                    <tr>
                        <td class="px-5 py-4 text-slate-500"><?= $indice + 1 ?></td>
                        <td class="px-5 py-4 font-mono text-xs font-semibold text-blue-700"><?= htmlspecialchars($persona['codigo_trabajador'] ?: 'Pendiente') ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($persona['numero_documento']) ?></td>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($persona['apellidos'].', '.$persona['nombres']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($persona['cargo'] ?: 'Sin cargo asignado') ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($persona['telefono'] ?: '—') ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($persona['correo'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if(!$personas): ?><tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No hay registros activos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
