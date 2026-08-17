<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/HorarioModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';
class HorarioController{
 public function index():void{autorizar('horarios.ver');global $conexion;extract((new HorarioModel($conexion))->datos());require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/horarios/index_v2.php';require __DIR__.'/../Views/horarios/grupo_v2.php';require __DIR__.'/../Views/horarios/gestion_jornadas_v2.php';require __DIR__.'/../Views/layouts/footer.php';}
 public function guardar():void{autorizar('horarios.gestionar');validarCsrf();global $conexion;try{$entidad=$_POST['entidad']??'';(new HorarioModel($conexion))->guardar($entidad,$_POST);$accion=substr($entidad,-11)==='_actualizar'?'ACTUALIZAR':(substr($entidad,-8)==='_activar'?'ACTIVAR':(substr($entidad,-11)==='_desactivar'?'DESACTIVAR':'CREAR'));(new AuditoriaService($conexion))->registrar($accion,'horarios',null,null,$_POST);mensaje('success','Horario guardado correctamente.');}catch(Throwable $e){mensaje('error','No se pudo guardar. Revise los datos ingresados.');}header('Location:index.php?url=horarios');exit;}
}
