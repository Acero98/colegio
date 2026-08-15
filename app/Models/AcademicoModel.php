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
            'secciones' => $this->db->query('SELECT s.*, g.nombre AS grado, n.nombre AS nivel FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel ORDER BY n.orden,g.orden,s.nombre')->fetchAll(),
        ];
    }

    public function crear(string $entidad, array $data): void
    {
        $consultas = [
            'periodo' => ['INSERT INTO periodos_academicos(nombre,fecha_inicio,fecha_fin,estado) VALUES(:nombre,:inicio,:fin,:estado)', [
                ':nombre' => $data['nombre'], ':inicio' => $data['fecha_inicio'], ':fin' => $data['fecha_fin'], ':estado' => $data['estado']
            ]],
            'nivel' => ['INSERT INTO niveles(nombre,orden,estado) VALUES(:nombre,:orden,"ACTIVO")', [
                ':nombre' => $data['nombre'], ':orden' => $data['orden']
            ]],
            'grado' => ['INSERT INTO grados(id_nivel,nombre,orden,estado) VALUES(:nivel,:nombre,:orden,"ACTIVO")', [
                ':nivel' => $data['id_nivel'], ':nombre' => $data['nombre'], ':orden' => $data['orden']
            ]],
            'seccion' => ['INSERT INTO secciones(id_grado,nombre,capacidad,estado) VALUES(:grado,:nombre,:capacidad,"ACTIVO")', [
                ':grado' => $data['id_grado'], ':nombre' => $data['nombre'], ':capacidad' => $data['capacidad'] ?: null
            ]],
        ];
        if (!isset($consultas[$entidad])) {
            throw new InvalidArgumentException('Registro académico no válido.');
        }
        [$sql, $parametros] = $consultas[$entidad];
        $this->db->prepare($sql)->execute($parametros);
    }
}
