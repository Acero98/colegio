<?php

if (PHP_SAPI !== 'cli') exit("Este proceso solo puede ejecutarse por consola.\n");
require_once __DIR__ . '/../config/conexion.php';

$errores = [];
$ok = [];

function comprobarDemo(bool $condicion, string $mensaje, array &$ok, array &$errores): void
{
    if ($condicion) $ok[] = $mensaje;
    else $errores[] = $mensaje;
}

$base = (string)$conexion->query('SELECT DATABASE()')->fetchColumn();
comprobarDemo($base === 'colegio', 'Conexion a la base colegio', $ok, $errores);

$tablas = $conexion->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
comprobarDemo(count($tablas) >= 43, 'Esquema completo (43 tablas)', $ok, $errores);
$vacias = [];
foreach ($tablas as $tabla) {
    if ((int)$conexion->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $tabla) . '`')->fetchColumn() === 0) $vacias[] = $tabla;
}
comprobarDemo(!$vacias, 'Todas las tablas funcionales tienen casos demostrativos', $ok, $errores);
if ($vacias) $errores[] = 'Tablas vacias: ' . implode(', ', $vacias);

$superAdmin = (int)$conexion->query("SELECT COUNT(DISTINCT u.id_usuario) FROM usuarios u JOIN usuario_rol ur ON ur.id_usuario=u.id_usuario JOIN roles r ON r.id_rol=ur.id_rol WHERE r.nombre='Super Administrador' AND u.estado='ACTIVO'")->fetchColumn();
comprobarDemo($superAdmin === 1, 'Existe un Super Administrador activo', $ok, $errores);

$usuariosDemo = $conexion->query("SELECT usuario,contrasena FROM usuarios WHERE usuario IN('director.demo','asistencia.demo','caja.demo','planilla.demo','docente.demo','apoderado.demo')")->fetchAll();
$clavesValidas = count($usuariosDemo) === 6;
foreach ($usuariosDemo as $usuario) $clavesValidas = $clavesValidas && password_verify('Demo2026*', $usuario['contrasena']);
comprobarDemo($clavesValidas, 'Seis usuarios demo y clave comun validos', $ok, $errores);

$periodoActivo = (int)$conexion->query("SELECT COUNT(*) FROM periodos_academicos WHERE estado='ACTIVO'")->fetchColumn();
comprobarDemo($periodoActivo === 1, 'Un unico periodo academico activo', $ok, $errores);
$aulaLlena = (int)$conexion->query("SELECT COUNT(*) FROM secciones s WHERE s.capacidad IS NOT NULL AND s.capacidad=(SELECT COUNT(*) FROM matriculas m JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico WHERE m.id_seccion=s.id_seccion AND m.estado='ACTIVO' AND pa.estado='ACTIVO')")->fetchColumn();
comprobarDemo($aulaLlena >= 1, 'Existe una seccion al limite de su capacidad', $ok, $errores);
$modalidades = (int)$conexion->query("SELECT COUNT(DISTINCT modalidad_pago) FROM vinculos_laborales WHERE estado='ACTIVO'")->fetchColumn();
comprobarDemo($modalidades === 4, 'Planilla cubre mensual, hora, sesion y mixto', $ok, $errores);

$estadosAlumno = (int)$conexion->query("SELECT COUNT(DISTINCT estado) FROM asistencias_diarias WHERE id_matricula IS NOT NULL")->fetchColumn();
$estadosPersonal = (int)$conexion->query("SELECT COUNT(DISTINCT estado) FROM asistencias_diarias WHERE id_matricula IS NULL AND (id_jornada IS NOT NULL OR id_carga IS NOT NULL)")->fetchColumn();
comprobarDemo($estadosAlumno >= 7, 'Asistencia de alumnos contiene estados variados', $ok, $errores);
comprobarDemo($estadosPersonal >= 7, 'Asistencia de personal contiene estados variados', $ok, $errores);
$validacionesMarcacion = (int)$conexion->query("SELECT COUNT(DISTINCT estado_validacion) FROM marcaciones_asistencia")->fetchColumn();
comprobarDemo($validacionesMarcacion === 3, 'Marcaciones validas, duplicadas y anuladas', $ok, $errores);
$estadosIncidencia = (int)$conexion->query("SELECT COUNT(DISTINCT estado) FROM incidencias_asistencia")->fetchColumn();
comprobarDemo($estadosIncidencia === 3, 'Incidencias pendientes, aprobadas y rechazadas', $ok, $errores);
$resolucionesTrazables = (int)$conexion->query("SELECT COUNT(*) FROM incidencias_asistencia WHERE estado<>'PENDIENTE' AND (resuelto_por IS NULL OR resuelto_en IS NULL OR resolucion_observacion IS NULL OR TRIM(resolucion_observacion)='')")->fetchColumn();
comprobarDemo($resolucionesTrazables === 0, 'Incidencias resueltas con responsable, fecha y observacion', $ok, $errores);
$permisoDirector = (int)$conexion->query("SELECT COUNT(*) FROM rol_permiso rp JOIN roles r ON r.id_rol=rp.id_rol JOIN permisos p ON p.id_permiso=rp.id_permiso WHERE r.nombre='Director' AND p.clave='incidencias.gestionar'")->fetchColumn();
comprobarDemo($permisoDirector === 1, 'Direccion puede autorizar permisos y licencias', $ok, $errores);

