<?php
class PlanillaModel
{
    private $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function datos(): array
    {
        return ['personas' => $this->db->query("SELECT p.id_persona,p.nombres,p.apellidos,GROUP_CONCAT(pp.tipo) perfiles FROM personas p JOIN persona_perfiles pp ON pp.id_persona=p.id_persona AND pp.estado='ACTIVO' LEFT JOIN vinculos_laborales v ON v.id_persona=p.id_persona AND v.estado='ACTIVO' WHERE p.estado='ACTIVO' AND v.id_vinculo IS NULL AND pp.tipo IN('DOCENTE','ADMINISTRATIVO') GROUP BY p.id_persona ORDER BY p.apellidos")->fetchAll(), 'vinculos' => $this->db->query("SELECT v.*,CONCAT(p.apellidos,', ',p.nombres) persona,COALESCE(c.nombre,'Sin cargo') cargo FROM vinculos_laborales v JOIN personas p ON p.id_persona=v.id_persona LEFT JOIN cargos c ON c.id_cargo=v.id_cargo ORDER BY v.estado='ACTIVO' DESC,p.apellidos,v.fecha_inicio DESC")->fetchAll(), 'periodos' => $this->db->query("SELECT * FROM periodos_pago ORDER BY fecha_inicio DESC")->fetchAll(), 'reglas' => $this->db->query("SELECT * FROM reglas_descuento WHERE estado='ACTIVO' ORDER BY evento,nombre")->fetchAll()];
    }
    public function personasParaVinculo(): array
    {
        return $this->db->query("SELECT p.id_persona,p.nombres,p.apellidos,p.codigo_trabajador,GROUP_CONCAT(pp.tipo) perfiles FROM personas p JOIN persona_perfiles pp ON pp.id_persona=p.id_persona AND pp.estado='ACTIVO' WHERE p.estado='ACTIVO' AND pp.tipo IN('DOCENTE','ADMINISTRATIVO') GROUP BY p.id_persona ORDER BY p.apellidos,p.nombres")->fetchAll();
    }
    public function vinculo(array $d): void
    {
        $idPersona = (int)($d['id_persona'] ?? 0);
        $tipo = $d['tipo'] ?? '';
        $inicio = $d['fecha_inicio'] ?? '';
        $cargoNombre = trim($d['cargo'] ?? '');
        $mensual = round((float)($d['monto_mensual'] ?? 0), 2);
        $hora = round((float)($d['monto_hora'] ?? 0), 2);
        $sesion = round((float)($d['monto_sesion'] ?? 0), 2);
        $baseAnterior = round((float)($d['monto_base'] ?? 0), 2);
        $modalidadAnterior = $d['modalidad_pago'] ?? 'MENSUAL';
        if ($mensual <= 0 && $hora <= 0 && $sesion <= 0 && $baseAnterior > 0) {
            if ($modalidadAnterior === 'HORA') $hora = $baseAnterior;
            elseif ($modalidadAnterior === 'SESION') $sesion = $baseAnterior;
            else $mensual = $baseAnterior;
        }
        $dias = array_values(array_unique(array_filter(array_map('intval', $d['dias_laborables'] ?? []), function ($dia) {
            return $dia >= 1 && $dia <= 7;
        })));
        if (!$dias) $dias = [1, 2, 3, 4, 5];
        sort($dias);
        if (!$idPersona || !in_array($tipo, ['DOCENTE', 'ADMINISTRATIVO'], true) || !$inicio || !$cargoNombre) throw new InvalidArgumentException('Complete trabajador, tipo, cargo y fecha de inicio.');
        if ($mensual < 0 || $hora < 0 || $sesion < 0 || ($mensual + $hora + $sesion) <= 0) throw new InvalidArgumentException('Indique al menos un componente de pago mayor que cero.');
        $this->db->beginTransaction();
        try {
            $cargo = $this->db->prepare('SELECT id_cargo FROM cargos WHERE nombre=? LIMIT 1');
            $cargo->execute([$cargoNombre]);
            $idCargo = (int)$cargo->fetchColumn();
            if (!$idCargo) {
                $this->db->prepare("INSERT INTO cargos(nombre,estado) VALUES(?,'ACTIVO')")->execute([$cargoNombre]);
                $idCargo = (int)$this->db->lastInsertId();
            }
            $codigo = $this->db->prepare('SELECT codigo_trabajador FROM personas WHERE id_persona=? FOR UPDATE');
            $codigo->execute([$idPersona]);
            if (!$codigo->fetchColumn()) {
                $anio = substr($inicio, 0, 4);
                $ultimo = $this->db->prepare("SELECT MAX(CAST(RIGHT(codigo_trabajador,4) AS UNSIGNED)) FROM personas WHERE codigo_trabajador LIKE ?");
                $ultimo->execute(['CS-' . $anio . '-%']);
                $nuevo = 'CS-' . $anio . '-' . str_pad((string)((int)$ultimo->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
                $this->db->prepare('UPDATE personas SET codigo_trabajador=? WHERE id_persona=?')->execute([$nuevo, $idPersona]);
            }
            $finAnterior = date('Y-m-d', strtotime($inicio . ' -1 day'));
            $this->db->prepare("UPDATE vinculos_laborales SET estado='FINALIZADO',fecha_fin=COALESCE(fecha_fin,?) WHERE id_persona=? AND estado='ACTIVO'")->execute([$finAnterior, $idPersona]);
            $otro = $tipo === 'DOCENTE' ? 'ADMINISTRATIVO' : 'DOCENTE';
            $this->db->prepare('UPDATE persona_perfiles SET estado="INACTIVO" WHERE id_persona=? AND tipo=?')->execute([$idPersona, $otro]);
            $this->db->prepare('INSERT INTO persona_perfiles(id_persona,tipo,estado,creado_en) VALUES(?,?,"ACTIVO",NOW()) ON DUPLICATE KEY UPDATE estado="ACTIVO"')->execute([$idPersona, $tipo]);
            $cantidadComponentes = (int)($mensual > 0) + (int)($hora > 0) + (int)($sesion > 0);
            $modalidad = $cantidadComponentes > 1 ? 'MIXTO' : ($mensual > 0 ? 'MENSUAL' : ($hora > 0 ? 'HORA' : 'SESION'));
            $montoBase = $mensual > 0 ? $mensual : ($hora > 0 ? $hora : $sesion);
            $s = $this->db->prepare("INSERT INTO vinculos_laborales(id_persona,id_cargo,tipo,modalidad_pago,monto_base,monto_mensual,monto_hora,monto_sesion,dias_laborables,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,?,?,?,?,?,?,?,?,'ACTIVO')");
            $s->execute([$idPersona, $idCargo, $tipo, $modalidad, $montoBase, $mensual, $hora, $sesion, implode(',', $dias), $inicio, $d['fecha_fin'] ?: null]);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function buscarVinculo(int $id): ?array
    {
        $consulta = $this->db->prepare("SELECT v.*,CONCAT(p.apellidos,', ',p.nombres) persona,COALESCE(c.nombre,'Sin cargo') cargo FROM vinculos_laborales v JOIN personas p ON p.id_persona=v.id_persona LEFT JOIN cargos c ON c.id_cargo=v.id_cargo WHERE v.id_vinculo=?");
        $consulta->execute([$id]);
        $vinculo = $consulta->fetch();
        return $vinculo ?: null;
    }

    public function versionarVinculo(array $d, int $usuario): int
    {
        $id = (int)($d['id_vinculo'] ?? 0);
        $vigencia = trim($d['vigencia_desde'] ?? '');
        $motivo = trim($d['motivo_cambio'] ?? '');
        $mensual = round((float)($d['monto_mensual'] ?? 0), 2);
        $hora = round((float)($d['monto_hora'] ?? 0), 2);
        $sesion = round((float)($d['monto_sesion'] ?? 0), 2);
        $dias = array_values(array_unique(array_filter(array_map('intval', $d['dias_laborables'] ?? []), function ($dia) {
            return $dia >= 1 && $dia <= 7;
        })));
        sort($dias);
        $fecha = DateTime::createFromFormat('Y-m-d', $vigencia);

        if (!$id || !$fecha || $fecha->format('Y-m-d') !== $vigencia) throw new InvalidArgumentException('Seleccione una fecha de vigencia válida.');
        if ($vigencia > date('Y-m-d')) throw new InvalidArgumentException('La fecha de vigencia no puede ser futura.');
        if (strlen($motivo) < 3 || strlen($motivo) > 250) throw new InvalidArgumentException('Indique un motivo de entre 3 y 250 caracteres.');
        if (!$dias) throw new InvalidArgumentException('Seleccione al menos un día laborable.');
        if ($mensual < 0 || $hora < 0 || $sesion < 0 || ($mensual + $hora + $sesion) <= 0) throw new InvalidArgumentException('Indique al menos un componente de pago mayor que cero.');

        $cantidadComponentes = (int)($mensual > 0) + (int)($hora > 0) + (int)($sesion > 0);
        $modalidad = $cantidadComponentes > 1 ? 'MIXTO' : ($mensual > 0 ? 'MENSUAL' : ($hora > 0 ? 'HORA' : 'SESION'));
        $montoBase = $mensual > 0 ? $mensual : ($hora > 0 ? $hora : $sesion);

        $this->db->beginTransaction();
        try {
            $consulta = $this->db->prepare("SELECT * FROM vinculos_laborales WHERE id_vinculo=? AND estado='ACTIVO' FOR UPDATE");
            $consulta->execute([$id]);
            $anterior = $consulta->fetch();
            if (!$anterior) throw new DomainException('El vínculo ya no está activo o no existe.');
            if ($vigencia < $anterior['fecha_inicio']) throw new DomainException('La vigencia no puede ser anterior al inicio del vínculo activo.');
            if ($anterior['fecha_fin'] && $vigencia > $anterior['fecha_fin']) throw new DomainException('La vigencia supera la fecha final del vínculo.');

            if ($vigencia === $anterior['fecha_inicio']) {
                $usado = $this->db->prepare('SELECT COUNT(*) FROM planilla_detalles WHERE id_vinculo=?');
                $usado->execute([$id]);
                if ((int)$usado->fetchColumn() > 0) throw new DomainException('Este vínculo ya tiene planillas. Use una fecha posterior para conservar el historial.');
                $this->db->prepare('UPDATE vinculos_laborales SET modalidad_pago=?,monto_base=?,monto_mensual=?,monto_hora=?,monto_sesion=?,dias_laborables=?,motivo_cambio=?,registrado_por=?,registrado_en=NOW() WHERE id_vinculo=?')->execute([$modalidad,$montoBase,$mensual,$hora,$sesion,implode(',',$dias),$motivo,$usuario ?: null,$id]);
                $this->db->commit();
                return $id;
            }

            $finAnterior = date('Y-m-d', strtotime($vigencia . ' -1 day'));
            $this->db->prepare("UPDATE vinculos_laborales SET fecha_fin=?,estado='FINALIZADO' WHERE id_vinculo=?")->execute([$finAnterior,$id]);
            $insertar = $this->db->prepare("INSERT INTO vinculos_laborales(id_persona,id_vinculo_anterior,id_area,id_cargo,tipo,modalidad_pago,monto_base,monto_mensual,monto_hora,monto_sesion,dias_laborables,fecha_inicio,fecha_fin,estado,motivo_cambio,registrado_por) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,'ACTIVO',?,?)");
            $insertar->execute([$anterior['id_persona'],$id,$anterior['id_area'],$anterior['id_cargo'],$anterior['tipo'],$modalidad,$montoBase,$mensual,$hora,$sesion,implode(',',$dias),$vigencia,$anterior['fecha_fin'],$motivo,$usuario ?: null]);
            $nuevoId = (int)$this->db->lastInsertId();
            $this->db->prepare("UPDATE carga_academica SET id_vinculo=? WHERE id_vinculo=? AND estado='ACTIVO'")->execute([$nuevoId,$id]);
            $this->db->commit();
            return $nuevoId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    public function periodo(array $d): void
    {
        $s = $this->db->prepare("INSERT INTO periodos_pago(nombre,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,'ABIERTO')");
        $s->execute([trim($d['nombre']), $d['fecha_inicio'], $d['fecha_fin']]);
    }
    public function regla(array $d): void
    {
        $s = $this->db->prepare("INSERT INTO reglas_descuento(nombre,evento,formula,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,? ,?,'ACTIVO')");
        $s->execute([trim($d['nombre']), $d['evento'], json_encode(['monto_unitario' => (float)$d['monto_unitario']]), $d['fecha_inicio'], $d['fecha_fin'] ?: null]);
    }
    private function concepto(string $evento): int
    {
        $tipo = $evento === 'TARDANZA' ? 'TARDANZA' : ($evento === 'FALTA' ? 'FALTA' : 'HORA');
        $s = $this->db->prepare("SELECT id_concepto_pago FROM conceptos_pago WHERE tipo=? AND naturaleza='DESCUENTO' LIMIT 1");
        $s->execute([$tipo]);
        $id = $s->fetchColumn();
        if ($id) return (int)$id;
        $this->db->prepare("INSERT INTO conceptos_pago(nombre,naturaleza,tipo,estado) VALUES(?,'DESCUENTO',?,'ACTIVO')")->execute(['Descuento por ' . strtolower(str_replace('_', ' ', $evento)), $tipo]);
        return (int)$this->db->lastInsertId();
    }
    public function calcular(int $id): int
    {
        $this->db->beginTransaction();
        try {
            $per = $this->db->prepare("SELECT * FROM periodos_pago WHERE id_periodo_pago=?");
            $per->execute([$id]);
            $p = $per->fetch();
            $q = $this->db->prepare("SELECT v.id_vinculo,v.id_persona,v.monto_base FROM vinculos_laborales v WHERE v.estado='ACTIVO' AND v.fecha_inicio<=? AND (v.fecha_fin IS NULL OR v.fecha_fin>=?)");
            $q->execute([$p['fecha_fin'], $p['fecha_inicio']]);
            $i = $this->db->prepare("INSERT INTO planilla_detalles(id_periodo_pago,id_vinculo,monto_base,bonificaciones,descuentos,monto_pagar,estado) VALUES(?,?,?,0,0,?,'CALCULADO') ON DUPLICATE KEY UPDATE monto_base=VALUES(monto_base),bonificaciones=0,descuentos=0,monto_pagar=VALUES(monto_pagar),calculado_en=NOW()");
            $n = 0;
            foreach ($q->fetchAll() as $v) {
                $i->execute([$id, $v['id_vinculo'], $v['monto_base'], $v['monto_base']]);
                $n++;
            }
            $this->db->prepare("DELETE pc FROM planilla_detalle_conceptos pc JOIN planilla_detalles pd ON pd.id_planilla_detalle=pc.id_planilla_detalle WHERE pd.id_periodo_pago=?")->execute([$id]);
            $r = $this->db->prepare("SELECT * FROM reglas_descuento WHERE estado='ACTIVO' AND fecha_inicio<=? AND (fecha_fin IS NULL OR fecha_fin>=?)");
            $r->execute([$p['fecha_fin'], $p['fecha_inicio']]);
            $detalle = $this->db->prepare("SELECT pd.id_planilla_detalle,pd.monto_base,v.id_persona,v.dias_laborables FROM planilla_detalles pd JOIN vinculos_laborales v ON v.id_vinculo=pd.id_vinculo WHERE pd.id_periodo_pago=?");
            $detalle->execute([$id]);
            foreach ($detalle->fetchAll() as $d) {
                $des = 0;
                foreach ($r->fetchAll() as $reg) {
                    $f = json_decode($reg['formula'], true);
                    $campo = $reg['evento'] === 'TARDANZA' ? "COALESCE(SUM(CASE WHEN NOT EXISTS(SELECT 1 FROM incidencias_asistencia ia WHERE ia.id_asistencia=a.id_asistencia AND ia.tipo='JUSTIFICACION_TARDANZA' AND ia.estado='APROBADA') THEN a.minutos_tardanza ELSE 0 END),0)" : ($reg['evento'] === 'FALTA' ? "COALESCE(SUM(a.estado='FALTA'),0)" : '0');
                    $a = $this->db->prepare("SELECT $campo FROM asistencias_diarias a WHERE a.id_persona=? AND a.fecha BETWEEN ? AND ? AND (a.id_carga IS NOT NULL OR (a.id_jornada IS NOT NULL AND FIND_IN_SET(CAST(WEEKDAY(a.fecha)+1 AS CHAR),?)>0))");
                    $a->execute([$d['id_persona'], $p['fecha_inicio'], $p['fecha_fin'], $d['dias_laborables'] ?: '1,2,3,4,5']);
                    $cant = (float)$a->fetchColumn();
                    $imp = round($cant * (float)($f['monto_unitario'] ?? 0), 2);
                    if ($imp > 0) {
                        $this->db->prepare("INSERT INTO planilla_detalle_conceptos(id_planilla_detalle,id_concepto_pago,id_regla,cantidad,importe,explicacion) VALUES(?,?,?,?,?,?)")->execute([$d['id_planilla_detalle'], $this->concepto($reg['evento']), $reg['id_regla'], $cant, -$imp, json_encode(['evento' => $reg['evento'], 'monto_unitario' => $f['monto_unitario']])]);
                        $des += $imp;
                    }
                }
                $this->db->prepare("UPDATE planilla_detalles SET descuentos=?,monto_pagar=? WHERE id_planilla_detalle=?")->execute([$des, max(0, (float)$d['monto_base'] - $des), $d['id_planilla_detalle']]);
            }
            $this->db->prepare("UPDATE periodos_pago SET estado='CALCULADO' WHERE id_periodo_pago=?")->execute([$id]);
            $this->db->commit();
            return $n;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
