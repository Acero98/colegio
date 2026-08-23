<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/AsistenciaModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class AsistenciaController
{
    public function escanear(): void
    {
        autorizar('asistencia.escanear');
        global $conexion;
        $dispositivos = (new AsistenciaModel($conexion))->dispositivosActivos();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/asistencia/escanear.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function registrar(): void
    {
        autorizar('asistencia.escanear');
        header('Content-Type: application/json; charset=utf-8');
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
            http_response_code(419);
            echo json_encode(['ok' => false, 'mensaje' => 'Sesión no válida. Recargue la pantalla.']);
            return;
        }
        $entrada = json_decode(file_get_contents('php://input'), true);
        if (!is_array($entrada)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Solicitud inválida.']);
            return;
        }
        global $conexion;
        try {
            $resultado = (new AsistenciaModel($conexion))->registrar($entrada, (int) $_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('REGISTRAR_ASISTENCIA', 'marcaciones_asistencia', null, null, ['persona' => $resultado['persona']['id_persona'], 'tipo' => $resultado['tipo']]);
            echo json_encode(['ok' => true, 'resultado' => $resultado]);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'No se pudo registrar la asistencia.']);
        }
    }

    public function hoy(): void
    {
        autorizar('asistencia.ver');
        global $conexion;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $fecha > date('Y-m-d')) $fecha = date('Y-m-d');
        $model = new AsistenciaModel($conexion);
        $asistencias = $model->resumenAlumnos($fecha);
        $cierreSeleccionado = $model->cierreAsistencia($fecha, 'ALUMNOS');
        $cierres = $model->cierresAsistencia('ALUMNOS');
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/asistencia/hoy.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
    public function manual(): void
    {
        autorizar('asistencia.ver'); global $conexion;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $model = new AsistenciaModel($conexion);
        $secciones = $model->seccionesManual($fecha);
        $idSeccion = (int) ($_GET['id_seccion'] ?? 0);
        $alumnos = $idSeccion ? $model->listaManual($idSeccion, $fecha) : [];
        require __DIR__ . '/../Views/layouts/header.php'; require __DIR__ . '/../Views/layouts/navbar.php'; require __DIR__ . '/../Views/asistencia/manual.php'; require __DIR__ . '/../Views/layouts/footer.php';
    }
    public function guardarManual(): void
    {
        autorizar('asistencia.modificar'); validarCsrf(); global $conexion;
        try { $cantidad = (new AsistenciaModel($conexion))->guardarManual($_POST, (int) $_SESSION['usuario']['id_usuario']); mensaje('success', "Asistencia manual guardada para {$cantidad} alumnos."); }
        catch (DomainException|InvalidArgumentException $e) { mensaje('warning', $e->getMessage()); }
        catch (Throwable $e) { mensaje('error', 'No se pudo guardar la asistencia manual. Revise la sección y los estados.'); }
        header('Location: index.php?url=asistencia_manual&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d')) . '&id_seccion=' . (int) ($_POST['id_seccion'] ?? 0)); exit;
    }
    public function cerrarDia(): void
    {
        autorizar('asistencia.modificar'); validarCsrf(); global $conexion;
        try { $resultado = (new AsistenciaModel($conexion))->cerrarAlumnosRegistrado($_POST['fecha'] ?? '', (int)$_SESSION['usuario']['id_usuario']); mensaje('success', "Cierre de alumnos completado: {$resultado['faltas']} faltas generadas."); }
        catch (DomainException|InvalidArgumentException $e) { mensaje('warning', $e->getMessage()); }
        catch (Throwable $e) { mensaje('error', 'No se pudo realizar el cierre diario.'); }
        header('Location: index.php?url=asistencia_hoy&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d'))); exit;
    }
    public function personal(): void
    {
        autorizar('asistencia.ver'); global $conexion; $fecha = $_GET['fecha'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $fecha > date('Y-m-d')) $fecha = date('Y-m-d');
        $model = new AsistenciaModel($conexion);
        $asistencias = $model->resumenPersonal($fecha);
        $cierreSeleccionado = $model->cierreAsistencia($fecha, 'PERSONAL');
        $planillaCerrada = $model->cierrePersonalBloqueado($fecha);
        $cierres = $model->cierresAsistencia('PERSONAL');
        require __DIR__ . '/../Views/layouts/header.php'; require __DIR__ . '/../Views/layouts/navbar.php'; require __DIR__ . '/../Views/asistencia/personal.php'; require __DIR__ . '/../Views/layouts/footer.php';
    }
    public function cerrarPersonal(): void
    {
        autorizar('asistencia.modificar'); validarCsrf(); global $conexion;
        try { $r = (new AsistenciaModel($conexion))->cerrarPersonalRegistrado($_POST['fecha'] ?? '', (int)$_SESSION['usuario']['id_usuario']); mensaje('success', "Cierre de personal: {$r['faltas_jornada']} faltas de jornada, {$r['faltas_sesion']} faltas de sesión y {$r['incompletas']} salidas pendientes."); }
        catch (DomainException|InvalidArgumentException $e) { mensaje('warning', $e->getMessage()); }
        catch (Throwable $e) { mensaje('error', 'No se pudo realizar el cierre de personal.'); }
        header('Location: index.php?url=asistencia_personal&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d'))); exit;
    }
    public function historial(): void
    {
        autorizar('asistencia.ver');global $conexion;$filtros=['fecha_inicio'=>$_GET['fecha_inicio']??date('Y-m-01'),'fecha_fin'=>$_GET['fecha_fin']??date('Y-m-d'),'estado'=>$_GET['estado']??''];$asistencias=(new AsistenciaModel($conexion))->historial($filtros);require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/asistencia/historial.php';require __DIR__.'/../Views/layouts/footer.php';
    }
    public function incidencias(): void
    {
        autorizar('incidencias.ver');global $conexion;$model=new AsistenciaModel($conexion);$incidencias=$model->incidencias();$asistencias=$model->asistenciasParaIncidencia();require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/asistencia/incidencias.php';require __DIR__.'/../Views/layouts/footer.php';
    }
    public function guardarIncidencia(): void
    {
        autorizar('incidencias.gestionar');validarCsrf();global $conexion;
        try{(new AsistenciaModel($conexion))->crearIncidencia($_POST,(int)$_SESSION['usuario']['id_usuario']);(new AuditoriaService($conexion))->registrar('CREAR','incidencias_asistencia',null,null,['id_asistencia'=>$_POST['id_asistencia']??null,'tipo'=>$_POST['tipo']??null,'motivo'=>$_POST['motivo']??null]);mensaje('success','Incidencia registrada para revisión.');}
        catch(DomainException|InvalidArgumentException $e){mensaje('warning',$e->getMessage());}
        catch(Throwable $e){mensaje('error','No se pudo registrar la incidencia.');}
        header('Location:index.php?url=incidencias');exit;
    }
    public function resolverIncidencia(): void
    {
        autorizar('incidencias.gestionar');validarCsrf();global $conexion;
        try{
            $resultado=(new AsistenciaModel($conexion))->resolverIncidencia((int)($_POST['id_incidencia']??0),(string)($_POST['decision']??''),(string)($_POST['observacion']??''),(int)$_SESSION['usuario']['id_usuario'],(string)($_SESSION['usuario']['rol']??''));
            (new AuditoriaService($conexion))->registrar($resultado['decision']==='APROBADA'?'APROBAR':'RECHAZAR','incidencias_asistencia',(int)($_POST['id_incidencia']??0),['estado'=>'PENDIENTE'],['estado'=>$resultado['decision'],'observacion'=>$resultado['observacion'],'asistencia_estado'=>$resultado['asistencia_estado_nuevo']]);
            mensaje('success',$resultado['decision']==='APROBADA'?'Incidencia aprobada correctamente.':'Incidencia rechazada correctamente.');
        }catch(DomainException|InvalidArgumentException $e){mensaje('warning',$e->getMessage());}
        catch(Throwable $e){mensaje('error','No se pudo resolver la incidencia.');}
        header('Location:index.php?url=incidencias');exit;
    }

    public function configuracion(): void
    {
        autorizar('asistencia.configurar');
        global $conexion;
        extract((new AsistenciaModel($conexion))->configuracion());
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/asistencia/configuracion.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardarConfiguracion(): void
    {
        autorizar('asistencia.configurar');
        validarCsrf();
        global $conexion;
        try {
            $model = new AsistenciaModel($conexion);
            $auditoria = new AuditoriaService($conexion);
            $entidad = (string)($_POST['entidad'] ?? '');
            if ($entidad === 'jornada') {
                $id=$model->crearJornada($_POST);$auditoria->registrar('CREAR','jornadas',$id,null,$model->jornadaPorId($id));$mensaje='Jornada registrada correctamente.';
            } elseif ($entidad === 'jornada_actualizar') {
                $id=(int)($_POST['id_jornada']??0);$antes=$model->jornadaPorId($id);$model->actualizarJornada($_POST);$auditoria->registrar('EDITAR','jornadas',$id,$antes,$model->jornadaPorId($id));$mensaje='Jornada actualizada correctamente.';
            } elseif ($entidad === 'jornada_estado') {
                $id=(int)($_POST['id_jornada']??0);$antes=$model->jornadaPorId($id);$model->cambiarEstadoJornada($id,(string)($_POST['estado']??''));$auditoria->registrar(($_POST['estado']??'')==='ACTIVO'?'ACTIVAR':'DESACTIVAR','jornadas',$id,$antes,$model->jornadaPorId($id));$mensaje='Estado de la jornada actualizado.';
            } elseif ($entidad === 'dispositivo') {
                $id=$model->crearDispositivo($_POST);$despues=$model->dispositivoPorId($id);unset($despues['token_hash']);$auditoria->registrar('CREAR','dispositivos_escaneo',$id,null,$despues);$mensaje='Dispositivo autorizado correctamente.';
            } elseif ($entidad === 'dispositivo_actualizar') {
                $id=(int)($_POST['id_dispositivo']??0);$antes=$model->dispositivoPorId($id);unset($antes['token_hash']);$model->actualizarDispositivo($_POST);$despues=$model->dispositivoPorId($id);unset($despues['token_hash']);$auditoria->registrar('EDITAR','dispositivos_escaneo',$id,$antes,$despues);$mensaje='Dispositivo actualizado correctamente.';
            } elseif ($entidad === 'dispositivo_estado') {
                $id=(int)($_POST['id_dispositivo']??0);$antes=$model->dispositivoPorId($id);unset($antes['token_hash']);$model->cambiarEstadoDispositivo($id,(string)($_POST['estado']??''));$despues=$model->dispositivoPorId($id);unset($despues['token_hash']);$auditoria->registrar(($_POST['estado']??'')==='ACTIVO'?'ACTIVAR':'DESACTIVAR','dispositivos_escaneo',$id,$antes,$despues);$mensaje='Estado del dispositivo actualizado.';
            } else throw new InvalidArgumentException('La operación de configuración no es válida.');
            mensaje('success', $mensaje);
        } catch (DomainException|InvalidArgumentException $e) { mensaje('warning',$e->getMessage()); }
        catch (Throwable $e) { mensaje('error', 'No se pudo guardar la configuración de asistencia.'); }
        header('Location: index.php?url=asistencia_configuracion');
        exit;
    }
}
