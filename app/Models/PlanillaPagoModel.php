<?php

class PlanillaPagoModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function datos(int $id): array
    {
        $q = $this->db->prepare('SELECT * FROM periodos_pago WHERE id_periodo_pago=?');
        $q->execute([$id]);
        $periodo = $q->fetch();
        if (!$periodo) throw new InvalidArgumentException('Período no encontrado.');

        $q = $this->db->prepare("SELECT pd.*,CONCAT(p.apellidos,', ',p.nombres) trabajador,
            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(dp.nombres,''),' ',COALESCE(dp.apellidos,''))),''),du.usuario) decision_responsable
            FROM planilla_detalles pd
            INNER JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo
            INNER JOIN personas p ON p.id_persona=v.id_persona
            LEFT JOIN usuarios du ON du.id_usuario=pd.decision_por
            LEFT JOIN personas dp ON dp.id_persona=du.id_persona
            WHERE pd.id_periodo_pago=? ORDER BY p.apellidos,p.nombres");
        $q->execute([$id]);
        $detalles = $q->fetchAll();

        $q = $this->db->prepare("SELECT pp.*,CONCAT(p.apellidos,', ',p.nombres) trabajador,
            COALESCE(NULLIF(CONCAT(COALESCE(pr.apellidos,''),', ',COALESCE(pr.nombres,'')),', '),u.usuario,'Usuario') responsable
            FROM pagos_planilla pp
            INNER JOIN planilla_detalles pd ON pd.id_planilla_detalle=pp.id_planilla_detalle
            INNER JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo
            INNER JOIN personas p ON p.id_persona=v.id_persona
            LEFT JOIN usuarios u ON u.id_usuario=pp.pagado_por
            LEFT JOIN personas pr ON pr.id_persona=u.id_persona
            WHERE pd.id_periodo_pago=? ORDER BY pp.pagado_en DESC,pp.id_pago_planilla DESC");
        $q->execute([$id]);
        $pagos = $q->fetchAll();

        $resumen = ['total' => count($detalles), 'pagados' => 0, 'parciales' => 0, 'pendientes' => 0, 'retenidos' => 0, 'excluidos' => 0, 'saldo' => 0.0, 'excluido' => 0.0];
        foreach ($detalles as $detalle) {
            $saldo = (float) $detalle['saldo_pendiente'];
            $pagado = (float) $detalle['monto_pagado'];
            $decision = $detalle['decision_pago'] ?? 'PENDIENTE';
            $resumen['saldo'] += $saldo;
            $resumen['excluido'] += (float) ($detalle['monto_excluido'] ?? 0);
            if ($decision === 'RETENIDO') $resumen['retenidos']++;
            elseif ($decision === 'EXCLUIDO') $resumen['excluidos']++;
            elseif ($saldo <= 0.009) $resumen['pagados']++;
            elseif ($pagado > 0) $resumen['parciales']++;
            else $resumen['pendientes']++;
        }

        return compact('periodo', 'detalles', 'pagos', 'resumen');
    }

    public function registrar(array $datos, int $usuario): int
    {
        $detalle = (int) ($datos['id_planilla_detalle'] ?? 0);
        $importe = round((float) ($datos['importe'] ?? 0), 2);
        $medio = $datos['medio_pago'] ?? '';
        if (!$detalle || $importe <= 0 || !in_array($medio, ['EFECTIVO', 'TRANSFERENCIA', 'DEPOSITO', 'OTRO'], true)) {
            throw new InvalidArgumentException('Complete correctamente el pago.');
        }

        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT pd.*,pp.estado periodo_estado FROM planilla_detalles pd INNER JOIN periodos_pago pp ON pp.id_periodo_pago=pd.id_periodo_pago WHERE pd.id_planilla_detalle=? FOR UPDATE");
            $q->execute([$detalle]);
            $planilla = $q->fetch();
            if (!$planilla || $planilla['periodo_estado'] !== 'CERRADO') throw new RuntimeException('El cálculo debe estar cerrado y tener saldo pendiente para registrar pagos.');
            if (($planilla['decision_pago'] ?? 'PENDIENTE') !== 'PENDIENTE') throw new RuntimeException('Este trabajador tiene el pago retenido o excluido. Reactive el pago antes de continuar.');
            if ($importe > (float) $planilla['saldo_pendiente']) throw new RuntimeException('El importe supera el saldo pendiente.');

            $this->db->prepare("INSERT INTO pagos_planilla(id_planilla_detalle,importe,medio_pago,referencia,observacion,pagado_por,estado) VALUES(?,?,?,?,?,?,'REGISTRADO')")->execute([
                $detalle,
                $importe,
                $medio,
                trim((string) ($datos['referencia'] ?? '')) ?: null,
                trim((string) ($datos['observacion'] ?? '')) ?: null,
                $usuario,
            ]);
            $pagado = round((float) $planilla['monto_pagado'] + $importe, 2);
            $saldo = max(0, round((float) $planilla['monto_pagar'] - $pagado - (float) ($planilla['monto_excluido'] ?? 0), 2));
            $estado = $saldo <= 0 ? 'PAGADO' : 'PARCIAL';
            $this->db->prepare('UPDATE planilla_detalles SET monto_pagado=?,saldo_pendiente=?,estado=? WHERE id_planilla_detalle=?')->execute([$pagado, $saldo, $estado, $detalle]);
            $this->actualizarPeriodo((int) $planilla['id_periodo_pago']);
            $this->db->commit();
            return (int) $planilla['id_periodo_pago'];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function anular(int $id, string $motivo, int $usuario): int
    {
        $motivo = trim($motivo);
        if (!$id || mb_strlen($motivo) < 3) throw new InvalidArgumentException('Indique el motivo de anulación.');

        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT pg.*,pd.id_periodo_pago,pd.monto_pagar,pd.monto_pagado,pd.monto_excluido,pd.decision_pago FROM pagos_planilla pg INNER JOIN planilla_detalles pd ON pd.id_planilla_detalle=pg.id_planilla_detalle WHERE pg.id_pago_planilla=? FOR UPDATE");
            $q->execute([$id]);
            $pago = $q->fetch();
            if (!$pago || $pago['estado'] !== 'REGISTRADO') throw new RuntimeException('El pago no puede anularse.');
            if ($pago['decision_pago'] === 'EXCLUIDO') throw new RuntimeException('Revierta primero la exclusión del saldo para poder anular este pago.');

            $this->db->prepare("UPDATE pagos_planilla SET estado='ANULADO',anulado_por=?,anulado_en=NOW(),motivo_anulacion=? WHERE id_pago_planilla=?")->execute([$usuario, $motivo, $id]);
            $pagado = max(0, round((float) $pago['monto_pagado'] - (float) $pago['importe'], 2));
            $saldo = max(0, round((float) $pago['monto_pagar'] - $pagado - (float) $pago['monto_excluido'], 2));
            $estado = $pagado <= 0 ? 'CERRADO' : ($saldo <= 0 ? 'PAGADO' : 'PARCIAL');
            $this->db->prepare('UPDATE planilla_detalles SET monto_pagado=?,saldo_pendiente=?,estado=? WHERE id_planilla_detalle=?')->execute([$pagado, $saldo, $estado, $pago['id_planilla_detalle']]);
            $this->actualizarPeriodo((int) $pago['id_periodo_pago']);
            $this->db->commit();
            return (int) $pago['id_periodo_pago'];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function decidir(int $idDetalle, string $decision, string $motivo, int $usuario): array
    {
        $decision = strtoupper(trim($decision));
        $motivo = trim($motivo);
        if ($idDetalle < 1 || !in_array($decision, ['RETENIDO', 'EXCLUIDO', 'PENDIENTE'], true)) throw new InvalidArgumentException('Decisión de pago inválida.');
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) throw new InvalidArgumentException('Indique un motivo de entre 5 y 500 caracteres.');

        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT pd.*,pp.estado periodo_estado FROM planilla_detalles pd INNER JOIN periodos_pago pp ON pp.id_periodo_pago=pd.id_periodo_pago WHERE pd.id_planilla_detalle=? FOR UPDATE");
            $q->execute([$idDetalle]);
            $detalle = $q->fetch();
            if (!$detalle || !in_array($detalle['periodo_estado'], ['CERRADO', 'FINALIZADO'], true)) throw new RuntimeException('Las decisiones de pago solo se administran después de cerrar el cálculo.');

            $anterior = $detalle['decision_pago'] ?? 'PENDIENTE';
            if ($decision === 'PENDIENTE') {
                if ($anterior === 'PENDIENTE') throw new RuntimeException('El pago ya se encuentra activo.');
                $saldo = $anterior === 'EXCLUIDO'
                    ? max(0, round((float) $detalle['monto_pagar'] - (float) $detalle['monto_pagado'], 2))
                    : (float) $detalle['saldo_pendiente'];
                $estado = (float) $detalle['monto_pagado'] > 0 ? 'PARCIAL' : 'CERRADO';
                $this->db->prepare("UPDATE planilla_detalles SET decision_pago='PENDIENTE',monto_excluido=0,saldo_pendiente=?,decision_motivo=?,decision_por=?,decision_en=NOW(),estado=? WHERE id_planilla_detalle=?")->execute([$saldo, $motivo, $usuario, $estado, $idDetalle]);
            } elseif ($decision === 'RETENIDO') {
                if ($anterior === 'EXCLUIDO') throw new RuntimeException('Revierta primero la exclusión antes de retener el pago.');
                if ((float) $detalle['saldo_pendiente'] <= 0) throw new RuntimeException('El trabajador no tiene saldo para retener.');
                $this->db->prepare("UPDATE planilla_detalles SET decision_pago='RETENIDO',decision_motivo=?,decision_por=?,decision_en=NOW() WHERE id_planilla_detalle=?")->execute([$motivo, $usuario, $idDetalle]);
            } else {
                if ((float) $detalle['saldo_pendiente'] <= 0) throw new RuntimeException('El trabajador no tiene saldo pendiente para excluir.');
                $montoExcluido = (float) $detalle['saldo_pendiente'];
                $this->db->prepare("UPDATE planilla_detalles SET decision_pago='EXCLUIDO',monto_excluido=?,saldo_pendiente=0,decision_motivo=?,decision_por=?,decision_en=NOW(),estado='CERRADO' WHERE id_planilla_detalle=?")->execute([$montoExcluido, $motivo, $usuario, $idDetalle]);
            }

            $this->actualizarPeriodo((int) $detalle['id_periodo_pago']);
            $this->db->commit();
            return ['id_periodo_pago' => (int) $detalle['id_periodo_pago'], 'anterior' => $anterior, 'decision' => $decision, 'motivo' => $motivo];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function actualizarPeriodo(int $id): void
    {
        $q = $this->db->prepare('SELECT COUNT(*) FROM planilla_detalles WHERE id_periodo_pago=? AND saldo_pendiente>0.009');
        $q->execute([$id]);
        if ((int) $q->fetchColumn() > 0) {
            $this->db->prepare("UPDATE periodos_pago SET estado='CERRADO' WHERE id_periodo_pago=?")->execute([$id]);
            return;
        }

        $q = $this->db->prepare("SELECT COUNT(*) FROM planilla_detalles WHERE id_periodo_pago=? AND decision_pago='EXCLUIDO'");
        $q->execute([$id]);
        $estado = (int) $q->fetchColumn() > 0 ? 'FINALIZADO' : 'PAGADO';
        $this->db->prepare('UPDATE periodos_pago SET estado=? WHERE id_periodo_pago=?')->execute([$estado, $id]);
    }
}
