<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/HorarioModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';
class HorarioController{
 public function index():void{autorizar('horarios.ver');global $conexion;extract((new HorarioModel($conexion))->datos());require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/horarios/index.php';require __DIR__.'/../Views/layouts/footer.php';}
 public function guardar():void{autorizar('horarios.gestionar');validarCsrf();global $conexion;try{(new HorarioModel($conexion))->guardar($_POST['entidad']??'',$_POST);(new AuditoriaService($conexion))->registrar('CREAR','horarios',null,null,$_POST);mensaje('success','Horario guardado correctamente.');}catch(Throwable $e){mensaje('error','No se pudo guardar. Revise que no esté duplicado y complete los datos.');}header('Location:index.php?url=horarios');exit;}
}
