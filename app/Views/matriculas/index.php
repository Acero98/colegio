<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Gestión académica</p>
        <h1 class="text-2xl font-bold">Matrículas</h1>
        <p class="mt-1 text-sm text-slate-500">Vincule al alumno con el período, sección y apoderado principal.</p>
    </div><button onclick="document.getElementById('modal-matricula').classList.remove('hidden')" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><i class="bi bi-plus-lg"></i> Nueva matrícula</button>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Código</th>
                    <th class="px-5 py-4">Alumno</th>
                    <th class="px-5 py-4">Período / sección</th>
                    <th class="px-5 py-4">Apoderado principal</th>
                    <th class="px-5 py-4">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($matriculas as $matricula): ?><tr>
                        <td class="px-5 py-4 font-mono text-xs"><?= htmlspecialchars($matricula['codigo_alumno']) ?></td>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($matricula['alumno']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($matricula['periodo']) ?><span class="block text-xs text-slate-500"><?= htmlspecialchars($matricula['seccion']) ?></span></td>
                        <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($matricula['apoderados'] ?: '—') ?></td>
                        <td class="px-5 py-4"><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700"><?= htmlspecialchars($matricula['estado']) ?></span></td>
                    </tr><?php endforeach; ?><?php if (!$matriculas): ?><tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500">Aún no hay matrículas registradas.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-matricula" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-8 max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold">Nueva matrícula</h2>
                <p class="text-sm text-slate-500">El código se genera automáticamente si se deja vacío.</p>
            </div><button onclick="document.getElementById('modal-matricula').classList.add('hidden')" class="rounded-lg p-2 hover:bg-slate-100"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="index.php?url=matriculas_guardar" class="mt-5 grid gap-4 sm:grid-cols-2"><?= campoCsrf() ?><label class="form-label sm:col-span-2">Alumno<select required name="id_alumno" class="form-input">
                    <option value="">Seleccione un alumno</option><?php foreach ($opciones['alumnos'] as $alumno): ?><option value="<?= $alumno['id_persona'] ?>"><?= htmlspecialchars($alumno['apellidos'] . ', ' . $alumno['nombres'] . ' · ' . $alumno['numero_documento']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Período<select required name="id_periodo" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($opciones['periodos'] as $periodo): ?><option value="<?= $periodo['id_periodo_academico'] ?>"><?= htmlspecialchars($periodo['nombre']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Sección<select required name="id_seccion" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($opciones['secciones'] as $seccion): ?><option value="<?= $seccion['id_seccion'] ?>"><?= htmlspecialchars($seccion['nombre'] . ($seccion['capacidad'] === null ? ' · Sin límite' : ' · ' . $seccion['matriculados'] . '/' . $seccion['capacidad'])) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Fecha<input required type="date" name="fecha_matricula" value="<?= date('Y-m-d') ?>" class="form-input"></label><label class="form-label">Código alumno <span class="font-normal text-slate-400">(opcional)</span><input name="codigo_alumno" class="form-input"></label><label class="form-label sm:col-span-2">Apoderado principal<select name="id_apoderado" class="form-input">
                    <option value="">Se asignará después</option><?php foreach ($opciones['apoderados'] as $apoderado): ?><option value="<?= $apoderado['id_persona'] ?>"><?= htmlspecialchars($apoderado['apellidos'] . ', ' . $apoderado['nombres']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Parentesco<input name="parentesco" value="Apoderado" class="form-input"></label>
            <div class="flex items-end justify-end gap-3"><button type="button" onclick="document.getElementById('modal-matricula').classList.add('hidden')" class="rounded-xl px-4 py-2 text-sm font-medium hover:bg-slate-100">Cancelar</button><button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Matricular</button></div>
        </form>
    </div>
</div>