<?php
$colores = ['PAGADA' => 'bg-emerald-50 text-emerald-700', 'PARCIAL' => 'bg-amber-50 text-amber-700', 'VENCIDA' => 'bg-red-50 text-red-700', 'ANULADA' => 'bg-slate-100 text-slate-600', 'PENDIENTE' => 'bg-blue-50 text-blue-700'];
$alumnoEstado = $estadoCuenta[0] ?? null;
?>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Finanzas escolares</p>
        <h1 class="text-2xl font-bold">Cobros y mensualidades</h1>
        <p class="mt-1 text-sm text-slate-500">Controle obligaciones, deudas históricas y pagos parciales con recibo.</p>
    </div>
    <div class="flex flex-wrap gap-2"><button onclick="document.getElementById('modal-generar').classList.remove('hidden')" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><i class="bi bi-plus-lg"></i> Generar cuentas</button></div>
</div>
<form class="mb-5 grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-4"><input type="hidden" name="url" value="cobros"><input type="month" name="mes" value="<?= htmlspecialchars($mes) ?>" class="form-input"><input name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Alumno, código o documento" class="form-input"><select name="estado" class="form-input">
        <option value="">Todos los estados</option><?php foreach (['PENDIENTE' => 'Pendiente', 'PARCIAL' => 'Parcial', 'VENCIDA' => 'Vencida', 'PROXIMO' => 'Vence en 7 días', 'MORA_1_30' => 'Mora de 1 a 30 días', 'MORA_31_60' => 'Mora de 31 a 60 días', 'MORA_60' => 'Mora mayor a 60 días', 'PAGADA' => 'Pagada', 'ANULADA' => 'Anulada'] as $valor => $etiqueta): ?><option value="<?= $valor ?>" <?= $estado === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option><?php endforeach; ?>
    </select><button class="rounded-xl bg-slate-800 px-4 text-sm font-semibold text-white">Filtrar</button><a href="index.php?url=cobros&mes=todos" class="text-center text-sm font-semibold text-blue-600 sm:col-span-4">Ver todas las deudas y años</a></form>
<section class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><?php foreach ([['Cuentas', $resumen['cuentas'] ?? 0, 'bi-receipt'], ['Facturado', 'S/ ' . number_format((float)($resumen['facturado'] ?? 0), 2), 'bi-cash-stack'], ['Cobrado', 'S/ ' . number_format((float)($resumen['cobrado'] ?? 0), 2), 'bi-check2-circle'], ['Pendiente', 'S/ ' . number_format((float)($resumen['pendiente'] ?? 0), 2), 'bi-hourglass-split']] as $kpi): ?><article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><i class="bi <?= $kpi[2] ?> text-xl text-blue-600"></i>
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-slate-500"><?= $kpi[0] ?></p>
            <p class="mt-1 text-2xl font-bold"><?= $kpi[1] ?></p>
        </article><?php endforeach; ?></section>
<section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5"><?php foreach ([['PROXIMO', 'Próximos a vencer', $alertas['proximos'] ?? 0, 'bg-blue-50 text-blue-800'], ['PARCIAL', 'Pagos parciales', $alertas['parciales'] ?? 0, 'bg-amber-50 text-amber-800'], ['MORA_1_30', 'Mora 1–30 días', $alertas['mora_1_30'] ?? 0, 'bg-orange-50 text-orange-800'], ['MORA_31_60', 'Mora 31–60 días', $alertas['mora_31_60'] ?? 0, 'bg-red-50 text-red-700'], ['MORA_60', 'Mora +60 días', $alertas['mora_60'] ?? 0, 'bg-rose-100 text-rose-800']] as $alerta): ?><a href="index.php?url=cobros&mes=todos&estado=<?= $alerta[0] ?>" class="rounded-2xl p-4 <?= $alerta[3] ?>">
            <p class="text-xs font-semibold uppercase tracking-wide"><?= $alerta[1] ?></p>
            <p class="mt-2 text-2xl font-bold"><?= $alerta[2] ?></p><span class="text-xs">Ver cuentas →</span>
        </a><?php endforeach; ?></section>
