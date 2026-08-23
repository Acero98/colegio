<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/CobroModel.php';
require_once __DIR__ . '/../Models/MovimientoFinancieroModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class CobroController
{
    public function index(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $model = new CobroModel($conexion);
        $mes = ($_GET['mes'] ?? date('Y-m')) === 'todos' ? '' : (preg_match('/^\d{4}-\d{2}$/', $_GET['mes'] ?? '') ? $_GET['mes'] : date('Y-m'));
        $estado = $_GET['estado'] ?? '';
        $buscar = trim($_GET['buscar'] ?? '');
        $idAlumno = (int) ($_GET['alumno'] ?? 0);
        $abrirConfiguracion = ($_GET['abrir'] ?? '') === 'tarifas';
        $opciones = $model->opciones();
        $grados = $model->grados();
        $cuentas = $model->listarCuentas($mes, $estado, $buscar);
        $resumen = $model->resumen($mes);
        $alertas = $model->alertasCobro();
        $estadoCuenta = $idAlumno ? $model->estadoCuentaAlumno($idAlumno) : [];
        $cajaAbierta = $model->cajaAbierta();
        $cajaDisponibleHoy = $cajaAbierta && $cajaAbierta['fecha'] === date('Y-m-d');
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function generar(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            $total = (new CobroModel($conexion))->generarMensualidades($_POST);
            (new AuditoriaService($conexion))->registrar('GENERAR', 'cuentas_cobrar', null, null, $_POST);
            mensaje('success', $total . ' cuenta(s) generada(s). Las cuentas existentes no se duplicaron.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes']));
        exit;
    }

    public function pagar(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        $archivosGuardados = [];
        $recibo = null;
        try {
            $archivosGuardados = $this->guardarEvidenciasPago($_FILES['evidencias'] ?? [], $_POST['medios'] ?? []);
            $_POST['evidencias_pago'] = $archivosGuardados;
            $recibo = (new CobroModel($conexion))->registrarPago($_POST, (int) $_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('COBRAR', 'pagos_alumno', (int)$recibo['id_pago'], null, ['numero_recibo' => $recibo['numero_recibo']] + $_POST);
            header('Location:index.php?url=cobros_recibo&id=' . (int)$recibo['id_pago'] . '&nuevo=1');
            exit;
        } catch (Throwable $e) {
            if (!$recibo) {
                foreach ($archivosGuardados as $archivo) {
                    $ruta = __DIR__ . '/../../storage/comprobantes_pago/' . basename($archivo);
                    if (is_file($ruta)) @unlink($ruta);
                }
            }
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes']));
        exit;
    }

    private function guardarEvidenciasPago(array $archivos, array $medios): array
    {
        if (!$archivos || !isset($archivos['name'], $archivos['error'], $archivos['tmp_name'], $archivos['size'])) return [];
        $mediosPermitidos = ['YAPE', 'PLIN', 'TRANSFERENCIA', 'TARJETA', 'DEPOSITO', 'OTRO'];
        $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $validados = [];
        foreach ((array)$archivos['name'] as $medio => $nombreOriginal) {
            if (!in_array($medio, $mediosPermitidos, true)) continue;
            if (round((float)($medios[$medio]['importe'] ?? 0), 2) <= 0) continue;
            $error = (int)($archivos['error'][$medio] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE || $nombreOriginal === '') continue;
            $temporal = (string)($archivos['tmp_name'][$medio] ?? '');
            $tamano = (int)($archivos['size'][$medio] ?? 0);
            if ($error !== UPLOAD_ERR_OK || $tamano < 1 || $tamano > 5 * 1024 * 1024 || !is_uploaded_file($temporal)) {
                throw new RuntimeException('Cada evidencia debe ser una imagen JPG, PNG o WEBP de máximo 5 MB.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporal);
            if (!isset($tiposPermitidos[$mime])) {
                throw new RuntimeException('Cada evidencia debe ser una imagen JPG, PNG o WEBP de máximo 5 MB.');
            }
            $validados[$medio] = ['temporal' => $temporal, 'extension' => $tiposPermitidos[$mime]];
        }
        if (!$validados) return [];
        $directorio = __DIR__ . '/../../storage/comprobantes_pago';
        if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar el almacenamiento de evidencias.');
        }
        $guardados = [];
        try {
            foreach ($validados as $medio => $archivo) {
                $nombre = bin2hex(random_bytes(20)) . '.' . $archivo['extension'];
                if (!move_uploaded_file($archivo['temporal'], $directorio . '/' . $nombre)) {
                    throw new RuntimeException('No se pudo guardar una de las evidencias del pago.');
                }
                $guardados[$medio] = $nombre;
            }
            return $guardados;
        } catch (Throwable $e) {
            foreach ($guardados as $nombre) {
                $ruta = $directorio . '/' . basename($nombre);
                if (is_file($ruta)) @unlink($ruta);
            }
            throw $e;
        }
    }

    public function evidenciaPago(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $archivo = (new CobroModel($conexion))->evidenciaPago((int)($_GET['id'] ?? 0));
        $ruta = $archivo ? __DIR__ . '/../../storage/comprobantes_pago/' . basename($archivo) : '';
        if (!$ruta || !is_file($ruta)) {
            http_response_code(404);
            exit;
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="evidencia-pago.' . pathinfo($ruta, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');
        readfile($ruta);
        exit;
    }

    public function configurar(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->guardarConfiguracion($_POST);
            mensaje('success', ($_POST['accion'] ?? '') === 'concepto' ? 'Concepto creado.' : 'Tarifa registrada.');
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo guardar. Revise los datos de la tarifa.');
        }
        header('Location:index.php?url=cobros_tarifas');
        exit;
    }

    public function tarifas(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $modelo = new CobroModel($conexion);
        $tarifas = $modelo->tarifas();
        $conceptos = $modelo->conceptos();
        $opciones = $modelo->opciones();
        $grados = $modelo->grados();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/tarifas.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function cambiarEstadoTarifa(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->cambiarEstadoTarifa((int) ($_POST['id_tarifa'] ?? 0), $_POST['estado'] ?? '');
            (new AuditoriaService($conexion))->registrar('CAMBIAR_ESTADO', 'tarifas_cobro', (int) ($_POST['id_tarifa'] ?? 0), null, $_POST);
            mensaje('success', ($_POST['estado'] ?? '') === 'ACTIVO' ? 'Tarifa activada.' : 'Tarifa desactivada. No se usará en nuevas generaciones.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_tarifas');
        exit;
    }

    public function cambiarEstadoConcepto(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->cambiarEstadoConcepto((int) ($_POST['id_concepto_cobro'] ?? 0), $_POST['estado'] ?? '');
            (new AuditoriaService($conexion))->registrar('CAMBIAR_ESTADO', 'conceptos_cobro', (int) ($_POST['id_concepto_cobro'] ?? 0), null, $_POST);
            mensaje('success', ($_POST['estado'] ?? '') === 'ACTIVO' ? 'Concepto activado.' : 'Concepto desactivado. No estará disponible para nuevas generaciones.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_tarifas');
        exit;
    }

    public function pagos(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $pagos = (new CobroModel($conexion))->pagos();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/pagos.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function recibo(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $formato = ($_GET['formato'] ?? 'a4') === 'termico' ? 'termico' : 'a4';
        $imprimir = ($_GET['imprimir'] ?? '') === '1';
        $nuevo = ($_GET['nuevo'] ?? '') === '1';
        try {
            $recibo = (new CobroModel($conexion))->reciboImprimible((int)($_GET['id'] ?? 0));
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
            header('Location:index.php?url=cobros_pagos');
            exit;
        }
        require __DIR__ . '/../Views/cobros/recibo.php';
    }

    public function anularPago(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->anularPago((int)($_POST['id_pago'] ?? 0), $_POST['motivo'] ?? '', (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('ANULAR', 'pagos_alumno', (int)($_POST['id_pago'] ?? 0), null, $_POST);
            mensaje('success', 'Recibo anulado y saldos restaurados.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_pagos');
        exit;
    }

    public function ajustarCuenta(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->ajustarCuenta((int)($_POST['id_cuenta'] ?? 0), $_POST['tipo'] ?? '', (float)($_POST['importe'] ?? 0), $_POST['motivo'] ?? '', (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('AJUSTAR', 'cuentas_cobrar', (int)($_POST['id_cuenta'] ?? 0), null, $_POST);
            mensaje('success', 'Ajuste registrado y saldo recalculado.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes'] ?? date('Y-m')));
        exit;
    }

    public function reportes(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $desde = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['desde'] ?? '') ? $_GET['desde'] : date('Y-m-01');
        $hasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['hasta'] ?? '') ? $_GET['hasta'] : date('Y-m-d');
        if ($desde > $hasta) {
            $temporal = $desde;
            $desde = $hasta;
            $hasta = $temporal;
        }
        $reportes = (new CobroModel($conexion))->reportesFinancieros($desde, $hasta);
        $reportes['flujo'] = (new MovimientoFinancieroModel($conexion))->reporteConsolidado($desde, $hasta);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/reportes.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function estadoCuenta(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        try {
            $estadoCuenta = (new CobroModel($conexion))->estadoCuentaImprimible((int)($_GET['alumno'] ?? 0));
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
            header('Location:index.php?url=cobros');
            exit;
        }
        require __DIR__ . '/../Views/cobros/estado_cuenta.php';
    }

    public function cierreCaja(): void
    {
        requiereLogin();
        if (!tienePermiso('cobros.gestionar') && !tienePermiso('caja.rectificar')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errores/403.php';
            exit;
        }
        global $conexion;
        $modelo = new CobroModel($conexion);
        $cajaPendiente = $modelo->cajaAbierta();
        $fechaSolicitada = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['fecha'] ?? '') ? $_GET['fecha'] : null;
        $fecha = $fechaSolicitada ?: ($cajaPendiente['fecha'] ?? date('Y-m-d'));
        $estadoCaja = $modelo->estadoCaja($fecha);
        $resumenCaja = $modelo->resumenCaja($fecha, (int)($estadoCaja['id_apertura'] ?? 0));
        $cajas = $modelo->cajasDiarias();
        $datosRevision = $modelo->datosRevisionCierres();
        foreach ($cajas as &$caja) {
            $revision = $datosRevision[(int)$caja['id_apertura']] ?? ['id_cierre'=>null,'estado_revision'=>null];
            $caja += $revision;
        }
        unset($caja);
        $mediosCierres = $modelo->mediosCierres();
        $rectificaciones = $modelo->rectificacionesCaja();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/cierre_caja.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardarCierreCaja(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->cerrarCaja($_POST, (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('CERRAR_CAJA', 'cierres_caja', null, null, $_POST);
            mensaje('success', 'Caja cerrada correctamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_cierre_caja&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d')));
        exit;
    }

    public function guardarAperturaCaja(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->abrirCaja($_POST, (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('ABRIR_CAJA', 'aperturas_caja', null, null, $_POST);
            mensaje('success', 'Caja abierta correctamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_cierre_caja&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d')));
        exit;
    }

    public function rectificarCaja(): void
    {
        autorizar('caja.rectificar');
        validarCsrf();
        global $conexion;
        $archivo=null; $id=null;
        try {
            $archivo=$this->guardarEvidenciaRectificacion($_FILES['evidencia']??[]);
            $_POST['evidencia_archivo']=$archivo;
            $modelo=new CobroModel($conexion);
            $id=$modelo->rectificarCaja($_POST,(int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('RECTIFICAR_CAJA','rectificaciones_caja',$id,null,['id_cierre'=>$_POST['id_cierre']??null,'accion'=>$_POST['accion']??null,'motivo_tipo'=>$_POST['motivo_tipo']??null,'motivo'=>$_POST['motivo']??null]);
            mensaje('success','La revisión del cierre fue registrada sin alterar su historial original.');
        } catch(Throwable $e){
            if(!$id&&$archivo){$ruta=__DIR__.'/../../storage/rectificaciones_caja/'.basename($archivo);if(is_file($ruta))@unlink($ruta);}
            mensaje('error',$e->getMessage());
        }
        header('Location:index.php?url=cobros_cierre_caja');exit;
    }

    public function evidenciaRectificacion(): void
    {
        autorizar('caja.rectificar'); global $conexion;
        $archivo=(new CobroModel($conexion))->evidenciaRectificacion((int)($_GET['id']??0));
        $ruta=$archivo?__DIR__.'/../../storage/rectificaciones_caja/'.basename($archivo):'';
        if(!$ruta||!is_file($ruta)){http_response_code(404);exit;}
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($ruta);
        if(!in_array($mime,['image/jpeg','image/png','image/webp','application/pdf'],true)){http_response_code(404);exit;}
        header('Content-Type: '.$mime);header('Content-Disposition:inline; filename="evidencia-rectificacion.'.pathinfo($ruta,PATHINFO_EXTENSION).'"');header('X-Content-Type-Options:nosniff');header('Cache-Control:private,max-age=3600');readfile($ruta);exit;
    }

    private function guardarEvidenciaRectificacion(array $archivo): ?string
    {
        if(!$archivo||($archivo['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE||empty($archivo['name']))return null;
        $permitidos=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
        if(($archivo['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||(int)($archivo['size']??0)>5*1024*1024||!is_uploaded_file($archivo['tmp_name']??''))throw new RuntimeException('La evidencia debe ser JPG, PNG, WEBP o PDF y pesar máximo 5 MB.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if(!isset($permitidos[$mime]))throw new RuntimeException('La evidencia debe ser JPG, PNG, WEBP o PDF y pesar máximo 5 MB.');
        $directorio=__DIR__.'/../../storage/rectificaciones_caja';
        if(!is_dir($directorio)&&!mkdir($directorio,0775,true)&&!is_dir($directorio))throw new RuntimeException('No se pudo preparar el almacenamiento de evidencias.');
        $nombre=bin2hex(random_bytes(20)).'.'.$permitidos[$mime];
        if(!move_uploaded_file($archivo['tmp_name'],$directorio.'/'.$nombre))throw new RuntimeException('No se pudo guardar la evidencia.');
        return $nombre;
    }
}
