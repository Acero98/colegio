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

    public function listarGestion(): array
    {
        return $this->db->query("SELECT u.id_usuario,u.usuario,u.estado,CONCAT(COALESCE(p.apellidos,''),CASE WHEN p.id_persona IS NULL THEN '' ELSE ', ' END,COALESCE(p.nombres,'')) persona,GROUP_CONCAT(r.nombre SEPARATOR ', ') roles FROM usuarios u LEFT JOIN personas p ON p.id_persona=u.id_persona LEFT JOIN usuario_rol ur ON ur.id_usuario=u.id_usuario LEFT JOIN roles r ON r.id_rol=ur.id_rol GROUP BY u.id_usuario ORDER BY u.usuario")->fetchAll();
    }
    public function opcionesGestion(): array
    {
        return ['personas'=>$this->db->query("SELECT p.id_persona,p.nombres,p.apellidos,p.numero_documento FROM personas p LEFT JOIN usuarios u ON u.id_persona=p.id_persona WHERE p.estado='ACTIVO' AND u.id_usuario IS NULL ORDER BY p.apellidos,p.nombres")->fetchAll(),'roles'=>$this->db->query("SELECT id_rol,nombre FROM roles WHERE estado='ACTIVO' ORDER BY nombre")->fetchAll()];
    }
    public function crear(array $d): int
    {
        if(trim($d['usuario']??'')===''||strlen((string)($d['contrasena']??''))<8||empty($d['id_rol']))throw new RuntimeException('Complete usuario, rol y una contraseña de al menos 8 caracteres.');
        $this->db->beginTransaction();try{$s=$this->db->prepare("INSERT INTO usuarios(id_persona,usuario,contrasena,estado) VALUES(?,?,?,'ACTIVO')");$s->execute([$d['id_persona']!==''?(int)$d['id_persona']:null,trim($d['usuario']),password_hash($d['contrasena'],PASSWORD_DEFAULT)]);$id=(int)$this->db->lastInsertId();$this->db->prepare('INSERT INTO usuario_rol(id_usuario,id_rol) VALUES(?,?)')->execute([$id,(int)$d['id_rol']]);$this->db->commit();return $id;}catch(Throwable $e){$this->db->rollBack();throw $e;}
    }
}
