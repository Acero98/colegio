<?php
// Ejecutar únicamente por CLI: php db/sembrar_datos_prueba.php --confirmar
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/sembrar_datos_prueba.php --confirmar\n");
}
require_once __DIR__ . '/../config/conexion.php';

function uno(PDO $db, string $sql, array $params = []) {
    $s = $db->prepare($sql); $s->execute($params); return $s->fetchColumn();
}
function personaDemo(PDO $db, string $doc, string $nombres, string $apellidos, ?string $codigo = null): int {
    $id = uno($db, 'SELECT id_persona FROM personas WHERE numero_documento=:doc', [':doc'=>$doc]);
    if ($id) return (int)$id;
    $s=$db->prepare("INSERT INTO personas(tipo_documento,numero_documento,nombres,apellidos,telefono,correo,codigo_trabajador,estado) VALUES('DNI',:doc,:n,:a,'999000000',:correo,:codigo,'ACTIVO')");
    $s->execute([':doc'=>$doc,':n'=>$nombres,':a'=>$apellidos,':correo'=>'demo.'.$doc.'@colegio.test',':codigo'=>$codigo]);
    return (int)$db->lastInsertId();
}
function perfilDemo(PDO $db, int $persona, string $tipo): void {
    $s=$db->prepare("INSERT INTO persona_perfiles(id_persona,tipo,estado) VALUES(:persona,:tipo,'ACTIVO') ON DUPLICATE KEY UPDATE estado='ACTIVO'");
    $s->execute([':persona'=>$persona,':tipo'=>$tipo]);
}

