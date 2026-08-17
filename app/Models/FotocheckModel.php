<?php
class FotocheckModel
{
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }
    public function listarPersonas(): array
    {
        return $this->db->query("SELECT p.id_persona,p.numero_documento,p.codigo_trabajador,p.nombres,p.apellidos,p.fotografia,COALESCE(MAX(m.codigo_alumno),p.codigo_trabajador,p.numero_documento) codigo_alumno,COALESCE(MAX(CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre)),(SELECT COALESCE(c.nombre,v.tipo) FROM vinculos_laborales v LEFT JOIN cargos c ON c.id_cargo=v.id_cargo WHERE v.id_persona=p.id_persona AND v.estado='ACTIVO' ORDER BY v.fecha_inicio DESC LIMIT 1),'Personal') seccion,CASE WHEN MAX(m.id_matricula) IS NOT NULL THEN 'ALUMNO' WHEN SUM(pp.tipo='DOCENTE')>0 THEN 'DOCENTE' ELSE 'ADMINISTRATIVO' END tipo_fotocheck,q.id_qr,q.emitido_en FROM personas p LEFT JOIN persona_perfiles pp ON pp.id_persona=p.id_persona AND pp.estado='ACTIVO' LEFT JOIN matriculas m ON m.id_persona=p.id_persona AND m.estado='ACTIVO' AND EXISTS(SELECT 1 FROM periodos_academicos periodo_m WHERE periodo_m.id_periodo_academico=m.id_periodo_academico AND periodo_m.estado='ACTIVO') LEFT JOIN secciones s ON s.id_seccion=m.id_seccion LEFT JOIN grados g ON g.id_grado=s.id_grado LEFT JOIN niveles n ON n.id_nivel=g.id_nivel LEFT JOIN codigos_qr q ON q.id_persona=p.id_persona AND q.estado='ACTIVO' WHERE p.estado='ACTIVO' AND (pp.tipo IN('DOCENTE','ADMINISTRATIVO') OR m.id_matricula IS NOT NULL) GROUP BY p.id_persona,q.id_qr,q.emitido_en ORDER BY p.apellidos,p.nombres")->fetchAll();
    }
    public function obtenerPersona(int $idPersona): ?array
    {
        $stmt = $this->db->prepare("SELECT p.*,COALESCE(MAX(m.codigo_alumno),p.codigo_trabajador,p.numero_documento) codigo_alumno,COALESCE(MAX(CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre)),(SELECT COALESCE(c.nombre,v.tipo) FROM vinculos_laborales v LEFT JOIN cargos c ON c.id_cargo=v.id_cargo WHERE v.id_persona=p.id_persona AND v.estado='ACTIVO' ORDER BY v.fecha_inicio DESC LIMIT 1),'Personal') seccion,CASE WHEN MAX(m.id_matricula) IS NOT NULL THEN 'ALUMNO' WHEN SUM(pp.tipo='DOCENTE')>0 THEN 'DOCENTE' ELSE 'ADMINISTRATIVO' END tipo_fotocheck,q.id_qr FROM personas p LEFT JOIN persona_perfiles pp ON pp.id_persona=p.id_persona AND pp.estado='ACTIVO' LEFT JOIN matriculas m ON m.id_persona=p.id_persona AND m.estado='ACTIVO' AND EXISTS(SELECT 1 FROM periodos_academicos periodo_m WHERE periodo_m.id_periodo_academico=m.id_periodo_academico AND periodo_m.estado='ACTIVO') LEFT JOIN secciones s ON s.id_seccion=m.id_seccion LEFT JOIN grados g ON g.id_grado=s.id_grado LEFT JOIN niveles n ON n.id_nivel=g.id_nivel LEFT JOIN codigos_qr q ON q.id_persona=p.id_persona AND q.estado='ACTIVO' WHERE p.id_persona=:persona AND p.estado='ACTIVO' AND (pp.tipo IN('DOCENTE','ADMINISTRATIVO') OR m.id_matricula IS NOT NULL) GROUP BY p.id_persona,q.id_qr LIMIT 1");
        $stmt->execute([':persona' => $idPersona]); return $stmt->fetch() ?: null;
    }
    public function qr(int $idQr): ?array { $stmt = $this->db->prepare("SELECT archivo_qr FROM codigos_qr WHERE id_qr=:qr AND estado='ACTIVO' LIMIT 1"); $stmt->execute([':qr' => $idQr]); return $stmt->fetch() ?: null; }
}
