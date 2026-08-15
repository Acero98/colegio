<?php

class FotocheckModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function listarAlumnos(): array
    {
        return $this->db->query("SELECT p.id_persona, p.numero_documento, p.nombres, p.apellidos, p.fotografia, m.codigo_alumno, CONCAT(n.nombre, ' · ',g.nombre,' ',s.nombre) AS seccion, q.id_qr, q.emitido_en FROM matriculas m INNER JOIN personas p ON p.id_persona=m.id_persona INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel LEFT JOIN codigos_qr q ON q.id_persona=p.id_persona AND q.estado='ACTIVO' WHERE m.estado='ACTIVO' ORDER BY p.apellidos,p.nombres")->fetchAll();
    }

    public function obtenerAlumno(int $idPersona): ?array
    {
        $stmt = $this->db->prepare("SELECT p.*, m.codigo_alumno, CONCAT(n.nombre, ' · ',g.nombre,' ',s.nombre) AS seccion, q.id_qr FROM matriculas m INNER JOIN personas p ON p.id_persona=m.id_persona INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel LEFT JOIN codigos_qr q ON q.id_persona=p.id_persona AND q.estado='ACTIVO' WHERE p.id_persona=:persona AND m.estado='ACTIVO' LIMIT 1");
        $stmt->execute([':persona' => $idPersona]);
        return $stmt->fetch() ?: null;
    }

    public function qr(int $idQr): ?array
    {
        $stmt = $this->db->prepare("SELECT archivo_qr FROM codigos_qr WHERE id_qr=:qr AND estado='ACTIVO' LIMIT 1");
        $stmt->execute([':qr' => $idQr]);
        return $stmt->fetch() ?: null;
    }
}
