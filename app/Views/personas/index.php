<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Base institucional</p>
        <h1 class="text-2xl font-bold">Personas</h1>
        <p class="mt-1 text-sm text-slate-500">Gestione alumnos, docentes, administrativos y apoderados sin duplicar información.</p>
    </div><button onclick="document.getElementById('modal-persona').classList.remove('hidden')" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><i class="bi bi-plus-lg"></i> Nueva persona</button>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table id="tabla-personas" data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                <tr>
                    <th class="px-5 py-4">N°</th>
                    <th class="px-5 py-4">Documento</th>
                    <th class="px-5 py-4">Nombres y apellidos</th>
                    <th class="px-5 py-4">Perfiles</th>
                    <th class="px-5 py-4">Contacto</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="no-sort px-5 py-4">Acciones</th>
                </tr>
            </thead>
            <tbody id="lista-personas" class="divide-y divide-slate-100"><?php foreach ($personas as $i => $p): ?><tr data-buscar="<?= htmlspecialchars(strtolower($p['tipo_documento'] . ' ' . $p['numero_documento'] . ' ' . $p['nombres'] . ' ' . $p['apellidos'] . ' ' . $p['perfiles'] . ' ' . $p['estado']), ENT_QUOTES) ?>">
                        <td class="px-5 py-4 numero-persona"><?= $i + 1 ?></td>
                        <td class="px-5 py-4"><span class="block text-xs text-slate-400"><?= htmlspecialchars($p['tipo_documento']) ?></span><?= htmlspecialchars($p['numero_documento']) ?></td>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($p['perfiles'] ?: 'Sin perfil') ?></td>
                        <td class="px-5 py-4 text-slate-500"><?= htmlspecialchars($p['telefono'] ?: $p['correo'] ?: '—') ?></td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $p['estado'] === 'ACTIVO' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>"><?= $p['estado'] ?></span></td>
                        <td class="px-5 py-4"><button class="btn-editar" title="Editar" data-id="<?= $p['id_persona'] ?>" data-tipo="<?= $p['tipo_documento'] ?>" data-doc="<?= htmlspecialchars($p['numero_documento'], ENT_QUOTES) ?>" data-nombres="<?= htmlspecialchars($p['nombres'], ENT_QUOTES) ?>" data-apellidos="<?= htmlspecialchars($p['apellidos'], ENT_QUOTES) ?>" data-telefono="<?= htmlspecialchars($p['telefono'], ENT_QUOTES) ?>" data-correo="<?= htmlspecialchars($p['correo'], ENT_QUOTES) ?>" data-estado="<?= $p['estado'] ?>" data-perfiles="<?= htmlspecialchars($p['perfiles'], ENT_QUOTES) ?>" onclick="editarPersona(this)"><i class="bi bi-pencil-square"></i></button></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<template id="plantilla-estado-persona">
    <form method="post" action="index.php?url=personas_cambiar_estado" class="inline-flex" data-confirmar-accion>
        <?= campoCsrf() ?>
        <input type="hidden" name="id_persona">
        <input type="hidden" name="estado">
        <button type="submit" class="btn-estado"></button>
    </form>
</template>
<div id="modal-persona" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="modal-persona-contenido rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Nueva persona</h2>
        <form data-confirmar-accion="guardar" method="post" action="index.php?url=personas_guardar" enctype="multipart/form-data" class="mt-5 grid gap-4 sm:grid-cols-2"><?= campoCsrf() ?><label class="form-label">Tipo documento<select name="tipo_documento" class="form-input">
                    <option>DNI</option>
                    <option>CE</option>
                    <option>PASAPORTE</option>
                </select></label><label class="form-label">Número documento<input required name="numero_documento" maxlength="20" class="form-input"></label><label class="form-label">Nombres<input required name="nombres" class="form-input"></label><label class="form-label">Apellidos<input required name="apellidos" class="form-input"></label><label class="form-label">Teléfono<input name="telefono" class="form-input"></label><label class="form-label">Correo<input type="email" name="correo" class="form-input"></label><label class="form-label sm:col-span-2">Fotografía <input type="file" name="fotografia" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm"></label>
            <fieldset class="sm:col-span-2">
                <legend class="mb-2 text-sm font-medium">Perfiles</legend>
                <div class="flex flex-wrap gap-3"><?php foreach (['ALUMNO' => 'Alumno', 'DOCENTE' => 'Docente', 'ADMINISTRATIVO' => 'Administrativo', 'APODERADO' => 'Apoderado'] as $v => $e): ?><label class="flex items-center gap-2 rounded-xl border px-3 py-2 text-sm"><input type="checkbox" name="perfiles[]" value="<?= $v ?>"> <?= $e ?></label><?php endforeach; ?></div>
            </fieldset>
        </form>
    </div>
</div>
<div id="modal-editar-persona" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="modal-persona-contenido rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Editar persona</h2>
        <form data-confirmar-accion="editar" method="post" action="index.php?url=personas_actualizar" enctype="multipart/form-data" class="mt-5 grid gap-4 sm:grid-cols-2"><?= campoCsrf() ?><input id="ep-id" type="hidden" name="id_persona"><label class="form-label">Tipo documento<select id="ep-tipo" name="tipo_documento" class="form-input">
                    <option>DNI</option>
                    <option>CE</option>
                    <option>PASAPORTE</option>
                </select></label><label class="form-label">Número documento<input id="ep-doc" required name="numero_documento" class="form-input"></label><label class="form-label">Nombres<input id="ep-nombres" required name="nombres" class="form-input"></label><label class="form-label">Apellidos<input id="ep-apellidos" required name="apellidos" class="form-input"></label><label class="form-label">Teléfono<input id="ep-telefono" name="telefono" class="form-input"></label><label class="form-label">Correo<input id="ep-correo" type="email" name="correo" class="form-input"></label><label class="form-label sm:col-span-2">Cambiar fotografía <span class="font-normal text-slate-400">(opcional)</span><input type="file" name="fotografia" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm"></label>
            <fieldset class="sm:col-span-2">
                <legend class="mb-2 text-sm font-medium">Perfiles</legend>
                <div class="flex flex-wrap gap-3"><?php foreach (['ALUMNO' => 'Alumno', 'DOCENTE' => 'Docente', 'ADMINISTRATIVO' => 'Administrativo', 'APODERADO' => 'Apoderado'] as $v => $e): ?><label class="flex items-center gap-2 rounded-xl border px-3 py-2 text-sm"><input class="ep-perfil" type="checkbox" name="perfiles[]" value="<?= $v ?>"> <?= $e ?></label><?php endforeach; ?></div>
            </fieldset>
        </form>
    </div>
</div>
<script>
    function editarPersona(b) {
        ['id', 'doc', 'nombres', 'apellidos', 'telefono', 'correo'].forEach(k => document.getElementById('ep-' + k).value = b.dataset[k] || '');
        document.getElementById('ep-tipo').value = b.dataset.tipo;
        let ps = (b.dataset.perfiles || '').split(',').map(x => x.trim());
        document.querySelectorAll('.ep-perfil').forEach(x => x.checked = ps.includes(x.value));
        document.getElementById('modal-editar-persona').classList.remove('hidden');
    }
</script>
