<?php

class PlanillaEstadoModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function aprobar(int $id, int $usuario): void
    {
        $this->db->beginTransaction();
        try {
            $periodo = $this->bloquearPeriodo($id);
            if ($periodo['estado'] !== 'CALCULADO') throw new RuntimeException('Solo se puede aprobar una planilla calculada.');

            $cantidad = $this->db->prepare('SELECT COUNT(*) FROM planilla_detalles WHERE id_periodo_pago=?');
            $cantidad->execute([$id]);
            if ((int) $cantidad->fetchColumn() < 1) throw new RuntimeException('La planilla no tiene trabajadores calculados.');

            $this->db->prepare("UPDATE periodos_pago SET estado='REVISADO',revisado_por=?,revisado_en=NOW() WHERE id_periodo_pago=?")->execute([$usuario, $id]);
            $this->db->prepare("UPDATE planilla_detalles SET estado='REVISADO' WHERE id_periodo_pago=?")->execute([$id]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function cerrar(int $id, int $usuario): void
    {
        $this->db->beginTransaction();
        try {
            $periodo = $this->bloquearPeriodo($id);
            if ($periodo['estado'] !== 'REVISADO') throw new RuntimeException('La planilla debe estar revisada antes de cerrar su cálculo.');

            $this->db->prepare("UPDATE periodos_pago SET estado='CERRADO',cerrado_por=?,cerrado_en=NOW() WHERE id_periodo_pago=?")->execute([$usuario, $id]);
            $this->db->prepare("UPDATE planilla_detalles SET estado='CERRADO',decision_pago='PENDIENTE',monto_excluido=0,decision_motivo=NULL,decision_por=NULL,decision_en=NULL WHERE id_periodo_pago=?")->execute([$id]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function reabrir(int $id, string $motivo, int $usuario): void
    {
        $motivo = trim($motivo);
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) {
            throw new InvalidArgumentException('Indique un motivo de reapertura de entre 5 y 500 caracteres.');
        }

        $this->db->beginTransaction();
        try {
            $periodo = $this->bloquearPeriodo($id);
            if (!in_array($periodo['estado'], ['CERRADO', 'PAGADO', 'FINALIZADO'], true)) {
                throw new RuntimeException('Solo se puede reabrir una planilla cuyo cálculo ya fue cerrado.');
            }

            $pagos = $this->db->prepare('SELECT COUNT(*) FROM pagos_planilla pg INNER JOIN planilla_detalles pd ON pd.id_planilla_detalle=pg.id_planilla_detalle WHERE pd.id_periodo_pago=?');
            $pagos->execute([$id]);
            if ((int) $pagos->fetchColumn() > 0) {
                throw new RuntimeException('No se puede reabrir porque este período ya tiene pagos registrados, incluso si fueron anulados. Use un ajuste o una planilla complementaria.');
            }

            $this->db->prepare("UPDATE planilla_detalles SET monto_pagado=0,saldo_pendiente=monto_pagar,decision_pago='PENDIENTE',monto_excluido=0,decision_motivo=NULL,decision_por=NULL,decision_en=NULL,estado='CALCULADO' WHERE id_periodo_pago=?")->execute([$id]);
            $this->db->prepare("UPDATE periodos_pago SET estado='CALCULADO',revisado_por=NULL,revisado_en=NULL,cerrado_por=NULL,cerrado_en=NULL WHERE id_periodo_pago=?")->execute([$id]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function bloquearPeriodo(int $id): array
    {
        if ($id < 1) throw new InvalidArgumentException('Período de planilla inválido.');
        $consulta = $this->db->prepare('SELECT * FROM periodos_pago WHERE id_periodo_pago=? FOR UPDATE');
        $consulta->execute([$id]);
        $periodo = $consulta->fetch();
        if (!$periodo) throw new InvalidArgumentException('Período de planilla no encontrado.');
        return $periodo;
    }
}
