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
            'jornadas' => $this->db->query("SELECT j.*,(SELECT COUNT(*) FROM asignaciones_horario ah WHERE ah.id_jornada=j.id_jornada AND ah.estado='ACTIVO' AND ah.fecha_inicio<=CURDATE() AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=CURDATE())) asignaciones_activas,(SELECT COUNT(*) FROM asistencias_diarias a WHERE a.id_jornada=j.id_jornada) asistencias FROM jornadas j ORDER BY FIELD(j.estado,'ACTIVO','INACTIVO'),j.aplica_a,j.nombre")->fetchAll(),
            'dispositivos' => $this->db->query("SELECT d.*,(SELECT COUNT(*) FROM marcaciones_asistencia m WHERE m.id_dispositivo=d.id_dispositivo) marcaciones FROM dispositivos_escaneo d ORDER BY FIELD(d.estado,'ACTIVO','INACTIVO'),d.nombre")->fetchAll(),
        ];
    }

    public function crearJornada(array $data): int
    {
        $jornada = $this->validarJornada($data);
        $this->validarNombreJornada($jornada['nombre'], $jornada['aplica_a']);
        $this->db->beginTransaction();
        try {
            if ($jornada['es_predeterminada']) $this->db->exec("UPDATE jornadas SET es_predeterminada=FALSE WHERE aplica_a='ALUMNO'");
            if ($jornada['aplica_a']==='ALUMNO' && !$this->existePredeterminadaAlumnos()) $jornada['es_predeterminada']=1;
            $stmt = $this->db->prepare("INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,es_predeterminada,estado) VALUES(?,?,?,?,?,?,?,'ACTIVO')");
            $stmt->execute([$jornada['nombre'],$jornada['aplica_a'],$jornada['hora_entrada'],$jornada['hora_salida'],$jornada['tolerancia_minutos'],$jornada['requiere_salida'],$jornada['es_predeterminada']]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function actualizarJornada(array $data): int
    {
        $id=(int)($data['id_jornada']??0); if(!$id) throw new InvalidArgumentException('Seleccione una jornada válida.');
        $actual=$this->jornadaPorId($id); if(!$actual) throw new InvalidArgumentException('La jornada no existe.');
        $jornada=$this->validarJornada($data);
        $usos=$this->db->prepare("SELECT (SELECT COUNT(*) FROM asignaciones_horario WHERE id_jornada=?)+(SELECT COUNT(*) FROM asistencias_diarias WHERE id_jornada=?)");$usos->execute([$id,$id]);
        if($actual['aplica_a']!==$jornada['aplica_a'] && (int)$usos->fetchColumn()>0) throw new DomainException('No puede cambiar el tipo porque la jornada ya tiene asignaciones o asistencias.');
        $this->validarNombreJornada($jornada['nombre'],$jornada['aplica_a'],$id);
        $this->db->beginTransaction();
        try{
            if($jornada['es_predeterminada']) $this->db->prepare("UPDATE jornadas SET es_predeterminada=FALSE WHERE aplica_a='ALUMNO' AND id_jornada<>?")->execute([$id]);
            if((int)$actual['es_predeterminada']===1 && !$jornada['es_predeterminada']) throw new DomainException('Antes de quitarla como predeterminada, seleccione otra jornada de alumnos.');
            $stmt=$this->db->prepare('UPDATE jornadas SET nombre=?,aplica_a=?,hora_entrada=?,hora_salida=?,tolerancia_minutos=?,requiere_salida=?,es_predeterminada=? WHERE id_jornada=?');
            $stmt->execute([$jornada['nombre'],$jornada['aplica_a'],$jornada['hora_entrada'],$jornada['hora_salida'],$jornada['tolerancia_minutos'],$jornada['requiere_salida'],$jornada['es_predeterminada'],$id]);
            $this->db->commit(); return $id;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw$e;}
    }

    public function cambiarEstadoJornada(int $id,string $estado): void
    {
        if(!$id||!in_array($estado,['ACTIVO','INACTIVO'],true))throw new InvalidArgumentException('Estado de jornada no válido.');
        $j=$this->jornadaPorId($id);if(!$j)throw new InvalidArgumentException('La jornada no existe.');
        if($j['estado']===$estado)throw new DomainException('La jornada ya se encuentra '.$estado.'.');
        if($estado==='INACTIVO'){
            $q=$this->db->prepare("SELECT COUNT(*) FROM asignaciones_horario WHERE id_jornada=? AND estado='ACTIVO' AND fecha_inicio<=CURDATE() AND (fecha_fin IS NULL OR fecha_fin>=CURDATE())");$q->execute([$id]);
            if((int)$q->fetchColumn()>0)throw new DomainException('No puede desactivarla porque tiene personas asignadas actualmente. Reasígnelas primero desde Horarios.');
            if($j['aplica_a']==='ALUMNO'&&(int)$j['es_predeterminada']===1)throw new DomainException('No puede desactivar la jornada predeterminada. Active o seleccione primero otra jornada de alumnos.');
        }
        $this->db->beginTransaction();try{
            $this->db->prepare('UPDATE jornadas SET estado=? WHERE id_jornada=?')->execute([$estado,$id]);
            if($estado==='ACTIVO'&&$j['aplica_a']==='ALUMNO'&&!$this->existePredeterminadaAlumnos())$this->db->prepare('UPDATE jornadas SET es_predeterminada=TRUE,requiere_salida=FALSE WHERE id_jornada=?')->execute([$id]);
            $this->db->commit();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw$e;}
    }

    public function jornadaPorId(int $id): ?array
    {
        $q=$this->db->prepare('SELECT * FROM jornadas WHERE id_jornada=?');$q->execute([$id]);return$q->fetch()?:null;
    }

    private function validarJornada(array $data): array
    {
        $nombre=trim((string)($data['nombre']??''));$aplica=strtoupper(trim((string)($data['aplica_a']??'')));$entrada=(string)($data['hora_entrada']??'');$salida=(string)($data['hora_salida']??'');$tolerancia=(int)($data['tolerancia_minutos']??-1);
        if(mb_strlen($nombre)<3||mb_strlen($nombre)>100)throw new InvalidArgumentException('El nombre debe tener entre 3 y 100 caracteres.');
        if(!in_array($aplica,['ALUMNO','DOCENTE','ADMINISTRATIVO'],true))throw new InvalidArgumentException('Seleccione a quién aplica la jornada.');
        if(!preg_match('/^\d{2}:\d{2}$/',$entrada)||!preg_match('/^\d{2}:\d{2}$/',$salida)||$entrada>=$salida)throw new InvalidArgumentException('La hora de salida debe ser posterior a la entrada.');
        if($tolerancia<0||$tolerancia>180)throw new InvalidArgumentException('La tolerancia debe estar entre 0 y 180 minutos.');
        return['nombre'=>$nombre,'aplica_a'=>$aplica,'hora_entrada'=>$entrada,'hora_salida'=>$salida,'tolerancia_minutos'=>$tolerancia,'requiere_salida'=>$aplica==='ALUMNO'?0:(!empty($data['requiere_salida'])?1:0),'es_predeterminada'=>$aplica==='ALUMNO'&&!empty($data['es_predeterminada'])?1:0];
    }

    private function validarNombreJornada(string $nombre,string $aplica,int $excepto=0): void
    {
        $q=$this->db->prepare('SELECT COUNT(*) FROM jornadas WHERE nombre=? AND aplica_a=? AND id_jornada<>?');$q->execute([$nombre,$aplica,$excepto]);if((int)$q->fetchColumn()>0)throw new DomainException('Ya existe una jornada con ese nombre para el mismo tipo de persona.');
    }

    private function existePredeterminadaAlumnos(): bool
    {
        return(int)$this->db->query("SELECT COUNT(*) FROM jornadas WHERE aplica_a='ALUMNO' AND estado='ACTIVO' AND es_predeterminada=TRUE")->fetchColumn()>0;
    }

    public function crearDispositivo(array $data): int
    {
        $dispositivo=$this->validarDispositivo($data);
        $this->validarCodigoDispositivo($dispositivo['codigo']);
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare('INSERT INTO dispositivos_escaneo(nombre,codigo,token_hash,punto_control,estado) VALUES(:nombre,:codigo,:token,:punto,"ACTIVO")');
        $stmt->execute([
            ':nombre' => $dispositivo['nombre'], ':codigo' => $dispositivo['codigo'],
            ':token' => hash('sha256', $token), ':punto' => $dispositivo['punto_control'],
        ]);
        return(int)$this->db->lastInsertId();
    }

    public function actualizarDispositivo(array $data): int
    {
        $id=(int)($data['id_dispositivo']??0);if(!$id)throw new InvalidArgumentException('Seleccione un dispositivo válido.');
        $actual=$this->dispositivoPorId($id);if(!$actual)throw new InvalidArgumentException('El dispositivo no existe.');
        $d=$this->validarDispositivo($data);
        $q=$this->db->prepare('SELECT COUNT(*) FROM marcaciones_asistencia WHERE id_dispositivo=?');$q->execute([$id]);$tieneMarcaciones=(int)$q->fetchColumn()>0;
        if($tieneMarcaciones){
            if(strtoupper($actual['codigo'])!==$d['codigo'])throw new DomainException('El código no puede cambiarse porque el dispositivo ya tiene marcaciones.');
            $d['codigo']=$actual['codigo'];
        }
        $this->validarCodigoDispositivo($d['codigo'],$id);
        $this->db->prepare('UPDATE dispositivos_escaneo SET nombre=?,codigo=?,punto_control=? WHERE id_dispositivo=?')->execute([$d['nombre'],$d['codigo'],$d['punto_control'],$id]);
        return$id;
    }

    public function cambiarEstadoDispositivo(int $id,string $estado): void
    {
        if(!$id||!in_array($estado,['ACTIVO','INACTIVO'],true))throw new InvalidArgumentException('Estado de dispositivo no válido.');
        $d=$this->dispositivoPorId($id);if(!$d)throw new InvalidArgumentException('El dispositivo no existe.');
        if($d['estado']===$estado)throw new DomainException('El dispositivo ya se encuentra '.$estado.'.');
        $this->db->prepare('UPDATE dispositivos_escaneo SET estado=? WHERE id_dispositivo=?')->execute([$estado,$id]);
    }

    public function dispositivoPorId(int $id): ?array
    {
        $q=$this->db->prepare('SELECT * FROM dispositivos_escaneo WHERE id_dispositivo=?');$q->execute([$id]);return$q->fetch()?:null;
    }

    private function validarDispositivo(array $data): array
    {
        $nombre=trim((string)($data['nombre']??''));$codigo=strtoupper(trim((string)($data['codigo']??'')));$punto=trim((string)($data['punto_control']??''));
        if(mb_strlen($nombre)<3||mb_strlen($nombre)>100)throw new InvalidArgumentException('El nombre del dispositivo debe tener entre 3 y 100 caracteres.');
        if(!preg_match('/^[A-Z0-9-]{3,50}$/',$codigo))throw new InvalidArgumentException('El código solo puede contener letras, números y guiones.');
        if(mb_strlen($punto)<3||mb_strlen($punto)>120)throw new InvalidArgumentException('Indique un punto de control válido.');
        return['nombre'=>$nombre,'codigo'=>$codigo,'punto_control'=>$punto];
    }

    private function validarCodigoDispositivo(string $codigo,int $excepto=0): void
    {
        $q=$this->db->prepare('SELECT COUNT(*) FROM dispositivos_escaneo WHERE codigo=? AND id_dispositivo<>?');$q->execute([$codigo,$excepto]);if((int)$q->fetchColumn()>0)throw new DomainException('El código del dispositivo ya está registrado.');
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
        $alcanceCierre = $this->matriculaVigente((int) $persona['id_persona'], $fecha) ? 'ALUMNOS' : 'PERSONAL';
        if ($this->cierreRegistrado($fecha, $alcanceCierre)) {
            throw new DomainException('La asistencia de esta fecha ya fue cerrada. Cualquier correccion debe gestionarse mediante incidencias y justificaciones.');
        }
        $carga = $this->obtenerCargaVigente((int) $persona['id_persona'], $fechaDispositivo);
        $jornada = $this->obtenerJornada((int) $persona['id_persona'], $fecha);
        if ($alcanceCierre === 'PERSONAL' && !$carga && $jornada && !$this->esDiaLaborablePersonal((int)$persona['id_persona'], $fecha)) {
            throw new DomainException('Hoy no corresponde a uno de los dias laborables asignados a esta persona. Registre primero un cambio de horario si realizara trabajo extraordinario.');
        }
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
            $this->db->prepare('UPDATE dispositivos_escaneo SET ultimo_sync_en=NOW() WHERE id_dispositivo=?')->execute([$idDispositivo]);
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
        $stmt = $this->db->prepare("SELECT j.* FROM jornadas j INNER JOIN persona_perfiles pp ON pp.tipo='ALUMNO' AND pp.id_persona=:persona AND pp.estado='ACTIVO' WHERE j.estado='ACTIVO' AND j.aplica_a='ALUMNO' ORDER BY j.es_predeterminada DESC,j.id_jornada LIMIT 1");
        $stmt->execute([':persona' => $idPersona]);
        return $stmt->fetch() ?: null;
    }

    private function esDiaLaborablePersonal(int $idPersona, string $fecha): bool
    {
        $dia = (int)(new DateTimeImmutable($fecha))->format('N');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vinculos_laborales WHERE id_persona=? AND estado='ACTIVO' AND fecha_inicio<=? AND (fecha_fin IS NULL OR fecha_fin>=?) AND FIND_IN_SET(?,dias_laborables)>0");
        $stmt->execute([$idPersona,$fecha,$fecha,(string)$dia]);
        return (int)$stmt->fetchColumn() > 0;
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
        if ($this->cierreRegistrado($fecha, 'ALUMNOS')) throw new DomainException('La asistencia de alumnos de esta fecha ya fue cerrada. Use incidencias para realizar una correccion con trazabilidad.');
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

    private function cerrarDia(string $fecha): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new InvalidArgumentException('La fecha no es válida.');
        if ((int) date('N', strtotime($fecha)) >= 6) throw new DomainException('No se puede cerrar asistencia de alumnos en fin de semana.');
        $calendario = $this->db->prepare("SELECT 1 FROM calendario_institucional WHERE fecha=? AND estado='ACTIVO' LIMIT 1");
        $calendario->execute([$fecha]);
        if ($calendario->fetchColumn()) throw new DomainException('La fecha está marcada como no laborable en el calendario institucional.');
        $sql = "INSERT INTO asistencias_diarias(id_persona,fecha,id_matricula,estado,observacion,calculado_en) SELECT m.id_persona,:fecha,m.id_matricula,'FALTA','Generada por cierre diario',NOW() FROM matriculas m INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=m.id_periodo_academico LEFT JOIN asistencias_diarias a ON a.id_matricula=m.id_matricula AND a.fecha=:fecha2 WHERE m.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha3 BETWEEN pa.fecha_inicio AND pa.fecha_fin AND a.id_asistencia IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fecha' => $fecha, ':fecha2' => $fecha, ':fecha3' => $fecha]);
        $faltas = $stmt->rowCount();
        // La salida de los alumnos es opcional; el cierre solo genera faltas para quienes no marcaron.
        $incompletas = $this->db->prepare("UPDATE asistencias_diarias SET calculado_en=calculado_en WHERE fecha=? AND 1=0");
        $incompletas->execute([$fecha]);
        return ['faltas' => $faltas, 'incompletas' => $incompletas->rowCount()];
    }

    private function cerrarPersonalDia(string $fecha): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new InvalidArgumentException('La fecha no es válida.');
        $calendario = $this->db->prepare("SELECT 1 FROM calendario_institucional WHERE fecha=? AND estado='ACTIVO' LIMIT 1");
        $calendario->execute([$fecha]);
        if ($calendario->fetchColumn()) throw new DomainException('La fecha está marcada como no laborable en el calendario institucional.');
        $diaSemana = (int)date('N', strtotime($fecha));
        if (!$this->hayPersonalProgramado($fecha, $diaSemana)) throw new DomainException('No existe personal con jornada o sesion programada para esta fecha.');
        $transaccionPropia = !$this->db->inTransaction();
        if ($transaccionPropia) $this->db->beginTransaction();
        try {
            $faltasJornada = $this->db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_jornada,id_vinculo,estado,observacion,calculado_en) SELECT ah.id_persona,:fecha,j.id_jornada,v.id_vinculo,'FALTA','Generada por cierre de jornada',NOW() FROM asignaciones_horario ah INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada INNER JOIN vinculos_laborales v ON v.id_persona=ah.id_persona AND v.estado='ACTIVO' LEFT JOIN asistencias_diarias a ON a.id_persona=ah.id_persona AND a.fecha=:fecha2 AND a.id_jornada=j.id_jornada WHERE ah.estado='ACTIVO' AND j.estado='ACTIVO' AND ah.fecha_inicio<=:fecha3 AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=:fecha4) AND v.fecha_inicio<=:fecha5 AND (v.fecha_fin IS NULL OR v.fecha_fin>=:fecha6) AND FIND_IN_SET(:dia,v.dias_laborables)>0 AND a.id_asistencia IS NULL");
            $faltasJornada->execute([':fecha'=>$fecha, ':fecha2'=>$fecha, ':fecha3'=>$fecha, ':fecha4'=>$fecha, ':fecha5'=>$fecha, ':fecha6'=>$fecha, ':dia'=>(string)$diaSemana]);
            $faltasSesion = $this->db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_carga,id_vinculo,estado,observacion,calculado_en) SELECT v.id_persona,:fecha,c.id_carga,c.id_vinculo,'FALTA','Generada por cierre de sesión',NOW() FROM carga_academica c INNER JOIN vinculos_laborales v ON v.id_vinculo=c.id_vinculo INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=c.id_periodo_academico LEFT JOIN asistencias_diarias a ON a.id_carga=c.id_carga AND a.fecha=:fecha2 WHERE c.estado='ACTIVO' AND v.estado='ACTIVO' AND pa.estado='ACTIVO' AND :fecha3 BETWEEN pa.fecha_inicio AND pa.fecha_fin AND c.dia_semana=:dia AND a.id_asistencia IS NULL");
            $faltasSesion->execute([':fecha'=>$fecha, ':fecha2'=>$fecha, ':fecha3'=>$fecha, ':dia'=>$diaSemana]);
            $incompletas = $this->db->prepare("UPDATE asistencias_diarias a INNER JOIN vinculos_laborales v ON v.id_persona=a.id_persona SET a.estado='INCOMPLETO',a.observacion='Entrada registrada sin salida al cierre de personal',a.calculado_en=NOW() WHERE a.fecha=? AND a.id_matricula IS NULL AND (a.id_carga IS NOT NULL OR (a.id_jornada IS NOT NULL AND FIND_IN_SET(?,v.dias_laborables)>0)) AND a.hora_entrada IS NOT NULL AND a.hora_salida IS NULL AND a.estado IN('PUNTUAL','TARDANZA') AND v.estado='ACTIVO'");
            $incompletas->execute([$fecha,(string)$diaSemana]);
            if ($transaccionPropia) $this->db->commit();
            return ['faltas_jornada'=>$faltasJornada->rowCount(), 'faltas_sesion'=>$faltasSesion->rowCount(), 'incompletas'=>$incompletas->rowCount()];
        } catch (Throwable $e) { if ($transaccionPropia && $this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    private function hayPersonalProgramado(string $fecha, int $diaSemana): bool
    {
        $jornadas = $this->db->prepare("SELECT COUNT(*) FROM asignaciones_horario ah INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada AND j.estado='ACTIVO' INNER JOIN vinculos_laborales v ON v.id_persona=ah.id_persona AND v.estado='ACTIVO' WHERE ah.estado='ACTIVO' AND ah.fecha_inicio<=? AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=?) AND v.fecha_inicio<=? AND (v.fecha_fin IS NULL OR v.fecha_fin>=?) AND FIND_IN_SET(?,v.dias_laborables)>0");
        $jornadas->execute([$fecha,$fecha,$fecha,$fecha,(string)$diaSemana]);
        if ((int)$jornadas->fetchColumn() > 0) return true;
        $sesiones = $this->db->prepare("SELECT COUNT(*) FROM carga_academica c INNER JOIN vinculos_laborales v ON v.id_vinculo=c.id_vinculo AND v.estado='ACTIVO' INNER JOIN periodos_academicos pa ON pa.id_periodo_academico=c.id_periodo_academico AND pa.estado='ACTIVO' WHERE c.estado='ACTIVO' AND c.dia_semana=? AND ? BETWEEN pa.fecha_inicio AND pa.fecha_fin AND v.fecha_inicio<=? AND (v.fecha_fin IS NULL OR v.fecha_fin>=?)");
        $sesiones->execute([$diaSemana,$fecha,$fecha,$fecha]);
        return (int)$sesiones->fetchColumn() > 0;
    }

    public function cerrarAlumnosRegistrado(string $fecha, ?int $usuario, string $tipo = 'MANUAL', ?string $proceso = null): array
    {
        $this->validarFechaManualCierre($fecha);
        $periodo = $this->db->prepare("SELECT 1 FROM periodos_academicos WHERE estado='ACTIVO' AND ? BETWEEN fecha_inicio AND fecha_fin LIMIT 1");
        $periodo->execute([$fecha]);
        if (!$periodo->fetchColumn()) throw new DomainException('La fecha no pertenece al periodo academico activo.');
        if ($this->cierreRegistrado($fecha, 'ALUMNOS')) throw new DomainException('La asistencia de alumnos del ' . $fecha . ' ya fue cerrada.');
        $this->db->beginTransaction();
        try {
            $resultado = $this->cerrarDia($fecha);
            $resultado['id_cierre'] = $this->registrarCierre($fecha, 'ALUMNOS', $tipo, $usuario, $proceso, (int)$resultado['faltas'], 0, ['faltas_alumnos'=>(int)$resultado['faltas']]);
            $this->db->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function cerrarPersonalRegistrado(string $fecha, ?int $usuario, string $tipo = 'MANUAL', ?string $proceso = null): array
    {
        $this->validarFechaManualCierre($fecha);
        if ($this->periodoPlanillaCerrado($fecha)) throw new DomainException('No se puede cerrar la asistencia de personal porque la planilla de este periodo ya esta cerrada o pagada.');
        if ($this->cierreRegistrado($fecha, 'PERSONAL')) throw new DomainException('La asistencia de personal del ' . $fecha . ' ya fue cerrada.');
        $this->db->beginTransaction();
        try {
            $resultado = $this->cerrarPersonalDia($fecha);
            $faltas = (int)$resultado['faltas_jornada'] + (int)$resultado['faltas_sesion'];
            $resultado['id_cierre'] = $this->registrarCierre($fecha, 'PERSONAL', $tipo, $usuario, $proceso, $faltas, (int)$resultado['incompletas'], ['faltas_jornada'=>(int)$resultado['faltas_jornada'],'faltas_sesion'=>(int)$resultado['faltas_sesion']]);
            $this->db->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function validarFechaManualCierre(string $fecha): void
    {
        $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$objeto || $objeto->format('Y-m-d') !== $fecha) throw new InvalidArgumentException('La fecha no es valida.');
        if ($fecha > date('Y-m-d')) throw new DomainException('No se puede cerrar una fecha futura.');
    }

    private function registrarCierre(string $fecha, string $alcance, string $tipo, ?int $usuario, ?string $proceso, int $faltas, int $incompletas, array $detalle): int
    {
        if (!in_array($tipo, ['MANUAL','AUTOMATICO'], true)) throw new InvalidArgumentException('El tipo de cierre no es valido.');
        $stmt = $this->db->prepare('INSERT INTO cierres_asistencia(fecha,alcance,tipo,ejecutado_por,proceso,faltas_generadas,incompletas_generadas,detalle) VALUES(?,?,?,?,?,?,?,?)');
        $stmt->execute([$fecha,$alcance,$tipo,$usuario,$proceso,$faltas,$incompletas,json_encode($detalle, JSON_UNESCAPED_UNICODE)]);
        return (int)$this->db->lastInsertId();
    }

    private function cierreRegistrado(string $fecha, string $alcance): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM cierres_asistencia WHERE fecha=? AND alcance=?');
        $stmt->execute([$fecha,$alcance]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function cierreAsistencia(string $fecha, string $alcance): ?array
    {
        $stmt = $this->db->prepare("SELECT ca.*,COALESCE(NULLIF(CONCAT(TRIM(COALESCE(p.apellidos,'')),IF(p.apellidos IS NOT NULL AND p.nombres IS NOT NULL,', ',''),TRIM(COALESCE(p.nombres,''))),''),u.usuario,ca.proceso,'Sistema') responsable FROM cierres_asistencia ca LEFT JOIN usuarios u ON u.id_usuario=ca.ejecutado_por LEFT JOIN personas p ON p.id_persona=u.id_persona WHERE ca.fecha=? AND ca.alcance=? LIMIT 1");
        $stmt->execute([$fecha,$alcance]);
        return $stmt->fetch() ?: null;
    }

    public function cierresAsistencia(string $alcance, int $limite = 30): array
    {
        $limite = max(1, min(100, $limite));
        $stmt = $this->db->prepare("SELECT ca.*,COALESCE(NULLIF(CONCAT(TRIM(COALESCE(p.apellidos,'')),IF(p.apellidos IS NOT NULL AND p.nombres IS NOT NULL,', ',''),TRIM(COALESCE(p.nombres,''))),''),u.usuario,ca.proceso,'Sistema') responsable FROM cierres_asistencia ca LEFT JOIN usuarios u ON u.id_usuario=ca.ejecutado_por LEFT JOIN personas p ON p.id_persona=u.id_persona WHERE ca.alcance=? ORDER BY ca.fecha DESC,ca.ejecutado_en DESC LIMIT {$limite}");
        $stmt->execute([$alcance]);
        return $stmt->fetchAll();
    }

    public function cierrePersonalBloqueado(string $fecha): bool
    {
        return $this->periodoPlanillaCerrado($fecha);
    }

    public function resumenPersonal(string $fecha): array
    {
        $stmt = $this->db->prepare("SELECT a.*,CONCAT(p.apellidos,', ',p.nombres) persona,COALESCE(j.nombre,CONCAT('Sesión · ',asig.nombre,' · ',s.nombre)) alcance FROM asistencias_diarias a INNER JOIN personas p ON p.id_persona=a.id_persona LEFT JOIN jornadas j ON j.id_jornada=a.id_jornada LEFT JOIN carga_academica c ON c.id_carga=a.id_carga LEFT JOIN asignaturas asig ON asig.id_asignatura=c.id_asignatura LEFT JOIN secciones s ON s.id_seccion=c.id_seccion WHERE a.fecha=? AND a.id_matricula IS NULL AND (a.id_jornada IS NOT NULL OR a.id_carga IS NOT NULL) ORDER BY persona,alcance");
        $stmt->execute([$fecha]);
        return $stmt->fetchAll();
    }

    public function resumenAlumnos(string $fecha): array
    {
        $stmt = $this->db->prepare("SELECT a.*,CONCAT(p.apellidos,', ',p.nombres) persona FROM asistencias_diarias a INNER JOIN personas p ON p.id_persona=a.id_persona WHERE a.fecha=? AND a.id_matricula IS NOT NULL ORDER BY a.hora_entrada DESC");
        $stmt->execute([$fecha]);
        return $stmt->fetchAll();
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
        return $this->db->query("SELECT i.*,CONCAT(p.apellidos,', ',p.nombres) persona,a.fecha,a.estado asistencia_estado,
            EXISTS(SELECT 1 FROM periodos_pago pp WHERE a.id_matricula IS NULL AND a.fecha BETWEEN pp.fecha_inicio AND pp.fecha_fin AND pp.estado IN('CERRADO','PAGADO')) planilla_cerrada,
            COALESCE(NULLIF(CONCAT(TRIM(COALESCE(pr.apellidos,'')),IF(pr.apellidos IS NOT NULL AND pr.nombres IS NOT NULL,', ',''),TRIM(COALESCE(pr.nombres,''))),''),ur.usuario,'Sistema') registrado_por_nombre,
            COALESCE(NULLIF(CONCAT(TRIM(COALESCE(ps.apellidos,'')),IF(ps.apellidos IS NOT NULL AND ps.nombres IS NOT NULL,', ',''),TRIM(COALESCE(ps.nombres,''))),''),us.usuario) resuelto_por_nombre
            FROM incidencias_asistencia i
            INNER JOIN asistencias_diarias a ON a.id_asistencia=i.id_asistencia
            INNER JOIN personas p ON p.id_persona=a.id_persona
            LEFT JOIN usuarios ur ON ur.id_usuario=i.registrado_por LEFT JOIN personas pr ON pr.id_persona=ur.id_persona
            LEFT JOIN usuarios us ON us.id_usuario=i.resuelto_por LEFT JOIN personas ps ON ps.id_persona=us.id_persona
            ORDER BY i.creado_en DESC LIMIT 300")->fetchAll();
    }
    public function asistenciasParaIncidencia(): array
    {
        return $this->db->query("SELECT a.id_asistencia,a.fecha,a.estado,CONCAT(p.apellidos,', ',p.nombres) persona
            FROM asistencias_diarias a INNER JOIN personas p ON p.id_persona=a.id_persona
            WHERE a.estado IN('FALTA','TARDANZA','INCOMPLETO','SALIDA_ANTICIPADA')
            AND NOT EXISTS(SELECT 1 FROM incidencias_asistencia ia WHERE ia.id_asistencia=a.id_asistencia AND ia.estado IN('PENDIENTE','APROBADA') AND ia.tipo IN('JUSTIFICACION_FALTA','JUSTIFICACION_TARDANZA','PERMISO','LICENCIA'))
            AND (a.id_matricula IS NOT NULL OR NOT EXISTS(SELECT 1 FROM periodos_pago pp WHERE a.fecha BETWEEN pp.fecha_inicio AND pp.fecha_fin AND pp.estado IN('CERRADO','PAGADO')))
            ORDER BY a.fecha DESC,p.apellidos,p.nombres LIMIT 500")->fetchAll();
    }
    public function crearIncidencia(array $d,int $usuario): void
    {
        $idAsistencia=(int)($d['id_asistencia']??0);$tipo=(string)($d['tipo']??'');$motivo=trim((string)($d['motivo']??''));
        $tipos=['JUSTIFICACION_FALTA','JUSTIFICACION_TARDANZA','PERMISO','LICENCIA','OBSERVACION'];
        if($idAsistencia<1||!in_array($tipo,$tipos,true)||mb_strlen($motivo)<3) throw new InvalidArgumentException('Seleccione una asistencia, un tipo válido y detalle el sustento.');
        $existe=$this->db->prepare('SELECT id_asistencia,fecha,estado,id_matricula FROM asistencias_diarias WHERE id_asistencia=?');$existe->execute([$idAsistencia]);$asistencia=$existe->fetch();
        if(!$asistencia) throw new InvalidArgumentException('La asistencia seleccionada no existe.');
        if(!in_array($asistencia['estado'],['FALTA','TARDANZA','INCOMPLETO','SALIDA_ANTICIPADA'],true)) throw new DomainException('Esta asistencia no admite incidencias porque está puntual, anulada o ya fue regularizada.');
        if($tipo==='JUSTIFICACION_FALTA'&&$asistencia['estado']!=='FALTA') throw new DomainException('La justificación de falta solo puede aplicarse a una asistencia con estado FALTA.');
        if($tipo==='JUSTIFICACION_TARDANZA'&&$asistencia['estado']!=='TARDANZA') throw new DomainException('La justificación de tardanza solo puede aplicarse a una asistencia con estado TARDANZA.');
        if($asistencia['id_matricula']===null&&$this->periodoPlanillaCerrado($asistencia['fecha'])) throw new DomainException('No se puede registrar la incidencia porque la planilla de ese período ya fue cerrada o pagada.');
        $aprobada=$this->db->prepare("SELECT COUNT(*) FROM incidencias_asistencia WHERE id_asistencia=? AND estado='APROBADA' AND tipo IN('JUSTIFICACION_FALTA','JUSTIFICACION_TARDANZA','PERMISO','LICENCIA')");$aprobada->execute([$idAsistencia]);
        if((int)$aprobada->fetchColumn()) throw new DomainException('Esta incidencia ya fue aprobada y permanece disponible únicamente en el historial.');
        $pendiente=$this->db->prepare("SELECT COUNT(*) FROM incidencias_asistencia WHERE id_asistencia=? AND tipo=? AND estado='PENDIENTE'");$pendiente->execute([$idAsistencia,$tipo]);
        if((int)$pendiente->fetchColumn()) throw new DomainException('Ya existe una incidencia pendiente del mismo tipo para esta asistencia.');
        $s=$this->db->prepare("INSERT INTO incidencias_asistencia(id_asistencia,tipo,motivo,estado,registrado_por,creado_en) VALUES(:asistencia,:tipo,:motivo,'PENDIENTE',:usuario,NOW())");
        $s->execute([':asistencia'=>$idAsistencia,':tipo'=>$tipo,':motivo'=>$motivo,':usuario'=>$usuario]);
    }
    public function resolverIncidencia(int $id,string $decision,string $observacion,int $usuario,string $rol): array
    {
        $decision=strtoupper(trim($decision));$observacion=trim($observacion);
        if($id<1||!in_array($decision,['APROBADA','RECHAZADA'],true)) throw new InvalidArgumentException('La decisión indicada no es válida.');
        if(mb_strlen($observacion)<3) throw new InvalidArgumentException('Ingrese una observación de resolución.');
        $transaccionPropia=!$this->db->inTransaction();
        if($transaccionPropia)$this->db->beginTransaction();
        try{
            $q=$this->db->prepare("SELECT i.*,a.estado asistencia_estado,a.fecha asistencia_fecha,a.id_matricula FROM incidencias_asistencia i INNER JOIN asistencias_diarias a ON a.id_asistencia=i.id_asistencia WHERE i.id_incidencia=? FOR UPDATE");$q->execute([$id]);$incidencia=$q->fetch();
            if(!$incidencia) throw new InvalidArgumentException('La incidencia no existe.');
            if($incidencia['estado']!=='PENDIENTE') throw new DomainException('Esta incidencia ya fue resuelta.');
            if($decision==='APROBADA'&&$incidencia['id_matricula']===null&&$this->periodoPlanillaCerrado($incidencia['asistencia_fecha'])) throw new DomainException('No se puede aprobar porque la planilla de ese período ya fue cerrada o pagada. Puede rechazar la solicitud para cerrar el pendiente.');
            $esDireccion=in_array($rol,['Director','Administrador','Super Administrador'],true);
            $requiereDireccion=in_array($incidencia['tipo'],['PERMISO','LICENCIA'],true);
            if($requiereDireccion&&!$esDireccion) throw new DomainException('Los permisos y las licencias deben ser resueltos por Dirección.');
            if($rol==='Director'&&!$requiereDireccion) throw new DomainException('Dirección solo autoriza permisos y licencias. Las justificaciones corresponden al responsable de asistencia.');
            $u=$this->db->prepare('UPDATE incidencias_asistencia SET estado=?,resolucion_observacion=?,resuelto_por=?,resuelto_en=NOW() WHERE id_incidencia=?');$u->execute([$decision,$observacion,$usuario,$id]);
            $nuevoEstado=$incidencia['asistencia_estado'];
            if($decision==='APROBADA'){
                if($incidencia['tipo']==='JUSTIFICACION_FALTA'&&$incidencia['asistencia_estado']==='FALTA')$nuevoEstado='JUSTIFICADO';
                elseif($incidencia['tipo']==='PERMISO')$nuevoEstado='PERMISO';
                elseif($incidencia['tipo']==='LICENCIA')$nuevoEstado='LICENCIA';
                if($nuevoEstado!==$incidencia['asistencia_estado'])$this->db->prepare('UPDATE asistencias_diarias SET estado=?,calculado_en=NOW() WHERE id_asistencia=?')->execute([$nuevoEstado,$incidencia['id_asistencia']]);
            }
            if($transaccionPropia)$this->db->commit();
            return ['incidencia'=>$incidencia,'decision'=>$decision,'observacion'=>$observacion,'asistencia_estado_anterior'=>$incidencia['asistencia_estado'],'asistencia_estado_nuevo'=>$nuevoEstado];
        }catch(Throwable $e){if($transaccionPropia&&$this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    private function periodoPlanillaCerrado(string $fecha): bool
    {
        $q=$this->db->prepare("SELECT COUNT(*) FROM periodos_pago WHERE ? BETWEEN fecha_inicio AND fecha_fin AND estado IN('CERRADO','PAGADO')");$q->execute([$fecha]);return (int)$q->fetchColumn()>0;
    }
}
