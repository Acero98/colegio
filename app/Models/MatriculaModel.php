<?php

class MatriculaModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function opciones(): array
    {
        $personas = new PersonaModel($this->db);
        return [
            'periodos' => $this->db->query("SELECT * FROM periodos_academicos WHERE estado='ACTIVO' ORDER BY fecha_inicio DESC")->fetchAll(),
            'secciones' => $this->db->query("SELECT s.id_seccion, CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS nombre FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE s.estado='ACTIVO' ORDER BY n.orden,g.orden,s.nombre")->fetchAll(),
            'alumnos' => $personas->listar('ALUMNO'),
            'apoderados' => $personas->listar('APODERADO'),
        ];
    }

    public function listar(): array
    {
        return $this->db->query("SELECT m.*, CONCAT(p.apellidos, ', ', p.nombres) AS alumno, pa.nombre AS periodo, CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS seccion, GROUP_CONCAT(DISTINCT CONCAT(ap.apellidos, ', ', ap.nombres) SEPARATOR ' · ') AS apoderados FROM matriculas m INNER JOIN personas p ON p.id_persona=m.id_persona INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel LEFT JOIN alumno_apoderado aa ON aa.id_alumno=m.id_persona AND aa.estado='ACTIVO' LEFT JOIN personas ap ON ap.id_persona=aa.id_apoderado GROUP BY m.id_matricula ORDER BY pa.fecha_inicio DESC,p.apellidos,p.nombres")->fetchAll();
    }

    public function crear(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $codigo = trim($data['codigo_alumno']);
            if ($codigo === '') {
                $anio = date('Y', strtotime($data['fecha_matricula']));
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM matriculas WHERE YEAR(fecha_matricula)=:anio');
                $stmt->execute([':anio' => $anio]);
                $codigo = 'A-' . $anio . '-' . str_pad((string) ((int) $stmt->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
            }
            $stmt = $this->db->prepare('INSERT INTO matriculas(id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado) VALUES(:alumno,:periodo,:seccion,:codigo,:fecha,"ACTIVO")');
            $stmt->execute([
                ':alumno' => $data['id_alumno'], ':periodo' => $data['id_periodo'], ':seccion' => $data['id_seccion'],
                ':codigo' => $codigo, ':fecha' => $data['fecha_matricula']
            ]);
            $idMatricula = (int) $this->db->lastInsertId();
            if (!empty($data['id_apoderado'])) {
                $relacion = $this->db->prepare("INSERT INTO alumno_apoderado(id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,estado) VALUES(:alumno,:apoderado,:parentesco,TRUE,TRUE,'ACTIVO') ON DUPLICATE KEY UPDATE parentesco=VALUES(parentesco),es_principal=TRUE,responsable_economico=TRUE,estado='ACTIVO'");
                $relacion->execute([':alumno' => $data['id_alumno'], ':apoderado' => $data['id_apoderado'], ':parentesco' => $data['parentesco']]);
            }
            $this->db->commit();
            return $idMatricula;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
