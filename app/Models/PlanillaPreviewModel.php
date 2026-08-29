<?php
class PlanillaPreviewModel
{
    private $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function generar(int $id): array
    {
        $q = $this->db->prepare('SELECT * FROM periodos_pago WHERE id_periodo_pago=?');
        $q->execute([$id]);
        $periodo = $q->fetch();
        if (!$periodo) throw new InvalidArgumentException('El período no existe.');
        $q = $this->db->prepare("SELECT v.*,CONCAT(p.apellidos,', ',p.nombres) trabajador FROM vinculos_laborales v JOIN personas p ON p.id_persona=v.id_persona WHERE v.fecha_inicio<=? AND (v.fecha_fin IS NULL OR v.fecha_fin>=?) AND v.estado<>'INACTIVO' ORDER BY p.apellidos,p.nombres");
        $q->execute([$periodo['fecha_fin'], $periodo['fecha_inicio']]);
        $vinculos = $q->fetchAll();
        $q = $this->db->prepare("SELECT * FROM reglas_descuento WHERE estado='ACTIVO' AND fecha_inicio<=? AND (fecha_fin IS NULL OR fecha_fin>=?)");
        $q->execute([$periodo['fecha_fin'], $periodo['fecha_inicio']]);
        $reglas = $q->fetchAll();
        $q = $this->db->prepare("SELECT fecha FROM calendario_institucional WHERE estado='ACTIVO' AND fecha BETWEEN ? AND ?");
        $q->execute([$periodo['fecha_inicio'], $periodo['fecha_fin']]);
        $calendario = array_flip($q->fetchAll(PDO::FETCH_COLUMN));
        $buscarJornada = $this->db->prepare("SELECT ah.*,j.nombre jornada,j.hora_entrada,j.hora_salida,j.tolerancia_minutos FROM asignaciones_horario ah JOIN jornadas j ON j.id_jornada=ah.id_jornada WHERE ah.id_persona=? AND ah.estado='ACTIVO' AND j.estado='ACTIVO' AND ah.fecha_inicio<=? AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=?) ORDER BY ah.fecha_inicio DESC LIMIT 1");
        $buscarCargas = $this->db->prepare("SELECT c.*,pa.fecha_inicio periodo_inicio,pa.fecha_fin periodo_fin FROM carga_academica c JOIN periodos_academicos pa ON pa.id_periodo_academico=c.id_periodo_academico JOIN vinculos_laborales vc ON vc.id_vinculo=c.id_vinculo WHERE vc.id_persona=? AND c.estado='ACTIVO' AND pa.fecha_inicio<=? AND pa.fecha_fin>=?");
        $asistencia = $this->db->prepare("SELECT COALESCE(SUM(CASE WHEN NOT EXISTS(SELECT 1 FROM incidencias_asistencia ia WHERE ia.id_asistencia=a.id_asistencia AND ia.tipo='JUSTIFICACION_TARDANZA' AND ia.estado='APROBADA') THEN a.minutos_tardanza ELSE 0 END),0) tardanza,COUNT(DISTINCT CASE WHEN a.estado='FALTA' THEN a.fecha END) faltas,COUNT(DISTINCT CASE WHEN a.hora_entrada IS NOT NULL THEN a.fecha END) asistidos,COALESCE(SUM(CASE WHEN a.hora_salida IS NOT NULL THEN a.minutos_trabajados ELSE 0 END),0) minutos,COUNT(DISTINCT CASE WHEN a.id_carga IS NOT NULL AND a.estado IN('PUNTUAL','TARDANZA') THEN CONCAT(a.fecha,'-',a.id_carga) END) sesiones,COUNT(CASE WHEN a.hora_entrada IS NOT NULL AND a.hora_salida IS NULL THEN 1 END) incompletos FROM asistencias_diarias a WHERE a.id_persona=? AND a.fecha BETWEEN ? AND ? AND a.estado<>'ANULADO' AND (a.id_carga IS NOT NULL OR (a.id_jornada IS NOT NULL AND FIND_IN_SET(CAST(WEEKDAY(a.fecha)+1 AS CHAR),?)>0))");
        $filas = [];
        $totales = ['ingresos' => 0, 'descuentos' => 0, 'neto' => 0];
        foreach ($vinculos as $v) {
            $buscarJornada->execute([$v['id_persona'], $periodo['fecha_fin'], $periodo['fecha_inicio']]);
            $jornada = $buscarJornada->fetch() ?: null;
            $buscarCargas->execute([$v['id_persona'], $periodo['fecha_fin'], $periodo['fecha_inicio']]);
            $cargas = $buscarCargas->fetchAll();
            $diasPermitidos = array_map('intval', explode(',', $v['dias_laborables'] ?: '1,2,3,4,5'));
            $diasPeriodo = 0;
            $diasVigentes = 0;
            $feriados = 0;
            $sesionesEsperadas = 0;
            $minutosProgramados = 0;
            for ($fecha = new DateTimeImmutable($periodo['fecha_inicio']); $fecha <= new DateTimeImmutable($periodo['fecha_fin']); $fecha = $fecha->modify('+1 day')) {
                $valor = $fecha->format('Y-m-d');
                $esFeriado = isset($calendario[$valor]);
                $vigente = $valor >= $v['fecha_inicio'] && (!$v['fecha_fin'] || $valor <= $v['fecha_fin']);
                if (in_array((int)$fecha->format('N'), $diasPermitidos, true)) {
                    if ($esFeriado) {
                        if ($vigente) $feriados++;
                    } else {
                        $diasPeriodo++;
                        $asignado = !$jornada || ($valor >= $jornada['fecha_inicio'] && (!$jornada['fecha_fin'] || $valor <= $jornada['fecha_fin']));
                        if ($vigente && $asignado) $diasVigentes++;
                    }
                }
                if ($esFeriado || $valor < $v['fecha_inicio'] || ($v['fecha_fin'] && $valor > $v['fecha_fin'])) continue;
                foreach ($cargas as $c) {
                    if ((int)$fecha->format('N') !== (int)$c['dia_semana'] || $valor < $c['periodo_inicio'] || $valor > $c['periodo_fin']) continue;
                    $sesionesEsperadas++;
                    $minutosProgramados += max(0, (int)((strtotime($valor . ' ' . $c['hora_fin']) - strtotime($valor . ' ' . $c['hora_inicio'])) / 60));
                }
            }
            $asistenciaDesde = max($periodo['fecha_inicio'], $v['fecha_inicio']);
            $asistenciaHasta = min($periodo['fecha_fin'], $v['fecha_fin'] ?: $periodo['fecha_fin']);
            $asistencia->execute([$v['id_persona'], $asistenciaDesde, $asistenciaHasta, $v['dias_laborables'] ?: '1,2,3,4,5']);
            $a = $asistencia->fetch();
            $mensual = round((float)$v['monto_mensual'] * ($diasPeriodo ? $diasVigentes / $diasPeriodo : 0), 2);
            $horas = round((float)$a['minutos'] / 60, 2);
            $pagoHora = round($horas * (float)$v['monto_hora'], 2);
            $pagoSesion = round((float)$a['sesiones'] * (float)$v['monto_sesion'], 2);
            $descuentos = 0;
            $bonificaciones = 0;
            $reglasAplicadas = [];
            foreach ($reglas as $r) {
                $formula = json_decode($r['formula'], true);
                $cantidad = $r['evento'] === 'TARDANZA' ? $a['tardanza'] : ($r['evento'] === 'FALTA' ? $a['faltas'] : 1);
                $importe = round($cantidad * (float)($formula['monto_unitario'] ?? 0), 2);
                if ($importe <= 0) continue;
                if (($r['naturaleza'] ?? 'DESCUENTO') === 'BONIFICACION') $bonificaciones += $importe;
                else $descuentos += $importe;
                $reglasAplicadas[] = ['id_regla'=>(int)$r['id_regla'],'nombre'=>$r['nombre'],'naturaleza'=>$r['naturaleza']??'DESCUENTO','evento'=>$r['evento'],'cantidad'=>(float)$cantidad,'importe'=>$importe];
            }
            $ingresos = $mensual + $pagoHora + $pagoSesion + $bonificaciones;
            $neto = max(0, $ingresos - $descuentos);
            $advertencias = [];
            if (((float)$v['monto_mensual'] > 0 || (float)$v['monto_hora'] > 0) && !$jornada && !$cargas) $advertencias[] = 'Sin jornada ni sesiones';
            if ((float)$v['monto_hora'] > 0 && (int)$a['incompletos'] > 0) $advertencias[] = $a['incompletos'] . ' asistencia(s) sin salida';
            if ((float)$v['monto_sesion'] > 0 && !$cargas) $advertencias[] = 'Sin sesiones programadas';
            $tramo = ['id_vinculo'=>(int)$v['id_vinculo'],'desde'=>$asistenciaDesde,'hasta'=>$asistenciaHasta,'tipo'=>$v['tipo'],'modalidad'=>$v['modalidad_pago'],'dias_laborables'=>$v['dias_laborables'],'dias_vigentes'=>$diasVigentes,'mensual'=>$mensual,'horas'=>$horas,'pago_hora'=>$pagoHora,'sesiones'=>(int)$a['sesiones'],'pago_sesion'=>$pagoSesion];
            $filas[] = compact('v', 'jornada', 'diasVigentes', 'feriados', 'sesionesEsperadas', 'minutosProgramados', 'a', 'mensual', 'horas', 'pagoHora', 'pagoSesion', 'bonificaciones', 'descuentos', 'neto', 'advertencias', 'reglasAplicadas', 'tramo');
            $totales['ingresos'] += $ingresos;
            $totales['descuentos'] += $descuentos;
            $totales['neto'] += $neto;
        }
        $filas = $this->consolidarPorTrabajador($filas, $reglas);
        $totales = ['ingresos'=>0.0,'descuentos'=>0.0,'neto'=>0.0];
        foreach ($filas as $fila) {
            $totales['ingresos'] += $fila['mensual'] + $fila['pagoHora'] + $fila['pagoSesion'] + $fila['bonificaciones'];
            $totales['descuentos'] += $fila['descuentos'];
            $totales['neto'] += $fila['neto'];
        }
        return compact('periodo', 'filas', 'totales');
    }

