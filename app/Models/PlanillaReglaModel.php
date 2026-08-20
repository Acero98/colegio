<?php
class PlanillaReglaModel
{
    private $db;
    function __construct(PDO $db)
    {
        $this->db = $db;
    }
    function listar(): array
    {
        return $this->db->query("SELECT * FROM reglas_descuento ORDER BY FIELD(estado,'ACTIVO','INACTIVO'),fecha_inicio DESC,nombre")->fetchAll();
    }
    function guardar(array $d): void
    {
        $n = trim($d['nombre'] ?? '');
        $nat = $d['naturaleza'] ?? '';
        $e = $d['evento'] ?? '';
        $m = round((float)($d['monto_unitario'] ?? 0), 2);
        $i = $d['fecha_inicio'] ?? '';
        if (!$n || !in_array($nat, ['DESCUENTO', 'BONIFICACION'], true) || !in_array($e, ['TARDANZA', 'FALTA', 'FIJO'], true) || $m <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $i)) throw new InvalidArgumentException('Complete correctamente la regla.');
        if ($nat === 'BONIFICACION') $e = 'FIJO';
        $this->db->prepare("INSERT INTO reglas_descuento(nombre,naturaleza,evento,formula,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,?,?,?,'ACTIVO')")->execute([$n, $nat, $e, json_encode(['monto_unitario' => $m]), $i, $d['fecha_fin'] ?: null]);
    }
    function estado(int $id, string $e): void
    {
        if (!$id || !in_array($e, ['ACTIVO', 'INACTIVO'], true)) throw new InvalidArgumentException('Regla no válida.');
        $this->db->prepare('UPDATE reglas_descuento SET estado=? WHERE id_regla=?')->execute([$e, $id]);
    }
}
