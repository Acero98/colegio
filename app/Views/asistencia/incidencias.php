<?php
$puedeGestionar = tienePermiso('incidencias.gestionar');
$rolActual = (string)($_SESSION['usuario']['rol'] ?? '');
$etiquetasTipo = [
    'JUSTIFICACION_FALTA' => 'Justificación de falta',
    'JUSTIFICACION_TARDANZA' => 'Justificación de tardanza',
    'PERMISO' => 'Permiso',
    'LICENCIA' => 'Licencia',
    'OBSERVACION' => 'Observación',
];
$coloresIncidencia = [
    'PENDIENTE' => 'bg-amber-100 text-amber-800',
    'APROBADA' => 'bg-emerald-100 text-emerald-700',
    'RECHAZADA' => 'bg-rose-100 text-rose-700',
];
$coloresAsistencia = [
    'PUNTUAL'=>'bg-emerald-100 text-emerald-700','TARDANZA'=>'bg-amber-100 text-amber-800','FALTA'=>'bg-red-100 text-red-700',
    'JUSTIFICADO'=>'bg-sky-100 text-sky-700','PERMISO'=>'bg-violet-100 text-violet-700','INCOMPLETO'=>'bg-orange-100 text-orange-700',
    'LICENCIA'=>'bg-slate-200 text-slate-700','SALIDA_ANTICIPADA'=>'bg-pink-100 text-pink-700'
];
?>
<div class="mb-6">
    <p class="text-sm font-medium text-blue-600">Asistencia</p>
    <h1 class="text-2xl font-bold">Justificaciones e incidencias</h1>
    <p class="mt-1 text-sm text-slate-500">Registre el sustento y conserve la decisión, el responsable y la fecha de resolución.</p>
</div>

