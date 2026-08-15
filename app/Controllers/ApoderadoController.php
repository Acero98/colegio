<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PersonaModel.php';

class ApoderadoController
{
    public function index(): void
    {
        autorizar('apoderados.ver');
        global $conexion;
        $apoderados = (new PersonaModel($conexion))->listar('APODERADO');
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/apoderados/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
