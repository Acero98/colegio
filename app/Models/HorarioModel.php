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
            'asignaciones' => $this->db->query("SELECT ah.*,CONCAT(p.apellidos,', ',p.nombres) persona,j.nombre jornada FROM asignaciones_horario ah INNER JOIN personas p ON p.id_persona=ah.id_persona INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada WHERE ah.estado='ACTIVO' ORDER BY p.apellidos,p.nombres")->fetchAll(),
            'cargas' => $this->db->query("SELECT c.*,CONCAT(p.apellidos,', ',p.nombres) docente,a.nombre asignatura,s.nombre seccion FROM carga_academica c INNER JOIN vinculos_laborales v ON v.id_vinculo=c.id_vinculo INNER JOIN personas p ON p.id_persona=v.id_persona INNER JOIN asignaturas a ON a.id_asignatura=c.id_asignatura INNER JOIN secciones s ON s.id_seccion=c.id_seccion WHERE c.estado='ACTIVO' ORDER BY c.dia_semana,c.hora_inicio")->fetchAll(),
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
        if($entidad==='sesion'){
            $this->db->prepare("INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,tolerancia_minutos,estado) VALUES(:vinculo,:periodo,:seccion,:asignatura,:dia,:inicio,:fin,:tolerancia,'ACTIVO')")->execute([':vinculo'=>$d['id_vinculo'],':periodo'=>$d['id_periodo_academico'],':seccion'=>$d['id_seccion'],':asignatura'=>$d['id_asignatura'],':dia'=>$d['dia_semana'],':inicio'=>$d['hora_inicio'],':fin'=>$d['hora_fin'],':tolerancia'=>$d['tolerancia_minutos']]); return;
        }
        throw new InvalidArgumentException('Tipo de horario no válido.');
    }
}