$db=$conexion; $db->beginTransaction();
try {
    $periodo=uno($db,"SELECT id_periodo_academico FROM periodos_academicos WHERE estado='ACTIVO' ORDER BY fecha_inicio DESC LIMIT 1");
    $seccion=uno($db,"SELECT id_seccion FROM secciones WHERE estado='ACTIVO' ORDER BY id_seccion LIMIT 1");
    if (!$periodo || !$seccion) throw new RuntimeException('Primero configure un período académico y una sección activos.');
    $grado=uno($db,'SELECT id_grado FROM secciones WHERE id_seccion=:id',[':id'=>$seccion]);

    $alumno=personaDemo($db,'90000001','Valentina','Demostración'); perfilDemo($db,$alumno,'ALUMNO');
    $apoderado=personaDemo($db,'90000002','María','Apoderada Demo'); perfilDemo($db,$apoderado,'APODERADO');
    $docente=personaDemo($db,'90000003','Carlos','Docente Demo','CS-2026-9001'); perfilDemo($db,$docente,'DOCENTE');
    $administrativo=personaDemo($db,'90000004','Lucía','Administrativa Demo','CS-2026-9002'); perfilDemo($db,$administrativo,'ADMINISTRATIVO');

    $s=$db->prepare("INSERT INTO matriculas(id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado) VALUES(:persona,:periodo,:seccion,'DEMO-2026-001',CURDATE(),'ACTIVO') ON DUPLICATE KEY UPDATE id_seccion=VALUES(id_seccion),estado='ACTIVO'");
    $s->execute([':persona'=>$alumno,':periodo'=>$periodo,':seccion'=>$seccion]);
    $matricula=(int)uno($db,'SELECT id_matricula FROM matriculas WHERE id_persona=:persona AND id_periodo_academico=:periodo',[':persona'=>$alumno,':periodo'=>$periodo]);
    $s=$db->prepare("INSERT INTO alumno_apoderado(id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,contacto_emergencia,estado) VALUES(:alumno,:apoderado,'Madre',1,1,1,'ACTIVO') ON DUPLICATE KEY UPDATE estado='ACTIVO',responsable_economico=1");
    $s->execute([':alumno'=>$alumno,':apoderado'=>$apoderado]);

    $area=uno($db,"SELECT id_area FROM areas WHERE nombre='DEMO · Académica'"); if(!$area){$db->exec("INSERT INTO areas(nombre,estado) VALUES('DEMO · Académica','ACTIVO')");$area=(int)$db->lastInsertId();}
    $cargo=uno($db,"SELECT id_cargo FROM cargos WHERE nombre='Docente demo'"); if(!$cargo){$s=$db->prepare("INSERT INTO cargos(id_area,nombre,estado) VALUES(:area,'Docente demo','ACTIVO')");$s->execute([':area'=>$area]);$cargo=(int)$db->lastInsertId();}
    $s=$db->prepare("INSERT INTO vinculos_laborales(id_persona,id_area,id_cargo,tipo,modalidad_pago,monto_base,fecha_inicio,estado) SELECT :persona,:area,:cargo,:tipo,'MENSUAL',1800,CURDATE(),'ACTIVO' WHERE NOT EXISTS(SELECT 1 FROM vinculos_laborales WHERE id_persona=:persona2 AND tipo=:tipo2 AND estado='ACTIVO')");
    foreach ([[$docente,'DOCENTE'],[$administrativo,'ADMINISTRATIVO']] as $fila) $s->execute([':persona'=>$fila[0],':area'=>$area,':cargo'=>$cargo,':tipo'=>$fila[1],':persona2'=>$fila[0],':tipo2'=>$fila[1]]);

    $jornada=uno($db,"SELECT id_jornada FROM jornadas WHERE nombre='DEMO · Jornada personal'"); if(!$jornada){$db->exec("INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,estado) VALUES('DEMO · Jornada personal','DOCENTE','08:00','16:00',10,1,'ACTIVO')");$jornada=(int)$db->lastInsertId();}
    foreach ([$docente,$administrativo] as $persona) { $s=$db->prepare("INSERT INTO asignaciones_horario(id_persona,id_jornada,fecha_inicio,estado) SELECT :persona,:jornada,CURDATE(),'ACTIVO' WHERE NOT EXISTS(SELECT 1 FROM asignaciones_horario WHERE id_persona=:persona2 AND id_jornada=:jornada2 AND estado='ACTIVO')");$s->execute([':persona'=>$persona,':jornada'=>$jornada,':persona2'=>$persona,':jornada2'=>$jornada]); }
    $asignatura=uno($db,"SELECT id_asignatura FROM asignaturas WHERE nombre='DEMO · Tutoría'"); if(!$asignatura){$db->exec("INSERT INTO asignaturas(nombre,estado) VALUES('DEMO · Tutoría','ACTIVO')");$asignatura=(int)$db->lastInsertId();}
    $vinculo=(int)uno($db,"SELECT id_vinculo FROM vinculos_laborales WHERE id_persona=:persona AND tipo='DOCENTE' AND estado='ACTIVO' ORDER BY id_vinculo DESC LIMIT 1",[':persona'=>$docente]);
    $s=$db->prepare("INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,estado) SELECT :v,:p,:s,:a,1,'08:00','09:00','ACTIVO' WHERE NOT EXISTS(SELECT 1 FROM carga_academica WHERE id_vinculo=:v2 AND id_periodo_academico=:p2 AND id_seccion=:s2 AND id_asignatura=:a2 AND estado='ACTIVO')");$s->execute([':v'=>$vinculo,':p'=>$periodo,':s'=>$seccion,':a'=>$asignatura,':v2'=>$vinculo,':p2'=>$periodo,':s2'=>$seccion,':a2'=>$asignatura]);

    $conceptos=[['DEMO · Mensualidad','MENSUALIDAD',1,350],['DEMO · Taller de ciencia','TALLER',0,80],['DEMO · Material educativo','CUOTA',1,120]];
    foreach($conceptos as $c){$id=uno($db,'SELECT id_concepto_cobro FROM conceptos_cobro WHERE nombre=:n',[':n'=>$c[0]]);if(!$id){$s=$db->prepare("INSERT INTO conceptos_cobro(nombre,tipo,admite_parcial,estado) VALUES(:n,:t,:p,'ACTIVO')");$s->execute([':n'=>$c[0],':t'=>$c[1],':p'=>$c[2]]);$id=(int)$db->lastInsertId();}$existe=uno($db,'SELECT id_tarifa FROM tarifas_cobro WHERE id_concepto_cobro=:c AND id_periodo_academico=:p AND id_grado=:g',[':c'=>$id,':p'=>$periodo,':g'=>$grado]);if(!$existe){$s=$db->prepare('INSERT INTO tarifas_cobro(id_concepto_cobro,id_periodo_academico,id_grado,importe,vigencia_inicio) VALUES(:c,:p,:g,:i,CURDATE())');$s->execute([':c'=>$id,':p'=>$periodo,':g'=>$grado,':i'=>$c[3]]);}}
    $mensualidad=(int)uno($db,"SELECT id_concepto_cobro FROM conceptos_cobro WHERE nombre='DEMO · Mensualidad'"); $taller=(int)uno($db,"SELECT id_concepto_cobro FROM conceptos_cobro WHERE nombre='DEMO · Taller de ciencia'");
    $mes=date('Y-m-01'); $s=$db->prepare("INSERT INTO cuentas_cobrar(id_matricula,id_concepto_cobro,periodo_cobro,fecha_vencimiento,importe_original,descuento,saldo,estado) VALUES(:m,:c,:mes,DATE_SUB(CURDATE(),INTERVAL 6 DAY),350,0,175,'PARCIAL') ON DUPLICATE KEY UPDATE id_cuenta=id_cuenta");$s->execute([':m'=>$matricula,':c'=>$mensualidad,':mes'=>$mes]);
    $s=$db->prepare("INSERT INTO cuentas_cobrar(id_matricula,id_concepto_cobro,periodo_cobro,fecha_vencimiento,importe_original,descuento,saldo,estado) VALUES(:m,:c,:mes,DATE_ADD(CURDATE(),INTERVAL 8 DAY),80,0,80,'PENDIENTE') ON DUPLICATE KEY UPDATE id_cuenta=id_cuenta");$s->execute([':m'=>$matricula,':c'=>$taller,':mes'=>$mes]);
    $s=$db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_matricula,hora_entrada,hora_salida,minutos_trabajados,estado,observacion) SELECT :persona,DATE_SUB(CURDATE(),INTERVAL 2 DAY),:matricula,'08:03','13:00',297,'PUNTUAL','DEMO · registro para pruebas' WHERE NOT EXISTS(SELECT 1 FROM asistencias_diarias WHERE id_matricula=:matricula2 AND fecha=DATE_SUB(CURDATE(),INTERVAL 2 DAY))");$s->execute([':persona'=>$alumno,':matricula'=>$matricula,':matricula2'=>$matricula]);
    $db->commit();
    echo "Datos demo listos: personas, matrícula, estructura/carga académica, horarios, asistencia, conceptos, tarifas y cuentas de cobro.\n";
} catch (Throwable $e) { if($db->inTransaction())$db->rollBack(); fwrite(STDERR,"Error: ".$e->getMessage()."\n"); exit(1); }
