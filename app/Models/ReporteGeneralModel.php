<?php

class ReporteGeneralModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function datos(array $entrada): array
    {
        $periodos = $this->db->query("SELECT id_periodo_academico,nombre,fecha_inicio,fecha_fin,estado,YEAR(fecha_inicio) anio FROM periodos_academicos ORDER BY fecha_inicio DESC")->fetchAll();
        $anios = [];
        foreach ($periodos as $item) $anios[(int)$item['anio']] = true;
        $anios = array_keys($anios);
        rsort($anios);

        $periodo = $this->resolverPeriodo($periodos, $entrada);
        $anio = $periodo ? (int)$periodo['anio'] : (int)($entrada['anio'] ?? date('Y'));
        $desde = $periodo ? $this->fechaEnPeriodo($entrada['desde'] ?? '', $periodo['fecha_inicio'], $periodo) : date('Y-m-01');
        $hasta = $periodo ? $this->fechaEnPeriodo($entrada['hasta'] ?? '', $periodo['fecha_fin'], $periodo) : date('Y-m-d');
        if ($desde > $hasta && $periodo) {
            $desde = $periodo['fecha_inicio'];
            $hasta = $periodo['fecha_fin'];
        }

        $secciones = $this->db->query("SELECT s.id_seccion,CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) nombre FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel ORDER BY n.orden,g.orden,s.nombre")->fetchAll();
        $idSeccion = max(0, (int)($entrada['seccion'] ?? 0));
        if ($idSeccion && !in_array($idSeccion, array_map('intval', array_column($secciones, 'id_seccion')), true)) $idSeccion = 0;

        $institucion = $this->db->query('SELECT * FROM institucion ORDER BY id_institucion LIMIT 1')->fetch() ?: [];
        $matriculas = [];
        $asistenciaAlumnos = [];
        $asistenciaPersonal = [];
        $incidenciasResumen = [];
        $incidencias = [];

        if ($periodo) {
            $idPeriodo = (int)$periodo['id_periodo_academico'];
            $matriculas = $this->matriculas($idPeriodo, $idSeccion);
            $asistenciaAlumnos = $this->asistenciaAlumnos($idPeriodo, $idSeccion, $desde, $hasta);
            $asistenciaPersonal = $this->asistenciaPersonal($desde, $hasta);
            $incidenciasResumen = $this->incidenciasResumen($desde, $hasta);
            $incidencias = $this->incidencias($desde, $hasta);
        }

        $asistenciaSecciones = $this->agruparAsistenciaSecciones($asistenciaAlumnos);
        $kpis = $this->kpis($matriculas, $asistenciaAlumnos, $asistenciaPersonal, $incidenciasResumen);

        return compact(
            'institucion', 'periodos', 'anios', 'periodo', 'anio', 'desde', 'hasta',
            'secciones', 'idSeccion', 'matriculas', 'asistenciaAlumnos',
            'asistenciaSecciones', 'asistenciaPersonal', 'incidenciasResumen',
            'incidencias', 'kpis'
        );
    }

    private function resolverPeriodo(array $periodos, array $entrada): ?array
    {
        if (!$periodos) return null;
        $id = max(0, (int)($entrada['periodo'] ?? 0));
        $anio = max(0, (int)($entrada['anio'] ?? 0));

        if ($id) {
            foreach ($periodos as $periodo) {
                if ((int)$periodo['id_periodo_academico'] === $id && (!$anio || (int)$periodo['anio'] === $anio)) return $periodo;
            }
        }
        if ($anio) {
            foreach ($periodos as $periodo) if ((int)$periodo['anio'] === $anio && $periodo['estado'] === 'ACTIVO') return $periodo;
            foreach ($periodos as $periodo) if ((int)$periodo['anio'] === $anio) return $periodo;
        }
        foreach ($periodos as $periodo) if ($periodo['estado'] === 'ACTIVO') return $periodo;
        return $periodos[0];
    }

    private function fechaEnPeriodo(string $fecha, string $predeterminada, array $periodo): string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $fecha = $predeterminada;
        if ($fecha < $periodo['fecha_inicio']) return $periodo['fecha_inicio'];
        if ($fecha > $periodo['fecha_fin']) return $periodo['fecha_fin'];
        return $fecha;
    }

    private function matriculas(int $periodo, int $seccion): array
    {
        $sql = "SELECT s.id_seccion,n.nombre nivel,g.nombre grado,s.nombre seccion,s.capacidad,
                       SUM(CASE WHEN m.estado='ACTIVO' THEN 1 ELSE 0 END) activos,
                       SUM(CASE WHEN m.estado='RETIRADO' THEN 1 ELSE 0 END) retirados,
                       SUM(CASE WHEN m.estado='EGRESADO' THEN 1 ELSE 0 END) egresados,
                       SUM(CASE WHEN m.estado='ANULADO' THEN 1 ELSE 0 END) anulados,
                       COUNT(m.id_matricula) total
                FROM secciones s
                INNER JOIN grados g ON g.id_grado=s.id_grado
                INNER JOIN niveles n ON n.id_nivel=g.id_nivel
                LEFT JOIN matriculas m ON m.id_seccion=s.id_seccion AND m.id_periodo_academico=?
                WHERE 1=1";
        $parametros = [$periodo];
        if ($seccion) {
            $sql .= ' AND s.id_seccion=?';
            $parametros[] = $seccion;
        }
        $sql .= ' GROUP BY s.id_seccion,n.nombre,n.orden,g.nombre,g.orden,s.nombre,s.capacidad ORDER BY n.orden,g.orden,s.nombre';
        return $this->consultar($sql, $parametros);
    }

    private function asistenciaAlumnos(int $periodo, int $seccion, string $desde, string $hasta): array
    {
        $sql = "SELECT m.id_matricula,m.codigo_alumno,m.estado estado_matricula,m.id_seccion,
                       CONCAT(p.apellidos,', ',p.nombres) alumno,p.numero_documento,
                       n.nombre nivel,g.nombre grado,s.nombre seccion,
                       SUM(CASE WHEN a.estado IN('PUNTUAL','TARDANZA','FALTA','JUSTIFICADO','PERMISO','LICENCIA','SALIDA_ANTICIPADA','INCOMPLETO') THEN 1 ELSE 0 END) jornadas,
                       SUM(CASE WHEN a.estado IN('PUNTUAL','TARDANZA','SALIDA_ANTICIPADA','INCOMPLETO') THEN 1 ELSE 0 END) asistencias,
                       SUM(CASE WHEN a.estado='PUNTUAL' THEN 1 ELSE 0 END) puntuales,
                       SUM(CASE WHEN a.estado='TARDANZA' THEN 1 ELSE 0 END) tardanzas,
                       SUM(CASE WHEN a.estado='FALTA' THEN 1 ELSE 0 END) faltas,
                       SUM(CASE WHEN a.estado IN('JUSTIFICADO','PERMISO','LICENCIA') THEN 1 ELSE 0 END) justificadas,
                       COALESCE(SUM(a.minutos_tardanza),0) minutos_tardanza
                FROM matriculas m
                INNER JOIN personas p ON p.id_persona=m.id_persona
                INNER JOIN secciones s ON s.id_seccion=m.id_seccion
                INNER JOIN grados g ON g.id_grado=s.id_grado
                INNER JOIN niveles n ON n.id_nivel=g.id_nivel
                LEFT JOIN asistencias_diarias a ON a.id_matricula=m.id_matricula AND a.fecha BETWEEN ? AND ?
                WHERE m.id_periodo_academico=? AND m.estado<>'ANULADO'";
        $parametros = [$desde, $hasta, $periodo];
        if ($seccion) {
            $sql .= ' AND m.id_seccion=?';
            $parametros[] = $seccion;
        }
        $sql .= " GROUP BY m.id_matricula,m.codigo_alumno,m.estado,m.id_seccion,p.apellidos,p.nombres,p.numero_documento,n.nombre,n.orden,g.nombre,g.orden,s.nombre
                  ORDER BY n.orden,g.orden,s.nombre,p.apellidos,p.nombres";
        $filas = $this->consultar($sql, $parametros);
        foreach ($filas as &$fila) $fila['porcentaje'] = (int)$fila['jornadas'] > 0 ? ((int)$fila['asistencias'] / (int)$fila['jornadas']) * 100 : 0;
        unset($fila);
        return $filas;
    }

    private function asistenciaPersonal(string $desde, string $hasta): array
    {
        $sql = "SELECT p.id_persona,CONCAT(p.apellidos,', ',p.nombres) trabajador,p.codigo_trabajador,
                       COALESCE((SELECT c.nombre FROM vinculos_laborales v LEFT JOIN cargos c ON c.id_cargo=v.id_cargo WHERE v.id_persona=p.id_persona ORDER BY v.fecha_inicio DESC,v.id_vinculo DESC LIMIT 1),'Sin cargo') cargo,
                       COALESCE((SELECT GROUP_CONCAT(DISTINCT pp.tipo ORDER BY pp.tipo SEPARATOR ' / ') FROM persona_perfiles pp WHERE pp.id_persona=p.id_persona AND pp.tipo IN('DOCENTE','ADMINISTRATIVO') AND pp.estado='ACTIVO'),'PERSONAL') tipo,
                       x.jornadas,x.asistencias,x.puntuales,x.tardanzas,x.faltas,x.justificadas,x.incompletas,x.minutos_tardanza,x.minutos_trabajados
                FROM (
                    SELECT a.id_persona,
                           SUM(CASE WHEN a.estado IN('PUNTUAL','TARDANZA','FALTA','JUSTIFICADO','PERMISO','LICENCIA','SALIDA_ANTICIPADA','INCOMPLETO') THEN 1 ELSE 0 END) jornadas,
                           SUM(CASE WHEN a.estado IN('PUNTUAL','TARDANZA','SALIDA_ANTICIPADA','INCOMPLETO') THEN 1 ELSE 0 END) asistencias,
                           SUM(CASE WHEN a.estado='PUNTUAL' THEN 1 ELSE 0 END) puntuales,
                           SUM(CASE WHEN a.estado='TARDANZA' THEN 1 ELSE 0 END) tardanzas,
                           SUM(CASE WHEN a.estado='FALTA' THEN 1 ELSE 0 END) faltas,
                           SUM(CASE WHEN a.estado IN('JUSTIFICADO','PERMISO','LICENCIA') THEN 1 ELSE 0 END) justificadas,
                           SUM(CASE WHEN a.estado='INCOMPLETO' THEN 1 ELSE 0 END) incompletas,
                           COALESCE(SUM(a.minutos_tardanza),0) minutos_tardanza,
                           COALESCE(SUM(a.minutos_trabajados),0) minutos_trabajados
                    FROM asistencias_diarias a
                    WHERE a.fecha BETWEEN ? AND ? AND a.id_matricula IS NULL AND (a.id_jornada IS NOT NULL OR a.id_carga IS NOT NULL)
                    GROUP BY a.id_persona
                ) x INNER JOIN personas p ON p.id_persona=x.id_persona
                ORDER BY p.apellidos,p.nombres";
        $filas = $this->consultar($sql, [$desde, $hasta]);
        foreach ($filas as &$fila) $fila['porcentaje'] = (int)$fila['jornadas'] > 0 ? ((int)$fila['asistencias'] / (int)$fila['jornadas']) * 100 : 0;
        unset($fila);
        return $filas;
    }

    private function incidenciasResumen(string $desde, string $hasta): array
    {
        return $this->consultar(
            "SELECT i.estado,i.tipo,COUNT(*) cantidad FROM incidencias_asistencia i INNER JOIN asistencias_diarias a ON a.id_asistencia=i.id_asistencia WHERE a.fecha BETWEEN ? AND ? GROUP BY i.estado,i.tipo ORDER BY FIELD(i.estado,'PENDIENTE','APROBADA','RECHAZADA'),i.tipo",
            [$desde, $hasta]
        );
    }

    private function incidencias(string $desde, string $hasta): array
    {
        return $this->consultar(
            "SELECT i.id_incidencia,i.tipo,i.estado,i.motivo,i.creado_en,i.resuelto_en,a.fecha,a.estado estado_asistencia,CONCAT(p.apellidos,', ',p.nombres) persona FROM incidencias_asistencia i INNER JOIN asistencias_diarias a ON a.id_asistencia=i.id_asistencia INNER JOIN personas p ON p.id_persona=a.id_persona WHERE a.fecha BETWEEN ? AND ? ORDER BY a.fecha DESC,i.creado_en DESC LIMIT 500",
            [$desde, $hasta]
        );
    }

    private function agruparAsistenciaSecciones(array $alumnos): array
    {
        $grupos = [];
        foreach ($alumnos as $fila) {
            $id = (int)$fila['id_seccion'];
            if (!isset($grupos[$id])) {
                $grupos[$id] = [
                    'id_seccion' => $id,
                    'seccion' => $fila['nivel'] . ' · ' . $fila['grado'] . ' ' . $fila['seccion'],
                    'alumnos' => 0, 'jornadas' => 0, 'asistencias' => 0, 'puntuales' => 0,
                    'tardanzas' => 0, 'faltas' => 0, 'justificadas' => 0, 'minutos_tardanza' => 0,
                ];
            }
            $grupos[$id]['alumnos']++;
            foreach (['jornadas','asistencias','puntuales','tardanzas','faltas','justificadas','minutos_tardanza'] as $campo) $grupos[$id][$campo] += (int)$fila[$campo];
        }
        foreach ($grupos as &$grupo) $grupo['porcentaje'] = $grupo['jornadas'] > 0 ? ($grupo['asistencias'] / $grupo['jornadas']) * 100 : 0;
        unset($grupo);
        return array_values($grupos);
    }

    private function kpis(array $matriculas, array $alumnos, array $personal, array $incidencias): array
    {
        $activos = array_sum(array_map('intval', array_column($matriculas, 'activos')));
        $capacidad = array_sum(array_map('intval', array_column($matriculas, 'capacidad')));
        $jornadas = array_sum(array_map('intval', array_column($alumnos, 'jornadas')));
        $asistencias = array_sum(array_map('intval', array_column($alumnos, 'asistencias')));
        $pendientes = 0;
        foreach ($incidencias as $fila) if ($fila['estado'] === 'PENDIENTE') $pendientes += (int)$fila['cantidad'];
        return [
            'matriculados' => $activos,
            'capacidad' => $capacidad,
            'vacantes' => max(0, $capacidad - $activos),
            'asistencia_alumnos' => $jornadas > 0 ? ($asistencias / $jornadas) * 100 : 0,
            'personal_reportado' => count($personal),
            'incidencias_pendientes' => $pendientes,
        ];
    }

    private function consultar(string $sql, array $parametros = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}
