<?php

class MovimientoFinancieroModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function categorias(bool $todas = false): array
    {
        $sql = 'SELECT * FROM categorias_movimiento_financiero';
        if (!$todas) $sql .= " WHERE estado='ACTIVO'";
        $sql .= " ORDER BY tipo,clasificacion,nombre";
        return $this->db->query($sql)->fetchAll();
    }

    public function cajaAbiertaHoy(): ?array
    {
        $caja = $this->db->query("SELECT id_apertura,fecha,abierto_en FROM aperturas_caja WHERE estado='ABIERTA' ORDER BY id_apertura DESC LIMIT 1")->fetch();
        return $caja && $caja['fecha'] === date('Y-m-d') ? $caja : null;
    }

    public function listar(array $filtros): array
    {
        $sql = "SELECT m.*,c.nombre AS categoria,c.clasificacion,
            COALESCE(NULLIF(CONCAT(COALESCE(p.apellidos,''),', ',COALESCE(p.nombres,'')),', '),u.usuario,'Sistema') AS responsable
            FROM movimientos_financieros m
            INNER JOIN categorias_movimiento_financiero c ON c.id_categoria=m.id_categoria
            LEFT JOIN usuarios u ON u.id_usuario=m.registrado_por
            LEFT JOIN personas p ON p.id_persona=u.id_persona
            WHERE m.fecha_movimiento BETWEEN :desde AND :hasta";
        $parametros = [':desde' => $filtros['desde'] . ' 00:00:00', ':hasta' => $filtros['hasta'] . ' 23:59:59'];
        if (in_array($filtros['tipo'] ?? '', ['INGRESO','EGRESO'], true)) {
            $sql .= ' AND m.tipo=:tipo';
            $parametros[':tipo'] = $filtros['tipo'];
        }
        if ((int)($filtros['categoria'] ?? 0) > 0) {
            $sql .= ' AND m.id_categoria=:categoria';
            $parametros[':categoria'] = (int)$filtros['categoria'];
        }
        if (in_array($filtros['estado'] ?? '', ['REGISTRADO','ANULADO'], true)) {
            $sql .= ' AND m.estado=:estado';
            $parametros[':estado'] = $filtros['estado'];
        }
        $sql .= ' ORDER BY m.fecha_movimiento DESC,m.id_movimiento DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function resumen(array $movimientos): array
    {
        $ingresos = $egresos = 0.0;
        foreach ($movimientos as $movimiento) {
            if ($movimiento['estado'] !== 'REGISTRADO') continue;
            if ($movimiento['tipo'] === 'INGRESO') $ingresos += (float)$movimiento['monto'];
            else $egresos += (float)$movimiento['monto'];
        }
        return ['ingresos' => $ingresos, 'egresos' => $egresos, 'balance' => $ingresos - $egresos];
    }

    public function guardar(array $datos, int $usuario): int
    {
        $tipo = $datos['tipo'] ?? '';
        $categoria = (int)($datos['id_categoria'] ?? 0);
        $fecha = str_replace('T',' ',trim((string)($datos['fecha_movimiento'] ?? '')));
        $monto = round((float)($datos['monto'] ?? 0), 2);
        $medio = $datos['medio'] ?? '';
        $descripcion = trim((string)($datos['descripcion'] ?? ''));
        $referencia = trim((string)($datos['referencia'] ?? ''));
        $evidencia = trim((string)($datos['evidencia_archivo'] ?? ''));
        $permitidos = ['EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO'];
        if (!in_array($tipo, ['INGRESO','EGRESO'], true) || !$categoria || $monto <= 0 || !in_array($medio, $permitidos, true) || strlen($descripcion) < 3) {
            throw new InvalidArgumentException('Complete correctamente el tipo, categoría, monto, medio y descripción.');
        }
        $timestamp = strtotime($fecha);
        if (!$timestamp || date('Y-m-d H:i', $timestamp) !== $fecha) throw new InvalidArgumentException('Indique una fecha y hora válidas.');
        if (date('Y-m-d', $timestamp) > date('Y-m-d')) throw new RuntimeException('No puede registrar movimientos con una fecha futura.');
        if ($medio !== 'EFECTIVO' && $referencia === '' && $evidencia === '') {
            throw new RuntimeException('Los movimientos por medios digitales requieren una referencia o evidencia.');
        }
        $consultaCategoria = $this->db->prepare("SELECT tipo FROM categorias_movimiento_financiero WHERE id_categoria=:id AND estado='ACTIVO'");
        $consultaCategoria->execute([':id' => $categoria]);
        if ($consultaCategoria->fetchColumn() !== $tipo) throw new RuntimeException('La categoría seleccionada no corresponde al tipo de movimiento.');

        $this->db->beginTransaction();
        $bloqueo = false;
        try {
            if (date('Y-m-d', $timestamp) !== date('Y-m-d')) {
                throw new RuntimeException('Los movimientos del turno solo pueden registrarse con la fecha actual.');
            }
            $caja = $this->db->query("SELECT id_apertura,fecha FROM aperturas_caja WHERE estado='ABIERTA' ORDER BY id_apertura DESC LIMIT 1 FOR UPDATE")->fetch();
            if (!$caja) throw new RuntimeException('Debe abrir un turno de caja antes de registrar ingresos o egresos.');
            if ($caja['fecha'] !== date('Y-m-d')) {
                throw new RuntimeException('La caja del ' . $caja['fecha'] . ' sigue abierta. Debe cerrarla y abrir una caja para hoy.');
            }
            $idApertura = (int)$caja['id_apertura'];
            $anio = date('Y', $timestamp);
            $bloqueo = (bool)$this->db->query("SELECT GET_LOCK('movimiento_financiero_{$anio}',5)")->fetchColumn();
            if (!$bloqueo) throw new RuntimeException('No se pudo reservar el correlativo del movimiento. Intente nuevamente.');
            $siguiente = (int)$this->db->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo,'-',-1) AS UNSIGNED)),0)+1 FROM movimientos_financieros WHERE codigo LIKE 'MF-{$anio}-%'")->fetchColumn();
            $codigo = 'MF-' . $anio . '-' . str_pad((string)$siguiente, 6, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("INSERT INTO movimientos_financieros(codigo,tipo,id_categoria,fecha_movimiento,monto,medio,tercero,documento,referencia,descripcion,evidencia_archivo,id_apertura,registrado_por,estado) VALUES(:codigo,:tipo,:categoria,:fecha,:monto,:medio,:tercero,:documento,:referencia,:descripcion,:evidencia,:apertura,:usuario,'REGISTRADO')");
            $stmt->execute([
                ':codigo'=>$codigo, ':tipo'=>$tipo, ':categoria'=>$categoria, ':fecha'=>date('Y-m-d H:i:s',$timestamp), ':monto'=>$monto, ':medio'=>$medio,
                ':tercero'=>trim((string)($datos['tercero'] ?? '')) ?: null, ':documento'=>trim((string)($datos['documento'] ?? '')) ?: null,
                ':referencia'=>$referencia !== '' ? $referencia : null, ':descripcion'=>$descripcion,
                ':evidencia'=>$evidencia !== '' ? $evidencia : null, ':apertura'=>$idApertura, ':usuario'=>$usuario,
            ]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            $this->db->query("SELECT RELEASE_LOCK('movimiento_financiero_{$anio}')");
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ($bloqueo) $this->db->query("SELECT RELEASE_LOCK('movimiento_financiero_" . (isset($anio) ? $anio : date('Y')) . "')");
            throw $e;
        }
    }

    public function anular(int $id, string $motivo, int $usuario): void
    {
        $motivo = trim($motivo);
        if ($id < 1 || strlen($motivo) < 3) throw new InvalidArgumentException('Indique el motivo de anulación.');
        $stmt = $this->db->prepare("UPDATE movimientos_financieros SET estado='ANULADO',anulado_por=:usuario,anulado_en=NOW(),motivo_anulacion=:motivo WHERE id_movimiento=:id AND estado='REGISTRADO'");
        $stmt->execute([':usuario'=>$usuario, ':motivo'=>$motivo, ':id'=>$id]);
        if (!$stmt->rowCount()) throw new RuntimeException('El movimiento ya fue anulado o no existe.');
    }

    public function guardarCategoria(array $datos): int
    {
        $nombre = trim((string)($datos['nombre'] ?? ''));
        $tipo = $datos['tipo'] ?? '';
        $clasificacion = $datos['clasificacion'] ?? '';
        if (strlen($nombre) < 3 || !in_array($tipo,['INGRESO','EGRESO'],true) || !in_array($clasificacion,['OPERATIVO','FINANCIAMIENTO','CAPITAL'],true)) throw new InvalidArgumentException('Complete correctamente la categoría.');
        $stmt = $this->db->prepare("INSERT INTO categorias_movimiento_financiero(nombre,tipo,clasificacion,estado) VALUES(:nombre,:tipo,:clasificacion,'ACTIVO')");
        $stmt->execute([':nombre'=>$nombre, ':tipo'=>$tipo, ':clasificacion'=>$clasificacion]);
        return (int)$this->db->lastInsertId();
    }

    public function cambiarEstadoCategoria(int $id, string $estado): void
    {
        if ($id < 1 || !in_array($estado,['ACTIVO','INACTIVO'],true)) throw new InvalidArgumentException('Categoría no válida.');
        $stmt = $this->db->prepare('UPDATE categorias_movimiento_financiero SET estado=:estado WHERE id_categoria=:id');
        $stmt->execute([':estado'=>$estado, ':id'=>$id]);
    }

    public function evidencia(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT evidencia_archivo FROM movimientos_financieros WHERE id_movimiento=:id');
        $stmt->execute([':id'=>$id]);
        $archivo = $stmt->fetchColumn();
        return $archivo ? basename((string)$archivo) : null;
    }

    public function reporteConsolidado(string $desde, string $hasta): array
    {
        $rango = [':desde'=>$desde . ' 00:00:00', ':hasta'=>$hasta . ' 23:59:59'];
        $cobros = $this->db->prepare("SELECT pm.medio,COUNT(DISTINCT p.id_pago) operaciones,SUM(pm.importe_aplicado) total FROM pagos_alumno p INNER JOIN pagos_alumno_medios pm ON pm.id_pago=p.id_pago WHERE p.estado='REGISTRADO' AND p.fecha_pago BETWEEN :desde AND :hasta GROUP BY pm.medio ORDER BY total DESC");
        $cobros->execute($rango);
        $manuales = $this->db->prepare("SELECT m.tipo,m.medio,c.nombre categoria,c.clasificacion,COUNT(*) operaciones,SUM(m.monto) total FROM movimientos_financieros m INNER JOIN categorias_movimiento_financiero c ON c.id_categoria=m.id_categoria WHERE m.estado='REGISTRADO' AND m.fecha_movimiento BETWEEN :desde AND :hasta GROUP BY m.tipo,m.medio,c.id_categoria,c.nombre,c.clasificacion ORDER BY m.tipo,c.clasificacion,c.nombre");
        $manuales->execute($rango);
        $planilla = [];
        if ($this->db->query("SHOW TABLES LIKE 'pagos_planilla'")->fetchColumn()) {
            $stmt = $this->db->prepare("SELECT medio_pago medio,COUNT(*) operaciones,SUM(importe) total FROM pagos_planilla WHERE estado='REGISTRADO' AND pagado_en BETWEEN :desde AND :hasta GROUP BY medio_pago ORDER BY total DESC");
            $stmt->execute($rango);
            $planilla = $stmt->fetchAll();
        }
        $filasCobros = $cobros->fetchAll();
        $filasManuales = $manuales->fetchAll();
        $totales = ['cobros'=>0.0,'otros_ingresos'=>0.0,'planilla'=>0.0,'otros_egresos'=>0.0,'ingresos'=>0.0,'egresos'=>0.0,'flujo_neto'=>0.0,'resultado_operativo'=>0.0,'financiamiento_neto'=>0.0,'capital_neto'=>0.0];
        foreach ($filasCobros as $fila) $totales['cobros'] += (float)$fila['total'];
        foreach ($planilla as $fila) $totales['planilla'] += (float)$fila['total'];
        foreach ($filasManuales as $fila) {
            $importe = (float)$fila['total'];
            if ($fila['tipo']==='INGRESO') $totales['otros_ingresos'] += $importe; else $totales['otros_egresos'] += $importe;
            $signo = $fila['tipo']==='INGRESO' ? 1 : -1;
            if ($fila['clasificacion']==='OPERATIVO') $totales['resultado_operativo'] += $signo*$importe;
            elseif ($fila['clasificacion']==='FINANCIAMIENTO') $totales['financiamiento_neto'] += $signo*$importe;
            else $totales['capital_neto'] += $signo*$importe;
        }
        $totales['resultado_operativo'] += $totales['cobros'] - $totales['planilla'];
        $totales['ingresos'] = $totales['cobros'] + $totales['otros_ingresos'];
        $totales['egresos'] = $totales['planilla'] + $totales['otros_egresos'];
        $totales['flujo_neto'] = $totales['ingresos'] - $totales['egresos'];
        $porMedio = [];
        foreach ($filasCobros as $fila) { $porMedio[$fila['medio']]['ingreso'] = ($porMedio[$fila['medio']]['ingreso'] ?? 0) + (float)$fila['total']; }
        foreach ($filasManuales as $fila) { $clave=$fila['tipo']==='INGRESO'?'ingreso':'egreso'; $porMedio[$fila['medio']][$clave]=($porMedio[$fila['medio']][$clave]??0)+(float)$fila['total']; }
        foreach ($planilla as $fila) { $porMedio[$fila['medio']]['egreso']=($porMedio[$fila['medio']]['egreso']??0)+(float)$fila['total']; }
        foreach ($porMedio as $medio=>&$valores) { $valores=['medio'=>$medio,'ingreso'=>$valores['ingreso']??0,'egreso'=>$valores['egreso']??0]; $valores['neto']=$valores['ingreso']-$valores['egreso']; }
        unset($valores);
        $cierres=$this->db->prepare("SELECT c.id_cierre,c.fecha,c.diferencia,c.estado_revision,c.cerrado_en,a.id_apertura,COALESCE(NULLIF(CONCAT(COALESCE(p.apellidos,''),', ',COALESCE(p.nombres,'')),', '),u.usuario,'Sistema') responsable,(SELECT COUNT(*) FROM rectificaciones_caja r WHERE r.id_cierre=c.id_cierre) revisiones FROM cierres_caja c INNER JOIN aperturas_caja a ON a.id_apertura=c.id_apertura LEFT JOIN usuarios u ON u.id_usuario=c.cerrado_por LEFT JOIN personas p ON p.id_persona=u.id_persona WHERE c.fecha BETWEEN :desdeFecha AND :hastaFecha ORDER BY c.cerrado_en DESC");
        $cierres->execute([':desdeFecha'=>$desde,':hastaFecha'=>$hasta]);
        $filasCierres=$cierres->fetchAll();
        $controlCaja=['cierres'=>count($filasCierres),'con_diferencia'=>0,'pendientes'=>0,'diferencia_neta'=>0.0,'revisiones'=>0];
        foreach($filasCierres as $fila){if(abs((float)$fila['diferencia'])>0.009)$controlCaja['con_diferencia']++;if($fila['estado_revision']==='PENDIENTE')$controlCaja['pendientes']++;$controlCaja['diferencia_neta']+=(float)$fila['diferencia'];$controlCaja['revisiones']+=(int)$fila['revisiones'];}
        $controlCaja['diferencia_neta']=round($controlCaja['diferencia_neta'],2);
        return ['totales'=>$totales,'cobros'=>$filasCobros,'manuales'=>$filasManuales,'planilla'=>$planilla,'por_medio'=>array_values($porMedio),'cierres'=>$filasCierres,'control_caja'=>$controlCaja];
    }
}
