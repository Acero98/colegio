<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PersonaModel.php';

class AlumnoController
{
    public function index(): void
    {
        autorizar('alumnos.ver');
        global $conexion;
        $alumnos = (new PersonaModel($conexion))->listar('ALUMNO');
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/alumnos/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
