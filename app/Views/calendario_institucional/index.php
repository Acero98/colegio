<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Configuración institucional</p>
        <h1 class="text-2xl font-bold">Calendario institucional</h1>
        <p class="mt-1 text-sm text-slate-500">Define las fechas que no deben generar faltas ni días exigibles en Planilla.</p>
    </div>
    <form method="get" class="flex items-end gap-2 rounded-xl border border-blue-200 bg-blue-50 p-3">
        <input type="hidden" name="url" value="calendario_institucional">
        <label class="form-label">Año
            <select name="anio" class="form-input mt-1">
                <?php foreach ($anios as $opcionAnio): ?><option value="<?= $opcionAnio ?>" <?= $anio === $opcionAnio ? 'selected' : '' ?>><?= $opcionAnio ?></option><?php endforeach; ?>
            </select>
        </label>
        <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Consultar</button>
    </form>
</div>

<section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold uppercase text-slate-500">Fechas activas</p><p class="mt-1 text-2xl font-bold"><?= $resumen['total'] ?></p></article>
    <article class="rounded-2xl border border-blue-200 bg-blue-50 p-4"><p class="text-xs font-semibold uppercase text-blue-600">Feriados</p><p class="mt-1 text-2xl font-bold text-blue-900"><?= $resumen['FERIADO'] ?></p></article>
    <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs font-semibold uppercase text-amber-700">No laborables</p><p class="mt-1 text-2xl font-bold text-amber-900"><?= $resumen['NO_LABORABLE'] ?></p></article>
    <article class="rounded-2xl border border-rose-200 bg-rose-50 p-4"><p class="text-xs font-semibold uppercase text-rose-600">Suspensiones</p><p class="mt-1 text-2xl font-bold text-rose-900"><?= $resumen['SUSPENSION'] ?></p></article>
</section>

<div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
    <b>Importante:</b> los cambios se aplican a nuevos cierres de asistencia y a planillas abiertas o recalculadas. Las planillas cerradas conservan sus importes históricos.
</div>

<section class="grid gap-5 lg:grid-cols-[380px_1fr]">
    <article class="rounded-2xl border border-blue-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Nueva fecha institucional</h2>
        <p class="mt-1 text-sm text-slate-500">Puede registrar fechas pasadas o futuras.</p>
        <form method="post" action="index.php?url=calendario_guardar" data-confirmar-accion="calendario-guardar" class="mt-4 grid gap-3">
            <?= campoCsrf() ?>
            <label class="form-label">Fecha<input required type="date" name="fecha" value="<?= $anio . '-01-01' ?>" class="form-input"></label>
            <label class="form-label">Tipo<select required name="tipo" class="form-input"><option value="FERIADO">Feriado</option><option value="NO_LABORABLE">Día no laborable</option><option value="SUSPENSION">Suspensión de actividades</option></select></label>
            <label class="form-label">Descripción<input required minlength="3" maxlength="200" name="descripcion" class="form-input" placeholder="Ej. Fiestas Patrias"></label>
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Guardar fecha</button>
        </form>
    </article>

    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold">Fechas registradas · <?= $anio ?></h2><p class="text-sm text-slate-500">Los registros inactivos permanecen visibles y pueden reactivarse.</p></div>
        <div class="overflow-x-auto">
            <table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Descripción</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Responsable</th><th class="no-sort px-4 py-3 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($registros as $registro):
                    $claseTipo = $registro['tipo'] === 'FERIADO' ? 'bg-blue-100 text-blue-800' : ($registro['tipo'] === 'NO_LABORABLE' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800');
                    $nombreTipo = $registro['tipo'] === 'FERIADO' ? 'Feriado' : ($registro['tipo'] === 'NO_LABORABLE' ? 'No laborable' : 'Suspensión');
                ?>
                    <tr>
                        <td class="px-4 py-3 font-semibold"><?= date('d/m/Y', strtotime($registro['fecha'])) ?><small class="block font-normal text-slate-500"><?= ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][(int) date('w', strtotime($registro['fecha']))] ?></small></td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= $claseTipo ?>"><?= $nombreTipo ?></span></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($registro['descripcion']) ?></td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= $registro['estado'] === 'ACTIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' ?>"><?= htmlspecialchars($registro['estado']) ?></span></td>
                        <td class="px-4 py-3 text-xs text-slate-500"><?= htmlspecialchars($registro['responsable']) ?><br><?= htmlspecialchars($registro['actualizado_en'] ?: $registro['creado_en']) ?></td>
                        <td class="px-4 py-3 text-right"><div class="inline-flex gap-2">
                            <button type="button" onclick="editarFechaInstitucional(this)" data-id="<?= $registro['id_calendario'] ?>" data-fecha="<?= htmlspecialchars($registro['fecha']) ?>" data-tipo="<?= htmlspecialchars($registro['tipo']) ?>" data-descripcion="<?= htmlspecialchars($registro['descripcion'], ENT_QUOTES, 'UTF-8') ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-800 hover:bg-amber-200" title="Editar fecha"><i class="bi bi-pencil-square"></i></button>
                            <form method="post" action="index.php?url=calendario_estado" data-confirmar-accion="<?= $registro['estado'] === 'ACTIVO' ? 'calendario-desactivar' : 'calendario-activar' ?>"><?= campoCsrf() ?><input type="hidden" name="id_calendario" value="<?= $registro['id_calendario'] ?>"><input type="hidden" name="anio" value="<?= $anio ?>"><input type="hidden" name="estado" value="<?= $registro['estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO' ?>"><button class="inline-flex h-10 w-10 items-center justify-center rounded-lg <?= $registro['estado'] === 'ACTIVO' ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' ?>" title="<?= $registro['estado'] === 'ACTIVO' ? 'Desactivar fecha' : 'Activar fecha' ?>"><i class="bi <?= $registro['estado'] === 'ACTIVO' ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i></button></form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$registros): ?><tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay fechas institucionales registradas para <?= $anio ?>.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<div id="modal-editar-calendario" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-10 max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Editar fecha institucional</h2>
        <p class="mt-1 text-sm text-slate-500">Los cambios afectarán únicamente procesos abiertos o futuros.</p>
        <form method="post" action="index.php?url=calendario_actualizar" data-confirmar-accion="calendario-editar" class="mt-4 grid gap-3">
            <?= campoCsrf() ?><input id="ci-id" type="hidden" name="id_calendario">
            <label class="form-label">Fecha<input id="ci-fecha" required type="date" name="fecha" class="form-input"></label>
            <label class="form-label">Tipo<select id="ci-tipo" required name="tipo" class="form-input"><option value="FERIADO">Feriado</option><option value="NO_LABORABLE">Día no laborable</option><option value="SUSPENSION">Suspensión de actividades</option></select></label>
            <label class="form-label">Descripción<input id="ci-descripcion" required minlength="3" maxlength="200" name="descripcion" class="form-input"></label>
            <button class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-amber-950">Guardar cambios</button>
        </form>
    </div>
</div>

<script>
function editarFechaInstitucional(boton) {
    document.getElementById('ci-id').value = boton.dataset.id;
    document.getElementById('ci-fecha').value = boton.dataset.fecha;
    document.getElementById('ci-tipo').value = boton.dataset.tipo;
    document.getElementById('ci-descripcion').value = boton.dataset.descripcion;
    document.getElementById('modal-editar-calendario').classList.remove('hidden');
}
</script>
