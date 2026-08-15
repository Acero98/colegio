<?php

class UsuarioModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function buscarParaLogin(string $usuario): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id_usuario, u.id_persona, u.usuario, u.contrasena, u.estado,
                    p.nombres, p.apellidos, r.id_rol, r.nombre
             FROM usuarios u
             LEFT JOIN personas p ON p.id_persona = u.id_persona
             INNER JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
             INNER JOIN roles r ON r.id_rol = ur.id_rol AND r.estado = "ACTIVO"
             WHERE u.usuario = :usuario AND u.estado = "ACTIVO"
             ORDER BY r.id_rol LIMIT 1'
        );
        $stmt->execute([':usuario' => $usuario]);
        return $stmt->fetch() ?: null;
    }

    public function permisos(int $idUsuario): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT p.clave
             FROM usuario_rol ur
             INNER JOIN rol_permiso rp ON rp.id_rol = ur.id_rol
             INNER JOIN permisos p ON p.id_permiso = rp.id_permiso AND p.estado = "ACTIVO"
             WHERE ur.id_usuario = :id'
        );
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
