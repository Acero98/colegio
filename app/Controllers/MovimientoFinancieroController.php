<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/MovimientoFinancieroModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class MovimientoFinancieroController
{
    private function modelo(): MovimientoFinancieroModel
    {
        global $conexion;
        return new MovimientoFinancieroModel($conexion);
    }

    public function index(): void
    {
        autorizar('cobros.gestionar');
        $desde = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['desde'] ?? '') ? $_GET['desde'] : date('Y-m-01');
        $hasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['hasta'] ?? '') ? $_GET['hasta'] : date('Y-m-d');
        if ($desde > $hasta) [$desde,$hasta] = [$hasta,$desde];
        $filtros = ['desde'=>$desde,'hasta'=>$hasta,'tipo'=>$_GET['tipo']??'','categoria'=>(int)($_GET['categoria']??0),'estado'=>$_GET['estado']??''];
        $modelo = $this->modelo();
        $movimientos = $modelo->listar($filtros);
        $categorias = $modelo->categorias(true);
        $cajaAbierta = $modelo->cajaAbiertaHoy();
        $resumen = $modelo->resumen($movimientos);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/finanzas/movimientos.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardar(): void
    {
        autorizar('cobros.gestionar'); validarCsrf(); global $conexion;
        $archivo = null; $id = null;
        try {
            $archivo = $this->guardarEvidencia($_FILES['evidencia'] ?? []);
            $_POST['evidencia_archivo'] = $archivo;
            $id = $this->modelo()->guardar($_POST,(int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('REGISTRAR_MOVIMIENTO','movimientos_financieros',$id,null,$_POST);
            mensaje('success','Movimiento financiero registrado correctamente.');
        } catch (Throwable $e) {
            if (!$id && $archivo) { $ruta=__DIR__.'/../../storage/movimientos_financieros/'.basename($archivo); if(is_file($ruta)) @unlink($ruta); }
            mensaje('error',$e->getMessage());
        }
        header('Location:index.php?url=finanzas_movimientos'); exit;
    }

    public function anular(): void
    {
        autorizar('cobros.gestionar'); validarCsrf(); global $conexion;
        try {
            $id=(int)($_POST['id_movimiento']??0);
            $this->modelo()->anular($id,$_POST['motivo']??'',(int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('ANULAR_MOVIMIENTO','movimientos_financieros',$id,null,$_POST);
            mensaje('success','Movimiento anulado. Se conserva en el historial.');
        } catch(Throwable $e) { mensaje('error',$e->getMessage()); }
        header('Location:index.php?url=finanzas_movimientos'); exit;
    }

    public function guardarCategoria(): void
    {
        autorizar('cobros.gestionar'); validarCsrf(); global $conexion;
        try {
            $id=$this->modelo()->guardarCategoria($_POST);
            (new AuditoriaService($conexion))->registrar('CREAR_CATEGORIA_FINANCIERA','categorias_movimiento_financiero',$id,null,$_POST);
            mensaje('success','Categoría financiera creada.');
        } catch(Throwable $e) { mensaje('error',$e->getMessage()); }
        header('Location:index.php?url=finanzas_movimientos'); exit;
    }

    public function estadoCategoria(): void
    {
        autorizar('cobros.gestionar'); validarCsrf(); global $conexion;
        try {
            $id=(int)($_POST['id_categoria']??0); $estado=$_POST['estado']??'';
            $this->modelo()->cambiarEstadoCategoria($id,$estado);
            (new AuditoriaService($conexion))->registrar('ESTADO_CATEGORIA_FINANCIERA','categorias_movimiento_financiero',$id,null,$_POST);
            mensaje('success',$estado==='ACTIVO'?'Categoría activada.':'Categoría desactivada.');
        } catch(Throwable $e) { mensaje('error',$e->getMessage()); }
        header('Location:index.php?url=finanzas_movimientos'); exit;
    }

    public function evidencia(): void
    {
        autorizar('cobros.gestionar');
        $archivo=$this->modelo()->evidencia((int)($_GET['id']??0));
        $ruta=$archivo?__DIR__.'/../../storage/movimientos_financieros/'.basename($archivo):'';
        if(!$ruta||!is_file($ruta)){http_response_code(404);exit;}
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($ruta);
        if(!in_array($mime,['image/jpeg','image/png','image/webp','application/pdf'],true)){http_response_code(404);exit;}
        header('Content-Type: '.$mime); header('Content-Disposition:inline; filename="evidencia.'.pathinfo($ruta,PATHINFO_EXTENSION).'"');
        header('X-Content-Type-Options:nosniff'); header('Cache-Control:private,max-age=3600'); readfile($ruta); exit;
    }

    public function reporteImprimir(): void
    {
        autorizar('cobros.gestionar'); global $conexion;
        $desde=preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['desde']??'')?$_GET['desde']:date('Y-m-01');
        $hasta=preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['hasta']??'')?$_GET['hasta']:date('Y-m-d');
        if($desde>$hasta)[$desde,$hasta]=[$hasta,$desde];
        $flujo=$this->modelo()->reporteConsolidado($desde,$hasta);
        $institucion=$conexion->query('SELECT * FROM institucion ORDER BY id_institucion LIMIT 1')->fetch()?:['nombre'=>'Institución educativa'];
        require __DIR__.'/../Views/finanzas/reporte_imprimir.php';
    }

    private function guardarEvidencia(array $archivo): ?string
    {
        if (!$archivo || ($archivo['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE || empty($archivo['name'])) return null;
        $permitidos=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
        if (($archivo['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || (int)($archivo['size']??0)>5*1024*1024 || !is_uploaded_file($archivo['tmp_name']??'')) throw new RuntimeException('La evidencia debe ser JPG, PNG, WEBP o PDF y pesar máximo 5 MB.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if(!isset($permitidos[$mime])) throw new RuntimeException('La evidencia debe ser JPG, PNG, WEBP o PDF y pesar máximo 5 MB.');
        $directorio=__DIR__.'/../../storage/movimientos_financieros';
        if(!is_dir($directorio)&&!mkdir($directorio,0775,true)&&!is_dir($directorio)) throw new RuntimeException('No se pudo preparar el almacenamiento de evidencias.');
        $nombre=bin2hex(random_bytes(20)).'.'.$permitidos[$mime];
        if(!move_uploaded_file($archivo['tmp_name'],$directorio.'/'.$nombre)) throw new RuntimeException('No se pudo guardar la evidencia.');
        return $nombre;
    }
}
