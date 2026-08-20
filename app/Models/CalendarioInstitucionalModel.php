<?php

class CalendarioInstitucionalModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function datos(int $anio): array
    {
        $consulta = $this->db->prepare(
            "SELECT ci.*,
                    COALESCE(NULLIF(CONCAT(COALESCE(p.apellidos,''), ', ', COALESCE(p.nombres,'')), ', '), u.usuario, 'Sistema') AS responsable
             FROM calendario_institucional ci
             LEFT JOIN usuarios u ON u.id_usuario = COALESCE(ci.actualizado_por, ci.creado_por)
             LEFT JOIN personas p ON p.id_persona = u.id_persona
             WHERE YEAR(ci.fecha) = ?
             ORDER BY ci.fecha, FIELD(ci.tipo, 'FERIADO', 'NO_LABORABLE', 'SUSPENSION')"
        );
        $consulta->execute([$anio]);
        $registros = $consulta->fetchAll();

        $anios = array_map('intval', $this->db->query(
            'SELECT DISTINCT YEAR(fecha) AS anio FROM calendario_institucional ORDER BY anio DESC'
        )->fetchAll(PDO::FETCH_COLUMN));
        if (!in_array($anio, $anios, true)) {
            $anios[] = $anio;
            rsort($anios);
        }

        $resumen = ['total' => 0, 'FERIADO' => 0, 'NO_LABORABLE' => 0, 'SUSPENSION' => 0];
        foreach ($registros as $registro) {
            if ($registro['estado'] !== 'ACTIVO') {
                continue;
            }
            $resumen['total']++;
            $resumen[$registro['tipo']]++;
        }

        return compact('registros', 'anios', 'anio', 'resumen');
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->db->prepare('SELECT * FROM calendario_institucional WHERE id_calendario = ?');
        $consulta->execute([$id]);
        $registro = $consulta->fetch();
        return $registro ?: null;
    }

    public function guardar(array $datos, int $usuario): int
    {
        list($fecha, $tipo, $descripcion) = $this->validar($datos);
        try {
            $consulta = $this->db->prepare(
                "INSERT INTO calendario_institucional(fecha, tipo, descripcion, estado, creado_por)
                 VALUES(?, ?, ?, 'ACTIVO', ?)"
            );
            $consulta->execute([$fecha, $tipo, $descripcion, $usuario ?: null]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DomainException('Ya existe un registro del mismo tipo para esa fecha. Puede editarlo o reactivarlo.');
            }
            throw $e;
        }
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos, int $usuario): void
    {
        if (!$id || !$this->buscar($id)) {
            throw new InvalidArgumentException('Registro de calendario no encontrado.');
        }
        list($fecha, $tipo, $descripcion) = $this->validar($datos);
        try {
            $consulta = $this->db->prepare(
                'UPDATE calendario_institucional
                 SET fecha = ?, tipo = ?, descripcion = ?, actualizado_por = ?, actualizado_en = NOW()
                 WHERE id_calendario = ?'
            );
            $consulta->execute([$fecha, $tipo, $descripcion, $usuario ?: null, $id]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DomainException('Ya existe otro registro del mismo tipo para esa fecha.');
            }
            throw $e;
        }
    }

    public function cambiarEstado(int $id, string $estado, int $usuario): void
    {
        if (!$id || !in_array($estado, ['ACTIVO', 'INACTIVO'], true) || !$this->buscar($id)) {
            throw new InvalidArgumentException('Solicitud de calendario no válida.');
        }
        $consulta = $this->db->prepare(
            'UPDATE calendario_institucional
             SET estado = ?, actualizado_por = ?, actualizado_en = NOW()
             WHERE id_calendario = ?'
        );
        $consulta->execute([$estado, $usuario ?: null, $id]);
    }

    private function validar(array $datos): array
    {
        $fecha = trim($datos['fecha'] ?? '');
        $tipo = $datos['tipo'] ?? '';
        $descripcion = trim($datos['descripcion'] ?? '');
        $objetoFecha = DateTime::createFromFormat('Y-m-d', $fecha);

        if (!$objetoFecha || $objetoFecha->format('Y-m-d') !== $fecha) {
            throw new InvalidArgumentException('Seleccione una fecha válida.');
        }
        if (!in_array($tipo, ['FERIADO', 'NO_LABORABLE', 'SUSPENSION'], true)) {
            throw new InvalidArgumentException('Seleccione un tipo válido.');
        }
        if (strlen($descripcion) < 3 || strlen($descripcion) > 200) {
            throw new InvalidArgumentException('La descripción debe tener entre 3 y 200 caracteres.');
        }

        return [$fecha, $tipo, $descripcion];
    }
}
