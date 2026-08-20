<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Estado de cuenta · <?= htmlspecialchars($estadoCuenta['alumno']['nombre']) ?></title>
    <link rel="stylesheet" href="assets/css/output.css">
    <style>
        @media print {
            .no-print {
                display: none !important
            }

            body {
                background: white !important
            }

            .sombra {
                box-shadow: none !important
            }
        }
    </style>
</head>

<body class="bg-slate-100 p-6 text-slate-800">
    <main class="sombra mx-auto max-w-4xl rounded-2xl bg-white p-8">
        <div class="no-print mb-6 flex justify-between"><a href="index.php?url=cobros&mes=todos&alumno=<?= $estadoCuenta['alumno']['id_persona'] ?>" class="rounded-lg border px-4 py-2 text-sm font-semibold">Volver</a><button onclick="window.print()" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Imprimir estado de cuenta</button></div>
        <header class="border-b-2 border-blue-600 pb-5">
            <p class="text-sm font-semibold text-blue-600">COLEGIO · CONTROL DE ASISTENCIA Y PAGOS</p>
            <h1 class="mt-1 text-2xl font-bold">Estado de cuenta</h1>
            <div class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                <p><b>Alumno:</b> <?= htmlspecialchars($estadoCuenta['alumno']['nombre']) ?></p>
                <p><b>Documento:</b> <?= htmlspecialchars($estadoCuenta['alumno']['numero_documento']) ?></p>
                <p><b>Emitido:</b> <?= date('d/m/Y H:i') ?></p>
            </div>
        </header>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="p-3">Período / sección</th>
                        <th class="p-3">Concepto</th>
                        <th class="p-3">Vencimiento</th>
                        <th class="p-3">Importe final</th>
                        <th class="p-3">Pagado</th>
                        <th class="p-3">Saldo</th>
                        <th class="p-3">Estado</th>
                    </tr>
                </thead>
                <tbody><?php $total = 0;
                        $pagado = 0;
                        $saldo = 0;
                        foreach ($estadoCuenta['cuentas'] as $cuenta): $total += (float)$cuenta['importe_final'];
                            $pagado += (float)$cuenta['pagado'];
                            $saldo += (float)$cuenta['saldo']; ?><tr class="border-b">
                            <td class="p-3"><?= htmlspecialchars($cuenta['periodo_academico']) ?><span class="block text-xs text-slate-500"><?= htmlspecialchars($cuenta['seccion']) ?></span></td>
                            <td class="p-3"><?= htmlspecialchars($cuenta['concepto']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($cuenta['fecha_vencimiento'] ?: '—') ?></td>
                            <td class="p-3">S/ <?= number_format((float)$cuenta['importe_final'], 2) ?></td>
                            <td class="p-3 text-emerald-700">S/ <?= number_format((float)$cuenta['pagado'], 2) ?></td>
                            <td class="p-3 font-semibold">S/ <?= number_format((float)$cuenta['saldo'], 2) ?></td>
                            <td class="p-3"><?= htmlspecialchars($cuenta['estado']) ?></td>
                        </tr><?php endforeach; ?></tbody>
                <tfoot class="bg-slate-50 font-bold">
                    <tr>
                        <td colspan="3" class="p-3">Totales</td>
                        <td class="p-3">S/ <?= number_format($total, 2) ?></td>
                        <td class="p-3 text-emerald-700">S/ <?= number_format($pagado, 2) ?></td>
                        <td class="p-3">S/ <?= number_format($saldo, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <p class="mt-8 text-xs text-slate-500">Documento informativo. Para consultas, comuníquese con Tesorería.</p>
    </main>
</body>

</html>