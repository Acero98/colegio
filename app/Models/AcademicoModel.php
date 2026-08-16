<?php

class AcademicoModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function datos(): array
    {
        return [
            'periodos' => $this->db->query('SELECT * FROM periodos_academicos ORDER BY fecha_inicio DESC')->fetchAll(),
            'niveles' => $this->db->query('SELECT * FROM niveles ORDER BY orden, nombre')->fetchAll(),
            'grados' => $this->db->query('SELECT g.*, n.nombre AS nivel FROM grados g INNER JOIN niveles n ON n.id_nivel=g.id_nivel ORDER BY n.orden,g.orden')->fetchAll(),
            'secciones' => $this->db->query("SELECT s.*, g.nombre AS grado, n.nombre AS nivel,
                (SELECT COUNT(*) FROM matriculas m INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico AND pa.estado='ACTIVO' WHERE m.id_seccion=s.id_seccion AND m.estado='ACTIVO') AS matriculados
                FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel
                ORDER BY n.orden,g.orden,s.nombre")->fetchAll(),
        ];
    }

    public function crear(string $entidad, array $data): void
    {
        $consultas = [
            'periodo' => ['INSERT INTO periodos_academicos(nombre,fecha_inicio,fecha_fin,estado) VALUES(:nombre,:inicio,:fin,:estado)', [':nombre' => $data['nombre'], ':inicio' => $data['fecha_inicio'], ':fin' => $data['fecha_fin'], ':estado' => $data['estado']]],
            'nivel' => ['INSERT INTO niveles(nombre,orden,estado) VALUES(:nombre,:orden,"ACTIVO")', [':nombre' => $data['nombre'], ':orden' => $data['orden']]],
            'grado' => ['INSERT INTO grados(id_nivel,nombre,orden,estado) VALUES(:nivel,:nombre,:orden,"ACTIVO")', [':nivel' => $data['id_nivel'], ':nombre' => $data['nombre'], ':orden' => $data['orden']]],
            'seccion' => ['INSERT INTO secciones(id_grado,nombre,capacidad,estado) VALUES(:grado,:nombre,:capacidad,"ACTIVO")', [':grado' => $data['id_grado'], ':nombre' => $data['nombre'], ':capacidad' => $data['capacidad'] ?: null]],
        ];
        if (!isset($consultas[$entidad])) throw new InvalidArgumentException('Registro académico no válido.');
        [$sql, $parametros] = $consultas[$entidad];
        $this->db->prepare($sql)->execute($parametros);
    }

    public function actualizar(string $entidad, array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        if ($id < 1) throw new InvalidArgumentException('Registro no válido.');
        $consultas = [
            'periodo' => ['UPDATE periodos_academicos SET nombre=?,fecha_inicio=?,fecha_fin=?,estado=? WHERE id_periodo_academico=?', [$data['nombre'], $data['fecha_inicio'], $data['fecha_fin'], $data['estado'], $id]],
            'nivel' => ['UPDATE niveles SET nombre=?,orden=? WHERE id_nivel=?', [$data['nombre'], $data['orden'], $id]],
            'grado' => ['UPDATE grados SET id_nivel=?,nombre=?,orden=? WHERE id_grado=?', [$data['id_nivel'], $data['nombre'], $data['orden'], $id]],
            'seccion' => ['UPDATE secciones SET id_grado=?,nombre=?,capacidad=? WHERE id_seccion=?', [$data['id_grado'], $data['nombre'], $data['capacidad'] ?: null, $id]],
        ];
        if (!isset($consultas[$entidad])) throw new InvalidArgumentException('Registro académico no válido.');
        [$sql, $valores] = $consultas[$entidad];
        $this->db->prepare($sql)->execute($valores);
    }

    public function cambiarEstado(string $entidad, int $id, string $estado): void
    {
        $mapa = [
            'nivel' => ['niveles', 'id_nivel'],
            'grado' => ['grados', 'id_grado'],
            'seccion' => ['secciones', 'id_seccion'],
        ];
        if ($id < 1 || !isset($mapa[$entidad]) || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) throw new InvalidArgumentException('Estado no válido.');
        [$tabla, $campo] = $mapa[$entidad];
        $stmt = $this->db->prepare("UPDATE {$tabla} SET estado=? WHERE {$campo}=?");
        $stmt->execute([$estado, $id]);
        if ($stmt->rowCount() === 0) throw new RuntimeException('Registro no encontrado.');
    }
}
