<?php

class CobroModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function opciones(): array
    {
        return [
            'periodos' => $this->db->query("SELECT * FROM periodos_academicos WHERE estado='ACTIVO' ORDER BY fecha_inicio DESC")->fetchAll(),
            'conceptos' => $this->db->query("SELECT * FROM conceptos_cobro WHERE estado='ACTIVO' ORDER BY tipo,nombre")->fetchAll(),
            'tarifas' => $this->db->query("SELECT t.*,c.nombre AS concepto,g.nombre AS grado,p.nombre AS periodo FROM tarifas_cobro t INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=t.id_concepto_cobro INNER JOIN periodos_academicos p ON p.id_periodo_academico=t.id_periodo_academico LEFT JOIN grados g ON g.id_grado=t.id_grado ORDER BY p.fecha_inicio DESC,c.nombre,g.orden")->fetchAll()
        ];
    }

    public function listarCuentas(string $mes = ''): array
    {
        $sql = "SELECT cc.*, c.nombre AS concepto, c.tipo, m.codigo_alumno, CONCAT(al.apellidos, ', ', al.nombres) AS alumno,
                CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS seccion,
                (SELECT CONCAT(ap.apellidos, ', ', ap.nombres) FROM alumno_apoderado aa INNER JOIN personas ap ON ap.id_persona=aa.id_apoderado WHERE aa.id_alumno=m.id_persona AND aa.estado='ACTIVO' ORDER BY aa.responsable_economico DESC,aa.es_principal DESC LIMIT 1) AS apoderado
                FROM cuentas_cobrar cc
                INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=cc.id_concepto_cobro
                INNER JOIN matriculas m ON m.id_matricula=cc.id_matricula
                INNER JOIN personas al ON al.id_persona=m.id_persona
                INNER JOIN secciones s ON s.id_seccion=m.id_seccion
                INNER JOIN grados g ON g.id_grado=s.id_grado
                INNER JOIN niveles n ON n.id_nivel=g.id_nivel";
        $params = [];
        if ($mes !== '') {
            $sql .= ' WHERE cc.periodo_cobro=:mes';
            $params[':mes'] = $mes . '-01';
        }
        $sql .= ' ORDER BY cc.estado IN (\'PENDIENTE\',\'PARCIAL\',\'VENCIDA\') DESC, cc.fecha_vencimiento, al.apellidos, al.nombres';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function resumen(string $mes): array
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cuentas, COALESCE(SUM(importe_original-descuento),0) AS facturado, COALESCE(SUM(saldo),0) AS pendiente, COALESCE(SUM(importe_original-descuento-saldo),0) AS cobrado FROM cuentas_cobrar WHERE periodo_cobro=:mes");
        $stmt->execute([':mes' => $mes . '-01']);
        return $stmt->fetch() ?: [];
    }

    public function guardarConfiguracion(array $data): void
    {
        if (($data['accion'] ?? '') === 'concepto') {
            $stmt = $this->db->prepare("INSERT INTO conceptos_cobro(nombre,tipo,admite_parcial,estado) VALUES(:nombre,:tipo,:parcial,'ACTIVO')");
            $stmt->execute([':nombre' => trim($data['nombre']), ':tipo' => $data['tipo'], ':parcial' => !empty($data['admite_parcial']) ? 1 : 0]);
            return;
        }
        $stmt = $this->db->prepare('INSERT INTO tarifas_cobro(id_concepto_cobro,id_periodo_academico,id_grado,importe,vigencia_inicio,vigencia_fin) VALUES(:concepto,:periodo,:grado,:importe,:inicio,:fin)');
        $stmt->execute([
            ':concepto' => $data['id_concepto_cobro'], ':periodo' => $data['id_periodo_academico'],
            ':grado' => $data['id_grado'] !== '' ? $data['id_grado'] : null, ':importe' => $data['importe'],
            ':inicio' => $data['vigencia_inicio'], ':fin' => $data['vigencia_fin'] ?: null
        ]);
    }

    public function grados(): array
    {
        return $this->db->query("SELECT g.id_grado,CONCAT(n.nombre,' · ',g.nombre) AS nombre FROM grados g INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE g.estado='ACTIVO' ORDER BY n.orden,g.orden")->fetchAll();
    }

    public function generarMensualidades(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $periodoCobro = $data['mes'] . '-01';
            $matriculas = $this->db->prepare("SELECT m.id_matricula,g.id_grado FROM matriculas m INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado WHERE m.id_periodo_academico=:periodo AND m.estado='ACTIVO'");
            $matriculas->execute([':periodo' => $data['id_periodo_academico']]);
            $tarifa = $this->db->prepare("SELECT importe FROM tarifas_cobro WHERE id_concepto_cobro=:concepto AND id_periodo_academico=:periodo AND (id_grado=:grado OR id_grado IS NULL) AND vigencia_inicio<=:fecha AND (vigencia_fin IS NULL OR vigencia_fin>=:fecha) ORDER BY id_grado IS NULL, id_tarifa DESC LIMIT 1");
            $crear = $this->db->prepare("INSERT INTO cuentas_cobrar(id_matricula,id_concepto_cobro,periodo_cobro,fecha_vencimiento,importe_original,descuento,saldo,estado) VALUES(:matricula,:concepto,:mes,:vencimiento,:importe,0,:importe,'PENDIENTE') ON DUPLICATE KEY UPDATE id_cuenta=id_cuenta");
            $creadas = 0;
            foreach ($matriculas->fetchAll() as $matricula) {
                $tarifa->execute([':concepto' => $data['id_concepto_cobro'], ':periodo' => $data['id_periodo_academico'], ':grado' => $matricula['id_grado'], ':fecha' => $periodoCobro]);
                $importe = $tarifa->fetchColumn();
                if ($importe === false) {
                    continue;
                }
                $crear->execute([':matricula' => $matricula['id_matricula'], ':concepto' => $data['id_concepto_cobro'], ':mes' => $periodoCobro, ':vencimiento' => $data['fecha_vencimiento'] ?: null, ':importe' => $importe]);
                $creadas += $crear->rowCount();
            }
            $this->db->commit();
            return $creadas;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function registrarPago(array $data, int $usuario): string
    {
        $idCuenta = (int) ($data['id_cuenta'] ?? 0);
        $importe = round((float) ($data['importe'] ?? 0), 2);
        if ($idCuenta < 1 || $importe <= 0) {
            throw new RuntimeException('Seleccione una cuenta e indique un importe válido.');
        }
        $this->db->beginTransaction();
        try {
            $cuenta = $this->db->prepare("SELECT cc.*, (SELECT aa.id_apoderado FROM matriculas m INNER JOIN alumno_apoderado aa ON aa.id_alumno=m.id_persona AND aa.estado='ACTIVO' WHERE m.id_matricula=cc.id_matricula ORDER BY aa.responsable_economico DESC,aa.es_principal DESC LIMIT 1) AS id_apoderado FROM cuentas_cobrar cc WHERE cc.id_cuenta=:id FOR UPDATE");
            $cuenta->execute([':id' => $idCuenta]);
            $cuenta = $cuenta->fetch();
            if (!$cuenta || $cuenta['estado'] === 'ANULADA' || $importe > (float) $cuenta['saldo']) {
                throw new RuntimeException('El importe no es válido para el saldo pendiente.');
            }
            $numero = 'R-' . date('Y') . '-' . str_pad((string) ((int) $this->db->query('SELECT COUNT(*) FROM pagos_alumno WHERE YEAR(fecha_pago)=' . (int) date('Y'))->fetchColumn() + 1), 6, '0', STR_PAD_LEFT);
            $pago = $this->db->prepare("INSERT INTO pagos_alumno(numero_recibo,id_apoderado,fecha_pago,medio_pago,importe_total,estado,registrado_por,observacion) VALUES(:recibo,:apoderado,NOW(),:medio,:importe,'REGISTRADO',:usuario,:observacion)");
            $pago->execute([':recibo' => $numero, ':apoderado' => $cuenta['id_apoderado'] ?: null, ':medio' => $data['medio_pago'], ':importe' => $importe, ':usuario' => $usuario, ':observacion' => trim($data['observacion']) ?: null]);
            $idPago = (int) $this->db->lastInsertId();
            $detalle = $this->db->prepare('INSERT INTO pagos_alumno_detalles(id_pago,id_cuenta,importe) VALUES(:pago,:cuenta,:importe)');
            $detalle->execute([':pago' => $idPago, ':cuenta' => $idCuenta, ':importe' => $importe]);
            $saldo = round((float) $cuenta['saldo'] - $importe, 2);
            $actualizar = $this->db->prepare("UPDATE cuentas_cobrar SET saldo=:saldo,estado=:estado WHERE id_cuenta=:id");
            $actualizar->execute([':saldo' => $saldo, ':estado' => $saldo <= 0 ? 'PAGADA' : 'PARCIAL', ':id' => $idCuenta]);
            $this->db->commit();
            return $numero;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
