<?php

class DashboardModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function periodoActivo(): ?array
    {
        $periodo = $this->db->query("SELECT * FROM periodos_academicos WHERE estado='ACTIVO' ORDER BY fecha_inicio DESC LIMIT 1")->fetch();
        return $periodo ?: null;
    }

    public function poblacion(?int $idPeriodo): array
    {
        $resultado = [
            'alumnos' => 0,
            'docentes' => 0,
            'administrativos' => 0,
            'apoderados' => 0,
            'personal' => 0,
        ];

        if ($idPeriodo) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM matriculas WHERE id_periodo_academico=? AND estado='ACTIVO'");
            $stmt->execute([$idPeriodo]);
            $resultado['alumnos'] = (int) $stmt->fetchColumn();
        }

        $perfiles = $this->db->query("SELECT pp.tipo,COUNT(DISTINCT pp.id_persona) total FROM persona_perfiles pp INNER JOIN personas p ON p.id_persona=pp.id_persona AND p.estado='ACTIVO' WHERE pp.estado='ACTIVO' GROUP BY pp.tipo")->fetchAll();
        $mapaPerfiles = ['DOCENTE' => 'docentes', 'ADMINISTRATIVO' => 'administrativos', 'APODERADO' => 'apoderados'];
        foreach ($perfiles as $perfil) {
            if (isset($mapaPerfiles[$perfil['tipo']])) {
                $resultado[$mapaPerfiles[$perfil['tipo']]] = (int) $perfil['total'];
            }
        }
        $resultado['personal'] = (int) $this->db->query("SELECT COUNT(DISTINCT pp.id_persona) FROM persona_perfiles pp INNER JOIN personas p ON p.id_persona=pp.id_persona AND p.estado='ACTIVO' WHERE pp.tipo IN('DOCENTE','ADMINISTRATIVO') AND pp.estado='ACTIVO'")->fetchColumn();
        return $resultado;
    }

    public function asistenciaHoy(): array
    {
        $fila = $this->db->query("SELECT
            COUNT(DISTINCT CASE WHEN id_matricula IS NOT NULL AND hora_entrada IS NOT NULL THEN id_persona END) alumnos_presentes,
            COUNT(DISTINCT CASE WHEN id_matricula IS NOT NULL AND estado='PUNTUAL' THEN id_persona END) alumnos_puntuales,
            COUNT(DISTINCT CASE WHEN id_matricula IS NOT NULL AND estado='TARDANZA' THEN id_persona END) alumnos_tardanzas,
            COUNT(DISTINCT CASE WHEN id_matricula IS NOT NULL AND estado='FALTA' THEN id_persona END) alumnos_faltas,
            COUNT(DISTINCT CASE WHEN id_matricula IS NOT NULL AND estado IN('JUSTIFICADO','PERMISO','LICENCIA') THEN id_persona END) alumnos_justificados,
            COUNT(DISTINCT CASE WHEN id_matricula IS NULL AND hora_entrada IS NOT NULL THEN id_persona END) personal_presentes,
            COUNT(DISTINCT CASE WHEN id_matricula IS NULL AND estado='TARDANZA' THEN id_persona END) personal_tardanzas,
            COUNT(DISTINCT CASE WHEN id_matricula IS NULL AND estado='FALTA' THEN id_persona END) personal_faltas,
            COUNT(CASE WHEN id_matricula IS NULL AND hora_entrada IS NOT NULL AND hora_salida IS NULL THEN 1 END) personal_incompletos
            FROM asistencias_diarias WHERE fecha=CURDATE() AND estado<>'ANULADO'")->fetch();

        return array_map('intval', $fila ?: []);
    }

    public function tendenciaAlumnos(): array
    {
        return $this->db->query("SELECT fecha,
            COUNT(DISTINCT CASE WHEN hora_entrada IS NOT NULL THEN id_persona END) presentes,
            COUNT(DISTINCT CASE WHEN estado='PUNTUAL' THEN id_persona END) puntuales,
            COUNT(DISTINCT CASE WHEN estado='TARDANZA' THEN id_persona END) tardanzas,
            COUNT(DISTINCT CASE WHEN estado='FALTA' THEN id_persona END) faltas
            FROM asistencias_diarias
            WHERE id_matricula IS NOT NULL AND fecha>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) AND estado<>'ANULADO'
            GROUP BY fecha ORDER BY fecha")->fetchAll();
    }

    public function cierresAsistenciaHoy(): array
    {
        $resultado = ['ALUMNOS' => null, 'PERSONAL' => null];
        foreach ($this->db->query("SELECT alcance,tipo,ejecutado_en FROM cierres_asistencia WHERE fecha=CURDATE()") as $cierre) {
            $resultado[$cierre['alcance']] = $cierre;
        }
        return $resultado;
    }

    public function incidenciasPendientes(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM incidencias_asistencia WHERE estado='PENDIENTE'")->fetchColumn();
    }

    public function personalSinProgramacion(): int
    {
        return (int) $this->db->query("SELECT COUNT(DISTINCT v.id_persona)
            FROM vinculos_laborales v
            WHERE v.estado='ACTIVO' AND v.fecha_inicio<=CURDATE() AND (v.fecha_fin IS NULL OR v.fecha_fin>=CURDATE())
            AND NOT EXISTS(
                SELECT 1 FROM asignaciones_horario ah INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada
                WHERE ah.id_persona=v.id_persona AND ah.estado='ACTIVO' AND j.estado='ACTIVO'
                AND ah.fecha_inicio<=CURDATE() AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=CURDATE())
            )
            AND NOT EXISTS(
                SELECT 1 FROM carga_academica ca INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=ca.id_periodo_academico
                WHERE ca.id_vinculo=v.id_vinculo AND ca.estado='ACTIVO' AND pa.estado='ACTIVO'
            )")->fetchColumn();
    }

    public function matriculas(?int $idPeriodo): array
    {
        $resultado = ['matriculados' => 0, 'capacidad' => 0, 'vacantes' => 0, 'sin_apoderado' => 0];
        if (!$idPeriodo) return $resultado;

        $stmt = $this->db->prepare("SELECT
            COUNT(DISTINCT m.id_matricula) matriculados,
            COALESCE((SELECT SUM(COALESCE(s2.capacidad,0)) FROM secciones s2 WHERE s2.estado='ACTIVO'),0) capacidad,
            COUNT(DISTINCT CASE WHEN NOT EXISTS(SELECT 1 FROM alumno_apoderado aa WHERE aa.id_alumno=m.id_persona AND aa.estado='ACTIVO') THEN m.id_matricula END) sin_apoderado
            FROM matriculas m WHERE m.id_periodo_academico=? AND m.estado='ACTIVO'");
        $stmt->execute([$idPeriodo]);
        $fila = $stmt->fetch() ?: $resultado;
        $resultado['matriculados'] = (int) $fila['matriculados'];
        $resultado['capacidad'] = (int) $fila['capacidad'];
        $resultado['vacantes'] = max(0, $resultado['capacidad'] - $resultado['matriculados']);
        $resultado['sin_apoderado'] = (int) $fila['sin_apoderado'];
        return $resultado;
    }

    public function finanzasMes(): array
    {
        $resultado = [
            'cobros_mes' => 0.0,
            'cobros_hoy' => 0.0,
            'otros_ingresos' => 0.0,
            'egresos' => 0.0,
            'balance' => 0.0,
            'deuda_vencida' => 0.0,
            'cuentas_vencidas' => 0,
        ];

        $pagos = $this->db->query("SELECT
            COALESCE(SUM(CASE WHEN DATE_FORMAT(fecha_pago,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m') THEN importe_total ELSE 0 END),0) mes,
            COALESCE(SUM(CASE WHEN DATE(fecha_pago)=CURDATE() THEN importe_total ELSE 0 END),0) hoy
            FROM pagos_alumno WHERE estado='REGISTRADO'")->fetch();
        $resultado['cobros_mes'] = (float) $pagos['mes'];
        $resultado['cobros_hoy'] = (float) $pagos['hoy'];

        $movimientos = $this->db->query("SELECT
            COALESCE(SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE 0 END),0) ingresos,
            COALESCE(SUM(CASE WHEN tipo='EGRESO' THEN monto ELSE 0 END),0) egresos
            FROM movimientos_financieros
            WHERE estado='REGISTRADO' AND DATE_FORMAT(fecha_movimiento,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetch();
        $resultado['otros_ingresos'] = (float) $movimientos['ingresos'];
        $resultado['egresos'] = (float) $movimientos['egresos'];
        $resultado['balance'] = $resultado['cobros_mes'] + $resultado['otros_ingresos'] - $resultado['egresos'];

        $deuda = $this->db->query("SELECT COUNT(*) cuentas,COALESCE(SUM(saldo),0) total
            FROM cuentas_cobrar
            WHERE estado IN('PENDIENTE','PARCIAL','VENCIDA') AND fecha_vencimiento<CURDATE() AND saldo>0")->fetch();
        $resultado['cuentas_vencidas'] = (int) $deuda['cuentas'];
        $resultado['deuda_vencida'] = (float) $deuda['total'];
        return $resultado;
    }

    public function cajaActual(): ?array
    {
        $caja = $this->db->query("SELECT a.*,
            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(p.nombres,''),' ',COALESCE(p.apellidos,''))),''),u.usuario,'Usuario') responsable
            FROM aperturas_caja a
            LEFT JOIN usuarios u ON u.id_usuario=a.abierto_por
            LEFT JOIN personas p ON p.id_persona=u.id_persona
            WHERE a.estado='ABIERTA' ORDER BY a.id_apertura DESC LIMIT 1")->fetch();
        if (!$caja) return null;

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(total),0) total FROM (
            SELECT SUM(pm.importe_aplicado) total
            FROM pagos_alumno pa INNER JOIN pagos_alumno_medios pm ON pm.id_pago=pa.id_pago
            WHERE pa.id_apertura=:apertura1 AND pa.estado='REGISTRADO'
            UNION ALL
            SELECT SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE -monto END) total
            FROM movimientos_financieros
            WHERE id_apertura=:apertura2 AND estado='REGISTRADO'
        ) flujo");
        $stmt->execute([':apertura1' => $caja['id_apertura'], ':apertura2' => $caja['id_apertura']]);
        $caja['movimiento_turno'] = (float) $stmt->fetchColumn();
        $caja['esperado'] = (float) $caja['monto_apertura'] + $caja['movimiento_turno'];
        return $caja;
    }

    public function planillaActual(): ?array
    {
        $periodo = $this->db->query("SELECT pp.*,
            COALESCE((SELECT SUM(pd.saldo_pendiente) FROM planilla_detalles pd WHERE pd.id_periodo_pago=pp.id_periodo_pago),0) saldo_pendiente,
            COALESCE((SELECT SUM(pd.monto_excluido) FROM planilla_detalles pd WHERE pd.id_periodo_pago=pp.id_periodo_pago),0) monto_excluido
            FROM periodos_pago pp
            WHERE CURDATE() BETWEEN pp.fecha_inicio AND pp.fecha_fin
               OR DATE_FORMAT(pp.fecha_inicio,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')
            ORDER BY pp.fecha_inicio DESC LIMIT 1")->fetch();
        return $periodo ?: null;
    }
}
