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
        $jornada = $this->obtenerJornada((int) $persona['id_persona']);
        if (!$jornada) {
            throw new InvalidArgumentException('La persona no tiene una jornada configurada.');
        }
        $carga = $this->obtenerCargaVigente((int) $persona['id_persona'], $fechaDispositivo);

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

    private function obtenerJornada(int $idPersona): ?array
    {
        $stmt = $this->db->prepare("SELECT j.* FROM asignaciones_horario ah INNER JOIN jornadas j ON j.id_jornada=ah.id_jornada WHERE ah.id_persona=:persona AND ah.estado='ACTIVO' AND j.estado='ACTIVO' AND (ah.fecha_fin IS NULL OR ah.fecha_fin>=CURDATE()) ORDER BY ah.fecha_inicio DESC LIMIT 1");
        $stmt->execute([':persona' => $idPersona]);
        $jornada = $stmt->fetch();
        if ($jornada) return $jornada;
        $stmt = $this->db->prepare("SELECT j.* FROM jornadas j INNER JOIN persona_perfiles pp ON pp.tipo=j.aplica_a AND pp.id_persona=:persona AND pp.estado='ACTIVO' WHERE j.estado='ACTIVO' ORDER BY FIELD(j.aplica_a,'ALUMNO','DOCENTE','ADMINISTRATIVO') LIMIT 1");
        $stmt->execute([':persona' => $idPersona]);
        return $stmt->fetch() ?: null;
    }

    private function obtenerCargaVigente(int $idPersona, DateTimeImmutable $fechaHora): ?array
    {
        $dia = (int) $fechaHora->format('N');
        $hora = $fechaHora->format('H:i:s');
        $stmt = $this->db->prepare("SELECT c.* FROM carga_academica c INNER JOIN vinculos_laborales v ON v.id_vinculo=c.id_vinculo WHERE v.id_persona=:persona AND c.estado='ACTIVO' AND c.dia_semana=:dia AND :hora BETWEEN SUBTIME(c.hora_inicio, SEC_TO_TIME(c.tolerancia_minutos*60)) AND c.hora_fin ORDER BY c.hora_inicio LIMIT 1");
        $stmt->execute([':persona' => $idPersona, ':dia' => $dia, ':hora' => $hora]);
        return $stmt->fetch() ?: null;
    }

    private function actualizarResumen(int $idPersona, string $fecha, string $hora, string $tipo, array $jornada, string $estado, int $tardanza, ?array $carga): void
    {
        $sql = $carga ? "SELECT id_asistencia,hora_entrada,estado FROM asistencias_diarias WHERE id_persona=:persona AND fecha=:fecha AND id_carga=:carga LIMIT 1" : "SELECT id_asistencia,hora_entrada,estado FROM asistencias_diarias WHERE id_persona=:persona AND fecha=:fecha AND id_jornada=:jornada ORDER BY id_asistencia DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $params = [':persona' => $idPersona, ':fecha' => $fecha];
        $params[$carga ? ':carga' : ':jornada'] = $carga ? $carga['id_carga'] : $jornada['id_jornada'];
        $stmt->execute($params);
        $asistencia = $stmt->fetch();
        if ($tipo === 'ENTRADA' && !$asistencia) {
            $matricula = $this->db->prepare("SELECT id_matricula FROM matriculas WHERE id_persona=:persona AND estado='ACTIVO' LIMIT 1");
            $matricula->execute([':persona' => $idPersona]);
            $idMatricula = $matricula->fetchColumn() ?: null;
            $insertar = $this->db->prepare("INSERT INTO asistencias_diarias(id_persona,fecha,id_jornada,id_matricula,id_carga,hora_entrada,minutos_tardanza,estado,calculado_en) VALUES(:persona,:fecha,:jornada,:matricula,:carga,:hora,:tardanza,:estado,NOW())");
            $insertar->execute([':persona' => $idPersona, ':fecha' => $fecha, ':jornada' => $carga ? null : $jornada['id_jornada'], ':matricula' => $idMatricula, ':carga' => $carga ? $carga['id_carga'] : null, ':hora' => $hora, ':tardanza' => $tardanza, ':estado' => $estado]);
        } elseif ($tipo === 'SALIDA' && $asistencia) {
            $trabajados = max(0, (int) floor((strtotime($fecha . ' ' . $hora) - strtotime($fecha . ' ' . $asistencia['hora_entrada'])) / 60));
            $this->db->prepare("UPDATE asistencias_diarias SET hora_salida=:salida,minutos_trabajados=:trabajados,calculado_en=NOW() WHERE id_asistencia=:id")
                ->execute([':salida' => $hora, ':trabajados' => $trabajados, ':id' => $asistencia['id_asistencia']]);
        }
    }

    public function resumenHoy(): array
    {
        return $this->db->query("SELECT a.*,CONCAT(p.apellidos,', ',p.nombres) persona FROM asistencias_diarias a INNER JOIN personas p ON p.id_persona=a.id_persona WHERE a.fecha=CURDATE() ORDER BY a.hora_entrada DESC")->fetchAll();
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
