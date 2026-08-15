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
                (tipo_documento, numero_documento, nombres, apellidos, telefono, correo, estado, creado_en)
                VALUES (:tipoDocumento, :numeroDocumento, :nombres, :apellidos, :telefono, :correo, "ACTIVO", NOW())'
            );
            $stmt->execute([
                ':tipoDocumento' => $data['tipo_documento'],
                ':numeroDocumento' => $data['numero_documento'],
                ':nombres' => $data['nombres'],
                ':apellidos' => $data['apellidos'],
                ':telefono' => $data['telefono'] ?: null,
                ':correo' => $data['correo'] ?: null,
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
}
