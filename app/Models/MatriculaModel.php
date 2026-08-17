<?php

class MatriculaModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function opciones(): array
    {
        $personas = new PersonaModel($this->db);
        return [
            'periodos' => $this->db->query("SELECT * FROM periodos_academicos WHERE estado='ACTIVO' ORDER BY fecha_inicio DESC")->fetchAll(),
            'periodos_edicion' => $this->db->query('SELECT * FROM periodos_academicos ORDER BY fecha_inicio DESC')->fetchAll(),
            'secciones' => $this->db->query("SELECT s.id_seccion, s.id_grado, CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS nombre, s.capacidad, (SELECT COUNT(*) FROM matriculas m WHERE m.id_seccion=s.id_seccion AND m.estado='ACTIVO' AND m.id_periodo_academico IN (SELECT id_periodo_academico FROM periodos_academicos WHERE estado='ACTIVO')) AS matriculados FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE s.estado='ACTIVO' ORDER BY n.orden,g.orden,s.nombre")->fetchAll(),
            'secciones_edicion' => $this->db->query("SELECT s.id_seccion, CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS nombre, s.estado FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel ORDER BY n.orden,g.orden,s.nombre")->fetchAll(),
            'grados' => $this->db->query("SELECT g.id_grado, CONCAT(n.nombre, ' · ', g.nombre) AS nombre FROM grados g INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE g.estado='ACTIVO' AND n.estado='ACTIVO' ORDER BY n.orden,g.orden")->fetchAll(),
            'alumnos' => $personas->listar('ALUMNO'),
            'apoderados' => $personas->listar('APODERADO'),
        ];
    }

    public function listar(): array
    {
        return $this->db->query("SELECT m.*, CONCAT(p.apellidos, ', ', p.nombres) AS alumno, pa.nombre AS periodo, CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS seccion, GROUP_CONCAT(DISTINCT CONCAT(ap.apellidos, ', ', ap.nombres) SEPARATOR ' · ') AS apoderados FROM matriculas m INNER JOIN personas p ON p.id_persona=m.id_persona INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel LEFT JOIN alumno_apoderado aa ON aa.id_alumno=m.id_persona AND aa.estado='ACTIVO' LEFT JOIN personas ap ON ap.id_persona=aa.id_apoderado GROUP BY m.id_matricula ORDER BY pa.fecha_inicio DESC,p.apellidos,p.nombres")->fetchAll();
    }

    public function crear(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $cupo = $this->db->prepare('SELECT capacidad FROM secciones WHERE id_seccion=:seccion AND estado="ACTIVO" FOR UPDATE');
            $cupo->execute([':seccion' => $data['id_seccion']]);
            $capacidad = $cupo->fetchColumn();
            if ($capacidad === false) {
                throw new DomainException('La sección seleccionada no está disponible.');
            }
            if ($capacidad !== null) {
                $ocupacion = $this->db->prepare('SELECT COUNT(*) FROM matriculas WHERE id_seccion=:seccion AND id_periodo_academico=:periodo AND estado="ACTIVO"');
                $ocupacion->execute([':seccion' => $data['id_seccion'], ':periodo' => $data['id_periodo']]);
                if ((int) $ocupacion->fetchColumn() >= (int) $capacidad) {
                    throw new DomainException('La sección alcanzó su capacidad máxima. Seleccione otra sección o amplíe su cupo.');
                }
            }
            $codigo = trim($data['codigo_alumno']);
            if ($codigo === '') {
                $anio = date('Y', strtotime($data['fecha_matricula']));
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM matriculas WHERE YEAR(fecha_matricula)=:anio');
                $stmt->execute([':anio' => $anio]);
                $codigo = 'A-' . $anio . '-' . str_pad((string) ((int) $stmt->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
            }
            $stmt = $this->db->prepare('INSERT INTO matriculas(id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado) VALUES(:alumno,:periodo,:seccion,:codigo,:fecha,"ACTIVO")');
            $stmt->execute([
                ':alumno' => $data['id_alumno'], ':periodo' => $data['id_periodo'], ':seccion' => $data['id_seccion'],
                ':codigo' => $codigo, ':fecha' => $data['fecha_matricula']
            ]);
            $idMatricula = (int) $this->db->lastInsertId();
            if (!empty($data['id_apoderado'])) {
                $relacion = $this->db->prepare("INSERT INTO alumno_apoderado(id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,estado) VALUES(:alumno,:apoderado,:parentesco,TRUE,TRUE,'ACTIVO') ON DUPLICATE KEY UPDATE parentesco=VALUES(parentesco),es_principal=TRUE,responsable_economico=TRUE,estado='ACTIVO'");
                $relacion->execute([':alumno' => $data['id_alumno'], ':apoderado' => $data['id_apoderado'], ':parentesco' => $data['parentesco']]);
            }
            $this->db->commit();
            return $idMatricula;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizar(array $data): int
    {
        $idMatricula = (int) ($data['id_matricula'] ?? 0);
        $estado = $data['estado'] ?? '';
        if ($idMatricula < 1 || !in_array($estado, ['ACTIVO', 'RETIRADO', 'ANULADO', 'EGRESADO'], true)) {
            throw new InvalidArgumentException('Datos de matrícula no válidos.');
        }
        $this->db->beginTransaction();
        try {
            $actual = $this->db->prepare('SELECT id_persona FROM matriculas WHERE id_matricula=? FOR UPDATE');
            $actual->execute([$idMatricula]);
            $idAlumno = $actual->fetchColumn();
            if (!$idAlumno) throw new RuntimeException('Matrícula no encontrada.');

            if ($estado === 'ACTIVO') {
                $cupo = $this->db->prepare('SELECT capacidad FROM secciones WHERE id_seccion=:seccion AND estado="ACTIVO" FOR UPDATE');
                $cupo->execute([':seccion' => $data['id_seccion']]);
                $capacidad = $cupo->fetchColumn();
                if ($capacidad === false) throw new DomainException('La sección seleccionada no está disponible.');
                if ($capacidad !== null) {
                    $ocupacion = $this->db->prepare('SELECT COUNT(*) FROM matriculas WHERE id_seccion=:seccion AND id_periodo_academico=:periodo AND estado="ACTIVO" AND id_matricula<>:matricula');
                    $ocupacion->execute([':seccion' => $data['id_seccion'], ':periodo' => $data['id_periodo'], ':matricula' => $idMatricula]);
                    if ((int) $ocupacion->fetchColumn() >= (int) $capacidad) throw new DomainException('La sección alcanzó su capacidad máxima. Seleccione otra sección o amplíe su cupo.');
                }
            }

            $codigo = trim($data['codigo_alumno'] ?? '');
            if ($codigo === '') throw new InvalidArgumentException('El código de alumno es obligatorio.');
            $stmt = $this->db->prepare('UPDATE matriculas SET id_periodo_academico=:periodo,id_seccion=:seccion,codigo_alumno=:codigo,fecha_matricula=:fecha,estado=:estado WHERE id_matricula=:matricula');
            $stmt->execute([':periodo' => $data['id_periodo'], ':seccion' => $data['id_seccion'], ':codigo' => $codigo, ':fecha' => $data['fecha_matricula'], ':estado' => $estado, ':matricula' => $idMatricula]);

            if (!empty($data['id_apoderado'])) {
                $this->db->prepare('UPDATE alumno_apoderado SET es_principal=FALSE,responsable_economico=FALSE WHERE id_alumno=? AND estado="ACTIVO"')->execute([$idAlumno]);
                $relacion = $this->db->prepare("INSERT INTO alumno_apoderado(id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,estado) VALUES(:alumno,:apoderado,:parentesco,TRUE,TRUE,'ACTIVO') ON DUPLICATE KEY UPDATE parentesco=VALUES(parentesco),es_principal=TRUE,responsable_economico=TRUE,estado='ACTIVO'");
                $relacion->execute([':alumno' => $idAlumno, ':apoderado' => $data['id_apoderado'], ':parentesco' => $data['parentesco'] ?: 'Apoderado']);
            }
            $this->db->commit();
            return $idMatricula;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function candidatosPromocion(int $idOrigen, int $idDestino, int $idGrado = 0, int $idSeccion = 0): array
    {
        if ($idOrigen < 1 || $idDestino < 1 || $idOrigen === $idDestino) return [];
        $filtro = $idSeccion > 0 ? ' AND s.id_seccion=:seccion' : ($idGrado > 0 ? ' AND g.id_grado=:grado' : '');
        $stmt = $this->db->prepare("SELECT m.id_matricula,m.id_persona,m.codigo_alumno,g.id_grado,s.id_seccion AS id_seccion_origen,s.nombre AS nombre_seccion_origen,CONCAT(p.apellidos, ', ', p.nombres) AS alumno,CONCAT(n.nombre, ' · ', g.nombre, ' ', s.nombre) AS seccion_origen,
            (SELECT siguiente.id_grado FROM grados siguiente INNER JOIN niveles nivel_siguiente ON nivel_siguiente.id_nivel=siguiente.id_nivel WHERE siguiente.estado='ACTIVO' AND nivel_siguiente.estado='ACTIVO' AND (nivel_siguiente.orden>n.orden OR (nivel_siguiente.orden=n.orden AND siguiente.orden>g.orden)) ORDER BY nivel_siguiente.orden,siguiente.orden LIMIT 1) AS id_grado_siguiente
            FROM matriculas m INNER JOIN personas p ON p.id_persona=m.id_persona INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel
            WHERE m.id_periodo_academico=:origen AND m.estado='ACTIVO' AND NOT EXISTS (SELECT 1 FROM matriculas destino WHERE destino.id_persona=m.id_persona AND destino.id_periodo_academico=:destino)$filtro
            ORDER BY p.apellidos,p.nombres");
        $parametros = [':origen' => $idOrigen, ':destino' => $idDestino];
        if ($idSeccion > 0) $parametros[':seccion'] = $idSeccion;
        elseif ($idGrado > 0) $parametros[':grado'] = $idGrado;
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function promover(array $data): array
    {
        $idOrigen = (int) ($data['id_origen'] ?? 0);
        $idDestino = (int) ($data['id_destino'] ?? 0);
        if ($idOrigen < 1 || $idDestino < 1 || $idOrigen === $idDestino) throw new InvalidArgumentException('Seleccione períodos diferentes.');
        $promover = $data['promover'] ?? [];
        $secciones = $data['seccion_destino'] ?? [];
        $repetir = $data['repetir'] ?? [];
        $seccionesRepite = $data['seccion_repite'] ?? [];
        $retirar = array_map('intval', $data['retirar'] ?? []);
        $retirar = array_values(array_unique($retirar));
        $egresar = array_values(array_diff(array_unique(array_map('intval', $data['egresar'] ?? [])), $retirar));
        $repetir = array_diff_key($repetir, $promover);
        $this->db->beginTransaction();
        try {
            $periodo = $this->db->prepare("SELECT fecha_inicio FROM periodos_academicos WHERE id_periodo_academico=? AND estado IN('PLANIFICADO','ACTIVO') FOR UPDATE");
            $periodo->execute([$idDestino]);
            $fechaInicio = $periodo->fetchColumn();
            if (!$fechaInicio) throw new DomainException('El período destino debe estar planificado o activo.');

            $retirados = 0;
            if ($retirar) {
                $marcarRetiro = $this->db->prepare("UPDATE matriculas SET estado='RETIRADO' WHERE id_matricula=? AND id_periodo_academico=? AND estado='ACTIVO'");
                foreach ($retirar as $idMatricula) {
                    $marcarRetiro->execute([$idMatricula, $idOrigen]);
                    $retirados += $marcarRetiro->rowCount();
                }
            }
            $egresados = 0;
            if ($egresar) {
                $marcarEgreso = $this->db->prepare("UPDATE matriculas SET estado='EGRESADO' WHERE id_matricula=? AND id_periodo_academico=? AND estado='ACTIVO'");
                foreach ($egresar as $idMatricula) {
                    $marcarEgreso->execute([$idMatricula, $idOrigen]);
                    $egresados += $marcarEgreso->rowCount();
                }
            }

            $buscarAlumno = $this->db->prepare("SELECT m.id_persona,g.id_grado,n.orden AS orden_nivel,g.orden AS orden_grado FROM matriculas m INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE m.id_matricula=? AND m.id_periodo_academico=? AND m.estado='ACTIVO' FOR UPDATE");
            $existeDestino = $this->db->prepare('SELECT 1 FROM matriculas WHERE id_persona=? AND id_periodo_academico=? LIMIT 1');
            $seccion = $this->db->prepare('SELECT s.capacidad,s.id_grado FROM secciones s INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE s.id_seccion=? AND s.estado="ACTIVO" AND g.estado="ACTIVO" AND n.estado="ACTIVO" FOR UPDATE');
            $siguienteGrado = $this->db->prepare("SELECT siguiente.id_grado FROM grados siguiente INNER JOIN niveles nivel_siguiente ON nivel_siguiente.id_nivel=siguiente.id_nivel WHERE siguiente.estado='ACTIVO' AND nivel_siguiente.estado='ACTIVO' AND (nivel_siguiente.orden>? OR (nivel_siguiente.orden=? AND siguiente.orden>?)) ORDER BY nivel_siguiente.orden,siguiente.orden LIMIT 1");
            $ocupacion = $this->db->prepare('SELECT COUNT(*) FROM matriculas WHERE id_seccion=? AND id_periodo_academico=? AND estado="ACTIVO"');
            $insertar = $this->db->prepare('INSERT INTO matriculas(id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado) VALUES(?,?,?,?,?,"ACTIVO")');
            $promovidos = 0;
            $repitentes = 0;
            $anio = date('Y', strtotime($fechaInicio));
            foreach ($promover + $repetir as $idMatricula => $seleccionado) {
                $idMatricula = (int) $idMatricula;
                if ($idMatricula < 1 || in_array($idMatricula, $retirar, true) || in_array($idMatricula, $egresar, true)) continue;
                $esRepite = array_key_exists($idMatricula, $repetir);
                $idSeccion = (int) ($esRepite ? ($seccionesRepite[$idMatricula] ?? 0) : ($secciones[$idMatricula] ?? 0));
                if ($idSeccion < 1) throw new DomainException('Seleccione una sección destino para cada alumno promovido.');
                $buscarAlumno->execute([$idMatricula, $idOrigen]);
                $alumno = $buscarAlumno->fetch();
                if (!$alumno) continue;
                $idAlumno = (int) $alumno['id_persona'];
                $existeDestino->execute([$idAlumno, $idDestino]);
                if ($existeDestino->fetchColumn()) throw new DomainException('Uno de los alumnos ya tiene matrícula en el período destino.');
                $seccion->execute([$idSeccion]);
                $datosSeccion = $seccion->fetch();
                if (!$datosSeccion) throw new DomainException('Una sección destino no está disponible.');
                if ($esRepite) {
                    if ((int) $alumno['id_grado'] !== (int) $datosSeccion['id_grado']) throw new DomainException('El alumno repitente debe mantenerse en el mismo grado.');
                } else {
                    $siguienteGrado->execute([$alumno['orden_nivel'], $alumno['orden_nivel'], $alumno['orden_grado']]);
                    if ((int) $siguienteGrado->fetchColumn() !== (int) $datosSeccion['id_grado']) throw new DomainException('La sección destino debe pertenecer al grado inmediatamente siguiente.');
                }
                $capacidad = $datosSeccion['capacidad'];
                if ($capacidad !== null) {
                    $ocupacion->execute([$idSeccion, $idDestino]);
                    if ((int) $ocupacion->fetchColumn() >= (int) $capacidad) throw new DomainException('Una sección destino alcanzó su capacidad máxima.');
                }
                $insertar->execute([$idAlumno, $idDestino, $idSeccion, $this->siguienteCodigo($anio), $fechaInicio]);
                if ($esRepite) $repitentes++; else $promovidos++;
            }
            $this->db->commit();
            return ['promovidos' => $promovidos, 'repitentes' => $repitentes, 'retirados' => $retirados, 'egresados' => $egresados];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function siguienteCodigo(string $anio): string
    {
        $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(codigo_alumno,'-',-1) AS UNSIGNED)) FROM matriculas WHERE codigo_alumno LIKE ?");
        $stmt->execute(['A-' . $anio . '-%']);
        return 'A-' . $anio . '-' . str_pad((string) ((int) $stmt->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
    }
}
