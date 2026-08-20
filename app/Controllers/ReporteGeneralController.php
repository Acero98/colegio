<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/ReporteGeneralModel.php';

class ReporteGeneralController
{
    private function datos(): array
    {
        autorizar('reportes.ver');
        global $conexion;
        return (new ReporteGeneralModel($conexion))->datos($_GET);
    }

    public function index(): void
    {
        extract($this->datos());
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/reportes/generales.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function imprimir(): void
    {
        extract($this->datos());
        require __DIR__ . '/../Views/reportes/generales_imprimir.php';
    }
}
