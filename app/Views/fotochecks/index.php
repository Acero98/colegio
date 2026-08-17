<div class="mb-6">
    <p class="text-sm font-medium text-blue-600">Identificación segura</p>
    <h1 class="text-2xl font-bold">Fotochecks y códigos QR</h1>
    <p class="mt-1 text-sm text-slate-500">Disponible para alumnos, docentes y administrativos. El QR contiene un token seguro y puede renovarse.</p>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table id="tabla-fotochecks" data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Persona</th>
                    <th class="px-5 py-4">Perfil</th>
                    <th class="px-5 py-4">Código / documento</th>
                    <th class="px-5 py-4">Sección / función</th>
                    <th class="px-5 py-4">QR</th>
                    <th class="no-sort px-5 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($personas as $persona): ?><tr>
                        <td class="px-5 py-4"><strong class="block"><?= htmlspecialchars($persona['apellidos'] . ', ' . $persona['nombres']) ?></strong><span class="text-xs text-slate-500"><?= htmlspecialchars($persona['numero_documento']) ?></span></td>
                        <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700"><?= htmlspecialchars($persona['tipo_fotocheck']) ?></span></td>
                        <td class="px-5 py-4 font-mono text-xs"><?= htmlspecialchars($persona['codigo_alumno']) ?></td>
                        <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($persona['seccion']) ?></td>
                        <td class="px-5 py-4"><?php if ($persona['id_qr']): ?><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Activo</span><?php else: ?><span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">Pendiente</span><?php endif; ?></td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex gap-2"><?php if ($persona['id_qr']): ?><a href="index.php?url=fotocheck_ver&id=<?= $persona['id_persona'] ?>" target="_blank" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700"><i class="bi bi-eye"></i> Ver</a><?php endif; ?><?php if (tienePermiso('fotochecks.generar')): ?><form method="post" action="index.php?url=fotocheck_generar" data-confirmar-accion="fotocheck-generar"><?= campoCsrf() ?><input type="hidden" name="id_persona" value="<?= $persona['id_persona'] ?>"><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="bi bi-qr-code"></i> <?= $persona['id_qr'] ? 'Renovar' : 'Generar' ?></button></form><?php endif; ?></div>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$personas): ?><tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">No hay alumnos o personal habilitado para generar fotocheck.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>