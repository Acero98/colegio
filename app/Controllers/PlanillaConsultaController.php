<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PlanillaConsultaModel.php';

class PlanillaConsultaController
{
    private function m(): PlanillaConsultaModel
    {
        global $conexion;
        return new PlanillaConsultaModel($conexion);
    }

    public function constancia(): void
    {
        autorizar('planilla.cerrar');
        try {
            extract($this->m()->constancia((int) ($_GET['id'] ?? 0)));
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
            header('Location:index.php?url=planilla');
            exit;
        }
        require __DIR__ . '/../Views/planilla/constancia.php';
    }

    public function historial(): void
    {
        autorizar('planilla.calcular');
        extract($this->m()->historial((int) ($_GET['persona'] ?? 0)));
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/planilla/historial.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function reporte(): void
    {
        autorizar('planilla.calcular');
        extract($this->m()->reporte(
            (int) ($_GET['periodo'] ?? 0),
            (int) ($_GET['persona'] ?? 0),
            (int) ($_GET['anio'] ?? 0),
            $_GET['estado_cuenta'] ?? 'TODOS'
        ));
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/planilla/reporte.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function reporteImprimir(): void
    {
        autorizar('planilla.calcular');
        extract($this->m()->reporte(
            (int) ($_GET['periodo'] ?? 0),
            (int) ($_GET['persona'] ?? 0),
            (int) ($_GET['anio'] ?? 0),
            $_GET['estado_cuenta'] ?? 'TODOS'
        ));
        require __DIR__ . '/../Views/planilla/reporte_imprimir.php';
    }
}
