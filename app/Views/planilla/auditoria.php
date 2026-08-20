<?php
$formatearDetalle = static function ($valor): array {
    if (!$valor) return [];

    $datos = json_decode($valor, true);
    if (!is_array($datos)) return [['Detalle', (string)$valor]];

    $etiquetas = [
        'anterior' => 'Vínculo anterior',
        'modalidad' => 'Modalidad de pago',
        'mensual' => 'Sueldo mensual',
        'hora' => 'Pago por hora',
        'sesion' => 'Pago por sesión',
        'dias' => 'Días laborables',
        'motivo' => 'Motivo',
        'periodo' => 'Período',
        'estado' => 'Estado',
        'trabajadores' => 'Trabajadores',
        'naturaleza' => 'Tipo de ajuste',
        'importe' => 'Importe',
        'medio' => 'Medio de pago',
        'referencia' => 'Referencia',
    ];
    $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $camposMonetarios = ['mensual', 'hora', 'sesion', 'importe'];
    $modalidades = ['MENSUAL' => 'Mensual', 'HORAS' => 'Por horas', 'MIXTO' => 'Mixto'];
    $filas = [];

    foreach ($datos as $clave => $dato) {
        $etiqueta = $etiquetas[$clave] ?? ucwords(str_replace('_', ' ', $clave));

        if ($clave === 'dias' && is_string($dato)) {
            $nombres = [];
            foreach (explode(',', $dato) as $numero) {
                $numero = (int)trim($numero);
                if (isset($diasSemana[$numero])) $nombres[] = $diasSemana[$numero];
            }
            $dato = $nombres ? implode(', ', $nombres) : 'No registrados';
        } elseif ($clave === 'modalidad' && isset($modalidades[(string)$dato])) {
            $dato = $modalidades[(string)$dato];
        } elseif (in_array($clave, $camposMonetarios, true) && is_numeric($dato)) {
            $dato = 'S/ ' . number_format((float)$dato, 2);
        } elseif ($clave === 'anterior' && ($dato === null || $dato === '')) {
            $dato = 'Primer registro';
        } elseif ($clave === 'motivo' && ($dato === null || $dato === '')) {
            $dato = 'Sin motivo (registro inicial)';
        } elseif ($dato === null || $dato === '') {
            $dato = 'No registrado';
        } elseif (is_bool($dato)) {
            $dato = $dato ? 'Sí' : 'No';
        } elseif (is_array($dato)) {
            $dato = implode(', ', array_map('strval', $dato));
        }

        $filas[] = [$etiqueta, (string)$dato];
    }

    return $filas;
};
?>

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-slate-600">Control interno</p>
        <h1 class="text-2xl font-bold">Auditoría de Planilla</h1>
        <p class="text-sm text-slate-500">Operaciones sensibles, responsables y validaciones de integridad.</p>
    </div>
    <a href="index.php?url=planilla" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Volver</a>
</div>

