<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Código alumno</th>
                    <th class="px-5 py-4">Documento</th>
                    <th class="px-5 py-4">Nombres y apellidos</th>
                    <th class="px-5 py-4">Nivel / grado / sección</th>
                    <th class="px-5 py-4">Apoderado principal</th>
                    <th class="px-5 py-4">Teléfono</th>
                    <th class="px-5 py-4">Correo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($alumnos as $alumno): ?>
                    <tr>
                        <td class="px-5 py-4 font-mono text-xs font-semibold text-blue-700"><?= htmlspecialchars($alumno['codigo_alumno'] ?: 'Pendiente') ?></td>
                        <td class="px-5 py-4"><span class="block text-xs text-slate-400"><?= htmlspecialchars($alumno['tipo_documento']) ?></span><?= htmlspecialchars($alumno['numero_documento']) ?></td>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($alumno['apellidos'].', '.$alumno['nombres']) ?></td>
                        <td class="px-5 py-4"><?php if($alumno['ubicacion_academica']): ?><span class="font-medium text-slate-700"><?= htmlspecialchars($alumno['ubicacion_academica']) ?></span><?php else: ?><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Sin matrícula activa</span><?php endif; ?></td>
                        <td class="px-5 py-4"><?php if($alumno['apoderado_principal']): ?><span class="font-medium text-slate-700"><?= htmlspecialchars($alumno['apoderado_principal']) ?></span><small class="mt-1 block text-slate-500"><?= htmlspecialchars($alumno['apoderado_parentesco'] ?: 'Parentesco no indicado') ?><?= $alumno['apoderado_telefono'] ? ' · '.htmlspecialchars($alumno['apoderado_telefono']) : '' ?><?= (int)$alumno['apoderados_adicionales'] > 0 ? ' · +'.(int)$alumno['apoderados_adicionales'].' adicional(es)' : '' ?></small><?php else: ?><span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Sin apoderado</span><?php endif; ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($alumno['telefono'] ?: '—') ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($alumno['correo'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if(!$alumnos): ?><tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No hay alumnos activos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
