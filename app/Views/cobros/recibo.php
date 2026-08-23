<?php
$pago = $recibo['pago'];
$detalles = $recibo['detalles'];
$medios = $recibo['medios'];
$institucion = $recibo['institucion'];
$alumnos = [];
foreach ($detalles as $detalle) {
    $alumnos[$detalle['codigo_alumno']] = [
        'nombre' => $detalle['alumno'],
        'documento' => $detalle['documento_alumno'],
        'codigo' => $detalle['codigo_alumno'],
        'seccion' => $detalle['seccion'],
    ];
}
$esAnulado = $pago['estado'] === 'ANULADO';
$claseFormato = $formato === 'termico' ? 'formato-termico' : 'formato-a4';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Recibo <?= htmlspecialchars($pago['numero_recibo']) ?></title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#eef4ff;color:#172554;font-family:Arial,sans-serif}.acciones{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px;max-width:900px;margin:22px auto 12px;padding:0 12px}.acciones a,.acciones button{border:0;border-radius:10px;padding:10px 15px;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer}.acciones a{background:#fff;color:#334155;border:1px solid #cbd5e1}.acciones .activo{background:#dbeafe;color:#1d4ed8;border-color:#93c5fd}.acciones button{background:#2563eb;color:#fff}.recibo{position:relative;margin:0 auto 30px;background:#fff;border:1px solid #bfdbfe;box-shadow:0 18px 50px #1e3a8a1f;overflow:hidden}.formato-a4{width:min(900px,calc(100% - 24px));min-height:560px;border-radius:20px;padding:34px}.formato-termico{width:80mm;min-height:120mm;padding:7mm 5mm;font-size:11px}.cabecera{display:flex;justify-content:space-between;gap:20px;padding-bottom:20px;border-bottom:2px solid #dbeafe}.institucion h1{font-size:24px;margin:0 0 5px}.institucion p{margin:3px 0;color:#475569;font-size:13px}.logo-comprobante{display:block;max-width:74px;max-height:58px;margin-bottom:9px;object-fit:contain}.numero{text-align:right;color:#1d4ed8;font-size:15px;font-weight:800}.numero strong{display:block;margin-top:6px;color:#172554;font-size:21px}.estado-anulado{margin-top:10px;border:2px solid #dc2626;border-radius:8px;padding:6px;color:#dc2626;text-align:center;font-weight:900;transform:rotate(-3deg)}.bloques{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0}.bloque{border-radius:12px;background:#eff6ff;padding:13px}.bloque small{display:block;margin-bottom:5px;color:#64748b}.bloque b,.bloque span{display:block;line-height:1.35}.tabla{width:100%;border-collapse:collapse;margin-top:18px;font-size:13px}.tabla th{padding:10px 8px;background:#eff6ff;color:#475569;text-align:left;font-size:11px;text-transform:uppercase}.tabla td{padding:11px 8px;border-bottom:1px solid #e2e8f0;vertical-align:top}.tabla .monto{text-align:right;white-space:nowrap}.tabla small{display:block;color:#64748b;margin-top:3px}.total{display:flex;justify-content:flex-end;margin-top:18px}.total div{min-width:260px;border-radius:13px;background:#dcfce7;padding:15px;color:#166534}.total span{display:flex;justify-content:space-between;align-items:center}.total strong{font-size:25px}.pie{display:flex;justify-content:space-between;gap:20px;margin-top:28px;padding-top:15px;border-top:1px dashed #94a3b8;color:#64748b;font-size:11px;line-height:1.45}.aviso{margin-top:14px;text-align:center;color:#64748b;font-size:10px}.marca-anulado{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;color:#dc26261a;font-size:100px;font-weight:900;transform:rotate(-28deg)}
        .confirmacion{max-width:900px;margin:0 auto 12px;border:1px solid #86efac;border-radius:12px;background:#dcfce7;padding:12px 16px;color:#166534;font-size:14px;font-weight:700}.formato-termico .cabecera{display:block;text-align:center;padding-bottom:10px}.formato-termico .institucion h1{font-size:17px}.formato-termico .institucion p{font-size:10px}.formato-termico .numero{text-align:center;margin-top:10px;font-size:11px}.formato-termico .numero strong{font-size:16px}.formato-termico .bloques{display:block;margin:10px 0}.formato-termico .bloque{padding:7px;background:transparent;border-bottom:1px dashed #94a3b8;border-radius:0}.formato-termico .tabla{font-size:10px;margin-top:8px}.formato-termico .tabla th{padding:5px 2px;font-size:8px}.formato-termico .tabla td{padding:6px 2px}.formato-termico .col-seccion,.formato-termico .col-saldo{display:none}.formato-termico .total{margin-top:10px}.formato-termico .total div{min-width:100%;padding:9px}.formato-termico .total strong{font-size:19px}.formato-termico .pie{display:block;margin-top:14px;padding-top:9px}.formato-termico .pie span{display:block;margin-bottom:4px}.formato-termico .marca-anulado{font-size:38px}.formato-termico .estado-anulado{font-size:10px}
        @media(max-width:640px){.formato-a4{padding:20px}.cabecera{display:block}.numero{text-align:left;margin-top:12px}.bloques{grid-template-columns:1fr}.tabla{font-size:11px}.tabla th,.tabla td{padding:8px 5px}.pie{display:block}.pie span{display:block;margin-bottom:6px}}
        @media print{body{background:#fff}.acciones,.confirmacion{display:none}.recibo{margin:0;box-shadow:none;border:0;border-radius:0}.formato-a4{width:100%;min-height:auto;padding:12mm}.formato-termico{width:80mm;padding:4mm 3mm}@page{margin:5mm}}
    </style>
</head>
<body>
<nav class="acciones">
    <a href="index.php?url=cobros_pagos">Volver</a>
    <a class="<?= $formato === 'a4' ? 'activo' : '' ?>" href="index.php?url=cobros_recibo&id=<?= (int)$pago['id_pago'] ?>&formato=a4">Formato A4</a>
    <a class="<?= $formato === 'termico' ? 'activo' : '' ?>" href="index.php?url=cobros_recibo&id=<?= (int)$pago['id_pago'] ?>&formato=termico">Formato 80 mm</a>
    <button type="button" onclick="window.print()">Imprimir</button>
</nav>
<?php if ($nuevo): ?><div class="confirmacion">Pago registrado correctamente. Se generó el recibo <?= htmlspecialchars($pago['numero_recibo']) ?>.</div><?php endif; ?>
<main class="recibo <?= $claseFormato ?>">
    <?php if ($esAnulado): ?><div class="marca-anulado">ANULADO</div><?php endif; ?>
    <header class="cabecera">
        <div class="institucion">
            <?php if (!empty($institucion['logo'])): ?><img src="index.php?url=institucion_logo" class="logo-comprobante" alt="Logo institucional"><?php endif; ?>
            <h1><?= htmlspecialchars($institucion['nombre']) ?></h1>
            <?php if (!empty($institucion['razon_social']) && $institucion['razon_social'] !== $institucion['nombre']): ?><p><?= htmlspecialchars($institucion['razon_social']) ?></p><?php endif; ?>
            <?php if (!empty($institucion['ruc'])): ?><p>RUC <?= htmlspecialchars($institucion['ruc']) ?></p><?php endif; ?>
            <p><?= htmlspecialchars($institucion['direccion'] ?? '') ?></p>
            <p><?= htmlspecialchars(trim(($institucion['telefono'] ?? '') . ' · ' . ($institucion['correo'] ?? ''), ' ·')) ?></p>
        </div>
        <div class="numero">RECIBO INTERNO<strong><?= htmlspecialchars($pago['numero_recibo']) ?></strong><?php if ($esAnulado): ?><div class="estado-anulado">DOCUMENTO ANULADO</div><?php endif; ?></div>
    </header>
    <section class="bloques">
        <div class="bloque"><small>Alumno(s)</small><?php foreach ($alumnos as $alumno): ?><b><?= htmlspecialchars($alumno['nombre']) ?></b><span><?= htmlspecialchars($alumno['codigo'] . ' · ' . $alumno['seccion']) ?></span><?php endforeach; ?></div>
        <div class="bloque"><small>Apoderado / responsable</small><b><?= htmlspecialchars(trim($pago['apoderado'], ', ') ?: 'No consignado') ?></b><span><?= htmlspecialchars($pago['documento_apoderado'] ?: 'Documento no consignado') ?></span></div>
        <div class="bloque"><small>Fecha y medios de pago</small><b><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></b><?php foreach ($medios as $medio): ?><span><?= htmlspecialchars($medio['medio']) ?>: S/ <?= number_format((float)$medio['importe_aplicado'], 2) ?><?php if (!empty($medio['referencia'])): ?> · Ref. <?= htmlspecialchars($medio['referencia']) ?><?php endif; ?><?php if (!empty($medio['evidencia_archivo'])): ?> · <a target="_blank" href="index.php?url=cobros_evidencia&id=<?= (int)$medio['id_pago_medio'] ?>" style="color:#1d4ed8;font-weight:700">Ver evidencia</a><?php endif; ?></span><?php endforeach; ?></div>
        <div class="bloque"><small>Registrado por</small><b><?= htmlspecialchars($pago['cajero']) ?></b><span><?= htmlspecialchars($pago['observacion'] ?: 'Sin observaciones') ?></span></div>
    </section>
    <table class="tabla">
        <thead><tr><th>Concepto</th><th>Período</th><th class="col-seccion">Alumno / sección</th><th class="monto">Pagado</th><th class="monto col-saldo">Saldo actual</th></tr></thead>
        <tbody><?php foreach ($detalles as $detalle): ?><tr><td><b><?= htmlspecialchars($detalle['concepto']) ?></b><small><?= htmlspecialchars($detalle['tipo_concepto']) ?></small></td><td><?= date('m/Y', strtotime($detalle['periodo_cobro'])) ?><small><?= htmlspecialchars($detalle['periodo_academico']) ?></small></td><td class="col-seccion"><?= htmlspecialchars($detalle['alumno']) ?><small><?= htmlspecialchars($detalle['seccion']) ?></small></td><td class="monto">S/ <?= number_format((float)$detalle['importe'], 2) ?></td><td class="monto col-saldo">S/ <?= number_format((float)$detalle['saldo_actual'], 2) ?><small><?= htmlspecialchars($detalle['estado_cuenta']) ?></small></td></tr><?php endforeach; ?></tbody>
    </table>
    <?php $efectivoRecibido = 0.0; $vuelto = 0.0; foreach ($medios as $medio) { if ($medio['medio'] === 'EFECTIVO') { $efectivoRecibido += (float)$medio['importe_recibido']; $vuelto += (float)$medio['vuelto']; } } ?>
    <div class="total"><div><span>Total aplicado <strong>S/ <?= number_format((float)$pago['importe_total'], 2) ?></strong></span><?php if ($efectivoRecibido > 0): ?><span>Efectivo recibido <b>S/ <?= number_format($efectivoRecibido, 2) ?></b></span><span>Vuelto <b>S/ <?= number_format($vuelto, 2) ?></b></span><?php endif; ?></div></div>
    <?php if ($esAnulado): ?><p class="estado-anulado">Anulado el <?= $pago['anulado_en'] ? date('d/m/Y H:i', strtotime($pago['anulado_en'])) : '—' ?> por <?= htmlspecialchars($pago['anulador'] ?: 'usuario autorizado') ?>. Motivo: <?= htmlspecialchars($pago['motivo_anulacion'] ?: 'No consignado') ?></p><?php endif; ?>
    <footer class="pie"><span>Emitido: <?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?><br>Documento generado por el sistema institucional.</span><span>Este recibo acredita el pago registrado en el colegio.<br>Conserve este documento para cualquier consulta.</span></footer>
    <p class="aviso">Comprobante interno de pago. No constituye comprobante electrónico autorizado por SUNAT.</p>
</main>
<?php if ($imprimir): ?><script>window.addEventListener('load',function(){window.print()});</script><?php endif; ?>
</body>
</html>
