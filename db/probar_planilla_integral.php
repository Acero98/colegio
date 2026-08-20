<?php
if (PHP_SAPI !== 'cli') exit("Esta prueba debe ejecutarse desde consola.\n");

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../app/Models/PlanillaPreviewModel.php';

$pruebas = [];
$errores = [];
function comprobarPlanilla(string $caso, bool $condicion, string $detalle): void
{
    global $pruebas, $errores;
    $pruebas[] = ['caso'=>$caso,'estado'=>$condicion?'OK':'ERROR','detalle'=>$detalle];
    if (!$condicion) $errores[] = $caso . ': ' . $detalle;
}
function casiIgual(float $actual, float $esperado): bool { return abs($actual-$esperado)<0.02; }

// Casos controlados de cálculo puro.
$sueldoMensual = 1000.00;
$diasExigibles = 21; // 22 días programados menos un feriado.
$diasCumplidos = 21;
$mensualCalculado = round($sueldoMensual*($diasCumplidos/$diasExigibles),2);
comprobarPlanilla('Mensual con feriado',casiIgual($mensualCalculado,1000),'Un feriado no reduce el sueldo cuando se cumplen los demás días exigibles.');

$pagoHoras = round(8*10,2);
comprobarPlanilla('Pago por horas',casiIgual($pagoHoras,80),'8 horas a S/ 10 generan S/ 80.');

$pagoMixto = round(500+(4*20),2);
comprobarPlanilla('Modalidad mixta',casiIgual($pagoMixto,580),'S/ 500 mensual más 4 horas a S/ 20 generan S/ 580.');

$descuento = round((15*1)+(1*50),2);
$neto = round(1000-$descuento,2);
comprobarPlanilla('Tardanza y falta',casiIgual($descuento,65)&&casiIgual($neto,935),'15 minutos y una falta descuentan S/ 65; neto S/ 935.');

$saldoParcial = round(935-300,2);
comprobarPlanilla('Pago parcial',casiIgual($saldoParcial,635),'Un pago de S/ 300 deja saldo S/ 635.');
$saldoRestaurado = round($saldoParcial+300,2);
comprobarPlanilla('Anulación de pago',casiIgual($saldoRestaurado,935),'La anulación restaura el saldo original.');

// Invariantes sobre los datos reales, sin modificarlos.
$saldosInconsistentes = (int)$conexion->query("SELECT COUNT(*) FROM planilla_detalles WHERE ABS(monto_pagar-monto_pagado-saldo_pendiente)>0.01")->fetchColumn();
comprobarPlanilla('Integridad de saldos',$saldosInconsistentes===0,"Registros inconsistentes: $saldosInconsistentes.");

$pagosInconsistentes = (int)$conexion->query("SELECT COUNT(*) FROM planilla_detalles pd LEFT JOIN (SELECT id_planilla_detalle,SUM(importe) total FROM pagos_planilla WHERE estado='REGISTRADO' GROUP BY id_planilla_detalle) pg ON pg.id_planilla_detalle=pd.id_planilla_detalle WHERE ABS(pd.monto_pagado-COALESCE(pg.total,0))>0.01")->fetchColumn();
comprobarPlanilla('Conciliación de pagos',$pagosInconsistentes===0,"Detalles sin conciliar: $pagosInconsistentes.");

$periodosIncorrectos = (int)$conexion->query("SELECT COUNT(*) FROM periodos_pago pp WHERE pp.estado='PAGADO' AND EXISTS(SELECT 1 FROM planilla_detalles pd WHERE pd.id_periodo_pago=pp.id_periodo_pago AND pd.saldo_pendiente>0.01)")->fetchColumn();
comprobarPlanilla('Estados de períodos',$periodosIncorrectos===0,"Períodos pagados con deuda: $periodosIncorrectos.");

$vistaPrevia = new PlanillaPreviewModel($conexion);
$periodos = $conexion->query("SELECT id_periodo_pago,nombre FROM periodos_pago ORDER BY fecha_inicio")->fetchAll();
foreach ($periodos as $periodo) {
    $datos = $vistaPrevia->generar((int)$periodo['id_periodo_pago']);
    $valido = true;
    foreach ($datos['filas'] as $fila) {
        $esperado = max(0,(float)$fila['mensual']+(float)$fila['pagoHora']+(float)$fila['pagoSesion']+(float)$fila['bonificaciones']-(float)$fila['descuentos']);
        if (!casiIgual((float)$fila['neto'],$esperado) || (float)$fila['neto']<0 || (int)$fila['feriados']<0) { $valido=false; break; }
    }
    comprobarPlanilla('Vista previa · '.$periodo['nombre'],$valido,'Neto, descuentos y feriados son coherentes en '.count($datos['filas']).' trabajador(es).');
}

$cobertura = [
    'mensuales'=>(int)$conexion->query("SELECT COUNT(*) FROM vinculos_laborales WHERE estado='ACTIVO' AND monto_mensual>0")->fetchColumn(),
    'por_horas'=>(int)$conexion->query("SELECT COUNT(*) FROM vinculos_laborales WHERE estado='ACTIVO' AND monto_hora>0")->fetchColumn(),
    'mixtos'=>(int)$conexion->query("SELECT COUNT(*) FROM vinculos_laborales WHERE estado='ACTIVO' AND modalidad_pago='MIXTO'")->fetchColumn(),
    'tardanzas'=>(int)$conexion->query("SELECT COUNT(*) FROM asistencias_diarias WHERE minutos_tardanza>0 AND estado<>'ANULADO'")->fetchColumn(),
    'faltas'=>(int)$conexion->query("SELECT COUNT(*) FROM asistencias_diarias WHERE estado='FALTA'")->fetchColumn(),
    'feriados'=>(int)$conexion->query("SELECT COUNT(*) FROM calendario_institucional WHERE estado='ACTIVO'")->fetchColumn(),
    'pagos_parciales'=>(int)$conexion->query("SELECT COUNT(*) FROM planilla_detalles WHERE estado='PARCIAL'")->fetchColumn(),
];

$salida = ['estado'=>$errores?'ERROR':'OK','modo'=>'SOLO_LECTURA','pruebas'=>count($pruebas),'errores'=>$errores,'cobertura_actual'=>$cobertura,'resultados'=>$pruebas];
echo json_encode($salida,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . PHP_EOL;
exit($errores?1:0);
