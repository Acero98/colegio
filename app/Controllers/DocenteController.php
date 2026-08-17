<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PersonaModel.php';

class DocenteController
{
    public function index(): void
    {
        autorizar('personas.ver');
        global $conexion;
        $docentes = (new PersonaModel($conexion))->listar('DOCENTE');
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/docentes/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