<section class="grid gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
    <?php if ($puedeGestionar): ?>
    <article class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Registrar incidencia</h2>
        <p class="mt-1 text-xs text-slate-500">La solicitud quedará pendiente hasta que un responsable la apruebe o rechace. Las incidencias aprobadas solo permanecen en el historial.</p>
        <form method="post" action="index.php?url=incidencia_guardar" class="mt-4 space-y-3" data-confirmar-accion="registrar esta incidencia">
            <?= campoCsrf() ?>
            <select required name="id_asistencia" class="form-input mt-0">
                <option value="">Seleccione asistencia</option>
                <?php foreach($asistencias as $a): ?>
                    <option value="<?= (int)$a['id_asistencia'] ?>"><?= htmlspecialchars($a['fecha'].' · '.$a['persona'].' · '.$a['estado']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="tipo" class="form-input mt-0">
                <?php foreach ($etiquetasTipo as $valor => $etiqueta): ?><option value="<?= $valor ?>"><?= htmlspecialchars($etiqueta) ?></option><?php endforeach; ?>
            </select>
            <textarea required minlength="3" name="motivo" rows="4" class="form-input mt-0" placeholder="Detalle y sustento"></textarea>
            <button class="w-full rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"><i class="bi bi-file-earmark-plus mr-1"></i> Registrar</button>
        </form>
    </article>
    <?php endif; ?>

    <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm <?= $puedeGestionar ? '' : 'xl:col-span-2' ?>">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold">Historial de incidencias</h2>
            <p class="text-xs text-slate-500">Dirección autoriza permisos y licencias; el responsable de asistencia resuelve las justificaciones.</p>
        </div>
        <div class="overflow-x-auto">
            <table data-datatable data-page-options="5,10,25,50" data-order="1:desc" class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr>
                    <th class="px-4 py-3">Persona</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Solicitud</th><th class="px-4 py-3">Asistencia</th><th class="px-4 py-3">Resolución</th><?php if($puedeGestionar): ?><th class="px-4 py-3">Acciones</th><?php endif; ?>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach($incidencias as $i):
                    $requiereDireccion = in_array($i['tipo'],['PERMISO','LICENCIA'],true);
                    $esDireccion = in_array($rolActual,['Director','Administrador','Super Administrador'],true);
                    $puedeResolver = $puedeGestionar && $i['estado']==='PENDIENTE' && ($requiereDireccion ? $esDireccion : $rolActual!=='Director');
                    $puedeAprobar = $puedeResolver && !(bool)$i['planilla_cerrada'];
                ?>
                    <tr>
                        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($i['persona']) ?><small class="mt-1 block font-normal text-slate-500">Registró: <?= htmlspecialchars($i['registrado_por_nombre']) ?><br><?= htmlspecialchars($i['creado_en']) ?></small></td>
                        <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($i['fecha']) ?></td>
                        <td class="max-w-xs px-4 py-3"><span class="font-semibold text-slate-700"><?= htmlspecialchars($etiquetasTipo[$i['tipo']] ?? $i['tipo']) ?></span><small class="mt-1 block whitespace-normal text-slate-500"><?= htmlspecialchars($i['motivo']) ?></small></td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= $coloresAsistencia[$i['asistencia_estado']] ?? 'bg-slate-100 text-slate-700' ?>"><?= htmlspecialchars($i['asistencia_estado']) ?></span></td>
                        <td class="max-w-xs px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= $coloresIncidencia[$i['estado']] ?? 'bg-slate-100 text-slate-700' ?>"><?= htmlspecialchars($i['estado']) ?></span><?php if($i['resuelto_en']): ?><small class="mt-1 block whitespace-normal text-slate-500">Por: <?= htmlspecialchars($i['resuelto_por_nombre'] ?: 'Usuario') ?><br><?= htmlspecialchars($i['resuelto_en']) ?><?php if(!empty($i['resolucion_observacion'])): ?><br><?= htmlspecialchars($i['resolucion_observacion']) ?><?php endif; ?></small><?php endif; ?></td>
                        <?php if($puedeGestionar): ?><td class="px-4 py-3"><?php if($puedeResolver): ?><div class="flex items-center gap-2"><?php if($puedeAprobar): ?><button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 transition hover:bg-emerald-200" title="Aprobar" onclick='resolverIncidencia(<?= (int)$i['id_incidencia'] ?>,"APROBADA",<?= json_encode($i['persona'],JSON_HEX_APOS|JSON_HEX_QUOT) ?>,<?= json_encode($etiquetasTipo[$i['tipo']]??$i['tipo'],JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="bi bi-check-lg"></i></button><?php else: ?><span class="rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-500" title="La planilla de este período está cerrada o pagada">Planilla cerrada</span><?php endif; ?><button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-rose-100 text-rose-700 transition hover:bg-rose-200" title="Rechazar" onclick='resolverIncidencia(<?= (int)$i['id_incidencia'] ?>,"RECHAZADA",<?= json_encode($i['persona'],JSON_HEX_APOS|JSON_HEX_QUOT) ?>,<?= json_encode($etiquetasTipo[$i['tipo']]??$i['tipo'],JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="bi bi-x-lg"></i></button></div><?php else: ?><span class="text-xs text-slate-400">—</span><?php endif; ?></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if(!$incidencias): ?><tr><td colspan="<?= $puedeGestionar ? 6 : 5 ?>" class="px-5 py-12 text-center text-slate-500">No hay incidencias registradas.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php if($puedeGestionar): ?>
<form id="form-resolver-incidencia" method="post" action="index.php?url=incidencia_resolver" class="hidden"><?= campoCsrf() ?><input type="hidden" name="id_incidencia"><input type="hidden" name="decision"><input type="hidden" name="observacion"></form>
<script>
function resolverIncidencia(id, decision, persona, tipo) {
    const aprobar = decision === 'APROBADA';
    const enviar = (observacion) => {
        const form = document.getElementById('form-resolver-incidencia');
        form.elements.id_incidencia.value = id;
        form.elements.decision.value = decision;
        form.elements.observacion.value = observacion;
        form.submit();
    };
    if (typeof Swal === 'undefined') {
        const observacion = window.prompt('Observación de la resolución:');
        if (observacion && observacion.trim().length >= 3 && window.confirm('¿Confirma la decisión?')) enviar(observacion.trim());
        return;
    }
    Swal.fire({
        icon: aprobar ? 'question' : 'warning',
        title: aprobar ? '¿Aprobar incidencia?' : '¿Rechazar incidencia?',
        text: `${persona} · ${tipo}`,
        input: 'textarea',
        inputLabel: 'Observación de resolución',
        inputPlaceholder: aprobar ? 'Ej. Sustento verificado y conforme' : 'Ej. Documento insuficiente o fuera de plazo',
        inputAttributes: { maxlength: '500' },
        showCancelButton: true,
        confirmButtonText: aprobar ? 'Sí, aprobar' : 'Sí, rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: aprobar ? '#059669' : '#e11d48',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        inputValidator: value => !value || value.trim().length < 3 ? 'Ingrese una observación de al menos 3 caracteres.' : undefined
    }).then(resultado => { if (resultado.isConfirmed) enviar(resultado.value.trim()); });
}
</script>
<?php endif; ?>