<form method="get" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2">
    <input type="hidden" name="url" value="planilla_auditoria">
    <label class="form-label">Año
        <select name="anio" class="form-input">
            <option value="0">Todos los años</option>
            <?php foreach ($anios as $opcion): ?>
                <option value="<?= $opcion ?>" <?= $anio === $opcion ? 'selected' : '' ?>><?= $opcion ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="form-label">Operación
        <select name="accion" class="form-input">
            <option value="">Todas las operaciones</option>
            <?php foreach ($acciones as $opcion): ?>
                <option value="<?= htmlspecialchars($opcion) ?>" <?= $accion === $opcion ? 'selected' : '' ?>><?= htmlspecialchars(str_replace('_', ' ', $opcion)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="flex gap-2 sm:col-span-2">
        <button class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white">Aplicar filtros</button>
        <a href="index.php?url=planilla_auditoria" class="rounded-xl border px-5 py-2.5 text-sm font-semibold">Limpiar</a>
    </div>
</form>

<section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-blue-200 bg-blue-50 p-4"><p class="text-xs font-semibold uppercase text-blue-600">Eventos</p><p class="mt-1 text-2xl font-bold text-blue-900"><?= $resumen['eventos'] ?></p></article>
    <article class="rounded-2xl border border-violet-200 bg-violet-50 p-4"><p class="text-xs font-semibold uppercase text-violet-600">Usuarios</p><p class="mt-1 text-2xl font-bold text-violet-900"><?= $resumen['usuarios'] ?></p></article>
    <article class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold uppercase text-slate-500">Último evento</p><p class="mt-1 text-sm font-bold"><?= htmlspecialchars($resumen['ultimo'] ?: 'Sin registros') ?></p></article>
    <article class="rounded-2xl border <?= $resumen['incidencias'] ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50' ?> p-4"><p class="text-xs font-semibold uppercase <?= $resumen['incidencias'] ? 'text-rose-600' : 'text-emerald-600' ?>">Incidencias</p><p class="mt-1 text-2xl font-bold"><?= $resumen['incidencias'] ?></p></article>
</section>

<section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
    <?php foreach ($controles as $control): ?>
        <article class="rounded-2xl border p-4 <?= $control['cantidad'] ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50' ?>">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold"><?= htmlspecialchars($control['nombre']) ?></h2>
                <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $control['cantidad'] ? 'bg-rose-200 text-rose-800' : 'bg-emerald-200 text-emerald-800' ?>"><?= $control['cantidad'] ?: 'OK' ?></span>
            </div>
            <p class="mt-2 text-xs text-slate-600"><?= htmlspecialchars($control['detalle']) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<section class="overflow-hidden rounded-2xl border bg-white">
    <div class="border-b p-4">
        <h2 class="font-semibold">Historial de operaciones</h2>
        <p class="text-sm text-slate-500">Se muestran hasta 1,000 eventos según los filtros seleccionados.</p>
    </div>
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="10,25,50,100" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr><th class="p-4">Fecha</th><th class="p-4">Responsable</th><th class="p-4">Operación</th><th class="p-4">Entidad / código</th><th class="no-sort p-4">Cambios</th><th class="p-4">IP</th></tr>
            </thead>
            <tbody>
                <?php foreach ($eventos as $evento): ?>
                    <?php $antes = $formatearDetalle($evento['valores_antes']); $despues = $formatearDetalle($evento['valores_despues']); ?>
                    <tr class="border-t">
                        <td class="p-4 text-xs"><?= htmlspecialchars($evento['creado_en']) ?></td>
                        <td class="p-4 font-medium"><?= htmlspecialchars($evento['responsable']) ?></td>
                        <td class="p-4"><span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800"><?= htmlspecialchars(str_replace('_', ' ', $evento['accion'])) ?></span></td>
                        <td class="p-4"><?= htmlspecialchars($evento['entidad']) ?><small class="block text-slate-500">#<?= htmlspecialchars($evento['id_entidad'] ?: '—') ?></small></td>
                        <td class="p-4">
                            <?php if ($antes || $despues): ?>
                                <details>
                                    <summary class="cursor-pointer text-xs font-semibold text-violet-700">Ver detalle</summary>
                                    <div class="mt-2 grid min-w-72 gap-2 text-xs">
                                        <?php if ($antes): ?>
                                            <div class="rounded-lg border border-rose-100 bg-rose-50 p-3">
                                                <p class="mb-2 font-bold text-rose-700">Valor anterior</p>
                                                <dl class="grid gap-1.5"><?php foreach ($antes as $fila): ?><div class="grid grid-cols-2 gap-3"><dt class="font-semibold text-slate-600"><?= htmlspecialchars($fila[0]) ?></dt><dd><?= htmlspecialchars($fila[1]) ?></dd></div><?php endforeach; ?></dl>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($despues): ?>
                                            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                                                <p class="mb-2 font-bold text-emerald-700"><?= $antes ? 'Valor posterior' : 'Datos registrados' ?></p>
                                                <dl class="grid gap-1.5"><?php foreach ($despues as $fila): ?><div class="grid grid-cols-2 gap-3"><dt class="font-semibold text-slate-600"><?= htmlspecialchars($fila[0]) ?></dt><dd><?= htmlspecialchars($fila[1]) ?></dd></div><?php endforeach; ?></dl>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            <?php else: ?>
                                <span class="text-slate-400">Sin detalle</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-xs text-slate-500"><?= htmlspecialchars($evento['ip'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$eventos): ?><div class="border-t p-8 text-center text-slate-500">Todavía no hay eventos de Planilla para estos filtros.</div><?php endif; ?>
</section>
