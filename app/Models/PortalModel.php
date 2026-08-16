<?php
class PortalModel {
    private $db;
    public function __construct(PDO $db){$this->db=$db;}
    public function hijos(int $id):array {
        $q="SELECT p.id_persona,CONCAT(p.nombres,' ',p.apellidos) alumno,m.codigo_alumno,CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) seccion FROM alumno_apoderado aa JOIN personas p ON p.id_persona=aa.id_alumno LEFT JOIN matriculas m ON m.id_persona=p.id_persona AND m.estado='ACTIVO' LEFT JOIN secciones s ON s.id_seccion=m.id_seccion LEFT JOIN grados g ON g.id_grado=s.id_grado LEFT JOIN niveles n ON n.id_nivel=g.id_nivel WHERE aa.id_apoderado=? AND aa.estado='ACTIVO' ORDER BY p.apellidos,p.nombres";
        $s=$this->db->prepare($q);$s->execute([$id]);return $s->fetchAll();
    }
    public function asistencias(int $id):array {
        $q="SELECT a.*,CONCAT(p.nombres,' ',p.apellidos) alumno FROM asistencias_diarias a JOIN personas p ON p.id_persona=a.id_persona JOIN alumno_apoderado aa ON aa.id_alumno=p.id_persona AND aa.id_apoderado=? AND aa.estado='ACTIVO' WHERE a.fecha>=DATE_SUB(CURDATE(),INTERVAL 30 DAY) ORDER BY a.fecha DESC,p.apellidos LIMIT 50";
        $s=$this->db->prepare($q);$s->execute([$id]);return $s->fetchAll();
    }
    public function deudas(int $id):array {
        $q="SELECT cc.*,c.nombre concepto,CONCAT(p.nombres,' ',p.apellidos) alumno FROM cuentas_cobrar cc JOIN matriculas m ON m.id_matricula=cc.id_matricula JOIN personas p ON p.id_persona=m.id_persona JOIN conceptos_cobro c ON c.id_concepto_cobro=cc.id_concepto_cobro JOIN alumno_apoderado aa ON aa.id_alumno=m.id_persona AND aa.id_apoderado=? AND aa.estado='ACTIVO' WHERE cc.estado IN('PENDIENTE','PARCIAL','VENCIDA') ORDER BY cc.fecha_vencimiento,p.apellidos";
        $s=$this->db->prepare($q);$s->execute([$id]);return $s->fetchAll();
    }
}
