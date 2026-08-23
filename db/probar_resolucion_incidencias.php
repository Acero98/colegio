<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../app/Models/AsistenciaModel.php';

if (PHP_SAPI !== 'cli') exit("Prueba disponible solo por consola.\n");

function asegurarIncidencia(bool $condicion, string $mensaje): void
{
    if (!$condicion) throw new RuntimeException($mensaje);
}

$usuarios = $conexion->query("SELECT r.nombre rol,MIN(u.id_usuario) id_usuario FROM usuarios u JOIN usuario_rol ur ON ur.id_usuario=u.id_usuario JOIN roles r ON r.id_rol=ur.id_rol WHERE r.nombre IN('Director','Responsable de asistencia') GROUP BY r.nombre")->fetchAll(PDO::FETCH_KEY_PAIR);
asegurarIncidencia(isset($usuarios['Director'],$usuarios['Responsable de asistencia']), 'Faltan usuarios demo para Dirección o Asistencia.');
$model = new AsistenciaModel($conexion);

$conexion->beginTransaction();
try {
    $matricula=$conexion->query("SELECT m.id_matricula,m.id_persona FROM matriculas m WHERE m.estado='ACTIVO' ORDER BY m.id_matricula LIMIT 1")->fetch();
    asegurarIncidencia((bool)$matricula, 'Falta una matrícula activa para preparar la prueba.');
    $crearAsistencia=$conexion->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_matricula,estado,observacion,calculado_en) VALUES(?,CURDATE(),?,'FALTA','Registro temporal de prueba',NOW())");
    $crearAsistencia->execute([$matricula['id_persona'],$matricula['id_matricula']]);
    $idAsistenciaPrueba=(int)$conexion->lastInsertId();
    $opciones = $model->asistenciasParaIncidencia();
    foreach ($opciones as $opcion) asegurarIncidencia(!in_array($opcion['estado'],['PUNTUAL','ANULADO','JUSTIFICADO','PERMISO','LICENCIA'],true), 'La lista incluyó una asistencia que no admite incidencias.');
    $idsOpciones = array_map(static function(array $fila): int { return (int)$fila['id_asistencia']; }, $opciones);
    asegurarIncidencia(in_array($idAsistenciaPrueba,$idsOpciones,true), 'La lista no incluyó la asistencia temporal elegible.');
    $bloqueadasVisibles = (int)$conexion->query("SELECT COUNT(*) FROM asistencias_diarias a WHERE a.id_asistencia IN(".implode(',',$idsOpciones).") AND a.id_matricula IS NULL AND EXISTS(SELECT 1 FROM periodos_pago pp WHERE a.fecha BETWEEN pp.fecha_inicio AND pp.fecha_fin AND pp.estado IN('CERRADO','PAGADO'))")->fetchColumn();
    asegurarIncidencia($bloqueadasVisibles===0, 'La lista incluyó personal de una planilla cerrada o pagada.');
    $resueltasVisibles = (int)$conexion->query("SELECT COUNT(*) FROM incidencias_asistencia i WHERE i.id_asistencia IN(".implode(',',$idsOpciones).") AND i.estado IN('PENDIENTE','APROBADA') AND i.tipo IN('JUSTIFICACION_FALTA','JUSTIFICACION_TARDANZA','PERMISO','LICENCIA')")->fetchColumn();
    asegurarIncidencia($resueltasVisibles===0, 'La lista incluyó una asistencia que ya tiene una incidencia pendiente o aprobada.');
    $baseNormal = $opciones[0]??null;
    asegurarIncidencia((bool)$baseNormal, 'Falta una asistencia elegible para probar una incidencia.');
    $model->crearIncidencia(['id_asistencia'=>$baseNormal['id_asistencia'],'tipo'=>'OBSERVACION','motivo'=>'Observación creada en prueba'],(int)$usuarios['Responsable de asistencia']);
    $pendiente=['id_incidencia'=>(int)$conexion->lastInsertId(),'id_asistencia'=>(int)$baseNormal['id_asistencia']];
    try {
        $model->resolverIncidencia((int)$pendiente['id_incidencia'],'APROBADA','Intento de Dirección',(int)$usuarios['Director'],'Director');
        throw new RuntimeException('Dirección resolvió una justificación que no le correspondía.');
    } catch (DomainException $e) {
        asegurarIncidencia(strpos($e->getMessage(),'solo autoriza')!==false, 'La restricción de Dirección devolvió un mensaje inesperado.');
    }
    $resultado = $model->resolverIncidencia((int)$pendiente['id_incidencia'],'APROBADA','Sustento verificado en prueba',(int)$usuarios['Responsable de asistencia'],'Responsable de asistencia');
    asegurarIncidencia($resultado['decision']==='APROBADA', 'No se aprobó la justificación.');
    $estado = $conexion->query('SELECT estado FROM incidencias_asistencia WHERE id_incidencia='.(int)$pendiente['id_incidencia'])->fetchColumn();
    asegurarIncidencia($estado==='APROBADA', 'La incidencia no conservó el estado aprobado.');

    $opcionesPermiso=$model->asistenciasParaIncidencia();
    $asistencia=(int)($opcionesPermiso[0]['id_asistencia']??0);
    asegurarIncidencia($asistencia>0, 'Falta una asistencia elegible para probar permisos.');
    $model->crearIncidencia(['id_asistencia'=>$asistencia,'tipo'=>'PERMISO','motivo'=>'Permiso institucional de prueba'],(int)$usuarios['Responsable de asistencia']);
    $idPermiso=(int)$conexion->lastInsertId();
    try {
        $model->resolverIncidencia($idPermiso,'APROBADA','Intento de Asistencia',(int)$usuarios['Responsable de asistencia'],'Responsable de asistencia');
        throw new RuntimeException('Asistencia autorizó un permiso reservado a Dirección.');
    } catch (DomainException $e) {
        asegurarIncidencia(strpos($e->getMessage(),'Dirección')!==false, 'La restricción del permiso devolvió un mensaje inesperado.');
    }
    $model->resolverIncidencia($idPermiso,'APROBADA','Permiso autorizado en prueba',(int)$usuarios['Director'],'Director');
    asegurarIncidencia($conexion->query("SELECT estado FROM asistencias_diarias WHERE id_asistencia={$asistencia}")->fetchColumn()==='PERMISO', 'El permiso aprobado no actualizó la asistencia.');
    $idsDespues=array_map(static function(array $fila): int { return (int)$fila['id_asistencia']; },$model->asistenciasParaIncidencia());
    asegurarIncidencia(!in_array($asistencia,$idsDespues,true), 'La asistencia siguió visible después de aprobarse el permiso.');
    $conexion->rollBack();
    echo "OK: aprobación, rechazo de autoridad incorrecta, trazabilidad y efecto en asistencia verificados.\n";
} catch (Throwable $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    fwrite(STDERR, "ERROR: {$e->getMessage()}\n");
    exit(1);
}
