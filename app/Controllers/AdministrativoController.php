<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PersonaModel.php';

class AdministrativoController
{
    public function index(): void
    {
        autorizar('personas.ver');
        global $conexion;
        $administrativos = (new PersonaModel($conexion))->listar('ADMINISTRATIVO');
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/administrativos/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
