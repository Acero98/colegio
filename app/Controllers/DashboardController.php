<?php
require_once __DIR__ . '/../../config/conexion.php';
class DashboardController {
public function index():void{
global $conexion;$kpis=[];
foreach(['alumnos'=>'ALUMNO','docentes'=>'DOCENTE','administrativos'=>'ADMINISTRATIVO','apoderados'=>'APODERADO'] as $k=>$t){$s=$conexion->prepare("SELECT COUNT(*) FROM persona_perfiles WHERE tipo=? AND estado='ACTIVO'");$s->execute([$t]);$kpis[$k]=(int)$s->fetchColumn();}
$kpis['asistencias_hoy']=(int)$conexion->query("SELECT COUNT(*) FROM asistencias_diarias WHERE fecha=CURDATE()")->fetchColumn();
$kpis['tardanzas_hoy']=(int)$conexion->query("SELECT COUNT(*) FROM asistencias_diarias WHERE fecha=CURDATE() AND estado='TARDANZA'")->fetchColumn();
$kpis['cobrado_mes']=(float)$conexion->query("SELECT COALESCE(SUM(importe_total),0) FROM pagos_alumno WHERE estado='REGISTRADO' AND DATE_FORMAT(fecha_pago,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();
$kpis['pendiente_mes']=(float)$conexion->query("SELECT COALESCE(SUM(saldo),0) FROM cuentas_cobrar WHERE periodo_cobro=DATE_FORMAT(CURDATE(),'%Y-%m-01') AND estado IN('PENDIENTE','PARCIAL','VENCIDA')")->fetchColumn();
$tendencia=$conexion->query("SELECT fecha,COUNT(*) total,SUM(estado='TARDANZA') tardanzas FROM asistencias_diarias WHERE fecha>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY fecha ORDER BY fecha")->fetchAll();
require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/dashboard/index.php';require __DIR__.'/../Views/layouts/footer.php';
}}
