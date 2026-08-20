<?php

class PlanillaAuditoriaModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function datos(int $anio = 0, string $accion = ''): array
    {
        $eventosCompletos = $this->eventosCentrales();
        $eventosCompletos = array_merge($eventosCompletos, $this->eventosOperativos());
        usort($eventosCompletos, function ($a, $b) {
            $fecha = strcmp($b['creado_en'], $a['creado_en']);
            return $fecha !== 0 ? $fecha : strcmp((string)$b['id_auditoria'], (string)$a['id_auditoria']);
        });

        $acciones = array_values(array_unique(array_column($eventosCompletos, 'accion')));
        sort($acciones);
        $anios = [];
        foreach ($eventosCompletos as $evento) {
            $anioEvento = (int)substr($evento['creado_en'], 0, 4);
            if ($anioEvento) $anios[$anioEvento] = true;
        }
        $anios = array_keys($anios);
        rsort($anios);
        if ($anio > 0 && !in_array($anio, $anios, true)) {
            $anios[] = $anio;
            rsort($anios);
        }

        $eventos = array_values(array_filter($eventosCompletos, function ($evento) use ($anio, $accion) {
            if ($anio > 0 && (int)substr($evento['creado_en'], 0, 4) !== $anio) return false;
            if ($accion !== '' && $evento['accion'] !== $accion) return false;
            return true;
        }));
        $eventos = array_slice($eventos, 0, 1000);

        $usuarios = [];
        foreach ($eventos as $evento) if ($evento['id_usuario']) $usuarios[(int)$evento['id_usuario']] = true;
        $resumen = ['eventos'=>count($eventos),'usuarios'=>count($usuarios),'ultimo'=>$eventos ? $eventos[0]['creado_en'] : null];
        $controles = $this->controles();
        $resumen['incidencias'] = array_sum(array_column($controles, 'cantidad'));
        return compact('eventos','acciones','anios','anio','accion','resumen','controles');
    }

    private function eventosCentrales(): array
    {
        return $this->db->query(
            "SELECT a.*,COALESCE(NULLIF(CONCAT(COALESCE(p.apellidos,''),', ',COALESCE(p.nombres,'')),', '),u.usuario,'Sistema') responsable
             FROM auditoria a
             LEFT JOIN usuarios u ON u.id_usuario=a.id_usuario
             LEFT JOIN personas p ON p.id_persona=u.id_persona
             WHERE a.entidad IN ('vinculos_laborales','periodos_pago','planilla_detalles','planilla_ajustes','pagos_planilla','reglas_descuento')
             ORDER BY a.creado_en DESC,a.id_auditoria DESC LIMIT 1000"
        )->fetchAll();
    }

    private function eventosOperativos(): array
    {
        $eventos = [];
        $responsable = "COALESCE(NULLIF(CONCAT(COALESCE(p.apellidos,''),', ',COALESCE(p.nombres,'')),', '),u.usuario,'Sistema')";

        $q = $this->db->query("SELECT pp.id_periodo_pago id,pp.nombre,pp.revisado_por usuario,pp.revisado_en fecha,$responsable responsable FROM periodos_pago pp LEFT JOIN usuarios u ON u.id_usuario=pp.revisado_por LEFT JOIN personas p ON p.id_persona=u.id_persona WHERE pp.revisado_en IS NOT NULL");
        foreach ($q->fetchAll() as $fila) $eventos[] = $this->evento('REVISION_APROBADA','periodos_pago',$fila['id'],$fila['usuario'],$fila['responsable'],$fila['fecha'],['periodo'=>$fila['nombre'],'estado'=>'REVISADO']);

        $q = $this->db->query("SELECT pp.id_periodo_pago id,pp.nombre,pp.cerrado_por usuario,pp.cerrado_en fecha,$responsable responsable FROM periodos_pago pp LEFT JOIN usuarios u ON u.id_usuario=pp.cerrado_por LEFT JOIN personas p ON p.id_persona=u.id_persona WHERE pp.cerrado_en IS NOT NULL");
        foreach ($q->fetchAll() as $fila) $eventos[] = $this->evento('PLANILLA_CERRADA','periodos_pago',$fila['id'],$fila['usuario'],$fila['responsable'],$fila['fecha'],['periodo'=>$fila['nombre'],'estado'=>'CERRADO']);

        $q = $this->db->query("SELECT pp.id_periodo_pago id,pp.nombre,MAX(pd.calculado_en) fecha,COUNT(*) trabajadores FROM periodos_pago pp JOIN planilla_detalles pd ON pd.id_periodo_pago=pp.id_periodo_pago GROUP BY pp.id_periodo_pago,pp.nombre");
        foreach ($q->fetchAll() as $fila) $eventos[] = $this->evento('PLANILLA_CALCULADA','periodos_pago',$fila['id'],null,'Sistema',$fila['fecha'],['periodo'=>$fila['nombre'],'trabajadores'=>(int)$fila['trabajadores']]);

        $q = $this->db->query("SELECT a.*, $responsable responsable FROM planilla_ajustes a LEFT JOIN usuarios u ON u.id_usuario=a.registrado_por LEFT JOIN personas p ON p.id_persona=u.id_persona");
        foreach ($q->fetchAll() as $fila) {
            $eventos[] = $this->evento('AJUSTE_REGISTRADO','planilla_ajustes',$fila['id_ajuste'],$fila['registrado_por'],$fila['responsable'],$fila['registrado_en'],['naturaleza'=>$fila['naturaleza'],'importe'=>$fila['importe'],'motivo'=>$fila['motivo']]);
            if ($fila['anulado_en']) {
                $eventos[] = $this->evento('AJUSTE_ANULADO','planilla_ajustes',$fila['id_ajuste'],$fila['anulado_por'],'Usuario #'.$fila['anulado_por'],$fila['anulado_en'],['motivo'=>$fila['motivo_anulacion'],'estado'=>'ANULADO']);
            }
        }

        $q = $this->db->query("SELECT pg.*, $responsable responsable FROM pagos_planilla pg LEFT JOIN usuarios u ON u.id_usuario=pg.pagado_por LEFT JOIN personas p ON p.id_persona=u.id_persona");
        foreach ($q->fetchAll() as $fila) {
            $eventos[] = $this->evento('PAGO_REGISTRADO','pagos_planilla',$fila['id_pago_planilla'],$fila['pagado_por'],$fila['responsable'],$fila['pagado_en'],['importe'=>$fila['importe'],'medio'=>$fila['medio_pago'],'referencia'=>$fila['referencia'],'estado'=>$fila['estado']]);
            if ($fila['anulado_en']) {
                $eventos[] = $this->evento('PAGO_ANULADO','pagos_planilla',$fila['id_pago_planilla'],$fila['anulado_por'],'Usuario #'.$fila['anulado_por'],$fila['anulado_en'],['importe'=>$fila['importe'],'motivo'=>$fila['motivo_anulacion'],'estado'=>'ANULADO']);
            }
        }

        $q = $this->db->query("SELECT v.id_vinculo,v.id_vinculo_anterior,v.motivo_cambio,v.registrado_por,v.registrado_en,v.modalidad_pago,v.monto_mensual,v.monto_hora,v.monto_sesion,v.dias_laborables,$responsable responsable FROM vinculos_laborales v LEFT JOIN usuarios u ON u.id_usuario=v.registrado_por LEFT JOIN personas p ON p.id_persona=u.id_persona WHERE v.registrado_en IS NOT NULL");
        foreach ($q->fetchAll() as $fila) $eventos[] = $this->evento($fila['id_vinculo_anterior']?'VINCULO_VERSIONADO':'VINCULO_REGISTRADO','vinculos_laborales',$fila['id_vinculo'],$fila['registrado_por'],$fila['responsable'],$fila['registrado_en'],['anterior'=>$fila['id_vinculo_anterior'],'modalidad'=>$fila['modalidad_pago'],'mensual'=>$fila['monto_mensual'],'hora'=>$fila['monto_hora'],'sesion'=>$fila['monto_sesion'],'dias'=>$fila['dias_laborables'],'motivo'=>$fila['motivo_cambio']]);
        return $eventos;
    }

    private function evento(string $accion, string $entidad, $id, $usuario, string $responsable, string $fecha, array $despues): array
    {
        return ['id_auditoria'=>'op-'.$entidad.'-'.$id.'-'.$accion,'id_usuario'=>$usuario,'accion'=>$accion,'entidad'=>$entidad,'id_entidad'=>$id,'valores_antes'=>null,'valores_despues'=>json_encode($despues,JSON_UNESCAPED_UNICODE),'ip'=>null,'creado_en'=>$fecha,'responsable'=>$responsable];
    }

    private function controles(): array
    {
        $consultas = [
            ['nombre'=>'Saldos inconsistentes','detalle'=>'Neto menos pagado no coincide con el saldo pendiente.','sql'=>"SELECT COUNT(*) FROM planilla_detalles WHERE ABS(monto_pagar-monto_pagado-saldo_pendiente)>0.01"],
            ['nombre'=>'Pagos sin conciliar','detalle'=>'El acumulado de pagos vigentes no coincide con el monto pagado.','sql'=>"SELECT COUNT(*) FROM planilla_detalles pd LEFT JOIN (SELECT id_planilla_detalle,SUM(importe) total FROM pagos_planilla WHERE estado='REGISTRADO' GROUP BY id_planilla_detalle) pg ON pg.id_planilla_detalle=pd.id_planilla_detalle WHERE ABS(pd.monto_pagado-COALESCE(pg.total,0))>0.01"],
            ['nombre'=>'Períodos pagados con deuda','detalle'=>'Un período figura pagado y todavía contiene deuda.','sql'=>"SELECT COUNT(*) FROM periodos_pago pp WHERE pp.estado='PAGADO' AND EXISTS(SELECT 1 FROM planilla_detalles pd WHERE pd.id_periodo_pago=pp.id_periodo_pago AND pd.saldo_pendiente>0.01)"],
            ['nombre'=>'Cierres sin responsable','detalle'=>'Planillas cerradas o pagadas sin usuario o fecha de cierre.','sql'=>"SELECT COUNT(*) FROM periodos_pago WHERE estado IN('CERRADO','PAGADO') AND (cerrado_por IS NULL OR cerrado_en IS NULL)"],
            ['nombre'=>'Pagos sin responsable','detalle'=>'Pagos registrados sin identificar al usuario responsable.','sql'=>"SELECT COUNT(*) FROM pagos_planilla WHERE pagado_por IS NULL"],
        ];
        foreach ($consultas as &$control) {
            $control['cantidad'] = (int)$this->db->query($control['sql'])->fetchColumn();
            unset($control['sql']);
        }
        unset($control);
        return $consultas;
    }
}
