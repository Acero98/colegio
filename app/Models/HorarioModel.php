<?php

class HorarioModel
{
    private $db;
    public function __construct(PDO $db){ $this->db=$db; }
    public function datos(): array
    {
        return [
            'personas' => $this->db->query("SELECT p.id_persona,CONCAT(p.apellidos,', ',p.nombres) nombre,GROUP_CONCAT(pp.tipo SEPARATOR ', ') perfiles FROM personas p INNER JOIN persona_perfiles pp ON pp.id_persona=p.id_persona AND pp.estado='ACTIVO' WHERE p.estado='ACTIVO' GROUP BY p.id_persona ORDER BY p.apellidos,p.nombres")->fetchAll(),
            'jornadas' => $this->db->query("SELECT * FROM jornadas WHERE estado='ACTIVO' ORDER BY aplica_a,nombre")->fetchAll(),
            'periodos' => $this->db->query("SELECT * FROM periodos_academicos WHERE estado='ACTIVO' ORDER BY fecha_inicio DESC")->fetchAll(),
            'secciones' => $this->db->query("SELECT s.id_seccion,CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) nombre FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE s.estado='ACTIVO' ORDER BY n.orden,g.orden,s.nombre")->fetchAll(),
            'docentes' => $this->db->query("SELECT v.id_vinculo,CONCAT(p.apellidos,', ',p.nombres) nombre FROM vinculos_laborales v INNER JOIN personas p ON p.id_persona=v.id_persona WHERE v.tipo='DOCENTE' AND v.estado='ACTIVO' ORDER BY p.apellidos,p.nombres")->fetchAll(),
            'asignaturas' => $this->db->query("SELECT * FROM asignaturas WHERE estado='ACTIVO' ORDER BY nombre")->fetchAll(),
            'asignaciones' => $this->db->query("SELECT ah.*,CONCAT(p.apellidos,', ',p.nombres) persona,j.nombre jornada FROM asignaciones_horario ah INNER JOIN personas p ON p.id_persona=ah.id_persona INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada ORDER BY FIELD(ah.estado,'ACTIVO','INACTIVO'),p.apellidos,p.nombres,ah.fecha_inicio DESC")->fetchAll(),
            'cargas' => $this->db->query("SELECT c.*,CONCAT(p.apellidos,', ',p.nombres) docente,a.nombre asignatura,CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) seccion FROM carga_academica c INNER JOIN vinculos_laborales v ON v.id_vinculo=c.id_vinculo INNER JOIN personas p ON p.id_persona=v.id_persona INNER JOIN asignaturas a ON a.id_asignatura=c.id_asignatura INNER JOIN secciones s ON s.id_seccion=c.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel ORDER BY FIELD(c.estado,'ACTIVO','INACTIVO'),c.dia_semana,c.hora_inicio")->fetchAll(),
        ];
    }
    public function guardar(string $entidad,array $d): void
    {
        if($entidad==='asignatura'){
            $this->db->prepare("INSERT INTO asignaturas(nombre,estado) VALUES(:nombre,'ACTIVO')")->execute([':nombre'=>trim($d['nombre'])]); return;
        }
        if($entidad==='jornada_persona'){
            $this->db->prepare("INSERT INTO asignaciones_horario(id_persona,id_jornada,fecha_inicio,fecha_fin,estado) VALUES(:persona,:jornada,:inicio,:fin,'ACTIVO')")->execute([':persona'=>$d['id_persona'],':jornada'=>$d['id_jornada'],':inicio'=>$d['fecha_inicio'],':fin'=>$d['fecha_fin']?:null]); return;
        }
        if($entidad==='jornada_grupo'){
            $personas=array_values(array_unique(array_map('intval',$d['personas']??[]))); if(!$personas)throw new InvalidArgumentException('Seleccione al menos una persona.');
            $this->db->beginTransaction(); try{$desactivar=$this->db->prepare("UPDATE asignaciones_horario SET estado='INACTIVO',fecha_fin=COALESCE(fecha_fin,?) WHERE id_persona=? AND estado='ACTIVO'");$insertar=$this->db->prepare("INSERT INTO asignaciones_horario(id_persona,id_jornada,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,?, 'ACTIVO')");foreach($personas as $persona){$desactivar->execute([$d['fecha_inicio'],$persona]);$insertar->execute([$persona,$d['id_jornada'],$d['fecha_inicio'],$d['fecha_fin']?:null]);}$this->db->commit();return;}catch(Throwable $e){$this->db->rollBack();throw $e;}
        }
        if($entidad==='jornada_actualizar'){
            $id=(int)($d['id_asignacion']??0); if(!$id)throw new InvalidArgumentException('Asignación no válida.');
            $this->db->prepare("UPDATE asignaciones_horario SET id_jornada=?,fecha_inicio=?,fecha_fin=? WHERE id_asignacion=? AND estado='ACTIVO'")->execute([$d['id_jornada'],$d['fecha_inicio'],$d['fecha_fin']?:null,$id]); return;
        }
        if($entidad==='jornada_desactivar'){
            $id=(int)($d['id_asignacion']??0); if(!$id)throw new InvalidArgumentException('Asignación no válida.');
            $this->db->prepare("UPDATE asignaciones_horario SET estado='INACTIVO',fecha_fin=COALESCE(fecha_fin,CURDATE()) WHERE id_asignacion=? AND estado='ACTIVO'")->execute([$id]); return;
        }
        if($entidad==='jornada_activar'){
            $id=(int)($d['id_asignacion']??0); if(!$id)throw new InvalidArgumentException('AsignaciÃ³n no vÃ¡lida.');
            $persona=$this->db->prepare("SELECT id_persona FROM asignaciones_horario WHERE id_asignacion=? AND estado='INACTIVO'");$persona->execute([$id]);$idPersona=(int)$persona->fetchColumn();if(!$idPersona)throw new InvalidArgumentException('AsignaciÃ³n no disponible.');
            $this->db->beginTransaction();try{$this->db->prepare("UPDATE asignaciones_horario SET estado='INACTIVO',fecha_fin=COALESCE(fecha_fin,CURDATE()) WHERE id_persona=? AND estado='ACTIVO'")->execute([$idPersona]);$this->db->prepare("UPDATE asignaciones_horario SET estado='ACTIVO',fecha_fin=NULL WHERE id_asignacion=?")->execute([$id]);$this->db->commit();return;}catch(Throwable $e){$this->db->rollBack();throw $e;}
        }
        if($entidad==='sesion'){
            $this->db->prepare("INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,tolerancia_minutos,estado) VALUES(:vinculo,:periodo,:seccion,:asignatura,:dia,:inicio,:fin,:tolerancia,'ACTIVO')")->execute([':vinculo'=>$d['id_vinculo'],':periodo'=>$d['id_periodo_academico'],':seccion'=>$d['id_seccion'],':asignatura'=>$d['id_asignatura'],':dia'=>$d['dia_semana'],':inicio'=>$d['hora_inicio'],':fin'=>$d['hora_fin'],':tolerancia'=>$d['tolerancia_minutos']]); return;
        }
        if($entidad==='sesion_actualizar'){
            $id=(int)($d['id_carga']??0); if(!$id)throw new InvalidArgumentException('SesiÃ³n no vÃ¡lida.');
            $this->db->prepare("UPDATE carga_academica SET id_vinculo=?,id_periodo_academico=?,id_seccion=?,id_asignatura=?,dia_semana=?,hora_inicio=?,hora_fin=?,tolerancia_minutos=? WHERE id_carga=? AND estado='ACTIVO'")->execute([$d['id_vinculo'],$d['id_periodo_academico'],$d['id_seccion'],$d['id_asignatura'],$d['dia_semana'],$d['hora_inicio'],$d['hora_fin'],$d['tolerancia_minutos'],$id]); return;
        }
        if($entidad==='sesion_desactivar'){
            $id=(int)($d['id_carga']??0); if(!$id)throw new InvalidArgumentException('SesiÃ³n no vÃ¡lida.');
            $this->db->prepare("UPDATE carga_academica SET estado='INACTIVO' WHERE id_carga=? AND estado='ACTIVO'")->execute([$id]); return;
        }
        if($entidad==='sesion_activar'){
            $id=(int)($d['id_carga']??0); if(!$id)throw new InvalidArgumentException('SesiÃ³n no vÃ¡lida.');
            $this->db->prepare("UPDATE carga_academica SET estado='ACTIVO' WHERE id_carga=? AND estado='INACTIVO'")->execute([$id]); return;
        }
        throw new InvalidArgumentException('Tipo de horario no válido.');
    }
}
