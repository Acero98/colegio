<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold">Gestionar jornadas individuales</h2>
        <p class="text-sm text-slate-500">Edita vigencia o jornada. Al quitarla se conserva el historial, pero ya no permitirá marcaciones.</p>
    </div>
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-4">Persona</th>
                    <th class="px-5 py-4">Jornada</th>
                    <th class="px-5 py-4">Vigencia</th>
                    <th class="no-sort px-5 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($asignaciones as $a): ?><tr>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($a['persona']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($a['jornada']) ?></td>
                        <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($a['fecha_inicio'] . ' — ' . ($a['fecha_fin'] ?: 'Sin fin')) ?></td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex gap-2"><button type="button" onclick="editarJornada(this)" data-id="<?= $a['id_asignacion'] ?>" data-jornada="<?= $a['id_jornada'] ?>" data-inicio="<?= $a['fecha_inicio'] ?>" data-fin="<?= $a['fecha_fin'] ?>" class="btn-editar" title="Editar jornada"><i class="bi bi-pencil-square"></i></button>
                                <form method="post" action="index.php?url=horarios_guardar" data-confirmar-accion="jornada-desactivar"><?= campoCsrf() ?><input type="hidden" name="entidad" value="jornada_desactivar"><input type="hidden" name="id_asignacion" value="<?= $a['id_asignacion'] ?>"><button class="rounded-lg bg-red-50 px-2 py-1 text-red-700 hover:bg-red-100" title="Quitar jornada"><i class="bi bi-x-circle"></i></button></form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$asignaciones): ?><tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500">No hay jornadas individuales activas.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-editar-jornada" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-12 max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Editar jornada individual</h2>
        <form method="post" action="index.php?url=horarios_guardar" class="mt-4 grid gap-3"><?= campoCsrf() ?><input type="hidden" name="entidad" value="jornada_actualizar"><input id="ej-id" type="hidden" name="id_asignacion"><label class="form-label">Jornada<select id="ej-jornada" required name="id_jornada" class="form-input"><?php foreach ($jornadas as $j): ?><option value="<?= $j['id_jornada'] ?>"><?= htmlspecialchars($j['nombre'] . ' · ' . $j['aplica_a']) ?></option><?php endforeach; ?></select></label><label class="form-label">Desde<input id="ej-inicio" required type="date" name="fecha_inicio" class="form-input"></label><label class="form-label">Hasta<input id="ej-fin" type="date" name="fecha_fin" class="form-input"></label>
            <div class="flex justify-end gap-2"><button type="button" onclick="document.getElementById('modal-editar-jornada').classList.add('hidden')" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancelar</button><button class="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-amber-950">Guardar cambios</button></div>
        </form>
    </div>
</div>
<script>
    function editarJornada(b) {
        document.getElementById('ej-id').value = b.dataset.id;
        document.getElementById('ej-jornada').value = b.dataset.jornada;
        document.getElementById('ej-inicio').value = b.dataset.inicio;
        document.getElementById('ej-fin').value = b.dataset.fin;
        document.getElementById('modal-editar-jornada').classList.remove('hidden');
    }
</script>