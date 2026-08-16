<?php
require_once __DIR__.'/../../config/conexion.php';require_once __DIR__.'/../Models/UsuarioModel.php';require_once __DIR__.'/../Services/AuditoriaService.php';
class UsuarioController {
public function index():void{autorizar('usuarios.gestionar');global $conexion;$m=new UsuarioModel($conexion);$usuarios=$m->listarGestion();$opciones=$m->opcionesGestion();require __DIR__.'/../Views/layouts/header.php';require __DIR__.'/../Views/layouts/navbar.php';require __DIR__.'/../Views/usuarios/index.php';require __DIR__.'/../Views/layouts/footer.php';}
public function guardar():void{autorizar('usuarios.gestionar');validarCsrf();global $conexion;try{$id=(new UsuarioModel($conexion))->crear($_POST);(new AuditoriaService($conexion))->registrar('CREAR','usuarios',$id);mensaje('success','Usuario creado correctamente.');}catch(Throwable $e){mensaje('error','No se pudo crear. Verifique que el usuario sea único y la persona no tenga una cuenta.');}header('Location:index.php?url=usuarios');exit;}
}