<?php if ($estadoCuenta): ?><section class="mb-5 rounded-2xl border border-blue-200 bg-blue-50 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold">Estado de cuenta: <?= htmlspecialchars($alumnoEstado['codigo_alumno']) ?></h2>
                <p class="text-sm text-slate-600">Incluye obligaciones pendientes de todos sus períodos académicos.</p>
            </div>
            <div class="flex gap-4"><a target="_blank" href="index.php?url=cobros_estado_cuenta&alumno=<?= $alumnoEstado['id_persona'] ?>" class="text-sm font-semibold text-blue-700">Imprimir estado</a><a href="index.php?url=cobros&mes=<?= urlencode($mes ?: 'todos') ?>" class="text-sm font-semibold text-blue-700">Cerrar</a></div>
        </div>
        <?php if (!$cajaDisponibleHoy): ?><div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900"><p class="font-semibold"><i class="bi bi-lock mr-1"></i> Los pagos están bloqueados</p><p class="mt-1"><?= $cajaAbierta ? 'La caja del ' . htmlspecialchars($cajaAbierta['fecha']) . ' continúa abierta. Ciérrela y abra la caja de hoy.' : 'Primero debe abrir un turno de caja para hoy.' ?></p><a href="index.php?url=cobros_cierre_caja<?= $cajaAbierta ? '&fecha=' . urlencode($cajaAbierta['fecha']) : '' ?>" class="mt-3 inline-flex rounded-lg bg-red-600 px-4 py-2 font-semibold text-white"><?= $cajaAbierta ? 'Cerrar caja pendiente' : 'Abrir caja' ?></a></div><?php else: ?>
        <form id="form-pago-mixto" method="post" enctype="multipart/form-data" action="index.php?url=cobros_pagar" data-confirmar-accion="cobrar" class="mt-4 grid gap-4"><?= campoCsrf() ?><input type="hidden" name="mes" value="<?= htmlspecialchars($mes) ?>">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500">
                            <th class="p-2">Período / sección</th>
                            <th class="p-2">Concepto</th>
                            <th class="p-2">Vence</th>
                            <th class="p-2">Saldo</th>
                            <th class="p-2">A pagar</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($estadoCuenta as $c): ?><tr class="border-t border-blue-100">
                                <td class="p-2"><?= htmlspecialchars($c['periodo_academico']) ?><span class="block text-xs text-slate-500"><?= htmlspecialchars($c['seccion']) ?></span></td>
                                <td class="p-2"><?= htmlspecialchars($c['concepto']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($c['fecha_vencimiento'] ?: '—') ?></td>
                                <td class="p-2 font-semibold">S/ <?= number_format((float)$c['saldo'], 2) ?></td>
                                <td class="p-2"><input type="number" min="0" max="<?= $c['saldo'] ?>" step="0.01" name="detalles[<?= $c['id_cuenta'] ?>]" class="form-input pago-deuda w-28" placeholder="0.00"></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-white p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div><h3 class="font-bold text-slate-800">Distribución del pago</h3><p class="text-xs text-slate-500">Agregue únicamente los medios utilizados por el apoderado.</p></div>
                    <div class="flex flex-wrap gap-2"><button id="completar-efectivo" type="button" class="rounded-lg bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-800"><i class="bi bi-cash mr-1"></i> Completar con efectivo</button><button id="agregar-medio" type="button" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white"><i class="bi bi-plus-lg mr-1"></i> Agregar medio</button></div>
                </div>
                <div id="medios-vacio" class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">Todavía no agregó ningún medio de pago.</div>
                <div id="lista-medios" class="mt-4 grid gap-3"></div>
                <div class="mt-4 grid gap-2 rounded-xl bg-slate-900 p-4 text-white sm:grid-cols-4">
                    <div><span class="block text-xs text-slate-300">Deudas seleccionadas</span><strong id="pago-total-deudas" class="text-lg">S/ 0.00</strong></div>
                    <div><span class="block text-xs text-slate-300">Medios distribuidos</span><strong id="pago-total-medios" class="text-lg">S/ 0.00</strong></div>
                    <div><span class="block text-xs text-slate-300">Falta distribuir</span><strong id="pago-falta" class="text-lg">S/ 0.00</strong></div>
                    <div><span class="block text-xs text-slate-300">Vuelto</span><strong id="pago-vuelto" class="text-lg text-emerald-300">S/ 0.00</strong></div>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-[1fr_auto]"><input name="observacion" maxlength="250" class="form-input" placeholder="Observación opcional"><button id="registrar-pago" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"><i class="bi bi-receipt"></i> Registrar y generar recibo</button></div>
        </form><?php endif; ?>
    </section><?php endif; ?>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table id="tablaCobros" class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-4">Alumno</th>
                    <th class="px-5 py-4">Concepto</th>
                    <th class="px-5 py-4">Período</th>
                    <th class="px-5 py-4">Vencimiento</th>
                    <th class="px-5 py-4">Pagado</th>
                    <th class="px-5 py-4">Saldo</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100"><?php foreach ($cuentas as $cuenta): $visual = $cuenta['estado_visual']; ?><tr>
                        <td class="px-5 py-4 font-medium"><?= htmlspecialchars($cuenta['alumno']) ?><span class="block text-xs font-normal text-slate-500"><?= htmlspecialchars($cuenta['codigo_alumno'] . ' · ' . $cuenta['seccion']) ?></span></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($cuenta['concepto']) ?><span class="block text-xs text-slate-500"><?= htmlspecialchars($cuenta['apoderado'] ?: 'Sin apoderado') ?></span></td>
                        <td class="px-5 py-4"><?= htmlspecialchars(substr($cuenta['periodo_cobro'], 0, 7)) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($cuenta['fecha_vencimiento'] ?: '—') ?></td>
                        <td class="px-5 py-4 text-emerald-700">S/ <?= number_format((float)$cuenta['importe_final'] - (float)$cuenta['saldo'], 2) ?></td>
                        <td class="px-5 py-4 font-semibold">S/ <?= number_format((float)$cuenta['saldo'], 2) ?></td>
                        <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-semibold <?= $colores[$visual] ?>"><?= $visual ?></span></td>
                        <td class="px-5 py-4">
                            <div class="flex gap-3"><a class="text-sm font-semibold text-blue-600" href="index.php?url=cobros&mes=<?= urlencode($mes ?: 'todos') ?>&alumno=<?= $cuenta['id_persona'] ?>">Estado de cuenta</a><?php if ($cuenta['estado'] !== 'ANULADA'): ?><button type="button" onclick="abrirAjuste(<?= $cuenta['id_cuenta'] ?>,'<?= htmlspecialchars($cuenta['alumno'], ENT_QUOTES) ?>')" class="text-sm font-semibold text-amber-700">Ajustar</button><?php endif; ?></div>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$cuentas): ?><tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">No hay cuentas para los filtros seleccionados.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-generar" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-12 max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex justify-between">
            <div>
                <h2 class="text-lg font-bold">Generar cuentas</h2>
                <p class="text-sm text-slate-500">Crea obligaciones sin duplicar registros existentes.</p>
            </div><button onclick="this.closest('.fixed').classList.add('hidden')" class="p-2"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="index.php?url=cobros_generar" data-confirmar-accion="generar-cobros" class="mt-5 grid gap-4"><?= campoCsrf() ?><label class="form-label">Mes de cobro<input type="month" required name="mes" value="<?= htmlspecialchars($mes ?: date('Y-m')) ?>" class="form-input"></label><label class="form-label">Periodo académico<select required name="id_periodo_academico" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($opciones['periodos'] as $p): ?><option value="<?= $p['id_periodo_academico'] ?>"><?= htmlspecialchars($p['nombre']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Concepto<select required name="id_concepto_cobro" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($opciones['conceptos'] as $c): ?><option value="<?= $c['id_concepto_cobro'] ?>"><?= htmlspecialchars($c['nombre'] . ' · ' . $c['tipo'] . ' · ' . ((int)$c['admite_parcial'] ? 'Admite parcial' : 'Pago completo')) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Alcance<select id="alcance-cobro" name="alcance" class="form-input">
                    <option value="TODOS">Todos los matriculados</option>
                    <option value="SECCION">Una sección</option>
                    <option value="ALUMNO">Un alumno</option>
                </select></label><label id="campo-seccion-cobro" class="form-label hidden">Sección<select name="id_seccion" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($opciones['secciones'] as $seccion): ?><option value="<?= $seccion['id_seccion'] ?>"><?= htmlspecialchars($seccion['nombre']) ?></option><?php endforeach; ?>
                </select></label><label id="campo-alumno-cobro" class="form-label hidden">Alumno<select name="id_alumno" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($opciones['alumnos'] as $alumno): ?><option value="<?= $alumno['id_persona'] ?>"><?= htmlspecialchars($alumno['nombre']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Fecha de vencimiento<input type="date" name="fecha_vencimiento" class="form-input"></label><button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Generar</button></form>
    </div>
</div>
<div id="modal-tarifa" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-8 max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex justify-between">
            <div>
                <h2 class="text-lg font-bold">Conceptos y tarifas</h2>
                <p class="text-sm text-slate-500">La tarifa por grado tiene prioridad sobre la general.</p>
            </div><button onclick="this.closest('.fixed').classList.add('hidden')" class="p-2"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="mt-5 grid gap-6 md:grid-cols-2">
            <form method="post" action="index.php?url=cobros_configurar" data-confirmar-accion="guardar" class="grid gap-3 rounded-xl bg-slate-50 p-4"><?= campoCsrf() ?><input type="hidden" name="accion" value="concepto">
                <h3 class="font-bold">Nuevo concepto</h3><label class="form-label">Nombre<input required name="nombre" class="form-input"></label><label class="form-label">Tipo<select name="tipo" class="form-input"><?php foreach (['MATRICULA', 'MENSUALIDAD', 'TALLER', 'CUOTA', 'OTRO'] as $tipo): ?><option><?= $tipo ?></option><?php endforeach; ?></select></label><label class="flex gap-2 text-sm"><input type="checkbox" name="admite_parcial" checked> Admite pagos parciales</label><button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Crear concepto</button>
            </form>
            <form method="post" action="index.php?url=cobros_configurar" data-confirmar-accion="guardar" class="grid gap-3 rounded-xl bg-slate-50 p-4"><?= campoCsrf() ?><input type="hidden" name="accion" value="tarifa">
                <h3 class="font-bold">Nueva tarifa</h3><label class="form-label">Concepto<select required name="id_concepto_cobro" class="form-input">
                        <option value="">Seleccione</option><?php foreach ($opciones['conceptos'] as $c): ?><option value="<?= $c['id_concepto_cobro'] ?>"><?= htmlspecialchars($c['nombre'] . ' · ' . $c['tipo'] . ' · ' . ((int)$c['admite_parcial'] ? 'Admite parcial' : 'Pago completo')) ?></option><?php endforeach; ?>
                    </select></label><label class="form-label">Periodo<select required name="id_periodo_academico" class="form-input">
                        <option value="">Seleccione</option><?php foreach ($opciones['periodos'] as $p): ?><option value="<?= $p['id_periodo_academico'] ?>"><?= htmlspecialchars($p['nombre']) ?></option><?php endforeach; ?>
                    </select></label><label class="form-label">Grado<select name="id_grado" class="form-input">
                        <option value="">Tarifa general</option><?php foreach ($grados as $g): ?><option value="<?= $g['id_grado'] ?>"><?= htmlspecialchars($g['nombre']) ?></option><?php endforeach; ?>
                    </select></label><label class="form-label">Importe<input required min="0" step="0.01" type="number" name="importe" class="form-input"></label><label class="form-label">Vigencia desde<input required type="date" name="vigencia_inicio" value="<?= date('Y-m-d') ?>" class="form-input"></label><label class="form-label">Vigencia hasta<input type="date" name="vigencia_fin" class="form-input"></label><button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Registrar tarifa</button>
            </form>
        </div>
    </div>
</div>
<div id="modal-ajuste" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-20 max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-bold">Ajustar cuenta</h2>
        <p id="ajuste-alumno" class="mt-1 text-sm text-slate-500"></p>
        <form method="post" action="index.php?url=cobros_ajustar" data-confirmar-accion="ajuste-cobro" class="mt-4 grid gap-3"><?= campoCsrf() ?><input type="hidden" name="mes" value="<?= htmlspecialchars($mes) ?>"><input id="ajuste-cuenta" type="hidden" name="id_cuenta"><label class="form-label">Tipo<select name="tipo" class="form-input">
                    <option value="DESCUENTO">Descuento</option>
                    <option value="BECA">Beca</option>
                    <option value="EXONERACION">Exoneración</option>
                    <option value="RECARGO">Recargo</option>
                </select></label><label class="form-label">Importe<input required min="0.01" step="0.01" type="number" name="importe" class="form-input"></label><label class="form-label">Motivo<input required maxlength="250" name="motivo" class="form-input" placeholder="Sustento del ajuste"></label><button class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white">Registrar ajuste</button></form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.simpleDatatables && document.querySelector('#tablaCobros')) new simpleDatatables.DataTable('#tablaCobros', {
            perPage: 10,
            perPageSelect: [5, 10, 25, 50],
            searchable: true,
            sortable: true,
            labels: {
                placeholder: 'Buscar...',
                perPage: 'Mostrar',
                noRows: 'No hay registros',
                noResults: 'No se encontraron resultados',
                info: 'Mostrando {start} a {end} de {rows} registros'
            }
        })
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var alcance = document.getElementById('alcance-cobro');
        if (!alcance) return;
        var seccion = document.getElementById('campo-seccion-cobro'),
            alumno = document.getElementById('campo-alumno-cobro');

        function actualizar() {
            seccion.classList.toggle('hidden', alcance.value !== 'SECCION');
            alumno.classList.toggle('hidden', alcance.value !== 'ALUMNO');
        }
        alcance.addEventListener('change', actualizar);
        actualizar();
    });
