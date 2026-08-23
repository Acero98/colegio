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
            'tarifas' => $this->db->query("SELECT t.*,c.nombre AS concepto,g.nombre AS grado,p.nombre AS periodo FROM tarifas_cobro t INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=t.id_concepto_cobro INNER JOIN periodos_academicos p ON p.id_periodo_academico=t.id_periodo_academico LEFT JOIN grados g ON g.id_grado=t.id_grado WHERE t.estado='ACTIVO' ORDER BY p.fecha_inicio DESC,c.nombre,g.orden")->fetchAll(),
            'secciones' => $this->db->query("SELECT s.id_seccion,CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) AS nombre FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE s.estado='ACTIVO' ORDER BY n.orden,g.orden,s.nombre")->fetchAll(),
            'alumnos' => $this->db->query("SELECT DISTINCT p.id_persona,p.apellidos AS orden_apellidos,p.nombres AS orden_nombres,CONCAT(p.apellidos,', ',p.nombres,' · ',m.codigo_alumno) AS nombre FROM matriculas m INNER JOIN personas p ON p.id_persona=m.id_persona WHERE m.estado='ACTIVO' ORDER BY orden_apellidos,orden_nombres")->fetchAll()
        ];
    }

    public function tarifas(): array
    {
        return $this->db->query("SELECT t.*,c.nombre AS concepto,c.tipo,g.nombre AS grado,p.nombre AS periodo FROM tarifas_cobro t INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=t.id_concepto_cobro INNER JOIN periodos_academicos p ON p.id_periodo_academico=t.id_periodo_academico LEFT JOIN grados g ON g.id_grado=t.id_grado ORDER BY t.estado='ACTIVO' DESC,p.fecha_inicio DESC,c.nombre,g.orden")->fetchAll();
    }

    public function conceptos(): array
    {
        return $this->db->query("SELECT * FROM conceptos_cobro ORDER BY estado='ACTIVO' DESC,tipo,nombre")->fetchAll();
    }

    public function cambiarEstadoTarifa(int $idTarifa, string $estado): void
    {
        if ($idTarifa < 1 || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            throw new RuntimeException('Tarifa no válida.');
        }
        $stmt = $this->db->prepare('UPDATE tarifas_cobro SET estado=:estado WHERE id_tarifa=:id');
        $stmt->execute([':estado' => $estado, ':id' => $idTarifa]);
    }

    public function cambiarEstadoConcepto(int $idConcepto, string $estado): void
    {
        if ($idConcepto < 1 || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            throw new RuntimeException('Concepto no válido.');
        }
        $stmt = $this->db->prepare('UPDATE conceptos_cobro SET estado=:estado WHERE id_concepto_cobro=:id');
        $stmt->execute([':estado' => $estado, ':id' => $idConcepto]);
    }

    public function actualizarVencidas(): void
    {
        $this->db->query("UPDATE cuentas_cobrar SET estado='VENCIDA' WHERE estado IN ('PENDIENTE','PARCIAL') AND saldo>0 AND fecha_vencimiento IS NOT NULL AND fecha_vencimiento<CURDATE()");
    }

    public function listarCuentas(string $mes = '', string $estado = '', string $buscar = ''): array
    {
        $sql = "SELECT cc.*, (cc.importe_original+cc.recargo-cc.descuento) AS importe_final, m.id_persona, CASE WHEN cc.estado IN ('PENDIENTE','PARCIAL') AND cc.saldo>0 AND cc.fecha_vencimiento IS NOT NULL AND cc.fecha_vencimiento<CURDATE() THEN 'VENCIDA' ELSE cc.estado END AS estado_visual, c.nombre AS concepto, c.tipo, m.codigo_alumno, CONCAT(al.apellidos, ', ', al.nombres) AS alumno,
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
        $where = [];
        if ($mes !== '') {
            $where[] = 'cc.periodo_cobro=:mes';
            $params[':mes'] = $mes . '-01';
        }
        if ($estado === 'VENCIDA') {
            $where[] = "cc.estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND cc.saldo>0 AND cc.fecha_vencimiento IS NOT NULL AND cc.fecha_vencimiento<CURDATE()";
        } elseif ($estado === 'PROXIMO') {
            $where[] = "cc.estado IN ('PENDIENTE','PARCIAL') AND cc.saldo>0 AND cc.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY)";
        } elseif ($estado === 'MORA_1_30') {
            $where[] = "cc.estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND cc.saldo>0 AND DATEDIFF(CURDATE(),cc.fecha_vencimiento) BETWEEN 1 AND 30";
        } elseif ($estado === 'MORA_31_60') {
            $where[] = "cc.estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND cc.saldo>0 AND DATEDIFF(CURDATE(),cc.fecha_vencimiento) BETWEEN 31 AND 60";
        } elseif ($estado === 'MORA_60') {
            $where[] = "cc.estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND cc.saldo>0 AND DATEDIFF(CURDATE(),cc.fecha_vencimiento)>60";
        } elseif ($estado !== '' && in_array($estado, ['PENDIENTE', 'PARCIAL', 'PAGADA', 'ANULADA'], true)) {
            $where[] = 'cc.estado=:estado';
            $params[':estado'] = $estado;
        }
        if ($buscar !== '') {
            $where[] = '(al.nombres LIKE :buscar OR al.apellidos LIKE :buscar OR m.codigo_alumno LIKE :buscar OR al.numero_documento LIKE :buscar)';
            $params[':buscar'] = '%' . $buscar . '%';
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY cc.estado IN (\'PENDIENTE\',\'PARCIAL\',\'VENCIDA\') DESC, cc.fecha_vencimiento, al.apellidos, al.nombres';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function alertasCobro(): array
    {
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN estado IN ('PENDIENTE','PARCIAL') AND saldo>0 AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY) THEN 1 ELSE 0 END),0) AS proximos,
            COALESCE(SUM(CASE WHEN estado='PARCIAL' AND saldo>0 THEN 1 ELSE 0 END),0) AS parciales,
            COALESCE(SUM(CASE WHEN estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND saldo>0 AND DATEDIFF(CURDATE(),fecha_vencimiento) BETWEEN 1 AND 30 THEN 1 ELSE 0 END),0) AS mora_1_30,
            COALESCE(SUM(CASE WHEN estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND saldo>0 AND DATEDIFF(CURDATE(),fecha_vencimiento) BETWEEN 31 AND 60 THEN 1 ELSE 0 END),0) AS mora_31_60,
            COALESCE(SUM(CASE WHEN estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND saldo>0 AND DATEDIFF(CURDATE(),fecha_vencimiento)>60 THEN 1 ELSE 0 END),0) AS mora_60
            FROM cuentas_cobrar";
        return $this->db->query($sql)->fetch() ?: [];
    }

    public function reportesFinancieros(string $desde, string $hasta): array
    {
        $rango = [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59'];
        $caja = $this->db->prepare("SELECT DATE(p.fecha_pago) AS fecha,pm.medio AS medio_pago,COUNT(DISTINCT p.id_pago) AS operaciones,SUM(pm.importe_aplicado) AS total FROM pagos_alumno p INNER JOIN pagos_alumno_medios pm ON pm.id_pago=p.id_pago WHERE p.estado='REGISTRADO' AND p.fecha_pago BETWEEN :desde AND :hasta GROUP BY DATE(p.fecha_pago),pm.medio ORDER BY fecha DESC,medio_pago");
        $caja->execute($rango);
        $conceptos = $this->db->prepare("SELECT c.nombre,c.tipo,COUNT(DISTINCT p.id_pago) AS recibos,SUM(d.importe) AS cobrado FROM pagos_alumno_detalles d INNER JOIN pagos_alumno p ON p.id_pago=d.id_pago INNER JOIN cuentas_cobrar cc ON cc.id_cuenta=d.id_cuenta INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=cc.id_concepto_cobro WHERE p.estado='REGISTRADO' AND p.fecha_pago BETWEEN :desde AND :hasta GROUP BY c.id_concepto_cobro ORDER BY cobrado DESC,c.nombre");
        $conceptos->execute($rango);
        $morosidad = $this->db->query("SELECT n.nombre AS nivel,g.nombre AS grado,COUNT(*) AS cuentas,COUNT(DISTINCT m.id_persona) AS alumnos,SUM(cc.saldo) AS deuda FROM cuentas_cobrar cc INNER JOIN matriculas m ON m.id_matricula=cc.id_matricula INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE cc.estado IN ('PENDIENTE','PARCIAL','VENCIDA') AND cc.saldo>0 AND cc.fecha_vencimiento IS NOT NULL AND cc.fecha_vencimiento<CURDATE() GROUP BY g.id_grado ORDER BY deuda DESC,n.orden,g.orden")->fetchAll();
        return ['caja' => $caja->fetchAll(), 'conceptos' => $conceptos->fetchAll(), 'morosidad' => $morosidad];
    }

    public function estadoCuentaAlumno(int $idPersona): array
    {
        $stmt = $this->db->prepare("SELECT cc.*, c.nombre AS concepto,c.admite_parcial,m.id_persona,m.codigo_alumno,pa.nombre AS periodo_academico, CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS seccion FROM cuentas_cobrar cc INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=cc.id_concepto_cobro INNER JOIN matriculas m ON m.id_matricula=cc.id_matricula INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE m.id_persona=:persona AND cc.estado NOT IN ('PAGADA','ANULADA') ORDER BY pa.fecha_inicio DESC,cc.fecha_vencimiento,cc.periodo_cobro");
        $stmt->execute([':persona' => $idPersona]);
        return $stmt->fetchAll();
    }

    public function estadoCuentaImprimible(int $idPersona): array
    {
        $alumno = $this->db->prepare("SELECT id_persona,numero_documento,CONCAT(apellidos,', ',nombres) AS nombre FROM personas WHERE id_persona=:id");
        $alumno->execute([':id' => $idPersona]);
        $alumno = $alumno->fetch();
        if (!$alumno) throw new RuntimeException('Alumno no encontrado.');
        $cuentas = $this->db->prepare("SELECT cc.*, (cc.importe_original+cc.recargo-cc.descuento) AS importe_final,c.nombre AS concepto,pa.nombre AS periodo_academico,CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) AS seccion,COALESCE((SELECT SUM(d.importe) FROM pagos_alumno_detalles d INNER JOIN pagos_alumno p ON p.id_pago=d.id_pago WHERE d.id_cuenta=cc.id_cuenta AND p.estado='REGISTRADO'),0) AS pagado FROM cuentas_cobrar cc INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=cc.id_concepto_cobro INNER JOIN matriculas m ON m.id_matricula=cc.id_matricula INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE m.id_persona=:persona ORDER BY pa.fecha_inicio DESC,cc.periodo_cobro DESC,c.nombre");
        $cuentas->execute([':persona' => $idPersona]);
        return ['alumno' => $alumno, 'cuentas' => $cuentas->fetchAll()];
    }

    public function resumen(string $mes): array
    {
        $sql = "SELECT COUNT(*) AS cuentas, COALESCE(SUM(importe_original+recargo-descuento),0) AS facturado, COALESCE(SUM(saldo),0) AS pendiente, COALESCE(SUM(importe_original+recargo-descuento-saldo),0) AS cobrado FROM cuentas_cobrar";
        $params = [];
        if ($mes !== '') {
            $sql .= ' WHERE periodo_cobro=:mes';
            $params[':mes'] = $mes . '-01';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
            ':concepto' => $data['id_concepto_cobro'],
            ':periodo' => $data['id_periodo_academico'],
            ':grado' => $data['id_grado'] !== '' ? $data['id_grado'] : null,
            ':importe' => $data['importe'],
            ':inicio' => $data['vigencia_inicio'],
            ':fin' => $data['vigencia_fin'] ?: null
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
            // La tarifa se aplica al mes seleccionado; usar su último día permite
            // configurar una tarifa durante el mes sin impedir su generación.
            $fechaTarifa = date('Y-m-t', strtotime($periodoCobro));
            $sqlMatriculas = "SELECT m.id_matricula,g.id_grado FROM matriculas m INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado WHERE m.id_periodo_academico=? AND m.estado='ACTIVO'";
            $paramMatriculas = [$data['id_periodo_academico']];
            $alcance = $data['alcance'] ?? 'TODOS';
            if ($alcance === 'SECCION' && (int)($data['id_seccion'] ?? 0) > 0) {
                $sqlMatriculas .= ' AND m.id_seccion=?';
                $paramMatriculas[] = (int)$data['id_seccion'];
            } elseif ($alcance === 'ALUMNO' && (int)($data['id_alumno'] ?? 0) > 0) {
                $sqlMatriculas .= ' AND m.id_persona=?';
                $paramMatriculas[] = (int)$data['id_alumno'];
            } elseif ($alcance !== 'TODOS') {
                throw new RuntimeException('Seleccione la sección o el alumno para generar el cobro.');
            }
            $matriculas = $this->db->prepare($sqlMatriculas);
            $matriculas->execute($paramMatriculas);
            $tarifa = $this->db->prepare("SELECT importe FROM tarifas_cobro WHERE id_concepto_cobro=? AND id_periodo_academico=? AND (id_grado=? OR id_grado IS NULL) AND vigencia_inicio<=? AND (vigencia_fin IS NULL OR vigencia_fin>=?) ORDER BY id_grado IS NULL, id_tarifa DESC LIMIT 1");
            $crear = $this->db->prepare("INSERT INTO cuentas_cobrar(id_matricula,id_concepto_cobro,periodo_cobro,fecha_vencimiento,importe_original,descuento,recargo,saldo,estado) VALUES(?,?,?,?,?,0,0,?,'PENDIENTE') ON DUPLICATE KEY UPDATE id_cuenta=id_cuenta");
            $creadas = 0;
            $sinTarifa = 0;
            $filasMatricula = $matriculas->fetchAll();
            if (!$filasMatricula) {
                throw new RuntimeException('El período seleccionado no tiene alumnos con matrícula activa.');
            }
            foreach ($filasMatricula as $matricula) {
                $tarifa->execute([$data['id_concepto_cobro'], $data['id_periodo_academico'], $matricula['id_grado'], $fechaTarifa, $fechaTarifa]);
                $importe = $tarifa->fetchColumn();
                if ($importe === false) {
                    $sinTarifa++;
                    continue;
                }
                $crear->execute([$matricula['id_matricula'], $data['id_concepto_cobro'], $periodoCobro, $data['fecha_vencimiento'] ?: null, $importe, $importe]);
                $creadas += $crear->rowCount();
            }
            if ($creadas === 0 && $sinTarifa > 0) {
                throw new RuntimeException('No existe una tarifa vigente para este concepto, período y los grados de las matrículas activas.');
            }
            $this->db->commit();
            return $creadas;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function registrarPago(array $data, int $usuario): array
    {
        $detalles = $data['detalles'] ?? [];
        if (!is_array($detalles) || !$detalles) {
            throw new RuntimeException('Seleccione al menos una deuda e indique el importe a pagar.');
        }
        $this->db->beginTransaction();
        try {
            $caja = $this->db->prepare("SELECT id_apertura,fecha,estado FROM aperturas_caja WHERE estado='ABIERTA' ORDER BY id_apertura DESC LIMIT 1 FOR UPDATE");
            $caja->execute();
            $caja = $caja->fetch();
            if (!$caja) throw new RuntimeException('Primero debe abrir un turno de caja para registrar pagos.');
            if ($caja['fecha'] !== date('Y-m-d')) {
                throw new RuntimeException('La caja del ' . $caja['fecha'] . ' sigue abierta. Debe cerrarla y abrir una caja para hoy antes de registrar pagos.');
            }
            $cuenta = $this->db->prepare("SELECT cc.*, (SELECT aa.id_apoderado FROM matriculas m INNER JOIN alumno_apoderado aa ON aa.id_alumno=m.id_persona AND aa.estado='ACTIVO' WHERE m.id_matricula=cc.id_matricula ORDER BY aa.responsable_economico DESC,aa.es_principal DESC LIMIT 1) AS id_apoderado FROM cuentas_cobrar cc WHERE cc.id_cuenta=:id FOR UPDATE");
            $total = 0.0;
            $cuentas = [];
            $idApoderado = null;
            foreach ($detalles as $idCuenta => $valor) {
                $importe = round((float) $valor, 2);
                if ($importe <= 0) {
                    continue;
                }
                $cuenta->execute([':id' => (int) $idCuenta]);
                $fila = $cuenta->fetch();
                if (!$fila || in_array($fila['estado'], ['ANULADA', 'PAGADA'], true) || $importe > (float) $fila['saldo']) {
                    throw new RuntimeException('Uno de los importes no es válido para el saldo pendiente.');
                }
                $concepto = $this->db->prepare('SELECT admite_parcial FROM conceptos_cobro WHERE id_concepto_cobro=:id');
                $concepto->execute([':id' => $fila['id_concepto_cobro']]);
                if (!(bool) $concepto->fetchColumn() && $importe < (float) $fila['saldo']) {
                    throw new RuntimeException('Uno de los conceptos seleccionados no admite pagos parciales.');
                }
                $cuentas[] = ['cuenta' => $fila, 'importe' => $importe];
                $total += $importe;
                $idApoderado = $idApoderado ?: $fila['id_apoderado'];
            }
            if (!$cuentas) {
                throw new RuntimeException('Indique al menos un importe mayor a cero.');
            }
            $total = round($total, 2);
            $mediosEntrada = $data['medios'] ?? [];
            $permitidos = ['EFECTIVO', 'YAPE', 'PLIN', 'TRANSFERENCIA', 'TARJETA', 'DEPOSITO', 'OTRO'];
            $medios = [];
            $totalMedios = 0.0;
            foreach ($permitidos as $medio) {
                $entrada = is_array($mediosEntrada[$medio] ?? null) ? $mediosEntrada[$medio] : [];
                $importe = round((float)($entrada['importe'] ?? 0), 2);
                if ($importe <= 0) continue;
                $recibido = $medio === 'EFECTIVO' ? round((float)($entrada['recibido'] ?? 0), 2) : $importe;
                $referencia = trim((string)($entrada['referencia'] ?? ''));
                if ($medio === 'EFECTIVO' && $recibido < $importe) {
                    throw new RuntimeException('El efectivo recibido no puede ser menor que el efectivo aplicado al pago.');
                }
                $evidencia = $medio !== 'EFECTIVO' ? ($data['evidencias_pago'][$medio] ?? null) : null;
                if ($medio !== 'EFECTIVO' && $referencia === '' && !$evidencia) {
                    throw new RuntimeException('Indique una referencia o adjunte evidencia de ' . ucfirst(strtolower($medio)) . '.');
                }
                $medios[] = [
                    'medio' => $medio,
                    'importe' => $importe,
                    'recibido' => $recibido,
                    'vuelto' => $medio === 'EFECTIVO' ? round($recibido - $importe, 2) : 0,
                    'referencia' => $referencia !== '' ? $referencia : null,
                    'evidencia' => $evidencia,
                ];
                $totalMedios += $importe;
            }
            if (!$medios) throw new RuntimeException('Distribuya el pago en al menos un medio de pago.');
            if (abs(round($totalMedios, 2) - $total) > 0.009) {
                throw new RuntimeException('La suma de los medios de pago debe ser igual al total aplicado a las deudas.');
            }
            $medioResumen = count($medios) > 1 ? 'MIXTO' : $medios[0]['medio'];
            $numero = 'R-' . date('Y') . '-' . str_pad((string) ((int) $this->db->query('SELECT COUNT(*) FROM pagos_alumno WHERE YEAR(fecha_pago)=' . (int) date('Y'))->fetchColumn() + 1), 6, '0', STR_PAD_LEFT);
            $pago = $this->db->prepare("INSERT INTO pagos_alumno(numero_recibo,id_apoderado,id_apertura,fecha_pago,medio_pago,importe_total,estado,registrado_por,observacion) VALUES(:recibo,:apoderado,:apertura,NOW(),:medio,:importe,'REGISTRADO',:usuario,:observacion)");
            $pago->execute([':recibo' => $numero, ':apoderado' => $idApoderado ?: null, ':apertura' => $caja['id_apertura'], ':medio' => $medioResumen, ':importe' => $total, ':usuario' => $usuario, ':observacion' => trim((string)($data['observacion'] ?? '')) ?: null]);
            $idPago = (int) $this->db->lastInsertId();
            $guardarMedio = $this->db->prepare('INSERT INTO pagos_alumno_medios(id_pago,medio,importe_aplicado,importe_recibido,vuelto,referencia,evidencia_archivo) VALUES(:pago,:medio,:importe,:recibido,:vuelto,:referencia,:evidencia)');
            foreach ($medios as $medio) {
                $guardarMedio->execute([':pago' => $idPago, ':medio' => $medio['medio'], ':importe' => $medio['importe'], ':recibido' => $medio['recibido'], ':vuelto' => $medio['vuelto'], ':referencia' => $medio['referencia'], ':evidencia' => $medio['evidencia']]);
            }
            $actualizar = $this->db->prepare("UPDATE cuentas_cobrar SET saldo=:saldo,estado=:estado WHERE id_cuenta=:id");
            $detalle = $this->db->prepare('INSERT INTO pagos_alumno_detalles(id_pago,id_cuenta,importe) VALUES(:pago,:cuenta,:importe)');
            foreach ($cuentas as $item) {
                $fila = $item['cuenta'];
                $importe = $item['importe'];
                $detalle->execute([':pago' => $idPago, ':cuenta' => $fila['id_cuenta'], ':importe' => $importe]);
                $saldo = round((float)$fila['saldo'] - $importe, 2);
                $actualizar->execute([':saldo' => $saldo, ':estado' => $saldo <= 0 ? 'PAGADA' : 'PARCIAL', ':id' => $fila['id_cuenta']]);
            }
            $this->db->commit();
            return ['id_pago' => $idPago, 'numero_recibo' => $numero];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function resumenCaja(string $fecha, ?int $idApertura = null): array
    {
        $tipos = ['EFECTIVO','YAPE','PLIN','TRANSFERENCIA','TARJETA','DEPOSITO','OTRO'];
        $medios = array_fill_keys($tipos, 0.0);
        $condicionPago = $idApertura ? 'p.id_apertura=:apertura' : 'DATE(p.fecha_pago)=:fecha';
        $parametros = $idApertura ? [':apertura'=>$idApertura] : [':fecha'=>$fecha];
        $pagos = $this->db->prepare("SELECT pm.medio,SUM(pm.importe_aplicado) total FROM pagos_alumno p INNER JOIN pagos_alumno_medios pm ON pm.id_pago=p.id_pago WHERE p.estado='REGISTRADO' AND {$condicionPago} GROUP BY pm.medio");
        $pagos->execute($parametros);
        foreach ($pagos->fetchAll() as $fila) $medios[$fila['medio']] += (float)$fila['total'];
        $conteoPagos = $this->db->prepare("SELECT COUNT(DISTINCT p.id_pago) FROM pagos_alumno p WHERE p.estado='REGISTRADO' AND " . ($idApertura ? 'p.id_apertura=:apertura' : 'DATE(p.fecha_pago)=:fecha'));
        $conteoPagos->execute($parametros);
        $operaciones = (int)$conteoPagos->fetchColumn();

        $condicionMovimiento = $idApertura ? 'id_apertura=:apertura' : 'DATE(fecha_movimiento)=:fecha';
        $movimientos = $this->db->prepare("SELECT medio,SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE -monto END) total,COUNT(*) operaciones FROM movimientos_financieros WHERE estado='REGISTRADO' AND {$condicionMovimiento} GROUP BY medio");
        $movimientos->execute($parametros);
        foreach ($movimientos->fetchAll() as $fila) {
            $medios[$fila['medio']] += (float)$fila['total'];
            $operaciones += (int)$fila['operaciones'];
        }
        if ($idApertura) {
            $apertura = $this->db->prepare('SELECT monto_apertura FROM aperturas_caja WHERE id_apertura=:id');
            $apertura->execute([':id'=>$idApertura]);
            $medios['EFECTIVO'] += (float)($apertura->fetchColumn() ?: 0);
        }
        foreach ($medios as &$importe) $importe = round($importe, 2);
        unset($importe);
        $efectivo = $medios['EFECTIVO'];
        $otros = round(array_sum($medios) - $efectivo, 2);
        return ['efectivo'=>$efectivo,'otros'=>$otros,'total'=>round($efectivo+$otros,2),'operaciones'=>$operaciones,'medios'=>$medios];
    }

    public function cierresCaja(): array
    {
        return $this->db->query("SELECT c.*,CONCAT(COALESCE(p.apellidos,''),', ',COALESCE(p.nombres,'')) AS usuario FROM cierres_caja c LEFT JOIN usuarios u ON u.id_usuario=c.cerrado_por LEFT JOIN personas p ON p.id_persona=u.id_persona ORDER BY c.fecha DESC")->fetchAll();
    }

    public function estadoCaja(string $fecha): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM aperturas_caja WHERE fecha=:fecha ORDER BY id_apertura DESC LIMIT 1');
        $stmt->execute([':fecha' => $fecha]);
        $caja = $stmt->fetch();
        if ($caja) return $caja;
        $cierre = $this->db->prepare('SELECT id_cierre FROM cierres_caja WHERE fecha=:fecha');
        $cierre->execute([':fecha' => $fecha]);
        return $cierre->fetch() ? ['estado' => 'CERRADA', 'monto_apertura' => 0] : null;
    }

    public function cajaAbierta(): ?array
    {
        $caja = $this->db->query("SELECT * FROM aperturas_caja WHERE estado='ABIERTA' ORDER BY id_apertura DESC LIMIT 1")->fetch();
        return $caja ?: null;
    }

    public function cajasDiarias(): array
    {
        return $this->db->query("SELECT a.*,c.efectivo_sistema,c.efectivo_declarado,c.otros_sistema,c.otros_declarado,(c.efectivo_declarado+c.otros_declarado) AS total_cierre_declarado,c.diferencia,c.cerrado_en,COALESCE(r.efectivo,0) AS efectivo_cobrado,COALESCE(r.otros,0) AS otros_cobrados,COALESCE(r.total,0) AS total_cobrado,cm.medios_sistema,cm.medios_declarados,CONCAT(COALESCE(pa.apellidos,''),', ',COALESCE(pa.nombres,'')) AS usuario_apertura,ua.usuario AS login_apertura,CONCAT(COALESCE(pc.apellidos,''),', ',COALESCE(pc.nombres,'')) AS usuario_cierre,uc.usuario AS login_cierre FROM aperturas_caja a LEFT JOIN cierres_caja c ON c.id_apertura=a.id_apertura LEFT JOIN (SELECT id_apertura,SUM(CASE WHEN medio='EFECTIVO' THEN importe ELSE 0 END) efectivo,SUM(CASE WHEN medio<>'EFECTIVO' THEN importe ELSE 0 END) otros,SUM(importe) total FROM (SELECT p.id_apertura,pm.medio,pm.importe_aplicado importe FROM pagos_alumno p INNER JOIN pagos_alumno_medios pm ON pm.id_pago=p.id_pago WHERE p.estado='REGISTRADO' UNION ALL SELECT id_apertura,medio,CASE WHEN tipo='INGRESO' THEN monto ELSE -monto END FROM movimientos_financieros WHERE estado='REGISTRADO' AND id_apertura IS NOT NULL) flujo GROUP BY id_apertura) r ON r.id_apertura=a.id_apertura LEFT JOIN (SELECT id_cierre,GROUP_CONCAT(CONCAT(medio,': S/ ',FORMAT(importe_sistema,2)) ORDER BY FIELD(medio,'EFECTIVO','YAPE','PLIN','TRANSFERENCIA','TARJETA','DEPOSITO','OTRO') SEPARATOR ' | ') medios_sistema,GROUP_CONCAT(CONCAT(medio,': S/ ',FORMAT(importe_declarado,2)) ORDER BY FIELD(medio,'EFECTIVO','YAPE','PLIN','TRANSFERENCIA','TARJETA','DEPOSITO','OTRO') SEPARATOR ' | ') medios_declarados FROM cierres_caja_medios GROUP BY id_cierre) cm ON cm.id_cierre=c.id_cierre LEFT JOIN usuarios ua ON ua.id_usuario=a.abierto_por LEFT JOIN personas pa ON pa.id_persona=ua.id_persona LEFT JOIN usuarios uc ON uc.id_usuario=c.cerrado_por LEFT JOIN personas pc ON pc.id_persona=uc.id_persona ORDER BY a.abierto_en DESC")->fetchAll();
    }

    public function abrirCaja(array $data, int $usuario): void
    {
        $fecha = $data['fecha'] ?? '';
        $monto = round((float)($data['monto_apertura'] ?? 0), 2);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $monto < 0) throw new RuntimeException('Complete la fecha y el monto de apertura.');
        if ($fecha !== date('Y-m-d')) throw new RuntimeException('Solo puede abrir una caja con la fecha actual.');
        $this->db->beginTransaction();
        try {
            $abierta = $this->db->query("SELECT fecha FROM aperturas_caja WHERE estado='ABIERTA' LIMIT 1 FOR UPDATE")->fetchColumn();
            if ($abierta) throw new RuntimeException('La caja del ' . $abierta . ' sigue abierta. Debe cerrarla antes de abrir otra.');
            $guardar = $this->db->prepare("INSERT INTO aperturas_caja(fecha,monto_apertura,observacion,abierto_por,estado) VALUES(:fecha,:monto,:observacion,:usuario,'ABIERTA')");
            $guardar->execute([':fecha' => $fecha, ':monto' => $monto, ':observacion' => trim($data['observacion'] ?? '') ?: null, ':usuario' => $usuario]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function cerrarCaja(array $data, int $usuario): void
    {
        $fecha = $data['fecha'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new RuntimeException('Seleccione una fecha válida.');
        $declaradosEntrada = is_array($data['declarados'] ?? null) ? $data['declarados'] : [];
        $this->db->beginTransaction();
        try {
            $idApertura = (int)($data['id_apertura'] ?? 0);
            $apertura = $this->db->prepare("SELECT id_apertura,fecha FROM aperturas_caja WHERE id_apertura=:id AND estado='ABIERTA' FOR UPDATE");
            $apertura->execute([':id' => $idApertura]);
            $apertura = $apertura->fetch();
            if (!$apertura) throw new RuntimeException('El turno de caja debe estar abierto para poder cerrarlo.');
            $existe = $this->db->prepare('SELECT id_cierre FROM cierres_caja WHERE id_apertura=:apertura FOR UPDATE');
            $existe->execute([':apertura' => $idApertura]);
            if ($existe->fetch()) throw new RuntimeException('Este turno de caja ya fue cerrado.');
            $fecha = $apertura['fecha'];
            $resumen = $this->resumenCaja($fecha, $idApertura);
            $declarados = [];
            foreach ($resumen['medios'] as $medio=>$importeSistema) {
                $declarado = round((float)($declaradosEntrada[$medio] ?? $importeSistema), 2);
                if ($medio === 'EFECTIVO' && $declarado < 0) throw new RuntimeException('El efectivo declarado no puede ser negativo.');
                $declarados[$medio] = $declarado;
            }
            $efectivo = $declarados['EFECTIVO'];
            $otros = round(array_sum($declarados) - $efectivo, 2);
            $sistema = (float)$resumen['efectivo'] + (float)$resumen['otros'];
            $declarado = $efectivo + $otros;
            $diferencia = round($declarado - $sistema, 2);
            $guardar = $this->db->prepare('INSERT INTO cierres_caja(fecha,id_apertura,efectivo_sistema,efectivo_declarado,otros_sistema,otros_declarado,diferencia,estado_revision,observacion,cerrado_por) VALUES(:fecha,:apertura,:efectivoSistema,:efectivoDeclarado,:otrosSistema,:otrosDeclarado,:diferencia,:revision,:observacion,:usuario)');
            $guardar->execute([':fecha' => $fecha, ':apertura' => $idApertura, ':efectivoSistema' => $resumen['efectivo'], ':efectivoDeclarado' => $efectivo, ':otrosSistema' => $resumen['otros'], ':otrosDeclarado' => $otros, ':diferencia' => $diferencia, ':revision'=>abs($diferencia)>0.009?'PENDIENTE':'SIN_DIFERENCIA', ':observacion' => trim($data['observacion'] ?? '') ?: null, ':usuario' => $usuario]);
            $idCierre = (int)$this->db->lastInsertId();
            $guardarMedio = $this->db->prepare('INSERT INTO cierres_caja_medios(id_cierre,medio,importe_sistema,importe_declarado,diferencia) VALUES(:cierre,:medio,:sistema,:declarado,:diferencia)');
            foreach ($resumen['medios'] as $medio=>$importeSistema) {
                $guardarMedio->execute([':cierre'=>$idCierre,':medio'=>$medio,':sistema'=>$importeSistema,':declarado'=>$declarados[$medio],':diferencia'=>round($declarados[$medio]-$importeSistema,2)]);
            }
            $actualizar = $this->db->prepare("UPDATE aperturas_caja SET estado='CERRADA' WHERE id_apertura=:id");
            $actualizar->execute([':id' => $idApertura]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function pagos(): array
    {
        return $this->db->query("SELECT p.*,CONCAT(COALESCE(a.apellidos,''),', ',COALESCE(a.nombres,'')) AS apoderado,(SELECT COUNT(*) FROM pagos_alumno_detalles d WHERE d.id_pago=p.id_pago) AS deudas,(SELECT GROUP_CONCAT(CONCAT(pm.medio,' S/ ',FORMAT(pm.importe_aplicado,2)) ORDER BY pm.id_pago_medio SEPARATOR ' + ') FROM pagos_alumno_medios pm WHERE pm.id_pago=p.id_pago) AS medios_resumen,(SELECT COALESCE(SUM(pm.vuelto),0) FROM pagos_alumno_medios pm WHERE pm.id_pago=p.id_pago) AS vuelto,(SELECT COUNT(*) FROM pagos_alumno_medios pm WHERE pm.id_pago=p.id_pago AND pm.evidencia_archivo IS NOT NULL) AS evidencias FROM pagos_alumno p LEFT JOIN personas a ON a.id_persona=p.id_apoderado ORDER BY p.fecha_pago DESC,p.id_pago DESC")->fetchAll();
    }

    public function mediosCierres(): array
    {
        $filas = $this->db->query("SELECT cm.* FROM cierres_caja_medios cm INNER JOIN cierres_caja c ON c.id_cierre=cm.id_cierre ORDER BY cm.id_cierre,FIELD(cm.medio,'EFECTIVO','YAPE','PLIN','TRANSFERENCIA','TARJETA','DEPOSITO','OTRO')")->fetchAll();
        $resultado = [];
        foreach ($filas as $fila) $resultado[(int)$fila['id_cierre']][] = $fila;
        return $resultado;
    }

    public function datosRevisionCierres(): array
    {
        $resultado=[];
        foreach($this->db->query('SELECT id_cierre,id_apertura,estado_revision FROM cierres_caja')->fetchAll() as $fila){
            $resultado[(int)$fila['id_apertura']]=['id_cierre'=>(int)$fila['id_cierre'],'estado_revision'=>$fila['estado_revision']];
        }
        return $resultado;
    }

    public function rectificacionesCaja(): array
    {
        return $this->db->query("SELECT r.*,c.fecha,c.id_apertura,COALESCE(NULLIF(CONCAT(COALESCE(p.apellidos,''),', ',COALESCE(p.nombres,'')),', '),u.usuario,'Sistema') responsable FROM rectificaciones_caja r INNER JOIN cierres_caja c ON c.id_cierre=r.id_cierre LEFT JOIN usuarios u ON u.id_usuario=r.registrado_por LEFT JOIN personas p ON p.id_persona=u.id_persona ORDER BY r.registrado_en DESC,r.id_rectificacion DESC")->fetchAll();
    }

    public function rectificarCaja(array $data, int $usuario): int
    {
        $idCierre = (int)($data['id_cierre'] ?? 0);
        $accion = $data['accion'] ?? '';
        $motivoTipo = $data['motivo_tipo'] ?? '';
        $motivo = trim((string)($data['motivo'] ?? ''));
        $referencia = trim((string)($data['referencia_regularizacion'] ?? ''));
        $acciones = ['RECTIFICACION','JUSTIFICACION','REGULARIZACION'];
        $motivos = ['ERROR_CONTEO','ERROR_DIGITACION','MOVIMIENTO_OMITIDO','DIFERENCIA_REAL','OTRO'];
        if ($idCierre < 1 || !in_array($accion,$acciones,true) || !in_array($motivoTipo,$motivos,true) || strlen($motivo)<5) throw new InvalidArgumentException('Complete la acción y explique el motivo de la corrección.');
        if ($accion === 'RECTIFICACION' && !in_array($motivoTipo,['ERROR_CONTEO','ERROR_DIGITACION','OTRO'],true)) throw new RuntimeException('Una rectificación debe corresponder a un error de conteo o digitación.');
        if ($accion === 'REGULARIZACION' && $referencia === '') throw new RuntimeException('Indique la referencia del movimiento utilizado para regularizar la diferencia.');

        $this->db->beginTransaction();
        try {
            $consulta = $this->db->prepare('SELECT * FROM cierres_caja WHERE id_cierre=:id FOR UPDATE');
            $consulta->execute([':id'=>$idCierre]);
            $cierre = $consulta->fetch();
            if (!$cierre) throw new RuntimeException('El cierre seleccionado no existe.');
            $consultaMedios = $this->db->prepare('SELECT * FROM cierres_caja_medios WHERE id_cierre=:id ORDER BY id_cierre_medio FOR UPDATE');
            $consultaMedios->execute([':id'=>$idCierre]);
            $medios = $consultaMedios->fetchAll();
            if (!$medios) throw new RuntimeException('El cierre no tiene un detalle por medios disponible para rectificar.');
            $nuevosEntrada = is_array($data['declarados'] ?? null) ? $data['declarados'] : [];
            $detalles = []; $cambio = false; $totalSistema = 0.0; $totalNuevo = 0.0;
            foreach ($medios as $medio) {
                $anterior = (float)$medio['importe_declarado'];
                $nuevo = $accion === 'RECTIFICACION' ? round((float)($nuevosEntrada[$medio['medio']] ?? $anterior),2) : $anterior;
                if ($medio['medio']==='EFECTIVO' && $nuevo<0) throw new RuntimeException('El efectivo rectificado no puede ser negativo.');
                if (abs($nuevo-$anterior)>0.009) $cambio=true;
                $sistema=(float)$medio['importe_sistema'];
                $detalles[]=['id'=>(int)$medio['id_cierre_medio'],'medio'=>$medio['medio'],'sistema'=>$sistema,'anterior'=>$anterior,'nuevo'=>$nuevo];
                $totalSistema += $sistema; $totalNuevo += $nuevo;
            }
            if ($accion==='RECTIFICACION' && !$cambio) throw new RuntimeException('Modifique al menos un importe declarado para registrar la rectificación.');
            if ($accion!=='RECTIFICACION' && abs((float)$cierre['diferencia'])<=0.009) throw new RuntimeException('Este cierre ya no tiene una diferencia pendiente que resolver.');
            $diferenciaNueva=round($totalNuevo-$totalSistema,2);
            $guardar=$this->db->prepare("INSERT INTO rectificaciones_caja(id_cierre,accion,motivo_tipo,motivo,referencia_regularizacion,evidencia_archivo,diferencia_anterior,diferencia_nueva,registrado_por) VALUES(:cierre,:accion,:tipo,:motivo,:referencia,:evidencia,:anterior,:nueva,:usuario)");
            $guardar->execute([':cierre'=>$idCierre,':accion'=>$accion,':tipo'=>$motivoTipo,':motivo'=>$motivo,':referencia'=>$referencia?:null,':evidencia'=>trim((string)($data['evidencia_archivo']??''))?:null,':anterior'=>$cierre['diferencia'],':nueva'=>$diferenciaNueva,':usuario'=>$usuario]);
            $idRectificacion=(int)$this->db->lastInsertId();
            $guardarDetalle=$this->db->prepare('INSERT INTO rectificaciones_caja_medios(id_rectificacion,medio,importe_sistema,importe_anterior,importe_nuevo,diferencia_anterior,diferencia_nueva) VALUES(:rectificacion,:medio,:sistema,:anterior,:nuevo,:difAnterior,:difNueva)');
            $actualizarMedio=$this->db->prepare('UPDATE cierres_caja_medios SET importe_declarado=:declarado,diferencia=:diferencia WHERE id_cierre_medio=:id');
            foreach($detalles as $detalle){
                $guardarDetalle->execute([':rectificacion'=>$idRectificacion,':medio'=>$detalle['medio'],':sistema'=>$detalle['sistema'],':anterior'=>$detalle['anterior'],':nuevo'=>$detalle['nuevo'],':difAnterior'=>round($detalle['anterior']-$detalle['sistema'],2),':difNueva'=>round($detalle['nuevo']-$detalle['sistema'],2)]);
                if($accion==='RECTIFICACION')$actualizarMedio->execute([':declarado'=>$detalle['nuevo'],':diferencia'=>round($detalle['nuevo']-$detalle['sistema'],2),':id'=>$detalle['id']]);
            }
            $efectivoNuevo=0.0;
            foreach($detalles as $detalle)if($detalle['medio']==='EFECTIVO')$efectivoNuevo=$detalle['nuevo'];
            $estado=$accion==='RECTIFICACION'?'RECTIFICADO':($accion==='JUSTIFICACION'?'JUSTIFICADO':'REGULARIZADO');
            $actualizar=$this->db->prepare('UPDATE cierres_caja SET efectivo_declarado=:efectivo,otros_declarado=:otros,diferencia=:diferencia,estado_revision=:estado WHERE id_cierre=:id');
            $actualizar->execute([':efectivo'=>$efectivoNuevo,':otros'=>round($totalNuevo-$efectivoNuevo,2),':diferencia'=>$diferenciaNueva,':estado'=>$estado,':id'=>$idCierre]);
            $this->db->commit();
            return $idRectificacion;
        } catch(Throwable $e){ if($this->db->inTransaction())$this->db->rollBack(); throw $e; }
    }

    public function evidenciaRectificacion(int $id): ?string
    {
        $stmt=$this->db->prepare('SELECT evidencia_archivo FROM rectificaciones_caja WHERE id_rectificacion=:id');
        $stmt->execute([':id'=>$id]);
        $archivo=$stmt->fetchColumn();
        return $archivo?basename((string)$archivo):null;
    }

    public function reciboImprimible(int $idPago): array
    {
        if ($idPago < 1) throw new InvalidArgumentException('Seleccione un recibo valido.');

        $pago = $this->db->prepare("SELECT pg.*,
            CONCAT(COALESCE(ap.apellidos,''),', ',COALESCE(ap.nombres,'')) AS apoderado,
            ap.numero_documento AS documento_apoderado,
            COALESCE(NULLIF(CONCAT(COALESCE(pc.apellidos,''),', ',COALESCE(pc.nombres,'')),', '),uc.usuario,'Sistema') AS cajero,
            COALESCE(NULLIF(CONCAT(COALESCE(pn.apellidos,''),', ',COALESCE(pn.nombres,'')),', '),un.usuario,NULL) AS anulador,
            ac.abierto_en AS caja_abierta_en
            FROM pagos_alumno pg
            LEFT JOIN personas ap ON ap.id_persona=pg.id_apoderado
            LEFT JOIN usuarios uc ON uc.id_usuario=pg.registrado_por
            LEFT JOIN personas pc ON pc.id_persona=uc.id_persona
            LEFT JOIN usuarios un ON un.id_usuario=pg.anulado_por
            LEFT JOIN personas pn ON pn.id_persona=un.id_persona
            LEFT JOIN aperturas_caja ac ON ac.id_apertura=pg.id_apertura
            WHERE pg.id_pago=:id LIMIT 1");
        $pago->execute([':id' => $idPago]);
        $pago = $pago->fetch();
        if (!$pago) throw new RuntimeException('El recibo no existe.');

        $detalle = $this->db->prepare("SELECT d.id_pago_detalle,d.importe,
            cc.periodo_cobro,cc.fecha_vencimiento,cc.saldo AS saldo_actual,cc.estado AS estado_cuenta,
            c.nombre AS concepto,c.tipo AS tipo_concepto,
            m.codigo_alumno,al.numero_documento AS documento_alumno,
            CONCAT(al.apellidos,', ',al.nombres) AS alumno,
            pa.nombre AS periodo_academico,
            CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) AS seccion
            FROM pagos_alumno_detalles d
            INNER JOIN cuentas_cobrar cc ON cc.id_cuenta=d.id_cuenta
            INNER JOIN conceptos_cobro c ON c.id_concepto_cobro=cc.id_concepto_cobro
            INNER JOIN matriculas m ON m.id_matricula=cc.id_matricula
            INNER JOIN personas al ON al.id_persona=m.id_persona
            INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico
            INNER JOIN secciones s ON s.id_seccion=m.id_seccion
            INNER JOIN grados g ON g.id_grado=s.id_grado
            INNER JOIN niveles n ON n.id_nivel=g.id_nivel
            WHERE d.id_pago=:id
            ORDER BY al.apellidos,al.nombres,cc.periodo_cobro,c.nombre");
        $detalle->execute([':id' => $idPago]);
        $detalles = $detalle->fetchAll();
        if (!$detalles) throw new RuntimeException('El recibo no tiene conceptos asociados.');

        $consultaMedios = $this->db->prepare('SELECT id_pago_medio,medio,importe_aplicado,importe_recibido,vuelto,referencia,evidencia_archivo FROM pagos_alumno_medios WHERE id_pago=:id ORDER BY id_pago_medio');
        $consultaMedios->execute([':id' => $idPago]);
        $medios = $consultaMedios->fetchAll();

        $institucion = $this->db->query('SELECT * FROM institucion ORDER BY id_institucion LIMIT 1')->fetch() ?: ['nombre' => 'Institucion educativa'];
        return compact('pago', 'detalles', 'medios', 'institucion');
    }

    public function evidenciaPago(int $idPagoMedio): ?string
    {
        $stmt = $this->db->prepare('SELECT evidencia_archivo FROM pagos_alumno_medios WHERE id_pago_medio=:id');
        $stmt->execute([':id' => $idPagoMedio]);
        $archivo = $stmt->fetchColumn();
        return $archivo ? basename((string)$archivo) : null;
    }

    public function anularPago(int $idPago, string $motivo, int $usuario): void
    {
        if ($idPago < 1 || trim($motivo) === '') {
            throw new RuntimeException('Indique el motivo de la anulación.');
        }
        $this->db->beginTransaction();
        try {
            $pago = $this->db->prepare('SELECT * FROM pagos_alumno WHERE id_pago=:id FOR UPDATE');
            $pago->execute([':id' => $idPago]);
            $pago = $pago->fetch();
            if (!$pago) throw new RuntimeException('El recibo no existe.');
            if ($pago['estado'] === 'ANULADO') throw new RuntimeException('Este recibo ya fue anulado.');
            $detalles = $this->db->prepare('SELECT * FROM pagos_alumno_detalles WHERE id_pago=:pago FOR UPDATE');
            $detalles->execute([':pago' => $idPago]);
            $detalles = $detalles->fetchAll();
            if (!$detalles) throw new RuntimeException('El recibo no tiene detalles de cobro.');
            $cuenta = $this->db->prepare('SELECT importe_original,descuento,recargo,saldo,fecha_vencimiento FROM cuentas_cobrar WHERE id_cuenta=:id FOR UPDATE');
            $actualizar = $this->db->prepare("UPDATE cuentas_cobrar SET saldo=:saldo,estado=:estado WHERE id_cuenta=:id");
            foreach ($detalles as $detalle) {
                $cuenta->execute([':id' => $detalle['id_cuenta']]);
                $fila = $cuenta->fetch();
                if (!$fila) throw new RuntimeException('Una de las cuentas del recibo ya no existe.');
                $saldo = round((float)$fila['saldo'] + (float)$detalle['importe'], 2);
                $total = round((float)$fila['importe_original'] + (float)$fila['recargo'] - (float)$fila['descuento'], 2);
                $vencida = $fila['fecha_vencimiento'] && $fila['fecha_vencimiento'] < date('Y-m-d');
                $actualizar->execute([':saldo' => $saldo, ':estado' => $vencida ? 'VENCIDA' : ($saldo >= $total ? 'PENDIENTE' : 'PARCIAL'), ':id' => $detalle['id_cuenta']]);
            }
            $anular = $this->db->prepare("UPDATE pagos_alumno SET estado='ANULADO',motivo_anulacion=:motivo,anulado_por=:usuario,anulado_en=NOW() WHERE id_pago=:id");
            $anular->execute([':motivo' => trim($motivo), ':usuario' => $usuario, ':id' => $idPago]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function ajustarCuenta(int $idCuenta, string $tipo, float $importe, string $motivo, int $usuario): void
    {
        if ($idCuenta < 1 || !in_array($tipo, ['DESCUENTO', 'BECA', 'EXONERACION', 'RECARGO'], true) || $importe <= 0 || trim($motivo) === '') throw new RuntimeException('Complete correctamente el ajuste y su motivo.');
        $this->db->beginTransaction();
        try {
            $cuenta = $this->db->prepare('SELECT * FROM cuentas_cobrar WHERE id_cuenta=:id FOR UPDATE');
            $cuenta->execute([':id' => $idCuenta]);
            $cuenta = $cuenta->fetch();
            if (!$cuenta || $cuenta['estado'] === 'ANULADA') throw new RuntimeException('La cuenta no está disponible para ajustes.');
            $totalActual = (float)$cuenta['importe_original'] + (float)$cuenta['recargo'] - (float)$cuenta['descuento'];
            $pagado = $totalActual - (float)$cuenta['saldo'];
            $nuevoDescuento = (float)$cuenta['descuento'];
            $nuevoRecargo = (float)$cuenta['recargo'];
            if ($tipo === 'RECARGO') $nuevoRecargo += $importe;
            else $nuevoDescuento += $importe;
            $nuevoTotal = (float)$cuenta['importe_original'] + $nuevoRecargo - $nuevoDescuento;
            if ($nuevoTotal < $pagado || $nuevoTotal < 0) throw new RuntimeException('El ajuste no puede reducir la deuda por debajo de lo ya pagado.');
            $nuevoSaldo = round($nuevoTotal - $pagado, 2);
            $vencida = $cuenta['fecha_vencimiento'] && $cuenta['fecha_vencimiento'] < date('Y-m-d');
            $estado = $nuevoSaldo <= 0 ? 'PAGADA' : ($vencida ? 'VENCIDA' : ($pagado > 0 ? 'PARCIAL' : 'PENDIENTE'));
            $actualizar = $this->db->prepare('UPDATE cuentas_cobrar SET descuento=:descuento,recargo=:recargo,saldo=:saldo,estado=:estado WHERE id_cuenta=:id');
            $actualizar->execute([':descuento' => $nuevoDescuento, ':recargo' => $nuevoRecargo, ':saldo' => $nuevoSaldo, ':estado' => $estado, ':id' => $idCuenta]);
            $registro = $this->db->prepare('INSERT INTO ajustes_cobro(id_cuenta,tipo,importe,motivo,registrado_por) VALUES(:cuenta,:tipo,:importe,:motivo,:usuario)');
            $registro->execute([':cuenta' => $idCuenta, ':tipo' => $tipo, ':importe' => $importe, ':motivo' => trim($motivo), ':usuario' => $usuario]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
