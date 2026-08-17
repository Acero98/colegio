<?php

class AsistenciaModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function configuracion(): array
    {
        return [
            'jornadas' => $this->db->query('SELECT * FROM jornadas ORDER BY aplica_a,nombre')->fetchAll(),
            'dispositivos' => $this->db->query('SELECT id_dispositivo,nombre,codigo,punto_control,estado,ultimo_sync_en FROM dispositivos_escaneo ORDER BY nombre')->fetchAll(),
        ];
    }

    public function crearJornada(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,estado) VALUES(:nombre,:aplica,:entrada,:salida,:tolerancia,:requiere,"ACTIVO")');
        $stmt->execute([
            ':nombre' => $data['nombre'], ':aplica' => $data['aplica_a'], ':entrada' => $data['hora_entrada'],
            ':salida' => $data['hora_salida'], ':tolerancia' => (int) $data['tolerancia_minutos'],
            ':requiere' => !empty($data['requiere_salida']) ? 1 : 0,
        ]);
    }

    public function crearDispositivo(array $data): void
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare('INSERT INTO dispositivos_escaneo(nombre,codigo,token_hash,punto_control,estado) VALUES(:nombre,:codigo,:token,:punto,"ACTIVO")');
        $stmt->execute([
            ':nombre' => $data['nombre'], ':codigo' => strtoupper(trim($data['codigo'])),
            ':token' => hash('sha256', $token), ':punto' => $data['punto_control'],
        ]);
    }

    public function dispositivosActivos(): array
    {
        return $this->db->query("SELECT id_dispositivo,nombre,punto_control FROM dispositivos_escaneo WHERE estado='ACTIVO' ORDER BY nombre")->fetchAll();
    }

    public function registrar(array $entrada, int $idUsuario): array
    {
        $token = trim($entrada['token'] ?? '');
        $idDispositivo = (int) ($entrada['id_dispositivo'] ?? 0);
        $uuid = preg_replace('/[^a-zA-Z0-9-]/', '', $entrada['uuid_cliente'] ?? '');
        if (strlen($token) !== 64 || !$idDispositivo || strlen($uuid) < 16) {
            throw new InvalidArgumentException('Datos de marcación inválidos.');
        }

        $dispositivo = $this->db->prepare("SELECT id_dispositivo FROM dispositivos_escaneo WHERE id_dispositivo=:id AND estado='ACTIVO'");
        $dispositivo->execute([':id' => $idDispositivo]);
        if (!$dispositivo->fetch()) {
            throw new InvalidArgumentException('El dispositivo seleccionado no está activo.');
        }

        $personaStmt = $this->db->prepare("SELECT p.id_persona,p.nombres,p.apellidos,p.fotografia, GROUP_CONCAT(pp.tipo ORDER BY pp.tipo SEPARATOR ', ') perfiles FROM codigos_qr q INNER JOIN personas p ON p.id_persona=q.id_persona INNER JOIN persona_perfiles pp ON pp.id_persona=p.id_persona AND pp.estado='ACTIVO' WHERE q.token_hash=:hash AND q.estado='ACTIVO' AND p.estado='ACTIVO' GROUP BY p.id_persona LIMIT 1");
        $personaStmt->execute([':hash' => hash('sha256', $token)]);
        $persona = $personaStmt->fetch();
        if (!$persona) {
            throw new InvalidArgumentException('Código QR no válido o revocado.');
        }

        $fechaDispositivo = !empty($entrada['fecha_hora_dispositivo'])
            ? new DateTimeImmutable($entrada['fecha_hora_dispositivo'])
            : new DateTimeImmutable('now');
        $fecha = $fechaDispositivo->format('Y-m-d');
        $hora = $fechaDispositivo->format('H:i:s');
        $carga = $this->obtenerCargaVigente((int) $persona['id_persona'], $fechaDispositivo);
        $jornada = $this->obtenerJornada((int) $persona['id_persona'], $fecha);
        if (!$carga && !$jornada) {
            throw new InvalidArgumentException('La persona no tiene una jornada o sesión asignada para esta marcación.');
        }

        if ($carga) {
            $ultima = $this->db->prepare("SELECT m.tipo,m.fecha_hora_dispositivo FROM asistencias_diarias a INNER JOIN marcaciones_asistencia m ON m.id_persona=a.id_persona AND DATE(m.fecha_hora_dispositivo)=a.fecha WHERE a.id_persona=:persona AND a.fecha=:fecha AND a.id_carga=:carga AND m.estado_validacion='VALIDA' ORDER BY m.fecha_hora_dispositivo DESC LIMIT 1");
            $ultima->execute([':persona' => $persona['id_persona'], ':fecha' => $fecha, ':carga' => $carga['id_carga']]);
        } else {
            $ultima = $this->db->prepare("SELECT tipo,fecha_hora_dispositivo FROM marcaciones_asistencia WHERE id_persona=:persona AND DATE(fecha_hora_dispositivo)=:fecha AND estado_validacion='VALIDA' ORDER BY fecha_hora_dispositivo DESC LIMIT 1");
            $ultima->execute([':persona' => $persona['id_persona'], ':fecha' => $fecha]);
        }
        $ultimaMarcacion = $ultima->fetch();
        $tipo = $ultimaMarcacion ? 'SALIDA' : 'ENTRADA';
        if ($ultimaMarcacion && strtotime($fechaDispositivo->format('Y-m-d H:i:s')) - strtotime($ultimaMarcacion['fecha_hora_dispositivo']) < 60) {
            return ['duplicada' => true, 'persona' => $persona, 'tipo' => $ultimaMarcacion['tipo'], 'mensaje' => 'Marcación ya registrada hace menos de un minuto.'];
        }

        $tardanza = 0;
        $estado = 'PUNTUAL';
        if ($tipo === 'ENTRADA') {
            $horaEsperada = $carga ? $carga['hora_inicio'] : $jornada['hora_entrada'];
            $tolerancia = $carga ? $carga['tolerancia_minutos'] : $jornada['tolerancia_minutos'];
            $limite = strtotime($fecha . ' ' . $horaEsperada) + ((int) $tolerancia * 60);
            $tardanza = max(0, (int) floor((strtotime($fechaDispositivo->format('Y-m-d H:i:s')) - $limite) / 60));
            $estado = $tardanza > 0 ? 'TARDANZA' : 'PUNTUAL';
        }

        $this->db->beginTransaction();
        try {
            $marcacion = $this->db->prepare("INSERT INTO marcaciones_asistencia(uuid_cliente,id_persona,id_dispositivo,id_usuario,fecha_hora_dispositivo,tipo,metodo,estado_validacion) VALUES(:uuid,:persona,:dispositivo,:usuario,:fechaHora,:tipo,:metodo,'VALIDA')");
            $marcacion->execute([
                ':uuid' => $uuid, ':persona' => $persona['id_persona'], ':dispositivo' => $idDispositivo, ':usuario' => $idUsuario,
                ':fechaHora' => $fechaDispositivo->format('Y-m-d H:i:s'), ':tipo' => $tipo, ':metodo' => !empty($entrada['offline']) ? 'SINCRONIZACION' : 'QR',
            ]);
            $this->actualizarResumen((int) $persona['id_persona'], $fecha, $hora, $tipo, $jornada, $estado, $tardanza, $carga);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return ['duplicada' => false, 'persona' => $persona, 'tipo' => $tipo, 'estado' => $estado, 'tardanza' => $tardanza, 'hora' => $hora];
    }

    private function obtenerJornada(int $idPersona, string $fecha): ?array
    {
        $stmt = $this->db->prepare("SELECT j.* FROM asignaciones_horario ah INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada WHERE ah.id_persona=:persona AND ah.estado='ACTIVO' AND j.estado='ACTIVO' AND ah.fecha_inicio<=:fecha AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=:fecha2) ORDER BY ah.fecha_inicio DESC LIMIT 1");
        $stmt->execute([':persona' => $idPersona, ':fecha' => $fecha, ':fecha2' => $fecha]);
        $jornada = $stmt->fetch();
        if ($jornada) return $jornada;
        $stmt = $this->db->prepare("SELECT j.* FROM jornadas j INNER JOIN persona_perfiles pp ON pp.tipo='ALUMNO' AND pp.id_persona=:persona AND pp.estado='ACTIVO' WHERE j.estado='ACTIVO' AND j.aplica_a='ALUMNO' LIMIT 1");
        $stmt->execute([':persona' => $idPersona]);
        return $stmt->fetch() ?: null;
    }

    private function obtenerCargaVigente(int $idPersona, DateTimeImmutable $fechaHora): ?array
    {
        $dia = (int) $fechaHora->format('N');
        $hora = $fechaHora->format('H:i:s');
        $stmt = $this->db->prepare("SELECT c.* FROM carga_academica c INNER JOIN vinculos_laborales v ON v.id_vinculo=c.id_vinculo INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=c.id_periodo_academico WHERE v.id_persona=:persona AND v.estado='ACTIVO' AND c.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha BETWEEN pa.fecha_inicio AND pa.fecha_fin AND c.dia_semana=:dia AND :hora BETWEEN SUBTIME(c.hora_inicio, SEC_TO_TIME(c.tolerancia_minutos*60)) AND c.hora_fin ORDER BY c.hora_inicio LIMIT 1");
        $stmt->execute([':persona' => $idPersona, ':fecha' => $fechaHora->format('Y-m-d'), ':dia' => $dia, ':hora' => $hora]);
        return $stmt->fetch() ?: null;
    }

    private function actualizarResumen(int $idPersona, string $fecha, string $hora, string $tipo, ?array $jornada, string $estado, int $tardanza, ?array $carga): void
    {
        $idMatriculaVigente = $this->matriculaVigente($idPersona, $fecha);
        $sql = $carga ? "SELECT id_asistencia,hora_entrada,estado FROM asistencias_diarias WHERE id_persona=:persona AND fecha=:fecha AND id_carga=:carga LIMIT 1" : ($idMatriculaVigente ? "SELECT id_asistencia,hora_entrada,estado FROM asistencias_diarias WHERE id_matricula=:matricula AND fecha=:fecha LIMIT 1" : "SELECT id_asistencia,hora_entrada,estado FROM asistencias_diarias WHERE id_persona=:persona AND fecha=:fecha AND id_jornada=:jornada ORDER BY id_asistencia DESC LIMIT 1");
        $stmt = $this->db->prepare($sql);
        $params = [':fecha' => $fecha];
        if ($carga || !$idMatriculaVigente) $params[':persona'] = $idPersona;
        $params[$carga ? ':carga' : ($idMatriculaVigente ? ':matricula' : ':jornada')] = $carga ? $carga['id_carga'] : ($idMatriculaVigente ?: $jornada['id_jornada']);
        $stmt->execute($params);
        $asistencia = $stmt->fetch();
        if ($tipo === 'ENTRADA' && !$asistencia) {
            $idMatricula = $idMatriculaVigente;
            $insertar = $this->db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_jornada,id_matricula,id_carga,hora_entrada,minutos_tardanza,estado,calculado_en) VALUES(:persona,:fecha,:jornada,:matricula,:carga,:hora,:tardanza,:estado,NOW())");
            $insertar->execute([':persona' => $idPersona, ':fecha' => $fecha, ':jornada' => $carga ? null : $jornada['id_jornada'], ':matricula' => $idMatricula, ':carga' => $carga ? $carga['id_carga'] : null, ':hora' => $hora, ':tardanza' => $tardanza, ':estado' => $estado]);
        } elseif ($tipo === 'ENTRADA' && $asistencia && empty($asistencia['hora_entrada'])) {
            $this->db->prepare("UPDATE asistencias_diarias SET hora_entrada=:entrada,minutos_tardanza=:tardanza,estado=:estado,observacion=NULL,calculado_en=NOW() WHERE id_asistencia=:id")
                ->execute([':entrada' => $hora, ':tardanza' => $tardanza, ':estado' => $estado, ':id' => $asistencia['id_asistencia']]);
        } elseif ($tipo === 'SALIDA' && $asistencia) {
            $trabajados = max(0, (int) floor((strtotime($fecha . ' ' . $hora) - strtotime($fecha . ' ' . $asistencia['hora_entrada'])) / 60));
            $this->db->prepare("UPDATE asistencias_diarias SET hora_salida=:salida,minutos_trabajados=:trabajados,estado=IF(estado='INCOMPLETO',IF(minutos_tardanza>0,'TARDANZA','PUNTUAL'),estado),observacion=IF(estado='INCOMPLETO',NULL,observacion),calculado_en=NOW() WHERE id_asistencia=:id")
                ->execute([':salida' => $hora, ':trabajados' => $trabajados, ':id' => $asistencia['id_asistencia']]);
        }
    }

    private function matriculaVigente(int $idPersona, string $fecha): ?int
    {
        $stmt = $this->db->prepare("SELECT m.id_matricula FROM matriculas m INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico WHERE m.id_persona=:persona AND m.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha BETWEEN pa.fecha_inicio AND pa.fecha_fin LIMIT 1");
        $stmt->execute([':persona' => $idPersona, ':fecha' => $fecha]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function seccionesManual(string $fecha): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT s.id_seccion,CONCAT(n.nombre,' · ',g.nombre,' ',s.nombre) AS nombre,n.orden AS orden_nivel,g.orden AS orden_grado FROM matriculas m INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico INNER JOIN secciones s ON s.id_seccion=m.id_seccion INNER JOIN grados g ON g.id_grado=s.id_grado INNER JOIN niveles n ON n.id_nivel=g.id_nivel WHERE m.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha BETWEEN pa.fecha_inicio AND pa.fecha_fin ORDER BY orden_nivel,orden_grado,nombre");
        $stmt->execute([':fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    public function listaManual(int $idSeccion, string $fecha): array
    {
        $stmt = $this->db->prepare("SELECT m.id_matricula,m.id_persona,CONCAT(p.apellidos,', ',p.nombres) alumno,COALESCE(a.estado,'PENDIENTE') estado,a.hora_entrada,a.minutos_tardanza FROM matriculas m INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico INNER JOIN personas p ON p.id_persona=m.id_persona LEFT JOIN asistencias_diarias a ON a.id_matricula=m.id_matricula AND a.fecha=:fecha WHERE m.id_seccion=:seccion AND m.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha2 BETWEEN pa.fecha_inicio AND pa.fecha_fin ORDER BY p.apellidos,p.nombres");
        $stmt->execute([':seccion' => $idSeccion, ':fecha' => $fecha, ':fecha2' => $fecha]);
        return $stmt->fetchAll();
    }

    public function guardarManual(array $data, int $usuario): int
    {
        $fecha = $data['fecha'] ?? '';
        $idSeccion = (int) ($data['id_seccion'] ?? 0);
        $estados = $data['estado'] ?? [];
        $hora = preg_match('/^\d{2}:\d{2}$/', $data['hora_entrada'] ?? '') ? $data['hora_entrada'] . ':00' : date('H:i:s');
        if (!$idSeccion || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !is_array($estados)) throw new InvalidArgumentException('Seleccione una sección, fecha y estados válidos.');
        $alumnos = $this->listaManual($idSeccion, $fecha);
        $permitidos = ['PUNTUAL', 'TARDANZA', 'FALTA', 'JUSTIFICADO', 'PERMISO'];
        $this->db->beginTransaction();
        try {
            $buscar = $this->db->prepare('SELECT id_asistencia FROM asistencias_diarias WHERE id_matricula=? AND fecha=? LIMIT 1');
            $insertar = $this->db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_matricula,hora_entrada,minutos_tardanza,estado,observacion,calculado_en) VALUES(?,?,?,?,?,?,?,NOW())");
            $actualizar = $this->db->prepare("UPDATE asistencias_diarias SET hora_entrada=?,minutos_tardanza=?,estado=?,observacion=?,calculado_en=NOW() WHERE id_asistencia=?");
            $marcacion = $this->db->prepare("INSERT INTO marcaciones_asistencia(uuid_cliente,id_persona,id_usuario,fecha_hora_dispositivo,tipo,metodo,estado_validacion,observacion) VALUES(?,?,?,?,?,'MANUAL','VALIDA',?)");
            $guardados = 0;
            foreach ($alumnos as $alumno) {
                $estado = $estados[$alumno['id_matricula']] ?? '';
                if (!in_array($estado, $permitidos, true)) continue;
                $entrada = in_array($estado, ['PUNTUAL', 'TARDANZA'], true) ? $hora : null;
                $tardanza = $estado === 'TARDANZA' ? 1 : 0;
                $buscar->execute([$alumno['id_matricula'], $fecha]);
                $idAsistencia = $buscar->fetchColumn();
                if ($idAsistencia) $actualizar->execute([$entrada, $tardanza, $estado, 'Registro manual', $idAsistencia]);
                else $insertar->execute([$alumno['id_persona'], $fecha, $alumno['id_matricula'], $entrada, $tardanza, $estado, 'Registro manual']);
                $marcacion->execute([bin2hex(random_bytes(16)), $alumno['id_persona'], $usuario, $fecha . ' ' . $hora, 'ENTRADA', 'Registro manual: ' . $estado]);
                $guardados++;
            }
            $this->db->commit();
            return $guardados;
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function cerrarDia(string $fecha): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new InvalidArgumentException('La fecha no es válida.');
        if ((int) date('N', strtotime($fecha)) >= 6) throw new DomainException('No se puede cerrar asistencia en fin de semana.');
        $calendario = $this->db->prepare('SELECT 1 FROM calendario_institucional WHERE fecha=? LIMIT 1');
        $calendario->execute([$fecha]);
        if ($calendario->fetchColumn()) throw new DomainException('La fecha está marcada como no laborable en el calendario institucional.');
        $sql = "INSERT INTO asistencias_diarias(id_persona,fecha,id_matricula,estado,observacion,calculado_en) SELECT m.id_persona,:fecha,m.id_matricula,'FALTA','Generada por cierre diario',NOW() FROM matriculas m INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico LEFT JOIN asistencias_diarias a ON a.id_matricula=m.id_matricula AND a.fecha=:fecha2 WHERE m.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha3 BETWEEN pa.fecha_inicio AND pa.fecha_fin AND a.id_asistencia IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fecha' => $fecha, ':fecha2' => $fecha, ':fecha3' => $fecha]);
        $faltas = $stmt->rowCount();
        $incompletas = $this->db->prepare("UPDATE asistencias_diarias a INNER JOIN matriculas m ON m.id_matricula=a.id_matricula INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico SET a.estado='INCOMPLETO',a.observacion='Entrada registrada sin salida al cierre diario',a.calculado_en=NOW() WHERE a.fecha=? AND m.estado='ACTIVO' AND pa.estado='ACTIVO' AND a.hora_entrada IS NOT NULL AND a.hora_salida IS NULL AND a.estado IN('PUNTUAL','TARDANZA') AND EXISTS(SELECT 1 FROM jornadas j INNER JOIN persona_perfiles pp ON pp.id_persona=a.id_persona AND pp.tipo=j.aplica_a AND pp.estado='ACTIVO' WHERE j.estado='ACTIVO' AND j.aplica_a IN('DOCENTE','ADMINISTRATIVO') AND j.requiere_salida=TRUE)");
        $incompletas->execute([$fecha]);
        return ['faltas' => $faltas, 'incompletas' => $incompletas->rowCount()];
    }

    public function cerrarPersonalDia(string $fecha): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new InvalidArgumentException('La fecha no es válida.');
        if ((int) date('N', strtotime($fecha)) >= 6) throw new DomainException('No se puede cerrar asistencia en fin de semana.');
        $calendario = $this->db->prepare('SELECT 1 FROM calendario_institucional WHERE fecha=? LIMIT 1');
        $calendario->execute([$fecha]);
        if ($calendario->fetchColumn()) throw new DomainException('La fecha está marcada como no laborable en el calendario institucional.');
        $this->db->beginTransaction();
        try {
            $faltasJornada = $this->db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_jornada,id_vinculo,estado,observacion,calculado_en) SELECT ah.id_persona,:fecha,j.id_jornada,v.id_vinculo,'FALTA','Generada por cierre de jornada',NOW() FROM asignaciones_horario ah INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada INNER JOIN vinculos_laborales v ON v.id_persona=ah.id_persona AND v.estado='ACTIVO' LEFT JOIN asistencias_diarias a ON a.id_persona=ah.id_persona AND a.fecha=:fecha2 AND a.id_jornada=j.id_jornada WHERE ah.estado='ACTIVO' AND j.estado='ACTIVO' AND ah.fecha_inicio<=:fecha3 AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=:fecha4) AND v.fecha_inicio<=:fecha5 AND (v.fecha_fin IS NULL OR v.fecha_fin>=:fecha6) AND a.id_asistencia IS NULL");
            $faltasJornada->execute([':fecha'=>$fecha, ':fecha2'=>$fecha, ':fecha3'=>$fecha, ':fecha4'=>$fecha, ':fecha5'=>$fecha, ':fecha6'=>$fecha]);
            $faltasSesion = $this->db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_carga,id_vinculo,estado,observacion,calculado_en) SELECT v.id_persona,:fecha,c.id_carga,c.id_vinculo,'FALTA','Generada por cierre de sesión',NOW() FROM carga_academica c INNER JOIN vinculos_laborales v ON v.id_vinculo=c.id_vinculo INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=c.id_periodo_academico LEFT JOIN asistencias_diarias a ON a.id_carga=c.id_carga AND a.fecha=:fecha2 WHERE c.estado='ACTIVO' AND v.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha3 BETWEEN pa.fecha_inicio AND pa.fecha_fin AND c.dia_semana=:dia AND a.id_asistencia IS NULL");
            $faltasSesion->execute([':fecha'=>$fecha, ':fecha2'=>$fecha, ':fecha3'=>$fecha, ':dia'=>(int) date('N', strtotime($fecha))]);
            $incompletas = $this->db->prepare("UPDATE asistencias_diarias a INNER JOIN vinculos_laborales v ON v.id_persona=a.id_persona SET a.estado='INCOMPLETO',a.observacion='Entrada registrada sin salida al cierre de personal',a.calculado_en=NOW() WHERE a.fecha=? AND a.id_matricula IS NULL AND (a.id_jornada IS NOT NULL OR a.id_carga IS NOT NULL) AND a.hora_entrada IS NOT NULL AND a.hora_salida IS NULL AND a.estado IN('PUNTUAL','TARDANZA') AND v.estado='ACTIVO'");
            $incompletas->execute([$fecha]);
            $this->db->commit();
            return ['faltas_jornada'=>$faltasJornada->rowCount(), 'faltas_sesion'=>$faltasSesion->rowCount(), 'incompletas'=>$incompletas->rowCount()];
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function resumenPersonal(string $fecha): array
    {
        $stmt = $this->db->prepare("SELECT a.*,CONCAT(p.apellidos,', ',p.nombres) persona,COALESCE(j.nombre,CONCAT('Sesión · ',asig.nombre,' · ',s.nombre)) alcance FROM asistencias_diarias a INNER JOIN personas p ON p.id_persona=a.id_persona LEFT JOIN jornadas j ON j.id_jornada=a.id_jornada LEFT JOIN carga_academica c ON c.id_carga=a.id_carga LEFT JOIN asignaturas asig ON asig.id_asignatura=c.id_asignatura LEFT JOIN secciones s ON s.id_seccion=c.id_seccion WHERE a.fecha=? AND a.id_matricula IS NULL AND (a.id_jornada IS NOT NULL OR a.id_carga IS NOT NULL) ORDER BY persona,alcance");
        $stmt->execute([$fecha]);
        return $stmt->fetchAll();
    }

    public function resumenHoy(): array
    {
        return $this->db->query("SELECT a.*,CONCAT(p.apellidos,', ',p.nombres) persona FROM asistencias_diarias a INNER JOIN personas p ON p.id_persona=a.id_persona WHERE a.fecha=CURDATE() AND a.id_matricula IS NOT NULL ORDER BY a.hora_entrada DESC")->fetchAll();
    }
    public function historial(array $filtros): array
    {
        $sql="SELECT a.*,CONCAT(p.apellidos,', ',p.nombres) persona FROM asistencias_diarias a INNER JOIN personas p ON p.id_persona=a.id_persona WHERE 1=1";$par=[];
        if(!empty($filtros['fecha_inicio'])){$sql.=' AND a.fecha>=:inicio';$par[':inicio']=$filtros['fecha_inicio'];}
        if(!empty($filtros['fecha_fin'])){$sql.=' AND a.fecha<=:fin';$par[':fin']=$filtros['fecha_fin'];}
        if(!empty($filtros['estado'])){$sql.=' AND a.estado=:estado';$par[':estado']=$filtros['estado'];}
        $sql.=' ORDER BY a.fecha DESC,a.hora_entrada DESC LIMIT 500';$s=$this->db->prepare($sql);$s->execute($par);return $s->fetchAll();
    }
    public function incidencias(): array
    {
        return $this->db->query("SELECT i.*,CONCAT(p.apellidos,', ',p.nombres) persona,a.fecha,a.estado asistencia_estado FROM incidencias_asistencia i INNER JOIN asistencias_diarias a ON a.id_asistencia=i.id_asistencia INNER JOIN personas p ON p.id_persona=a.id_persona ORDER BY i.creado_en DESC LIMIT 300")->fetchAll();
    }
    public function crearIncidencia(array $d,int $usuario): void
    {
        $s=$this->db->prepare("INSERT INTO incidencias_asistencia(id_asistencia,tipo,motivo,estado,registrado_por,creado_en) VALUES(:asistencia,:tipo,:motivo,'PENDIENTE',:usuario,NOW())");
        $s->execute([':asistencia'=>$d['id_asistencia'],':tipo'=>$d['tipo'],':motivo'=>trim($d['motivo']),':usuario'=>$usuario]);
    }
}
