<?php

require_once __DIR__ . '/PlanillaPreviewModel.php';

class PlanillaBorradorModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function concepto(string $nombre, string $naturaleza, string $tipo): int
    {
        $q = $this->db->prepare('SELECT id_concepto_pago FROM conceptos_pago WHERE nombre=? AND naturaleza=? AND tipo=? LIMIT 1');
        $q->execute([$nombre, $naturaleza, $tipo]);
        if ($id = $q->fetchColumn()) return (int) $id;
        $this->db->prepare("INSERT INTO conceptos_pago(nombre,naturaleza,tipo,estado) VALUES(?,?,?,'ACTIVO')")->execute([$nombre, $naturaleza, $tipo]);
        return (int) $this->db->lastInsertId();
    }

    public function generar(int $idPeriodo): int
    {
        $vista = (new PlanillaPreviewModel($this->db))->generar($idPeriodo);
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare('SELECT estado FROM periodos_pago WHERE id_periodo_pago=? FOR UPDATE');
            $q->execute([$idPeriodo]);
            $estado = $q->fetchColumn();
            if (!in_array($estado, ['ABIERTO', 'CALCULADO'], true)) throw new RuntimeException('Solo se puede generar o recalcular un período abierto o calculado.');

            $operaciones = $this->db->prepare('SELECT COUNT(*) FROM pagos_planilla pg INNER JOIN planilla_detalles pd ON pd.id_planilla_detalle=pg.id_planilla_detalle WHERE pd.id_periodo_pago=?');
            $operaciones->execute([$idPeriodo]);
            if ((int)$operaciones->fetchColumn()>0) throw new RuntimeException('No se puede recalcular porque el periodo ya tiene operaciones de pago.');
            $ajustesExistentes = $this->db->prepare('SELECT COUNT(*) FROM planilla_ajustes a INNER JOIN planilla_detalles pd ON pd.id_planilla_detalle=a.id_planilla_detalle WHERE pd.id_periodo_pago=?');
            $ajustesExistentes->execute([$idPeriodo]);
            if ((int)$ajustesExistentes->fetchColumn()>0) throw new RuntimeException('No se puede recalcular mientras existan ajustes manuales registrados en el periodo.');

            $this->db->prepare('DELETE pc FROM planilla_detalle_conceptos pc INNER JOIN planilla_detalles pd ON pd.id_planilla_detalle=pc.id_planilla_detalle WHERE pd.id_periodo_pago=?')->execute([$idPeriodo]);
            $this->db->prepare('DELETE FROM planilla_detalles WHERE id_periodo_pago=?')->execute([$idPeriodo]);
            $guardar = $this->db->prepare("INSERT INTO planilla_detalles(id_periodo_pago,id_vinculo,monto_base,bonificaciones,descuentos,monto_pagar,estado)
                VALUES(?,?,?,?,?,?,'CALCULADO')
                ON DUPLICATE KEY UPDATE monto_base=VALUES(monto_base),bonificaciones=VALUES(bonificaciones),descuentos=VALUES(descuentos),monto_pagar=VALUES(monto_pagar),monto_pagado=0,saldo_pendiente=VALUES(monto_pagar),decision_pago='PENDIENTE',monto_excluido=0,decision_motivo=NULL,decision_por=NULL,decision_en=NULL,estado='CALCULADO',calculado_en=NOW()");
            $buscar = $this->db->prepare('SELECT id_planilla_detalle FROM planilla_detalles WHERE id_periodo_pago=? AND id_vinculo=?');
            $agregar = $this->db->prepare('INSERT INTO planilla_detalle_conceptos(id_planilla_detalle,id_concepto_pago,id_regla,cantidad,importe,explicacion) VALUES(?,?,?,?,?,?)');

            $cantidad = 0;
            foreach ($vista['filas'] as $fila) {
                $base = $fila['mensual'] + $fila['pagoHora'] + $fila['pagoSesion'];
                $guardar->execute([$idPeriodo, $fila['v']['id_vinculo'], $base, $fila['bonificaciones'], $fila['descuentos'], $fila['neto']]);
                $buscar->execute([$idPeriodo, $fila['v']['id_vinculo']]);
                $detalle = (int) $buscar->fetchColumn();

                foreach ($fila['tramos'] as $tramo) {
                    $explicacion = ['tramo'=>true,'id_vinculo'=>$tramo['id_vinculo'],'desde'=>$tramo['desde'],'hasta'=>$tramo['hasta'],'modalidad'=>$tramo['modalidad'],'dias_laborables'=>$tramo['dias_laborables']];
                    if ($tramo['mensual'] > 0) $agregar->execute([$detalle,$this->concepto('Remuneración mensual','INGRESO','BASE'),null,$tramo['dias_vigentes'],$tramo['mensual'],json_encode($explicacion+['dias'=>$tramo['dias_vigentes']])]);
                    if ($tramo['pago_hora'] > 0) $agregar->execute([$detalle,$this->concepto('Pago por horas','INGRESO','HORA'),null,$tramo['horas'],$tramo['pago_hora'],json_encode($explicacion+['horas'=>$tramo['horas']])]);
                    if ($tramo['pago_sesion'] > 0) $agregar->execute([$detalle,$this->concepto('Pago por sesiones','INGRESO','HORA'),null,$tramo['sesiones'],$tramo['pago_sesion'],json_encode($explicacion+['sesiones'=>$tramo['sesiones']])]);
                }

                foreach ($fila['reglasAplicadas'] as $regla) {
                    $ingreso = $regla['naturaleza'] === 'BONIFICACION';
                    $tipo = $ingreso ? 'BONIFICACION' : ($regla['evento'] === 'TARDANZA' ? 'TARDANZA' : ($regla['evento'] === 'FALTA' ? 'FALTA' : 'MANUAL'));
                    $agregar->execute([$detalle, $this->concepto($regla['nombre'], $ingreso ? 'INGRESO' : 'DESCUENTO', $tipo), $regla['id_regla'], $regla['cantidad'], $ingreso ? $regla['importe'] : -$regla['importe'], json_encode(['evento' => $regla['evento'], 'automatico' => true])]);
                }
                $cantidad++;
            }

            $this->db->prepare("UPDATE periodos_pago SET estado='CALCULADO' WHERE id_periodo_pago=?")->execute([$idPeriodo]);
            $this->db->commit();
            return $cantidad;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function revision(int $idPeriodo): array
    {
        $q = $this->db->prepare("SELECT pp.*,(SELECT COUNT(*) FROM pagos_planilla pg INNER JOIN planilla_detalles pd ON pd.id_planilla_detalle=pg.id_planilla_detalle WHERE pd.id_periodo_pago=pp.id_periodo_pago) pagos_existentes FROM periodos_pago pp WHERE pp.id_periodo_pago=?");
        $q->execute([$idPeriodo]);
        $periodo = $q->fetch();
        if (!$periodo) throw new InvalidArgumentException('Período no encontrado.');

        $q = $this->db->prepare("SELECT pd.*,CONCAT(p.apellidos,', ',p.nombres) trabajador,v.tipo,v.modalidad_pago FROM planilla_detalles pd INNER JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo INNER JOIN personas p ON p.id_persona=v.id_persona WHERE pd.id_periodo_pago=? ORDER BY p.apellidos,p.nombres");
        $q->execute([$idPeriodo]);
        $detalles = $q->fetchAll();

        $conceptos = $this->db->prepare("SELECT pc.*,cp.nombre,cp.naturaleza,cp.tipo FROM planilla_detalle_conceptos pc INNER JOIN conceptos_pago cp ON cp.id_concepto_pago=pc.id_concepto_pago WHERE pc.id_planilla_detalle=? ORDER BY cp.naturaleza DESC,pc.id_detalle_concepto");
        foreach ($detalles as &$detalle) {
            $conceptos->execute([$detalle['id_planilla_detalle']]);
            $detalle['conceptos'] = $conceptos->fetchAll();
            foreach ($detalle['conceptos'] as &$concepto) $concepto['explicacion_datos'] = $concepto['explicacion'] ? (json_decode($concepto['explicacion'],true) ?: []) : [];
            unset($concepto);
        }
        unset($detalle);
        return compact('periodo', 'detalles');
    }
}
