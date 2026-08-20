<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div><p class="text-sm font-medium text-violet-600">Personal</p><h1 class="text-2xl font-bold">Vínculos laborales</h1><p class="mt-1 text-sm text-slate-500">Administre ingresos, transiciones y cambios remunerativos conservando el historial.</p></div>
    <a href="index.php?url=planilla" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Volver a Planilla</a>
</div>

<div class="mb-5 rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm text-violet-900"><b>Edición controlada:</b> un cambio con nueva vigencia finaliza la versión anterior y crea otra. Las planillas cerradas permanecen intactas.</div>

<section class="grid gap-5 lg:grid-cols-[420px_1fr]">
    <article class="rounded-2xl border border-violet-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Nuevo vínculo o transición</h2>
        <form method="post" action="index.php?url=vinculo_laboral_guardar" data-confirmar-accion="transicion-laboral" class="mt-4 grid gap-3">
            <?= campoCsrf() ?>
            <label class="form-label">Persona<select required name="id_persona" class="form-input"><option value="">Seleccione</option><?php foreach ($personas as $p): ?><option value="<?= $p['id_persona'] ?>"><?= htmlspecialchars($p['apellidos'] . ', ' . $p['nombres'] . ' · ' . ($p['codigo_trabajador'] ?: 'Sin código')) ?></option><?php endforeach; ?></select></label>
            <label class="form-label">Nuevo tipo<select required name="tipo" class="form-input"><option value="DOCENTE">Docente</option><option value="ADMINISTRATIVO">Administrativo</option></select></label>
            <label class="form-label">Cargo<input required name="cargo" placeholder="Ej. Director, secretaria o docente" class="form-input"></label>
            <div class="grid gap-3 rounded-xl bg-slate-50 p-3 sm:grid-cols-3"><label class="form-label text-xs">Pago mensual<input min="0" step=".01" type="number" name="monto_mensual" value="0" class="form-input"></label><label class="form-label text-xs">Pago por hora<input min="0" step=".01" type="number" name="monto_hora" value="0" class="form-input"></label><label class="form-label text-xs">Pago por sesión<input min="0" step=".01" type="number" name="monto_sesion" value="0" class="form-input"></label></div>
            <fieldset class="rounded-xl border border-slate-200 p-3"><legend class="px-1 text-xs font-semibold text-slate-600">Días laborables</legend><div class="mt-1 grid grid-cols-4 gap-2 text-xs sm:grid-cols-7"><?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $indice => $dia): ?><label class="flex items-center gap-1"><input type="checkbox" name="dias_laborables[]" value="<?= $indice + 1 ?>" <?= $indice < 5 ? 'checked' : '' ?>><?= $dia ?></label><?php endforeach; ?></div></fieldset>
            <div class="grid grid-cols-2 gap-3"><label class="form-label">Inicio<input required type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" class="form-input"></label><label class="form-label">Fin <span class="font-normal text-slate-400">(opcional)</span><input type="date" name="fecha_fin" class="form-input"></label></div>
            <button class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white">Guardar vínculo</button>
        </form>
    </article>

    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold">Historial laboral</h2><p class="text-sm text-slate-500">Edite únicamente la versión activa; las anteriores quedan como consulta.</p></div>
        <div class="overflow-x-auto"><table data-datatable data-page-options="5,10,25,50" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Persona</th><th class="px-4 py-3">Tipo / cargo</th><th class="px-4 py-3">Componentes</th><th class="px-4 py-3">Días</th><th class="px-4 py-3">Vigencia</th><th class="px-4 py-3">Estado</th><th class="no-sort px-4 py-3 text-right">Acción</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($vinculos as $v):
                $etiquetasDias = [1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb',7=>'Dom'];
                $diasVinculo = array_filter(array_map('intval', explode(',', $v['dias_laborables'] ?: '1,2,3,4,5')));
                $textoDias = implode(', ', array_map(function ($dia) use ($etiquetasDias) { return $etiquetasDias[$dia] ?? ''; }, $diasVinculo));
            ?><tr>
                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($v['persona']) ?><?php if ($v['id_vinculo_anterior']): ?><small class="block text-violet-600">Versión de vínculo #<?= $v['id_vinculo_anterior'] ?></small><?php endif; ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($v['tipo']) ?><small class="block text-slate-500"><?= htmlspecialchars($v['cargo']) ?></small></td>
                <td class="px-4 py-3 text-xs">Mensual: S/ <?= number_format((float)$v['monto_mensual'], 2) ?><br>Hora: S/ <?= number_format((float)$v['monto_hora'], 2) ?><br>Sesión: S/ <?= number_format((float)$v['monto_sesion'], 2) ?></td>
                <td class="px-4 py-3 text-xs"><?= htmlspecialchars($textoDias) ?></td>
                <td class="px-4 py-3 text-xs"><?= htmlspecialchars($v['fecha_inicio']) ?><br><?= htmlspecialchars($v['fecha_fin'] ?: 'Vigente') ?><?php if ($v['motivo_cambio']): ?><small class="mt-1 block text-slate-500"><?= htmlspecialchars($v['motivo_cambio']) ?></small><?php endif; ?></td>
                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= $v['estado'] === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' ?>"><?= htmlspecialchars($v['estado']) ?></span></td>
                <td class="px-4 py-3 text-right"><?php if ($v['estado'] === 'ACTIVO'): ?><button type="button" onclick="editarComponentesVinculo(this)" data-id="<?= $v['id_vinculo'] ?>" data-persona="<?= htmlspecialchars($v['persona'], ENT_QUOTES, 'UTF-8') ?>" data-tipo="<?= htmlspecialchars($v['tipo'], ENT_QUOTES, 'UTF-8') ?>" data-cargo="<?= htmlspecialchars($v['cargo'], ENT_QUOTES, 'UTF-8') ?>" data-inicio="<?= $v['fecha_inicio'] ?>" data-mensual="<?= $v['monto_mensual'] ?>" data-hora="<?= $v['monto_hora'] ?>" data-sesion="<?= $v['monto_sesion'] ?>" data-dias="<?= htmlspecialchars($v['dias_laborables']) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-800 hover:bg-amber-200" title="Editar componentes y días"><i class="bi bi-pencil-square"></i></button><?php endif; ?></td>
            </tr><?php endforeach; ?>
            </tbody>
        </table></div>
    </article>
</section>

<div id="modal-versionar-vinculo" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-8 max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Editar componentes del vínculo</h2><p id="ev-trabajador" class="mt-1 text-sm text-slate-500"></p>
        <form method="post" action="index.php?url=vinculo_laboral_versionar" data-confirmar-accion="vinculo-versionar" class="mt-4 grid gap-4">
            <?= campoCsrf() ?><input id="ev-id" type="hidden" name="id_vinculo">
            <div class="grid gap-3 rounded-xl bg-amber-50 p-3 sm:grid-cols-3"><label class="form-label text-xs">Pago mensual<input id="ev-mensual" required min="0" step=".01" type="number" name="monto_mensual" class="form-input"></label><label class="form-label text-xs">Pago por hora<input id="ev-hora" required min="0" step=".01" type="number" name="monto_hora" class="form-input"></label><label class="form-label text-xs">Pago por sesión<input id="ev-sesion" required min="0" step=".01" type="number" name="monto_sesion" class="form-input"></label></div>
            <fieldset class="rounded-xl border border-slate-200 p-3"><legend class="px-1 text-xs font-semibold text-slate-600">Días laborables</legend><div class="mt-1 grid grid-cols-4 gap-2 text-xs sm:grid-cols-7"><?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $indice => $dia): ?><label class="flex items-center gap-1"><input id="ev-dia-<?= $indice + 1 ?>" type="checkbox" name="dias_laborables[]" value="<?= $indice + 1 ?>"><?= $dia ?></label><?php endforeach; ?></div></fieldset>
            <label class="form-label">Vigente desde<input id="ev-vigencia" required max="<?= date('Y-m-d') ?>" type="date" name="vigencia_desde" value="<?= date('Y-m-d') ?>" class="form-input"><small class="mt-1 block font-normal text-slate-500">No se permiten fechas futuras. Si ya existen planillas, debe ser posterior al inicio actual.</small></label>
            <label class="form-label">Motivo del cambio<input required minlength="3" maxlength="250" name="motivo_cambio" class="form-input" placeholder="Ej. Incremento remunerativo desde agosto"></label>
            <button class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-amber-950">Guardar nueva versión</button>
        </form>
    </div>
</div>

<script>
function editarComponentesVinculo(boton) {
    document.getElementById('ev-id').value = boton.dataset.id;
    document.getElementById('ev-trabajador').textContent = boton.dataset.persona + ' · ' + boton.dataset.tipo + ' · ' + boton.dataset.cargo;
    document.getElementById('ev-mensual').value = boton.dataset.mensual;
    document.getElementById('ev-hora').value = boton.dataset.hora;
    document.getElementById('ev-sesion').value = boton.dataset.sesion;
    const dias = boton.dataset.dias.split(',');
    for (let dia = 1; dia <= 7; dia++) document.getElementById('ev-dia-' + dia).checked = dias.includes(String(dia));
    const vigencia = document.getElementById('ev-vigencia');
    vigencia.min = boton.dataset.inicio;
    vigencia.value = '<?= date('Y-m-d') ?>';
    document.getElementById('modal-versionar-vinculo').classList.remove('hidden');
}
</script>
