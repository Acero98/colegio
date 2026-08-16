<div class="mb-6">
    <p class="text-sm font-medium text-blue-600">Identificación segura</p>
    <h1 class="text-2xl font-bold">Fotochecks y códigos QR</h1>
    <p class="mt-1 text-sm text-slate-500">El QR contiene un token seguro, no datos personales. Al renovarlo, el anterior queda invalidado.</p>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table id="tabla-fotochecks" data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Alumno</th>
                    <th class="px-5 py-4">Código</th>
                    <th class="px-5 py-4">Sección</th>
                    <th class="px-5 py-4">QR</th>
                    <th class="no-sort px-5 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($alumnos as $alumno): ?><tr>
                        <td class="px-5 py-4"><strong class="block"><?= htmlspecialchars($alumno['apellidos'] . ', ' . $alumno['nombres']) ?></strong><span class="text-xs text-slate-500"><?= htmlspecialchars($alumno['numero_documento']) ?></span></td>
                        <td class="px-5 py-4 font-mono text-xs"><?= htmlspecialchars($alumno['codigo_alumno']) ?></td>
                        <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($alumno['seccion']) ?></td>
                        <td class="px-5 py-4"><?php if ($alumno['id_qr']): ?><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Activo</span><?php else: ?><span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">Pendiente</span><?php endif; ?></td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex gap-2"><?php if ($alumno['id_qr']): ?><a href="index.php?url=fotocheck_ver&id=<?= $alumno['id_persona'] ?>" target="_blank" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700"><i class="bi bi-eye"></i> Ver</a><?php endif; ?><?php if (tienePermiso('fotochecks.generar')): ?><form method="post" action="index.php?url=fotocheck_generar" onsubmit="return confirm('¿Generar un nuevo QR? El anterior quedará invalidado.')"><?= campoCsrf() ?><input type="hidden" name="id_persona" value="<?= $alumno['id_persona'] ?>"><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="bi bi-qr-code"></i> <?= $alumno['id_qr'] ? 'Renovar' : 'Generar' ?></button></form><?php endif; ?></div>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$alumnos): ?><tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500">No hay alumnos con matrícula activa para generar fotocheck.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>