<?php

class PlanillaConsultaModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function constancia(int $id): array
    {
        $sql = "SELECT pg.*,pd.monto_pagar,pd.monto_pagado,pd.saldo_pendiente,pp.nombre periodo,pp.fecha_inicio,pp.fecha_fin,p.codigo_trabajador,p.numero_documento AS documento_numero,CONCAT(p.apellidos,', ',p.nombres) trabajador,c.nombre cargo,COALESCE(NULLIF(CONCAT(COALESCE(pr.apellidos,''),', ',COALESCE(pr.nombres,'')),', '),u.usuario,'Usuario') responsable FROM pagos_planilla pg JOIN planilla_detalles pd ON pd.id_planilla_detalle=pg.id_planilla_detalle JOIN periodos_pago pp ON pp.id_periodo_pago=pd.id_periodo_pago JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo JOIN personas p ON p.id_persona=v.id_persona LEFT JOIN cargos c ON c.id_cargo=v.id_cargo LEFT JOIN usuarios u ON u.id_usuario=pg.pagado_por LEFT JOIN personas pr ON pr.id_persona=u.id_persona WHERE pg.id_pago_planilla=?";
        $q = $this->db->prepare($sql);
        $q->execute([$id]);
        $pago = $q->fetch();
        if (!$pago) throw new InvalidArgumentException('Pago no encontrado.');
        $institucion = $this->db->query('SELECT * FROM institucion ORDER BY id_institucion LIMIT 1')->fetch() ?: ['nombre' => 'Institución educativa'];
        return compact('pago', 'institucion');
    }

    public function historial(int $persona = 0): array
    {
        $trabajadores = $this->db->query("SELECT DISTINCT p.id_persona,CONCAT(p.apellidos,', ',p.nombres) trabajador,p.codigo_trabajador FROM planilla_detalles pd JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo JOIN personas p ON p.id_persona=v.id_persona ORDER BY trabajador,p.codigo_trabajador")->fetchAll();
        $sql = "SELECT pd.*,pp.nombre periodo,pp.fecha_inicio,pp.fecha_fin,pp.estado periodo_estado,v.id_persona,CONCAT(p.apellidos,', ',p.nombres) trabajador,p.codigo_trabajador FROM planilla_detalles pd JOIN periodos_pago pp ON pp.id_periodo_pago=pd.id_periodo_pago JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo JOIN personas p ON p.id_persona=v.id_persona";
        $params = [];
        if ($persona) {
            $sql .= ' WHERE v.id_persona=?';
            $params[] = $persona;
        }
        $sql .= ' ORDER BY pp.fecha_inicio DESC,p.apellidos,p.nombres';
        $q = $this->db->prepare($sql);
        $q->execute($params);
        $historial = $q->fetchAll();
        $totales = ['neto' => 0, 'pagado' => 0, 'excluido' => 0, 'saldo' => 0];
        foreach ($historial as $h) {
            $totales['neto'] += (float)$h['monto_pagar'];
            $totales['pagado'] += (float)$h['monto_pagado'];
            $totales['excluido'] += (float)($h['monto_excluido'] ?? 0);
            $totales['saldo'] += (float)$h['saldo_pendiente'];
        }
        return compact('trabajadores', 'historial', 'totales', 'persona');
    }

    public function reporte(int $periodo = 0, int $persona = 0, int $anio = 0, string $estadoCuenta = 'TODOS'): array
    {
        if (!in_array($estadoCuenta, ['TODOS', 'PENDIENTES', 'PARCIALES', 'PAGADOS', 'RETENIDOS', 'EXCLUIDOS'], true)) {
            $estadoCuenta = 'TODOS';
        }

        $periodos = $this->db->query(
            "SELECT id_periodo_pago,nombre,fecha_inicio,fecha_fin,estado
             FROM periodos_pago ORDER BY fecha_inicio DESC,id_periodo_pago DESC"
        )->fetchAll();
        $anios = array_map('intval', $this->db->query(
            'SELECT DISTINCT YEAR(fecha_inicio) anio FROM periodos_pago ORDER BY anio DESC'
        )->fetchAll(PDO::FETCH_COLUMN));
        if ($anio > 0 && !in_array($anio, $anios, true)) {
            $anios[] = $anio;
            rsort($anios);
        }

        $trabajadores = $this->db->query(
            "SELECT DISTINCT p.id_persona,CONCAT(p.apellidos, ', ', p.nombres) trabajador,p.codigo_trabajador
             FROM planilla_detalles pd
             JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo
             JOIN personas p ON p.id_persona=v.id_persona
             ORDER BY trabajador,p.codigo_trabajador"
        )->fetchAll();

        $condiciones = [];
        $params = [];
        if ($periodo > 0) {
            $condiciones[] = 'pp.id_periodo_pago=?';
            $params[] = $periodo;
        }
        if ($persona > 0) {
            $condiciones[] = 'v.id_persona=?';
            $params[] = $persona;
        }
        if ($anio > 0) {
            $condiciones[] = 'YEAR(pp.fecha_inicio)=?';
            $params[] = $anio;
        }
        if ($estadoCuenta === 'PENDIENTES') $condiciones[] = 'pd.saldo_pendiente>0';
        if ($estadoCuenta === 'PARCIALES') $condiciones[] = 'pd.monto_pagado>0 AND pd.saldo_pendiente>0';
        if ($estadoCuenta === 'PAGADOS') $condiciones[] = "pd.saldo_pendiente<=0 AND pd.decision_pago='PENDIENTE'";
        if ($estadoCuenta === 'RETENIDOS') $condiciones[] = "pd.decision_pago='RETENIDO'";
        if ($estadoCuenta === 'EXCLUIDOS') $condiciones[] = "pd.decision_pago='EXCLUIDO'";
        $where = $condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '';

        $sql = "SELECT pd.id_planilla_detalle,pd.monto_base,pd.bonificaciones,pd.descuentos,
                       pd.monto_pagar,pd.monto_pagado,pd.saldo_pendiente,pd.monto_excluido,pd.decision_pago,pd.decision_motivo,pd.estado,
                       pp.id_periodo_pago,pp.nombre periodo,pp.fecha_inicio,pp.fecha_fin,
                       pp.estado periodo_estado,v.id_persona,v.tipo,v.modalidad_pago,
                       CONCAT(p.apellidos, ', ', p.nombres) trabajador,p.codigo_trabajador,
                       p.numero_documento,COALESCE(c.nombre, 'Sin cargo') cargo
                FROM planilla_detalles pd
                JOIN periodos_pago pp ON pp.id_periodo_pago=pd.id_periodo_pago
                JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo
                JOIN personas p ON p.id_persona=v.id_persona
                LEFT JOIN cargos c ON c.id_cargo=v.id_cargo" . $where .
                ' ORDER BY pp.fecha_inicio DESC,p.apellidos,p.nombres,pd.id_planilla_detalle';
        $consulta = $this->db->prepare($sql);
        $consulta->execute($params);
        $filas = $consulta->fetchAll();

        $totales = ['trabajadores' => 0, 'base' => 0.0, 'bonificaciones' => 0.0, 'descuentos' => 0.0, 'neto' => 0.0, 'pagado' => 0.0, 'excluido' => 0.0, 'saldo' => 0.0];
        $personasUnicas = [];
        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        $resumenMensual = [];
        if ($anio > 0 && !$periodo) {
            foreach ($meses as $numero => $nombre) {
                $clave = sprintf('%04d-%02d', $anio, $numero);
                $resumenMensual[$clave] = ['etiqueta'=>$nombre . ' ' . $anio,'base'=>0.0,'bonificaciones'=>0.0,'descuentos'=>0.0,'neto'=>0.0,'pagado'=>0.0,'excluido'=>0.0,'saldo'=>0.0];
            }
        }
        foreach ($filas as $fila) {
            $personasUnicas[(int)$fila['id_persona']] = true;
            foreach (['base'=>'monto_base','bonificaciones'=>'bonificaciones','descuentos'=>'descuentos','neto'=>'monto_pagar','pagado'=>'monto_pagado','excluido'=>'monto_excluido','saldo'=>'saldo_pendiente'] as $total => $campo) {
                $totales[$total] += (float)$fila[$campo];
            }
            $clave = substr($fila['fecha_inicio'], 0, 7);
            if (!isset($resumenMensual[$clave])) {
                $numeroMes = (int)substr($fila['fecha_inicio'], 5, 2);
                $resumenMensual[$clave] = ['etiqueta'=>$meses[$numeroMes] . ' ' . substr($fila['fecha_inicio'], 0, 4),'base'=>0.0,'bonificaciones'=>0.0,'descuentos'=>0.0,'neto'=>0.0,'pagado'=>0.0,'excluido'=>0.0,'saldo'=>0.0];
            }
            foreach (['base'=>'monto_base','bonificaciones'=>'bonificaciones','descuentos'=>'descuentos','neto'=>'monto_pagar','pagado'=>'monto_pagado','excluido'=>'monto_excluido','saldo'=>'saldo_pendiente'] as $total => $campo) {
                $resumenMensual[$clave][$total] += (float)$fila[$campo];
            }
        }
        $totales['trabajadores'] = count($personasUnicas);
        ksort($resumenMensual);

        $sqlMedios = "SELECT pg.medio_pago,COUNT(*) cantidad,COALESCE(SUM(pg.importe),0) total
                      FROM pagos_planilla pg
                      JOIN planilla_detalles pd ON pd.id_planilla_detalle=pg.id_planilla_detalle
                      JOIN periodos_pago pp ON pp.id_periodo_pago=pd.id_periodo_pago
                      JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo" .
                      ($where ? $where . " AND pg.estado='REGISTRADO'" : " WHERE pg.estado='REGISTRADO'") .
                      " GROUP BY pg.medio_pago ORDER BY FIELD(pg.medio_pago,'EFECTIVO','TRANSFERENCIA','DEPOSITO','OTRO')";
        $consultaMedios = $this->db->prepare($sqlMedios);
        $consultaMedios->execute($params);
        $pagosPorMedio = $consultaMedios->fetchAll();
        $totalMedios = 0.0;
        foreach ($pagosPorMedio as $pagoMedio) $totalMedios += (float)$pagoMedio['total'];

        $sqlAnulados = "SELECT COUNT(*) cantidad,COALESCE(SUM(pg.importe),0) total
                        FROM pagos_planilla pg
                        JOIN planilla_detalles pd ON pd.id_planilla_detalle=pg.id_planilla_detalle
                        JOIN periodos_pago pp ON pp.id_periodo_pago=pd.id_periodo_pago
                        JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo" .
                        ($where ? $where . " AND pg.estado='ANULADO'" : " WHERE pg.estado='ANULADO'");
        $consultaAnulados = $this->db->prepare($sqlAnulados);
        $consultaAnulados->execute($params);
        $pagosAnulados = $consultaAnulados->fetch() ?: ['cantidad'=>0,'total'=>0];

        $institucion = $this->db->query('SELECT * FROM institucion ORDER BY id_institucion LIMIT 1')->fetch() ?: ['nombre' => 'Institución educativa'];

        return compact('periodos','anios','trabajadores','filas','totales','resumenMensual','pagosPorMedio','totalMedios','pagosAnulados','periodo','persona','anio','estadoCuenta','institucion');
    }
}
