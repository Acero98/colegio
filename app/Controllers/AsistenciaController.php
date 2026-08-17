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
        $asistencias = (new AsistenciaModel($conexion))->resumenHoy();
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
        autorizar('asistencia.ver'); validarCsrf(); global $conexion;
        try { $cantidad = (new AsistenciaModel($conexion))->guardarManual($_POST, (int) $_SESSION['usuario']['id_usuario']); mensaje('success', "Asistencia manual guardada para {$cantidad} alumnos."); }
        catch (Throwable $e) { mensaje('error', 'No se pudo guardar la asistencia manual. Revise la sección y los estados.'); }
        header('Location: index.php?url=asistencia_manual&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d')) . '&id_seccion=' . (int) ($_POST['id_seccion'] ?? 0)); exit;
    }
    public function cerrarDia(): void
    {
        autorizar('asistencia.ver'); validarCsrf(); global $conexion;
        try { $resultado = (new AsistenciaModel($conexion))->cerrarDia($_POST['fecha'] ?? ''); mensaje('success', "Cierre diario completado: {$resultado['faltas']} faltas y {$resultado['incompletas']} salidas pendientes."); }
        catch (DomainException|InvalidArgumentException $e) { mensaje('warning', $e->getMessage()); }
        catch (Throwable $e) { mensaje('error', 'No se pudo realizar el cierre diario.'); }
        header('Location: index.php?url=asistencia_hoy'); exit;
    }
    public function personal(): void
    {
        autorizar('asistencia.ver'); global $conexion; $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $asistencias = (new AsistenciaModel($conexion))->resumenPersonal($fecha);
        require __DIR__ . '/../Views/layouts/header.php'; require __DIR__ . '/../Views/layouts/navbar.php'; require __DIR__ . '/../Views/asistencia/personal.php'; require __DIR__ . '/../Views/layouts/footer.php';
    }
    public function cerrarPersonal(): void
    {
        autorizar('asistencia.ver'); validarCsrf(); global $conexion;
        try { $r = (new AsistenciaModel($conexion))->cerrarPersonalDia($_POST['fecha'] ?? ''); mensaje('success', "Cierre de personal: {$r['faltas_jornada']} faltas de jornada, {$r['faltas_sesion']} faltas de sesión y {$r['incompletas']} salidas pendientes."); }
        catch (DomainException|InvalidArgumentException $e) { mensaje('warning', $e->getMessage()); }
        catch (Throwable $e) { mensaje('error', 'No se pudo realizar el cierre de personal.'); }
        header('Location: index.php?url=asistencia_personal'); exit;
    }
    public function historial(): void
    {
        autorizar('asistencia.ver');global $conexion;$filtros=['fecha_inicio'=>$_GET['fecha_inicio']??date('Y-m-01'),'fecha_fin'=>$_GET['fecha_fin']??date('Y-m-d'),'estado'=>$_GET['estado']??''];$asistencias=(new AsistenciaModel($conexion))->historial($filtros);require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/asistencia/historial.php';require __DIR__.'/../Views/layouts/footer.php';
    }
    public function incidencias(): void
    {
        autorizar('incidencias.ver');global $conexion;$incidencias=(new AsistenciaModel($conexion))->incidencias();$asistencias=(new AsistenciaModel($conexion))->historial([]);require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/asistencia/incidencias.php';require __DIR__.'/../Views/layouts/footer.php';
    }
    public function guardarIncidencia(): void
    {
        autorizar('incidencias.gestionar');validarCsrf();global $conexion;(new AsistenciaModel($conexion))->crearIncidencia($_POST,(int)$_SESSION['usuario']['id_usuario']);(new AuditoriaService($conexion))->registrar('CREAR','incidencias_asistencia',null,null,$_POST);mensaje('success','Incidencia registrada para revisión.');header('Location:index.php?url=incidencias');exit;
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
            if (($_POST['entidad'] ?? '') === 'jornada') $model->crearJornada($_POST);
            if (($_POST['entidad'] ?? '') === 'dispositivo') $model->crearDispositivo($_POST);
            mensaje('success', 'Configuración de asistencia guardada.');
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo guardar. Revise que el código de dispositivo no esté duplicado.');
        }
        header('Location: index.php?url=asistencia_configuracion');
        exit;
    }
}
