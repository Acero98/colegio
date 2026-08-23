<?php
$coloresTipo = ['INGRESO' => 'bg-emerald-100 text-emerald-800', 'EGRESO' => 'bg-rose-100 text-rose-800'];
$coloresEstado = ['REGISTRADO' => 'bg-blue-100 text-blue-800', 'ANULADO' => 'bg-slate-200 text-slate-600'];
?>
<div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Finanzas escolares</p>
        <h1 class="text-2xl font-bold">Ingresos y egresos</h1>
        <p class="mt-1 text-sm text-slate-500">Registre operaciones adicionales a cobros y planillas, conservando evidencia y trazabilidad.</p>
    </div>
    <div class="flex flex-wrap gap-2"><button type="button" <?= $cajaAbierta ? 'onclick="abrirMovimiento(\'INGRESO\')"' : 'disabled' ?> class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40"><i class="bi bi-arrow-down-circle mr-1"></i> Nuevo ingreso</button><button type="button" <?= $cajaAbierta ? 'onclick="abrirMovimiento(\'EGRESO\')"' : 'disabled' ?> class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40"><i class="bi bi-arrow-up-circle mr-1"></i> Nuevo egreso</button><button type="button" onclick="document.getElementById('modal-categorias-finanzas').classList.remove('hidden')" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700"><i class="bi bi-tags mr-1"></i> Categorías</button><a href="index.php?url=reportes_financieros" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><i class="bi bi-bar-chart-line mr-1"></i> Reporte mensual</a></div>
</div>
<?php if (!$cajaAbierta): ?><section class="mb-5 flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-bold"><i class="bi bi-lock mr-1"></i> No hay un turno de caja abierto para hoy</h2>
            <p class="mt-1 text-sm">La caja identifica al responsable y permite conciliar todos los medios. Abra un turno antes de registrar movimientos.</p>
        </div><a href="index.php?url=cobros_cierre_caja" class="shrink-0 rounded-xl bg-amber-600 px-4 py-2.5 text-center text-sm font-semibold text-white">Abrir caja</a>
    </section><?php else: ?><section class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm text-emerald-800"><i class="bi bi-unlock mr-1"></i> Turno de caja abierto desde <?= htmlspecialchars($cajaAbierta['abierto_en']) ?>. Todos los medios quedarán asociados a este turno.</section><?php endif; ?>
<section class="mb-5 grid gap-4 sm:grid-cols-3">
    <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <p class="text-xs font-semibold uppercase text-emerald-700">Otros ingresos</p>
        <p class="mt-2 text-2xl font-bold text-emerald-800">S/ <?= number_format($resumen['ingresos'], 2) ?></p>
    </article>
    <article class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
        <p class="text-xs font-semibold uppercase text-rose-700">Otros egresos</p>
        <p class="mt-2 text-2xl font-bold text-rose-800">S/ <?= number_format($resumen['egresos'], 2) ?></p>
    </article>
    <article class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
        <p class="text-xs font-semibold uppercase text-blue-700">Flujo manual neto</p>
        <p class="mt-2 text-2xl font-bold <?= $resumen['balance'] >= 0 ? 'text-blue-800' : 'text-rose-700' ?>">S/ <?= number_format($resumen['balance'], 2) ?></p>
    </article>
</section>
<form class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 xl:grid-cols-6"><input type="hidden" name="url" value="finanzas_movimientos"><label class="form-label">Desde<input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>" class="form-input"></label><label class="form-label">Hasta<input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>" class="form-input"></label><label class="form-label">Tipo<select name="tipo" class="form-input">
            <option value="">Todos</option>
            <option value="INGRESO" <?= $filtros['tipo'] === 'INGRESO' ? 'selected' : '' ?>>Ingresos</option>
            <option value="EGRESO" <?= $filtros['tipo'] === 'EGRESO' ? 'selected' : '' ?>>Egresos</option>
        </select></label><label class="form-label">Categoría<select name="categoria" class="form-input">
            <option value="0">Todas</option><?php foreach ($categorias as $categoria): ?><option value="<?= $categoria['id_categoria'] ?>" <?= $filtros['categoria'] == $categoria['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nombre']) ?></option><?php endforeach; ?>
        </select></label><label class="form-label">Estado<select name="estado" class="form-input">
            <option value="">Todos</option>
            <option value="REGISTRADO" <?= $filtros['estado'] === 'REGISTRADO' ? 'selected' : '' ?>>Registrado</option>
            <option value="ANULADO" <?= $filtros['estado'] === 'ANULADO' ? 'selected' : '' ?>>Anulado</option>
        </select></label><button class="self-end rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white">Filtrar</button></form>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table data-datatable data-page-options="5,10,25,50" data-order="2:desc" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="no-sort p-4">N°</th>
                    <th class="p-4">Código</th>
                    <th class="p-4">Fecha</th>
                    <th class="p-4">Tipo / categoría</th>
                    <th class="p-4">Detalle</th>
                    <th class="p-4">Medio</th>
                    <th class="p-4">Monto</th>
                    <th class="p-4">Responsable</th>
                    <th class="p-4">Estado</th>
                    <th class="no-sort p-4">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($movimientos as $indice => $m): ?><tr>
                        <td class="p-4 text-slate-500"><?= $indice + 1 ?></td>
                        <td class="p-4 font-semibold"><?= htmlspecialchars($m['codigo']) ?></td>
                        <td class="p-4"><?= htmlspecialchars($m['fecha_movimiento']) ?></td>
                        <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $coloresTipo[$m['tipo']] ?>"><?= $m['tipo'] ?></span><small class="mt-2 block text-slate-600"><?= htmlspecialchars($m['categoria']) ?> · <?= htmlspecialchars($m['clasificacion']) ?></small></td>
                        <td class="p-4"><b><?= htmlspecialchars($m['descripcion']) ?></b><small class="block text-slate-500"><?= htmlspecialchars($m['tercero'] ?: 'Sin tercero') ?><?= $m['documento'] ? ' · ' . htmlspecialchars($m['documento']) : '' ?></small></td>
                        <td class="p-4"><?= htmlspecialchars($m['medio']) ?><?php if ($m['referencia']): ?><small class="block text-slate-500">Ref. <?= htmlspecialchars($m['referencia']) ?></small><?php endif; ?></td>
                        <td class="p-4 font-bold <?= $m['tipo'] === 'INGRESO' ? 'text-emerald-700' : 'text-rose-700' ?>"><?= $m['tipo'] === 'INGRESO' ? '+' : '−' ?> S/ <?= number_format((float)$m['monto'], 2) ?></td>
                        <td class="p-4 text-xs"><?= htmlspecialchars($m['responsable']) ?><small class="block text-slate-500"><?= htmlspecialchars($m['registrado_en']) ?></small></td>
                        <td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $coloresEstado[$m['estado']] ?>"><?= $m['estado'] ?></span><?php if ($m['motivo_anulacion']): ?><small class="mt-1 block text-slate-500"><?= htmlspecialchars($m['motivo_anulacion']) ?></small><?php endif; ?></td>
                        <td class="p-4">
                            <div class="flex gap-2"><?php if ($m['evidencia_archivo']): ?><a target="_blank" href="index.php?url=finanzas_movimiento_evidencia&id=<?= $m['id_movimiento'] ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700" title="Ver evidencia"><i class="bi bi-paperclip"></i></a><?php endif; ?><?php if ($m['estado'] === 'REGISTRADO'): ?><button type="button" onclick="abrirAnulacion(<?= $m['id_movimiento'] ?>,'<?= htmlspecialchars($m['codigo'], ENT_QUOTES) ?>')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-700" title="Anular"><i class="bi bi-x-circle"></i></button><?php endif; ?></div>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$movimientos): ?><tr>
                        <td colspan="10" class="p-10 text-center text-slate-500">No hay movimientos en el rango seleccionado.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-movimiento-financiero" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-8 max-w-3xl rounded-2xl bg-white p-6 shadow-2xl">
        <div>
            <h2 id="titulo-movimiento" class="text-lg font-bold">Nuevo movimiento</h2>
            <p class="text-sm text-slate-500">No registre aquí cobros de alumnos ni pagos de planilla: esos se incorporan automáticamente al reporte.</p>
        </div>
        <form method="post" enctype="multipart/form-data" action="index.php?url=finanzas_movimiento_guardar" data-confirmar-accion="movimiento-financiero-guardar" class="mt-5 grid gap-4 sm:grid-cols-2"><?= campoCsrf() ?><input id="movimiento-tipo" type="hidden" name="tipo"><label class="form-label">Categoría<select id="movimiento-categoria" required name="id_categoria" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($categorias as $categoria): if ($categoria['estado'] !== 'ACTIVO') continue; ?><option value="<?= $categoria['id_categoria'] ?>" data-tipo="<?= $categoria['tipo'] ?>"><?= htmlspecialchars($categoria['nombre'] . ' · ' . $categoria['clasificacion']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Fecha y hora<input required type="datetime-local" min="<?= date('Y-m-d\T00:00') ?>" max="<?= date('Y-m-d\TH:i') ?>" name="fecha_movimiento" value="<?= date('Y-m-d\TH:i') ?>" class="form-input"></label><label class="form-label">Monto S/<input required min="0.01" step="0.01" type="number" name="monto" class="form-input"></label><label class="form-label">Medio<select id="movimiento-medio" required name="medio" class="form-input"><?php foreach (['EFECTIVO', 'TRANSFERENCIA', 'YAPE', 'PLIN', 'TARJETA', 'DEPOSITO', 'OTRO'] as $medio): ?><option><?= $medio ?></option><?php endforeach; ?></select></label><label class="form-label">Persona o empresa<input maxlength="160" name="tercero" class="form-input" placeholder="Proveedor, socio, banco..."></label><label class="form-label">Documento<input maxlength="80" name="documento" class="form-input" placeholder="RUC, DNI, factura o boleta"></label><label class="form-label">Referencia <span id="referencia-ayuda" class="font-normal text-slate-400">(opcional en efectivo)</span><input id="movimiento-referencia" maxlength="120" name="referencia" class="form-input" placeholder="Operación, contrato, comprobante"></label><label class="form-label">Evidencia <span id="evidencia-ayuda" class="font-normal text-slate-400">(opcional)</span><input id="movimiento-evidencia" type="file" name="evidencia" accept="image/jpeg,image/png,image/webp,application/pdf" class="mt-2 block w-full text-sm"></label><label class="form-label sm:col-span-2">Descripción<textarea required minlength="3" maxlength="500" name="descripcion" rows="3" class="form-input" placeholder="Explique el origen o destino del dinero"></textarea></label>
            <div class="rounded-xl bg-blue-50 p-3 text-sm text-blue-800 sm:col-span-2">Este movimiento quedará vinculado al turno abierto. Para medios digitales debe indicar una referencia o adjuntar evidencia.</div><button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white sm:col-span-2">Registrar movimiento</button>
        </form>
    </div>
</div>
<div id="modal-categorias-finanzas" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-8 max-w-3xl rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Categorías financieras</h2>
        <p class="text-sm text-slate-500">La clasificación permite separar operación, préstamos y movimientos de capital.</p>
        <form method="post" action="index.php?url=finanzas_categoria_guardar" data-confirmar-accion="categoria-financiera-guardar" class="mt-4 grid gap-3 sm:grid-cols-4"><?= campoCsrf() ?><input required minlength="3" name="nombre" class="form-input" placeholder="Nombre de categoría"><select name="tipo" class="form-input">
                <option>INGRESO</option>
                <option>EGRESO</option>
            </select><select name="clasificacion" class="form-input">
                <option value="OPERATIVO">Operativo</option>
                <option value="FINANCIAMIENTO">Financiamiento</option>
                <option value="CAPITAL">Capital</option>
            </select><button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Crear categoría</button></form>
        <div class="mt-5 max-h-80 overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="p-3">Categoría</th>
                        <th class="p-3">Tipo</th>
                        <th class="p-3">Clasificación</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3">Acción</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($categorias as $categoria): ?><tr class="border-t">
                            <td class="p-3 font-medium"><?= htmlspecialchars($categoria['nombre']) ?></td>
                            <td class="p-3"><?= $categoria['tipo'] ?></td>
                            <td class="p-3"><?= $categoria['clasificacion'] ?></td>
                            <td class="p-3"><?= $categoria['estado'] ?></td>
                            <td class="p-3">
                                <form method="post" action="index.php?url=finanzas_categoria_estado" data-confirmar-accion="<?= $categoria['estado'] === 'ACTIVO' ? 'categoria-financiera-desactivar' : 'categoria-financiera-activar' ?>"><?= campoCsrf() ?><input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>"><input type="hidden" name="estado" value="<?= $categoria['estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO' ?>"><button class="rounded-lg px-3 py-2 text-xs font-semibold <?= $categoria['estado'] === 'ACTIVO' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>"><?= $categoria['estado'] === 'ACTIVO' ? 'Desactivar' : 'Activar' ?></button></form>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<div id="modal-anular-movimiento" class="fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-20 max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Anular movimiento</h2>
        <p id="texto-anular-movimiento" class="mt-1 text-sm text-slate-500"></p>
        <form method="post" action="index.php?url=finanzas_movimiento_anular" data-confirmar-accion="movimiento-financiero-anular" class="mt-4 grid gap-3"><?= campoCsrf() ?><input id="id-anular-movimiento" type="hidden" name="id_movimiento"><label class="form-label">Motivo<input required minlength="3" maxlength="250" name="motivo" class="form-input"></label><button class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white">Anular movimiento</button></form>
    </div>
</div>
<script>
    function abrirMovimiento(tipo) {
        const modal = document.getElementById('modal-movimiento-financiero'),
            campo = document.getElementById('movimiento-tipo'),
            select = document.getElementById('movimiento-categoria');
        campo.value = tipo;
        document.getElementById('titulo-movimiento').textContent = tipo === 'INGRESO' ? 'Nuevo ingreso' : 'Nuevo egreso';
        [...select.options].forEach(o => {
            o.hidden = o.dataset.tipo && o.dataset.tipo !== tipo;
            o.disabled = o.hidden
        });
        select.value = '';
        modal.classList.remove('hidden')
    }

    function abrirAnulacion(id, codigo) {
        document.getElementById('id-anular-movimiento').value = id;
        document.getElementById('texto-anular-movimiento').textContent = 'Se anulará ' + codigo + ' sin eliminar su historial.';
        document.getElementById('modal-anular-movimiento').classList.remove('hidden')
    }
    document.addEventListener('DOMContentLoaded', () => {
        const medio = document.getElementById('movimiento-medio'),
            ayuda = document.getElementById('referencia-ayuda');
        if (medio) {
            const actualizar = () => {
                ayuda.textContent = medio.value === 'EFECTIVO' ? '(opcional en efectivo)' : '(obligatoria si no adjunta evidencia)'
            };
            medio.addEventListener('change', actualizar);
            actualizar()
        }
    });
</script>