    private function consolidarPorTrabajador(array $segmentos, array $reglas): array
    {
        $grupos = [];
        foreach ($segmentos as $fila) {
            $persona = (int)$fila['v']['id_persona'];
            if (!isset($grupos[$persona])) {
                $grupo = $fila;
                foreach (['diasVigentes','feriados','sesionesEsperadas','minutosProgramados','mensual','horas','pagoHora','pagoSesion','bonificaciones','descuentos','neto'] as $campo) $grupo[$campo] = 0;
                foreach (['tardanza','faltas','asistidos','minutos','sesiones','incompletos'] as $campo) $grupo['a'][$campo] = 0;
                $grupo['advertencias'] = [];
                $grupo['reglasAplicadas'] = [];
                $grupo['tramos'] = [];
                $grupos[$persona] = $grupo;
            }
            $grupo =& $grupos[$persona];
            if ($fila['v']['fecha_inicio'] >= $grupo['v']['fecha_inicio']) {
                $grupo['v'] = $fila['v'];
                $grupo['jornada'] = $fila['jornada'];
            }
            foreach (['diasVigentes','feriados','sesionesEsperadas','minutosProgramados','mensual','pagoHora','pagoSesion'] as $campo) $grupo[$campo] += (float)$fila[$campo];
            foreach (['tardanza','faltas','asistidos','minutos','sesiones','incompletos'] as $campo) $grupo['a'][$campo] += (float)($fila['a'][$campo] ?? 0);
            $grupo['advertencias'] = array_values(array_unique(array_merge($grupo['advertencias'],$fila['advertencias'])));
            $grupo['tramos'][] = $fila['tramo'];
            unset($grupo);
        }
        foreach ($grupos as &$grupo) {
            usort($grupo['tramos'], function(array $a,array $b): int { return strcmp($a['desde'],$b['desde']); });
            $grupo['horas'] = round((float)$grupo['a']['minutos']/60,2);
            $grupo['bonificaciones'] = 0.0;
            $grupo['descuentos'] = 0.0;
            foreach ($reglas as $regla) {
                $formula = json_decode($regla['formula'],true);
                $cantidad = $regla['evento']==='TARDANZA' ? $grupo['a']['tardanza'] : ($regla['evento']==='FALTA' ? $grupo['a']['faltas'] : 1);
                $importe = round((float)$cantidad*(float)($formula['monto_unitario']??0),2);
                if ($importe<=0) continue;
                if (($regla['naturaleza']??'DESCUENTO')==='BONIFICACION') $grupo['bonificaciones'] += $importe;
                else $grupo['descuentos'] += $importe;
                $grupo['reglasAplicadas'][]=['id_regla'=>(int)$regla['id_regla'],'nombre'=>$regla['nombre'],'naturaleza'=>$regla['naturaleza']??'DESCUENTO','evento'=>$regla['evento'],'cantidad'=>(float)$cantidad,'importe'=>$importe];
            }
            $grupo['neto'] = max(0,round($grupo['mensual']+$grupo['pagoHora']+$grupo['pagoSesion']+$grupo['bonificaciones']-$grupo['descuentos'],2));
        }
        unset($grupo);
        return array_values($grupos);
    }
}
