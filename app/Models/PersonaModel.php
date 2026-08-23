<?php

class PersonaModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function listar(?string $tipo = null): array
    {
        $sql = 'SELECT p.id_persona, p.tipo_documento, p.numero_documento, p.codigo_trabajador, p.nombres,
                       p.apellidos, p.telefono, p.correo, p.estado,
                       (SELECT m.codigo_alumno FROM matriculas m
                        INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico
                        WHERE m.id_persona=p.id_persona AND m.estado="ACTIVO" AND pa.estado="ACTIVO"
                        ORDER BY m.fecha_matricula DESC,m.id_matricula DESC LIMIT 1) AS codigo_alumno,
                       (SELECT CONCAT(n.nombre," · ",g.nombre," ",s.nombre) FROM matriculas m
                        INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico
                        INNER JOIN secciones s ON s.id_seccion=m.id_seccion
                        INNER JOIN grados g ON g.id_grado=s.id_grado
                        INNER JOIN niveles n ON n.id_nivel=g.id_nivel
                        WHERE m.id_persona=p.id_persona AND m.estado="ACTIVO" AND pa.estado="ACTIVO"
                        ORDER BY m.fecha_matricula DESC,m.id_matricula DESC LIMIT 1) AS ubicacion_academica,
                       (SELECT CONCAT(ap.apellidos,", ",ap.nombres) FROM alumno_apoderado aa
                        INNER JOIN personas ap ON ap.id_persona=aa.id_apoderado
                        WHERE aa.id_alumno=p.id_persona AND aa.estado="ACTIVO"
                        ORDER BY aa.es_principal DESC,aa.responsable_economico DESC,aa.id_alumno_apoderado LIMIT 1) AS apoderado_principal,
                       (SELECT aa.parentesco FROM alumno_apoderado aa
                        WHERE aa.id_alumno=p.id_persona AND aa.estado="ACTIVO"
                        ORDER BY aa.es_principal DESC,aa.responsable_economico DESC,aa.id_alumno_apoderado LIMIT 1) AS apoderado_parentesco,
                       (SELECT ap.telefono FROM alumno_apoderado aa INNER JOIN personas ap ON ap.id_persona=aa.id_apoderado
                        WHERE aa.id_alumno=p.id_persona AND aa.estado="ACTIVO"
                        ORDER BY aa.es_principal DESC,aa.responsable_economico DESC,aa.id_alumno_apoderado LIMIT 1) AS apoderado_telefono,
                       (SELECT GREATEST(COUNT(*)-1,0) FROM alumno_apoderado aa
                        WHERE aa.id_alumno=p.id_persona AND aa.estado="ACTIVO") AS apoderados_adicionales,
                       (SELECT GROUP_CONCAT(DISTINCT COALESCE(c.nombre, v.tipo) ORDER BY c.nombre SEPARATOR ", ")
                        FROM vinculos_laborales v LEFT JOIN cargos c ON c.id_cargo=v.id_cargo
                        WHERE v.id_persona=p.id_persona AND v.estado="ACTIVO") AS cargo,
                       GROUP_CONCAT(pp.tipo ORDER BY pp.tipo SEPARATOR ", ") AS perfiles
                FROM personas p
                LEFT JOIN persona_perfiles pp ON pp.id_persona = p.id_persona
                    AND pp.estado = "ACTIVO"';
        $parametros = [];
        if ($tipo) {
            $sql .= ' WHERE EXISTS (
                SELECT 1 FROM persona_perfiles filtro
                WHERE filtro.id_persona = p.id_persona
                AND filtro.tipo = :tipo AND filtro.estado = "ACTIVO"
            )';
            $parametros[':tipo'] = $tipo;
        }
        $sql .= ' GROUP BY p.id_persona ORDER BY p.apellidos, p.nombres';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function crear(array $data, array $perfiles): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO personas
                (tipo_documento, numero_documento, nombres, apellidos, telefono, correo, fotografia, estado, creado_en)
                VALUES (:tipoDocumento, :numeroDocumento, :nombres, :apellidos, :telefono, :correo, :fotografia, "ACTIVO", NOW())'
            );
            $stmt->execute([
                ':tipoDocumento' => $data['tipo_documento'],
                ':numeroDocumento' => $data['numero_documento'],
                ':nombres' => $data['nombres'],
                ':apellidos' => $data['apellidos'],
                ':telefono' => $data['telefono'] ?: null,
                ':correo' => $data['correo'] ?: null,
                ':fotografia' => $data['fotografia'] ?: null,
            ]);
            $idPersona = (int) $this->db->lastInsertId();
            $perfilStmt = $this->db->prepare(
                'INSERT INTO persona_perfiles (id_persona, tipo, estado, creado_en)
                 VALUES (:persona, :tipo, "ACTIVO", NOW())'
            );
            foreach (array_unique($perfiles) as $perfil) {
                $perfilStmt->execute([':persona' => $idPersona, ':tipo' => $perfil]);
            }
            $this->db->commit();
            return $idPersona;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function documentoExiste(string $tipo, string $numero): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM personas WHERE tipo_documento = :tipo AND numero_documento = :numero LIMIT 1'
        );
        $stmt->execute([':tipo' => $tipo, ':numero' => $numero]);
        return (bool) $stmt->fetchColumn();
    }

    public function foto(int $idPersona): ?string
    {
        $stmt = $this->db->prepare('SELECT fotografia FROM personas WHERE id_persona=:id LIMIT 1');
        $stmt->execute([':id' => $idPersona]);
        $foto = $stmt->fetchColumn();
        return $foto ?: null;
    }

    public function actualizar(array $data, array $perfiles, ?string $fotografia = null): void
    {
        $id=(int)$data['id_persona'];if($id<1||!$perfiles)throw new RuntimeException('Complete la persona y al menos un perfil.');
        $this->db->beginTransaction();
        try {
            $sql = 'UPDATE personas SET tipo_documento=?,numero_documento=?,nombres=?,apellidos=?,telefono=?,correo=?';
            $valores = [$data['tipo_documento'], $data['numero_documento'], $data['nombres'], $data['apellidos'], $data['telefono'] ?: null, $data['correo'] ?: null];
            if ($fotografia !== null) {
                $sql .= ', fotografia=?';
                $valores[] = $fotografia;
            }
            $sql .= ' WHERE id_persona=?';
            $valores[] = $id;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($valores);
            $this->db->prepare('UPDATE persona_perfiles SET estado="INACTIVO" WHERE id_persona=?')->execute([$id]);
            foreach(array_unique($perfiles) as $tipo){$this->db->prepare('INSERT INTO persona_perfiles(id_persona,tipo,estado,creado_en) VALUES(? ,?,"ACTIVO",NOW()) ON DUPLICATE KEY UPDATE estado="ACTIVO"')->execute([$id,$tipo]);}
            $this->db->commit();
        } catch(Throwable $e){$this->db->rollBack();throw $e;}
    }
    public function cambiarEstado(int $idPersona, string $estado): void
    {
        $stmt = $this->db->prepare('UPDATE personas SET estado = ? WHERE id_persona = ?');
        $stmt->execute([$estado, $idPersona]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Persona no encontrada.');
        }
    }
}
