<?php

class AuditoriaService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function registrar(
        string $accion,
        string $entidad,
        ?int $idEntidad = null,
        ?array $antes = null,
        ?array $despues = null
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO auditoria
            (id_usuario, accion, entidad, id_entidad, valores_antes, valores_despues, ip, creado_en)
            VALUES (:usuario, :accion, :entidad, :idEntidad, :antes, :despues, :ip, NOW())'
        );
        $stmt->execute([
            ':usuario' => $_SESSION['usuario']['id_usuario'] ?? null,
            ':accion' => $accion,
            ':entidad' => $entidad,
            ':idEntidad' => $idEntidad,
            ':antes' => $antes ? json_encode($antes, JSON_UNESCAPED_UNICODE) : null,
            ':despues' => $despues ? json_encode($despues, JSON_UNESCAPED_UNICODE) : null,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
