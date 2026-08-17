<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Control diario</p>
        <h1 class="text-2xl font-bold">Registro manual por sección</h1>
        <p class="mt-1 text-sm text-slate-500">Registra presentes, tardanzas, faltas y justificaciones sin usar el QR.</p>
    </div><a href="index.php?url=asistencia_hoy" class="text-sm font-semibold text-blue-600">Ver asistencia de hoy</a>
</div>
<form method="get" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_2fr_auto]"><input type="hidden" name="url" value="asistencia_manual"><label class="form-label">Fecha<input required type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" class="form-input"></label><label class="form-label">Sección<select required name="id_seccion" class="form-input">
            <option value="">Seleccione una sección</option><?php foreach ($secciones as $seccion): ?><option value="<?= $seccion['id_seccion'] ?>" <?= $idSeccion === (int)$seccion['id_seccion'] ? 'selected' : '' ?>><?= htmlspecialchars($seccion['nombre']) ?></option><?php endforeach; ?>
        </select></label><button class="self-end rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Cargar lista</button></form>
<?php if ($idSeccion): ?><form method="post" action="index.php?url=asistencia_manual_guardar" data-confirmar-accion="asistencia-manual" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><?= campoCsrf() ?><input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>"><input type="hidden" name="id_seccion" value="<?= $idSeccion ?>">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-semibold">Lista de asistencia</h2>
                <p class="text-sm text-slate-500"><?= count($alumnos) ?> alumnos matriculados.</p>
            </div><label class="form-label w-full sm:w-44">Hora de entrada<input type="time" name="hora_entrada" value="<?= date('H:i') ?>" class="form-input"></label>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Alumno</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4">Registro actual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"><?php foreach ($alumnos as $alumno): $estado = $alumno['estado'] === 'PENDIENTE' ? 'PUNTUAL' : $alumno['estado']; ?><tr>
                            <td class="px-5 py-4 font-medium"><?= htmlspecialchars($alumno['alumno']) ?></td>
                            <td class="px-5 py-4"><select name="estado[<?= $alumno['id_matricula'] ?>]" class="form-input mt-0 max-w-xs"><?php foreach (['PUNTUAL' => 'Presente', 'TARDANZA' => 'Tardanza', 'FALTA' => 'Falta', 'JUSTIFICADO' => 'Justificado', 'PERMISO' => 'Permiso'] as $valor => $texto): ?><option value="<?= $valor ?>" <?= $estado === $valor ? 'selected' : '' ?>><?= $texto ?></option><?php endforeach; ?></select></td>
                            <td class="px-5 py-4 text-slate-500"><?= $alumno['estado'] === 'PENDIENTE' ? 'Sin registro' : htmlspecialchars($alumno['estado'] . ($alumno['hora_entrada'] ? ' · ' . $alumno['hora_entrada'] : '')) ?></td>
                        </tr><?php endforeach; ?><?php if (!$alumnos): ?><tr>
                            <td colspan="3" class="px-5 py-10 text-center text-slate-500">No hay alumnos activos en esta sección para la fecha elegida.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
        <div class="flex justify-end border-t border-slate-100 bg-slate-50 p-4"><button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Guardar asistencia</button></div>
    </form><?php endif; ?>