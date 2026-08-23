<?php $t = $flujo['totales']; ?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte financiero <?= htmlspecialchars($desde . ' a ' . $hasta) ?></title>
    <style>
        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: #172554;
            font: 13px Arial, sans-serif
        }

        .acciones {
            max-width: 1000px;
            margin: 18px auto;
            text-align: right
        }

        .acciones button {
            border: 0;
            border-radius: 9px;
            background: #2563eb;
            color: #fff;
            padding: 10px 16px;
            font-weight: bold;
            cursor: pointer
        }

        .reporte {
            width: min(1000px, calc(100% - 24px));
            margin: 0 auto 30px;
            background: #fff;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 15px 45px #0f172a1a
        }

        .cabecera {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 2px solid #bfdbfe;
            padding-bottom: 18px
        }

        .cabecera h1 {
            margin: 0 0 5px;
            font-size: 23px
        }

        .cabecera p {
            margin: 3px 0;
            color: #64748b
        }

        .periodo {
            text-align: right
        }

        .periodo b {
            display: block;
            font-size: 18px
        }

        .kpis {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 18px 0
        }

        .kpi {
            border-radius: 12px;
            padding: 14px;
            background: #eff6ff
        }

        .kpi.ingreso {
            background: #ecfdf5;
            color: #047857
        }

        .kpi.egreso {
            background: #fff1f2;
            color: #be123c
        }

        .kpi small {
            display: block;
            text-transform: uppercase
        }

        .kpi b {
            display: block;
            margin-top: 5px;
            font-size: 19px
        }

        .nota {
            padding: 12px;
            border-left: 4px solid #7c3aed;
            background: #f5f3ff;
            color: #5b21b6
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px
        }

        .bloque {
            margin-top: 20px
        }

        .bloque h2 {
            font-size: 15px;
            margin: 0 0 8px
        }

        .tabla {
            width: 100%;
            border-collapse: collapse
        }

        .tabla th {
            background: #eff6ff;
            color: #475569;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
            padding: 8px
        }

        .tabla td {
            border-bottom: 1px solid #e2e8f0;
            padding: 9px
        }

        .monto {
            text-align: right;
            white-space: nowrap
        }

        .pie {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px dashed #94a3b8;
            color: #64748b;
            font-size: 10px
        }

        @media(max-width:700px) {

            .kpis,
            .grid {
                grid-template-columns: 1fr 1fr
            }

            .reporte {
                padding: 18px
            }

            .cabecera {
                display: block
            }

            .periodo {
                text-align: left;
                margin-top: 12px
            }
        }

        @media print {
            body {
                background: #fff
            }

            .acciones {
                display: none
            }

            .reporte {
                width: 100%;
                margin: 0;
                padding: 8mm;
                box-shadow: none;
                border-radius: 0
            }

            @page {
                size: A4;
                margin: 7mm
            }
        }
    </style>
</head>

<body>
    <div class="acciones"><button onclick="window.print()">Imprimir</button></div>
    <main class="reporte">
        <header class="cabecera">
            <div>
                <h1><?= htmlspecialchars($institucion['nombre']) ?></h1><?php if (!empty($institucion['ruc'])): ?><p>RUC <?= htmlspecialchars($institucion['ruc']) ?></p><?php endif; ?><p><?= htmlspecialchars($institucion['direccion'] ?? '') ?></p>
            </div>
            <div class="periodo"><span>REPORTE FINANCIERO</span><b><?= date('d/m/Y', strtotime($desde)) ?> al <?= date('d/m/Y', strtotime($hasta)) ?></b><small>Emitido <?= date('d/m/Y H:i') ?></small></div>
        </header>
        <section class="kpis">
            <div class="kpi ingreso"><small>Entradas</small><b>S/ <?= number_format($t['ingresos'], 2) ?></b></div>
            <div class="kpi egreso"><small>Salidas</small><b>S/ <?= number_format($t['egresos'], 2) ?></b></div>
            <div class="kpi"><small>Flujo neto</small><b>S/ <?= number_format($t['flujo_neto'], 2) ?></b></div>
            <div class="kpi"><small>Resultado operativo</small><b>S/ <?= number_format($t['resultado_operativo'], 2) ?></b></div>
        </section>
        <p class="nota">El flujo neto incluye aportes de capital y préstamos. El resultado operativo los excluye para no confundir liquidez con ganancia.</p>
        <div class="grid">
            <section class="bloque">
                <h2>Resumen por origen</h2>
                <table class="tabla">
                    <tbody>
                        <tr>
                            <td>Cobros de alumnos</td>
                            <td class="monto">S/ <?= number_format($t['cobros'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Otros ingresos</td>
                            <td class="monto">S/ <?= number_format($t['otros_ingresos'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Pagos de planilla</td>
                            <td class="monto">S/ <?= number_format($t['planilla'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Otros egresos</td>
                            <td class="monto">S/ <?= number_format($t['otros_egresos'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Financiamiento neto</td>
                            <td class="monto">S/ <?= number_format($t['financiamiento_neto'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Capital neto</td>
                            <td class="monto">S/ <?= number_format($t['capital_neto'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </section>
            <section class="bloque">
                <h2>Flujo por medio</h2>
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Medio</th>
                            <th class="monto">Entró</th>
                            <th class="monto">Salió</th>
                            <th class="monto">Neto</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($flujo['por_medio'] as $fila): ?><tr>
                                <td><?= htmlspecialchars($fila['medio']) ?></td>
                                <td class="monto">S/ <?= number_format($fila['ingreso'], 2) ?></td>
                                <td class="monto">S/ <?= number_format($fila['egreso'], 2) ?></td>
                                <td class="monto">S/ <?= number_format($fila['neto'], 2) ?></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </section>
        </div>
        <section class="bloque">
            <h2>Otros movimientos por categoría</h2>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Clasificación</th>
                        <th>Medio</th>
                        <th>Operaciones</th>
                        <th class="monto">Total</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($flujo['manuales'] as $fila): ?><tr>
                            <td><?= $fila['tipo'] ?></td>
                            <td><?= htmlspecialchars($fila['categoria']) ?></td>
                            <td><?= $fila['clasificacion'] ?></td>
                            <td><?= $fila['medio'] ?></td>
                            <td><?= $fila['operaciones'] ?></td>
                            <td class="monto">S/ <?= number_format((float)$fila['total'], 2) ?></td>
                        </tr><?php endforeach; ?><?php if (!$flujo['manuales']): ?><tr>
                            <td colspan="6">No se registraron movimientos adicionales en el período.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </section>
        <section class="bloque">
            <h2>Control de caja</h2>
            <table class="tabla">
                <tbody>
                    <tr>
                        <td>Turnos cerrados</td>
                        <td class="monto"><?= $flujo['control_caja']['cierres'] ?></td>
                    </tr>
                    <tr>
                        <td>Pendientes de revisión</td>
                        <td class="monto"><?= $flujo['control_caja']['pendientes'] ?></td>
                    </tr>
                    <tr>
                        <td>Revisiones registradas</td>
                        <td class="monto"><?= $flujo['control_caja']['revisiones'] ?></td>
                    </tr>
                    <tr>
                        <td>Diferencia neta actual</td>
                        <td class="monto">S/ <?= number_format($flujo['control_caja']['diferencia_neta'], 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </section>
        <footer class="pie">Reporte interno de flujo financiero. Los movimientos anulados no forman parte de los totales. Documento generado por el sistema institucional.</footer>
    </main>
</body>

</html>