$cuentasInconsistentes = (int)$conexion->query("SELECT COUNT(*) FROM cuentas_cobrar cc LEFT JOIN (SELECT d.id_cuenta,SUM(d.importe) pagado FROM pagos_alumno_detalles d JOIN pagos_alumno p ON p.id_pago=d.id_pago AND p.estado='REGISTRADO' GROUP BY d.id_cuenta) x ON x.id_cuenta=cc.id_cuenta WHERE ABS((cc.importe_original+cc.recargo-cc.descuento)-COALESCE(x.pagado,0)-cc.saldo)>0.01")->fetchColumn();
comprobarDemo($cuentasInconsistentes === 0, 'Saldos de cuentas conciliados con pagos vigentes', $ok, $errores);
$recibosInconsistentes = (int)$conexion->query("SELECT COUNT(*) FROM pagos_alumno p LEFT JOIN (SELECT id_pago,SUM(importe) total FROM pagos_alumno_detalles GROUP BY id_pago) d ON d.id_pago=p.id_pago WHERE ABS(p.importe_total-COALESCE(d.total,0))>0.01")->fetchColumn();
comprobarDemo($recibosInconsistentes === 0, 'Importes de recibos conciliados con sus deudas', $ok, $errores);
$estadosCuenta = (int)$conexion->query("SELECT COUNT(DISTINCT estado) FROM cuentas_cobrar")->fetchColumn();
comprobarDemo($estadosCuenta === 5, 'Cuentas pendientes, parciales, pagadas, vencidas y anuladas', $ok, $errores);
$turnosMismoDia = (int)$conexion->query("SELECT MAX(cantidad) FROM (SELECT fecha,COUNT(*) cantidad FROM aperturas_caja GROUP BY fecha) x")->fetchColumn();
comprobarDemo($turnosMismoDia >= 2, 'Caja permite varios turnos el mismo dia', $ok, $errores);
$cajasAbiertas = (int)$conexion->query("SELECT COUNT(*) FROM aperturas_caja WHERE estado='ABIERTA'")->fetchColumn();
comprobarDemo($cajasAbiertas === 1, 'Existe exactamente una caja abierta para pruebas', $ok, $errores);
$cierresInconsistentes = (int)$conexion->query("SELECT COUNT(*) FROM cierres_caja c LEFT JOIN (SELECT id_apertura,SUM(CASE WHEN estado='REGISTRADO' AND medio_pago='EFECTIVO' THEN importe_total ELSE 0 END) efectivo,SUM(CASE WHEN estado='REGISTRADO' AND medio_pago<>'EFECTIVO' THEN importe_total ELSE 0 END) otros FROM pagos_alumno GROUP BY id_apertura) p ON p.id_apertura=c.id_apertura WHERE ABS(c.efectivo_sistema-COALESCE(p.efectivo,0))>0.01 OR ABS(c.otros_sistema-COALESCE(p.otros,0))>0.01 OR ABS(c.diferencia-((c.efectivo_declarado+c.otros_declarado)-(c.efectivo_sistema+c.otros_sistema)))>0.01")->fetchColumn();
comprobarDemo($cierresInconsistentes === 0, 'Cierres de caja conciliados por turno', $ok, $errores);

$saldosPlanilla = (int)$conexion->query("SELECT COUNT(*) FROM planilla_detalles WHERE ABS(monto_pagar-monto_pagado-saldo_pendiente)>0.01")->fetchColumn();
$pagosPlanilla = (int)$conexion->query("SELECT COUNT(*) FROM planilla_detalles pd LEFT JOIN (SELECT id_planilla_detalle,SUM(importe) total FROM pagos_planilla WHERE estado='REGISTRADO' GROUP BY id_planilla_detalle) pg ON pg.id_planilla_detalle=pd.id_planilla_detalle WHERE ABS(pd.monto_pagado-COALESCE(pg.total,0))>0.01")->fetchColumn();
$periodosPagadosDeuda = (int)$conexion->query("SELECT COUNT(*) FROM periodos_pago pp WHERE pp.estado='PAGADO' AND EXISTS(SELECT 1 FROM planilla_detalles pd WHERE pd.id_periodo_pago=pp.id_periodo_pago AND pd.saldo_pendiente>0.01)")->fetchColumn();
$cierresSinResponsable = (int)$conexion->query("SELECT COUNT(*) FROM periodos_pago WHERE estado IN('CERRADO','PAGADO') AND (cerrado_por IS NULL OR cerrado_en IS NULL)")->fetchColumn();
comprobarDemo($saldosPlanilla === 0, 'Saldos de planilla consistentes', $ok, $errores);
comprobarDemo($pagosPlanilla === 0, 'Pagos de planilla conciliados', $ok, $errores);
comprobarDemo($periodosPagadosDeuda === 0, 'Periodos pagados sin deuda', $ok, $errores);
comprobarDemo($cierresSinResponsable === 0, 'Cierres de planilla con responsable y fecha', $ok, $errores);

$qrActivos = $conexion->query("SELECT archivo_qr FROM codigos_qr WHERE estado='ACTIVO'")->fetchAll(PDO::FETCH_COLUMN);
$qrArchivos = count($qrActivos) >= 10;
foreach ($qrActivos as $archivo) $qrArchivos = $qrArchivos && is_file(__DIR__ . '/../storage/qr/' . $archivo);
comprobarDemo($qrArchivos, 'QR activos con archivos PNG disponibles', $ok, $errores);

foreach ($ok as $mensaje) echo "[OK] {$mensaje}\n";
foreach ($errores as $mensaje) fwrite(STDERR, "[ERROR] {$mensaje}\n");
echo "Resultado: " . count($ok) . " comprobaciones correctas, " . count($errores) . " errores.\n";
exit($errores ? 1 : 0);
