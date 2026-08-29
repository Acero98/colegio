<section id="panel-jornada-grupo" data-panel-jornada class="mt-6 hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div>
        <p class="text-sm font-medium text-blue-600">Asignación masiva</p>
        <h2 class="text-lg font-bold">Asignar jornada por grupo</h2>
        <p class="mt-1 text-sm text-slate-500">Selecciona todos y desmarca las excepciones. Al guardar, se reemplaza la jornada activa de las personas elegidas.</p>
    </div>
    <form method="post" action="index.php?url=horarios_guardar" class="mt-5">
        <?= campoCsrf() ?><input type="hidden" name="entidad" value="jornada_grupo">
        <div class="grid gap-3 sm:grid-cols-3"><label class="form-label">Jornada<select required name="id_jornada" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($jornadas as $j): ?><option value="<?= $j['id_jornada'] ?>"><?= htmlspecialchars($j['nombre'] . ' · ' . $j['aplica_a']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Desde<input required type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" class="form-input"></label><label class="form-label">Hasta <span class="font-normal text-slate-400">(opcional)</span><input type="date" name="fecha_fin" class="form-input"></label></div>
        <div class="mt-4 flex items-center justify-between"><strong class="text-sm">Personal docente y administrativo</strong><label class="text-sm font-semibold text-blue-700"><input id="seleccionar-personal" type="checkbox" checked> Seleccionar todos</label></div>
        <div class="mt-3 grid max-h-64 gap-2 overflow-y-auto rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3"><?php foreach ($personas as $p): if (strpos($p['perfiles'], 'DOCENTE') === false && strpos($p['perfiles'], 'ADMINISTRATIVO') === false) continue; ?><label class="flex items-center gap-2 rounded-lg p-2 text-sm hover:bg-slate-50"><input class="persona-grupo" checked type="checkbox" name="personas[]" value="<?= $p['id_persona'] ?>"> <?= htmlspecialchars($p['nombre'] . ' · ' . $p['perfiles']) ?></label><?php endforeach; ?></div>
        <div class="mt-4 flex justify-end"><button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Asignar a seleccionados</button></div>
    </form>
</section>
<script>
    function mostrarPanelJornada(id) {
        document.getElementById(id)?.classList.toggle('hidden');
        document.getElementById(id)?.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    }
    document.getElementById('seleccionar-personal')?.addEventListener('change', e => document.querySelectorAll('.persona-grupo').forEach(c => c.checked = e.target.checked));
    document.addEventListener('DOMContentLoaded', () => document.getElementById('panel-gestionar-jornadas')?.classList.remove('hidden'));
</script>
