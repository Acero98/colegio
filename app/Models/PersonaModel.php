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
        $sql = 'SELECT p.id_persona, p.tipo_documento, p.numero_documento, p.nombres,
                       p.apellidos, p.telefono, p.correo, p.estado,
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

    public function actualizar(array $data, array $perfiles): void
    {
        $id=(int)$data['id_persona'];if($id<1||!$perfiles)throw new RuntimeException('Complete la persona y al menos un perfil.');
        $this->db->beginTransaction();
        try {
            $stmt=$this->db->prepare('UPDATE personas SET tipo_documento=?,numero_documento=?,nombres=?,apellidos=?,telefono=?,correo=?,estado=? WHERE id_persona=?');
            $stmt->execute([$data['tipo_documento'],$data['numero_documento'],$data['nombres'],$data['apellidos'],$data['telefono']?:null,$data['correo']?:null,$data['estado'],$id]);
            $this->db->prepare('UPDATE persona_perfiles SET estado="INACTIVO" WHERE id_persona=?')->execute([$id]);
            foreach(array_unique($perfiles) as $tipo){$this->db->prepare('INSERT INTO persona_perfiles(id_persona,tipo,estado,creado_en) VALUES(? ,?,"ACTIVO",NOW()) ON DUPLICATE KEY UPDATE estado="ACTIVO"')->execute([$id,$tipo]);}
            $this->db->commit();
        } catch(Throwable $e){$this->db->rollBack();throw $e;}
    }
}