</script>
<script>
    function abrirAjuste(id, alumno) {
        document.getElementById('ajuste-cuenta').value = id;
        document.getElementById('ajuste-alumno').textContent = alumno;
        document.getElementById('modal-ajuste').classList.remove('hidden');
    }
</script>
<?php if ($abrirConfiguracion): ?><script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-tarifa').classList.remove('hidden');
        });
    </script><?php endif; ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-pago-mixto');
        if (!form) return;
        const deudas = [...form.querySelectorAll('.pago-deuda')];
        const listaMedios = document.getElementById('lista-medios');
        const avisoVacio = document.getElementById('medios-vacio');
        const tiposMedio = {
            EFECTIVO: 'Efectivo', YAPE: 'Yape', PLIN: 'Plin', TRANSFERENCIA: 'Transferencia',
            TARJETA: 'Tarjeta', DEPOSITO: 'Depósito', OTRO: 'Otro'
        };
        const dinero = valor => 'S/ ' + valor.toFixed(2);
        const sumar = campos => campos.reduce((total, campo) => total + (parseFloat(campo.value) || 0), 0);

        function filasMedio() {
            return [...listaMedios.querySelectorAll('[data-fila-medio]')];
        }
        function tiposUsados(ignorarFila) {
            return filasMedio().filter(fila => fila !== ignorarFila).map(fila => fila.querySelector('.medio-tipo').value);
        }
        function actualizarOpciones() {
            filasMedio().forEach(fila => {
                const usados = tiposUsados(fila);
                [...fila.querySelector('.medio-tipo').options].forEach(opcion => opcion.disabled = usados.includes(opcion.value));
            });
            avisoVacio.classList.toggle('hidden', filasMedio().length > 0);
            document.getElementById('agregar-medio').disabled = filasMedio().length >= Object.keys(tiposMedio).length;
        }
        function mostrarCampoComplementario(fila) {
            const tipo = fila.querySelector('.medio-tipo').value;
            const importe = fila.querySelector('.medio-importe');
            importe.name = `medios[${tipo}][importe]`;
            importe.dataset.medioImporte = tipo;
            const contenedor = fila.querySelector('.medio-complemento');
            contenedor.className = tipo === 'EFECTIVO' ? 'medio-complemento' : 'medio-complemento grid gap-2 md:grid-cols-2';
            contenedor.innerHTML = tipo === 'EFECTIVO'
                ? '<label class="text-xs text-slate-500">Recibido S/<input type="number" min="0" step="0.01" value="0" name="medios[EFECTIVO][recibido]" class="form-input efectivo-recibido mt-1"></label>'
                : `<label class="text-xs text-slate-500">Referencia <span class="text-slate-400">(o evidencia)</span><input maxlength="120" name="medios[${tipo}][referencia]" class="form-input medio-referencia mt-1" placeholder="N.° operación / detalle"></label><label class="text-xs text-slate-500">Evidencia <span class="text-slate-400">(o referencia)</span><input type="file" name="evidencias[${tipo}]" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-blue-100 file:px-3 file:py-2 file:font-semibold file:text-blue-700"></label>`;
            const campoCalculo = contenedor.querySelector('input[type="number"]');
            if (campoCalculo) campoCalculo.addEventListener('input', actualizarPago);
        }
        function agregarMedio(tipoPreferido) {
            const usados = tiposUsados(null);
            const disponibles = Object.keys(tiposMedio).filter(tipo => !usados.includes(tipo));
            if (!disponibles.length) return null;
            const tipoInicial = disponibles.includes(tipoPreferido) ? tipoPreferido : disponibles[0];
            const fila = document.createElement('div');
            fila.dataset.filaMedio = '';
            fila.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-[minmax(150px,0.8fr)_minmax(130px,0.7fr)_minmax(180px,1fr)_auto] sm:items-end';
            fila.innerHTML = `<label class="text-xs text-slate-500">Medio<select class="form-input medio-tipo mt-1">${Object.entries(tiposMedio).map(([valor, etiqueta]) => `<option value="${valor}"${valor === tipoInicial ? ' selected' : ''}>${etiqueta}</option>`).join('')}</select></label><label class="text-xs text-slate-500">Aplicar S/<input type="number" min="0" step="0.01" value="0" class="form-input medio-importe mt-1"></label><div class="medio-complemento"></div><button type="button" class="quitar-medio inline-flex h-10 items-center justify-center rounded-lg bg-red-100 px-3 text-sm font-semibold text-red-700" title="Quitar medio"><i class="bi bi-trash"></i><span class="ml-2 sm:hidden">Quitar</span></button>`;
            listaMedios.appendChild(fila);
            mostrarCampoComplementario(fila);
            fila.querySelector('.medio-importe').addEventListener('input', actualizarPago);
            const selector = fila.querySelector('.medio-tipo');
            selector.dataset.anterior = tipoInicial;
            selector.addEventListener('change', function() {
                if (tiposUsados(fila).includes(selector.value)) {
                    selector.value = selector.dataset.anterior;
                    if (window.Swal) Swal.fire({icon:'info',title:'Medio ya agregado',text:'Edite la fila existente para ese medio.',confirmButtonColor:'#2563eb'});
                    return;
                }
                selector.dataset.anterior = selector.value;
                mostrarCampoComplementario(fila);
                actualizarOpciones();
                actualizarPago();
            });
            fila.querySelector('.quitar-medio').addEventListener('click', function() {
                fila.remove();
                actualizarOpciones();
                actualizarPago();
            });
            actualizarOpciones();
            actualizarPago();
            return fila;
        }
        function actualizarPago() {
            const medios = [...form.querySelectorAll('.medio-importe')];
            const efectivoAplicado = form.querySelector('[data-medio-importe="EFECTIVO"]');
            const efectivoRecibido = form.querySelector('.efectivo-recibido');
            const totalDeudas = Math.round(sumar(deudas) * 100) / 100;
            const totalMedios = Math.round(sumar(medios) * 100) / 100;
            const falta = Math.round((totalDeudas - totalMedios) * 100) / 100;
            const vuelto = efectivoAplicado && efectivoRecibido ? Math.max(0, Math.round(((parseFloat(efectivoRecibido.value) || 0) - (parseFloat(efectivoAplicado.value) || 0)) * 100) / 100) : 0;
            document.getElementById('pago-total-deudas').textContent = dinero(totalDeudas);
            document.getElementById('pago-total-medios').textContent = dinero(totalMedios);
            document.getElementById('pago-falta').textContent = falta < 0 ? 'Excede ' + dinero(Math.abs(falta)) : dinero(falta);
            document.getElementById('pago-falta').classList.toggle('text-red-300', Math.abs(falta) > 0.009);
            document.getElementById('pago-vuelto').textContent = dinero(vuelto);
            return {totalDeudas, totalMedios, falta};
        }
        deudas.forEach(campo => campo.addEventListener('input', actualizarPago));
        document.getElementById('agregar-medio').addEventListener('click', function() { agregarMedio(); });
        document.getElementById('completar-efectivo').addEventListener('click', function() {
            let filaEfectivo = filasMedio().find(fila => fila.querySelector('.medio-tipo').value === 'EFECTIVO');
            if (!filaEfectivo) filaEfectivo = agregarMedio('EFECTIVO');
            const efectivoAplicado = filaEfectivo.querySelector('.medio-importe');
            const efectivoRecibido = filaEfectivo.querySelector('.efectivo-recibido');
            const medios = [...form.querySelectorAll('.medio-importe')];
            const totalDeudas = Math.round(sumar(deudas) * 100) / 100;
            const otros = Math.round(medios.filter(campo => campo !== efectivoAplicado).reduce((total, campo) => total + (parseFloat(campo.value) || 0), 0) * 100) / 100;
            efectivoAplicado.value = Math.max(0, Math.round((totalDeudas - otros) * 100) / 100).toFixed(2);
            if ((parseFloat(efectivoRecibido.value) || 0) < parseFloat(efectivoAplicado.value)) efectivoRecibido.value = efectivoAplicado.value;
            actualizarPago();
        });
        form.addEventListener('submit', function(event) {
            const resumen = actualizarPago();
            const efectivoAplicado = form.querySelector('[data-medio-importe="EFECTIVO"]');
            const efectivoRecibido = form.querySelector('.efectivo-recibido');
            let mensaje = '';
            if (resumen.totalDeudas <= 0) mensaje = 'Indique cuánto se aplicará a una o más deudas.';
            else if (resumen.totalMedios <= 0) mensaje = 'Distribuya el importe en al menos un medio de pago.';
            else if (Math.abs(resumen.falta) > 0.009) mensaje = 'La suma de los medios debe coincidir con el total aplicado a las deudas.';
            else if (efectivoAplicado && efectivoRecibido && (parseFloat(efectivoAplicado.value) || 0) > (parseFloat(efectivoRecibido.value) || 0)) mensaje = 'El efectivo recibido no puede ser menor que el efectivo aplicado.';
            if (mensaje) {
                event.preventDefault();
                event.stopImmediatePropagation();
                if (window.Swal) Swal.fire({icon:'warning',title:'Revise el pago',text:mensaje,confirmButtonColor:'#059669'});
                else alert(mensaje);
            }
        });
        actualizarOpciones();
        actualizarPago();
    });
</script>
