<?php
require_once __DIR__.'/../../config/conexion.php';require_once __DIR__.'/../Models/PortalModel.php';
class PortalController {
    public function index():void {
        autorizar('portal.ver');
        if(empty($_SESSION['usuario']['id_persona'])){http_response_code(403);exit('La cuenta no está vinculada a un apoderado.');}
        global $conexion;$model=new PortalModel($conexion);$id=(int)$_SESSION['usuario']['id_persona'];$hijos=$model->hijos($id);$asistencias=$model->asistencias($id);$deudas=$model->deudas($id);
        require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/portal/index.php';require __DIR__.'/../Views/layouts/footer.php';
    }
}
